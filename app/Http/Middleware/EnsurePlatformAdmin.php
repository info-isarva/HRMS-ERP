<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Central\DemoTenantController;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePlatformAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! DemoTenantController::canAccess($user->email)) {
            abort(403, 'Demo Tenant Manager is only available to ISARVA internal staff (ISARVADEV login).');
        }

        return $next($request);
    }
}
