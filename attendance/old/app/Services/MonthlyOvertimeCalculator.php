<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Overtime;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MonthlyOvertimeCalculator
{
    private $thresholdHours = 208; // Monthly threshold

    public function calculateForMonth($month, $year, $employeeId = null)
    {
        $query = Attendance::whereYear('date', $year)
            ->whereMonth('date', $month);

        if ($employeeId) {
            $query->where('employee_payroll_id', $employeeId);
        }

        // Get total hours per employee
        $summaries = $query->select('employee_payroll_id', DB::raw('SUM(total_hours) as worked_hours'))
            ->groupBy('employee_payroll_id')
            ->get();

        $count = 0;
        foreach ($summaries as $summary) {
            $otHours = max(0, $summary->worked_hours - $this->thresholdHours);

            // Update or Create Overtime Record
            // Assuming 'overtime_hours' in table stores the *calculated monthly amount*
            Overtime::updateOrCreate(
                [
                    'employee_payroll_id' => $summary->employee_payroll_id,
                    'month' => $month,
                    'year' => $year,
                ],
                [
                    'overtime_hours' => $otHours,
                    // If table has 'is_locked', we should perhaps check it before updating?
                    // assuming we can update if not locked.
                ]
            );
            $count++;
        }

        return $count;
    }
}
