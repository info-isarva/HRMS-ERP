<?php

namespace App\Services;

use Carbon\Carbon;
use App\Helpers\FinancialYearHelper;
use Illuminate\Support\Facades\Log;

class LeaveProRatingCalculator
{
    /**
     * Calculate pro-rated leave allocations based on joining date
     * 
     * @param array $leaveTypes Array of leave types from attendance API
     * @param string $joiningDate Employee joining date
     * @param string|null $financialYear Financial year (optional, uses current if not provided)
     * @return array Pro-rated leave allocations
     */
    public function calculateProRatedLeaves($leaveTypes, $joiningDate, $financialYear = null)
    {
        try {
            $joiningDate = Carbon::parse($joiningDate);
            
            // Get financial year details
            $fyDetails = $this->getFinancialYearDetails($financialYear);
            if (!$fyDetails) {
                throw new \Exception('Unable to determine financial year details');
            }

            $fyStartDate = Carbon::parse($fyDetails['start_date']);
            $fyEndDate = Carbon::parse($fyDetails['end_date']);

            Log::info("Calculating pro-rated leaves", [
                'joining_date' => $joiningDate->toDateString(),
                'fy_start' => $fyStartDate->toDateString(),
                'fy_end' => $fyEndDate->toDateString(),
                'leave_types_count' => count($leaveTypes)
            ]);

            // Calculate pro-rating factor
            $proRatingData = $this->calculateProRatingFactor($joiningDate, $fyStartDate, $fyEndDate);

            $proRatedLeaves = [];

            foreach ($leaveTypes as $leaveType) {
                $originalDays = $leaveType['days_allowed'];
                $proRatedDays = $this->applyProRating($originalDays, $proRatingData);

                $proRatedLeaves[] = [
                    'id' => $leaveType['id'],
                    'leave_type_name' => $leaveType['leave_type_name'],
                    'leave_type_code' => $leaveType['leave_type_code'],
                    'description' => $leaveType['description'],
                    'original_days' => $originalDays,
                    'allocated_days' => $proRatedDays,
                    'is_pro_rated' => $proRatingData['is_pro_rated'],
                    'pro_rated_factor' => $proRatingData['factor'],
                    'pro_rating_details' => $proRatingData,
                    'assigned_departments' => $leaveType['assigned_departments'],
                    'financial_year' => $leaveType['financial_year'],
                    'status' => $leaveType['status'],
                    // Fields for override functionality
                    'override_days' => null,
                    'is_manual_override' => false,
                    'effective_days' => $proRatedDays,
                ];
            }

            Log::info("Pro-rating calculation completed", [
                'processed_leave_types' => count($proRatedLeaves),
                'pro_rating_factor' => $proRatingData['factor'],
                'is_pro_rated' => $proRatingData['is_pro_rated']
            ]);

            return $proRatedLeaves;

        } catch (\Exception $e) {
            Log::error("Error calculating pro-rated leaves: " . $e->getMessage(), [
                'joining_date' => $joiningDate ?? null,
                'financial_year' => $financialYear,
                'leave_types_count' => count($leaveTypes ?? [])
            ]);

            throw $e;
        }
    }

