<?php

namespace App\Http\Controllers\Admin\Settings\Automation;

use App\Http\Controllers\Controller;
use App\Traits\AuditsSystem;
use Billmora;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    use AuditsSystem;

    /**
     * Applies permission-based middleware for accessing automation service settings
     * 
     * @return void
     */
    public function __construct()
    {
        $this->middleware('permission:settings.automation.view')->only('index');
        $this->middleware('permission:settings.automation.update')->only('update');
    }

    /**
     * Display the automation service settings page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('admin::settings.automation.service');
    }

    /**
     * Validate and persist the automation service configuration settings.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'service_suspend_days' => ['required', 'integer', 'min:0'],
            'service_terminate_days' => ['required', 'integer', 'min:0', 'gte:service_suspend_days'],
            'auto_accept_cancellation' => ['required', 'boolean'],
        ]);

        $this->updateSettings('automation', $validated);

        Billmora::setAutomation($validated);

        return redirect()->back()->with('success', __('common.save_success', ['attribute' => __('admin/settings/automation.title')]));
    }
}
