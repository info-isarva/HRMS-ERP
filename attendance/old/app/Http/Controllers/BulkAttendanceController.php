<?php

namespace App\Http\Controllers;

use App\Models\AttendanceBatch;
use App\Models\AttendanceRecord;
use App\Models\Employee;
use App\Models\LeaveApplication;
use App\Models\LeaveType;
use App\Models\PublicHoliday;
use App\Models\PublicHolidayApplication;
use App\Services\AttendanceService;
use App\Services\PayrollApiService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BulkAttendanceController extends Controller
{
    protected $attendanceService;
    protected $payrollApiService;

    public function __construct(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
        $this->payrollApiService = app(PayrollApiService::class);
        // Since we already have this middleware in the route groups, we don't need to add it here
        // to avoid potential middleware duplication issues
    }

    /**
     * Display the bulk attendance page.
     */
    public function index()
    {
        $months = [
            1 => 'January',
            2 => 'February',
            3 => 'March',
            4 => 'April',
            5 => 'May',
            6 => 'June',
            7 => 'July',
            8 => 'August',
            9 => 'September',
            10 => 'October',
            11 => 'November',
            12 => 'December'
        ];

        $currentYear = Carbon::now()->year;
        $years = range($currentYear - 2, $currentYear + 2);

        return view('admin.attendance.bulk-index', compact('months', 'years'));
    }

    /**
     * Generate or fetch attendance data for preview.
     */
    public function preview(Request $request)
    {
        $request->validate([
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer|min:2000',
            'mode' => 'nullable|in:timestation,biometric,general,portal_attendance',
        ]);

        $month = $request->input('month');
        $year = $request->input('year');
        $mode = $request->input('mode', 'general'); // Default to general

        $startDate = Carbon::createFromDate($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();

        // Check for pending leaves
        $pendingLeaves = LeaveApplication::where('status', 'pending')
            ->where(function($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                      ->orWhereBetween('end_date', [$startDate, $endDate])
                      ->orWhere(function($q) use ($startDate, $endDate) {
                          $q->where('start_date', '<', $startDate)
                            ->where('end_date', '>', $endDate);
                      });
            })
            ->whereHas('employee', function($q) {
                $q->where('exclude_from_payroll', 0);
            })
            ->with(['employee'])
            ->get();

        if ($pendingLeaves->count() > 0) {
            return redirect()->route('admin.attendance.index')
                ->with('pendingLeaves', $pendingLeaves)
                ->with('error', 'There are ' . $pendingLeaves->count() . ' pending leave applications for ' . $startDate->format('F Y') . '. Please approve or reject them before processing attendance.');
        }

        $daysInMonth = $endDate->day;
        


        // Check if we have existing records
        $existingRecordsSubset = AttendanceRecord::forMonthYear($month, $year);
        $existingRecordsCount = $existingRecordsSubset->count();
        $isLocked = AttendanceRecord::forMonthYear($month, $year)->where('is_locked', true)->exists();
        
        // Get the mode of existing records (if any)
        $firstRecord = AttendanceRecord::forMonthYear($month, $year)->first();
        $existingMode = $firstRecord ? $firstRecord->mode : null;

        // If records exist but in a different mode, and not locked, force regeneration to reflect the requested mode
        if ($existingRecordsCount > 0 && $existingMode !== $mode && !$isLocked) {
            Log::debug("Attendance mode mismatch (Existing: $existingMode, Requested: $mode). Forcing regeneration.");
            $existingRecordsCount = 0;
        }
        
        // Force regeneration if requested for testing purposes
        if ($request->has('force_regenerate')) {
            Log::debug("Forcing attendance record regeneration via request flag");
            $existingRecordsCount = 0;
        }

        $leaveTypes = LeaveType::active()->get();

        // Fetch public holidays for the month from the public_holidays table
        $publicHolidays = PublicHoliday::where('status', 'active')
            ->whereBetween('date', [$startDate, $endDate])
            ->get();
            
        // Separate fixed and flexible holidays
        $fixedHolidays = $publicHolidays->where('type', 'fixed')
            ->keyBy(function ($holiday) {
                return $holiday->date->format('Y-m-d');
            });
            
        $flexibleHolidays = $publicHolidays->where('type', 'flexible')
            ->keyBy(function ($holiday) {
                return $holiday->date->format('Y-m-d');
            });
            
        // Create a calendar array with dates and holiday information
        $calendarDates = collect(range(1, $daysInMonth))->map(function($day) use ($month, $year, $fixedHolidays, $flexibleHolidays) {
            $date = Carbon::createFromDate($year, $month, $day);
            $dateString = $date->format('Y-m-d');
            $isFixedHoliday = isset($fixedHolidays[$dateString]);
            $isFlexibleHoliday = isset($flexibleHolidays[$dateString]);
            
            return [
                'day' => $day,
                'date' => $date,
                'is_weekend' => $date->isWeekend(),
                'is_fixed_holiday' => $isFixedHoliday,
                'is_flexible_holiday' => $isFlexibleHoliday,
                'fixed_holiday_name' => $isFixedHoliday ? $fixedHolidays[$dateString]->name : null,
                'flexible_holiday_name' => $isFlexibleHoliday ? $flexibleHolidays[$dateString]->name : null,
                'fixed_holiday_id' => $isFixedHoliday ? $fixedHolidays[$dateString]->id : null,
                'flexible_holiday_id' => $isFlexibleHoliday ? $flexibleHolidays[$dateString]->id : null,
                'day_name' => $date->format('D'),
            ];
        });

        // If no existing records or force regenerate is requested, generate them
        if ($existingRecordsCount === 0 || $request->has('force_regenerate')) {
            try {
                Log::debug("Generating attendance records for month $month/$year");
                
                // If there are existing records and we're forcing regeneration, delete them first
                if ($existingRecordsCount > 0 && $request->has('force_regenerate')) {
                    Log::debug("Deleting existing attendance records before regeneration");
                    AttendanceRecord::forMonthYear($month, $year)->unlocked()->delete();
                }
                
                // Get employees for attendance generation with updated filtering logic
                $startOfMonth = Carbon::createFromDate($year, $month, 1)->startOfDay();
                $endOfMonth = Carbon::createFromDate($year, $month, 1)->endOfMonth();
                
                $employees = Employee::where('exclude_from_payroll', 0)->where(function($query) use ($startOfMonth, $endOfMonth) {
                    // Employee must have joined before or during the month (or no joining date)
                    $query->where(function($q) use ($endOfMonth) {
                        $q->whereDate('date_of_joining', '<=', $endOfMonth)
                          ->orWhereNull('date_of_joining');
                    })
                    // AND either still employed OR resigned after start of month
                    ->where(function($q) use ($startOfMonth) {
                        $q->whereNull('date_of_resignation')
                          ->orWhereDate('date_of_resignation', '>=', $startOfMonth);
                    });
                })->get();
                $records = $this->attendanceService->prepareAttendanceRecords($employees, $month, $year, 0, $mode);
                
                Log::debug("Prepared " . count($records) . " attendance records");
                
                // Process all records synchronously
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
                                'month' => $month,
                                'year' => $year,
                                'batch_id' => 0,
                                'is_locked' => false,
                                'mode' => $mode,
                            ]
                        );
                    } catch (\Exception $e) {
                        Log::error('Failed to process attendance record', [
                            'employee_id' => $record['employee_id'] ?? null,
                            'employee_email' => $record['employee_email'] ?? null,
                            'date' => $record['date'] ?? null,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
                
                Log::debug("Completed generating attendance records");
                
                session()->flash('success', 'Attendance records have been generated.');
                $existingRecordsCount = AttendanceRecord::forMonthYear($month, $year)->count();
                Log::debug("After generation, found $existingRecordsCount records");
                
                // If still no records after generation attempt, show empty state
                if ($existingRecordsCount === 0) {
                    $isLocked = false; // Default to unlocked state
                    $attendanceRecords = collect(); // Empty collection
                    
                    // Get employees for display anyway, even if no records exist yet
                    $startOfMonth = Carbon::createFromDate($year, $month, 1)->startOfDay();
                    $endOfMonth = Carbon::createFromDate($year, $month, 1)->endOfMonth();
                    
                    $employees = Employee::where('exclude_from_payroll', 0)->where(function($query) use ($startOfMonth, $endOfMonth) {
                        // Employee must have joined before or during the month (or no joining date)
                        $query->where(function($q) use ($endOfMonth) {
                            $q->whereDate('date_of_joining', '<=', $endOfMonth)
                              ->orWhereNull('date_of_joining');
                        })
                        // AND either still employed OR resigned after start of month
                        ->where(function($q) use ($startOfMonth) {
                            $q->whereNull('date_of_resignation')
                              ->orWhereDate('date_of_resignation', '>=', $startOfMonth);
                        });
                    })
                        ->with(['department', 'leaveApplications' => function($query) use ($month, $year) {
                            $startDate = Carbon::createFromDate($year, $month, 1);
                            $endDate = $startDate->copy()->endOfMonth();
                            
                            $query->where('status', 'approved')
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
                                });
                        }])
                        ->paginate(25);
                    
                    return view('admin.attendance.bulk-preview', compact(
                        'month', 'year', 'leaveTypes', 'isLocked', 'daysInMonth', 'calendarDates', 'employees', 'attendanceRecords'
                    ))->with('info', 'No attendance records available. Records are being generated in the background.');
                }
            } catch (\Exception $e) {
                Log::error("Failed to generate attendance records: " . $e->getMessage());
                return redirect()->route('admin.attendance.index')
                    ->with('error', 'Failed to generate attendance records: ' . $e->getMessage());
            }
        }

        // Get employees for the specified month/year with updated filtering logic
        // Include all status types, filter by joining/resignation dates
        $startOfMonth = Carbon::createFromDate($year, $month, 1)->startOfDay();
        $endOfMonth = Carbon::createFromDate($year, $month, 1)->endOfMonth();
        
        $employees = Employee::where('exclude_from_payroll', 0)->where(function($query) use ($startOfMonth, $endOfMonth) {
            // Employee must have joined before or during the month (or no joining date)
            $query->where(function($q) use ($endOfMonth) {
                $q->whereDate('date_of_joining', '<=', $endOfMonth)
                  ->orWhereNull('date_of_joining');
            })
            // AND either still employed OR resigned after start of month
            ->where(function($q) use ($startOfMonth) {
                $q->whereNull('date_of_resignation')
                  ->orWhereDate('date_of_resignation', '>=', $startOfMonth);
            });
        })
        ->with(['department'])
        ->paginate(25);

        // Fetch leave applications separately using email matching between Employee and User tables
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
            
        // Group leave applications by email_id field from LeaveApplication table (avoid User table usage)
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
        $leaveApplicationsByPayrollId = collect();
        
        // Convert email-based leave applications to payroll_id-based using employees table
        foreach ($leaveApplicationsByEmail as $email => $applications) {
            $employee = Employee::where('email', $email)->first();
            if ($employee && $employee->payroll_id) {
                $leaveApplicationsByPayrollId[$employee->payroll_id] = $applications;
                $totalLopDays = $applications->where('has_lop', true)->sum('lop_days');
                if ($totalLopDays > 0) {
                    $lopTotalsByPayrollId[$employee->payroll_id] = $totalLopDays;
                }
            }
        }
        
        Log::info('Fetched leave applications for bulk attendance', [
            'total_count' => $leaveApplicationsRaw->count(),
            'grouped_count' => $leaveApplicationsByEmail->count(),
            'emails_with_leaves' => $leaveApplicationsByEmail->keys()->toArray(),
            'lop_totals_count' => count($lopTotalsByEmail)
        ]);

        // Fetch flexible holiday applications separately using email matching
        $flexibleHolidayApplicationsRaw = PublicHolidayApplication::where('status', 'approved')
            ->whereHas('publicHoliday', function($q) use ($month, $year) {
                $startDate = Carbon::createFromDate($year, $month, 1);
                $endDate = $startDate->copy()->endOfMonth();
                $q->where('type', 'flexible')
                  ->whereBetween('date', [$startDate, $endDate]);
            })
            ->with(['publicHoliday', 'user'])
            ->get();
            
        // Group by user email (lowercase)
        $flexibleHolidayApplications = $flexibleHolidayApplicationsRaw->groupBy(function($item) {
            return strtolower($item->user->email ?? '');
        })->filter(function($group, $email) {
            return !empty($email);
        });
        
        Log::info('Fetched flexible holiday applications', [
            'total_count' => $flexibleHolidayApplicationsRaw->count(),
            'grouped_count' => $flexibleHolidayApplications->count(),
            'emails_with_applications' => $flexibleHolidayApplications->keys()->toArray()
        ]);
                
        // More comprehensive debug log to check if the leave applications are being loaded correctly
        Log::debug("Fetched " . $employees->count() . " employees for attendance grid");
        foreach ($employees as $employee) {
            // Log information about employees with approved leaves
            if ($employee->leaveApplications->count() > 0) {
                Log::debug("Employee ID {$employee->id}: {$employee->name} has {$employee->leaveApplications->count()} approved leaves");
                foreach ($employee->leaveApplications as $leave) {
                    Log::debug("Leave: Start: {$leave->start_date->format('Y-m-d')}, End: {$leave->end_date->format('Y-m-d')}, Type: {$leave->leave_type}, Status: {$leave->status}");
                }
            }
        }

        // Get attendance records for these employees using employee emails
        $employeeEmails = $employees->pluck('email')->filter()->toArray();
        
        // Make sure there are employee emails to avoid empty query issues
        if (empty($employeeEmails)) {
            $attendanceRecords = collect();
        } else {
            $employeePayrollIds = $employees->pluck('payroll_id')->filter()->toArray();
            $attendanceRecords = AttendanceRecord::forMonthYear($month, $year)
                ->whereIn('payroll_id', $employeePayrollIds)
                ->with(['leaveType', 'leaveApplication', 'publicHoliday', 'modifiedBy', 'employee'])
                ->get()
                ->groupBy(function($record) {
                    return $record->payroll_id;
                });
        }

        // Get active attendance policy
        $attendancePolicy = \App\Models\AttendancePolicy::getActivePolicy();

        // Get week-off configurations for employees from payroll API
        try {
            $weekOffConfigurations = $this->payrollApiService->getEmployeeWeekOffsIndexedByEmail();
            $weekOffConfigurationsByPayrollId = $this->payrollApiService->getEmployeeWeekOffsIndexedByPayrollId();
            Log::info('Retrieved week-off configurations for bulk attendance view', [
                'count_by_email' => count($weekOffConfigurations),
                'count_by_payroll_id' => count($weekOffConfigurationsByPayrollId)
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch week-off configurations from payroll API', [
                'error' => $e->getMessage()
            ]);
            $weekOffConfigurations = []; // Fallback to empty array
            $weekOffConfigurationsByPayrollId = []; // Fallback to empty array
        }

        // We've already created the calendar dates above, so we can just use them
        
        // Calculate data source statistics
        $dataSourceStats = \App\Models\Attendance::whereYear('date', $year)
            ->whereMonth('date', $month)
            ->selectRaw('source, COUNT(DISTINCT employee_payroll_id) as employee_count, COUNT(*) as record_count')
            ->groupBy('source')
            ->get()
            ->keyBy('source');
        
        $timestationStats = $dataSourceStats->get('timestation_fetch', (object)['employee_count' => 0, 'record_count' => 0]);
        $biometricStats = $dataSourceStats->get('biometric_excel', (object)['employee_count' => 0, 'record_count' => 0]);
        $manualStats = $dataSourceStats->get('manual', (object)['employee_count' => 0, 'record_count' => 0]);
        
        // Get last TimeStation sync time
        $lastTimestationSync = \App\Models\Attendance::where('source', 'timestation_fetch')
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->max('processed_at');
        
        return view('admin.attendance.bulk-preview', compact(
            'month', 'year', 'mode', 'employees', 'attendanceRecords', 'calendarDates', 
            'daysInMonth', 'isLocked', 'leaveTypes', 'weekOffConfigurations', 'weekOffConfigurationsByPayrollId',
            'fixedHolidays', 'flexibleHolidays', 'flexibleHolidayApplications', 
            'leaveApplicationsByEmail', 'lopTotalsByEmail', 'leaveApplicationsByPayrollId', 'lopTotalsByPayrollId',
            'attendancePolicy', 'timestationStats', 'biometricStats', 'manualStats', 'lastTimestationSync'
        ));
    }

    /**
     * Diagnostic endpoint to help debug all leave applications for a month
     */
    public function diagnoseMonthLeaves(Request $request)
    {
        $month = $request->input('month', 8); // Default to August
        $year = $request->input('year', 2025); // Default to 2025
        
        $startDate = Carbon::createFromDate($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();
        
        // Get all approved leave applications for the month
        $leaves = LeaveApplication::where('status', 'approved')
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
            ->with(['user', 'leaveType'])
            ->get();
            
        // Get all attendance records for these leaves
        $leaveUserIds = $leaves->pluck('user_id')->unique()->toArray();
        
        $attendanceRecords = [];
        if (count($leaveUserIds) > 0) {
            $attendanceRecords = AttendanceRecord::forMonthYear($month, $year)
                ->whereIn('user_id', $leaveUserIds)
                ->where('status', 'absent')
                ->with(['user', 'leaveType'])
                ->get()
                ->groupBy('user_id');
        }
        
        return response()->json([
            'month' => $month,
            'year' => $year,
            'leaves_count' => $leaves->count(),
            'leaves' => $leaves->map(function($leave) use ($attendanceRecords) {
                $recordsForUser = $attendanceRecords[$leave->user_id] ?? collect();
                $datesWithinLeave = [];
                
                // For each leave, check if there are attendance records within the leave period
                foreach ($recordsForUser as $record) {
                    if ($record->date->between($leave->start_date, $leave->end_date, true)) {
                        $datesWithinLeave[] = [
                            'date' => $record->date->format('Y-m-d'),
                            'status' => $record->status,
                            'leave_type_id' => $record->leave_type_id
                        ];
                    }
                }
                
                // Calculate expected days within leave period
                $leaveDates = [];
                $period = new \DatePeriod(
                    $leave->start_date,
                    new \DateInterval('P1D'),
                    $leave->end_date->addDay() // Add a day to include the end date
                );
                
                foreach ($period as $date) {
                    $leaveDates[] = $date->format('Y-m-d');
                }
                
                return [
                    'id' => $leave->id,
                    'user' => [
                        'id' => $leave->user->id,
                        'name' => $leave->user->name,
                        'employee_id' => $leave->user->employee_id
                    ],
                    'period' => [
                        'start_date' => $leave->start_date->format('Y-m-d'),
                        'end_date' => $leave->end_date->format('Y-m-d'),
                        'days_count' => count($leaveDates)
                    ],
                    'leave_type' => [
                        'id' => $leave->leave_type_id,
                        'name' => $leave->leave_type
                    ],
                    'attendance_records' => [
                        'count' => count($datesWithinLeave),
                        'records' => $datesWithinLeave
                    ],
                    'expected_dates' => $leaveDates
                ];
            })
        ]);
    }
    
    /**
     * Diagnostic endpoint to help debug leave application issues
     */
    public function diagnoseLeave(Request $request)
    {
        // Require the user to specify the user_id parameter
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'date' => 'required|date',
        ]);
        
        $userId = $request->input('user_id');
        $date = $request->input('date');
        
        // Get the user with their leave applications
        $user = User::with(['leaveApplications' => function($query) {
                $query->where('status', 'approved');
            }])
            ->findOrFail($userId);
            
        // Check for leave applications on the specific date
        $dateObj = Carbon::parse($date);
        $month = $dateObj->month;
        $year = $dateObj->year;
        
        $matchingLeaves = [];
        $allLeaves = [];
        
        foreach ($user->leaveApplications as $leave) {
            $allLeaves[] = [
                'id' => $leave->id,
                'start_date' => $leave->start_date->format('Y-m-d'),
                'end_date' => $leave->end_date->format('Y-m-d'),
                'status' => $leave->status,
                'leave_type' => $leave->leave_type,
                'leave_type_id' => $leave->leave_type_id,
                'matches_date' => $dateObj->between($leave->start_date, $leave->end_date, true)
            ];
            
            if ($dateObj->between($leave->start_date, $leave->end_date, true)) {
                $matchingLeaves[] = $allLeaves[count($allLeaves) - 1];
            }
        }
        
        // Check AttendanceRecord for this date
        $record = AttendanceRecord::where('user_id', $userId)
            ->where('date', $date)
            ->first();
            
        $recordData = null;
        if ($record) {
            $recordData = [
                'id' => $record->id,
                'date' => $record->date->format('Y-m-d'),
                'status' => $record->status,
                'leave_type_id' => $record->leave_type_id,
                'leave_application_id' => $record->leave_application_id
            ];
        }
        
        // Get a direct DB query for leave applications
        $directQueryLeaves = LeaveApplication::where('user_id', $userId)
            ->where('status', 'approved')
            ->get()
            ->map(function($leave) use ($dateObj) {
                return [
                    'id' => $leave->id,
                    'start_date' => $leave->start_date->format('Y-m-d'),
                    'end_date' => $leave->end_date->format('Y-m-d'),
                    'status' => $leave->status,
                    'leave_type' => $leave->leave_type,
                    'leave_type_id' => $leave->leave_type_id,
                    'matches_date' => $dateObj->between($leave->start_date, $leave->end_date, true)
                ];
            });
        
        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'employee_id' => $user->employee_id
            ],
            'date' => $date,
            'month' => $month,
            'year' => $year,
            'matching_leaves' => $matchingLeaves,
            'all_leaves' => $allLeaves,
            'direct_query_leaves' => $directQueryLeaves,
            'attendance_record' => $recordData
        ]);
    }
    
    /**
     * Update a specific attendance record.
     */
    public function updateRecord(Request $request)
    {
        $request->validate([
            'payroll_id' => 'required|integer',
            'employee_email' => 'nullable|email',
            'employee_id' => 'nullable|string',
            'date' => 'required|date',
            'status' => 'required|string',
            'leave_type_id' => 'nullable|exists:leave_types,id',
            'record_id' => 'nullable|exists:attendance_records,id',
        ]);

        $payrollId = $request->input('payroll_id');
        $employeeEmail = $request->input('employee_email');
        $employeeId = $request->input('employee_id');
        $date = Carbon::parse($request->input('date'));
        $status = $request->input('status');
        $leaveTypeId = $request->input('leave_type_id');

        try {
            DB::beginTransaction();

            // Find the employee to ensure they exist
            $employee = Employee::where('payroll_id', $payrollId)->first();
            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee not found with payroll_id: ' . $payrollId
                ], 404);
            }

            // First try to find record by ID if provided
            if ($request->has('record_id') && !empty($request->input('record_id'))) {
                $record = AttendanceRecord::find($request->input('record_id'));
            }
            
            // Use updateOrCreate to handle both new records and updates
            $record = AttendanceRecord::updateOrCreate(
                [
                    'payroll_id' => $payrollId,
                    'date' => $date->format('Y-m-d'),
                ],
                [
                    'employee_id' => $employee->employee_id,
                    'employee_email' => $employeeEmail,
                    'user_id' => null, // Only used for created_by/updated_by tracking
                    'month' => $date->month,
                    'year' => $date->year,
                    'status' => $status, // Include status in initial creation
                    'leave_type_id' => $status === 'absent' ? $leaveTypeId : null,
                ]
            );

            // Store original values if this is the first override
            if (!$record->is_override) {
                $record->original_status = $record->status;
                $record->original_leave_type_id = $record->leave_type_id;
            }

            $record->status = $status;
            $record->leave_type_id = $status === 'absent' ? $leaveTypeId : null;
            $record->is_override = true;
            $record->modified_by = auth()->id();
            $record->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Attendance record updated successfully',
                'record' => [
                    'id' => $record->id,
                    'status' => $record->status,
                    'leave_type' => $record->leaveType ? $record->leaveType->name : null,
                    'is_override' => $record->is_override,
                    'has_original' => !is_null($record->original_status),
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update attendance record', [
                'employee_email' => $employeeEmail,
                'employee_id' => $employeeId,
                'date' => $date->format('Y-m-d'),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update attendance record: ' . $e->getMessage()
            ], 500);
        }
    }

    public function convertPMToAbsent(Request $request)
    {
        $request->validate([
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer|min:2000',
        ]);

        $month = $request->input('month');
        $year = $request->input('year');

        try {
            DB::beginTransaction();

            $updated = AttendanceRecord::forMonthYear($month, $year)
                ->unlocked()
                ->where('status', 'pm')
                ->update([
                    'status' => 'absent',
                    'is_override' => true,
                    'original_status' => 'pm'
                ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Successfully marked {$updated} Punch Miss (PM) records as Absent."
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Revert an attendance record to its original state.
     */
    public function revertRecord(Request $request)
    {
        $request->validate([
            'record_id' => 'required|exists:attendance_records,id',
        ]);

        try {
            DB::beginTransaction();

            $record = AttendanceRecord::findOrFail($request->input('record_id'));

            if (!$record->is_override || is_null($record->original_status)) {
                return response()->json([
                    'success' => false,
                    'message' => 'This record cannot be reverted as it has no original state.'
                ], 400);
            }

            $record->status = $record->original_status;
            $record->leave_type_id = $record->original_leave_type_id;
            $record->original_status = null;
            $record->original_leave_type_id = null;
            $record->is_override = false;
            $record->modified_by = null;
            $record->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Attendance record reverted successfully',
                'record' => [
                    'id' => $record->id,
                    'status' => $record->status,
                    'leave_type' => $record->leaveType ? $record->leaveType->name : null,
                    'is_override' => false,
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to revert attendance record', [
                'record_id' => $request->input('record_id'),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to revert attendance record: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Regenerate attendance records for a month.
     */
    public function regenerate(Request $request)
    {
        $request->validate([
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer|min:2000',
            'mode' => 'nullable|in:timestation,biometric,general,portal_attendance',
        ]);

        $month = $request->input('month');
        $year = $request->input('year');

        try {
            // Delete existing records that are not locked
            AttendanceRecord::forMonthYear($month, $year)
                ->unlocked()
                ->delete();

            // Generate new records
            $this->attendanceService->generateAttendanceRecords($month, $year, auth()->id(), false, $request->input('mode', 'general'));

            return redirect()->route('admin.attendance.preview', ['month' => $month, 'year' => $year, 'mode' => $request->input('mode', 'general')])
                ->with('success', 'Attendance records have been regenerated. Please refresh in a moment.');
        } catch (\Exception $e) {
            Log::error('Failed to regenerate attendance records', [
                'month' => $month,
                'year' => $year,
                'error' => $e->getMessage()
            ]);

            return redirect()->route('admin.attendance.preview', ['month' => $month, 'year' => $year])
                ->with('error', 'Failed to regenerate attendance records: ' . $e->getMessage());
        }
    }

    /**
     * Save attendance records (without locking) - AJAX endpoint with progress tracking.
     */
    public function save(Request $request)
    {
        $request->validate([
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer|min:2000',
        ]);

        $month = $request->input('month');
        $year = $request->input('year');

        // If it's an AJAX request, handle with progress tracking
        if ($request->ajax()) {
            return $this->saveWithProgress($request);
        }

        try {
            // For non-AJAX requests, simply redirect back - records are already being saved as they are modified
            return redirect()->route('admin.attendance.preview', ['month' => $month, 'year' => $year])
                ->with('success', 'Attendance records saved successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to save attendance records', [
                'month' => $month,
                'year' => $year,
                'error' => $e->getMessage()
            ]);

            return redirect()->route('admin.attendance.preview', ['month' => $month, 'year' => $year])
                ->with('error', 'Failed to save attendance records: ' . $e->getMessage());
        }
    }

    /**
     * AJAX endpoint for saving with progress tracking
     */
    public function saveWithProgress(Request $request)
    {
        $request->validate([
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer|min:2000',
            'mode' => 'nullable|in:timestation,biometric,general,portal_attendance',
        ]);

        $month = $request->input('month');
        $year = $request->input('year');
        $mode = $request->input('mode', 'general');

        try {
            // Initialize progress tracking
            $sessionKey = "save_progress_{$month}_{$year}_" . auth()->id();
            session([$sessionKey => ['percentage' => 0, 'status' => 'starting', 'message' => 'Initializing save operation...']]);

            // Check if any records exist, if not generate them first
            $existingRecords = AttendanceRecord::forMonthYear($month, $year)->count();
            
            if ($existingRecords === 0) {
                session([$sessionKey => ['percentage' => 5, 'status' => 'processing', 'message' => 'No attendance records found. Generating default attendance...']]);
                
                // Generate attendance records first
                try {
                    $startOfMonth = Carbon::createFromDate($year, $month, 1)->startOfDay();
                    $endOfMonth = Carbon::createFromDate($year, $month, 1)->endOfMonth();
                    
                    $employees = Employee::where('exclude_from_payroll', 0)->where(function($query) use ($startOfMonth, $endOfMonth) {
                        // Employee must have joined before or during the month (or no joining date)
                        $query->where(function($q) use ($endOfMonth) {
                            $q->whereDate('date_of_joining', '<=', $endOfMonth)
                              ->orWhereNull('date_of_joining');
                        })
                        // AND either no resignation date, OR resigned after start of month
                        ->where(function($q) use ($startOfMonth) {
                            $q->whereNull('date_of_resignation')
                              ->orWhereDate('date_of_resignation', '>=', $startOfMonth);
                        });
                    })->get();
                    
                    session([$sessionKey => ['percentage' => 10, 'status' => 'processing', 'message' => 'Found ' . count($employees) . ' employees to generate attendance for...']]);
                    
                    $records = $this->attendanceService->prepareAttendanceRecords($employees, $month, $year, 0, $mode);
                    
                    session([$sessionKey => ['percentage' => 15, 'status' => 'processing', 'message' => 'Saving ' . count($records) . ' attendance records...']]);
                    
                    foreach ($records as $record) {
                        AttendanceRecord::updateOrCreate(
                            [
                                'user_id' => $record['user_id'],
                                'date' => $record['date'],
                            ],
                            [
                                'status' => $record['status'],
                                'leave_type_id' => $record['leave_type_id'] ?? null,
                                'leave_application_id' => $record['leave_application_id'] ?? null,
                                'public_holiday_id' => $record['public_holiday_id'] ?? null,
                                'month' => $month,
                                'year' => $year,
                                'batch_id' => 0,
                                'is_locked' => false,
                                'mode' => $mode,
                            ]
                        );
                    }
                    
                    session([$sessionKey => ['percentage' => 25, 'status' => 'processing', 'message' => 'Default attendance records generated successfully']]);
                    
                } catch (\Exception $e) {
                    session([$sessionKey => ['percentage' => 0, 'status' => 'error', 'message' => 'Failed to generate attendance records: ' . $e->getMessage()]]);
                    
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to generate attendance records: ' . $e->getMessage()
                    ], 500);
                }
            }

            // Get total records count
            $totalRecords = AttendanceRecord::forMonthYear($month, $year)->count();
            
            if ($totalRecords === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No attendance data available for the specified month and year.'
                ], 404);
            }

            session([$sessionKey => ['percentage' => 10, 'status' => 'processing', 'message' => "Found {$totalRecords} attendance records"]]);

            // Process records in batches for efficiency
            $batchSize = 1000; // Process 1000 records at a time
            $totalBatches = ceil($totalRecords / $batchSize);
            $processedRecords = 0;

            DB::beginTransaction();

            for ($batch = 0; $batch < $totalBatches; $batch++) {
                $offset = $batch * $batchSize;
                
                // Get batch of records
                $records = AttendanceRecord::forMonthYear($month, $year)
                    ->offset($offset)
                    ->limit($batchSize)
                    ->get();

                // Process each record in the batch
                foreach ($records as $record) {
                    // Perform any necessary validations or updates here
                    // For now, just ensure the record is valid and saved
                    if ($record->isDirty()) {
                        $record->save();
                    }
                    $processedRecords++;
                }

                // Update progress
                $percentage = 10 + (($processedRecords / $totalRecords) * 80); // 10% to 90%
                $message = "Processing attendance records... {$processedRecords}/{$totalRecords}";
                
                session([$sessionKey => [
                    'percentage' => round($percentage), 
                    'status' => 'processing', 
                    'message' => $message,
                    'processed' => $processedRecords,
                    'total' => $totalRecords
                ]]);

                // Small delay to prevent overwhelming the database
                usleep(10000); // 10ms delay
            }

            DB::commit();

            // Final success status
            session([$sessionKey => [
                'percentage' => 100, 
                'status' => 'completed', 
                'message' => "Successfully saved {$totalRecords} attendance records",
                'processed' => $totalRecords,
                'total' => $totalRecords
            ]]);

            return response()->json([
                'success' => true,
                'message' => "Attendance records saved successfully",
                'total_records' => $totalRecords,
                'session_key' => $sessionKey
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            session([$sessionKey => [
                'percentage' => 0, 
                'status' => 'error', 
                'message' => 'Error: ' . $e->getMessage()
            ]]);

            Log::error('Failed to save attendance records', [
                'month' => $month,
                'year' => $year,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to save attendance records: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lock attendance records and make them available for payroll - with progress tracking.
     */
    public function lock(Request $request)
    {
        $request->validate([
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer|min:2000',
        ]);

        $month = $request->input('month');
        $year = $request->input('year');

        // If it's an AJAX request, handle with progress tracking
        if ($request->ajax()) {
            return $this->lockWithProgress($request);
        }

        try {
            $success = $this->attendanceService->lockAttendanceRecords($month, $year, auth()->id());

            if ($success) {
                return redirect()->route('admin.attendance.preview', ['month' => $month, 'year' => $year])
                    ->with('success', 'Attendance records locked and submitted to payroll successfully.');
            } else {
                return redirect()->route('admin.attendance.preview', ['month' => $month, 'year' => $year])
                    ->with('error', 'Failed to lock attendance records.');
            }
        } catch (\Exception $e) {
            Log::error('Failed to lock attendance records', [
                'month' => $month,
                'year' => $year,
                'error' => $e->getMessage()
            ]);

            return redirect()->route('admin.attendance.preview', ['month' => $month, 'year' => $year])
                ->with('error', 'Failed to lock attendance records: ' . $e->getMessage());
        }
    }

    /**
     * AJAX endpoint for locking with progress tracking
     */
    public function lockWithProgress(Request $request)
    {
        $request->validate([
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer|min:2000',
        ]);

        $month = $request->input('month');
        $year = $request->input('year');

        try {
            // Initialize progress tracking
            $sessionKey = "lock_progress_{$month}_{$year}_" . auth()->id();
            session([$sessionKey => ['percentage' => 0, 'status' => 'starting', 'message' => 'Initializing lock operation...']]);

            // Check if records are already locked
            $alreadyLocked = AttendanceRecord::forMonthYear($month, $year)->locked()->exists();
            if ($alreadyLocked) {
                return response()->json([
                    'success' => false,
                    'message' => 'Attendance records for this period are already locked.'
                ], 400);
            }

            // Check if any records exist, if not generate them first
            $existingRecords = AttendanceRecord::forMonthYear($month, $year)->count();
            
            if ($existingRecords === 0) {
                session([$sessionKey => ['percentage' => 5, 'status' => 'processing', 'message' => 'No attendance records found. Generating default attendance...']]);
                
                // Generate attendance records first
                try {
                    $startOfMonth = Carbon::createFromDate($year, $month, 1)->startOfDay();
                    $endOfMonth = Carbon::createFromDate($year, $month, 1)->endOfMonth();
                    
                    $employees = Employee::where('exclude_from_payroll', 0)->where(function($query) use ($startOfMonth, $endOfMonth) {
                        // Employee must have joined before or during the month (or no joining date)
                        $query->where(function($q) use ($endOfMonth) {
                            $q->whereDate('date_of_joining', '<=', $endOfMonth)
                              ->orWhereNull('date_of_joining');
                        })
                        // AND either no resignation date, OR resigned after start of month
                        ->where(function($q) use ($startOfMonth) {
                            $q->whereNull('date_of_resignation')
                              ->orWhereDate('date_of_resignation', '>=', $startOfMonth);
                        });
                    })->get();
                    
                    session([$sessionKey => ['percentage' => 10, 'status' => 'processing', 'message' => 'Found ' . count($employees) . ' employees to generate attendance for...']]);
                    
                    $records = $this->attendanceService->prepareAttendanceRecords($employees, $month, $year, 0);
                    
                    session([$sessionKey => ['percentage' => 15, 'status' => 'processing', 'message' => 'Saving ' . count($records) . ' attendance records...']]);
                    
                    foreach ($records as $record) {
                        AttendanceRecord::updateOrCreate(
                            [
                                'user_id' => $record['user_id'],
                                'date' => $record['date'],
                            ],
                            [
                                'status' => $record['status'],
                                'leave_type_id' => $record['leave_type_id'] ?? null,
                                'leave_application_id' => $record['leave_application_id'] ?? null,
                                'public_holiday_id' => $record['public_holiday_id'] ?? null,
                                'month' => $month,
                                'year' => $year,
                                'batch_id' => 0,
                                'is_locked' => false,
                                'mode' => $request->input('mode', 'general'),
                            ]
                        );
                    }
                    
                    session([$sessionKey => ['percentage' => 25, 'status' => 'processing', 'message' => 'Default attendance records generated successfully']]);
                    
                } catch (\Exception $e) {
                    session([$sessionKey => ['percentage' => 0, 'status' => 'error', 'message' => 'Failed to generate attendance records: ' . $e->getMessage()]]);
                    
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to generate attendance records: ' . $e->getMessage()
                    ], 500);
                }
            }

            // Get total unlocked records count  
            $totalRecords = AttendanceRecord::forMonthYear($month, $year)->unlocked()->count();
            
            if ($totalRecords === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'All attendance records for this period are already locked.'
                ], 400);
            }

            session([$sessionKey => ['percentage' => 10, 'status' => 'processing', 'message' => "Found {$totalRecords} records to lock"]]);

            // Process records in batches for efficiency
            $batchSize = 500; // Smaller batch size for locking operations
            $totalBatches = ceil($totalRecords / $batchSize);
            $processedRecords = 0;

            DB::beginTransaction();

            for ($batch = 0; $batch < $totalBatches; $batch++) {
                // Update batch of records
                $affected = AttendanceRecord::forMonthYear($month, $year)
                    ->unlocked()
                    ->offset($batch * $batchSize)
                    ->limit($batchSize)
                    ->update([
                        'is_locked' => true,
                        'locked_at' => now(),
                        'locked_by' => auth()->id()
                    ]);

                $processedRecords += $affected;

                // Update progress
                $percentage = 10 + (($processedRecords / $totalRecords) * 70); // 10% to 80%
                $message = "Locking attendance records... {$processedRecords}/{$totalRecords}";
                
                session([$sessionKey => [
                    'percentage' => round($percentage), 
                    'status' => 'processing', 
                    'message' => $message,
                    'processed' => $processedRecords,
                    'total' => $totalRecords
                ]]);

                // Small delay to prevent overwhelming the database
                usleep(5000); // 5ms delay
            }

            // Update the batch if it exists
            session([$sessionKey => ['percentage' => 85, 'status' => 'processing', 'message' => 'Updating batch status...']]);
            
            $batch = AttendanceBatch::forMonthYear($month, $year)->latest()->first();
            if ($batch) {
                $batch->update([
                    'is_locked' => true,
                    'completed_at' => now()
                ]);
            }

            session([$sessionKey => ['percentage' => 95, 'status' => 'processing', 'message' => 'Finalizing lock operation...']]);

            DB::commit();

            // Final success status
            session([$sessionKey => [
                'percentage' => 100, 
                'status' => 'completed', 
                'message' => "Successfully locked {$totalRecords} attendance records",
                'processed' => $totalRecords,
                'total' => $totalRecords
            ]]);

            return response()->json([
                'success' => true,
                'message' => "Attendance records locked and submitted to payroll successfully",
                'total_records' => $totalRecords,
                'session_key' => $sessionKey
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            session([$sessionKey => [
                'percentage' => 0, 
                'status' => 'error', 
                'message' => 'Error: ' . $e->getMessage()
            ]]);

            Log::error('Failed to lock attendance records', [
                'month' => $month,
                'year' => $year,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to lock attendance records: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API endpoint to get attendance data for payroll.
     */
    public function apiAttendanceData(Request $request)
    {
        try {
            // Check if user is authenticated via JWT (for /api/attendance-data route)
            $isJwtAuthenticated = auth('api')->check();
            
            // Validation rules - api_key is only required if not JWT authenticated
            $rules = [
                'month' => 'required|integer|between:1,12',
                'year' => 'required|integer|min:2000|max:' . (date('Y') + 5),
            ];
            
            if (!$isJwtAuthenticated) {
                $rules['api_key'] = 'required|string';
            }
            
            // Custom validation messages
            $messages = [
                'month.required' => 'Month parameter is required.',
                'month.integer' => 'Month must be a valid integer.',
                'month.between' => 'Month must be between 1 and 12.',
                'year.required' => 'Year parameter is required.',
                'year.integer' => 'Year must be a valid integer.',
                'year.min' => 'Year must be at least 2000.',
                'year.max' => 'Year cannot be more than ' . (date('Y') + 5) . '.',
                'api_key.required' => 'API key is required for this endpoint.',
                'api_key.string' => 'API key must be a valid string.',
            ];
            
            $validator = \Illuminate\Support\Facades\Validator::make($request->all(), $rules, $messages);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Validation failed',
                    'message' => 'Invalid input parameters provided',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Check API key only if not JWT authenticated (for legacy /payroll/attendance-data route)
            if (!$isJwtAuthenticated) {
                $apiKey = $request->input('api_key');
                $expectedToken = env('ATTENDANCE_API_TOKEN', 'hrms_sync_token_2025_secure_key');
                
                if ($apiKey !== $expectedToken) {
                    return response()->json([
                        'success' => false,
                        'error' => 'Unauthorized',
                        'message' => 'Invalid API key provided',
                        'code' => 'INVALID_API_KEY'
                    ], 401);
                }
            }

            $month = (int) $request->input('month');
            $year = (int) $request->input('year');

            // Check if any attendance records exist for this month/year
            $recordsExist = AttendanceRecord::forMonthYear($month, $year)->exists();
            
            if (!$recordsExist) {
                return response()->json([
                    'success' => false,
                    'error' => 'No attendance data found',
                    'message' => "No attendance records found for {$this->getMonthName($month)} {$year}",
                    'code' => 'NO_ATTENDANCE_DATA',
                    'month' => $month,
                    'year' => $year
                ], 404);
            }

            // Check if records for this month are locked (ALWAYS enforce - no exceptions)
            $isLocked = AttendanceRecord::forMonthYear($month, $year)->locked()->exists();
            
            if (!$isLocked) {
                $totalRecords = AttendanceRecord::forMonthYear($month, $year)->count();
                return response()->json([
                    'success' => false,
                    'error' => 'Attendance not locked',
                    'message' => "Attendance records for {$this->getMonthName($month)} {$year} are not yet locked and ready for payroll processing",
                    'code' => 'ATTENDANCE_NOT_LOCKED',
                    'month' => $month,
                    'year' => $year,
                    'total_records' => $totalRecords,
                    'locked_records' => 0
                ], 400);
            }

            // Get the attendance summary
            $summary = $this->attendanceService->getAttendanceSummary($month, $year);

            if (empty($summary)) {
                return response()->json([
                    'success' => false,
                    'error' => 'No employee data found',
                    'message' => "No employee attendance data available for {$this->getMonthName($month)} {$year}",
                    'code' => 'NO_EMPLOYEE_DATA',
                    'month' => $month,
                    'year' => $year
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => "Attendance data retrieved successfully for {$this->getMonthName($month)} {$year}",
                'month' => $month,
                'year' => $year,
                'total_employees' => count($summary),
                'data' => $summary
            ], 200);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Validation failed',
                'message' => 'Invalid input parameters provided',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            Log::error('Bulk attendance API error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Internal server error',
                'message' => 'An unexpected error occurred while processing your request',
                'code' => 'INTERNAL_ERROR'
            ], 500);
        }
    }

    /**
     * Helper method to get month name
     */
    private function getMonthName(int $month): string
    {
        $months = [
            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
            5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
            9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
        ];
        
        return $months[$month] ?? 'Unknown';
    }

    /**
     * Check progress of save/lock operations
     */
    public function checkProgress(Request $request)
    {
        $request->validate([
            'session_key' => 'required|string'
        ]);

        $sessionKey = $request->input('session_key');
        $progress = session($sessionKey);

        if (!$progress) {
            return response()->json([
                'success' => false,
                'message' => 'Progress session not found or expired'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'progress' => $progress
        ]);
    }

    /**
     * Test endpoint to check payroll API integration and week-off configurations
     */
    public function testPayrollApi()
    {
        try {
            $employees = $this->payrollApiService->getEmployees();
            $weekOffConfigurations = $this->payrollApiService->getEmployeeWeekOffsIndexedByEmail();

            return response()->json([
                'success' => true,
                'employees_count' => $employees ? count($employees) : 0,
                'week_off_configurations_count' => count($weekOffConfigurations),
                'sample_employee' => $employees && count($employees) > 0 ? $employees[0] : null,
                'sample_week_off_config' => !empty($weekOffConfigurations) ? array_values($weekOffConfigurations)[0] : null,
                'week_off_emails' => array_keys($weekOffConfigurations)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }
}
