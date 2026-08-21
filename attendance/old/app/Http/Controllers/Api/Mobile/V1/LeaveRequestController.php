<?php

namespace App\Http\Controllers\Api\Mobile\V1;

use App\Http\Controllers\Controller;
use App\Models\FinancialYear;
use App\Models\LeaveApplication;
use App\Models\LeaveApplicationDay;
use App\Models\LeaveType;
use App\Services\LeaveCalculationService;
use App\Services\LeaveNotificationService;
use App\Services\LeavePushNotificationService;
use App\Services\PayrollLeaveService;
use App\Support\MobileLeaveApplicationSupport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LeaveRequestController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = Auth::guard('api')->user();
        $fy = MobileLeaveApplicationSupport::resolveOperationalFinancialYearName(
            $request->query('financial_year')
        );

        $query = $user->leaveApplications()
            ->with(['leaveType', 'managerApprovedBy:id,name', 'hrApprovedBy:id,name', 'rejectedBy:id,name', 'forwardedBy:id,name'])
            ->where('financial_year', $fy);

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        $perPage = min(max((int) $request->query('per_page', 20), 1), 100);
        $paginator = $query->latest()->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => [
                'items' => collect($paginator->items())->map(fn (LeaveApplication $l) => $this->formatLeaveSummary($l))->values(),
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
        $this->authorizeForUser($user, 'view', $leave);

        $leave->load([
            'leaveType',
            'leaveDays',
            'managerApprovedBy:id,name',
            'hrApprovedBy:id,name',
            'rejectedBy:id,name',
            'forwardedBy:id,name',
        ]);

        return response()->json([
            'success' => true,
            'data' => $this->formatLeaveDetail($leave),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = Auth::guard('api')->user();
        $this->authorizeForUser($user, 'create', LeaveApplication::class);

        $employee = MobileLeaveApplicationSupport::employeeForUser($user);
        if (! $employee) {
            return response()->json([
                'success' => false,
                'message' => 'Only employees with a linked staff profile can apply for leave.',
            ], 403);
        }

        $request->validate([
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|min:10|max:1000',
            'custom_half_days' => 'nullable',
            'emergency_contact_name' => 'nullable|string|max:100',
            'emergency_contact_phone' => 'nullable|string|max:15',
            'lop_acknowledged' => 'sometimes|boolean',
        ]);

        $requestedFy = FinancialYear::getByDate($request->start_date);
        if ($requestedFy && $requestedFy->is_closed) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot apply for leave in a closed financial year: '.$requestedFy->name,
            ], 422);
        }

        $leaveType = LeaveType::findOrFail($request->leave_type_id);

        if ($user->payroll_id) {
            $emp = \App\Models\Employee::where('payroll_id', $user->payroll_id)->first();
            if ($emp && ! $emp->hasAccessToLeaveType($leaveType->id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'The selected leave type is not available for your department.',
                ], 422);
            }
        }

        $overlap = MobileLeaveApplicationSupport::checkLeaveOverlap($user->id, $request->start_date, $request->end_date);
        if ($overlap['hasOverlap']) {
            return response()->json([
                'success' => false,
                'message' => $overlap['message'],
            ], 422);
        }

        $customHalfDays = $this->normalizeCustomHalfDays($request->input('custom_half_days'));

        $leaveService = new LeaveCalculationService();
        $calculation = $leaveService->calculateDetailedLeaveDaysWithLOP(
            $request->start_date,
            $request->end_date,
            $customHalfDays,
            $leaveType->id,
            $user
        );

        if (($calculation['has_lop'] ?? false) && ! $request->boolean('lop_acknowledged')) {
            return response()->json([
                'success' => false,
                'message' => 'This request includes Loss of Pay (LOP) days. Set lop_acknowledged to true to confirm.',
                'data' => [
                    'total_days' => $calculation['total_days'],
                    'paid_days' => $calculation['paid_days'] ?? null,
                    'lop_days' => $calculation['lop_days'] ?? null,
                    'available_balance' => $calculation['available_balance'] ?? null,
                    'leave_type_name' => $leaveType->name,
                ],
            ], 422);
        }

        $leaveFy = FinancialYear::getByDate($request->start_date);
        $leaveFyName = $leaveFy ? $leaveFy->name : MobileLeaveApplicationSupport::currentFinancialYearName();

        $leaveApplication = LeaveApplication::create([
            'user_id' => $user->id,
            'email_id' => $user->email,
            'leave_type_id' => $leaveType->id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'total_days' => $calculation['total_days'],
            'paid_days' => $calculation['paid_days'] ?? $calculation['total_days'],
            'lop_days' => $calculation['lop_days'] ?? 0,
            'has_lop' => $calculation['has_lop'] ?? false,
            'lop_acknowledged' => $request->boolean('lop_acknowledged'),
            'reason' => $request->reason,
            'status' => 'pending',
            'financial_year' => $leaveFyName,
            'emergency_contact_name' => $request->emergency_contact_name,
            'emergency_contact_phone' => $request->emergency_contact_phone,
        ]);

        foreach ($calculation['leave_days'] as $dayData) {
            LeaveApplicationDay::create([
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

        activity()
            ->performedOn($leaveApplication)
            ->causedBy($user)
            ->withProperties([
                'leave_type' => $leaveType->name,
                'total_days' => $calculation['total_days'],
                'breakdown' => $calculation['breakdown'] ?? null,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
            ])
            ->log('Leave application submitted (mobile API)');

        try {
            $notificationService = new LeaveNotificationService();
            $notificationService->sendLeaveSubmittedNotifications($leaveApplication);
        } catch (\Throwable $e) {
            Log::error('Mobile leave submit: email notification failed', ['leave_id' => $leaveApplication->id, 'error' => $e->getMessage()]);
        }

        try {
            app(LeavePushNotificationService::class)->notifyAdminsLeaveSubmitted($leaveApplication);
        } catch (\Throwable $e) {
            Log::error('Mobile leave submit: push notification failed', ['leave_id' => $leaveApplication->id, 'error' => $e->getMessage()]);
        }

        $leaveApplication->load(['leaveType', 'leaveDays']);

        return response()->json([
            'success' => true,
            'message' => 'Leave application submitted successfully.',
            'data' => $this->formatLeaveDetail($leaveApplication),
        ], 201);
    }

    public function update(Request $request, LeaveApplication $leave): JsonResponse
    {
        $user = Auth::guard('api')->user();
        $this->authorizeForUser($user, 'update', $leave);

        if ($leave->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Only pending leave applications can be updated.',
            ], 422);
        }

        $request->validate([
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|min:10|max:500',
            'custom_half_days' => 'nullable',
            'emergency_contact_name' => 'nullable|string|max:100',
            'emergency_contact_phone' => 'nullable|string|max:15',
            'lop_acknowledged' => 'sometimes|boolean',
        ]);

        $leaveType = LeaveType::findOrFail($request->leave_type_id);

        if ($user->payroll_id) {
            $emp = \App\Models\Employee::where('payroll_id', $user->payroll_id)->first();
            if ($emp && ! $emp->hasAccessToLeaveType($leaveType->id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'The selected leave type is not available for your department.',
                ], 422);
            }
        }

        $overlap = MobileLeaveApplicationSupport::checkLeaveOverlap($user->id, $request->start_date, $request->end_date, $leave->id);
        if ($overlap['hasOverlap']) {
            return response()->json([
                'success' => false,
                'message' => $overlap['message'],
            ], 422);
        }

        $customHalfDays = $this->normalizeCustomHalfDays($request->input('custom_half_days'));

        $leaveService = new LeaveCalculationService();
        $calculation = $leaveService->calculateDetailedLeaveDaysWithLOP(
            $request->start_date,
            $request->end_date,
            $customHalfDays,
            $leaveType->id,
            $user
        );

        if (($calculation['has_lop'] ?? false) && ! $request->boolean('lop_acknowledged')) {
            return response()->json([
                'success' => false,
                'message' => 'This request includes Loss of Pay (LOP) days. Set lop_acknowledged to true to confirm.',
                'data' => [
                    'total_days' => $calculation['total_days'],
                    'paid_days' => $calculation['paid_days'] ?? null,
                    'lop_days' => $calculation['lop_days'] ?? null,
                    'available_balance' => $calculation['available_balance'] ?? null,
                    'leave_type_name' => $leaveType->name,
                ],
            ], 422);
        }

        $currentFinancialYear = MobileLeaveApplicationSupport::currentFinancialYearName();
        $requestedFy = FinancialYear::getByDate($request->start_date);
        $fyName = $requestedFy ? $requestedFy->name : $currentFinancialYear;

        $payrollService = new PayrollLeaveService();
        $balanceInfo = $payrollService->getLeaveTypeBalance($leaveType->id, $user, $fyName);
        $availableBalance = $balanceInfo['balance'];
        if ($leaveType->id == $leave->leave_type_id) {
            $availableBalance += $leave->total_days;
        }

        if ($calculation['total_days'] > $availableBalance) {
            return response()->json([
                'success' => false,
                'message' => "Insufficient leave balance. You have {$availableBalance} days available for {$leaveType->name}.",
            ], 422);
        }

        $totalDays = $calculation['total_days'];

        $leave->update([
            'leave_type_id' => $leaveType->id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'total_days' => $totalDays,
            'paid_days' => $calculation['paid_days'] ?? $totalDays,
            'lop_days' => $calculation['lop_days'] ?? 0,
            'has_lop' => $calculation['has_lop'] ?? false,
            'lop_acknowledged' => $request->boolean('lop_acknowledged'),
            'reason' => $request->reason,
            'emergency_contact_name' => $request->emergency_contact_name,
            'emergency_contact_phone' => $request->emergency_contact_phone,
        ]);

        $leave->leaveDays()->delete();
        foreach ($calculation['leave_days'] as $dayData) {
            LeaveApplicationDay::create([
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
            ->log('Leave application updated (mobile API)');

        $leave->load(['leaveType', 'leaveDays']);

        return response()->json([
            'success' => true,
            'message' => 'Leave application updated successfully.',
            'data' => $this->formatLeaveDetail($leave),
        ]);
    }

    public function withdraw(Request $request, LeaveApplication $leave): JsonResponse
    {
        $user = Auth::guard('api')->user();
        $this->authorizeForUser($user, 'delete', $leave);

        if ($leave->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Only pending leave applications can be withdrawn.',
            ], 422);
        }

        $leave->update(['status' => 'cancelled']);

        activity()
            ->performedOn($leave)
            ->causedBy($user)
            ->log('Leave application withdrawn (mobile API)');

        try {
            app(LeavePushNotificationService::class)->notifyAdminsLeaveCancelled($leave);
        } catch (\Throwable $e) {
            Log::error('Mobile leave withdraw: push notification failed', ['leave_id' => $leave->id, 'error' => $e->getMessage()]);
        }

        $leave->load('leaveType');

        return response()->json([
            'success' => true,
            'message' => 'Leave application withdrawn successfully.',
            'data' => $this->formatLeaveSummary($leave),
        ]);
    }

    public function calculate(Request $request): JsonResponse
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'half_day_dates' => 'sometimes|array',
            'leave_type_id' => 'sometimes|integer|exists:leave_types,id',
            'exclude_leave_id' => 'sometimes|integer|exists:leave_applications,id',
        ]);

        $user = Auth::guard('api')->user();

        $overlap = MobileLeaveApplicationSupport::checkLeaveOverlap(
            $user->id,
            $request->start_date,
            $request->end_date,
            $request->integer('exclude_leave_id') ?: null
        );

        if ($overlap['hasOverlap']) {
            return response()->json([
                'success' => false,
                'message' => $overlap['message'],
                'data' => ['overlapping_leave_id' => $overlap['overlappingLeave']->id ?? null],
            ], 422);
        }

        $leaveService = new LeaveCalculationService();
        if ($request->filled('leave_type_id')) {
            $result = $leaveService->calculateDetailedLeaveDaysWithLOP(
                $request->start_date,
                $request->end_date,
                $request->input('half_day_dates', []),
                (int) $request->leave_type_id,
                $user
            );
        } else {
            $result = $leaveService->calculateDetailedLeaveDays(
                $request->start_date,
                $request->end_date,
                $request->input('half_day_dates', []),
                $user
            );
        }

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    private function normalizeCustomHalfDays(mixed $raw): array
    {
        if ($raw === null || $raw === '') {
            return [];
        }
        if (is_array($raw)) {
            return $raw;
        }
        if (is_string($raw)) {
            return json_decode($raw, true) ?? [];
        }

        return [];
    }

    private function formatLeaveSummary(LeaveApplication $leave): array
    {
        return [
            'id' => $leave->id,
            'status' => $leave->status,
            'start_date' => $leave->start_date?->format('Y-m-d'),
            'end_date' => $leave->end_date?->format('Y-m-d'),
            'total_days' => (float) $leave->total_days,
            'paid_days' => $leave->paid_days !== null ? (float) $leave->paid_days : null,
            'lop_days' => $leave->lop_days !== null ? (float) $leave->lop_days : null,
            'has_lop' => (bool) $leave->has_lop,
            'financial_year' => $leave->financial_year,
            'reason' => $leave->reason,
            'rejection_reason' => $leave->rejection_reason,
            'created_at' => $leave->created_at?->toISOString(),
            'leave_type' => $leave->leaveType ? [
                'id' => $leave->leaveType->id,
                'name' => $leave->leaveType->name,
                'code' => $leave->leaveType->code,
            ] : null,
        ];
    }

    private function formatLeaveDetail(LeaveApplication $leave): array
    {
        $base = $this->formatLeaveSummary($leave);
        $base['emergency_contact_name'] = $leave->emergency_contact_name;
        $base['emergency_contact_phone'] = $leave->emergency_contact_phone;
        $base['forwarding_note'] = $leave->forwarding_note;
        $base['manager_approved_at'] = $leave->manager_approved_at?->toISOString();
        $base['hr_approved_at'] = $leave->hr_approved_at?->toISOString();
        $base['rejected_at'] = $leave->rejected_at?->toISOString();
        $base['forwarded_at'] = $leave->forwarded_at?->toISOString();
        $base['manager_approved_by'] = $leave->managerApprovedBy ? ['id' => $leave->managerApprovedBy->id, 'name' => $leave->managerApprovedBy->name] : null;
        $base['hr_approved_by'] = $leave->hrApprovedBy ? ['id' => $leave->hrApprovedBy->id, 'name' => $leave->hrApprovedBy->name] : null;
        $base['rejected_by'] = $leave->rejectedBy ? ['id' => $leave->rejectedBy->id, 'name' => $leave->rejectedBy->name] : null;
        $base['forwarded_by'] = $leave->forwardedBy ? ['id' => $leave->forwardedBy->id, 'name' => $leave->forwardedBy->name] : null;

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
