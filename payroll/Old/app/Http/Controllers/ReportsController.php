<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\{
    EmployeePayrollAttendancePayoutMonthStatus,
    EmployeePayrollAttendance,
    EmployeeBasicDetail,
    SalaryComponent,
    StatutoryComponent,
    CompanySettings
};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use PDF;
use Mpdf\Mpdf;
use App\Helpers\FinancialYearHelper;

class ReportsController extends Controller
{
    /**
     * Analytical (single month) payroll dashboard
     */
    public function payrollAnalytics(Request $request)
    {
        $months = $this->getFinalizedMonths();
        $selected = $request->get('payout_month_year');
        $analytics = null;

        // If no month is selected, use the latest finalized month by default
        if (!$selected && $months->isNotEmpty()) {
            $selected = $months->first()['key']; // Get the first (latest) month
        }

        if ($selected) {
            [$month, $year] = explode('-', $selected);
            $analytics = $this->buildMonthAnalytics((int)$month, (int)$year);
        }

        return view('reports.payroll-analytics', compact('months', 'selected', 'analytics'));
    }

    /**
     * Comparison between two finalized months
     */
    public function payrollComparison(Request $request)
    {
        $months = $this->getFinalizedMonths();
        $base = $request->get('base');
        $next = $request->get('next');
        $comparison = null;

        if ($base && $next) {
            [$bMonth, $bYear] = explode('-', $base);
            [$nMonth, $nYear] = explode('-', $next);

            $baseData = $this->buildMonthAnalytics((int)$bMonth, (int)$bYear);
            $nextData = $this->buildMonthAnalytics((int)$nMonth, (int)$nYear);

            if ($baseData && $nextData) {
                $comparison = $this->compareAnalytics($baseData, $nextData);
            }
        }

        return view('reports.payroll-comparison', compact('months', 'base', 'next', 'comparison'));
    }

