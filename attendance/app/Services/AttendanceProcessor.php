<?php

namespace App\Services;

use App\Models\TimeStationLog;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class AttendanceProcessor
{
    public function processLogs($employeeId = null)
    {
        // Get logs that are mapped but not yet processed
        $query = TimeStationLog::where('sync_status', 'mapped')
            ->orderBy('timestamp', 'asc');

        if ($employeeId) {
            $query->where('employee_payroll_id', $employeeId);
        }

        $logs = $query->get()->groupBy('employee_payroll_id');

        $processedCount = 0;

        foreach ($logs as $empId => $empLogs) {
            $processedCount += $this->processEmployeeLogs($empId, $empLogs);
        }

        return $processedCount;
    }

    private function processEmployeeLogs($empId, $logs)
    {
        $count = 0;
        
        $pendingCheckIn = null;

        foreach ($logs as $log) {
            $type = $log->activity_type; // CheckIn, CheckOut

            if ($type === 'CheckIn') {
                $pendingCheckIn = $log;
            } elseif ($type === 'CheckOut') {
                if ($pendingCheckIn) {
                    // Pair found!
                    $this->createAttendanceRecord($pendingCheckIn, $log);
                    
                    // Mark logs as processed
                    $pendingCheckIn->sync_status = 'processed';
                    $pendingCheckIn->save();
                    $log->sync_status = 'processed';
                    $log->save();
                    
                    $pendingCheckIn = null;
                    $count++;
                }
            }
        }

        return $count;
    }

    private function createAttendanceRecord($inLog, $outLog)
    {
        $inTime = Carbon::parse($inLog->timestamp);
        $outTime = Carbon::parse($outLog->timestamp);
        $empId = $inLog->employee_payroll_id;
        $employee = Employee::where('payroll_id', $empId)->first();

        // Calculate Duration
        $durationHours = $inTime->diffInMinutes($outTime) / 60;

        // Check if Continuous Shift (> 24h is unlikely but let's say > 16h and spans crossing day)
        $isNextDay = $outTime->format('Y-m-d') !== $inTime->format('Y-m-d');
        
        // Insert Adjustment
        \App\Models\AttendanceRecord::updateOrCreate(
            [
                'payroll_id' => $empId,
                'date' => $inTime->format('Y-m-d'),
            ],
            [
                'employee_id' => $employee ? $employee->employee_id : null,
                'employee_email' => $employee ? $employee->email : null,
                'check_in_time' => $inTime->format('H:i:s'),
                'check_out_time' => $outTime->format('H:i:s'),
                'total_hours' => round($durationHours, 2),
                'status' => 'present',
                'data_source' => 'biometric', // Must be one of: manual, biometric, hybrid
                'month' => $inTime->month,
                'year' => $inTime->year,
                'has_biometric_data' => true,
                'is_locked' => false
            ]
        );

        // Handle Rest Day if continuous shift
        if ($durationHours > 20 || ($isNextDay && $durationHours > 12)) {
            $this->markRestDay($empId, $outTime, $employee);
        }
    }

    private function markRestDay($empId, $outTime, $employee)
    {
        $restDate = $outTime->format('Y-m-d');
        $dateObj = Carbon::parse($restDate);
        
        // Check if record exists
        $exists = \App\Models\AttendanceRecord::where('payroll_id', $empId)
            ->where('date', $restDate)
            ->exists();
            
        if (!$exists) {
            \App\Models\AttendanceRecord::create([
                'payroll_id' => $empId,
                'employee_id' => $employee ? $employee->employee_id : null,
                'employee_email' => $employee ? $employee->email : null,
                'date' => $restDate,
                'month' => $dateObj->month,
                'year' => $dateObj->year,
                'status' => 'present',
                'total_hours' => 0,
                'data_source' => 'manual', // Auto-rest counts as manual adjustment/policy
                'is_locked' => false
            ]);
        }
    }
}
