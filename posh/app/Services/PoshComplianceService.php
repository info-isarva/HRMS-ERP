<?php

namespace App\Services;

use App\Models\Organization;
use App\Models\PoshAnnualReport;
use App\Models\PoshComplaint;
use App\Models\PoshEmployerDuty;
use App\Models\PoshPolicyAcknowledgement;
use App\Models\PoshPreventionEvent;
use Carbon\Carbon;

class PoshComplianceService
{
    public function seedDutiesForOrganization(int $organizationId): void
    {
        foreach (config('posh.employer_duties') as $key => $text) {
            PoshEmployerDuty::firstOrCreate(
                ['organization_id' => $organizationId, 'duty_key' => $key],
                ['duty_text' => $text]
            );
        }
    }

    public function dutiesCompletionPercent(int $organizationId): int
    {
        $total = PoshEmployerDuty::where('organization_id', $organizationId)->count();
        if ($total === 0) {
            return 0;
        }
        $done = PoshEmployerDuty::where('organization_id', $organizationId)->where('is_done', true)->count();

        return (int) round(($done / $total) * 100);
    }

    public function buildAnnualReportData(int $organizationId, int $year): array
    {
        $start = Carbon::create($year, 1, 1)->startOfDay();
        $end = Carbon::create($year, 12, 31)->endOfDay();
        $org = Organization::find($organizationId);

        $cases = PoshComplaint::where('organization_id', $organizationId)
            ->whereBetween('created_at', [$start, $end])
            ->get();

        $preventionEvents = PoshPreventionEvent::where('organization_id', $organizationId)
            ->whereBetween('held_on', [$start->toDateString(), $end->toDateString()])
            ->orderBy('held_on')
            ->get()
            ->map(fn (PoshPreventionEvent $e) => [
                'event_type' => $e->event_type,
                'event_type_label' => $this->preventionEventTypeLabel($e->event_type),
                'title' => $e->title,
                'held_on' => $e->held_on?->format('Y-m-d'),
                'held_on_display' => $e->held_on?->format('d M Y') ?? '—',
                'attendee_count' => $e->attendee_count,
                'notes' => $e->notes,
            ])
            ->values()
            ->all();

        $employerDuties = PoshEmployerDuty::where('organization_id', $organizationId)
            ->orderBy('id')
            ->get()
            ->map(fn (PoshEmployerDuty $d) => [
                'duty_key' => $d->duty_key,
                'duty_text' => $d->duty_text,
                'is_done' => (bool) $d->is_done,
                'done_on' => $d->done_on?->format('Y-m-d'),
                'done_on_display' => $d->done_on?->format('d M Y') ?? '—',
                'notes' => $d->notes,
            ])
            ->values()
            ->all();

        $dutiesDone = collect($employerDuties)->where('is_done', true)->count();
        $dutiesTotal = count($employerDuties);

        return [
            'year' => $year,
            'organization' => Organization::sanitizeDisplayName($org?->name),
            'employee_count' => $org?->employee_count,
            'cases_filed' => $cases->count(),
            'cases_closed' => $cases->whereIn('status', ['Closed', 'Archived'])->count(),
            'cases_pending' => $cases->whereNotIn('status', config('posh.closed_statuses'))->count(),
            'workshops' => collect($preventionEvents)->where('event_type', 'workshop')->count(),
            'prevention_events' => $preventionEvents,
            'prevention_events_count' => count($preventionEvents),
            'employer_duties' => $employerDuties,
            'employer_duties_done' => $dutiesDone,
            'employer_duties_total' => $dutiesTotal,
            'employer_duties_percent' => $dutiesTotal > 0 ? (int) round(($dutiesDone / $dutiesTotal) * 100) : 0,
            'policy_acknowledgements' => PoshPolicyAcknowledgement::whereHas(
                'policy',
                fn ($q) => $q->where('organization_id', $organizationId)
            )->whereBetween('acknowledged_at', [$start, $end])->count(),
            'nature_of_outcomes' => $cases->groupBy('status')->map->count()->toArray(),
            'generated_at' => now()->toIso8601String(),
        ];
    }

    protected function preventionEventTypeLabel(string $type): string
    {
        return match ($type) {
            'workshop' => 'Employee workshop',
            'ic_orientation' => 'IC orientation',
            'display' => 'Display / posters',
            default => 'Other',
        };
    }

    public function saveAnnualReport(int $organizationId, int $year, int $userId): PoshAnnualReport
    {
        $data = $this->buildAnnualReportData($organizationId, $year);

        return PoshAnnualReport::updateOrCreate(
            ['organization_id' => $organizationId, 'report_year' => $year],
            [
                'report_data' => $data,
                'generated_at' => now(),
                'generated_by' => $userId,
            ]
        );
    }
}
