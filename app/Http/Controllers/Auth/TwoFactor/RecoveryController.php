<?php

namespace App\Http\Controllers\Auth\TwoFactor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RecoveryController extends Controller
{

    /**
     * Show the recovery code input page.
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function index()
    {
        $user = Auth::user();

        if (!$user->twoFactor) {
            return redirect()->route('client.account.security')->with('error', __('auth.2fa.setup.not_setup'));
        }

        return view('client::auth.two-factor.recovery');
    }

    /**
     * Process the submitted recovery code and disable Two-Factor Authentication.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'recovery_code' => ['required', 'string'],
        ]);

        $user = Auth::user();

        $codes = $user->twoFactor->recovery_codes ?? [];
        if (!in_array($request->recovery_code, $codes)) {
            return back()->withErrors(['recovery_code' => __('auth.2fa.recovery.invalid_code')])->withInput();
        }

        $user->twoFactor->update([
            'enabled_at' => null,
        ]);
        session()->forget('2fa_passed');

        return redirect()->route('client.account.security')->with('success', __('common.disable_success', ['attribute' => __('auth.2fa.title')]));
    }
}