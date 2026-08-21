<?php
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\EmployeePayrollAttendancePayoutMonthStatus;

$payoutMonth = EmployeePayrollAttendancePayoutMonthStatus::where([
    'payout_month' => 10,
    'payout_year' => 2025
])->first();

if ($payoutMonth) {
    echo "Status: " . $payoutMonth->status . "\n";
    echo "Status === 'finalized': " . ($payoutMonth->status === 'finalized' ? 'true' : 'false') . "\n";
    echo "Status === 'completed': " . ($payoutMonth->status === 'completed' ? 'true' : 'false') . "\n";
} else {
    echo "Payout month not found\n";
}
