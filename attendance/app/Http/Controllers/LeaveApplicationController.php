<?php

namespace App\Http\Controllers;

use App\Models\LeaveApplication;
use App\Models\LeaveType;
use App\Models\PublicHoliday;
use App\Models\User;
use App\Models\StaticNotificationUser;
use App\Notifications\LeaveApplicationSubmitted;
use App\Notifications\LeaveForwardedToManager;
use App\Notifications\LeaveStatusUpdated;
use App\Services\LeaveNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Carbon\Carbon;
use Spatie\Activitylog\Models\Activity;

class LeaveApplicationController extends Controller
{
    /**
     * Display a listing of the leave applications for the logged in user
     */
    public function index()
    {
        $user = Auth::user();
        $currentFinancialYear = active_fy_label();
        
        // Regular employee only sees their own leave applications for the current active/selected financial year
        $leaves = $user->leaveApplications()
            ->with(['employee.payrollDepartment', 'leaveType', 'managerApprovedBy', 'hrApprovedBy', 'rejectedBy', 'forwardedBy'])
            ->where('financial_year', $currentFinancialYear)
            ->latest()
            ->get();
        
        // Group by status for view
        $pendingLeaves = $leaves->where('status', 'pending');
        $forwardedLeaves = $leaves->where('status', 'forwarded_to_manager');
        $managerApprovedLeaves = $leaves->where('status', 'approved_by_manager');
        $approvedLeaves = $leaves->where('status', 'approved');
        $rejectedLeaves = $leaves->where('status', 'rejected');
        
        // In self-view, no manager/HR actions are required on own leaves
        $leavesRequiringManagerAction = collect();
        $leavesRequiringHRAction = collect();
        
        $isSelfView = true;
        
        return view('leaves.index', compact(
            'leaves', 
            'pendingLeaves', 
            'forwardedLeaves',
            'managerApprovedLeaves',
            'approvedLeaves', 
            'rejectedLeaves', 
            'leavesRequiringManagerAction',
            'leavesRequiringHRAction',
            'currentFinancialYear',
            'isSelfView'
        ));
    }

    /**
     * Display a listing of Pending Leaves for management (Admin/HR/Manager)
     */
    public function pending()
    {
        $user = Auth::user();
        
        // Check if user is a manager (has reportees)
        $managerEmployee = \App\Models\Employee::where('email', $user->email)->first();
        $hasReportees = false;
        if ($managerEmployee) {
            $hasReportees = \App\Models\Employee::where('reporting_manager_payroll_id', $managerEmployee->payroll_id)->exists();
        }
        
        // Define admin/hr status
        $isAdminOrHR = $user->isAdmin() || $user->isSuperAdmin() || $user->role === 'hr';
        
        if (!$isAdminOrHR && !$hasReportees) {
            return redirect()->route('leaves.index')->with('error', 'You do not have permission to view the pending applications page.');
        }

        // Different queries based on user role
        if ($isAdminOrHR) {
            // Admin/HR sees all leave applications that are not yet finalized
            // Finalized = approved, rejected, or cancelled
            $leaves = LeaveApplication::with(['user', 'employee.payrollDepartment', 'leaveType', 'managerApprovedBy', 'hrApprovedBy', 'rejectedBy', 'forwardedBy'])
                ->whereNotIn('status', ['approved', 'rejected', 'cancelled'])
                ->latest()
                ->get();
        } else {
            // Managers see only their reportees' non-finalized leaves
            $reporteeEmails = \App\Models\Employee::where('reporting_manager_payroll_id', $managerEmployee->payroll_id)
                ->pluck('email')->toArray();
            $reporteeIds = \App\Models\User::whereIn('email', $reporteeEmails)->pluck('id')->toArray();
            
            $leaves = LeaveApplication::with(['user', 'employee.payrollDepartment', 'leaveType', 'managerApprovedBy', 'hrApprovedBy', 'rejectedBy', 'forwardedBy'])
                ->whereIn('user_id', $reporteeIds)
                ->whereNotIn('status', ['approved', 'rejected', 'cancelled'])
                ->latest()
                ->get();
        }
        
        $currentFinancialYear = active_fy_label();
        
        // Group by status
        $pendingLeaves = $leaves->where('status', 'pending');
        $forwardedLeaves = $leaves->where('status', 'forwarded_to_manager');
        $managerApprovedLeaves = $leaves->where('status', 'approved_by_manager');
        $approvedLeaves = $leaves->where('status', 'approved');
        $rejectedLeaves = $leaves->where('status', 'rejected');
        
        // Filter leaves requiring action
        $leavesRequiringManagerAction = collect();
        $leavesRequiringHRAction = collect();
        
        if ($hasReportees) {
            $reporteeEmails = \App\Models\Employee::where('reporting_manager_payroll_id', $managerEmployee->payroll_id)
                ->pluck('email')->toArray();
            $reporteeIds = \App\Models\User::whereIn('email', $reporteeEmails)->pluck('id')->toArray();
            
            $leavesRequiringManagerAction = $forwardedLeaves->filter(function($leave) use ($reporteeIds) {
                return in_array($leave->user_id, $reporteeIds);
            });
        }
        
        if ($isAdminOrHR) {
            $leavesRequiringHRAction = $managerApprovedLeaves->merge($pendingLeaves);
        }
        
        $isSelfView = false;
        
        return view('leaves.index', compact(
            'leaves', 
            'pendingLeaves', 
            'forwardedLeaves',
            'managerApprovedLeaves',
            'approvedLeaves', 
            'rejectedLeaves', 
            'leavesRequiringManagerAction',
            'leavesRequiringHRAction',
            'currentFinancialYear',
            'isSelfView'
        ));
    }

