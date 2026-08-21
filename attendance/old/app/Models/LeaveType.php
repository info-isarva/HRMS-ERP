<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class LeaveType extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'name',
        'code',
        'description',
        'days_count',
        'is_active',
        'financial_year',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'days_count' => 'integer',
    ];

    /**
     * Get the departments that are assigned this leave type.
     */
    public function departments()
    {
        return $this->belongsToMany(Department::class, 'department_leave_types')
                    ->withTimestamps();
    }

    /**
     * Check if this leave type is available for a specific payroll department ID
     */
    public function isAvailableForPayrollDepartment($payrollDepartmentId)
    {
        return \DB::table('department_leave_types')
                    ->where('leave_type_id', $this->id)
                    ->where('payroll_department_id', $payrollDepartmentId)
                    ->exists();
    }

    /**
     * Get leave types available for a specific payroll department ID
     */
    public static function availableForPayrollDepartment($payrollDepartmentId)
    {
        return static::whereExists(function ($query) use ($payrollDepartmentId) {
            $query->select(\DB::raw(1))
                  ->from('department_leave_types')
                  ->whereColumn('department_leave_types.leave_type_id', 'leave_types.id')
                  ->where('department_leave_types.payroll_department_id', $payrollDepartmentId);
        })->active();
    }

    /**
     * Get the leave applications for this leave type.
     */
    public function leaveApplications()
    {
        return $this->hasMany(LeaveApplication::class);
    }

    /**
     * Scope a query to only include active leave types.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to filter by financial year.
     */
    public function scopeForFinancialYear($query, $year)
    {
        return $query->where('financial_year', $year);
    }

    /**
     * Get the activity log options.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'code', 'description', 'days_count', 'is_active', 'financial_year'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => "Leave type {$eventName}")
            ->useLogName('leave_type');
    }
}
