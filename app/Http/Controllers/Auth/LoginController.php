<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\BillmoraService as Billmora;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\CaptchaService;

class LoginController extends Controller
{
    public function index()
    {
        return view('client::auth.login');
    }

    /**
     * Handle an authentication attempt.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login(Request $request)
    {
        CaptchaService::verifyOrFail('user_login', $request);
        
        $request->validate([
            'email' => 'required|string|email:dns',
            'password' => 'required',
        ]);
    
        if (Auth::attempt($request->only('email', 'password'))) {
            $user = Auth::user();
    
            if (Billmora::getAuth('user_verified')) {
                if (!$user->hasVerifiedEmail()) {
                    Auth::logout();
        
                    $verification = $user->emailVerification()->latest()->first();
                    $token = $verification ? encrypt($verification->id) : null;
        
                    return back()
                        ->with('error', __('auth.email_not_verified'))
                        ->with('resend_token', $token);
                }
            }
    
            $request->session()->regenerate();
            return redirect()->intended('/');
        }
    
        return back()->with('error', __('auth.invalid_credentials'));
    }

    /**
     * Handle user logout.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
