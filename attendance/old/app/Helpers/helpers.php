<?php

use App\Services\FinancialYearService;

if (!function_exists('active_fy')) {
    /**
     * Get the active financial year object.
     */
    function active_fy()
    {
        return app(FinancialYearService::class)->getActiveFY();
    }
}

if (!function_exists('active_fy_label')) {
    /**
     * Get the label of the active financial year.
     */
    function active_fy_label()
    {
        $fy = active_fy();
        return $fy ? $fy->name : app(FinancialYearService::class)->getFYLabelByDate(now());
    }
}
