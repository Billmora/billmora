<?php

namespace App\Http\Controllers\Admin\Settings\Automation;

use App\Http\Controllers\Controller;
use App\Traits\AuditsSystem;
use Billmora;
use Illuminate\Http\Request;

class BillingController extends Controller
{
    use AuditsSystem;

    /**
     * Applies permission-based middleware for accessing automation billing settings
     * 
     * @return void
     */
    public function __construct()
    {
        $this->middleware('permission:settings.automation.view')->only('index');
        $this->middleware('permission:settings.automation.update')->only('update');
    }

    /**
     * Display the automation billing settings page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('admin::settings.automation.billing');
    }

    /**
     * Validate and persist the automation billing configuration settings.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'invoice_generation_days' => ['required', 'integer', 'min:0'],
            'invoice_reminder_days' => ['required', 'integer', 'min:0', 'lte:invoice_generation_days'],
            'invoice_overdue_first_days' => ['required', 'integer', 'min:0'],
            'invoice_overdue_second_days' => ['required', 'integer', 'min:0'],
            'invoice_overdue_third_days' => ['required', 'integer', 'min:0'],
            'invoice_auto_cancel_days' => ['required', 'integer', 'min:0'],
        ]);

        $this->updateSettings('automation', $validated);

        Billmora::setAutomation($validated);

        return redirect()->back()->with('success', __('common.save_success', ['attribute' => __('admin/settings/automation.title')]));
    }
}
