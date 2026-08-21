<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

echo "=================================================\n";
echo "Testing Attendance -> Payroll Notification API\n";
echo "=================================================\n\n";

// Get Abhishek's user from attendance database
$user = DB::table('users')
    ->where('email', 'abhishek@idaksh.in')
    ->first();

if (!$user) {
    echo "ERROR: User not found in attendance database\n";
    exit(1);
}

echo "Attendance User Details:\n";
echo "  - User ID: {$user->id}\n";
echo "  - Name: {$user->name}\n";
echo "  - Email: {$user->email}\n";
echo "  - Employee ID: {$user->employee_id}\n\n";

// Get payroll API URL
$payrollApiUrl = env('PAYROLL_API_BASE_URL', env('PAYROLL_API_URL', 'https://payrolldev.isarva.in/api'));
echo "Payroll API URL: {$payrollApiUrl}\n\n";

// Test the API call
echo "Making API call...\n";
try {
    $response = Http::timeout(10)->get($payrollApiUrl . '/notifications/user', [
        'user_id' => $user->id,
        'email' => $user->email,
        'employee_id' => $user->employee_id ?? null,
    ]);
    
    echo "Response Status: {$response->status()}\n";
    
    if ($response->successful()) {
        $data = $response->json();
        
        echo "Success: " . ($data['success'] ? 'true' : 'false') . "\n";
        
        if (isset($data['data']['notifications'])) {
            $notifications = $data['data']['notifications'];
            echo "Total Notifications: " . count($notifications) . "\n";
            echo "Unread Count: " . ($data['data']['unread_count'] ?? 0) . "\n\n";
            
            echo "Manual Notifications:\n";
            foreach ($notifications as $notif) {
                if ($notif['type'] === 'manual') {
                    echo "  - {$notif['title']} (ID: {$notif['id']}, Priority: {$notif['priority']}, Read: " . ($notif['is_read'] ? 'Yes' : 'No') . ")\n";
                }
            }
        } else {
            echo "No notifications data in response\n";
            echo "Response:\n";
            print_r($data);
        }
    } else {
        echo "API call failed!\n";
        echo "Response Body: " . $response->body() . "\n";
    }
    
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=================================================\n";
echo "Test completed!\n";
echo "=================================================\n";
