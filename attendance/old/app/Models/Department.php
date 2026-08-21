<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Department extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'name',
        'code',
        'description',
        'is_active',
        'api_department_id'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function departmentHolidayConfigs()
    {
        return $this->hasMany(DepartmentHolidayConfig::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }
    
    /**
     * Get employees by payroll_department_id (authoritative from payroll API)
     */
    public function payrollEmployees()
    {
        return $this->hasMany(Employee::class, 'payroll_department_id', 'api_department_id');
    }

    public function activeUsers()
    {
        return $this->hasMany(User::class)->whereNotNull('password');
    }

    public function publicHolidays()
    {
        return $this->belongsToMany(PublicHoliday::class, 'department_public_holidays')
                    ->withTimestamps();
    }

    public function leaveTypes()
    {
        return $this->belongsToMany(LeaveType::class, 'department_leave_types')
                    ->withTimestamps();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'code', 'description', 'is_active'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => "Department {$eventName}")
            ->useLogName('department');
    }
}
