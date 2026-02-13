<?php

namespace App\Http\Controllers\Auth;

use App\Facades\Audit;
use Billmora;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Mail\TemplateMail;
use App\Models\UserEmailVerification;
use App\Services\CaptchaService;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Mail;

class RegisterController extends Controller
{

    /**
     * Show the client registration form.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse Returns the registration view if enabled, otherwise redirects to login.
     */
    public function index()
    {
        if (!Billmora::getAuth('user_registration')) {
            return redirect()->route('client.login')->with('warning', __('auth.registration_disabled'));
        }

        return view('client::auth.register');
    }

    /**
     * Handle a new client registration.
     *
     * @param \Illuminate\Http\Request $request The incoming HTTP request containing registration data.
     * @return \Illuminate\Http\RedirectResponse Redirects to the login page with a success message.
     *
     * @throws \Illuminate\Validation\ValidationException If validation fails.
     */
    public function store(Request $request)
    {
        if (!Billmora::getAuth('user_registration')) {
            return redirect()->route('client.login')->with('warning', __('auth.registration_disabled'));
        }

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'min:3', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255','unique:users'],
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
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        CaptchaService::verifyOrFail('register_form', $request);

        $user = User::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
        ]);

        $user->billing()->create([
            'phone_number' => $validated['phone_number'],
            'company_name' => $validated['company_name'],
            'street_address_1' => $validated['street_address_1'],
            'street_address_2' => $validated['street_address_2'],
            'city' => $validated['city'],
            'country' => $validated['country'],
            'state' => $validated['state'],
            'postcode' => $validated['postcode'],
        ]);

        $token = Str::random(64);
        UserEmailVerification::create([
            'user_id' => $user->id,
            'token' => $token,
            'expires_at' => now()->addMinutes(60),
        ]);

        $auditEmail = Audit::email(
            $user->id,
            $user->email,
            'user_registration',
            'pending',
            [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]
        );

        try {
            Mail::to($user->email)->send(new TemplateMail(
                'user_registration', 
                [
                    'client_name' => $user->fullname,
                    'company_name' => Billmora::getGeneral('company_name'),
                    'verify_url' => route('client.email.verify', ['token' => $token]),
                    'clientarea_url' => config('app.url'),
                ],
                $user->language,
            ));

            $auditEmail->update([
                'status' => 'sent',
            ]);
        } catch (\Throwable $e) {
            $auditEmail->update([
                'status' => 'failed',
                'properties' => array_merge($auditEmail->properties ?? [], [
                    'error' => $e->getMessage(),
                ]),
            ]);
        }

        return redirect()->route('client.login')->with('success', __('auth.registration_successful'));
    }
}
