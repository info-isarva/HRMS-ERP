<?php

namespace App\Console\Commands;

use App\Models\Organization;
use App\Models\PoshComplaint;
use App\Services\PoshSlaService;
use Illuminate\Console\Command;

class CheckPoshSlas extends Command
{
    protected $signature = 'posh:check-slas';

    protected $description = 'Flag POSH cases approaching or exceeding statutory SLAs';

    public function handle(PoshSlaService $sla): int
    {
        foreach (Organization::where('is_active', true)->pluck('id') as $orgId) {
            $alerts = $sla->alertsForOrganization($orgId);
            foreach ($alerts as $a) {
                $this->line("[{$orgId}] {$a['case']}: {$a['msg']}");
            }
        }

        PoshComplaint::whereNotNull('inquiry_started_at')
            ->whereNull('report_due_at')
            ->each(fn ($c) => $sla->setInquirySlas($c));

        $this->info('SLA check complete.');

        return self::SUCCESS;
    }
}
