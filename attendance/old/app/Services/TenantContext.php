<?php

namespace App\Services;

use App\Models\Central\Tenant;

class TenantContext
{
    protected ?Tenant $tenant = null;

    protected ?string $resolvedHost = null;

    protected ?string $module = null;

    public function setModule(string $module): void
    {
        $this->module = $module;
    }

    public function module(): ?string
    {
        return $this->module;
    }

    public function setTenant(?Tenant $tenant, string $host): void
    {
        $this->tenant = $tenant;
        $this->resolvedHost = $host;
    }

    public function tenant(): ?Tenant
    {
        return $this->tenant;
    }

    public function id(): ?int
    {
        return $this->tenant?->id;
    }

    public function companyCode(): ?string
    {
        return $this->tenant?->company_code;
    }

    public function databaseForCurrentModule(): ?string
    {
        if (! $this->tenant || ! $this->module) {
            return null;
        }

        return $this->tenant->databaseForModule($this->module);
    }
}
