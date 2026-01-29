-- Clear Demo Data SQL Script
-- Run this in your database to clear all leave applications and bulk attendance data

-- Disable foreign key checks temporarily
SET FOREIGN_KEY_CHECKS = 0;

-- Clear Leave Application related tables
TRUNCATE TABLE leave_application_days;
TRUNCATE TABLE leave_applications;

-- Clear Bulk Attendance related tables (if they exist)
-- Note: Some tables might not exist in your setup
TRUNCATE TABLE bulk_attendance_records;
TRUNCATE TABLE bulk_attendance_sessions;
TRUNCATE TABLE attendance_records;
TRUNCATE TABLE employee_attendance;

-- Clear leave-related activity logs
DELETE FROM activity_log WHERE subject_type = 'App\\Models\\LeaveApplication';

-- Reset auto increment values
ALTER TABLE leave_applications AUTO_INCREMENT = 1;
ALTER TABLE leave_application_days AUTO_INCREMENT = 1;

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- Show results
SELECT 'Leave Applications Cleared' as Status;
SELECT COUNT(*) as remaining_leave_applications FROM leave_applications;
SELECT COUNT(*) as remaining_leave_days FROM leave_application_days;