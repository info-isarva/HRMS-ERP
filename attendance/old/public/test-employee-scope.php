<?php

// Test the Employee model and its new scope for attendance
require_once '/home/hrmsdev.isarva.in/public_html/attendance/vendor/autoload.php';

use App\Models\Employee;
use Carbon\Carbon;

echo "<h2>Testing Employee Model for Bulk Attendance</h2>";

try {
    // Test the new scope
    $month = 8;
    $year = 2025;
    
    echo "<h3>Testing forAttendanceMonth scope for August 2025:</h3>";
    
    // Get employees for attendance
    $employees = Employee::active()->forAttendanceMonth($month, $year)->get();
    
    echo "<p>Found " . count($employees) . " active employees eligible for attendance in $month/$year</p>";
    
    foreach ($employees->take(5) as $employee) {
        echo "<div>";
        echo "<strong>Employee:</strong> {$employee->name} ({$employee->employee_id})<br>";
        echo "<strong>DOJ:</strong> " . ($employee->date_of_joining ? $employee->date_of_joining->format('Y-m-d') : 'N/A') . "<br>";
        echo "<strong>Resignation:</strong> " . ($employee->date_of_resignation ? $employee->date_of_resignation->format('Y-m-d') : 'N/A') . "<br>";
        echo "<strong>Status:</strong> {$employee->status}<br>";
        echo "<strong>Department:</strong> " . ($employee->department ? $employee->department->name : 'N/A') . "<br>";
        echo "</div><br>";
    }
    
    if (count($employees) > 5) {
        echo "<p>... and " . (count($employees) - 5) . " more employees</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
