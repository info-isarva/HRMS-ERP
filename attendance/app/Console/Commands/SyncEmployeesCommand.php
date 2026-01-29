<?php

namespace App\Console\Commands;

use App\Services\EmployeeSyncService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncEmployeesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'employees:sync {--force : Force update all employees} {--delete : Allow deletion of extra employees} {--safe : Use safe mode (default, no deletions)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Comprehensive sync of employees from payroll to attendance system (safe mode by default)';

    /**
     * Execute the console command.
     */
    public function handle(EmployeeSyncService $syncService)
    {
        $this->info('Starting comprehensive employee synchronization from payroll to attendance...');
        
        // Safe mode is default unless --delete is explicitly specified
        $safeMode = !$this->option('delete');
        
        // Get command options
        $options = [
            'force_update' => $this->option('force'),
            'delete_extra' => $this->option('delete'), // Only delete if explicitly requested
            'verbose' => $this->getOutput()->isVerbose()
        ];
        
        if ($safeMode) {
            $this->info('Running in SAFE MODE (no deletions will be performed)');
        } else {
            $this->warn('Running in FULL MODE (deletions may be performed)');
        }
        
        try {
            // Use safe sync by default, or full sync if delete option is specified
            if ($safeMode) {
                $result = $syncService->safeSyncEmployees($options);
            } else {
                $result = $syncService->syncAllEmployees($options);
            }
            
            if ($result['success']) {
                $this->info($result['message']);
                
                // Show sync statistics
                if (isset($result['stats'])) {
                    $stats = $result['stats'];
                    $this->line('');
                    $this->line('Sync Statistics:');
                    $this->line("- Created: {$stats['created']}");
                    $this->line("- Updated: {$stats['updated']}");
                    $this->line("- Skipped: {$stats['skipped']}");
                    $this->line("- Deleted: {$stats['deleted']}");
                    
                    if ($stats['errors'] > 0) {
                        $this->error("- Errors: {$stats['errors']}");
                    }
                    
                    $this->line('');
                    $total = $stats['created'] + $stats['updated'] + $stats['skipped'];
                    $this->info("Total employees processed: {$total}");
                }
                
                return 0;
            } else {
                $this->error($result['message']);
                return 1;
            }
            
        } catch (\Exception $e) {
            $this->error('Error during employee synchronization: ' . $e->getMessage());
            Log::error('Employee sync command failed: ' . $e->getMessage(), ['exception' => $e]);
            return 1;
        }
    }
}
