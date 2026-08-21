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
        // Resolve tenant after session/cookies are initialized.
        $middleware->web(append: [\App\Http\Middleware\ResolveTenant::class]);
        // API should resolve tenant as early as possible (JWT/token).
        $middleware->api(prepend: [\App\Http\Middleware\ResolveTenant::class]);

        $middleware->group('api', [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            \Illuminate\Routing\Middleware\ThrottleRequests::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);
        
        // Exclude notification API routes from CSRF verification
        $middleware->validateCsrfTokens(except: [
            'api/notifications/*',
        ]);
        
        // Register custom permission middleware for use in routes
        $middleware->alias([
            'auth' => \App\Http\Middleware\EnsurePayrollAuthenticated::class,
            'permission' => \App\Http\Middleware\CheckPermission::class,
            'check.route.permission' => \App\Http\Middleware\CheckRoutePermission::class,
            'posh.legacy.block' => \App\Http\Middleware\RedirectLegacyPosh::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
