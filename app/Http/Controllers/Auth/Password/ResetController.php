<?php

namespace App\Http\Controllers\Auth\Password;

use App\Http\Controllers\Controller;
use App\Models\UserPasswordReset;
use Illuminate\Http\Request;

class ResetController extends Controller
{

    /**
     * Display the password reset form for a given token.
     *
     * @param  string  $token  The password reset token provided in the request.
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function index($token)
    {
        $verification = UserPasswordReset::where('token', $token)->first();

        if (!$verification) {
            return redirect()->route('client.login')->with('error', __('auth.password.invalid_request'));
        }

        if ($verification->isVerified() || $verification->isExpired()) {
            return redirect()->route('client.login')->with('error', __('auth.password.expired_request'));
        }

        return view('client::auth.password.reset')->with([
            'email' => $verification->user->email,
            'token' => $token,
        ]);
    }

    /**
     * Handle the password reset request.
     *
     * @param  \Illuminate\Http\Request  $request  The HTTP request containing the reset form data.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'email' => ['required', 'string', 'email:dns'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'reset_token' => ['required', 'string'],
        ]);

        $verification = UserPasswordReset::where('token', $request->reset_token)->first();

        if (!$verification || $verification->isExpired()) {
            return redirect()->route('client.login')->with('error', __('auth.password.invalid_request'));
        }

        $user = $verification->user;

        if ($user->email !== $request->email) {
            return redirect()->route('client.password.reset', ['token' => $request->reset_token])->with('error', __('auth.password.email_not_found'));
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

        return redirect()->route('client.login')->with('success', __('auth.password.reset_success'));
    }
}
