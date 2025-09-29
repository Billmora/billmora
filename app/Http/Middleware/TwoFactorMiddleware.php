<?php

namespace App\Http\Middleware;

use Billmora;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class TwoFactorMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (Billmora::getAuth('user_require_two_factor') && !$user?->twoFactor?->isActive()) {
            return redirect()->route('client.two-factor.setup')->with('warning', __('auth.2fa.required'));
        }

        if ($user->twoFactor?->isActive() && !session()->get('2fa_passed')) {
            return redirect()->route('client.two-factor.verify');
        }

        return $next($request);
    }
}
