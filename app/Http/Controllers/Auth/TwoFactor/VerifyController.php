<?php

namespace App\Http\Controllers\Auth\TwoFactor;

use App\Http\Controllers\Controller;
use App\Services\TwoFactorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;

class VerifyController extends Controller
{
    /**
     * The Two-Factor Authentication service instance.
     *
     * @var \App\Services\TwoFactorService
     */
    protected $twoFA;

    /**
     * Create a new controller instance.
     *
     * @param  \App\Services\TwoFactorService  $twoFA
     * @return void
     */
    public function __construct(TwoFactorService $twoFA)
    {
        $this->twoFA = $twoFA;
    }
    
    /**
     * Display the verification page for Two-Factor Authentication.
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function index()
    {
        $user = Auth::user();

        if (!$user->twoFactor) {
            return redirect()->route('client.account.security')->with('error', __('auth.2fa.setup.not_setup'));
        }

        return view('client::auth.two-factor.verify');
    }

    /**
     * Verify the provided TOTP code and enable Two-Factor Authentication.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'totp' => ['required', 'digits:6']
        ]);

        $user = Auth::user();
        $secret = Crypt::decrypt($user->twoFactor->secret_key);

        if (!$this->twoFA->verifyOtp($secret, $request->totp)) {
            return back()->withErrors(['totp' => __('auth.2fa.verify.invalid_totp')])->withInput();
        }

        $user->twoFactor()->update([
            'enabled_at' => now()
        ]);
        session()->put('2fa_passed', true);

        return redirect()->route('client.account.security')->with('success', __('common.enable_success', ['attribute' => __('auth.2fa.title')]));
    } 
}