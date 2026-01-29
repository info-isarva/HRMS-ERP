<?php

// Check payroll database directly
chdir('/home/hrmsdev.isarva.in/public_html/payroll');

// Include Laravel's autoloader
require_once '/home/hrmsdev.isarva.in/public_html/payroll/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once '/home/hrmsdev.isarva.in/public_html/payroll/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Payroll Database Check ===\n";

try {
    // Check YATHEESHA's raw data in payroll
    $employee = \App\Models\EmployeeBasicDetail::where('employee_id', 'DRI-069')->first();
    
    if ($employee) {
        echo "YATHEESHA K V (DRI-069) raw data from payroll:\n";
        echo "  - Status (raw): {$employee->status}\n";
        echo "  - Name: {$employee->name}\n";
        echo "  - Email: " . ($employee->email ?: 'NULL') . "\n";
        echo "  - ID: {$employee->id}\n";
    }
    
    // Check employee_statuses table
    echo "\nEmployee statuses table:\n";
    $statuses = \App\Models\EmployeeStatus::active()->get();
    foreach ($statuses as $status) {
        echo "  - ID {$status->id}: '{$status->status_name}' ({$status->short_name})\n";
    }
    
    // Test the mapping
    echo "\nTesting status mapping:\n";
    $employeeStatuses = \App\Models\EmployeeStatus::active()->pluck('status_name', 'id')->toArray();
    if ($employee) {
        $statusName = $employeeStatuses[$employee->status] ?? 'Unknown';
        echo "  - Employee status ID {$employee->status} maps to: '{$statusName}'\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
