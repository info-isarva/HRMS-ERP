<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Permission;

class PermissionsController extends Controller
{
    public function index()
    {
        \Log::debug('Loading permissions index page');
        
        $permissions = Permission::orderBy('module')->orderBy('name')->get();
        \Log::debug('Found ' . $permissions->count() . ' permissions');
        
        // Debug all permissions and their routes
        $this->debugAllPermissions();

        $allRoutes = collect(\Route::getRoutes())->map(fn($r) => $r->getName())->filter()->sort()->values();
        \Log::debug('Total system routes: ' . $allRoutes->count());
        
        $usedRoutes = collect();
        if (is_callable([Permission::class, 'getAllUsedRouteNames'])) {
            $usedRoutes = Permission::getAllUsedRouteNames();
            if (!($usedRoutes instanceof \Illuminate\Support\Collection)) {
                $usedRoutes = collect($usedRoutes);
            }
            \Log::debug('Used routes count: ' . $usedRoutes->count());
            \Log::debug('Used routes: ' . implode(', ', $usedRoutes->toArray()));
        }

        $availableRoutes = $allRoutes->diff($usedRoutes)->values()->toArray();
        \Log::debug('Available routes count: ' . count($availableRoutes));
        
        // Test SQL queries for both problematic routes to confirm our fix
        $testRoutes = ["leave-type.create", "leave-type.destroy"];
        
        foreach ($testRoutes as $testRoute) {
            // Test direct JSON_CONTAINS query
            $testQuery = DB::table("permissions")
                ->whereRaw("JSON_CONTAINS(route_names, JSON_QUOTE(?))", [$testRoute])
                ->select("id", "name", "route_name", "route_names")
                ->get();
            
            \Log::debug("Testing JSON_CONTAINS for route '$testRoute':");
            \Log::debug("Found " . $testQuery->count() . " permissions with this route");
            foreach ($testQuery as $result) {
                \Log::debug("- Permission #{$result->id}: {$result->name}, route_names: {$result->route_names}");
            }
            
            // Also check route_name column
            $testQuery2 = DB::table("permissions")
                ->where("route_name", $testRoute)
                ->select("id", "name", "route_name")
                ->get();
            
            \Log::debug("Testing route_name column for route '$testRoute':");
            \Log::debug("Found " . $testQuery2->count() . " permissions with this route");
            
            // Final test with our combined query
            $testQuery3 = DB::table("permissions")
                ->where(function($q) use ($testRoute) {
                    $q->where("route_name", $testRoute)
                      ->orWhereRaw("JSON_CONTAINS(route_names, JSON_QUOTE(?))", [$testRoute]);
                })
                ->select("id", "name", "route_name", "route_names")
                ->get();
            
            \Log::debug("Testing combined query for route '$testRoute':");
            \Log::debug("Found " . $testQuery3->count() . " permissions with this route");
        }
        
        // Check if our collection now contains the routes properly
        if ($usedRoutes->contains('leave-type.create')) {
            \Log::debug('leave-type.create is correctly marked as used');
        } else {
            \Log::debug('leave-type.create is NOT marked as used');
        }
        
        if ($usedRoutes->contains('leave-type.destroy')) {
            \Log::debug('leave-type.destroy is correctly marked as used');
        } else {
            \Log::debug('leave-type.destroy is NOT marked as used');
        }

        $routeSuggestions = $this->getRouteSuggestions($allRoutes);

        return view('settings.permissions.index', compact('permissions', 'availableRoutes', 'routeSuggestions'));
    }

