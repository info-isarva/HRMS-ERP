<?php

namespace App\Http\Controllers;

use App\Models\PoshPolicy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PolicyController extends Controller
{
    public function index()
    {
        $policies = PoshPolicy::where('organization_id', Auth::user()->organization_id)
            ->orderByDesc('published_at')
            ->orderByDesc('created_at')
            ->get();

        return view('policies.index', compact('policies'));
    }

    public function create()
    {
        return view('policies.edit', [
            'policy' => new PoshPolicy([
                'version' => 'v' . now()->format('Y.m'),
                'title' => 'POSH Workplace Policy',
            ]),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['organization_id'] = Auth::user()->organization_id;
        $data['published_by'] = Auth::id();
        $data['is_active'] = $request->input('publish') === '1';
        $data['published_at'] = $data['is_active'] ? now() : null;

        if ($data['is_active']) {
            PoshPolicy::where('organization_id', $data['organization_id'])->update(['is_active' => false]);
        }

        if ($request->hasFile('policy_file')) {
            $data['file_path'] = $request->file('policy_file')->store('policies', 'local');
        }

        PoshPolicy::create($data);

        return redirect()->route('policies.index')->with('success', 'Policy saved.');
    }

    public function edit(PoshPolicy $policy)
    {
        $this->authorizeOrg($policy);

        return view('policies.edit', compact('policy'));
    }

    public function update(Request $request, PoshPolicy $policy)
    {
        $this->authorizeOrg($policy);
        $data = $this->validated($request);

        if ($request->boolean('publish')) {
            PoshPolicy::where('organization_id', $policy->organization_id)->update(['is_active' => false]);
            $data['is_active'] = true;
            $data['published_at'] = now();
            $data['published_by'] = Auth::id();
        }

        if ($request->hasFile('policy_file')) {
            if ($policy->file_path) {
                Storage::disk('local')->delete($policy->file_path);
            }
            $data['file_path'] = $request->file('policy_file')->store('policies', 'local');
        }

        $policy->update($data);

        return redirect()->route('policies.index')->with('success', 'Policy updated.');
    }

    public function activate(PoshPolicy $policy)
    {
        $this->authorizeOrg($policy);

        PoshPolicy::where('organization_id', $policy->organization_id)->update(['is_active' => false]);
        $policy->update([
            'is_active' => true,
            'published_at' => now(),
            'published_by' => Auth::id(),
        ]);

        return redirect()->route('policies.index')->with('success', 'Policy published for employees.');
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'version' => 'required|string|max:32',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'policy_file' => 'nullable|file|mimes:pdf|max:10240',
        ]);
    }

    protected function authorizeOrg(PoshPolicy $policy): void
    {
        if ($policy->organization_id !== Auth::user()->organization_id) {
            abort(403);
        }
    }
}
