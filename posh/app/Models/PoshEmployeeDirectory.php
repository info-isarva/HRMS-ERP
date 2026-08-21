<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PoshEmployeeDirectory extends Model
{
    protected $table = 'posh_employee_directory';

    protected $fillable = [
        'organization_id',
        'name',
        'email',
        'employee_code',
        'department',
        'designation',
        'source',
        'payroll_ref',
        'user_id',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sourceLabel(): string
    {
        return match ($this->source) {
            'payroll' => 'Payroll',
            'posh' => 'POSH',
            default => ucfirst($this->source),
        };
    }
}
