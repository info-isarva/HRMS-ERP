<?php

namespace App\Http\Controllers\Api\Mobile\V1;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogger;
use App\Support\MobileLeaveApplicationSupport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Mobile app login — issues JWT (same guard as legacy /api/login).
     */
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (! $token = Auth::guard('api')->attempt($credentials)) {
            ActivityLogger::logFailedLogin($credentials['email']);

            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials',
            ], 401);
        }

        $user = Auth::guard('api')->user();
        ActivityLogger::logLogin($user, 'mobile_api');
        self::syncUserFinancialYear($user);

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => $this->userPayload($user),
                'token' => $token,
                'token_type' => 'Bearer',
                'expires_in' => (int) (config('jwt.ttl', 60) * 60),
            ],
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $user = Auth::guard('api')->user();
        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated',
            ], 401);
        }

        $user->loadMissing('department:id,name');
        self::syncUserFinancialYear($user);
        $payload = $this->userPayload($user);
        if ($user->relationLoaded('department') && $user->department) {
            $payload['department_name'] = $user->department->name;
        }

        return response()->json([
            'success' => true,
            'message' => 'User details retrieved successfully',
            'data' => ['user' => $payload],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        try {
            Auth::guard('api')->logout();

            return response()->json([
                'success' => true,
                'message' => 'Successfully logged out',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to logout',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

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
                    'expires_in' => (int) (config('jwt.ttl', 60) * 60),
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to refresh token',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private function userPayload($user): array
    {
        $currentFy = MobileLeaveApplicationSupport::currentFinancialYear();

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'employee_id' => $user->employee_id,
            'payroll_id' => $user->payroll_id,
            'role' => $user->role,
            'designation' => $user->designation,
            'department_id' => $user->department_id,
            'date_of_joining' => $user->date_of_joining,
            'financial_year' => MobileLeaveApplicationSupport::currentFinancialYearName(),
            'financial_year_id' => $currentFy?->id,
            'is_admin' => $user->hasAdminAccess(),
        ];
    }

    private static function syncUserFinancialYear($user): void
    {
        $currentName = MobileLeaveApplicationSupport::currentFinancialYearName();
        if ($user->financial_year !== $currentName) {
            $user->financial_year = $currentName;
            $user->saveQuietly();
        }
    }
}
