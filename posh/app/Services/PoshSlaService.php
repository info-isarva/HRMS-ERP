<?php

namespace App\Services;

use App\Models\PoshComplaint;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class PoshSlaService
{
    public function checkFilingDeadline(Carbon $incidentDate, bool $hasExtension = false): array
    {
        $months = config('posh.statutory_sla_days.filing_window_months', 3);
        $deadline = $incidentDate->copy()->addMonths($months);
        $maxDeadline = $hasExtension
            ? $deadline->copy()->addMonths(config('posh.statutory_sla_days.filing_extension_months', 3))
            : $deadline;

        return [
            'within' => now()->lte($maxDeadline),
            'deadline' => $deadline,
            'max_deadline' => $maxDeadline,
        ];
    }

    public function setInquirySlas(PoshComplaint $complaint): void
    {
        if (! $complaint->inquiry_started_at) {
            return;
        }
        $start = $complaint->inquiry_started_at;
        $complaint->report_due_at = $start->copy()->addDays(config('posh.statutory_sla_days.inquiry_days', 90));
        $complaint->management_action_due_at = $complaint->report_due_at?->copy()->addDays(
            config('posh.statutory_sla_days.report_after_inquiry_days', 10) + config('posh.statutory_sla_days.management_action_days', 60)
        );
        $complaint->save();
    }

    public function alertsForOrganization(int $organizationId): Collection
    {
        $alerts = collect();
        $open = PoshComplaint::where('organization_id', $organizationId)
            ->whereNotIn('status', config('posh.closed_statuses'))
            ->get();

        foreach ($open as $c) {
            if ($c->report_due_at && now()->gt($c->report_due_at)) {
                $alerts->push(['type' => 'danger', 'case' => $c->case_number, 'msg' => 'Inquiry/report SLA exceeded (90 days)']);
            } elseif ($c->report_due_at && now()->diffInDays($c->report_due_at, false) <= 15) {
                $alerts->push(['type' => 'warn', 'case' => $c->case_number, 'msg' => 'Inquiry SLA due within 15 days']);
            }
            if ($c->management_action_due_at && now()->gt($c->management_action_due_at)) {
                $alerts->push(['type' => 'danger', 'case' => $c->case_number, 'msg' => 'Management action SLA exceeded']);
            }
            if (! $c->filing_within_deadline) {
                $alerts->push(['type' => 'warn', 'case' => $c->case_number, 'msg' => 'Filed outside 3+3 month window']);
            }
        }

        return $alerts;
    }
}
