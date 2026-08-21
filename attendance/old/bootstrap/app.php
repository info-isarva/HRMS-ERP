<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Laravel\Sanctum\SanctumServiceProvider; // Add this line
use Tymon\JWTAuth\Providers\LaravelServiceProvider as JWTAuthServiceProvider;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Resolve tenant after session/cookies are initialized.
        $middleware->web(append: [\App\Http\Middleware\ResolveTenant::class]);
        // API should resolve tenant as early as possible (JWT/token).
        $middleware->api(prepend: [\App\Http\Middleware\ResolveTenant::class]);

        $middleware->alias([
            'auth' => \App\Http\Middleware\EnsureAttendanceAuthenticated::class,
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'posh.legacy.block' => \App\Http\Middleware\RedirectLegacyPosh::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->withProviders([ // Add this block
        SanctumServiceProvider::class,
        JWTAuthServiceProvider::class,
    ])->create();