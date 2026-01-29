<?php

// Check current status of DRI-069 in both systems
echo "=== Employee Status Check ===\n";

// Check in payroll system
echo "\n1. Checking Payroll System...\n";
try {
    // Connect to payroll database directly
    $payrollDb = new PDO('mysql:host=localhost;dbname=hrms_payroll', 'root', '');
    $payrollDb->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $payrollDb->prepare("
        SELECT ebd.employee_id, ebd.first_name, ebd.last_name, ebd.status_id, es.status
        FROM employee_basic_details ebd
        LEFT JOIN employee_statuses es ON ebd.status_id = es.id
        WHERE ebd.employee_id = 'DRI-069'
    ");
    $stmt->execute();
    $payrollEmployee = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($payrollEmployee) {
        echo "✅ Found in payroll:\n";
        echo "   ID: " . $payrollEmployee['employee_id'] . "\n";
        echo "   Name: " . $payrollEmployee['first_name'] . ' ' . $payrollEmployee['last_name'] . "\n";
        echo "   Status ID: " . $payrollEmployee['status_id'] . "\n";
        echo "   Status: " . $payrollEmployee['status'] . "\n";
    } else {
        echo "❌ Employee DRI-069 not found in payroll\n";
    }
} catch (Exception $e) {
    echo "❌ Error checking payroll: " . $e->getMessage() . "\n";
}

// Check in attendance system
echo "\n2. Checking Attendance System...\n";
try {
    // Connect to attendance database directly
    $attendanceDb = new PDO('mysql:host=localhost;dbname=hrms_attendance', 'root', '');
    $attendanceDb->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $attendanceDb->prepare("
        SELECT employee_id, name, email, status, updated_at
        FROM employees
        WHERE employee_id = 'DRI-069'
    ");
    $stmt->execute();
    $attendanceEmployee = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($attendanceEmployee) {
        echo "✅ Found in attendance:\n";
        echo "   ID: " . $attendanceEmployee['employee_id'] . "\n";
        echo "   Name: " . $attendanceEmployee['name'] . "\n";
        echo "   Email: " . ($attendanceEmployee['email'] ?: 'null') . "\n";
        echo "   Status: " . $attendanceEmployee['status'] . "\n";
        echo "   Last Updated: " . $attendanceEmployee['updated_at'] . "\n";
    } else {
        echo "❌ Employee DRI-069 not found in attendance\n";
    }
} catch (Exception $e) {
    echo "❌ Error checking attendance: " . $e->getMessage() . "\n";
}

echo "\n=== Status Check Complete ===\n";
