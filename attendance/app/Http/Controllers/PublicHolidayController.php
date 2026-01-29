<?php
namespace App\Http\Controllers;

use App\Models\PublicHoliday;
use App\Models\Department;
use App\Models\DepartmentHolidayConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PublicHolidayController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin,super_admin')->except(['index', 'show']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $selectedYear = $request->get('financial_year', $this->getCurrentFinancialYear());
        $statusFilter = $request->get('status', 'all');
        $typeFilter = $request->get('type', 'all');
        
        $query = PublicHoliday::with(['creator', 'updater'])
            ->forFinancialYear($selectedYear)
            ->orderBy('date', 'asc');

        if ($statusFilter !== 'all') {
            $query->where('status', $statusFilter);
        }

        if ($typeFilter !== 'all') {
            $query->ofType($typeFilter);
        }

        $holidays = $query->get();
        $financialYears = PublicHoliday::getFinancialYears();
        
        // Add current year if not exists
        if (!$financialYears->contains($selectedYear)) {
            $financialYears->push($selectedYear);
            $financialYears = $financialYears->sort()->values();
        }

        $stats = [
            'total' => $holidays->count(),
            'active' => $holidays->where('status', 'active')->count(),
            'upcoming' => $holidays->where('status', 'active')->filter(function($holiday) {
                return $holiday->date->isFuture();
            })->count(),
            'fixed' => $holidays->where('type', 'fixed')->count(),
            'flexible' => $holidays->where('type', 'flexible')->count(),
        ];

        return view('public-holidays.index', compact(
            'holidays', 
            'financialYears', 
            'selectedYear', 
            'statusFilter', 
            'typeFilter', 
            'stats'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $financialYears = $this->getFinancialYearOptions();
        $currentYear = $this->getCurrentFinancialYear();
        
        // Get selected year from request or default to current year
        $selectedYear = $request->get('financial_year', $currentYear);
        
        // Get departments with their holiday configurations for the selected year
        $departments = \App\Models\Department::active()
            ->with(['departmentHolidayConfigs' => function($query) use ($selectedYear) {
                $query->where('financial_year', $selectedYear)->where('is_active', true);
            }])
            ->get();
        
        return view('public-holidays.create', compact('financialYears', 'currentYear', 'selectedYear', 'departments'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'date' => 'required|date',
            'financial_year' => 'required|string',
            'type' => 'required|in:fixed,flexible',
            'status' => 'required|in:active,inactive',
            'is_national' => 'boolean',
            'color' => ['required', 'string', function ($attribute, $value, $fail) {
                if (!preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $value)) {
                    $fail('The ' . $attribute . ' must be a valid hex color code.');
                }
            }],
            'departments' => 'required|array|min:1',
            'departments.*' => 'exists:departments,id',
        ]);

        // Check if holiday already exists for this date and financial year
        $existingHoliday = PublicHoliday::where('date', $request->date)
            ->where('financial_year', $request->financial_year)
            ->first();

        if ($existingHoliday) {
            return back()->withErrors(['date' => 'A holiday already exists on this date for the selected financial year.'])
                        ->withInput();
        }

        // Validate department holiday limits based on type
        $departmentConfigs = \App\Models\DepartmentHolidayConfig::whereIn('department_id', $request->departments)
            ->where('financial_year', $request->financial_year)
            ->where('is_active', true)
            ->get();

        $errors = [];
        foreach ($request->departments as $departmentId) {
            $config = $departmentConfigs->where('department_id', $departmentId)->first();
            if (!$config) {
                $department = \App\Models\Department::find($departmentId);
                $errors[] = "No holiday configuration found for {$department->name} in {$request->financial_year}.";
                continue;
            }
            
            // Only validate limits for fixed holidays
            if ($request->type === 'fixed') {
                // Count existing fixed holidays for this department and year
                $existingFixedHolidays = PublicHoliday::whereHas('departments', function($query) use ($departmentId) {
                    $query->where('departments.id', $departmentId);
                })
                ->where('financial_year', $request->financial_year)
                ->where('type', 'fixed')
                ->where('status', 'active')
                ->count();
                
                if ($existingFixedHolidays >= $config->fixed_public_holidays) {
                    $errors[] = "{$config->department->name} has already reached its fixed holiday limit ({$config->fixed_public_holidays}).";
                }
            }
            // For flexible holidays, no limit check is needed
        }

        if (!empty($errors)) {
            return back()->withErrors(['departments' => implode(' ', $errors)])->withInput();
        }

        $publicHoliday = PublicHoliday::create([
            'name' => $request->name,
            'description' => $request->description,
            'date' => $request->date,
            'financial_year' => $request->financial_year,
            'type' => $request->type,
            'status' => $request->status,
            'is_national' => $request->boolean('is_national'),
            'color' => $request->color,
            'created_by' => Auth::id(),
        ]);

        // Attach departments and update used holidays count
        $publicHoliday->departments()->sync($request->departments);
        
        // Update payroll_department_id in department_public_holidays table
        $this->updatePayrollDepartmentIds($publicHoliday->id, $request->departments);
        
        // Update used holidays count for each department (only for fixed holidays)
        if ($request->type === 'fixed') {
            foreach ($request->departments as $departmentId) {
                $config = $departmentConfigs->where('department_id', $departmentId)->first();
                if ($config) {
                    $config->increment('used_holidays');
                }
            }
        }

        return redirect()->route('public-holidays.index', ['financial_year' => $request->financial_year])
                        ->with('success', 'Public holiday created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(PublicHoliday $publicHoliday)
    {
        $publicHoliday->load(['creator', 'updater']);
        return view('public-holidays.show', compact('publicHoliday'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PublicHoliday $publicHoliday)
    {
        $financialYears = $this->getFinancialYearOptions();
        
        // Get departments with their holiday configurations for the holiday's financial year
        $departments = \App\Models\Department::active()
            ->with(['departmentHolidayConfigs' => function($query) use ($publicHoliday) {
                $query->where('financial_year', $publicHoliday->financial_year)->where('is_active', true);
            }])
            ->get();
        
        // Get currently selected departments for this holiday
        $selectedDepartments = $publicHoliday->departments()->pluck('departments.id')->toArray();
        
        return view('public-holidays.edit', compact('publicHoliday', 'financialYears', 'departments', 'selectedDepartments'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PublicHoliday $publicHoliday)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'date' => 'required|date',
            'status' => 'required|in:active,inactive',
            'is_national' => 'boolean',
            'color' => ['required', 'string', function ($attribute, $value, $fail) {
                if (!preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $value)) {
                    $fail('The ' . $attribute . ' must be a valid hex color code.');
                }
            }],
            'departments' => 'required|array|min:1',
            'departments.*' => 'integer|exists:departments,id',
        ]);

        // Prevent changing type after creation
        if ($request->has('type') && $request->type !== $publicHoliday->type) {
            return back()->withErrors(['type' => 'Holiday type cannot be changed after creation.'])
                        ->withInput();
        }

        // Check if holiday already exists for this date and financial year (excluding current)
        $existingHoliday = PublicHoliday::where('date', $request->date)
            ->where('financial_year', $publicHoliday->financial_year)
            ->where('id', '!=', $publicHoliday->id)
            ->first();

        if ($existingHoliday) {
            return back()->withErrors(['date' => 'Another holiday already exists on this date for this financial year.'])
                        ->withInput();
        }

        // Validate selected departments have available quota (only for fixed holidays)
        $selectedDepartments = collect($request->departments);
        $departmentConfigs = DepartmentHolidayConfig::whereIn('department_id', $selectedDepartments)
            ->where('financial_year', $publicHoliday->financial_year)
            ->where('is_active', true)
            ->get();

        // Only validate limits for fixed holidays
        if ($publicHoliday->type === 'fixed') {
            // Check if any selected department doesn't have available quota
            foreach ($selectedDepartments as $departmentId) {
                $config = $departmentConfigs->where('department_id', $departmentId)->first();
                if (!$config) {
                    $department = Department::find($departmentId);
                    return back()->withErrors(['departments' => "No holiday configuration found for department '{$department->name}'."])
                                ->withInput();
                }
                
                // Count existing fixed holidays for this department and year (excluding current holiday)
                $existingFixedHolidays = PublicHoliday::whereHas('departments', function($query) use ($departmentId) {
                    $query->where('departments.id', $departmentId);
                })
                ->where('financial_year', $publicHoliday->financial_year)
                ->where('type', 'fixed')
                ->where('status', 'active')
                ->where('id', '!=', $publicHoliday->id)
                ->count();
                
                if ($existingFixedHolidays >= $config->fixed_public_holidays) {
                    $department = Department::find($departmentId);
                    return back()->withErrors(['departments' => "Department '{$department->name}' has already reached its fixed holiday limit ({$config->fixed_public_holidays})."])
                                ->withInput();
                }
            }
        }
        // For flexible holidays, no limit check is needed

        // Get currently assigned departments
        $currentDepartments = $publicHoliday->departments()->pluck('departments.id')->toArray();
        $newDepartments = $request->departments;
        
        // Find departments being removed and added
        $departmentsToRemove = array_diff($currentDepartments, $newDepartments);
        $departmentsToAdd = array_diff($newDepartments, $currentDepartments);
        
        $publicHoliday->update([
            'name' => $request->name,
            'description' => $request->description,
            'date' => $request->date,
            'status' => $request->status,
            'is_national' => $request->boolean('is_national'),
            'color' => $request->color,
            'updated_by' => Auth::id(),
        ]);

        // Update department associations
        $publicHoliday->departments()->sync($request->departments);
        
        // Update payroll_department_id in department_public_holidays table
        $this->updatePayrollDepartmentIds($publicHoliday->id, $request->departments);
        
        // Update used holidays counts (only for fixed holidays)
        if ($publicHoliday->type === 'fixed') {
            // Decrement for removed departments
            if (!empty($departmentsToRemove)) {
                DepartmentHolidayConfig::whereIn('department_id', $departmentsToRemove)
                    ->where('financial_year', $publicHoliday->financial_year)
                    ->where('used_holidays', '>', 0)
                    ->decrement('used_holidays');
            }
            
            // Increment for added departments
            if (!empty($departmentsToAdd)) {
                DepartmentHolidayConfig::whereIn('department_id', $departmentsToAdd)
                    ->where('financial_year', $publicHoliday->financial_year)
                    ->increment('used_holidays');
            }
        }

        return redirect()->route('public-holidays.index', ['financial_year' => $publicHoliday->financial_year])
                        ->with('success', 'Public holiday updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PublicHoliday $publicHoliday)
    {
        $financialYear = $publicHoliday->financial_year;
        
        // Get assigned departments before deletion
        $assignedDepartments = $publicHoliday->departments()->pluck('departments.id')->toArray();
        
        // Decrement used holidays for assigned departments (only for fixed holidays)
        if (!empty($assignedDepartments) && $publicHoliday->type === 'fixed') {
            DepartmentHolidayConfig::whereIn('department_id', $assignedDepartments)
                ->where('financial_year', $financialYear)
                ->where('used_holidays', '>', 0)
                ->decrement('used_holidays');
        }
        
        $publicHoliday->delete();

        return redirect()->route('public-holidays.index', ['financial_year' => $financialYear])
                        ->with('success', 'Public holiday deleted successfully!');
    }

    /**
     * Toggle holiday status
     */
    public function toggleStatus(PublicHoliday $publicHoliday)
    {
        $publicHoliday->update([
            'status' => $publicHoliday->status === 'active' ? 'inactive' : 'active',
            'updated_by' => Auth::id(),
        ]);

        return back()->with('success', 'Holiday status updated successfully!');
    }

    /**
     * Get current financial year
     */
    private function getCurrentFinancialYear()
    {
        $month = now()->month;
        $year = now()->year;
        return $month >= 4 ? "$year-" . ($year + 1) : ($year - 1) . "-$year";
    }

    /**
     * Get financial year options for dropdown
     */
    private function getFinancialYearOptions()
    {
        $options = [];
        $currentYear = now()->year;
        
        // Generate 5 years back and 5 years forward
        for ($i = -2; $i <= 5; $i++) {
            $startYear = $currentYear + $i;
            $endYear = $startYear + 1;
            $options["$startYear-$endYear"] = "FY $startYear-$endYear";
        }
        
        return $options;
    }

    /**
     * Update payroll_department_id in department_public_holidays table
     */
    private function updatePayrollDepartmentIds($publicHolidayId, $departmentIds)
    {
        foreach ($departmentIds as $departmentId) {
            $department = \App\Models\Department::find($departmentId);
            if ($department && $department->api_department_id) {
                \DB::table('department_public_holidays')
                    ->where('public_holiday_id', $publicHolidayId)
                    ->where('department_id', $departmentId)
                    ->update(['payroll_department_id' => $department->api_department_id]);
            }
        }
    }
}