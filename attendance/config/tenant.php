<?php

return [
    'module' => env('TENANT_MODULE', 'attendance'),
    'resolve_tenant_from_domain' => env('TENANT_RESOLVE_DOMAIN', false),
    'resolve_from_session' => env('TENANT_RESOLVE_FROM_SESSION', true),
    'resolve_from_jwt' => env('TENANT_RESOLVE_FROM_JWT', true),
    'switch_database_connection' => env('TENANT_SWITCH_DATABASE', false),
    'strict_domain_match' => env('TENANT_STRICT_DOMAIN', true),
    'log_resolutions' => env('TENANT_LOG_RESOLUTIONS', true),
    'debug_header' => env('TENANT_DEBUG_HEADER', true),
    'bypass_hosts' => array_filter(array_map('trim', explode(',', env('TENANT_BYPASS_HOSTS', 'localhost,127.0.0.1')))),
];
