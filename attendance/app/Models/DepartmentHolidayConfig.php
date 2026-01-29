<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DepartmentHolidayConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'department_id',
        'payroll_department_id',
        'financial_year',
        'allowed_holidays',
        'used_holidays',
        'fixed_public_holidays',
        'flexible_public_holidays',
        'is_active',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'allowed_holidays' => 'integer',
        'used_holidays' => 'integer',
        'fixed_public_holidays' => 'integer',
        'flexible_public_holidays' => 'integer',
        'payroll_department_id' => 'integer'
    ];

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName()
    {
        return 'id';
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function getRemainingHolidaysAttribute()
    {
        return max(0, $this->allowed_holidays - $this->used_holidays);
    }

    public function getUsagePercentageAttribute()
    {
        if ($this->allowed_holidays == 0) return 0;
        return round(($this->used_holidays / $this->allowed_holidays) * 100, 2);
    }

    public function getEmployeeCountAttribute()
    {
        return $this->department ? $this->department->employees()->active()->count() : 0;
    }

    public function getTotalAvailableHolidaysAttribute()
    {
        return $this->remaining_holidays * $this->employee_count;
    }

    public function scopeForFinancialYear($query, $year)
    {
        return $query->where('financial_year', $year);
    }

    public function scopeForPayrollDepartment($query, $payrollDepartmentId)
    {
        return $query->where('payroll_department_id', $payrollDepartmentId);
    }

    public function syncUsedHolidays()
    {
        // Only count fixed holidays for the used_holidays count
        $actualUsedHolidays = \DB::table('department_public_holidays')
            ->join('public_holidays', 'public_holidays.id', '=', 'department_public_holidays.public_holiday_id')
            ->where('department_public_holidays.department_id', $this->department_id)
            ->where('public_holidays.financial_year', $this->financial_year)
            ->where('public_holidays.status', 'active')
            ->where('public_holidays.type', 'fixed') // Only count fixed holidays
            ->count();
        
        $this->update(['used_holidays' => $actualUsedHolidays]);
        return $actualUsedHolidays;
    }

    /**
     * Get the count of used fixed holidays
     */
    public function getUsedFixedHolidaysAttribute()
    {
        return \DB::table('department_public_holidays')
            ->join('public_holidays', 'public_holidays.id', '=', 'department_public_holidays.public_holiday_id')
            ->where('department_public_holidays.department_id', $this->department_id)
            ->where('public_holidays.financial_year', $this->financial_year)
            ->where('public_holidays.status', 'active')
            ->where('public_holidays.type', 'fixed')
            ->count();
    }

    /**
     * Get the count of used flexible holidays
     */
    public function getUsedFlexibleHolidaysAttribute()
    {
        return \DB::table('department_public_holidays')
            ->join('public_holidays', 'public_holidays.id', '=', 'department_public_holidays.public_holiday_id')
            ->where('department_public_holidays.department_id', $this->department_id)
            ->where('public_holidays.financial_year', $this->financial_year)
            ->where('public_holidays.status', 'active')
            ->where('public_holidays.type', 'flexible')
            ->count();
    }

    /**
     * Get remaining fixed holidays
     */
    public function getRemainingFixedHolidaysAttribute()
    {
        return max(0, $this->fixed_public_holidays - $this->used_fixed_holidays);
    }

    /**
     * Check if department can add more fixed holidays
     */
    public function canAddFixedHoliday()
    {
        return $this->remaining_fixed_holidays > 0;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
