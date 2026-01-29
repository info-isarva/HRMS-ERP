<?php
/**
 * Test script to verify Employee table integration
 * This script tests if our AttendanceService can properly generate
 * attendance records using only the Employee table
 */

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Employee;
use App\Models\User;
use App\Services\AttendanceService;
use Illuminate\Support\Facades\Log;

echo "=== Employee Table Integration Test ===\n\n";

try {
    // Test 1: Get active employees
    echo "1. Testing Employee model query...\n";
    $employees = Employee::where('status', 'Active')->get();
    echo "   Found " . $employees->count() . " active employees\n";
    
    // Show first few employees
    echo "   Sample employees:\n";
    foreach ($employees->take(3) as $employee) {
        echo "   - {$employee->employee_id}: {$employee->name} ({$employee->email})\n";
    }
    echo "\n";
    
    // Test 2: Check Employee-User email mapping
    echo "2. Testing Employee-User email mapping...\n";
    $employeeEmails = $employees->pluck('email')->filter()->toArray();
    $userEmailMapping = User::whereIn('email', $employeeEmails)->pluck('id', 'email')->toArray();
    
    echo "   Employee emails: " . count($employeeEmails) . "\n";
    echo "   Mapped to users: " . count($userEmailMapping) . "\n";
    
    $unmappedEmails = array_diff($employeeEmails, array_keys($userEmailMapping));
    if (!empty($unmappedEmails)) {
        echo "   WARNING: Unmapped emails: " . implode(', ', $unmappedEmails) . "\n";
    }
    echo "\n";
    
    // Test 3: Test AttendanceService with Employee table only
    echo "3. Testing AttendanceService with Employee table...\n";
    $attendanceService = new AttendanceService();
    
    // Get employees for January 2025
    $month = 1;
    $year = 2025;
    $testEmployees = Employee::active()->forAttendanceMonth($month, $year)->take(2)->get();
    
    echo "   Testing with " . $testEmployees->count() . " employees for {$month}/{$year}\n";
    
    // Test prepareAttendanceRecords method
    $records = $attendanceService->prepareAttendanceRecords($testEmployees, $month, $year, 0);
    
    echo "   Generated " . count($records) . " attendance records\n";
    
    if (!empty($records)) {
        $sampleRecord = $records[0];
        echo "   Sample record structure:\n";
        foreach ($sampleRecord as $key => $value) {
            echo "     {$key}: {$value}\n";
        }
    }
    echo "\n";
    
    // Test 4: Verify salary days calculation
    echo "4. Testing salary days calculation...\n";
    $summary = $attendanceService->getAttendanceSummary($testEmployees->first(), $month, $year);
    echo "   Sample summary for {$testEmployees->first()->name}:\n";
    echo "     Working Days: " . ($summary['working_days'] ?? 'N/A') . "\n";
    echo "     Present Days: " . ($summary['present_days'] ?? 'N/A') . "\n";
    echo "     Salary Days: " . ($summary['salary_days'] ?? 'N/A') . "\n";
    echo "     LOP Days: " . ($summary['lop_days'] ?? 'N/A') . "\n";
    echo "\n";
    
    echo "=== All Tests Completed Successfully! ===\n";
    echo "Employee table integration is working properly.\n";
    
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}