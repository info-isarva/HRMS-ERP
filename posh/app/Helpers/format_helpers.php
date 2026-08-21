<?php

if (! function_exists('format_amount')) {
    function format_amount($amount, $decimals = 2)
    {
        return \App\Helpers\MoneyFormatter::format($amount, $decimals);
    }
}