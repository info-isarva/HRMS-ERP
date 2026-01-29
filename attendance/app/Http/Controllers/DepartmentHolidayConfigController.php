<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\DepartmentHolidayConfig;
use App\Services\DepartmentApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DepartmentHolidayConfigController extends Controller
{
    protected $departmentApiService;

    public function __construct(DepartmentApiService $departmentApiService)
    {
        $this->middleware('auth');
        $this->middleware('role:admin,super_admin');
        $this->departmentApiService = $departmentApiService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $selectedYear = $request->get('financial_year', $this->getCurrentFinancialYear());
        
        // Sync departments first (with error handling)
        try {
            $this->departmentApiService->syncDepartments();
        } catch (\Exception $e) {
            // Log the error but don't break the page
            \Log::error('Department sync failed: ' . $e->getMessage());
        }
        
        // Auto-cleanup orphaned configs (only if there are any)
        $orphanedCount = DepartmentHolidayConfig::whereDoesntHave('department')->count();
        if ($orphanedCount > 0) {
            DepartmentHolidayConfig::whereDoesntHave('department')->delete();
            session()->flash('info', "Automatically cleaned up {$orphanedCount} orphaned configuration(s).");
        }
        
        // Sync used holidays count for all configs
        $this->syncUsedHolidaysCount($selectedYear);
        
        $configs = DepartmentHolidayConfig::with('department')
            ->forFinancialYear($selectedYear)
            ->orderBy('department_id')
            ->get();
        
        $financialYears = $this->getFinancialYearOptions();
        
        // Get departments that don't have configs yet
        $departmentsWithoutConfig = Department::active()
            ->whereNotIn('id', $configs->pluck('department_id'))
            ->get();
        
        return view('department-holiday-configs.index', compact(
            'configs', 
            'selectedYear', 
            'financialYears', 
            'departmentsWithoutConfig'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Sync departments first (with error handling)
        try {
            $this->departmentApiService->syncDepartments();
        } catch (\Exception $e) {
            // Log the error but don't break the page
            \Log::error('Department sync failed: ' . $e->getMessage());
        }
        
        $departments = Department::active()->get();
        $financialYears = $this->getFinancialYearOptions();
        $currentYear = $this->getCurrentFinancialYear();
        
        return view('department-holiday-configs.create', compact('departments', 'financialYears', 'currentYear'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'department_id' => 'required|exists:departments,id',
            'financial_year' => 'required|string',
            'allowed_holidays' => 'required|integer|min:0|max:50',
            'fixed_public_holidays' => 'required|integer|min:0',
            'flexible_public_holidays' => 'required|integer|min:0',
        ]);

        // Custom validation: fixed + flexible should equal allowed_holidays
        if ($request->fixed_public_holidays + $request->flexible_public_holidays != $request->allowed_holidays) {
            return back()->withErrors([
                'fixed_public_holidays' => 'Fixed and Flexible public holidays must sum up to the Total Public Holidays per Employee.',
                'flexible_public_holidays' => 'Fixed and Flexible public holidays must sum up to the Total Public Holidays per Employee.'
            ])->withInput();
        }

        // Check if config already exists
        $existingConfig = DepartmentHolidayConfig::where('department_id', $request->department_id)
            ->where('financial_year', $request->financial_year)
            ->first();

        if ($existingConfig) {
            return back()->withErrors(['department_id' => 'Holiday configuration already exists for this department and financial year.'])
                        ->withInput();
        }

        // Get the payroll_department_id from the department
        $department = Department::find($request->department_id);
        $payrollDepartmentId = $department ? $department->api_department_id : null;

        DepartmentHolidayConfig::create([
            'department_id' => $request->department_id,
            'payroll_department_id' => $payrollDepartmentId,
            'financial_year' => $request->financial_year,
            'allowed_holidays' => $request->allowed_holidays,
            'fixed_public_holidays' => $request->fixed_public_holidays,
            'flexible_public_holidays' => $request->flexible_public_holidays,
            'used_holidays' => 0,
            'is_active' => true,
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('holiday-department-configs.index', ['financial_year' => $request->financial_year])
                        ->with('success', 'Department holiday configuration created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(DepartmentHolidayConfig $holiday_department_config)
    {
        $holiday_department_config->load(['department', 'creator', 'updater']);
        
        // Check if department exists and add error message if not
        $errorMessage = null;
        if (!$holiday_department_config->department) {
            $errorMessage = 'Department associated with this configuration no longer exists. Configuration ID: ' . $holiday_department_config->id;
        }
        
        return view('department-holiday-configs.show', [
            'departmentHolidayConfig' => $holiday_department_config,
            'errorMessage' => $errorMessage
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DepartmentHolidayConfig $holiday_department_config)
    {
        $holiday_department_config->load('department');
        
        // Check if department exists and add error message if not
        $errorMessage = null;
        if (!$holiday_department_config->department) {
            $errorMessage = 'Department associated with this configuration no longer exists. Configuration ID: ' . $holiday_department_config->id;
        }
        
        $departments = Department::active()->get();
        $financialYears = $this->getFinancialYearOptions();
        
        return view('department-holiday-configs.edit', [
            'departmentHolidayConfig' => $holiday_department_config,
            'departments' => $departments,
            'financialYears' => $financialYears,
            'errorMessage' => $errorMessage
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DepartmentHolidayConfig $holiday_department_config)
    {
        // Check if department exists
        if (!$holiday_department_config->department) {
            return redirect()->route('holiday-department-configs.index')
                ->with('error', 'Cannot update configuration: Department no longer exists. Configuration ID: ' . $holiday_department_config->id);
        }

        $request->validate([
            'allowed_holidays' => 'required|integer|min:0|max:50',
            'fixed_public_holidays' => 'required|integer|min:0',
            'flexible_public_holidays' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ]);

        // Custom validation: fixed + flexible should equal allowed_holidays
        if ($request->fixed_public_holidays + $request->flexible_public_holidays != $request->allowed_holidays) {
            return back()->withErrors([
                'fixed_public_holidays' => 'Fixed and Flexible public holidays must sum up to the Total Public Holidays per Employee.',
                'flexible_public_holidays' => 'Fixed and Flexible public holidays must sum up to the Total Public Holidays per Employee.'
            ])->withInput();
        }

        // Check if new allowed holidays is less than used holidays
        if ($request->allowed_holidays < $holiday_department_config->used_holidays) {
            return back()->withErrors(['allowed_holidays' => 'Allowed holidays cannot be less than already used holidays (' . $holiday_department_config->used_holidays . ').'])
                        ->withInput();
        }

        $holiday_department_config->update([
            'allowed_holidays' => $request->allowed_holidays,
            'fixed_public_holidays' => $request->fixed_public_holidays,
            'flexible_public_holidays' => $request->flexible_public_holidays,
            'is_active' => $request->boolean('is_active'),
            'updated_by' => Auth::id(),
        ]);

        return redirect()->route('holiday-department-configs.index', ['financial_year' => $holiday_department_config->financial_year])
                        ->with('success', 'Department holiday configuration updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DepartmentHolidayConfig $holiday_department_config)
    {
        if ($holiday_department_config->used_holidays > 0) {
            return back()->withErrors(['error' => 'Cannot delete configuration with used holidays.']);
        }

        $holiday_department_config->delete();

        return redirect()->route('holiday-department-configs.index')
                        ->with('success', 'Department holiday configuration deleted successfully!');
    }

    /**
     * Sync departments from API
     */
    public function syncDepartments()
    {
        try {
            $synced = $this->departmentApiService->syncDepartments();
            
            return redirect()->route('holiday-department-configs.index')
                            ->with('success', 'Departments synchronized successfully! ' . count($synced) . ' departments updated.');
        } catch (\Exception $e) {
            \Log::error('Department sync failed: ' . $e->getMessage());
            return redirect()->route('holiday-department-configs.index')
                            ->with('error', 'Failed to sync departments: ' . $e->getMessage());
        }
    }

    /**
     * Sync used holidays count from actual assignments
     */
    public function syncUsedHolidaysPublic(Request $request)
    {
        $financialYear = $request->get('financial_year', $this->getCurrentFinancialYear());
        
        try {
            $this->syncUsedHolidaysCount($financialYear);
            
            return redirect()->route('holiday-department-configs.index', ['financial_year' => $financialYear])
                            ->with('success', 'Used holidays count synchronized successfully!');
        } catch (\Exception $e) {
            return redirect()->route('holiday-department-configs.index', ['financial_year' => $financialYear])
                            ->with('error', 'Failed to sync used holidays: ' . $e->getMessage());
        }
    }

    /**
     * Clean up orphaned configurations (where department no longer exists)
     */
    public function cleanupOrphanedConfigs(Request $request)
    {
        try {
            $orphanedConfigs = DepartmentHolidayConfig::whereDoesntHave('department')->get();
            $count = $orphanedConfigs->count();
            
            if ($count > 0) {
                DepartmentHolidayConfig::whereDoesntHave('department')->delete();
                return redirect()->route('holiday-department-configs.index')
                    ->with('success', "Cleaned up {$count} orphaned configuration(s).");
            } else {
                return redirect()->route('holiday-department-configs.index')
                    ->with('info', 'No orphaned configurations found.');
            }
        } catch (\Exception $e) {
            return redirect()->route('holiday-department-configs.index')
                ->with('error', 'Failed to cleanup orphaned configurations: ' . $e->getMessage());
        }
    }

    /**
     * Sync used holidays count from actual assignments
     */
    private function syncUsedHolidaysCount($financialYear)
    {
        $configs = DepartmentHolidayConfig::where('financial_year', $financialYear)->get();
        
        foreach ($configs as $config) {
            $config->syncUsedHolidays();
        }
    }

    /**
     * Get current financial year
     */
    private function getCurrentFinancialYear()
    {
        $currentDate = now();
        $year = $currentDate->month >= 4 ? $currentDate->year : $currentDate->year - 1;
        return $year . '-' . ($year + 1);
    }

    /**
     * Get financial year options
     */
    private function getFinancialYearOptions()
    {
        $currentFinancialYear = $this->getCurrentFinancialYear();
        $currentYear = intval(explode('-', $currentFinancialYear)[0]); // Extract the starting year
        $years = collect();
        
        for ($i = -2; $i <= 2; $i++) {
            $year = $currentYear + $i;
            $financialYear = $year . '-' . ($year + 1);
            $years->put($financialYear, $year . '-' . ($year + 1));
        }
        
        return $years;
    }
}
