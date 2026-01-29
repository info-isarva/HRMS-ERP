<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeIncrement extends Model
{
    protected $fillable = [
        'employee_id',
        'type',
        'previous_designation_id',
        'new_designation_id',
        'previous_ctc',
        'new_ctc',
        'increment_amount',
        'increment_percentage',
        'current_salary_structure',
        'new_salary_structure',
        'effective_date',
        'status',
        'processed_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'current_salary_structure' => 'array',
        'new_salary_structure' => 'array',
        'effective_date' => 'date',
        'processed_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(EmployeeBasicDetail::class, 'employee_id');
    }

    public function previousDesignation()
    {
        return $this->belongsTo(PositionType::class, 'previous_designation_id');
    }

    public function newDesignation()
    {
        return $this->belongsTo(PositionType::class, 'new_designation_id');
    }
    
    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }
    
    public function updater()
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }

    /**
     * Check if this increment is the latest one for the employee
     */
    public function isLatest()
    {
        $latest = self::where('employee_id', $this->employee_id)
            ->orderBy('created_at', 'desc')
            ->first();
            
        return $latest && $latest->id === $this->id;
    }
}

