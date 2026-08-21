<?php

namespace App\Http\Controllers;

use App\Models\PoshComplaint;
use App\Models\PoshIcMember;
use App\Models\PoshPolicy;
use App\Models\PoshPolicyAcknowledgement;
use App\Services\PoshComplianceService;
use App\Services\PoshSlaService;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(PoshSlaService $sla, PoshComplianceService $compliance)
    {
        $user = Auth::user();
        $orgId = $user->organization_id;
        $compliance->seedDutiesForOrganization($orgId);

        $caseQuery = PoshComplaint::where('organization_id', $orgId);
        $stats = [
            'ic_members' => PoshIcMember::where('organization_id', $orgId)->where('is_active', true)->count(),
            'active_policy' => PoshPolicy::where('organization_id', $orgId)->where('is_active', true)->exists(),
            'acknowledgements' => PoshPolicyAcknowledgement::whereHas('policy', fn ($q) => $q->where('organization_id', $orgId))->count(),
            'total_cases' => (clone $caseQuery)->count(),
            'open_cases' => (clone $caseQuery)->whereNotIn('status', config('posh.closed_statuses'))->count(),
            'closed_cases' => (clone $caseQuery)->whereIn('status', ['Closed', 'Archived'])->count(),
        ];

        $recentCases = $user->hasIcAccess()
            ? PoshComplaint::where('organization_id', $orgId)->orderByDesc('created_at')->limit(5)->get()
            : PoshComplaint::where('filed_by_user_id', $user->id)->orderByDesc('created_at')->limit(5)->get();

        $activePolicy = PoshPolicy::where('organization_id', $orgId)->where('is_active', true)->first();
        $userAcked = $activePolicy
            ? PoshPolicyAcknowledgement::where('posh_policy_id', $activePolicy->id)->where('user_id', $user->id)->exists()
            : false;

        $slaAlerts = $user->hasIcAccess() ? $sla->alertsForOrganization($orgId) : collect();
        $compliancePercent = $compliance->dutiesCompletionPercent($orgId);

        return view('dashboard.index', compact('stats', 'activePolicy', 'userAcked', 'recentCases', 'slaAlerts', 'compliancePercent'));
    }
}
