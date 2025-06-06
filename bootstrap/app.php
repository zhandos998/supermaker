<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up'
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role'  =>  \App\Http\Middleware\RoleMiddleware::class, // наш мидлвар роли
            'Image' => Intervention\Image\Facades\Image::class,
            'FFMpeg' => Pbmedia\LaravelFFMpeg\FFMpegFacade::class,
            'optional.auth' => \App\Http\Middleware\OptionalAuth::class,

        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
