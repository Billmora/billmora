<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Billmora;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{

    /**
     * Show the client login form.
     *
     * @return \Illuminate\View\View The login view.
     */
    public function index()
    {
        return view('client::auth.login');
    }

    /**
     * Handle an authentication attempt.
     *
     * @param \Illuminate\Http\Request $request The incoming HTTP request containing login credentials.
     * @return \Illuminate\Http\RedirectResponse Redirects to intended route on success, or back with an error message on failure.
     */
    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email:dns',
            'password' => 'required',
        ]);
    
        if (Auth::attempt($request->only('email', 'password'))) {
            $request->session()->regenerate();

            return redirect()->intended('/');
        }
    
        return back()->with('error', __('auth.invalid_credentials'));
    }

    /**
     * Log the user out of the application.
     *
     * @param \Illuminate\Http\Request $request The incoming HTTP request instance.
     * @return \Illuminate\Http\RedirectResponse Redirects to the home page after logout.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
