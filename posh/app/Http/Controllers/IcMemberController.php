<?php

namespace App\Http\Controllers;

use App\Models\PoshEmployeeDirectory;
use App\Models\PoshIcMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IcMemberController extends Controller
{
    public function index()
    {
        $orgId = Auth::user()->organization_id;
        $members = PoshIcMember::where('organization_id', $orgId)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $womenCount = $members->where('is_woman', true)->count();
        $total = $members->count();

        $directoryEmployees = PoshEmployeeDirectory::where('organization_id', $orgId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('ic-members.index', [
            'members' => $members,
            'icRoles' => config('posh.ic_roles'),
            'womenCount' => $womenCount,
            'total' => $total,
            'meetsWomenQuota' => $total > 0 && ($womenCount / $total) >= 0.5,
            'directoryEmployees' => $directoryEmployees,
            'org' => Auth::user()->organization,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required_without:employee_directory_id|string|max:255',
            'employee_directory_id' => 'nullable|exists:posh_employee_directory,id',
            'employee_code' => 'nullable|string|max:64',
            'department' => 'nullable|string|max:128',
            'designation' => 'nullable|string|max:128',
            'ic_role' => 'required|in:' . implode(',', array_keys(config('posh.ic_roles'))),
            'contact_number' => 'nullable|string|max:32',
            'email' => 'nullable|email|max:255',
            'is_woman' => 'boolean',
        ]);

        $orgId = Auth::user()->organization_id;
        $memberOrigin = $data['ic_role'] === 'external_member' ? 'external' : 'internal';

        if (! empty($data['employee_directory_id']) && $memberOrigin === 'internal') {
            $dir = PoshEmployeeDirectory::where('organization_id', $orgId)
                ->findOrFail($data['employee_directory_id']);
            $data['name'] = $dir->name;
            $data['email'] = $dir->email;
            $data['employee_code'] = $dir->employee_code;
            $data['department'] = $dir->department;
            $data['designation'] = $dir->designation;
        }

        PoshIcMember::create([
            'organization_id' => $orgId,
            'name' => $data['name'],
            'employee_code' => $data['employee_code'] ?? null,
            'department' => $data['department'] ?? null,
            'designation' => $data['designation'] ?? null,
            'ic_role' => $data['ic_role'],
            'member_origin' => $memberOrigin,
            'employee_directory_id' => $memberOrigin === 'internal' ? ($data['employee_directory_id'] ?? null) : null,
            'contact_number' => $data['contact_number'] ?? null,
            'email' => $data['email'] ?? null,
            'is_woman' => $request->boolean('is_woman'),
            'sort_order' => (int) $request->input('sort_order', 0),
        ]);

        return redirect()->route('ic-members.index')->with('success', 'IC member added.');
    }

    public function update(Request $request, PoshIcMember $icMember)
    {
        $this->authorizeOrg($icMember);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'employee_code' => 'nullable|string|max:64',
            'department' => 'nullable|string|max:128',
            'designation' => 'nullable|string|max:128',
            'ic_role' => 'required|in:' . implode(',', array_keys(config('posh.ic_roles'))),
            'contact_number' => 'nullable|string|max:32',
            'email' => 'nullable|email|max:255',
            'is_woman' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $data['is_woman'] = $request->boolean('is_woman');
        $data['is_active'] = $request->boolean('is_active', true);

        $icMember->update($data);

        return redirect()->route('ic-members.index')->with('success', 'IC member updated.');
    }

    public function destroy(PoshIcMember $icMember)
    {
        $this->authorizeOrg($icMember);
        $icMember->delete();

        return redirect()->route('ic-members.index')->with('success', 'IC member removed.');
    }

    protected function authorizeOrg(PoshIcMember $member): void
    {
        if ($member->organization_id !== Auth::user()->organization_id) {
            abort(403);
        }
    }
}
