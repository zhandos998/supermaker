<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // $middleware->append(\App\Http\Middleware\RoleMiddleware::class);

        // $middleware->append(\Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class);
        // $middleware->append('throttle:api');
        // $middleware->append(\Illuminate\Routing\Middleware\SubstituteBindings::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
