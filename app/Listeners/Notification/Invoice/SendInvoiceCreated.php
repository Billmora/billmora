<?php

namespace App\Listeners\Notification\Invoice;

use App\Events\Invoice as InvoiceEvents;
use App\Jobs\NotificationJob;
use App\Services\CurrencyService;
use Billmora;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendInvoiceCreatedNotification implements ShouldQueue
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
    public function handle(InvoiceEvents\Created $event): void
    {
        $invoice = $event->invoice;

        $invoice->loadMissing('user');
        $user = $invoice->user;

        if (!$user) {
            return;
        }

        $placeholder = [
            'client_name' => $user->fullname,
            'company_name' => Billmora::getGeneral('company_name'),
            'invoice_number' => $invoice->invoice_number,
            'total_amount' => $this->currencyService->format($invoice->total, $invoice->currency),
            'due_date' => $invoice->due_date->format(Billmora::getGeneral('company_date_format')),
            'invoice_url' => route('client.invoices.show', ['invoice' => $invoice->invoice_number]),
        ];

        NotificationJob::dispatch(
            $user->email,
            'invoice_created',
            $placeholder,
            $user->language,
            $user->id
        );
    }
}
