<?php

namespace App\Services;

use App\Models\FinancialYear;
use App\Models\SystemSetting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class FinancialYearService
{
    /**
     * Get the active financial year.
     * If none is active, it tries to find one that covers today's date.
     */
    public function getActiveFY()
    {
        // 1. Check if user has manually switched FY in this session
        if (session()->has('selected_financial_year_id')) {
            $sessionFyId = session('selected_financial_year_id');
            $sessionFy = FinancialYear::find($sessionFyId);
            if ($sessionFy) {
                return $sessionFy;
            }
        }

        // 2. Fallback to global active FY (with caching)
        return Cache::remember('active_financial_year', 3600, function () {
            $active = FinancialYear::where('is_active', true)->first();
            
            if (!$active) {
                $today = now()->toDateString();
                $active = FinancialYear::where('start_date', '<=', $today)
                    ->where('end_date', '>=', $today)
                    ->first();
            }
            
            return $active;
        });
    }

    /**
     * Get the financial year for a specific date.
     */
    public function getFYByDate($date)
    {
        $date = Carbon::parse($date)->toDateString();
        return FinancialYear::where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->first();
    }

    /**
     * Get the label for a date based on the fiscal cycle.
     * e.g., "2024-25" or "2024"
     */
    public function getFYLabelByDate($date)
    {
        $fy = $this->getFYByDate($date);
        if ($fy) {
            return $fy->name;
        }

        // Fallback logic if FY record doesn't exist
        $startMonth = (int) SystemSetting::get('fy_start_month', 4);
        $dt = Carbon::parse($date);
        $month = $dt->month;
        $year = $dt->year;

        if ($startMonth === 1) {
            return (string) $year;
        }

        if ($month >= $startMonth) {
            return $year . "-" . ($year + 1);
        } else {
            return ($year - 1) . "-" . $year;
        }
    }

    /**
     * Clear the FY cache.
     */
    public function clearCache()
    {
        Cache::forget('active_financial_year');
    }
}
