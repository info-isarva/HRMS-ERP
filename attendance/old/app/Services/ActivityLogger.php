<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Spatie\Activitylog\Models\Activity;

class ActivityLogger
{
    /**
     * Log a custom activity
     */
    public static function log($description, $logName = 'custom', $user = null, $properties = [])
    {
        if (!$user) {
            $user = Auth::user();
        }
        
        if (is_array($description)) {
            $properties = $description;
            $description = isset($properties['description']) ? $properties['description'] : 'Activity logged';
            unset($properties['description']);
        }

        $activityProperties = array_merge([
            'url' => Request::fullUrl(),
            'method' => Request::method(),
            'timestamp' => now()->toISOString(),
        ], (array)$properties);

        // Only add IP addresses if they are valid
        $clientIp = self::getClientIp();
        if ($clientIp && $clientIp !== 'unknown' && $clientIp !== '127.0.0.1' && $clientIp !== '::1') {
            $activityProperties['ip'] = $clientIp;
            $activityProperties['client_ip'] = $clientIp;
        }

        $serverIp = self::getServerIp();
        if ($serverIp && $serverIp !== 'unknown' && $serverIp !== '127.0.0.1' && $serverIp !== '::1') {
            $activityProperties['server_ip'] = $serverIp;
        }

        // Only add user agent if it's not empty
        $userAgent = Request::userAgent();
        if ($userAgent && !empty($userAgent)) {
            $activityProperties['user_agent'] = $userAgent;
        }

        return activity()
            ->causedBy($user)
            ->withProperties($activityProperties)
            ->useLog($logName)
            ->log($description);
    }

    /**
     * Get client IP address
     */
    private static function getClientIp()
    {
        $ipHeaders = [
            'HTTP_CF_CONNECTING_IP',
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];

        foreach ($ipHeaders as $header) {
            $ip = Request::server($header);
            if (!empty($ip) && $ip !== 'unknown') {
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }

        return Request::ip();
    }

    /**
     * Get server IP address
     */
    private static function getServerIp()
    {
        $serverIp = Request::server('SERVER_ADDR');
        
        if (empty($serverIp)) {
            $serverIp = Request::server('LOCAL_ADDR');
        }
        
        if (empty($serverIp)) {
            $hostname = Request::server('SERVER_NAME');
            if ($hostname) {
                $serverIp = gethostbyname($hostname);
            }
        }
        
        if (empty($serverIp) || $serverIp === Request::server('SERVER_NAME')) {
            $serverIp = self::getLocalServerIp();
        }

        return $serverIp ?: 'unknown';
    }

    /**
     * Get local server IP
     */
    private static function getLocalServerIp()
    {
        // Try multiple methods to get the local server IP
        
        // Method 1: Using socket connection
        try {
            $socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
            if ($socket) {
                socket_connect($socket, '8.8.8.8', 80);
                socket_getsockname($socket, $localIp);
                socket_close($socket);
                if (filter_var($localIp, FILTER_VALIDATE_IP)) {
                    return $localIp;
                }
            }
        } catch (Exception $e) {
            // Continue to next method
        }

        // Method 2: Using hostname -I command (Linux/Mac)
        try {
            $output = shell_exec('hostname -I 2>/dev/null');
            if ($output) {
                $ips = explode(' ', trim($output));
                foreach ($ips as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                        return $ip;
                    }
                }
                // If no public IP found, return the first private IP
                if (isset($ips[0]) && filter_var(trim($ips[0]), FILTER_VALIDATE_IP)) {
                    return trim($ips[0]);
                }
            }
        } catch (Exception $e) {
            // Continue to next method
        }

        // Method 3: Using ipconfig command (Windows)
        try {
            $output = shell_exec('ipconfig 2>/dev/null');
            if ($output) {
                if (preg_match('/IPv4 Address[.\s]*:\s*([0-9.]+)/', $output, $matches)) {
                    return $matches[1];
                }
            }
        } catch (Exception $e) {
            // Continue to next method
        }

        // Method 4: Using ifconfig command (Linux/Mac)
        try {
            $output = shell_exec('ifconfig 2>/dev/null');
            if ($output) {
                if (preg_match('/inet (?:addr:)?([0-9.]+)/', $output, $matches)) {
                    if ($matches[1] !== '127.0.0.1') {
                        return $matches[1];
                    }
                }
            }
        } catch (Exception $e) {
            // Continue to next method
        }

        // Method 5: Using curl to get external IP
        try {
            $externalIp = @file_get_contents('https://api.ipify.org');
            if ($externalIp && filter_var($externalIp, FILTER_VALIDATE_IP)) {
                return $externalIp;
            }
        } catch (Exception $e) {
            // Continue to next method
        }

        // Method 6: Try alternative external IP services
        $ipServices = [
            'https://icanhazip.com',
            'https://ipv4.icanhazip.com',
            'https://checkip.amazonaws.com',
            'https://ipinfo.io/ip'
        ];

        foreach ($ipServices as $service) {
            try {
                $externalIp = @file_get_contents($service);
                if ($externalIp) {
                    $externalIp = trim($externalIp);
                    if (filter_var($externalIp, FILTER_VALIDATE_IP)) {
                        return $externalIp;
                    }
                }
            } catch (Exception $e) {
                continue;
            }
        }

        return 'unknown';
    }

