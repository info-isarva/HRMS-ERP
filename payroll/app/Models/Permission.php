<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'route_name',
        'route_names',
        'module',
        'action',
        'display_name',
        'description',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'route_names' => 'array',
    ];

    /**
     * Get the users that have this permission.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_permissions')
                    ->withPivot('granted')
                    ->withTimestamps();
    }

    /**
     * Get the roles that have this permission.
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_permissions_pivot')
                    ->withTimestamps();
    }

    /**
     * Scope a query to only include active permissions.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to filter by module.
     */
    public function scopeByModule($query, $module)
    {
        return $query->where('module', $module);
    }

    /**
     * Get permissions grouped by module.
     */
    public static function getGroupedByModule()
    {
        return static::active()
                    ->orderBy('module')
                    ->orderBy('action')
                    ->get()
                    ->groupBy('module');
    }

    /**
     * Get all route names from all permissions.
     */
    public static function getAllUsedRouteNames()
    {
        $allRoutes = collect();
        
        static::all()->each(function ($permission) use (&$allRoutes) {
            // Add single route_name if exists
            if ($permission->route_name) {
                $allRoutes->push($permission->route_name);
            }
            
            // Add multiple route_names if exists  
            if ($permission->route_names && is_array($permission->route_names)) {
                foreach ($permission->route_names as $routeName) {
                    $allRoutes->push($routeName);
                }
            }
        });
        
        return $allRoutes->unique()->values();
    }

    /**
     * Check if a route name is already used in any permission.
     */
    public static function isRouteNameUsed($routeName)
    {
        return static::getAllUsedRouteNames()->contains($routeName);
    }

    /**
     * Get the route names for this permission.
     */
    public function getRouteNamesAttribute($value)
    {
        // If route_names is set, return it
        if ($this->attributes['route_names']) {
            return json_decode($this->attributes['route_names'], true) ?: [];
        }
        
        // Otherwise, return route_name as array for backward compatibility
        if ($this->attributes['route_name']) {
            return [$this->attributes['route_name']];
        }
        
        return [];
    }

    /**
     * Check if this permission includes a specific route name.
     */
    public function hasRouteName($routeName)
    {
        $routeNames = $this->route_names ?: [];
        return in_array($routeName, $routeNames) || $this->route_name === $routeName;
    }
}
