<?php

namespace App\Listeners\Notification\Invoice;

use App\Events\Invoice\Paid;
use App\Jobs\NotificationJob;
use App\Services\CurrencyService;
use Billmora;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendInvoicePaid implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;

    /**
     * Create the event listener.
     */
    public function __construct(private CurrencyService $currencyService)
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(Paid $event): void
    {
        $invoice = $event->invoice;
        $client = $invoice->user;

        if (!$client) {
            return;
        }

        $datetimeFormat = Billmora::getGeneral('company_date_format') . ' g:i A';
        $lastTransaction = $invoice->transactions()->with('plugin')->latest()->first();
        $paymentMethod = $lastTransaction?->plugin?->name ?? 'Manual Payment';

        $itemsData = [];
        foreach ($invoice->items as $item) {
            $itemsData[] = [
                $item->description,
                $item->quantity,
                $this->currencyService->format($item->amount, $invoice->currency), 
            ];
        }

        $totalsData = [
            [
                'label' => 'Subtotal', 
                'value' => $this->currencyService->format($invoice->subtotal, $invoice->currency)
            ]
        ];

        if ($invoice->discount > 0) {
            $totalsData[] = [
                'label' => 'Discount', 
                'value' => $this->currencyService->format($invoice->discount, $invoice->currency),
                'is_discount' => true 
            ];
        }

        $totalsData[] = [
            'label' => 'Total Paid', 
            'value' => $this->currencyService->format($invoice->total, $invoice->currency),
            'is_highlighted' => true 
        ];

        $invoiceItemsHtml = view('email::components.items', [
            'headers' => ['Description', 'Qty', 'Amount'], 
            'items' => $itemsData,
            'totals' => $totalsData
        ])->render();

        $placeholder = [
            'client_name' => $client->fullname,
            'company_name' => Billmora::getGeneral('company_name'),
            'invoice_number' => $invoice->invoice_number,
            'paid_at' => $invoice->paid_at ? $invoice->paid_at->format($datetimeFormat) : now()->format($datetimeFormat),
            'payment_method' => $paymentMethod,
            'invoice_url' => route('client.invoices.show', $invoice->invoice_number),
            'invoice_items_table' => $invoiceItemsHtml, 
        ];

        NotificationJob::dispatch(
            $client->email,
            'invoice_paid',
            $placeholder,
            $client->language,
            $client->id
        );
    }
}