    /**
     * Calculate pro-rating factor based on joining date and financial year
     */
    private function calculateProRatingFactor($joiningDate, $fyStartDate, $fyEndDate)
    {
        // If joining before or on FY start date, no pro-rating needed
        if ($joiningDate->lte($fyStartDate)) {
            return [
                'is_pro_rated' => false,
                'factor' => 1.0,
                'remaining_months' => 12,
                'total_months' => 12,
                'remaining_days' => $fyEndDate->diffInDays($fyStartDate) + 1,
                'total_days' => $fyEndDate->diffInDays($fyStartDate) + 1,
                'calculation_method' => 'full_year',
                'joining_date' => $joiningDate->toDateString(),
                'fy_start_date' => $fyStartDate->toDateString(),
                'fy_end_date' => $fyEndDate->toDateString(),
            ];
        }

        // If joining after FY end date, no allocation
        if ($joiningDate->gt($fyEndDate)) {
            return [
                'is_pro_rated' => true,
                'factor' => 0.0,
                'remaining_months' => 0,
                'total_months' => 12,
                'remaining_days' => 0,
                'total_days' => $fyEndDate->diffInDays($fyStartDate) + 1,
                'calculation_method' => 'no_allocation',
                'joining_date' => $joiningDate->toDateString(),
                'fy_start_date' => $fyStartDate->toDateString(),
                'fy_end_date' => $fyEndDate->toDateString(),
            ];
        }

        // Calculate pro-rating based on remaining months
        // Use proper month calculation based on month boundaries, not exact days
        $fyStartMonth = $fyStartDate->copy()->startOfMonth();
        $fyEndMonth = $fyEndDate->copy()->startOfMonth();
        $joiningMonth = $joiningDate->copy()->startOfMonth();
        
        // Calculate total months in the financial year
        $totalMonths = $fyStartMonth->diffInMonths($fyEndMonth) + 1;
        
        // Calculate remaining months from joining month to end of FY
        $remainingMonths = $joiningMonth->diffInMonths($fyEndMonth) + 1;
        
        // Alternative: Calculate based on remaining days for verification
        $totalDays = $fyEndDate->diffInDays($fyStartDate) + 1;
        $remainingDays = $fyEndDate->diffInDays($joiningDate) + 1;
        
        // Use monthly calculation by default, but can be configured
        $monthlyFactor = $remainingMonths / $totalMonths;
        $dailyFactor = $remainingDays / $totalDays;

        // Use the more conservative (monthly) approach by default
        $factor = $monthlyFactor;

        return [
            'is_pro_rated' => true,
            'factor' => round($factor, 4),
            'remaining_months' => $remainingMonths,
            'total_months' => $totalMonths,
            'remaining_days' => $remainingDays,
            'total_days' => $totalDays,
            'monthly_factor' => round($monthlyFactor, 4),
            'daily_factor' => round($dailyFactor, 4),
            'calculation_method' => 'monthly_proration',
            'joining_date' => $joiningDate->toDateString(),
            'fy_start_date' => $fyStartDate->toDateString(),
            'fy_end_date' => $fyEndDate->toDateString(),
        ];
    }

    /**
     * Apply pro-rating to leave days
     */
    private function applyProRating($originalDays, $proRatingData)
    {
        if (!$proRatingData['is_pro_rated']) {
            return $originalDays;
        }

        $proRatedDays = $originalDays * $proRatingData['factor'];
        
        // Round to nearest 0.5 increment
        $proRatedDays = $this->roundToHalfStep($proRatedDays);
        
        // Optional: Set minimum allocation (e.g., at least 0.5 days if original > 0)
        if ($originalDays > 0 && $proRatedDays < 0.5) {
            $proRatedDays = 0.5;
        }

        return $proRatedDays;
    }

    /**
     * Round value to nearest 0.5 increment with custom logic
     * Values < 1.0: Round up (ceil) to give minimum benefit
     * Values >= 1.0: Round down (floor) to be conservative
     */
    private function roundToHalfStep($value)
    {
        if ($value < 1.0) {
            // For values less than 1, round up to nearest 0.5
            // This ensures employees get at least some benefit
            return ceil($value * 2) / 2;
        } else {
            // For values 1.0 and above, round down to nearest 0.5
            // This is more conservative for higher allocations
            return floor($value * 2) / 2;
        }
    }

