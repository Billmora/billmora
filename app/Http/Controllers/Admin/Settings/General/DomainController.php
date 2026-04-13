<?php

namespace App\Http\Controllers\Admin\Settings\General;

use App\Http\Controllers\Controller;
use App\Traits\AuditsSystem;
use Billmora;
use Illuminate\Http\Request;

class DomainController extends Controller
{
    use AuditsSystem;
        
    /**
     * Applies permission-based middleware for accessing general domain settings
     * 
     * @return void
     */
    public function __construct()
    {
        $this->middleware('permission:settings.general.view')->only('index');
        $this->middleware('permission:settings.general.update')->only('update');
    }

    /**
     * Display the domain settings view.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('admin::settings.general.domain');
    }


    /**
     * Update general domain settings.
     *
     * @param \Illuminate\Http\Request $request The incoming HTTP request containing domain settings.
     *
     * @return \Illuminate\Http\RedirectResponse Redirects back with a success flash message.
     *
     * @throws \Illuminate\Validation\ValidationException If validation fails.
     */
    public function update(Request $request)
    {


        $validated = $request->validate([
            'domain_registration_enabled' => ['required', 'boolean'],
            'domain_transfer_enabled' => ['required', 'boolean'],
            'domain_number_increment' => ['required', 'integer', 'min:1'],
            'domain_number_padding' => ['required', 'integer', 'min:1'],
            'domain_number_format' => [
                'required',
                'string',
                'regex:/^\S+$/',
                'regex:/\{number\}/',
                'regex:/^[^{}]*(\{(number|day|month|year)\}[^{}]*)*$/',
            ],
        ]);

        $this->updateSettings('general', $validated);

        Billmora::setGeneral($validated);

        return redirect()->back()->with('success', __('common.save_success', ['attribute' => __('admin/settings/general.title')]));
    }
}
