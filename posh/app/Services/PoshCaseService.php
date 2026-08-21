<?php

namespace App\Services;

use App\Models\PoshAuditLog;
use App\Models\PoshComplaint;
use App\Models\PoshComplaintLog;
use App\Models\User;
use Illuminate\Http\Request;
use App\Services\PoshSlaService;

class PoshCaseService
{
    public function generateCaseNumber(int $organizationId): string
    {
        $year = now()->format('Y');
        $prefix = 'POSH-' . $year . '-';

        $last = PoshComplaint::where('organization_id', $organizationId)
            ->where('case_number', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->value('case_number');

        $seq = 1;
        if ($last && preg_match('/-(\d+)$/', $last, $m)) {
            $seq = (int) $m[1] + 1;
        }

        return $prefix . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }

    public function logTimeline(PoshComplaint $complaint, User $user, string $actionType, ?string $notes = null, ?string $oldStatus = null, ?string $newStatus = null): void
    {
        PoshComplaintLog::create([
            'posh_complaint_id' => $complaint->id,
            'user_id' => $user->id,
            'action_type' => $actionType,
            'old_status' => $oldStatus,
            'new_status' => $newStatus ?? $complaint->status,
            'notes' => $notes,
        ]);
    }

    public function audit(?int $organizationId, ?User $user, string $action, ?string $caseNumber = null, ?string $details = null, ?Request $request = null): void
    {
        PoshAuditLog::create([
            'organization_id' => $organizationId,
            'user_id' => $user?->id,
            'action' => $action,
            'case_number' => $caseNumber,
            'details' => $details,
            'ip_address' => $request?->ip(),
        ]);
    }

    public function transitionStatus(PoshComplaint $complaint, User $user, string $newStatus, ?string $notes = null): void
    {
        $old = $complaint->status;
        $complaint->status = $newStatus;

        if ($newStatus === 'Acknowledged' && ! $complaint->acknowledged_at) {
            $complaint->acknowledged_at = now();
        }
        if (in_array($newStatus, config('posh.closed_statuses'), true)) {
            $complaint->closed_at = now();
        }
        if ($newStatus === 'Inquiry Started' && ! $complaint->inquiry_started_at) {
            $complaint->inquiry_started_at = now();
        }

        $complaint->save();

        if ($newStatus === 'Inquiry Started') {
            app(PoshSlaService::class)->setInquirySlas($complaint->fresh());
        }

        $this->logTimeline($complaint, $user, 'status_change', $notes, $old, $newStatus);
        $this->audit($complaint->organization_id, $user, 'Status changed', $complaint->case_number, "{$old} → {$newStatus}", request());
    }
}