    public function save(Request $request)
    {
        try {
            $validated = $request->validate([
                'display_name' => 'required|string|max:255',
                'name' => 'required|string|max:255|unique:permissions,name',
                'module' => 'required|string|max:255',
                'action' => 'required|string|max:255',
                'description' => 'nullable|string',
                'route_names' => 'required|array|min:1',
                'route_names.*' => 'string'
            ]);

            // Check for duplicate route names
            foreach ($validated['route_names'] as $routeName) {
                // Use raw SQL with JSON_CONTAINS for more accurate matching of JSON array values
                $existing = DB::table('permissions')
                    ->where(function ($q) use ($routeName) {
                        $q->where('route_name', $routeName)
                          ->orWhereRaw("JSON_CONTAINS(route_names, JSON_QUOTE(?))", [$routeName]);
                    })
                    ->first();

                if ($existing) {
                    return response()->json([
                        'success' => false,
                        'errors' => [
                            'route_names' => ["Route '{$routeName}' is already assigned to permission '{$existing->name}'."]
                        ],
                        'message' => "Route '{$routeName}' is already assigned to permission '{$existing->name}'."
                    ], 422);
                }
            }

            // Log the routes that we're about to assign
            \Log::debug('Saving permission with routes: ' . implode(', ', $validated['route_names']));
            
            // Ensure route_names is properly JSON-encoded when stored
            $permission = Permission::create([
                'display_name' => $validated['display_name'],
                'name' => $validated['name'],
                'module' => $validated['module'],
                'action' => $validated['action'],
                'description' => $validated['description'],
                'route_names' => json_encode($validated['route_names']), // Explicitly JSON encode
                'route_name' => $validated['route_names'][0] ?? null, // For backwards compatibility
                'is_active' => true
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Permission created successfully',
                'permission' => $permission
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
                'message' => 'Validation failed: ' . implode(', ', collect($e->errors())->flatten()->all())
            ], 422);
        } catch (\Exception $e) {
            Log::error('Permission save error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to save permission: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request)
    {
        try {
            $validated = $request->validate([
                'id' => 'required|exists:permissions,id',
                'name' => 'required|string|max:255|unique:permissions,name,' . $request->id,
                'route_names' => 'required|array|min:1',
                'route_names.*' => 'required|string|max:255',
                'module' => 'required|string|max:255',
                'action' => 'required|string|max:255',
                'display_name' => 'required|string|max:255',
                'description' => 'nullable|string|max:255',
            ]);

            DB::beginTransaction();
            try {
                $permission = Permission::findOrFail($validated['id']);

                foreach ($validated['route_names'] as $routeName) {
                    // Use raw SQL with JSON_CONTAINS for more accurate matching of JSON array values
                    $existing = DB::table('permissions')
                        ->where('id', '!=', $validated['id'])
                        ->where(function ($q) use ($routeName) {
                            $q->where('route_name', $routeName)
                              ->orWhereRaw("JSON_CONTAINS(route_names, JSON_QUOTE(?))", [$routeName]);
                        })
                        ->first();

                    if ($existing) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false, 
                            'errors' => [
                                'route_names' => ["Route '{$routeName}' is already assigned to permission '{$existing->name}'"]
                            ],
                            'message' => "Route '{$routeName}' is already assigned to permission '{$existing->name}'."
                        ], 422);
                    }
                }

                // Log the routes that we're about to update
                \Log::debug('Updating permission #' . $validated['id'] . ' with routes: ' . implode(', ', $validated['route_names']));
                
                $permission->update([
                    'name' => $validated['name'],
                    'route_name' => $validated['route_names'][0] ?? null,
                    'route_names' => json_encode($validated['route_names']), // Explicitly JSON encode
                    'module' => $validated['module'],
                    'action' => $validated['action'],
                    'display_name' => $validated['display_name'],
                    'description' => $validated['description'],
                ]);

                DB::commit();
                return response()->json(['success' => true, 'message' => 'Permission updated successfully']);
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Permission update error: ' . $e->getMessage());
                return response()->json([
                    'success' => false, 
                    'message' => 'Failed to update permission: ' . $e->getMessage()
                ], 500);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
                'message' => 'Validation failed: ' . implode(', ', collect($e->errors())->flatten()->all())
            ], 422);
        }
    }

    public function get($id)
    {
        $permission = Permission::find($id);
        if (!$permission) return response()->json(['success' => false, 'message' => 'Permission not found']);

        $allRoutes = collect(\Route::getRoutes())->map(fn($r) => $r->getName())->filter()->sort()->values();

        // Process permission to ensure route_names is properly formatted
        if ($permission->route_names) {
            \Log::debug('Original route_names value: ' . print_r($permission->route_names, true));
            
            // If it's a JSON string, decode it
            if (is_string($permission->route_names)) {
                try {
                    $decoded = json_decode($permission->route_names, true);
                    if (is_array($decoded)) {
                        $permission->route_names = $decoded;
                        \Log::debug('Decoded route_names from JSON string: ' . print_r($permission->route_names, true));
                    } else {
                        // If it's not valid JSON but a simple string, treat it as a single route
                        $permission->route_names = [$permission->route_names];
                        \Log::debug('Treating route_names as a single route string: ' . print_r($permission->route_names, true));
                    }
                } catch (\Exception $e) {
                    \Log::error('Error decoding route_names: ' . $e->getMessage());
                    $permission->route_names = [];
                }
            } else if (!is_array($permission->route_names)) {
                \Log::warning('route_names is not a string or array, type: ' . gettype($permission->route_names));
                $permission->route_names = [];
            }
        } else {
            $permission->route_names = [];
            
            // Fallback to route_name if route_names is empty
            if ($permission->route_name) {
                $permission->route_names = [$permission->route_name];
                \Log::debug('Using route_name as fallback: ' . $permission->route_name);
            }
        }
        
        // Final check to ensure it's an array
        if (!is_array($permission->route_names)) {
            $permission->route_names = [];
            \Log::error('route_names is still not an array after processing');
        }

        // Use the improved getAllUsedRouteNames method from the Permission model
        // but exclude the current permission's routes
        $usedRoutes = collect();
        
        // Direct DB query to get all permissions except current one
        $otherPermissions = DB::table('permissions')
            ->where('id', '!=', $id)
            ->select('id', 'name', 'route_name', 'route_names')
            ->get();
        
        foreach ($otherPermissions as $perm) {
            // Add single route_name if it exists
            if (!empty($perm->route_name)) {
                $usedRoutes->push($perm->route_name);
            }
            
            // Process route_names JSON if it exists
            if (!empty($perm->route_names)) {
                try {
                    $routeNames = json_decode($perm->route_names, true);
                    if (is_array($routeNames)) {
                        foreach ($routeNames as $route) {
                            if (!empty($route)) {
                                $usedRoutes->push($route);
                            }
                        }
                    }
                } catch (\Exception $e) {
                    // If JSON decode fails, try using it as a single string
                    if (is_string($perm->route_names)) {
                        $usedRoutes->push($perm->route_names);
                    }
                }
            }
        }
        
        $usedRoutes = $usedRoutes->unique()->values();
        $availableRoutes = $allRoutes->diff($usedRoutes)->values()->toArray();

        // Add current permission's routes to available routes as they should be selectable
        if (!empty($permission->route_names)) {
            $availableRoutes = array_merge($availableRoutes, $permission->route_names);
            $availableRoutes = array_unique($availableRoutes);
            sort($availableRoutes);
        }

        return response()->json(['success' => true, 'permission' => $permission, 'availableRoutes' => $availableRoutes]);
    }

    public function delete(Request $request)
    {
        try {
            $validated = $request->validate(['id' => 'required|exists:permissions,id']);

            DB::beginTransaction();
            try {
                $permission = Permission::findOrFail($validated['id']);
                $permissionName = $permission->display_name;
                $permission->delete();
                DB::commit();
                return response()->json([
                    'success' => true, 
                    'message' => "Permission '{$permissionName}' deleted successfully"
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Permission delete error: ' . $e->getMessage());
                return response()->json([
                    'success' => false, 
                    'message' => 'Failed to delete permission: ' . $e->getMessage()
                ], 500);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
                'message' => 'Validation failed: ' . implode(', ', collect($e->errors())->flatten()->all())
            ], 422);
        }
    }

    /**
     * Debug helper to check all permissions and their route assignments
     */
    private function debugAllPermissions()
    {
        \Log::debug('--------- DEBUG ALL PERMISSIONS ---------');
        $permissions = Permission::all();
        
        foreach ($permissions as $permission) {
            \Log::debug("Permission #{$permission->id}: {$permission->name}");
            
            // Debug raw attributes to see what's actually stored
            $rawRouteNames = $permission->getAttributes()['route_names'] ?? null;
            $rawRouteName = $permission->getAttributes()['route_name'] ?? null;
            
            \Log::debug(" - Raw route_name: " . ($rawRouteName ? $rawRouteName : 'null'));
            \Log::debug(" - Raw route_names: " . ($rawRouteNames ? (is_string($rawRouteNames) ? $rawRouteNames : json_encode($rawRouteNames)) : 'null'));
            
            // Debug accessor values
            $accessorRouteNames = $permission->route_names;
            \Log::debug(" - Accessor route_names: " . json_encode($accessorRouteNames));
            
            // Debug if it contains specific routes
            if (is_array($accessorRouteNames)) {
                if (in_array('leave-type.create', $accessorRouteNames)) {
                    \Log::debug(" - Contains leave-type.create");
                }
                if (in_array('leave-type.destroy', $accessorRouteNames)) {
                    \Log::debug(" - Contains leave-type.destroy");
                }
            }
        }
        \Log::debug('--------- END DEBUG ---------');
    }

    private function getRouteSuggestions($allRoutes)
    {
        $suggestions = [];
        $grouped = [];

        foreach ($allRoutes as $route) {
            if (!str_contains($route, '.')) continue;
            $parts = explode('.', $route);
            $base = $parts[0];
            $action = $parts[1] ?? '';
            $grouped[$base][$action] = $route;
        }

        foreach ($grouped as $base => $routes) {
            if (isset($routes['create']) && isset($routes['store'])) {
                $suggestions[] = ['label' => ucfirst($base) . ' - Create', 'routes' => [$routes['create'], $routes['store']], 'description' => 'Routes for creating ' . $base];
            }
            if (isset($routes['edit']) && isset($routes['update'])) {
                $suggestions[] = ['label' => ucfirst($base) . ' - Edit/Update', 'routes' => [$routes['edit'], $routes['update']], 'description' => 'Routes for editing ' . $base];
            }
            if (isset($routes['index']) && isset($routes['show'])) {
                $suggestions[] = ['label' => ucfirst($base) . ' - View', 'routes' => [$routes['index'], $routes['show']], 'description' => 'Routes for viewing ' . $base];
            }
        }

        return $suggestions;
    }
}
