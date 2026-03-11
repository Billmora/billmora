<?php

namespace App\Http\Controllers\Admin\Invoices;

use App\Contracts\Gateway\RefundableGateway;
use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Transaction;
use App\Services\PluginManager;
use App\Traits\AuditsSystem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class RefundController extends Controller
{
    use AuditsSystem;

    /**
     * Applies permission-based middleware for accessing invoice refund.
     * 
     * @return void
     */
    public function __construct()
    {
        $this->middleware('permission:transactions.create')->only(['index', 'store']);
    }

    /**
     * Display the refund form with the maximum refundable amount for the specified invoice.
     *
     * @param  \App\Models\Invoice  $invoice
     * @param  \App\Services\PluginManager  $pluginManager
     * @return \Illuminate\View\View
     */
    public function index(Invoice $invoice, PluginManager $pluginManager)
    {
        $totalRefunded = abs($invoice->transactions()->where('amount', '<', 0)->sum('amount'));
        
        $maxRefundable = max(0, $invoice->total - $totalRefunded);

        return view('admin::invoices.refund.index', compact('invoice', 'maxRefundable', 'totalRefunded'));
    }

    /**
     * Validate and process a gateway or manual refund transaction for the specified invoice.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Invoice  $invoice
     * @param  \App\Services\PluginManager  $pluginManager
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, Invoice $invoice, PluginManager $pluginManager)
    {
        $totalRefunded = abs($invoice->transactions()->where('amount', '<', 0)->sum('amount'));
        $maxRefundable = max(0, $invoice->total - $totalRefunded);

        if ($maxRefundable <= 0) {
            return back()->with('error', __('admin/invoices.refund.already_fully_refunded'));
        }

        $validated = $request->validate([
            'refund_type' => ['required', Rule::in(['gateway', 'manual'])],
            'refund_amount' => ['required', 'numeric', 'min:0.01', 'max:' . $maxRefundable],
            'refund_reference' => ['nullable', 'string', 'max:255'],
        ]);

        if ($validated['refund_type'] === 'gateway') {
            if (!$invoice->plugin) {
                return back()->with('error', __('admin/invoices.refund.no_gateway_associated'));
            }

            $gateway = $pluginManager->bootInstance($invoice->plugin);
            
            if (!$gateway instanceof RefundableGateway) {
                return back()->with('error', __('admin/invoices.refund.gateway_not_support_refund'));
            }

            $success = $gateway->refund($invoice->invoice_number, $validated['refund_amount'], $invoice->currency);

            if (!$success) {
                return back()->with('error', __('admin/invoices.refund.gateway_rejected'));
            }
        }

        $transaction = DB::transaction(function () use ($invoice, $validated, $totalRefunded) {

            $transaction = Transaction::create([
                'user_id' => $invoice->user_id,
                'invoice_id' => $invoice->id,
                'plugin_id' => $validated['refund_type'] === 'gateway' ? $invoice->plugin_id : null,
                'reference' => $validated['refund_reference'],
                'description' => __('admin/invoices.refund.transaction_description', [
                    'type' => ucfirst($validated['refund_type']),
                ]),
                'currency' => $invoice->currency,
                'amount' => -$validated['refund_amount'],
                'fee' => 0,
            ]);

            $newTotalRefunded = $totalRefunded + $validated['refund_amount'];
            
            if (round($newTotalRefunded, 2) >= round($invoice->total, 2)) {
                $invoice->update(['status' => 'refunded']);
            }

            return $transaction;
        });

        $this->recordCreate('invoice.transaction.refund', $transaction->toArray());

        return redirect()->route('admin.invoices.edit', ['invoice' => $invoice->id])
            ->with('success', __('admin/invoices.refund.success'));
    }
}
