<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EmployeeIncrement;
use App\Models\EmployeeBasicDetail;
use App\Models\PositionType;
use App\Models\Department;

class IncrementController extends Controller
{

    public function index()
    {
        $increments = EmployeeIncrement::with([
            'employee.designationObj', 
            'employee.departmentObj',
            'previousDesignation', 
            'newDesignation',
            'creator',
            'updater'
        ])
            ->orderBy('created_at', 'desc')
            ->get();
            
        $salaryComponents = \App\Models\SalaryComponent::all()->keyBy('id');
        $statutoryComponents = \App\Models\StatutoryComponent::all()->keyBy('id');
        $departments = Department::get();
        $designations = PositionType::where('status', 1)->orderBy('position')->get();
            
        return view('increments.index', compact('increments', 'salaryComponents', 'statutoryComponents', 'departments', 'designations'));
    }

    public function create()
    {
        $employees = EmployeeBasicDetail::where('status', 1)->get();
        $designations = PositionType::where('status', 1)->get();
        
        return view('increments.create', compact('employees', 'designations'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employee_basic_details,id',
            'type' => 'required|in:increment,promotion,both',
            'effective_date' => 'required|date',
            'new_ctc' => 'required|numeric|min:0',
            // 'new_designation_id' required if type is promotion or both
            'new_designation_id' => 'required_if:type,promotion,both|nullable|exists:position_types,id',
        ]);

        $employee = EmployeeBasicDetail::with(['salaryComponents', 'statutoryComponents'])->find($request->employee_id);
        
        // Capture current state
        $currentStructure = [
            'annual_ctc' => $employee->annual_ctc,
            'monthly_ctc' => $employee->monthly_ctc,
            'salary_components' => $employee->salaryComponents->toArray(),
            'statutory_components' => $employee->statutoryComponents->map(function($c) {
                 return [
                     'id' => $c->statutory_component_id,
                     'value' => $c->value,
                     'epf_option' => $c->epf_option,
                     'full_amount_deduct_from_ctc' => $c->full_amount_deduct_from_ctc
                 ];
            })->toArray()
        ];
        
        // Decode JSON from hidden input
        $newStructure = json_decode($request->input('new_salary_structure'), true);

        $increment = EmployeeIncrement::create([
            'employee_id' => $request->employee_id,
            'type' => $request->type,
            'previous_designation_id' => $employee->designation,
            'new_designation_id' => $request->new_designation_id ?? $employee->designation,
            'previous_ctc' => $employee->annual_ctc,
            'new_ctc' => $request->new_ctc,
            'increment_amount' => $request->new_ctc - $employee->annual_ctc,
            'increment_percentage' => $employee->annual_ctc > 0 ? (($request->new_ctc - $employee->annual_ctc) / $employee->annual_ctc) * 100 : 0,
            'current_salary_structure' => $currentStructure,
            'new_salary_structure' => $newStructure,
            'effective_date' => $request->effective_date,
            'status' => 'approved',
            'created_by' => auth()->id(),
        ]);
        
        // Process immediately if effective date is today or past
        if (\Carbon\Carbon::parse($request->effective_date)->startOfDay()->lte(\Carbon\Carbon::today()->startOfDay())) {
            $this->processIncrement($increment);
        }

        return redirect()->route('increments.index')->with('success', 'Increment/Promotion recorded successfully.');
    }
    
    public function edit($id)
    {
        $increment = EmployeeIncrement::with(['employee', 'previousDesignation', 'newDesignation'])->findOrFail($id);
        $employees = EmployeeBasicDetail::where('status', 1)->get();
        $designations = PositionType::where('status', 1)->get();
        
        return view('increments.edit', compact('increment', 'employees', 'designations'));
    }
    
    public function update(Request $request, $id)
    {
        $request->validate([
            'employee_id' => 'required|exists:employee_basic_details,id',
            'type' => 'required|in:increment,promotion,both',
            'effective_date' => 'required|date',
            'new_ctc' => 'required|numeric|min:0',
            'new_designation_id' => 'required_if:type,promotion,both|nullable|exists:position_types,id',
        ]);

        $increment = EmployeeIncrement::findOrFail($id);
        
        $employee = EmployeeBasicDetail::with(['salaryComponents', 'statutoryComponents'])->find($request->employee_id);
        
        // Decode JSON
        $newStructure = json_decode($request->input('new_salary_structure'), true);

        $increment->update([
            'employee_id' => $request->employee_id,
            'type' => $request->type,
            'previous_designation_id' => $employee->designation,
            'new_designation_id' => $request->new_designation_id ?? $employee->designation,
            'previous_ctc' => $employee->annual_ctc,
            'new_ctc' => $request->new_ctc,
            'increment_amount' => $request->new_ctc - $employee->annual_ctc,
            'increment_percentage' => $employee->annual_ctc > 0 ? (($request->new_ctc - $employee->annual_ctc) / $employee->annual_ctc) * 100 : 0,
            'new_salary_structure' => $newStructure,
            'effective_date' => $request->effective_date,
            'updated_by' => auth()->id(),
        ]);
        
        // Re-process if date is valid
        if ($increment->status != 'processed' && \Carbon\Carbon::parse($request->effective_date)->startOfDay()->lte(\Carbon\Carbon::today()->startOfDay())) {
            $this->processIncrement($increment);
        }

        return redirect()->route('increments.index')->with('success', 'Increment/Promotion updated successfully.');
    }
    
