<?php

namespace App\Console\Commands;

use App\Models\Organization;
use App\Models\PoshAnnualReport;
use Illuminate\Console\Command;

class SanitizeOrganizationNames extends Command
{
    protected $signature = 'posh:sanitize-org-names {--dry-run : Preview changes without saving}';

    protected $description = 'Remove vendor prefixes (e.g. ISARVA) from organization names and annual report snapshots';

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');

        foreach (Organization::all() as $org) {
            $clean = Organization::sanitizeDisplayName($org->name);
            if ($clean !== $org->name) {
                $this->line("Organization #{$org->id}: \"{$org->name}\" → \"{$clean}\"");
                if (! $dry) {
                    $org->update(['name' => $clean]);
                }
            }
        }

        foreach (PoshAnnualReport::all() as $report) {
            $data = $report->report_data ?? [];
            if (empty($data['organization'])) {
                continue;
            }
            $clean = Organization::sanitizeDisplayName($data['organization']);
            if ($clean !== $data['organization']) {
                $this->line("Report {$report->report_year} (#{$report->id}): org name sanitized");
                if (! $dry) {
                    $data['organization'] = $clean;
                    $report->update(['report_data' => $data]);
                }
            }
        }

        $this->info($dry ? 'Dry run complete.' : 'Organization names sanitized.');

        return self::SUCCESS;
    }
}
