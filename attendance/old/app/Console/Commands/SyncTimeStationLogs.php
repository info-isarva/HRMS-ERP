<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TimeStationService;
use Carbon\Carbon;

class SyncTimeStationLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:sync-timestation {--days=1 : Number of days to look back}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch raw attendance logs from TimeStation API into Staging table';

    /**
     * Execute the console command.
     */
    public function handle(TimeStationService $service)
    {
        $days = $this->option('days');
        $startDate = Carbon::now()->subDays($days)->format('Y-m-d');
        $endDate = Carbon::now()->format('Y-m-d');

        $this->info("Fetching data from $startDate to $endDate...");

        $activities = $service->fetchActivities($startDate, $endDate);
        
        if (empty($activities)) {
            $this->warn("No data returned or API error.");
            return;
        }

        $this->info("Fetched " . count($activities) . " records.");
        
        $count = $service->syncLogs($activities);
        
        $this->info("Synced $count logs to Staging.");
    }
}
