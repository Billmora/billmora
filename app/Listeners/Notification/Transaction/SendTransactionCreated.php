<?php

namespace App\Listeners\Notification\Transaction;

use App\Events\Transaction\Created;
use App\Jobs\NotificationJob;
use App\Services\CurrencyService;
use Billmora;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendTransactionCreated implements ShouldQueue
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
    public function handle(Created $event): void
    {
        $transaction = $event->transaction;
        $client = $transaction->user;
        $invoice = $transaction->invoice;

        if (!$client || !$invoice) {
            return;
        }
        $isRefund = $transaction->amount < 0;
        $transactionType = $isRefund ? 'Refund' : 'Payment';

        $datetimeFormat = Billmora::getGeneral('company_date_format') . ' g:i A';
        $formattedAmount = $this->currencyService->format(abs($transaction->amount), $transaction->currency);

        $placeholder = [
            'client_name' => $client->fullname,
            'company_name' => Billmora::getGeneral('company_name'),
            'transaction_type' => $transactionType,
            'transaction_amount' => $formattedAmount,
            'transaction_date' => $transaction->created_at->format($datetimeFormat),
            'transaction_description' => $transaction->description,
            'transaction_reference' => $transaction->reference ?? 'N/A',
            'invoice_number' => $invoice->invoice_number,
            'invoice_url' => route('client.invoices.show', ['invoice' => $invoice->invoice_number]),
        ];

        NotificationJob::dispatch(
            $client->email,
            'transaction_recorded',
            $placeholder,
            $client->language,
            $client->id
        );
    }
}
