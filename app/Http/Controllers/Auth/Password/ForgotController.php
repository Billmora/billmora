<?php

namespace App\Http\Controllers\Auth\Password;

use App\Http\Controllers\Controller;
use App\Mail\AuthMail;
use App\Models\User;
use App\Models\UserPasswordReset;
use App\Services\BillmoraService as Billmora;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ForgotController extends Controller
{
    public function index()
    {
        return view('client::auth.password.forgot');
    }

    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email:dns',
        ]);

        $user = User::where('email', $request->email)->first();
        
        if ($user) {
            $activeToken = UserPasswordReset::where('user_id', $user->id)
                    ->whereNull('verified_at')
                    ->where('expires_at', '>', now())
                    ->latest()
                    ->first();
    
            if ($activeToken) {
                return redirect()->route('client.login')->with('error', __('auth.password_have_request'));
            }

            $newToken = Str::random(64);
            UserPasswordReset::create([
                'user_id' => $user->id,
                'token' => $newToken,
                'expires_at' => now()->addMinutes(60),
            ]);

            Mail::to($user->email)->send(new AuthMail('user_password_reset', [
                'name' => $user->name,
                'company_name' => Billmora::getGeneral('company_name'),
                'company_url' => config('app.url'),
                'reset_url' => route('client.password.reset', ['token' => $newToken]),
                'signature' => Billmora::getMail('mail_template_signature'),
            ]));
        }

        return redirect()->route('client.password.forgot')->with('success', __('auth.password_reset_request'));
    }
}
