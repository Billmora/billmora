<?php

namespace App\Listeners\Notification\Invoice;

use App\Events\Invoice\Generated;
use App\Jobs\NotificationJob;
use App\Services\CurrencyService;
use Billmora;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendInvoiceGenerated implements ShouldQueue
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
    public function handle(Generated $event): void
    {
        $invoice = $event->invoice;
        $client = $invoice->user;

        if (!$client) {
            return;
        }

        $invoice->loadMissing('items');

        $itemsData = [];
        foreach ($invoice->items as $item) {
            $itemsData[] = [
                $item->description,
                $this->currencyService->format($item->amount, $invoice->currency)
            ];
        }

        $totalsData = [
            [
                'label' => 'Subtotal', 
                'value' => $this->currencyService->format($invoice->subtotal, $invoice->currency)
            ],
            [
                'label' => 'Total Due', 
                'value' => $this->currencyService->format($invoice->total, $invoice->currency),
                'is_highlighted' => true
            ]
        ];

        $invoiceItemsHtml = view('email::components.items', [
            'headers' => ['Description', 'Amount'],
            'items' => $itemsData,
            'totals' => $totalsData
        ])->render();

        $placeholder = [
            'client_name' => $client->fullname,
            'company_name' => Billmora::getGeneral('company_name'),
            'invoice_number' => $invoice->invoice_number,
            'due_date' => $invoice->due_date->format(Billmora::getGeneral('company_date_format')),
            'invoice_items_table' => $invoiceItemsHtml,
            'invoice_url' => route('client.invoices.show', ['invoice' => $invoice->invoice_number]),
        ];

        NotificationJob::dispatch(
            $client->email,
            'invoice_generated',
            $placeholder,
            $client->language,
            $client->id
        );
    }
}
