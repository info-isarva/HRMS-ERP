<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Facades\CauserResolver;
use Spatie\Activitylog\Models\Activity;

class LogUserActivity
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Only log for authenticated users
        if (Auth::check()) {
            $this->logActivity($request);
        }

        return $response;
    }

    private function logActivity(Request $request)
    {
        $user = Auth::user();
        $route = $request->route();
        $routeName = $route ? $route->getName() : 'unknown';
        $method = $request->method();
        $url = $request->fullUrl();
        $clientIp = $this->getClientIp($request);
        $serverIp = $this->getServerIp($request);
        $userAgent = $request->userAgent();
        
        // Don't log certain routes to avoid spam
        $excludedRoutes = [
            'activity-logs.index',
            'activity-logs.show',
            'logout',
            'heartbeat',
            'health-check'
        ];

        if (in_array($routeName, $excludedRoutes)) {
            return;
        }

        // Create activity log entry
        $properties = [
            'method' => $method,
            'url' => $url,
            'route_name' => $routeName,
            'request_data' => $this->getFilteredRequestData($request),
        ];

        // Only add IP addresses if they are valid
        if ($clientIp && $clientIp !== 'unknown' && $clientIp !== '127.0.0.1' && $clientIp !== '::1') {
            $properties['ip'] = $clientIp;
            $properties['client_ip'] = $clientIp;
        }

        if ($serverIp && $serverIp !== 'unknown' && $serverIp !== '127.0.0.1' && $serverIp !== '::1') {
            $properties['server_ip'] = $serverIp;
        }

        // Only add user agent if it's not empty
        if ($userAgent && !empty($userAgent)) {
            $properties['user_agent'] = $userAgent;
        }

        $description = $this->getActivityDescription($request, $routeName, $method);

        activity()
            ->causedBy($user)
            ->withProperties($properties)
            ->useLog('user_activity')
            ->log($description);
    }

    private function getClientIp(Request $request)
    {
        // Check for various headers that might contain the real client IP
        $ipHeaders = [
            'HTTP_CF_CONNECTING_IP',     // Cloudflare
            'HTTP_CLIENT_IP',            // Proxy
            'HTTP_X_FORWARDED_FOR',      // Load balancer/proxy
            'HTTP_X_FORWARDED',          // Proxy
            'HTTP_X_CLUSTER_CLIENT_IP',  // Cluster
            'HTTP_FORWARDED_FOR',        // Proxy
            'HTTP_FORWARDED',            // Proxy
            'REMOTE_ADDR'                // Standard
        ];

        foreach ($ipHeaders as $header) {
            $ip = $request->server($header);
            if (!empty($ip) && $ip !== 'unknown') {
                // Handle comma-separated IPs (X-Forwarded-For can contain multiple IPs)
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                
                // Validate IP address
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }

        // Fallback to request IP
        return $request->ip();
    }

    private function getServerIp(Request $request)
    {
        // Try to get server IP from various sources
        $serverIp = $request->server('SERVER_ADDR');
        
        if (empty($serverIp)) {
            // Try to get from local IP
            $serverIp = $request->server('LOCAL_ADDR');
        }
        
        if (empty($serverIp)) {
            // Try to get from hostname
            $hostname = $request->server('SERVER_NAME');
            if ($hostname) {
                $serverIp = gethostbyname($hostname);
            }
        }
        
        if (empty($serverIp) || $serverIp === $request->server('SERVER_NAME')) {
            // Fallback: try to get local machine IP
            $serverIp = $this->getLocalServerIp();
        }

        return $serverIp ?: 'unknown';
    }

    private function getLocalServerIp()
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

    private function getFilteredRequestData(Request $request)
    {
        $data = $request->all();
        
        // Remove sensitive data
        $sensitiveFields = ['password', 'password_confirmation', 'token', '_token', 'remember_token'];
        
        foreach ($sensitiveFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = '***FILTERED***';
            }
        }

        // Limit the size of request data
        if (strlen(json_encode($data)) > 1000) {
            return ['message' => 'Request data too large to log'];
        }

        return $data;
    }

    private function getActivityDescription(Request $request, $routeName, $method)
    {
        $action = '';
        
        switch ($method) {
            case 'GET':
                $action = 'viewed';
                break;
            case 'POST':
                $action = 'created/submitted';
                break;
            case 'PUT':
            case 'PATCH':
                $action = 'updated';
                break;
            case 'DELETE':
                $action = 'deleted';
                break;
            default:
                $action = 'accessed';
        }

        $resource = $this->getResourceFromRoute($routeName);
        
        return "User {$action} {$resource}";
    }

    private function getResourceFromRoute($routeName)
    {
        if (empty($routeName) || $routeName === 'unknown') {
            return 'unknown page';
        }

        // Extract resource from route name
        $parts = explode('.', $routeName);
        $resource = $parts[0] ?? 'page';
        
        return str_replace(['-', '_'], ' ', $resource);
    }
}
