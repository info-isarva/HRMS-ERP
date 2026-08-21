<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

class ActivityLogService
{
    /**
     * Log activity with comprehensive details
     */
    public static function log($activityType, $module, $description, $oldData = null, $newData = null, $userId = null)
    {
        try {
            // Get user information
            $user = Auth::user();
            $sessionUserId = Session::get('user_id');
            $sessionUserName = Session::get('name');
            $sessionEmail = Session::get('email');
            $sessionRole = Session::get('role_name');
            $sessionPhone = Session::get('phone_number');

            // Use provided userId or get from session/auth
            $logUserId = $userId ?? $sessionUserId ?? ($user ? $user->user_id : null);
            $logUserName = $sessionUserName ?? ($user ? $user->name : 'System');
            $logEmail = $sessionEmail ?? ($user ? $user->email : null);
            $logRole = $sessionRole ?? ($user ? $user->role_name : null);
            $logPhone = $sessionPhone ?? ($user ? $user->phone_number : null);

            ActivityLog::create([
                'user_id' => $logUserId,
                'user_name' => $logUserName,
                'email' => $logEmail,
                'phone_number' => $logPhone,
                'role_name' => $logRole,
                'activity_type' => strtoupper($activityType),
                'module' => strtoupper($module),
                'description' => $description,
                'old_data' => $oldData,
                'new_data' => $newData,
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
                'session_id' => Session::getId(),
                'activity_timestamp' => now(),
            ]);

        } catch (\Exception $e) {
            // Log the error but don't break the application
            \Log::error('Failed to log activity: ' . $e->getMessage(), [
                'activity_type' => $activityType,
                'module' => $module,
                'description' => $description
            ]);
        }
    }

    /**
     * Log user login with detailed information
     */
    public static function logLogin($userId, $userName, $email, $additionalData = [])
    {
        // Get detailed browser and device information
        $userAgent = Request::userAgent();
        $ip = Request::ip();
        
        // Parse user agent for better details
        $browserInfo = self::parseUserAgent($userAgent);
        
        // Determine login method
        $loginMethod = $additionalData['login_method'] ?? 'Standard Login';
        
        // Prepare detailed login data
        $loginData = [
            'user_id' => $userId,
            'user_name' => $userName,
            'email' => $email,
            'ip_address' => $ip,
            'browser' => $browserInfo['browser'],
            'browser_version' => $browserInfo['browser_version'],
            'platform' => $browserInfo['platform'],
            'device_type' => $browserInfo['device_type'],
            'login_method' => $loginMethod,
            'session_id' => Session::getId(),
            'login_timestamp' => now()->toDateTimeString(),
            'user_agent_full' => $userAgent,
        ];
        
        // Add location info if available
        if (isset($additionalData['location'])) {
            $loginData['location'] = $additionalData['location'];
        }
        
        // Enhanced description with more details
        $description = "User {$userName} logged in successfully from {$browserInfo['browser']} on {$browserInfo['platform']}";
        if ($ip !== '127.0.0.1' && $ip !== '::1') {
            $description .= " (IP: {$ip})";
        }
        
        self::log('LOGIN', 'AUTHENTICATION', $description, null, $loginData, $userId);
    }

    /**
     * Log user logout with detailed information
     */
    public static function logLogout($userId, $userName)
    {
        // Get detailed browser and device information
        $userAgent = Request::userAgent();
        $ip = Request::ip();
        
        // Parse user agent for better details
        $browserInfo = self::parseUserAgent($userAgent);
        
        // Prepare detailed logout data
        $logoutData = [
            'user_id' => $userId,
            'user_name' => $userName,
            'ip_address' => $ip,
            'browser' => $browserInfo['browser'],
            'platform' => $browserInfo['platform'],
            'session_id' => Session::getId(),
            'logout_timestamp' => now()->toDateTimeString(),
        ];
        
        // Enhanced description
        $description = "User {$userName} logged out from {$browserInfo['browser']} on {$browserInfo['platform']}";
        if ($ip !== '127.0.0.1' && $ip !== '::1') {
            $description .= " (IP: {$ip})";
        }
        
        self::log('LOGOUT', 'AUTHENTICATION', $description, null, $logoutData, $userId);
    }

