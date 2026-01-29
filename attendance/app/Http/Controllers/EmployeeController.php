<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Department;

class EmployeeController extends Controller
{
    public function index()
    {
        // Fetch employees from employees table (NOT users table)
        $employees = Employee::with('department')->orderBy('name')->get();
        
        return view('employees.index', compact('employees'));
    }

    public function api()
    {
        // Get employees from employees table (NOT users table)
        $employees = Employee::with('department')
            ->select('id', 'employee_id', 'payroll_id', 'name', 'email', 'department_id', 'designation', 'phone', 'date_of_joining', 'date_of_resignation', 'status', 'financial_year')
            ->orderBy('name')
            ->get()
            ->map(function ($employee) {
                return [
                    'id' => $employee->payroll_id ?? $employee->id,
                    'employee_id' => $employee->employee_id,
                    'name' => $employee->name,
                    'email' => $employee->email === 'No email provided' ? null : $employee->email,
                    'department_id' => $employee->department_id,
                    'department_name' => $employee->department ? $employee->department->name : null,
                    'designation' => $employee->designation,
                    'phone' => $employee->phone,
                    'date_of_joining' => $employee->date_of_joining ? $employee->date_of_joining->format('Y-m-d') : null,
                    'date_of_resignation' => $employee->date_of_resignation ? $employee->date_of_resignation->format('Y-m-d') : null,
                    'status' => $employee->status,
                    'financial_year' => $employee->financial_year ?? $this->getCurrentFinancialYear(),
                ];
            });

        return response()->json($employees);
    }

    private function getCurrentFinancialYear()
    {
        $month = now()->month;
        $year = now()->year;
        return $month >= 4 ? "$year-" . ($year + 1) : ($year - 1) . "-$year";
    }
}