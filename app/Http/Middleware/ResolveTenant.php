<?php

namespace App\Http\Middleware;

use App\Models\Central\Tenant;
use App\Services\TenantContext;
use App\Services\TenantDatabaseManager;
use App\Services\TenantLoginService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;

class ResolveTenant
{
    public function __construct(
        private TenantContext $tenantContext,
        private TenantDatabaseManager $databaseManager,
        private TenantLoginService $tenantLogin,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $this->tenantContext->setModule((string) config('tenant.module', 'workspace'));

        if ($this->shouldBypass($request)) {
            return $next($request);
        }

        if (! config('tenant.resolve_from_session')
            && ! config('tenant.resolve_tenant_from_domain')
            && ! config('tenant.resolve_from_jwt')) {
            return $next($request);
        }

        $tenant = null;
        $source = null;

        if (config('tenant.resolve_from_session')) {
            $tenant = $this->tenantLogin->resolveFromRequest($request);
            $source = $tenant ? 'session_or_cookie' : null;
        }

        if (! $tenant && $request->is('api/*') && config('tenant.resolve_from_jwt')) {
            $tenant = $this->tenantFromJwt($request);
            $source = 'jwt';
        }

        if (! $tenant && config('tenant.resolve_tenant_from_domain')) {
            $tenant = $this->tenantFromDomain($request);
            $source = 'domain';
        }

        if (! $tenant) {
            return $next($request);
        }

        return $this->continueWithTenant($request, $next, $tenant, $source);
    }

    private function continueWithTenant(Request $request, Closure $next, Tenant $tenant, string $source): Response
    {
        $host = strtolower($request->getHost());

        $this->tenantContext->setTenant($tenant, $host);
        $request->attributes->set('tenant', $tenant);
        $request->attributes->set('tenant_id', $tenant->id);
        $request->attributes->set('company_code', $tenant->company_code);

        try {
            $this->databaseManager->switchForTenant($tenant, $this->tenantContext->module() ?? 'workspace');
        } catch (\Throwable $e) {
            Log::error('Tenant database switch failed', [
                'source' => $source,
                'tenant_id' => $tenant->id,
                'company_code' => $tenant->company_code,
                'error' => $e->getMessage(),
            ]);

            return response()->view('errors.tenant-registry-error', [], 503);
        }

        if (config('tenant.log_resolutions')) {
            Log::debug('Tenant resolved', [
                'source' => $source,
                'tenant_id' => $tenant->id,
                'company_code' => $tenant->company_code,
                'shard_db' => $this->tenantContext->databaseForCurrentModule(),
            ]);
        }

        $response = $next($request);

        if (config('app.debug') && config('tenant.debug_header') && method_exists($response, 'header')) {
            $response->header('X-Tenant-Code', $tenant->company_code);
            $response->header('X-Tenant-Id', (string) $tenant->id);
            $response->header('X-Tenant-Source', $source);
            if (config('tenant.switch_database_connection')) {
                $response->header('X-Tenant-Database', (string) $this->databaseManager->activeDatabase());
            }
        }

        return $response;
    }

    private function tenantFromJwt(Request $request): ?Tenant
    {
        if (! $request->bearerToken()) {
            return null;
        }

        try {
            $payload = JWTAuth::setToken($request->bearerToken())->getPayload();
            $tenantId = $payload->get('tenant_id');

            if (! $tenantId) {
                return null;
            }

            return Tenant::query()->where('status', 'active')->where('id', $tenantId)->first();
        } catch (\Throwable) {
            return null;
        }
    }

    private function tenantFromDomain(Request $request): ?Tenant
    {
        $host = strtolower($request->getHost());

        try {
            $tenant = Tenant::findByDomain($host);
        } catch (\Throwable $e) {
            Log::error('Tenant registry lookup failed', ['host' => $host, 'error' => $e->getMessage()]);

            if (config('tenant.strict_domain_match')) {
                abort(503, 'Tenant registry unavailable');
            }

            return null;
        }

        if (! $tenant || ! $this->hostMatchesModule($tenant, $host)) {
            if (config('tenant.strict_domain_match')) {
                abort(404, 'Tenant not found for domain');
            }

            return null;
        }

        return $tenant;
    }

    private function shouldBypass(Request $request): bool
    {
        if ($request->is('up')) {
            return true;
        }

        // Login form company_code must win over stale tenant cookies from another company.
        if ($request->is('login') && $request->isMethod('POST') && $request->filled('company_code')) {
            return true;
        }

        return in_array(strtolower($request->getHost()), config('tenant.bypass_hosts', []), true);
    }

    private function hostMatchesModule(Tenant $tenant, string $host): bool
    {
        $field = $this->tenantContext->module().'_domain';

        if (! in_array($field, ['workspace_domain', 'payroll_domain', 'attendance_domain', 'crm_domain'], true)) {
            return true;
        }

        return strtolower((string) $tenant->{$field}) === $host;
    }
}
