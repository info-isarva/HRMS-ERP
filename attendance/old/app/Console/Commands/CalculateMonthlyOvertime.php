<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MonthlyOvertimeCalculator;
use Carbon\Carbon;

class CalculateMonthlyOvertime extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:calculate-monthly-overtime {--month= : Month (1-12)} {--year= : Year} {--employee= : ID of specific employee}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate monthly overtime based on 208-hour threshold';

    /**
     * Execute the console command.
     */
    public function handle(MonthlyOvertimeCalculator $calculator)
    {
        $month = $this->option('month') ?: Carbon::now()->month;
        $year = $this->option('year') ?: Carbon::now()->year;
        $employeeId = $this->option('employee');

        $this->info("Calculating overtime for {$month}/{$year}...");
        
        $count = $calculator->calculateForMonth($month, $year, $employeeId);
        
        $this->info("Updated overtime records for {$count} employees.");
    }
}
