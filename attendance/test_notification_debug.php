<?php
/**
 * Quick test script to check email notification functionality
 * Run this with: php test_notification_debug.php
 */

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\LeaveApplication;
use App\Models\User;
use App\Models\Employee;
use App\Services\LeaveNotificationService;
use Illuminate\Support\Facades\Log;

echo "=== Email Notification Debug Test ===\n\n";

try {
    // Test 1: Check if we can find HR users
    echo "1. Testing HR user detection...\n";
    $hrUsers = User::where('role', 'admin')->get();
    echo "   Found " . $hrUsers->count() . " HR users (admin role)\n";
    foreach ($hrUsers as $hr) {
        echo "   - {$hr->name} ({$hr->email})\n";
    }
    echo "\n";
    
    // Test 2: Check employees table data
    echo "2. Testing employees table...\n";
    $employeesCount = Employee::count();
    echo "   Total employees: {$employeesCount}\n";
    
    $employeesWithManagers = Employee::whereNotNull('reporting_manager_payroll_id')->count();
    echo "   Employees with managers: {$employeesWithManagers}\n";
    
    // Show sample employee-manager relationships
    $sampleEmployees = Employee::whereNotNull('reporting_manager_payroll_id')
        ->with('reportingManager')
        ->take(3)
        ->get();
    
    echo "   Sample relationships:\n";
    foreach ($sampleEmployees as $emp) {
        $manager = $emp->reportingManager;
        echo "     - {$emp->name} ({$emp->email}) reports to: ";
        echo $manager ? "{$manager->name} ({$manager->email})" : "Manager not found";
        echo "\n";
    }
    echo "\n";
    
    // Test 3: Check leave applications
    echo "3. Testing leave applications...\n";
    $leavesCount = LeaveApplication::count();
    echo "   Total leave applications: {$leavesCount}\n";
    
    if ($leavesCount > 0) {
        $sampleLeave = LeaveApplication::with('user')->first();
        echo "   Sample leave: ID {$sampleLeave->id} by {$sampleLeave->user->name}\n";
        
        // Test the notification service
        echo "   Testing notification service...\n";
        $notificationService = new LeaveNotificationService();
        
        // Test getting HR users
        echo "   - Testing getHRUsers method...\n";
        $reflection = new ReflectionClass($notificationService);
        $method = $reflection->getMethod('getHRUsers');
        $method->setAccessible(true);
        $hrUsersFromService = $method->invoke($notificationService);
        echo "     Found {$hrUsersFromService->count()} HR users from service\n";
        
        // Test getting reporting manager
        echo "   - Testing getReportingManager method...\n";
        $method = $reflection->getMethod('getReportingManager');
        $method->setAccessible(true);
        $reportingManager = $method->invoke($notificationService, $sampleLeave);
        echo "     Reporting manager: " . ($reportingManager ? $reportingManager->name : "None found") . "\n";
        
    } else {
        echo "   No leave applications found to test with\n";
    }
    
    echo "\n=== Test Completed ===\n";
    
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}