<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeBasicDetail extends Model
{
    use SoftDeletes;

    protected $table = 'employee_basic_details';

    protected $fillable = [
        'employee_id',
        'unique_id',
        'location_id',
        'reporting_manager_id',
        'name',
        'email',
        'contact_number',
        'date_of_birth',
        'gender',
        'marital_status',
        'designation',
        'department',
        'date_of_joining',
        'date_of_resignation',
        'status',
        'role',
        'ot_status',
        'ot_per_hour',
        'incentive_status',
        'incentive_per_month',
        'exclude_from_payroll',
        'enable_self_portal',
        'enable_payroll',
        'profile_image',
        'created_by',
        'updated_by',
        'annual_ctc',
        'monthly_ctc',
    ];
    public function personalDetail()
    {
        return $this->hasOne(EmployeePersonalDetail::class, 'emp_id', 'id');
    }

    public function employeeDocument()
    {
        return $this->hasMany(EmployeeDocument::class, 'emp_id', 'id');
    }

    public function bankDetail()
    {
        return $this->hasOne(EmployeeBankDetail::class, 'emp_id', 'id');
    }

    public function statutoryComponents()
    {
        return $this->hasMany(EmployeeStatutoryComponent::class, 'emp_id', 'id');
    }

    public function salaryComponents()
    {
        return $this->hasMany(EmployeeSalaryComponent::class, 'emp_id', 'id');
    }

    public function leaveAllocations()
    {
        return $this->hasMany(EmployeeLeaveAllocation::class, 'emp_id', 'id');
    }

    public function currentFinancialYearLeaves()
    {
        $currentFY = \App\Helpers\FinancialYearHelper::getCurrentFinancialYear();
        if (!$currentFY) {
            return $this->leaveAllocations()->whereRaw('1 = 0'); // Return empty relation
        }
        
        return $this->leaveAllocations()
                    ->where('financial_year', $currentFY->name)
                    ->whereNull('deleted_at');
    }

    public function advances()
    {
        return $this->hasMany(EmployeeAdvance::class, 'employee_id', 'id');
    }

    public function activeAdvances()
    {
        return $this->hasMany(EmployeeAdvance::class, 'employee_id', 'id')->where('status', 'active');
    }

    public function advanceDeductions()
    {
        return $this->hasManyThrough(EmployeeAdvanceDeduction::class, EmployeeAdvance::class, 'employee_id', 'advance_id', 'id', 'id');
    }

    /**
     * Get total advance deducted for a specific month/year
     */
    public function getTotalAdvanceDeductionForMonth($month, $year)
    {
        $total = 0;
        foreach ($this->activeAdvances as $advance) {
            $total += $advance->getDeductionForMonth($month, $year);
        }
        return $total;
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1); // 1 = Active status
    }

    public function reportingManager()
    {
        return $this->belongsTo(EmployeeBasicDetail::class, 'reporting_manager_id');
    }
    /**
     * Get the user associated with the employee.
     */
    public function user()
    {
        return $this->hasOne(\App\Models\User::class, 'employee_id');
    }

    /**
     * Get the designation object associated with the employee
     */
    public function designationObj()
    {
        return $this->belongsTo(\App\Models\PositionType::class, 'designation');
    }

    /**
     * Get the department object associated with the employee
     */
    public function departmentObj()
    {
        return $this->belongsTo(\App\Models\Department::class, 'department');
    }

    /**
     * Get the role object associated with the employee
     */
    public function roleObj()
    {
        return $this->belongsTo(\App\Models\Role::class, 'role');
    }

    /**
     * Get the week off configuration for the employee
     */
    public function weekOff()
    {
        return $this->hasOne(EmployeeWeekOff::class, 'employee_id');
    }

    /**
     * Get the location object associated with the employee
     */
    public function locationObj()
    {
        return $this->belongsTo(\App\Models\Location::class, 'location_id');
    }

    /**
     * Get the exit details associated with the employee
     */
    public function exitDetails()
    {
        return $this->hasMany(EmployeeExitDetail::class, 'emp_id', 'id');
    }

    /**
     * Get the employment history associated with the employee
     */
    public function employmentHistory()
    {
        return $this->hasMany(EmploymentHistory::class, 'emp_id', 'id');
    }
}
