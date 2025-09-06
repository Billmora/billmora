<?php

namespace App\Http\Controllers\Auth\TwoFactor;

use App\Http\Controllers\Controller;
use App\Services\TwoFactorService;
use Billmora;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;

class SetupController extends Controller
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
     * Display the Two-Factor Authentication setup page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $twofa = $user->twoFactor;

        if ($user->twoFactor?->enabled) {
            return redirect()->route('client.account.security')->with('error',__('auth.2fa.setup.has_setup'));
        }

        if (!$twofa || $twofa->updated_at->diffInMinutes(now()) > 60) {
            $secretKey = $this->twoFA->generateSecretKey();

            $twofa = $user->twoFactor()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'secret_key' => Crypt::encrypt($secretKey),
                    'recovery_codes' => $this->twoFA->generateRecoveryCodes(),
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

    /**
     * Verify the submitted TOTP code during Two-Factor Authentication setup.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'totp' => ['required', 'digits:6']
        ]);

        $secretKey = Crypt::decrypt($user->twoFactor->secret_key);

        if (!$this->twoFA->verifyOtp($secretKey, $request->totp)) {
            return back()->withErrors(['totp' => __('auth.2fa.verify.invalid_totp')])->withInput();
        }

        return redirect()->route('client.two-factor.backup');
    }

    /**
     * Disable Two-Factor Authentication (2FA) for the authenticated user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function disable(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'totp' => ['required', 'digits:6']
        ]);

        $secretKey = Crypt::decrypt($user->twoFactor->secret_key);

        if (!$this->twoFA->verifyOtp($secretKey, $request->totp)) {
            return back()->withErrors(['totp' => __('auth.2fa.verify.invalid_totp')])->withInput();
        }

        $user->twoFactor->update([
            'enabled_at' => null,
        ]);

        return redirect()->back()->with('success', __('common.disable_success', ['attribute' => __('auth.2fa.title')]));
    }
}