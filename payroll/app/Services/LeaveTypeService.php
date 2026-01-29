<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Models\CachedLeaveType;
use App\Models\LeaveTypeSyncLog;
use App\Helpers\FinancialYearHelper;
use Carbon\Carbon;

class LeaveTypeService
{
    private $attendanceApiUrl;
    private $apiTimeout;
    private $cacheTimeout;
    private $apiToken;

    public function __construct()
    {
        $this->attendanceApiUrl = config('app.attendance_api_url', 'https://attendancedev.isarva.in/api');
        $this->apiTimeout = config('app.attendance_api_timeout', 30);
        $this->cacheTimeout = config('app.leave_cache_timeout', 3600); // 1 hour default
        $this->apiToken = env('ATTENDANCE_API_TOKEN', 'hrms_sync_token_2025_secure_key');
    }

    /**
     * Get leave types for a specific department
     */
    public function getLeaveTypesForDepartment($departmentId, $useCache = true)
    {
        try {
            $currentFY = FinancialYearHelper::getCurrentFinancialYear();
            if (!$currentFY) {
                throw new \Exception('No current financial year found');
            }

            $financialYearName = $currentFY->name;
            $cacheKey = "leave_types_dept_{$departmentId}_fy_{$financialYearName}";

            if ($useCache) {
                $cached = Cache::get($cacheKey);
                if ($cached) {
                    Log::info("Retrieved cached leave types for department {$departmentId}");
                    return $cached;
                }
            }

            // Fetch from API
            $allLeaveTypes = $this->fetchLeaveTypesFromAPI($financialYearName);
            
            // Filter by department
            $departmentLeaveTypes = collect($allLeaveTypes)->filter(function ($leaveType) use ($departmentId) {
                $assignedDepts = collect($leaveType['assigned_departments']);
                return $assignedDepts->pluck('api_department_id')->contains($departmentId);
            })->values()->toArray();

            // Cache the result
            Cache::put($cacheKey, $departmentLeaveTypes, $this->cacheTimeout);

            Log::info("Retrieved " . count($departmentLeaveTypes) . " leave types for department {$departmentId}");
            
            return $departmentLeaveTypes;

        } catch (\Exception $e) {
            Log::error("Error fetching leave types for department {$departmentId}: " . $e->getMessage());
            
            // Try to return cached data even if expired
            $cacheKey = "leave_types_dept_{$departmentId}_fy_{$financialYearName}";
            $cached = Cache::get($cacheKey);
            if ($cached) {
                Log::warning("Returning expired cached data due to API error");
                return $cached;
            }

            throw $e;
        }
    }

    /**
     * Fetch leave types from attendance API
     */
    public function fetchLeaveTypesFromAPI($financialYear = null)
    {
        try {
            // Use the payroll-specific endpoint with token authentication
            $url = $this->attendanceApiUrl . '/payroll/leave-types';
            
            $params = [];
            if ($financialYear) {
                $params['financial_year'] = $financialYear;
            }

            Log::info("Fetching leave types from API: {$url}", $params);

            $response = Http::timeout($this->apiTimeout)
                ->withHeaders([
                    'X-API-Token' => $this->apiToken,
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ])
                ->get($url, $params);

            if (!$response->successful()) {
                throw new \Exception("API request failed with status: " . $response->status() . " - " . $response->body());
            }

            $data = $response->json();

            if (!isset($data['success']) || !$data['success']) {
                throw new \Exception("API returned error: " . ($data['message'] ?? 'Unknown error'));
            }

            $leaveTypes = $data['data'] ?? [];
            
            // Store in cache table for persistence
            $this->cacheLeaveTypes($leaveTypes, $financialYear);

            Log::info("Successfully fetched " . count($leaveTypes) . " leave types from API");

            return $leaveTypes;

        } catch (\Exception $e) {
            Log::error("Failed to fetch leave types from API: " . $e->getMessage());
            
            // Try to return cached database data
            return $this->getCachedLeaveTypesFromDB($financialYear);
        }
    }

    /**
     * Cache leave types in database
     */
    private function cacheLeaveTypes($leaveTypes, $financialYear)
    {
        try {
            foreach ($leaveTypes as $leaveType) {
                CachedLeaveType::updateOrCreate(
                    ['attendance_leave_type_id' => $leaveType['id']],
                    [
                        'leave_type_name' => $leaveType['leave_type_name'],
                        'leave_type_code' => $leaveType['leave_type_code'],
                        'days_allowed' => $leaveType['days_allowed'],
                        'status' => $leaveType['status'],
                        'description' => $leaveType['description'],
                        'financial_year' => $leaveType['financial_year'],
                        'assigned_departments' => json_encode($leaveType['assigned_departments']),
                        'is_active' => $leaveType['status'] === 'Active',
                        'last_synced_at' => now(),
                    ]
                );
            }

            Log::info("Cached " . count($leaveTypes) . " leave types in database");

        } catch (\Exception $e) {
            Log::error("Failed to cache leave types in database: " . $e->getMessage());
        }
    }

