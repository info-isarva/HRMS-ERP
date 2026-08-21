<?php

namespace App\Http\Controllers\Compliance;

/**
 * @deprecated Phase 0 — Legacy basic POSH in Payroll. Replaced by ISARVA POSH module.
 *             Routes blocked unless POSH_LEGACY_ENABLED=true. Do not extend.
 */
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EmployeeBasicDetail;
use App\Models\PoshIccMember;
use App\Models\PoshComplaint;
use App\Models\PoshComplaintLog;
use App\Models\UserConsent;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class PoshComplianceController extends Controller
{
    /**
     * Display ICC Board Members
     */
    public function iccBoard()
    {
        $members = PoshIccMember::with('employee.designationObj', 'employee.departmentObj')->get();
        $employees = EmployeeBasicDetail::active()->orderBy('name', 'asc')->get();

        return view('compliance.posh.icc_board', compact('members', 'employees'));
    }

    /**
     * Store new ICC Board Member
     */
    public function storeIccMember(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employee_basic_details,id|unique:posh_icc_members,employee_id',
            'icc_role' => 'required|string',
            'contact_number' => 'nullable|string',
            'email' => 'nullable|email',
        ]);

        $employee = EmployeeBasicDetail::findOrFail($request->employee_id);

        PoshIccMember::create([
            'employee_id' => $request->employee_id,
            'icc_role' => $request->icc_role,
            'contact_number' => $request->contact_number ?? $employee->contact_number,
            'email' => $request->email ?? $employee->email,
        ]);

        flash()->success('ICC member added successfully.');
        return redirect()->back();
    }

    /**
     * Update ICC Board Member
     */
    public function updateIccMember(Request $request, $id)
    {
        $request->validate([
            'icc_role' => 'required|string',
            'contact_number' => 'nullable|string',
            'email' => 'nullable|email',
        ]);

        $member = PoshIccMember::findOrFail($id);
        $member->update([
            'icc_role' => $request->icc_role,
            'contact_number' => $request->contact_number,
            'email' => $request->email,
        ]);

        flash()->success('ICC member updated successfully.');
        return redirect()->back();
    }

    /**
     * Delete ICC Board Member
     */
    public function deleteIccMember($id)
    {
        $member = PoshIccMember::findOrFail($id);
        $member->delete();

        flash()->success('ICC member removed successfully.');
        return redirect()->back();
    }

    /**
     * Tracking Dashboard for Policy Distribution
     */
    public function policyDistribution(Request $request)
    {
        // Get all active employees
        $activeEmployeesQuery = EmployeeBasicDetail::active()->with('user');

        if ($request->filled('search')) {
            $search = $request->search;
            $activeEmployeesQuery->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%')
                  ->orWhere('employee_id', 'like', '%' . $search . '%');
            });
        }

        $allEmployees = $activeEmployeesQuery->get();

        // Get POSH consents
        $consents = UserConsent::where('policy_type', 'posh_policy')
            ->where('is_accepted', true)
            ->get()
            ->keyBy('user_id');

        $data = $allEmployees->map(function($employee) use ($consents) {
            $user = $employee->user;
            $consent = $user ? $consents->get($user->id) : null;
            return (object) [
                'user_id' => $employee->employee_id,
                'name' => $employee->name,
                'email' => $employee->email,
                'status' => $consent ? 'Acknowledged' : 'Pending',
                'accepted_at' => $consent ? $consent->accepted_at : null,
                'ip_address' => $consent ? $consent->ip_address : null,
            ];
        });

        if ($request->filled('status')) {
            $status = $request->status;
            $data = $data->filter(function($item) use ($status) {
                return $item->status === $status;
            });
        }

        // Calculate statistics
        $totalEmployees = $allEmployees->count();
        $acknowledgedCount = $data->filter(function($item) { return $item->status === 'Acknowledged'; })->count();
        $pendingCount = $totalEmployees - $acknowledgedCount;

        $filters = $request->all();

        return view('compliance.posh.policy_distribution', compact('data', 'totalEmployees', 'acknowledgedCount', 'pendingCount', 'filters'));
    }

    /**
     * Confidential Grievances / Complaints List
     */
    public function complaints(Request $request)
    {
        // Access control check
        $user = Auth::user();
        $isIccMember = PoshIccMember::where('employee_id', $user->employee_id)->exists();
        $isAdmin = $user->role_name === 'Super Admin' || $user->role_name === 'Admin';

        if (!$isIccMember && !$isAdmin) {
            abort(403, 'Access Denied: Only Internal Complaints Committee (ICC) members or Administrators are authorized to access this secure portal.');
        }

        $query = PoshComplaint::with('employee')->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('complaint_number', 'like', '%' . $search . '%')
                  ->orWhere('respondent_name', 'like', '%' . $search . '%')
                  ->orWhere('incident_location', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $complaints = $query->get();
        $filters = $request->all();

        return view('compliance.posh.complaints_list', compact('complaints', 'filters'));
    }

    /**
     * View detailed case file
     */
    public function complaintDetails($id)
    {
        // Access control check
        $user = Auth::user();
        $isIccMember = PoshIccMember::where('employee_id', $user->employee_id)->exists();
        $isAdmin = $user->role_name === 'Super Admin' || $user->role_name === 'Admin';

        if (!$isIccMember && !$isAdmin) {
            abort(403, 'Access Denied: Only Internal Complaints Committee (ICC) members or Administrators are authorized to access this secure portal.');
        }

        $complaint = PoshComplaint::with(['employee.designationObj', 'employee.departmentObj', 'logs.actionByUser'])->findOrFail($id);

        return view('compliance.posh.complaint_details', compact('complaint'));
    }

    /**
     * Add log entry or change complaint status
     */
    public function logComplaintAction(Request $request, $id)
    {
        // Access control check
        $user = Auth::user();
        $isIccMember = PoshIccMember::where('employee_id', $user->employee_id)->exists();
        $isAdmin = $user->role_name === 'Super Admin' || $user->role_name === 'Admin';

        if (!$isIccMember && !$isAdmin) {
            abort(403, 'Access Denied: Only Internal Complaints Committee (ICC) members or Administrators are authorized to access this secure portal.');
        }

        $request->validate([
            'action_type' => 'required|string',
            'notes' => 'required_without_all:minutes_of_meeting,status|nullable|string',
            'minutes_of_meeting' => 'nullable|string',
            'status' => 'nullable|string',
            'resolution_summary' => 'required_if:status,resolved,dismissed|nullable|string',
            'attachment' => 'nullable|file|max:10240', // 10MB max
        ]);

        $complaint = PoshComplaint::findOrFail($id);

        // Upload attachment if any
        $attachmentPath = null;
        $originalFilename = null;
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $originalFilename = $file->getClientOriginalName();
            $attachmentPath = $file->store('posh_attachments', 'public');
        }

        // Create log record
        PoshComplaintLog::create([
            'complaint_id' => $complaint->id,
            'action_by_user_id' => $user->id,
            'action_type' => $request->action_type,
            'notes' => $request->notes,
            'minutes_of_meeting' => $request->minutes_of_meeting,
            'attachment_path' => $attachmentPath,
            'original_filename' => $originalFilename,
        ]);

        // Handle status update
        if ($request->filled('status') && $request->status !== $complaint->status) {
            $oldStatus = $complaint->status;
            $complaint->status = $request->status;

            if (in_array($request->status, ['resolved', 'dismissed'])) {
                $complaint->resolution_summary = $request->resolution_summary;
                $complaint->resolved_at = Carbon::now();
            }

            $complaint->save();

            // Log status change
            PoshComplaintLog::create([
                'complaint_id' => $complaint->id,
                'action_by_user_id' => $user->id,
                'action_type' => 'status_change',
                'notes' => "Changed status from '" . ucfirst(str_replace('_', ' ', $oldStatus)) . "' to '" . ucfirst(str_replace('_', ' ', $request->status)) . "'.",
            ]);
        }

        flash()->success('Timeline log entry recorded successfully.');
        return redirect()->back();
    }
}
