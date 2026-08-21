<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Employee;

// Get first 3 employees
$employees = Employee::take(3)->get();

foreach ($employees as $employee) {
    $additionalData = $employee->additional_data ?? [];
    $additionalData['date_of_birth'] = '2025-10-17';
    $employee->additional_data = $additionalData;
    $employee->save();
    echo "Updated {$employee->name}\n";
}

echo "Done\n";