<?php

namespace App\Helpers;

use App\Models\FinancialYear;
use Illuminate\Support\Facades\Session;

class FinancialYearHelper
{
    /**
     * Get the currently selected financial year
     */
    public static function getSelectedFinancialYear()
    {
        $selectedFyId = Session::get('selected_financial_year_id');
        $selectedFY = $selectedFyId ? FinancialYear::find($selectedFyId) : null;
        
        if (!$selectedFY) {
            $selectedFY = FinancialYear::where('is_current', true)->first();
            if ($selectedFY) {
                Session::put('selected_financial_year_id', $selectedFY->id);
            }
        }
        
        return $selectedFY;
    }
    
    /**
     * Get the current financial year (marked as current in DB)
     */
    public static function getCurrentFinancialYear()
    {
        return FinancialYear::where('is_current', true)->first();
    }
    
    /**
     * Get all financial years ordered by start date (most recent first)
     */
    public static function getAllFinancialYears()
    {
        return FinancialYear::orderBy('start_date', 'desc')->get();
    }
    
    /**
     * Set the selected financial year
     */
    public static function setSelectedFinancialYear($fyId)
    {
        Session::put('selected_financial_year_id', $fyId);
        return true;
    }
    
    /**
     * Get financial year by ID
     */
    public static function getFinancialYearById($fyId)
    {
        return FinancialYear::find($fyId);
    }
    
    /**
     * Check if the selected financial year is editable (current FY only)
     */
    public static function isSelectedFinancialYearEditable()
    {
        $selectedFY = self::getSelectedFinancialYear();
        $currentFY = self::getCurrentFinancialYear();
        
        return $selectedFY && $currentFY && $selectedFY->id === $currentFY->id;
    }
    
    /**
     * Require editable context - throw exception if not editable
     */
    public static function requireEditableContext($message = 'Action not allowed for previous financial years')
    {
        if (!self::isSelectedFinancialYearEditable()) {
            throw new \Exception($message);
        }
    }
    
    /**
     * Get financial year context for views and controllers
     */
    public static function getFinancialYearContext()
    {
        $selectedFY = self::getSelectedFinancialYear();
        $currentFY = self::getCurrentFinancialYear();
        $isEditable = $selectedFY && $currentFY && $selectedFY->id === $currentFY->id;
        
        return [
            'selectedFinancialYear' => $selectedFY,
            'currentFinancialYear' => $currentFY,
            'isFinancialYearEditable' => $isEditable,
            'availableFinancialYears' => FinancialYear::orderBy('start_date', 'desc')->get()
        ];
    }
    
    /**
     * Filter query by selected financial year date range
     * Use this to filter data based on the selected FY's start and end dates
     */
    public static function filterBySelectedFinancialYear($query, $dateColumn = 'created_at')
    {
        $selectedFY = self::getSelectedFinancialYear();
        
        if ($selectedFY) {
            return $query->whereBetween($dateColumn, [
                $selectedFY->start_date,
                $selectedFY->end_date
            ]);
        }
        
        return $query;
    }

    /**
     * Filter payroll queries by selected financial year
     * For payroll tables that have payout_month and payout_year
     */
    public static function filterPayrollBySelectedFinancialYear($query)
    {
        $selectedFY = self::getSelectedFinancialYear();
        
        if ($selectedFY) {
            $startYear = $selectedFY->start_date->year;
            $endYear = $selectedFY->end_date->year;
            $startMonth = $selectedFY->start_date->month;
            $endMonth = $selectedFY->end_date->month;
            
            // If financial year spans multiple calendar years
            if ($startYear !== $endYear) {
                return $query->where(function($q) use ($startYear, $endYear, $startMonth, $endMonth) {
                    $q->where(function($subQ) use ($startYear, $startMonth) {
                        $subQ->where('payout_year', $startYear)
                             ->where('payout_month', '>=', $startMonth);
                    })->orWhere(function($subQ) use ($endYear, $endMonth) {
                        $subQ->where('payout_year', $endYear)
                             ->where('payout_month', '<=', $endMonth);
                    });
                });
            } else {
                // Same calendar year
                return $query->where('payout_year', $startYear)
                           ->whereBetween('payout_month', [$startMonth, $endMonth]);
            }
        }
        
        return $query;
    }

    /**
     * Get months within the selected financial year
     * Returns array of [year, month] pairs
     */
    public static function getMonthsInSelectedFinancialYear()
    {
        $selectedFY = self::getSelectedFinancialYear();
        
        if (!$selectedFY) {
            return [];
        }
        
        $months = [];
        $start = $selectedFY->start_date->copy();
        $end = $selectedFY->end_date->copy();
        
        while ($start <= $end) {
            $months[] = [
                'year' => $start->year,
                'month' => $start->month,
                'label' => $start->format('M Y')
            ];
            $start->addMonth();
        }
        
        return $months;
    }