    /**
     * Log user creation
     */
    public static function logUserCreated($userData)
    {
        self::log('CREATE', 'USER_MANAGEMENT', "New user created: {$userData['name']}", null, $userData);
    }

    /**
     * Log user update
     */
    public static function logUserUpdated($userId, $oldData, $newData)
    {
        self::log('UPDATE', 'USER_MANAGEMENT', "User {$newData['name']} updated", $oldData, $newData);
    }

    /**
     * Log user deletion
     */
    public static function logUserDeleted($userData)
    {
        self::log('DELETE', 'USER_MANAGEMENT', "User {$userData['name']} deleted", $userData, null);
    }

    /**
     * Log employee creation
     */
    public static function logEmployeeCreated($employeeData)
    {
        $employeeName = $employeeData['basic']['name'] ?? $employeeData['name'] ?? 'Unknown Employee';
        self::log('CREATE', 'EMPLOYEE_MANAGEMENT', "New employee created: {$employeeName}", null, $employeeData);
    }

    /**
     * Log employee update
     */
    public static function logEmployeeUpdated($employeeId, $oldData, $newData)
    {
        $employeeName = $newData['basic']['name'] ?? $newData['name'] ?? 'Unknown Employee';
        self::log('UPDATE', 'EMPLOYEE_MANAGEMENT', "Employee {$employeeName} updated", $oldData, $newData);
    }

    /**
     * Log employee deletion
     */
    public static function logEmployeeDeleted($employeeData)
    {
        $employeeName = $employeeData['name'] ?? 'Unknown Employee';
        self::log('DELETE', 'EMPLOYEE_MANAGEMENT', "Employee {$employeeName} deleted", $employeeData, null);
    }

    /**
     * Log payroll processing
     */
    public static function logPayrollProcessed($payrollData)
    {
        self::log('PROCESS', 'PAYROLL', "Payroll processed for period: {$payrollData['period']}", null, $payrollData);
    }

    /**
     * Log password change
     */
    public static function logPasswordChanged($userId, $userName)
    {
        $userAgent = Request::userAgent();
        $ip = Request::ip();
        $browserInfo = self::parseUserAgent($userAgent);

        $passwordChangeData = [
            'user_id' => $userId,
            'user_name' => $userName,
            'action' => 'password_change',
            'ip_address' => $ip,
            'browser' => $browserInfo['browser'],
            'platform' => $browserInfo['platform'],
            'device_type' => $browserInfo['device_type'],
            'changed_timestamp' => now()->toDateTimeString(),
        ];

        $description = "Password changed for user {$userName} from {$browserInfo['browser']} on {$browserInfo['platform']}";
        if ($ip !== '127.0.0.1' && $ip !== '::1') {
            $description .= " (IP: {$ip})";
        }

        self::log('PASSWORD_CHANGE', 'AUTHENTICATION', $description, null, $passwordChangeData, $userId);
    }

    /**
     * Log system configuration changes
     */
    public static function logConfigurationChanged($configKey, $oldValue, $newValue)
    {
        self::log('UPDATE', 'SYSTEM_CONFIGURATION', "Configuration changed: {$configKey}", [
            'key' => $configKey,
            'value' => $oldValue
        ], [
            'key' => $configKey,
            'value' => $newValue
        ]);
    }

    /**
     * Log report generation
     */
    public static function logReportGenerated($reportType, $parameters = [])
    {
        self::log('GENERATE', 'REPORTS', "Report generated: {$reportType}", null, [
            'report_type' => $reportType,
            'parameters' => $parameters
        ]);
    }

    /**
     * Log file upload
     */
    public static function logFileUploaded($fileName, $fileType, $module)
    {
        self::log('UPLOAD', $module, "File uploaded: {$fileName}", null, [
            'file_name' => $fileName,
            'file_type' => $fileType
        ]);
    }