    /**
     * Get financial year details
     */
    private function getFinancialYearDetails($financialYear = null)
    {
        try {
            if ($financialYear) {
                // If specific FY provided, find it in the database
                $fy = \App\Models\FinancialYear::where('name', $financialYear)->first();
                if ($fy) {
                    return [
                        'name' => $fy->name,
                        'start_date' => $fy->start_date->toDateString(),
                        'end_date' => $fy->end_date->toDateString(),
                    ];
                }
            }

            // Use current financial year from helper
            $currentFY = FinancialYearHelper::getCurrentFinancialYear();
            if ($currentFY) {
                return [
                    'name' => $currentFY->name,
                    'start_date' => $currentFY->start_date->toDateString(),
                    'end_date' => $currentFY->end_date->toDateString(),
                ];
            }

            return null;

        } catch (\Exception $e) {
            Log::error("Error getting financial year details: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Calculate remaining leave days based on current date
     */
    public function calculateRemainingLeaveAllocation($originalDays, $joiningDate, $financialYear = null, $currentDate = null)
    {
        $currentDate = $currentDate ? Carbon::parse($currentDate) : Carbon::now();
        
        $fyDetails = $this->getFinancialYearDetails($financialYear);
        if (!$fyDetails) {
            return $originalDays;
        }

        $fyStartDate = Carbon::parse($fyDetails['start_date']);
        $fyEndDate = Carbon::parse($fyDetails['end_date']);
        $joiningDate = Carbon::parse($joiningDate);

        // Use the later of joining date or FY start date
        $effectiveStartDate = $joiningDate->gt($fyStartDate) ? $joiningDate : $fyStartDate;

        // Calculate remaining time in FY
        $totalDaysInFY = $fyEndDate->diffInDays($effectiveStartDate) + 1;
        $remainingDaysInFY = $fyEndDate->diffInDays($currentDate) + 1;

        if ($remainingDaysInFY <= 0) {
            return 0;
        }

        $factor = $remainingDaysInFY / $totalDaysInFY;
        return round($originalDays * $factor, 2);
    }

    /**
     * Get pro-rating summary for display
     */
    public function getProRatingSummary($leaveAllocations)
    {
        $summary = [
            'total_leave_types' => count($leaveAllocations),
            'pro_rated_count' => 0,
            'full_allocation_count' => 0,
            'total_original_days' => 0,
            'total_allocated_days' => 0,
            'average_pro_rating_factor' => 0,
            'savings_days' => 0,
        ];

        foreach ($leaveAllocations as $allocation) {
            $summary['total_original_days'] += $allocation['original_days'];
            $summary['total_allocated_days'] += $allocation['allocated_days'];

            if ($allocation['is_pro_rated']) {
                $summary['pro_rated_count']++;
            } else {
                $summary['full_allocation_count']++;
            }
        }

        $summary['savings_days'] = $summary['total_original_days'] - $summary['total_allocated_days'];
        
        if ($summary['pro_rated_count'] > 0) {
            $totalFactor = array_sum(array_column(
                array_filter($leaveAllocations, fn($a) => $a['is_pro_rated']), 
                'pro_rated_factor'
            ));
            $summary['average_pro_rating_factor'] = round($totalFactor / $summary['pro_rated_count'], 4);
        }

        return $summary;
    }

    /**
     * Validate joining date against financial year
     */
    public function validateJoiningDate($joiningDate, $financialYear = null)
    {
        try {
            $joiningDate = Carbon::parse($joiningDate);
            $fyDetails = $this->getFinancialYearDetails($financialYear);

            if (!$fyDetails) {
                return [
                    'valid' => false,
                    'message' => 'Financial year details not available',
                ];
            }

            $fyStartDate = Carbon::parse($fyDetails['start_date']);
            $fyEndDate = Carbon::parse($fyDetails['end_date']);

            if ($joiningDate->gt($fyEndDate)) {
                return [
                    'valid' => false,
                    'message' => 'Joining date is after the financial year end date',
                ];
            }

            return [
                'valid' => true,
                'message' => 'Valid joining date',
                'pro_rating_required' => $joiningDate->gt($fyStartDate),
            ];

        } catch (\Exception $e) {
            return [
                'valid' => false,
                'message' => 'Invalid joining date format',
            ];
        }
    }
}