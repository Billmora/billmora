<?php

namespace App\Http\Controllers\Admin\Settings\Mail;

use App\Mail\TemplateMail;
use Billmora;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class MailerController extends Controller
{

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
     * Store mail mailer settings.
     *
     * @param \Illuminate\Http\Request $request The incoming HTTP request containing mailer settings.
     *
     * @return \Illuminate\Http\RedirectResponse Redirects back with a success flash message.
     *
     * @throws \Illuminate\Validation\ValidationException If validation fails.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'mailer_driver' => ['required', 'string', 'in:smtp,mailgun'],
            'mailer_from_address' => ['required', 'email'],
            'mailer_from_name' => ['required', 'string'],
            'mailer_smtp_host' => ['nullable', 'required_if:mailer_driver,smtp', 'string'],
            'mailer_smtp_port' => ['nullable', 'required_if:mailer_driver,smtp', 'integer', 'between:1,65535'],
            'mailer_smtp_username' => ['nullable', 'required_if:mailer_driver,smtp', 'string'],
            'mailer_smtp_password' => ['nullable', 'required_if:mailer_driver,smtp', 'string'],
            'mailer_smtp_encryption' => ['nullable', 'string', 'in:tls,ssl'],
            'mailer_mailgun_domain' => ['nullable', 'required_if:mailer_driver,mailgun', 'string'],
            'mailer_mailgun_secret' => ['nullable', 'required_if:mailer_driver,mailgun', 'string'],
            'mailer_mailgun_endpoint' => ['nullable', 'required_if:mailer_driver,mailgun', 'string'],
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

        return redirect()->back()->with('success', __('admin/common.save_success', ['item' => __('admin/settings/mail.title')]));
    }


    /**
     * Send a test email using the configured mailer and template system.
     *
     * @return \Illuminate\Http\RedirectResponse Redirects back to the previous page with either success or error status.
     */
    public function test()
    {
        try {
            $user = auth()->user();
            Mail::to($user->email)->send(new TemplateMail('test_message', [
                'client_name' => 'Billmora', // TODO: will be replaced with name of user.
                'company_name' => Billmora::getGeneral('company_name'),
            ]));

            return redirect()->back()->with('success', __('admin/common.send_success', ['item' => __('admin/settings/mail.mailer_test_label')]));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
