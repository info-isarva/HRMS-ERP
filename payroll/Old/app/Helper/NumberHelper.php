<?php

namespace App\Helper;

use App\Models\Setting;

class NumberHelper
{
    /**
     * Convert number to words based on active currency
     */
    public static function numberToWords($num)
    {
        $currency = CurrencyHelper::getCurrentCurrency();
        $code = CurrencyHelper::getActiveCode();
        
        $words = ($code === 'INR') ? self::numberToWordsIndian($num) : self::numberToWordsWestern($num);
        
        return $words . ' ' . ($currency['name'] ?? 'Rupees') . ' Only';
    }

    /**
     * Western Numbering (Million/Billion)
     */
    public static function numberToWordsWestern($num)
    {
        $a = [
            '', 'One', 'Two', 'Three', 'Four', 'Five', 'Six',
            'Seven', 'Eight', 'Nine', 'Ten', 'Eleven', 'Twelve',
            'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen',
            'Seventeen', 'Eighteen', 'Nineteen'
        ];

        $b = [
            '', '', 'Twenty', 'Thirty', 'Forty', 'Fifty',
            'Sixty', 'Seventy', 'Eighty', 'Ninety'
        ];

        $num = str_replace(',', '', $num);
        $num = floatval($num);
        $whole = floor($num);
        $decimal = round(($num - $whole) * 100);

        $inWords = function($n) use (&$inWords, $a, $b) {
            if ($n == 0) return '';
            if ($n < 20) return $a[$n];
            if ($n < 100) return $b[floor($n / 10)] . ($n % 10 ? ' ' . $a[$n % 10] : '');
            if ($n < 1000) return $a[floor($n / 100)] . ' Hundred' . ($n % 100 ? ' ' . $inWords($n % 100) : '');
            if ($n < 1000000) return $inWords(floor($n / 1000)) . ' Thousand' . ($n % 1000 ? ' ' . $inWords($n % 1000) : '');
            if ($n < 1000000000) return $inWords(floor($n / 1000000)) . ' Million' . ($n % 1000000 ? ' ' . $inWords($n % 1000000) : '');
            return 'Number too large';
        };

        $words = $whole == 0 ? 'Zero' : $inWords($whole);

        if ($decimal > 0) {
            $currency = CurrencyHelper::getCurrentCurrency();
            $subunit = $currency['subunit'] ?? 'Cents';
            $words .= ' and ' . $inWords($decimal) . ' ' . $subunit;
        }

        return trim($words);
    }

    /**
     * Indian Numbering (Lakh/Crore)
     */
    public static function numberToWordsIndian($num)
    {
        $a = [
            '', 'One', 'Two', 'Three', 'Four', 'Five', 'Six',
            'Seven', 'Eight', 'Nine', 'Ten', 'Eleven', 'Twelve',
            'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen',
            'Seventeen', 'Eighteen', 'Nineteen'
        ];

        $b = [
            '', '', 'Twenty', 'Thirty', 'Forty', 'Fifty',
            'Sixty', 'Seventy', 'Eighty', 'Ninety'
        ];

        $num = str_replace(',', '', $num);
        $num = floatval($num);
        
        $whole = floor($num);
        $decimal = round(($num - $whole) * 100);

        if ($whole == 0 && $decimal == 0) return 'Zero';

        $inWords = function($n) use (&$inWords, $a, $b) {
            if ($n == 0) return '';
            if ($n < 20) return $a[$n];
            if ($n < 100) return $b[intval($n / 10)] . ($n % 10 ? ' ' . $a[$n % 10] : '');
            if ($n < 1000) return $a[intval($n / 100)] . ' Hundred' . ($n % 100 ? ' ' . $inWords($n % 100) : '');
            return '';
        };

        $output = '';

        if ($whole > 0) {
            $temp = $whole;
            $crore = floor($temp / 10000000);
            $temp %= 10000000;
            $lakh = floor($temp / 100000);
            $temp %= 100000;
            $thousand = floor($temp / 1000);
            $temp %= 1000;
            $hundredAndBelow = $temp;

            if ($crore) $output .= $inWords($crore) . ' Crore ';
            if ($lakh) $output .= $inWords($lakh) . ' Lakh ';
            if ($thousand) $output .= $inWords($thousand) . ' Thousand ';
            if ($hundredAndBelow) $output .= $inWords($hundredAndBelow);
        } else {
            $output = 'Zero';
        }

        if ($decimal > 0) {
            $currency = CurrencyHelper::getCurrentCurrency();
            $subunit = $currency['subunit'] ?? 'Paise';
            $output .= ($whole > 0 ? ' and ' : '') . $inWords($decimal) . ' ' . $subunit;
        }

        return trim($output);
    }
}