<?php

namespace App\Http\Controllers\Admin\Settings\Ticket;

use App\Http\Controllers\Controller;
use App\Traits\AuditsSystem;
use Billmora;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PipingController extends Controller
{
    use AuditsSystem;

    /**
     * Applies permission-based middleware for accessing ticket piping settings
     * 
     * @return void
     */
    public function __construct()
    {
        $this->middleware('permission:settings.ticket.view')->only('index');
        $this->middleware('permission:settings.ticket.update')->only('update');
    }

    /**
     * Display the email piping settings page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('admin::settings.ticket.piping');
    }

    /**
     * Validate and persist the email piping configuration settings.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        $pipingEnabled = $request->boolean('piping_enabled');

        $validated = $request->validate([
            'piping_enabled' => ['boolean'],
            'piping_mail_host' => [Rule::requiredIf($pipingEnabled), 'nullable', 'string'],
            'piping_mail_port' => [Rule::requiredIf($pipingEnabled), 'nullable', 'integer'],
            'piping_mail_address' => [Rule::requiredIf($pipingEnabled), 'nullable', 'email'],
            'piping_mail_password' => [Rule::requiredIf($pipingEnabled), 'nullable', 'string'],
        ]);

        Billmora::setEnv([
            'PIPING_MAIL_HOST' => $validated['piping_mail_host'],
            'PIPING_MAIL_PORT' => $validated['piping_mail_port'],
            'PIPING_MAIL_ADDRESS' => $validated['piping_mail_address'],
            'PIPING_MAIL_PASSWORD' => $validated['piping_mail_password'],
        ]);

        $validated['piping_mail_password'] = encrypt($validated['piping_mail_password']);

        $this->updateSettings('ticket', $validated);

        return redirect()->back()->with('success', __('common.save_success', ['attribute' => __('admin/settings/ticket.title')]));
    }
}
