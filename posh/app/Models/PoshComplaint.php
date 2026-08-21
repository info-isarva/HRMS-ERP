<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PoshComplaint extends Model
{
    protected $fillable = [
        'organization_id',
        'case_number',
        'filed_by_user_id',
        'complainant_name',
        'complainant_email',
        'employee_code',
        'department',
        'is_anonymous',
        'filed_by_relation',
        'respondent_name',
        'respondent_type',
        'respondent_department',
        'vs_employer',
        'incident_date',
        'incident_location',
        'description',
        'routed_to',
        'status',
        'operate_step',
        'inquiry_started_at',
        'report_due_at',
        'management_action_due_at',
        'filing_within_deadline',
        'extension_reason',
        'intake_channel',
        'acknowledged_at',
        'closed_at',
        'submitted_at',
        'case_data',
    ];

    protected function casts(): array
    {
        return [
            'is_anonymous' => 'boolean',
            'vs_employer' => 'boolean',
            'incident_date' => 'date',
            'filing_within_deadline' => 'boolean',
            'inquiry_started_at' => 'datetime',
            'report_due_at' => 'datetime',
            'management_action_due_at' => 'datetime',
            'acknowledged_at' => 'datetime',
            'closed_at' => 'datetime',
            'submitted_at' => 'datetime',
            'case_data' => 'array',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function filedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'filed_by_user_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(PoshComplaintLog::class)->orderBy('created_at');
    }

    public function evidence(): HasMany
    {
        return $this->hasMany(PoshComplaintEvidence::class);
    }

    public function isClosed(): bool
    {
        return in_array($this->status, config('posh.closed_statuses'), true);
    }

    public function isOpen(): bool
    {
        return ! $this->isClosed();
    }

    public function displayComplainant(): string
    {
        if ($this->is_anonymous) {
            return 'Anonymous (confidential)';
        }

        return $this->complainant_name ?: 'Complainant';
    }

    public function getCaseData(string $key, mixed $default = null): mixed
    {
        return data_get($this->case_data, $key, $default);
    }

    public function setCaseData(string $key, mixed $value): void
    {
        $data = $this->case_data ?? [];
        data_set($data, $key, $value);
        $this->case_data = $data;
    }
}