    /**
     * Log data export
     */
    public static function logDataExported($exportType, $recordCount)
    {
        self::log('EXPORT', 'DATA_EXPORT', "Data exported: {$exportType} ({$recordCount} records)", null, [
            'export_type' => $exportType,
            'record_count' => $recordCount
        ]);
    }

    /**
     * Log database sync operations
     */
    public static function logSyncOperation($syncType, $status, $details = [])
    {
        self::log('SYNC', 'DATABASE_SYNC', "Sync operation: {$syncType} - Status: {$status}", null, [
            'sync_type' => $syncType,
            'status' => $status,
            'details' => $details
        ]);
    }

    // ============================
    // PAYROLL ACTIVITY LOGGING
    // ============================

    /**
     * Log payroll creation/initialization
     */
    public static function logPayrollCreated($month, $year, $status, $details = [])
    {
        self::log('CREATE', 'PAYROLL', "Payroll created for ". Carbon::createFromDate($year, $month, 1)->format('M Y') ." with status: {$status}", null, [
            'month' => $month,
            'year' => $year,
            'status' => $status,
            'details' => $details
        ]);
    }

    /**
     * Log payroll status changes
     */
    public static function logPayrollStatusChanged($month, $year, $oldStatus, $newStatus, $details = [])
    {
        self::log('UPDATE', 'PAYROLL', "Payroll status changed for ". Carbon::createFromDate($year, $month, 1)->format('M Y')." from {$oldStatus} to {$newStatus}", [
            'month' => $month,
            'year' => $year,
            'status' => $oldStatus
        ], [
            'month' => $month,
            'year' => $year,
            'status' => $newStatus,
            'details' => $details
        ]);
    }

    /**
     * Log attendance save
     */
    public static function logAttendanceSaved($month, $year, $attendanceData)
    {
        self::log('UPDATE', 'PAYROLL_ATTENDANCE', "Attendance saved for ". Carbon::createFromDate($year, $month, 1)->format('M Y'), null, [
            
            'month' => $month,
            'year' => $year,
            //'employee_count' => count($attendanceData),
            'attendance_summary' => $attendanceData
        ]);
    }

    /**
     * Log individual employee attendance update
     */
    public static function logEmployeeAttendanceUpdated($employeeId, $employeeName, $month, $year, $oldData, $newData)
    {
        self::log('UPDATE', 'PAYROLL_ATTENDANCE', "Attendance updated for employee {$employeeName} ({$month}/{$year})", $oldData, $newData);
    }

    /**
     * Log payroll component override
     */
    public static function logComponentOverride($employeeId, $employeeName, $componentType, $componentId, $oldValue, $newValue, $month, $year)
    {
        self::log('OVERRIDE', 'PAYROLL_COMPONENT', "Component override for employee {$employeeName} - {$componentType} component", [
            'employee_id' => $employeeId,
            'component_type' => $componentType,
            'component_id' => $componentId,
            'old_value' => $oldValue,
            'month' => $month,
            'year' => $year
        ], [
            'employee_id' => $employeeId,
            'component_type' => $componentType,
            'component_id' => $componentId,
            'new_value' => $newValue,
            'month' => $month,
            'year' => $year
        ]);
    }

    /**
     * Log payroll component override (alternative signature)
     */
    public static function logPayrollComponentOverride($employeeName, $componentName, $componentType, $oldValue, $newValue, $details = [])
    {
        self::log('OVERRIDE', 'PAYROLL_COMPONENT', "Component override for employee {$employeeName} - {$componentName} ({$componentType})", [
            'employee_name' => $employeeName,
            'component_name' => $componentName,
            'component_type' => $componentType,
            'old_value' => $oldValue
        ], [
            'employee_name' => $employeeName,
            'component_name' => $componentName,
            'component_type' => $componentType,
            'new_value' => $newValue,
            'details' => $details
        ]);
    }