    /**
     * Log login activity
     */
    public static function logLogin($user, $method = 'email')
    {
        $properties = [
            'ip' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'login_method' => $method,
            'timestamp' => now()->toISOString(),
        ];

        return activity()
            ->causedBy($user)
            ->withProperties($properties)
            ->useLog('authentication')
            ->log('User logged in');
    }

    /**
     * Log logout activity
     */
    public static function logLogout($user)
    {
        $properties = [
            'ip' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'timestamp' => now()->toISOString(),
        ];

        return activity()
            ->causedBy($user)
            ->withProperties($properties)
            ->useLog('authentication')
            ->log('User logged out');
    }

    /**
     * Log failed login attempt
     */
    public static function logFailedLogin($email, $reason = 'Invalid credentials')
    {
        $properties = [
            'ip' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'attempted_email' => $email,
            'failure_reason' => $reason,
            'timestamp' => now()->toISOString(),
        ];

        return activity()
            ->withProperties($properties)
            ->useLog('authentication')
            ->log('Failed login attempt');
    }

    /**
     * Log sensitive action
     */
    public static function logSensitiveAction($description, $properties = [])
    {
        return self::log($description, $properties, 'sensitive');
    }

    /**
     * Log admin action
     */
    public static function logAdminAction($description, $properties = [])
    {
        return self::log($description, $properties, 'admin');
    }

    /**
     * Log data export
     */
    public static function logDataExport($exportType, $recordCount = null)
    {
        $properties = [
            'export_type' => $exportType,
            'record_count' => $recordCount,
            'timestamp' => now()->toISOString(),
        ];

        return self::log("Data export: {$exportType}", $properties, 'export');
    }

    /**
     * Log file upload
     */
    public static function logFileUpload($filename, $size, $type)
    {
        $properties = [
            'filename' => $filename,
            'file_size' => $size,
            'file_type' => $type,
            'timestamp' => now()->toISOString(),
        ];

        return self::log("File uploaded: {$filename}", $properties, 'file');
    }

    /**
     * Log permission change
     */
    public static function logPermissionChange($targetUser, $oldRole, $newRole)
    {
        $properties = [
            'target_user_id' => $targetUser->id,
            'target_user_email' => $targetUser->email,
            'old_role' => $oldRole,
            'new_role' => $newRole,
            'timestamp' => now()->toISOString(),
        ];

        return self::log("User role changed from {$oldRole} to {$newRole}", $properties, 'permission');
    }

    /**
     * Log system configuration change
     */
    public static function logConfigChange($configKey, $oldValue, $newValue)
    {
        $properties = [
            'config_key' => $configKey,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'timestamp' => now()->toISOString(),
        ];

        return self::log("System configuration changed: {$configKey}", $properties, 'config');
    }

    /**
     * Log batch operation
     */
    public static function logBatchOperation($operation, $recordCount, $details = [])
    {
        $properties = array_merge([
            'operation' => $operation,
            'record_count' => $recordCount,
            'timestamp' => now()->toISOString(),
        ], $details);

        return self::log("Batch operation: {$operation} ({$recordCount} records)", $properties, 'batch');
    }

    /**
     * Log security event
     */
    public static function logSecurityEvent($event, $severity = 'medium', $details = [])
    {
        $properties = array_merge([
            'security_event' => $event,
            'severity' => $severity,
            'timestamp' => now()->toISOString(),
        ], $details);

        return self::log("Security event: {$event}", $properties, 'security');
    }

    /**
     * Log user creation via sync
     */
    public static function logUserCreated($user, $source = 'manual')
    {
        $properties = [
            'user_id' => $user->id,
            'employee_id' => $user->employee_id,
            'payroll_id' => $user->payroll_id,
            'email' => $user->email,
            'role' => $user->role,
            'source' => $source,
            'timestamp' => now()->toISOString(),
        ];

        return self::log('user_management', "User created: {$user->name} ({$user->employee_id})", null, $properties);
    }

    /**
     * Log user update via sync
     */
    public static function logUserUpdated($user, $source = 'manual')
    {
        $properties = [
            'user_id' => $user->id,
            'employee_id' => $user->employee_id,
            'payroll_id' => $user->payroll_id,
            'email' => $user->email,
            'role' => $user->role,
            'source' => $source,
            'timestamp' => now()->toISOString(),
        ];

        return self::log('user_management', "User updated: {$user->name} ({$user->employee_id})", null, $properties);
    }

    /**
     * Log user deletion via sync
     */
    public static function logUserDeleted($user, $source = 'manual')
    {
        $properties = [
            'user_id' => $user->id,
            'employee_id' => $user->employee_id,
            'payroll_id' => $user->payroll_id,
            'email' => $user->email,
            'role' => $user->role,
            'source' => $source,
            'timestamp' => now()->toISOString(),
        ];

        return self::log('user_management', "User deleted: {$user->name} ({$user->employee_id})", null, $properties);
    }

    /**
     * Log password sync
     */
    public static function logPasswordSync($user)
    {
        $properties = [
            'user_id' => $user->id,
            'employee_id' => $user->employee_id,
            'payroll_id' => $user->payroll_id,
            'email' => $user->email,
            'source' => 'payroll_sync',
            'timestamp' => now()->toISOString(),
        ];

        return self::log('security', "Password synchronized for: {$user->name} ({$user->employee_id})", null, $properties);
    }
}
