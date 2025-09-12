<?php

namespace App\Http\Middleware;

use Closure;
use Billmora;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class MaintenanceMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Billmora::getGeneral('company_maintenance') && !Auth::user()->isAdmin()) {
            $maintenanceUrl = Billmora::getGeneral('company_maintenance_url');
            $maintenanceMsg = Billmora::getGeneral('company_maintenance_message');
            
            if ($maintenanceUrl) {
                return redirect()->to($maintenanceUrl);
            } else {
                return response()->view('client::maintenance',[
                    'message' => $maintenanceMsg
                ], 503);
            }
        }
        
        return $next($request);
    }
}
