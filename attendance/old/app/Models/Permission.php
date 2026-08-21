<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Permission extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'module',
        'action',
        'display_name',
        'description',
        'is_active',
        'route_names',
        'route_name',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        // Remove route_names cast to handle JSON manually
    ];

        /**
     * Get all route names that are currently assigned to any permission.
     *
     * @return \Illuminate\Support\Collection
     */
    public static function getAllUsedRouteNames()
    {
        $allRoutes = collect();
        
        \Log::debug('Collecting used routes from all permissions');
        
        // DIRECT DATABASE QUERY - This bypasses model attribute casting
        $rawPermissions = DB::table('permissions')->select('id', 'name', 'route_name', 'route_names')->get();
        
        \Log::debug("Found " . count($rawPermissions) . " permissions in DB query");
        
        foreach ($rawPermissions as $permission) {
            \Log::debug("Checking permission #{$permission->id}: {$permission->name}");
            
            // Add single route_name if it exists
            if (!empty($permission->route_name)) {
                \Log::debug(" - Adding route_name: {$permission->route_name}");
                $allRoutes->push($permission->route_name);
            }
            
            // Handle route_names which should be JSON string in the database
            if (!empty($permission->route_names)) {
                \Log::debug(" - Raw route_names from DB: " . print_r($permission->route_names, true));
                
                // Try to decode JSON string
                try {
                    $decoded = json_decode($permission->route_names, true);
                    if (is_array($decoded)) {
                        foreach ($decoded as $route) {
                            if (!empty($route)) {
                                \Log::debug(" - Adding route from route_names JSON: {$route}");
                                $allRoutes->push($route);
                            }
                        }
                    } else if (is_string($permission->route_names)) {
                        // If not valid JSON but a string, add as single route
                        \Log::debug(" - Adding route_names as single string: {$permission->route_names}");
                        $allRoutes->push($permission->route_names);
                    }
                } catch (\Exception $e) {
                    \Log::error("Error decoding route_names for permission #{$permission->id}: " . $e->getMessage());
                    // Still try to use it as a string
                    if (is_string($permission->route_names)) {
                        \Log::debug(" - Adding route_names as fallback string: {$permission->route_names}");
                        $allRoutes->push($permission->route_names);
                    }
                }
            }
        }
        
        $uniqueRoutes = $allRoutes->unique()->values();
        \Log::debug('All used routes: ' . implode(', ', $uniqueRoutes->toArray()));
        
        return $uniqueRoutes;
    }

    /**
     * Check if a route name is already used by any permission.
     */
    public static function isRouteNameUsed($routeName)
    {
        return static::getAllUsedRouteNames()->contains($routeName);
    }

    /**
     * Ensure route_names attribute always returns an array (backwards compatibility with route_name)
     */
    public function getRouteNamesAttribute($value)
    {
        \Log::debug("Getting route_names attribute for permission #{$this->id}, raw value: " . print_r($value, true));
        
        $result = [];
        
        // First check if we have the raw attribute value
        if (array_key_exists('route_names', $this->attributes)) {
            $rawValue = $this->attributes['route_names'];
            \Log::debug("Raw route_names from attributes: " . (is_string($rawValue) ? $rawValue : print_r($rawValue, true)));
            
            // If it's a string, try to decode JSON
            if (is_string($rawValue) && !empty($rawValue)) {
                try {
                    $decoded = json_decode($rawValue, true);
                    if (is_array($decoded)) {
                        \Log::debug("Successfully decoded JSON route_names: " . print_r($decoded, true));
                        $result = $decoded;
                    } else {
                        // If it's not valid JSON, treat as a single route
                        \Log::debug("Not valid JSON, treating as single route: " . $rawValue);
                        $result = [$rawValue];
                    }
                } catch (\Exception $e) {
                    \Log::error("Error decoding route_names JSON: " . $e->getMessage());
                    // If JSON decode fails, treat as a single string
                    $result = [$rawValue];
                }
            } 
            // If it's already an array, use it directly
            else if (is_array($rawValue)) {
                \Log::debug("route_names is already an array: " . print_r($rawValue, true));
                $result = $rawValue;
            }
        }
        
        // If we still have no routes, try using the provided value (from cast)
        if (empty($result) && !empty($value)) {
            if (is_array($value)) {
                \Log::debug("Using provided array value: " . print_r($value, true));
                $result = $value;
            } else if (is_string($value)) {
                try {
                    $decoded = json_decode($value, true);
                    if (is_array($decoded)) {
                        \Log::debug("Decoded provided value as JSON: " . print_r($decoded, true));
                        $result = $decoded;
                    } else {
                        $result = [$value];
                    }
                } catch (\Exception $e) {
                    \Log::error("Error decoding provided value: " . $e->getMessage());
                    $result = [$value];
                }
            }
        }
        
        // If still empty, fall back to route_name
        if (empty($result) && array_key_exists('route_name', $this->attributes) && !empty($this->attributes['route_name'])) {
            $routeName = $this->attributes['route_name'];
            \Log::debug("Falling back to route_name: " . $routeName);
            $result = [$routeName];
        }
        
        \Log::debug("Final route_names array for permission #{$this->id}: " . print_r($result, true));
        return $result;
    }

    /**
     * Check whether this permission includes the given route name.
     */
    public function hasRouteName($routeName)
    {
        $routeNames = $this->route_names ?: [];
        return in_array($routeName, $routeNames) || ($this->route_name === $routeName);
    }
}
