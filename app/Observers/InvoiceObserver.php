<?php

namespace App\Observers;

use App\Events\Invoice as InvoiceEvents;
use App\Models\Invoice;
use Billmora;
use Illuminate\Support\Facades\DB;

class InvoiceObserver
{
    /**
     * Handle the Invoice "created" event.
     */
    public function created(Invoice $invoice): void
    {
        if ($invoice->amount_due <= 0 && $invoice->status !== 'paid') {
            DB::afterCommit(function () use ($invoice) {
                $invoice->update([
                    'status' => 'paid',
                    'paid_at' => now()
                ]);
            });
        }
        
        event(new InvoiceEvents\Created($invoice, $invoice->sendEmailNotification));
    }

    /**
     * Handle the Invoice "updated" event.
     */
    public function updated(Invoice $invoice): void
    {
        if ($invoice->wasChanged('status')) {
            switch ($invoice->status) {
                case 'paid':
                    if ($invoice->order?->package?->stock > 0) {
                        $invoice->order->package->decrement('stock');
                    }
                    
                    event(new InvoiceEvents\Paid($invoice));
                    break;

                case 'refunded':
                    event(new InvoiceEvents\Refunded($invoice));
                    break;

                case 'overdue':
                    event(new InvoiceEvents\Overdue($invoice));
                    break;
            }
        }
    }

    /**
     * Handle the Invoice "deleted" event.
     */
    public function deleted(Invoice $invoice): void
    {
        //
    }

    /**
     * Handle the Invoice "restored" event.
     */
    public function restored(Invoice $invoice): void
    {
        //
    }

    /**
     * Handle the Invoice "force deleted" event.
     */
    public function forceDeleted(Invoice $invoice): void
    {
        //
    }
}
