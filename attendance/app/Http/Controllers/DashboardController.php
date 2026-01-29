<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\LeaveApplication;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Get current year for filtering
        $currentYear = date('Y');
        
        // Get top 5 employees with most approved leaves (by total days) for current year
        $topLeaveUsers = LeaveApplication::select('user_id', DB::raw('SUM(total_days) as total_leave_days'))
            ->where('status', 'approved')
            ->whereYear('start_date', $currentYear)
            ->with('user:id,name')
            ->groupBy('user_id')
            ->having('total_leave_days', '>', 0)
            ->orderBy('total_leave_days', 'desc')
            ->limit(5)
            ->get();

        // Get employees with least approved leaves (including those with 0 days)
        // Use LEFT JOIN to include all users, even those with no approved leaves
        $leastLeaveUsers = User::leftJoin('leave_applications', function($join) use ($currentYear) {
                $join->on('users.id', '=', 'leave_applications.user_id')
                     ->where('leave_applications.status', '=', 'approved')
                     ->whereYear('leave_applications.start_date', '=', $currentYear);
            })
            ->select('users.id as user_id', 'users.name', DB::raw('COALESCE(SUM(leave_applications.total_days), 0) as total_leave_days'))
            ->groupBy('users.id', 'users.name')
            ->orderBy('total_leave_days', 'asc')
            ->limit(5)
            ->get()
            ->map(function($user) {
                // Transform to match the structure expected by the view
                return (object) [
                    'user_id' => $user->user_id,
                    'total_leave_days' => $user->total_leave_days,
                    'user' => (object) ['name' => $user->name]
                ];
            });

        // Get employees on leave today
        $employeesOnLeaveToday = LeaveApplication::where('status', 'approved')
            ->whereDate('start_date', '<=', now()->toDateString())
            ->whereDate('end_date', '>=', now()->toDateString())
            ->with(['user:id,name,employee_id,payroll_id', 'user.department:id,name'])
            ->get();

        // Get employees on leave tomorrow
        $employeesOnLeaveTomorrow = LeaveApplication::where('status', 'approved')
            ->whereDate('start_date', '<=', now()->addDay()->toDateString())
            ->whereDate('end_date', '>=', now()->addDay()->toDateString())
            ->with(['user:id,name,employee_id,payroll_id', 'user.department:id,name'])
            ->get();

        // Get leave trends for last 6 months (based on created_at)
        $leaveTrendsQuery = LeaveApplication::select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', now()->subMonths(6)->startOfMonth());

        // Filter by user for non-admin users
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->isAdmin()) {
            $leaveTrendsQuery->where('user_id', auth()->id());
        }

        $leaveTrends = $leaveTrendsQuery
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get()
            ->pluck('count', 'month')
            ->toArray();

        // Fill in missing months with 0
        $leaveTrendsData = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i)->format('Y-m');
            $leaveTrendsData[$month] = $leaveTrends[$month] ?? 0;
        }

        // Get leave distribution by type
        $leaveTypeQuery = DB::table('leave_applications')
            ->join('leave_types', 'leave_applications.leave_type_id', '=', 'leave_types.id')
            ->select('leave_types.name', DB::raw('COUNT(*) as count'))
            ->groupBy('leave_types.id', 'leave_types.name');

        // Filter by user role and department
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->isAdmin()) {
            // For regular users, get their department's allowed leave types
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
                // If no department found, show no data
                $leaveTypeQuery->whereRaw('1 = 0');
            }
        }
        // For admins, no additional filtering needed - they see all data

        $leaveTypeData = $leaveTypeQuery
            ->orderBy('count', 'desc')
            ->get()
            ->pluck('count', 'name')
            ->toArray();

        // Get monthly leave applications by status for last 6 months
        $monthlyLeaveQuery = DB::table('leave_applications')
            ->select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                'status',
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', now()->subMonths(6)->startOfMonth())
            ->groupBy('month', 'status')
            ->orderBy('month', 'asc');

        // Filter by user for non-admin users
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->isAdmin()) {
            $monthlyLeaveQuery->where('user_id', auth()->id());
        }

        $monthlyLeaveRaw = $monthlyLeaveQuery->get();

        // Format data for chart (approved, pending, rejected arrays)
        $monthlyLeaveData = [
            'labels' => [],
            'approved' => [],
            'pending' => [],
            'rejected' => []
        ];

        // Initialize all months with 0 values
        for ($i = 5; $i >= 0; $i--) {
            $monthKey = now()->subMonths($i)->format('Y-m');
            $monthLabel = now()->subMonths($i)->format('M Y');
            $monthlyLeaveData['labels'][] = $monthLabel;
            $monthlyLeaveData['approved'][] = 0;
            $monthlyLeaveData['pending'][] = 0;
            $monthlyLeaveData['rejected'][] = 0;
        }

        // Fill in actual data
        foreach ($monthlyLeaveRaw as $record) {
            $monthIndex = array_search(now()->createFromFormat('Y-m', $record->month)->format('M Y'), $monthlyLeaveData['labels']);
            if ($monthIndex !== false) {
                $status = $record->status;
                if (isset($monthlyLeaveData[$status]) && isset($monthlyLeaveData[$status][$monthIndex])) {
                    $monthlyLeaveData[$status][$monthIndex] = (int) $record->count;
                }
            }
        }

        // Get approval status overview from leave_applications table
        $approvalStatusQuery = DB::table('leave_applications')
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status');

        // Filter by user for non-admin users
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->isAdmin()) {
            $approvalStatusQuery->where('user_id', auth()->id());
        }

        $approvalStatusData = $approvalStatusQuery
            ->get()
            ->pluck('count', 'status')
            ->toArray();

        // Ensure all statuses are present with defaults
        $approvalStatusData = array_merge([
            'approved' => 0,
            'pending' => 0,
            'rejected' => 0
        ], $approvalStatusData);

        // Get active employees count based on joining/resignation dates
        $activeEmployeesCount = Employee::currentlyActive()->count();

        // Calculate leave balance for regular users
        $leaveBalance = 0;
        if (!auth()->user()->isSuperAdmin()) {
            try {
                $payrollService = new \App\Services\PayrollLeaveService();
                $leaveData = $payrollService->getEmployeeLeaveBalance(auth()->user());

                if ($leaveData['success'] && isset($leaveData['leave_types'])) {
                    // Sum up all available leave balances
                    $leaveBalance = $leaveData['leave_types']->sum('balance');
                }
            } catch (\Exception $e) {
                // If there's an error calculating leave balance, keep it as 0
                $leaveBalance = 0;
            }
        }

        // Fetch users for employee filter in analytics
        if (auth()->user()->isAdmin() || auth()->user()->isSuperAdmin()) {
            $allUsers = \App\Models\User::whereIn('email', function($subQuery) {
                    $subQuery->select('email')
                            ->from('employees')
                            ->where('exclude_from_payroll', 0);
                })->orderBy('name')->get();
        } else {
            $allUsers = collect([auth()->user()]);
        }

        return view('dashboard', compact('topLeaveUsers', 'leastLeaveUsers', 'employeesOnLeaveToday', 'employeesOnLeaveTomorrow', 'leaveTrendsData', 'leaveTypeData', 'monthlyLeaveData', 'approvalStatusData', 'activeEmployeesCount', 'leaveBalance', 'allUsers'));
    }

    /**
     * API to get monthly leave data for a specific employee.
     */
    public function getEmployeeMonthlyLeaves(Request $request)
    {
        $userId = $request->input('user_id', 'all');
        $year = $request->input('year', date('Y'));

        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'User ID is required'], 400);
        }

        // Security check: non-admins can only see their own data
        if (!auth()->user()->isAdmin() && !auth()->user()->isSuperAdmin() && $userId != auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        // Fetch all leaves that touch the selected year
        $query = \App\Models\LeaveApplication::with('leaveType')
            ->where(function($q) use ($year) {
                $q->whereYear('start_date', $year)
                  ->orWhereYear('end_date', $year);
            });

        if ($userId !== 'all') {
            $query->where('user_id', $userId);
        }

        $leaves = $query->get();

        // 1. Initialize data buckets (1-indexed for months 1-12)
        $approvedDays = array_fill(1, 12, 0);       // Actual days taken (Only Approved)
        $requestVolume = array_fill(1, 12, 0);      // Total application counts present in month
        $statusBreakdown = [
            'approved' => array_fill(1, 12, 0),
            'pending' => array_fill(1, 12, 0),
            'rejected' => array_fill(1, 12, 0)
        ];
        $typeDistribution = [];
        $overallOverview = ['approved' => 0, 'pending' => 0, 'rejected' => 0];

        foreach ($leaves as $leave) {
            $start = \Carbon\Carbon::parse($leave->start_date);
            $end = \Carbon\Carbon::parse($leave->end_date);
            
            // Map statuses for UI standard (approved/pending/rejected)
            $rawStatus = strtolower($leave->status);
            if (in_array($rawStatus, ['approved', 'approved_by_manager'])) {
                $status = 'approved';
            } elseif (in_array($rawStatus, ['pending', 'processing', 'submitted'])) {
                $status = 'pending';
            } else {
                $status = 'rejected';
            }
            
            // Update Overall Overview
            $overallOverview[$status]++;
            
            // Calculate monthly coverage
            $monthsForThisLeave = [];
            $current = $start->copy();
            
            // Loop through every day of the leave to accurately assign days and application presence
            while ($current->lte($end)) {
                if ($current->year == $year) {
                    $m = (int)$current->month;
                    $monthsForThisLeave[$m] = true;
                    
                    // Count as an 'Approved Day' if status is approved
                    if ($status === 'approved') {
                        $approvedDays[$m]++;
                        
                        // Count days per type for distribution
                        $typeName = $leave->leaveType ? $leave->leaveType->name : 'Other';
                        $typeDistribution[$typeName] = ($typeDistribution[$typeName] ?? 0) + 1;
                    }
                }
                $current->addDay();
            }

            // Increment application counts for every month this specific leave touches
            foreach (array_keys($monthsForThisLeave) as $m) {
                $requestVolume[$m]++;
                $statusBreakdown[$status][$m]++;
            }
        }

        // Return unified data with clear key names
        return response()->json([
            'success' => true,
            'approvedDays' => array_values($approvedDays),  // Maps to "Approved Leave Days"
            'requestVolume' => array_values($requestVolume), // Maps to "Leave Request Trends"
            'distribution' => $typeDistribution,
            'statusMonthly' => [
                'approved' => array_values($statusBreakdown['approved']),
                'pending' => array_values($statusBreakdown['pending']),
                'rejected' => array_values($statusBreakdown['rejected']),
            ],
            'statusOverview' => $overallOverview,
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            'year' => $year,
            'user' => $userId
        ]);
    }
}