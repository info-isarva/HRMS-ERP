<?php

namespace App\Http\Controllers;

use App\Models\PoshIcMember;
use App\Models\PoshPolicy;
use App\Models\PoshPolicyAcknowledgement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmployeePortalController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $orgId = $user->organization_id;

        $activePolicy = PoshPolicy::where('organization_id', $orgId)->where('is_active', true)->first();
        $hasAcked = $activePolicy
            ? PoshPolicyAcknowledgement::where('posh_policy_id', $activePolicy->id)->where('user_id', $user->id)->exists()
            : false;

        $icMembers = PoshIcMember::where('organization_id', $orgId)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return view('employee.index', compact('activePolicy', 'hasAcked', 'icMembers'));
    }

    public function policy()
    {
        $user = Auth::user();
        $policy = PoshPolicy::where('organization_id', $user->organization_id)
            ->where('is_active', true)
            ->firstOrFail();

        $acknowledgement = PoshPolicyAcknowledgement::where('posh_policy_id', $policy->id)
            ->where('user_id', $user->id)
            ->first();

        return view('employee.policy', [
            'policy' => $policy,
            'hasAcked' => $acknowledgement !== null,
            'acknowledgement' => $acknowledgement,
        ]);
    }

    public function acknowledge(Request $request)
    {
        $policy = PoshPolicy::where('organization_id', Auth::user()->organization_id)
            ->where('is_active', true)
            ->firstOrFail();

        PoshPolicyAcknowledgement::updateOrCreate(
            [
                'posh_policy_id' => $policy->id,
                'user_id' => Auth::id(),
            ],
            [
                'acknowledged_at' => now(),
                'ip_address' => $request->ip(),
            ]
        );

        return redirect()->route('employee.portal')->with('success', 'Thank you. Your acknowledgement has been recorded.');
    }
}
