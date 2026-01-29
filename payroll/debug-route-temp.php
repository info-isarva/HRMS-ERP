<?php
// Add this route temporarily to debug permissions

Route::get('/debug-permissions-data', function() {
    try {
        // Get all permissions
        $permissions = \App\Models\Permission::all();
        
        $debug = [
            'total_permissions' => $permissions->count(),
            'permissions_detail' => [],
            'used_routes' => [],
            'employees_routes_in_system' => []
        ];
        
        // Get all routes related to employees
        $allRoutes = collect(\Route::getRoutes())
            ->map(function ($route) {
                return $route->getName();
            })
            ->filter()
            ->filter(function($route) {
                return str_contains($route, 'employees');
            })
            ->sort()
            ->values();
        
        $debug['employees_routes_in_system'] = $allRoutes->toArray();
        
        // Analyze each permission
        foreach ($permissions as $permission) {
            $routes = [];
            
            // Get route_name
            if ($permission->route_name) {
                $routes[] = $permission->route_name;
            }
            
            // Get route_names (JSON field)
            if ($permission->route_names && is_array($permission->route_names)) {
                $routes = array_merge($routes, $permission->route_names);
            }
            
            $debug['permissions_detail'][] = [
                'id' => $permission->id,
                'name' => $permission->name,
                'display_name' => $permission->display_name,
                'route_name' => $permission->route_name,
                'route_names_raw' => $permission->getAttributes()['route_names'] ?? null,
                'route_names_processed' => $permission->route_names,
                'all_routes_for_permission' => $routes
            ];
            
            // Add to used routes
            $debug['used_routes'] = array_merge($debug['used_routes'], $routes);
        }
        
        $debug['used_routes'] = array_unique($debug['used_routes']);
        
        // Get what Permission::getAllUsedRouteNames() returns
        $debug['getAllUsedRouteNames_result'] = \App\Models\Permission::getAllUsedRouteNames()->toArray();
        
        // Check available routes
        $allSystemRoutes = collect(\Route::getRoutes())
            ->map(function ($route) {
                return $route->getName();
            })
            ->filter()
            ->sort()
            ->values();
            
        $usedRoutes = \App\Models\Permission::getAllUsedRouteNames();
        $availableRoutes = $allSystemRoutes->diff($usedRoutes)->values();
        
        $debug['available_routes_count'] = $availableRoutes->count();
        $debug['available_employees_routes'] = $availableRoutes->filter(function($route) {
            return str_contains($route, 'employees');
        })->values()->toArray();
        
        return response()->json($debug, JSON_PRETTY_PRINT);
        
    } catch (Exception $e) {
        return response()->json(['error' => $e->getMessage()]);
    }
});