    /**
     * Log payroll finalization
     */
    public static function logPayrollFinalized($month, $year, $finalizationData = [])
    {
        self::log('FINALIZE', 'PAYROLL', "Payroll finalized for {$month}/{$year}", null, [
            'month' => $month,
            'year' => $year,
            'finalization_data' => $finalizationData,
            'finalized_at' => now()
        ]);
    }

    /**
     * Log payroll attendance save
     */
    public static function logPayrollAttendanceSave($month, $year, $attendanceData)
    {
        $monthYear = \Carbon\Carbon::createFromDate($year, $month, 1)->format('M Y');
        self::log('PAYROLL_ATTENDANCE_SAVE', 'PAYROLL', "Saved payroll attendance for {$monthYear}", null, $attendanceData);
    }

    /**
     * Log salary component override
     */
    public static function logSalaryComponentOverride($employeeName, $componentName, $overrideData)
    {
        self::log('SALARY_COMPONENT_OVERRIDE', 'PAYROLL', "Overrode {$componentName} for {$employeeName}", null, $overrideData);
    }

    /**
     * Log salary component management
     */
    public static function logSalaryComponentCreated($componentData)
    {
        self::log('CREATE', 'SALARY_COMPONENT_MANAGEMENT', "Created salary component: {$componentData['name']}", null, $componentData);
    }

    public static function logSalaryComponentUpdated($componentName, $oldData, $newData)
    {
        self::log('UPDATE', 'SALARY_COMPONENT_MANAGEMENT', "Updated salary component: {$componentName}", $oldData, $newData);
    }

    public static function logSalaryComponentDeleted($componentData)
    {
        self::log('DELETE', 'SALARY_COMPONENT_MANAGEMENT', "Deleted salary component: {$componentData['name']}", $componentData, null);
    }

    /**
     * Log employee advance management
     */
    public static function logEmployeeAdvanceCreated($employeeName, $advanceData)
    {
        self::log('CREATE', 'EMPLOYEE_ADVANCE_MANAGEMENT', "Created advance for {$employeeName}", null, $advanceData);
    }

    public static function logEmployeeAdvanceStatusUpdated($employeeName, $oldStatus, $newStatus, $advanceData)
    {
        self::log('UPDATE', 'EMPLOYEE_ADVANCE_MANAGEMENT', "Updated advance status for {$employeeName} from {$oldStatus} to {$newStatus}", ['status' => $oldStatus], $advanceData);
    }

    /**
     * Log OT and Holiday work management
     */
    public static function logOtHolidayWorkSaved($month, $year, $otHolidayData)
    {
        $monthYear = \Carbon\Carbon::createFromDate($year, $month, 1)->format('M Y');
        self::log('OT_HOLIDAY_SAVE', 'OVERTIME_HOLIDAY_MANAGEMENT', "Saved OT and Holiday work for {$monthYear}", null, $otHolidayData);
    }

    public static function logOtFinalized($month, $year, $otData)
    {
        $monthYear = \Carbon\Carbon::createFromDate($year, $month, 1)->format('M Y');
        self::log('OT_FINALIZE', 'OVERTIME_MANAGEMENT', "Finalized OT for {$monthYear}", null, $otData);
    }

    /**
     * Log Incentive management
     */
    public static function logIncentiveSaved($month, $year, $incentiveData)
    {
        $monthYear = \Carbon\Carbon::createFromDate($year, $month, 1)->format('M Y');
        self::log('INCENTIVE_SAVE', 'INCENTIVE_MANAGEMENT', "Saved and finalized incentives for {$monthYear}", null, $incentiveData);
    }

    public static function logIncentiveFinalized($month, $year, $incentiveData)
    {
        $monthYear = \Carbon\Carbon::createFromDate($year, $month, 1)->format('M Y');
        self::log('INCENTIVE_FINALIZE', 'INCENTIVE_MANAGEMENT', "Finalized incentives for {$monthYear}", null, $incentiveData);
    }

