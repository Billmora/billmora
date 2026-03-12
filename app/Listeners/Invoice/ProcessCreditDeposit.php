<?php

namespace App\Listeners\Invoice;

use App\Events\Invoice\Paid;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ProcessCreditDeposit
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(Paid $event): void
    {
        $invoice = $event->invoice;
        $isCreditDeposit = $invoice->items()->where('description', 'like', 'Credit Deposit%')->exists();

        if ($isCreditDeposit) {
            $wallet = $invoice->user->getCreditWallet($invoice->currency);
            
            $wallet->addCredit((float) $invoice->total);
        }
    }
}
