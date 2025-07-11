<?php

namespace App\Http\Controllers\Auth\TwoFactor;

use App\Http\Controllers\Controller;
use App\Services\TwoFactorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;

class VerifyController extends Controller
{
    protected TwoFactorService $twoFA;

    public function __construct(TwoFactorService $twoFA)
    {
        $this->twoFA = $twoFA;
    }
    
    public function index()
    {
        $user = Auth::user();

        if (!$user->twoFactor) {
            return redirect()->route('client.account.security')->with('error', __('client.2fa_not_setup'));
        }

        return view('client::auth.two-factor.verify');
    }

    public function store(Request $request)
    {
        $request->validate([
            'totp' => 'required|digits:6'
        ]);

        $user = Auth::user();
        $secret = Crypt::decrypt($user->twoFactor->secret_key);

        if (!$this->twoFA->verifyOtp($secret, $request->totp)) {
            return back()->withErrors(['totp' => __('auth.totp_invalid')])->withInput();
        }

        $user->twoFactor()->update(['enabled' => true]);
        session()->put('2fa_passed', true);

        return redirect()->route('client.account.security')->with('success', __('client.2fa_enabled'));
    }
}
