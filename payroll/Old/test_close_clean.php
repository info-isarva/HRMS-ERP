<?php
require 'vendor/autoload.php';
require 'bootstrap/app.php';
$app = app();
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$fy = \App\Models\FinancialYear::where('is_closed', false)->where('end_date', '<', \Carbon\Carbon::now())->first();
if (!$fy) {
    echo "No FY to close";
    exit;
}

try {
    $service = app(\App\Services\FinancialYearService::class);
    $service->closeFinancialYear($fy);
    echo "Success";
} catch (\Exception $e) {
    echo "Exception: " . substr($e->getMessage(), 0, 1000) . "\n";
}
