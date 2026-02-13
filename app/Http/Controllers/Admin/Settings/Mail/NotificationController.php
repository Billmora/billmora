<?php

namespace App\Http\Controllers\Admin\Settings\Mail;

use App\Models\Notification;
use App\Http\Controllers\Controller;
use App\Traits\AuditsSystem;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    use AuditsSystem;

    /**
     * Applies permission-based middleware for accessing mail notification settings.
     * 
     * @return void
     */
    public function __construct()
    {
        $this->middleware('permission:settings.mail.notification.view')->only('index');
        $this->middleware('permission:settings.mail.notification.update')->only(['edit', 'update']);
    }

    /**
     * Display the mail notifications table.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $search = $request->query('searchNotificationMail');

        $notifications = Notification::select('id', 'key', 'name', 'is_active')
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('key', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
                });
            })
            ->paginate(25)
            ->withQueryString();

        return view('admin::settings.mail.notification.index', compact('notifications', 'search'));
    }

    /**
     * Show the form for editing a specific mail notification translation.
     *
     * @param \Illuminate\Http\Request $request The incoming HTTP request, optionally containing a `lang` query parameter.
     * @param int $id The ID of the mail notification to edit.
     *
     * @return \Illuminate\View\View The view displaying the mail notification edit form with the chosen translation.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If the mail notification is not found.
     */
    public function edit(Request $request, $id)
    {
        $lang = $request->query('lang', config('app.fallback_locale'));

        $notification = Notification::with('translations')->findOrFail($id);

        $translation = $notification->translations->where('lang', $lang)->first();

        $noTranslation = false;
        if (!$translation && $lang !== config('app.fallback_locale')) {
            $translation = $notification->translations->where('lang', config('app.fallback_locale'))->first();
            $noTranslation = true;
        }

        return view('admin::settings.mail.notification.edit', compact('notification', 'translation', 'noTranslation'));
    }

    /**
     * Update the specified mail notification and its translation.
     *
     * @param \Illuminate\Http\Request $request The incoming HTTP request containing notification update data.
     * @param int $id The ID of the mail notification to update.
     *
     * @return \Illuminate\Http\RedirectResponse Redirects back with a success flash message.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If the mail notification is not found.
     */
    public function update(Request $request, $id)
    {
        $notification = Notification::with('translations')->findOrFail($id);

        $validated = $request->validate([
            'language' => ['required', 'string'],
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'is_active' => ['required', 'boolean'],
            'cc' => ['nullable', 'array'],
            'bcc' => ['nullable', 'array'],
        ]);

        $oldNotification = $notification->translations->firstWhere('lang', $validated['language'])?->toArray();

        $notification->update([
            'is_active' => $validated['is_active'],
            'cc' => $validated['cc'] ?? [],
            'bcc' => $validated['bcc'] ?? [],
        ]);

        $translation = $notification->translations()->updateOrCreate(
            ['lang' => $validated['language']],
            [
                'subject' => $validated['subject'],
                'body'    => $validated['body'],
            ]
        );

        $newNotification = $translation->toArray();

        $this->recordUpdate('mail.notification.update', $oldNotification, $newNotification);

        return redirect()->route('admin.settings.mail.notification')->with('success', __('common.save_success', ['attribute' => __('admin/settings/mail.title')]));
    }
}
