<?php

use App\Models\EmployeeBasicDetail;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$employee = EmployeeBasicDetail::where('employee_id', 'ISIT104')->first();

if (!$employee) {
    echo "Employee ISIT104 not found.\n";
    exit;
}

echo "Employee: " . $employee->name . " (ID: " . $employee->id . ")\n";

$exitDetails = $employee->exitDetails()->whereNull('deleted_at')->get();

echo "Exit Details Count: " . $exitDetails->count() . "\n";

foreach ($exitDetails as $detail) {
    echo "ID: " . $detail->id . "\n";
    echo "Status: '" . $detail->status . "'\n";
    echo "Exit Type: " . $detail->exit_type . "\n"; 
    echo "Last Working Day: " . $detail->last_working_day . "\n";
    echo "Deleted At: " . ($detail->deleted_at ?? 'NULL') . "\n";
}
