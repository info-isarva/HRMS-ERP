<?php

namespace App\Http\Controllers;

use App\Models\LeaveApplication;
use App\Models\Employee;
use App\Services\PayrollLeaveService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $today = now()->startOfDay();
        $upcomingPublicHolidays = \App\Models\PublicHoliday::where('date', '>=', $today)
            ->where('type', 'fixed')
            ->orderBy('date')
            ->take(4)
            ->get();

        $employeesOnLeaveToday = $this->leavesOnDate(now()->toDateString());
        $employeesOnLeaveTomorrow = $this->leavesOnDate(now()->addDay()->toDateString());

        $canViewLeaveRoster = auth()->user()->isAdmin()
            || auth()->user()->isSuperAdmin()
            || auth()->user()->role === 'hr';
        $leaveRosterDate = $request->input('leave_date', now()->toDateString());

        try {
            $leaveRosterDateCarbon = Carbon::parse($leaveRosterDate)->startOfDay();
            $leaveRosterDate = $leaveRosterDateCarbon->toDateString();
        } catch (\Exception $e) {
            $leaveRosterDateCarbon = now()->startOfDay();
            $leaveRosterDate = $leaveRosterDateCarbon->toDateString();
        }

        $leavesOnSelectedDate = collect();
        $rosterLeaveBalances = [];

        if ($canViewLeaveRoster) {
            $leavesOnSelectedDate = $this->leavesOnDate($leaveRosterDate, true);
            $rosterLeaveBalances = (new PayrollLeaveService())->getRosterLeaveBalances($leavesOnSelectedDate);
        }

        // Get leave trends for last 6 months (based on created_at)
        $leaveTrendsQuery = LeaveApplication::select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', now()->subMonths(6)->startOfMonth());

        if (! auth()->user()->isSuperAdmin() && ! auth()->user()->isAdmin()) {
            $leaveTrendsQuery->where('user_id', auth()->id());
        }

        $leaveTrends = $leaveTrendsQuery
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get()
            ->pluck('count', 'month')
            ->toArray();

        $leaveTrendsData = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i)->format('Y-m');
            $leaveTrendsData[$month] = $leaveTrends[$month] ?? 0;
        }

        $leaveTypeQuery = DB::table('leave_applications')
            ->join('leave_types', 'leave_applications.leave_type_id', '=', 'leave_types.id')
            ->select('leave_types.name', DB::raw('COUNT(*) as count'))
            ->groupBy('leave_types.id', 'leave_types.name');

        if (! auth()->user()->isSuperAdmin() && ! auth()->user()->isAdmin()) {
            $userDepartment = DB::table('employees')
                ->where('payroll_id', auth()->user()->payroll_id)
                ->value('payroll_department_id');

            if ($userDepartment) {
                $allowedLeaveTypes = DB::table('department_leave_types')
                    ->where('payroll_department_id', $userDepartment)
                    ->pluck('leave_type_id')
                    ->toArray();

                $leaveTypeQuery->whereIn('leave_applications.leave_type_id', $allowedLeaveTypes)
                              ->where('leave_applications.user_id', auth()->id());
            } else {
                $leaveTypeQuery->whereRaw('1 = 0');
            }
        }

        $leaveTypeData = $leaveTypeQuery
            ->orderBy('count', 'desc')
            ->get()
            ->pluck('count', 'name')
            ->toArray();

        $monthlyLeaveQuery = DB::table('leave_applications')
            ->select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                'status',
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', now()->subMonths(6)->startOfMonth())
            ->groupBy('month', 'status')
            ->orderBy('month', 'asc');

        if (! auth()->user()->isSuperAdmin() && ! auth()->user()->isAdmin()) {
            $monthlyLeaveQuery->where('user_id', auth()->id());
        }

        $monthlyLeaveRaw = $monthlyLeaveQuery->get();

        $monthlyLeaveData = [
            'labels' => [],
            'approved' => [],
            'pending' => [],
            'rejected' => [],
        ];

        for ($i = 5; $i >= 0; $i--) {
            $monthKey = now()->subMonths($i)->format('Y-m');
            $monthLabel = now()->subMonths($i)->format('M Y');
            $monthlyLeaveData['labels'][] = $monthLabel;
            $monthlyLeaveData['approved'][] = 0;
            $monthlyLeaveData['pending'][] = 0;
            $monthlyLeaveData['rejected'][] = 0;
        }

        foreach ($monthlyLeaveRaw as $record) {
            $monthIndex = array_search(now()->createFromFormat('Y-m', $record->month)->format('M Y'), $monthlyLeaveData['labels']);
            if ($monthIndex !== false) {
                $status = $record->status;
                if (isset($monthlyLeaveData[$status]) && isset($monthlyLeaveData[$status][$monthIndex])) {
                    $monthlyLeaveData[$status][$monthIndex] = (int) $record->count;
                }
            }
        }

        $approvalStatusQuery = DB::table('leave_applications')
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status');

        if (! auth()->user()->isSuperAdmin() && ! auth()->user()->isAdmin()) {
            $approvalStatusQuery->where('user_id', auth()->id());
        }

        $approvalStatusData = $approvalStatusQuery
            ->get()
            ->pluck('count', 'status')
            ->toArray();

        $approvalStatusData = array_merge([
            'approved' => 0,
            'pending' => 0,
            'rejected' => 0,
        ], $approvalStatusData);

        $activeEmployeesCount = Employee::currentlyActive()->count();

        $leaveBalance = 0;
        if (! auth()->user()->isSuperAdmin()) {
            try {
                $payrollService = new PayrollLeaveService();
                $leaveData = $payrollService->getEmployeeLeaveBalance(auth()->user());

                if ($leaveData['success'] && isset($leaveData['leave_types'])) {
                    $leaveBalance = $leaveData['leave_types']->sum('balance');
                }
            } catch (\Exception $e) {
                $leaveBalance = 0;
            }
        }

        return view('dashboard', compact(
            'upcomingPublicHolidays',
            'employeesOnLeaveToday',
            'employeesOnLeaveTomorrow',
            'leaveTrendsData',
            'leaveTypeData',
            'monthlyLeaveData',
            'approvalStatusData',
            'activeEmployeesCount',
            'leaveBalance',
            'leaveRosterDate',
            'leaveRosterDateCarbon',
            'leavesOnSelectedDate',
            'rosterLeaveBalances',
            'canViewLeaveRoster'
        ));
    }

    /**
     * Leave applications covering a calendar date.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, LeaveApplication>
     */
    private function leavesOnDate(string $date, bool $includePending = false)
    {
        $statuses = $includePending
            ? ['pending', 'approved', 'approved_by_manager', 'forwarded_to_manager']
            : ['approved', 'approved_by_manager'];

        return LeaveApplication::query()
            ->with(['user:id,name,employee_id,payroll_id,email', 'user.department:id,name', 'leaveType:id,name'])
            ->whereIn('status', $statuses)
            ->where(function ($query) use ($date) {
                $query->whereHas('leaveDays', fn ($q) => $q->whereDate('leave_date', $date))
                    ->orWhere(function ($q) use ($date) {
                        $q->whereDate('start_date', '<=', $date)
                            ->whereDate('end_date', '>=', $date);
                    });
            })
            ->orderBy('start_date')
            ->get();
    }

    /**
     * API to get monthly leave data for a specific employee.
     */
    public function getEmployeeMonthlyLeaves(Request $request)
    {
        $userId = $request->input('user_id', 'all');
        $year = $request->input('year', date('Y'));

        if (! $userId) {
            return response()->json(['success' => false, 'message' => 'User ID is required'], 400);
        }

        if (! auth()->user()->isAdmin() && ! auth()->user()->isSuperAdmin() && $userId != auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $query = LeaveApplication::with('leaveType')
            ->where(function ($q) use ($year) {
                $q->whereYear('start_date', $year)
                  ->orWhereYear('end_date', $year);
            });

        if ($userId !== 'all') {
            $query->where('user_id', $userId);
        }

        $leaves = $query->get();

        $approvedDays = array_fill(1, 12, 0);
        $requestVolume = array_fill(1, 12, 0);
        $statusBreakdown = [
            'approved' => array_fill(1, 12, 0),
            'pending' => array_fill(1, 12, 0),
            'rejected' => array_fill(1, 12, 0),
        ];
        $typeDistribution = [];
        $overallOverview = ['approved' => 0, 'pending' => 0, 'rejected' => 0];

        foreach ($leaves as $leave) {
            $start = Carbon::parse($leave->start_date);
            $end = Carbon::parse($leave->end_date);

            $rawStatus = strtolower($leave->status);
            if (in_array($rawStatus, ['approved', 'approved_by_manager'])) {
                $status = 'approved';
            } elseif (in_array($rawStatus, ['pending', 'processing', 'submitted'])) {
                $status = 'pending';
            } else {
                $status = 'rejected';
            }

            $overallOverview[$status]++;

            $monthsForThisLeave = [];
            $current = $start->copy();

            while ($current->lte($end)) {
                if ($current->year == $year) {
                    $m = (int) $current->month;
                    $monthsForThisLeave[$m] = true;

                    if ($status === 'approved') {
                        $approvedDays[$m]++;

                        $typeName = $leave->leaveType ? $leave->leaveType->name : 'Other';
                        $typeDistribution[$typeName] = ($typeDistribution[$typeName] ?? 0) + 1;
                    }
                }
                $current->addDay();
            }

            foreach (array_keys($monthsForThisLeave) as $m) {
                $requestVolume[$m]++;
                $statusBreakdown[$status][$m]++;
            }
        }

        return response()->json([
            'success' => true,
            'approvedDays' => array_values($approvedDays),
            'requestVolume' => array_values($requestVolume),
            'distribution' => $typeDistribution,
            'statusMonthly' => [
                'approved' => array_values($statusBreakdown['approved']),
                'pending' => array_values($statusBreakdown['pending']),
                'rejected' => array_values($statusBreakdown['rejected']),
            ],
            'statusOverview' => $overallOverview,
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            'year' => $year,
            'user' => $userId,
        ]);
    }
}
