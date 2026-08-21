<?php

namespace App\Http\Controllers;

use App\Models\PoshComplaint;
use App\Models\PoshComplaintEvidence;
use App\Services\PoshCaseService;
use App\Services\PoshSlaService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ComplaintController extends Controller
{
    public function __construct(protected PoshCaseService $cases)
    {
    }

    public function create()
    {
        return view('complaints.create', [
            'respondentTypes' => config('posh.respondent_types'),
        ]);
    }

    public function store(Request $request, PoshSlaService $sla)
    {
        $user = Auth::user();
        $data = $request->validate([
            'is_anonymous' => 'boolean',
            'complainant_name' => 'nullable|string|max:255',
            'employee_code' => 'nullable|string|max:64',
            'department' => 'nullable|string|max:128',
            'filed_by_relation' => 'nullable|string|max:32',
            'respondent_name' => 'required|string|max:255',
            'respondent_type' => 'required|in:' . implode(',', array_keys(config('posh.respondent_types'))),
            'respondent_department' => 'nullable|string|max:128',
            'vs_employer' => 'boolean',
            'incident_date' => 'required|date|before_or_equal:today',
            'incident_location' => 'nullable|string|max:255',
            'description' => 'required|string|min:20',
            'extension_reason' => 'nullable|string|max:500',
            'evidence.*' => 'nullable|file|max:10240|mimes:pdf,jpg,jpeg,png,doc,docx',
        ]);

        $incident = Carbon::parse($data['incident_date']);
        $deadline = $sla->checkFilingDeadline($incident, (bool) $request->input('extension_approved'));

        $anonymous = $request->boolean('is_anonymous');
        $vsEmployer = $request->boolean('vs_employer');
        $routedTo = ($vsEmployer || $data['respondent_type'] === 'employer') ? 'LC' : 'IC';

        $complaint = DB::transaction(function () use ($user, $data, $anonymous, $vsEmployer, $routedTo, $request) {
            $caseNumber = $this->cases->generateCaseNumber($user->organization_id);

            $complaint = PoshComplaint::create([
                'organization_id' => $user->organization_id,
                'case_number' => $caseNumber,
                'filed_by_user_id' => $user->id,
                'complainant_name' => $anonymous ? null : ($data['complainant_name'] ?? $user->name),
                'complainant_email' => $anonymous ? null : $user->email,
                'employee_code' => $data['employee_code'] ?? null,
                'department' => $data['department'] ?? $user->department,
                'is_anonymous' => $anonymous,
                'filed_by_relation' => $data['filed_by_relation'] ?? 'self',
                'respondent_name' => $data['respondent_name'],
                'respondent_type' => $data['respondent_type'],
                'respondent_department' => $data['respondent_department'] ?? null,
                'vs_employer' => $vsEmployer,
                'incident_date' => $data['incident_date'],
                'incident_location' => $data['incident_location'] ?? null,
                'description' => $data['description'],
                'filing_within_deadline' => $deadline['within'],
                'extension_reason' => $data['extension_reason'] ?? null,
                'intake_channel' => 'portal',
                'submitted_at' => now(),
                'routed_to' => $routedTo,
                'status' => 'Submitted',
                'operate_step' => 0,
                'case_data' => ['timeline' => [['at' => now()->toIso8601String(), 'status' => 'Submitted', 'note' => 'Complaint filed']]],
            ]);

            if ($request->hasFile('evidence')) {
                foreach ($request->file('evidence') as $file) {
                    $path = $file->store('complaints/' . $complaint->id . '/evidence', 'local');
                    PoshComplaintEvidence::create([
                        'posh_complaint_id' => $complaint->id,
                        'uploaded_by_user_id' => $user->id,
                        'storage_path' => $path,
                        'original_filename' => $file->getClientOriginalName(),
                        'mime_type' => $file->getMimeType(),
                        'file_size' => $file->getSize(),
                    ]);
                }
            }

            $this->cases->logTimeline($complaint, $user, 'complaint_filed', 'Complaint submitted via employee portal');
            $this->cases->audit($user->organization_id, $user, 'Complaint filed', $caseNumber, $complaint->displayComplainant() . ' vs ' . $complaint->respondent_name, $request);

            return $complaint;
        });

        return redirect()->route('complaints.show', $complaint)
            ->with('success', 'Complaint ' . $complaint->case_number . ' submitted. Routed to ' . $complaint->routed_to . '.');
    }

    public function show(PoshComplaint $complaint)
    {
        $this->authorizeView($complaint);
        $complaint->load(['logs.user', 'evidence']);

        return view('complaints.show', compact('complaint'));
    }

    public function myCases()
    {
        $complaints = PoshComplaint::where('organization_id', Auth::user()->organization_id)
            ->where('filed_by_user_id', Auth::id())
            ->orderByDesc('created_at')
            ->get();

        return view('complaints.my-cases', compact('complaints'));
    }

    public function downloadEvidence(Request $request, PoshComplaint $complaint, PoshComplaintEvidence $evidence)
    {
        $this->authorizeView($complaint);
        abort_unless($evidence->posh_complaint_id === $complaint->id, 404);
        abort_unless(Auth::user()->hasIcAccess(), 403, 'Only IC can download evidence.');

        $this->cases->audit($complaint->organization_id, Auth::user(), 'Evidence downloaded', $complaint->case_number, $evidence->original_filename, $request);

        return Storage::disk('local')->download($evidence->storage_path, $evidence->original_filename);
    }

    protected function authorizeView(PoshComplaint $complaint): void
    {
        $user = Auth::user();

        if ($complaint->organization_id !== $user->organization_id) {
            abort(403);
        }

        if ($user->hasIcAccess()) {
            return;
        }

        if ($complaint->filed_by_user_id !== $user->id) {
            abort(403, 'You can only view your own complaints.');
        }
    }
}
