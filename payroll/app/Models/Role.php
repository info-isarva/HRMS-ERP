<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'role_name',
        'short_name',
        'description',
        'status'
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    /**
     * Get the permissions for this role.
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_permissions_pivot')
                    ->withTimestamps();
    }

    /**
     * Get the users that have this role.
     */
    public function users()
    {
        return $this->hasMany(User::class, 'role_name', 'role_name');
    }

    /**
     * Scope a query to only include active roles.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    /**
     * Check if role has a specific permission.
     */
    public function hasPermission($permission)
    {
        if (is_string($permission)) {
            return $this->permissions()->where('permissions.name', $permission)->exists();
        }
        
        return $this->permissions()->where('permissions.id', $permission->id)->exists();
    }

    /**
     * Assign a permission to this role.
     */
    public function givePermission($permission)
    {
        if (is_string($permission)) {
            $permission = Permission::where('name', $permission)->first();
        }
        
        if ($permission && !$this->hasPermission($permission)) {
            $this->permissions()->attach($permission->id);
        }
        
        return $this;
    }
}
