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

        // 2. Fetch active FY from payroll API first, fallback to local DB (with caching)
        return Cache::remember('active_financial_year', 3600, function () {
            try {
                $payrollApiService = app(\App\Services\PayrollApiService::class);
                $payrollFy = $payrollApiService->getCurrentFinancialYear();
                
                if ($payrollFy && isset($payrollFy['name'])) {
                    $apiName = $payrollFy['name'];
                    $normalizedName = $apiName;
                    if (preg_match('/^(\d{4})-(\d{2})$/', $apiName, $matches)) {
                        $startYear = $matches[1];
                        $endYear = substr($startYear, 0, 2) . $matches[2];
                        $normalizedName = $startYear . '-' . $endYear;
                    }
                    
                    $localFy = FinancialYear::where('name', $normalizedName)->first();
                    if (!$localFy) {
                        $localFy = FinancialYear::create([
                            'name' => $normalizedName,
                            'start_date' => Carbon::parse($payrollFy['start_date'])->toDateString(),
                            'end_date' => Carbon::parse($payrollFy['end_date'])->toDateString(),
                            'is_active' => true,
                        ]);
                    } else {
                        if (!$localFy->is_active) {
                            FinancialYear::where('is_active', true)->update(['is_active' => false]);
                            $localFy->update(['is_active' => true]);
                        }
                    }
                    return $localFy;
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Failed to get active FY from payroll API: ' . $e->getMessage());
            }

            // Local fallback
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
