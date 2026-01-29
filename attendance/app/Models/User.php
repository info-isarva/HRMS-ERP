<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, LogsActivity, Notifiable;

    protected $fillable = [
        'employee_id',
        'payroll_id',
        'payroll_user_id',      // Add this field
        'name',
        'email',
        'password',
        'google_id',
        'role',
        'designation',
        'financial_year',
        'department_id',
        'date_of_joining',
        'date_of_resignation',
        'reporting_manager_id',
        'permissions_json',
    ];

    protected $casts = [
        'password' => 'hashed',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'date_of_joining' => 'date',
        'date_of_resignation' => 'date',
        'permissions_json' => 'array',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function leaveApplications()
    {
        return $this->hasMany(LeaveApplication::class);
    }

    public function publicHolidayApplications()
    {
        return $this->hasMany(PublicHolidayApplication::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
    
    public function reportingManager()
    {
        // Look up the manager by matching our reporting_manager_id 
        // with another user's payroll_id
        return $this->belongsTo(User::class, 'reporting_manager_id', 'payroll_id');
    }
    
    public function reportees()
    {
        // Find all users whose reporting_manager_id matches this user's payroll_id
        return $this->hasMany(User::class, 'reporting_manager_id', 'payroll_id');
    }

    public function isSuperAdmin()
    {
        return $this->role === 'super_admin';
    }

    public function getIsSuperAdminAttribute()
    {
        return $this->isSuperAdmin();
    }

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isStaff()
    {
        return $this->role === 'staff';
    }

    /**
     * Check if user is Admin or Super Admin with unrestricted access.
     * Admins always have access to admin routes, even with specific permissions set.
     */
    public function hasUnrestrictedAccess()
    {
        return $this->role === 'super_admin' ||
               ($this->role === 'admin' && (is_null($this->permissions_json) || empty($this->permissions_json) || !is_array($this->permissions_json)));
    }

    /**
     * Check if user has admin-level access (can access admin routes).
     * This includes super admins and all admins.
     */
    public function hasAdminAccess()
    {
        return $this->role === 'super_admin' || $this->role === 'admin';
    }

    /**
     * Check if user has a specific permission.
     */
    public function hasPermission($permission)
    {
        // If user has unrestricted access (Admin/Super Admin with null permissions_json), allow everything
        if ($this->hasUnrestrictedAccess()) {
            return true;
        }

        if (is_string($permission)) {
            $permissionName = $permission;
            $permission = \App\Models\Permission::where('name', $permission)->first();
        } elseif (is_numeric($permission)) {
            // Handle permission ID
            $permission = \App\Models\Permission::find($permission);
            $permissionName = $permission ? $permission->name : 'unknown';
        } else {
            $permissionName = $permission->name;
        }

        // If permission doesn't exist, deny access for non-unrestricted users
        if (!$permission) {
            return false;
        }

        // For Admin/Super Admin with specific permissions set, check those permissions
        if ($this->role === 'super_admin' || $this->role === 'admin') {
            // If specific permissions are set, restrict to only those permissions
            if (!empty($this->permissions_json) && is_array($this->permissions_json)) {
                return in_array((int)$permission->id, array_map('intval', $this->permissions_json));
            }
        }

        // Check JSON permissions for regular users
        if (!empty($this->permissions_json) && is_array($this->permissions_json)) {
            $hasDirectPermission = in_array((int)$permission->id, array_map('intval', $this->permissions_json));
            if ($hasDirectPermission) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user has a specific permission using JSON storage (faster)
     */
    public function hasPermissionFast($permissionId)
    {
        // Super Admin and Admin logic
        if ($this->role === 'super_admin' || $this->role === 'admin') {
            // If no specific permissions are set, allow everything
            if (empty($this->permissions_json) || !is_array($this->permissions_json)) {
                return true;
            }
            // If specific permissions are set, restrict to only those permissions
            return in_array((int)$permissionId, array_map('intval', $this->permissions_json));
        }

        // Check JSON permissions for regular users
        if (!empty($this->permissions_json) && is_array($this->permissions_json)) {
            return in_array((int)$permissionId, array_map('intval', $this->permissions_json));
        }

        // No permissions found
        return false;
    }

    /**
     * Get user permissions from JSON (fast method)
     */
    public function getPermissionIds()
    {
        return $this->permissions_json ?? [];
    }

    /**
     * Get all permissions for this user.
     */
    public function getAllPermissions()
    {
        // Get direct permissions from JSON
        $directPermissionIds = $this->permissions_json ?? [];
        $directPermissions = \App\Models\Permission::whereIn('id', $directPermissionIds)->get();

        return $directPermissions;
    }

    /**
     * Give a permission to this user.
     */
    public function givePermission($permission, $granted = true)
    {
        if (is_string($permission)) {
            $permission = \App\Models\Permission::where('name', $permission)->first();
        } elseif (is_numeric($permission)) {
            // Handle permission ID
            $permission = \App\Models\Permission::find($permission);
        }

        if ($permission && $granted) {
            $currentPermissions = $this->permissions_json ?? [];
            if (!in_array((int)$permission->id, array_map('intval', $currentPermissions))) {
                $currentPermissions[] = (int)$permission->id;
                $this->permissions_json = $currentPermissions;
                $this->save();
            }
        }

        return $this;
    }

    /**
     * Assign multiple permissions to the user, replacing all existing permissions
     */
    public function givePermissions($permissionIds = [])
    {
        if (empty($permissionIds)) {
            // If no permissions provided, remove all existing permissions
            $this->permissions_json = null;
            $this->save();
            return $this;
        }

        // Store permissions as JSON array
        $this->permissions_json = array_map('intval', $permissionIds);
        $this->save();

        return $this;
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     */
    public function getJWTCustomClaims()
    {
        return [
            'employee_id' => $this->employee_id,
            'payroll_id' => $this->payroll_id,
            'role' => $this->role,
            'department_id' => $this->department_id,
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email', 'role', 'department_id'])
            ->logOnlyDirty()
            ->dontLogIfAttributesChangedOnly(['updated_at', 'remember_token'])
            ->setDescriptionForEvent(fn(string $eventName) => "User {$eventName}")
            ->useLogName('user');
    }
}