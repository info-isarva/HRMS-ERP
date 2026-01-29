<?php
// Script to sync user data from payroll to attendance databases
// This script should be run from the server that has access to both databases

// Payroll database connection
$payrollHost = "127.0.0.1";
$payrollDb = "hrms_dev_demo_payroll";
$payrollUser = "hrms_dev_demo_payroll_user";
$payrollPass = "vFFu9Aiv%@ysguDe";

// Attendance database connection
$attendanceHost = "127.0.0.1";
$attendanceDb = "hrms_dev_demo_attendance";
$attendanceUser = "hrms_dev_demo_attendance_user";
$attendancePass = "Fu9Aiv%@ysguDe";

try {
    // Connect to payroll database
    $payrollConn = new PDO("mysql:host=$payrollHost;dbname=$payrollDb", $payrollUser, $payrollPass);
    $payrollConn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Connect to attendance database
    $attendanceConn = new PDO("mysql:host=$attendanceHost;dbname=$attendanceDb", $attendanceUser, $attendancePass);
    $attendanceConn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to both databases successfully.\n";
    
    // Get payroll users with position, department, line_manager data
    $stmt = $payrollConn->prepare("
        SELECT id, user_id, name, email, position, department, line_manager, role_name 
        FROM users 
        WHERE user_id IS NOT NULL 
        AND (position IS NOT NULL OR department IS NOT NULL OR line_manager IS NOT NULL OR role_name IS NOT NULL)
    ");
    $stmt->execute();
    $payrollUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Found " . count($payrollUsers) . " users in payroll with data to sync.\n\n";
    
    // Create log array
    $syncLog = [
        'total' => 0,
        'positions_updated' => 0,
        'departments_updated' => 0,
        'line_managers_updated' => 0,
        'roles_updated' => 0,
        'details' => []
    ];
    
    // Process each user
    foreach ($payrollUsers as $payrollUser) {
        echo "Processing user: {$payrollUser['name']} ({$payrollUser['user_id']})...\n";
        
        // Find matching user in attendance
        $stmt = $attendanceConn->prepare("SELECT * FROM users WHERE user_id = ?");
        $stmt->execute([$payrollUser['user_id']]);
        $attendanceUser = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$attendanceUser) {
            echo "  - User not found in attendance database. Skipping.\n";
            continue;
        }
        
        // Prepare update data
        $updates = [];
        $logEntry = [
            'user_id' => $attendanceUser['id'],
            'name' => $attendanceUser['name'],
            'old_position' => $attendanceUser['position'],
            'new_position' => $payrollUser['position'],
            'old_department' => $attendanceUser['department'],
            'new_department' => $payrollUser['department'],
            'old_line_manager' => $attendanceUser['line_manager'],
            'new_line_manager' => $payrollUser['line_manager'],
            'old_role' => $attendanceUser['role_name'],
            'new_role' => $payrollUser['role_name']
        ];
        
        $changed = false;
        
        // Check position
        if (($payrollUser['position'] !== null) && 
            ($attendanceUser['position'] === null || $attendanceUser['position'] !== $payrollUser['position'])) {
            $updates[] = "position = " . $attendanceConn->quote($payrollUser['position']);
            $syncLog['positions_updated']++;
            $changed = true;
        }
        
        // Check department
        if (($payrollUser['department'] !== null) && 
            ($attendanceUser['department'] === null || $attendanceUser['department'] !== $payrollUser['department'])) {
            $updates[] = "department = " . $attendanceConn->quote($payrollUser['department']);
            $syncLog['departments_updated']++;
            $changed = true;
        }
        
        // Check line_manager
        if (($payrollUser['line_manager'] !== null) && 
            ($attendanceUser['line_manager'] === null || $attendanceUser['line_manager'] !== $payrollUser['line_manager'])) {
            $updates[] = "line_manager = " . $attendanceConn->quote($payrollUser['line_manager']);
            $syncLog['line_managers_updated']++;
            $changed = true;
        }
        
        // Check role_name
        if (($payrollUser['role_name'] !== null) && 
            ($attendanceUser['role_name'] === null || $attendanceUser['role_name'] !== $payrollUser['role_name'])) {
            $updates[] = "role_name = " . $attendanceConn->quote($payrollUser['role_name']);
            $syncLog['roles_updated']++;
            $changed = true;
        }
        
        // Update if changes were found
        if ($changed) {
            $updates[] = "updated_at = NOW()";
            $sql = "UPDATE users SET " . implode(", ", $updates) . " WHERE id = " . $attendanceUser['id'];
            $attendanceConn->exec($sql);
            
            echo "  - User updated with new data.\n";
            $syncLog['total']++;
            
            if ($syncLog['total'] <= 20) {
                $syncLog['details'][] = $logEntry;
            }
        } else {
            echo "  - No changes needed for this user.\n";
        }
    }
    
    // Print summary
    echo "\n===== SYNC COMPLETE =====\n";
    echo "Total users updated: {$syncLog['total']}\n";
    echo "Positions updated: {$syncLog['positions_updated']}\n";
    echo "Departments updated: {$syncLog['departments_updated']}\n";
    echo "Line managers updated: {$syncLog['line_managers_updated']}\n";
    echo "Roles updated: {$syncLog['roles_updated']}\n";
    
    if (!empty($syncLog['details'])) {
        echo "\nDetailed changes (up to 20 users):\n";
        echo "--------------------------------\n";
        
        foreach ($syncLog['details'] as $detail) {
            echo "User: {$detail['name']} (ID: {$detail['user_id']})\n";
            
            if ($detail['old_position'] !== $detail['new_position']) {
                echo "  Position: {$detail['old_position']} → {$detail['new_position']}\n";
            }
            
            if ($detail['old_department'] !== $detail['new_department']) {
                echo "  Department: {$detail['old_department']} → {$detail['new_department']}\n";
            }
            
            if ($detail['old_line_manager'] !== $detail['new_line_manager']) {
                echo "  Line Manager: {$detail['old_line_manager']} → {$detail['new_line_manager']}\n";
            }
            
            if ($detail['old_role'] !== $detail['new_role']) {
                echo "  Role: {$detail['old_role']} → {$detail['new_role']}\n";
            }
            
            echo "\n";
        }
    }
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

// Close connections
$payrollConn = null;
$attendanceConn = null;
