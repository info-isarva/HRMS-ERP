<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LeaveApplication;
use App\Models\User;
use App\Models\LeaveType;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Services\PayrollApiService;

class ReportsController extends Controller
{
    protected $payrollApiService;

    public function __construct(PayrollApiService $payrollApiService)
    {
        $this->payrollApiService = $payrollApiService;
    }

    /**
     * Display the reports dashboard.
     */
    public function index()
    {
        return view('reports.index');
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

        return view('reports.employee-monthly', compact('users', 'selectedUser', 'userId', 'year', 'monthlyData'));
    }

    /**
     * Display the approved leave list report.
     */
    public function leaveApproved(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $query = LeaveApplication::with(['user', 'leaveType'])
            ->where('status', 'approved');

        if ($startDate && $endDate) {
            $query->where(function ($q) use ($startDate, $endDate) {
                // Check for overlap
                $q->where('start_date', '<=', $endDate)
                  ->where('end_date', '>=', $startDate);
            });
        }

        $leaves = $query->orderBy('start_date', 'desc')->get();

        return view('reports.leave-approved', compact('leaves', 'startDate', 'endDate'));
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

        return view('reports.leave-rejected', compact('leaves', 'startDate', 'endDate'));
    }

    /**
     * Display the employee leave status report.
     */
    public function employeeLeaveStatus(Request $request)
    {
        // Fetch all users with their approved leave applications for calculation
        $users = User::whereIn('email', function($subQuery) {
                $subQuery->select('email')
                        ->from('employees')
                        ->where('exclude_from_payroll', 0);
            })
            ->with(['leaveApplications' => function ($query) {
                $query->whereIn('status', ['approved', 'approved_by_manager']);
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
            // Available = Sum(Allocated_i - Used_i) (max 0)
            $currentAvailable = 0;
            $hasAllocations = !empty($allocations);

            if ($hasAllocations) {
                foreach ($allocations as $alloc) {
                    $typeId = $alloc['leave_type_id'];
                    $effectiveDays = $alloc['effective_days'] ?? 0;
                    $used = $typeUsed[$typeId] ?? 0;
                    
                    // Balance for this leave type
                    $balance = max(0, $effectiveDays - $used);
                    $currentAvailable += $balance;
                }
            } else {
                // If no allocations found (e.g. sync issue or new employee), 
                // Available is 0.
                $currentAvailable = 0;
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

        return view('reports.employee-leave-status', compact('reportData'));
    }
}
