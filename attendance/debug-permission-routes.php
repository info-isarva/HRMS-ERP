<?php

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/bootstrap/app.php';

use App\Models\Permission;
use Illuminate\Support\Facades\Log;

// Find the most recent permission
$permission = Permission::orderBy('id', 'desc')->first();

if ($permission) {
    echo "Debugging Permission #{$permission->id}: {$permission->name}\n\n";
    
    echo "Raw route_names attribute: " . $permission->attributes['route_names'] . "\n";
    echo "Route names after accessor: " . print_r($permission->route_names, true) . "\n";
    
    // Test the getAllUsedRouteNames method
    $usedRoutes = Permission::getAllUsedRouteNames();
    echo "All used routes: " . implode(', ', $usedRoutes->toArray()) . "\n";
    
    // Check specific routes
    $leaveTypeCreate = 'leave-type.create';
    $leaveTypeDestroy = 'leave-type.destroy';
    
    echo "\nChecking specific routes:\n";
    echo "$leaveTypeCreate is " . ($usedRoutes->contains($leaveTypeCreate) ? "used" : "NOT used") . "\n";
    echo "$leaveTypeDestroy is " . ($usedRoutes->contains($leaveTypeDestroy) ? "used" : "NOT used") . "\n";
} else {
    echo "No permissions found\n";
}

echo "\n\nAll Permissions:\n";
$allPermissions = Permission::all();
foreach ($allPermissions as $perm) {
    echo "Permission #{$perm->id}: {$perm->name}\n";
    echo "  route_name: " . ($perm->route_name ?: 'null') . "\n";
    echo "  route_names: " . print_r($perm->route_names, true) . "\n\n";
}