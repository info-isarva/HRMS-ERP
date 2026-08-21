<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackfillDepartmentHolidayConfigsPayrollId extends Command
{
    protected $signature = 'department-holiday-configs:backfill-payroll-id';
    protected $description = 'Backfill payroll_department_id in department_holiday_configs from departments.api_department_id';

    public function handle()
    {
        $this->info('Starting backfill of department_holiday_configs.payroll_department_id...');

        $updated = DB::update(
            'UPDATE department_holiday_configs dhc
             JOIN departments d ON dhc.department_id = d.id
             SET dhc.payroll_department_id = d.api_department_id
             WHERE d.api_department_id IS NOT NULL AND dhc.payroll_department_id IS NULL'
        );

        $this->info("Backfill complete. Rows updated: {$updated}");
        return 0;
    }
}