    /**
     * Show the form for creating a new leave application
     */
    public function create()
    {
        $user = Auth::user();
        
        // Check if user has an employee record
        $employee = $this->getEmployeeForUser($user);
        if (!$employee) {
            return redirect()->route('leaves.index')->with('error', 'Super admin users cannot apply for leaves as they are not registered as employees.');
        }
        
        $payrollService = new \App\Services\PayrollLeaveService();
        
        // Get leave types and balances from payroll system (or fallback to local)
        $leaveData = $payrollService->getEmployeeLeaveBalance($user);
        
        $availableLeaveTypes = $leaveData['leave_types'];
        $currentFinancialYear = $leaveData['financial_year'];
        
        // Format leave balances for the view
        $leaveBalances = [];
        foreach ($availableLeaveTypes as $leaveType) {
            $allocated = $leaveType->effective_days ?? $leaveType->days_count ?? 0;
            $used = $leaveType->used ?? 0;
            $balance = $leaveType->balance ?? 0;
            
            $leaveBalances[$leaveType->id] = [
                'allocated' => $allocated,
                'used' => $used,
                'balance' => $balance
            ];
        }
        
        // Calculate LOP summary for the user
        $lopSummary = $this->calculateUserLOPSummary($user);
        
        // Add a flag to indicate data source for debugging
        $dataSource = $leaveData['fallback'] ?? false ? 'local_fallback' : 'payroll_api';
        
        return view('leaves.create', compact('availableLeaveTypes', 'leaveBalances', 'currentFinancialYear', 'dataSource', 'lopSummary'));
    }

