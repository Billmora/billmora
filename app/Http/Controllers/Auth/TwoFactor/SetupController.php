<?php

namespace App\Http\Controllers\Auth\TwoFactor;

use Illuminate\Http\Request;
use App\Services\TwoFactorService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use App\Services\BillmoraService as Billmora;

class SetupController extends Controller
{
    protected TwoFactorService $twoFA;

    public function __construct(TwoFactorService $twoFA)
    {
        $this->twoFA = $twoFA;
    }
    
    public function index(Request $request)
    {
        $user = Auth::user();

        $twofa = $user->twoFactor;

        if ($user->twoFactor?->enabled) {
            return redirect()->route('client.account.security')->with('error',__('client.2fa_has_setup'));
        }

        if (!$twofa || $twofa->updated_at->diffInMinutes(now()) > 60) {
            $secretKey = $this->twoFA->generateSecretKey();

            $twofa = $user->twoFactor()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'secret_key' => Crypt::encrypt($secretKey),
                    'recovery_codes' => $this->twoFA->generateRecoveryCodes(),
                    'enabled' => false,
                    'downloaded' => false,
                ]
            );
        } else {
            $secretKey = Crypt::decrypt($twofa->secret_key);
        }

        $qrCode = $this->twoFA->generateQRCodeUrl(
            Billmora::getGeneral('company_name'),
            $user->email,
            $secretKey
        );

        return view('client::auth.two-factor.setup', compact('qrCode', 'secretKey'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'totp' => 'required|digits:6'
        ]);

        $secretKey = Crypt::decrypt($user->twoFactor->secret_key);

        if (!$this->twoFA->verifyOtp($secretKey, $request->totp)) {
            return back()->withErrors(['totp' => __('auth.totp_invalid')])->withInput();
        }

        return redirect()->route('client.two-factor.backup');
    }

    public function disable(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'totp' => 'required|digits:6'
        ]);

        $secretKey = Crypt::decrypt($user->twoFactor->secret_key);

        if (!$this->twoFA->verifyOtp($secretKey, $request->totp)) {
            return back()->withErrors(['totp' => __('auth.totp_invalid')])->withInput();
        }

        $user->twoFactor->update([
            'enabled' => false,
        ]);

        return back()->with('success', __('client.2fa_disabled'));
    }
}
