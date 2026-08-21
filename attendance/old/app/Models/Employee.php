<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Employee extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'employee_id',
        'payroll_id',
        'name',
        'email',
        'designation',
        'phone',
        'status',
        'department_id',
        'payroll_department_id',
        'financial_year',
        'date_of_joining',
        'date_of_resignation',
        'reporting_manager_payroll_id',
        'additional_data',
        'exclude_from_payroll',
    ];

    protected $casts = [
        'date_of_joining' => 'date',
        'date_of_resignation' => 'date',
        'additional_data' => 'array',
        'payroll_id' => 'integer',
        'reporting_manager_payroll_id' => 'integer',
        'payroll_department_id' => 'integer',
    ];

    // Relationships
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function financialYear()
    {
        return $this->belongsTo(FinancialYear::class, 'financial_year', 'name');
    }
    
    /**
     * Get the department based on payroll_department_id (authoritative from payroll API)
     */
    public function payrollDepartment()
    {
        return $this->belongsTo(Department::class, 'payroll_department_id', 'api_department_id');
    }

    /**
     * Get available leave types for this employee based on their payroll department ID
     */
    public function availableLeaveTypes()
    {
        if (!$this->payroll_department_id) {
            return LeaveType::active();
        }
        
        return LeaveType::availableForPayrollDepartment($this->payroll_department_id);
    }

    /**
     * Check if a specific leave type is available for this employee
     */
    public function hasAccessToLeaveType($leaveTypeId)
    {
        if (!$this->payroll_department_id) {
            return true; // If no payroll department, allow all leave types
        }
        
        $leaveType = LeaveType::find($leaveTypeId);
        return $leaveType && $leaveType->isAvailableForPayrollDepartment($this->payroll_department_id);
    }

    public function reportingManager()
    {
        // Find manager by payroll_id
        return $this->belongsTo(Employee::class, 'reporting_manager_payroll_id', 'payroll_id');
    }

    public function reportees()
    {
        // Find all employees reporting to this employee
        return $this->hasMany(Employee::class, 'reporting_manager_payroll_id', 'payroll_id');
    }

    public function leaveApplications()
    {
        // Since LeaveApplication uses user_id (from User table) but we want to avoid User table,
        // we'll handle this in the controller using email matching
        return $this->hasMany(LeaveApplication::class, 'user_id', 'id');
    }

    public function publicHolidayApplications()
    {
        return $this->hasMany(PublicHolidayApplication::class, 'user_id', 'id');
    }

    public function attendanceRecords()
    {
        return $this->hasMany(AttendanceRecord::class, 'employee_id', 'employee_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'Active');
    }

    /**
     * Scope for employees who are currently active based on joining and resignation dates
     */
    public function scopeCurrentlyActive($query)
    {
        $today = now()->toDateString();
        return $query->whereDate('date_of_joining', '<=', $today)
                    ->where(function($q) use ($today) {
                        $q->whereNull('date_of_resignation')
                          ->orWhereDate('date_of_resignation', '>', $today);
                    });
    }

    public function scopeByDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    public function scopeByFinancialYear($query, $year)
    {
        return $query->where('financial_year', $year);
    }

    /**
     * Scope to get employees who should have attendance records for a given month/year
     * based on their date of joining and resignation date
     */
    public function scopeForAttendanceMonth($query, $month, $year)
    {
        $startOfMonth = Carbon::createFromDate($year, $month, 1)->startOfDay();
        $endOfMonth = Carbon::createFromDate($year, $month, 1)->endOfMonth();
        
        return $query->where(function($q) use ($startOfMonth, $endOfMonth) {
            // Employee joined before or during the month
            $q->where(function($inner) use ($startOfMonth) {
                $inner->whereDate('date_of_joining', '<=', $startOfMonth->endOfMonth())
                      ->orWhereNull('date_of_joining');
            })
            // AND (no resignation date OR resigned after the start of the month)
            ->where(function($inner) use ($startOfMonth) {
                $inner->whereNull('date_of_resignation')
                      ->orWhereDate('date_of_resignation', '>=', $startOfMonth->startOfDay());
            });
        });
    }

    // Accessors
    public function getIsActiveAttribute()
    {
        return $this->status === 'Active';
    }

    public function getFullNameAttribute()
    {
        return $this->name;
    }

    /**
     * Get available public holidays for this employee based on payroll_department_id
     */
    public function availablePublicHolidays()
    {
        if (!$this->payroll_department_id) {
            return collect();
        }

        return \App\Models\PublicHoliday::availableForPayrollDepartment($this->payroll_department_id);
    }

    /**
     * Check if this employee has access to a specific public holiday
     */
    public function hasAccessToPublicHoliday($publicHoliday)
    {
        if (!$this->payroll_department_id) {
            return false;
        }

        return $publicHoliday->isAvailableForPayrollDepartment($this->payroll_department_id);
    }

    // Activity Logging
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email', 'designation', 'department_id', 'status'])
            ->logOnlyDirty()
            ->dontLogIfAttributesChangedOnly(['updated_at'])
            ->setDescriptionForEvent(fn(string $eventName) => "Employee {$eventName}")
            ->useLogName('employee');
    }
}
