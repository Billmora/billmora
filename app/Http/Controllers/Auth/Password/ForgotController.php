<?php

namespace App\Http\Controllers\Auth\Password;

use App\Events\User as UserEvents;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserPasswordReset;
use App\Services\CaptchaService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ForgotController extends Controller
{
    /**
     * Show the forgot password form.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('client::auth.password.forgot');
    }

    /**
     * Handle forgot password form submission.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'email' => ['required', 'string', 'email:dns'],
        ]);

        CaptchaService::verifyOrFail('forgot_password_form', $request);

        $user = User::where('email', $request->email)->first();
        
        if ($user) {
            $activeToken = UserPasswordReset::where('user_id', $user->id)
                    ->whereNull('verified_at')
                    ->latest()
                    ->first();
    
            if ($activeToken && $activeToken->isActive()) {
                return redirect()->route('client.login')->with('warning', __('auth.password.already_requested'));
            }

            $newToken = Str::random(64);
            UserPasswordReset::create([
                'user_id' => $user->id,
                'token' => $newToken,
                'expires_at' => now()->addMinutes(60),
            ]);

            event(new UserEvents\PasswordResetRequested($user, $newToken));
        }

        return redirect()->route('client.password.forgot')->with('success', __('auth.password.reset_request_sent'));
    }
}