    /**
     * Process the increment: update employee master data
     */
    protected function processIncrement($increment)
    {
        $employee = EmployeeBasicDetail::find($increment->employee_id);
        $newStructure = $increment->new_salary_structure;
        
        // Ensure newStructure is array
        if (is_string($newStructure)) {
            $newStructure = json_decode($newStructure, true);
        }
        
        // Update Basic Details
        $updateData = [
            'annual_ctc' => $increment->new_ctc,
            'monthly_ctc' => $increment->new_ctc / 12,
        ];
        
        if (($increment->type == 'promotion' || $increment->type == 'both') && $increment->new_designation_id) {
            $updateData['designation'] = $increment->new_designation_id;
        }
        
        $employee->update($updateData);
        
        // Update Salary Components
        if (isset($newStructure['salary']) && is_array($newStructure['salary'])) {
            // Delete existing
            \App\Models\EmployeeSalaryComponent::where('emp_id', $employee->id)->delete();
            
            // Add new
            foreach ($newStructure['salary'] as $component) {
                \App\Models\EmployeeSalaryComponent::create([
                    'emp_id' => $employee->id,
                    'salary_component_id' => $component['salary_component_id'],
                    'value' => $component['value'],
                    'created_by' => auth()->id()
                ]);
            }
        }
        
        // Update Statutory Components
        if (isset($newStructure['statutory']) && is_array($newStructure['statutory'])) {
            // Delete existing
            \App\Models\EmployeeStatutoryComponent::where('emp_id', $employee->id)->delete();
            
            // Add new
            foreach ($newStructure['statutory'] as $component) {
                \App\Models\EmployeeStatutoryComponent::create([
                    'emp_id' => $employee->id,
                    'statutory_component_id' => $component['statutory_component_id'],
                    'value' => $component['value'],
                    'epf_option' => $component['epf_option'] ?? null,
                    'full_amount_deduct_from_ctc' => $component['full_amount_deduct_from_ctc'] ?? 0,
                    'created_by' => auth()->id()
                ]);
            }
        }
        
        $increment->update([
            'status' => 'processed',
            'processed_at' => now()
        ]);
    }

    /**
     * Revert the latest increment
     */
    public function revert($id)
    {
        $increment = EmployeeIncrement::findOrFail($id);
        
        // Check if it's the latest increment
        if (!$increment->isLatest()) {
            return redirect()->back()->with('error', 'Only the latest increment can be reverted.');
        }
        
        // If it was processed, restore old data
        if ($increment->status == 'processed') {
            $employee = EmployeeBasicDetail::find($increment->employee_id);
            $oldStructure = $increment->current_salary_structure;
            
            // Restore CTC
            $employee->update([
                'annual_ctc' => $increment->previous_ctc,
                'monthly_ctc' => $increment->previous_ctc / 12,
                'designation' => $increment->previous_designation_id // Restore designation
            ]);
            
            // Restore Salary Components
            if (isset($oldStructure['salary_components'])) {
                \App\Models\EmployeeSalaryComponent::where('emp_id', $employee->id)->delete();
                foreach ($oldStructure['salary_components'] as $component) {
                    \App\Models\EmployeeSalaryComponent::create([
                        'emp_id' => $employee->id,
                        'salary_component_id' => $component['salary_component_id'],
                        'value' => $component['value'],
                        'created_by' => auth()->id()
                    ]);
                }
            }
            
            // Restore Statutory Components
            if (isset($oldStructure['statutory_components'])) {
                \App\Models\EmployeeStatutoryComponent::where('emp_id', $employee->id)->delete();
                foreach ($oldStructure['statutory_components'] as $component) {
                    // Fix key names if different in capture vs create
                    \App\Models\EmployeeStatutoryComponent::create([
                        'emp_id' => $employee->id,
                        'statutory_component_id' => $component['id'] ?? $component['statutory_component_id'],
                        'value' => $component['value'],
                        'epf_option' => $component['epf_option'] ?? null,

                        'full_amount_deduct_from_ctc' => $component['full_amount_deduct_from_ctc'] ?? 0,
                        'created_by' => auth()->id()
                    ]);
                }
            }
        }
        
        $increment->delete(); // Soft delete or force delete
        
        return redirect()->route('increments.index')->with('success', 'Increment reverted successfully. Employee salary restored.');
    }

    public function getEmployeeDetails($id)
    {
        $employee = EmployeeBasicDetail::with([
            'designationObj', 
            'salaryComponents.salaryComponent', 
            'statutoryComponents.statutoryComponent'
        ])->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => $employee
        ]);
    }

    public function getHistoryPartial($employeeId)
    {
        $increments = EmployeeIncrement::with('previousDesignation', 'newDesignation', 'creator', 'updater')
            ->where('employee_id', $employeeId)
            ->orderBy('created_at', 'desc') // Latest first
            ->get();
            
        $employee = EmployeeBasicDetail::with('designationObj')->find($employeeId);
        
        // Wrap in array structure expected by the partial for loop
        // The partial expects $employeeIncrements[$empId]... or we can adjust how we pass it.
        // Actually, the partial view `_history_modal.blade.php` iterates over $employeeIncrements.
        // Let's adapt it or create a cleaner inner partial. 
        // For simplicity, let's reuse `_history_modal.blade.php` but we need to structure data exactly as it expects
        // OR better: Create a new simple partial for just the TABLE CONTENT, or mock the structure.
        
        // Let's create a simpler view for this AJAX purpose to avoid complex nesting issues or Create a standard structure.
        
        return view('increments._history_table', compact('increments', 'employee'));
    }
}

