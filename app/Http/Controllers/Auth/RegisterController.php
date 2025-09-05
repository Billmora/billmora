<?php

namespace App\Http\Controllers\Auth;

use App\Mail\TemplateMail;
use App\Models\User;
use App\Http\Controllers\Controller;
use Billmora;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Mail;

class RegisterController extends Controller
{

    /**
     * Show the client registration form.
     *
     * @return \Illuminate\View\View The registration view.
     */
    public function index()
    {
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
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'min:3', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255','unique:users'],
            'phone_number' => ['nullable', 'numeric'],
            'company_name' => ['nullable', 'string'],
            'street_address_1' => ['nullable', 'string'],
            'street_address_2' => ['nullable', 'string'],
            'city' => ['nullable', 'string'],
            'state' => ['nullable', 'string'],
            'postcode' => ['nullable', 'string'],
            'country' => ['nullable', 'string', Rule::in(array_keys(config('utils.countries'))) ],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

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

        Mail::to($user->email)->send(new TemplateMail('user_registration', [
            'client_name' => $user->fullname,
            'company_name' => Billmora::getGeneral('company_name'),
            'verify_url' => 'https://billmora.com', // TODO: Will be replace from real url verification
            'client_url' => config('app.url'),
        ]));

        return redirect()->route('client.login')->with('success', __('auth.registration_successful'));
    }
}
