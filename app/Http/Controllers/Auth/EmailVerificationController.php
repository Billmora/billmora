<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\AuthMail;
use App\Models\User;
use App\Models\UserEmailVerification;
use App\Services\BillmoraService as Billmora;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class EmailVerificationController extends Controller
{
    public function handle(Request $request, string $token)
    {
        $verification = UserEmailVerification::where('token', $token)->first();

        if ($verification->isVerified()) {
            return redirect()->route('client.login')->with('error', __('auth.email_already_verified'));
        }

        if (! $verification) {
            return redirect()->route('client.login')->with('error', __('auth.email_invalid_token'));
        }
        
        if ($verification->isExpired()) {
            return redirect()->route('client.login')
                ->with('error', __('auth.email_expired_token'))
                ->with('resend_token', encrypt($verification->id));
        }

        $verification->update([
            'verified_at' => now(),
        ]);

        $verification->user->update([
            'email_verified_at' => now(),
        ]);

        return redirect()->route('client.login')->with('success', __('auth.email_has_verified'));
    }

    public function resend(Request $request)
    {
        $request->validate([
            'resend_token' => 'required|string',
        ]);

        try {
            $verificationId = decrypt($request->resend_token);
            $oldVerification = UserEmailVerification::findOrFail($verificationId);

            if ($oldVerification->isVerified()) {
                return redirect()->route('client.login')->with('error', __('auth.email_already_verified'));
            }

            $user = $oldVerification->user;

            $activeToken = UserEmailVerification::where('user_id', $user->id)
                ->whereNull('verified_at')
                ->where('expires_at', '>', now())
                ->latest()
                ->first();

            if ($activeToken) {
                return redirect()->route('client.login')->with('error', __('auth.email_have_token'));
            }

            $oldVerification->update([
                'expires_at' => now()->subMinute(),
            ]);

            $newToken = Str::random(64);
            $newVerification = UserEmailVerification::create([
                'user_id' => $user->id,
                'token' => $newToken,
                'expires_at' => now()->addMinutes(60),
            ]);

            Mail::to($user->email)->send(new AuthMail('user_resend_verification', [
                'name' => $user->full_name,
                'company_name' => Billmora::getGeneral('company_name'),
                'company_url' => config('app.url'),
                'verify_url' => route('client.email.verify', ['token' => $newToken]),
                'signature' => Billmora::getMail('mail_template_signature'),
            ]));

            return redirect()->route('client.login')->with('success', __('auth.email_resend_token'));
        } catch (\Exception $e) {
            return redirect()->route('client.login')->with('error', __('auth.email_invalid_request_token'));
        }
    }
}
