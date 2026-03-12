<?php

namespace App\Listeners\Invoice;

use App\Events\Invoice\Paid;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ProcessAddFunds
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
        $isAddFunds = $invoice->items()->where('description', 'like', '%(credits)%')->exists();

        if ($isAddFunds) {
            $wallet = $invoice->user->getCreditWallet($invoice->currency);
            
            $wallet->addCredit((float) $invoice->total);
        }
    }
}
