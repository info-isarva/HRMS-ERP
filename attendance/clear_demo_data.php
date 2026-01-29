<?php

// Clear Demo Data - Leave Applications and Bulk Attendance
require_once __DIR__ . '/vendor/autoload.php';

// Load Laravel Application
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🧹 Clearing Demo Data...\n\n";

try {
    DB::beginTransaction();
    
    // 1. Clear Leave Applications and related data
    echo "1. Clearing Leave Applications...\n";
    
    // Get count before deletion
    $leaveAppsCount = DB::table('leave_applications')->count();
    $leaveDaysCount = DB::table('leave_application_days')->count();
    
    // Clear leave application days first (foreign key constraint)
    DB::table('leave_application_days')->truncate();
    echo "   ✓ Cleared {$leaveDaysCount} leave application days\n";
    
    // Clear leave applications
    DB::table('leave_applications')->truncate();
    echo "   ✓ Cleared {$leaveAppsCount} leave applications\n";
    
    // 2. Clear Bulk Attendance Data
    echo "\n2. Clearing Bulk Attendance Data...\n";
    
    // Check if bulk attendance tables exist
    $tables = [
        'bulk_attendance_records',
        'bulk_attendance_sessions', 
        'attendance_records',
        'employee_attendance'
    ];
    
    foreach ($tables as $table) {
        if (Schema::hasTable($table)) {
            $count = DB::table($table)->count();
            if ($count > 0) {
                DB::table($table)->truncate();
                echo "   ✓ Cleared {$count} records from {$table}\n";
            } else {
                echo "   - {$table} was already empty\n";
            }
        } else {
            echo "   - {$table} table does not exist\n";
        }
    }
    
    // 3. Clear Activity Logs related to leaves (optional)
    echo "\n3. Clearing Leave-related Activity Logs...\n";
    
    if (Schema::hasTable('activity_log')) {
        $activityCount = DB::table('activity_log')
            ->where('subject_type', 'App\\Models\\LeaveApplication')
            ->count();
            
        DB::table('activity_log')
            ->where('subject_type', 'App\\Models\\LeaveApplication')
            ->delete();
            
        echo "   ✓ Cleared {$activityCount} leave-related activity logs\n";
    }
    
    // 4. Reset Auto Increment IDs
    echo "\n4. Resetting Auto Increment IDs...\n";
    
    $resetTables = ['leave_applications', 'leave_application_days'];
    foreach ($resetTables as $table) {
        if (Schema::hasTable($table)) {
            DB::statement("ALTER TABLE {$table} AUTO_INCREMENT = 1");
            echo "   ✓ Reset auto increment for {$table}\n";
        }
    }
    
    DB::commit();
    
    echo "\n" . str_repeat("=", 50) . "\n";
    echo "✅ DEMO DATA CLEARED SUCCESSFULLY!\n";
    echo str_repeat("=", 50) . "\n\n";
    
    echo "📊 Summary:\n";
    echo "- Leave Applications: {$leaveAppsCount} cleared\n";
    echo "- Leave Application Days: {$leaveDaysCount} cleared\n";
    echo "- Activity Logs: {$activityCount} cleared\n";
    echo "- Bulk Attendance: Cleared from all related tables\n";
    echo "- Auto Increment IDs: Reset to 1\n\n";
    
    echo "🎯 Database is now clean and ready for fresh data!\n";
    
} catch (Exception $e) {
    DB::rollBack();
    echo "❌ Error clearing demo data: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "Demo data clearing completed.\n";
echo str_repeat("=", 50) . "\n";

?>