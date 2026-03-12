<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\Ticket;
use App\Models\User;
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
     * Applies permission-based middleware for accessing tickets management.
     * 
     * @return void
     */
    public function __construct()
    {
        $this->middleware('permission:tickets.view')->only(['index']);
        $this->middleware('permission:tickets.create')->only(['create', 'store']);
        $this->middleware('permission:tickets.update')->only(['edit', 'update', 'close']);
        $this->middleware('permission:tickets.delete')->only(['destroy']);
    }

    /**
     * Display a paginated list of tickets with optional search filtering.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = Ticket::with('user', 'service');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%");
            });
        }

        $tickets = $query->orderByDesc('created_at')->paginate(Billmora::getGeneral('misc_admin_pagination'));

        $tickets->appends(['search' => $search]);

        return view('admin::tickets.index', compact('tickets'));
    }

    /**
     * Display the form for creating a new ticket.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $userOptions = User::query()
            ->select('id', 'first_name', 'last_name', 'email')
            ->get()
            ->map(fn ($user) => [
                'value' => $user->id,
                'title' => $user->fullname,
                'subtitle' => $user->email,
            ])
            ->values()
            ->toArray();

        $assigneds = User::select('id', 'first_name', 'last_name', 'email')
            ->admins()
            ->get();

        $services = Service::select('id', 'name', 'status', 'user_id')->get();

        return view('admin::tickets.create', compact('userOptions', 'assigneds', 'services'));
    }

    /**
     * Validate and store a newly created ticket along with its initial message and attachments.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'ticket_status' => ['required', Rule::in('open', 'answered', 'replied', 'closed', 'on_hold', 'in_progress')],
            'ticket_priority' => ['required', Rule::in('low', 'normal', 'medium', 'high')],
            'ticket_department' => ['nullable', Rule::in(Billmora::getTicket('ticketing_departments'))],
            'ticket_user_id' => ['required', Rule::exists('users', 'id')],
            'ticket_assigned_to' => ['nullable', Rule::exists('users', 'id')],
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

        $ticket = DB::transaction(function () use ($validated) {
            $ticket = Ticket::create([
                'status' => $validated['ticket_status'],
                'priority' => $validated['ticket_priority'],
                'department' => $validated['ticket_department'] ?? null,
                'service_id' => $validated['ticket_service_id'] ?? null,
                'user_id' => $validated['ticket_user_id'],
                'assigned_to' => $validated['ticket_assigned_id'] ?? null,
                'subject' => $validated['ticket_subject'],
                'last_reply_at' => now(),
            ]);

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

            return $ticket;
        });

        $this->recordCreate('ticket.create', $ticket->toArray());

        return redirect()->route('admin.tickets')->with('success', __('common.create_success', ['attribute' => $ticket->ticket_number]));
    }

    /**
     * Display the form for editing an existing ticket.
     *
     * @param  \App\Models\Ticket  $ticket
     * @return \Illuminate\View\View
     */
    public function edit(Ticket $ticket)
    {
        $userOptions = User::query()
            ->select('id', 'first_name', 'last_name', 'email')
            ->get()
            ->map(fn ($user) => [
                'value' => $user->id,
                'title' => $user->fullname,
                'subtitle' => $user->email,
            ])
            ->values()
            ->toArray();

        $assignedOptions = User::select('id', 'first_name', 'last_name', 'email')
            ->admins()
            ->get()
            ->map(fn ($user) => [
                'value' => $user->id,
                'title' => "{$user->first_name} {$user->last_name}",
                'subtitle' => $user->email,
            ])
            ->values()
            ->toArray();

        $services = Service::select('id', 'name', 'status', 'user_id')->get();

        return view('admin::tickets.edit', compact('ticket', 'userOptions', 'assignedOptions', 'services'));
    }

    /**
     * Validate and update the specified ticket's details.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Ticket  $ticket
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Ticket $ticket)
    {
        $validated = $request->validate([
            'ticket_status' => ['required', Rule::in('open', 'answered', 'replied', 'closed', 'on_hold', 'in_progress')],
            'ticket_priority' => ['required', Rule::in('low', 'normal', 'medium', 'high')],
            'ticket_department' => ['nullable', Rule::in(Billmora::getTicket('ticketing_departments'))],
            'ticket_user_id' => ['required', Rule::exists('users', 'id')],
            'ticket_assigned_id' => ['nullable', Rule::exists('users', 'id')],
            'ticket_service_id' => ['nullable', Rule::exists('services', 'id')],
        ]);

        $oldTicket = $ticket->getOriginal();

        $ticket->update([
            'status' => $validated['ticket_status'],
            'priority' => $validated['ticket_priority'],
            'department' => $validated['ticket_department'] ?? null,
            'service_id' => $validated['ticket_service_id'] ?? null,
            'user_id' => $validated['ticket_user_id'],
            'assigned_to' => $validated['ticket_assigned_id'] ?? null,
        ]);

        $this->recordUpdate('ticket.update', $oldTicket, $ticket->getChanges());

        return redirect()
            ->route('admin.tickets')
            ->with('success', __('common.update_success', ['attribute' => $ticket->ticket_number]));
    }

    /**
     * Delete the specified ticket from the system.
     *
     * @param  \App\Models\Ticket  $ticket
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Ticket $ticket)
    {
        $ticket->delete();

        $this->recordDelete('ticket.delete', $ticket->toArray());

        return redirect()
            ->route('admin.tickets')
            ->with('success', __('common.delete_success', ['attribute' => $ticket->ticket_number]));
    }

    /**
     * Close the specified ticket.
     *
     * @param  \App\Models\Ticket  $ticket
     * @return \Illuminate\Http\RedirectResponse
     */
    public function close(Ticket $ticket)
    {
        if ($ticket->status === 'closed') {
            abort(403);
        }

        $oldTicket = $ticket->getOriginal();

        $ticket->update([
            'status' => 'closed',
            'closed_at' => now(),
        ]);

        $this->recordUpdate('ticket.update', $oldTicket, $ticket->getChanges());

        return redirect()
            ->route('admin.tickets.reply', ['ticket' => $ticket->id])
            ->with('success', __('common.close_success', ['attribute' => $ticket->ticket_number]));
    }
}
