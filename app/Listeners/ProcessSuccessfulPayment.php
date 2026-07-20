<?php

namespace App\Listeners;

use App\Events\PaymentCaptured;
use App\Models\Invoice;
use App\Models\Transaction;
use App\Traits\AuditsSystem;
use Illuminate\Support\Facades\DB;

class ProcessSuccessfulPayment
{
    use AuditsSystem;

    /**
     * Handle the event by running database updates within a transaction.
     *
     * @param \App\Events\PaymentCaptured $event
     * @return void
     */
    public function handle(PaymentCaptured $event): void
    {
        $invoice = $event->invoice;
        $response = $event->response;
        $plugin = $event->plugin;

        if ($invoice->status === 'paid') {
            return;
        }

        DB::transaction(function () use ($invoice, $response, $plugin) {
            $lockedInvoice = Invoice::where('id', $invoice->id)->lockForUpdate()->first();

            if (!$lockedInvoice || $lockedInvoice->status === 'paid') {
                return;
            }

            $lockedInvoice->update([
                'status' => 'paid',
                'paid_at' => now(),
            ]);

            $this->recordSystem('invoice.paid', $lockedInvoice->toArray(), 'gateway');

            $transaction = Transaction::create([
                'user_id' => $lockedInvoice->user_id,
                'invoice_id' => $lockedInvoice->id,
                'plugin_id' => $plugin->id,
                'reference' => $response->getGatewayReference(),
                'description' => "Payment of Invoice {$lockedInvoice->invoice_number} via {$plugin->name}",
                'currency' => $lockedInvoice->currency,
                'amount' => $response->getAmount(),
                'fee' => $response->getFee(),
            ]);

            $this->recordSystem('transaction.created', $transaction->toArray(), 'gateway');
        });
    }
}
