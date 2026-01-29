<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Permission;

// Find user ISIT100
$user = User::where('employee_id', 'ISIT100')->first();

if (!$user) {
    echo "User ISIT100 not found!\n";
    exit;
}

echo "User Details:\n";
echo "-------------\n";
echo "Name: " . $user->name . "\n";
echo "Employee ID: " . $user->employee_id . "\n";
echo "Role Name: " . $user->role_name . "\n";
echo "Permissions JSON: " . json_encode($user->permissions_json) . "\n\n";

// Check specific permission
$permissionName = 'userManagement';
$permission = Permission::where('name', $permissionName)->first();

if ($permission) {
    echo "Permission '{$permissionName}' exists:\n";
    echo "  ID: " . $permission->id . "\n";
    echo "  Name: " . $permission->name . "\n";
    echo "  Display Name: " . ($permission->display_name ?? 'N/A') . "\n\n";
    
    echo "User has this permission? " . ($user->hasPermission($permissionName) ? "YES" : "NO") . "\n\n";
} else {
    echo "Permission '{$permissionName}' NOT FOUND in database!\n\n";
}

// Check if user has unrestricted access
echo "Has unrestricted access? " . ($user->hasUnrestrictedAccess() ? "YES" : "NO") . "\n";

// If permissions_json is set, show all permissions user has
if (!empty($user->permissions_json) && is_array($user->permissions_json)) {
    echo "\nUser's Permission IDs: " . implode(', ', $user->permissions_json) . "\n";
    
    echo "\nUser's Permissions:\n";
    foreach ($user->permissions_json as $permId) {
        $perm = Permission::find($permId);
        if ($perm) {
            echo "  - [{$perm->id}] {$perm->name} ({$perm->display_name})\n";
        }
    }
}

// Check role permissions
if ($user->roleObject) {
    echo "\nRole: " . $user->roleObject->role_name . "\n";
    if (!empty($user->roleObject->permissions) && is_array($user->roleObject->permissions)) {
        echo "Role Permission IDs: " . implode(', ', $user->roleObject->permissions) . "\n";
        
        echo "\nRole's Permissions:\n";
        foreach ($user->roleObject->permissions as $permId) {
            $perm = Permission::find($permId);
            if ($perm) {
                echo "  - [{$perm->id}] {$perm->name} ({$perm->display_name})\n";
            }
        }
    }
} else {
    echo "\nNo role object found\n";
}

echo "\n--- Testing Permission Check ---\n";
echo "Testing hasPermission('userManagement'): " . ($user->hasPermission('userManagement') ? "TRUE" : "FALSE") . "\n";

