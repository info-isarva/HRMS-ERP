<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Testing BiometricImportService with real data...\n\n";

$service = new \App\Services\BiometricImportService();
$filePath = storage_path('app/test_biometric_samples/zkteco_sample.dat');

echo "Importing file: $filePath\n";
$result = $service->import($filePath, 'zkteco');

echo "\nImport Results:\n";
echo "Success: " . ($result['success'] ? 'YES' : 'NO') . "\n";
echo "Processed: " . $result['processed'] . "\n";
echo "Imported: " . $result['imported'] . "\n";
echo "Updated: " . $result['updated'] . "\n";
echo "Errors: " . count($result['errors']) . "\n";

if (!empty($result['errors'])) {
    echo "\nError Details:\n";
    foreach($result['errors'] as $error) {
        print_r($error);
    }
}

echo "\nChecking saved attendance records...\n";
$attendances = \App\Models\Attendance::where('source', 'biometric_device')
    ->with('shift')
    ->orderBy('date', 'desc')
    ->orderBy('employee_payroll_id')
    ->limit(5)
    ->get();

echo "Found " . $attendances->count() . " biometric records\n\n";
foreach($attendances as $att) {
    echo "Employee: {$att->employee_payroll_id}, Date: {$att->date}\n";
    echo "  Shift: " . ($att->shift ? $att->shift->name : 'NULL') . " (ID: {$att->shift_id})\n";
    echo "  Scheduled: {$att->scheduled_start_time} - {$att->scheduled_end_time}\n";
    echo "  Actual: {$att->check_in_time} - {$att->check_out_time}\n";
    echo "  Late: {$att->late_arrival_minutes} min, OT: {$att->overtime_hours} hrs\n";
    echo "  Total Hours: {$att->total_hours}\n";
    echo "  Status: {$att->status}\n\n";
}
