<?php

namespace App\Models;

/** @deprecated Legacy Payroll POSH — replaced by ISARVA POSH module (Phase 1+). */

use Illuminate\Database\Eloquent\Model;

class PoshComplaint extends Model
{
    protected $table = 'posh_complaints';

    protected $fillable = [
        'complaint_number',
        'employee_id',
        'complainant_name',
        'complainant_email',
        'is_anonymous',
        'incident_date',
        'incident_location',
        'respondent_name',
        'respondent_department',
        'description',
        'status',
        'resolution_summary',
        'resolved_at',
    ];

    protected $casts = [
        'is_anonymous' => 'boolean',
        'incident_date' => 'date',
        'resolved_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(EmployeeBasicDetail::class, 'employee_id');
    }

    public function logs()
    {
        return $this->hasMany(PoshComplaintLog::class, 'complaint_id')->orderBy('created_at', 'asc');
    }
}
