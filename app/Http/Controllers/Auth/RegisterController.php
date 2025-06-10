<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\AuthMail;
use App\Models\User;
use App\Models\UserEmailVerification;
use App\Services\BillmoraService as Billmora;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Services\CaptchaService;

class RegisterController extends Controller
{
    public function index()
    {
        return view('client::auth.register');
    }

    public function register(Request $request)
    {
        CaptchaService::verifyOrFail('user_register', $request);
        
        $requiredFields = Billmora::getAuth('form_required', []);
        $disabledFields = Billmora::getAuth('form_disable', []);

        $validation = [
            'first_name' => 'required|string|min:3|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone_number' => 'nullable|string',
            'company_name' => 'nullable|string',
            'street_address_1' => 'nullable|string',
            'street_address_2' => 'nullable|string',
            'city' => 'nullable|string',
            'country' => 'nullable|string',
            'state' => 'nullable|string',
            'postcode' => 'nullable|string',
            'password' => 'nullable|string|min:8|confirmed',
        ];

        foreach ($disabledFields as $field) {
            unset($validation[$field]);
        }
    
        foreach ($requiredFields as $field) {
            if (isset($validation[$field])) {
                $validation[$field] = str_replace('nullable', 'required', $validation[$field]);
            }
        }

        $request->validate($validation);

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'email_verified_at' => null,
        ]);

        $user->billing()->create([
            'phone_number' => $request->phone_number,
            'company_name' => $request->company_name,
            'street_address_1' => $request->street_address_1,
            'street_address_2' => $request->street_address_2,
            'city' => $request->city,
            'country' => $request->country,
            'state' => $request->state,
            'postcode' => $request->postcode,
        ]);

        $token = Str::random(64);
        UserEmailVerification::create([
            'user_id' => $user->id,
            'token' => $token,
            'expires_at' => now()->addMinutes(60),
        ]);

        Mail::to($user->email)->send(new AuthMail('user_registration', [
            'name' => $user->name,
            'company_name' => Billmora::getGeneral('company_name'),
            'company_url' => config('app.url'),
            'verify_url' => route('client.email.verify', ['token' => $token]),
            'signature' => Billmora::getMail('mail_template_signature'),
        ]));

        return redirect()->route('client.login')->with('success', __('auth.email_registering'));
    }
}
