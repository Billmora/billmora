<?php

namespace App\Http\Controllers\Admin\Settings\General;

use App\Http\Controllers\Controller;
use App\Traits\AuditsSystem;
use Billmora;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CompanyController extends Controller
{
    use AuditsSystem;

    /**
     * Applies permission-based middleware for accessing general company settings
     * 
     * @return void
     */
    public function __construct()
    {
        $this->middleware('permission:settings.general.view')->only('index');
        $this->middleware('permission:settings.general.update')->only('update');
    }

    /**
     * Display the company settings view.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('admin::settings.general.company');
    }

    /**
     * Update general company settings.
     *
     * @param \Illuminate\Http\Request $request The incoming HTTP request containing company settings.
     *
     * @return \Illuminate\Http\RedirectResponse Redirects back with a success flash message.
     *
     * @throws \Illuminate\Validation\ValidationException If validation fails.
     */
    public function update(Request $request)
    {
        $validated = $request->validate( [
            'company_name' => ['required', 'string'],
            'company_logo' => ['required', 'url'],
            'company_favicon' => ['required', 'url'],
            'company_description' => ['required', 'string'],
            'company_portal' => ['required', 'boolean'],
            'company_date_format' => ['required', 'string'],
            'company_timezone' => ['required', 'string'],
            'company_language' => ['required', 'string'],
            'company_debug' => ['nullable', 'boolean'],
            'company_maintenance' => ['nullable', 'boolean'],
            'company_maintenance_url' => ['nullable', 'url'],
            'company_maintenance_message' => ['nullable', 'string'],
        ]);

        $this->updateSettings('general', $validated);

        Billmora::setEnv([
            'APP_LOCALE' => $validated['company_language'],
            'APP_DEBUG' => $validated['company_debug'],
            'APP_TIMEZONE' => $validated['company_timezone'],
        ]);

        Billmora::setGeneral($validated);

        return redirect()->back()->with('success', __('common.save_success', ['attribute' => __('admin/settings/general.title')]));
    }
}
