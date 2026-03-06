<?php

namespace App\Listeners\Notification\Ticket;

use App\Events\Ticket\Created;
use App\Jobs\NotificationJob;
use Billmora;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendTicketCreated
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
    public function handle(Created $event): void
    {
        $ticket = $event->ticket;
        $client = $ticket->user;

        if (!$client) {
            return;
        }

        $firstMessage = $ticket->messages()->oldest()->first();
        $isStaffCreated = $firstMessage ? $firstMessage->is_staff_reply : false;

        if ($isStaffCreated) {
            if (!Billmora::getTicket('notify_client_on_staff_open')) {
                return;
            }
        } 
        else {
            if (!Billmora::getTicket('notify_client_on_open')) {
                return;
            }
        }

        $placeholder = [
            'client_name' => $client->fullname,
            'company_name' => Billmora::getGeneral('company_name'),
            'ticket_number' => $ticket->ticket_number,
            'ticket_subject' => $ticket->subject,
            'ticket_department' => $ticket->department,
            'ticket_url' => route('client.tickets.reply', ['ticket' => $ticket->ticket_number]),
        ];

        NotificationJob::dispatch(
            $client->email,
            'ticket_created',
            $placeholder,
            $client->language,
            $client->id
        );
    }
}
