<?php

namespace App\Services;

use App\Models\Central\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
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

        if ($code === '') {
            throw ValidationException::withMessages([
                'company_code' => 'Company code is required.',
            ]);
        }

        $tenant = Tenant::findByCompanyCode($code);

        if (! $tenant) {
            $aliases = [
                'ISARVA' => 'ISARVADEV',
            ];

            if (isset($aliases[$code])) {
                $tenant = Tenant::findByCompanyCode($aliases[$code]);
            }
        }

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
        $module = (string) config('tenant.module', 'workspace');
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
        $companyCode = strtoupper($tenant->company_code);
        session([
            'tenant_id' => $tenant->id,
            'company_code' => $companyCode,
        ]);

        // Cookie fallback keeps tenant context available very early in request lifecycle.
        Cookie::queue('tenant_id', (string) $tenant->id, 525600);
        Cookie::queue('company_code', $companyCode, 525600);
    }

    public function clearSession(): void
    {
        session()->forget(['tenant_id', 'company_code', 'pending_tenant_id', 'pending_company_code']);
        Cookie::queue(Cookie::forget('tenant_id'));
        Cookie::queue(Cookie::forget('company_code'));
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

    public function resolveFromRequest(Request $request): ?Tenant
    {
        if ($tenantId = $request->cookie('tenant_id')) {
            $tenant = Tenant::query()->where('status', 'active')->where('id', (int) $tenantId)->first();
            if ($tenant) {
                return $tenant;
            }
        }

        if ($companyCode = $request->cookie('company_code')) {
            $tenant = Tenant::findByCompanyCode((string) $companyCode);
            if ($tenant) {
                return $tenant;
            }
        }

        return $this->resolveFromSession();
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
