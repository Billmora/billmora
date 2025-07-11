<?php

namespace App\Http\Controllers\Auth\TwoFactor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RecoveryController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if (!$user->twoFactor) {
            return redirect()->route('client.account.security')->with('error', __('client.2fa_not_setup'));
        }

        return view('client::auth.two-factor.recovery');
    }

    public function store(Request $request)
    {
        $request->validate([
            'recovery_code' => 'required|string',
        ]);

        $user = Auth::user();

        $codes = $user->twoFactor->recovery_codes ?? [];
        if (!in_array($request->recovery_code, $codes)) {
            return back()->withErrors([
                'recovery_code' => __('auth.recovery_code_invalid'),
            ])->withInput();
        }

        $user->twoFactor->update(
            ['enabled' => false]
        );
        session()->forget('2fa_passed');

        return redirect()->route('client.dashboard')->with('success', __('client.2fa_disabled'));
    }
}
