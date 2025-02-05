<?php

namespace App\Http\Middleware;

use Closure;
use App\Helpers\Config;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PortalMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $route): Response
    {
        if (Config::setting('company_portal', '1') == '0' && $request->is($route)) {
            return redirect('/client');
        }

        return $next($request);
    }
}
