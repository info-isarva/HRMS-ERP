<?php
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\EmployeePayrollAttendancePayoutMonthStatus;
use App\Models\EmployeePayrollAttendance;

$payoutMonth = EmployeePayrollAttendancePayoutMonthStatus::where([
    'payout_month' => 11,
    'payout_year' => 2025
])->first();

if ($payoutMonth) {
    echo "Payout Month ID: " . $payoutMonth->id . "\n";
    echo "Status: " . $payoutMonth->status . "\n\n";
    
    $attendances = EmployeePayrollAttendance::where('payout_month_id', $payoutMonth->id)->get();
    
    echo "Total Attendance Records: " . $attendances->count() . "\n\n";
    
    if ($attendances->count() > 0) {
        $first = $attendances->first();
        echo "First Record:\n";
        echo "Employee ID: " . $first->emp_id . "\n";
        echo "Gross Pay: " . ($first->gross_pay ?? 'NULL') . "\n";
        echo "Total Deduction: " . ($first->total_deduction ?? 'NULL') . "\n";
        echo "Total Payable: " . ($first->total_payable ?? 'NULL') . "\n";
        echo "Earnings (JSON): " . ($first->earnings ?? 'NULL') . "\n";
        echo "Deductions (JSON): " . ($first->deductions ?? 'NULL') . "\n";
    }
    
    // Calculate totals
    $totalGross = 0;
    $totalDeductions = 0;
    $totalPayable = 0;
    
    foreach ($attendances as $att) {
        $totalGross += $att->gross_pay ?? 0;
        $totalDeductions += $att->total_deduction ?? 0;
        $totalPayable += $att->total_payable ?? 0;
    }
    
    echo "\n=== TOTALS ===\n";
    echo "Total Gross Pay: ₹" . number_format($totalGross, 2) . "\n";
    echo "Total Deductions: ₹" . number_format($totalDeductions, 2) . "\n";
    echo "Total Payable: ₹" . number_format($totalPayable, 2) . "\n";
} else {
    echo "No payout month found for November 2025\n";
}
