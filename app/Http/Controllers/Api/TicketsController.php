<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TicketResource;
use App\Models\Ticket;
use App\Models\TicketMessage;
use Illuminate\Http\Request;

class TicketsController extends Controller
{
    /**
     * Display a paginated listing of tickets.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(Request $request)
    {
        $tickets = Ticket::with('user')
            ->when($request->status, fn($q, $status) => $q->where('status', $status))
            ->when($request->user_id, fn($q, $userId) => $q->where('user_id', $userId))
            ->when($request->priority, fn($q, $priority) => $q->where('priority', $priority))
            ->when($request->search, fn($q, $search) => $q->where('ticket_number', 'like', "%{$search}%")
                ->orWhere('subject', 'like', "%{$search}%"))
            ->latest()
            ->paginate($request->input('per_page', 15));

        return TicketResource::collection($tickets);
    }

    /**
     * Display the specified ticket.
     *
     * @param  \App\Models\Ticket  $ticket
     * @return \App\Http\Resources\TicketResource
     */
    public function show(Ticket $ticket)
    {
        $ticket->load(['user', 'messages.user']);

        return new TicketResource($ticket);
    }

    /**
     * Store a newly created ticket.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \App\Http\Resources\TicketResource
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'subject' => ['required', 'string', 'max:255'],
            'department' => ['required', 'string', 'max:255'],
            'priority' => ['required', 'string', 'in:low,medium,high'],
            'message' => ['required', 'string'],
        ]);

        $ticket = Ticket::create([
            'user_id' => $validated['user_id'],
            'subject' => $validated['subject'],
            'department' => $validated['department'],
            'priority' => $validated['priority'],
            'status' => 'open',
        ]);

        TicketMessage::create([
            'ticket_id' => $ticket->id,
            'user_id' => $validated['user_id'],
            'message' => $validated['message'],
            'is_staff_reply' => false,
        ]);

        return (new TicketResource($ticket->load(['user', 'messages.user'])))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Update the specified ticket.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Ticket  $ticket
     * @return \App\Http\Resources\TicketResource
     */
    public function update(Request $request, Ticket $ticket)
    {
        $validated = $request->validate([
            'status' => ['sometimes', 'string', 'in:open,answered,customer-reply,closed'],
            'priority' => ['sometimes', 'string', 'in:low,medium,high'],
            'department' => ['sometimes', 'string', 'max:255'],
            'assigned_to' => ['sometimes', 'nullable', 'exists:users,id'],
        ]);

        $ticket->update($validated);

        return new TicketResource($ticket->fresh()->load(['user', 'messages.user']));
    }

    /**
     * Remove the specified ticket.
     *
     * @param  \App\Models\Ticket  $ticket
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Ticket $ticket)
    {
        $ticket->delete();

        return response()->json(['message' => 'Ticket deleted successfully.'], 200);
    }
}