    /**
     * Log payroll report generation
     */
    public static function logPayrollReportGenerated($reportType, $month, $year, $parameters = [])
    {
        self::log('GENERATE', 'PAYROLL_REPORTS', "Payroll report generated: {$reportType} for {$month}/{$year}", null, [
            'report_type' => $reportType,
            'month' => $month,
            'year' => $year,
            'parameters' => $parameters,
            'generated_at' => now()
        ]);
    }

    /**
     * Log payroll data export
     */
    public static function logPayrollDataExported($exportType, $month, $year, $format, $recordCount = null)
    {
        self::log('EXPORT', 'PAYROLL_EXPORT', "Payroll data exported: {$exportType} for {$month}/{$year} in {$format} format", null, [
            'export_type' => $exportType,
            'month' => $month,
            'year' => $year,
            'format' => $format,
            'record_count' => $recordCount,
            'exported_at' => now()
        ]);
    }

    /**
     * Log salary component management
     */
    public static function logSalaryComponentChanged($employeeId, $employeeName, $componentName, $oldValue, $newValue)
    {
        self::log('UPDATE', 'SALARY_COMPONENT', "Salary component '{$componentName}' changed for employee {$employeeName}", [
            'employee_id' => $employeeId,
            'component_name' => $componentName,
            'old_value' => $oldValue
        ], [
            'employee_id' => $employeeId,
            'component_name' => $componentName,
            'new_value' => $newValue
        ]);
    }

    /**
     * Log statutory component management
     */
    public static function logStatutoryComponentChanged($employeeId, $employeeName, $componentName, $oldValue, $newValue)
    {
        self::log('UPDATE', 'STATUTORY_COMPONENT', "Statutory component '{$componentName}' changed for employee {$employeeName}", [
            'employee_id' => $employeeId,
            'component_name' => $componentName,
            'old_value' => $oldValue
        ], [
            'employee_id' => $employeeId,
            'component_name' => $componentName,
            'new_value' => $newValue
        ]);
    }

    /**
     * Log payroll calculation/recalculation
     */
    public static function logPayrollCalculated($employeeId, $employeeName, $month, $year, $calculationData)
    {
        self::log('CALCULATE', 'PAYROLL_CALCULATION', "Payroll calculated for employee {$employeeName} ({$month}/{$year})", null, [
            'employee_id' => $employeeId,
            'month' => $month,
            'year' => $year,
            'calculation_data' => $calculationData,
            'calculated_at' => now()
        ]);
    }

    /**
     * Log payslip generation
     */
    public static function logPayslipGenerated($employeeName, $employeeId, $month, $year, $payslipData = [])
    {
        self::log('GENERATE', 'PAYSLIP', "Payslip generated for employee {$employeeName} ({$month}/{$year})", null, [
            'employee_id' => $employeeId,
            'employee_name' => $employeeName,
            'month' => $month,
            'year' => $year,
            'payslip_data' => $payslipData,
            'generated_at' => now()
        ]);
    }

    /**
     * Log bank transfer file generation
     */
    public static function logBankTransferGenerated($month, $year, $fileType, $employeeCount, $totalAmount)
    {
        self::log('GENERATE', 'BANK_TRANSFER', "Bank transfer file generated for {$month}/{$year} ({$fileType})", null, [
            'month' => $month,
            'year' => $year,
            'file_type' => $fileType,
            'employee_count' => $employeeCount,
            'total_amount' => $totalAmount,
            'generated_at' => now()
        ]);
    }

    /**
     * Log EPF/ESI report generation
     */
    public static function logStatutoryReportGenerated($reportType, $month, $year, $employeeCount)
    {
        self::log('GENERATE', 'STATUTORY_REPORTS', "{$reportType} report generated for {$month}/{$year}", null, [
            'report_type' => $reportType,
            'month' => $month,
            'year' => $year,
            'employee_count' => $employeeCount,
            'generated_at' => now()
        ]);
    }

