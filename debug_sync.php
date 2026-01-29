<?php
// Simulate the user creation process to debug the sync issue

// First, let's check the payroll sync method
echo "Testing the sync process step by step...\n";
echo "========================================\n\n";

// Test 1: Check if the attendance API is responding
$attendanceUrl = 'https://attendancedemo.isarva.in/api';
$apiToken = 'hrms_sync_token_2025_secure_key';

$headers = [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $apiToken,
    'Accept: application/json'
];

echo "1. Testing Attendance API connectivity...\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $attendanceUrl . '/users/verify-token');
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "   HTTP Status: $httpCode\n";
echo "   Response: $response\n\n";

// Test 2: Try to sync the existing mismatched user
echo "2. Attempting to sync the mismatched user (Sai Kiran)...\n";

$userData = [
    'user_id' => 'DRI--001',
    'name' => 'Sai Kiran',
    'email' => 'saikiran@isarva.in',
    'role_name' => 'Admin',
    'status' => 'Active',
    'department' => 'Human Resources',
    'designation' => 'Senior Developer',
    'phone' => '123456789'
];

// First, try to update the existing user
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $attendanceUrl . '/users/' . $userData['user_id'] . '/sync-simple');
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($userData));
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "   Update attempt - HTTP Status: $httpCode\n";
echo "   Response: $response\n\n";

if ($httpCode === 404) {
    echo "   User not found, trying to create/sync...\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $attendanceUrl . '/users/sync-simple');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($userData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    echo "   Create attempt - HTTP Status: $httpCode\n";
    echo "   Response: $response\n\n";
}

echo "3. Testing current user generation in attendance system...\n";
// Check what user_id would be generated if we create a user directly in attendance

echo "Debug complete.\n";
