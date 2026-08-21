<?php

namespace App\Http\Controllers;

use App\Models\PoshEmployeeDirectory;
use App\Models\User;
use App\Services\PoshPayrollSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class EmployeeDirectoryController extends Controller
{
    public function index(Request $request)
    {
        $org = $request->user()->organization;
        $employees = PoshEmployeeDirectory::where('organization_id', $org->id)
            ->orderBy('name')
            ->get();

        return view('employees.index', [
            'org' => $org,
            'employees' => $employees,
        ]);
    }

    public function store(Request $request)
    {
        $org = $request->user()->organization;
        abort_unless(! $org->usesPayrollEmployees(), 403, 'Employees are synced from Payroll in ERP mode.');

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'employee_code' => 'nullable|string|max:64',
            'department' => 'nullable|string|max:128',
            'designation' => 'nullable|string|max:128',
            'create_login' => 'boolean',
        ]);

        $entry = PoshEmployeeDirectory::create([
            'organization_id' => $org->id,
            'name' => $data['name'],
            'email' => $data['email'],
            'employee_code' => $data['employee_code'] ?? null,
            'department' => $data['department'] ?? null,
            'designation' => $data['designation'] ?? null,
            'source' => 'posh',
            'is_active' => true,
        ]);

        if ($request->boolean('create_login')) {
            $this->ensureUserForDirectory($entry, $org->id);
        }

        return back()->with('success', 'Employee added to POSH directory.');
    }

    public function update(Request $request, PoshEmployeeDirectory $directory)
    {
        $this->authorizeOrg($directory);
        abort_unless(! $directory->organization->usesPayrollEmployees(), 403);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'employee_code' => 'nullable|string|max:64',
            'department' => 'nullable|string|max:128',
            'designation' => 'nullable|string|max:128',
            'is_active' => 'boolean',
        ]);

        $directory->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'employee_code' => $data['employee_code'] ?? null,
            'department' => $data['department'] ?? null,
            'designation' => $data['designation'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('success', 'Employee updated.');
    }

    public function destroy(PoshEmployeeDirectory $directory)
    {
        $this->authorizeOrg($directory);
        abort_unless(! $directory->organization->usesPayrollEmployees(), 403);

        $directory->delete();

        return back()->with('success', 'Employee removed from directory.');
    }

    public function sync(Request $request, PoshPayrollSyncService $syncService)
    {
        $org = $request->user()->organization;
        abort_unless($org->usesPayrollEmployees(), 403);

        $count = $syncService->sync($org);

        return back()->with('success', "Synced {$count} employees from Payroll (demo data).");
    }

    public function enableLogin(PoshEmployeeDirectory $directory)
    {
        $this->authorizeOrg($directory);
        $this->ensureUserForDirectory($directory, $directory->organization_id);

        return back()->with('success', 'Portal login enabled for ' . $directory->name . '. Default password: password');
    }

    protected function ensureUserForDirectory(PoshEmployeeDirectory $entry, int $orgId): User
    {
        $user = User::updateOrCreate(
            ['email' => $entry->email],
            [
                'name' => $entry->name,
                'password' => Hash::make('password'),
                'organization_id' => $orgId,
                'employee_code' => $entry->employee_code,
                'department' => $entry->department,
                'designation' => $entry->designation,
                'posh_role' => 'employee',
                'user_source' => $entry->source === 'payroll' ? 'payroll' : 'posh',
                'status' => 1,
            ]
        );

        $entry->update(['user_id' => $user->id]);

        return $user;
    }

    protected function authorizeOrg(PoshEmployeeDirectory $employee): void
    {
        if ($employee->organization_id !== Auth::user()->organization_id) {
            abort(403);
        }
    }
}
