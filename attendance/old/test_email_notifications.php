<?php

// Test Email Notification Functionality
require_once __DIR__ . '/vendor/autoload.php';

use App\Models\StaticNotificationUser;
use App\Notifications\LeaveApplicationSubmitted;
use App\Models\LeaveApplication;
use App\Models\User;
use App\Models\LeaveType;

// Load Laravel Application
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing Email Notification System...\n\n";

try {
    // Test 1: Check if Brevo configuration is working
    echo "1. Checking Mail Configuration...\n";
    $mailConfig = config('mail');
    echo "Mail Driver: " . $mailConfig['default'] . "\n";
    echo "SMTP Host: " . $mailConfig['mailers']['smtp']['host'] . "\n";
    echo "SMTP Port: " . $mailConfig['mailers']['smtp']['port'] . "\n";
    echo "SMTP Username: " . $mailConfig['mailers']['smtp']['username'] . "\n";
    echo "✓ Mail configuration loaded successfully\n\n";
    
    // Test 2: Create static notification users
    echo "2. Testing Static Notification Users...\n";
    $hrUser = StaticNotificationUser::hr();
    $managerUser = StaticNotificationUser::reportingManager();
    echo "HR Email: " . $hrUser->email . "\n";
    echo "Manager Email: " . $managerUser->email . "\n";
    echo "✓ Static notification users created successfully\n\n";
    
    // Test 3: Get a sample leave application for testing
    echo "3. Finding Sample Leave Application...\n";
    $sampleLeave = LeaveApplication::with(['user', 'leaveType'])->first();
    
    if (!$sampleLeave) {
        echo "❌ No leave applications found in database for testing\n";
        echo "Creating a mock leave application data structure...\n";
        
        // Create mock data for testing
        $mockUser = new User([
            'name' => 'Test Employee',
            'email' => 'test@example.com',
            'employee_id' => 'EMP001'
        ]);
        
        $mockLeaveType = new LeaveType([
            'name' => 'Annual Leave',
            'days_count' => 21
        ]);
        
        $mockLeave = new LeaveApplication([
            'id' => 999,
            'start_date' => now()->addDays(7)->format('Y-m-d'),
            'end_date' => now()->addDays(9)->format('Y-m-d'),
            'total_days' => 3,
            'paid_days' => 3,
            'lop_days' => 0,
            'has_lop' => false,
            'reason' => 'Personal work and family function',
            'status' => 'pending',
            'emergency_contact_name' => 'John Doe',
            'emergency_contact_phone' => '+91 9876543210'
        ]);
        
        // Set relationships manually for testing
        $mockLeave->setRelation('user', $mockUser);
        $mockLeave->setRelation('leaveType', $mockLeaveType);
        
        $sampleLeave = $mockLeave;
        echo "✓ Mock leave application created for testing\n\n";
    } else {
        echo "✓ Sample leave application found: ID " . $sampleLeave->id . "\n";
        echo "Employee: " . $sampleLeave->user->name . "\n";
        echo "Leave Type: " . $sampleLeave->leaveType->name . "\n\n";
    }
    
    // Test 4: Send test email notifications
    echo "4. Sending Test Email Notifications...\n";
    
    // Test HR notification
    echo "Sending notification to HR (" . $hrUser->email . ")...\n";
    $hrUser->notify(new LeaveApplicationSubmitted($sampleLeave));
    echo "✓ HR notification sent successfully\n";
    
    // Test Manager notification
    echo "Sending notification to Manager (" . $managerUser->email . ")...\n";
    $managerUser->notify(new LeaveApplicationSubmitted($sampleLeave));
    echo "✓ Manager notification sent successfully\n\n";
    
    echo "🎉 Email notification test completed successfully!\n";
    echo "📧 Check the following email addresses for test notifications:\n";
    echo "   - HR: " . $hrUser->email . "\n";
    echo "   - Manager: " . $managerUser->email . "\n\n";
    
    echo "📝 Email Features Tested:\n";
    echo "   ✓ Brevo SMTP configuration\n";
    echo "   ✓ Static notification users\n";
    echo "   ✓ Leave application email template\n";
    echo "   ✓ Notification system integration\n";
    
} catch (Exception $e) {
    echo "❌ Error during email test: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "Email notification test completed.\n";
echo "Check your email inbox for test notifications.\n";
echo str_repeat("=", 50) . "\n";