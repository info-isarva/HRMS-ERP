<?php

// Test script to check specific employee status
// Change to the attendance directory
chdir('/home/hrmsdev.isarva.in/public_html/attendance');

// Include Laravel's autoloader
require_once '/home/hrmsdev.isarva.in/public_html/attendance/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once '/home/hrmsdev.isarva.in/public_html/attendance/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Employee Status Check ===\n";

try {
    // Check YATHEESHA's current status in attendance
    $employee = \App\Models\Employee::where('employee_id', 'DRI-069')->first();
    
    if ($employee) {
        echo "YATHEESHA K V (DRI-069) in attendance system:\n";
        echo "  - Status: '{$employee->status}'\n";
        echo "  - Email: " . ($employee->email ?: 'NULL') . "\n";
        echo "  - Payroll ID: " . ($employee->payroll_id ?: 'NULL') . "\n";
        echo "  - Updated at: {$employee->updated_at}\n";
    } else {
        echo "❌ YATHEESHA K V (DRI-069) not found in attendance system\n";
    }
    
    // Check what status payroll API returns for YATHEESHA
    echo "\nChecking payroll API for YATHEESHA...\n";
    $payrollService = app('App\Services\PayrollApiService');
    $employees = $payrollService->getEmployees();
    
    $yatheesha = collect($employees)->firstWhere('employee_id', 'DRI-069');
    if ($yatheesha) {
        echo "YATHEESHA K V (DRI-069) from payroll API:\n";
        echo "  - Status: '{$yatheesha['status']}'\n";
        echo "  - Status ID: " . ($yatheesha['status_id'] ?? 'N/A') . "\n";
        echo "  - Email: " . ($yatheesha['email'] ?: 'NULL') . "\n";
        echo "  - Payroll ID: {$yatheesha['payroll_id']}\n";
    } else {
        echo "❌ YATHEESHA K V (DRI-069) not found in payroll API\n";
    }
    
    // Check all unique statuses in attendance system
    echo "\nAll employee statuses in attendance system:\n";
    $statuses = \App\Models\Employee::distinct('status')->pluck('status')->filter()->toArray();
    foreach ($statuses as $status) {
        $count = \App\Models\Employee::where('status', $status)->count();
        echo "  - '$status': $count employees\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
