<?php

use Illuminate\Http\Request;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(dirname(__DIR__))
    ->withRouting(
        web: [
            'portal' => __DIR__.'/../routes/web/portal.php',
            'client' => __DIR__.'/../routes/web/client.php',
        ],
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(\App\Http\Middleware\LanguageMiddleware::class);
        $middleware->group('maintenance', [
            \App\Http\Middleware\MaintenanceMiddleware::class,
        ]);
        $middleware->group('2fa', [
            \App\Http\Middleware\TwoFactorMiddleware::class,
        ]);
        $middleware->redirectGuestsTo(fn (Request $request) => route('client.login'));
        $middleware->redirectUsersTo(fn (Request $request) => route('client.dashboard'));
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
