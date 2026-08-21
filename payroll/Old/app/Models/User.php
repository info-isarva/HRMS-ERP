<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Hash;
use DB;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $table = 'users'; // Specify the table name if it's not pluralized

    protected $fillable = [
        'name',
        'email',
        'user_id',
        'employee_id',
        'password',
        'status',
        'role_name',
        'last_login',
        'avatar',
        'join_date',
        'position',
        'department',
        'line_manager',
        'second_line_manager',
        'permissions_json',
        'phone_number',
        'enable_crm',
        'enable_self_portal',
        'enable_payroll',
        'location_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'permissions_json' => 'array',
        'enable_self_portal' => 'boolean',
        'enable_payroll' => 'boolean',
        'enable_crm' => 'boolean',
        'location_id' => 'integer',
    ];

    /** generate id */
    protected static function boot()
    {
        parent::boot();

        self::creating(function ($model) {
            // Only auto-generate user_id if it's not already set (for manual creation)
            if (empty($model->user_id)) {
                // Get the latest user with DRI prefix to maintain consistency
                $latestUser = self::where('user_id', 'REGEXP', '^DRI-[0-9]+$')->orderByRaw('CAST(REPLACE(user_id, "DRI-", "") AS UNSIGNED) DESC')->first();
                
                if ($latestUser && preg_match('/^DRI-0*(\d+)$/', $latestUser->user_id, $matches)) {
                    $nextID = intval($matches[1]) + 1;
                } else {
                    $nextID = 1;
                }
                $model->user_id = 'DRI-' . sprintf("%04d", $nextID);

                // Ensure the user_id is unique
                while (self::where('user_id', $model->user_id)->exists()) {
                    $nextID++;
                    $model->user_id = 'DRI-' . sprintf("%04d", $nextID);
                }
            }
        });
    }

    /** Insert New Users */
    public function saveNewuser(Request $request)
    {
        $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|string|email|max:255|unique:users',
            'role_name' => 'required|string|max:255',
            'password'  => 'required|string|min:8|confirmed',
        ]);
        
        try {
            $todayDate = Carbon::now()->toDayDateTimeString();
            $save             = new User;
            $save->name       = $request->name;
            $save->avatar     = $request->image;
            $save->email      = $request->email;
            $save->join_date  = $todayDate;
            $save->role_name  = $request->role_name;
            $save->status     = 'Active';
            $save->password   = Hash::make($request->password);
            $save->save();

            flash()->success('Account created successfully :)');
            return redirect('login');
        } catch (\Exception $e) {
            \Log::error($e);
            flash()->error('Failed to Create Account. Please try again.');
            return redirect()->back();
        }
    }

    /**
     * Create a user account from employee data
     *
     * @param \App\Models\EmployeeBasicDetail $employee
     * @return \App\Models\User
     */
    public static function createFromEmployee(EmployeeBasicDetail $employee)
    {
        // Ensure employee has an email address
        if (empty($employee->email)) {
            throw new \InvalidArgumentException('Employee must have an email address to create a user account.');
        }
        
        // Ensure enable_self_portal is enabled
        if (!$employee->enable_self_portal) {
            throw new \InvalidArgumentException('Employee must have Enable Self Portal checked to create a user account.');
        }
        
        // Generate a default password (employee_id without special chars + first 4 chars of name uppercase)
        $cleanEmployeeId = preg_replace('/[^a-zA-Z0-9]/', '', $employee->employee_id);
        $nameFirstFourChars = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $employee->name), 0, 4));
        $defaultPassword = $cleanEmployeeId . $nameFirstFourChars;
        
        // Get related data for the employee
        $designation = $employee->designationObj ? $employee->designationObj->position : null;
        $department = $employee->departmentObj ? $employee->departmentObj->department : null;
        $reportingManager = $employee->reportingManager ? $employee->reportingManager->name : null;
        
        // Create user with employee data
        $user = self::create([
            'name' => $employee->name,
            'email' => $employee->email, // Email is guaranteed to be non-empty due to validation above
            'user_id' => $employee->employee_id, // Use employee_id as user_id
            'employee_id' => $employee->id, // Store reference to employee record
            'password' => Hash::make($defaultPassword),
            'status' => $employee->enable_self_portal ? 'Active' : 'Inactive',
            'role_name' => $employee->roleObj ? $employee->roleObj->role_name : 'Employee',
            'join_date' => $employee->date_of_joining,
            'avatar' => $employee->profile_image ?? null,
            'position' => $employee->designation, // Store as ID, not name
            'department' => $employee->department, // Store as ID, not name
            'line_manager' => $reportingManager, // Set line manager as reporting manager
            'phone_number' => $employee->contact_number, // Add phone number
            'enable_crm' => $employee->enable_crm, // Add CRM permission
            'enable_self_portal' => $employee->enable_self_portal,
            'enable_payroll' => $employee->enable_payroll,
            'location_id' => $employee->location_id,
        ]);
        
        return $user;
    }

    /**
     * Update user account from employee data
     *
     * @param \App\Models\User $user
     * @param \App\Models\EmployeeBasicDetail $employee
     * @return \App\Models\User
     */
    public static function updateFromEmployee(User $user, EmployeeBasicDetail $employee)
    {
        // Get related data for the employee
        $designation = $employee->designationObj ? $employee->designationObj->position : null;
        $department = $employee->departmentObj ? $employee->departmentObj->department : null;
        $reportingManager = $employee->reportingManager ? $employee->reportingManager->name : null;
        $roleName = $employee->roleObj ? $employee->roleObj->role_name : 'Employee';
        
        // Map employee status to user status
        $statusName = null;
        if ($employee->status) {
            $employeeStatus = DB::table('employee_statuses')->where('id', $employee->status)->first();
            if ($employeeStatus) {
                $statusName = $employeeStatus->status_name;
            }
        }
        
        // Get role name from employee role
        if ($employee->role) {
            $roleRecord = DB::table('roles')->where('id', $employee->role)->first();
            $roleName = $roleRecord ? $roleRecord->role_name : 'Employee';
        }
        
        // Update user with employee data - ONLY update fields that should be controlled by employee
        $updateData = [
            'name' => $employee->name,
            'email' => $employee->email,
            'phone_number' => $employee->contact_number,
            'position' => $employee->designation, // Store designation ID
            'department' => $employee->department, // Store department ID
            'line_manager' => $reportingManager,
            'avatar' => $employee->profile_image ? basename($employee->profile_image) : $user->avatar,
            'enable_crm' => $employee->enable_crm, // Sync CRM permission
            'enable_self_portal' => $employee->enable_self_portal,
            'enable_payroll' => $employee->enable_payroll,
            'location_id' => $employee->location_id,
        ];
        
        // Only update status and role if employee has self portal enabled
        if ($employee->enable_self_portal) {
            if ($statusName) {
                $updateData['status'] = $statusName;
            }
            if ($roleName) {
                $updateData['role_name'] = $roleName;
            }
        }
        
        $user->update($updateData);
        
        return $user;
    }

    /**
     * Check if this user was created from an employee (employee-converted user)
     *
     * @return bool
     */
    public function isEmployeeUser()
    {
        return !empty($this->employee_id);
    }

    /**
     * Get the fields that should be read-only for employee-converted users
     *
     * @return array
     */
    public function getReadOnlyFields()
    {
        if ($this->isEmployeeUser()) {
            return [
                'name',
                'email', 
                'phone_number',
                'department',
                'position',
                'status',
                'role_name',
                'join_date',
                'avatar'
            ];
        }
        
        return [];
    }

    /**
     * Get the fields that can be edited for employee-converted users
     *
     * @return array
     */
    public function getEditableFields()
    {
        if ($this->isEmployeeUser()) {
            return [
                'password' // Only password can be changed for employee users
            ];
        }
        
        return [
            'name',
            'email',
            'phone_number', 
            'department',
            'position',
            'status',
            'role_name',
            'password',
            'avatar'
        ];
    }

    /**
     * Get the employee record associated with the user.
     */
    public function employee()
    {
        return $this->belongsTo(EmployeeBasicDetail::class, 'employee_id');
    }

    /**
     * Get the permissions directly assigned to this user.
     */
    /**
     * Get the role object for this user.
     */
    public function roleObject()
    {
        return $this->belongsTo(Role::class, 'role_name', 'role_name');
    }

    /**
     * Check if user is Admin or Super Admin with unrestricted access.
     */
    public function hasUnrestrictedAccess()
    {
        return ($this->role_name === 'Super Admin' || $this->role_name === 'Admin') && 
               (is_null($this->permissions_json) || empty($this->permissions_json) || !is_array($this->permissions_json));
    }

    /**
     * Check if user has a specific permission.
     * Admin/Super Admin logic: 
     * - If permissions_json is NULL or empty = full access to everything
     * - If permissions_json has specific permissions = restricted to only those permissions
     */
    public function hasPermission($permission)
    {
        // If user has unrestricted access (Admin/Super Admin with null permissions_json), allow everything
        if ($this->hasUnrestrictedAccess()) {
            return true;
        }

        if (is_string($permission)) {
            $permissionName = $permission;
            $permission = Permission::where('name', $permission)->first();
        } elseif (is_numeric($permission)) {
            // Handle permission ID
            $permission = Permission::find($permission);
            $permissionName = $permission ? $permission->name : 'unknown';
        } else {
            $permissionName = $permission->name;
        }

        // If permission doesn't exist, deny access for non-unrestricted users
        if (!$permission) {
            return false;
        }

        // For Admin/Super Admin with specific permissions set, check those permissions
        if ($this->role_name === 'Super Admin' || $this->role_name === 'Admin') {
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

        // Check role-based permissions
        $role = $this->roleObject;
        if ($role) {
            return $role->hasPermission($permission);
        }

        return false;
    }

    /**
     * Give a permission to this user.
     */
    public function givePermission($permission, $granted = true)
    {
        if (is_string($permission)) {
            $permission = Permission::where('name', $permission)->first();
        } elseif (is_numeric($permission)) {
            // Handle permission ID
            $permission = Permission::find($permission);
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
     * Check if user has a specific permission using JSON storage (faster)
     */
    public function hasPermissionFast($permissionId)
    {
        // Super Admin and Admin logic
        if ($this->role_name === 'Super Admin' || $this->role_name === 'Admin') {
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
     * Get all permissions for this user (direct + role-based).
     */
    public function getAllPermissions()
    {
        // Get direct permissions from JSON
        $directPermissionIds = $this->permissions_json ?? [];
        $directPermissions = Permission::whereIn('id', $directPermissionIds)->get();
        
        $rolePermissions = collect();
        if ($this->roleObject) {
            $rolePermissions = $this->roleObject->permissions;
        }

        return $directPermissions->merge($rolePermissions)->unique('id');
    }

    /**
     * Check if user can perform an action on a module.
     * Super Admin can perform all actions.
     */
    public function can($action, $module = null)
    {
        // Super Admin can do everything
        if ($this->role_name === 'Super Admin' || $this->role_name === 'Admin') {
            return true;
        }

        if ($module) {
            $permission = "{$module}.{$action}";
        } else {
            $permission = $action;
        }

        return $this->hasPermission($permission);
    }

    /**
     * Get the user's consents.
     */
    public function consents()
    {
        return $this->hasMany(UserConsent::class);
    }
}
