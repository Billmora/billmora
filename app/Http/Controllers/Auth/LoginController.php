<?php

namespace App\Http\Controllers\Auth;

use App\Facades\Audit;
use App\Http\Controllers\Controller;
use App\Services\CaptchaService;
use App\Traits\AuditsUser;
use Billmora;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    use AuditsUser;

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
            'email' => ['required', 'string', 'email:dns'],
            'password' => ['required'],
        ]);

        CaptchaService::verifyOrFail('login_form', $request);
    
        if (Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            $user = Auth::user();

            if (in_array($user->status, ['suspended', 'closed'])) {
                Auth::logout();
                return redirect()->back()->with('error', __('auth.account_' . $user->status));
            }

            if (Billmora::getAuth('user_require_verified') && !$user->isEmailVerified()) {
                $intended = session()->get('intended');

                Auth::logout();
        
                $verification = $user->getEmailVerification()->latest()->first();
                $token = $verification ? encrypt($verification->id) : null;

                if ($intended) {
                    session()->put('intended', $intended);
                }

                return redirect()->back()
                        ->with('error', __('auth.email.not_verified'))
                        ->with('email_token', $token);
            }

            $request->session()->regenerate();

            if ($user->twoFactor?->isActive()) {
                session()->forget('2fa_passed');
                return redirect()->route('client.two-factor.verify');
            }

            $this->recordActivity('account.login', ['method' => 'password'], $request);

            return redirect()->intended(route('client.dashboard'));
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

        session()->forget('2fa_passed');

        return redirect('/');
    }
}
