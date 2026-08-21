<?php

return [
    'payroll_api' => [
        'base_url' => env('PAYROLL_API_BASE_URL', 'https://payrolldev.isarva.in/api'),
        'email' => env('PAYROLL_API_EMAIL', 'sup_admin@gmail.com'),
        'password' => env('PAYROLL_API_PASSWORD', 'admin'),
        'jwt_token' => env('PAYROLL_API_JWT_TOKEN', 'BaISZ4z15mywkXzuJflHvtlnCID4EJG4IfQ0EoeE3TSLzOy120tFThLabNlzS0Sc'),
    ],
];