    /**
     * Get cached leave types from database
     */
    private function getCachedLeaveTypesFromDB($financialYear = null)
    {
        try {
            $query = CachedLeaveType::where('is_active', true);
            
            if ($financialYear) {
                $query->where('financial_year', $financialYear);
            }

            $cachedTypes = $query->get();

            $leaveTypes = $cachedTypes->map(function ($cached) {
                return [
                    'id' => $cached->attendance_leave_type_id,
                    'leave_type_name' => $cached->leave_type_name,
                    'leave_type_code' => $cached->leave_type_code,
                    'days_allowed' => $cached->days_allowed,
                    'status' => $cached->status,
                    'description' => $cached->description,
                    'financial_year' => $cached->financial_year,
                    'assigned_departments' => json_decode($cached->assigned_departments, true),
                ];
            })->toArray();

            Log::info("Retrieved " . count($leaveTypes) . " cached leave types from database");

            return $leaveTypes;

        } catch (\Exception $e) {
            Log::error("Failed to get cached leave types from database: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Sync leave types with audit logging
     */
    public function syncLeaveTypes($financialYear = null, $syncType = 'manual')
    {
        $logEntry = LeaveTypeSyncLog::create([
            'sync_type' => $syncType,
            'financial_year' => $financialYear ?? FinancialYearHelper::getCurrentFinancialYear()->name,
            'started_at' => now(),
            'triggered_by' => auth()->id(),
            'status' => 'running',
        ]);

        try {
            $leaveTypes = $this->fetchLeaveTypesFromAPI($financialYear);
            
            $logEntry->update([
                'total_synced' => count($leaveTypes),
                'success_count' => count($leaveTypes),
                'error_count' => 0,
                'completed_at' => now(),
                'status' => 'completed',
                'sync_details' => [
                    'api_response_count' => count($leaveTypes),
                    'cached_count' => CachedLeaveType::count(),
                    'synced_at' => now()->toISOString(),
                ]
            ]);

            // Clear relevant cache
            $this->clearLeaveTypeCache($financialYear);

            Log::info("Leave type sync completed successfully", [
                'sync_id' => $logEntry->id,
                'total_synced' => count($leaveTypes)
            ]);

            return [
                'success' => true,
                'message' => 'Leave types synced successfully',
                'total_synced' => count($leaveTypes),
                'sync_id' => $logEntry->id
            ];

        } catch (\Exception $e) {
            $logEntry->update([
                'error_count' => 1,
                'errors' => json_encode([$e->getMessage()]),
                'completed_at' => now(),
                'status' => 'failed',
            ]);

            Log::error("Leave type sync failed", [
                'sync_id' => $logEntry->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to sync leave types: ' . $e->getMessage(),
                'sync_id' => $logEntry->id
            ];
        }
    }

    /**
     * Clear leave type cache
     */
    public function clearLeaveTypeCache($financialYear = null)
    {
        try {
            // For non-Redis cache drivers, we'll clear specific known cache keys
            $cacheKeys = [];
            
            if ($financialYear) {
                // Clear specific financial year cache
                $cacheKeys[] = "leave_types_fy_{$financialYear}";
            } else {
                // Clear all known cache patterns
                $helper = new \App\Helpers\FinancialYearHelper();
                $currentFY = $helper->getCurrentFinancialYear();
                if ($currentFY) {
                    $cacheKeys[] = "leave_types_fy_{$currentFY['name']}";
                }
                $cacheKeys[] = "leave_types_all";
            }
            
            // Add department-specific cache keys
            for ($deptId = 1; $deptId <= 20; $deptId++) {
                $cacheKeys[] = "leave_types_dept_{$deptId}";
                if ($financialYear) {
                    $cacheKeys[] = "leave_types_dept_{$deptId}_fy_{$financialYear}";
                } else if (isset($currentFY)) {
                    $cacheKeys[] = "leave_types_dept_{$deptId}_fy_{$currentFY['name']}";
                }
            }
            
            // Clear all cache keys
            foreach ($cacheKeys as $key) {
                Cache::forget($key);
            }

            Log::info("Cleared leave type cache", ['keys_cleared' => count($cacheKeys)]);

        } catch (\Exception $e) {
            Log::warning("Failed to clear leave type cache: " . $e->getMessage());
        }
    }

    /**
     * Get departments that have access to a specific leave type
     */
    public function getLeaveTypeDepartments($leaveTypeId)
    {
        try {
            $cached = CachedLeaveType::where('attendance_leave_type_id', $leaveTypeId)->first();
            
            if ($cached) {
                return json_decode($cached->assigned_departments, true);
            }

            // If not cached, fetch from API and cache
            $leaveTypes = $this->fetchLeaveTypesFromAPI();
            $leaveType = collect($leaveTypes)->firstWhere('id', $leaveTypeId);
            
            return $leaveType ? $leaveType['assigned_departments'] : [];

        } catch (\Exception $e) {
            Log::error("Error getting departments for leave type {$leaveTypeId}: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get all available leave types with department filtering
     */
    public function getAllLeaveTypes($departmentId = null, $financialYear = null)
    {
        try {
            $currentFY = $financialYear ?: FinancialYearHelper::getCurrentFinancialYear()->name;
            $leaveTypes = $this->fetchLeaveTypesFromAPI($currentFY);

            if ($departmentId) {
                $leaveTypes = collect($leaveTypes)->filter(function ($leaveType) use ($departmentId) {
                    $assignedDepts = collect($leaveType['assigned_departments']);
                    return $assignedDepts->pluck('id')->contains($departmentId);
                })->values()->toArray();
            }

            return $leaveTypes;

        } catch (\Exception $e) {
            Log::error("Error getting all leave types: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Test API connectivity
     */
    public function testAPIConnectivity()
    {
        try {
            $response = Http::timeout(10)->get($this->attendanceApiUrl . '/leave-types');
            
            return [
                'success' => $response->successful(),
                'status_code' => $response->status(),
                'response_time' => $response->transferStats->getTransferTime(),
                'message' => $response->successful() ? 'API is reachable' : 'API returned error: ' . $response->status()
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'API connection failed: ' . $e->getMessage()
            ];
        }
    }
}