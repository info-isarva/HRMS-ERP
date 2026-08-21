<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TenantLoginService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    public function __construct(private TenantLoginService $tenantLogin)
    {
    }

    /**
     * Login and get JWT token
     */
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'company_code' => 'required|string|max:32',
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        try {
            $tenant = $this->tenantLogin->findActiveByCompanyCode($credentials['company_code']);
            $this->tenantLogin->apply($tenant);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->errors()['company_code'][0] ?? 'Invalid company code',
            ], 422);
        }

        if (! Auth::guard('api')->attempt([
            'email' => $credentials['email'],
            'password' => $credentials['password'],
        ])) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials',
            ], 401);
        }

        $user = Auth::guard('api')->user();

        $token = JWTAuth::customClaims([
            'tenant_id' => $tenant->id,
            'company_code' => $tenant->company_code,
        ])->fromUser($user);

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'employee_id' => $user->employee_id,
                    'role' => $user->role,
                    'department_id' => $user->department_id,
                ],
                'tenant' => [
                    'id' => $tenant->id,
                    'company_code' => $tenant->company_code,
                    'name' => $tenant->name,
                ],
                'token' => $token,
                'token_type' => 'Bearer',
                'expires_in' => config('jwt.ttl') * 60,
            ],
        ]);
    }

    /**
     * Get authenticated user details
     */
    public function me(Request $request): JsonResponse
    {
        try {
            $user = Auth::guard('api')->user();

            if (! $user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated',
                ], 401);
            }

            return response()->json([
                'success' => true,
                'message' => 'User details retrieved successfully',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'employee_id' => $user->employee_id,
                        'payroll_id' => $user->payroll_id,
                        'role' => $user->role,
                        'designation' => $user->designation,
                        'department_id' => $user->department_id,
                        'date_of_joining' => $user->date_of_joining,
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get user details',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Logout and invalidate token
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            Auth::guard('api')->logout();

            return response()->json([
                'success' => true,
                'message' => 'Successfully logged out',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to logout',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Refresh JWT token
     */
    public function refresh(Request $request): JsonResponse
    {
        try {
            $token = Auth::guard('api')->refresh();

            return response()->json([
                'success' => true,
                'message' => 'Token refreshed successfully',
                'data' => [
                    'token' => $token,
                    'token_type' => 'Bearer',
                    'expires_in' => config('jwt.ttl') * 60,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to refresh token',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
