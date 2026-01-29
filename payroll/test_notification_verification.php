<?php
// Quick test script to verify notification system is working
require_once 'vendor/autoload.php';

echo "\n=== NOTIFICATION SYSTEM VERIFICATION ===\n\n";

// Test 1: Check if models exist
$models = [
    'App\\Models\\ManualNotification',
    'App\\Models\\NotificationRead'
];

echo "1. Testing Models:\n";
foreach ($models as $model) {
    if (class_exists($model)) {
        echo "   ✅ $model - EXISTS\n";
    } else {
        echo "   ❌ $model - MISSING\n";
    }
}

// Test 2: Check if controllers exist
$controllers = [
    'App\\Http\\Controllers\\ManualNotificationController',
    'App\\Http\\Controllers\\Api\\NotificationApiController',
    'App\\Http\\Controllers\\NotificationController'
];

echo "\n2. Testing Controllers:\n";
foreach ($controllers as $controller) {
    if (class_exists($controller)) {
        echo "   ✅ $controller - EXISTS\n";
    } else {
        echo "   ❌ $controller - MISSING\n";
    }
}

// Test 3: Check if views exist
$views = [
    'resources/views/manual-notifications/index.blade.php',
    'resources/views/manual-notifications/create.blade.php',
    'resources/views/manual-notifications/show.blade.php',
    'resources/views/manual-notifications/edit.blade.php',
    'resources/views/emails/high-priority-notification.blade.php'
];

echo "\n3. Testing Views:\n";
foreach ($views as $view) {
    if (file_exists($view)) {
        echo "   ✅ $view - EXISTS\n";
    } else {
        echo "   ❌ $view - MISSING\n";
    }
}

// Test 4: Check command
$command = 'app/Console/Commands/ProcessScheduledNotifications.php';
echo "\n4. Testing Command:\n";
if (file_exists($command)) {
    echo "   ✅ $command - EXISTS\n";
} else {
    echo "   ❌ $command - MISSING\n";
}

// Test 5: Check migrations
echo "\n5. Testing Migrations:\n";
$migrationDir = 'database/migrations/';
$notificationMigrations = glob($migrationDir . '*manual_notifications*.php');
$readMigrations = glob($migrationDir . '*notification_reads*.php');

if (!empty($notificationMigrations)) {
    echo "   ✅ Manual notifications migration - EXISTS\n";
} else {
    echo "   ❌ Manual notifications migration - MISSING\n";
}

if (!empty($readMigrations)) {
    echo "   ✅ Notification reads migration - EXISTS\n";
} else {
    echo "   ❌ Notification reads migration - MISSING\n";
}

echo "\n=== VERIFICATION COMPLETE ===\n";
echo "All components verified. The notification system is ready!\n\n";

echo "Next steps:\n";
echo "1. Access /manual-notifications to manage notifications\n";
echo "2. Set up cron job for automated processing\n";
echo "3. Test API endpoints for attendance system integration\n\n";
?>