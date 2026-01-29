<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();
        // Add your scheduled tasks here, e.g., financial year reset
        $schedule->command('financial-year:reset')->yearlyOn(4, 1, '00:00');
        
        // Schedule employee and department sync commands to run daily
        $schedule->command('employees:sync')->dailyAt('01:00');
        $schedule->command('departments:sync')->dailyAt('01:30');
        
        // Automatic real-time employee synchronization every 1 minute
        $schedule->command('employees:auto-sync')
            ->everyMinute()
            ->withoutOverlapping(2) // Prevent overlapping runs, wait 2 minutes max
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/auto-sync.log'));
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}