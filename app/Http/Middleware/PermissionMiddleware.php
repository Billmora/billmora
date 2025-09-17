<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Spatie\Permission\Middleware\PermissionMiddleware as SpatiePermission;
use Symfony\Component\HttpFoundation\Response;

class PermissionMiddleware extends SpatiePermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, $next, $permission, $guard = null)
    {
        if ($request->user()?->isRootAdmin()) {
            return $next($request);
        }

        return parent::handle($request, $next, $permission, $guard);
    }
}
