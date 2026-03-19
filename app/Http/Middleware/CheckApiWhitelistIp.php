<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class CheckApiWhitelistIp
{
    /**
     * Handle an incoming request.
     *
     * Checks the request IP against the token's whitelist_ips.
     * If whitelist_ips is empty/null, all IPs are allowed.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->user()?->currentAccessToken();

        if (!$token instanceof PersonalAccessToken) {
            return $next($request);
        }

        $whitelist = $token->whitelist_ips;

        if (!empty($whitelist)) {
            $allowedIps = array_map('trim', explode(',', $whitelist));

            if (!in_array($request->ip(), $allowedIps)) {
                return response()->json([
                    'message' => 'Your IP address is not allowed to use this token.',
                ], 403);
            }
        }

        return $next($request);
    }
}
