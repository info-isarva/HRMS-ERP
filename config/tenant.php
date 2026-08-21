<?php

/**
 * Multi-tenant registry configuration (Phase 1).
 *
 * The central database holds the tenants table only.
 * Application databases are switched at runtime when TENANT_SWITCH_DATABASE=true.
 */
return [

    'central_connection' => env('TENANT_CENTRAL_CONNECTION', 'central'),

    /*
    | Which module this Laravel app serves (workspace | payroll | attendance | crm).
    */
    'module' => env('TENANT_MODULE', 'workspace'),

    'resolve_tenant_from_domain' => env('TENANT_RESOLVE_DOMAIN', false),
    'resolve_from_session' => env('TENANT_RESOLVE_FROM_SESSION', true),
    'resolve_from_jwt' => env('TENANT_RESOLVE_FROM_JWT', true),
    'switch_database_connection' => env('TENANT_SWITCH_DATABASE', false),

    /*
    | Reject HTTP requests when domain is not in hrms_central.tenants.
    */
    'strict_domain_match' => env('TENANT_STRICT_DOMAIN', true),

    'log_resolutions' => env('TENANT_LOG_RESOLUTIONS', true),

    /*
    | When APP_DEBUG=true, add X-Tenant-Code / X-Tenant-Id response headers.
    */
    'debug_header' => env('TENANT_DEBUG_HEADER', true),

    'bypass_hosts' => array_filter(array_map('trim', explode(',', env('TENANT_BYPASS_HOSTS', 'localhost,127.0.0.1')))),

];
