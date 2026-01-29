<?php

namespace App\Http\Controllers;

use App\Models\EmployeeStatus;
use Illuminate\Http\Request;

class EmployeeStatusController extends Controller
{
    // Index - Manage Employee Statuses
    public function index()
    {
        $employeeStatuses = EmployeeStatus::latest()->get();
        return view('masters.employee-statuses.index', compact('employeeStatuses'));
    }    

    // Store Employee Status
    public function store(Request $request)
    {
        $validated = $request->validate([
            'status_name' => 'required|string|max:255|unique:employee_statuses',
            'short_name' => 'nullable|string|max:50|unique:employee_statuses',
            'description' => 'nullable|string',
            'status' => 'required|boolean'
        ], [
            'status_name.unique' => 'An employee status with this name already exists.',
            'short_name.unique' => 'An employee status with this short name already exists.',
        ]);

        EmployeeStatus::create($validated);

        return redirect()->route('form/employee-status/manage')
            ->with('success', 'Employee Status created successfully');
    }

    
    public function getById($id)
    {
        $employeeStatus = EmployeeStatus::findOrFail($id);
        return response()->json($employeeStatus);
    }

    // Update Employee Status
    public function update(Request $request)
    {
        $employeeStatus = EmployeeStatus::findOrFail($request->id);
        
        $validated = $request->validate([
            'status_name' => 'required|string|max:255|unique:employee_statuses,status_name,' . $employeeStatus->id,
            'short_name' => 'nullable|string|max:50|unique:employee_statuses,short_name,' . $employeeStatus->id,
            'description' => 'nullable|string',
            'status' => 'required|boolean'
        ], [
            'status_name.unique' => 'An employee status with this name already exists.',
            'short_name.unique' => 'An employee status with this short name already exists.',
        ]);

        $employeeStatus->update($validated);

        return redirect()->route('form/employee-status/manage')->with('success', 'Employee Status updated successfully.');
    }

    // Delete Employee Status
    public function destroy(Request $request)
    {
        $employeeStatus = EmployeeStatus::findOrFail($request->id);
        $employeeStatus->delete();
        return redirect()->route('form/employee-status/manage')
            ->with('success', 'Employee Status deleted successfully');
    }
}
