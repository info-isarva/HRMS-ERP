<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Permission;

class CheckRoutePermission
{
    /**
     * Handle an incoming request.
     * Check if user has permission for the current route.
     */
    public function handle(Request $request, Closure $next, $fallbackPermission = null)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $currentRoute = $request->route()->getName();

        // Super Admin and Admin with unrestricted access (null permissions_json) have full access
        if ($user->hasUnrestrictedAccess()) {
            return $next($request);
        }

        // Find permission by route name (check both single route_name and route_names array)
        $permission = Permission::where(function($query) use ($currentRoute) {
            $query->where('route_name', $currentRoute)
                  ->orWhereJsonContains('route_names', $currentRoute);
        })->first();
        
        // If no permission found for this route, check fallback permission
        if (!$permission && $fallbackPermission) {
            $permission = Permission::where('name', $fallbackPermission)->first();
        }

        // If still no permission found, allow access (route not protected)
        if (!$permission) {
            return $next($request);
        }

        // Check if user has this permission
        if (!$user->hasPermission($permission)) {
            // For AJAX requests, return JSON error
            if ($request->ajax()) {
                return response()->json([
                    'error' => 'Unauthorized access to route: ' . $currentRoute,
                    'permission_required' => $permission->name,
                    'route' => $currentRoute
                ], 403);
            }
            
            // For regular requests, show 403 error page
            abort(403, 'You are not authorized to access this route. Required permission: ' . $permission->display_name);
        }

        return $next($request);
    }
}