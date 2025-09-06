<?php

namespace App\Http\Controllers\Client\Account;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

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
            'phone_number' => ['nullable', 'numeric'],
            'company_name' => ['nullable', 'string'],
            'street_address_1' => ['nullable', 'string'],
            'street_address_2' => ['nullable', 'string'],
            'city' => ['nullable', 'string'],
            'state' => ['nullable', 'string'],
            'postcode' => ['nullable', 'string'],
            'country' => ['nullable', 'string', Rule::in(array_keys(config('utils.countries'))) ],
        ]);

        $user->update([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
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

        return redirect()->back()->with('success', __('common.update_success', ['attribute' => __('common.account_information')]));
    }
}
