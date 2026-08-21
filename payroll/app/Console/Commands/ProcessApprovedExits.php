<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ExitProcessorService;

class ProcessApprovedExits extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hrms:process-exits';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically process approved employee exits whose last working day has passed';

    /**
     * Execute the console command.
     */
    public function handle(ExitProcessorService $processor)
    {
        $this->info('Starting exit processor...');
        $processor->processApprovedExits();
        $this->info('Exit processor completed.');
    }
}
