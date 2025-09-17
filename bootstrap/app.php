<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: [
            'admin' => __DIR__.'/../routes/web/admin.php',
            'client' => __DIR__.'/../routes/web/client.php',
            'common' => __DIR__.'/../routes/web/common.php',
        ],
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(\App\Http\Middleware\LanguageMiddleware::class);
        $middleware->alias([
            'permission' => \App\Http\Middleware\PermissionMiddleware::class,
            'role' => Spatie\Permission\Middleware\RoleMiddleware::class,
            'role_or_permission' => Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'admin'   => \App\Http\Middleware\AdminMiddleware::class,
            'maintenance'   => \App\Http\Middleware\MaintenanceMiddleware::class,
            '2fa'   => \App\Http\Middleware\TwoFactorMiddleware::class,
        ]);
        $middleware->redirectGuestsTo(fn () => route('client.login'));
        $middleware->redirectUsersTo(fn () => route('client.dashboard'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
