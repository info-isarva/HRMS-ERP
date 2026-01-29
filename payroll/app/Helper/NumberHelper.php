<?php

namespace App\Helper;

class NumberHelper
{
    public static function numberToWords($num)
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

        // Recursive function
        $inWords = function($n) use (&$inWords, $a, $b) {
            if ($n < 20) return $a[$n];
            if ($n < 100) return $b[floor($n / 10)] . ($n % 10 ? ' ' . $a[$n % 10] : '');
            if ($n < 1000) return $a[floor($n / 100)] . ' Hundred' . ($n % 100 ? ' ' . $inWords($n % 100) : '');
            if ($n < 1000000) return $inWords(floor($n / 1000)) . ' Thousand' . ($n % 1000 ? ' ' . $inWords($n % 1000) : '');
            return 'Number too large';
        };

        $words = $inWords($whole);
        if ($decimal > 0) {
            $words .= ' and ' . $inWords($decimal) . ' Paise';
        }

        return trim($words);
    }

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
    $num = (int) $num;

    if ($num == 0) return 'Zero';

    $inWords = function($n) use (&$inWords, $a, $b) {
        if ($n < 20) return $a[$n];
        if ($n < 100) return $b[intval($n / 10)] . ($n % 10 ? ' ' . $a[$n % 10] : '');
        if ($n < 1000) return $a[intval($n / 100)] . ' Hundred' . ($n % 100 ? ' ' . $inWords($n % 100) : '');
        return '';
    };

    $output = '';

    $crore = floor($num / 10000000);
    $num %= 10000000;

    $lakh = floor($num / 100000);
    $num %= 100000;

    $thousand = floor($num / 1000);
    $num %= 1000;

    $hundredAndBelow = $num;

    if ($crore) $output .= $inWords($crore) . ' Crore ';
    if ($lakh) $output .= $inWords($lakh) . ' Lakh ';
    if ($thousand) $output .= $inWords($thousand) . ' Thousand ';
    if ($hundredAndBelow) $output .= $inWords($hundredAndBelow);

    return trim($output);
}
}