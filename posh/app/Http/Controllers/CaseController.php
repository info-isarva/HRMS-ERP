<?php

namespace App\Http\Controllers;

use App\Models\PoshComplaint;
use App\Services\PoshCaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CaseController extends Controller
{
    public function __construct(protected PoshCaseService $cases)
    {
    }

    public function index(Request $request)
    {
        $query = PoshComplaint::where('organization_id', Auth::user()->organization_id)
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($builder) use ($q) {
                $builder->where('case_number', 'like', "%{$q}%")
                    ->orWhere('respondent_name', 'like', "%{$q}%")
                    ->orWhere('complainant_name', 'like', "%{$q}%");
            });
        }

        $complaints = $query->paginate(15)->withQueryString();

        return view('cases.index', [
            'complaints' => $complaints,
            'statuses' => config('posh.statuses'),
        ]);
    }

    public function operate(Request $request, PoshComplaint $complaint)
    {
        $this->authorizeOrg($complaint);
        $complaint->load(['logs.user', 'evidence']);

        $stepIndex = (int) $request->get('step', $complaint->operate_step);
        $steps = config('posh.operate_steps');
        $stepIndex = max(0, min($stepIndex, count($steps) - 1));

        return view('cases.operate', [
            'complaint' => $complaint,
            'steps' => $steps,
            'stepIndex' => $stepIndex,
            'statuses' => config('posh.statuses'),
        ]);
    }

    public function saveStep(Request $request, PoshComplaint $complaint)
    {
        $this->authorizeOrg($complaint);
        $user = Auth::user();
        $steps = config('posh.operate_steps');

        $stepIndex = (int) $request->input('operate_step', $complaint->operate_step);
        $stepIndex = max(0, min($stepIndex, count($steps) - 1));
        $step = $steps[$stepIndex];

        $data = $request->validate([
            'operate_step' => 'required|integer|min:0|max:8',
            'review_outcome' => 'nullable|string|max:64',
            'review_notes' => 'nullable|string',
            'conciliation_requested' => 'nullable|boolean',
            'conciliation_outcome' => 'nullable|string',
            'interim_relief' => 'nullable|string',
            'notice_date' => 'nullable|date',
            'hearing_date' => 'nullable|date',
            'witnesses' => 'nullable|string',
            'hearing_notes' => 'nullable|string',
            'finding' => 'nullable|string|max:64',
            'recommendation' => 'nullable|string',
            'action_taken' => 'nullable|string',
            'appeal_filed' => 'nullable|boolean',
            'closure_notes' => 'nullable|string',
            'step_notes' => 'nullable|string',
        ]);

        $caseData = $complaint->case_data ?? [];
        foreach (['review_outcome', 'review_notes', 'conciliation_requested', 'conciliation_outcome', 'interim_relief', 'notice_date', 'hearing_date', 'witnesses', 'hearing_notes', 'finding', 'recommendation', 'action_taken', 'appeal_filed', 'closure_notes'] as $key) {
            if (array_key_exists($key, $data)) {
                $caseData[$key] = $data[$key];
            }
        }

        $timeline = $caseData['timeline'] ?? [];
        $timeline[] = [
            'at' => now()->toIso8601String(),
            'step' => $step['key'],
            'status' => $step['status'],
            'note' => $data['step_notes'] ?? 'Step saved',
            'by' => $user->name,
        ];
        $caseData['timeline'] = $timeline;

        $complaint->case_data = $caseData;
        $complaint->operate_step = $stepIndex;

        if ($complaint->status !== $step['status']) {
            $this->cases->transitionStatus($complaint, $user, $step['status'], $data['step_notes'] ?? null);
            $complaint->case_data = $caseData;
            $complaint->operate_step = $stepIndex;
            $complaint->save();
        } else {
            $complaint->save();
            $this->cases->logTimeline($complaint, $user, 'step_save', $data['step_notes'] ?? 'Operate step: ' . $step['label']);
        }

        $this->cases->audit($complaint->organization_id, $user, 'Operate step saved', $complaint->case_number, $step['label'], $request);

        $next = min($stepIndex + 1, count($steps) - 1);

        return redirect()->route('cases.operate', ['complaint' => $complaint, 'step' => $request->boolean('stay') ? $stepIndex : $next])
            ->with('success', 'Step saved: ' . $step['label']);
    }

    protected function authorizeOrg(PoshComplaint $complaint): void
    {
        if ($complaint->organization_id !== Auth::user()->organization_id) {
            abort(403);
        }
    }
}
