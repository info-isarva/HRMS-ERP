<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class PublicHoliday extends Model
{
    use LogsActivity;
    protected $fillable = [
        'name', 
        'description', 
        'date', 
        'financial_year', 
        'type', 
        'status', 
        'is_national', 
        'color',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'date' => 'date',
        'is_national' => 'boolean',
    ];

    /**
     * Get the user who created this holiday
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this holiday
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the departments associated with this holiday
     */
    public function departments()
    {
        return $this->belongsToMany(Department::class, 'department_public_holidays')
                    ->withTimestamps();
    }

    /**
     * Check if this holiday is available for a specific payroll department
     */
    public function isAvailableForPayrollDepartment($payrollDepartmentId)
    {
        return \DB::table('department_public_holidays')
            ->where('public_holiday_id', $this->id)
            ->where('payroll_department_id', $payrollDepartmentId)
            ->exists();
    }

    /**
     * Scope to get holidays available for a specific payroll department
     */
    public function scopeAvailableForPayrollDepartment($query, $payrollDepartmentId)
    {
        return $query->whereHas('departments', function($q) use ($payrollDepartmentId) {
            $q->whereRaw('EXISTS (
                SELECT 1 FROM department_public_holidays dph 
                WHERE dph.public_holiday_id = public_holidays.id 
                AND dph.payroll_department_id = ?
            )', [$payrollDepartmentId]);
        });
    }

    /**
     * Scope to get holidays for a specific financial year
     */
    public function scopeForFinancialYear($query, $year)
    {
        return $query->where('financial_year', $year);
    }

    /**
     * Scope to get active holidays
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to get holidays by type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Get formatted date
     */
    public function getFormattedDateAttribute()
    {
        return $this->date->format('M d, Y');
    }

    /**
     * Get day of week
     */
    public function getDayOfWeekAttribute()
    {
        return $this->date->format('l');
    }

    /**
     * Check if holiday is upcoming
     */
    public function getIsUpcomingAttribute()
    {
        return $this->date->isFuture();
    }

    /**
     * Check if holiday is today
     */
    public function getIsTodayAttribute()
    {
        return $this->date->isToday();
    }

    /**
     * Get financial years available
     */
    public static function getFinancialYears()
    {
        return self::distinct()->pluck('financial_year')->sort()->values();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'description', 'date', 'type', 'status', 'is_national'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => "Public holiday {$eventName}")
            ->useLogName('public_holiday');
    }
}