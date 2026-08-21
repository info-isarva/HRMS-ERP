<?php

/** for side bar menu active */
function set_active($route) {
    if (is_array($route )){
        return in_array(Request::path(), $route) ? 'active' : '';
    }
    return Request::path() == $route ? 'active' : '';
}

if (!function_exists('getGenders')) {
    function getGenders()
    {
        return [
            '1' => 'Male',
            '2' => 'Female',
            '3' => 'Other'
        ];
    }
}

if (!function_exists('getMaritalStatuses')) {
    function getMaritalStatuses()
    {
        return [
            '1' => 'Single',
            '2' => 'Married',
            '3' => 'Widowed',
            '4' => 'Divorced',
            '5' => 'Separated',
        ];
    }
}

if (!function_exists('getDesignations')) {
    function getDesignations()
    {
        return [
            '1' => 'Driver',
            '2' => 'Surveyor',
            '3' => 'Engineer QA/QC',
            '4' => 'Manager - Project',
            '5' => 'Vehicle in Charge',
            '6' => 'Supervisor',
            '7' => 'Chief Financial Officer',
            '8' => 'Machine Operator',
            '9' => 'Administrator',
            '10' => 'Senior Engineer',
            '11' => 'Store Supervisor',
            '12' => 'Safety Supervisor',
            '13' => 'Operator',
            '14' => 'Store Keeper',
            '15' => 'Admin Manager',
            '16' => 'HR Manager',
            '17' => 'Accounts Manager',
        ];

    }
}

if (!function_exists('getDepartments')) {
    function getDepartments()
    {
        return [
            '1' => 'HR',
            '2' => 'Driver',
            '3' => 'Operater',
            '4' => 'Engineering',
            '5' => 'Office',
            '6' => 'Finance',
            '7' => 'Civil',
            '8' => 'Vehicle',
            '9' => 'Housekeeping',
            '10' => 'Store',
        ];
    }
}

if (!function_exists('getEmployeeStatuses')) {
    function getEmployeeStatuses()
    {
        return [
            '1' => 'Active',
            '2' => 'Probation Period',
            '3' => 'Left',
            '4' => 'Onboard',
           
        ];
    }
}

if (!function_exists('getRoles')) {
    function getRoles()
    {
        return [
            '1' => 'Admin',
            '2' => 'Employee',           
           
        ];
    }
}

if (!function_exists('getBloodGroups')) {
    function getBloodGroups()
    {
        return [
            '1' => 'A+',
            '2' => 'A-',
            '3' => 'B+',
            '4' => 'B-',
            '5' => 'AB+',
            '6' => 'AB-',
            '7' => 'O+',
            '8' => 'O-',           
        ];
    }
}

if (!function_exists('getPaymentTypes')) {
    function getPaymentTypes()
    {
        return [
            '1' => 'Bank transfer',
            '2' => 'Cash',
            '3' => 'Cheque'         
           
        ];
    }
}

function getDocumentTypes()
{
    return [
        '1' => 'Aadhaar Card',
        '2' => 'PAN Card',
        '3' => 'Passport',
        '4' => 'Driving License',
        '5' => 'Voter ID',
        '6' => 'Education Certificate',
        '7' => 'Experience Certificate',
        '8' => 'Photograph',
        '9' => 'Signature',
        '10' => 'Resignation Letter',
        '11' => 'Other Document'
    ];
}

if (!function_exists('getTransactionTypes')) {
    function getTransactionTypes()
    {
        return [
            '1' => 'NEFT TRANSFER',
            '2' => 'RTGS TRANSFER',
            '3' => 'INTERNAL TRANSFER',
            '4' => 'IMPS TRANSFER-MMID',
            '5' => 'IMPS TRANSFER-IFSC',    
           
        ];
    }
}

if (!function_exists('getTransactionTypesICICI')) {
    function getTransactionTypesICICI()
    {
        return [
            '1' => 'NEFT',
            '2' => 'RTGS',
            '3' => 'FT',
            '4' => 'IMPS',
            '5' => 'IMPS',
                       
        ];
    }
}

if (!function_exists('toastr')) {
    function toastr()
    {
        return app('flasher');
    }
}

/**
 * Get document type label from its value
 *
 * @param string $value
 * @return string
 */
function getDocumentTypeLabel($value)
{
    $types = getDocumentTypes();
    return $types[$value] ?? 'Unknown Document';
}

/**
 * Global shortcut for formatting currency
 */
if (!function_exists('format_currency')) {
    function format_currency($amount)
    {
        return \App\Helper\CurrencyHelper::format($amount);
    }
}

/**
 * Get active currency symbol
 */
if (!function_exists('get_currency_symbol')) {
    function get_currency_symbol()
    {
        return \App\Helper\CurrencyHelper::symbol();
    }
}

/**
 * Get active currency locale
 */
if (!function_exists('get_currency_locale')) {
    function get_currency_locale()
    {
        return \App\Helper\CurrencyHelper::getLocale();
    }
}

/**
 * Get active currency code
 */
if (!function_exists('get_currency_code')) {
    function get_currency_code()
    {
        return \App\Helper\CurrencyHelper::getActiveCode();
    }
}

/**
 * Get active currency name
 */
if (!function_exists('get_currency_name')) {
    function get_currency_name()
    {
        return \App\Helper\CurrencyHelper::getActiveCurrencyName();
    }
}

/**
 * Get active currency subunit
 */
if (!function_exists('get_currency_subunit')) {
    function get_currency_subunit()
    {
        $config = \App\Helper\CurrencyHelper::getCurrentCurrency();
        return $config['subunit'] ?? '';
    }
}
