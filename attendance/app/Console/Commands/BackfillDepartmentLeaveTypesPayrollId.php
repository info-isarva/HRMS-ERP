<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackfillDepartmentLeaveTypesPayrollId extends Command
{
    protected $signature = 'department-leave-types:backfill-payroll-id';
    protected $description = 'Backfill payroll_department_id in department_leave_types from departments.api_department_id';

    public function handle()
    {
        $this->info('Starting backfill of department_leave_types.payroll_department_id...');

        $updated = DB::update(
            'UPDATE department_leave_types dlt
             JOIN departments d ON dlt.department_id = d.id
             SET dlt.payroll_department_id = d.api_department_id
             WHERE d.api_department_id IS NOT NULL'
        );

        $this->info("Backfill complete. Rows updated: {$updated}");
        return 0;
    }
}
