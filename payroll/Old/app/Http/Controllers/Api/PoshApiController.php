<?php

namespace App\Http\Controllers\Api;

/**
 * @deprecated Phase 0 — Legacy POSH API for Attendance proxy. Use ISARVA POSH module.
 */
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PoshIccMember;
use App\Models\PoshComplaint;
use App\Models\PoshComplaintLog;
use App\Models\EmployeeBasicDetail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PoshApiController extends Controller
{
    private function validateToken(Request $request)
    {
        $expectedToken = env('ATTENDANCE_SYNC_TOKEN', env('JWT_HMAC_SECRET'));
        if ($request->sync_token !== $expectedToken && $request->bearerToken() !== $expectedToken) {
            return false;
        }
        return true;
    }

    /**
     * Get ICC Board members list
     */
    public function getIccBoard(Request $request)
    {
        if (!$this->validateToken($request)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $members = PoshIccMember::with(['employee.designationObj', 'employee.departmentObj'])->get();

        $data = $members->map(function($m) {
            return [
                'name' => $m->employee->name ?? 'Unknown',
                'role' => $m->icc_role,
                'email' => $m->email ?? $m->employee->email ?? '',
                'contact_number' => $m->contact_number ?? $m->employee->contact_number ?? '',
                'designation' => $m->employee->designationObj->position ?? $m->employee->designation ?? 'N/A',
                'department' => $m->employee->departmentObj->department ?? $m->employee->department ?? 'N/A',
            ];
        });

        return response()->json([
            'success' => true,
            'members' => $data
        ]);
    }

    /**
     * Get complaints filed by a specific user
     */
    public function getComplaints(Request $request)
    {
        if (!$this->validateToken($request)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $request->validate([
            'email' => 'required|email'
        ]);

        $email = $request->email;

        $complaints = PoshComplaint::where(function($query) use ($email) {
            $query->where('complainant_email', $email)
                  ->orWhereHas('employee', function($q) use ($email) {
                      $q->where('email', $email);
                  });
        })->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'complaints' => $complaints
        ]);
    }

    /**
     * File a new POSH complaint
     */
    public function storeComplaint(Request $request)
    {
        if (!$this->validateToken($request)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $request->validate([
            'email' => 'required|email',
            'is_anonymous' => 'required|boolean',
            'incident_date' => 'required|date',
            'incident_location' => 'required|string',
            'respondent_name' => 'required|string',
            'respondent_department' => 'nullable|string',
            'description' => 'required|string',
        ]);

        // Find complainant employee record
        $employee = EmployeeBasicDetail::where('email', $request->email)->first();

        // Generate Case Number
        $complaintNumber = 'COMP-' . date('Ymd') . '-' . strtoupper(Str::random(4));
        while (PoshComplaint::where('complaint_number', $complaintNumber)->exists()) {
            $complaintNumber = 'COMP-' . date('Ymd') . '-' . strtoupper(Str::random(4));
        }

        try {
            $complaint = PoshComplaint::create([
                'complaint_number' => $complaintNumber,
                'employee_id' => $employee ? $employee->id : null,
                'complainant_name' => $employee ? $employee->name : null,
                'complainant_email' => $request->email,
                'is_anonymous' => $request->is_anonymous,
                'incident_date' => $request->incident_date,
                'incident_location' => $request->incident_location,
                'respondent_name' => $request->respondent_name,
                'respondent_department' => $request->respondent_department,
                'description' => $request->description,
                'status' => 'pending',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Complaint filed successfully.',
                'complaint' => $complaint
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to submit POSH complaint via API', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to log POSH complaint.'], 500);
        }
    }

    /**
     * Get complaint details and history log
     */
    public function getComplaintDetails(Request $request, $id)
    {
        if (!$this->validateToken($request)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $request->validate([
            'email' => 'required|email'
        ]);

        $complaint = PoshComplaint::with(['employee', 'logs.actionByUser'])->findOrFail($id);

        // Security check: Make sure complainant matches request email
        if ($complaint->complainant_email !== $request->email && (!$complaint->employee || $complaint->employee->email !== $request->email)) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        // Mask internal details if client-facing anonymous requires it
        // But for employee's own portal, they know who they are.

        // Map logs for client
        $logs = $complaint->logs->map(function($l) {
            // Mask action by user details from employee view if preferred, or just display "ICC Committee"
            return [
                'action_type' => $l->action_type,
                'notes' => $l->notes,
                'minutes_of_meeting' => $l->minutes_of_meeting, // employee typically doesn't see confidential meeting minutes
                'created_at' => $l->created_at,
                'attachment_path' => $l->attachment_path,
                'original_filename' => $l->original_filename,
            ];
        });

        // Strip minutes_of_meeting from employee view to maintain strict ICC confidentiality
        $cleanedLogs = $logs->map(function($l) {
            if ($l['action_type'] === 'meeting_minutes') {
                return [
                    'action_type' => 'meeting_minutes',
                    'notes' => 'Committee meeting held regarding the case.',
                    'created_at' => $l['created_at'],
                    'attachment_path' => null,
                    'original_filename' => null,
                ];
            }
            return $l;
        });

        return response()->json([
            'success' => true,
            'complaint' => $complaint,
            'logs' => $cleanedLogs
        ]);
    }
}
