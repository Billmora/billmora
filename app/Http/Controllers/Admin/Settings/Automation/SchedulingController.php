<?php

namespace App\Http\Controllers\Admin\Settings\Automation;

use App\Http\Controllers\Controller;
use App\Traits\AuditsSystem;
use Billmora;
use Illuminate\Http\Request;

class SchedulingController extends Controller
{
    use AuditsSystem;

    /**
     * Applies permission-based middleware for accessing automation scheduling settings
     * 
     * @return void
     */
    public function __construct()
    {
        $this->middleware('permission:settings.automation.view')->only('index');
        $this->middleware('permission:settings.automation.update')->only('update');
    }

    /**
     * Display the automation scheduling settings page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('admin::settings.automation.scheduling');
    }

    /**
     * Validate and persist the automation scheduling configuration settings.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'time_of_day' => ['required', 'date_format:H:i'],
            'prune_email_history_days' => ['required', 'integer', 'min:0'],
            'prune_system_logs_days' => ['required', 'integer', 'min:0'],
        ]);

        $this->updateSettings('automation', $validated);

        Billmora::setAutomation($validated);

        return redirect()->back()->with('success', __('common.save_success', ['attribute' => __('admin/settings/automation.title')]));
    }
}
