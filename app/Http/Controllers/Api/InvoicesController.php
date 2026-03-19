<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\InvoiceResource;
use App\Models\Invoice;
use Illuminate\Http\Request;

class InvoicesController extends Controller
{
    /**
     * Display a paginated listing of invoices.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(Request $request)
    {
        $invoices = Invoice::with('user')
            ->when($request->status, fn($q, $status) => $q->where('status', $status))
            ->when($request->user_id, fn($q, $userId) => $q->where('user_id', $userId))
            ->when($request->search, fn($q, $search) => $q->where('invoice_number', 'like', "%{$search}%"))
            ->latest()
            ->paginate($request->input('per_page', 15));

        return InvoiceResource::collection($invoices);
    }

    /**
     * Display the specified invoice.
     *
     * @param  \App\Models\Invoice  $invoice
     * @return \App\Http\Resources\InvoiceResource
     */
    public function show(Invoice $invoice)
    {
        $invoice->load(['user', 'items']);

        return new InvoiceResource($invoice);
    }

    /**
     * Store a newly created invoice.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \App\Http\Resources\InvoiceResource
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'due_date' => ['required', 'date'],
            'status' => ['nullable', 'string', 'in:unpaid,paid,cancelled,refunded'],
            'currency' => ['nullable', 'string', 'max:10'],
            'notes' => ['nullable', 'string'],
        ]);

        $invoice = Invoice::create($validated);

        return (new InvoiceResource($invoice->load(['user', 'items'])))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Update the specified invoice.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Invoice  $invoice
     * @return \App\Http\Resources\InvoiceResource
     */
    public function update(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'status' => ['sometimes', 'string', 'in:unpaid,paid,cancelled,refunded'],
            'due_date' => ['sometimes', 'date'],
            'notes' => ['sometimes', 'nullable', 'string'],
        ]);

        $invoice->update($validated);

        return new InvoiceResource($invoice->fresh()->load(['user', 'items']));
    }

    /**
     * Remove the specified invoice.
     *
     * @param  \App\Models\Invoice  $invoice
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Invoice $invoice)
    {
        $invoice->delete();

        return response()->json(['message' => 'Invoice deleted successfully.'], 200);
    }
}
