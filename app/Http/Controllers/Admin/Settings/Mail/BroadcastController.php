<?php

namespace App\Http\Controllers\Admin\Settings\Mail;

use App\Http\Controllers\Controller;
use App\Jobs\MailBroadcastJob;
use App\Models\MailBroadcast;
use App\Models\User;
use Illuminate\Http\Request;

class BroadcastController extends Controller
{

    /**
     * Display the list mail broadcast table.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $broadcasts = MailBroadcast::select('id', 'subject', 'recipient_group', 'schedule_at', 'created_at')->get();

        return view('admin::settings.mail.broadcast.index', compact('broadcasts'));
    }

    /**
     * Show the form for creating a new mail broadcast.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('admin::settings.mail.broadcast.create');
    }

    /**
     * Store a newly created mail broadcast in the database and queue it for sending.
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
            'broadcast_recipient_group' => ['required', 'in:all_users,custom_users'],
            'broadcast_recipient_custom' => ['required', 'array'],
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

        $broadcast = MailBroadcast::create([
            'subject' => $validated['broadcast_subject'],
            'body' => $validated['broadcast_body'],
            'recipient_group' => $recipient_group,
            'recipient_custom' => $recipient_custom,
            'cc' => $validated['broadcast_cc'] ?? [],
            'bcc' => $validated['broadcast_bcc'] ?? [],
            'schedule_at' => $validated['broadcast_schedule'] ?? null,
        ]);

        if ($broadcast->schedule_at) {
            MailBroadcastJob::dispatch($broadcast)->delay($broadcast->schedule_at);
        } else {
            MailBroadcastJob::dispatch($broadcast);
        }

        return redirect()->route('admin.settings.mail.broadcast')->with('success', __('admin/common.create_success', ['item' => __('admin/settings/mail.tabs.broadcast')]));
    }

    /**
     * Show the form for editing an existing mail broadcast.
     *
     * @param  int  $id  The ID of the mail broadcast to edit.
     * @return \Illuminate\View\View The view containing the edit form.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If the broadcast is not found.
     */
    public function edit($id)
    {
        $broadcast = MailBroadcast::findOrFail($id);
        return view("admin::settings.mail.broadcast.edit", compact('broadcast'));
    }

    /**
     * Update an existing mail broadcast.
     *
     * @param  \Illuminate\Http\Request  $request  The incoming request containing broadcast update data.
     * @param  int  $id  The ID of the mail broadcast to update.
     * @return \Illuminate\Http\RedirectResponse Redirects back to the broadcast index with success message.
     *
     * @throws \Illuminate\Validation\ValidationException If validation fails.
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If the broadcast is not found.
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'broadcast_subject' => ['required', 'string', 'max:255'],
            'broadcast_body' => ['required', 'string'],
            'broadcast_recipient_group' => ['required', 'in:all_users,custom_users'],
            'broadcast_recipient_custom' => ['required', 'array'],
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

        $broadcast = MailBroadcast::findOrFail($id);

        $broadcast->update([
            'subject' => $validated['broadcast_subject'],
            'body' => $validated['broadcast_body'],
            'recipient_group' => $recipient_group,
            'recipient_custom' => $recipient_custom,
            'cc' => $validated['broadcast_cc'] ?? [],
            'bcc' => $validated['broadcast_bcc'] ?? [],
            'schedule_at' => $validated['broadcast_schedule'] ?? null,
        ]);

        if ($broadcast->schedule_at) {
            MailBroadcastJob::dispatch($broadcast)->delay($broadcast->schedule_at);
        } else {
            MailBroadcastJob::dispatch($broadcast);
        }

        return redirect()->route('admin.settings.mail.broadcast')->with('success', __('admin/common.save_success', ['item' => __('admin/settings/mail.tabs.broadcast')]));
    }

    /**
     * Remove the specified mail broadcast from storage.
     *
     * @param  int  $id  The ID of the mail broadcast to delete.
     * @return \Illuminate\Http\RedirectResponse Redirects back to the broadcast index with success message.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If the broadcast is not found.
     */
    public function destroy($id)
    {
        $broadcast = MailBroadcast::findOrFail($id);
        $broadcast->delete();

        return redirect()->route('admin.settings.mail.broadcast')->with('success', __('admin/common.delete_success', ['item' => __('admin/settings/mail.tabs.broadcast')]));
    }
}
