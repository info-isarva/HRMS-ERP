<?php

namespace App\Http\Controllers\Api\Mobile\V1;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\LeaveApplication;
use App\Models\User;
use App\Services\LeaveNotificationService;
use App\Services\LeavePushNotificationService;
use App\Support\MobileLeaveApplicationSupport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AdminLeaveController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = Auth::guard('api')->user();
        if (! $this->userCanAccessAdminLeaveQueue($user)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to view this list.',
            ], 403);
        }

        $fy = MobileLeaveApplicationSupport::resolveOperationalFinancialYearName(
            $request->query('financial_year')
        );
        $pendingOnly = $request->boolean('pending_only', true);

        $isAdminOrHr = $user->isAdmin() || $user->isSuperAdmin() || $user->role === 'hr';

        if ($isAdminOrHr) {
            $query = LeaveApplication::with(['user', 'leaveType', 'managerApprovedBy:id,name', 'hrApprovedBy:id,name', 'rejectedBy:id,name', 'forwardedBy:id,name'])
                ->where('financial_year', $fy);
            if ($pendingOnly) {
                $query->whereNotIn('status', ['approved', 'rejected', 'cancelled']);
            }
        } else {
            $managerEmployee = Employee::where('email', $user->email)->first();
            if (! $managerEmployee) {
                return response()->json(['success' => false, 'message' => 'Manager profile not found.'], 403);
            }
            $reporteeEmails = Employee::where('reporting_manager_payroll_id', $managerEmployee->payroll_id)->pluck('email')->toArray();
            $reporteeIds = User::whereIn('email', $reporteeEmails)->pluck('id')->toArray();

            $query = LeaveApplication::with(['user', 'leaveType', 'managerApprovedBy:id,name', 'hrApprovedBy:id,name', 'rejectedBy:id,name', 'forwardedBy:id,name'])
                ->whereIn('user_id', $reporteeIds)
                ->where('financial_year', $fy);
            if ($pendingOnly) {
                $query->whereNotIn('status', ['approved', 'rejected', 'cancelled']);
            }
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        $perPage = min(max((int) $request->query('per_page', 30), 1), 100);
        $paginator = $query->latest()->paginate($perPage);

        $items = collect($paginator->items())->map(function (LeaveApplication $leave) {
            return [
                'id' => $leave->id,
                'status' => $leave->status,
                'start_date' => $leave->start_date?->format('Y-m-d'),
                'end_date' => $leave->end_date?->format('Y-m-d'),
                'total_days' => (float) $leave->total_days,
                'financial_year' => $leave->financial_year,
                'reason' => $leave->reason,
                'created_at' => $leave->created_at?->toISOString(),
                'employee' => $leave->user ? [
                    'id' => $leave->user->id,
                    'name' => $leave->user->name,
                    'email' => $leave->user->email,
                    'employee_id' => $leave->user->employee_id,
                ] : null,
                'leave_type' => $leave->leaveType ? [
                    'id' => $leave->leaveType->id,
                    'name' => $leave->leaveType->name,
                    'code' => $leave->leaveType->code,
                ] : null,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => [
                'items' => $items,
                'meta' => [
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                ],
            ],
        ]);
    }

    public function show(Request $request, LeaveApplication $leave): JsonResponse
    {
        $user = Auth::guard('api')->user();
        if (! $this->userCanAccessAdminLeaveQueue($user)) {
            return response()->json(['success' => false, 'message' => 'Forbidden.'], 403);
        }
        $this->authorizeForUser($user, 'view', $leave);

        $leave->load([
            'user',
            'leaveType',
            'leaveDays',
            'managerApprovedBy:id,name',
            'hrApprovedBy:id,name',
            'rejectedBy:id,name',
            'forwardedBy:id,name',
        ]);

        return response()->json([
            'success' => true,
            'data' => $this->formatAdminLeaveDetail($leave),
        ]);
    }

    public function approve(Request $request, LeaveApplication $leave): JsonResponse
    {
        $user = Auth::guard('api')->user();
        $this->authorizeForUser($user, 'approve', $leave);

        if ($leave->status === 'approved_by_manager' && $leave->canApproveAsHR($user)) {
            $leave->update([
                'status' => 'approved',
                'hr_approved_by' => $user->id,
                'hr_approved_at' => now(),
            ]);

            try {
                $notificationService = new LeaveNotificationService();
                $notificationService->sendLeaveStatusUpdatedNotifications($leave, 'approved', null, $user->name);
            } catch (\Throwable $e) {
                Log::error('Mobile admin approve HR: notification failed', ['id' => $leave->id, 'error' => $e->getMessage()]);
            }

            $this->sendLeaveStatusPush($leave, 'approved');

            activity()->performedOn($leave)->causedBy($user)->log('Leave application approved by HR (mobile API)');

            return response()->json([
                'success' => true,
                'message' => 'Leave application fully approved.',
                'data' => ['id' => $leave->id, 'status' => $leave->status],
            ]);
        }

        if (! $leave->isForwardedToManager() && ! ($user->role === 'admin' || $user->role === 'super_admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Only forwarded leave applications can be approved by managers.',
            ], 422);
        }

        if (! $leave->canApproveAsManager($user)) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to approve this leave application.',
            ], 403);
        }

        if ($user->role === 'admin' || $user->role === 'super_admin') {
            $leave->update([
                'status' => 'approved',
                'hr_approved_by' => $user->id,
                'hr_approved_at' => now(),
            ]);

            try {
                $notificationService = new LeaveNotificationService();
                $notificationService->sendLeaveStatusUpdatedNotifications($leave, 'approved', null, $user->name);
            } catch (\Throwable $e) {
                Log::error('Mobile admin approve direct: notification failed', ['id' => $leave->id, 'error' => $e->getMessage()]);
            }

            $this->sendLeaveStatusPush($leave, 'approved');

            activity()->performedOn($leave)->causedBy($user)->log('Leave application approved by HR directly (mobile API)');

            return response()->json([
                'success' => true,
                'message' => 'Leave application approved successfully.',
                'data' => ['id' => $leave->id, 'status' => $leave->status],
            ]);
        }

        $leave->update([
            'status' => 'approved_by_manager',
            'manager_approved_by' => $user->id,
            'manager_approved_at' => now(),
        ]);

        try {
            $notificationService = new LeaveNotificationService();
            $notificationService->sendLeaveStatusUpdatedNotifications($leave, 'approved_by_manager', null, $user->name);
        } catch (\Throwable $e) {
            Log::error('Mobile manager approve: notification failed', ['id' => $leave->id, 'error' => $e->getMessage()]);
        }

        $this->sendLeaveStatusPush($leave, 'approved_by_manager');

        activity()->performedOn($leave)->causedBy($user)->log('Leave application approved by manager (mobile API)');

        return response()->json([
            'success' => true,
            'message' => 'Leave approved by manager. HR approval may still be pending.',
            'data' => ['id' => $leave->id, 'status' => $leave->status],
        ]);
    }

    public function reject(Request $request, LeaveApplication $leave): JsonResponse
    {
        $user = Auth::guard('api')->user();
        $this->authorizeForUser($user, 'reject', $leave);

        $request->validate([
            'rejection_reason' => 'required|string|min:10|max:500',
        ]);

        if (! $leave->isPending() && ! $leave->isForwardedToManager() && ! $leave->isManagerApproved()) {
            return response()->json([
                'success' => false,
                'message' => 'Only pending, forwarded, or manager-approved leave applications can be rejected.',
            ], 422);
        }

        if (($leave->isPending() || $leave->isForwardedToManager()) && ! $leave->canApproveAsManager($user)) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to reject this leave application.',
            ], 403);
        }

        if ($leave->isManagerApproved() && ! $leave->canApproveAsHR($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Only HR can reject a manager-approved leave application.',
            ], 403);
        }

        $leave->update([
            'status' => 'rejected',
            'rejection_reason' => $request->rejection_reason,
            'rejected_by' => $user->id,
            'rejected_at' => now(),
        ]);

        try {
            $notificationService = new LeaveNotificationService();
            $notificationService->sendLeaveStatusUpdatedNotifications($leave, 'rejected', $request->rejection_reason, $user->name);
        } catch (\Throwable $e) {
            Log::error('Mobile reject: notification failed', ['id' => $leave->id, 'error' => $e->getMessage()]);
        }

        $this->sendLeaveStatusPush($leave, 'rejected', $request->rejection_reason);

        activity()
            ->performedOn($leave)
            ->causedBy($user)
            ->withProperties(['rejection_reason' => $request->rejection_reason])
            ->log('Leave application rejected (mobile API)');

        return response()->json([
            'success' => true,
            'message' => 'Leave application rejected.',
            'data' => ['id' => $leave->id, 'status' => $leave->status],
        ]);
    }

    public function forward(Request $request, LeaveApplication $leave): JsonResponse
    {
        $user = Auth::guard('api')->user();

        if (! $leave->canForward($user)) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to forward this leave application or it cannot be forwarded.',
            ], 403);
        }

        $request->validate([
            'forwarding_note' => 'nullable|string|max:500',
        ]);

        $employee = Employee::where('email', $leave->user->email)->first();
        $reportingManager = null;
        if ($employee && $employee->reporting_manager_payroll_id) {
            $reportingManager = Employee::where('payroll_id', $employee->reporting_manager_payroll_id)->first();
        }

        if (! $reportingManager) {
            return response()->json([
                'success' => false,
                'message' => 'Reporting manager not found. Cannot forward leave application.',
            ], 422);
        }

        $leave->update([
            'status' => 'forwarded_to_manager',
            'forwarded_by' => $user->id,
            'forwarded_at' => now(),
            'forwarding_note' => $request->forwarding_note,
        ]);

        try {
            $notificationService = new LeaveNotificationService();
            $notificationService->sendLeaveForwardedNotification($leave, $request->forwarding_note);
        } catch (\Throwable $e) {
            Log::error('Mobile forward: notification failed', ['id' => $leave->id, 'error' => $e->getMessage()]);
        }

        activity()
            ->performedOn($leave)
            ->causedBy($user)
            ->withProperties([
                'forwarded_to_manager' => $reportingManager->name,
                'forwarding_note' => $request->forwarding_note,
            ])
            ->log('Leave application forwarded (mobile API)');

        return response()->json([
            'success' => true,
            'message' => 'Leave application forwarded to reporting manager.',
            'data' => ['id' => $leave->id, 'status' => $leave->status],
        ]);
    }

    public function employeeLeaveBalances(Request $request, User $employee): JsonResponse
    {
        $actor = Auth::guard('api')->user();
        if (! ($actor->isAdmin() || $actor->isSuperAdmin() || $actor->role === 'hr')) {
            $mgr = Employee::where('email', $actor->email)->first();
            $reporteeEmails = $mgr
                ? Employee::where('reporting_manager_payroll_id', $mgr->payroll_id)->pluck('email')->toArray()
                : [];
            if (! in_array($employee->email, $reporteeEmails, true)) {
                return response()->json(['success' => false, 'message' => 'Forbidden.'], 403);
            }
        }

        $fy = MobileLeaveApplicationSupport::resolveOperationalFinancialYearName(
            $request->query('financial_year')
        );
        $payroll = new \App\Services\PayrollLeaveService();
        $result = $payroll->getEmployeeLeaveBalance($employee, $fy);

        $types = collect($result['leave_types'] ?? [])->map(function ($row) {
            $allocated = $row->effective_days ?? $row->days_count ?? 0;
            $used = $row->used ?? 0;
            $balance = $row->balance ?? max(0, $allocated - $used);

            return [
                'leave_type_id' => $row->id,
                'name' => $row->name,
                'code' => $row->code ?? null,
                'allocated' => $allocated,
                'used' => $used,
                'balance' => $balance,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => [
                'employee' => [
                    'id' => $employee->id,
                    'name' => $employee->name,
                    'email' => $employee->email,
                    'employee_id' => $employee->employee_id,
                ],
                'financial_year' => $fy,
                'leave_types' => $types,
            ],
        ]);
    }

    private function sendLeaveStatusPush(LeaveApplication $leave, string $status, ?string $rejectionReason = null): void
    {
        try {
            app(LeavePushNotificationService::class)->notifyEmployeeLeaveStatusUpdated($leave, $status, $rejectionReason);
        } catch (\Throwable $e) {
            Log::error('Mobile leave status push failed', [
                'leave_id' => $leave->id,
                'status' => $status,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function userCanAccessAdminLeaveQueue(User $user): bool
    {
        if ($user->isAdmin() || $user->isSuperAdmin() || $user->role === 'hr') {
            return true;
        }
        $managerEmployee = Employee::where('email', $user->email)->first();
        if (! $managerEmployee) {
            return false;
        }

        return Employee::where('reporting_manager_payroll_id', $managerEmployee->payroll_id)->exists();
    }

    private function formatAdminLeaveDetail(LeaveApplication $leave): array
    {
        $base = [
            'id' => $leave->id,
            'status' => $leave->status,
            'start_date' => $leave->start_date?->format('Y-m-d'),
            'end_date' => $leave->end_date?->format('Y-m-d'),
            'total_days' => (float) $leave->total_days,
            'paid_days' => $leave->paid_days !== null ? (float) $leave->paid_days : null,
            'lop_days' => $leave->lop_days !== null ? (float) $leave->lop_days : null,
            'financial_year' => $leave->financial_year,
            'reason' => $leave->reason,
            'rejection_reason' => $leave->rejection_reason,
            'forwarding_note' => $leave->forwarding_note,
            'created_at' => $leave->created_at?->toISOString(),
            'employee' => $leave->user ? [
                'id' => $leave->user->id,
                'name' => $leave->user->name,
                'email' => $leave->user->email,
                'employee_id' => $leave->user->employee_id,
            ] : null,
            'leave_type' => $leave->leaveType ? [
                'id' => $leave->leaveType->id,
                'name' => $leave->leaveType->name,
                'code' => $leave->leaveType->code,
            ] : null,
        ];

        if ($leave->relationLoaded('leaveDays') && $leave->leaveDays->isNotEmpty()) {
            $base['leave_days'] = $leave->leaveDays->sortBy('leave_date')->values()->map(function ($day) {
                return [
                    'leave_date' => $day->leave_date?->format('Y-m-d'),
                    'day_type' => $day->day_type,
                    'days_count' => (float) $day->days_count,
                    'is_public_holiday' => (bool) $day->is_public_holiday,
                    'is_week_off' => (bool) $day->is_week_off,
                    'exclude_from_calculation' => (bool) $day->exclude_from_calculation,
                    'notes' => $day->notes,
                ];
            });
        }

        return $base;
    }
}
