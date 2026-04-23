<?php

namespace App\Http\Middleware;

use Billmora;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DomainEnabled
{
    /**
     * Handle an incoming request.
     * Aborts with 404 if domain features are globally disabled.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $registrationEnabled = (bool) Billmora::getGeneral('domain_registration_enabled');
        $transferEnabled     = (bool) Billmora::getGeneral('domain_transfer_enabled');

        if (!$registrationEnabled && !$transferEnabled) {
            abort(404);
        }

        return $next($request);
    }
}
