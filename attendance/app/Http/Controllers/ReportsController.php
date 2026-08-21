<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LeaveApplication;
use App\Models\User;
use App\Models\LeaveType;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Services\PayrollApiService;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportsController extends Controller
{
    protected $payrollApiService;

    public function __construct(PayrollApiService $payrollApiService)
    {
        $this->payrollApiService = $payrollApiService;
    }
    
     /**
     * Display the employee monthly leave report.
     */
    public function employeeMonthlyReport(Request $request)
    {
        $year = $request->input('year', date('Y'));
        
        if (auth()->user()->isAdmin() || auth()->user()->isSuperAdmin()) {
            $users = User::whereIn('email', function($subQuery) {
                    $subQuery->select('email')
                            ->from('employees')
                            ->where('exclude_from_payroll', 0);
                })->orderBy('name')->get();
            $userId = $request->input('user_id', 'all');
        } else {
            $users = collect([auth()->user()]);
            $userId = auth()->id();
        }

        $selectedUser = null;
        $monthlyData = array_fill(1, 12, 0);

        if ($userId) {
            $query = LeaveApplication::whereIn('status', ['approved', 'approved_by_manager'])
                ->where(function($q) use ($year) {
                    $q->whereYear('start_date', $year)
                      ->orWhereYear('end_date', $year);
                });

            if ($userId !== 'all') {
                $selectedUser = User::find($userId);
                if ($selectedUser) {
                    $query->where('user_id', $userId);
                } else {
                    $userId = 'all'; // Fallback if user not found
                }
            }

            $leaves = $query->get();

            foreach ($leaves as $leave) {
                $start = Carbon::parse($leave->start_date);
                $end = Carbon::parse($leave->end_date);
                
                $current = $start->copy();
                while ($current->lte($end)) {
                    if ($current->year == $year) {
                        $monthlyData[$current->month]++;
                    }
                    $current->addDay();
                }
            }
        }

        if ($request->input('format') === 'pdf') {
            $reportTitle = 'Employee Monthly Leave Report - ' . $year;
            $pdf = Pdf::loadView('reports.pdf.employee-monthly', compact('users', 'selectedUser', 'userId', 'year', 'monthlyData', 'reportTitle'))
                ->setOption('isPhpEnabled', true)
                ->setPaper('a4', 'landscape');
            return $pdf->download('employee-monthly-report-' . $year . '.pdf');
        }

        return view('reports.employee-monthly', compact('users', 'selectedUser', 'userId', 'year', 'monthlyData'));
    }


    /**
     * Display the reports dashboard.
     */
    public function index()
    {
        return view('reports.index');
    }

    /**
     * Display the approved leave list report.
     */
    public function leaveApproved(Request $request)
    {
        $data = $this->getLeaveApprovedData($request);

        if ($request->input('format') === 'pdf') {
            $reportTitle = 'Approved Leave Report';
            $pdf = Pdf::loadView('reports.pdf.leave-approved', [
                'leaves' => $data['leaves'],
                'startDate' => $data['startDate'],
                'endDate' => $data['endDate'],
                'reportTitle' => $reportTitle,
            ])
                ->setOption('isPhpEnabled', true);

            return $pdf->download('approved-leaves.pdf');
        }

        return view('reports.leave-approved', $data);
    }

    /**
     * Build approved leave report dataset.
     *
     * @return array{leaves: \Illuminate\Support\Collection, startDate: string, endDate: string, employeeName: ?string}
     */
    private function getLeaveApprovedData(Request $request): array
    {
        $today = Carbon::today()->toDateString();
        $startDate = $request->input('start_date', $today);
        $endDate = $request->input('end_date', $today);
        $employeeName = $request->input('employee_name');

        $query = LeaveApplication::with(['user', 'leaveType', 'managerApprovedBy', 'hrApprovedBy'])
            ->where('status', 'approved');

        if ($startDate && $endDate) {
            $query->where(function ($q) use ($startDate, $endDate) {
                $q->where('start_date', '<=', $endDate)
                    ->where('end_date', '>=', $startDate);
            });
        }

        if ($employeeName) {
            $term = trim($employeeName);
            $query->whereHas('user', function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%");
            });
        }

        $leaves = $query->orderBy('start_date', 'desc')->get();

        return compact('leaves', 'startDate', 'endDate', 'employeeName');
    }

    /**
     * Display the rejected leave list report.
     */
    public function leaveRejected(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $query = LeaveApplication::with(['user', 'leaveType']) // Assuming 'rejected_by' relation exists or just user info
            ->where('status', 'rejected');

        if ($startDate && $endDate) {
            $query->where(function ($q) use ($startDate, $endDate) {
                 // Check for overlap
                $q->where('start_date', '<=', $endDate)
                  ->where('end_date', '>=', $startDate);
            });
        }

        $leaves = $query->orderBy('start_date', 'desc')->get();

        if ($request->input('format') === 'pdf') {
            $reportTitle = 'Rejected Leave Report';
            $pdf = Pdf::loadView('reports.pdf.leave-rejected', compact('leaves', 'startDate', 'endDate', 'reportTitle'))
                ->setOption('isPhpEnabled', true);
            return $pdf->download('rejected-leaves.pdf');
        }

        return view('reports.leave-rejected', compact('leaves', 'startDate', 'endDate'));
    }

    /**
     * Display the employee leave status report.
     */
    public function employeeLeaveStatus(Request $request)
    {
        $currentFinancialYear = active_fy_label();

        // Fetch all users with their approved leave applications for calculation in the current financial year
        $users = User::whereIn('email', function($subQuery) {
                $subQuery->select('email')
                        ->from('employees')
                        ->where('exclude_from_payroll', 0)
                        ->where('status', 'Active');
            })
            ->with(['leaveApplications' => function ($query) use ($currentFinancialYear) {
                $query->whereIn('status', ['approved', 'approved_by_manager'])
                      ->where('financial_year', $currentFinancialYear);
            }])->get();

        // Fetch payroll employees once to avoid N+1 API calls
        $payrollEmployees = $this->payrollApiService->getEmployees();
        
        // Index payroll data by email for fast lookup
        $payrollMap = [];
        if ($payrollEmployees) {
            foreach ($payrollEmployees as $emp) {
                if (isset($emp['email'])) {
                    $payrollMap[strtolower($emp['email'])] = $emp;
                }
            }
        }

        // Fetch active leave type IDs for the current financial year
        $activeLeaveTypeIds = \App\Models\LeaveType::where('financial_year', $currentFinancialYear)
            ->pluck('id')
            ->toArray();

        $reportData = [];

        foreach ($users as $user) {
            $userEmail = strtolower($user->email);
            $allocations = $payrollMap[$userEmail]['leave_allocations'] ?? [];
            
            // Calculate usage per leave type
            $typeUsed = []; // leave_type_id => used_days
            $totalTaken = 0;
            
            foreach ($user->leaveApplications as $leaveApp) {
                // Determine if we should count this leave. 
                // Currently user asks for "Leave Taken Count". 
                // We sum up all approved leave days.
                $days = $leaveApp->total_days;
                
                if (!isset($typeUsed[$leaveApp->leave_type_id])) {
                    $typeUsed[$leaveApp->leave_type_id] = 0;
                }
                $typeUsed[$leaveApp->leave_type_id] += $days;
                $totalTaken += $days;
            }

            // Calculate Available based on allocations
            // Available = Sum(Allocated_i - Used_i)
            // Note: We allow negative balance if user has exceeded their allocation or taken leaves without allocation.
            $currentAvailable = 0;
            $processedTypeIds = [];

            if (!empty($allocations)) {
                foreach ($allocations as $alloc) {
                    $typeId = $alloc['leave_type_id'];
                    
                    // Only process allocations for leave types active in the current financial year
                    if (!in_array($typeId, $activeLeaveTypeIds)) {
                        continue;
                    }
                    
                    $effectiveDays = $alloc['effective_days'] ?? 0;
                    $used = $typeUsed[$typeId] ?? 0;
                    
                    // Balance for this leave type - allow negative values
                    $balance = $effectiveDays - $used;
                    $currentAvailable += $balance;
                    $processedTypeIds[] = $typeId;
                }
            }

            // Also subtract any leaves taken for types that are NOT in the allocations
            foreach ($typeUsed as $typeId => $used) {
                if (!in_array($typeId, $processedTypeIds)) {
                    $currentAvailable -= $used;
                }
            }
            
            $reportData[] = [
                'user' => $user,
                'available_leave' => $currentAvailable,
                'leave_taken' => $totalTaken
            ];
        }

        // Sort by available_leave descending (most available first)
        usort($reportData, function ($a, $b) {
            return $b['available_leave'] <=> $a['available_leave'];
        });

        if ($request->input('format') === 'pdf') {
            $reportTitle = 'Employee Leave Status Report';
            $pdf = Pdf::loadView('reports.pdf.employee-leave-status', compact('reportData', 'reportTitle'))
                ->setOption('isPhpEnabled', true);
            return $pdf->download('employee-leave-status.pdf');
        }

        return view('reports.employee-leave-status', compact('reportData'));
    }

    /**
     * Display the LOP (Loss of Pay) report.
     */
    public function leaveLop(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $search = $request->input('search');

        $query = LeaveApplication::with(['user', 'leaveType', 'managerApprovedBy', 'hrApprovedBy'])
            ->where('lop_days', '>', 0)
            ->whereIn('status', ['approved', 'approved_by_manager']);

        if ($startDate && $endDate) {
            $query->where(function ($q) use ($startDate, $endDate) {
                // Check for overlap
                $q->where('start_date', '<=', $endDate)
                  ->where('end_date', '>=', $startDate);
            });
        }

        if ($search) {
            $query->whereHas('user', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $leaves = $query->orderBy('start_date', 'desc')->get();

        if ($request->input('format') === 'pdf') {
            $reportTitle = 'Loss of Pay (LOP) Report';
            $pdf = Pdf::loadView('reports.pdf.leave-lop', compact('leaves', 'startDate', 'endDate', 'search', 'reportTitle'))
                ->setOption('isPhpEnabled', true);
            return $pdf->download('leave-lop-report.pdf');
        }

        return view('reports.leave-lop', compact('leaves', 'startDate', 'endDate', 'search'));
    }
    
    /**
     * Display the Daily Leave Schedule report.
     */
    public function dailyLeave(Request $request)
    {
        $data = $this->getDailyLeaveData($request);
        return view('reports.daily-leave', $data);
    }

    public function dailyLeavePdf(Request $request)
    {
        $data = $this->getDailyLeaveData($request);
        $data['reportTitle'] = 'Daily Leave Schedule Report';
        $pdf = Pdf::loadView('reports.pdf.daily-leave', $data);
        return $pdf->download('daily-leave-report.pdf');
    }

    private function getDailyLeaveData(Request $request)
    {
        $startDate = $request->input('start_date') ?: Carbon::today()->toDateString();
        $endDate = $request->input('end_date') ?: $startDate;
        $search = $request->input('search');

        $query = LeaveApplication::with(['user', 'leaveType'])
            ->where('status', '!=', 'rejected');

        if ($startDate && $endDate) {
            $query->where(function ($q) use ($startDate, $endDate) {
                // Check for overlap
                $q->where('start_date', '<=', $endDate)
                  ->where('end_date', '>=', $startDate);
            });
        }

        if ($search) {
            $query->whereHas('user', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $leaves = $query->orderBy('start_date', 'desc')->get();
        return compact('leaves', 'startDate', 'endDate', 'search');
    }
}
