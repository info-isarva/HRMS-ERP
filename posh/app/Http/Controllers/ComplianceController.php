<?php

namespace App\Http\Controllers;

use App\Models\PoshEmployerDuty;
use App\Models\PoshPreventionEvent;
use App\Services\PoshComplianceService;
use Illuminate\Http\Request;

class ComplianceController extends Controller
{
    public function __construct(protected PoshComplianceService $compliance) {}

    public function index(Request $request)
    {
        $orgId = $request->user()->organization_id;
        $this->compliance->seedDutiesForOrganization($orgId);
        $duties = PoshEmployerDuty::where('organization_id', $orgId)->orderBy('duty_key')->get();
        $events = PoshPreventionEvent::where('organization_id', $orgId)->orderByDesc('held_on')->limit(20)->get();
        $percent = $this->compliance->dutiesCompletionPercent($orgId);

        return view('compliance.index', compact('duties', 'events', 'percent'));
    }

    public function updateDuty(Request $request, PoshEmployerDuty $duty)
    {
        abort_unless($duty->organization_id === $request->user()->organization_id, 403);
        $validated = $request->validate([
            'is_done' => 'boolean',
            'done_on' => 'nullable|date',
            'notes' => 'nullable|string|max:2000',
        ]);
        $duty->update([
            'is_done' => $request->boolean('is_done'),
            'done_on' => $validated['done_on'] ?? ($request->boolean('is_done') ? now()->toDateString() : null),
            'notes' => $validated['notes'] ?? $duty->notes,
        ]);

        return back()->with('success', 'Duty updated.');
    }

    public function storeEvent(Request $request)
    {
        $validated = $request->validate([
            'event_type' => 'required|in:workshop,ic_orientation,display,other',
            'title' => 'required|string|max:255',
            'held_on' => 'required|date',
            'attendee_count' => 'nullable|integer|min:0',
            'notes' => 'nullable|string|max:2000',
        ]);
        PoshPreventionEvent::create([
            ...$validated,
            'organization_id' => $request->user()->organization_id,
            'recorded_by' => $request->user()->id,
        ]);

        return back()->with('success', 'Prevention event recorded.');
    }
}
