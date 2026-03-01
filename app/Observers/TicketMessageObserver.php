<?php

namespace App\Observers;

use App\Events\Ticket as TicketEvents;
use App\Models\TicketMessage;

class TicketMessageObserver
{
    /**
     * Handle the TicketMessage "created" event.
     */
    public function created(TicketMessage $ticketMessage): void
    {
        event(new TicketEvents\Replied($ticketMessage));
    }

    /**
     * Handle the TicketMessage "updated" event.
     */
    public function updated(TicketMessage $ticketMessage): void
    {
        //
    }

    /**
     * Handle the TicketMessage "deleted" event.
     */
    public function deleted(TicketMessage $ticketMessage): void
    {
        //
    }

    /**
     * Handle the TicketMessage "restored" event.
     */
    public function restored(TicketMessage $ticketMessage): void
    {
        //
    }

    /**
     * Handle the TicketMessage "force deleted" event.
     */
    public function forceDeleted(TicketMessage $ticketMessage): void
    {
        //
    }
}
