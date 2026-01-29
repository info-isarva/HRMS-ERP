<?php

namespace App\Console\Commands;

use App\Services\FinancialYearService;
use Illuminate\Console\Command;

class FinancialYearMaintenance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'financial-year:maintenance 
                            {--auto-close : Auto-close expired financial years}
                            {--auto-create : Auto-create next financial year}
                            {--force : Force operations without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run financial year maintenance tasks (auto-close, auto-create)';

    protected $financialYearService;

    /**
     * Create a new command instance.
     */
    public function __construct(FinancialYearService $financialYearService)
    {
        parent::__construct();
        $this->financialYearService = $financialYearService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting Financial Year Maintenance...');
        
        $autoClose = $this->option('auto-close');
        $autoCreate = $this->option('auto-create');
        $force = $this->option('force');
        
        // If no specific options provided, run all maintenance tasks
        if (!$autoClose && !$autoCreate) {
            $autoClose = true;
            $autoCreate = true;
        }
        
        try {
            if ($autoClose) {
                $this->runAutoClose($force);
            }
            
            if ($autoCreate) {
                $this->runAutoCreate($force);
            }
            
            $this->info('Financial Year Maintenance completed successfully!');
            
        } catch (\Exception $e) {
            $this->error('Maintenance failed: ' . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
    
    /**
     * Run auto-close expired financial years
     */
    protected function runAutoClose($force = false)
    {
        $this->info('Running auto-close for expired financial years...');
        
        if (!$force && !$this->confirm('Do you want to auto-close expired financial years?')) {
            $this->info('Skipping auto-close.');
            return;
        }
        
        $this->financialYearService->autoCloseExpiredFinancialYears();
        $this->info('Auto-close completed.');
    }
    
    /**
     * Run auto-create next financial year
     */
    protected function runAutoCreate($force = false)
    {
        $this->info('Running auto-create for next financial year...');
        
        if (!$force && !$this->confirm('Do you want to auto-create the next financial year if needed?')) {
            $this->info('Skipping auto-create.');
            return;
        }
        
        $this->financialYearService->autoCreateNextFinancialYear();
        $this->info('Auto-create completed.');
    }
}
