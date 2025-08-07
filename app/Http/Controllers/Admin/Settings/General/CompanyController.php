<?php

namespace App\Http\Controllers\Admin\Settings\General;

use Billmora;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class CompanyController extends Controller
{

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
     * Store general company settings.
     *
     * @param \Illuminate\Http\Request $request The incoming HTTP request containing company settings.
     *
     * @return \Illuminate\Http\RedirectResponse Redirects back with a success flash message.
     *
     * @throws \Illuminate\Validation\ValidationException If validation fails.
     */
    public function store(Request $request)
    {
        $validated = $request->validate( [
            'company_name' => ['required', 'string'],
            'company_logo' => ['required', 'url'],
            'company_favicon' => ['required', 'url'],
            'company_description' => ['required', 'string'],
            'company_portal' => ['required', 'boolean'],
            'company_date_format' => ['required', 'string'],
            'company_language' => ['required', 'string'],
            'company_maintenance' => ['nullable', 'boolean'],
            'company_maintenance_url' => ['nullable', 'url'],
            'company_maintenance_message' => ['nullable', 'string'],
        ]);

        Billmora::setEnv(['APP_LOCALE' => $validated['company_language']]);

        Billmora::setGeneral($validated);

        return redirect()->back()->with('success', __('admin/common.save_success', ['item' => __('admin/settings/general.title')]));
    }
}
