<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ServiceScalingMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $scalingRoutes = [
            'client.services.scaling.show',
            'client.services.scaling.store',
        ];

        $currentRoute = $request->route()->getName();

        if (!in_array($currentRoute, $scalingRoutes)) {
            if ($request->session()->has('scaling')) {
                $request->session()->forget('scaling');
            }
        }

        return $next($request);
    }
}
