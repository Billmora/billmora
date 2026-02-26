<?php

namespace App\Http\Controllers\Admin\Settings\Ticket;

use App\Http\Controllers\Controller;
use App\Traits\AuditsSystem;
use Billmora;
use Illuminate\Http\Request;

class TicketingController extends Controller
{
    use AuditsSystem;

    /**
     * Applies permission-based middleware for accessing ticket ticketing settings
     * 
     * @return void
     */
    public function __construct()
    {
        $this->middleware('permission:settings.ticket.view')->only('index');
        $this->middleware('permission:settings.ticket.update')->only('update');
    }

    /**
     * Display the ticketing settings page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('admin::settings.ticket.ticketing');
    }

    /**
     * Validate and persist the ticketing configuration settings.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'ticketing_departements' => ['required', 'array'],
            'ticketing_number_increment' => ['required', 'integer', 'min:1'],
            'ticketing_number_padding' => ['required', 'integer', 'min:1'],
            'ticketing_number_format' => [
                'required',
                'string',
                'regex:/^\S+$/',
                'regex:/\{number\}/',
                'regex:/^[^{}]*(\{(number|day|month|year)\}[^{}]*)*$/',
            ],
            'ticketing_allow_client_close' => ['boolean'],
        ]);

        $this->updateSettings('ticket', $validated);

        Billmora::setTicket($validated);

        return redirect()->back()->with('success', __('common.save_success', ['attribute' => __('admin/settings/ticket.title')]));
    }
}
