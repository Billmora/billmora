<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\AuthMail;
use App\Models\User;
use App\Services\BillmoraService as Billmora;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class RegisterController extends Controller
{
    public function index()
    {
        return view('client::auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|min:3|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone_number' => 'nullable|string',
            'company_name' => 'nullable|string',
            'street_address_1' => 'required|string',
            'street_address_2' => 'nullable|string',
            'city' => 'required|string',
            'country' => 'required|string',
            'state' => 'required|string',
            'postcode' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'company_name' => $request->company_name,
            'street_address_1' => $request->street_address_1,
            'street_address_2' => $request->street_address_2,
            'city' => $request->city,
            'country' => $request->country,
            'state' => $request->state,
            'postcode' => $request->postcode,
            'password' => bcrypt($request->password),
            'email_verified_at' => null,
        ]);

        Mail::to($user->email)->send(new AuthMail('user_registration', [
            'name' => $user->fullname,
            'company_name' => Billmora::getGeneral('company_name'),
            'company_url' => config('app.url'),
            'verify_url' => 'httpsL//billmora.com',
            'signature' => Billmora::getMail('mail_template_signature', '<p>Regards,<br/>Billmora<p/>'),
        ]));

        return redirect()->route('client.login')->with('success', 'Thank you for registering. We have sent you an email for verification. Please check your inbox and follow the instructions to verify your email.');
    }
}
