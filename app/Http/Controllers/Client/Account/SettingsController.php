<?php

namespace App\Http\Controllers\Client\Account;

use App\Http\Controllers\Controller;
use Billmora;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class SettingsController extends Controller
{

    /**
     * Display the authenticated user's account settings page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = Auth::user()->with('billing')->first();

        return view('client::account.settings', compact('user'));
    }

    /**
     * Update the authenticated user's account and billing information.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'min:3', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'currency' => ['required', 'string'], // TODO: Add currency validation rule
            'language' => ['required', 'string', Rule::in(array_map('basename', File::directories(lang_path())))],
            'phone_number' => [
                Rule::requiredIf(Billmora::hasAuth('user_billing_required_inputs', 'phone_number')),
                Rule::prohibitedIf(Billmora::hasAuth('user_registration_disabled_inputs', 'phone_number')),
                'nullable', 'numeric',
            ],
            'company_name' => [
                Rule::requiredIf(Billmora::hasAuth('user_billing_required_inputs', 'company_name')),
                Rule::prohibitedIf(Billmora::hasAuth('user_registration_disabled_inputs', 'company_name')),
                'nullable', 'string',
            ],
            'street_address_1' => [
                Rule::requiredIf(Billmora::hasAuth('user_billing_required_inputs', 'street_address_1')),
                Rule::prohibitedIf(Billmora::hasAuth('user_registration_disabled_inputs', 'street_address_1')),
                'nullable', 'string',
            ],
            'street_address_2' => [
                Rule::requiredIf(Billmora::hasAuth('user_billing_required_inputs', 'street_address_2')),
                Rule::prohibitedIf(Billmora::hasAuth('user_registration_disabled_inputs', 'street_address_2')),
                'nullable', 'string',
            ],
            'city' => [
                Rule::requiredIf(Billmora::hasAuth('user_billing_required_inputs', 'city')),
                Rule::prohibitedIf(Billmora::hasAuth('user_registration_disabled_inputs', 'city')),
                'nullable', 'string',
            ],
            'state' => [
                Rule::requiredIf(Billmora::hasAuth('user_billing_required_inputs', 'state')),
                Rule::prohibitedIf(Billmora::hasAuth('user_registration_disabled_inputs', 'state')),
                'nullable', 'string',
            ],
            'postcode' => [
                Rule::requiredIf(Billmora::hasAuth('user_billing_required_inputs', 'postcode')),
                Rule::prohibitedIf(Billmora::hasAuth('user_registration_disabled_inputs', 'postcode')),
                'nullable', 'string',
            ],
            'country' => [
                Rule::requiredIf(Billmora::hasAuth('user_billing_required_inputs', 'country')),
                Rule::prohibitedIf(Billmora::hasAuth('user_registration_disabled_inputs', 'country')),
                'nullable', 'string',
                Rule::in(array_keys(config('utils.countries'))),
            ],
        ]);

        $user->update([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'currency' => $validated['currency'],
            'language' => $validated['language'],
        ]);

        $user->refresh();

        $user->billing()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'phone_number' => $validated['phone_number'],
                'company_name' => $validated['company_name'],
                'street_address_1' => $validated['street_address_1'],
                'street_address_2' => $validated['street_address_2'],
                'city' => $validated['city'],
                'state' => $validated['state'],
                'postcode' => $validated['postcode'],
                'country' => $validated['country'],
            ]
        );

        return redirect()->back()->with('success', __('common.update_success', ['attribute' => __('common.account_settings')]));
    }
}
