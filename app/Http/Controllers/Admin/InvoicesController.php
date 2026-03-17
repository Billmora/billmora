<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\Invoice;
use App\Models\User;
use App\Traits\AuditsSystem;
use Barryvdh\DomPDF\Facade\Pdf;
use Billmora;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class InvoicesController extends Controller
{
    use AuditsSystem;

    /**
     * Applies permission-based middleware for accessing invoices management.
     * 
     * @return void
     */
    public function __construct()
    {
        $this->middleware('permission:invoices.view')->only(['index']);
        $this->middleware('permission:invoices.create')->only(['create', 'store']);
        $this->middleware('permission:invoices.update')->only(['edit', 'update', 'download']);
        $this->middleware('permission:invoices.delete')->only(['destroy']);
    }

    /**
     * Display a paginated list of all invoices.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = Invoice::with(['order.items', 'user']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                ->orWhere('status', 'like', "%{$search}%")
                ->orWhereHas('user', function ($userQuery) use ($search) {
                    $userQuery->where('email', 'like', "%{$search}%")
                              ->orWhere('first_name', 'like', "%{$search}%")
                              ->orWhere('last_name', 'like', "%{$search}%");
                });
            });
        }

        $invoices = $query->latest('id')->paginate(Billmora::getGeneral('misc_admin_pagination'));

        return view('admin::invoices.index', compact('invoices'));
    }

    /**
     * Show the form for creating a new invoice.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('admin::invoices.create');
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
            'invoice_user' => ['required', Rule::exists('users', 'id')],
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
                'user_id' => $validated['invoice_user'],
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
                $invoice->sendEmailNotification = $request->boolean('invoice_email');
            }

            DB::commit();

            $this->recordCreate('invoice.create', $invoice->toArray());

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

    /**
     * Show the form for editing the specified invoice.
     *
     * @param \App\Models\Invoice $invoice
     * @return \Illuminate\View\View
     */
    public function edit(Invoice $invoice)
    {
        return view('admin::invoices.edit', compact('invoice'));
    }

    /**
     * Update the specified invoice in database.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Invoice $invoice
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'invoice_user' => ['required', Rule::exists('users', 'id')],
            'invoice_status' => ['required', Rule::in(['unpaid', 'paid', 'cancelled', 'refunded'])],
            'invoice_date' => ['required', 'date'],
            'invoice_due_date' => ['required', 'date', 'after_or_equal:invoice_date'],
            'invoice_currency' => ['required', Rule::exists('currencies', 'code')],
            'invoice_email' => ['nullable', 'boolean'],
            'invoice_items' => ['nullable', 'array'],
            'invoice_items.*.id' => ['nullable', Rule::exists('invoice_items', 'id')],
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

            $oldData = [
                'invoice' => $invoice->getOriginal(),
                'items' => $invoice->items->toArray(),
            ];

            $invoice->update([
                'user_id' => $validated['invoice_user'],
                'status' => $validated['invoice_status'],
                'currency' => $validated['invoice_currency'],
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total' => $total,
                'due_date' => $validated['invoice_due_date'],
                'paid_at' => $validated['invoice_status'] === 'paid' ? now() : null,
                'created_at' => $validated['invoice_date'],
            ]);

            $invoiceChanges = $invoice->getChanges();

            $submittedIds = collect($validated['invoice_items'] ?? [])->pluck('id')->filter();

            $invoice->items()->whereNotIn('id', $submittedIds)->delete();

            foreach ($validated['invoice_items'] ?? [] as $item) {
                $lineAmount = $item['quantity'] * $item['unit_price'];

                if (!empty($item['id'])) {
                    $invoice->items()->where('id', $item['id'])->update([
                        'description' => $item['description'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'amount' => $lineAmount,
                    ]);
                } else {
                    $invoice->items()->create([
                        'description' => $item['description'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'amount' => $lineAmount,
                    ]);
                }
            }

            $invoice->refresh();
            $newData = [
                'invoice' => $invoiceChanges,
                'items' => $invoice->items->toArray(),
            ];

            DB::commit();

            $this->recordUpdate('invoice.update', $oldData, $newData);

            return redirect()
                ->route('admin.invoices')
                ->with('success', __('common.update_success', ['attribute' => $invoice->invoice_number]));

        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()
                ->withInput()
                ->with('error', __('common.update_failed', ['attribute' => $e->getMessage()]));
        }
    }

    /**
     * Remove the specified invoice from database.
     *
     * @param \App\Models\Invoice $invoice
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Invoice $invoice)
    {
        $tempInvoice = $invoice;

        $invoice->delete();

        $this->recordDelete('invoice.delete', [
            'id' => $tempInvoice->id,
            'invoice_number' => $tempInvoice->invoice_number,
        ]);

        return redirect()->route('admin.invoices')->with('success', __('common.delete_success', ['attribute' => $tempInvoice->invoice_number]));
    }

    /**
     * Download the invoice as a PDF file.
     *
     * @param \App\Models\Invoice $invoice
     * @return \Illuminate\Http\Response
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    public function download(Invoice $invoice)
    {
        $paperSize = Billmora::getGeneral('invoice_pdf_size');

        $pdf = Pdf::loadView('invoice::index', [
            'invoice' => $invoice->load([
                'user',
                'order',
                'items',
                'items.service',
            ]),
        ])
        ->setPaper($paperSize, 'portrait')
        ->setOption('enable-local-file-access', true)
        ->setOption('isHtml5ParserEnabled', true)
        ->setOption('isRemoteEnabled', true);

        $filename = "Invoice-{$invoice->invoice_number}.pdf";
        
        return $pdf->download($filename);
    }
}
