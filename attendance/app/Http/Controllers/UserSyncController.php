<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Department;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class UserSyncController extends Controller
{
    /**
     * Verify API Token
     */
    public function verifyToken(Request $request)
    {
        if (!$this->validateApiToken($request)) {
            return response()->json(['status' => 'error', 'message' => 'Invalid API token'], 401);
        }
        
        return response()->json(['status' => 'success', 'message' => 'Token is valid']);
    }

    /**
     * Sync User from Payroll - Create/Update
     */
    public function syncUserFromPayroll(Request $request)
    {
        if (!$this->validateApiToken($request)) {
            return response()->json(['status' => 'error', 'message' => 'Invalid API token'], 401);
        }

        return $this->handleUserSync($request, 'create');
    }

    /**
     * Update User from Payroll
     */
    public function updateUserFromPayroll(Request $request, $user_id)
    {
        if (!$this->validateApiToken($request)) {
            return response()->json(['status' => 'error', 'message' => 'Invalid API token'], 401);
        }

        return $this->handleUserSync($request, 'update', $user_id);
    }

    /**
     * Delete User from Payroll
     */
    public function deleteUserFromPayroll(Request $request, $user_id)
    {
        if (!$this->validateApiToken($request)) {
            return response()->json(['status' => 'error', 'message' => 'Invalid API token'], 401);
        }

        return $this->handleUserSync($request, 'delete', $user_id);
    }

    /**
     * Sync Password from Payroll
     */
    public function syncPasswordFromPayroll(Request $request, $user_id)
    {
        if (!$this->validateApiToken($request)) {
            return response()->json(['status' => 'error', 'message' => 'Invalid API token'], 401);
        }

        try {
            $validator = Validator::make($request->all(), [
                'password' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Find user by payroll_id first (matches payroll employee_id)
            $user = User::where('payroll_id', $user_id)->first();
            
            // If not found by payroll_id, try by payroll_user_id
            if (!$user) {
                $user = User::where('payroll_user_id', $user_id)->first();
            }
            
            // Last resort: try by employee_id
            if (!$user) {
                $user = User::where('employee_id', $user_id)->first();
            }
            
            if (!$user) {
                return response()->json(['status' => 'error', 'message' => 'User not found'], 404);
            }

            // Update password (preserve hash if already hashed)
            $user->update(['password' => $this->handlePassword($request->password)]);

            ActivityLogger::logPasswordSync($user);
            Log::info("Password updated in attendance system via sync", ['user_id' => $user_id]);

            return response()->json([
                'status' => 'success',
                'message' => 'Password synchronized successfully'
            ]);

        } catch (\Exception $e) {
            Log::error("Password sync failed", [
                'user_id' => $user_id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Password sync operation failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle User Synchronization from Payroll
     */
    private function handleUserSync(Request $request, $action, $id = null)
    {
        try {
            if ($action === 'delete') {
                // Find user by payroll_id (should match employee_id from payroll)
                $user = User::where('payroll_id', $id)->first();
                
                // If not found by payroll_id, try by payroll_user_id
                if (!$user) {
                    $user = User::where('payroll_user_id', $id)->first();
                }
                
                // Last resort: try by employee_id
                if (!$user) {
                    $user = User::where('employee_id', $id)->first();
                }
                           
                if (!$user) {
                    return response()->json(['status' => 'error', 'message' => 'User not found'], 404);
                }
                
                ActivityLogger::logUserDeleted($user);
                $user->delete();
                
                Log::info("User deleted in attendance system via sync", ['user_id' => $id]);
                
                return response()->json(['status' => 'success', 'message' => 'User deleted successfully']);
            }

            // Validation rules for create/update
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|string',        // This maps to employee_id
                'payroll_id' => 'nullable|string',     // Employee ID from payroll system (can be string or number)
                'payroll_user_id' => 'nullable|integer', // User ID from payroll system
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'role_name' => 'nullable|string|max:100',
                'status' => 'nullable|string|in:Active,Inactive',  // Not stored but validated
                'department' => 'nullable|string|max:255',
                'designation' => 'nullable|string|max:255',  // Maps to position in old system
                'phone' => 'nullable|string|max:20',        // Not stored in new system
                'password' => 'nullable|string',
                'join_date' => 'nullable|date',              // Maps to date_of_joining
                'line_manager' => 'nullable|string',         // Maps to reporting_manager_id
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $userData = $validator->validated();

            if ($action === 'create') {
                // Check for existing user by payroll_id first (matches payroll's employee_id)
                $existingUser = null;
                if (isset($userData['payroll_id'])) {
                    $existingUser = User::where('payroll_id', $userData['payroll_id'])->first();
                }
                
                // If not found by payroll_id, check by payroll_user_id
                if (!$existingUser && isset($userData['payroll_user_id'])) {
                    $existingUser = User::where('payroll_user_id', $userData['payroll_user_id'])->first();
                }
                
                // If not found by either, check by employee_id (user_id from payroll)
                if (!$existingUser) {
                    $existingUser = User::where('employee_id', $userData['user_id'])->first();
                }
                
                // Last resort: check by email
                if (!$existingUser) {
                    $existingUser = User::where('email', $userData['email'])->first();
                }
                
                if ($existingUser) {
                    // Update existing user
                    $user = $this->updateUserData($existingUser, $userData);
                    ActivityLogger::logUserUpdated($user, 'sync_update');
                    
                    Log::info("Existing user updated from payroll sync", [
                        'employee_id' => $userData['user_id'],
                        'email' => $userData['email']
                    ]);
                } else {
                    // Create new user
                    $user = $this->createNewUser($userData);
                    ActivityLogger::logUserCreated($user, 'sync_create');
                    
                    Log::info("New user created from payroll sync", ['employee_id' => $userData['user_id']]);
                }

                return response()->json([
                    'status' => 'success',
                    'message' => 'User synced successfully',
                    'user' => [
                        'id' => $user->id,
                        'employee_id' => $user->employee_id,
                        'payroll_id' => $user->payroll_id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->role,
                    ]
                ]);
            }

            if ($action === 'update') {
                // Find user by payroll_id (matches payroll's employee_id)
                $user = null;
                if (isset($userData['payroll_id'])) {
                    $user = User::where('payroll_id', $userData['payroll_id'])->first();
                }
                
                // If not found by payroll_id, try by payroll_user_id
                if (!$user && isset($userData['payroll_user_id'])) {
                    $user = User::where('payroll_user_id', $userData['payroll_user_id'])->first();
                }
                
                // If not found, try by employee_id (user_id from payroll)
                if (!$user) {
                    $user = User::where('employee_id', $id)->first();
                }
                
                if (!$user) {
                    return response()->json(['status' => 'error', 'message' => 'User not found'], 404);
                }

                $user = $this->updateUserData($user, $userData);
                ActivityLogger::logUserUpdated($user, 'sync_update');

                Log::info("User updated in attendance system via sync", ['user_id' => $id]);

                return response()->json([
                    'status' => 'success',
                    'message' => 'User updated successfully',
                    'user' => [
                        'id' => $user->id,
                        'employee_id' => $user->employee_id,
                        'payroll_id' => $user->payroll_id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->role,
                    ]
                ]);
            }

        } catch (\Exception $e) {
            Log::error("User sync failed", [
                'action' => $action,
                'user_id' => $id ?? $request->user_id ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Sync operation failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create New User with Field Mapping
     */
    private function createNewUser($userData)
    {
        return User::create([
            'employee_id' => $userData['user_id'],
            'payroll_id' => $userData['payroll_id'] ?? null,        // Employee ID from payroll
            'payroll_user_id' => $userData['payroll_user_id'] ?? null, // User ID from payroll
            'name' => $userData['name'],
            'email' => $userData['email'],
            'password' => $this->handlePassword($userData['password'] ?? 'attendance123'),
            'role' => $this->mapRoleFromOld($userData['role_name'] ?? 'Employee'),
            'designation' => $userData['designation'] ?? null,
            'department_id' => $this->getDepartmentId($userData['department'] ?? null),
            'date_of_joining' => isset($userData['join_date']) ? Carbon::parse($userData['join_date'])->format('Y-m-d') : null,
            'reporting_manager_id' => $this->getManagerId($userData['line_manager'] ?? null),
            'financial_year' => $this->getCurrentFinancialYear(),
        ]);
    }

    /**
     * Update User Data with Field Mapping
     */
    private function updateUserData($user, $userData)
    {
        $updateData = array_filter([
            'employee_id' => $userData['user_id'],
            'payroll_id' => $userData['payroll_id'] ?? $user->payroll_id,
            'payroll_user_id' => $userData['payroll_user_id'] ?? $user->payroll_user_id, // User ID from payroll
            'name' => $userData['name'],
            'email' => $userData['email'],
            'role' => $this->mapRoleFromOld($userData['role_name'] ?? null) ?? $user->role,
            'designation' => $userData['designation'] ?? $user->designation,
            'department_id' => $this->getDepartmentId($userData['department'] ?? null) ?? $user->department_id,
            'date_of_joining' => isset($userData['join_date']) ? Carbon::parse($userData['join_date'])->format('Y-m-d') : $user->date_of_joining,
            'reporting_manager_id' => $this->getManagerId($userData['line_manager'] ?? null) ?? $user->reporting_manager_id,
        ], function($value) {
            return $value !== null;
        });

        // Add password if provided (preserve hash from payroll system)
        if (!empty($userData['password'])) {
            // If password starts with $2y$ it's already hashed, preserve it
            if (str_starts_with($userData['password'], '$2y$')) {
                $updateData['password'] = $userData['password'];
            } else {
                // If it's plaintext, hash it
                $updateData['password'] = Hash::make($userData['password']);
            }
        }

        $user->update($updateData);
        return $user;
    }

    /**
     * Map Old Role Names to New Role Structure
     */
    private function mapRoleFromOld($oldRole)
    {
        if (!$oldRole) {
            return null;
        }

        $roleMapping = [
            'Super Admin' => 'super_admin',
            'Administrator' => 'admin',
            'Admin' => 'admin',
            'Employee' => 'staff',
            'Staff' => 'staff',
            'Normal User' => 'staff',
            'Client' => 'staff',
        ];

        return $roleMapping[$oldRole] ?? 'staff';
    }

    /**
     * Get Department ID by Name
     */
    private function getDepartmentId($departmentName)
    {
        if (!$departmentName) {
            return null;
        }

        $department = Department::where('name', $departmentName)
                               ->orWhere('code', $departmentName)
                               ->first();
        
        return $department ? $department->id : null;
    }

    /**
     * Get Manager ID (for now return null, can be enhanced later)
     */
    private function getManagerId($managerReference)
    {
        if (!$managerReference) {
            return null;
        }

        // Try to find manager by employee_id or payroll_id
        $manager = User::where('employee_id', $managerReference)
                      ->orWhere('payroll_id', $managerReference)
                      ->first();
        
        return $manager ? $manager->payroll_id : null;
    }

    /**
     * Get Current Financial Year
     */
    private function getCurrentFinancialYear()
    {
        $month = now()->month;
        $year = now()->year;
        return $month >= 4 ? "$year-" . ($year + 1) : ($year - 1) . "-$year";
    }

    /**
     * Handle password - preserve hash if already hashed, otherwise hash it
     */
    private function handlePassword($password)
    {
        // If password starts with $2y$ it's already hashed, preserve it
        if (str_starts_with($password, '$2y$')) {
            return $password;
        } else {
            // If it's plaintext, hash it
            return Hash::make($password);
        }
    }

    /**
     * Validate API Token for Sync Operations
     */
    private function validateApiToken(Request $request)
    {
        $token = $request->header('Authorization');
        $bearerToken = $request->bearerToken();
        $expectedToken = env('ATTENDANCE_API_TOKEN', 'hrms_sync_token_2025_secure_key');
        
        return ($token === 'Bearer ' . $expectedToken) || ($bearerToken === $expectedToken);
    }

    /**
     * Sync password from payroll system to attendance system (bidirectional sync)
     * This method receives password changes from payroll and updates attendance user by email
     */
    public function syncPasswordFromPayrollByEmail(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_email' => 'required|email',
                'new_password' => 'required|string',
                'sync_token' => 'required|string' // Add security token
            ]);

            if ($validator->fails()) {
                Log::error('Password sync from payroll validation failed', [
                    'errors' => $validator->errors()->toArray(),
                    'request' => $request->all()
                ]);
                return response()->json(['error' => 'Validation failed', 'details' => $validator->errors()], 400);
            }

            // Verify sync token for security
            $expectedToken = env('PAYROLL_SYNC_TOKEN', 'default-token');
            if ($request->sync_token !== $expectedToken) {
                Log::warning('Invalid sync token for password sync from payroll', [
                    'provided_token' => $request->sync_token,
                    'ip' => $request->ip()
                ]);
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            // Find user by email in attendance system
            $user = User::where('email', $request->user_email)->first();
            
            if (!$user) {
                Log::warning('User not found in attendance system for password sync', [
                    'email' => $request->user_email
                ]);
                return response()->json(['error' => 'User not found in attendance system'], 404);
            }

            // Update password in attendance system
            $user->password = Hash::make($request->new_password);
            $user->save();

            // Log successful password sync
            ActivityLogger::log('Password synced from payroll system', 'Users', $user->id, [
                'synced_from' => 'payroll', 
                'user_email' => $request->user_email,
                'user_name' => $user->name
            ]);

            Log::info('Password successfully synced from payroll to attendance', [
                'user_email' => $request->user_email,
                'user_id' => $user->id,
                'synced_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Password successfully synced to attendance system',
                'user_id' => $user->id
            ]);

        } catch (\Exception $e) {
            Log::error('Error syncing password from payroll system', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);
            
            return response()->json(['error' => 'Failed to sync password'], 500);
        }
    }
}
