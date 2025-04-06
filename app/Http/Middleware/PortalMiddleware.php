<?php

namespace App\Http\Middleware;

use App\Services\BillmoraService as Billmora;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PortalMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Billmora::getGeneral('company_portal', true) == false && $request->is('/')) {
            return redirect('/client');
        }
        
        return $next($request);
    }
}
