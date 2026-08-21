<?php

namespace App\Helper;

use App\Models\Setting;

class CurrencyHelper
{
    /**
     * Get currency configurations
     */
    public static function getCurrencies()
    {
        return [
            'AED' => [
                'name' => 'UAE Dirham',
                'symbol' => 'AED',
                'subunit' => 'Fils',
                'locale' => 'en_AE',
                'decimal_points' => 2,
                'thousands_sep' => ',',
                'decimal_sep' => '.',
            ],
            'USD' => [
                'name' => 'US Dollar',
                'symbol' => '$',
                'subunit' => 'Cents',
                'locale' => 'en_US',
                'decimal_points' => 2,
                'thousands_sep' => ',',
                'decimal_sep' => '.',
            ],
            'INR' => [
                'name' => 'Indian Rupee',
                'symbol' => '₹',
                'subunit' => 'Paise',
                'locale' => 'en_IN',
                'decimal_points' => 2,
                'thousands_sep' => ',',
                'decimal_sep' => '.',
            ],
            'GBP' => [
                'name' => 'Pound Sterling',
                'symbol' => '£',
                'subunit' => 'Pence',
                'locale' => 'en_GB',
                'decimal_points' => 2,
                'thousands_sep' => ',',
                'decimal_sep' => '.',
            ],
        ];
    }

    /**
     * Get the current active currency configuration
     */
    public static function getCurrentCurrency()
    {
        $activeCode = Setting::getValue('active_currency', 'INR');
        $currencies = self::getCurrencies();
        
        return $currencies[$activeCode] ?? $currencies['INR'];
    }

    /**
     * Format an amount based on current currency
     */
    public static function format($amount)
    {
        $config = self::getCurrentCurrency();
        $formatted = number_format(
            (float)$amount, 
            $config['decimal_points'], 
            $config['decimal_sep'], 
            $config['thousands_sep']
        );
        
        return $config['symbol'] . ' ' . $formatted;
    }

    /**
     * Get only the symbol
     */
    public static function symbol()
    {
        $config = self::getCurrentCurrency();
        return $config['symbol'];
    }

    /**
     * Get the active currency code
     */
    public static function getActiveCode()
    {
        return Setting::getValue('active_currency', 'INR');
    }

    /**
     * Get the locale for the current currency
     */
    public static function getLocale()
    {
        $config = self::getCurrentCurrency();
        return str_replace('_', '-', $config['locale']);
    }

    /**
     * Get the active currency name
     */
    public static function getActiveCurrencyName()
    {
        $config = self::getCurrentCurrency();
        return $config['name'];
    }
}
