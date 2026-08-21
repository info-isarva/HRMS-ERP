<?php

$apiKey = '319d0q7p62jnwvme0ox5kyzeg3r81lrq';
$url = 'https://api.mytimestation.com/v1.2/reports/EmployeeActivity?report_startdate=2026-01-29&report_enddate=2026-01-29&exportformat=json';

$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'header' => "Authorization: Basic " . base64_encode("$apiKey:") . "\r\n" .
                    "User-Agent: PostmanRuntime/7.29.0\r\n" . 
                    "Accept: application/json\r\n",
        'ignore_errors' => true
    ],
    'ssl' => [
        'verify_peer' => false,
        'verify_peer_name' => false,
    ]
]);

echo "Attempting fetch...\n";
$response = file_get_contents($url, false, $context);

echo "Response Headers:\n";
print_r($http_response_header);

echo "\nResponse Body:\n";
echo $response;
