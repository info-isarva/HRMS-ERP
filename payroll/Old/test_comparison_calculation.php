<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\EmployeePayrollAttendancePayoutMonthStatus;
use App\Models\EmployeePayrollAttendance;
use Carbon\Carbon;

echo "Testing Comparison Calculation for November 2025\n";
echo str_repeat("=", 60) . "\n";

$month = 11;
$year = 2025;

$payoutMonth = EmployeePayrollAttendancePayoutMonthStatus::where([
    'payout_month' => $month,
    'payout_year' => $year
])->first();

if (!$payoutMonth) {
    echo "Payout month not found!\n";
    exit;
}

echo "Payout Month ID: {$payoutMonth->id}\n";
echo "Status: {$payoutMonth->status}\n";
echo str_repeat("-", 60) . "\n";

// Get attendance records with relationships
$attendances = EmployeePayrollAttendance::with([
    'employee' => function($query) {
        $query->with([
            'salaryComponents' => function($q) {
                $q->withTrashed();
            },
            'statutoryComponents' => function($q) {
                $q->withTrashed();
            }
        ]);
    },
    'salaryOverrides',
    'statutoryOverrides'
])->where('payout_month_id', $payoutMonth->id)->get();

echo "Total Attendance Records: " . $attendances->count() . "\n";

if ($attendances->isEmpty()) {
    echo "No attendance records found!\n";
    exit;
}

// Get components
$earningComponents = \App\Models\SalaryComponent::where('type', 'earning')->where('status', '1')->get()
    ->merge(\App\Models\StatutoryComponent::where('type', 'earning')->where('status', '1')->get());

$deductionComponents = \App\Models\StatutoryComponent::where('type', 'deduction')->where('status', '1')->get()
    ->merge(\App\Models\SalaryComponent::where('type', 'deduction')->where('status', '1')->get());

echo "\nCalculating totals dynamically...\n";
echo str_repeat("-", 60) . "\n";

$totalGrossPay = 0;
$totalDeductions = 0;
$totalNetPayable = 0;
$processedEmployees = 0;

foreach ($attendances as $attendance) {
    $employee = $attendance->employee;
    if (!$employee) continue;

    $factor = $attendance->total_working_days > 0 
        ? $attendance->employee_worked_days / $attendance->total_working_days 
        : 0;

    // Create component maps
    $salaryComponentMap = [];
    $statutoryComponentMap = [];

    foreach ($employee->salaryComponents->whereNull('deleted_at') as $component) {
        $salaryComponentMap[$component->salary_component_id] = $component->value;
    }

    foreach ($employee->statutoryComponents->whereNull('deleted_at') as $component) {
        $statutoryComponentMap[$component->statutory_component_id] = $component->value;
    }

    // Calculate earnings
    $grossPay = 0;
    foreach ($earningComponents as $component) {
        $isApplicable = $component instanceof \App\Models\SalaryComponent
            ? array_key_exists($component->id, $salaryComponentMap)
            : array_key_exists($component->id, $statutoryComponentMap);
        
        if ($isApplicable) {
            $baseValue = $component instanceof \App\Models\SalaryComponent
                ? ($salaryComponentMap[$component->id] ?? 0)
                : ($statutoryComponentMap[$component->id] ?? 0);
            
            $value = round($baseValue * $factor);
            $grossPay += $value;
        }
    }

    // Calculate deductions
    $deductions = 0;
    $epfComponentIds = [1, 2, 4];
    
    // Calculate EPF wage
    $epfWage = 0;
    foreach ($earningComponents as $component) {
        if (in_array($component->id, $epfComponentIds)) {
            $isApplicable = $component instanceof \App\Models\SalaryComponent
                ? array_key_exists($component->id, $salaryComponentMap)
                : array_key_exists($component->id, $statutoryComponentMap);
            
            if ($isApplicable) {
                $baseValue = $component instanceof \App\Models\SalaryComponent
                    ? ($salaryComponentMap[$component->id] ?? 0)
                    : ($statutoryComponentMap[$component->id] ?? 0);
                
                $value = round($baseValue * $factor);
                $epfWage += $value;
            }
        }
    }

    foreach ($deductionComponents as $component) {
        $isApplicable = $component instanceof \App\Models\SalaryComponent
            ? array_key_exists($component->id, $salaryComponentMap)
            : array_key_exists($component->id, $statutoryComponentMap);
        
        if ($isApplicable) {
            $baseValue = $component instanceof \App\Models\SalaryComponent
                ? ($salaryComponentMap[$component->id] ?? 0)
                : ($statutoryComponentMap[$component->id] ?? 0);
            
            $value = 0;
            
            if ($component->id == 1) { // EPF
                $value = round(0.12 * $epfWage);
            } elseif ($component->id == 3) { // ESIC
                if ($grossPay <= 21000) {
                    $value = round(0.0075 * $grossPay);
                }
            } elseif ($component->id == 4) { // PT
                $value = ($grossPay >= 25000) ? 200 : 0;
            } else {
                $value = round($baseValue * $factor);
            }
            
            $deductions += $value;
        }
    }

    $netPayable = $grossPay - $deductions;
    
    $totalGrossPay += $grossPay;
    $totalDeductions += $deductions;
    $totalNetPayable += $netPayable;
    $processedEmployees++;

    if ($processedEmployees <= 3) {
        echo "\nEmployee: {$employee->name}\n";
        echo "  Worked Days: {$attendance->employee_worked_days}/{$attendance->total_working_days} (Factor: " . number_format($factor, 4) . ")\n";
        echo "  Gross Pay: ₹" . number_format($grossPay, 2) . "\n";
        echo "  Deductions: ₹" . number_format($deductions, 2) . "\n";
        echo "  Net Payable: ₹" . number_format($netPayable, 2) . "\n";
    }
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "TOTALS:\n";
echo "  Employees Processed: {$processedEmployees}\n";
echo "  Total Gross Pay: ₹" . number_format($totalGrossPay, 2) . "\n";
echo "  Total Deductions: ₹" . number_format($totalDeductions, 2) . "\n";
echo "  Total Net Payable: ₹" . number_format($totalNetPayable, 2) . "\n";
echo str_repeat("=", 60) . "\n";
