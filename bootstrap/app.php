<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'panel.auth' => \App\Http\Middleware\DoctorPanelAuth::class,
            'panel.api' => \App\Http\Middleware\RequireApiIntegration::class,
            'panel.paket' => \App\Http\Middleware\RequirePaketOzellik::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'webhook/receiver',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
