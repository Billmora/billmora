<?php

namespace App\Http\Controllers\Auth;

use App\Facades\Audit;
use App\Http\Controllers\Controller;
use App\Mail\TemplateMail;
use App\Models\UserEmailVerification;
use Billmora;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;

class EmailVerificationController extends Controller
{

    /**
     * Handle email verification request using a token.
     *
     * @param  string  $token  The verification token provided in the verification link.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function verify(Request $request, $token)
    {
        $verification = UserEmailVerification::where('token', $token)->first();

        if (!$verification) {
            return redirect()->route('client.login')->with('error', __('auth.email.invalid_token'));
        }

        if ($verification->isVerified()) {
            return redirect()->route('client.login')->with('warning', __('auth.email.already_verified'));
        }
        
        if ($verification->isExpired()) {
            return redirect()->route('client.login')
                ->with('error', __('auth.email.expired_token'))
                ->with('email_token', encrypt($verification->id));
        }

        $verification->update([
            'verified_at' => now(),
        ]);

        $verification->user->update([
            'email_verified_at' => now(),
        ]);

        $user = $verification->user;
        
        Audit::user($user->id, 'account.email.verify', [
            'method' => 'token',
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('client.login')->with('success', __('auth.email.has_verified'));
    }

    /**
     * Resend a new verification email when the previous token has expired.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function resend(Request $request)
    {
        $request->validate([
            'email_token' => ['required', 'string'],
        ]);

        try {
            $verificationId = decrypt($request->email_token);
            $oldVerification = UserEmailVerification::findOrFail($verificationId);

            if ($oldVerification->isVerified()) {
                return redirect()->route('client.login')->with('warning', __('auth.email.already_verified'));
            }

            $user = $oldVerification->user;

            $activeToken = UserEmailVerification::where('user_id', $user->id)
                ->whereNull('verified_at')
                ->latest()
                ->first();

            if ($activeToken && $activeToken->isActive()) {
                return redirect()->route('client.login')->with('warning', __('auth.email.already_requested'));
            }

            $newToken = Str::random(64);
            UserEmailVerification::create([
                'user_id' => $user->id,
                'token' => $newToken,
                'expires_at' => now()->addMinutes(60),
            ]);

            $auditEmail = Audit::email(
                $user->id,
                $user->email,
                'user_resend_verification',
                'pending',
            );

            try {
                Mail::to($user->email)->send(new TemplateMail('user_resend_verification', [
                    'client_name' => $user->fullname,
                    'company_name' => Billmora::getGeneral('company_name'),
                    'verify_url' => route('client.email.verify', ['token' => $newToken]),
                    'clientarea_url' => config('app.url'),
                ]));

                $auditEmail->update([
                    'status' => 'sent',
                    'properties' => [
                        'ip' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                    ],
                ]);
            } catch (\Throwable $e) {
                $auditEmail->update([
                    'status' => 'failed',
                    'properties' => [
                        'ip' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                        'error' => $e->getMessage(),
                    ],
                ]);
            }

            return redirect()->route('client.login')->with('success', __('auth.email.resent'));
        } catch (\Exception $e) {
            return redirect()->route('client.login')->with('error', __('auth.email.invalid_request'));
        }
    }
}
