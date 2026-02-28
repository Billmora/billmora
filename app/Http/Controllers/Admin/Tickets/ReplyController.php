<?php

namespace App\Http\Controllers\Admin\Tickets;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Traits\AuditsSystem;
use Billmora;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ReplyController extends Controller
{
    use AuditsSystem;

    /**
     * Applies permission-based middleware for accessing message ticket.
     * 
     * @return void
     */
    public function __construct()
    {
        $this->middleware('permission:tickets.reply')->only(['index', 'send', 'reply']);
    }

    /**
     * Display the reply thread for the specified ticket.
     *
     * @param  \App\Models\Ticket  $ticket
     * @return \Illuminate\View\View
     */
    public function index(Ticket $ticket)
    {
        $ticket->load([
            'user',
            'assignedTo',
            'service',
            'messages.user',
            'messages.attachments',
        ]);
        
        return view('admin::tickets.reply.index', compact('ticket'));
    }

    /**
     * Validate and send a staff reply with optional attachments to the specified ticket.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Ticket  $ticket
     * @return \Illuminate\Http\RedirectResponse
     */
    public function send(Request $request, Ticket $ticket)
    {
        $validated = $request->validate([
            'ticket_message' => ['required', 'string'],
            'ticket_attachments' => ['nullable', 'array'],
            'ticket_attachments.*' => [
                'file',
                'max:' . (Billmora::getTicket('ticketing_max_attachment_size') * 1024),
                'mimes:' . Billmora::getTicket('ticketing_allowed_attachment_types'),
            ],
        ]);

        $message = DB::transaction(function () use ($validated, $ticket) {
            $message = $ticket->messages()->create([
                'user_id' => Auth::id(),
                'message' => $validated['ticket_message'],
                'is_staff_reply' => true,
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
                'status' => 'answered',
                'last_reply_at' => now(),
            ]);

            return $message;
        });

        $this->recordCreate('ticket.message.create', $message->toArray());

        return redirect()
            ->route('admin.tickets.reply', $ticket->ticket_number)
            ->with('success', __('common.send_success', ['attribute' => __('common.reply')]));
    }

    /**
     * Delete the specified message and its attachments from the ticket thread.
     *
     * @param  \App\Models\Ticket  $ticket
     * @param  \App\Models\TicketMessage  $message
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Ticket $ticket, TicketMessage $message)
    {
        if ($message->ticket_id !== $ticket->id) {
            abort(403);
        }

        DB::transaction(function () use ($message, $ticket) {
            if ($message->attachments()->exists()) {
                $message->attachments->each(function ($attachment) {
                    Storage::disk('public')->delete($attachment->file_path);
                });
                $message->attachments()->delete();
            }

            $message->delete();

            $lastMessage = $ticket->messages()->latest()->first();
            $ticket->update([
                'last_reply_at' => $lastMessage?->created_at ?? $ticket->created_at,
            ]);
        });

        $this->recordDelete('ticket.message.delete', $message->toArray());

        return redirect()
            ->route('admin.tickets.reply', $ticket->ticket_number)
            ->with('success', __('common.delete_success', ['attribute' => __('common.reply')]));
    }
}
