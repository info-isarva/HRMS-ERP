<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PayrollApiService
{
    protected $baseUrl;
    protected $email;
    protected $password;
    protected $jwtToken;
    private $token = null;

    public function __construct()
    {
        $this->baseUrl = config('external_api.payroll_api.base_url');
        $this->email = config('external_api.payroll_api.email');
        $this->password = config('external_api.payroll_api.password');
        $this->jwtToken = config('external_api.payroll_api.jwt_token');
    }

    /**
     * Get the authentication token
     *
     * @return string
     */

    public function getToken()
    {
        // Get the currently logged-in user from Attendance
        $user = \Illuminate\Support\Facades\Auth::user();
        
        if (!$user || !$user->email) {
            Log::error('No authenticated user found for Payroll API token request');
            return null;
        }

        // Use user's email as cache key so each user has their own token
        $cacheKey = 'payroll_api_token_' . $user->email;
        
        // Check if token exists in cache for this user
        if (Cache::has($cacheKey)) {
            Log::info('Using cached payroll API Sanctum token for user', ['email' => $user->email]);
            return Cache::get($cacheKey);
        }

        Log::info('Requesting new payroll API Sanctum token for logged-in user', ['email' => $user->email]);
        Log::debug('API URL: ' . $this->baseUrl . '/login');
        Log::debug('Using JWT token (first 10 chars): ' . substr($this->jwtToken, 0, 10) . '...');
        
        try {
            // Use the currently logged-in user's email and password to authenticate with Payroll
            // The Payroll API /login endpoint expects:
            // - JWT token in Authorization header (to validate the request is from trusted source)
            // - Current user's email and password in request body
            // - Returns a Sanctum session token in response
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->jwtToken}",
                'Accept' => 'application/json',
            ])->post("{$this->baseUrl}/login", [
                'email' => $user->email,
                'password' => $user->password, // Using current user's password from Attendance
            ]);

            // Log the full request for debugging
            Log::debug('API /login Request Headers: ', [
                'Authorization' => 'Bearer ' . substr($this->jwtToken, 0, 10) . '...',
                'Accept' => 'application/json',
            ]);
            
            Log::debug('API /login Request Body: ', [
                'email' => $user->email,
                'password' => '[REDACTED]',
            ]);

            // Log the raw response for debugging
            Log::debug('API /login Raw Response: ' . $response->body());

            if ($response->successful()) {
                $data = $response->json();
                
                // The API returns a 'token' field which is the Sanctum session token
                $sanctumToken = $data['token'] ?? null;

                if ($sanctumToken) {
                    Log::info('Successfully obtained new Sanctum API token from /api/login', ['email' => $user->email]);
                    // Cache the token for 23 hours per user (assuming tokens expire in 24 hours)
                    Cache::put($cacheKey, $sanctumToken, now()->addHours(23));
                    $this->token = $sanctumToken;
                    return $sanctumToken;
                } else {
                    Log::error('API /login response did not contain token field', ['response' => $data, 'email' => $user->email]);
                    return null;
                }
            } else {
                Log::error('Failed API response from /api/login', [
                    'status' => $response->status(),
                    'reason' => $response->reason(),
                    'body' => $response->body(),
                    'email' => $user->email
                ]);
            }

            return null;
            
        } catch (\Exception $e) {
            Log::error('Exception when getting API token: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
                'email' => $user->email
            ]);
            return null;
        }
    }
    /**
     * Get employees from the API
     *
     * @return array|null
     */
    public function getEmployees()
    {
        Log::info('Fetching employees from payroll API');
        
        $token = $this->getToken();
        
        if (!$token) {
            Log::error('No token available for API request');
            return null;
        }

        Log::debug('Using token (first 10 chars): ' . substr($token, 0, 10) . '...');
        Log::debug('API URL: ' . $this->baseUrl . '/employees');
        
        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$token}",
                'Accept' => 'application/json',
            ])->get("{$this->baseUrl}/employees");
            
            // Log the raw response for debugging
            Log::debug('Employee API Raw Response: ' . substr($response->body(), 0, 1000) . '...');

            if ($response->successful()) {
                $data = $response->json();
                
                // Check if the response has the expected structure
                if (isset($data['status']) && $data['status'] === 'success' && isset($data['data'])) {
                    $employees = $data['data'];
                    
                    Log::info('Successfully fetched employees from API', [
                        'count' => count($employees)
                    ]);
                    
                    // Log the first employee to verify structure
                    if (count($employees) > 0) {
                        Log::debug('First employee structure:', [
                            'employee' => json_encode($employees[0])
                        ]);
                    }
                    
                    return $employees;
                } else {
                    Log::error('Unexpected API response format', [
                        'status' => $data['status'] ?? 'unknown',
                        'has_data' => isset($data['data']),
                        'response' => json_encode($data)
                    ]);
                    return null;
                }
            }

            Log::error('Failed to get employees from API', [
                'status' => $response->status(),
                'reason' => $response->reason(),
                'response' => $response->body()
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Exception when getting employees from API: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }
    
    /**
     * Get departments from the API
     *
     * @return array|null
     */
    public function getDepartments()
    {
        Log::info('Fetching departments from payroll API');
        
        $token = $this->getToken();
        
        if (!$token) {
            Log::error('No token available for API request');
            return null;
        }

        Log::debug('Using token (first 10 chars): ' . substr($token, 0, 10) . '...');
        Log::debug('API URL: ' . $this->baseUrl . '/departments');
        
        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$token}",
                'Accept' => 'application/json',
            ])->get("{$this->baseUrl}/departments");
            
            // Log the raw response for debugging
            Log::debug('Department API Raw Response: ' . substr($response->body(), 0, 1000) . '...');
            
            // Log the full request for debugging
            Log::debug('Department API Request details', [
                'url' => $this->baseUrl . '/departments',
                'method' => 'GET',
                'headers' => [
                    'Authorization' => 'Bearer ' . substr($token, 0, 10) . '...',
                    'Accept' => 'application/json',
                ]
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                // Check if the response has the expected structure (similar to employees)
                if (isset($data['status']) && $data['status'] === 'success' && isset($data['data'])) {
                    $departments = $data['data'];
                    
                    if (empty($departments)) {
                        Log::warning('API returned empty departments array', [
                            'status' => $response->status(),
                            'response' => $response->body()
                        ]);
                    }
                    
                    Log::info('Successfully fetched departments from API', [
                        'count' => count($departments)
                    ]);
                    
                    return $departments;
                } else {
                    // Fallback for different API structure
                    $departments = $data['data'] ?? [];
                    
                    if (empty($departments)) {
                        Log::warning('API returned empty departments array or unexpected format', [
                            'status' => $response->status(),
                            'response' => $response->body()
                        ]);
                    }
                    
                    Log::info('Fetched departments with fallback format', [
                        'count' => count($departments)
                    ]);
                    
                    return $departments;
                }
            }

            Log::error('Failed to get departments from API', [
                'status' => $response->status(),
                'reason' => $response->reason(),
                'response' => $response->body()
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Exception when getting departments from API: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
                'url' => $this->baseUrl . '/departments'
            ]);
            return null;
        }
    }

    /**
     * Get employee week-off configuration by email
     * 
     * @param string $email
     * @return array|null
     */
    public function getEmployeeWeekOffByEmail($email)
    {
        $employees = $this->getEmployees();
        
        if (!$employees) {
            return null;
        }

        foreach ($employees as $employee) {
            if (isset($employee['email']) && strtolower($employee['email']) === strtolower($email)) {
                return $employee['week_off_configuration'] ?? null;
            }
        }

        return null;
    }

    /**
     * Get employee week-off configuration by payroll_id
     * 
     * @param string $payrollId
     * @return array|null
     */
    public function getEmployeeWeekOffByPayrollId($payrollId)
    {
        $employees = $this->getEmployees();
        
        if (!$employees) {
            return null;
        }

        foreach ($employees as $employee) {
            if (isset($employee['payroll_id']) && $employee['payroll_id'] === $payrollId) {
                return $employee['week_off_configuration'] ?? null;
            }
        }

        return null;
    }

    /**
     * Get all employees with their week-off configurations indexed by email
     * 
     * @return array
     */
    public function getEmployeeWeekOffsIndexedByEmail()
    {
        $employees = $this->getEmployees();
        $indexed = [];

        if ($employees) {
            foreach ($employees as $employee) {
                if (isset($employee['email']) && isset($employee['week_off_configuration'])) {
                    $indexed[strtolower($employee['email'])] = $employee['week_off_configuration'];
                }
            }
        }

        Log::info('Indexed week-off configurations by email', [
            'count' => count($indexed)
        ]);

        return $indexed;
    }

    /**
     * Get all employees with their week-off configurations indexed by payroll_id
     * 
     * @return array
     */
    public function getEmployeeWeekOffsIndexedByPayrollId()
    {
        $employees = $this->getEmployees();
        $indexed = [];

        if ($employees) {
            foreach ($employees as $employee) {
                if (isset($employee['payroll_id']) && isset($employee['week_off_configuration'])) {
                    $indexed[$employee['payroll_id']] = $employee['week_off_configuration'];
                }
            }
        }

        Log::info('Indexed week-off configurations by payroll_id', [
            'count' => count($indexed)
        ]);

        return $indexed;
    }

    /**
     * Check if a specific date is a week-off for an employee
     * 
     * @param string $email
     * @param \Carbon\Carbon $date
     * @return bool
     */
    public function isWeekOffForEmployee($email, $date)
    {
        $weekOffConfig = $this->getEmployeeWeekOffByEmail($email);
        
        if (!$weekOffConfig || !isset($weekOffConfig['week_off_days'])) {
            // Default to weekend (Saturday=6, Sunday=0) if no configuration found
            Log::debug('No week-off configuration found for employee, using default weekend', [
                'email' => $email,
                'date' => $date->format('Y-m-d'),
                'is_weekend' => $date->isWeekend()
            ]);
            return $date->isWeekend();
        }

        // Get day of week (0 = Sunday, 1 = Monday, etc.)
        $dayOfWeek = $date->dayOfWeek;
        $isWeekOff = in_array($dayOfWeek, $weekOffConfig['week_off_days']);
        
        Log::debug('Checking week-off for employee', [
            'email' => $email,
            'date' => $date->format('Y-m-d'),
            'day_of_week' => $dayOfWeek,
            'week_off_days' => $weekOffConfig['week_off_days'],
            'is_week_off' => $isWeekOff
        ]);

        return $isWeekOff;
    }

    /**
     * Clear the cached employees data
     * 
     * @return void
     */
    public function clearCache()
    {
        Cache::forget('payroll_api_token');
        Log::info('Cleared payroll API cache');
    }
}
