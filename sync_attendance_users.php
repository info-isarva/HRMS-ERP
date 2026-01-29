<?php
// First, ensure that this script can connect to both databases 

// Payroll database credentials
$payroll_host = '127.0.0.1';
$payroll_db = 'hrms_dev_demo_payroll';
$payroll_user = 'hrms_dev_demo_payroll_user';
$payroll_pass = 'vFFu9Aiv%@ysguDe';

// Attendance database credentials
$attendance_host = '127.0.0.1';
$attendance_db = 'hrms_dev_demo_attendance';
$attendance_user = 'hrms_dev_demo_attendance_user';
$attendance_pass = 'Fu9Aiv%@ysguDe';

// Connect to Payroll Database
try {
    $payroll_conn = new PDO("mysql:host=$payroll_host;dbname=$payroll_db", $payroll_user, $payroll_pass);
    $payroll_conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected successfully to Payroll database\n";
} catch(PDOException $e) {
    echo "Connection to Payroll database failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Connect to Attendance Database
try {
    $attendance_conn = new PDO("mysql:host=$attendance_host;dbname=$attendance_db", $attendance_user, $attendance_pass);
    $attendance_conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected successfully to Attendance database\n";
} catch(PDOException $e) {
    echo "Connection to Attendance database failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Get updated users from Payroll
try {
    $stmt = $payroll_conn->query("SELECT user_id, position, department, line_manager, role_name FROM users WHERE position IS NOT NULL OR department IS NOT NULL OR line_manager IS NOT NULL");
    $updated_users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Found " . count($updated_users) . " users with data to sync\n";
} catch(PDOException $e) {
    echo "Error fetching users from Payroll: " . $e->getMessage() . "\n";
    exit(1);
}

// Update users in Attendance database
$updated_count = 0;
$errors = 0;

try {
    $stmt = $attendance_conn->prepare("UPDATE users SET 
        position = :position, 
        department = :department, 
        line_manager = :line_manager, 
        role_name = :role_name,
        updated_at = NOW() 
        WHERE user_id = :user_id");
    
    foreach ($updated_users as $user) {
        try {
            $stmt->execute([
                ':position' => $user['position'],
                ':department' => $user['department'],
                ':line_manager' => $user['line_manager'],
                ':role_name' => $user['role_name'],
                ':user_id' => $user['user_id']
            ]);
            
            if ($stmt->rowCount() > 0) {
                $updated_count++;
                echo "Updated user with ID: {$user['user_id']}\n";
            }
        } catch(PDOException $e) {
            echo "Error updating user {$user['user_id']}: " . $e->getMessage() . "\n";
            $errors++;
        }
    }
    
    echo "\n=== SYNC COMPLETE ===\n";
    echo "Updated $updated_count users in Attendance database\n";
    if ($errors > 0) {
        echo "Encountered $errors errors during synchronization\n";
    }
    
} catch(PDOException $e) {
    echo "Error preparing update statement: " . $e->getMessage() . "\n";
    exit(1);
}

// Close connections
$payroll_conn = null;
$attendance_conn = null;
?>
