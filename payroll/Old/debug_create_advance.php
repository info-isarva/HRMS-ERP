<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\EmployeeAdvance;
use App\Models\EmployeeBasicDetail;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

echo "Simulating Advance Creation...\n";

// Mock Auth if needed (though created_by might differ)
// We'll just pick a random employee
$employee = EmployeeBasicDetail::first();
if (!$employee) {
    die("No employees found.\n");
}
echo "Using Employee: {$employee->name} ({$employee->id})\n";

DB::beginTransaction();

try {
    $startDate = Carbon::now()->startOfMonth()->addMonths(1);
    $endDate = $startDate->copy()->addMonths(5)->endOfMonth();
    
    $advance = EmployeeAdvance::create([
        'employee_id' => $employee->id,
        'advance_amount' => 10000,
        'tenure_months' => 6,
        'monthly_deduction' => 1666.66,
        'start_date' => $startDate,
        'end_date' => $endDate,
        'notes' => 'Test Advance from Script',
        'status' => 'active',
        'created_by' => 1, // Assuming admin ID 1
    ]);

    echo "Advance Created in memory. ID: " . $advance->id . "\n";
    
    DB::commit();
    echo "Transaction Committed.\n";
    
    // Verify Persistence
    $check = EmployeeAdvance::find($advance->id);
    if ($check) {
        echo "VERIFIED: Advance ID {$check->id} exists in DB.\n";
    } else {
        echo "FAILED: Advance ID {$advance->id} NOT found in DB after commit.\n";
    }

} catch (\Exception $e) {
    DB::rollback();
    echo "Error: " . $e->getMessage() . "\n";
}
