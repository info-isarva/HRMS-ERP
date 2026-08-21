<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Legacy POSH in Payroll (deprecated — Phase 0)
    |--------------------------------------------------------------------------
    |
    | When false, all compliance/posh/* routes redirect to HRMS POSH module.
    | Set POSH_LEGACY_ENABLED=true only for temporary access to export old data.
    |
    */

    'product_name' => env('POSH_PRODUCT_NAME', 'POSH Compliance'),

    'legacy_enabled' => env('POSH_LEGACY_ENABLED', false),

    'workspace_url' => rtrim(env('SSO_WORKSPACE_URL', env('APP_URL', 'http://localhost')), '/'),

    'coming_soon_path' => '/posh',

];
