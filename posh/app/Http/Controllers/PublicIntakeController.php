<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\PoshComplaint;
use App\Services\PoshCaseService;
use App\Services\PoshSlaService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PublicIntakeController extends Controller
{
    public function show(string $orgKey)
    {
        $org = Organization::where('intake_key', $orgKey)->where('is_active', true)->firstOrFail();

        return view('intake.public', compact('org'));
    }

    public function store(Request $request, string $orgKey, PoshCaseService $cases, PoshSlaService $sla)
    {
        $org = Organization::where('intake_key', $orgKey)->where('is_active', true)->firstOrFail();
        $validated = $request->validate([
            'complainant_name' => 'nullable|string|max:255',
            'employee_code' => 'nullable|string|max:64',
            'department' => 'nullable|string|max:128',
            'is_anonymous' => 'boolean',
            'respondent_name' => 'required|string|max:255',
            'respondent_type' => 'required|string',
            'incident_date' => 'required|date',
            'description' => 'required|string|max:10000',
        ]);

        $incident = Carbon::parse($validated['incident_date']);
        $deadline = $sla->checkFilingDeadline($incident, (bool) $request->input('extension_approved'));

        $complaint = PoshComplaint::create([
            'organization_id' => $org->id,
            'case_number' => $cases->generateCaseNumber($org->id),
            'status' => 'Submitted',
            'complainant_name' => $validated['complainant_name'] ?? 'Anonymous (QR)',
            'employee_code' => $validated['employee_code'] ?? null,
            'department' => $validated['department'],
            'is_anonymous' => $request->boolean('is_anonymous', true),
            'respondent_name' => $validated['respondent_name'],
            'respondent_type' => $validated['respondent_type'],
            'incident_date' => $incident,
            'description' => $validated['description'],
            'filing_within_deadline' => $deadline['within'],
            'intake_channel' => 'qr_public',
            'submitted_at' => now(),
        ]);

        $cases->audit($org->id, null, 'QR intake complaint', $complaint->case_number, 'Public intake', $request);

        return view('intake.thankyou', ['complaint' => $complaint]);
    }
}
