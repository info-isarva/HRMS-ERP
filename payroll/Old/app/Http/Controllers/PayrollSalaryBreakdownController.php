<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\FinancialYearHelper;
use Carbon\Carbon;

class PayrollSalaryBreakdownController extends Controller
{
    /**
     * Show salary breakdown for finalized months
     */
    public function index()
    {
        $fyContext = FinancialYearHelper::getFinancialYearContext();
        $finalizedMonths = FinancialYearHelper::getFinalizedPayoutMonths();
        
        return view('payroll.salary-breakdown.index', compact('fyContext', 'finalizedMonths'));
    }
    
    /**
     * Show salary breakdown for specific month/year
     */
    public function showBreakdown($month, $year)
    {
        // Validate parameters
        if (!is_numeric($month) || !is_numeric($year) || $month < 1 || $month > 12) {
            return redirect()->route('payroll.salary-breakdown')
                ->with('error', 'Invalid month or year specified');
        }
        
        $payoutMonth = (int) $month;
        $payoutYear = (int) $year;
        
        // Check if month is within selected financial year
        if (!FinancialYearHelper::isMonthInSelectedFinancialYear($payoutMonth, $payoutYear)) {
            return redirect()->route('payroll.salary-breakdown')
                ->with('error', 'Selected month is not within the current financial year');
        }
        
        // Check if month is finalized
        if (!FinancialYearHelper::isPayoutMonthFinalized($payoutMonth, $payoutYear)) {
            return redirect()->route('payroll.salary-breakdown')
                ->with('error', 'No finalized salary data available for the selected month');
        }
        
        // Get salary breakdown data
        $salaryBreakdown = $this->getSalaryBreakdownData($payoutMonth, $payoutYear);
        
        $fyContext = FinancialYearHelper::getFinancialYearContext();
        return view('payroll.salary-breakdown.breakdown', compact('salaryBreakdown', 'payoutMonth', 'payoutYear', 'fyContext'));
    }
    
    /**
     * Check status of a specific month/year via AJAX
     */
    public function checkStatus(Request $request)
    {
        $request->validate([
            'payout_month' => 'required|integer|min:1|max:12',
            'payout_year' => 'required|integer|min:2020|max:2030'
        ]);
        
        $month = $request->payout_month;
        $year = $request->payout_year;
        
        $status = FinancialYearHelper::getPayoutMonthStatus($month, $year);
        
        return response()->json([
            'success' => true,
            'status' => $status,
            'breakdown_url' => $status['is_finalized'] ? 
                route('payroll.salary-breakdown.show', [$month, $year]) : null
        ]);
    }
    
    /**
     * Get detailed salary breakdown data
     */
    private function getSalaryBreakdownData($payoutMonth, $payoutYear)
    {
        $query = DB::table('employee_salary_finalize as esf')
            ->leftJoin('employee_basic_details as ebd', 'esf.employee_id', '=', 'ebd.id')
            ->leftJoin('departments as dept', 'ebd.department_id', '=', 'dept.id')
            ->leftJoin('designations as desig', 'ebd.designation_id', '=', 'desig.id')
            ->select(
                'esf.*',
                'ebd.employee_code',
                'ebd.first_name',
                'ebd.last_name',
                'ebd.email',
                'dept.department_name',
                'desig.designation_name'
            )
            ->where('esf.payout_month', $payoutMonth)
            ->where('esf.payout_year', $payoutYear)
            ->where('esf.is_finalized', true)
            ->orderBy('ebd.employee_code');
        
        $employees = $query->get();
        
        // Calculate summary statistics
        $summary = [
            'total_employees' => $employees->count(),
            'total_gross_salary' => $employees->sum('gross_salary'),
            'total_deductions' => $employees->sum('total_deductions'),
            'total_net_salary' => $employees->sum('net_salary'),
            'total_overtime' => $employees->sum('overtime_amount'),
            'total_incentives' => $employees->sum('incentive_amount'),
            'total_leaves' => $employees->sum('total_leave_days'),
            'total_present_days' => $employees->sum('present_days')
        ];
        
        return [
            'employees' => $employees,
            'summary' => $summary,
            'month' => $payoutMonth,
            'year' => $payoutYear,
            'month_name' => Carbon::createFromDate($payoutYear, $payoutMonth, 1)->format('F Y')
        ];
    }
    
    /**
     * Export salary breakdown to Excel
     */
    public function exportBreakdown(Request $request)
    {
        $request->validate([
            'payout_month' => 'required|integer|min:1|max:12',
            'payout_year' => 'required|integer|min:2020|max:2030'
        ]);
        
        $payoutMonth = $request->payout_month;
        $payoutYear = $request->payout_year;
        
        // Check if month is within selected financial year
        if (!FinancialYearHelper::isMonthInSelectedFinancialYear($payoutMonth, $payoutYear)) {
            return back()->with('error', 'Selected month is not within the selected financial year');
        }
        
        // Check if month is finalized
        if (!FinancialYearHelper::isPayoutMonthFinalized($payoutMonth, $payoutYear)) {
            return back()->with('error', 'No finalized salary data available for the selected month');
        }
        
        $salaryBreakdown = $this->getSalaryBreakdownData($payoutMonth, $payoutYear);
        
        // Generate filename
        $filename = 'salary_breakdown_' . $payoutYear . '_' . str_pad($payoutMonth, 2, '0', STR_PAD_LEFT) . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($salaryBreakdown) {
            $file = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($file, [
                'Employee Code', 'Employee Name', 'Department', 'Designation',
                'Basic Salary', 'HRA', 'Transport Allowance', 'Other Allowances',
                'Gross Salary', 'PF Deduction', 'ESI Deduction', 'Tax Deduction',
                'Other Deductions', 'Total Deductions', 'Overtime Hours', 'Overtime Amount',
                'Incentive Amount', 'Present Days', 'Leave Days', 'Net Salary'
            ]);
            
            // Data rows
            foreach ($salaryBreakdown['employees'] as $employee) {
                fputcsv($file, [
                    $employee->employee_code,
                    $employee->first_name . ' ' . $employee->last_name,
                    $employee->department_name,
                    $employee->designation_name,
                    $employee->basic_salary,
                    $employee->hra_amount ?? 0,
                    $employee->transport_allowance ?? 0,
                    $employee->other_allowance ?? 0,
                    $employee->gross_salary,
                    $employee->pf_deduction ?? 0,
                    $employee->esi_deduction ?? 0,
                    $employee->tax_deduction ?? 0,
                    $employee->other_deductions ?? 0,
                    $employee->total_deductions,
                    $employee->overtime_hours ?? 0,
                    $employee->overtime_amount ?? 0,
                    $employee->incentive_amount ?? 0,
                    $employee->present_days ?? 0,
                    $employee->total_leave_days ?? 0,
                    $employee->net_salary
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
}
