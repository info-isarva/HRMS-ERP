<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

class AttendanceWebhookService
{
    protected $webhookUrl;
    protected $fyWebhookUrl;
    protected $timeout = 10; // 10 seconds timeout
    
    public function __construct()
    {
        $this->webhookUrl = Config::get('services.attendance.employee_webhook_url', 'https://attendancedev.isarva.in/api/employee-sync/webhook');
        $this->fyWebhookUrl = Config::get('services.attendance.financial_year_webhook_url', 'https://attendancedev.isarva.in/api/financial-year/sync');
    }

    /**
     * Send employee update webhook to attendance system
     */
    public function sendEmployeeUpdate($action, $employeeData)
    {
        try {
            $payload = [
                'action' => $action,
                'timestamp' => now()->toISOString(),
                'employee_data' => $employeeData
            ];

            Log::info("Sending employee {$action} webhook", [
                'employee_id' => $employeeData['employee_id'] ?? 'unknown',
                'name' => $employeeData['name'] ?? 'unknown',
                'action' => $action
            ]);

            $response = Http::timeout($this->timeout)
                ->post($this->webhookUrl, $payload);

            if ($response->successful()) {
                Log::info("Employee {$action} webhook sent successfully", [
                    'employee_id' => $employeeData['employee_id'] ?? 'unknown',
                    'response' => $response->json()
                ]);
                return true;
            } else {
                Log::warning("Employee {$action} webhook failed", [
                    'employee_id' => $employeeData['employee_id'] ?? 'unknown',
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                return false;
            }

        } catch (\Exception $e) {
            Log::error("Employee {$action} webhook error: " . $e->getMessage(), [
                'employee_id' => $user->employee_id ?? 'unknown',
                'exception' => $e
            ]);
            return false;
        }
    }

    /**
     * Prepare employee data for webhook payload
     */
    protected function prepareEmployeeData($user)
    {
        // Get department ID from department name
        $departmentId = $this->getDepartmentId($user->department);
        
        return [
            'id' => $user->id,
            'employee_id' => $user->employee_id,
            'name' => $user->name,
            'email' => $user->email,
            'designation' => $user->position,
            'department_id' => $departmentId,
            'date_of_joining' => $user->join_date ? $user->join_date : null,
            'date_of_resignation' => ($user->status === 'Active') ? 'Active' : $user->status,
            'phone' => $user->phone ?? null,
            'status' => $user->status ?? 'Active',
            'reporting_manager_payroll_id' => $this->getManagerPayrollId($user->line_manager),
            'financial_year' => $this->getCurrentFinancialYear(),
        ];
    }

    /**
     * Get department ID from department name
     */
    protected function getDepartmentId($departmentName)
    {
        if (empty($departmentName)) {
            return null;
        }

        // Map department names to IDs based on the departments table
        $departmentMap = [
            'HR' => 1,
            'Driver' => 2,
            'Operater' => 3,
            'Engineering' => 4,
            'Office' => 5,
            'Finance' => 6,
            'Civil' => 7,
            'Vehicle' => 8,
            'Housekeeping' => 9,
            'Store' => 10,
        ];

        return $departmentMap[$departmentName] ?? null;
    }

    /**
     * Get manager's payroll ID from manager name
     */
    protected function getManagerPayrollId($managerName)
    {
        if (empty($managerName)) {
            return null;
        }

        // Find manager by name and return their ID
        $manager = \App\Models\User::where('name', $managerName)->first();
        return $manager ? $manager->id : null;
    }

    /**
     * Get current financial year
     */
    protected function getCurrentFinancialYear()
    {
        // Use the financial year service for consistent logic
        $financialYearService = app(\App\Services\FinancialYearService::class);
        $currentFY = $financialYearService->getCurrentFinancialYear();
        
        return $currentFY ? $currentFY->name : $this->getFallbackFinancialYear();
    }

    /**
     * Fallback financial year calculation if no FY is set up
     */
    protected function getFallbackFinancialYear()
    {
        $settings = \App\Models\FinancialYearSetting::getSettings();
        $currentYear = date('Y');
        $currentMonth = date('n');
        
        // Use settings start month instead of hardcoded April
        if ($currentMonth >= $settings->start_month) {
            return $currentYear . '-' . ($currentYear + 1);
        } else {
            return ($currentYear - 1) . '-' . $currentYear;
        }
    }

    /**
     * Send generic webhook (for financial year updates, etc.)
     */
    public function sendWebhook($payload)
    {
        try {
            Log::info("Sending generic webhook", ['payload' => $payload]);

            $url = ($payload['action'] ?? '') === 'financial_year_update' 
                ? $this->fyWebhookUrl 
                : $this->webhookUrl;

            $response = Http::timeout($this->timeout)
                ->post($url, $payload);

            if ($response->successful()) {
                Log::info("Generic webhook sent successfully", [
                    'action' => $payload['action'] ?? 'unknown',
                    'response' => $response->json()
                ]);
                return true;
            } else {
                Log::warning("Generic webhook failed", [
                    'action' => $payload['action'] ?? 'unknown',
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                return false;
            }

        } catch (\Exception $e) {
            Log::error("Generic webhook error: " . $e->getMessage(), [
                'payload' => $payload,
                'exception' => $e
            ]);
            return false;
        }
    }

    /**
     * Send employee deletion webhook
     */
    public function sendEmployeeDelete($employeeId, $employeeName = null)
    {
        try {
            $payload = [
                'action' => 'delete',
                'timestamp' => now()->toISOString(),
                'employee_data' => [
                    'employee_id' => $employeeId,
                    'name' => $employeeName
                ]
            ];

            Log::info("Sending employee delete webhook", [
                'employee_id' => $employeeId,
                'name' => $employeeName
            ]);

            $response = Http::timeout($this->timeout)
                ->post($this->webhookUrl, $payload);

            if ($response->successful()) {
                Log::info("Employee delete webhook sent successfully", [
                    'employee_id' => $employeeId,
                    'response' => $response->json()
                ]);
                return true;
            } else {
                Log::warning("Employee delete webhook failed", [
                    'employee_id' => $employeeId,
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                return false;
            }

        } catch (\Exception $e) {
            Log::error("Employee delete webhook error: " . $e->getMessage(), [
                'employee_id' => $employeeId,
                'exception' => $e
            ]);
            return false;
        }
    }
}
