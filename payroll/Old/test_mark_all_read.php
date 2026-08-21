<?php
// Simple test to verify mark-all-read functionality
require __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\NotificationController;
use App\Models\User;

$app = require_once __DIR__ . '/bootstrap/app.php';

// Get first user for testing
$user = User::first();

if (!$user) {
    echo "No users found in database\n";
    exit(1);
}

echo "Testing mark-all-read for user: {$user->name} (ID: {$user->id})\n";

// Try to manually call the controller method with authentication context
try {
    // Create a fake request to simulate the HTTP context
    $request = app(\Illuminate\Http\Request::class);
    
    // Create controller instance
    $controller = new NotificationController();
    
    // Manually set the authenticated user
    Auth::guard()->setUser($user);
    
    // Call the method
    $response = $controller->markAllAsRead();
    
    echo "Response: " . $response->getContent() . "\n";
    echo "Test completed successfully!\n";
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}
