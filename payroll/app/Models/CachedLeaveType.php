<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CachedLeaveType extends Model
{
    protected $table = 'cached_leave_types';

    protected $fillable = [
        'attendance_leave_type_id',
        'leave_type_name',
        'leave_type_code',
        'days_allowed',
        'status',
        'description',
        'financial_year',
        'assigned_departments',
        'is_active',
        'last_synced_at',
    ];

    protected $casts = [
        'assigned_departments' => 'array',
        'is_active' => 'boolean',
        'last_synced_at' => 'datetime',
    ];

    /**
     * Scope to filter by financial year
     */
    public function scopeForFinancialYear($query, $financialYear)
    {
        return $query->where('financial_year', $financialYear);
    }

    /**
     * Scope to get active leave types
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Check if leave type is assigned to a department
     */
    public function isAssignedToDepartment($departmentId)
    {
        $departments = $this->assigned_departments ?: [];
        return collect($departments)->pluck('id')->contains($departmentId);
    }

    /**
     * Get assigned department names
     */
    public function getAssignedDepartmentNamesAttribute()
    {
        $departments = $this->assigned_departments ?: [];
        return collect($departments)->pluck('name')->toArray();
    }

    /**
     * Get assigned department IDs
     */
    public function getAssignedDepartmentIdsAttribute()
    {
        $departments = $this->assigned_departments ?: [];
        return collect($departments)->pluck('id')->toArray();
    }
}