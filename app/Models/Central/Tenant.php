<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    protected $connection = 'central';

    protected $fillable = [
        'company_code',
        'name',
        'workspace_domain',
        'payroll_domain',
        'attendance_domain',
        'crm_domain',
        'workspace_database',
        'payroll_database',
        'attendance_database',
        'crm_database',
        'status',
        'is_demo',
        'demo_expires_at',
        'demo_admin_email',
        'seed_profile',
        'contact_name',
        'internal_notes',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
            'is_demo' => 'boolean',
            'demo_expires_at' => 'datetime',
        ];
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isDemoExpired(): bool
    {
        if (! $this->is_demo || ! $this->demo_expires_at) {
            return false;
        }

        return $this->demo_expires_at->isPast();
    }

    public function demoDaysRemaining(): ?int
    {
        if (! $this->is_demo || ! $this->demo_expires_at) {
            return null;
        }

        return (int) now()->startOfDay()->diffInDays($this->demo_expires_at->endOfDay(), false);
    }

    public function demoStatusLabel(): string
    {
        if (! $this->is_demo) {
            return 'Production';
        }

        if ($this->status === 'inactive') {
            return 'Expired';
        }

        if ($this->isDemoExpired()) {
            return 'Expired';
        }

        $days = $this->demoDaysRemaining();

        if ($days !== null && $days <= 3) {
            return 'Ending soon';
        }

        return 'Active demo';
    }

    public function scopeDemos($query)
    {
        return $query->where('is_demo', true);
    }

    public function databaseForModule(string $module): ?string
    {
        return match ($module) {
            'workspace' => $this->workspace_database,
            'payroll' => $this->payroll_database,
            'attendance' => $this->attendance_database,
            'crm' => $this->crm_database,
            default => null,
        };
    }

    public static function findByDomain(string $host): ?self
    {
        $host = strtolower(trim($host));

        return static::query()
            ->where('status', 'active')
            ->where(function ($q) use ($host) {
                $q->where('workspace_domain', $host)
                    ->orWhere('payroll_domain', $host)
                    ->orWhere('attendance_domain', $host)
                    ->orWhere('crm_domain', $host);
            })
            ->first();
    }

    public static function findByCompanyCode(string $code): ?self
    {
        return static::query()
            ->where('company_code', strtoupper(trim($code)))
            ->where('status', 'active')
            ->first();
    }
}
