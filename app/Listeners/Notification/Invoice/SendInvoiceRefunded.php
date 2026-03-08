<?php

namespace App\Listeners\Notification\Invoice;

use App\Events\Invoice\Refunded;
use App\Jobs\NotificationJob;
use App\Services\CurrencyService;
use Billmora;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendInvoiceRefunded implements ShouldQueue
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
    public function handle(Refunded $event): void
    {
        $invoice = $event->invoice;
        $client = $invoice->user;

        if (!$client) {
            return;
        }

        $refundedAmount = abs($invoice->transactions()->where('amount', '<', 0)->sum('amount'));

        $placeholder = [
            'client_name' => $client->fullname,
            'company_name' => Billmora::getGeneral('company_name'),
            'invoice_number' => $invoice->invoice_number,
            'invoice_total' => $this->currencyService->format($invoice->total, $invoice->currency),
            'refunded_amount' => $this->currencyService->format($refundedAmount, $invoice->currency),
            'invoice_url' => route('client.invoices.show', ['invoice' => $invoice->invoice_number]),
        ];

        NotificationJob::dispatch(
            $client->email,
            'invoice_refunded',
            $placeholder,
            $client->language,
            $client->id
        );
    }
}
