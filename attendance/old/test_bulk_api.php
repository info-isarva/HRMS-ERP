<?php
// Simple test script for bulk attendance API
require_once 'vendor/autoload.php';

// First, get JWT token by logging in
$loginData = [
    'email' => 'admin@example.com', // Replace with actual admin email
    'password' => 'password123' // Replace with actual password
];

$loginUrl = 'https://attendancedev.isarva.in/api/login';
$attendanceUrl = 'https://attendancedev.isarva.in/api/attendance-data?month=10&year=2025';

// Login to get JWT token
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $loginUrl);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($loginData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$loginResponse = curl_exec($ch);
$loginHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Login Response (HTTP $loginHttpCode):\n";
echo $loginResponse . "\n\n";

$loginData = json_decode($loginResponse, true);

if ($loginHttpCode === 200 && isset($loginData['token'])) {
    $jwtToken = $loginData['token'];
    echo "JWT Token obtained: " . substr($jwtToken, 0, 20) . "...\n\n";
    
    // Now test the attendance data API
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $attendanceUrl);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $jwtToken,
        'Accept: application/json'
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $attendanceResponse = curl_exec($ch);
    $attendanceHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "Attendance Data Response (HTTP $attendanceHttpCode):\n";
    echo $attendanceResponse . "\n";
    
} else {
    echo "Failed to get JWT token. Please check login credentials.\n";
}
?>