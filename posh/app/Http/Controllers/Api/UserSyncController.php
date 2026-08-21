<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class UserSyncController extends Controller
{
    /**
     * Sync User from Payroll - Create
     */
    public function syncUserFromPayroll(Request $request)
    {
        if (!$this->validateApiToken($request)) {
            return response()->json(['status' => 'error', 'message' => 'Invalid API token'], 401);
        }

        return $this->handleUserSync($request, 'create');
    }

    /**
     * Sync User from Payroll - Update
     */
    public function updateUserFromPayroll(Request $request, $userId)
    {
        if (!$this->validateApiToken($request)) {
            return response()->json(['status' => 'error', 'message' => 'Invalid API token'], 401);
        }

        return $this->handleUserSync($request, 'update', $userId);
    }

    /**
     * Sync User from Payroll - Delete
     */
    public function deleteUserFromPayroll(Request $request, $userId)
    {
        if (!$this->validateApiToken($request)) {
            return response()->json(['status' => 'error', 'message' => 'Invalid API token'], 401);
        }

        return $this->handleUserSync($request, 'delete', $userId);
    }

    /**
     * Sync Password from Payroll
     */
    public function syncPasswordFromPayroll(Request $request)
    {
        if (!$this->validateApiToken($request)) {
            return response()->json(['status' => 'error', 'message' => 'Invalid API token'], 401);
        }

        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|string',
                'password' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Find user by employee_id
            $user = User::where('employee_id', $request->user_id)->first();

            if (!$user) {
                return response()->json(['status' => 'error', 'message' => 'User not found'], 404);
            }

            // If password starts with $2y$ it's already hashed, preserve it
            if (str_starts_with($request->password, '$2y$')) {
                $user->password = $request->password;
            } else {
                $user->password = Hash::make($request->password);
            }
            $user->save();

            Log::info("CRM user password synced from payroll", ['employee_id' => $request->user_id]);

            return response()->json(['status' => 'success', 'message' => 'Password synced successfully']);
        } catch (\Exception $e) {
            Log::error("CRM password sync failed", ['error' => $e->getMessage()]);
            return response()->json(['status' => 'error', 'message' => 'Password sync failed'], 500);
        }
    }

    /**
     * Handle User Synchronization from Payroll
     */
    private function handleUserSync(Request $request, $action, $id = null)
    {
        try {
            if ($action === 'delete') {
                $user = User::where('payroll_id', $id)->first()
                    ?? User::where('payroll_user_id', $id)->first()
                    ?? User::where('employee_id', $id)->first();

                if (!$user) {
                    return response()->json(['status' => 'error', 'message' => 'User not found'], 404);
                }

                $user->delete();
                Log::info("CRM user deleted via payroll sync", ['user_id' => $id]);

                return response()->json(['status' => 'success', 'message' => 'User deleted successfully']);
            }

            // Validation rules for create/update
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|string',
                'payroll_id' => 'nullable|string',
                'payroll_user_id' => 'nullable|integer',
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'phone' => 'nullable|string|max:20',
                'status' => 'nullable|string',
                'password' => 'nullable|string',
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
                // Check for existing user
                $existingUser = null;
                if (isset($userData['payroll_id'])) {
                    $existingUser = User::where('payroll_id', $userData['payroll_id'])->first();
                }
                if (!$existingUser && isset($userData['payroll_user_id'])) {
                    $existingUser = User::where('payroll_user_id', $userData['payroll_user_id'])->first();
                }
                if (!$existingUser) {
                    $existingUser = User::where('employee_id', $userData['user_id'])->first();
                }
                if (!$existingUser) {
                    $existingUser = User::where('email', $userData['email'])->first();
                }

                if ($existingUser) {
                    $user = $this->updateUserData($existingUser, $userData);
                    Log::info("Existing CRM user updated from payroll sync", [
                        'employee_id' => $userData['user_id'],
                        'email' => $userData['email']
                    ]);
                } else {
                    $user = $this->createNewUser($userData);
                    Log::info("New CRM user created from payroll sync", ['employee_id' => $userData['user_id']]);
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
                    ]
                ]);
            }

            if ($action === 'update') {
                $user = User::where('payroll_id', $id)->first()
                    ?? User::where('payroll_user_id', $id)->first()
                    ?? User::where('employee_id', $id)->first();

                if (!$user) {
                    // User not found for update — try creating instead
                    $user = $this->createNewUser($userData);
                    Log::info("CRM user not found for update, created new", ['user_id' => $id]);

                    return response()->json([
                        'status' => 'success',
                        'message' => 'User created (was not found for update)',
                        'user' => [
                            'id' => $user->id,
                            'employee_id' => $user->employee_id,
                            'name' => $user->name,
                            'email' => $user->email,
                        ]
                    ]);
                }

                $user = $this->updateUserData($user, $userData);
                Log::info("CRM user updated from payroll sync", ['user_id' => $id]);

                return response()->json([
                    'status' => 'success',
                    'message' => 'User updated successfully',
                    'user' => [
                        'id' => $user->id,
                        'employee_id' => $user->employee_id,
                        'name' => $user->name,
                        'email' => $user->email,
                    ]
                ]);
            }

        } catch (\Exception $e) {
            Log::error("CRM user sync failed", [
                'action' => $action,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Sync failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create New User with Field Mapping
     */
    private function createNewUser($userData)
    {
        $password = $userData['password'] ?? 'crm_default_123';

        // If password starts with $2y$ it's already hashed
        if (str_starts_with($password, '$2y$')) {
            $hashedPassword = $password;
        } else {
            $hashedPassword = Hash::make($password);
        }

        return User::create([
            'employee_id' => $userData['user_id'],
            'payroll_id' => $userData['payroll_id'] ?? null,
            'payroll_user_id' => $userData['payroll_user_id'] ?? null,
            'name' => $userData['name'],
            'email' => $userData['email'],
            'password' => $hashedPassword,
            'mobile' => $userData['phone'] ?? null,
            'status' => $this->mapStatus($userData['status'] ?? 'Active'),
            'crm_role_type' => 3, // Regular user role
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
            'payroll_user_id' => $userData['payroll_user_id'] ?? $user->payroll_user_id,
            'name' => $userData['name'],
            'email' => $userData['email'],
            'mobile' => $userData['phone'] ?? $user->mobile,
            'status' => isset($userData['status']) ? $this->mapStatus($userData['status']) : $user->status,
        ], function ($value) {
            return $value !== null;
        });

        // Handle password if provided
        if (!empty($userData['password'])) {
            if (str_starts_with($userData['password'], '$2y$')) {
                $updateData['password'] = $userData['password'];
            } else {
                $updateData['password'] = Hash::make($userData['password']);
            }
        }

        $user->update($updateData);
        return $user;
    }

    /**
     * Map payroll status to CRM status value
     * CRM uses integer status: 1 = Active, 0 = Inactive
     */
    private function mapStatus($status)
    {
        if (is_numeric($status)) {
            return (int) $status;
        }
        return strtolower($status) === 'active' ? 1 : 0;
    }

    /**
     * Validate API Token for Sync Operations
     */
    private function validateApiToken(Request $request)
    {
        $token = $request->header('Authorization');
        $bearerToken = $request->bearerToken();
        $expectedToken = env('CRM_API_TOKEN', 'hrms_sync_token_2025_secure_key');

        return ($token === 'Bearer ' . $expectedToken) || ($bearerToken === $expectedToken);
    }
}
