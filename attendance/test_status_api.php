<?php
// Simple test to verify employee status is included in API response
$url = 'https://attendancedev.isarva.in/api/attendance-data?month=10&year=2025';

// You need to replace this with your actual JWT token
$jwtToken = 'YOUR_JWT_TOKEN_HERE';

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
echo "Response:\n";

if ($httpCode === 200) {
    $data = json_decode($response, true);

    if (isset($data['data']) && count($data['data']) > 0) {
        $firstEmployee = $data['data'][0];
        echo "First Employee Data Structure:\n";
        echo "- employee_id: " . ($firstEmployee['employee_id'] ?? 'N/A') . "\n";
        echo "- name: " . ($firstEmployee['name'] ?? 'N/A') . "\n";
        echo "- email: " . ($firstEmployee['email'] ?? 'N/A') . "\n";
        echo "- status: " . ($firstEmployee['status'] ?? 'NOT FOUND') . "\n";
        echo "- department: " . ($firstEmployee['department'] ?? 'N/A') . "\n";
        echo "- total_days: " . ($firstEmployee['total_days'] ?? 'N/A') . "\n";
        echo "- present_days: " . ($firstEmployee['present_days'] ?? 'N/A') . "\n";

        if (isset($firstEmployee['status'])) {
            echo "\n✅ SUCCESS: status field is now included in API response!\n";
            echo "Status value: '" . $firstEmployee['status'] . "'\n";
        } else {
            echo "\n❌ ISSUE: status field is still missing from API response!\n";
        }
    } else {
        echo "No employee data found in response\n";
    }
} else {
    echo $response . "\n";
}

?>