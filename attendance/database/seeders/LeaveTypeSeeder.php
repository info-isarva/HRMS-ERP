<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\LeaveType;
use App\Models\Department;
use App\Services\DepartmentApiService;

class LeaveTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // First sync departments
        $departmentService = new DepartmentApiService();
        $departmentService->syncDepartments();
        
        // Get all departments
        $departments = Department::all();
        
        if ($departments->isEmpty()) {
            $this->command->warn('No departments found. Please sync departments first.');
            return;
        }
        
        // Create sample leave types
        $leaveTypes = [
            [
                'name' => 'Casual Leave',
                'code' => 'CL',
                'description' => 'Short-term leave for personal matters, emergencies, or urgent work.',
                'days_count' => 10,
                'financial_year' => '2025-2026',
                'is_active' => true,
            ],
            [
                'name' => 'Sick Leave',
                'code' => 'SL',
                'description' => 'Leave taken when an employee is unwell and cannot come to work.',
                'days_count' => 5,
                'financial_year' => '2025-2026',
                'is_active' => true,
            ],
            [
                'name' => 'Annual Leave',
                'code' => 'AL',
                'description' => 'Vacation leave for rest and recreation, planned in advance.',
                'days_count' => 21,
                'financial_year' => '2025-2026',
                'is_active' => true,
            ],
            [
                'name' => 'Maternity Leave',
                'code' => 'ML',
                'description' => 'Leave for new mothers before and after childbirth.',
                'days_count' => 180,
                'financial_year' => '2025-2026',
                'is_active' => true,
            ],
            [
                'name' => 'Paternity Leave',
                'code' => 'PL',
                'description' => 'Leave for new fathers to care for their newborn.',
                'days_count' => 15,
                'financial_year' => '2025-2026',
                'is_active' => true,
            ],
            [
                'name' => 'Study Leave',
                'code' => 'STL',
                'description' => 'Leave for educational purposes and professional development.',
                'days_count' => 7,
                'financial_year' => '2025-2026',
                'is_active' => true,
            ],
        ];
        
        foreach ($leaveTypes as $leaveTypeData) {
            // Check if leave type already exists
            $existingLeaveType = LeaveType::where('code', $leaveTypeData['code'])
                ->where('financial_year', $leaveTypeData['financial_year'])
                ->first();
                
            if (!$existingLeaveType) {
                $leaveType = LeaveType::create($leaveTypeData);
                
                // Assign to all departments for demonstration
                // In real scenario, different leave types might be for specific departments
                $departmentIds = $departments->pluck('id')->toArray();
                
                // For some leave types, assign to specific departments
                switch ($leaveTypeData['code']) {
                    case 'CL':
                    case 'SL':
                    case 'AL':
                        // Assign to all departments
                        $leaveType->departments()->attach($departmentIds);
                        break;
                    case 'ML':
                    case 'PL':
                        // Assign to all departments but these are gender-specific
                        $leaveType->departments()->attach($departmentIds);
                        break;
                    case 'STL':
                        // Assign to specific departments that need professional development
                        $devDept = $departments->where('code', 'DEV')->first();
                        $itDept = $departments->where('code', 'IT')->first();
                        $qaDept = $departments->where('code', 'QA')->first();
                        
                        $specificDepts = collect([$devDept, $itDept, $qaDept])->filter()->pluck('id')->toArray();
                        if (!empty($specificDepts)) {
                            $leaveType->departments()->attach($specificDepts);
                        } else {
                            // Fallback to all departments if specific ones not found
                            $leaveType->departments()->attach($departmentIds);
                        }
                        break;
                    default:
                        $leaveType->departments()->attach($departmentIds);
                        break;
                }
                
                $this->command->info("Created leave type: {$leaveType->name} ({$leaveType->code})");
            } else {
                $this->command->warn("Leave type {$leaveTypeData['name']} ({$leaveTypeData['code']}) already exists for {$leaveTypeData['financial_year']}");
            }
        }
        
        $this->command->info('Leave types seeding completed!');
    }
}
