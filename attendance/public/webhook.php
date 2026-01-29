<?php

// Employee sync webhook endpoint
header('Content-Type: application/json');

/**
 * Get employee email with proper fallback
 */
function getEmployeeEmail($employeeData, $employeeId) {
    // Check direct email field
    if (isset($employeeData['email']) && !empty(trim($employeeData['email']))) {
        $email = trim($employeeData['email']);
        // Validate email format
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $email;
        }
    }
    
    // Check in additional_data if available
    if (isset($employeeData['additional_data']['email']) && !empty(trim($employeeData['additional_data']['email']))) {
        $email = trim($employeeData['additional_data']['email']);
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $email;
        }
    }
    
    // Return "No email provided" instead of placeholder
    return "No email provided";
}

/**
 * Get date of joining with proper formatting
 */
function getDateOfJoining($employeeData) {
    $dateFields = [
        'date_of_joining',
        'joining_date',
        'doj'
    ];
    
    foreach ($dateFields as $field) {
        // Check direct field
        if (isset($employeeData[$field]) && !empty($employeeData[$field])) {
            $date = formatDate($employeeData[$field]);
            if ($date) return $date;
        }
        
        // Check in additional_data
        if (isset($employeeData['additional_data'][$field]) && !empty($employeeData['additional_data'][$field])) {
            $date = formatDate($employeeData['additional_data'][$field]);
            if ($date) return $date;
        }
    }
    
    return null;
}

/**
 * Format date to MySQL compatible format
 */
function formatDate($dateString) {
    if (empty($dateString)) return null;
    
    try {
        // Handle various date formats
        $date = new DateTime($dateString);
        return $date->format('Y-m-d');
    } catch (Exception $e) {
        // Try parsing dd-mm-yyyy format (common in Indian systems)
        if (preg_match('/^(\d{1,2})-(\d{1,2})-(\d{4})$/', $dateString, $matches)) {
            $day = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
            $month = str_pad($matches[2], 2, '0', STR_PAD_LEFT);
            $year = $matches[3];
            return "$year-$month-$day";
        }
        
        error_log("Failed to parse date: " . $dateString);
        return null;
    }
}

try {
    // Get the raw POST data
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    // Validate data
    if (!$data || !isset($data['action']) || !isset($data['employee_data'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid webhook data']);
        exit;
    }
    
    // Log the webhook call
    error_log("Employee sync webhook received: " . json_encode($data));
    file_put_contents('/home/hrmsdev.isarva.in/public_html/attendance/webhook.log', 
        date('Y-m-d H:i:s') . " - Webhook received: " . json_encode($data) . "\n", 
        FILE_APPEND | LOCK_EX);
    
    // Bootstrap minimal Laravel for database access
    require_once __DIR__ . '/../vendor/autoload.php';
    
    // Load environment variables
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();
    
    // Setup database connection
    $capsule = new Illuminate\Database\Capsule\Manager();
    $capsule->addConnection([
        'driver' => 'mysql',
        'host' => $_ENV['DB_HOST'],
        'database' => $_ENV['DB_DATABASE'],
        'username' => $_ENV['DB_USERNAME'],
        'password' => $_ENV['DB_PASSWORD'],
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ]);
    $capsule->setAsGlobal();
    $capsule->bootEloquent();
    
    $action = $data['action'];
    $employeeData = $data['employee_data'];
    $employeeId = $employeeData['employee_id'] ?? null;
    
    if (!$employeeId) {
        http_response_code(400);
        echo json_encode(['error' => 'Employee ID is required']);
        exit;
    }
    
    // Process the sync
    $result = [];
    
    switch ($action) {
        case 'created':
        case 'updated':
        case 'update': // Handle both 'updated' and 'update' action formats
            // Find or create employee
            $employee = $capsule::table('employees')->where('employee_id', $employeeId)->first();
            
            // Map department ID
            $departmentId = null;
            if (isset($employeeData['department_id'])) {
                $department = $capsule::table('departments')
                    ->where('api_department_id', $employeeData['department_id'])
                    ->first();
                if ($department) {
                    $departmentId = $department->id;
                    file_put_contents('/home/hrmsdev.isarva.in/public_html/attendance/webhook.log', 
                        date('Y-m-d H:i:s') . " - Department mapping: " . $employeeData['department_id'] . " -> " . $departmentId . "\n", 
                        FILE_APPEND | LOCK_EX);
                } else {
                    file_put_contents('/home/hrmsdev.isarva.in/public_html/attendance/webhook.log', 
                        date('Y-m-d H:i:s') . " - Department mapping failed: " . $employeeData['department_id'] . " not found\n", 
                        FILE_APPEND | LOCK_EX);
                }
            }
            
            $updateData = [
                'name' => $employeeData['name'] ?? '',
                'email' => getEmployeeEmail($employeeData, $employeeId),
                'employee_id' => $employeeId,
                'payroll_id' => $employeeData['payroll_id'] ?? null,
                'department_id' => $departmentId,
                'status' => 'active',
                'financial_year' => $employeeData['financial_year'] ?? date('Y'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            // Add optional fields only if they exist
            if (isset($employeeData['designation']) && $employeeData['designation']) {
                $updateData['designation'] = $employeeData['designation'];
            }
            if (isset($employeeData['phone']) && $employeeData['phone']) {
                $updateData['phone'] = $employeeData['phone'];
            }
            
            // Handle date_of_joining - check multiple possible field names
            $dateOfJoining = getDateOfJoining($employeeData);
            if ($dateOfJoining) {
                $updateData['date_of_joining'] = $dateOfJoining;
            }
            
            if (isset($employeeData['date_of_resignation']) && $employeeData['date_of_resignation']) {
                $updateData['date_of_resignation'] = $employeeData['date_of_resignation'];
            }
            if (isset($employeeData['reporting_manager_payroll_id']) && $employeeData['reporting_manager_payroll_id']) {
                $updateData['reporting_manager_payroll_id'] = $employeeData['reporting_manager_payroll_id'];
            }
            
            if ($employee) {
                // Update existing employee
                $capsule::table('employees')
                    ->where('employee_id', $employeeId)
                    ->update($updateData);
                $result = ['action' => 'updated', 'employee_id' => $employeeId];
            } else {
                // Create new employee
                $updateData['created_at'] = date('Y-m-d H:i:s');
                $capsule::table('employees')->insert($updateData);
                $result = ['action' => 'created', 'employee_id' => $employeeId];
            }
            break;
            
        case 'deleted':
            // Mark employee as inactive
            $updated = $capsule::table('employees')
                ->where('employee_id', $employeeId)
                ->update([
                    'status' => 'inactive',
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            
            $result = [
                'action' => 'deactivated', 
                'employee_id' => $employeeId,
                'affected_rows' => $updated
            ];
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Unknown action: ' . $action]);
            exit;
    }
    
    // Log success
    error_log("Employee sync completed: " . json_encode($result));
    file_put_contents('/home/hrmsdev.isarva.in/public_html/attendance/webhook.log', 
        date('Y-m-d H:i:s') . " - Sync completed: " . json_encode($result) . "\n", 
        FILE_APPEND | LOCK_EX);
    
    // Return success response
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Employee sync processed successfully',
        'timestamp' => date('Y-m-d H:i:s'),
        'action' => $action,
        'result' => $result
    ]);
    
} catch (Exception $e) {
    error_log("Webhook error: " . $e->getMessage() . "\n" . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode([
        'error' => 'Internal server error', 
        'message' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}
