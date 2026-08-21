<?php

return [

    'product_name' => env('POSH_PRODUCT_NAME', 'POSH Compliance'),

    'legacy_enabled' => env('POSH_LEGACY_ENABLED', false),

    'workspace_url' => rtrim(env('SSO_WORKSPACE_URL', env('APP_URL', 'http://localhost')), '/'),

    'coming_soon_path' => '/posh',

];
