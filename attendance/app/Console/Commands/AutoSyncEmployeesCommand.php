<?php

namespace App\Console\Commands;

use App\Services\EmployeeSyncService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class AutoSyncEmployeesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'employees:auto-sync {--dry-run : Show what would be synced without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatic background employee synchronization (runs every few minutes)';

    /**
     * Execute the console command.
     */
    public function handle(EmployeeSyncService $syncService)
    {
        $isDryRun = $this->option('dry-run');
        
        if ($isDryRun) {
            $this->info('Running in dry-run mode - no changes will be made');
        }
        
        try {
            // First check if sync is needed to avoid unnecessary API calls
            $syncStatus = $syncService->getSyncStatus();
            
            if (!$syncStatus['sync_needed'] && !$isDryRun) {
                // No sync needed, exit quietly
                return 0;
            }
            
            if ($isDryRun) {
                $this->line("Sync status check:");
                $this->line("- Payroll employees: " . $syncStatus['payroll_employees']);
                $this->line("- Attendance employees: " . $syncStatus['attendance_employees']);
                $this->line("- Difference: " . $syncStatus['difference']);
                $this->line("- Sync needed: " . ($syncStatus['sync_needed'] ? 'Yes' : 'No'));
                $this->line("- Last sync: " . $syncStatus['last_sync']);
                
                if (!$syncStatus['sync_needed']) {
                    $this->info('No synchronization needed - systems are in sync');
                    return 0;
                }
            }

            // Run the sync with conservative options
            $options = [
                'force_update' => false,      // Only update when changes detected
                'delete_extra' => false,      // Don't auto-delete in scheduled sync (safety)
                'verbose' => $isDryRun
            ];
            
            if ($isDryRun) {
                $this->line("\nWould run sync with options:");
                $this->line("- Force update: No");
                $this->line("- Delete extra: No");
                $this->line("- Verbose: Yes");
                return 0;
            }

            $result = $syncService->syncAllEmployees($options);
            
            if ($result['success']) {
                $stats = $result['stats'] ?? [];
                
                // Only log if there were actual changes
                $totalChanges = ($stats['created'] ?? 0) + ($stats['updated'] ?? 0) + ($stats['deleted'] ?? 0);
                
                if ($totalChanges > 0) {
                    Log::info('Automatic employee sync completed', [
                        'created' => $stats['created'] ?? 0,
                        'updated' => $stats['updated'] ?? 0,
                        'skipped' => $stats['skipped'] ?? 0,
                        'deleted' => $stats['deleted'] ?? 0,
                        'errors' => $stats['errors'] ?? 0
                    ]);
                }
                
                return 0;
            } else {
                Log::error('Automatic employee sync failed: ' . $result['message']);
                return 1;
            }
            
        } catch (\Exception $e) {
            Log::error('Automatic employee sync error: ' . $e->getMessage(), ['exception' => $e]);
            return 1;
        }
    }
}
