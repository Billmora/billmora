<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Services\BillmoraService as Billmora;
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
        if (Billmora::getGeneral('company_maintenance') && !Auth::user()->is_admin) {
            $maintenanceUrl = Billmora::getGeneral('company_maintenance_url');
            
            if ($maintenanceUrl) {
                return redirect()->to($maintenanceUrl);
            } else {
                return response()->view('client::maintenance', [
                    'message' => Billmora::getGeneral('company_maintenance_message')
                ], 503);
            }
        }
        
        return $next($request);
    }
}
