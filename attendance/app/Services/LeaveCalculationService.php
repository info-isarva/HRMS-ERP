<?php

namespace App\Services;

use App\Models\PublicHoliday;
use App\Models\User;
use Carbon\Carbon;

class LeaveCalculationService
{
    /**
     * Calculate detailed leave days with public holiday, week-off, and half-day handling
     */
    public function calculateDetailedLeaveDays(
        $startDate, 
        $endDate, 
        array $halfDayDates = [], 
        User $user = null
    ): array {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $currentFinancialYear = $this->getCurrentFinancialYear();
        
        // Get public holidays for the financial year
        $publicHolidays = $this->getApplicablePublicHolidays($currentFinancialYear, $user);
        
        // Get employee's week-off configuration from payroll API
        $weekOffConfig = $this->getEmployeeWeekOffConfiguration($user);
        $weekOffDays = $weekOffConfig['week_off_days'] ?? [0, 6]; // Default to Sunday, Saturday
        
        $leaveDays = [];
        $totalDays = 0;
        $current = $start->copy();

        while ($current <= $end) {
            $currentDateStr = $current->toDateString();
            
            // Check if it's employee's week-off day (replaces generic isWeekend() check)
            $isWeekOffDay = in_array($current->dayOfWeek, $weekOffDays);
            
            if ($isWeekOffDay) {
                $current->addDay();
                continue;
            }
            
            // Check if it's a public holiday
            $isPublicHoliday = in_array($currentDateStr, $publicHolidays);
            
            // Check if it's specified as half day
            $dayType = 'full_day';
            $daysCount = 1.0;
            
            if (isset($halfDayDates[$currentDateStr])) {
                $dayType = $halfDayDates[$currentDateStr];
                $daysCount = 0.5;
            }
            
            // If it's a public holiday, exclude from calculation but keep in record
            $excludeFromCalculation = $isPublicHoliday;
            $notes = null;
            
            if ($isPublicHoliday) {
                $daysCount = 0;
                $notes = 'Public Holiday - Not counted in leave';
            }
            
            $leaveDays[] = [
                'leave_date' => $currentDateStr,
                'day_type' => $dayType,
                'days_count' => $daysCount,
                'is_public_holiday' => $isPublicHoliday,
                'is_week_off' => false, // This date is a working day (not week-off)
                'exclude_from_calculation' => $excludeFromCalculation,
                'notes' => $notes
            ];
            
            $totalDays += $daysCount;
            $current->addDay();
        }

        return [
            'leave_days' => $leaveDays,
            'total_days' => $totalDays,
            'breakdown' => $this->generateBreakdown($leaveDays)
        ];
    }
    
    /**
     * Get applicable public holidays for user
     */
    private function getApplicablePublicHolidays(string $financialYear, User $user = null): array
    {
        $query = PublicHoliday::where('financial_year', $financialYear)
            ->where('status', 'active');
            
        // If user is provided, filter by department-specific holidays
        if ($user && $user->department) {
            $query->where(function($q) use ($user) {
                // Include national holidays
                $q->where('is_national', true)
                  // Include department-specific holidays
                  ->orWhereHas('departments', function($dq) use ($user) {
                      $dq->where('department_id', $user->department_id);
                  });
            });
        }
        
        return $query->pluck('date')
            ->map(fn($date) => Carbon::parse($date)->toDateString())
            ->toArray();
    }
    
    /**
     * Generate breakdown summary
     */
    private function generateBreakdown(array $leaveDays): array
    {
        $breakdown = [
            'full_days' => 0,
            'half_days' => 0,
            'public_holidays' => 0,
            'week_off_days' => 0,
            'total_working_days' => 0
        ];
        
        foreach ($leaveDays as $day) {
            if ($day['is_public_holiday']) {
                $breakdown['public_holidays']++;
            } elseif ($day['is_week_off'] ?? false) {
                $breakdown['week_off_days']++;
            } elseif ($day['day_type'] === 'full_day') {
                $breakdown['full_days']++;
                $breakdown['total_working_days'] += 1;
            } else {
                $breakdown['half_days']++;
                $breakdown['total_working_days'] += 0.5;
            }
        }
        
        return $breakdown;
    }
    
    /**
     * Get employee's week-off configuration
     */
    private function getEmployeeWeekOffConfiguration(User $user = null): array
    {
        if (!$user) {
            return [
                'week_off_days' => [0, 6], // Default: Sunday, Saturday
                'source' => 'default_no_user'
            ];
        }
        
        try {
            $payrollLeaveService = new \App\Services\PayrollLeaveService();
            $weekOffConfig = $payrollLeaveService->getEmployeeWeekOffConfiguration($user);
            
            return $weekOffConfig;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error fetching week-off configuration in LeaveCalculationService', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            
            return [
                'week_off_days' => [0, 6], // Default: Sunday, Saturday
                'source' => 'default_error_fallback'
            ];
        }
    }
    
    /**
     * Calculate LOP (Loss of Pay) details for a leave request
     */
    public function calculateLOPDetails($leaveTypeId, $totalLeaveDays, User $user = null): array
    {
        $payrollService = new \App\Services\PayrollLeaveService();
        $balanceInfo = $payrollService->getLeaveTypeBalance($leaveTypeId, $user);
        
        $availableBalance = $balanceInfo['balance'];
        $lopDays = 0;
        $paidDays = $totalLeaveDays;
        $hasLop = false;
        
        if ($totalLeaveDays > $availableBalance) {
            $hasLop = true;
            $paidDays = max(0, $availableBalance);
            $lopDays = $totalLeaveDays - $paidDays;
        }
        
        return [
            'total_days' => $totalLeaveDays,
            'paid_days' => $paidDays,
            'lop_days' => $lopDays,
            'has_lop' => $hasLop,
            'available_balance' => $availableBalance
        ];
    }
    
    /**
     * Calculate detailed leave days with LOP information
     */
    public function calculateDetailedLeaveDaysWithLOP($startDate, $endDate, array $halfDayDates = [], $leaveTypeId = null, User $user = null): array
    {
        // First calculate normal leave days
        $leaveCalculation = $this->calculateDetailedLeaveDays($startDate, $endDate, $halfDayDates, $user);
        
        // If leave type is provided, calculate LOP
        if ($leaveTypeId && $user) {
            $lopDetails = $this->calculateLOPDetails($leaveTypeId, $leaveCalculation['total_days'], $user);
            
            // Merge LOP calculation into result
            $leaveCalculation['lop_calculation'] = $lopDetails;
            $leaveCalculation['has_lop'] = $lopDetails['has_lop'];
            $leaveCalculation['paid_days'] = $lopDetails['paid_days'];
            $leaveCalculation['lop_days'] = $lopDetails['lop_days'];
            $leaveCalculation['available_balance'] = $lopDetails['available_balance'];
        }
        
        return $leaveCalculation;
    }

    /**
     * Get current financial year
     */
    private function getCurrentFinancialYear(): string
    {
        $month = now()->month;
        $year = now()->year;
        return $month >= 4 ? "$year-" . ($year + 1) : ($year - 1) . "-$year";
    }
}