<?php

namespace App\Observers;

use App\Events\Ticket as TicketEvents;
use App\Models\Ticket;

class TicketObserver
{
    /**
     * Handle the Ticket "created" event.
     */
    public function created(Ticket $ticket): void
    {
        event(new TicketEvents\Created($ticket));
    }

    /**
     * Handle the Ticket "updated" event.
     */
    public function updated(Ticket $ticket): void
    {
        if ($ticket->wasChanged('status')) {
            event(new TicketEvents\StatusChanged(
                $ticket, 
                $ticket->getOriginal('status'), 
                $ticket->status
            ));
        }

        if ($ticket->wasChanged('assigned_to') && $ticket->assigned_to !== null) {
            event(new TicketEvents\Assigned($ticket));
        }
    }

    /**
     * Handle the Ticket "deleted" event.
     */
    public function deleted(Ticket $ticket): void
    {
        //
    }

    /**
     * Handle the Ticket "restored" event.
     */
    public function restored(Ticket $ticket): void
    {
        //
    }

    /**
     * Handle the Ticket "force deleted" event.
     */
    public function forceDeleted(Ticket $ticket): void
    {
        //
    }
}
