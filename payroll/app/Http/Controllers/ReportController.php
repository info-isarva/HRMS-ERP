<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EmployeePayrollAttendance;
use App\Models\EmployeePayrollAttendancePayoutMonthStatus;
use App\Models\SalaryComponent;
use App\Models\StatutoryComponent;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class ReportController extends Controller
{
    /**
     * High-level payroll analytics for a single finalized month
     */
    public function payrollAnalytics(Request $request)
    {
        $months = $this->getFinalizedMonths();
        $selected = $request->get('payout_month_year');
        $analytics = null;

        if ($selected) {
            [$month, $year] = explode('-', $selected);
            $analytics = $this->buildMonthAnalytics((int)$month, (int)$year);
        }

        return view('reports.payroll-analytics', compact('months', 'selected', 'analytics'));
    }

    /**
     * Compare two consecutive finalized months and highlight component shifts
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

    private function getFinalizedMonths()
    {
        return EmployeePayrollAttendancePayoutMonthStatus::where('status', 'completed')
            ->orderByDesc('payout_year')
            ->orderByDesc('payout_month')
            ->get()
            ->map(function ($row) {
                $key = str_pad($row->payout_month, 2, '0', STR_PAD_LEFT) . '-' . $row->payout_year;
                return [
                    'key' => $row->payout_month . '-' . $row->payout_year,
                    'label' => Carbon::createFromDate($row->payout_year, $row->payout_month, 1)->format('M Y'),
                    'raw' => $row
                ];
            });
    }

    private function buildMonthAnalytics(int $month, int $year)
    {
        $cacheKey = "payroll.analytics.$year.$month";
        return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($month, $year) {
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
            $totalDeduction = $attendances->sum('total_deduction');
            $totalNet = $attendances->sum('total_payable');
            $avgGross = $employeeCount ? round($totalGross / $employeeCount, 2) : 0;
            $avgNet = $employeeCount ? round($totalNet / $employeeCount, 2) : 0;

            // Aggregate component distribution (earnings + deductions) from stored JSON
            $componentTotals = [];
            foreach ($attendances as $a) {
                $earnings = json_decode($a->earnings, true) ?: [];
                foreach ($earnings as $id => $row) {
                    if (!isset($row['value'])) continue;
                    $componentTotals[$id]['earnings'] = ($componentTotals[$id]['earnings'] ?? 0) + $row['value'];
                    $componentTotals[$id]['name'] = $row['name'] ?? ('C'.$id);
                }
                $deductions = json_decode($a->deductions, true) ?: [];
                foreach ($deductions as $id => $row) {
                    if (!isset($row['value'])) continue;
                    $componentTotals[$id]['deductions'] = ($componentTotals[$id]['deductions'] ?? 0) + $row['value'];
                    $componentTotals[$id]['name'] = $row['name'] ?? ('C'.$id);
                }
            }

            return [
                'month' => $month,
                'year' => $year,
                'label' => Carbon::createFromDate($year, $month, 1)->format('F Y'),
                'employee_count' => $employeeCount,
                'gross_total' => $totalGross,
                'deduction_total' => $totalDeduction,
                'net_total' => $totalNet,
                'avg_gross' => $avgGross,
                'avg_net' => $avgNet,
                'component_totals' => $componentTotals,
            ];
        });
    }

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
}
