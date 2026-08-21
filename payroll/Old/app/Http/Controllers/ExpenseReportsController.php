<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use App\Helpers\FinancialYearHelper;
use App\Models\EmployeePayrollAttendancePayoutMonthStatus;
use Carbon\Carbon;

class ExpenseReportsController extends Controller
{
    // view page
    public function index()
    {
        return view('reports.expensereport');
    }

    // view page
    public function invoiceReports()
    {
        return view('reports.invoicereports');
    }
    
    // daily report page
    public function dailyReport()
    {
        return view('reports.dailyreports');
    }

    // leave reports page
    public function leaveReport()
    {
        $leaves = DB::table('leaves_admins')
                ->join('users', 'users.user_id','leaves_admins.user_id')
                ->select('leaves_admins.*', 'users.*')
                ->get();
        return view('reports.leavereports',compact('leaves'));
    }

    /** payment report index page */
    public function paymentsReportIndex()
    {
        return view('reports.payments-reports');
    }

    /** employee-reports page */
    public function employeeReportsIndex()
    {
        return view('reports.employee-reports');
    }

    /** Payslip Reports */
    public function payslipReports()
    {
        // Get financial year context
        $fyContext = FinancialYearHelper::getFinancialYearContext();
        $selectedFY = $fyContext['selectedFinancialYear'];
        
        // Get available months from payroll status table with financial year filtering
        $availableMonthsQuery = EmployeePayrollAttendancePayoutMonthStatus::select('payout_month', 'payout_year', 'status')
            ->where('status', '=', 'completed');
            
        // Filter by selected financial year
        if ($selectedFY) {
            $availableMonthsQuery = FinancialYearHelper::filterPayrollBySelectedFinancialYear($availableMonthsQuery);
        }
        
        $availableMonths = $availableMonthsQuery
            ->groupBy('payout_year', 'payout_month', 'status')  // Add status to GROUP BY
            ->orderByDesc('payout_year')
            ->orderByDesc('payout_month')
            ->get()
            ->map(function ($item) {
                return [
                    'payout_month' => $item->payout_month,
                    'payout_year' => $item->payout_year,
                    'status' => $item->status,
                    'label' => Carbon::createFromDate($item->payout_year, $item->payout_month, 1)->format('M-Y')
                ];
            });

        return view('reports.payslipreports', compact('fyContext', 'availableMonths'));
    }

    /** Attendance Reports */
    public function attendanceReports()
    {
        return view('reports.attendance-reports');
    }
}
