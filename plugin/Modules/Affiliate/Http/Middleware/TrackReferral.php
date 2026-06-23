<?php

namespace Plugins\Modules\Affiliate\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Plugins\Modules\Affiliate\Models\AffiliateMember;
use Plugins\Modules\Affiliate\Models\AffiliateReferral;
use App\Models\Plugin;

class TrackReferral
{
    /**
     * Handle an incoming request and set referral cookie if applicable.
     */
    public function handle(Request $request, Closure $next)
    {
        $refQuery = $request->query('ref');
        $refCookie = $request->cookie('affiliate_ref');

        // If no referral code in query OR cookie, skip entirely
        if (empty($refQuery) && empty($refCookie)) {
            return $next($request);
        }

        $activeRef = $refQuery ?: $refCookie;

        // Check if the Affiliate module is active
        $plugin = Plugin::where('type', 'module')
            ->where('provider', 'Affiliate')
            ->where('is_active', true)
            ->first();

        if (!$plugin) {
            return $next($request);
        }

        $config = $plugin->config ?? [];
        $scope = data_get($config, 'referral_scope', 'new_users_only');

        // If scope is new_users_only, skip for logged-in users
        if ($scope === 'new_users_only' && Auth::check()) {
            // But if there's a query param, we should still queue the cookie 
            // in case they log out and register a new account?
            // Actually, if they are logged in and scope is new_users_only, 
            // we don't track them.
            return $next($request);
        }

        // If scope is all_users and user is logged in, check if already referred
        if ($scope === 'all_users' && Auth::check()) {
            if (AffiliateReferral::where('referred_user_id', Auth::id())->exists()) {
                return $next($request);
            }
        }

        // Validate referral code exists and is active
        $member = AffiliateMember::where('referral_code', $activeRef)
            ->where('status', 'active')
            ->first();

        if (!$member) {
            return $next($request);
        }

        // Prevent self-referral for logged-in users
        if (Auth::check() && $member->user_id === Auth::id()) {
            return $next($request);
        }

        $lifetime = (int) data_get($config, 'cookie_lifetime_days', 30);

        // Only queue the cookie if it was passed via query, or we want to refresh it.
        // Actually, just always queue it to refresh the lifetime.
        Cookie::queue('affiliate_ref', $activeRef, $lifetime * 60 * 24);

        // If scope is all_users and user is already logged in, create referral directly
        if ($scope === 'all_users' && Auth::check()) {
            if (!AffiliateReferral::where('referred_user_id', Auth::id())->exists()) {
                AffiliateReferral::create([
                    'affiliate_member_id' => $member->id,
                    'referred_user_id'    => Auth::id(),
                ]);
                Cookie::queue(Cookie::forget('affiliate_ref'));
            }
        }

        return $next($request);
    }
}
