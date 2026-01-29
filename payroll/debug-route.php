Route::get('debug/permissions', function() {
    try {
        // Get all routes
        $allRoutes = collect(\Route::getRoutes())
            ->map(function ($route) {
                return $route->getName();
            })
            ->filter()
            ->sort()
            ->values();

        // Get permissions
        $permissions = \App\Models\Permission::all();
        
        // Get used routes
        $usedRoutes = \App\Models\Permission::getAllUsedRouteNames();
        
        // Get available routes
        $availableRoutes = $allRoutes->diff($usedRoutes)->values();

        return response()->json([
            'total_routes' => $allRoutes->count(),
            'total_permissions' => $permissions->count(),
            'used_routes_count' => $usedRoutes->count(),
            'available_routes_count' => $availableRoutes->count(),
            'sample_routes' => $allRoutes->take(10),
            'used_routes' => $usedRoutes,
            'available_routes' => $availableRoutes->take(10),
            'permissions' => $permissions->map(function($p) {
                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'route_name' => $p->route_name,
                    'route_names' => $p->route_names
                ];
            })
        ]);

    } catch (Exception $e) {
        return response()->json(['error' => $e->getMessage()]);
    }
});