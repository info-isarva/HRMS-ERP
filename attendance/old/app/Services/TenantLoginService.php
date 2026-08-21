<?php

namespace App\Services;

use App\Models\Central\Tenant;
use Illuminate\Validation\ValidationException;

class TenantLoginService
{
    public function __construct(
        private TenantContext $tenantContext,
        private TenantDatabaseManager $databaseManager,
    ) {
    }

    public function findActiveByCompanyCode(string $companyCode): Tenant
    {
        $code = strtoupper(trim($companyCode));

        $aliases = [
            'ISARVA' => 'ISARVADEV',
        ];

        if (isset($aliases[$code])) {
            $code = $aliases[$code];
        }

        if ($code === '') {
            throw ValidationException::withMessages([
                'company_code' => 'Company code is required.',
            ]);
        }

        $tenant = Tenant::findByCompanyCode($code);

        if (! $tenant) {
            throw ValidationException::withMessages([
                'company_code' => 'Invalid company code.',
            ]);
        }

        if (! $tenant->isActive()) {
            throw ValidationException::withMessages([
                'company_code' => 'This company is not active.',
            ]);
        }

        if ($tenant->is_demo && $tenant->isDemoExpired()) {
            throw ValidationException::withMessages([
                'company_code' => 'This demo has expired. Please contact ISARVA to extend access.',
            ]);
        }

        return $tenant;
    }

    public function apply(Tenant $tenant, ?string $host = null, bool $persistSession = false): void
    {
        $module = (string) config('tenant.module', 'attendance');
        $host = $host ?? (request()->getHost() ?? 'localhost');

        $this->tenantContext->setModule($module);
        $this->tenantContext->setTenant($tenant, $host);

        if (config('tenant.switch_database_connection')) {
            $this->databaseManager->switchForTenant($tenant, $module);
        }

        if ($persistSession && ! app()->runningInConsole()) {
            $this->persistSession($tenant);
        }
    }

    public function persistSession(Tenant $tenant): void
    {
        session([
            'tenant_id' => $tenant->id,
            'company_code' => strtoupper($tenant->company_code),
        ]);
    }

    public function clearSession(): void
    {
        session()->forget(['tenant_id', 'company_code', 'pending_tenant_id', 'pending_company_code']);
    }

    public function storePendingCompanyCode(Tenant $tenant): void
    {
        session([
            'pending_tenant_id' => $tenant->id,
            'pending_company_code' => strtoupper($tenant->company_code),
        ]);
    }

    public function resolveFromSession(): ?Tenant
    {
        $tenantId = session('tenant_id');
        $companyCode = session('company_code');

        if (! $tenantId && ! $companyCode) {
            return null;
        }

        if ($tenantId) {
            return Tenant::query()->where('status', 'active')->where('id', $tenantId)->first();
        }

        return Tenant::findByCompanyCode((string) $companyCode);
    }

    public function resolvePendingCompanyCode(): ?Tenant
    {
        if ($tenantId = session('pending_tenant_id')) {
            return Tenant::query()->where('status', 'active')->where('id', $tenantId)->first();
        }

        if ($code = session('pending_company_code')) {
            return Tenant::findByCompanyCode((string) $code);
        }

        return null;
    }
}