    /**
     * Check if a specific month/year falls within selected financial year
     */
    public static function isMonthInSelectedFinancialYear($month, $year)
    {
        try {
            $selectedFY = self::getSelectedFinancialYear();
            
            if (!$selectedFY || !$selectedFY->start_date || !$selectedFY->end_date) {
                return false;
            }
            
            $checkDate = \Carbon\Carbon::createFromDate($year, $month, 1);
            $startDate = \Carbon\Carbon::parse($selectedFY->start_date)->startOfMonth();
            $endDate = \Carbon\Carbon::parse($selectedFY->end_date)->endOfMonth();
            
            return $checkDate >= $startDate && $checkDate <= $endDate;
        } catch (\Exception $e) {
            \Log::error('Error in isMonthInSelectedFinancialYear: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get finalized payout months for selected financial year
     */
    public static function getFinalizedPayoutMonths()
    {
        try {
            $selectedFY = self::getSelectedFinancialYear();
            
            if (!$selectedFY) {
                return collect([]);
            }
            
            // Get finalized payouts within the selected financial year
            $query = \DB::table('employee_salary_finalize')
                ->select('payout_month', 'payout_year')
                ->where('is_finalized', true)
                ->distinct()
                ->orderBy('payout_year', 'desc')
                ->orderBy('payout_month', 'desc');
            
            // Apply financial year filtering
            $startYear = \Carbon\Carbon::parse($selectedFY->start_date)->year;
            $endYear = \Carbon\Carbon::parse($selectedFY->end_date)->year;
            $startMonth = \Carbon\Carbon::parse($selectedFY->start_date)->month;
            $endMonth = \Carbon\Carbon::parse($selectedFY->end_date)->month;
            
            if ($startYear !== $endYear) {
                $query->where(function($q) use ($startYear, $endYear, $startMonth, $endMonth) {
                    $q->where(function($subQ) use ($startYear, $startMonth) {
                        $subQ->where('payout_year', $startYear)
                             ->where('payout_month', '>=', $startMonth);
                    })->orWhere(function($subQ) use ($endYear, $endMonth) {
                        $subQ->where('payout_year', $endYear)
                             ->where('payout_month', '<=', $endMonth);
                    });
                });
            } else {
                $query->where('payout_year', $startYear)
                      ->whereBetween('payout_month', [$startMonth, $endMonth]);
            }
            
            return $query->get();
        } catch (\Exception $e) {
            \Log::error('Error in getFinalizedPayoutMonths: ' . $e->getMessage());
            return collect([]);
        }
    }
    
    /**
     * Check if editing is allowed for current context
     */
    public static function canEditFinancialYearData()
    {
        $selectedFY = self::getSelectedFinancialYear();
        $currentFY = self::getCurrentFinancialYear();
        
        return $selectedFY && $currentFY && $selectedFY->id === $currentFY->id;
    }
    
    /**
     * Get appropriate message for read-only context
     */
    public static function getReadOnlyMessage()
    {
        $selectedFY = self::getSelectedFinancialYear();
        
        if (!$selectedFY) {
            return 'No financial year selected';
        }
        
        if (!self::canEditFinancialYearData()) {
            return "Viewing historical data for Financial Year {$selectedFY->year_name}. Editing is not allowed for previous years.";
        }
        
        return null;
    }
    
    /**
     * Check if a specific payout month is finalized
     */
    public static function isPayoutMonthFinalized($month, $year)
    {
        try {
            // Check if month is within selected financial year
            if (!self::isMonthInSelectedFinancialYear($month, $year)) {
                return false;
            }
            
            // Check if any employee has finalized salary for this month/year
            return \DB::table('employee_salary_finalize')
                ->where('payout_month', $month)
                ->where('payout_year', $year)
                ->where('is_finalized', true)
                ->exists();
        } catch (\Exception $e) {
            \Log::error('Error in isPayoutMonthFinalized: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get finalized status for a specific payout month
     */
    public static function getPayoutMonthStatus($month, $year)
    {
        if (!self::isMonthInSelectedFinancialYear($month, $year)) {
            return [
                'is_finalized' => false,
                'employee_count' => 0,
                'message' => 'Month is not within selected financial year'
            ];
        }
        
        $finalizedCount = \DB::table('employee_salary_finalize')
            ->where('payout_month', $month)
            ->where('payout_year', $year)
            ->where('is_finalized', true)
            ->count();
            
        $totalProcessed = \DB::table('employee_salary_finalize')
            ->where('payout_month', $month)
            ->where('payout_year', $year)
            ->count();
        
        return [
            'is_finalized' => $finalizedCount > 0,
            'finalized_count' => $finalizedCount,
            'total_processed' => $totalProcessed,
            'message' => $finalizedCount > 0 ? 
                "Salary breakdown available for {$finalizedCount} employees" : 
                'No finalized salaries for this month'
        ];
    }
}
