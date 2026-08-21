<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Blocks deprecated Attendance POSH portal when legacy mode is off (Phase 0 default).
 */
class RedirectLegacyPosh
{
    public function handle(Request $request, Closure $next): Response
    {
        if (config('posh.legacy_enabled')) {
            return $next($request);
        }

        $target = config('posh.workspace_url') . config('posh.coming_soon_path');

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => 'Legacy POSH portal in Attendance is deprecated. Use ' . config('posh.product_name') . ' from the HRMS workspace.',
                'redirect' => $target,
                'phase' => 0,
            ], 410);
        }

        return redirect()->away($target);
    }
}