    /**
     * Analytical Comparison Report with Financial Year filtering and month-over-month comparisons
     * Shows graphical charts and detailed breakdowns with color-coded percentage changes
     */
    public function analyticalPayrollComparison(Request $request)
    {
        // Get financial year context
        $fyContext = FinancialYearHelper::getFinancialYearContext();
        $defaultFY = $fyContext['selectedFinancialYear'];
        
        // Get all financial years for dropdown
        $financialYears = \App\Models\FinancialYear::orderByDesc('start_date')->get();
        
        // Determine selected financial year ID from request or use current
        $selectedFinancialYearId = $request->get('financial_year_id', $defaultFY ? $defaultFY->id : null);
        
        $selectedFY = null;
        if ($selectedFinancialYearId) {
            $selectedFY = \App\Models\FinancialYear::find($selectedFinancialYearId);
        }
        
        // Get completed payrolls for the selected financial year
        $payrollQuery = EmployeePayrollAttendancePayoutMonthStatus::where('status', 'completed')
            ->orderByDesc('payout_year')
            ->orderByDesc('payout_month');
            
        // Apply financial year filtering if a specific FY is selected
        if ($selectedFY) {
            $startYear = $selectedFY->start_date->year;
            $endYear = $selectedFY->end_date->year;
            $startMonth = $selectedFY->start_date->month;
            $endMonth = $selectedFY->end_date->month;
            
            // If financial year spans multiple calendar years
            if ($startYear !== $endYear) {
                $payrollQuery = $payrollQuery->where(function($q) use ($startYear, $endYear, $startMonth, $endMonth) {
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
                $payrollQuery = $payrollQuery->where('payout_year', $startYear)
                                           ->whereBetween('payout_month', [$startMonth, $endMonth]);
            }
        }
        
        $completedPayrolls = $payrollQuery->get();
        
        // Get month-wise data for comparison
        $monthlyData = [];
        $chartData = [
            'months' => [],
            'employeeCount' => [],
            'grossPay' => [],
            'totalDeductions' => [],
            'netPay' => []
        ];
        
        foreach ($completedPayrolls as $payroll) {
            $monthKey = $payroll->payout_year . '-' . str_pad($payroll->payout_month, 2, '0', STR_PAD_LEFT);
            $monthLabel = Carbon::createFromDate($payroll->payout_year, $payroll->payout_month, 1)->format('M Y');
            
            // Get payroll data for this month
            $attendances = EmployeePayrollAttendance::where('payout_month_id', $payroll->id)->get();
            
            $employeeCount = $attendances->count();
            $totalGrossPay = $attendances->sum('gross_pay');
            $totalDeductions = $attendances->sum('total_deduction');
            $totalNetPay = $attendances->sum('total_payable');
            
            // Calculate component-wise totals
            $epfTotal = 0;
            $esiTotal = 0;
            $ptTotal = 0;
            $tdsTotal = 0;
            $advanceTotal = 0;
            
            foreach ($attendances as $attendance) {
                $deductions = json_decode($attendance->deductions, true) ?? [];
                
                foreach ($deductions as $componentId => $deduction) {
                    if (isset($deduction['applicable']) && $deduction['applicable'] && isset($deduction['value'])) {
                        switch ($componentId) {
                            case '1': // EPF
                                $epfTotal += $deduction['value'];
                                break;
                            case '2': // ESI
                                $esiTotal += $deduction['value'];
                                break;
                            case '4': // PT
                                $ptTotal += $deduction['value'];
                                break;
                            case '3': // TDS
                                $tdsTotal += $deduction['value'];
                                break;
                            case 'advance': // Advance
                                $advanceTotal += $deduction['value'];
                                break;
                        }
                    }
                }
            }
            
            $monthlyData[$monthKey] = [
                'month' => $payroll->payout_month,
                'year' => $payroll->payout_year,
                'label' => $monthLabel,
                'employee_count' => $employeeCount,
                'gross_pay' => $totalGrossPay,
                'total_deductions' => $totalDeductions,
                'net_pay' => $totalNetPay,
                'epf' => $epfTotal,
                'esi' => $esiTotal,
                'pt' => $ptTotal,
                'tds' => $tdsTotal,
                'advance' => $advanceTotal
            ];
            
            // Prepare chart data
            $chartData['months'][] = $monthLabel;
            $chartData['employeeCount'][] = $employeeCount;
            $chartData['grossPay'][] = round($totalGrossPay);
            $chartData['totalDeductions'][] = round($totalDeductions);
            $chartData['netPay'][] = round($totalNetPay);
        }
        
        // Calculate month-over-month comparisons
        $comparisons = [];
        $monthKeys = array_keys($monthlyData);
        
        for ($i = 0; $i < count($monthKeys) - 1; $i++) {
            $currentMonthKey = $monthKeys[$i];
            $previousMonthKey = $monthKeys[$i + 1];
            
            $current = $monthlyData[$currentMonthKey];
            $previous = $monthlyData[$previousMonthKey];
            
            $comparison = [
                'current_month' => $current['label'],
                'previous_month' => $previous['label'],
                'employee_count' => $this->calculateChange($current['employee_count'], $previous['employee_count']),
                'gross_pay' => $this->calculateChange($current['gross_pay'], $previous['gross_pay']),
                'total_deductions' => $this->calculateChange($current['total_deductions'], $previous['total_deductions']),
                'net_pay' => $this->calculateChange($current['net_pay'], $previous['net_pay']),
                'epf' => $this->calculateChange($current['epf'], $previous['epf']),
                'esi' => $this->calculateChange($current['esi'], $previous['esi']),
                'pt' => $this->calculateChange($current['pt'], $previous['pt']),
                'tds' => $this->calculateChange($current['tds'], $previous['tds']),
                'advance' => $this->calculateChange($current['advance'], $previous['advance'])
            ];
            
            $comparisons[] = $comparison;
        }
        
        return view('reports.analytical-payroll-comparison', compact(
            'financialYears',
            'selectedFY',
            'selectedFinancialYearId',
            'monthlyData',
            'comparisons',
            'chartData'
        ));
    }

    /**
     * Export Analytical Payroll Comparison Report as PDF
     */
    public function analyticalPayrollComparisonPDF(Request $request)
    {
        // Get financial year context
        $fyContext = FinancialYearHelper::getFinancialYearContext();
        $defaultFY = $fyContext['selectedFinancialYear'];
        
        // Get all financial years for dropdown
        $financialYears = \App\Models\FinancialYear::orderByDesc('start_date')->get();
        
        // Determine selected financial year ID from request or use current
        $selectedFinancialYearId = $request->get('financial_year_id', $defaultFY ? $defaultFY->id : null);
        
        $selectedFY = null;
        if ($selectedFinancialYearId) {
            $selectedFY = \App\Models\FinancialYear::find($selectedFinancialYearId);
        }
        
        // Get completed payrolls for the selected financial year (same logic as main method)
        $payrollQuery = EmployeePayrollAttendancePayoutMonthStatus::where('status', 'completed')
            ->orderByDesc('payout_year')
            ->orderByDesc('payout_month');
            
        // Apply financial year filtering if a specific FY is selected
        if ($selectedFY) {
            $startYear = $selectedFY->start_date->year;
            $endYear = $selectedFY->end_date->year;
            $startMonth = $selectedFY->start_date->month;
            $endMonth = $selectedFY->end_date->month;
            
            // If financial year spans multiple calendar years
            if ($startYear !== $endYear) {
                $payrollQuery = $payrollQuery->where(function($q) use ($startYear, $endYear, $startMonth, $endMonth) {
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
                $payrollQuery = $payrollQuery->where('payout_year', $startYear)
                                           ->whereBetween('payout_month', [$startMonth, $endMonth]);
            }
        }
        
        $completedPayrolls = $payrollQuery->get();
        
        // Get month-wise data for comparison (same logic as main method)
        $monthlyData = [];
        $chartData = [
            'months' => [],
            'employeeCount' => [],
            'grossPay' => [],
            'totalDeductions' => [],
            'netPay' => []
        ];
        
        foreach ($completedPayrolls as $payroll) {
            $monthKey = $payroll->payout_year . '-' . str_pad($payroll->payout_month, 2, '0', STR_PAD_LEFT);
            $monthLabel = Carbon::createFromDate($payroll->payout_year, $payroll->payout_month, 1)->format('M Y');
            
            // Get payroll data for this month
            $attendances = EmployeePayrollAttendance::where('payout_month_id', $payroll->id)->get();
            
            $employeeCount = $attendances->count();
            $totalGrossPay = $attendances->sum('gross_pay');
            $totalDeductions = $attendances->sum('total_deduction');
            $totalNetPay = $attendances->sum('total_payable');
            
            // Calculate component-wise totals
            $epfTotal = 0;
            $esiTotal = 0;
            $ptTotal = 0;
            $tdsTotal = 0;
            $advanceTotal = 0;
            
            foreach ($attendances as $attendance) {
                $deductions = json_decode($attendance->deductions, true) ?? [];
                
                foreach ($deductions as $componentId => $deduction) {
                    if (isset($deduction['applicable']) && $deduction['applicable'] && isset($deduction['value'])) {
                        switch ($componentId) {
                            case '1': // EPF
                                $epfTotal += $deduction['value'];
                                break;
                            case '2': // ESI
                                $esiTotal += $deduction['value'];
                                break;
                            case '3': // PT
                                $ptTotal += $deduction['value'];
                                break;
                            case '4': // TDS
                                $tdsTotal += $deduction['value'];
                                break;
                            case '5': // Advance
                                $advanceTotal += $deduction['value'];
                                break;
                        }
                    }
                }
            }
            
            $monthlyData[$monthKey] = [
                'label' => $monthLabel,
                'employee_count' => $employeeCount,
                'gross_pay' => $totalGrossPay,
                'total_deductions' => $totalDeductions,
                'net_pay' => $totalNetPay,
                'epf' => $epfTotal,
                'esi' => $esiTotal,
                'pt' => $ptTotal,
                'tds' => $tdsTotal,
                'advance' => $advanceTotal
            ];
            
            // Prepare chart data
            $chartData['months'][] = $monthLabel;
            $chartData['employeeCount'][] = $employeeCount;
            $chartData['grossPay'][] = $totalGrossPay;
            $chartData['totalDeductions'][] = $totalDeductions;
            $chartData['netPay'][] = $totalNetPay;
        }
        
        // Calculate month-over-month comparisons
        $comparisons = [];
        $monthKeys = array_keys($monthlyData);
        
        for ($i = 0; $i < count($monthKeys) - 1; $i++) {
            $currentMonthKey = $monthKeys[$i];
            $previousMonthKey = $monthKeys[$i + 1];
            
            $current = $monthlyData[$currentMonthKey];
            $previous = $monthlyData[$previousMonthKey];
            
            $comparison = [
                'current_month' => $current['label'],
                'previous_month' => $previous['label'],
                'employee_count' => $this->calculateChange($current['employee_count'], $previous['employee_count']),
                'gross_pay' => $this->calculateChange($current['gross_pay'], $previous['gross_pay']),
                'total_deductions' => $this->calculateChange($current['total_deductions'], $previous['total_deductions']),
                'net_pay' => $this->calculateChange($current['net_pay'], $previous['net_pay']),
                'epf' => $this->calculateChange($current['epf'], $previous['epf']),
                'esi' => $this->calculateChange($current['esi'], $previous['esi']),
                'pt' => $this->calculateChange($current['pt'], $previous['pt']),
                'tds' => $this->calculateChange($current['tds'], $previous['tds']),
                'advance' => $this->calculateChange($current['advance'], $previous['advance'])
            ];
            
            $comparisons[] = $comparison;
        }

        // Get company settings for logo
        $companySettings = DB::table('company_settings')->first();

        // Generate PDF
        $pdf = \PDF::loadView('reports.pdf.analytical-payroll-comparison', compact(
            'selectedFY',
            'monthlyData',
            'comparisons',
            'chartData',
            'companySettings'
        ));
        
        // Set paper size and orientation
        $pdf->setPaper('A4', 'landscape');
        
        // Generate filename
        $filename = 'analytical-payroll-comparison-' . 
                   ($selectedFY ? $selectedFY->year_name : 'all-years') . '-' . 
                   date('Y-m-d') . '.pdf';
        
        return $pdf->download($filename);
    }

    /**
     * Calculate percentage change between two values
     */
    private function calculateChange($current, $previous)
    {
        if ($previous == 0) {
            return [
                'current' => $current,
                'previous' => $previous,
                'difference' => $current,
                'percentage' => $current > 0 ? 100 : 0,
                'is_increase' => $current > 0
            ];
        }
        
        $difference = $current - $previous;
        $percentage = ($difference / $previous) * 100;
        
        return [
            'current' => $current,
            'previous' => $previous,
            'difference' => $difference,
            'percentage' => round($percentage, 2),
            'is_increase' => $difference > 0
        ];
    }

    /**
     * Helper: list of finalized months for selects
     */
    private function getFinalizedMonths()
    {
        return EmployeePayrollAttendancePayoutMonthStatus::where('status', 'completed')
            ->orderByDesc('payout_year')
            ->orderByDesc('payout_month')
            ->get()
            ->map(function ($row) {
                return [
                    'key' => $row->payout_month . '-' . $row->payout_year,
                    'label' => Carbon::createFromDate($row->payout_year, $row->payout_month, 1)->format('M Y'),
                    'month' => $row->payout_month,
                    'year' => $row->payout_year
                ];
            });
    }

    /**
     * Build aggregated analytics for a month (cached 10 min)
     */
    private function buildMonthAnalytics(int $month, int $year)
    {
        $cacheKey = "payroll.analytics.$year.$month";
        return Cache::remember($cacheKey, 600, function () use ($month, $year) {
            $payout = EmployeePayrollAttendancePayoutMonthStatus::where([
                'payout_month' => $month,
                'payout_year' => $year,
                'status' => 'completed'
            ])->first();
            if (!$payout) return null;

            $attendances = EmployeePayrollAttendance::where('payout_month_id', $payout->id)->get();
            if ($attendances->isEmpty()) return null;

            $employeeCount = $attendances->count();
            $totalGross = $attendances->sum('gross_pay');
            $totalNet = $attendances->sum('total_payable');

            $componentTotals = [];
            $calculatedTotalDeduction = 0; // Calculate total deductions properly
            
            // Get master component data for proper naming
            $salaryComponents = \App\Models\SalaryComponent::where('status', '1')->get()->keyBy('id');
            $statutoryComponents = \App\Models\StatutoryComponent::where('status', '1')->get()->keyBy('id');
            
            // Process each attendance record like in generatePayrollReport
            foreach ($attendances as $attendance) {
                $earnings = json_decode($attendance->earnings, true) ?: [];
                $deductions = json_decode($attendance->deductions, true) ?: [];
                
                // Find advance deduction from stored deductions by name (like in generatePayrollReport)
                $storedAdvanceData = null;
                $advanceComponentId = null;
                foreach ($deductions as $componentId => $deduction) {
                    if (isset($deduction['name']) && 
                        (strtoupper($deduction['name']) === 'ADVC' || 
                         strtoupper($deduction['name']) === 'ADVANCE' ||
                         stripos($deduction['name'], 'advance') !== false)) {
                        $storedAdvanceData = $deduction;
                        $advanceComponentId = $componentId;
                        break;
                    }
                }
                
                // Calculate current advance deductions (in case new advances were added after finalization)
                $currentAdvanceDeduction = $this->calculateAdvanceDeduction(
                    $attendance->emp_id, 
                    $month, 
                    $year
                );
                
                // Determine the final advance value to use (like in generatePayrollReport)
                $finalAdvanceValue = 0;
                $finalAdvanceApplicable = false;
                
                if ($storedAdvanceData && $storedAdvanceData['applicable'] && $storedAdvanceData['value'] > 0) {
                    // Use stored advance data if it exists and has value
                    $finalAdvanceValue = $storedAdvanceData['value'];
                    $finalAdvanceApplicable = true;
                } elseif ($currentAdvanceDeduction > 0) {
                    // Use current advance calculation if no stored data or stored data is zero
                    $finalAdvanceValue = $currentAdvanceDeduction;
                    $finalAdvanceApplicable = true;
                }
                
                // Add advance deduction with 'advance' key for consistency (like in generatePayrollReport)
                $deductions['advance'] = [
                    'value' => $finalAdvanceValue,
                    'applicable' => $finalAdvanceApplicable,
                    'name' => 'Advance',
                    'default_value' => $finalAdvanceValue,
                    'overridden' => false,
                    'type' => 'advance'
                ];
                
                // Remove the original ADVC entry to prevent duplication (like in generatePayrollReport)
                if ($advanceComponentId !== null) {
                    unset($deductions[$advanceComponentId]);
                }
                
                // Calculate total deductions from the actual deductions array to avoid duplication
                $attendanceDeductionTotal = 0;
                foreach ($deductions as $deduction) {
                    if (isset($deduction['applicable']) && $deduction['applicable'] && isset($deduction['value'])) {
                        $attendanceDeductionTotal += $deduction['value'];
                    }
                }
                $calculatedTotalDeduction += $attendanceDeductionTotal;

                // Process earnings components - all applicable earnings go to earnings
                foreach ($earnings as $id => $row) {
                    if (!isset($row['value']) || !($row['applicable'] ?? false) || $row['value'] <= 0) continue;
                    
                    // Create unique key for earnings to avoid conflicts with deductions
                    $componentKey = 'earning_' . $id;
                    
                    // Use the stored name directly as it's already correct
                    $componentName = $row['name'] ?? 'Unknown';
                    
                    $componentTotals[$componentKey]['earnings'] = ($componentTotals[$componentKey]['earnings'] ?? 0) + $row['value'];
                    $componentTotals[$componentKey]['deductions'] = 0; // Always 0 for earnings
                    $componentTotals[$componentKey]['name'] = $componentName;
                    $componentTotals[$componentKey]['type'] = 'earning';
                }
                
                // Process deductions components - all applicable deductions go to deductions
                foreach ($deductions as $id => $row) {
                    if (!isset($row['value']) || !($row['applicable'] ?? false) || $row['value'] <= 0) continue;
                    
                    // Skip inactive ADVC components (but allow active advance)
                    if ($id !== 'advance' && isset($row['name']) && 
                        (strtoupper($row['name']) === 'ADVC' || 
                         strtoupper($row['name']) === 'ADVANCE' ||
                         stripos($row['name'], 'advance') !== false) &&
                        (!$row['applicable'] || $row['value'] <= 0)) {
                        continue; // Skip inactive ADVC components
                    }
                    
                    // Create unique key for deductions to avoid conflicts with earnings
                    $componentKey = ($id === 'advance') ? 'deduction_advance' : 'deduction_' . $id;
                    
                    // Use the stored name directly as it's already correct
                    $componentName = $row['name'] ?? 'Unknown';
                    
                    $componentTotals[$componentKey]['deductions'] = ($componentTotals[$componentKey]['deductions'] ?? 0) + $row['value'];
                    $componentTotals[$componentKey]['earnings'] = 0; // Always 0 for deductions
                    $componentTotals[$componentKey]['name'] = $componentName;
                    $componentTotals[$componentKey]['type'] = 'deduction';
                }
            }

            // Separate earnings and deductions for better chart visualization
            $earningsComponents = [];
            $deductionsComponents = [];
            
            foreach ($componentTotals as $id => $component) {
                // Include in earnings if it has earnings value > 0
                if (($component['earnings'] ?? 0) > 0) {
                    $earningsComponents[$id] = $component;
                    $earningsComponents[$id]['type'] = 'earning'; // Ensure type is set
                }
                
                // Include in deductions if it has deductions value > 0
                if (($component['deductions'] ?? 0) > 0) {
                    $deductionsComponents[$id] = $component;
                    $deductionsComponents[$id]['type'] = 'deduction'; // Ensure type is set
                }
            }

            return [
                'month' => $month,
                'year' => $year,
                'label' => Carbon::createFromDate($year, $month, 1)->format('F Y'),
                'employee_count' => $employeeCount,
                'gross_total' => $totalGross,
                'deduction_total' => $calculatedTotalDeduction, // Use calculated total deductions
                'net_total' => $totalNet,
                'avg_gross' => $employeeCount ? round($totalGross / $employeeCount, 2) : 0,
                'avg_net' => $employeeCount ? round($totalNet / $employeeCount, 2) : 0,
                'component_totals' => $componentTotals,
                'earnings_components' => $earningsComponents,
                'deductions_components' => $deductionsComponents,
            ];
        });
    }

    /**
     * Diff two analytics arrays
     */
    private function compareAnalytics(array $base, array $next)
    {
        $comparison = [
            'base' => $base,
            'next' => $next,
            'gross_diff' => $next['gross_total'] - $base['gross_total'],
            'net_diff' => $next['net_total'] - $base['net_total'],
            'deduction_diff' => $next['deduction_total'] - $base['deduction_total'],
            'components' => []
        ];

        $allIds = collect(array_keys($base['component_totals']))
            ->merge(array_keys($next['component_totals']))
            ->unique();

        foreach ($allIds as $id) {
            $b = $base['component_totals'][$id] ?? ['earnings' => 0, 'deductions' => 0, 'name' => 'C'.$id];
            $n = $next['component_totals'][$id] ?? ['earnings' => 0, 'deductions' => 0, 'name' => 'C'.$id];
            $comparison['components'][$id] = [
                'name' => $b['name'] ?? $n['name'],
                'base_earnings' => $b['earnings'] ?? 0,
                'next_earnings' => $n['earnings'] ?? 0,
                'earnings_diff' => ($n['earnings'] ?? 0) - ($b['earnings'] ?? 0),
                'base_deductions' => $b['deductions'] ?? 0,
                'next_deductions' => $n['deductions'] ?? 0,
                'deductions_diff' => ($n['deductions'] ?? 0) - ($b['deductions'] ?? 0),
            ];
        }

        return $comparison;
    }
    public function payrollReport()
    {
        // Get all completed payroll months
        $completedMonths = EmployeePayrollAttendancePayoutMonthStatus::where('status', 'completed')
            ->orderByDesc('payout_year')
            ->orderByDesc('payout_month')
            ->get()
            ->map(function ($month) {
                return [
                    'value' => $month->payout_month . '-' . $month->payout_year,
                    'label' => Carbon::createFromDate($month->payout_year, $month->payout_month, 1)->format('M Y')
                ];
            });

        // Get all active components for filters (only status = '1')
        $earningComponents = SalaryComponent::where('type', 'earning')
            ->where('status', '1')
            ->orderBy('id')
            ->get()
            ->merge(
                StatutoryComponent::where('type', 'earning')
                    ->where('status', '1')
                    ->orderBy('id')
                    ->get()
            );

        $deductionComponents = StatutoryComponent::where('type', 'deduction')
            ->where('status', '1')
            ->orderBy('id')
            ->get()
            ->merge(
                SalaryComponent::where('type', 'deduction')
                    ->where('status', '1')
                    ->orderBy('id')
                    ->get()
            );

        $employees = EmployeeBasicDetail::orderBy('name')->get();

        return view('payroll.reports.index', compact(
            'completedMonths',
            'earningComponents',
            'deductionComponents',
            'employees'
        ));
    }

    public function generatePayrollReport(Request $request)
    {
        $request->validate([
            'months' => 'required|array',
            'months.*' => 'required|string',
            'component_filters' => 'nullable|array',
            'employees' => 'nullable|array',
            'view_type' => 'required|in:monthly,consolidated'
        ]);

        $filteredEarningIds = [];
        $filteredDeductionIds = [];

        if ($request->filled('component_filters')) {
            foreach ($request->component_filters as $filter) {
                [$type, $id] = explode('_', $filter);
                if ($type === 'earning') {
                    $filteredEarningIds[] = (int)$id;
                } elseif ($type === 'deduction') {
                    $filteredDeductionIds[] = (int)$id;
                }
            }
        }

        $viewType = $request->view_type;
        // Process selected months
        $selectedMonths = collect($request->months)->map(function ($monthYear) {
            list($month, $year) = explode('-', $monthYear);
            return [
                'month' => (int)$month,
                'year' => (int)$year,
                'label' => Carbon::createFromDate($year, $month, 1)->format('M Y')
            ];
        });

        if (!empty($filteredEarningIds)) {
            $earningComponents = $earningComponents->filter(function($comp) use ($filteredEarningIds) {
                return in_array($comp->id, $filteredEarningIds);
            });
        }
    
        if (!empty($filteredDeductionIds)) {
            $deductionComponents = $deductionComponents->filter(function($comp) use ($filteredDeductionIds) {
                return in_array($comp->id, $filteredDeductionIds);
            });
        }

        // Get payout month IDs
        $payoutMonthIds = EmployeePayrollAttendancePayoutMonthStatus::where('status', 'completed')
            ->where(function ($query) use ($selectedMonths) {
                foreach ($selectedMonths as $month) {
                    $query->orWhere(function ($q) use ($month) {
                        $q->where('payout_month', $month['month'])
                          ->where('payout_year', $month['year']);
                    });
                }
            })
            ->pluck('id');

        // Base query for attendances
        $attendancesQuery = EmployeePayrollAttendance::with([
            'employee' => function($query) {
                $query->with([
                    'salaryComponents' => function($q) {
                        $q->withTrashed();
                    },
                    'statutoryComponents' => function($q) {
                        $q->withTrashed();
                    }
                ]);
            },
            'salaryOverrides',
            'statutoryOverrides',
            'payoutMonth'
        ])
        ->whereIn('payout_month_id', $payoutMonthIds);

        // Apply employee filters if any
        if ($request->filled('employees')) {
            $attendancesQuery->whereIn('emp_id', $request->employees);
        }

        $attendances = $attendancesQuery->get();

        // Process advance deductions for finalized payrolls
        $attendances->transform(function ($attendance) {
            $deductions = is_array($attendance->deductions) ? $attendance->deductions : (json_decode($attendance->deductions, true) ?? []);
            
            // Find advance deduction from stored deductions by name
            $storedAdvanceData = null;
            $advanceComponentId = null;
            foreach ($deductions as $componentId => $deduction) {
                if (isset($deduction['name']) && 
                    (strtoupper($deduction['name']) === 'ADVC' || 
                     strtoupper($deduction['name']) === 'ADVANCE' ||
                     stripos($deduction['name'], 'advance') !== false)) {
                    $storedAdvanceData = $deduction;
                    $advanceComponentId = $componentId;
                    break;
                }
            }
            
            // Also calculate current advance deductions (in case new advances were added after finalization)
            $currentAdvanceDeduction = $this->calculateAdvanceDeduction(
                $attendance->emp_id, 
                $attendance->payoutMonth->payout_month, 
                $attendance->payoutMonth->payout_year
            );
            
            // Determine the final advance value to use
            $finalAdvanceValue = 0;
            $finalAdvanceApplicable = false;
            
            if ($storedAdvanceData && $storedAdvanceData['applicable'] && $storedAdvanceData['value'] > 0) {
                // Use stored advance data if it exists and has value
                $finalAdvanceValue = $storedAdvanceData['value'];
                $finalAdvanceApplicable = true;
            } elseif ($currentAdvanceDeduction > 0) {
                // Use current advance calculation if no stored data or stored data is zero
                $finalAdvanceValue = $currentAdvanceDeduction;
                $finalAdvanceApplicable = true;
            }
            
            // Add advance deduction with 'advance' key for consistency
            $deductions['advance'] = [
                'value' => $finalAdvanceValue,
                'applicable' => $finalAdvanceApplicable,
                'name' => 'Advance',
                'default_value' => $finalAdvanceValue,
                'overridden' => false,
                'type' => 'advance'
            ];
            
            // Remove the original ADVC entry to prevent duplication
            if ($advanceComponentId !== null) {
                unset($deductions[$advanceComponentId]);
            }

            // --- Dynamic EPF Wage Calculation ---
            $earnings = is_array($attendance->earnings) ? $attendance->earnings : (json_decode($attendance->earnings, true) ?? []);
            $epfComponentIds = [];
            if ($attendance->employee && $attendance->employee->statutoryComponents) {
                foreach ($attendance->employee->statutoryComponents as $statComp) {
                    if ($statComp->statutory_component_id == 1 && $statComp->epf_option) {
                        $epfComponentIds = array_keys($earnings);
                        break;
                    }
                }
            }
            $rawEpfWage = 0;
            foreach ($epfComponentIds as $componentId) {
                if (isset($earnings[$componentId]) && (!isset($earnings[$componentId]['applicable']) || $earnings[$componentId]['applicable'])) {
                    $rawEpfWage += $earnings[$componentId]['value'] ?? 0;
                }
            }
            $epfOption = 'restrict_15000';
            $employeeStatutoryComponent = null;
            if ($attendance->employee && $attendance->employee->statutoryComponents) {
                $employeeStatutoryComponent = $attendance->employee->statutoryComponents
                    ->where('statutory_component_id', 1)
                    ->whereNull('deleted_at')
                    ->first();
            }
            if ($employeeStatutoryComponent && $employeeStatutoryComponent->epf_option) {
                $epfOption = $employeeStatutoryComponent->epf_option;
            }
            switch ($epfOption) {
                case 'restrict_15000':
                    $epfWage = min(15000, $rawEpfWage);
                    break;
                case '12_percent':
                case 'manual_value':
                    $epfWage = $rawEpfWage;
                    break;
                default:
                    $epfWage = min(15000, $rawEpfWage);
            }
            $attendance->epfWage = round($epfWage, 2);
            // --- END Dynamic EPF Wage Calculation ---
            
            // Calculate total deductions from the actual deductions array to avoid duplication
            $calculatedTotalDeductions = 0;
            foreach ($deductions as $deduction) {
                if (isset($deduction['applicable']) && $deduction['applicable'] && isset($deduction['value'])) {
                    $calculatedTotalDeductions += $deduction['value'];
                }
            }
            
            // Update attendance totals with calculated values
            $attendance->total_deduction = $calculatedTotalDeductions;
            $attendance->total_payable = $attendance->gross_pay - $calculatedTotalDeductions;
            
            $attendance->deductions = $deductions;
            return $attendance;
        });

        $consolidatedData = null;
        if ($viewType === 'consolidated') {
            $consolidatedData = $attendances->groupBy('emp_id')->map(function ($empAttendances) {
                return [
                    'employee' => $empAttendances->first()->employee,
                    'employee_worked_days' => $empAttendances->sum('employee_worked_days'),
                    'total_working_days' => $empAttendances->sum('total_working_days'),
                    'gross_pay' => $empAttendances->sum('gross_pay'),
                    'epfWage' => $empAttendances->sum('epfWage'),
                    'total_deduction' => $empAttendances->sum('total_deduction'),
                    'total_payable' => $empAttendances->sum('total_payable'),
                    'earnings' => $this->sumComponents($empAttendances, 'earnings'),
                    'deductions' => $this->sumComponents($empAttendances, 'deductions'),
                ];
            });
        }

        // Group by month for the view
        $groupedAttendances = $attendances->groupBy(function ($attendance) {
            return Carbon::createFromDate(
                $attendance->payoutMonth->payout_year,
                $attendance->payoutMonth->payout_month,
                1
            )->format('M Y');
        });

        // Get all active components for the view (only status = '1')
        $earningComponents = SalaryComponent::where('type', 'earning')
            ->where('status', '1')
            ->orderBy('id')
            ->get()
            ->merge(
                StatutoryComponent::where('type', 'earning')
                    ->where('status', '1')
                    ->orderBy('id')
                    ->get()
            );

        $deductionComponents = StatutoryComponent::where('type', 'deduction')
            ->where('status', '1')
            ->orderBy('id')
            ->get()
            ->merge(
                SalaryComponent::where('type', 'deduction')
                    ->where('status', '1')
                    ->orderBy('id')
                    ->get()
            );

        // Filter components that have actual data for any employee
        $earningComponents = $earningComponents->filter(function($component) use ($attendances) {
            foreach ($attendances as $attendance) {
                $earnings = is_array($attendance->earnings) ? $attendance->earnings : (json_decode($attendance->earnings, true) ?? []);
                $componentData = $earnings[$component->id] ?? null;
                if ($componentData && 
                    (($componentData['applicable'] ?? false) || 
                     (($componentData['value'] ?? 0) > 0))) {
                    return true;
                }
            }
            return false;
        });

        $deductionComponents = $deductionComponents->filter(function($component) use ($attendances) {
            foreach ($attendances as $attendance) {
                $deductions = is_array($attendance->deductions) ? $attendance->deductions : (json_decode($attendance->deductions, true) ?? []);
                $componentData = $deductions[$component->id] ?? null;
                if ($componentData && 
                    (($componentData['applicable'] ?? false) || 
                     (($componentData['value'] ?? 0) > 0))) {
                    return true;
                }
            }
            return false;
        });

        // Reset collection keys to ensure proper indexing
        $earningComponents = $earningComponents->values();
        $deductionComponents = $deductionComponents->values();

        // Check if any employee has advance deductions
        $hasAdvanceDeductions = false;
        foreach ($attendances as $attendance) {
            $deductions = is_array($attendance->deductions) ? $attendance->deductions : (json_decode($attendance->deductions, true) ?? []);
            if (isset($deductions['advance']['applicable']) && 
                $deductions['advance']['applicable'] && 
                ($deductions['advance']['value'] ?? 0) > 0) {
                $hasAdvanceDeductions = true;
                break;
            }
        }

        // Calculate totals
        $totals = [
            'gross_pay' => $attendances->sum('gross_pay'),
            'total_deduction' => $attendances->sum('total_deduction'),
            'net_pay' => $attendances->sum('total_payable'),
            'epfWage' => $attendances->sum('epfWage')
        ];

        // Get applied filters for the view
        $appliedFilters = [
            'months' => $selectedMonths->pluck('label')->toArray(),
            'components' => $request->component_filters ?? [],
            'employees' => $request->employees ?? []
        ];

        return view('payroll.reports.results', compact(
            'groupedAttendances',
            'earningComponents',
            'deductionComponents',
            'totals',
            'appliedFilters',
            'selectedMonths',
            'viewType',
            'consolidatedData',
            'hasAdvanceDeductions'
        ));
    }

    private function sumComponents($attendances, $type)
    {
        $result = [];
        foreach ($attendances as $attendance) {
            $items = $attendance->$type;
            
            if (!is_array($items)) {
                try {
                    $items = json_decode($items, true) ?? [];
                } catch (\Exception $e) {
                    $items = [];
                }
            }
            
            foreach ($items as $id => $component) {
                $id = (string)$id; // Cast to string
                $value = is_array($component) 
                    ? ($component['value'] ?? 0)
                    : (is_numeric($component) ? $component : 0);
                    
                $result[$id] = ($result[$id] ?? 0) + $value;
            }
        }
        return $result;
    }

    public function exportPayrollReport(Request $request)
    {
        $request->validate([
            'months' => 'required|array',
            'months.*' => 'required|string',
            'component_filters' => 'nullable|array',
            'employees' => 'nullable|array',
            'view_type' => 'required|in:monthly,consolidated'
        ]);

        $filteredEarningIds = [];
        $filteredDeductionIds = [];

        if ($request->filled('component_filters')) {
            foreach ($request->component_filters as $filter) {
                [$type, $id] = explode('_', $filter);
                if ($type === 'earning') {
                    $filteredEarningIds[] = (int)$id;
                } elseif ($type === 'deduction') {
                    $filteredDeductionIds[] = (int)$id;
                }
            }
        }

        $viewType = $request->view_type;
        // Process selected months
        $selectedMonths = collect($request->months)->map(function ($monthYear) {
            list($month, $year) = explode('-', $monthYear);
            return [
                'month' => (int)$month,
                'year' => (int)$year,
                'label' => Carbon::createFromDate($year, $month, 1)->format('M Y')
            ];
        });

        // Get payout month IDs
        $payoutMonthIds = EmployeePayrollAttendancePayoutMonthStatus::where('status', 'completed')
            ->where(function ($query) use ($selectedMonths) {
                foreach ($selectedMonths as $month) {
                    $query->orWhere(function ($q) use ($month) {
                        $q->where('payout_month', $month['month'])
                          ->where('payout_year', $month['year']);
                    });
                }
            })
            ->pluck('id');

        // Base query for attendances
        $attendancesQuery = EmployeePayrollAttendance::with([
            'employee' => function($query) {
                $query->with([
                    'salaryComponents' => function($q) {
                        $q->withTrashed();
                    },
                    'statutoryComponents' => function($q) {
                        $q->withTrashed();
                    }
                ]);
            },
            'salaryOverrides',
            'statutoryOverrides',
            'payoutMonth'
        ])
        ->whereIn('payout_month_id', $payoutMonthIds);

        // Apply employee filters if any
        if ($request->filled('employees')) {
            $attendancesQuery->whereIn('emp_id', $request->employees);
        }

        $attendances = $attendancesQuery->get();

        // Process advance deductions for finalized payrolls
        $attendances->transform(function ($attendance) {
            // Decode earnings and deductions if they are JSON strings
            $earnings = is_array($attendance->earnings) ? $attendance->earnings : (json_decode($attendance->earnings, true) ?? []);
            $deductions = is_array($attendance->deductions) ? $attendance->deductions : (json_decode($attendance->deductions, true) ?? []);
            
            // Find advance deduction from stored deductions by name
            $storedAdvanceData = null;
            $advanceComponentId = null;
            foreach ($deductions as $componentId => $deduction) {
                if (isset($deduction['name']) && 
                    (strtoupper($deduction['name']) === 'ADVC' || 
                     strtoupper($deduction['name']) === 'ADVANCE' ||
                     stripos($deduction['name'], 'advance') !== false)) {
                    $storedAdvanceData = $deduction;
                    $advanceComponentId = $componentId;
                    break;
                }
            }
            
            // Also calculate current advance deductions (in case new advances were added after finalization)
            $currentAdvanceDeduction = $this->calculateAdvanceDeduction(
                $attendance->emp_id, 
                $attendance->payoutMonth->payout_month, 
                $attendance->payoutMonth->payout_year
            );
            
            // Determine the final advance value to use
            $finalAdvanceValue = 0;
            $finalAdvanceApplicable = false;
            
            if ($storedAdvanceData && $storedAdvanceData['applicable'] && $storedAdvanceData['value'] > 0) {
                // Use stored advance data if it exists and has value
                $finalAdvanceValue = $storedAdvanceData['value'];
                $finalAdvanceApplicable = true;
            } elseif ($currentAdvanceDeduction > 0) {
                // Use current advance calculation if no stored data or stored data is zero
                $finalAdvanceValue = $currentAdvanceDeduction;
                $finalAdvanceApplicable = true;
            }
            
            // Add advance deduction with 'advance' key for consistency
            $deductions['advance'] = [
                'value' => $finalAdvanceValue,
                'applicable' => $finalAdvanceApplicable,
                'name' => 'Advance',
                'default_value' => $finalAdvanceValue,
                'overridden' => false,
                'type' => 'advance'
            ];
            
            // Remove the original ADVC entry to prevent duplication
            if ($advanceComponentId !== null) {
                unset($deductions[$advanceComponentId]);
            }
            
            // Calculate total deductions from the actual deductions array to avoid duplication
            $calculatedTotalDeductions = 0;
            foreach ($deductions as $deduction) {
                if (isset($deduction['applicable']) && $deduction['applicable'] && isset($deduction['value'])) {
                    $calculatedTotalDeductions += $deduction['value'];
                }
            }
            
            // Update attendance totals with calculated values
            $attendance->total_deduction = $calculatedTotalDeductions;
            $attendance->total_payable = $attendance->gross_pay - $calculatedTotalDeductions;
            
            // Set the decoded arrays back to the attendance object
            $attendance->earnings = $earnings;
            $attendance->deductions = $deductions;
            return $attendance;
        });

        // Determine grouping based on view type
        if ($viewType === 'consolidated') {
            // For consolidated view, consolidate data per employee across all months
            $consolidatedData = [];
            
            foreach ($attendances as $attendance) {
                $empId = $attendance->emp_id;
                
                if (!isset($consolidatedData[$empId])) {
                    // Initialize employee record
                    $consolidatedData[$empId] = [
                        'employee' => $attendance->employee,
                        'employee_worked_days' => 0,
                        'total_working_days' => 0,
                        'gross_pay' => 0,
                        'epf_wage' => 0,
                        'total_deduction' => 0,
                        'total_payable' => 0,
                        'earnings' => [],
                        'deductions' => []
                    ];
                }
                
                // Sum up the numeric values
                $consolidatedData[$empId]['employee_worked_days'] += $attendance->employee_worked_days;
                $consolidatedData[$empId]['total_working_days'] += $attendance->total_working_days;
                $consolidatedData[$empId]['gross_pay'] += $attendance->gross_pay;
                $consolidatedData[$empId]['epf_wage'] += $attendance->epf_wage;
                $consolidatedData[$empId]['total_deduction'] += $attendance->total_deduction;
                $consolidatedData[$empId]['total_payable'] += $attendance->total_payable;
                
                // Sum up earnings components
                $earnings = is_array($attendance->earnings) ? $attendance->earnings : (json_decode($attendance->earnings, true) ?? []);
                foreach ($earnings as $componentId => $earningData) {
                    if (!isset($consolidatedData[$empId]['earnings'][$componentId])) {
                        $consolidatedData[$empId]['earnings'][$componentId] = [
                            'value' => 0,
                            'applicable' => $earningData['applicable'] ?? false,
                            'name' => $earningData['name'] ?? ''
                        ];
                    }
                    $consolidatedData[$empId]['earnings'][$componentId]['value'] += $earningData['value'] ?? 0;
                }
                
                // Sum up deduction components
                $deductions = is_array($attendance->deductions) ? $attendance->deductions : (json_decode($attendance->deductions, true) ?? []);
                foreach ($deductions as $componentId => $deductionData) {
                    if (!isset($consolidatedData[$empId]['deductions'][$componentId])) {
                        $consolidatedData[$empId]['deductions'][$componentId] = [
                            'value' => 0,
                            'applicable' => $deductionData['applicable'] ?? false,
                            'name' => $deductionData['name'] ?? ''
                        ];
                    }
                    $consolidatedData[$empId]['deductions'][$componentId]['value'] += $deductionData['value'] ?? 0;
                }
            }
            
            // Convert consolidated data to collection of objects for consistent template access
            $consolidatedAttendances = collect($consolidatedData)->map(function ($data) {
                return (object) $data;
            });
            
            $groupedAttendances = collect(['All Months' => $consolidatedAttendances]);
        } else {
            // For monthly view, group by month
            $groupedAttendances = $attendances->groupBy(function ($attendance) {
                return Carbon::createFromDate(
                    $attendance->payoutMonth->payout_year,
                    $attendance->payoutMonth->payout_month,
                    1
                )->format('M Y');
            });
        }

        // Get all active components for the export
        $earningComponents = SalaryComponent::where('type', 'earning')
            ->where('status', '1')
            ->orderBy('id')
            ->get()
            ->merge(
                StatutoryComponent::where('type', 'earning')
                    ->where('status', '1')
                    ->orderBy('id')
                    ->get()
            );

        $deductionComponents = StatutoryComponent::where('type', 'deduction')
            ->where('status', '1')
            ->orderBy('id')
            ->get()
            ->merge(
                SalaryComponent::where('type', 'deduction')
                    ->where('status', '1')
                    ->orderBy('id')
                    ->get()
            );

        // Apply component filters if specified
        if (!empty($filteredEarningIds)) {
            $earningComponents = $earningComponents->filter(function($comp) use ($filteredEarningIds) {
                return in_array($comp->id, $filteredEarningIds);
            });
        }

        if (!empty($filteredDeductionIds)) {
            $deductionComponents = $deductionComponents->filter(function($comp) use ($filteredDeductionIds) {
                return in_array($comp->id, $filteredDeductionIds);
            });
        }

        // Filter components that have actual data for any employee
        $earningComponents = $earningComponents->filter(function($component) use ($attendances) {
            foreach ($attendances as $attendance) {
                $earnings = is_array($attendance->earnings) ? $attendance->earnings : (json_decode($attendance->earnings, true) ?? []);
                $componentData = $earnings[$component->id] ?? null;
                if ($componentData && 
                    (($componentData['applicable'] ?? false) || 
                     (($componentData['value'] ?? 0) > 0))) {
                    return true;
                }
            }
            return false;
        });

        $deductionComponents = $deductionComponents->filter(function($component) use ($attendances) {
            foreach ($attendances as $attendance) {
                $deductions = is_array($attendance->deductions) ? $attendance->deductions : (json_decode($attendance->deductions, true) ?? []);
                $componentData = $deductions[$component->id] ?? null;
                if ($componentData && 
                    (($componentData['applicable'] ?? false) || 
                     (($componentData['value'] ?? 0) > 0))) {
                    return true;
                }
            }
            return false;
        });

        // Reset collection keys to ensure proper indexing
        $earningComponents = $earningComponents->values();
        $deductionComponents = $deductionComponents->values();        // Generate PDF using mPDF (same as other export methods)
        $mpdf = new Mpdf([
            'format' => 'A4-L', // Landscape orientation for better table fit
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 15,
            'margin_bottom' => 15,
            'default_font' => 'helvetica',
            'default_font_size' => 10,
            'tempDir' => sys_get_temp_dir()
        ]);

        // Render the Blade view to HTML
        $html = view('payroll.reports.export-pdf', compact(
            'groupedAttendances',
            'earningComponents',
            'deductionComponents',
            'selectedMonths',
            'viewType'
        ))->render();

        // Write HTML to mPDF
        $mpdf->WriteHTML($html);

        // Output the PDF as a download
        return response()->streamDownload(function () use ($mpdf) {
            $mpdf->Output();
        }, 'payroll-report-' . now()->format('Y-m-d') . '.pdf');
    }

    public function exportPayrollReportExcel(Request $request)
    {
        $request->validate([
            'months' => 'required|array',
            'months.*' => 'required|string',
            'component_filters' => 'nullable|array',
            'employees' => 'nullable|array',
            'view_type' => 'required|in:monthly,consolidated'
        ]);

        $filteredEarningIds = [];
        $filteredDeductionIds = [];

        if ($request->filled('component_filters')) {
            foreach ($request->component_filters as $filter) {
                [$type, $id] = explode('_', $filter);
                if ($type === 'earning') {
                    $filteredEarningIds[] = (int)$id;
                } elseif ($type === 'deduction') {
                    $filteredDeductionIds[] = (int)$id;
                }
            }
        }

        $viewType = $request->view_type;
        // Process selected months
        $selectedMonths = collect($request->months)->map(function ($monthYear) {
            list($month, $year) = explode('-', $monthYear);
            return [
                'month' => (int)$month,
                'year' => (int)$year,
                'label' => Carbon::createFromDate($year, $month, 1)->format('M Y')
            ];
        });

        // Get payout month IDs
        $payoutMonthIds = EmployeePayrollAttendancePayoutMonthStatus::where('status', 'completed')
            ->where(function ($query) use ($selectedMonths) {
                foreach ($selectedMonths as $month) {
                    $query->orWhere(function ($q) use ($month) {
                        $q->where('payout_month', $month['month'])
                          ->where('payout_year', $month['year']);
                    });
                }
            })
            ->pluck('id');

        // Base query for attendances
        $attendancesQuery = EmployeePayrollAttendance::with([
            'employee' => function($query) {
                $query->with([
                    'salaryComponents' => function($q) {
                        $q->withTrashed();
                    },
                    'statutoryComponents' => function($q) {
                        $q->withTrashed();
                    }
                ]);
            },
            'salaryOverrides',
            'statutoryOverrides',
            'payoutMonth'
        ])
        ->whereIn('payout_month_id', $payoutMonthIds);

        // Apply employee filters if any
        if ($request->filled('employees')) {
            $attendancesQuery->whereIn('emp_id', $request->employees);
        }

        $attendances = $attendancesQuery->get();

        // Process advance deductions for finalized payrolls
        $attendances->transform(function ($attendance) {
            $deductions = is_array($attendance->deductions) ? $attendance->deductions : (json_decode($attendance->deductions, true) ?? []);
            
            // Find advance deduction from stored deductions by name
            $storedAdvanceData = null;
            $advanceComponentId = null;
            foreach ($deductions as $componentId => $deduction) {
                if (isset($deduction['name']) && 
                    (strtoupper($deduction['name']) === 'ADVC' || 
                     strtoupper($deduction['name']) === 'ADVANCE' ||
                     stripos($deduction['name'], 'advance') !== false)) {
                    $storedAdvanceData = $deduction;
                    $advanceComponentId = $componentId;
                    break;
                }
            }
            
            // Also calculate current advance deductions (in case new advances were added after finalization)
            $currentAdvanceDeduction = $this->calculateAdvanceDeduction(
                $attendance->emp_id, 
                $attendance->payoutMonth->payout_month, 
                $attendance->payoutMonth->payout_year
            );
            
            // Determine the final advance value to use
            $finalAdvanceValue = 0;
            $finalAdvanceApplicable = false;
            
            if ($storedAdvanceData && $storedAdvanceData['applicable'] && $storedAdvanceData['value'] > 0) {
                // Use stored advance data if it exists and has value
                $finalAdvanceValue = $storedAdvanceData['value'];
                $finalAdvanceApplicable = true;
            } elseif ($currentAdvanceDeduction > 0) {
                // Use current advance calculation if no stored data or stored data is zero
                $finalAdvanceValue = $currentAdvanceDeduction;
                $finalAdvanceApplicable = true;
            }
            
            // Add advance deduction with 'advance' key for consistency
            $deductions['advance'] = [
                'value' => $finalAdvanceValue,
                'applicable' => $finalAdvanceApplicable,
                'name' => 'Advance',
                'default_value' => $finalAdvanceValue,
                'overridden' => false,
                'type' => 'advance'
            ];
            
            // Remove the original ADVC entry to prevent duplication
            if ($advanceComponentId !== null) {
                unset($deductions[$advanceComponentId]);
            }
            
            // Calculate total deductions from the actual deductions array to avoid duplication
            $calculatedTotalDeductions = 0;
            foreach ($deductions as $deduction) {
                if (isset($deduction['applicable']) && $deduction['applicable'] && isset($deduction['value'])) {
                    $calculatedTotalDeductions += $deduction['value'];
                }
            }
            
            // Update attendance totals with calculated values
            $attendance->total_deduction = $calculatedTotalDeductions;
            $attendance->total_payable = $attendance->gross_pay - $calculatedTotalDeductions;
            
            $attendance->deductions = $deductions;
            return $attendance;
        });

        // Group by month for the export
        $groupedAttendances = $attendances->groupBy(function ($attendance) {
            return Carbon::createFromDate(
                $attendance->payoutMonth->payout_year,
                $attendance->payoutMonth->payout_month,
                1
            )->format('M Y');
        });

        // Get all active components for the export
        $earningComponents = SalaryComponent::where('type', 'earning')
            ->where('status', '1')
            ->orderBy('id')
            ->get()
            ->merge(
                StatutoryComponent::where('type', 'earning')
                    ->where('status', '1')
                    ->orderBy('id')
                    ->get()
            );

        $deductionComponents = StatutoryComponent::where('type', 'deduction')
            ->where('status', '1')
            ->orderBy('id')
            ->get()
            ->merge(
                SalaryComponent::where('type', 'deduction')
                    ->where('status', '1')
                    ->orderBy('id')
                    ->get()
            );

        // Apply component filters if specified
        if (!empty($filteredEarningIds)) {
            $earningComponents = $earningComponents->filter(function($comp) use ($filteredEarningIds) {
                return in_array($comp->id, $filteredEarningIds);
            });
        }

        if (!empty($filteredDeductionIds)) {
            $deductionComponents = $deductionComponents->filter(function($comp) use ($filteredDeductionIds) {
                return in_array($comp->id, $filteredDeductionIds);
            });
        }

        // Filter components that have actual data for any employee
        $earningComponents = $earningComponents->filter(function($component) use ($attendances) {
            foreach ($attendances as $attendance) {
                $earnings = is_array($attendance->earnings) ? $attendance->earnings : (json_decode($attendance->earnings, true) ?? []);
                $componentData = $earnings[$component->id] ?? null;
                if ($componentData && 
                    (($componentData['applicable'] ?? false) || 
                     (($componentData['value'] ?? 0) > 0))) {
                    return true;
                }
            }
            return false;
        });

        $deductionComponents = $deductionComponents->filter(function($component) use ($attendances) {
            foreach ($attendances as $attendance) {
                $deductions = is_array($attendance->deductions) ? $attendance->deductions : (json_decode($attendance->deductions, true) ?? []);
                $componentData = $deductions[$component->id] ?? null;
                if ($componentData && 
                    (($componentData['applicable'] ?? false) || 
                     (($componentData['value'] ?? 0) > 0))) {
                    return true;
                }
            }
            return false;
        });

        // Reset collection keys to ensure proper indexing
        $earningComponents = $earningComponents->values();
        $deductionComponents = $deductionComponents->values();

        // Generate Excel using PhpSpreadsheet
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers
        $row = 1;
        $col = 'A';

        // Title
        $sheet->setCellValue('A1', 'Payroll Report - ' . $selectedMonths->pluck('label')->join(', '));
        $sheet->mergeCells('A1:Z1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $row = 3;

        if ($viewType === 'monthly') {
            foreach ($groupedAttendances as $month => $monthAttendances) {
                // Month header
                $sheet->setCellValue('A' . $row, 'Month: ' . $month);
                $sheet->mergeCells('A' . $row . ':Z' . $row);
                $sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(12);
                $row += 2;

                // Table headers
                $headers = ['Employee ID', 'Employee Name', 'Worked Days', 'Total Days'];
                $col = 'A';
                foreach ($headers as $header) {
                    $sheet->setCellValue($col . $row, $header);
                    $sheet->getStyle($col . $row)->getFont()->setBold(true);
                    $col++;
                }

                // Earning headers
                foreach ($earningComponents as $component) {
                    $sheet->setCellValue($col . $row, $component->short_name);
                    $sheet->getStyle($col . $row)->getFont()->setBold(true);
                    $col++;
                }

                // Gross Pay and EPF
                $sheet->setCellValue($col . $row, 'Gross Pay');
                $sheet->getStyle($col . $row)->getFont()->setBold(true);
                $col++;
                $sheet->setCellValue($col . $row, 'EPF Wage');
                $sheet->getStyle($col . $row)->getFont()->setBold(true);
                $col++;

                // Deduction headers
                foreach ($deductionComponents as $component) {
                    $sheet->setCellValue($col . $row, $component->short_name);
                    $sheet->getStyle($col . $row)->getFont()->setBold(true);
                    $col++;
                }

                // Advance if applicable
                $hasAdvance = false;
                foreach ($monthAttendances as $attendance) {
                    $deductions = is_array($attendance->deductions) ? $attendance->deductions : (json_decode($attendance->deductions, true) ?? []);
                    if (isset($deductions['advance']['applicable']) && $deductions['advance']['applicable']) {
                        $hasAdvance = true;
                        break;
                    }
                }
                if ($hasAdvance) {
                    $sheet->setCellValue($col . $row, 'Advance');
                    $sheet->getStyle($col . $row)->getFont()->setBold(true);
                    $col++;
                }

                // Total Deductions and Net Pay
                $sheet->setCellValue($col . $row, 'Total Deductions');
                $sheet->getStyle($col . $row)->getFont()->setBold(true);
                $col++;
                $sheet->setCellValue($col . $row, 'Net Pay');
                $sheet->getStyle($col . $row)->getFont()->setBold(true);
                $row++;

                // Data rows
                foreach ($monthAttendances as $attendance) {
                    $col = 'A';
                    $sheet->setCellValue($col . $row, $attendance->employee->employee_id);
                    $col++;
                    $sheet->setCellValue($col . $row, $attendance->employee->name);
                    $col++;
                    $sheet->setCellValue($col . $row, $attendance->employee_worked_days);
                    $col++;
                    $sheet->setCellValue($col . $row, $attendance->total_working_days);
                    $col++;

                    // Earnings
                    foreach ($earningComponents as $component) {
                        $earnings = is_array($attendance->earnings) ? $attendance->earnings : (json_decode($attendance->earnings, true) ?? []);
                        $value = $earnings[$component->id]['value'] ?? 0;
                        $sheet->setCellValue($col . $row, $value);
                        $col++;
                    }

                    // Gross Pay and EPF
                    $sheet->setCellValue($col . $row, $attendance->gross_pay);
                    $col++;
                    $sheet->setCellValue($col . $row, $attendance->epfWage);
                    $col++;

                    // Deductions
                    foreach ($deductionComponents as $component) {
                        $deductions = is_array($attendance->deductions) ? $attendance->deductions : (json_decode($attendance->deductions, true) ?? []);
                        $value = $deductions[$component->id]['value'] ?? 0;
                        $sheet->setCellValue($col . $row, $value);
                        $col++;
                    }

                    // Advance
                    if ($hasAdvance) {
                        $deductions = is_array($attendance->deductions) ? $attendance->deductions : (json_decode($attendance->deductions, true) ?? []);
                        $advanceValue = $deductions['advance']['value'] ?? 0;
                        $sheet->setCellValue($col . $row, $advanceValue);
                        $col++;
                    }

                    // Total Deductions and Net Pay
                    $sheet->setCellValue($col . $row, $attendance->total_deduction);
                    $col++;
                    $sheet->setCellValue($col . $row, $attendance->total_payable);
                    $row++;
                }

                // Totals row
                $col = 'A';
                $sheet->setCellValue($col . $row, 'Totals');
                $sheet->getStyle($col . $row)->getFont()->setBold(true);
                $col++;
                $sheet->setCellValue($col . $row, '');
                $col++;
                $sheet->setCellValue($col . $row, $monthAttendances->sum('employee_worked_days'));
                $sheet->getStyle($col . $row)->getFont()->setBold(true);
                $col++;
                $sheet->setCellValue($col . $row, $monthAttendances->sum('total_working_days'));
                $sheet->getStyle($col . $row)->getFont()->setBold(true);
                $col++;

                // Earning totals
                foreach ($earningComponents as $component) {
                    $total = 0;
                    foreach ($monthAttendances as $attendance) {
                        $earnings = is_array($attendance->earnings) ? $attendance->earnings : (json_decode($attendance->earnings, true) ?? []);
                        $total += $earnings[$component->id]['value'] ?? 0;
                    }
                    $sheet->setCellValue($col . $row, $total);
                    $sheet->getStyle($col . $row)->getFont()->setBold(true);
                    $col++;
                }

                // Gross Pay and EPF totals
                $sheet->setCellValue($col . $row, $monthAttendances->sum('gross_pay'));
                $sheet->getStyle($col . $row)->getFont()->setBold(true);
                $col++;
                $sheet->setCellValue($col . $row, $monthAttendances->sum('epfWage'));
                $sheet->getStyle($col . $row)->getFont()->setBold(true);
                $col++;

                // Deduction totals
                foreach ($deductionComponents as $component) {
                    $total = 0;
                    foreach ($monthAttendances as $attendance) {
                        $deductions = is_array($attendance->deductions) ? $attendance->deductions : (json_decode($attendance->deductions, true) ?? []);
                        $total += $deductions[$component->id]['value'] ?? 0;
                    }
                    $sheet->setCellValue($col . $row, $total);
                    $sheet->getStyle($col . $row)->getFont()->setBold(true);
                    $col++;
                }

                // Advance total
                if ($hasAdvance) {
                    $advanceTotal = 0;
                    foreach ($monthAttendances as $attendance) {
                        $deductions = is_array($attendance->deductions) ? $attendance->deductions : (json_decode($attendance->deductions, true) ?? []);
                        $advanceTotal += $deductions['advance']['value'] ?? 0;
                    }
                    $sheet->setCellValue($col . $row, $advanceTotal);
                    $sheet->getStyle($col . $row)->getFont()->setBold(true);
                    $col++;
                }

                // Total Deductions and Net Pay totals
                $sheet->setCellValue($col . $row, $monthAttendances->sum('total_deduction'));
                $sheet->getStyle($col . $row)->getFont()->setBold(true);
                $col++;
                $sheet->setCellValue($col . $row, $monthAttendances->sum('total_payable'));
                $sheet->getStyle($col . $row)->getFont()->setBold(true);
                $row += 3; // Add space between months
            }
        } else {
            // Consolidated view
            $consolidatedData = $attendances->groupBy('emp_id')->map(function ($empAttendances) {
                return [
                    'employee' => $empAttendances->first()->employee,
                    'employee_worked_days' => $empAttendances->sum('employee_worked_days'),
                    'total_working_days' => $empAttendances->sum('total_working_days'),
                    'gross_pay' => $empAttendances->sum('gross_pay'),
                    'epfWage' => $empAttendances->sum('epfWage'),
                    'total_deduction' => $empAttendances->sum('total_deduction'),
                    'total_payable' => $empAttendances->sum('total_payable'),
                    'earnings' => $this->sumComponents($empAttendances, 'earnings'),
                    'deductions' => $this->sumComponents($empAttendances, 'deductions'),
                ];
            });

            // Table headers
            $headers = ['Employee ID', 'Employee Name', 'Worked Days', 'Total Days'];
            $col = 'A';
            foreach ($headers as $header) {
                $sheet->setCellValue($col . $row, $header);
                $sheet->getStyle($col . $row)->getFont()->setBold(true);
                $col++;
            }

            // Earning headers
            foreach ($earningComponents as $component) {
                $sheet->setCellValue($col . $row, $component->short_name);
                $sheet->getStyle($col . $row)->getFont()->setBold(true);
                $col++;
            }

            // Gross Pay and EPF
            $sheet->setCellValue($col . $row, 'Gross Pay');
            $sheet->getStyle($col . $row)->getFont()->setBold(true);
            $col++;
            $sheet->setCellValue($col . $row, 'EPF Wage');
            $sheet->getStyle($col . $row)->getFont()->setBold(true);
            $col++;

            // Deduction headers
            foreach ($deductionComponents as $component) {
                $sheet->setCellValue($col . $row, $component->short_name);
                $sheet->getStyle($col . $row)->getFont()->setBold(true);
                $col++;
            }

            // Advance if applicable
            $hasAdvance = false;
            foreach ($consolidatedData as $data) {
                if (isset($data['deductions']['advance']) && $data['deductions']['advance'] > 0) {
                    $hasAdvance = true;
                    break;
                }
            }
            if ($hasAdvance) {
                $sheet->setCellValue($col . $row, 'Advance');
                $sheet->getStyle($col . $row)->getFont()->setBold(true);
                $col++;
            }

            // Total Deductions and Net Pay
            $sheet->setCellValue($col . $row, 'Total Deductions');
            $sheet->getStyle($col . $row)->getFont()->setBold(true);
            $col++;
            $sheet->setCellValue($col . $row, 'Net Pay');
            $sheet->getStyle($col . $row)->getFont()->setBold(true);
            $row++;

            // Data rows
            foreach ($consolidatedData as $empId => $data) {
                $col = 'A';
                $sheet->setCellValue($col . $row, $data['employee']->employee_id);
                $col++;
                $sheet->setCellValue($col . $row, $data['employee']->name);
                $col++;
                $sheet->setCellValue($col . $row, $data['employee_worked_days']);
                $col++;
                $sheet->setCellValue($col . $row, $data['total_working_days']);
                $col++;

                // Earnings
                foreach ($earningComponents as $component) {
                    $value = $data['earnings'][(string)$component->id] ?? 0;
                    $sheet->setCellValue($col . $row, $value);
                    $col++;
                }

                // Gross Pay and EPF
                $sheet->setCellValue($col . $row, $data['gross_pay']);
                $col++;
                $sheet->setCellValue($col . $row, $data['epfWage']);
                $col++;

                // Deductions
                foreach ($deductionComponents as $component) {
                    $value = $data['deductions'][$component->id] ?? 0;
                    $sheet->setCellValue($col . $row, $value);
                    $col++;
                }

                // Advance
                if ($hasAdvance) {
                    $advanceValue = $data['deductions']['advance'] ?? 0;
                    $sheet->setCellValue($col . $row, $advanceValue);
                    $col++;
                }

                // Total Deductions and Net Pay
                $sheet->setCellValue($col . $row, $data['total_deduction']);
                $col++;
                $sheet->setCellValue($col . $row, $data['total_payable']);
                $row++;
            }

            // Totals row
            $col = 'A';
            $sheet->setCellValue($col . $row, 'Totals');
            $sheet->getStyle($col . $row)->getFont()->setBold(true);
            $col++;
            $sheet->setCellValue($col . $row, '');
            $col++;
            $sheet->setCellValue($col . $row, $consolidatedData->sum('employee_worked_days'));
            $sheet->getStyle($col . $row)->getFont()->setBold(true);
            $col++;
            $sheet->setCellValue($col . $row, $consolidatedData->sum('total_working_days'));
            $sheet->getStyle($col . $row)->getFont()->setBold(true);
            $col++;

            // Earning totals
            foreach ($earningComponents as $component) {
                $total = 0;
                foreach ($consolidatedData as $data) {
                    $total += $data['earnings'][(string)$component->id] ?? 0;
                }
                $sheet->setCellValue($col . $row, $total);
                $sheet->getStyle($col . $row)->getFont()->setBold(true);
                $col++;
            }

            // Gross Pay and EPF totals
            $sheet->setCellValue($col . $row, $consolidatedData->sum('gross_pay'));
            $sheet->getStyle($col . $row)->getFont()->setBold(true);
            $col++;
            $sheet->setCellValue($col . $row, $consolidatedData->sum('epfWage'));
            $sheet->getStyle($col . $row)->getFont()->setBold(true);
            $col++;

            // Deduction totals
            foreach ($deductionComponents as $component) {
                $total = 0;
                foreach ($consolidatedData as $data) {
                    $total += $data['deductions'][$component->id] ?? 0;
                }
                $sheet->setCellValue($col . $row, $total);
                $sheet->getStyle($col . $row)->getFont()->setBold(true);
                $col++;
            }

            // Advance total
            if ($hasAdvance) {
                $advanceTotal = 0;
                foreach ($consolidatedData as $data) {
                    $advanceTotal += $data['deductions']['advance'] ?? 0;
                }
                $sheet->setCellValue($col . $row, $advanceTotal);
                $sheet->getStyle($col . $row)->getFont()->setBold(true);
                $col++;
            }

            // Total Deductions and Net Pay totals
            $sheet->setCellValue($col . $row, $consolidatedData->sum('total_deduction'));
            $sheet->getStyle($col . $row)->getFont()->setBold(true);
            $col++;
            $sheet->setCellValue($col . $row, $consolidatedData->sum('total_payable'));
            $sheet->getStyle($col . $row)->getFont()->setBold(true);
        }

        // Auto-size columns
        foreach (range('A', $col) as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        // Create Excel writer and output
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        
        $filename = 'payroll-report-' . now()->format('Y-m-d') . '.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit;
    }


    /** START - OVERTIME REPORT CONTROLLER FUNCTION - SK - 20.06.2025 */
    public function overtimeReport(Request $request)
    {
        // Redirect to include default month and year if no query parameters are provided
        if (!$request->hasAny(['month', 'year', 'department_id', 'employee_id', 'report_type'])) {
            return redirect()->route('overtime.reports.index', [
                'month' => (int) date('m'),
                'year' => date('Y'),
                'department_id' => '',
                'employee_id' => '',
                'report_type' => 'overtime' // Default to overtime
            ]);
        }

        // Get filter parameters, cast month to integer
        $month = (int) $request->input('month', date('m'));
        $year = $request->input('year', date('Y'));
        $employee_id = $request->input('employee_id');
        $report_type = $request->input('report_type', 'overtime');

        // Determine the table and fields based on report type
        $query = null;
        $records = null;
        $total_hours_or_days = 0;
        $total_amount = 0;
        $avg_hours_or_days = 0;

        if ($report_type === 'overtime') {
            $query = DB::table('employee_ot_details as ot')
                ->join('employee_basic_details as emp', 'ot.emp_id', '=', 'emp.id')
                ->join('employee_payroll_attendance_payout_month_statuses as status', function($join) use ($month, $year) {
                    $join->where('status.payout_month', $month)
                        ->where('status.payout_year', $year)
                        ->where('status.ot_finalized', 1);
                })
                ->select(
                    'emp.id as employee_id',
                    'emp.name as employee_name',
                    'emp.employee_id as employee_code',
                    'ot.payout_month',
                    'ot.payout_year',
                    'ot.ot_hours',
                    'ot.ot_rate',
                    'ot.total_amount'
                )
                ->where('ot.payout_month', $month)
                ->where('ot.payout_year', $year);

            if ($employee_id) {
                $query->where('emp.id', $employee_id);
            }

            $records = $query->orderBy('emp.name')->get();
            $total_hours_or_days = $records->sum('ot_hours');
            $total_amount = $records->sum('total_amount');
            $avg_hours_or_days = $records->avg('ot_hours');
        } else if ($report_type === 'holiday_payout') {
            $query = DB::table('employee_holiday_payout_details as hp')
                ->join('employee_basic_details as emp', 'hp.emp_id', '=', 'emp.id')
                ->join('employee_payroll_attendance_payout_month_statuses as status', function($join) use ($month, $year) {
                    $join->where('status.payout_month', $month)
                        ->where('status.payout_year', $year);
                    // Removed holiday_finalized condition
                })
                ->select(
                    'emp.id as employee_id',
                    'emp.name as employee_name',
                    'emp.employee_id as employee_code',
                    'hp.payout_month',
                    'hp.payout_year',
                    'hp.holiday_work_days as ot_hours',
                    'hp.holiday_work_rate as ot_rate',
                    'hp.total_amount'
                )
                ->where('hp.payout_month', $month)
                ->where('hp.payout_year', $year);

            if ($employee_id) {
                $query->where('emp.id', $employee_id);
            }

            $records = $query->orderBy('emp.name')->get();
            $total_hours_or_days = $records->sum('ot_hours');
            $total_amount = $records->sum('total_amount');
            $avg_hours_or_days = $records->avg('ot_hours');
        }

        $employees = DB::table('employee_basic_details')->select('id', 'name')->orderBy('name')->get();

        // Get month names for dropdown
        $months = [];
        for ($i = 1; $i <= 12; $i++) {
            $months[$i] = date('F', mktime(0, 0, 0, $i, 1));
        }

        // Get years for dropdown (last 5 years)
        $current_year = (int) date('Y');
        $years = range($current_year - 4, $current_year);

        return view('overtime.reports.report-view', compact(
            'records',
            'employees',
            'months',
            'years',
            'month',
            'year',
            'employee_id',
            'total_hours_or_days',
            'total_amount',
            'avg_hours_or_days',
            'report_type'
        ));
    }

    /** EXPORT OVERTIME REPORT - SK - 17.06.2025 */
    public function exportOvertimeReport(Request $request)
    {
        // Get filter parameters
        $month = (int) $request->input('month', date('m'));
        $year = $request->input('year', date('Y'));
        $employee_id = $request->input('employee_id');
        $report_type = $request->input('report_type', 'overtime');

        // Build the query based on report type
        $query = null;
        $records = null;

        if ($report_type === 'overtime') {
            $query = DB::table('employee_ot_details as ot')
                ->join('employee_basic_details as emp', 'ot.emp_id', '=', 'emp.id')
                ->join('employee_payroll_attendance_payout_month_statuses as status', function($join) use ($month, $year) {
                    $join->where('status.payout_month', $month)
                        ->where('status.payout_year', $year)
                        ->where('status.ot_finalized', 1);
                })
                ->select(
                    'emp.id as employee_id',
                    'emp.name as employee_name',
                    'emp.employee_id as employee_code',
                    'ot.payout_month',
                    'ot.payout_year',
                    'ot.ot_hours',
                    'ot.ot_rate',
                    'ot.total_amount'
                )
                ->where('ot.payout_month', $month)
                ->where('ot.payout_year', $year);

            if ($employee_id) {
                $query->where('emp.id', $employee_id);
            }

            $records = $query->orderBy('emp.name')->get();
        } else if ($report_type === 'holiday_payout') {
            $query = DB::table('employee_holiday_payout_details as hp')
                ->join('employee_basic_details as emp', 'hp.emp_id', '=', 'emp.id')
                ->join('employee_payroll_attendance_payout_month_statuses as status', function($join) use ($month, $year) {
                    $join->where('status.payout_month', $month)
                        ->where('status.payout_year', $year);
                    // Removed holiday_finalized condition
                })
                ->select(
                    'emp.id as employee_id',
                    'emp.name as employee_name',
                    'emp.employee_id as employee_code',
                    'hp.payout_month',
                    'hp.payout_year',
                    'hp.holiday_work_days as ot_hours',
                    'hp.holiday_work_rate as ot_rate',
                    'hp.total_amount'
                )
                ->where('hp.payout_month', $month)
                ->where('hp.payout_year', $year);

            if ($employee_id) {
                $query->where('emp.id', $employee_id);
            }

            $records = $query->orderBy('emp.name')->get();
        }

        // Get month names
        $months = [];
        for ($i = 1; $i <= 12; $i++) {
            $months[$i] = date('F', mktime(0, 0, 0, $i, 1));
        }

        // Calculate summary statistics
        $total_hours_or_days = $records->sum('ot_hours');
        $total_amount = $records->sum('total_amount');
        $avg_hours_or_days = $records->count() > 0 ? $records->avg('ot_hours') : 0;

        // Prepare data for PDF
        $data = [
            'records' => $records,
            'months' => $months,
            'month' => $month,
            'year' => $year,
            'total_hours_or_days' => $total_hours_or_days,
            'total_amount' => $total_amount,
            'avg_hours_or_days' => $avg_hours_or_days,
            'report_date' => Carbon::now()->format('d M Y'),
            'report_type' => $report_type
        ];

        // Initialize mPDF
        $mpdf = new Mpdf([
            'format' => 'A4',
            'orientation' => 'P',
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 20,
            'margin_bottom' => 10,
        ]);

        // Render the Blade view to HTML
        $html = view('overtime.reports.export-pdf', $data)->render();

        // Write HTML to mPDF
        $mpdf->WriteHTML($html);

        // Output the PDF as a download
        return response()->streamDownload(function () use ($mpdf) {
            $mpdf->Output();
        }, ($report_type === 'overtime' ? 'overtime' : 'holiday-payout') . '-report-' . $year . '-' . sprintf('%02d', $month) . '.pdf');
    }
    /** END - OVERTIME REPORT CONTROLLER FUNCTION - SK - 20.06.2025 */


    /** START - INCENTIVE REPORT CONTROLLER FUNCTION - SK - 20.06.2025 */
    public function incentiveReport(Request $request)
    {
        // Redirect to include default month and year if no query parameters are provided
        if (!$request->hasAny(['month', 'year', 'department_id', 'employee_id'])) {
            return redirect()->route('incentive.reports.index', [
                'month' => (int) date('m'),
                'year' => date('Y'),
                'department_id' => '',
                'employee_id' => ''
            ]);
        }

        // Get filter parameters, cast month to integer
        $month = (int) $request->input('month', date('m'));
        $year = $request->input('year', date('Y'));
        $employee_id = $request->input('employee_id');

        // Start building the query
        $incentive = DB::table('employee_incentive_details as inc')
            ->join('employee_basic_details as emp', 'inc.emp_id', '=', 'emp.id')
            ->join('employee_payroll_attendance_payout_month_statuses as status', function($join) use ($month, $year) {
                $join->where('status.payout_month', $month)
                    ->where('status.payout_year', $year)
                    ->where('status.incentive_finalized', 1); // Assuming incentive_finalized column exists
            })
            ->select(
                'emp.id as employee_id',
                'emp.name as employee_name',
                'emp.employee_id as employee_code',
                'inc.payout_month',
                'inc.payout_year',
                'inc.incentive_days',
                'inc.incentive_rate',
                'inc.total_amount'
            )
            ->where('inc.payout_month', $month)
            ->where('inc.payout_year', $year);

        if ($employee_id) {
            $incentive->where('emp.id', $employee_id);
        }

        // Get results
        $incentive_records = $incentive->orderBy('emp.name')->get();

        // Calculate summary statistics
        $total_incentive_days = $incentive_records->sum('incentive_days');
        $total_incentive_amount = $incentive_records->sum('total_amount');
        $avg_incentive_days = $incentive_records->avg('incentive_days');

        $employees = DB::table('employee_basic_details')->select('id', 'name')->orderBy('name')->get();

        // Get month names for dropdown
        $months = [];
        for ($i = 1; $i <= 12; $i++) {
            $months[$i] = date('F', mktime(0, 0, 0, $i, 1));
        }

        // Get years for dropdown (last 5 years)
        $current_year = (int) date('Y');
        $years = range($current_year - 4, $current_year);

        return view('incentive.reports.report-view', compact(
            'incentive_records',
            'employees',
            'months',
            'years',
            'month',
            'year',
            'employee_id',
            'total_incentive_days',
            'total_incentive_amount',
            'avg_incentive_days'
        ));
    }

    /** EXPORT INCENTIVE REPORT - SK - 20.06.2025 */
    public function exportIncentiveReport(Request $request)
    {
        // Get filter parameters
        $month = (int) $request->input('month', date('m'));
        $year = $request->input('year', date('Y'));
        $employee_id = $request->input('employee_id');

        // Build the query
        $incentive = DB::table('employee_incentive_details as inc')
            ->join('employee_basic_details as emp', 'inc.emp_id', '=', 'emp.id')
            ->join('employee_payroll_attendance_payout_month_statuses as status', function($join) use ($month, $year) {
                $join->where('status.payout_month', $month)
                    ->where('status.payout_year', $year)
                    ->where('status.incentive_finalized', 1);
            })
            ->select(
                'emp.id as employee_id',
                'emp.name as employee_name',
                'emp.employee_id as employee_code',
                'inc.payout_month',
                'inc.payout_year',
                'inc.incentive_days',
                'inc.incentive_rate',
                'inc.total_amount'
            )
            ->where('inc.payout_month', $month)
            ->where('inc.payout_year', $year);

        if ($employee_id) {
            $incentive->where('emp.id', $employee_id);
        }

        // Get results
        $incentive_records = $incentive->orderBy('emp.name')->get();

        // Get month names
        $months = [];
        for ($i = 1; $i <= 12; $i++) {
            $months[$i] = date('F', mktime(0, 0, 0, $i, 1));
        }

        // Calculate summary statistics
        $total_incentive_days = $incentive_records->sum('incentive_days');
        $total_incentive_amount = $incentive_records->sum('total_amount');
        $avg_incentive_days = $incentive_records->count() > 0 ? $incentive_records->avg('incentive_days') : 0;

        // Prepare data for PDF
        $data = [
            'incentive_records' => $incentive_records,
            'months' => $months,
            'month' => $month,
            'year' => $year,
            'total_incentive_days' => $total_incentive_days,
            'total_incentive_amount' => $total_incentive_amount,
            'avg_incentive_days' => $avg_incentive_days,
            'report_date' => Carbon::now()->format('d M Y'),
        ];

        // Initialize mPDF
        $mpdf = new Mpdf([
            'format' => 'A4',
            'orientation' => 'P',
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 20,
            'margin_bottom' => 10,
        ]);

        // Render the Blade view to HTML
        $html = view('incentive.reports.export-pdf', $data)->render();

        // Write HTML to mPDF
        $mpdf->WriteHTML($html);

        // Output the PDF as a download
        return response()->streamDownload(function () use ($mpdf) {
            $mpdf->Output();
        }, 'incentive-report-' . $year . '-' . sprintf('%02d', $month) . '.pdf');
    }
    /** END - INCENTIVE REPORT CONTROLLER FUNCTION - SK - 20.06.2025 */


/** START - COMBINED REPORT CONTROLLER FUNCTION - SK - 20.06.2025 */
    public function combinedReport(Request $request)
    {
        // Redirect to include default month and year if no query parameters are provided
        if (!$request->hasAny(['month', 'year', 'department_id', 'employee_id'])) {
            return redirect()->route('combined.reports.index', [
                'month' => (int) date('m'),
                'year' => date('Y'),
                'department_id' => '',
                'employee_id' => ''
            ]);
        }

        // Get filter parameters, cast month to integer
        $month = (int) $request->input('month', date('m'));
        $year = $request->input('year', date('Y'));
        $employee_id = $request->input('employee_id');

        // Fetch employees with non-zero consolidated amount using a subquery
        $employees_with_amount = DB::table('employee_basic_details as emp')
            ->leftJoin('employee_holiday_payout_details as hp', function($join) use ($month, $year) {
                $join->on('hp.emp_id', '=', 'emp.id')
                    ->where('hp.payout_month', $month)
                    ->where('hp.payout_year', $year);
            })
            ->leftJoin('employee_ot_details as ot', function($join) use ($month, $year) {
                $join->on('ot.emp_id', '=', 'emp.id')
                    ->where('ot.payout_month', $month)
                    ->where('ot.payout_year', $year);
            })
            ->select(
                'emp.id',
                DB::raw('COALESCE(hp.total_amount, 0) as hp_total'),
                DB::raw('COALESCE(ot.total_amount, 0) as ot_total')
            )
            ->havingRaw('COALESCE(hp_total, 0) + COALESCE(ot_total, 0) > 0');

        if ($employee_id) {
            $employees_with_amount->where('emp.id', $employee_id);
        }

        $employee_ids = $employees_with_amount->pluck('id')->toArray();

        // Fetch Holiday Payout data for employees with non-zero amounts
        $holiday_payout = DB::table('employee_holiday_payout_details as hp')
            ->join('employee_basic_details as emp', 'hp.emp_id', '=', 'emp.id')
            ->join('employee_payroll_attendance_payout_month_statuses as status', function($join) use ($month, $year) {
                $join->where('status.payout_month', $month)
                    ->where('status.payout_year', $year);
            })
            ->select(
                'emp.name as employee_name',
                'hp.holiday_work_days',
                'hp.total_amount as holiday_amount'
            )
            ->where('hp.payout_month', $month)
            ->where('hp.payout_year', $year)
            ->whereIn('emp.id', $employee_ids);

        $holiday_payout_records = $holiday_payout->orderBy('emp.name')->get();

        // Fetch Overtime data for employees with non-zero amounts
        $overtime = DB::table('employee_ot_details as ot')
            ->join('employee_basic_details as emp', 'ot.emp_id', '=', 'emp.id')
            ->join('employee_payroll_attendance_payout_month_statuses as status', function($join) use ($month, $year) {
                $join->where('status.payout_month', $month)
                    ->where('status.payout_year', $year)
                    ->where('status.ot_finalized', 1);
            })
            ->select(
                'emp.name as employee_name',
                'ot.ot_hours',
                'ot.total_amount as ot_amount'
            )
            ->where('ot.payout_month', $month)
            ->where('ot.payout_year', $year)
            ->whereIn('emp.id', $employee_ids);

        $overtime_records = $overtime->orderBy('emp.name')->get();

        // Fetch Consolidated data for employees with non-zero amounts
        $consolidated = DB::table('employee_basic_details as emp')
            ->leftJoin('employee_holiday_payout_details as hp', function($join) use ($month, $year) {
                $join->on('hp.emp_id', '=', 'emp.id')
                    ->where('hp.payout_month', $month)
                    ->where('hp.payout_year', $year);
            })
            ->leftJoin('employee_ot_details as ot', function($join) use ($month, $year) {
                $join->on('ot.emp_id', '=', 'emp.id')
                    ->where('ot.payout_month', $month)
                    ->where('ot.payout_year', $year);
            })
            ->select(
                'emp.name as employee_name',
                DB::raw('COALESCE(hp.total_amount, 0) + COALESCE(ot.total_amount, 0) as consolidated_amount')
            )
            ->whereIn('emp.id', $employee_ids);

        $consolidated_records = $consolidated->orderBy('emp.name')->get();
        $total_consolidated = $consolidated_records->sum('consolidated_amount');

        $employees = DB::table('employee_basic_details')->select('id', 'name')->orderBy('name')->get();

        // Get month names for dropdown
        $months = [];
        for ($i = 1; $i <= 12; $i++) {
            $months[$i] = date('F', mktime(0, 0, 0, $i, 1));
        }

        // Get years for dropdown (last 5 years)
        $current_year = (int) date('Y');
        $years = range($current_year - 4, $current_year);

        return view('combined.reports.report-view', compact(
            'holiday_payout_records',
            'overtime_records',
            'consolidated_records',
            'employees',
            'months',
            'years',
            'month',
            'year',
            'employee_id',
            'total_consolidated'
        ));
    }

    /** EXPORT COMBINED REPORT - SK - 20.06.2025 */
    public function exportCombinedReport(Request $request)
    {
        // Get filter parameters
        $month = (int) $request->input('month', date('m'));
        $year = $request->input('year', date('Y'));
        $employee_id = $request->input('employee_id');

        // Fetch employees with non-zero consolidated amount using a subquery
        $employees_with_amount = DB::table('employee_basic_details as emp')
            ->leftJoin('employee_holiday_payout_details as hp', function($join) use ($month, $year) {
                $join->on('hp.emp_id', '=', 'emp.id')
                    ->where('hp.payout_month', $month)
                    ->where('hp.payout_year', $year);
            })
            ->leftJoin('employee_ot_details as ot', function($join) use ($month, $year) {
                $join->on('ot.emp_id', '=', 'emp.id')
                    ->where('ot.payout_month', $month)
                    ->where('ot.payout_year', $year);
            })
            ->select(
                'emp.id',
                DB::raw('COALESCE(hp.total_amount, 0) as hp_total'),
                DB::raw('COALESCE(ot.total_amount, 0) as ot_total')
            )
            ->havingRaw('COALESCE(hp_total, 0) + COALESCE(ot_total, 0) > 0');

        if ($employee_id) {
            $employees_with_amount->where('emp.id', $employee_id);
        }

        $employee_ids = $employees_with_amount->pluck('id')->toArray();

        // Fetch Holiday Payout data for employees with non-zero amounts
        $holiday_payout = DB::table('employee_holiday_payout_details as hp')
            ->join('employee_basic_details as emp', 'hp.emp_id', '=', 'emp.id')
            ->join('employee_payroll_attendance_payout_month_statuses as status', function($join) use ($month, $year) {
                $join->where('status.payout_month', $month)
                    ->where('status.payout_year', $year);
            })
            ->select(
                'emp.name as employee_name',
                'hp.holiday_work_days',
                'hp.total_amount as holiday_amount'
            )
            ->where('hp.payout_month', $month)
            ->where('hp.payout_year', $year)
            ->whereIn('emp.id', $employee_ids);

        $holiday_payout_records = $holiday_payout->orderBy('emp.name')->get();

        // Fetch Overtime data for employees with non-zero amounts
        $overtime = DB::table('employee_ot_details as ot')
            ->join('employee_basic_details as emp', 'ot.emp_id', '=', 'emp.id')
            ->join('employee_payroll_attendance_payout_month_statuses as status', function($join) use ($month, $year) {
                $join->where('status.payout_month', $month)
                    ->where('status.payout_year', $year)
                    ->where('status.ot_finalized', 1);
            })
            ->select(
                'emp.name as employee_name',
                'ot.ot_hours',
                'ot.total_amount as ot_amount'
            )
            ->where('ot.payout_month', $month)
            ->where('ot.payout_year', $year)
            ->whereIn('emp.id', $employee_ids);

        $overtime_records = $overtime->orderBy('emp.name')->get();

        // Fetch Consolidated data for employees with non-zero amounts
        $consolidated = DB::table('employee_basic_details as emp')
            ->leftJoin('employee_holiday_payout_details as hp', function($join) use ($month, $year) {
                $join->on('hp.emp_id', '=', 'emp.id')
                    ->where('hp.payout_month', $month)
                    ->where('hp.payout_year', $year);
            })
            ->leftJoin('employee_ot_details as ot', function($join) use ($month, $year) {
                $join->on('ot.emp_id', '=', 'emp.id')
                    ->where('ot.payout_month', $month)
                    ->where('ot.payout_year', $year);
            })
            ->select(
                'emp.name as employee_name',
                DB::raw('COALESCE(hp.total_amount, 0) + COALESCE(ot.total_amount, 0) as consolidated_amount')
            )
            ->whereIn('emp.id', $employee_ids);

        $consolidated_records = $consolidated->orderBy('emp.name')->get();
        $total_consolidated = $consolidated_records->sum('consolidated_amount');

        // Get month names for dropdown
        $months = [];
        for ($i = 1; $i <= 12; $i++) {
            $months[$i] = date('F', mktime(0, 0, 0, $i, 1));
        }

        // Prepare data for PDF
        $data = [
            'holiday_payout_records' => $holiday_payout_records,
            'overtime_records' => $overtime_records,
            'consolidated_records' => $consolidated_records,
            'month' => $month,
            'year' => $year,
            'total_consolidated' => $total_consolidated,
            'report_date' => Carbon::now()->format('d M Y'),
            'months' => $months
        ];

        // Initialize mPDF
        $mpdf = new Mpdf([
            'format' => 'A4',
            'orientation' => 'P',
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 20,
            'margin_bottom' => 10,
        ]);

        // Render the Blade view to HTML
        $html = view('combined.reports.export-pdf', $data)->render();

        // Write HTML to mPDF
        $mpdf->WriteHTML($html);

        // Output the PDF as a download
        return response()->streamDownload(function () use ($mpdf) {
            $mpdf->Output();
        }, 'combined-report-' . $year . '-' . sprintf('%02d', $month) . '.pdf');
    }
    /** END - COMBINED REPORT CONTROLLER FUNCTION - SK - 20.06.2025 */

       /** START - COMPARISION REPORT CONTROLLER FUNCTION - KPS - 08.07.2025 */
    public function comparisonReport()
    {
        // Get all completed payroll months
        $availableMonths = EmployeePayrollAttendancePayoutMonthStatus::where('status', 'completed')
            ->orderByDesc('payout_year')
            ->orderByDesc('payout_month')
            ->get()
            ->map(function ($month) {
                return [
                    'value' => $month->payout_month . '-' . $month->payout_year,
                    'label' => Carbon::createFromDate($month->payout_year, $month->payout_month, 1)->format('M Y')
                ];
            });

        $employees = EmployeeBasicDetail::orderBy('name')->get();

        return view('reports.comparission-payroll-report', compact('availableMonths', 'employees'));
    }

    public function generateComparisonReport(Request $request)
    {
        $request->validate([
            'first_month' => 'required|string',
            'second_month' => 'required|string|different:first_month',
            'employee_filter' => 'nullable|exists:employee_basic_details,id'
        ]);

        // Parse months
        list($firstMonth, $firstYear) = explode('-', $request->first_month);
        list($secondMonth, $secondYear) = explode('-', $request->second_month);

        // Get payout month statuses
        $firstPayoutMonth = EmployeePayrollAttendancePayoutMonthStatus::where([
            'payout_month' => $firstMonth,
            'payout_year' => $firstYear,
            'status' => 'completed'
        ])->firstOrFail();

        $secondPayoutMonth = EmployeePayrollAttendancePayoutMonthStatus::where([
            'payout_month' => $secondMonth,
            'payout_year' => $secondYear,
            'status' => 'completed'
        ])->firstOrFail();

        // Build query for attendances
        $firstMonthQuery = EmployeePayrollAttendance::with(['employee'])
            ->where('payout_month_id', $firstPayoutMonth->id);

        $secondMonthQuery = EmployeePayrollAttendance::with(['employee'])
            ->where('payout_month_id', $secondPayoutMonth->id);

        // Apply employee filter if specified
        if ($request->filled('employee_filter')) {
            $firstMonthQuery->where('emp_id', $request->employee_filter);
            $secondMonthQuery->where('emp_id', $request->employee_filter);
        }

        $firstMonthData = $firstMonthQuery->get()->keyBy('emp_id');
        $secondMonthData = $secondMonthQuery->get()->keyBy('emp_id');

        // Get all employee IDs from both months
        $employeeIds = $firstMonthData->keys()->merge($secondMonthData->keys())->unique();

        // Build comparison data
        $comparisonEmployees = [];
        $firstMonthTotals = [
            'gross_pay' => 0, 
            'total_deductions' => 0, 
            'net_pay' => 0,
            'epf' => 0,
            'esic' => 0,
            'employee_count' => $firstMonthData->count()  // Count all employees in first month
        ];
        $secondMonthTotals = [
            'gross_pay' => 0, 
            'total_deductions' => 0, 
            'net_pay' => 0,
            'epf' => 0,
            'esic' => 0,
            'employee_count' => $secondMonthData->count()  // Count all employees in second month
        ];

        // Calculate totals for first month (all employees) with dynamic EPF Wage
        foreach ($firstMonthData as $empId => $record) {
            $firstMonthTotals['gross_pay'] += $record->gross_pay ?? 0;
            $firstMonthTotals['total_deductions'] += $record->total_deduction ?? 0;
            $firstMonthTotals['net_pay'] += $record->total_payable ?? 0;

            // --- Dynamic EPF Wage Calculation ---
            $earnings = is_array($record->earnings) ? $record->earnings : (json_decode($record->earnings, true) ?? []);
            $epfComponentIds = [];
            foreach ($record->employee->statutoryComponents as $statComp) {
                if ($statComp->statutory_component_id == 1 && $statComp->epf_option) {
                    $epfComponentIds = array_keys($earnings);
                    break;
                }
            }
            $rawEpfWage = 0;
            foreach ($epfComponentIds as $componentId) {
                if (isset($earnings[$componentId]) && (!isset($earnings[$componentId]['applicable']) || $earnings[$componentId]['applicable'])) {
                    $rawEpfWage += $earnings[$componentId]['value'] ?? 0;
                }
            }
            $epfOption = 'restrict_15000';
            $employeeStatutoryComponent = $record->employee->statutoryComponents
                ->where('statutory_component_id', 1)
                ->whereNull('deleted_at')
                ->first();
            if ($employeeStatutoryComponent && $employeeStatutoryComponent->epf_option) {
                $epfOption = $employeeStatutoryComponent->epf_option;
            }
            switch ($epfOption) {
                case 'restrict_15000':
                    $epfWage = min(15000, $rawEpfWage);
                    break;
                case '12_percent':
                case 'manual_value':
                    $epfWage = $rawEpfWage;
                    break;
                default:
                    $epfWage = min(15000, $rawEpfWage);
            }
            $firstMonthTotals['epf'] += round($epfWage, 2);
            // --- END Dynamic EPF Wage Calculation ---

            // ESIC logic remains unchanged
            $deductions = is_string($record->deductions) ? json_decode($record->deductions, true) : $record->deductions;
            $esicValue = 0;
            if (isset($deductions[2]['value'])) {
                $esicValue = $deductions[2]['value'];
            } elseif (isset($deductions['2']['value'])) {
                $esicValue = $deductions['2']['value'];
            } elseif (isset($deductions[2])) {
                $esicValue = is_numeric($deductions[2]) ? $deductions[2] : 0;
            }
            $firstMonthTotals['esic'] += $esicValue;
        }

        // Calculate totals for second month (all employees) with dynamic EPF Wage
        foreach ($secondMonthData as $empId => $record) {
            $secondMonthTotals['gross_pay'] += $record->gross_pay ?? 0;
            $secondMonthTotals['total_deductions'] += $record->total_deduction ?? 0;
            $secondMonthTotals['net_pay'] += $record->total_payable ?? 0;

            // --- Dynamic EPF Wage Calculation ---
            $earnings = is_array($record->earnings) ? $record->earnings : (json_decode($record->earnings, true) ?? []);
            $epfComponentIds = [];
            foreach ($record->employee->statutoryComponents as $statComp) {
                if ($statComp->statutory_component_id == 1 && $statComp->epf_option) {
                    $epfComponentIds = array_keys($earnings);
                    break;
                }
            }
            $rawEpfWage = 0;
            foreach ($epfComponentIds as $componentId) {
                if (isset($earnings[$componentId]) && (!isset($earnings[$componentId]['applicable']) || $earnings[$componentId]['applicable'])) {
                    $rawEpfWage += $earnings[$componentId]['value'] ?? 0;
                }
            }
            $epfOption = 'restrict_15000';
            $employeeStatutoryComponent = $record->employee->statutoryComponents
                ->where('statutory_component_id', 1)
                ->whereNull('deleted_at')
                ->first();
            if ($employeeStatutoryComponent && $employeeStatutoryComponent->epf_option) {
                $epfOption = $employeeStatutoryComponent->epf_option;
            }
            switch ($epfOption) {
                case 'restrict_15000':
                    $epfWage = min(15000, $rawEpfWage);
                    break;
                case '12_percent':
                case 'manual_value':
                    $epfWage = $rawEpfWage;
                    break;
                default:
                    $epfWage = min(15000, $rawEpfWage);
            }
            $secondMonthTotals['epf'] += round($epfWage, 2);
            // --- END Dynamic EPF Wage Calculation ---

            // ESIC logic remains unchanged
            $deductions = is_string($record->deductions) ? json_decode($record->deductions, true) : $record->deductions;
            $esicValue = 0;
            if (isset($deductions[2]['value'])) {
                $esicValue = $deductions[2]['value'];
            } elseif (isset($deductions['2']['value'])) {
                $esicValue = $deductions['2']['value'];
            } elseif (isset($deductions[2])) {
                $esicValue = is_numeric($deductions[2]) ? $deductions[2] : 0;
            }
            $secondMonthTotals['esic'] += $esicValue;
        }

        // Build comparison data for employees (include ALL employees from both months)
        foreach ($employeeIds as $empId) {
            $firstRecord = $firstMonthData->get($empId);
            $secondRecord = $secondMonthData->get($empId);

            // Get employee info from whichever record exists
            $employee = $firstRecord ? $firstRecord->employee : $secondRecord->employee;

            // Handle first month data
            if ($firstRecord) {
                // Extract EPF and ESIC from deductions JSON - improved logic
                $firstDeductions = is_string($firstRecord->deductions) ? json_decode($firstRecord->deductions, true) : $firstRecord->deductions;
                
                // Try multiple possible structures for EPF (component 1)
                $firstEpf = 0;
                if (isset($firstDeductions[1]['value'])) {
                    $firstEpf = $firstDeductions[1]['value'];
                } elseif (isset($firstDeductions['1']['value'])) {
                    $firstEpf = $firstDeductions['1']['value'];
                } elseif (isset($firstDeductions[1])) {
                    $firstEpf = is_numeric($firstDeductions[1]) ? $firstDeductions[1] : 0;
                }
                
                // Try multiple possible structures for ESIC (component 2)  
                $firstEsic = 0;
                if (isset($firstDeductions[2]['value'])) {
                    $firstEsic = $firstDeductions[2]['value'];
                } elseif (isset($firstDeductions['2']['value'])) {
                    $firstEsic = $firstDeductions['2']['value'];
                } elseif (isset($firstDeductions[2])) {
                    $firstEsic = is_numeric($firstDeductions[2]) ? $firstDeductions[2] : 0;
                }
                
                $firstMonthPayroll = [
                    'gross_pay' => $firstRecord->gross_pay ?? 0,
                    'total_deductions' => $firstRecord->total_deduction ?? 0,
                    'net_pay' => $firstRecord->total_payable ?? 0,
                    'epf' => $firstEpf,
                    'esic' => $firstEsic,
                    'status' => 'active'
                ];
            } else {
                // Employee joined in second month
                $firstMonthPayroll = [
                    'gross_pay' => 0,
                    'total_deductions' => 0,
                    'net_pay' => 0,
                    'epf' => 0,
                    'esic' => 0,
                    'status' => 'not_joined'
                ];
            }

            // Handle second month data
            if ($secondRecord) {
                // Extract EPF and ESIC from deductions JSON - improved logic
                $secondDeductions = is_string($secondRecord->deductions) ? json_decode($secondRecord->deductions, true) : $secondRecord->deductions;
                
                // Try multiple possible structures for EPF (component 1)
                $secondEpf = 0;
                if (isset($secondDeductions[1]['value'])) {
                    $secondEpf = $secondDeductions[1]['value'];
                } elseif (isset($secondDeductions['1']['value'])) {
                    $secondEpf = $secondDeductions['1']['value'];
                } elseif (isset($secondDeductions[1])) {
                    $secondEpf = is_numeric($secondDeductions[1]) ? $secondDeductions[1] : 0;
                }
                
                // Try multiple possible structures for ESIC (component 2)
                $secondEsic = 0;
                if (isset($secondDeductions[2]['value'])) {
                    $secondEsic = $secondDeductions[2]['value'];
                } elseif (isset($secondDeductions['2']['value'])) {
                    $secondEsic = $secondDeductions['2']['value'];
                } elseif (isset($secondDeductions[2])) {
                    $secondEsic = is_numeric($secondDeductions[2]) ? $secondDeductions[2] : 0;
                }
                
                $secondMonthPayroll = [
                    'gross_pay' => $secondRecord->gross_pay ?? 0,
                    'total_deductions' => $secondRecord->total_deduction ?? 0,
                    'net_pay' => $secondRecord->total_payable ?? 0,
                    'epf' => $secondEpf,
                    'esic' => $secondEsic,
                    'status' => 'active'
                ];
            } else {
                // Employee left after first month
                $secondMonthPayroll = [
                    'gross_pay' => 0,
                    'total_deductions' => 0,
                    'net_pay' => 0,
                    'epf' => 0,
                    'esic' => 0,
                    'status' => 'left'
                ];
            }

            $comparisonEmployees[] = [
                'employee_id' => $employee->employee_id,
                'name' => $employee->name,
                'profile_image' => $employee->profile_image,
                'first_month' => $firstMonthPayroll,
                'second_month' => $secondMonthPayroll,
            ];
        }

        // Calculate summary changes
        $summary = [
            'total_employees' => count($comparisonEmployees), // All unique employees across both months
            'gross_pay_change' => $secondMonthTotals['gross_pay'] - $firstMonthTotals['gross_pay'],
            'deduction_change' => $secondMonthTotals['total_deductions'] - $firstMonthTotals['total_deductions'],
            'net_pay_change' => $secondMonthTotals['net_pay'] - $firstMonthTotals['net_pay'],
            'epf_change' => $secondMonthTotals['epf'] - $firstMonthTotals['epf'],
            'esic_change' => $secondMonthTotals['esic'] - $firstMonthTotals['esic'],
            'employee_count_change' => $secondMonthTotals['employee_count'] - $firstMonthTotals['employee_count'],
        ];

        $comparisonData = [
            'first_month_name' => Carbon::createFromDate($firstYear, $firstMonth, 1)->format('M Y'),
            'second_month_name' => Carbon::createFromDate($secondYear, $secondMonth, 1)->format('M Y'),
            'employees' => $comparisonEmployees,
            'totals' => [
                'first_month' => $firstMonthTotals,
                'second_month' => $secondMonthTotals,
            ],
            'summary' => $summary,
        ];

        // Get data for the form
        $availableMonths = EmployeePayrollAttendancePayoutMonthStatus::where('status', 'completed')
            ->orderByDesc('payout_year')
            ->orderByDesc('payout_month')
            ->get()
            ->map(function ($month) {
                return [
                    'value' => $month->payout_month . '-' . $month->payout_year,
                    'label' => Carbon::createFromDate($month->payout_year, $month->payout_month, 1)->format('M Y')
                ];
            });

        $employees = EmployeeBasicDetail::orderBy('name')->get();

        return view('reports.comparission-payroll-report', compact('availableMonths', 'employees', 'comparisonData'));
    }

    public function exportComparisonReport(Request $request)
    {
        $request->validate([
            'first_month' => 'required|string',
            'second_month' => 'required|string|different:first_month',
            'employee_filter' => 'nullable|exists:employee_basic_details,id'
        ]);

        // Parse months
        list($firstMonth, $firstYear) = explode('-', $request->first_month);
        list($secondMonth, $secondYear) = explode('-', $request->second_month);

        // Get payout month statuses
        $firstPayoutMonth = EmployeePayrollAttendancePayoutMonthStatus::where([
            'payout_month' => $firstMonth,
            'payout_year' => $firstYear,
            'status' => 'completed'
        ])->firstOrFail();

        $secondPayoutMonth = EmployeePayrollAttendancePayoutMonthStatus::where([
            'payout_month' => $secondMonth,
            'payout_year' => $secondYear,
            'status' => 'completed'
        ])->firstOrFail();

        // Build query for attendances
        $firstMonthQuery = EmployeePayrollAttendance::with(['employee'])
            ->where('payout_month_id', $firstPayoutMonth->id);

        $secondMonthQuery = EmployeePayrollAttendance::with(['employee'])
            ->where('payout_month_id', $secondPayoutMonth->id);

        // Apply employee filter if specified
        if ($request->filled('employee_filter')) {
            $firstMonthQuery->where('emp_id', $request->employee_filter);
            $secondMonthQuery->where('emp_id', $request->employee_filter);
        }

        $firstMonthData = $firstMonthQuery->get()->keyBy('emp_id');
        $secondMonthData = $secondMonthQuery->get()->keyBy('emp_id');

        // Get all employee IDs from both months
        $employeeIds = $firstMonthData->keys()->merge($secondMonthData->keys())->unique();

        // Build comparison data
        $comparisonEmployees = [];
        $firstMonthTotals = [
            'gross_pay' => 0, 
            'total_deductions' => 0, 
            'net_pay' => 0,
            'epf' => 0,
            'esic' => 0,
            'employee_count' => $firstMonthData->count()
        ];
        $secondMonthTotals = [
            'gross_pay' => 0, 
            'total_deductions' => 0, 
            'net_pay' => 0,
            'epf' => 0,
            'esic' => 0,
            'employee_count' => $secondMonthData->count()
        ];

        // Calculate totals for first month
        foreach ($firstMonthData as $empId => $record) {
            $firstMonthTotals['gross_pay'] += $record->gross_pay ?? 0;
            $firstMonthTotals['total_deductions'] += $record->total_deduction ?? 0;
            $firstMonthTotals['net_pay'] += $record->total_payable ?? 0;
            
            // Extract EPF and ESIC from deductions JSON
            $deductions = is_string($record->deductions) ? json_decode($record->deductions, true) : $record->deductions;
            
            $epfValue = 0;
            if (isset($deductions[1]['value'])) {
                $epfValue = $deductions[1]['value'];
            } elseif (isset($deductions['1']['value'])) {
                $epfValue = $deductions['1']['value'];
            } elseif (isset($deductions[1])) {
                $epfValue = is_numeric($deductions[1]) ? $deductions[1] : 0;
            }
            
            $esicValue = 0;
            if (isset($deductions[2]['value'])) {
                $esicValue = $deductions[2]['value'];
            } elseif (isset($deductions['2']['value'])) {
                $esicValue = $deductions['2']['value'];
            } elseif (isset($deductions[2])) {
                $esicValue = is_numeric($deductions[2]) ? $deductions[2] : 0;
            }
            
            $firstMonthTotals['epf'] += $epfValue;
            $firstMonthTotals['esic'] += $esicValue;
        }

        // Calculate totals for second month
        foreach ($secondMonthData as $empId => $record) {
            $secondMonthTotals['gross_pay'] += $record->gross_pay ?? 0;
            $secondMonthTotals['total_deductions'] += $record->total_deduction ?? 0;
            $secondMonthTotals['net_pay'] += $record->total_payable ?? 0;
            
            // Extract EPF and ESIC from deductions JSON
            $deductions = is_string($record->deductions) ? json_decode($record->deductions, true) : $record->deductions;
            
            $epfValue = 0;
            if (isset($deductions[1]['value'])) {
                $epfValue = $deductions[1]['value'];
            } elseif (isset($deductions['1']['value'])) {
                $epfValue = $deductions['1']['value'];
            } elseif (isset($deductions[1])) {
                $epfValue = is_numeric($deductions[1]) ? $deductions[1] : 0;
            }
            
            $esicValue = 0;
            if (isset($deductions[2]['value'])) {
                $esicValue = $deductions[2]['value'];
            } elseif (isset($deductions['2']['value'])) {
                $esicValue = $deductions['2']['value'];
            } elseif (isset($deductions[2])) {
                $esicValue = is_numeric($deductions[2]) ? $deductions[2] : 0;
            }
            
            $secondMonthTotals['epf'] += $epfValue;
            $secondMonthTotals['esic'] += $esicValue;
        }

        // Build comparison data for employees
        foreach ($employeeIds as $empId) {
            $firstRecord = $firstMonthData->get($empId);
            $secondRecord = $secondMonthData->get($empId);

            // Get employee info from whichever record exists
            $employee = $firstRecord ? $firstRecord->employee : $secondRecord->employee;

            // Handle first month data
            if ($firstRecord) {
                $firstDeductions = is_string($firstRecord->deductions) ? json_decode($firstRecord->deductions, true) : $firstRecord->deductions;
                
                $firstEpf = 0;
                if (isset($firstDeductions[1]['value'])) {
                    $firstEpf = $firstDeductions[1]['value'];
                } elseif (isset($firstDeductions['1']['value'])) {
                    $firstEpf = $firstDeductions['1']['value'];
                } elseif (isset($firstDeductions[1])) {
                    $firstEpf = is_numeric($firstDeductions[1]) ? $firstDeductions[1] : 0;
                }
                
                $firstEsic = 0;
                if (isset($firstDeductions[2]['value'])) {
                    $firstEsic = $firstDeductions[2]['value'];
                } elseif (isset($firstDeductions['2']['value'])) {
                    $firstEsic = $firstDeductions['2']['value'];
                } elseif (isset($firstDeductions[2])) {
                    $firstEsic = is_numeric($firstDeductions[2]) ? $firstDeductions[2] : 0;
                }
                
                $firstMonthPayroll = [
                    'gross_pay' => $firstRecord->gross_pay ?? 0,
                    'total_deductions' => $firstRecord->total_deduction ?? 0,
                    'net_pay' => $firstRecord->total_payable ?? 0,
                    'epf' => $firstEpf,
                    'esic' => $firstEsic,
                    'status' => 'active'
                ];
            } else {
                $firstMonthPayroll = [
                    'gross_pay' => 0,
                    'total_deductions' => 0,
                    'net_pay' => 0,
                    'epf' => 0,
                    'esic' => 0,
                    'status' => 'not_joined'
                ];
            }

            // Handle second month data
            if ($secondRecord) {
                $secondDeductions = is_string($secondRecord->deductions) ? json_decode($secondRecord->deductions, true) : $secondRecord->deductions;
                
                $secondEpf = 0;
                if (isset($secondDeductions[1]['value'])) {
                    $secondEpf = $secondDeductions[1]['value'];
                } elseif (isset($secondDeductions['1']['value'])) {
                    $secondEpf = $secondDeductions['1']['value'];
                } elseif (isset($secondDeductions[1])) {
                    $secondEpf = is_numeric($secondDeductions[1]) ? $secondDeductions[1] : 0;
                }
                
                $secondEsic = 0;
                if (isset($secondDeductions[2]['value'])) {
                    $secondEsic = $secondDeductions[2]['value'];
                } elseif (isset($secondDeductions['2']['value'])) {
                    $secondEsic = $secondDeductions['2']['value'];
                } elseif (isset($secondDeductions[2])) {
                    $secondEsic = is_numeric($secondDeductions[2]) ? $secondDeductions[2] : 0;
                }
                
                $secondMonthPayroll = [
                    'gross_pay' => $secondRecord->gross_pay ?? 0,
                    'total_deductions' => $secondRecord->total_deduction ?? 0,
                    'net_pay' => $secondRecord->total_payable ?? 0,
                    'epf' => $secondEpf,
                    'esic' => $secondEsic,
                    'status' => 'active'
                ];
            } else {
                $secondMonthPayroll = [
                    'gross_pay' => 0,
                    'total_deductions' => 0,
                    'net_pay' => 0,
                    'epf' => 0,
                    'esic' => 0,
                    'status' => 'left'
                ];
            }

            $comparisonEmployees[] = [
                'employee_id' => $employee->employee_id,
                'name' => $employee->name,
                'profile_image' => $employee->profile_image,
                'first_month' => $firstMonthPayroll,
                'second_month' => $secondMonthPayroll,
            ];
        }

        // Calculate summary changes
        $summary = [
            'total_employees' => count($comparisonEmployees),
            'gross_pay_change' => $secondMonthTotals['gross_pay'] - $firstMonthTotals['gross_pay'],
            'deduction_change' => $secondMonthTotals['total_deductions'] - $firstMonthTotals['total_deductions'],
            'net_pay_change' => $secondMonthTotals['net_pay'] - $firstMonthTotals['net_pay'],
            'epf_change' => $secondMonthTotals['epf'] - $firstMonthTotals['epf'],
            'esic_change' => $secondMonthTotals['esic'] - $firstMonthTotals['esic'],
            'employee_count_change' => $secondMonthTotals['employee_count'] - $firstMonthTotals['employee_count'],
        ];

        $comparisonData = [
            'first_month_name' => Carbon::createFromDate($firstYear, $firstMonth, 1)->format('M Y'),
            'second_month_name' => Carbon::createFromDate($secondYear, $secondMonth, 1)->format('M Y'),
            'employees' => $comparisonEmployees,
            'totals' => [
                'first_month' => $firstMonthTotals,
                'second_month' => $secondMonthTotals,
            ],
            'summary' => $summary,
        ];

        // Generate PDF using Mpdf
        $mpdf = new \Mpdf\Mpdf([
            'format' => 'A4-L', // Landscape orientation for better table fit
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 15,
            'margin_bottom' => 15,
            'default_font' => 'helvetica',
            'default_font_size' => 10,
            'tempDir' => sys_get_temp_dir()
        ]);

        $html = view('reports.export-comparission-report-pdf', compact('comparisonData'))->render();
        
        $mpdf->WriteHTML($html);
        
        $filename = 'Payroll_Comparison_Report_' . $comparisonData['first_month_name'] . '_vs_' . $comparisonData['second_month_name'] . '_' . date('Y-m-d_H-i-s') . '.pdf';
        
        return $mpdf->Output($filename, 'D'); // 'D' for download
    }
    /** END - COMPARISION REPORT CONTROLLER FUNCTION - KPS - 08.07.2025 */

    /**
     * Calculate advance deduction for a specific employee and month/year
     *
     * @param int $employeeId
     * @param int $month
     * @param int $year
     * @return float
     */
    protected function calculateAdvanceDeduction($employeeId, $month, $year)
    {
        $totalDeduction = 0;
        
        // Get all active advances for this employee in this month using direct query
        $activeAdvances = \App\Models\EmployeeAdvance::where('employee_id', $employeeId)
            ->where('status', 'active')
            ->where(function($query) use ($month, $year) {
                $currentMonth = \Carbon\Carbon::createFromDate($year, $month, 1);
                
                $query->where(function($q) use ($currentMonth) {
                    // Start date is before or in current month
                    $q->whereDate('start_date', '<=', $currentMonth->endOfMonth());
                })
                ->where(function($q) use ($currentMonth) {
                    // End date is after or in current month
                    $q->whereDate('end_date', '>=', $currentMonth->startOfMonth());
                });
            })
            ->get();
            
        foreach ($activeAdvances as $advance) {
            // Check if a deduction already exists for this advance in this month
            $existingDeduction = $advance->deductions()
                ->where('month', $month)
                ->where('year', $year)
                ->first();
                
            if ($existingDeduction) {
                // Use existing deduction amount
                $totalDeduction += $existingDeduction->amount;
            } else {
                // Calculate new deduction
                $remainingAmount = $advance->remaining_amount;
                $deductionAmount = min($advance->monthly_deduction, $remainingAmount);
                
                if ($deductionAmount > 0) {
                    $totalDeduction += $deductionAmount;
                }
            }
        }
        
        return $totalDeduction;
    }
}