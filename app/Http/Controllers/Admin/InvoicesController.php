<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class InvoicesController extends Controller
{
    /**
     * Display a paginated list of all invoices.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $invoices = Invoice::whereHas('order')
            ->with(['order.service.package.catalog'])
            ->orderBy('created_at', 'desc')
            ->paginate(25);

        return view('admin::invoices.index', compact('invoices'));
    }

    /**
     * Show the form for creating a new invoice.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $users = User::select('id', 'first_name', 'last_name', 'email')->get();

        $currencies = Currency::select('id', 'code')->get();

        return view('admin::invoices.create', compact('users', 'currencies'));
    }

    /**
     * Store a newly created invoice in database.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'invoice_user' => ['required', Rule::exists('users', 'email')],
            'invoice_status' => ['required', Rule::in(['unpaid', 'paid', 'cancelled', 'refunded'])],
            'invoice_date' => ['required', 'date'],
            'invoice_due_date' => ['required', 'date', 'after_or_equal:invoice_date'],
            'invoice_currency' => ['required', Rule::exists('currencies', 'code')],
            'invoice_email' => ['nullable', 'boolean'],
            'invoice_items' => ['nullable', 'array'],
            'invoice_items.*.description' => ['required', 'string', 'max:1000'],
            'invoice_items.*.quantity' => ['required', 'integer', 'min:1'],
            'invoice_items.*.unit_price' => ['required', 'numeric'],
        ]);

        DB::beginTransaction();
        
        try {
            $subtotal = 0;
            $discount = 0;

            if (!empty($validated['invoice_items'])) {
                foreach ($validated['invoice_items'] as $item) {
                    $lineTotal = $item['quantity'] * $item['unit_price'];
                    
                    if ($item['unit_price'] < 0) {
                        $discount += abs($lineTotal);
                    } else {
                        $subtotal += $lineTotal;
                    }
                }
            }

            $total = $subtotal - $discount;

            $invoice = Invoice::create([
                'user_id' => User::where('email', $validated['invoice_user'])->value('id'),
                'status' => $validated['invoice_status'],
                'invoice_number' => Invoice::generateInvoiceNumber(),
                'currency' => $validated['invoice_currency'],
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total' => $total,
                'due_date' => $validated['invoice_due_date'],
                'paid_at' => $validated['invoice_status'] === 'paid' ? now() : null,
                'created_at' => $validated['invoice_date'],
                'updated_at' => $validated['invoice_date'],
            ]);

            if (!empty($validated['invoice_items'])) {
                foreach ($validated['invoice_items'] as $item) {
                    $invoice->items()->create([
                        'description' => $item['description'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'amount' => $item['quantity'] * $item['unit_price'],
                    ]);
                }
            }

            if ($request->boolean('invoice_email')) {
                // TODO: Send email notify to user
            }

            DB::commit();

            return redirect()
                ->route('admin.invoices')
                ->with('success', __('common.create_success', ['attribute' => $invoice->invoice_number]));

        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()
                ->withInput()
                ->with('error', __('common.create_failed', ['attribute' => $e->getMessage()]));
        }
    }
}
