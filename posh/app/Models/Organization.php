<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Organization extends Model
{
    protected $fillable = [
        'name',
        'employee_count',
        'intake_key',
        'hub_tenant_key',
        'employee_source',
        'auth_mode',
        'payroll_synced_at',
        'is_active',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'payroll_synced_at' => 'datetime',
            'settings' => 'array',
        ];
    }

    public function employeeDirectory(): HasMany
    {
        return $this->hasMany(PoshEmployeeDirectory::class);
    }

    public function usesPayrollEmployees(): bool
    {
        return ($this->employee_source ?? 'payroll') === 'payroll';
    }

    public function usesNativeAuth(): bool
    {
        return ($this->auth_mode ?? 'sso') === 'native';
    }

    public function deploymentLabel(): string
    {
        return $this->usesPayrollEmployees()
            ? 'ERP — Payroll linked'
            : 'Standalone POSH';
    }

    public function authModeLabel(): string
    {
        return $this->usesNativeAuth() ? 'POSH login' : 'SSO (Workspace)';
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function icMembers(): HasMany
    {
        return $this->hasMany(PoshIcMember::class);
    }

    public function policies(): HasMany
    {
        return $this->hasMany(PoshPolicy::class);
    }

    /**
     * White-label display name — never show vendor prefixes (e.g. ISARVA) to end customers.
     */
    public function getDisplayNameAttribute(): string
    {
        return self::sanitizeDisplayName($this->name);
    }

    public static function sanitizeDisplayName(?string $name): string
    {
        $name = trim((string) $name);
        if ($name === '') {
            return 'Organization';
        }

        foreach (config('posh.org_name_prefixes_to_strip', []) as $prefix) {
            if ($prefix === '') {
                continue;
            }
            $name = preg_replace('/^' . preg_quote($prefix, '/') . '\s+/iu', '', $name) ?? $name;
        }

        return trim($name) ?: 'Organization';
    }
}
