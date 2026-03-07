<?php

namespace App\Http\Controllers\Admin\Settings\Automation;

use App\Http\Controllers\Controller;
use App\Traits\AuditsSystem;
use Billmora;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    use AuditsSystem;

    /**
     * Applies permission-based middleware for accessing automation ticket settings
     * 
     * @return void
     */
    public function __construct()
    {
        $this->middleware('permission:settings.automation.view')->only('index');
        $this->middleware('permission:settings.automation.update')->only('update');
    }

    /**
     * Display the automation ticket settings page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('admin::settings.automation.ticket');
    }

    /**
     * Validate and persist the automation ticket configuration settings.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'ticket_close_days' => ['required', 'integer', 'min:0'],
            'prune_ticket_attachments_days' => ['required', 'integer', 'min:0'],
        ]);

        $this->updateSettings('automation', $validated);

        Billmora::setAutomation($validated);

        return redirect()->back()->with('success', __('common.save_success', ['attribute' => __('admin/settings/automation.title')]));
    }
}
