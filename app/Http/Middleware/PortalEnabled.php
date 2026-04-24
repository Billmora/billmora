<?php

namespace App\Http\Middleware;

use Billmora;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PortalEnabled
{
    /**
     * Handle an incoming request.
     * Redirects to the client area if the company portal is disabled.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!(bool) Billmora::getGeneral('company_portal')) {
            return redirect()->route('client.dashboard');
        }

        return $next($request);
    }
}
