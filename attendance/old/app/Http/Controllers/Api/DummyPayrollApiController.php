<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DummyPayrollApiController extends Controller
{
    /**
     * Get employee leave balance from payroll system (DUMMY DATA)
     * This simulates the API that payroll software will provide
     */
    public function getEmployeeLeaveBalance(Request $request)
    {
        $user = Auth::user();
        $currentFinancialYear = active_fy_label();
        
        // DUMMY DATA - This will be replaced with real payroll API data
        $dummyLeaveBalances = $this->getDummyLeaveBalances($user, $currentFinancialYear);
        
        return response()->json([
            'success' => true,
            'data' => [
                'employee_id' => $user->id,
                'employee_payroll_id' => $user->payroll_id,
                'financial_year' => $currentFinancialYear,
                'leave_balances' => $dummyLeaveBalances
            ]
        ]);
    }
    
    /**
     * Generate actual leave balance data using real calculation
     * This calculates used days from approved leave applications and balances dynamically
     */
    private function getDummyLeaveBalances($user, $financialYear)
    {
        // Get all active leave types from the system
        $leaveTypes = \App\Models\LeaveType::where('financial_year', $financialYear)
            ->where('is_active', true)
            ->get();
            
        $leaveBalances = [];
        
        foreach ($leaveTypes as $leaveType) {
            // Calculate used days from attendance system leave applications
            // Use email_id to match employee and sum total_days from approved applications
            $usedDays = 0;
            if ($user && $user->email) {
                $usedDays = \App\Models\LeaveApplication::whereHas('user', function($query) use ($user) {
                        $query->where('email', $user->email);
                    })
                    ->where('leave_type_id', $leaveType->id)
                    ->where('financial_year', $financialYear)
                    ->where('status', 'approved')
                    ->sum('total_days') ?? 0;
            }
            
            // Use realistic allocated days (can be hardcoded or from a configuration)
            $allocatedDays = $this->getAllocatedDaysForLeaveType($leaveType->id, $user);
            $balance = max(0, $allocatedDays - $usedDays);
            
            $leaveBalances[] = [
                'leave_type_id' => $leaveType->id,
                'leave_type_name' => $leaveType->name,
                'leave_type_code' => $leaveType->code,
                'allocated_days' => $allocatedDays,
                'override_days' => null,
                'effective_days' => $allocatedDays, // Use allocated as effective for simplicity
                'used' => $usedDays, // Real calculation from approved applications
                'balance' => $balance, // Real calculation: allocated - used
                'is_active' => true,
                'is_manual_override' => false,
                'is_pro_rated' => false,
                'financial_year' => $financialYear
            ];
        }
        
        return $leaveBalances;
    }
    
    /**
     * Get allocated days for a specific leave type and user
     * This can be enhanced to fetch from payroll system or configuration
     */
    private function getAllocatedDaysForLeaveType($leaveTypeId, $user)
    {
        // Hardcoded allocation based on leave type
        // This should ideally come from payroll system or configuration
        $allocations = [
            1 => 15, // Casual Leave - 15 days 
            2 => 5,  // Sick Leave - 5 days
            3 => 21, // Earned Leave - 21 days
            4 => 10, // Any other leave types
        ];
        
        return $allocations[$leaveTypeId] ?? 10; // Default 10 days if not found
    }
    

}