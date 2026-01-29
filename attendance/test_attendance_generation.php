<?php
require_once __DIR__ . '/vendor/autoload.php';

use App\Services\AttendanceService;
use App\Services\PayrollApiService;
use Illuminate\Support\Facades\DB;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    echo "Testing attendance generation for October 2025...\n";
    
    $payrollApiService = new PayrollApiService();
    $attendanceService = new AttendanceService($payrollApiService);
    
    // Generate attendance for October 2025
    $batch = $attendanceService->generateAttendanceRecords(10, 2025, 1, false);
    
    echo "Batch created with ID: " . $batch->id . "\n";
    echo "Status: " . $batch->status . "\n";
    echo "Total records: " . $batch->total_records . "\n";
    echo "Processed records: " . $batch->processed_records . "\n";
    echo "Failed records: " . $batch->failed_records . "\n";
    
    // Check some specific records for Jayasheela
    $jayasheelaRecords = DB::table('attendance_records')
        ->where('employee_email', 'jayasheela@isarva.in')
        ->where('month', 10)
        ->where('year', 2025)
        ->whereIn('date', ['2025-10-02', '2025-10-09', '2025-10-17', '2025-10-20'])
        ->get();
        
    echo "\nJayasheela's records for problem dates:\n";
    foreach ($jayasheelaRecords as $record) {
        echo "Date: {$record->date}, Status: {$record->status}, Holiday ID: {$record->public_holiday_id}\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}