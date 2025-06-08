<?php

namespace App\Http\Controllers\Auth\Password;

use App\Http\Controllers\Controller;
use App\Models\UserPasswordReset;
use Illuminate\Http\Request;

class ResetController extends Controller
{
    public function index(Request $request, $token)
    {
        $verification = UserPasswordReset::where('token', $token)->first();

        if ($verification->isVerified()) {
            return redirect()->route('client.login')->with('error', __('auth.password_invalid_request'));
        }

        if (!$verification) {
            return redirect()->route('client.login')->with('error', __('auth.password_invalid_request'));
        }

        if ($verification->isExpired()) {
            return redirect()->route('client.login')->with('error', __('auth.password_expired_request'));
        }

        return view('client::auth.password.reset')->with([
            'email' => $verification->user->email,
            'token' => $token,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email:dns',
            'password' => 'required|string|min:8|confirmed',
            'token' => 'required',
        ]);

        $verification = UserPasswordReset::where('token', $request->token)->first();

        if (!$verification || $verification->isExpired()) {
            return redirect()->route('client.login')->with('error', __('auth.password_invalid_request'));
        }

        $user = $verification->user;

        if ($user->email !== $request->email) {
            return redirect()->route('client.password.reset', ['token' => $request->token])->with('error', __('auth.password_email_mismatch'));
        }

        $user->update([
            'password' => bcrypt($request->password),
        ]);

        if ($user->email_verified_at === null) {
            $user->update([
                'email_verified_at' => now(),
            ]);
        }

        $verification->markAsVerified();

        return redirect()->route('client.login')->with('success', __('auth.password_reset_success'));
    }
}
