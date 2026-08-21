<?php

namespace App\Helpers;

use App\Models\Company;

class MoneyFormatter
{
    /**
     * Format amount according to company settings and locale.
     * Returns a formatted string.
     */
    public static function format($amount, $decimals = 2)
    {
        if ($amount === null || $amount === '') {
            return '';
        }

        $company = Company::first();
        $currencyCode = $company->currency_code ?? null;
        $symbol = $company->currency_symbol ?? null;
        $position = $company->currency_position ?? 'prefix';
        $country = $company->country ?? null;

        $localeMap = [
            'United States' => 'en_US',
            'India' => 'en_IN',
            'United Kingdom' => 'en_GB',
            'European Union' => 'de_DE',
            'Germany' => 'de_DE',
            'France' => 'fr_FR',
            'Japan' => 'ja_JP',
            'Canada' => 'en_CA',
            'Australia' => 'en_AU',
        ];
        $locale = $localeMap[$country] ?? config('app.locale', 'en_US');

        // Try intl
        if (class_exists('\\NumberFormatter')) {
            try {
                if ($currencyCode && empty($symbol)) {
                    $nfCur = new \NumberFormatter($locale, \NumberFormatter::CURRENCY);
                    $nfCur->setAttribute(\NumberFormatter::FRACTION_DIGITS, $decimals);
                    return $nfCur->formatCurrency((float) $amount, $currencyCode);
                }

                $nf = new \NumberFormatter($locale, \NumberFormatter::DECIMAL);
                $nf->setAttribute(\NumberFormatter::FRACTION_DIGITS, $decimals);
                $formatted = $nf->format((float) $amount);
                $nbsp = "\u{00A0}";
                if ($symbol) {
                    if ($position === 'prefix') {
                        return $symbol . $nbsp . $formatted;
                    }
                    return $formatted . $nbsp . $symbol;
                }

                return $formatted;
            } catch (\Throwable $e) {
                // fall back
            }
        }

        // fallback
        $num = number_format((float) $amount, $decimals);
        $nbsp = "\u{00A0}";
        $sym = $symbol ?? $currencyCode ?? '';
        if ($sym) {
            if ($position === 'prefix') {
                return $sym . $nbsp . $num;
            }
            return $num . $nbsp . $sym;
        }

        return $num;
    }
}
