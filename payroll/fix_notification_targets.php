<?php

/**
 * Migration Script: Fix Manual Notification Targets
 * 
 * This script converts target_employees from employee_id strings (e.g., "ISIT100")
 * to employee table IDs (e.g., 11) for more reliable targeting.
 * 
 * Usage: php fix_notification_targets.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "===================================================\n";
echo "Fix Manual Notification Targets Script\n";
echo "===================================================\n\n";

try {
    // Get all manual notifications with employee targeting
    $notifications = DB::table('manual_notifications')
        ->where('target_type', 'specific_employees')
        ->whereNotNull('target_employees')
        ->get();
    
    echo "Found {$notifications->count()} notifications with employee targeting\n\n";
    
    $updated = 0;
    $skipped = 0;
    $errors = 0;
    
    foreach ($notifications as $notification) {
        $targetEmployees = json_decode($notification->target_employees, true);
        
        if (!is_array($targetEmployees) || empty($targetEmployees)) {
            echo "Notification ID {$notification->id}: Skipped (empty or invalid target_employees)\n";
            $skipped++;
            continue;
        }
        
        // Check if already using IDs (numeric values)
        $firstValue = $targetEmployees[0];
        if (is_numeric($firstValue)) {
            echo "Notification ID {$notification->id}: Skipped (already using employee IDs)\n";
            $skipped++;
            continue;
        }
        
        // Convert employee_id strings to employee table IDs
        $newTargetEmployees = [];
        foreach ($targetEmployees as $employeeId) {
            $employee = DB::table('employee_basic_details')
                ->where('employee_id', $employeeId)
                ->first();
            
            if ($employee) {
                $newTargetEmployees[] = $employee->id;
                echo "  - Converted '{$employeeId}' to ID {$employee->id} ({$employee->name})\n";
            } else {
                echo "  - WARNING: Could not find employee with employee_id '{$employeeId}'\n";
            }
        }
        
        if (!empty($newTargetEmployees)) {
            // Update the notification
            DB::table('manual_notifications')
                ->where('id', $notification->id)
                ->update([
                    'target_employees' => json_encode($newTargetEmployees),
                    'updated_at' => now()
                ]);
            
            echo "Notification ID {$notification->id} ('{$notification->title}'): Updated successfully\n";
            echo "  Old: " . json_encode($targetEmployees) . "\n";
            echo "  New: " . json_encode($newTargetEmployees) . "\n\n";
            $updated++;
        } else {
            echo "Notification ID {$notification->id}: Error - No valid employees found\n\n";
            $errors++;
        }
    }
    
    echo "\n===================================================\n";
    echo "Summary:\n";
    echo "  - Total notifications checked: {$notifications->count()}\n";
    echo "  - Updated: {$updated}\n";
    echo "  - Skipped: {$skipped}\n";
    echo "  - Errors: {$errors}\n";
    echo "===================================================\n";
    
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}

echo "\n✓ Migration completed successfully!\n";
