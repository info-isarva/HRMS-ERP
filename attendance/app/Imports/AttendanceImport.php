<?php

namespace App\Imports;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Shift;
use App\Models\DutyRoster;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Carbon\Carbon;

class AttendanceImport implements ToCollection, WithHeadingRow, WithValidation
{
    private $processed = 0;
    private $errors = [];

    /**
     * @param Collection $collection
     */
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            try {
                $this->processRow($row);
                $this->processed++;
            } catch (\Exception $e) {
                $this->errors[] = [
                    'row' => $this->processed + 1,
                    'error' => $e->getMessage(),
                    'data' => $row->toArray()
                ];
            }
        }
    }

    private function processRow($row)
    {
        // Skip empty rows
        if (empty($row['payroll_id']) && empty($row['employee_id'])) {
            return;
        }

        // Get employee ID (support both employee_id and payroll_id columns)
        $employeeId = $row['payroll_id'] ?? $row['employee_id'] ?? null;
        if (!$employeeId) {
            throw new \Exception('Employee ID or Payroll ID is required');
        }

        // Check if employee exists
        $employee = Employee::where('payroll_id', $employeeId)->first();
        if (!$employee) {
            throw new \Exception("Employee with payroll ID {$employeeId} not found");
        }

        // Parse date
        $date = $this->parseDate($row['date'] ?? $row['attendance_date'] ?? null);
        if (!$date) {
            throw new \Exception('Invalid date format');
        }

        // Parse times
        $checkInTime = $this->parseTime($row['check_in'] ?? $row['check_in_time'] ?? null);
        $checkOutTime = $this->parseTime($row['check_out'] ?? $row['check_out_time'] ?? null);

        // Calculate total hours
        $totalHours = null;
        if ($checkInTime && $checkOutTime) {
            $totalHours = $this->calculateTotalHours($checkInTime, $checkOutTime);
        }

        // Find employee's assigned shift for this date
        $assignedShift = $this->findEmployeeShift($employee->payroll_id, $date);
        $shiftId = $assignedShift ? $assignedShift->id : null;

        // Calculate detailed attendance status and metrics
        $attendanceDetails = $this->calculateAttendanceDetails($checkInTime, $checkOutTime, $assignedShift);

        // Prepare raw data for storage
        $rawData = [
            'original_employee_id' => $row['employee_id'] ?? null,
            'original_date' => $row['date'] ?? $row['attendance_date'] ?? null,
            'original_check_in' => $row['check_in'] ?? $row['check_in_time'] ?? null,
            'original_check_out' => $row['check_out'] ?? $row['check_out_time'] ?? null,
            'shift_name' => $row['shift_name'] ?? null,
            'department' => $row['department'] ?? null,
            'location' => $row['location'] ?? null,
            'biometric_device' => $row['device'] ?? $row['biometric_device'] ?? null,
            'assigned_shift' => $assignedShift ? $assignedShift->name : null,
            'expected_start_time' => $assignedShift ? $assignedShift->start_time : null,
            'expected_end_time' => $assignedShift ? $assignedShift->end_time : null,
        ];

        // Map human readable status to DB enum values
        $statusEnum = $this->mapStatusToEnum($attendanceDetails['status']);

        // Boolean flags
        $isLate = !empty($attendanceDetails['late_arrival_minutes']);
        $isEarlyDeparture = !empty($attendanceDetails['early_departure_minutes']);
        $isOvertime = !empty($attendanceDetails['overtime_hours']);

        // Additional flags: early arrival (arrived before scheduled start), late departure (left after scheduled end)
        $isEarlyArrival = false;
        $isLateDeparture = false;

        if ($assignedShift) {
            if ($checkInTime) {
                $isEarlyArrival = strtotime($checkInTime) < strtotime($assignedShift->start_time);
            }
            if ($checkOutTime) {
                $isLateDeparture = strtotime($checkOutTime) > strtotime($assignedShift->end_time);
            }
        }

        // Create or update attendance record with detailed information
        Attendance::updateOrCreate(
            [
                'employee_payroll_id' => $employee->payroll_id,
                'date' => $date,
            ],
            [
                'check_in_time' => $checkInTime,
                'check_out_time' => $checkOutTime,
                'total_hours' => $totalHours,
                'status' => $statusEnum,
                'shift_id' => $shiftId,
                'raw_data' => array_filter($rawData), // Remove null values
                'processed_at' => now(),
                'source' => 'biometric_excel',
                'notes' => $row['notes'] ?? null,
                // Additional calculated fields (match DB column names)
                'scheduled_start_time' => $attendanceDetails['expected_start'] ?? null,
                'scheduled_end_time' => $attendanceDetails['expected_end'] ?? null,
                // store numeric metrics as numbers (default 0)
                'late_arrival_minutes' => $attendanceDetails['late_arrival_minutes'] ?? 0,
                'early_departure_minutes' => $attendanceDetails['early_departure_minutes'] ?? 0,
                'overtime_hours' => $attendanceDetails['overtime_hours'] ?? 0,
                'undertime_hours' => $attendanceDetails['undertime_hours'] ?? 0,
                'is_late_arrival' => $isLate,
                'is_early_arrival' => $isEarlyArrival,
                'is_late_departure' => $isLateDeparture,
                'is_early_departure' => $isEarlyDeparture,
                'is_overtime' => $isOvertime,
            ]
        );
    }

    /**
     * Map various human-readable statuses returned by the details calculator to the DB enum.
     */
    private function mapStatusToEnum($status)
    {
        if (!$status) return 'present';

        $s = strtolower($status);
        if (strpos($s, 'absent') !== false || strpos($s, 'no shift') !== false) {
            return 'absent';
        }

        if (strpos($s, 'late') !== false) {
            return 'late';
        }

        if (strpos($s, 'early') !== false || strpos($s, 'early departure') !== false) {
            return 'early_departure';
        }

        if (strpos($s, 'half') !== false) {
            return 'half_day';
        }

        if (strpos($s, 'ot') !== false || strpos($s, 'overtime') !== false) {
            return 'overtime';
        }

        if (strpos($s, 'on time') !== false || strpos($s, 'present') !== false) {
            return 'present';
        }

        // Default
        return 'present';
    }

    private function parseDate($dateString)
    {
        if (!$dateString) return null;

        try {
            // Try different date formats
            $formats = ['Y-m-d', 'd/m/Y', 'm/d/Y', 'd-m-Y', 'm-d-Y'];
            foreach ($formats as $format) {
                $date = Carbon::createFromFormat($format, $dateString);
                if ($date) return $date->format('Y-m-d');
            }

            // Try to parse as Excel date (if it's a number)
            if (is_numeric($dateString)) {
                return Carbon::createFromTimestamp(($dateString - 25569) * 86400)->format('Y-m-d');
            }

            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    private function parseTime($timeString)
    {
        if (!$timeString || empty(trim($timeString))) {
            return null;
        }

        $timeString = trim($timeString);

        try {
            // First try direct H:i:s format
            if (preg_match('/^(\d{1,2}):(\d{2}):(\d{2})$/', $timeString, $matches)) {
                $hour = (int)$matches[1];
                $minute = (int)$matches[2];
                $second = (int)$matches[3];

                // Validate time ranges
                if ($hour >= 0 && $hour <= 23 && $minute >= 0 && $minute <= 59 && $second >= 0 && $second <= 59) {
                    return sprintf('%02d:%02d:%02d', $hour, $minute, $second);
                }
            }

            // Try other formats with Carbon
            $formats = ['H:i', 'H:i:s', 'h:i A', 'h:i:s A'];
            foreach ($formats as $format) {
                $time = Carbon::createFromFormat($format, $timeString);
                if ($time) {
                    return $time->format('H:i:s');
                }
            }

            // Try to parse as Excel time (decimal)
            if (is_numeric($timeString)) {
                $hours = floor($timeString * 24);
                $minutes = floor(($timeString * 24 - $hours) * 60);
                $seconds = floor((($timeString * 24 - $hours) * 60 - $minutes) * 60);
                return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
            }

            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    private function calculateTotalHours($checkIn, $checkOut)
    {
        if (!$checkIn || !$checkOut) return 0;

        $checkInTime = strtotime($checkIn);
        $checkOutTime = strtotime($checkOut);

        if ($checkOutTime <= $checkInTime) return 0;

        $totalSeconds = $checkOutTime - $checkInTime;
        return round($totalSeconds / 3600, 2);
    }

    private function findShiftId($shiftIdentifier)
    {
        if (!$shiftIdentifier) return null;

        // Try to find by ID first
        if (is_numeric($shiftIdentifier)) {
            $shift = Shift::find($shiftIdentifier);
            if ($shift) return $shift->id;
        }

        // Try to find by name
        $shift = Shift::where('name', 'like', "%{$shiftIdentifier}%")->first();
        return $shift ? $shift->id : null;
    }

    private function findEmployeeShift($payrollId, $date)
    {
        // Find duty roster entry for this employee on this date
        $dutyRoster = DutyRoster::where('employee_payroll_id', $payrollId)
            ->where('date', $date)
            ->with('shift')
            ->first();

        if ($dutyRoster && $dutyRoster->shift) {
            return $dutyRoster->shift;
        }

        // Fallback: try to find a sensible default shift
        // 1) If there's a "Morning" shift, prefer that
        $default = Shift::where('name', 'like', '%Morning%')->first();
        if ($default) return $default;

        // 2) Otherwise, return the first configured shift (if any)
        return Shift::orderBy('id')->first();
    }

    private function calculateAttendanceDetails($checkInTime, $checkOutTime, $assignedShift)
    {
        $details = [
            'status' => 'Absent',
            'expected_start' => null,
            'expected_end' => null,
            'late_arrival_minutes' => null,
            'early_departure_minutes' => null,
            'overtime_hours' => null,
            'undertime_hours' => null,
        ];

        // If no shift assigned, mark as no shift
        if (!$assignedShift) {
            $details['status'] = 'No Shift Assigned';
            return $details;
        }

        // Set expected times
        $details['expected_start'] = $assignedShift->start_time;
        $details['expected_end'] = $assignedShift->end_time;

        // If no check-in/check-out, mark as absent
        if (!$checkInTime && !$checkOutTime) {
            $details['status'] = 'Absent';
            return $details;
        }

        // If only check-in, mark as incomplete
        if ($checkInTime && !$checkOutTime) {
            $details['status'] = 'Incomplete (No Check-out)';
            return $details;
        }

        // If only check-out, mark as incomplete
        if (!$checkInTime && $checkOutTime) {
            $details['status'] = 'Incomplete (No Check-in)';
            return $details;
        }

        // Both check-in and check-out present, calculate detailed status
        $expectedStart = Carbon::createFromFormat('H:i:s', $assignedShift->start_time);
        $expectedEnd = Carbon::createFromFormat('H:i:s', $assignedShift->end_time);

        // Handle overnight shifts (end time is next day)
        if ($expectedEnd->lessThan($expectedStart)) {
            $expectedEnd->addDay();
        }

        $actualCheckIn = Carbon::createFromFormat('H:i:s', $checkInTime);
        $actualCheckOut = Carbon::createFromFormat('H:i:s', $checkOutTime);

        // Handle overnight actual attendance
        if ($actualCheckOut->lessThan($actualCheckIn)) {
            $actualCheckOut->addDay();
        }

        // Calculate late arrival
        $lateArrivalMinutes = 0;
        if ($actualCheckIn->greaterThan($expectedStart)) {
            $lateArrivalMinutes = $expectedStart->diffInMinutes($actualCheckIn);
        }

        // Calculate early departure
        $earlyDepartureMinutes = 0;
        if ($actualCheckOut->lessThan($expectedEnd)) {
            $earlyDepartureMinutes = $expectedEnd->diffInMinutes($actualCheckOut);
        }

        // Calculate overtime (total hours worked minus scheduled hours)
        $scheduledDurationMinutes = $expectedStart->diffInMinutes($expectedEnd);
        $actualWorkedMinutes = $actualCheckIn->diffInMinutes($actualCheckOut);
        $overtimeMinutes = max(0, $actualWorkedMinutes - $scheduledDurationMinutes);

        // Calculate undertime (worked less than expected shift duration)
        $undertimeMinutes = max(0, $scheduledDurationMinutes - $actualWorkedMinutes);

        // Determine overall status
        $status = 'Present';

        if ($lateArrivalMinutes > 0) {
            $status = 'Late Arrival';
        }

        if ($earlyDepartureMinutes > 0) {
            $status = $status === 'Late Arrival' ? 'Late & Early Departure' : 'Early Departure';
        }

        if ($overtimeMinutes > 0) {
            $status .= ' (OT)';
        }

        if ($undertimeMinutes > 0 && $status === 'Present') {
            $status = 'Undertime';
        }

        // If no issues, mark as on time
        if ($lateArrivalMinutes === 0 && $earlyDepartureMinutes === 0 && $overtimeMinutes === 0) {
            $status = 'On Time';
        }

        $details['status'] = $status;
        $details['late_arrival_minutes'] = $lateArrivalMinutes > 0 ? $lateArrivalMinutes : null;
        $details['early_departure_minutes'] = $earlyDepartureMinutes > 0 ? $earlyDepartureMinutes : null;
        $details['overtime_hours'] = $overtimeMinutes > 0 ? round($overtimeMinutes / 60, 2) : null;
        $details['undertime_hours'] = $undertimeMinutes > 0 ? round($undertimeMinutes / 60, 2) : null;

        return $details;
    }

    /**
     * Validation rules for each row
     */
    public function rules(): array
    {
        return [
            'payroll_id' => 'required_without:employee_id',
            'employee_id' => 'required_without:payroll_id',
            'date' => 'required_without:attendance_date',
            'attendance_date' => 'required_without:date',
        ];
    }

    /**
     * Custom validation messages
     */
    public function customValidationMessages()
    {
        return [
            'payroll_id.required_without' => 'Either Payroll ID or Employee ID is required',
            'employee_id.required_without' => 'Either Employee ID or Payroll ID is required',
            'date.required_without' => 'Either Date or Attendance Date is required',
            'attendance_date.required_without' => 'Either Attendance Date or Date is required',
        ];
    }

    /**
     * Get processing results
     */
    public function getResults()
    {
        return [
            'processed' => $this->processed,
            'errors' => $this->errors,
            'success' => count($this->errors) === 0
        ];
    }
}
