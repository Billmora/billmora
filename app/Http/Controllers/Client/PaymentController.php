<?php

namespace App\Http\Controllers\Client;

use App\Contracts\GatewayInterface;
use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Plugin;
use App\Models\Transaction;
use App\Services\PluginManager;
use App\Traits\AuditsSystem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Str;

class PaymentController extends Controller
{
    use AuditsSystem;

    /**
     * Process payment for the specified invoice using the selected gateway plugin.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Invoice $invoice
     * @param \App\Services\PluginManager $pluginManager
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function process(Request $request, Invoice $invoice, PluginManager $pluginManager)
    {
        if ($invoice->status !== 'unpaid') {
            return redirect()->route('client.invoices.show', ['invoice' => $invoice->invoice_number])
                ->with('error', __('client/invoices.payment.already_processed'));
        }

        $pluginId = $request->input('payment_method', $invoice->plugin_id);

        if (!$pluginId) {
            return redirect()->route('client.invoices.show', ['invoice' => $invoice->invoice_number])
                ->with('error', __('client/invoices.payment.method_required'));
        }

        if ($request->has('payment_method')) {
            $request->validate([
                'payment_method' => [
                    'required', 
                    Rule::exists('plugins', 'id')->where(fn ($query) => $query->where('type', 'gateway')->where('is_active', true))
                ],
            ]);
            
            $invoice->update(['plugin_id' => $pluginId]);
        }

        $plugin = Plugin::find($pluginId);
        $gateway = $pluginManager->bootInstance($plugin);

        if (!$gateway instanceof GatewayInterface) {
            return redirect()->route('client.invoices.show', ['invoice' => $invoice->invoice_number])
                ->with('error', __('client/invoices.payment.invalid_method'));
        }

        $invoice->load(['user.billing', 'items']);
    
        $options = [
            'description' => "Payment for Invoice #{$invoice->invoice_number}",
            'user' => $invoice->user->toArray(),
            'items' => $invoice->items->toArray(),
            'return_url' => route('client.invoices.show', ['invoice' => $invoice->invoice_number]),
        ];

        $response = $gateway->pay($invoice->invoice_number, (float) $invoice->amount_due, $invoice->currency, $options);

        if (isset($response['success']) && $response['success'] === true) {
            if ($response['type'] === 'redirect') {
                return redirect()->away($response['data']); 
            }
        }

        return redirect()->route('client.invoices.show', ['invoice' => $invoice->invoice_number])->with('error', $response['message']);
    }

    public function settle(Request $request, Invoice $invoice)
    {
        if ($invoice->status !== 'unpaid') {
            return redirect()->route('client.invoices.show', ['invoice' => $invoice->invoice_number])
                ->with('error', __('client/invoices.payment.already_processed'));
        }

        $isCreditDeposit = $invoice->items()->where('description', 'like', 'Credit Deposit%')->exists();
        if ($isCreditDeposit) {
            return redirect()->route('client.invoices.show', ['invoice' => $invoice->invoice_number])
                ->with('error', __('client/invoices.credit.cannot_pay_deposit'));
        }

        $wallet = $invoice->user->getCreditWallet($invoice->currency);
        if ($wallet->balance <= 0) {
            return redirect()->route('client.invoices.show', ['invoice' => $invoice->invoice_number])
                ->with('error', __('client/invoices.credit.insufficient_balance'));
        }

        DB::transaction(function () use ($invoice, $wallet, $request) {
            
            $amountToApply = min($wallet->balance, $invoice->amount_due);

            $wallet->removeCredit($amountToApply);

            $transaction = Transaction::create([
                'user_id' => $invoice->user_id,
                'invoice_id' => $invoice->id,
                'plugin_id' => null, 
                'reference' => 'CREDIT-' . strtoupper(Str::random(10)),
                'description' => __('client/invoices.credit.transaction_description'),
                'currency' => $invoice->currency,
                'amount' => $amountToApply,
                'fee' => 0,
            ]);

            $this->recordSystem('transaction.created', $transaction->toArray(), 'credit');

            $remainingDue = $invoice->amount_due - $amountToApply;

            if ($remainingDue <= 0) {
                $invoice->status = 'paid';
                $invoice->paid_at = now();
                $invoice->save();

                $this->recordSystem('invoice.paid', $invoice->toArray(), 'credit');
            }
        });

        $invoice->refresh();

        if ($invoice->status === 'paid') {
            return redirect()->route('client.invoices.show', ['invoice' => $invoice->invoice_number])
                ->with('success', __('client/invoices.credit.fully_settled'));
        }

        return redirect()->route('client.invoices.show', ['invoice' => $invoice->invoice_number])
            ->with('success', __('client/invoices.credit.partially_applied'));
    }
}
