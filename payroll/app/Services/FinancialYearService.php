<?php

namespace App\Services;

use App\Models\FinancialYear;
use App\Models\FinancialYearSetting;
use App\Models\FinancialYearReport;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class FinancialYearService
{
    /**
     * Get current financial year
     */
    public function getCurrentFinancialYear()
    {
        return FinancialYear::current();
    }

    /**
     * Get financial year by date
     */
    public function getFinancialYearByDate($date = null)
    {
        return FinancialYear::getByDate($date);
    }

    /**
     * Get or create current financial year based on settings
     */
    public function getOrCreateCurrentFinancialYear()
    {
        $current = $this->getCurrentFinancialYear();
        
        if (!$current) {
            $current = $this->createFinancialYearForDate();
        }
        
        return $current;
    }

    /**
     * Create financial year for given date (defaults to current date)
     */
    public function createFinancialYearForDate($date = null)
    {
        $date = $date ? Carbon::parse($date) : Carbon::now();
        $settings = FinancialYearSetting::getSettings();
        
        // Calculate FY start and end dates
        $startMonth = $settings->start_month;
        $currentYear = $date->year;
        $currentMonth = $date->month;
        
        // Determine the financial year
        if ($currentMonth >= $startMonth) {
            // Current FY
            $fyStartYear = $currentYear;
            $fyEndYear = $currentYear + 1;
        } else {
            // Previous FY
            $fyStartYear = $currentYear - 1;
            $fyEndYear = $currentYear;
        }
        
        $startDate = Carbon::create($fyStartYear, $startMonth, 1);
        $endMonth = $startMonth == 1 ? 12 : $startMonth - 1;
        $endDate = Carbon::create($fyEndYear, $endMonth, 1)->endOfMonth();
        
        // Generate FY name
        $fyName = $this->generateFinancialYearName($fyStartYear, $fyEndYear);
        
        // Check if FY already exists
        $existingFY = FinancialYear::where('name', $fyName)->first();
        if ($existingFY) {
            return $existingFY;
        }
        
        // Create new financial year
        return DB::transaction(function () use ($fyName, $startDate, $endDate) {
            // Mark all other FYs as non-current
            FinancialYear::where('is_current', true)->update(['is_current' => false]);
            
            return FinancialYear::create([
                'name' => $fyName,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'is_current' => true,
                'is_closed' => false,
                'description' => "Financial Year {$fyName}",
            ]);
        });
    }

    /**
     * Generate financial year name
     */
    private function generateFinancialYearName($startYear, $endYear)
    {
        return $startYear . '-' . substr($endYear, 2);
    }

    /**
     * Close financial year
     */
    public function closeFinancialYear(FinancialYear $financialYear, $summary = null)
    {
        if ($financialYear->is_closed) {
            throw new \Exception('Financial year is already closed.');
        }
        
        return DB::transaction(function () use ($financialYear, $summary) {
            // Generate closing summary
            $closingSummary = $summary ?? $this->generateClosingSummary($financialYear);
            
            // Close the financial year
            $financialYear->update([
                'is_closed' => true,
                'closed_at' => Carbon::now(),
                'closing_summary' => $closingSummary,
            ]);
            
            // Generate annual report
            $this->generateAnnualReport($financialYear);
            
            // Sync to attendance system
            $this->syncFinancialYearToAttendance($financialYear);
            
            // Auto-switch to next financial year
            $nextFY = FinancialYear::where('start_date', '>=', $financialYear->end_date)
                ->where('id', '!=', $financialYear->id)
                ->orderBy('start_date', 'asc')
                ->first();
                
            if ($nextFY) {
                FinancialYear::query()->update(['is_current' => false]);
                $nextFY->update(['is_current' => true]);
                $this->syncFinancialYearToAttendance($nextFY);
                
                Log::info("Automatically switched active financial year to {$nextFY->name}");
            }
            
            Log::info("Financial Year {$financialYear->name} closed successfully", [
                'financial_year_id' => $financialYear->id,
                'closing_summary' => $closingSummary,
            ]);
            
            return $financialYear;
        });
    }

    /**
     * Generate closing summary for financial year
     */
    private function generateClosingSummary(FinancialYear $financialYear)
    {
        // This would include various payroll metrics
        return [
            'total_employees' => $this->getTotalEmployeesInFY($financialYear),
            'total_payroll_cost' => $this->getTotalPayrollCost($financialYear),
            'total_deductions' => $this->getTotalDeductions($financialYear),
            'total_net_pay' => $this->getTotalNetPay($financialYear),
            'total_overtime_cost' => $this->getTotalOvertimeCost($financialYear),
            'total_incentives' => $this->getTotalIncentives($financialYear),
            'department_wise_cost' => $this->getDepartmentWiseCost($financialYear),
            'month_wise_summary' => $this->getMonthWiseSummary($financialYear),
            'closed_by' => auth()->user()?->id,
            'closed_at' => Carbon::now()->toISOString(),
        ];
    }

    /**
     * Get total employees in financial year
     */
    private function getTotalEmployeesInFY(FinancialYear $financialYear)
    {
        return DB::table('employee_basic_details')
            ->where('date_of_joining', '<=', $financialYear->end_date)
            ->where(function ($query) use ($financialYear) {
                $query->whereNull('date_of_resignation')
                      ->orWhere('date_of_resignation', '>=', $financialYear->start_date);
            })
            ->count();
    }

    /**
     * Get total payroll cost in financial year
     */
    private function getTotalPayrollCost(FinancialYear $financialYear)
    {
        return DB::table('employee_payroll_attendances as epa')
            ->join('employee_payroll_attendance_payout_month_statuses as pms', 'epa.payout_month_id', '=', 'pms.id')
            ->where(function ($q) use ($financialYear) {
                $this->applyFYDateFilter($q, $financialYear, 'pms');
            })
            ->sum('epa.gross_pay');
    }

    /**
     * Get total deductions in financial year
     */
    private function getTotalDeductions(FinancialYear $financialYear)
    {
        return DB::table('employee_payroll_attendances as epa')
            ->join('employee_payroll_attendance_payout_month_statuses as pms', 'epa.payout_month_id', '=', 'pms.id')
            ->where(function ($q) use ($financialYear) {
                $this->applyFYDateFilter($q, $financialYear, 'pms');
            })
            ->sum('epa.total_deduction');
    }

    /**
     * Get total net pay in financial year
     */
    private function getTotalNetPay(FinancialYear $financialYear)
    {
        return DB::table('employee_payroll_attendances as epa')
            ->join('employee_payroll_attendance_payout_month_statuses as pms', 'epa.payout_month_id', '=', 'pms.id')
            ->where(function ($q) use ($financialYear) {
                $this->applyFYDateFilter($q, $financialYear, 'pms');
            })
            ->sum('epa.total_payable');
    }

    /**
     * Get total overtime cost in financial year
     */
    private function getTotalOvertimeCost(FinancialYear $financialYear)
    {
        return DB::table('employee_ot_details')
            ->where(function ($q) use ($financialYear) {
                $this->applyFYDateFilter($q, $financialYear);
            })
            ->sum('total_amount');
    }

    /**
     * Get total incentives in financial year
     */
    private function getTotalIncentives(FinancialYear $financialYear)
    {
        return DB::table('employee_incentive_details')
            ->where(function ($q) use ($financialYear) {
                $this->applyFYDateFilter($q, $financialYear);
            })
            ->sum('total_amount');
    }

    /**
     * Get department wise cost breakdown
     */
    private function getDepartmentWiseCost(FinancialYear $financialYear)
    {
        return DB::table('employee_payroll_attendances as epa')
            ->join('employee_payroll_attendance_payout_month_statuses as pms', 'epa.payout_month_id', '=', 'pms.id')
            ->join('employee_basic_details as ebd', 'epa.emp_id', '=', 'ebd.id')
            ->join('departments as d', 'ebd.department', '=', 'd.id')
            ->whereRaw("CONCAT(pms.payout_year, '-', LPAD(pms.payout_month, 2, '0')) BETWEEN ? AND ?", [
                $financialYear->start_date->format('Y-m'),
                $financialYear->end_date->format('Y-m')
            ])
            ->select(
                'd.department as department_name',
                DB::raw('SUM(epa.gross_pay) as total_gross'),
                DB::raw('SUM(epa.total_deduction) as total_deductions'),
                DB::raw('SUM(epa.total_payable) as total_net'),
                DB::raw('COUNT(DISTINCT epa.emp_id) as employee_count')
            )
            ->groupBy('d.id', 'd.department')
            ->get()
            ->toArray();
    }

    /**
     * Get month wise summary
     */
    private function getMonthWiseSummary(FinancialYear $financialYear)
    {
        return DB::table('employee_payroll_attendances as epa')
            ->join('employee_payroll_attendance_payout_month_statuses as pms', 'epa.payout_month_id', '=', 'pms.id')
            ->whereRaw("CONCAT(pms.payout_year, '-', LPAD(pms.payout_month, 2, '0')) BETWEEN ? AND ?", [
                $financialYear->start_date->format('Y-m'),
                $financialYear->end_date->format('Y-m')
            ])
            ->select(
                DB::raw("CONCAT(pms.payout_year, '-', LPAD(pms.payout_month, 2, '0')) as month_year"),
                DB::raw('SUM(epa.gross_pay) as total_gross'),
                DB::raw('SUM(epa.total_deduction) as total_deductions'),
                DB::raw('SUM(epa.total_payable) as total_net'),
                DB::raw('COUNT(DISTINCT epa.emp_id) as employee_count')
            )
            ->groupBy('pms.payout_year', 'pms.payout_month')
            ->orderBy('pms.payout_year')
            ->orderBy('pms.payout_month')
            ->get()
            ->toArray();
    }

    /**
     * Auto-close expired financial years
     */
    public function autoCloseExpiredFinancialYears()
    {
        $settings = FinancialYearSetting::getSettings();
        
        if (!$settings->auto_close_enabled) {
            return;
        }
        
        $cutoffDate = Carbon::now()->subDays($settings->auto_close_days_after);
        
        $expiredFYs = FinancialYear::where('is_closed', false)
            ->where('end_date', '<', $cutoffDate)
            ->get();
        
        foreach ($expiredFYs as $fy) {
            try {
                $this->closeFinancialYear($fy);
                Log::info("Auto-closed expired financial year: {$fy->name}");
            } catch (\Exception $e) {
                Log::error("Failed to auto-close financial year {$fy->name}: " . $e->getMessage());
            }
        }
    }

    /**
     * Auto-create next financial year
     */
    public function autoCreateNextFinancialYear()
    {
        $settings = FinancialYearSetting::getSettings();
        
        if (!$settings->auto_create_next) {
            return;
        }
        
        $current = $this->getCurrentFinancialYear();
        if (!$current) {
            return;
        }
        
        $createDate = $current->end_date->copy()->subDays($settings->create_next_days_before);
        
        if (Carbon::now()->gte($createDate)) {
            $nextFYDate = $current->end_date->copy()->addDay();
            $this->createFinancialYearForDate($nextFYDate);
        }
    }

    /**
     * Generate annual report
     */
    public function generateAnnualReport(FinancialYear $financialYear)
    {
        $reportData = [
            'financial_year' => $financialYear->toArray(),
            'summary' => $financialYear->closing_summary,
            'generated_at' => Carbon::now()->toISOString(),
        ];
        
        return FinancialYearReport::create([
            'financial_year_id' => $financialYear->id,
            'report_type' => 'annual_summary',
            'report_name' => "Annual Report - {$financialYear->name}",
            'report_data' => $reportData,
            'generated_at' => Carbon::now(),
            'generated_by' => auth()->user()?->id ?? (\App\Models\User::first()->id ?? 1),
            'status' => 'completed',
        ]);
    }

    /**
     * Sync financial year to attendance system
     */
    public function syncFinancialYearToAttendance(FinancialYear $financialYear)
    {
        try {
            $attendanceWebhookService = app(AttendanceWebhookService::class);
            
            $payload = [
                'action' => 'financial_year_update',
                'financial_year' => [
                    'id' => $financialYear->id,
                    'name' => $financialYear->name,
                    'start_date' => $financialYear->start_date->toDateString(),
                    'end_date' => $financialYear->end_date->toDateString(),
                    'is_current' => $financialYear->is_current,
                    'is_closed' => $financialYear->is_closed,
                    'closed_at' => $financialYear->closed_at?->toISOString(),
                ],
                'settings' => FinancialYearSetting::getSettings()->toArray(),
            ];
            
            $attendanceWebhookService->sendWebhook($payload);
            
            Log::info("Financial year synced to attendance system", [
                'financial_year_id' => $financialYear->id,
                'financial_year_name' => $financialYear->name,
            ]);
            
        } catch (\Exception $e) {
            Log::error("Failed to sync financial year to attendance: " . $e->getMessage(), [
                'financial_year_id' => $financialYear->id,
                'error' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Get financial year statistics
     */
    public function getFinancialYearStatistics(FinancialYear $financialYear)
    {
        return [
            'basic_info' => [
                'name' => $financialYear->name,
                'start_date' => $financialYear->start_date->format('d M Y'),
                'end_date' => $financialYear->end_date->format('d M Y'),
                'duration_days' => $financialYear->getDurationInDays(),
                'progress_percentage' => $financialYear->getProgressPercentage(),
                'remaining_days' => $financialYear->getRemainingDays(),
                'is_current' => $financialYear->is_current,
                'is_closed' => $financialYear->is_closed,
            ],
            'quarters' => $financialYear->getQuarters(),
            'payroll_summary' => $this->getPayrollSummary($financialYear),
            'employee_summary' => $this->getEmployeeSummary($financialYear),
        ];
    }

    /**
     * Get payroll summary for financial year
     */
    private function getPayrollSummary(FinancialYear $financialYear)
    {
        $payrollData = DB::table('employee_payroll_attendances as epa')
            ->join('employee_payroll_attendance_payout_month_statuses as pms', 'epa.payout_month_id', '=', 'pms.id')
            ->whereRaw("CONCAT(pms.payout_year, '-', LPAD(pms.payout_month, 2, '0')) BETWEEN ? AND ?", [
                $financialYear->start_date->format('Y-m'),
                $financialYear->end_date->format('Y-m')
            ])
            ->selectRaw('
                SUM(epa.gross_pay) as total_gross,
                SUM(epa.total_deduction) as total_deductions,
                SUM(epa.total_payable) as total_net,
                COUNT(*) as total_payslips,
                COUNT(DISTINCT epa.emp_id) as unique_employees
            ')
            ->first();
        
        return [
            'total_gross' => $payrollData->total_gross ?? 0,
            'total_deductions' => $payrollData->total_deductions ?? 0,
            'total_net' => $payrollData->total_net ?? 0,
            'total_payslips' => $payrollData->total_payslips ?? 0,
            'unique_employees' => $payrollData->unique_employees ?? 0,
            'average_monthly_gross' => $payrollData->total_gross ? 
                round($payrollData->total_gross / 12, 2) : 0,
        ];
    }

    /**
     * Get employee summary for financial year
     */
    private function getEmployeeSummary(FinancialYear $financialYear)
    {
        $joinedCount = DB::table('employee_basic_details')
            ->whereBetween('date_of_joining', [
                $financialYear->start_date,
                $financialYear->end_date
            ])
            ->count();
        
        $resignedCount = DB::table('employee_basic_details')
            ->whereBetween('date_of_resignation', [
                $financialYear->start_date,
                $financialYear->end_date
            ])
            ->count();
        
        $activeCount = DB::table('employee_basic_details')
            ->where('date_of_joining', '<=', $financialYear->end_date)
            ->where(function ($query) use ($financialYear) {
                $query->whereNull('date_of_resignation')
                      ->orWhere('date_of_resignation', '>', $financialYear->end_date);
            })
            ->count();
        
        return [
            'employees_joined' => $joinedCount,
            'employees_resigned' => $resignedCount,
            'active_employees' => $activeCount,
            'net_change' => $joinedCount - $resignedCount,
        ];
    }

    /**
     * Apply financial year date filter using payout_month and payout_year columns
     */
    private function applyFYDateFilter($query, FinancialYear $financialYear, $tableAlias = null)
    {
        $prefix = $tableAlias ? "{$tableAlias}." : '';
        $startMonth = (int) $financialYear->start_date->format('m');
        $startYear = (int) $financialYear->start_date->format('Y');
        $endMonth = (int) $financialYear->end_date->format('m');
        $endYear = (int) $financialYear->end_date->format('Y');

        $query->whereRaw(
            "({$prefix}payout_year * 100 + {$prefix}payout_month) BETWEEN ? AND ?",
            [$startYear * 100 + $startMonth, $endYear * 100 + $endMonth]
        );
    }
}
