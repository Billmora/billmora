<?php

namespace App\Http\Controllers\Admin\Settings\Ticket;

use App\Http\Controllers\Controller;
use App\Traits\AuditsSystem;
use Billmora;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class NotifyController extends Controller
{
    use AuditsSystem;

    /**
     * Applies permission-based middleware for accessing ticket notify settings
     * 
     * @return void
     */
    public function __construct()
    {
        $this->middleware('permission:settings.ticket.view')->only('index');
        $this->middleware('permission:settings.ticket.update')->only('update');
    }

    /**
     * Display the ticket notify settings page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('admin::settings.ticket.notify');
    }

    /**
     * Validate and persist the ticket notify configuration settings.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'notify_client_on_open' => ['boolean'],
            'notify_client_on_staff_open' => ['boolean'],
            'notify_client_on_staff_answered' => ['boolean'],
            'notify_staff_on_client_reply' => ['boolean'],
            'notify_staff_fallback' => ['required', Rule::in('none', 'departement', 'assigned')],
        ]);

        $this->updateSettings('ticket', $validated);

        Billmora::setTicket($validated);

        return redirect()->back()->with('success', __('common.save_success', ['attribute' => __('admin/settings/ticket.title')]));
    }
}
