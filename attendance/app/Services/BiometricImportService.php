<?php

namespace App\Services;

use App\Services\BiometricParsers\BiometricParserInterface;
use App\Services\BiometricParsers\ZKTecoParser;
use App\Services\BiometricParsers\ESSLParser;
use App\Services\BiometricParsers\RealtimeParser;
use App\Services\BiometricParsers\GenericCSVParser;
use App\Models\Attendance;
use App\Models\AttendancePolicy;
use App\Models\Employee;
use App\Models\DutyRoster;
use App\Models\Shift;
use App\Models\Overtime;
use App\Models\ManualPunch;
use Carbon\Carbon;

class BiometricImportService
{
    private array $parsers = [];
    private array $errors = [];
    private int $processed = 0;
    private int $imported = 0;
    private int $updated = 0;

    public function __construct()
    {
        $this->registerParsers();
    }

    private function registerParsers(): void
    {
        $this->parsers = [
            'zkteco' => new ZKTecoParser(),
            'essl' => new ESSLParser(),
            'realtime' => new RealtimeParser(),
            'generic_csv' => new GenericCSVParser(),
        ];
    }

    /**
     * Get all available parsers
     */
    public function getAvailableParsers(): array
    {
        return $this->parsers;
    }

    /**
     * Get parser by key
     */
    public function getParser(string $key): ?BiometricParserInterface
    {
        return $this->parsers[$key] ?? null;
    }

    /**
     * Auto-detect format from file
     */
    public function detectFormat(string $filePath): ?string
    {
        foreach ($this->parsers as $key => $parser) {
            if ($parser->validate($filePath)) {
                return $key;
            }
        }

        return null;
    }

