<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ManualNotification;
use App\Models\User;
use Illuminate\Support\Facades\DB;

echo "=================================================\n";
echo "Testing Notification Targeting for Abhishek\n";
echo "=================================================\n\n";

// Get Abhishek's user
$user = User::where('name', 'ABHISHEK')->first();

if (!$user) {
    echo "ERROR: User 'ABHISHEK' not found!\n";
    exit(1);
}

echo "User Details:\n";
echo "  - User ID: {$user->id}\n";
echo "  - Name: {$user->name}\n";
echo "  - Employee ID field: {$user->employee_id}\n";

if ($user->employee) {
    echo "  - Employee relation exists: YES\n";
    echo "  - Employee table ID: {$user->employee->id}\n";
    echo "  - Employee code: {$user->employee->employee_id}\n";
    echo "  - Employee name: {$user->employee->name}\n";
} else {
    echo "  - Employee relation exists: NO\n";
}

echo "\n";

// Get the notification
$notification = ManualNotification::find(4);

if (!$notification) {
    echo "ERROR: Notification with ID 4 not found!\n";
    exit(1);
}

echo "Notification Details:\n";
echo "  - ID: {$notification->id}\n";
echo "  - Title: {$notification->title}\n";
echo "  - Target Type: {$notification->target_type}\n";
echo "  - Target Employees: " . json_encode($notification->target_employees) . "\n";
echo "  - Status: {$notification->status}\n";
echo "  - Show in Header: " . ($notification->show_in_header ? 'Yes' : 'No') . "\n";
echo "  - Start Date: {$notification->start_date}\n";

echo "\n";

// Test canUserView
echo "Testing canUserView():\n";
$canView = $notification->canUserView($user->id);
echo "  - Result: " . ($canView ? 'TRUE ✓' : 'FALSE ✗') . "\n";

echo "\n";

// Test the scope
echo "Testing ManualNotification::active()->forUser():\n";
$notifications = ManualNotification::active()
    ->forUser($user->id)
    ->get();

echo "  - Found {$notifications->count()} active notifications for user\n";

foreach ($notifications as $n) {
    echo "    * {$n->title} (ID: {$n->id})\n";
}

echo "\n";

// Test with show_in_header filter
echo "Testing with show_in_header filter:\n";
$notificationsWithHeader = ManualNotification::active()
    ->forUser($user->id)
    ->where('show_in_header', true)
    ->get();

echo "  - Found {$notificationsWithHeader->count()} notifications with show_in_header=true\n";

foreach ($notificationsWithHeader as $n) {
    echo "    * {$n->title} (ID: {$n->id})\n";
}

echo "\n";

// Debug the SQL query
echo "Debug: SQL Query for forUser scope:\n";
$query = ManualNotification::active()
    ->forUser($user->id)
    ->where('show_in_header', true);

echo "  - SQL: " . $query->toSql() . "\n";
echo "  - Bindings: " . json_encode($query->getBindings()) . "\n";

echo "\n";

// Test getTargetedUsers
echo "Testing getTargetedUsers():\n";
$targetedUsers = $notification->getTargetedUsers();
echo "  - Found {$targetedUsers->count()} targeted users\n";

foreach ($targetedUsers as $u) {
    echo "    * {$u->name} (User ID: {$u->id}, Employee ID: " . ($u->employee ? $u->employee->id : 'N/A') . ")\n";
}

echo "\n=================================================\n";
echo "Test completed!\n";
echo "=================================================\n";
