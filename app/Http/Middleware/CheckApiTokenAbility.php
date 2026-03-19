<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckApiTokenAbility
{
    /**
     * Handle an incoming request.
     *
     * Checks if the authenticated token has the required ability.
     * Returns 403 if the token lacks the required permission.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $ability
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, string $ability): Response
    {
        $token = $request->user()?->currentAccessToken();

        if (!$token) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        // Wildcard '*' grants all abilities
        if (!$token->can($ability)) {
            return response()->json([
                'message' => "This token does not have the required permission: {$ability}",
            ], 403);
        }

        return $next($request);
    }
}
