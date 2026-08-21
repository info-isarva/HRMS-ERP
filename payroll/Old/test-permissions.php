// Quick test for Permission model debugging
$permissions = \App\Models\Permission::all();
echo "Total permissions: " . $permissions->count() . "\n\n";

foreach ($permissions as $permission) {
    echo "Permission: " . $permission->name . "\n";
    echo "Route name: " . ($permission->route_name ?? 'null') . "\n";
    echo "Route names (raw): " . ($permission->getAttributes()['route_names'] ?? 'null') . "\n";
    echo "Route names (processed): " . json_encode($permission->route_names) . "\n";
    echo "---\n";
}

echo "\nAll used routes:\n";
$usedRoutes = \App\Models\Permission::getAllUsedRouteNames();
foreach ($usedRoutes as $route) {
    echo "- " . $route . "\n";
}

echo "\nEmployee routes that are used:\n";
foreach ($usedRoutes as $route) {
    if (str_contains($route, 'employees')) {
        echo "- " . $route . "\n";
    }
}