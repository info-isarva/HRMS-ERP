<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DepartmentApiService
{
    protected $apiUrl;
    
    public function __construct()
    {
        $this->apiUrl = config('services.department_api.url', 'https://payrolldev.isarva.in/api/departments');
    }

    /**
     * Get all departments from API
     */
    public function getAllDepartments()
    {
        try {
            Log::info('Fetching departments from API', ['url' => $this->apiUrl]);
            
            $response = Http::timeout(30)->get($this->apiUrl);
            
            if ($response->successful()) {
                $data = $response->json();
                
                Log::debug('Department API Raw Response', ['response' => $data]);
                
                // Check if data is valid
                if (!is_array($data)) {
                    Log::warning('API returned invalid response format');
                    return $this->getDummyDepartments();
                }
                
                // Handle the standardized API response format
                if (isset($data['status']) && $data['status'] === 'success' && isset($data['data'])) {
                    $departments = is_array($data['data']) ? $data['data'] : [];
                    Log::info('Successfully fetched departments from API', [
                        'count' => count($departments)
                    ]);
                    return $departments;
                } else {
                    // Fallback to older format or direct data
                    $departments = $data['data'] ?? $data;
                    $departments = is_array($departments) ? $departments : [];
                    Log::info('Fetched departments using fallback format', [
                        'count' => count($departments)
                    ]);
                    return $departments;
                }
            }
            
            Log::error('Department API failed', [
                'status' => $response->status(),
                'response' => $response->body()
            ]);
            
            // Fallback to dummy data if API fails
            Log::warning('Falling back to dummy departments data due to API failure');
            return $this->getDummyDepartments();
            
        } catch (\Exception $e) {
            Log::error('Department API error: ' . $e->getMessage());
            Log::warning('Falling back to dummy departments data due to exception');
            return $this->getDummyDepartments();
        }
    }

    /**
     * Get dummy departments data
     */
    private function getDummyDepartments()
    {
        return [
            [
                'id' => 1,
                'name' => 'Development Department',
                'code' => 'DEV',
                'description' => 'Software Development Team',
                'is_active' => true
            ],
            [
                'id' => 2,
                'name' => 'Human Resources',
                'code' => 'HR',
                'description' => 'Human Resources Department',
                'is_active' => true
            ],
            [
                'id' => 3,
                'name' => 'Finance Department',
                'code' => 'FIN',
                'description' => 'Finance and Accounting',
                'is_active' => true
            ],
            [
                'id' => 4,
                'name' => 'Marketing Department',
                'code' => 'MKT',
                'description' => 'Marketing and Sales',
                'is_active' => true
            ],
            [
                'id' => 5,
                'name' => 'Operations Department',
                'code' => 'OPS',
                'description' => 'Operations and Logistics',
                'is_active' => true
            ],
            [
                'id' => 6,
                'name' => 'Quality Assurance',
                'code' => 'QA',
                'description' => 'Quality Assurance Team',
                'is_active' => true
            ],
            [
                'id' => 7,
                'name' => 'IT Support',
                'code' => 'IT',
                'description' => 'IT Support and Infrastructure',
                'is_active' => true
            ],
            [
                'id' => 8,
                'name' => 'Customer Support',
                'code' => 'CS',
                'description' => 'Customer Support and Service',
                'is_active' => true
            ]
        ];
    }

    /**
     * Sync departments with local database - only add new departments
     */
    public function syncDepartments()
    {
        $departments = $this->getAllDepartments();
        $synced = [];
        $added = 0;
        $skipped = 0;

        Log::info('Starting department sync', ['total_departments' => count($departments)]);

        foreach ($departments as $deptData) {
            try {
                // Check if department already exists by api_department_id or name
                $existsByApiId = \App\Models\Department::where('api_department_id', $deptData['id'])->exists();
                $existsByName = \App\Models\Department::where('name', $deptData['name'])->exists();
                
                if ($existsByApiId || $existsByName) {
                    // Skip existing departments - don't update
                    $skipped++;
                    Log::info('Skipped existing department', [
                        'api_id' => $deptData['id'],
                        'name' => $deptData['name']
                    ]);
                    continue;
                } else {
                    // Create new department
                    $department = \App\Models\Department::create([
                        'api_department_id' => $deptData['id'],
                        'name' => $deptData['name'],
                        'code' => $this->generateDepartmentCode($deptData['name']),
                        'description' => $deptData['description'] ?? 'Synced from payroll system',
                        'is_active' => $deptData['status'] === 'Active',
                    ]);
                    
                    $synced[] = $department;
                    $added++;
                    
                    Log::info('Added new department', [
                        'department_id' => $department->id,
                        'api_department_id' => $department->api_department_id,
                        'name' => $department->name,
                        'code' => $department->code
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Error syncing department: ' . $e->getMessage(), [
                    'department_data' => $deptData
                ]);
                // Continue with other departments
            }
        }

        Log::info('Department sync completed', [
            'added' => $added,
            'skipped' => $skipped
        ]);

        return $synced;
    }

    /**
     * Generate department code from name
     */
    private function generateDepartmentCode($name)
    {
        // Remove special characters and spaces, take first 3-5 characters
        $code = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $name), 0, 5));
        
        // Ensure minimum length of 2 characters
        if (strlen($code) < 2) {
            $code = strtoupper(substr($name, 0, 3));
        }
        
        return $code;
    }
}