    /**
     * Import attendance from biometric file
     */
    public function import(string $filePath, string $formatKey): array
    {
        $this->reset();

        $parser = $this->getParser($formatKey);
        if (!$parser) {
            throw new \Exception("Invalid format: {$formatKey}");
        }

        // Parse the file
        $records = $parser->parse($filePath);

        // Process each record
        foreach ($records as $record) {
            try {
                $this->processRecord($record);
                $this->processed++;
            } catch (\Exception $e) {
                $this->errors[] = [
                    'employee_id' => $record['employee_id'] ?? 'Unknown',
                    'date' => $record['date'] ?? 'Unknown',
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $this->getResults();
    }

    private function processRecord(array $record): void
    {
        // Validate employee
        $employee = Employee::where('employee_id', $record['employee_id'])
            ->orWhere('payroll_id', $record['employee_id'])
            ->first();
        if (!$employee) {
            throw new \Exception("Employee with ID {$record['employee_id']} not found");
        }

        // Parse date
        $date = Carbon::parse($record['date'])->format('Y-m-d');

        // *** CHECK FOR MANUAL PUNCH FIRST ***
        $manualPunch = ManualPunch::where('employee_payroll_id', $employee->payroll_id)
            ->where('date', $date)
            ->where('status', 'approved')
            ->first();

        // If manual punch exists, use it as the source of truth
        if ($manualPunch) {
            $checkInTime = $manualPunch->punch_in_time;
            $checkOutTime = $manualPunch->punch_out_time;
            $source = 'manual_punch';
        } else {
            // Parse times from biometric data
            $checkInTime = isset($record['check_in']) ? $record['check_in'] : null;
            $checkOutTime = isset($record['check_out']) ? $record['check_out'] : null;
            $source = 'biometric_device';
        }

        if (!$checkInTime && !$checkOutTime) {
            throw new \Exception("No check-in or check-out time provided");
        }

        // Calculate total hours
        $totalHours = null;
        if ($checkInTime && $checkOutTime) {
            $totalHours = $this->calculateTotalHours($checkInTime, $checkOutTime);
        }

        // Find assigned shift (prefer manual punch shift if available)
        $assignedShift = null;
        if ($manualPunch && $manualPunch->shift_id) {
            $assignedShift = Shift::find($manualPunch->shift_id);
        }
        if (!$assignedShift) {
            $assignedShift = $this->findEmployeeShift($employee->payroll_id, $date);
        }
        $shiftId = $assignedShift ? $assignedShift->id : null;

        // Get active attendance policy
        $policy = AttendancePolicy::getActivePolicy();

        // Calculate attendance details with policy
        $attendanceDetails = $this->calculateAttendanceDetails($checkInTime, $checkOutTime, $assignedShift, $totalHours, $policy);

        // Prepare raw data
        $rawData = [
            'device_id' => $record['device_id'] ?? null,
            'employee_name' => $record['employee_name'] ?? null,
            'assigned_shift' => $assignedShift ? $assignedShift->name : null,
            'expected_start_time' => $assignedShift ? $assignedShift->start_time : null,
            'expected_end_time' => $assignedShift ? $assignedShift->end_time : null,
            'policy_applied' => $policy ? $policy->name : 'No Policy',
            'manual_punch_id' => $manualPunch ? $manualPunch->id : null,
        ];

        // Check if record exists
        $existing = Attendance::where('employee_payroll_id', $employee->payroll_id)
            ->where('date', $date)
            ->first();

        // Create or update
        $attendance = Attendance::updateOrCreate(
            [
                'employee_payroll_id' => $employee->payroll_id,
                'date' => $date,
            ],
            [
                'check_in_time' => $checkInTime,
                'check_out_time' => $checkOutTime,
                'total_hours' => $totalHours,
                'status' => $this->determineStatus($attendanceDetails['status']),
                'shift_id' => $shiftId,
                'raw_data' => array_filter($rawData),
                'processed_at' => now(),
                'source' => $source,
                'scheduled_start_time' => $attendanceDetails['expected_start'] ?? null,
                'scheduled_end_time' => $attendanceDetails['expected_end'] ?? null,
                'late_arrival_minutes' => $attendanceDetails['late_arrival_minutes'] ?? 0,
                'early_departure_minutes' => $attendanceDetails['early_departure_minutes'] ?? 0,
                'overtime_hours' => $attendanceDetails['overtime_hours'] ?? 0,
                'undertime_hours' => $attendanceDetails['undertime_hours'] ?? 0,
                'is_late_arrival' => !empty($attendanceDetails['late_arrival_minutes']),
                'is_early_arrival' => $attendanceDetails['is_early_arrival'] ?? false,
                'is_late_departure' => $attendanceDetails['is_late_departure'] ?? false,
                'is_early_departure' => !empty($attendanceDetails['early_departure_minutes']),
                'is_overtime' => !empty($attendanceDetails['overtime_hours']),
            ]
        );

        if ($existing) {
            $this->updated++;
        } else {
            $this->imported++;
        }

        // Auto-create/update OT record if overtime hours exist
        if (!empty($attendanceDetails['overtime_hours']) && $attendanceDetails['overtime_hours'] > 0) {
            $this->updateOvertimeRecord($employee->payroll_id, $date, $attendanceDetails['overtime_hours'], $policy);
        }
    }

    private function findEmployeeShift(string $payrollId, string $date): ?Shift
    {
        // Find from duty roster
        $roster = DutyRoster::where('employee_payroll_id', $payrollId)
            ->where('date', $date)
            ->first();

        if ($roster && $roster->shift_id) {
            return Shift::find($roster->shift_id);
        }

        // Try to find employee's most recent duty roster shift (within last 30 days)
        $recentRoster = DutyRoster::where('employee_payroll_id', $payrollId)
            ->where('date', '<=', $date)
            ->where('date', '>=', Carbon::parse($date)->subDays(30)->format('Y-m-d'))
            ->orderBy('date', 'desc')
            ->first();
        
        if ($recentRoster && $recentRoster->shift_id) {
            return Shift::find($recentRoster->shift_id);
        }

        // Fallback: Use the first shift as default
        return Shift::first();
    }

    private function calculateTotalHours(string $checkIn, string $checkOut): float
    {
        try {
            $checkInTime = strtotime($checkIn);
            $checkOutTime = strtotime($checkOut);

            if ($checkOutTime <= $checkInTime) {
                return 0;
            }

            $totalSeconds = $checkOutTime - $checkInTime;
            return round($totalSeconds / 3600, 2);
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function calculateAttendanceDetails(?string $checkIn, ?string $checkOut, ?Shift $shift, ?float $totalHours = null, ?AttendancePolicy $policy = null): array
    {
        $details = [
            'status' => 'present',
            'late_arrival_minutes' => 0,
            'early_departure_minutes' => 0,
            'overtime_hours' => 0,
            'undertime_hours' => 0,
            'is_early_arrival' => false,
            'is_late_departure' => false,
            'expected_start' => null,
            'expected_end' => null,
        ];

        if (!$shift) {
            return $details;
        }

        $details['expected_start'] = $shift->start_time;
        $details['expected_end'] = $shift->end_time;

        $expectedStart = strtotime($shift->start_time);
        $expectedEnd = strtotime($shift->end_time);

        // Check late arrival
        if ($checkIn) {
            $checkInTimestamp = strtotime($checkIn);
            if ($checkInTimestamp > $expectedStart) {
                $details['late_arrival_minutes'] = round(($checkInTimestamp - $expectedStart) / 60);
            } elseif ($checkInTimestamp < $expectedStart) {
                $details['is_early_arrival'] = true;
            }
        }

        // Check early/late departure
        if ($checkOut) {
            $checkOutTimestamp = strtotime($checkOut);
            if ($checkOutTimestamp < $expectedEnd) {
                $details['early_departure_minutes'] = round(($expectedEnd - $checkOutTimestamp) / 60);
                $details['undertime_hours'] = round($details['early_departure_minutes'] / 60, 2);
            } elseif ($checkOutTimestamp > $expectedEnd) {
                $details['is_late_departure'] = true;
                
                // Apply OT policy if exists
                if ($policy && $policy->enable_overtime) {
                    $overtimeMinutes = ($checkOutTimestamp - $expectedEnd) / 60;
                    // Only count OT if beyond the start threshold
                    if ($overtimeMinutes > $policy->overtime_start_after_minutes) {
                        $details['overtime_hours'] = round(($overtimeMinutes - $policy->overtime_start_after_minutes) / 60, 2);
                        // Apply max limit
                        $details['overtime_hours'] = min($details['overtime_hours'], $policy->maximum_overtime_hours_per_day);
                    }
                } else {
                    $overtimeMinutes = ($checkOutTimestamp - $expectedEnd) / 60;
                    $details['overtime_hours'] = round($overtimeMinutes / 60, 2);
                }
            }
        }

        // Apply policy rules to determine final status
        if ($policy) {
            $details['status'] = $policy->determineStatus([
                'late_arrival_minutes' => $details['late_arrival_minutes'],
                'early_departure_minutes' => $details['early_departure_minutes'],
                'total_hours' => $totalHours,
                'check_in_time' => $checkIn,
                'check_out_time' => $checkOut,
                'date' => $details['date'] ?? null,
            ]);
        } else {
            // Fallback to old logic if no policy
            if ($details['late_arrival_minutes'] > 0) {
                $details['status'] = 'late';
            } elseif ($details['early_departure_minutes'] > 0) {
                $details['status'] = 'early_departure';
            } else {
                $details['status'] = 'present';
            }
        }

        return $details;
    }

    private function determineStatus(string $status): string
    {
        $statusMap = [
            'present' => 'present',
            'late' => 'late',
            'early_departure' => 'early_departure',
            'absent' => 'absent',
            'half_day' => 'half_day',
            'overtime' => 'overtime',
        ];

        return $statusMap[strtolower($status)] ?? 'present';
    }

    private function reset(): void
    {
        $this->errors = [];
        $this->processed = 0;
        $this->imported = 0;
        $this->updated = 0;
    }

    /**
     * Update overtime record for employee (auto-accumulate monthly OT from biometric)
     */
    private function updateOvertimeRecord(string $payrollId, string $date, float $otHours, ?AttendancePolicy $policy): void
    {
        $carbonDate = Carbon::parse($date);
        $month = $carbonDate->month;
        $year = $carbonDate->year;

        // Get or create overtime record for the month
        $overtime = Overtime::firstOrCreate(
            [
                'employee_payroll_id' => $payrollId,
                'month' => $month,
                'year' => $year,
            ],
            [
                'overtime_hours' => 0,
                'calculated_ot_hours' => 0,
                'approval_status' => 'pending',
            ]
        );

        // Calculate total OT hours for this month from all attendances
        $totalMonthlyOt = Attendance::where('employee_payroll_id', $payrollId)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->sum('overtime_hours');

        // Update calculated OT hours
        $overtime->calculated_ot_hours = round($totalMonthlyOt, 2);
        
        // If not manually overridden, update the main overtime_hours field as well
        if (!$overtime->is_manually_overridden) {
            $overtime->overtime_hours = $overtime->calculated_ot_hours;
            // Auto-approve if policy says so and it's weekend OT
            if ($policy && $policy->auto_approve_weekend_overtime) {
                $overtime->approval_status = 'approved';
                $overtime->approved_at = now();
            }
        }

        $overtime->save();
    }

    public function getResults(): array
    {
        return [
            'success' => empty($this->errors),
            'processed' => $this->processed,
            'imported' => $this->imported,
            'updated' => $this->updated,
            'errors' => $this->errors,
        ];
    }
}
