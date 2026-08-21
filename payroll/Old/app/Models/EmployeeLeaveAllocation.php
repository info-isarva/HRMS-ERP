<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeLeaveAllocation extends Model
{
    use SoftDeletes;

    protected $table = 'employee_leave_allocations';

    protected $fillable = [
        'emp_id',
        'attendance_leave_type_id',
        'leave_type_name',
        'leave_type_code',
        'allocated_days',
        'override_days',
        'effective_days',
        'is_pro_rated',
        'pro_rated_factor',
        'financial_year',
        'is_manual_override',
        'department_assignment',
        'description',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'allocated_days' => 'decimal:2',
        'override_days' => 'decimal:2',
        'effective_days' => 'decimal:2',
        'pro_rated_factor' => 'decimal:4',
        'is_pro_rated' => 'boolean',
        'is_manual_override' => 'boolean',
        'department_assignment' => 'array',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * Get the employee that owns this leave allocation
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(EmployeeBasicDetail::class, 'emp_id');
    }

    /**
     * Get the user who created this allocation
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Get the user who last updated this allocation
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }

    /**
     * Scope to filter by financial year
     */
    public function scopeForFinancialYear($query, $financialYear)
    {
        return $query->where('financial_year', $financialYear);
    }

    /**
     * Scope to filter by employee
     */
    public function scopeForEmployee($query, $employeeId)
    {
        return $query->where('emp_id', $employeeId);
    }

    /**
     * Scope to get active allocations
     */
    public function scopeActive($query)
    {
        return $query->whereNull('deleted_at');
    }

    /**
     * Scope to get manual overrides
     */
    public function scopeManualOverrides($query)
    {
        return $query->where('is_manual_override', true);
    }

    /**
     * Scope to get pro-rated allocations
     */
    public function scopeProRated($query)
    {
        return $query->where('is_pro_rated', true);
    }

    /**
     * Calculate effective days based on override or allocated days
     */
    public function calculateEffectiveDays()
    {
        if ($this->is_manual_override && $this->override_days !== null) {
            return $this->override_days;
        }

        return $this->allocated_days;
    }

    /**
     * Check if this allocation has been manually overridden
     */
    public function isOverridden(): bool
    {
        return $this->is_manual_override && $this->override_days !== null;
    }

    /**
     * Get the display value for days (override if available, otherwise allocated)
     */
    public function getDisplayDaysAttribute()
    {
        return $this->isOverridden() ? $this->override_days : $this->allocated_days;
    }

    /**
     * Get pro-rating information
     */
    public function getProRatingInfoAttribute()
    {
        if (!$this->is_pro_rated) {
            return null;
        }

        return [
            'factor' => $this->pro_rated_factor,
            'percentage' => $this->pro_rated_factor ? round($this->pro_rated_factor * 100, 1) : 0,
            'original_days' => $this->pro_rated_factor ? round($this->allocated_days / $this->pro_rated_factor, 2) : $this->allocated_days,
        ];
    }

    /**
     * Update effective days when allocation changes
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($allocation) {
            $allocation->effective_days = $allocation->calculateEffectiveDays();
        });
    }

    /**
     * Create or update allocation for an employee
     */
    public static function createOrUpdateAllocation($employeeId, $leaveTypeData, $financialYear, $userId = null)
    {
        $allocation = static::updateOrCreate(
            [
                'emp_id' => $employeeId,
                'attendance_leave_type_id' => $leaveTypeData['id'],
                'financial_year' => $financialYear,
            ],
            [
                'leave_type_name' => $leaveTypeData['leave_type_name'],
                'leave_type_code' => $leaveTypeData['leave_type_code'],
                'allocated_days' => $leaveTypeData['allocated_days'],
                'override_days' => $leaveTypeData['override_days'] ?? null,
                'is_pro_rated' => $leaveTypeData['is_pro_rated'] ?? false,
                'pro_rated_factor' => $leaveTypeData['pro_rated_factor'] ?? null,
                'is_manual_override' => isset($leaveTypeData['override_days']) && $leaveTypeData['override_days'] !== null,
                'department_assignment' => $leaveTypeData['department_assignment'] ?? null,
                'description' => $leaveTypeData['description'] ?? null,
                'updated_by' => $userId ?: auth()->id(),
            ]
        );

        // Calculate and save effective days
        $allocation->effective_days = $allocation->calculateEffectiveDays();
        $allocation->save();

        return $allocation;
    }

    /**
     * Get summary of leave allocations for an employee
     */
    public static function getEmployeeLeaveSummary($employeeId, $financialYear)
    {
        $allocations = static::forEmployee($employeeId)
            ->forFinancialYear($financialYear)
            ->active()
            ->get();

        return [
            'total_leave_types' => $allocations->count(),
            'total_allocated_days' => $allocations->sum('allocated_days'),
            'total_effective_days' => $allocations->sum('effective_days'),
            'manual_overrides_count' => $allocations->where('is_manual_override', true)->count(),
            'pro_rated_count' => $allocations->where('is_pro_rated', true)->count(),
            'allocations' => $allocations->map(function ($allocation) {
                return [
                    'id' => $allocation->id,
                    'leave_type_name' => $allocation->leave_type_name,
                    'leave_type_code' => $allocation->leave_type_code,
                    'allocated_days' => $allocation->allocated_days,
                    'override_days' => $allocation->override_days,
                    'effective_days' => $allocation->effective_days,
                    'is_manual_override' => $allocation->is_manual_override,
                    'is_pro_rated' => $allocation->is_pro_rated,
                    'pro_rating_info' => $allocation->pro_rating_info,
                ];
            }),
        ];
    }

    /**
     * Bulk update allocations for an employee
     */
    public static function bulkUpdateAllocations($employeeId, $allocationsData, $financialYear, $userId = null)
    {
        $updatedAllocations = [];
        
        foreach ($allocationsData as $allocationData) {
            $allocation = static::createOrUpdateAllocation(
                $employeeId,
                $allocationData,
                $financialYear,
                $userId
            );
            $updatedAllocations[] = $allocation;
        }

        return $updatedAllocations;
    }
}