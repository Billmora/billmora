<?php

namespace App\Listeners\Notification\Ticket;

use App\Events\Ticket\Replied;
use App\Jobs\NotificationJob;
use App\Models\User;
use Billmora;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Str;

class SendTicketReplied
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
    public function handle(Replied $event): void
    {
        $ticket = $event->ticket;
        $message = $event->message;
        $client = $ticket->user; 

        if ($message->is_staff_reply) {
            
            if (!Billmora::getTicket('notify_client_on_staff_answered')) {
                return;
            }

            $placeholder = [
                'recipient_name' => $client->fullname,
                'company_name' => Billmora::getGeneral('company_name'),
                'ticket_number' => $ticket->ticket_number,
                'ticket_subject' => $ticket->subject,
                'ticket_status' => ucwords(str_replace('_', ' ', $ticket->status)),
                'reply_content' => $message->message,
                'ticket_url' => route('client.tickets.reply', ['ticket' => $ticket->ticket_number]),
            ];

            NotificationJob::dispatch(
                $client->email,
                'ticket_replied',
                $placeholder,
                $client->language,
                $client->id
            );

            return;
        }

        if (!$message->is_staff_reply) {
            
            if (!Billmora::getTicket('notify_staff_on_client_reply')) {
                return;
            }

            $fallbackMode = Billmora::getTicket('notify_staff_fallback');
            $staffsToNotify = collect();

            if ($fallbackMode === 'assigned' && $ticket->assigned_to) {
                $staffsToNotify->push($ticket->assignedTo);
            } elseif ($fallbackMode === 'department') {
                $staffsToNotify = User::admins()->where('department', $ticket->department)->get();
            } elseif ($fallbackMode === 'none') {
                return;
            }

            foreach ($staffsToNotify as $staff) {
                if (!$staff || !$staff->email) continue;

                $placeholder = [
                    'recipient_name' => $client->fullname,
                    'company_name' => Billmora::getGeneral('company_name'),
                    'ticket_number' => $ticket->ticket_number,
                    'ticket_subject' => $ticket->subject,
                    'ticket_status' => ucwords(str_replace('_', ' ', $ticket->status)),
                    'reply_content' => $message->message,
                    'ticket_url' => route('client.tickets.reply', ['ticket' => $ticket->ticket_number]),
                ];

                NotificationJob::dispatch(
                    $staff->email,
                    'ticket_replied',
                    $placeholder,
                    $staff->language,
                    $staff->id
                );
            }
        }
    }
}
