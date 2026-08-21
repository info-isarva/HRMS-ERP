<?php

namespace App\Services;

use Tymon\JWTAuth\Payload;

/**
 * Reject SSO tokens that are not bound to the domain-resolved tenant (Phase 5).
 */
class SsoTenantValidator
{
    public function __construct(private TenantContext $tenantContext)
    {
    }

    public function validatePayload(Payload $payload): void
    {
        if (! config('tenant.resolve_tenant_from_domain')) {
            return;
        }

        $tokenTenantId = $payload->get('tenant_id');

        if ($tokenTenantId === null) {
            throw new \RuntimeException('SSO token missing tenant binding');
        }

        $tenant = $this->tenantContext->tenant();

        if (! $tenant) {
            throw new \RuntimeException('Tenant context not resolved for this domain');
        }

        if ((int) $tokenTenantId !== (int) $tenant->id) {
            throw new \RuntimeException('SSO token tenant does not match this domain');
        }

        $tokenCompanyCode = $payload->get('company_code');

        if ($tokenCompanyCode && strtoupper((string) $tokenCompanyCode) !== strtoupper($tenant->company_code)) {
            throw new \RuntimeException('SSO token company code mismatch');
        }
    }
}
