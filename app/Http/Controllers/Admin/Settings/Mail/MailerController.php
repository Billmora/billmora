<?php

namespace App\Http\Controllers\Admin\Settings\Mail;

use App\Mail\NotificationMail;
use App\Traits\AuditsSystem;
use Billmora;
use App\Http\Controllers\Controller;
use App\Jobs\NotificationJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class MailerController extends Controller
{
    use AuditsSystem;

    /**
     * Applies permission-based middleware for accessing mailer settings.
     * 
     * @return void
     */
    public function __construct()
    {
        $this->middleware('permission:settings.mail.view')->only(['index']);
        $this->middleware('permission:settings.mail.update')->only(['update', 'test']);
    }

    /**
     * Display the mailer settings view.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('admin::settings.mail.mailer');
    }

    /**
     * Update mail mailer settings.
     *
     * @param \Illuminate\Http\Request $request The incoming HTTP request containing mailer settings.
     *
     * @return \Illuminate\Http\RedirectResponse Redirects back with a success flash message.
     *
     * @throws \Illuminate\Validation\ValidationException If validation fails.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'mailer_driver' => ['required', 'string', 'in:smtp,mailgun'],
            'mailer_from_address' => ['required', 'email'],
            'mailer_from_name' => ['required', 'string'],
            'mailer_smtp_host' => [
                'nullable',
                Rule::requiredIf($request->mailer_driver === 'smtp'), 
                'string'
            ],
            'mailer_smtp_port' => [
                'nullable',
                Rule::requiredIf($request->mailer_driver === 'smtp'), 
                'integer',
                'between:1,65535'
            ],
            'mailer_smtp_username' => [
                'nullable',
                Rule::requiredIf($request->mailer_driver === 'smtp'), 
                'string'
            ],
            'mailer_smtp_password' => [
                'nullable', 
                Rule::requiredIf($request->mailer_driver === 'smtp'), 
                'string'
            ],
            'mailer_smtp_encryption' => ['nullable', 'string', 'in:tls,ssl'],
            'mailer_mailgun_domain' => [
                'nullable',
                Rule::requiredIf($request->mailer_driver === 'mailgun'), 
                'string'
            ],
            'mailer_mailgun_secret' => [
                'nullable',
                Rule::requiredIf($request->mailer_driver === 'mailgun'), 
                'string'
            ],
            'mailer_mailgun_endpoint' => [
                'nullable',
                Rule::requiredIf($request->mailer_driver === 'mailgun'), 
                'string'
            ],
        ]);


        Billmora::setEnv([
            'MAIL_MAILER' => $validated['mailer_driver'],
            'MAIL_FROM_ADDRESS' => $validated['mailer_from_address'],
            'MAIL_FROM_NAME' => $validated['mailer_from_name'],
            'MAIL_HOST' => $validated['mailer_smtp_host'],
            'MAIL_PORT' => $validated['mailer_smtp_port'],
            'MAIL_USERNAME' => $validated['mailer_smtp_username'],
            'MAIL_PASSWORD' => $validated['mailer_smtp_password'],
            'MAIL_ENCRYPTION' => $validated['mailer_smtp_encryption'],
            'MAILGUN_DOMAIN' => $validated['mailer_mailgun_domain'],
            'MAILGUN_SECRET' => $validated['mailer_mailgun_secret'],
            'MAILGUN_ENDPOINT' => $validated['mailer_mailgun_endpoint'],
        ]);

        $validated['mailer_smtp_password'] = encrypt($validated['mailer_smtp_password']);
        $validated['mailer_mailgun_secret'] = encrypt($validated['mailer_mailgun_secret']);

        $this->updateSettings('mail', $validated);

        return redirect()->back()->with('success', __('common.save_success', ['attribute' => __('admin/settings/mail.title')]));
    }


    /**
     * Send a test email using the configured mailer and notification.
     *
     * @return \Illuminate\Http\RedirectResponse Redirects back to the previous page with either success or error status.
     */
    public function test()
    {
        $user = Auth::user();

        NotificationJob::dispatch(
            $user->email,
            'test_message', 
            [
                'client_name' => $user->fullname,
                'company_name' => Billmora::getGeneral('company_name'),
            ],
            $user->language
        );

        return redirect()->back()->with('success', __('common.send_success', ['attribute' => __('admin/settings/mail.mailer_test_label')]));
    }
}
