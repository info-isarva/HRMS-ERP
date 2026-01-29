<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Tymon\JWTAuth\JWTAuth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cookie;
use Carbon\Carbon;

class SSOAuthController extends Controller
{
    protected $jwt;

    public function __construct(JWTAuth $jwt)
    {
        $this->jwt = $jwt;
    }

    public function authenticate(Request $request)
    {
        $request->validate(['token' => 'required|string']);

        try {
            $token = $request->query('token');
            $payload = $this->jwt->setToken($token)->getPayload();
            
            // Verify token expiration
            if (now()->timestamp > $payload->get('exp')) {
                throw new \Exception('Token expired');
            }

            $userData = $payload->get('user');

            // Verify HMAC
            $expectedHmac = hash_hmac(
                'sha256',
                $userData['id'] . $userData['email'],
                env('JWT_HMAC_SECRET')
            );

            if (!hash_equals($expectedHmac, $userData['hmac'])) {
                throw new \Exception('Invalid token signature');
            }

            // Map old user fields to new User model structure
            $userAttributes = $this->mapUserAttributes($userData);

            // Find or create user
            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                $userAttributes
            );

            // Verify user is active (if status field exists)
            if (isset($user->status) && $user->status !== 'Active') {
                throw new \Exception('User account is inactive');
            }

            // Log in user
            Auth::login($user, true);
            
            // Set session data (compatible with old system)
            Session::put($this->prepareUserSessionData($user));

            // Update last login if field exists
            if ($user->getConnection()->getSchemaBuilder()->hasColumn('users', 'last_login')) {
                $user->update(['last_login' => now()]);
            }

            // Log successful SSO login
            ActivityLogger::logLogin($user, 'sso');

            return redirect()->intended(route('dashboard'));

        } catch (\Exception $e) {
            Log::error('SSO Authentication failed', [
                'error' => $e->getMessage(),
                'token' => $token ?? 'No token provided'
            ]);
            
            return redirect()->route('login')->withErrors(['sso' => 'SSO failed: ' . $e->getMessage()]);
        }
    }

    /**
     * Map old user attributes to new User model structure
     */
    private function mapUserAttributes($userData)
    {
        return [
            'name' => $userData['name'] ?? $userData['email'],
            'password' => Hash::make(Str::random(16)),
            'role' => $this->mapRole($userData['role_name'] ?? 'staff'),
            'designation' => $userData['position'] ?? null,
            'financial_year' => $this->getCurrentFinancialYear(),
            'employee_id' => $userData['user_id'] ?? null,
            'payroll_id' => $userData['user_id'] ?? null,
            'department_id' => $this->getDepartmentId($userData['department'] ?? null),
            'date_of_joining' => isset($userData['join_date']) ? Carbon::parse($userData['join_date']) : null,
            'reporting_manager_id' => $userData['line_manager'] ?? null,
        ];
    }

    /**
     * Map old role names to new role structure
     */
    private function mapRole($oldRole)
    {
        $roleMapping = [
            'Super Admin' => 'super_admin',
            'Administrator' => 'admin',
            'Admin' => 'admin',
            'Employee' => 'staff',
            'Staff' => 'staff',
        ];

        return $roleMapping[$oldRole] ?? 'staff';
    }

    /**
     * Get department ID by name (you may need to adjust this based on your department structure)
     */
    private function getDepartmentId($departmentName)
    {
        if (!$departmentName) {
            return null;
        }

        // Try to find department by name if Department model exists
        if (class_exists(\App\Models\Department::class)) {
            $department = \App\Models\Department::where('name', $departmentName)->first();
            return $department ? $department->id : null;
        }

        return null;
    }

    /**
     * Get current financial year
     */
    private function getCurrentFinancialYear()
    {
        $month = now()->month;
        $year = now()->year;
        return $month >= 4 ? "$year-" . ($year + 1) : ($year - 1) . "-$year";
    }

    /**
     * Prepare User Session Data (compatible with old system)
     */
    private function prepareUserSessionData($user)
    {
        return [
            'name' => $user->name,
            'email' => $user->email,
            'user_id' => $user->employee_id ?? $user->payroll_id ?? $user->id,
            'join_date' => $user->date_of_joining?->format('Y-m-d'),
            'phone_number' => $user->phone_number ?? null,
            'status' => 'Active', // Default to Active for new system
            'role_name' => $this->mapRoleToOldFormat($user->role),
            'avatar' => $user->avatar ?? null,
            'position' => $user->designation,
            'department' => $user->department?->name ?? null,
            'line_manager' => $user->reporting_manager_id,
            'second_line_manager' => null, // Not implemented in new system yet
        ];
    }

    /**
     * Map new role format back to old format for session compatibility
     */
    private function mapRoleToOldFormat($newRole)
    {
        $roleMapping = [
            'super_admin' => 'Super Admin',
            'admin' => 'Administrator',
            'staff' => 'Employee',
        ];

        return $roleMapping[$newRole] ?? 'Employee';
    }

    /**
     * Handle SSO passive logout
     */
    public function passiveLogout(Request $request)
    {
        $user = Auth::user();
        
        // Log logout before actually logging out
        if ($user) {
            ActivityLogger::logLogout($user);
        }
        
        Auth::logout();
        $request->session()->flush();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        $domain = config('session.domain');
        $cookies = [
            Cookie::forget(config('session.cookie'), '/', $domain),
            Cookie::forget('attendance_session', '/', $domain),
        ];
        
        return response()->make('', 200)->withCookies($cookies);
    }
}
