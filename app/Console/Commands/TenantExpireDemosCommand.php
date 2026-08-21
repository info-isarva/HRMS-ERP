<?php

namespace App\Console\Commands;

use App\Models\Central\Tenant;
use Illuminate\Console\Command;

class TenantExpireDemosCommand extends Command
{
    protected $signature = 'tenant:expire-demos {--dry-run : List tenants that would be expired without updating}';

    protected $description = 'Deactivate demo tenants whose expiry date has passed';

    public function handle(): int
    {
        $query = Tenant::query()
            ->where('is_demo', true)
            ->where('status', 'active')
            ->whereNotNull('demo_expires_at')
            ->where('demo_expires_at', '<', now());

        $tenants = $query->get();

        if ($tenants->isEmpty()) {
            $this->info('No expired demos to process.');

            return self::SUCCESS;
        }

        foreach ($tenants as $tenant) {
            $this->line("• {$tenant->company_code} — expired {$tenant->demo_expires_at->format('Y-m-d H:i')}");

            if (! $this->option('dry-run')) {
                $tenant->update([
                    'status' => 'inactive',
                    'meta' => array_merge($tenant->meta ?? [], [
                        'expired_at' => now()->toIso8601String(),
                        'expired_via' => 'tenant:expire-demos',
                    ]),
                ]);
            }
        }

        if ($this->option('dry-run')) {
            $this->warn('Dry run — no tenants were updated.');
        } else {
            $this->info('Expired '.$tenants->count().' demo tenant(s).');
        }

        return self::SUCCESS;
    }
}
