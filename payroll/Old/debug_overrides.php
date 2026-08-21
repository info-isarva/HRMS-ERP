<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\EmployeeBasicDetail;
use App\Models\EmployeePayrollAttendance;
use App\Models\EmployeePayrollAttendanceSalaryComponentOverride;
use App\Models\HeldSalary;
use App\Models\SalaryComponent;

$empId = 4; // Based on previous logs and user context (Ashoka Kiran)
$month = 9;
$year = 2025;

echo "Debugging for Employee ID: $empId, Month: $month/$year\n";

// 1. Check Held Salary Status
$held = HeldSalary::where('employee_id', $empId)->orderBy('id', 'desc')->first();
if ($held) {
    echo "Held Salary Status: " . $held->status . "\n";
    echo "Held Remarks: " . $held->remarks . "\n";
} else {
    echo "No Held Salary Record found.\n";
}

// 2. Find Attendance Record
// First determine payout_month_id
$payoutStatus = \App\Models\EmployeePayrollAttendancePayoutMonthStatus::where('payout_month', $month)
    ->where('payout_year', $year)
    ->first();

if ($payoutStatus) {
    echo "Payout Month ID: " . $payoutStatus->id . "\n";
    
    $attendance = EmployeePayrollAttendance::where('payout_month_id', $payoutStatus->id)
        ->where('emp_id', $empId)
        ->first();

    if ($attendance) {
        echo "Attendance Record Found. ID: " . $attendance->id . "\n";
        echo "Gross Pay in Attendance: " . $attendance->gross_pay . "\n";
        echo "Total Earnings in Attendance: " . $attendance->totalEarnings . "\n";

        // 3. Check Overrides
        $overrides = EmployeePayrollAttendanceSalaryComponentOverride::where('emp_id', $empId)
            ->where('payroll_attendance_id', $attendance->id)
            ->get();
        
        echo "Found " . $overrides->count() . " Overrides:\n";
        foreach ($overrides as $override) {
            $comp = SalaryComponent::find($override->salary_component_id);
            echo " - Component ID: " . $override->salary_component_id . 
                 " (" . ($comp ? $comp->name : 'Unknown') . ")" .
                 " | Value: " . $override->override_value . 
                 " | Default: " . $override->default_value . "\n";
        }

    } else {
        echo "No Attendance Record found for Payout Month ID " . $payoutStatus->id . "\n";
    }

} else {
    echo "No Payout Month Status found for $month/$year\n";
}
