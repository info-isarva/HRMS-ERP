<?php

namespace App\Http\Controllers;

use App\Models\LeaveType;
use App\Models\Department;
use App\Services\DepartmentApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class LeaveTypeController extends Controller
{
    protected $departmentService;

    public function __construct(DepartmentApiService $departmentService)
    {
        $this->departmentService = $departmentService;
        $this->middleware('role:admin,super_admin');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $selectedYear = $request->get('financial_year', '2025-2026');
        
        $leaveTypes = LeaveType::with(['departments'])
            ->forFinancialYear($selectedYear)
            ->orderBy('name')
            ->paginate(15);

        // Get available financial years
        $financialYears = LeaveType::select('financial_year')
            ->distinct()
            ->orderBy('financial_year', 'desc')
            ->pluck('financial_year');

        if ($financialYears->isEmpty()) {
            $financialYears = collect(['2025-2026']);
        }

        return view('leave-types.index', compact('leaveTypes', 'financialYears', 'selectedYear'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Sync departments before showing the form
        $this->departmentService->syncDepartments();
        
        $departments = Department::active()->orderBy('name')->get();
        
        return view('leave-types.create', compact('departments'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => [
                'required',
                'string',
                'max:10',
                Rule::unique('leave_types')->where(function ($query) use ($request) {
                    return $query->where('financial_year', $request->financial_year);
                }),
            ],
            'description' => 'nullable|string|max:1000',
            'days_count' => 'required|integer|min:1|max:365',
            'financial_year' => 'required|string',
            'departments' => 'required|array|min:1',
            'departments.*' => 'exists:departments,id',
        ]);

        try {
            DB::beginTransaction();

            $leaveType = LeaveType::create($validated);
            
            // Attach departments and persist payroll_department_id into pivot
            $attachData = [];
            foreach ($validated['departments'] as $deptId) {
                $dept = Department::find($deptId);
                $attachData[$deptId] = [
                    'payroll_department_id' => $dept ? $dept->api_department_id : null
                ];
            }
            $leaveType->departments()->attach($attachData);

            DB::commit();

            return redirect()
                ->route('leave-types.index')
                ->with('success', 'Leave type created successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to create leave type. Please try again.']);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(LeaveType $leaveType)
    {
        $leaveType->load(['departments', 'leaveApplications' => function($query) {
            $query->with('user')->latest();
        }]);

        return view('leave-types.show', compact('leaveType'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(LeaveType $leaveType)
    {
        // Sync departments before showing the form
        $this->departmentService->syncDepartments();
        
        $departments = Department::active()->orderBy('name')->get();
        $leaveType->load('departments');
        
        return view('leave-types.edit', compact('leaveType', 'departments'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, LeaveType $leaveType)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => [
                'required',
                'string',
                'max:10',
                Rule::unique('leave_types')->ignore($leaveType->id)->where(function ($query) use ($request) {
                    return $query->where('financial_year', $request->financial_year);
                }),
            ],
            'description' => 'nullable|string|max:1000',
            'days_count' => 'required|integer|min:1|max:365',
            'is_active' => 'boolean',
            'departments' => 'required|array|min:1',
            'departments.*' => 'exists:departments,id',
        ]);

        try {
            DB::beginTransaction();

            $leaveType->update($validated);
            
            // Sync departments and persist payroll_department_id into pivot
            $syncData = [];
            foreach ($validated['departments'] as $deptId) {
                $dept = Department::find($deptId);
                $syncData[$deptId] = [
                    'payroll_department_id' => $dept ? $dept->api_department_id : null
                ];
            }
            $leaveType->departments()->sync($syncData);

            DB::commit();

            return redirect()
                ->route('leave-types.index')
                ->with('success', 'Leave type updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to update leave type. Please try again.']);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LeaveType $leaveType)
    {
        try {
            // Check if leave type has any applications
            if ($leaveType->leaveApplications()->count() > 0) {
                return back()->withErrors(['error' => 'Cannot delete leave type with existing applications.']);
            }

            $leaveType->departments()->detach();
            $leaveType->delete();

            return redirect()
                ->route('leave-types.index')
                ->with('success', 'Leave type deleted successfully!');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to delete leave type. Please try again.']);
        }
    }

    /**
     * Sync departments from API
     */
    public function syncDepartments()
    {
        try {
            $syncedDepartments = $this->departmentService->syncDepartments();
            
            return redirect()
                ->route('leave-types.index')
                ->with('success', 'Departments synchronized successfully! ' . count($syncedDepartments) . ' departments updated.');
                
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to sync departments. Please try again.']);
        }
    }
}
