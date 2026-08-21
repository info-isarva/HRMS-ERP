<?php

return [

    /*
    |--------------------------------------------------------------------------
    | POSH module (Phase 0+)
    |--------------------------------------------------------------------------
    |
    | legacy_enabled: When false, Payroll/Attendance basic POSH routes redirect
    |   to the HRMS placeholder. Set true only for temporary data access during
    |   migration (do not extend legacy features).
    |
    | module_placeholder_enabled: Show POSH product card on HRMS workspace dashboard.
    |
    */

    'product_name' => env('POSH_PRODUCT_NAME', 'POSH Compliance'),
    'product_short_name' => env('POSH_PRODUCT_SHORT_NAME', 'POSH'),

    'legacy_enabled' => env('POSH_LEGACY_ENABLED', false),

    'module_placeholder_enabled' => env('POSH_MODULE_PLACEHOLDER_ENABLED', true),

    'module_url' => env('POSH_URL'),

    'workspace_url' => rtrim(env('SSO_WORKSPACE_URL', env('APP_URL', 'http://localhost')), '/'),

    'show_prototype_link' => env('POSH_SHOW_PROTOTYPE_LINK', false),

    'prototype_url' => env('POSH_PROTOTYPE_URL', '/poshactresearch/index.html'),

];
