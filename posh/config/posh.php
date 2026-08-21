<?php

return [

    /*
    | White-label product name (change per customer deployment via .env)
    */
    'product_name' => env('POSH_PRODUCT_NAME', 'POSH Compliance'),
    'product_short_name' => env('POSH_PRODUCT_SHORT_NAME', 'POSH'),
    'product_tagline' => env('POSH_PRODUCT_TAGLINE', 'Workplace Safety & Compliance'),

    /*
    | Prefixes stripped from organization names in UI/reports (white-label; comma-separated in .env)
    */
    'org_name_prefixes_to_strip' => array_values(array_filter(array_map(
        'trim',
        explode(',', env('POSH_ORG_NAME_PREFIXES_STRIP', 'ISARVA,Isarva,isarva'))
    ))),

    'workspace_url' => rtrim(env('SSO_WORKSPACE_URL', env('APP_URL', 'http://localhost')), '/'),

    'deployment_modes' => [
        'erp' => [
            'label' => 'ERP client (Payroll linked)',
            'employee_source' => 'payroll',
            'auth_mode' => 'sso',
        ],
        'standalone' => [
            'label' => 'Standalone POSH product',
            'employee_source' => 'posh',
            'auth_mode' => 'native',
        ],
    ],

    'employee_sources' => [
        'payroll' => 'Synced from Payroll',
        'posh' => 'Managed in POSH',
    ],

    'ic_roles' => [
        'presiding_officer' => 'Presiding Officer',
        'internal_member' => 'Internal Member',
        'external_member' => 'External Member',
        'member_secretary' => 'Member Secretary',
    ],

    'user_roles' => [
        'super_admin' => 'Super Admin',
        'hr_admin' => 'HR Admin',
        'presiding_officer' => 'Presiding Officer',
        'ic_member' => 'IC Member',
        'external_member' => 'External Member',
        'employee' => 'Employee',
    ],

    'admin_roles' => ['super_admin', 'hr_admin'],

    'ic_roles_access' => ['super_admin', 'hr_admin', 'presiding_officer', 'ic_member', 'external_member'],

    'statuses' => [
        'Draft', 'Submitted', 'Acknowledged', 'Under IC/LC Review', 'Additional Info Requested',
        'Rejected (with reasons)', 'Conciliation In Progress', 'Interim Relief Applied',
        'Inquiry Started', 'Notice Issued to Respondent', 'Hearing Completed',
        'Recommendation Pending', 'Management Action Pending (60 days)', 'Closed', 'Archived',
    ],

    'closed_statuses' => ['Closed', 'Archived', 'Rejected (with reasons)'],

    'operate_steps' => [
        ['key' => 'review', 'label' => '1. IC Review', 'status' => 'Under IC/LC Review'],
        ['key' => 'conciliation', 'label' => '2. Conciliation', 'status' => 'Conciliation In Progress'],
        ['key' => 'interim', 'label' => '3. Interim Relief', 'status' => 'Interim Relief Applied'],
        ['key' => 'notice', 'label' => '4. Notice to Respondent', 'status' => 'Notice Issued to Respondent'],
        ['key' => 'inquiry', 'label' => '5. Inquiry & Hearing', 'status' => 'Inquiry Started'],
        ['key' => 'hearing', 'label' => '6. Hearing Done', 'status' => 'Hearing Completed'],
        ['key' => 'recommendation', 'label' => '7. IC Recommendation', 'status' => 'Recommendation Pending'],
        ['key' => 'action', 'label' => '8. Management Action', 'status' => 'Management Action Pending (60 days)'],
        ['key' => 'appeal', 'label' => '9. Appeal / Close', 'status' => 'Closed'],
    ],

    'respondent_types' => [
        'employee' => 'Employee',
        'supervisor' => 'Supervisor / Manager',
        'third_party' => 'Client / Vendor / Third party',
        'employer' => 'Employer (routes to LC)',
    ],

    'employer_duties' => [
        'safe_environment' => 'Provide safe working environment',
        'display_penal' => 'Display penal consequences of sexual harassment at conspicuous place',
        'display_ic_order' => 'Display order constituting IC at conspicuous place',
        'workshops' => 'Organize awareness workshops at regular intervals for employees',
        'ic_orientation' => 'Organize orientation programmes for IC members',
        'facilitate_ic' => 'Facilitate IC/LC to conduct proceedings',
        'secure_attendance' => 'Assist securing attendance of respondent and witnesses',
        'provide_info' => 'Provide information to IC/LC as required',
        'assist_complaint' => 'Assist aggrieved woman if she cannot submit written complaint',
        'misconduct_rules' => 'Treat sexual harassment as misconduct under service rules',
        'criminal_action' => 'Initiate action under IPC or other law if criminal conduct',
        'medical_assistance' => 'Provide medical assistance / treatment for illness from harassment',
        'annual_report' => 'Submit annual report with prescribed particulars to District Officer',
        'service_rules' => 'Include sexual harassment in service rules / standing orders',
    ],

    'statutory_sla_days' => [
        'filing_window_months' => 3,
        'filing_extension_months' => 3,
        'inquiry_days' => 90,
        'report_after_inquiry_days' => 10,
        'management_action_days' => 60,
        'appeal_days' => 90,
    ],

    'locales' => ['en' => 'English', 'hi' => 'हिंदी'],

];
