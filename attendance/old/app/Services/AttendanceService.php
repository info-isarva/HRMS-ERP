<?php

namespace App\Services;

use App\Jobs\ProcessAttendanceRecords;
use App\Models\AttendanceBatch;
use App\Models\AttendanceRecord;
use App\Models\Employee;
use App\Models\LeaveApplication;
use App\Models\PublicHoliday;
use App\Models\Attendance; // Biometric attendance
use App\Models\AttendancePolicy;
use App\Models\DutyRoster;
use App\Models\Shift;
use App\Models\ManualPunch;
use App\Services\PayrollApiService;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AttendanceService
{
    protected $payrollApiService;

    public function __construct(PayrollApiService $payrollApiService)
    {
        $this->payrollApiService = $payrollApiService;
    }
    /**
     * Generate attendance records for a specific month and year.
     * 
     * @param int $month Month (1-12)
     * @param int $year Year (e.g., 2025)
     * @param int $initiatedBy User ID of the admin initiating the process
     * @param bool $lockRecords Whether to lock the records upon generation
     * @param string $mode Attendance processing mode (timestation, biometric, general)
     * @return AttendanceBatch
     */
    public function generateAttendanceRecords(int $month, int $year, int $initiatedBy, bool $lockRecords = false, string $mode = 'general'): AttendanceBatch
    {
        try {
            DB::beginTransaction();

            // Create a new batch
            $batch = AttendanceBatch::create([
                'month' => $month,
                'year' => $year,
                'status' => 'processing',
                'initiated_by' => $initiatedBy,
                'is_locked' => $lockRecords,
                'mode' => $mode,
            ]);

            // Get all active employees
            $totalEmployees = Employee::active()->count();
            $daysInMonth = $this->getDaysInMonth($month, $year);
            $batch->total_records = $totalEmployees * $daysInMonth;
            $batch->save();

            // For smaller datasets (under 100 employees), process synchronously for immediate results
            // This helps avoid redirect loops in the UI when records aren't ready yet
            if ($totalEmployees <= 10) {
                $employees = Employee::active()->get();
                $records = $this->prepareAttendanceRecords($employees, $month, $year, $batch->id, $mode);
                
                // Process synchronously
                $processedCount = 0;
                $failedCount = 0;
                
                foreach ($records as $record) {
                    try {
                        AttendanceRecord::updateOrCreate(
                            [
                                'payroll_id' => $record['payroll_id'],
                                'date' => $record['date'],
                            ],
                            [
                                'employee_id' => $record['employee_id'],
                                'employee_email' => $record['employee_email'],
                                'user_id' => $record['user_id'],
                                'status' => $record['status'],
                                'leave_type_id' => $record['leave_type_id'] ?? null,
                                'leave_application_id' => $record['leave_application_id'] ?? null,
                                'public_holiday_id' => $record['public_holiday_id'] ?? null,
                                'attendance_id' => $record['attendance_id'] ?? null,
                                'check_in_time' => $record['check_in_time'] ?? null,
                                'check_out_time' => $record['check_out_time'] ?? null,
                                'total_hours' => $record['total_hours'] ?? null,
                                'late_arrival_minutes' => $record['late_arrival_minutes'] ?? 0,
                                'early_departure_minutes' => $record['early_departure_minutes'] ?? 0,
                                'overtime_hours' => $record['overtime_hours'] ?? 0,
                                'undertime_hours' => $record['undertime_hours'] ?? 0,
                                'worked_on_holiday' => $record['worked_on_holiday'] ?? false,
                                'worked_on_weekend' => $record['worked_on_weekend'] ?? false,
                                'worked_on_leave' => $record['worked_on_leave'] ?? false,
                                'has_biometric_data' => $record['has_biometric_data'] ?? false,
                                'data_source' => $record['data_source'] ?? 'manual',
                                'shift_id' => $record['shift_id'] ?? null,
                                'scheduled_start_time' => $record['scheduled_start_time'] ?? null,
                                'scheduled_end_time' => $record['scheduled_end_time'] ?? null,
                                'month' => $month,
                                'year' => $year,
                                'batch_id' => $batch->id,
                                'is_locked' => $lockRecords,
                                'locked_at' => $lockRecords ? now() : null,
                                'locked_by' => $lockRecords ? $initiatedBy : null,
                                'mode' => $mode,
                            ]
                        );
                        $processedCount++;
                    } catch (\Exception $e) {
                        $failedCount++;
                        Log::error('Failed to process attendance record', [
                            'employee_id' => $record['employee_id'] ?? null,
                            'employee_email' => $record['employee_email'] ?? null,
                            'date' => $record['date'] ?? null,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
                
                // Update the batch with results
                $batch->processed_records = $processedCount;
                $batch->failed_records = $failedCount;
                $batch->status = 'completed';
                $batch->completed_at = now();
                $batch->save();
            } else {
                // For larger datasets, process in chunks asynchronously
                Employee::active()->chunk(100, function ($employees) use ($month, $year, $batch, $lockRecords, $mode) {
                    $records = $this->prepareAttendanceRecords($employees, $month, $year, $batch->id, $mode);
                    
                    // Process records in even smaller chunks
                    $chunks = array_chunk($records, 500);
                    foreach ($chunks as $chunk) {
                        ProcessAttendanceRecords::dispatch($batch->id, $chunk, $month, $year, $lockRecords);
                    }
                });
            }

            DB::commit();
            return $batch;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to generate attendance records', [
                'month' => $month,
                'year' => $year,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Prepare attendance records data for a set of employees.
     * 
     * @param \Illuminate\Database\Eloquent\Collection $employees
     * @param int $month
     * @param int $year
     * @param int $batchId
     * @return array
     */
    public function prepareAttendanceRecords($employees, int $month, int $year, int $batchId, string $mode = 'general'): array
    {
        $startDate = Carbon::createFromDate($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();
        $daysInMonth = $endDate->day;

        // Get payroll IDs for leave application matching
        $employeePayrollIds = $employees->pluck('payroll_id')->filter()->toArray();
        $employeeEmails = $employees->pluck('email')->filter()->toArray();
        
        Log::info('Preparing attendance records for employees', [
            'employee_payroll_ids_count' => count($employeePayrollIds),
            'employee_emails_count' => count($employeeEmails),
            'month' => $month,
            'year' => $year
        ]);

        // Get all leave applications for the month by email matching
        $leaveApplications = LeaveApplication::where('status', 'approved')
            ->whereIn('email_id', $employeeEmails)
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate])
                    ->orWhere(function ($q) use ($startDate, $endDate) {
                        $q->where('start_date', '<', $startDate)
                            ->where('end_date', '>', $endDate);
                    });
            })
            ->with(['leaveType'])
            ->get();

        // Get department-specific public holidays for the month
        // First get all unique department IDs from employees
        $departmentIds = $employees->pluck('department_id')->filter()->unique()->toArray();
        
        // Get public holidays assigned to these departments
        $publicHolidays = PublicHoliday::where('status', 'active')
            ->whereBetween('date', [$startDate, $endDate])
            ->whereHas('departments', function($query) use ($departmentIds) {
                $query->whereIn('department_id', $departmentIds);
            })
            ->with('departments')
            ->get()
            ->keyBy(function ($holiday) {
                return $holiday->date->format('Y-m-d');
            });

        // Get week-off configurations for all employees from payroll API
        try {
            $weekOffConfigurations = $this->payrollApiService->getEmployeeWeekOffsIndexedByPayrollId();
            Log::info('Retrieved week-off configurations for attendance generation', [
                'count' => count($weekOffConfigurations)
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch week-off configurations from payroll API during attendance generation', [
                'error' => $e->getMessage()
            ]);
            $weekOffConfigurations = []; // Fallback to empty array, will use default weekend logic
        }

        // Fetch biometric attendance data for the month
        $biometricAttendances = Attendance::whereIn('employee_payroll_id', $employeePayrollIds)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->get()
            ->keyBy(function ($attendance) {
                return $attendance->employee_payroll_id . '_' . $attendance->date->format('Y-m-d');
            });

        Log::info('Retrieved biometric attendance data for processing', [
            'count' => $biometricAttendances->count(),
            'sample_keys' => $biometricAttendances->keys()->take(5)->toArray(),
            'sources' => $biometricAttendances->pluck('source')->unique()->toArray()
        ]);

        // Fetch approved manual punches for the month
        $manualPunches = ManualPunch::whereIn('employee_payroll_id', $employeePayrollIds)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->where('status', 'approved')
            ->get()
            ->keyBy(function ($punch) {
                return $punch->employee_payroll_id . '_' . $punch->date->format('Y-m-d');
            });

        Log::info('Retrieved manual punch data for processing', [
            'count' => $manualPunches->count()
        ]);

        // Fetch duty rosters for the month
        $dutyRosters = DutyRoster::whereIn('employee_payroll_id', $employeePayrollIds)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->with('shift')
            ->get()
            ->keyBy(function ($roster) {
                $dateStr = is_string($roster->date) ? $roster->date : $roster->date->format('Y-m-d');
                return $roster->employee_payroll_id . '_' . $dateStr;
            });

        Log::info('Retrieved duty rosters for processing', [
            'count' => $dutyRosters->count()
        ]);

        // Get active attendance policy
        $policy = AttendancePolicy::getActivePolicy();

        $records = [];

        foreach ($employees as $employee) {
            // Skip if employee doesn't have payroll_id
            if (!$employee->payroll_id) {
                Log::warning('Skipping attendance record generation for employee without payroll_id', [
                    'employee_id' => $employee->employee_id,
                    'name' => $employee->name
                ]);
                continue;
            }

            // Generate a record for each day of the month
            for ($day = 1; $day <= $daysInMonth; $day++) {
                $date = Carbon::createFromDate($year, $month, $day);
                
                // Skip if employee hasn't joined yet or has already resigned
                if (($employee->date_of_joining && $date->lt($employee->date_of_joining)) || 
                    ($employee->date_of_resignation && $date->gte($employee->date_of_resignation))) {
                    continue;
                }

                $record = [
                    'payroll_id' => $employee->payroll_id,
                    'employee_id' => $employee->employee_id,
                    'employee_email' => $employee->email, // Can be null for employees without self portal
                    'user_id' => null, // Only used for created_by/updated_by tracking
                    'date' => $date->format('Y-m-d'),
                    'month' => $month,
                    'year' => $year,
                    'batch_id' => $batchId,
                    'status' => 'present',
                    'leave_type_id' => null,
                    'leave_application_id' => null,
                    'public_holiday_id' => null,
                    'attendance_id' => null,
                    'check_in_time' => null,
                    'check_out_time' => null,
                    'total_hours' => null,
                    'late_arrival_minutes' => 0,
                    'early_departure_minutes' => 0,
                    'overtime_hours' => 0,
                    'undertime_hours' => 0,
                    'worked_on_holiday' => false,
                    'worked_on_weekend' => false,
                    'worked_on_leave' => false,
                    'has_biometric_data' => false,
                    'data_source' => 'manual',
                    'shift_id' => null,
                    'scheduled_start_time' => null,
                    'scheduled_end_time' => null,
                ];

                // Find assigned shift from duty roster or employee default
                $rosterKey = $employee->payroll_id . '_' . $date->format('Y-m-d');
                $assignedRoster = $dutyRosters->get($rosterKey);
                
                if ($assignedRoster && $assignedRoster->shift) {
                    $record['shift_id'] = $assignedRoster->shift_id;
                    $record['scheduled_start_time'] = $assignedRoster->shift->start_time;
                    $record['scheduled_end_time'] = $assignedRoster->shift->end_time;
                } elseif ($employee->shift_id) {
                    // Use employee's default shift
                    $defaultShift = Shift::find($employee->shift_id);
                    if ($defaultShift) {
                        $record['shift_id'] = $defaultShift->id;
                        $record['scheduled_start_time'] = $defaultShift->start_time;
                        $record['scheduled_end_time'] = $defaultShift->end_time;
                    }
                }

                // Check for biometric attendance data
                $biometricKey = $employee->payroll_id . '_' . $date->format('Y-m-d');
                $biometricData = $biometricAttendances->get($biometricKey);

                // Determine if it's a week-off day
                $isWeekOff = false;
                if ($employee->payroll_id && isset($weekOffConfigurations[$employee->payroll_id])) {
                    $weekOffConfig = $weekOffConfigurations[$employee->payroll_id];
                    $dayOfWeek = $date->dayOfWeek; // 0 = Sunday, 1 = Monday, etc.
                    
                    if (isset($weekOffConfig['week_off_days']) && in_array($dayOfWeek, $weekOffConfig['week_off_days'])) {
                        $isWeekOff = true;
                    }
                } else {
                    // Fallback to default weekend if no payroll configuration found
                    $isWeekOff = $date->isWeekend();
                }

                // Determine if it's a public holiday
                $isPublicHoliday = false;
                $holidayId = null;
                $isFixedHoliday = false;
                
                if (isset($publicHolidays[$date->format('Y-m-d')])) {
                    $holiday = $publicHolidays[$date->format('Y-m-d')];
                    $isHolidayForEmployee = $holiday->departments->contains('id', $employee->department_id);
                    
                    if ($isHolidayForEmployee) {
                        if ($holiday->type === 'fixed') {
                            $isPublicHoliday = true;
                            $isFixedHoliday = true;
                            $holidayId = $holiday->id;
                        } else {
                            // Check if flexible holiday is approved
                            $hasApprovedFlexibleApplication = \App\Models\PublicHolidayApplication::where('payroll_id', $employee->payroll_id)
                                ->where('public_holiday_id', $holiday->id)
                                ->where('status', 'approved')
                                ->exists();
                                
                            if ($hasApprovedFlexibleApplication) {
                                $isPublicHoliday = true;
                                $isFixedHoliday = false;
                                $holidayId = $holiday->id;
                            }
                        }
                    }
                }

                // Check if employee has approved leave
                $hasLeave = false;
                $leaveTypeId = null;
                $leaveApplicationId = null;
                $dateStr = $date->format('Y-m-d');
                
                foreach ($leaveApplications as $leave) {
                    if (strtolower($leave->email_id) === strtolower($employee->email)) {
                        $leaveStartStr = $leave->start_date->format('Y-m-d');
                        $leaveEndStr = $leave->end_date->format('Y-m-d');
                        
                        if ($dateStr >= $leaveStartStr && $dateStr <= $leaveEndStr) {
                            $hasLeave = true;
                            $leaveTypeId = $leave->leave_type_id;
                            $leaveApplicationId = $leave->id;
                            break;
                        }
                    }
                }

                // NOW APPLY PRIORITY LOGIC BASED ON MODE
                // Check for manual punch data
                $manualPunchKey = $employee->payroll_id . '_' . $date->format('Y-m-d');
                $manualPunchData = $manualPunches->get($manualPunchKey);
                
                $useBiometric = ($mode === 'biometric' && $biometricData && in_array($biometricData->source, ['biometric_excel', 'biometric_device']));
                $useTimeStation = ($mode === 'timestation' && $biometricData && $biometricData->source === 'timestation_fetch');
                $usePortal = ($mode === 'portal_attendance' && $biometricData && $biometricData->source === 'self_attendance');
                
                if ($useBiometric || $useTimeStation || $usePortal) {
                    // Employee has punch data for the selected mode (highest priority)
                    $record['attendance_id'] = $biometricData->id;
                    $record['check_in_time'] = $biometricData->check_in_time;
                    $record['check_out_time'] = $biometricData->check_out_time;
                    $record['total_hours'] = $biometricData->total_hours;
                    $record['late_arrival_minutes'] = $biometricData->late_arrival_minutes ?? 0;
                    $record['early_departure_minutes'] = $biometricData->early_departure_minutes ?? 0;
                    $record['overtime_hours'] = $biometricData->overtime_hours ?? 0;
                    $record['undertime_hours'] = $biometricData->undertime_hours ?? 0;
                    $record['has_biometric_data'] = $usePortal ? false : true;
                    
                    // Override shift info from biometric if present
                    if ($biometricData->shift_id) {
                        $record['shift_id'] = $biometricData->shift_id;
                        $record['scheduled_start_time'] = $biometricData->scheduled_start_time;
                        $record['scheduled_end_time'] = $biometricData->scheduled_end_time;
                    }
                    
                    // Determine status based on biometric data
                    $record['status'] = $biometricData->status ?? 'present';
                    
                    // Check for special cases - worked on non-working days
                    if ($isWeekOff) {
                        $record['worked_on_weekend'] = true;
                        $record['data_source'] = $usePortal ? 'portal_hybrid' : 'hybrid';
                    }
                    
                    if ($isPublicHoliday) {
                        $record['worked_on_holiday'] = true;
                        $record['public_holiday_id'] = $holidayId;
                        $record['data_source'] = $usePortal ? 'portal_hybrid' : 'hybrid';
                    }
                    
                    if ($hasLeave) {
                        $record['worked_on_leave'] = true;
                        $record['leave_type_id'] = $leaveTypeId;
                        $record['leave_application_id'] = $leaveApplicationId;
                        $record['data_source'] = $usePortal ? 'portal_hybrid' : 'hybrid';
                    }
                    
                    $record['data_source'] = $record['worked_on_weekend'] || $record['worked_on_holiday'] || $record['worked_on_leave'] 
                        ? ($usePortal ? 'portal_hybrid' : 'hybrid') 
                        : ($usePortal ? 'portal' : ($useTimeStation ? 'timestation' : 'biometric'));
                } elseif ($manualPunchData && $mode !== 'general') {
                    // Employee has approved manual punch data (second priority, only if not in general mode)
                    $record['check_in_time'] = $manualPunchData->punch_in_time;
                    $record['check_out_time'] = $manualPunchData->punch_out_time;
                    
                    // Calculate total hours if both times are present
                    if ($manualPunchData->punch_in_time && $manualPunchData->punch_out_time) {
                        $checkIn = Carbon::parse($manualPunchData->punch_in_time);
                        $checkOut = Carbon::parse($manualPunchData->punch_out_time);
                        $record['total_hours'] = $checkOut->diffInHours($checkIn, true);
                    }
                    
                    $record['has_biometric_data'] = false;
                    $record['data_source'] = 'manual_punch';
                    $record['status'] = 'present';
                    
                    // Use shift info from manual punch if available
                    if ($manualPunchData->shift_id) {
                        $manualShift = Shift::find($manualPunchData->shift_id);
                        if ($manualShift) {
                            $record['shift_id'] = $manualShift->id;
                            $record['scheduled_start_time'] = $manualShift->start_time;
                            $record['scheduled_end_time'] = $manualShift->end_time;
                        }
                    }
                    
                    // Check for special cases
                    if ($isWeekOff) {
                        $record['worked_on_weekend'] = true;
                        $record['data_source'] = 'manual_punch_hybrid';
                    }
                    
                    if ($isPublicHoliday) {
                        $record['worked_on_holiday'] = true;
                        $record['public_holiday_id'] = $holidayId;
                        $record['data_source'] = 'manual_punch_hybrid';
                    }
                    
                    if ($hasLeave) {
                        $record['worked_on_leave'] = true;
                        $record['leave_type_id'] = $leaveTypeId;
                        $record['leave_application_id'] = $leaveApplicationId;
                        $record['data_source'] = 'manual_punch_hybrid';
                    }
                } else {
                    // No biometric/timestation data or general mode - use traditional logic
                    $record['data_source'] = $mode === 'general' ? 'general' : 'manual';
                    
                    if ($isWeekOff) {
                        $record['status'] = 'weekend';
                    } elseif ($isPublicHoliday) {
                        $record['status'] = $isFixedHoliday ? 'fixed_holiday' : 'flexible_holiday';
                        $record['public_holiday_id'] = $holidayId;
                    } elseif ($hasLeave) {
                        $record['status'] = 'absent';
                        $record['leave_type_id'] = $leaveTypeId;
                        $record['leave_application_id'] = $leaveApplicationId;
                    } else {
                        // In General mode, if no leave/holiday/weekend, employee is Present
                        // In other modes, if no punch data, employee is Absent (unauthorized)
                        if ($mode === 'general') {
                            $record['status'] = 'present';
                        } elseif ($mode === 'portal_attendance') {
                            $record['status'] = 'pm';
                            $record['leave_type_id'] = null;
                            $record['leave_application_id'] = null;
                        } else {
                            $record['status'] = 'absent';
                            $record['leave_type_id'] = null;
                            $record['leave_application_id'] = null;
                        }
                    }
                }

                $records[] = $record;
            }
        }

        return $records;
    }

    /**
     * Get number of days in a month.
     * 
     * @param int $month
     * @param int $year
     * @return int
     */
    protected function getDaysInMonth(int $month, int $year): int
    {
        return Carbon::createFromDate($year, $month, 1)->daysInMonth;
    }

    /**
     * Get attendance summary for a specific month and year.
     * 
     * @param int $monthSo
     * @param int $year
     * @return array
     */
    public function getAttendanceSummary(int $month, int $year): array
    {
        $startDate = Carbon::createFromDate($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();
        $daysInMonth = $endDate->day;
        
        $summary = [];
        
        // Get all employees who should have attendance records for this month
        // Include Active, Probation Period, Onboard, and Left employees (with date filtering)
        $employees = Employee::where(function($query) use ($startDate, $endDate) {
            $query->where('date_of_joining', '<=', $endDate)
                  ->where(function($subQuery) use ($startDate) {
                      $subQuery->where('status', '!=', 'Left')
                               ->orWhere('date_of_resignation', '>=', $startDate);
                  });
        })->with('department')->get();

        // Get all attendance records for the month
        $attendanceRecords = AttendanceRecord::forMonthYear($month, $year)
            ->with(['leaveType', 'publicHoliday'])
            ->get()
            ->groupBy(function($record) {
                return $record->payroll_id ?: '';
            });

        // Get leave applications to calculate LOP days
        $startDate = Carbon::createFromDate($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();
        
        $leaveApplicationsRaw = LeaveApplication::select([
                'id', 'user_id', 'email_id', 'leave_type_id', 'start_date', 'end_date', 
                'total_days', 'paid_days', 'lop_days', 'has_lop', 'status', 'reason'
            ])
            ->where('status', 'approved')
            ->where(function($q) use ($startDate, $endDate) {
                // Leave starts in the month
                $q->where(function($inner) use ($startDate, $endDate) {
                    $inner->whereDate('start_date', '>=', $startDate)
                          ->whereDate('start_date', '<=', $endDate);
                })
                // Or leave ends in the month
                ->orWhere(function($inner) use ($startDate, $endDate) {
                    $inner->whereDate('end_date', '>=', $startDate)
                          ->whereDate('end_date', '<=', $endDate);
                })
                // Or leave spans across the month
                ->orWhere(function($inner) use ($startDate, $endDate) {
                    $inner->whereDate('start_date', '<', $startDate)
                          ->whereDate('end_date', '>', $endDate);
                });
            })
            ->with(['leaveType', 'user'])
            ->get();
            
        // Group leave applications by email_id field from LeaveApplication table
        $leaveApplicationsByEmail = $leaveApplicationsRaw->groupBy(function($item) {
            // Use email_id from LeaveApplication table directly
            $email = $item->email_id ?? ($item->user->email ?? '');
            return strtolower($email);
        })->filter(function($group, $email) {
            return !empty($email);
        });
        
        // Calculate LOP totals per employee email for the month (keep for backward compatibility)
        $lopTotalsByEmail = [];
        foreach ($leaveApplicationsByEmail as $email => $applications) {
            $totalLopDays = $applications->where('has_lop', true)->sum('lop_days');
            if ($totalLopDays > 0) {
                $lopTotalsByEmail[$email] = $totalLopDays;
            }
        }

        // Calculate LOP totals per employee payroll_id for the month (NEW: payroll_id-based)
        $lopTotalsByPayrollId = [];
        // Convert email-based leave applications to payroll_id-based using employees table
        foreach ($leaveApplicationsByEmail as $email => $applications) {
            $employee = Employee::where('email', $email)->first();
            if ($employee && $employee->payroll_id) {
                $totalLopDays = $applications->where('has_lop', true)->sum('lop_days');
                if ($totalLopDays > 0) {
                    $lopTotalsByPayrollId[$employee->payroll_id] = $totalLopDays;
                }
            }
        }

        foreach ($employees as $employee) {
            // Get LOP days for this employee using payroll_id (preferred) or email (fallback)
            $employeeLopDays = 0;
            if ($employee->payroll_id && isset($lopTotalsByPayrollId[$employee->payroll_id])) {
                $employeeLopDays = $lopTotalsByPayrollId[$employee->payroll_id];
            } elseif ($employee->email && isset($lopTotalsByEmail[strtolower($employee->email)])) {
                $employeeLopDays = $lopTotalsByEmail[strtolower($employee->email)];
            }
            
            $employeeSummary = [
                'employee_id' => $employee->employee_id,
                'payroll_id' => $employee->payroll_id,
                'name' => $employee->name,
                'email' => $employee->email,
                'status' => $employee->status,
                'department' => $employee->department->name ?? 'No Department',
                'designation' => $employee->designation,
                'total_days' => $daysInMonth,
                'present_days' => 0,
                'leave_days' => 0,
                'weekend_days' => 0,
                'public_holiday_days' => 0,
                'absent_days' => 0,
                'lop_days' => 0, // Will be calculated from both sources
                'salary_days' => 0, // Will be calculated after counting all records
                'leave_breakdown' => []
            ];

            $leaveTypesCount = [];
            $lopStatusCount = 0; // Count LOP status records

            // Get attendance records for this employee by payroll_id
            $employeePayrollId = $employee->payroll_id ?: '';
            if ($employee->payroll_id && isset($attendanceRecords[$employeePayrollId])) {
                foreach ($attendanceRecords[$employeePayrollId] as $record) {
                    switch ($record->status) {
                        case 'present':
                            $employeeSummary['present_days']++;
                            break;
                        case 'absent':
                            if ($record->leave_type_id) {
                                // Count as leave (not just absent) if it has a leave type
                                $employeeSummary['leave_days']++;
                                $leaveTypeName = $record->leaveType->name ?? 'Unknown';
                                $leaveTypesCount[$leaveTypeName] = ($leaveTypesCount[$leaveTypeName] ?? 0) + 1;
                            } else {
                                // Count as absent if no leave type
                                $employeeSummary['absent_days']++;
                            }
                            break;
                        case 'lop':
                            $lopStatusCount++; // Count LOP status records
                            $employeeSummary['leave_days']++; // LOP is also a type of leave
                            if ($record->leave_type_id) {
                                $leaveTypeName = $record->leaveType->name ?? 'LOP';
                                $leaveTypesCount[$leaveTypeName] = ($leaveTypesCount[$leaveTypeName] ?? 0) + 1;
                            }
                            break;
                        case 'weekend':
                            $employeeSummary['weekend_days']++;
                            break;
                        case 'fixed_holiday':
                        case 'flexible_holiday':
                        case 'public_holiday': // For backward compatibility
                            $employeeSummary['public_holiday_days']++;
                            break;
                        case 'leave': // For backward compatibility with existing records
                            $employeeSummary['leave_days']++;
                            $leaveTypeName = $record->leaveType->name ?? 'Unknown';
                            $leaveTypesCount[$leaveTypeName] = ($leaveTypesCount[$leaveTypeName] ?? 0) + 1;
                            break;
                    }
                }
            }
            
            // Calculate total LOP days = LOP from leave applications + LOP status records
            $totalLopDays = $employeeLopDays + $lopStatusCount;
            $employeeSummary['lop_days'] = $totalLopDays;
            
            // Calculate Salary Days = Total Days in Month - Total LOP Days - Absent Days
            $employeeSummary['salary_days'] = $daysInMonth - $totalLopDays - $employeeSummary['absent_days'];
            
            // Convert leave type counts to array format
            foreach ($leaveTypesCount as $typeName => $count) {
                $employeeSummary['leave_breakdown'][] = [
                    'type' => $typeName,
                    'count' => $count
                ];
            }

            $summary[] = $employeeSummary;
        }

        return $summary;
    }

    /**
     * Lock attendance records for a specific month and year.
     * 
     * @param int $month
     * @param int $year
     * @param int $lockedBy
     * @return bool
     */
    public function lockAttendanceRecords(int $month, int $year, int $lockedBy): bool
    {
        try {
            DB::beginTransaction();

            // Update all records for the month to be locked
            AttendanceRecord::forMonthYear($month, $year)
                ->unlocked()
                ->update([
                    'is_locked' => true,
                    'locked_at' => now(),
                    'locked_by' => $lockedBy
                ]);

            // Update the batch if it exists
            $batch = AttendanceBatch::forMonthYear($month, $year)->latest()->first();
            if ($batch) {
                $batch->update([
                    'is_locked' => true,
                    'completed_at' => now()
                ]);
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to lock attendance records', [
                'month' => $month,
                'year' => $year,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}
