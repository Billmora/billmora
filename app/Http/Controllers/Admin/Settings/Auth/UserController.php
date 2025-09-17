<?php

namespace App\Http\Controllers\Admin\Settings\Auth;

use App\Http\Controllers\Controller;
use Billmora;
use Illuminate\Http\Request;

class UserController extends Controller
{

    /**
     * Display the authentication user settings page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('admin::settings.auth.user');
    }

    /**
     * Update authentication user settings.
     *
     * @param \Illuminate\Http\Request $request The incoming HTTP request containing authentication user settings.
     *
     * @return \Illuminate\Http\RedirectResponse Redirects back with a success flash message.
     *
     * @throws \Illuminate\Validation\ValidationException If the request validation fails.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'user_registration' => ['nullable', 'boolean'],
            'user_require_verified' => ['nullable', 'boolean'],
            'user_require_two_factor' => ['nullable', 'boolean'],
            'user_registration_disabled_inputs' => ['nullable', 'array'],
            'user_registration_disabled_inputs.*' => ['in:phone_number,company_name,street_address_1,street_address_2,city,state,postcode,country,'],
            'user_billing_required_inputs' => ['nullable', 'array'],
            'user_billing_required_inputs.*' => ['in:phone_number,company_name,street_address_1,street_address_2,city,state,postcode,country,'],
        ]);

        $validated += [
            'user_registration_disabled_inputs' => [],
            'user_billing_required_inputs' => [],
        ];

        Billmora::setAuth($validated);

        return redirect()->back()->with('success', __('common.save_success', ['attribute' => __('admin/settings/auth.title')]));
    }
}
