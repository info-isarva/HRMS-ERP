<?php

// Test script to verify password sync from attendance to payroll
require_once 'vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

echo "Testing password sync from attendance to payroll...\n";

$payrollUrl = $_ENV['PAYROLL_SYNC_URL'] ?? 'https://payrolldev.isarva.in';
$syncToken = $_ENV['PAYROLL_SYNC_TOKEN'] ?? 'default-token';

echo "Payroll URL: " . $payrollUrl . "\n";
echo "Sync Token: " . substr($syncToken, 0, 10) . "...\n";

// Test data
$testData = [
    'user_email' => 'sup_admin@gmail.com',
    'new_password' => 'testpassword123',
    'sync_token' => $syncToken,
    'synced_from' => 'attendance',
    'synced_at' => date('c')
];

echo "Test data: " . json_encode($testData, JSON_PRETTY_PRINT) . "\n";

// Make the HTTP request
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $payrollUrl . '/api/sync/password/from-attendance');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($testData));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/x-www-form-urlencoded',
    'User-Agent: AttendanceSystem/1.0'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);

curl_close($ch);

echo "HTTP Code: " . $httpCode . "\n";
echo "Response: " . $response . "\n";

if ($error) {
    echo "Curl Error: " . $error . "\n";
}

?>