    /**
     * Store a newly created leave application
     */
    public function store(Request $request)
    {
       
        $user = Auth::user();
        
        // Check if user has an employee record
        $employee = $this->getEmployeeForUser($user);
        if (!$employee) {
            return redirect()->route('leaves.index')->with('error', 'Super admin users cannot apply for leaves as they are not registered as employees.');
        }

        $request->validate([
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|min:10|max:500',
            'custom_half_days' => 'nullable|string', // JSON string from frontend
            'calculated_total_days' => 'required|numeric|min:0', // Pre-calculated days from frontend
            'emergency_contact_name' => 'nullable|string|max:100',
            'emergency_contact_phone' => 'nullable|string|max:15',
            'lop_acknowledged' => 'sometimes|boolean', // LOP acknowledgment
        ]);
        $currentFinancialYear = active_fy_label();
        $leaveType = LeaveType::findOrFail($request->leave_type_id);

        // Check if the leave type is available for the user's department using Employee model
        $employee = null;
        if ($user && $user->payroll_id) {
            $employee = \App\Models\Employee::where('payroll_id', $user->payroll_id)->first();
        }
        
        if ($employee && !$employee->hasAccessToLeaveType($leaveType->id)) {
            return redirect()->back()->with('error', 'The selected leave type is not available for your department.');
        }

        // Check for overlapping leave applications
        $overlapCheck = $this->checkLeaveOverlap(
            $user->id, 
            $request->start_date, 
            $request->end_date
        );
        
        if ($overlapCheck['hasOverlap']) {
            return redirect()->back()->with('error', $overlapCheck['message']);
        }

        // Use the enhanced calculation service
        $leaveService = new \App\Services\LeaveCalculationService();
        $customHalfDays = [];
        
        if ($request->filled('custom_half_days')) {
            $customHalfDaysJson = $request->custom_half_days;
            if (is_string($customHalfDaysJson)) {
                $customHalfDays = json_decode($customHalfDaysJson, true) ?? [];
            }
        }

        // DEBUG: Check what customHalfDays contains
        echo "DEBUG: custom_half_days from request: " . $request->custom_half_days . PHP_EOL;
        echo "DEBUG: decoded customHalfDays: "; print_r($customHalfDays); echo PHP_EOL;
        
        // Use LOP-aware calculation
        $calculation = $leaveService->calculateDetailedLeaveDaysWithLOP(
            $request->start_date,
            $request->end_date,
            $customHalfDays,
            $leaveType->id,
            $user
        );

        // DEBUG: Check calculation result
        echo "DEBUG: calculation leave_days: "; print_r($calculation['leave_days']); echo PHP_EOL;

        // Check if LOP is involved and if user acknowledged it
        if ($calculation['has_lop'] && !$request->lop_acknowledged) {
            return redirect()->back()
                ->withInput()
                ->with('lop_warning', [
                    'total_days' => $calculation['total_days'],
                    'paid_days' => $calculation['paid_days'],
                    'lop_days' => $calculation['lop_days'],
                    'available_balance' => $calculation['available_balance'],
                    'leave_type_name' => $leaveType->name
                ])
                ->with('error', "Warning: You are requesting {$calculation['total_days']} days but only have {$calculation['available_balance']} days available. {$calculation['lop_days']} days will be Loss of Pay (LOP). Please acknowledge to continue.");
        }

        // Create leave application with LOP details
        $leaveApplication = LeaveApplication::create([
            'user_id' => $user->id,
            'email_id' => $user->email,
            'leave_type_id' => $leaveType->id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'total_days' => $request->calculated_total_days, // Use pre-calculated value from frontend
            'paid_days' => $calculation['paid_days'] ?? $calculation['total_days'],
            'lop_days' => $calculation['lop_days'] ?? 0,
            'has_lop' => $calculation['has_lop'] ?? false,
            'lop_acknowledged' => $request->lop_acknowledged ?? false,
            'reason' => $request->reason,
            'status' => 'pending',
            'financial_year' => $currentFinancialYear,
            'emergency_contact_name' => $request->emergency_contact_name,
            'emergency_contact_phone' => $request->emergency_contact_phone,
        ]);
        
        // Store detailed leave days
        foreach ($calculation['leave_days'] as $dayData) {
            \App\Models\LeaveApplicationDay::create([
                'leave_application_id' => $leaveApplication->id,
                'leave_date' => $dayData['leave_date'],
                'day_type' => $dayData['day_type'],
                'days_count' => $dayData['days_count'],
                'is_public_holiday' => $dayData['is_public_holiday'],
                'is_week_off' => $dayData['is_week_off'] ?? false,
                'exclude_from_calculation' => $dayData['exclude_from_calculation'],
                'notes' => $dayData['notes'],
            ]);
        }

        // Log activity with detailed breakdown
        activity()
            ->performedOn($leaveApplication)
            ->causedBy($user)
            ->withProperties([
                'leave_type' => $leaveType->name,
                'total_days' => $calculation['total_days'],
                'breakdown' => $calculation['breakdown'],
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
            ])
            ->log('Leave application submitted with detailed day breakdown');

        // Send dynamic email notifications
        try {
            $notificationService = new LeaveNotificationService();
            $notificationService->sendLeaveSubmittedNotifications($leaveApplication);
            Log::info('Leave submitted notifications triggered', ['leave_id' => $leaveApplication->id]);
        } catch (\Exception $e) {
            Log::error('Failed to trigger leave submitted notifications', [
                'leave_id' => $leaveApplication->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }

        return redirect()->route('leaves.index')->with('success', 
            "Leave application submitted successfully. Total leave days to be deducted: {$calculation['total_days']} days. Email notifications have been sent to HR and your reporting manager.");
    }

    /**
     * Display the specified leave application
     */
    public function show(LeaveApplication $leave)
    {
        $this->authorize('view', $leave);
        
        $leave->load(['user', 'employee.payrollDepartment', 'leaveType', 'leaveDays']);
        $activities = Activity::where('subject_type', LeaveApplication::class)
                        ->where('subject_id', $leave->id)
                        ->latest()
                        ->get();
                        
        return view('leaves.show', compact('leave', 'activities'));
    }

    /**
     * Show the form for editing the specified leave application
     * Only pending applications can be edited
     */
    public function edit(LeaveApplication $leave)
    {
        $this->authorize('update', $leave);
        
        if ($leave->status !== 'pending') {
            return redirect()->route('leaves.show', $leave)->with('error', 'Only pending leave applications can be edited.');
        }
        
        $user = Auth::user();
        $payrollService = new \App\Services\PayrollLeaveService();
        
        // Get leave types and balances from payroll system (or fallback to local)
        $leaveData = $payrollService->getEmployeeLeaveBalance($user);
        
        $availableLeaveTypes = $leaveData['leave_types'];
        $currentFinancialYear = $leaveData['financial_year'];
        
        // Format leave balances for the view (adjust for current leave being edited)
        $leaveBalances = [];
        foreach ($availableLeaveTypes as $leaveType) {
            $allocated = $leaveType->effective_days ?? $leaveType->days_count ?? 0; // Use effective_days with fallback
            $used = $leaveType->used ?? 0;
            
            // If editing the same leave type, subtract the current leave days from used
            if ($leaveType->id == $leave->leave_type_id) {
                $used = max(0, $used - $leave->total_days);
            }
            
            $balance = $allocated - $used;
            
            $leaveBalances[$leaveType->id] = [
                'allocated' => $allocated,
                'used' => $used,
                'balance' => $balance
            ];
        }
        
        // Add a flag to indicate data source for debugging
        $dataSource = $leaveData['fallback'] ?? false ? 'local_fallback' : 'payroll_api';
        
        return view('leaves.edit', compact('leave', 'availableLeaveTypes', 'leaveBalances', 'currentFinancialYear', 'dataSource'));
    }

    /**
     * Update the specified leave application
     */
    public function update(Request $request, LeaveApplication $leave)
    {
        $this->authorize('update', $leave);
        
        if ($leave->status !== 'pending') {
            return redirect()->route('leaves.show', $leave)->with('error', 'Only pending leave applications can be updated.');
        }
        
        $request->validate([
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|min:10|max:500',
            'custom_half_days' => 'nullable|string', // JSON string from frontend
            'calculated_total_days' => 'required|numeric|min:0', // Pre-calculated days from frontend
            'emergency_contact_name' => 'nullable|string|max:100',
            'emergency_contact_phone' => 'nullable|string|max:15',
        ]);

        $user = Auth::user();
        $currentFinancialYear = active_fy_label();
        $leaveType = LeaveType::findOrFail($request->leave_type_id);
        
        // Check if the leave type is available for the user's department using Employee model
        $employee = null;
        if ($user && $user->payroll_id) {
            $employee = \App\Models\Employee::where('payroll_id', $user->payroll_id)->first();
        }
        
        if ($employee && !$employee->hasAccessToLeaveType($leaveType->id)) {
            return redirect()->back()->with('error', 'The selected leave type is not available for your department.');
        }
        
        // Check for overlapping leave applications (exclude current leave)
        $overlapCheck = $this->checkLeaveOverlap(
            $user->id, 
            $request->start_date, 
            $request->end_date,
            $leave->id
        );
        
        if ($overlapCheck['hasOverlap']) {
            return redirect()->back()->with('error', $overlapCheck['message']);
        }
        
        // Use the enhanced calculation service
        $leaveService = new \App\Services\LeaveCalculationService();
        $customHalfDays = [];
        
        if ($request->filled('custom_half_days')) {
            $customHalfDaysJson = $request->custom_half_days;
            if (is_string($customHalfDaysJson)) {
                $customHalfDays = json_decode($customHalfDaysJson, true) ?? [];
            }
        }
        
        // Log the custom half days for debugging
        \Log::debug('Update - Custom half days from request:', [
            'raw_input' => $request->custom_half_days,
            'decoded_half_days' => $customHalfDays
        ]);
        
        // Use LOP-aware calculation
        $calculation = $leaveService->calculateDetailedLeaveDaysWithLOP(
            $request->start_date,
            $request->end_date,
            $customHalfDays,
            $leaveType->id,
            $user
        );
        
        // Log the calculation result for debugging
        \Log::debug('Update - Leave calculation result:', [
            'total_days' => $calculation['total_days'],
            'leave_days' => $calculation['leave_days']
        ]);
        
        // Check if LOP is involved and if user acknowledged it
        if ($calculation['has_lop'] && !$request->lop_acknowledged) {
            return redirect()->back()
                ->withInput()
                ->with('lop_warning', [
                    'total_days' => $calculation['total_days'],
                    'paid_days' => $calculation['paid_days'],
                    'lop_days' => $calculation['lop_days'],
                    'available_balance' => $calculation['available_balance'],
                    'leave_type_name' => $leaveType->name
                ])
                ->with('error', "Warning: You are requesting {$calculation['total_days']} days but only have {$calculation['available_balance']} days available. {$calculation['lop_days']} days will be Loss of Pay (LOP). Please acknowledge to continue.");
        }
        
        // Check leave balance from payroll system
        $payrollService = new \App\Services\PayrollLeaveService();
        $balanceInfo = $payrollService->getLeaveTypeBalance($leaveType->id, $user);
        
        // Adjust balance for current leave being edited
        $availableBalance = $balanceInfo['balance'];
        if ($leaveType->id == $leave->leave_type_id) {
            $availableBalance += $leave->total_days; // Add back current leave days
        }
        
        if ($calculation['total_days'] > $availableBalance) {
            return redirect()->back()->with('error', "Insufficient leave balance. You have {$availableBalance} days available for {$leaveType->name}.");
        }
        
        // Calculate the correct total days value
        $totalDays = $request->filled('calculated_total_days') 
                    ? floatval($request->calculated_total_days) 
                    : $calculation['total_days'];
                    
        // Log the total days for debugging
        \Log::debug('Update - Total days calculation:', [
            'from_request' => $request->calculated_total_days,
            'from_calculation' => $calculation['total_days'],
            'final_value' => $totalDays
        ]);
                
        // Update leave application with LOP details
        $leave->update([
            'leave_type_id' => $leaveType->id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'total_days' => $totalDays, // Use the determined total days value
            'paid_days' => $calculation['paid_days'] ?? $totalDays,
            'lop_days' => $calculation['lop_days'] ?? 0,
            'has_lop' => $calculation['has_lop'] ?? false,
            'lop_acknowledged' => $request->lop_acknowledged ?? false,
            'reason' => $request->reason,
            'emergency_contact_name' => $request->emergency_contact_name,
            'emergency_contact_phone' => $request->emergency_contact_phone,
        ]);
        
        // Delete existing leave days and recreate them
        $leave->leaveDays()->delete();
        
        // Store detailed leave days
        foreach ($calculation['leave_days'] as $dayData) {
            \App\Models\LeaveApplicationDay::create([
                'leave_application_id' => $leave->id,
                'leave_date' => $dayData['leave_date'],
                'day_type' => $dayData['day_type'],
                'days_count' => $dayData['days_count'],
                'is_public_holiday' => $dayData['is_public_holiday'],
                'is_week_off' => $dayData['is_week_off'] ?? false,
                'exclude_from_calculation' => $dayData['exclude_from_calculation'],
                'notes' => $dayData['notes'],
            ]);
        }
        
        // Log activity with detailed breakdown
        activity()
            ->performedOn($leave)
            ->causedBy($user)
            ->withProperties([
                'leave_type' => $leaveType->name,
                'total_days' => $calculation['total_days'],
                'breakdown' => $calculation['breakdown'] ?? null,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
            ])
            ->log('Leave application updated');
            
        return redirect()->route('leaves.show', $leave)->with('success', 'Leave application updated successfully.');
    }

    /**
     * Cancel a leave application (only pending ones can be cancelled)
     */
    public function cancel(LeaveApplication $leave)
    {
        $this->authorize('delete', $leave);
        
        if ($leave->status !== 'pending') {
            return redirect()->route('leaves.show', $leave)->with('error', 'Only pending leave applications can be cancelled.');
        }
        
        $leave->update([
            'status' => 'cancelled'
        ]);
        
        // Log activity
        activity()
            ->performedOn($leave)
            ->causedBy(Auth::user())
            ->log('Leave application cancelled');
            
        return redirect()->route('leaves.index')->with('success', 'Leave application cancelled successfully.');
    }
    
    /**
     * Approve a leave application as a manager
     */
    public function approveAsManager(LeaveApplication $leave)
    {
        $this->authorize('approve', $leave);
        $user = Auth::user();
        
        if (!$leave->isForwardedToManager() && !($user->role === 'admin' || $user->role === 'super_admin')) {
            return redirect()->route('leaves.show', $leave)
                ->with('error', 'Only forwarded leave applications can be approved by managers.');
        }
        
        if (!$leave->canApproveAsManager($user)) {
            return redirect()->route('leaves.show', $leave)
                ->with('error', 'You are not authorized to approve this leave application as a manager.');
        }
        
        // If the user is HR/admin, approve directly
        if ($user->role === 'admin' || $user->role === 'super_admin') {
            $leave->update([
                'status' => 'approved',
                'hr_approved_by' => $user->id,
                'hr_approved_at' => now(),
            ]);
            
            // Send notification to employee
            $notificationService = new LeaveNotificationService();
            $notificationService->sendLeaveStatusUpdatedNotifications($leave, 'approved', null, $user->name);
            
            // Log activity
            activity()
                ->performedOn($leave)
                ->causedBy($user)
                ->log('Leave application approved by HR directly');
                
            return redirect()->route('leaves.show', $leave)
                ->with('success', 'Leave application approved successfully. Employee has been notified.');
        }
        
        // Otherwise, it's a manager approval
        $leave->update([
            'status' => 'approved_by_manager',
            'manager_approved_by' => $user->id,
            'manager_approved_at' => now(),
        ]);
        
        // Send notifications to employee and HR
        $notificationService = new LeaveNotificationService();
        $notificationService->sendLeaveStatusUpdatedNotifications($leave, 'approved_by_manager', null, $user->name);
        
        // Log activity
        activity()
            ->performedOn($leave)
            ->causedBy($user)
            ->log('Leave application approved by manager');
            
        return redirect()->route('leaves.show', $leave)
            ->with('success', 'Leave application approved by manager. HR approval is still pending. Notifications sent.');
    }
    
    /**
     * Approve a leave application as HR (admin/super-admin only)
     */
    public function approveAsHR(LeaveApplication $leave)
    {
        $this->authorize('approve', $leave);
        $user = Auth::user();
        
        if (!$leave->canApproveAsHR($user)) {
            return redirect()->route('leaves.show', $leave)
                ->with('error', 'You are not authorized to approve this leave application as HR.');
        }
        
        $leave->update([
            'status' => 'approved',
            'hr_approved_by' => $user->id,
            'hr_approved_at' => now(),
        ]);
        
        // Send notification to employee
        $notificationService = new LeaveNotificationService();
        $notificationService->sendLeaveStatusUpdatedNotifications($leave, 'approved', null, $user->name);
        
        // Log activity
        activity()
            ->performedOn($leave)
            ->causedBy($user)
            ->log('Leave application approved by HR');
            
        return redirect()->route('leaves.show', $leave)
            ->with('success', 'Leave application fully approved. Employee has been notified.');
    }
    
    /**
     * Reject a leave application (can be done by manager or HR)
     */
    public function reject(Request $request, LeaveApplication $leave)
    {
        $this->authorize('reject', $leave);
        $user = Auth::user();
        
        $request->validate([
            'rejection_reason' => 'required|string|min:10|max:500',
        ]);
        
        if (!$leave->isPending() && !$leave->isForwardedToManager() && !$leave->isManagerApproved()) {
            return redirect()->route('leaves.show', $leave)
                ->with('error', 'Only pending, forwarded, or manager-approved leave applications can be rejected.');
        }
        
        // Check authorization based on current status
        if (($leave->isPending() || $leave->isForwardedToManager()) && !$leave->canApproveAsManager($user)) {
            return redirect()->route('leaves.show', $leave)
                ->with('error', 'You are not authorized to reject this leave application.');
        }
        
        if ($leave->isManagerApproved() && !$leave->canApproveAsHR($user)) {
            return redirect()->route('leaves.show', $leave)
                ->with('error', 'Only HR can reject a manager-approved leave application.');
        }
        
        $leave->update([
            'status' => 'rejected',
            'rejection_reason' => $request->rejection_reason,
            'rejected_by' => $user->id,
            'rejected_at' => now(),
        ]);
        
        // Send notifications to employee and HR (if rejected by manager)
        $notificationService = new LeaveNotificationService();
        $notificationService->sendLeaveStatusUpdatedNotifications($leave, 'rejected', $request->rejection_reason, $user->name);
        
        // Log activity
        activity()
            ->performedOn($leave)
            ->causedBy($user)
            ->withProperties(['rejection_reason' => $request->rejection_reason])
            ->log('Leave application rejected');
            
        return redirect()->route('leaves.show', $leave)
            ->with('success', 'Leave application rejected successfully. Notifications sent.');
    }

    /**
     * Forward a leave application to the reporting manager
     */
    public function forwardToManager(Request $request, LeaveApplication $leave)
    {
        $user = Auth::user();
        
        // Only admin/HR can forward
        if (!$leave->canForward($user)) {
            return redirect()->route('leaves.show', $leave)
                ->with('error', 'You are not authorized to forward this leave application or it cannot be forwarded.');
        }
        
        $request->validate([
            'forwarding_note' => 'nullable|string|max:500',
        ]);
        
        // Get the employee from employees table using email (to link user to employee)
        $employee = \App\Models\Employee::where('email', $leave->user->email)->first();
        $reportingManager = null;
        
        if ($employee && $employee->reporting_manager_payroll_id) {
            // Find reporting manager using payroll_id from employees table only
            $reportingManager = \App\Models\Employee::where('payroll_id', $employee->reporting_manager_payroll_id)->first();
        }
        
        if (!$reportingManager) {
            return redirect()->route('leaves.show', $leave)
                ->with('error', 'Reporting manager not found. Cannot forward leave application.');
        }
        
        $leave->update([
            'status' => 'forwarded_to_manager',
            'forwarded_by' => $user->id,
            'forwarded_at' => now(),
            'forwarding_note' => $request->forwarding_note,
        ]);
        
        // Send notification to reporting manager
        try {
            $notificationService = new LeaveNotificationService();
            $notificationService->sendLeaveForwardedNotification($leave, $request->forwarding_note);
            Log::info('Leave forwarded notification triggered', ['leave_id' => $leave->id]);
        } catch (\Exception $e) {
            Log::error('Failed to trigger leave forwarded notification', [
                'leave_id' => $leave->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
        
        // Log activity
        activity()
            ->performedOn($leave)
            ->causedBy($user)
            ->withProperties([
                'forwarded_to_manager' => $reportingManager->name,
                'forwarding_note' => $request->forwarding_note,
            ])
            ->log('Leave application forwarded to reporting manager');
            
        return redirect()->route('leaves.show', $leave)
            ->with('success', "Leave application forwarded to {$reportingManager->name} for approval. Email notification has been sent.");
    }

    /**
     * Calculate leave days - API endpoint for frontend
     */
    public function calculateLeaveDaysApi(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'half_day_dates' => 'sometimes|array',
            'leave_type_id' => 'sometimes|integer',
            'exclude_leave_id' => 'sometimes|integer'
        ]);

        $user = Auth::user();
        
        // Check for overlapping leaves first
        $overlapCheck = $this->checkLeaveOverlap(
            $user->id, 
            $request->start_date, 
            $request->end_date,
            $request->exclude_leave_id
        );
        
        if ($overlapCheck['hasOverlap']) {
            return response()->json([
                'error' => true,
                'message' => $overlapCheck['message'],
                'overlapping_leave' => $overlapCheck['overlappingLeave']
            ], 422);
        }
        
        $leaveService = new \App\Services\LeaveCalculationService();
        
        // If leave type is provided, calculate with LOP
        if ($request->leave_type_id) {
            $result = $leaveService->calculateDetailedLeaveDaysWithLOP(
                $request->start_date,
                $request->end_date,
                $request->half_day_dates ?? [],
                $request->leave_type_id,
                $user
            );
        } else {
            $result = $leaveService->calculateDetailedLeaveDays(
                $request->start_date,
                $request->end_date,
                $request->half_day_dates ?? [],
                $user
            );
        }

        return response()->json($result);
    }

    /**
     * Get leave days for a specific leave application
     */
    public function getLeaveDays(LeaveApplication $leave)
    {
        $this->authorize('view', $leave);
        
        $leaveDays = $leave->leaveDays()->orderBy('leave_date')->get();
        
        return response()->json([
            'leave_days' => $leaveDays->map(function ($day) {
                return [
                    'leave_date' => $day->leave_date->format('Y-m-d'),
                    'day_type' => $day->day_type,
                    'days_count' => $day->days_count,
                    'is_public_holiday' => $day->is_public_holiday,
                    'is_week_off' => $day->is_week_off,
                    'exclude_from_calculation' => $day->exclude_from_calculation,
                    'notes' => $day->notes,
                ];
            })
        ]);
    }

    /**
     * Calculate leave days excluding weekends and public holidays (Updated method)
     */
    private function calculateLeaveDays($startDate, $endDate, $startHalfDay, $endHalfDay)
    {
        // Convert to new detailed calculation method
        $halfDayDates = [];
        
        if ($startHalfDay !== 'none') {
            $halfDayDates[$startDate] = $startHalfDay;
        }
        
        if ($endHalfDay !== 'none') {
            // Only add end half day if it's different from start date OR if start date doesn't have half day
            if ($startDate !== $endDate || $startHalfDay === 'none') {
                $halfDayDates[$endDate] = $endHalfDay;
            }
        }
        
        $user = Auth::user();
        $leaveService = new \App\Services\LeaveCalculationService();
        
        $result = $leaveService->calculateDetailedLeaveDays(
            $startDate,
            $endDate,
            $halfDayDates,
            $user
        );

        return $result['total_days'];
    }

    /**
     * Check for overlapping leave applications
     */
    private function checkLeaveOverlap($userId, $startDate, $endDate, $excludeLeaveId = null)
    {
        // Get all non-rejected leave applications for the user that might overlap
        $query = LeaveApplication::where('user_id', $userId)
            ->whereIn('status', ['pending', 'approved_by_manager', 'approved'])
            ->where(function($q) use ($startDate, $endDate) {
                // Check if the new leave dates overlap with existing leaves
                $q->where(function($dateQuery) use ($startDate, $endDate) {
                    // Case 1: New leave starts before existing leave ends
                    $dateQuery->where('start_date', '<=', $endDate)
                              ->where('end_date', '>=', $startDate);
                });
            });
            
        // Exclude current leave if editing
        if ($excludeLeaveId) {
            $query->where('id', '!=', $excludeLeaveId);
        }
        
        $overlappingLeaves = $query->get();
        
        if ($overlappingLeaves->count() > 0) {
            $overlappingLeave = $overlappingLeaves->first();
            $overlapStart = Carbon::parse($overlappingLeave->start_date)->format('M d, Y');
            $overlapEnd = Carbon::parse($overlappingLeave->end_date)->format('M d, Y');
            
            $message = "Leave application overlaps with an existing leave application from {$overlapStart} to {$overlapEnd} (Status: " . ucfirst(str_replace('_', ' ', $overlappingLeave->status)) . "). ";
            $message .= "Please choose different dates or cancel the existing application first.";
            
            return [
                'hasOverlap' => true,
                'message' => $message,
                'overlappingLeave' => $overlappingLeave
            ];
        }
        
        return ['hasOverlap' => false];
    }

    /**
     * Calculate user's LOP summary from approved leave applications
     */
    private function calculateUserLOPSummary($user)
    {
        $currentFinancialYear = active_fy_label();
        
        // Get all approved leave applications with LOP for current financial year using email_id
        $lopApplications = LeaveApplication::where('email_id', $user->email)
            ->where('financial_year', $currentFinancialYear)
            ->where('status', 'approved')
            ->where('has_lop', true)
            ->with('leaveType')
            ->get();
        
        if ($lopApplications->isEmpty()) {
            return null; // No LOP history
        }
        
        $totalLopDays = $lopApplications->sum('lop_days');
        $lopByLeaveType = [];
        
        foreach ($lopApplications as $application) {
            $leaveTypeName = $application->leaveType->name ?? 'Unknown';
            if (!isset($lopByLeaveType[$leaveTypeName])) {
                $lopByLeaveType[$leaveTypeName] = 0;
            }
            $lopByLeaveType[$leaveTypeName] += $application->lop_days;
        }
        
        return [
            'total_lop_days' => $totalLopDays,
            'lop_applications_count' => $lopApplications->count(),
            'lop_by_leave_type' => $lopByLeaveType,
            'recent_lop_applications' => $lopApplications->sortByDesc('created_at')->take(3),
            'financial_year' => $currentFinancialYear
        ];
    }



    /**
     * Get employee record for a user
     * Super admin users are not in the employees table, so they return null
     */
    private function getEmployeeForUser($user)
    {
        if (!$user) {
            return null;
        }

        // First try by payroll_id if available
        if ($user->payroll_id) {
            $employee = \App\Models\Employee::where('payroll_id', $user->payroll_id)->first();
            if ($employee) {
                return $employee;
            }
        }

        // Fallback to email lookup
        return \App\Models\Employee::where('email', $user->email)->first();
    }
}
