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
        // Resolve tenant after session/cookies are initialized.
        $middleware->web(append: [
            \App\Http\Middleware\ResolveTenant::class,
        ]);
        // Consent checks require authenticated user/session.
        $middleware->web(append: [
            \App\Http\Middleware\CheckAdminConsent::class,
        ]);
        $middleware->alias([
            'check.user.access' => \App\Http\Middleware\CheckUserAccess::class,
            'workspace.auth' => \App\Http\Middleware\EnsureWorkspaceAuthenticated::class,
            'platform.admin' => \App\Http\Middleware\EnsurePlatformAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
