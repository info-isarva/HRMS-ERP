<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\EmployeeAdvance;

echo "Checking recent advances...\n";

try {
    $advances = EmployeeAdvance::orderBy('created_at', 'desc')->take(5)->get();

    if ($advances->isEmpty()) {
        echo "No advances found.\n";
    } else {
        foreach ($advances as $advance) {
            echo "ID: {$advance->id} | EmpID: {$advance->employee_id} | Amount: {$advance->advance_amount} | Start: {$advance->start_date} | Status: {$advance->status} | Created: {$advance->created_at}\n";
        }
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
