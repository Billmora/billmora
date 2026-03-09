<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\BroadcastJob;
use App\Models\Broadcast;
use App\Models\User;
use App\Traits\AuditsSystem;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BroadcastsController extends Controller
{
    use AuditsSystem;

    /**
     * Applies permission-based middleware for accessing broadcasts management.
     * 
     * @return void
     */
    public function __construct()
    {
        $this->middleware('permission:broadcasts.view')->only(['index']);
        $this->middleware('permission:broadcasts.create')->only(['create', 'store']);
        $this->middleware('permission:broadcasts.update')->only(['edit', 'update']);
        $this->middleware('permission:broadcasts.delete')->only(['destroy']);
    }

    /**
     * Display the list broadcasts table.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $search = $request->query('searchBroadcast');

        $broadcasts = Broadcast::select('id', 'subject', 'recipient_group', 'schedule_at', 'created_at')
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('subject', 'like', "%{$search}%");
                });
            })
            ->paginate(25)
            ->withQueryString();

        return view('admin::broadcasts.index', compact('broadcasts'));
    }

    /**
     * Show the form for creating a new broadcast.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $users = User::select('id', 'first_name', 'last_name', 'email')->get();

        return view('admin::broadcasts.create', compact('users'));
    }

    /**
     * Store a newly created broadcast in the database and queue it for sending.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse Redirects to the broadcast index with a success message after storing.
     *
     * @throws \Illuminate\Validation\ValidationException If validation fails.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'broadcast_subject' => ['required', 'string', 'max:255'],
            'broadcast_body' => ['required', 'string'],
            'broadcast_recipient_group' => [
                'required',
                Rule::in(['all_users', 'custom_users']),
            ],
            'broadcast_recipient_custom' => [
                Rule::requiredIf($request->input('broadcast_recipient_group') === 'custom_users'),
                'array',
            ],
            'broadcast_recipient_custom.*' => ['email', 'exists:users,email'],
            'broadcast_cc' => ['nullable', 'array'],
            'broadcast_cc.*' => ['email'],
            'broadcast_bcc' => ['nullable', 'array'],
            'broadcast_bcc.*' => ['email'],
            'broadcast_schedule' => ['nullable', 'date', 'after_or_equal:now'],
        ]);

        switch ($validated['broadcast_recipient_group']) {
            case 'all_users':
                $recipient_group = $validated['broadcast_recipient_group'];
                $recipient_custom = [];
                break;
            case 'custom_users':
                $recipient_group = $validated['broadcast_recipient_group'];
                $recipient_custom = $validated['broadcast_recipient_custom'] ?? [];
                break;
        }

        $broadcast = Broadcast::create([
            'subject' => $validated['broadcast_subject'],
            'body' => $validated['broadcast_body'],
            'recipient_group' => $recipient_group,
            'recipient_custom' => $recipient_custom,
            'cc' => $validated['broadcast_cc'] ?? [],
            'bcc' => $validated['broadcast_bcc'] ?? [],
            'schedule_at' => $validated['broadcast_schedule'] ?? null,
        ]);

        $this->recordCreate('mail.broadcast.create', $broadcast->toArray());

        if ($broadcast->schedule_at) {
            BroadcastJob::dispatch($broadcast)->delay($broadcast->schedule_at);
        } else {
            BroadcastJob::dispatch($broadcast);
        }

        return redirect()->route('admin.broadcasts')->with('success', __('common.create_success', ['attribute' => __('admin/navigation.broadcasts')]));
    }

    /**
     * Show the form for editing an existing broadcast.
     *
     * @param  \App\Models\Broadcast  $broadcast  The ID of the broadcast to edit.
     * @return \Illuminate\View\View The view containing the edit form.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If the broadcast is not found.
     */
    public function edit(Broadcast $broadcast)
    {
        $users = User::select('id', 'first_name', 'last_name', 'email')->get();

        return view("admin::broadcasts.edit", compact('broadcast', 'users'));
    }

    /**
     * Update an existing broadcast.
     *
     * @param  \Illuminate\Http\Request  $request  The incoming request containing broadcast update data.
     * @param  \App\Models\Broadcast  $broadcast  The ID of the broadcast to update.
     * @return \Illuminate\Http\RedirectResponse Redirects back to the broadcast index with success message.
     *
     * @throws \Illuminate\Validation\ValidationException If validation fails.
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If the broadcast is not found.
     */
    public function update(Request $request, Broadcast $broadcast)
    {
        
        $validated = $request->validate([
            'broadcast_subject' => ['required', 'string', 'max:255'],
            'broadcast_body' => ['required', 'string'],
            'broadcast_recipient_group' => [
                'required',
                Rule::in(['all_users', 'custom_users']),
            ],
            'broadcast_recipient_custom' => [
                Rule::requiredIf($request->input('broadcast_recipient_group') === 'custom_users'),
                'array',
            ],
            'broadcast_recipient_custom.*' => ['email'],
            'broadcast_cc' => ['nullable', 'array'],
            'broadcast_cc.*' => ['email'],
            'broadcast_bcc' => ['nullable', 'array'],
            'broadcast_bcc.*' => ['email'],
            'broadcast_schedule' => ['nullable', 'date', 'after_or_equal:now'],
        ]);

        switch ($validated['broadcast_recipient_group']) {
            case 'all_users':
                $recipient_group = $validated['broadcast_recipient_group'];
                $recipient_custom = [];
                break;
            case 'custom_users':
                $recipient_group = $validated['broadcast_recipient_group'];
                $recipient_custom = $validated['broadcast_recipient_custom'] ?? [];
                break;
        }

        $oldBroadcast = $broadcast->getOriginal();

        $broadcast->update([
            'subject' => $validated['broadcast_subject'],
            'body' => $validated['broadcast_body'],
            'recipient_group' => $recipient_group,
            'recipient_custom' => $recipient_custom,
            'cc' => $validated['broadcast_cc'] ?? [],
            'bcc' => $validated['broadcast_bcc'] ?? [],
            'schedule_at' => $validated['broadcast_schedule'] ?? null,
        ]);

        $this->recordUpdate('mail.broadcast.update', $oldBroadcast, $broadcast->getChanges());

        if ($broadcast->schedule_at) {
            BroadcastJob::dispatch($broadcast)->delay($broadcast->schedule_at);
        } else {
            BroadcastJob::dispatch($broadcast);
        }

        return redirect()->route('admin.broadcasts')->with('success', __('common.save_success', ['attribute' => __('admin/navigation.broadcasts')]));
    }

    /**
     * Remove the specified broadcast from storage.
     *
     * @param  \App\Models\Broadcast  $broadcast  The ID of the broadcast to delete.
     * @return \Illuminate\Http\RedirectResponse Redirects back to the broadcast index with success message.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If the broadcast is not found.
     */
    public function destroy(Broadcast $broadcast)
    {

        $this->recordDelete('mail.broadcast.delete', [
            'name' => $broadcast->subject,
        ]);

        $broadcast->delete();

        return redirect()->route('admin.broadcasts')->with('success', __('common.delete_success', ['attribute' => __('admin/navigation.broadcasts')]));
    }
}
