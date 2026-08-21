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

        // Admin users have access to admin/management routes even with specific permissions
        if ($user->hasAdminAccess()) {
            $adminRoutes = [
                'permissions.manage', 'permissions.save', 'permissions.update', 'permissions.delete', 'permissions.get',
                'public-holidays.index', 'public-holidays.create', 'public-holidays.store', 'public-holidays.show', 'public-holidays.edit', 'public-holidays.update', 'public-holidays.destroy', 'public-holidays.toggle-status',
                'leave-types.index', 'leave-types.create', 'leave-types.store', 'leave-types.show', 'leave-types.edit', 'leave-types.update', 'leave-types.destroy', 'leave-types.sync-departments',
                'holiday-department-configs.index', 'holiday-department-configs.create', 'holiday-department-configs.store', 'holiday-department-configs.show', 'holiday-department-configs.edit', 'holiday-department-configs.update', 'holiday-department-configs.destroy', 'holiday-department-configs.sync-departments', 'holiday-department-configs.sync-used-holidays', 'holiday-department-configs.cleanup-orphaned'
            ];

            if (in_array($currentRoute, $adminRoutes)) {
                return $next($request);
            }
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

        // If still no permission found, deny access for non-admin users (security measure)
        // Only admin/super_admin users can access routes without explicit permissions
        if (!$permission) {
            // Allow access to basic user routes for all authenticated users
            $basicRoutes = [
                'dashboard', 'home', 'profile.show', 'profile.update-password',
                'leaves.index', 'leaves.create', 'leaves.store', 'leaves.show', 'leaves.edit', 'leaves.update', 'leaves.cancel', 'leaves.calculate',
                'public-holiday-applications.index', 'public-holiday-applications.store', 'public-holiday-applications.cancel'
            ];

            if (in_array($currentRoute, $basicRoutes)) {
                return $next($request);
            }

            // For other routes without permissions, deny access unless user has unrestricted access
            if (!$user->hasUnrestrictedAccess()) {
                abort(403, 'Access denied. This route requires specific permissions.');
            }

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