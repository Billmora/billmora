<?php

namespace App\Listeners\Notification\Ticket;

use App\Events\Ticket\Closed;
use App\Jobs\NotificationJob;
use Billmora;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendTicketClosed implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;

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
    public function handle(Closed $event): void
    {
        $ticket = $event->ticket;
        $client = $ticket->user;

        if (!$client) {
            return;
        }

        $placeholder = [
            'client_name' => $client->fullname,
            'company_name' => Billmora::getGeneral('company_name'),
            'ticket_subject' => $ticket->subject,
            'ticket_number' => $ticket->ticket_number,
            'ticket_url' => route('client.tickets.reply', ['ticket' => $ticket->ticket_number]),
        ];

        NotificationJob::dispatch(
            $client->email,
            'ticket_closed',
            $placeholder,
            $client->language,
            $client->id
        );
    }
}
