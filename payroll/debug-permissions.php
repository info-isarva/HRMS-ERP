<?php

// Debug script to check permission data
require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\Route;

// Simple debug to check routes and permissions
try {
    // Get all routes
    $allRoutes = collect(\Route::getRoutes())
        ->map(function ($route) {
            return $route->getName();
        })
        ->filter()
        ->sort()
        ->values();

    echo "<h3>Total Routes: " . $allRoutes->count() . "</h3>";
    echo "<h4>Sample Routes:</h4>";
    foreach ($allRoutes->take(20) as $route) {
        echo "- " . $route . "<br>";
    }

    // Check permissions
    $permissions = \App\Models\Permission::all();
    echo "<h3>Total Permissions: " . $permissions->count() . "</h3>";
    
    foreach ($permissions as $permission) {
        echo "<h4>Permission: " . $permission->name . "</h4>";
        echo "Route Name: " . $permission->route_name . "<br>";
        echo "Route Names: " . json_encode($permission->route_names) . "<br><br>";
    }

    // Check used routes
    $usedRoutes = \App\Models\Permission::getAllUsedRouteNames();
    echo "<h3>Used Routes: " . $usedRoutes->count() . "</h3>";
    foreach ($usedRoutes as $route) {
        echo "- " . $route . "<br>";
    }

    // Check available routes
    $availableRoutes = $allRoutes->diff($usedRoutes)->values();
    echo "<h3>Available Routes: " . $availableRoutes->count() . "</h3>";
    foreach ($availableRoutes->take(10) as $route) {
        echo "- " . $route . "<br>";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>