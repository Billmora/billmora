<?php

namespace App\Listeners\Notification\Invoice;

use App\Events\Invoice\Paid;
use App\Jobs\NotificationJob;
use App\Services\CurrencyService;
use Billmora;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendInvoicePaid
{
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

        $dateFormat = Billmora::getGeneral('company_date_format') . ' g:i A';
        $lastTransaction = $invoice->transactions()->latest()->first();

        $placeholder = [
            'client_name' => $client->fullname,
            'company_name' => Billmora::getGeneral('company_name'),
            'invoice_number' => $invoice->invoice_number,
            'total_amount' => $this->currencyService->format($invoice->total, $invoice->currency),
            'paid_at' => $invoice->paid_at ? $invoice->paid_at->format($dateFormat) : now()->format($dateFormat),
            'payment_method' => $lastTransaction->plugin->name ?? 'Manual Payment',
            'invoice_url' => route('client.invoices.show', $invoice->invoice_number),
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
