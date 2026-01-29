<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Services\ActivityLogService;

class DepartmentController extends Controller
{
    // Index - Manage Departments
    public function index()
    {
        $departments = Department::latest()->get();
        return view('masters.departments.index', compact('departments'));
    }    

    // Store Department
    public function store(Request $request)
    {
        $validated = $request->validate([
            'department' => 'required|string|max:255|unique:departments',
            'short_name' => 'nullable|string|max:50|unique:departments',
            'description' => 'nullable|string',
            'status' => 'required|boolean'
        ], [
            'department.unique' => 'A department with this name already exists.',
            'short_name.unique' => 'A department with this short name already exists.',
        ]);

        // Ensure short_name is in validated array even if not provided
        if (!isset($validated['short_name']) || empty($validated['short_name'])) {
            $validated['short_name'] = $this->generateShortName($validated['department']);
        }

        $department = Department::create($validated);

        // Send webhook to attendance system
        $this->sendDepartmentWebhook('create', [
            'id' => $department->id,
            'name' => $department->department,
            'code' => $department->short_name,
            'description' => $department->description,
            'is_active' => $department->status
        ]);

        // Log department creation
        ActivityLogService::log(
            'department_create',
            'Created department',
            "Created department: {$validated['department']}",
            [
                'department_name' => $validated['department'],
                'short_name' => $validated['short_name'],
                'description' => $validated['description'] ?? null,
                'status' => $validated['status']
            ]
        );

        return redirect()->route('form/department/manage')
            ->with('success', 'Department created successfully');
    }

    
    public function getById($id)
    {
        $department = Department::findOrFail($id);
        return response()->json($department);
    }

    // Update Department
    public function update(Request $request)
    {
        $department = Department::findOrFail($request->id);
        
        $validated = $request->validate([
            'department' => 'required|string|max:255|unique:departments,department,' . $department->id,
            'short_name' => 'nullable|string|max:50|unique:departments,short_name,' . $department->id,
            'description' => 'nullable|string',
            'status' => 'required|boolean'
        ], [
            'department.unique' => 'A department with this name already exists.',
            'short_name.unique' => 'A department with this short name already exists.',
        ]);

        // Ensure short_name is in validated array even if not provided
        if (!isset($validated['short_name']) || empty($validated['short_name'])) {
            $validated['short_name'] = $this->generateShortName($validated['department']);
        }

        $department->update($validated);

        // Send webhook to attendance system
        $this->sendDepartmentWebhook('update', [
            'id' => $department->id,
            'name' => $department->department,
            'code' => $department->short_name,
            'description' => $department->description,
            'is_active' => $department->status
        ]);

        // Log department update
        ActivityLogService::log(
            'department_update',
            'Updated department',
            "Updated department: {$validated['department']}",
            [
                'department_id' => $department->id,
                'department_name' => $validated['department'],
                'short_name' => $validated['short_name'],
                'description' => $validated['description'] ?? null,
                'status' => $validated['status']
            ]
        );

        return redirect()->route('form/department/manage')->with('success', 'Department updated successfully.');
    }

    // Delete Department
    public function destroy(Request $request)
    {
        $department = Department::findOrFail($request->id);
        
        // Send webhook to attendance system before deletion
        $this->sendDepartmentWebhook('delete', [
            'id' => $department->id
        ]);
        
        // Log department deletion
        ActivityLogService::log(
            'department_delete',
            'Deleted department',
            "Deleted department: {$department->department}",
            [
                'department_id' => $department->id,
                'department_name' => $department->department,
                'short_name' => $department->short_name ?? null,
                'description' => $department->description ?? null,
                'status' => $department->status
            ]
        );
        
        $department->delete();
        return redirect()->route('form/department/manage')
            ->with('success', 'Department deleted successfully');
    }

    /**
     * Generate a short name from department name
     * Uses the same logic as the attendance system
     */
    private function generateShortName($departmentName)
    {
        // Remove non-alphanumeric characters and take first 5 characters, convert to uppercase
        return strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $departmentName), 0, 5));
    }

    /**
     * Send webhook to attendance system for department changes
     */
    private function sendDepartmentWebhook($action, $departmentData)
    {
        try {
            $webhookUrl = config('services.attendance.webhook_url') ?: 'http://attendance.isarva.in/api/department-sync/webhook';
            
            $payload = [
                'action' => $action,
                'department_data' => $departmentData,
                'timestamp' => now()->toISOString()
            ];

            $response = Http::timeout(10)->post($webhookUrl, $payload);

            if ($response->successful()) {
                Log::info("Department webhook sent successfully", [
                    'action' => $action,
                    'department_id' => $departmentData['id'] ?? null,
                    'response_status' => $response->status()
                ]);
            } else {
                Log::error("Department webhook failed", [
                    'action' => $action,
                    'department_id' => $departmentData['id'] ?? null,
                    'response_status' => $response->status(),
                    'response_body' => $response->body()
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Department webhook exception", [
                'action' => $action,
                'department_id' => $departmentData['id'] ?? null,
                'error' => $e->getMessage()
            ]);
        }
    }
}
