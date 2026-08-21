<?php
// Comprehensive test for improved bulk attendance API error handling
require_once 'vendor/autoload.php';

$baseUrl = 'https://attendancedev.isarva.in/api/attendance-data';
$jwtToken = 'YOUR_JWT_TOKEN_HERE'; // Replace with actual token

function testAPI($url, $jwtToken, $testName) {
    echo "\n=== Testing: $testName ===\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $jwtToken,
        'Accept: application/json'
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "HTTP Status: $httpCode\n";
    
    $data = json_decode($response, true);
    if ($data) {
        echo "Success: " . ($data['success'] ? 'true' : 'false') . "\n";
        if (isset($data['error'])) echo "Error: " . $data['error'] . "\n";
        if (isset($data['message'])) echo "Message: " . $data['message'] . "\n";
        if (isset($data['code'])) echo "Code: " . $data['code'] . "\n";
        if (isset($data['errors'])) {
            echo "Validation Errors:\n";
            foreach ($data['errors'] as $field => $messages) {
                echo "  $field: " . implode(', ', $messages) . "\n";
            }
        }
    } else {
        echo "Raw Response: $response\n";
    }
    echo "\n";
}

// Test Cases

// 1. Invalid month (string)
testAPI($baseUrl . '?month=invalid&year=2025', $jwtToken, 'Invalid month (string)');

// 2. Invalid month (out of range)
testAPI($baseUrl . '?month=13&year=2025', $jwtToken, 'Invalid month (out of range)');

// 3. Invalid year (string)  
testAPI($baseUrl . '?month=10&year=invalid', $jwtToken, 'Invalid year (string)');

// 4. Invalid year (too old)
testAPI($baseUrl . '?month=10&year=1999', $jwtToken, 'Invalid year (too old)');

// 5. Missing parameters
testAPI($baseUrl, $jwtToken, 'Missing parameters');

// 6. Non-existent month (no records)
testAPI($baseUrl . '?month=1&year=2020', $jwtToken, 'Non-existent attendance data');

// 7. Unlocked records (June 2025)
testAPI($baseUrl . '?month=6&year=2025', $jwtToken, 'Unlocked attendance records');

// 8. Valid locked records (October 2025)
testAPI($baseUrl . '?month=10&year=2025', $jwtToken, 'Valid locked records');

echo "=== Test completed ===\n";
?>