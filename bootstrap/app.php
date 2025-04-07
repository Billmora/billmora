<?php

use Illuminate\Http\Request;
use App\Http\Middleware\LanguageMiddleware;
use App\Http\Middleware\PortalMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Session\Middleware\StartSession;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->append(StartSession::class);
        $middleware->append(LanguageMiddleware::class);
        $middleware->redirectGuestsTo(fn (Request $request) => route('client.login'));
        $middleware->redirectUsersTo(fn (Request $request) => route('client.dashboard'));
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
