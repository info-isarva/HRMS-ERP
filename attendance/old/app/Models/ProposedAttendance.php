<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProposedAttendance extends Model
{
    protected $table = 'proposed_attendance';

    protected $fillable = [
        'employee_payroll_id',
        'date',
        'check_in',
        'check_out',
        'total_hours',
        'status',
        'source_status',
        'is_overridden',
        'overridden_by',
        'notes',
        'month_year'
    ];

    protected $casts = [
        'date' => 'date',
        'is_overridden' => 'boolean',
        'total_hours' => 'decimal:2'
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_payroll_id', 'payroll_id');
    }
}
