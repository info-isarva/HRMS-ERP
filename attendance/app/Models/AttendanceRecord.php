<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class AttendanceRecord extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'payroll_id',
        'employee_id',
        'user_id', // Keep for backward compatibility
        'employee_email',
        'date',
        'status',
        'leave_type_id',
        'leave_application_id',
        'public_holiday_id',
        'attendance_id',
        'check_in_time',
        'check_out_time',
        'total_hours',
        'late_arrival_minutes',
        'early_departure_minutes',
        'overtime_hours',
        'undertime_hours',
        'worked_on_holiday',
        'worked_on_weekend',
        'worked_on_leave',
        'has_biometric_data',
        'data_source',
        'shift_id',
        'scheduled_start_time',
        'scheduled_end_time',
        'is_override',
        'original_status',
        'original_leave_type_id',
        'modified_by',
        'month',
        'year',
        'is_locked',
        'locked_at',
        'locked_by'
    ];

    protected $casts = [
        'date' => 'date',
        'total_hours' => 'decimal:2',
        'late_arrival_minutes' => 'decimal:2',
        'early_departure_minutes' => 'decimal:2',
        'overtime_hours' => 'decimal:2',
        'undertime_hours' => 'decimal:2',
        'worked_on_holiday' => 'boolean',
        'worked_on_weekend' => 'boolean',
        'worked_on_leave' => 'boolean',
        'has_biometric_data' => 'boolean',
        'is_override' => 'boolean',
        'is_locked' => 'boolean',
        'locked_at' => 'datetime',
    ];

    /**
     * Get the user that owns the attendance record.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the employee that owns the attendance record (by payroll_id).
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'payroll_id', 'payroll_id');
    }

    /**
     * Get the employee by email (for backward compatibility).
     */
    public function employeeByEmail()
    {
        return $this->belongsTo(Employee::class, 'employee_email', 'email');
    }

    /**
     * Get the employee by employee_id (fallback for compatibility).
     */
    public function employeeById()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_id');
    }

    /**
     * Get the leave type associated with this attendance record.
     */
    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class);
    }

    /**
     * Get the leave application associated with this attendance record.
     */
    public function leaveApplication()
    {
        return $this->belongsTo(LeaveApplication::class);
    }

    /**
     * Get the public holiday associated with this attendance record.
     */
    public function publicHoliday()
    {
        return $this->belongsTo(PublicHoliday::class);
    }

    /**
     * Get the biometric attendance record associated with this attendance record.
     */
    public function biometricAttendance()
    {
        return $this->belongsTo(Attendance::class, 'attendance_id');
    }

    /**
     * Get the shift associated with this attendance record.
     */
    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    /**
     * Get the user who modified this record.
     */
    public function modifiedBy()
    {
        return $this->belongsTo(User::class, 'modified_by');
    }

    /**
     * Get the user who locked this record.
     */
    public function lockedBy()
    {
        return $this->belongsTo(User::class, 'locked_by');
    }

    /**
     * Scope a query to only include records for a specific month and year.
     */
    public function scopeForMonthYear($query, $month, $year)
    {
        return $query->where('month', $month)->where('year', $year);
    }

    /**
     * Scope a query to only include locked records.
     */
    public function scopeLocked($query)
    {
        return $query->where('is_locked', true);
    }

    /**
     * Scope a query to only include unlocked records.
     */
    public function scopeUnlocked($query)
    {
        return $query->where('is_locked', false);
    }

    /**
     * Scope a query to only include overridden records.
     */
    public function scopeOverridden($query)
    {
        return $query->where('is_override', true);
    }

    /**
     * Get the activity log options.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'user_id', 'date', 'status', 'leave_type_id',
                'is_override', 'original_status', 'original_leave_type_id',
                'is_locked', 'locked_at', 'locked_by'
            ])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => "Attendance record {$eventName}")
            ->useLogName('attendance_record');
    }
}
