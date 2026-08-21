<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PoshIcMember extends Model
{
    protected $fillable = [
        'organization_id',
        'name',
        'employee_code',
        'department',
        'designation',
        'ic_role',
        'member_origin',
        'employee_directory_id',
        'contact_number',
        'email',
        'is_woman',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_woman' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function employeeDirectory(): BelongsTo
    {
        return $this->belongsTo(PoshEmployeeDirectory::class, 'employee_directory_id');
    }

    public function isExternal(): bool
    {
        return $this->member_origin === 'external'
            || $this->ic_role === 'external_member';
    }

    public function originLabel(): string
    {
        if ($this->isExternal()) {
            return 'External · not from payroll';
        }

        return $this->employeeDirectory?->sourceLabel() ?? 'Internal';
    }

    public function roleLabel(): string
    {
        return config('posh.ic_roles.' . $this->ic_role, $this->ic_role);
    }
}
