<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Schedule financial year maintenance to run daily at 2 AM
Schedule::command('financial-year:maintenance --auto-close --auto-create --force')
    ->dailyAt('02:00')
    ->withoutOverlapping()
    ->onOneServer();
