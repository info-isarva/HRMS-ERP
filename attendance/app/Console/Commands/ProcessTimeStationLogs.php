<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AttendanceProcessor;

class ProcessTimeStationLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:process-timestation {--employee= : ID of specific employee to process}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process mapped TimeStation logs into main Attendance records';

    /**
     * Execute the console command.
     */
    public function handle(AttendanceProcessor $processor)
    {
        $this->info("Starting attendance processing...");
        
        $employeeId = $this->option('employee');
        
        $count = $processor->processLogs($employeeId);
        
        $this->info("Processed $count attendance pairs.");
    }
}
