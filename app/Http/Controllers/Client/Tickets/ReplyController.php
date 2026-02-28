<?php

namespace App\Http\Controllers\Client\Tickets;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Services\CaptchaService;
use App\Traits\AuditsSystem;
use Billmora;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReplyController extends Controller
{
    use AuditsSystem;

    /**
     * Display the reply thread for the specified ticket owned by the authenticated client.
     *
     * @param  \App\Models\Ticket  $ticket
     * @return \Illuminate\View\View
     */
    public function index(Ticket $ticket)
    {
        if ($ticket->user->id !== Auth::id()) {
            abort(404);
        }

        $ticket->load([
            'user',
            'assignedTo',
            'service',
            'messages.user',
            'messages.attachments',
        ]);
        
        return view('client::tickets.reply.index', compact('ticket'));
    }

    /**
     * Validate and send a client reply with optional attachments to the specified ticket.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Ticket  $ticket
     * @return \Illuminate\Http\RedirectResponse
     */
    public function send(Request $request, Ticket $ticket)
    {
        if ($ticket->user->id !== Auth::id()) {
            abort(404);
        }

        $validated = $request->validate([
            'ticket_message' => ['required', 'string'],
            'ticket_attachments' => ['nullable', 'array'],
            'ticket_attachments.*' => [
                'file',
                'max:' . (Billmora::getTicket('ticketing_max_attachment_size') * 1024),
                'mimes:' . Billmora::getTicket('ticketing_allowed_attachment_types'),
            ],
        ]);

        CaptchaService::verifyOrFail('ticket_form', $request);

        $message = DB::transaction(function () use ($validated, $ticket) {
            $message = $ticket->messages()->create([
                'user_id' => Auth::id(),
                'message' => $validated['ticket_message'],
                'is_staff_reply' => false,
            ]);

            if (!empty($validated['ticket_attachments'])) {
                foreach ($validated['ticket_attachments'] as $file) {
                    $message->attachments()->create([
                        'file_name' => $file->getClientOriginalName(),
                        'file_path' => $file->store('tickets/attachments', 'public'),
                        'mime_type' => $file->getMimeType(),
                        'file_size' => $file->getSize(),
                    ]);
                }
            }

            $ticket->update([
                'status' => 'replied',
                'closed_at' => null,
                'last_reply_at' => now(),
            ]);

            return $message;
        });

        if (Billmora::getTicket('notify_staff_on_client_reply')) {
            # TODO: add notify to staff when client replied
        }

        $this->recordCreate('ticket.message.create', $message->toArray());

        return redirect()
            ->route('client.tickets.reply', $ticket->ticket_number)
            ->with('success', __('common.send_success', ['attribute' => __('common.reply')]));
    }
}