    /**
     * Parse user agent string to extract browser and platform information
     */
    private static function parseUserAgent($userAgent)
    {
        $browser = 'Unknown Browser';
        $browserVersion = 'Unknown Version';
        $platform = 'Unknown Platform';
        $deviceType = 'Desktop';

        // Detect platform/OS
        if (preg_match('/windows nt/i', $userAgent)) {
            $platform = 'Windows';
        } elseif (preg_match('/macintosh|mac os x/i', $userAgent)) {
            $platform = 'macOS';
        } elseif (preg_match('/linux/i', $userAgent)) {
            $platform = 'Linux';
        } elseif (preg_match('/android/i', $userAgent)) {
            $platform = 'Android';
            $deviceType = 'Mobile';
        } elseif (preg_match('/iphone|ipad|ipod/i', $userAgent)) {
            $platform = preg_match('/ipad/i', $userAgent) ? 'iPad' : 'iOS';
            $deviceType = preg_match('/ipad/i', $userAgent) ? 'Tablet' : 'Mobile';
        }

        // Detect browser
        if (preg_match('/firefox/i', $userAgent)) {
            $browser = 'Firefox';
            if (preg_match('/firefox\/([0-9\.]+)/i', $userAgent, $matches)) {
                $browserVersion = $matches[1];
            }
        } elseif (preg_match('/chrome/i', $userAgent) && !preg_match('/edg/i', $userAgent)) {
            $browser = 'Chrome';
            if (preg_match('/chrome\/([0-9\.]+)/i', $userAgent, $matches)) {
                $browserVersion = $matches[1];
            }
        } elseif (preg_match('/edg/i', $userAgent)) {
            $browser = 'Microsoft Edge';
            if (preg_match('/edg\/([0-9\.]+)/i', $userAgent, $matches)) {
                $browserVersion = $matches[1];
            }
        } elseif (preg_match('/safari/i', $userAgent) && !preg_match('/chrome/i', $userAgent)) {
            $browser = 'Safari';
            if (preg_match('/version\/([0-9\.]+)/i', $userAgent, $matches)) {
                $browserVersion = $matches[1];
            }
        } elseif (preg_match('/opera|opr/i', $userAgent)) {
            $browser = 'Opera';
            if (preg_match('/(?:opera|opr)\/([0-9\.]+)/i', $userAgent, $matches)) {
                $browserVersion = $matches[1];
            }
        } elseif (preg_match('/msie|trident/i', $userAgent)) {
            $browser = 'Internet Explorer';
            if (preg_match('/msie ([0-9\.]+)/i', $userAgent, $matches)) {
                $browserVersion = $matches[1];
            }
        }

        // Detect mobile devices more accurately
        if (preg_match('/mobile/i', $userAgent)) {
            $deviceType = 'Mobile';
        } elseif (preg_match('/tablet/i', $userAgent)) {
            $deviceType = 'Tablet';
        }

        return [
            'browser' => $browser,
            'browser_version' => $browserVersion,
            'platform' => $platform,
            'device_type' => $deviceType,
        ];
    }

    /**
     * Log failed login attempt
     */
    public static function logFailedLogin($email, $reason = 'Invalid credentials')
    {
        $userAgent = Request::userAgent();
        $ip = Request::ip();
        $browserInfo = self::parseUserAgent($userAgent);

        $failedLoginData = [
            'attempted_email' => $email,
            'failure_reason' => $reason,
            'ip_address' => $ip,
            'browser' => $browserInfo['browser'],
            'platform' => $browserInfo['platform'],
            'device_type' => $browserInfo['device_type'],
            'attempt_timestamp' => now()->toDateTimeString(),
            'user_agent_full' => $userAgent,
        ];

        $description = "Failed login attempt for email: {$email} from {$browserInfo['browser']} on {$browserInfo['platform']} (Reason: {$reason})";
        if ($ip !== '127.0.0.1' && $ip !== '::1') {
            $description .= " (IP: {$ip})";
        }

        self::log('FAILED_LOGIN', 'AUTHENTICATION', $description, null, $failedLoginData);
    }
}
