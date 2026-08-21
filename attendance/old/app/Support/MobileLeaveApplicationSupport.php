<?php

namespace App\Support;

use App\Models\Employee;
use App\Models\FinancialYear;
use App\Models\LeaveApplication;
use App\Models\User;
use Carbon\Carbon;

class MobileLeaveApplicationSupport
{
    public static function currentFinancialYear(): ?FinancialYear
    {
        return FinancialYear::where('is_active', true)->first()
            ?? FinancialYear::orderByDesc('start_date')->first();
    }

    public static function currentFinancialYearName(): string
    {
        $fy = self::currentFinancialYear();

        return $fy ? $fy->name : '2026-2027';
    }

    /**
     * Mobile should use the system "current" FY for operations.
     * Stale users.financial_year (e.g. 2025-2026) must not drive holidays/balances.
     * Only an explicit request for the actual current FY name is honoured as-is.
     */
    public static function resolveOperationalFinancialYearName(?string $requested = null): string
    {
        $currentName = self::currentFinancialYearName();

        if ($requested === null || $requested === '' || $requested === $currentName) {
            return $currentName;
        }

        return $currentName;
    }

    public static function employeeForUser(?User $user): ?Employee
    {
        if (! $user) {
            return null;
        }
        if ($user->payroll_id) {
            $employee = Employee::where('payroll_id', $user->payroll_id)->first();
            if ($employee) {
                return $employee;
            }
        }

        return Employee::where('email', $user->email)->first();
    }

    /**
     * @return array{hasOverlap: bool, message?: string, overlappingLeave?: LeaveApplication}
     */
    public static function checkLeaveOverlap(int $userId, $startDate, $endDate, ?int $excludeLeaveId = null): array
    {
        $query = LeaveApplication::where('user_id', $userId)
            ->whereIn('status', ['pending', 'approved_by_manager', 'approved'])
            ->where(function ($q) use ($startDate, $endDate) {
                $q->where(function ($dateQuery) use ($startDate, $endDate) {
                    $dateQuery->where('start_date', '<=', $endDate)
                        ->where('end_date', '>=', $startDate);
                });
            });

        if ($excludeLeaveId) {
            $query->where('id', '!=', $excludeLeaveId);
        }

        $overlappingLeaves = $query->get();

        if ($overlappingLeaves->count() > 0) {
            $overlappingLeave = $overlappingLeaves->first();
            $overlapStart = Carbon::parse($overlappingLeave->start_date)->format('M d, Y');
            $overlapEnd = Carbon::parse($overlappingLeave->end_date)->format('M d, Y');
            $message = "Leave application overlaps with an existing leave application from {$overlapStart} to {$overlapEnd} (Status: "
                .ucfirst(str_replace('_', ' ', $overlappingLeave->status)).'). ';
            $message .= 'Please choose different dates or cancel the existing application first.';

            return [
                'hasOverlap' => true,
                'message' => $message,
                'overlappingLeave' => $overlappingLeave,
            ];
        }

        return ['hasOverlap' => false];
    }
}
