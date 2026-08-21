@php
    $year = $data['year'] ?? $report->report_year;
    $outcomes = $data['nature_of_outcomes'] ?? [];
    $preventionEvents = $data['prevention_events'] ?? [];
    $employerDuties = $data['employer_duties'] ?? [];
    $dutiesDone = $data['employer_duties_done'] ?? 0;
    $dutiesTotal = $data['employer_duties_total'] ?? count($employerDuties);
    $dutiesPercent = $data['employer_duties_percent'] ?? 0;
@endphp

<style>
    .annual-report-wrap { font-family: Inter, system-ui, sans-serif; color: #1e293b; }
    .annual-stats { display: grid; grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); gap: 0.75rem; margin-bottom: 1.5rem; }
    .annual-stat { border: 1px solid #e2e8f0; border-radius: 0.75rem; padding: 0.85rem 1rem; background: #f8fafc; }
    .annual-stat strong { display: block; font-size: 1.25rem; font-weight: 700; color: #1e3a8a; line-height: 1.2; }
    .annual-stat span { font-size: 0.65rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.04em; color: #64748b; }
    .annual-section { margin-top: 1.5rem; }
    .annual-section h3 { font-size: 0.8rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #1e3a8a; margin: 0 0 0.75rem; padding-bottom: 0.35rem; border-bottom: 2px solid #e0e7ff; }
    .annual-section p.section-intro { font-size: 0.8125rem; color: #64748b; margin: -0.35rem 0 0.75rem; }
    .annual-table { width: 100%; border-collapse: collapse; font-size: 0.8125rem; margin-bottom: 0.5rem; }
    .annual-table th { text-align: left; padding: 0.5rem 0.65rem; background: #f1f5f9; color: #475569; font-size: 0.65rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; border: 1px solid #e2e8f0; }
    .annual-table td { padding: 0.55rem 0.65rem; border: 1px solid #e2e8f0; vertical-align: top; }
    .annual-table tbody tr:nth-child(even) { background: #f8fafc; }
    .annual-badge-yes { display: inline-flex; align-items: center; gap: 0.25rem; rounded: 9999px; padding: 0.15rem 0.5rem; font-size: 0.7rem; font-weight: 600; background: #d1fae5; color: #065f46; border-radius: 9999px; }
    .annual-badge-no { display: inline-flex; align-items: center; gap: 0.25rem; padding: 0.15rem 0.5rem; font-size: 0.7rem; font-weight: 600; background: #fef3c7; color: #92400e; border-radius: 9999px; }
    .annual-empty { padding: 1rem; text-align: center; font-size: 0.8125rem; color: #94a3b8; background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 0.5rem; }
    .annual-outcome-row { display: flex; justify-content: space-between; align-items: center; padding: 0.5rem 0.75rem; border-radius: 0.5rem; background: #fff; border: 1px solid #f1f5f9; margin-bottom: 0.35rem; font-size: 0.8125rem; }
    .annual-outcome-row em { font-style: normal; font-weight: 700; color: #4f46e5; }
    .annual-footer { margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid #e2e8f0; font-size: 0.75rem; color: #64748b; line-height: 1.6; }
    .annual-meta { font-size: 0.8125rem; color: #475569; margin-bottom: 1rem; line-height: 1.5; }
    @media print {
        .annual-table { page-break-inside: auto; }
        .annual-table tr { page-break-inside: avoid; }
        .annual-section { page-break-inside: avoid; }
    }
</style>

<div class="annual-report-wrap">
    <div class="annual-meta">
        <strong>Organization:</strong> {{ \App\Models\Organization::sanitizeDisplayName($data['organization'] ?? '') ?: '—' }}
        @if(!empty($data['employee_count']))
            · <strong>Workforce size:</strong> {{ number_format($data['employee_count']) }} employees
        @endif
        · <strong>Report year:</strong> {{ $year }}
        · <strong>Statutory reference:</strong> POSH Act Section 22, Rule 14
    </div>

    <div class="annual-stats">
        <div class="annual-stat"><span>Cases filed</span><strong>{{ $data['cases_filed'] ?? 0 }}</strong></div>
        <div class="annual-stat"><span>Cases closed</span><strong>{{ $data['cases_closed'] ?? 0 }}</strong></div>
        <div class="annual-stat"><span>Cases pending</span><strong>{{ $data['cases_pending'] ?? 0 }}</strong></div>
        <div class="annual-stat"><span>Prevention events</span><strong>{{ $data['prevention_events_count'] ?? count($preventionEvents) }}</strong></div>
        <div class="annual-stat"><span>Workshops</span><strong>{{ $data['workshops'] ?? 0 }}</strong></div>
        <div class="annual-stat"><span>Policy acks ({{ $year }})</span><strong>{{ $data['policy_acknowledgements'] ?? 0 }}</strong></div>
        <div class="annual-stat"><span>S.19 duties</span><strong>{{ $dutiesDone }}/{{ $dutiesTotal }}</strong></div>
        <div class="annual-stat"><span>S.19 complete</span><strong>{{ $dutiesPercent }}%</strong></div>
    </div>

    {{-- Section 19 duties — full detail --}}
    <div class="annual-section">
        <h3>Section 19 — Employer duties (compliance checklist)</h3>
        <p class="section-intro">Status of each statutory employer duty as recorded in the compliance module. Complainant identities are never included.</p>
        @if(count($employerDuties) > 0)
            <table class="annual-table">
                <thead>
                    <tr>
                        <th style="width:42%">Duty</th>
                        <th style="width:12%">Status</th>
                        <th style="width:14%">Completed on</th>
                        <th style="width:32%">Notes / evidence reference</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($employerDuties as $duty)
                        <tr>
                            <td>{{ $duty['duty_text'] ?? '—' }}</td>
                            <td>
                                @if(!empty($duty['is_done']))
                                    <span class="annual-badge-yes"><i class="fas fa-check"></i> Done</span>
                                @else
                                    <span class="annual-badge-no"><i class="fas fa-clock"></i> Pending</span>
                                @endif
                            </td>
                            <td>{{ $duty['done_on_display'] ?? '—' }}</td>
                            <td>{{ $duty['notes'] ?: '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="annual-empty">No Section 19 duties recorded. Complete the checklist on the Compliance page before generating this report.</p>
        @endif
    </div>

    {{-- Prevention events — full detail --}}
    <div class="annual-section">
        <h3>Prevention &amp; awareness activities — {{ $year }}</h3>
        <p class="section-intro">Workshops, IC orientations, poster displays, and other prevention events held during the report year.</p>
        @if(count($preventionEvents) > 0)
            <table class="annual-table">
                <thead>
                    <tr>
                        <th style="width:14%">Date</th>
                        <th style="width:18%">Type</th>
                        <th style="width:28%">Title / description</th>
                        <th style="width:10%">Attendees</th>
                        <th style="width:30%">Notes</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($preventionEvents as $event)
                        <tr>
                            <td>{{ $event['held_on_display'] ?? '—' }}</td>
                            <td>{{ $event['event_type_label'] ?? ucfirst($event['event_type'] ?? 'Event') }}</td>
                            <td>{{ $event['title'] ?? '—' }}</td>
                            <td>{{ isset($event['attendee_count']) && $event['attendee_count'] !== null ? number_format($event['attendee_count']) : '—' }}</td>
                            <td>{{ $event['notes'] ?: '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="annual-empty">No prevention events recorded for {{ $year }}. Add workshops and activities on the Compliance page.</p>
        @endif
    </div>

    {{-- Case outcomes --}}
    <div class="annual-section">
        <h3>Complaint case statistics — {{ $year }}</h3>
        <p class="section-intro">Aggregate case counts only. No complainant or respondent names are published (Section 16 confidentiality).</p>
        @if(!empty($outcomes))
            @foreach($outcomes as $status => $count)
                <div class="annual-outcome-row">
                    <span>{{ $status }}</span>
                    <em>{{ $count }}</em>
                </div>
            @endforeach
        @else
            <p class="annual-empty">No complaints filed during {{ $year }}.</p>
        @endif
    </div>

    <p class="annual-footer">
        Report year <strong>{{ $year }}</strong>
        · Generated {{ isset($data['generated_at']) ? \Illuminate\Support\Carbon::parse($data['generated_at'])->format('d M Y H:i') : ($report->generated_at?->format('d M Y H:i') ?? '—') }}
        · POSH Act Section 22 annual return — prescribed particulars for the District Officer
        @if($report->submitted_at ?? null)
            · <strong>Submitted to District Officer on {{ $report->submitted_at->format('d M Y') }}</strong>
        @else
            · Draft — not yet marked as submitted
        @endif
    </p>
</div>
