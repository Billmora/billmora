<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\Ticket;
use App\Models\User;
use App\Services\CaptchaService;
use App\Traits\AuditsSystem;
use Billmora;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class TicketsController extends Controller
{
    use AuditsSystem;

    /**
     * Display a paginated list of tickets belonging to the authenticated client.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $tickets = Ticket::where('user_id', Auth::id())
            ->with('service')
            ->orderBy('created_at', 'desc')
            ->paginate(15);;

        return view('client::tickets.index', compact('tickets'));
    }

    /**
     * Display the form for creating a new support ticket.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $services = Service::select('id', 'name', 'status')
            ->where('user_id', Auth::id())
            ->get();

        return view('client::tickets.create', compact('services'));
    }

    /**
     * Validate and store a newly created client ticket along with its initial message and attachments.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'ticket_priority' => ['required', Rule::in('low', 'normal', 'medium', 'high')],
            'ticket_department' => ['required', Rule::in(Billmora::getTicket('ticketing_departments'))],
            'ticket_service_id' => ['nullable', Rule::exists('services', 'id')],
            'ticket_subject' => ['required', 'string', 'max:255'],
            'ticket_message'=> ['required', 'string'],
            'ticket_attachments' => ['nullable', 'array'],
            'ticket_attachments.*' => [
                'file',
                'max:' . (Billmora::getTicket('ticketing_max_attachment_size') * 1024),
                'mimes:' . Billmora::getTicket('ticketing_allowed_attachment_types'),
            ],
        ]);

        CaptchaService::verifyOrFail('ticket_form', $request);

        $ticket = DB::transaction(function () use ($validated) {
            $ticket = Ticket::create([
                'status' => 'open',
                'priority' => $validated['ticket_priority'],
                'department' => $validated['ticket_department'] ?? null,
                'service_id' => $validated['ticket_service_id'] ?? null,
                'user_id' => Auth::id(),
                'subject' => $validated['ticket_subject'],
                'last_reply_at' => now(),
            ]);

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

            return $ticket;
        });

        $this->recordCreate('ticket.create', $ticket->toArray());

        if (Billmora::getTicket('notify_client_on_open')) {
            # TODO: add notify to client when opened the ticket
        }

        return redirect()->route('client.tickets')->with('success', __('common.create_success', ['attribute' => $ticket->ticket_number]));
    }

    /**
     * Close the specified ticket.
     *
     * @param  \App\Models\Ticket  $ticket
     * @return \Illuminate\Http\RedirectResponse
     */
    public function close(Ticket $ticket)
    {
        if ($ticket->user->id !== Auth::id()) {
            abort(404);
        }
        
        if (!Billmora::getTicket('ticketing_allow_client_close') || $ticket->status === 'closed') {
            abort(403);
        }

        $oldTicket = $ticket->getOriginal();

        $ticket->update([
            'status' => 'closed',
            'closed_at' => now(),
        ]);

        $this->recordUpdate('ticket.update', $oldTicket, $ticket->getChanges());

        return redirect()
            ->route('client.tickets.reply', $ticket->ticket_number)
            ->with('success', __('common.close_success', ['attribute' => $ticket->ticket_number]));
    }
}
