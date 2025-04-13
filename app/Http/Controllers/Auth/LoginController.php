<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

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
        $validator = $request->validate([
            'email' => 'required|string|email:dns',
            'password' => 'required',
        ]);
    
        if ($validator) {
            if (Auth::attempt($request->only('email', 'password'))) {
                $user = Auth::user();
    
                // if (!$user->hasVerifiedEmail()) {
                //     Auth::logout();
                //     return back()->with('error', 'Your email has not been verified.');
                // }
    
                $request->session()->regenerate();
                return redirect()->intended('/');
            }
        }
    
        return back()->with('error', 'Email or password is incorrect.')->onlyInput('email');;
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
