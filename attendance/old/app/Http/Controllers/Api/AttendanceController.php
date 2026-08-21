<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AttendanceRecord;
use App\Models\AttendanceReview;
use App\Models\Employee;
use App\Models\ManualPunch;
use App\Models\PublicHoliday;
use App\Models\PublicHolidayApplication;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AttendanceController extends Controller
{
    /**
     * Get the authenticated user.
     */
    private function getAuthenticatedUser()
    {
        return Auth::guard('api')->user();
    }

    /**
     * Get employee by user's payroll_id.
     */
    private function getEmployee($user)
    {
        if (!$user || !$user->payroll_id) {
            return null;
        }
        return Employee::where('payroll_id', $user->payroll_id)->first();
    }

    /**
     * Get today's attendance status for the logged-in employee.
     */
    public function todayStatus(Request $request): JsonResponse
    {
        try {
            $user = $this->getAuthenticatedUser();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
            }

            $employee = $this->getEmployee($user);
            if (!$employee) {
                return response()->json(['success' => false, 'message' => 'Employee profile not found.'], 404);
            }

            $today = Carbon::today();
            $attendance = Attendance::where('employee_payroll_id', $employee->payroll_id)
                ->whereDate('date', $today)
                ->first();

            return response()->json([
                'success' => true,
                'data' => [
                    'date' => $today->toDateString(),
                    'checked_in' => $attendance && $attendance->check_in_time ? true : false,
                    'checked_out' => $attendance && $attendance->check_out_time ? true : false,
                    'check_in_time' => $attendance ? $attendance->check_in_time : null,
                    'check_out_time' => $attendance ? $attendance->check_out_time : null,
                    'total_hours' => $attendance ? $attendance->total_hours : null,
                    'status' => $attendance ? $attendance->status : 'absent',
                    'location_name' => $attendance ? [
                        'check_in' => $attendance->check_in_location_name,
                        'check_out' => $attendance->check_out_location_name,
                    ] : null,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting today status: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to retrieve attendance status.'], 500);
        }
    }

    /**
     * Handle employee check-in.
     */
    public function checkIn(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'latitude' => 'nullable|numeric',
                'longitude' => 'nullable|numeric',
                'location_name' => 'nullable|string|max:255',
            ]);

            $user = $this->getAuthenticatedUser();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
            }

            $employee = $this->getEmployee($user);
            if (!$employee) {
                return response()->json(['success' => false, 'message' => 'Employee profile not found.'], 404);
            }

            $today = Carbon::today();

            // Check if already checked in
            $exists = Attendance::where('employee_payroll_id', $employee->payroll_id)
                ->whereDate('date', $today)
                ->first();

            if ($exists && $exists->check_in_time) {
                return response()->json(['success' => false, 'message' => 'You have already checked in today.'], 400);
            }

            $time = Carbon::now()->format('H:i:s');
            $ip = $request->ip();

            // Update or create in raw attendance table
            $attendance = Attendance::updateOrCreate(
                [
                    'employee_payroll_id' => $employee->payroll_id,
                    'date' => $today->toDateString(),
                ],
                [
                    'check_in_time' => $time,
                    'check_in_ip' => $ip,
                    'check_in_latitude' => $request->input('latitude'),
                    'check_in_longitude' => $request->input('longitude'),
                    'check_in_location_name' => $request->input('location_name', 'Mobile Check-In'),
                    'source' => 'self_attendance',
                    'status' => 'present'
                ]
            );

            // Sync with attendance_records
            AttendanceRecord::updateOrCreate(
                [
                    'payroll_id' => $employee->payroll_id,
                    'date' => $today->toDateString(),
                ],
                [
                    'employee_id' => $employee->employee_id,
                    'employee_email' => $employee->email,
                    'user_id' => $user->id,
                    'status' => 'present',
                    'check_in_time' => $time,
                    'data_source' => 'hybrid',
                    'month' => $today->month,
                    'year' => $today->year,
                    'has_biometric_data' => false,
                    'is_locked' => false
                ]
            );

            Log::info('Employee checked in via Mobile API', [
                'employee' => $employee->payroll_id,
                'time' => $time,
                'ip' => $ip
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Checked in successfully at ' . Carbon::now()->format('g:i A'),
                'data' => [
                    'check_in_time' => $time,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error checking in via Mobile API: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to check in: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Handle employee check-out.
     */
    public function checkOut(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'latitude' => 'nullable|numeric',
                'longitude' => 'nullable|numeric',
                'location_name' => 'nullable|string|max:255',
            ]);

            $user = $this->getAuthenticatedUser();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
            }

            $employee = $this->getEmployee($user);
            if (!$employee) {
                return response()->json(['success' => false, 'message' => 'Employee profile not found.'], 404);
            }

            $today = Carbon::today();

            $attendance = Attendance::where('employee_payroll_id', $employee->payroll_id)
                ->whereDate('date', $today)
                ->first();

            if (!$attendance || !$attendance->check_in_time) {
                return response()->json(['success' => false, 'message' => 'You must check in first before checking out.'], 400);
            }

            if ($attendance->check_out_time) {
                return response()->json(['success' => false, 'message' => 'You have already checked out today.'], 400);
            }

            $time = Carbon::now()->format('H:i:s');
            $ip = $request->ip();

            $attendance->check_out_time = $time;
            $attendance->check_out_ip = $ip;
            $attendance->check_out_latitude = $request->input('latitude');
            $attendance->check_out_longitude = $request->input('longitude');
            $attendance->check_out_location_name = $request->input('location_name', 'Mobile Check-Out');
            
            $totalHours = $attendance->calculateTotalHours();
            $attendance->total_hours = $totalHours;
            $attendance->status = $attendance->determineStatus();
            $attendance->save();

            // Sync with attendance_records
            AttendanceRecord::updateOrCreate(
                [
                    'payroll_id' => $employee->payroll_id,
                    'date' => $today->toDateString(),
                ],
                [
                    'check_out_time' => $time,
                    'total_hours' => $totalHours,
                    'status' => $attendance->status,
                    'data_source' => 'hybrid'
                ]
            );

            Log::info('Employee checked out via Mobile API', [
                'employee' => $employee->payroll_id,
                'time' => $time,
                'total_hours' => $totalHours
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Checked out successfully at ' . Carbon::now()->format('g:i A'),
                'data' => [
                    'check_out_time' => $time,
                    'total_hours' => $totalHours,
                    'status' => $attendance->status,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error checking out via Mobile API: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to check out: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get monthly attendance summary/details for calendar view.
     */
    public function monthlySummary(Request $request): JsonResponse
    {
        try {
            $user = $this->getAuthenticatedUser();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
            }

            $employee = $this->getEmployee($user);
            if (!$employee) {
                return response()->json(['success' => false, 'message' => 'Employee profile not found.'], 404);
            }

            $month = (int) $request->input('month', date('n'));
            $year = (int) $request->input('year', date('Y'));
            
            $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();
            
            // Fetch monthly data for calendar
            $attendanceRecords = AttendanceRecord::where('payroll_id', $employee->payroll_id)
                ->whereBetween('date', [$startDate, $endDate])
                ->get()
                ->keyBy(function($item) {
                    return $item->date->format('Y-m-d');
                });

            // Fetch raw attendances to merge check-in/out details
            $rawAttendances = Attendance::where('employee_payroll_id', $employee->payroll_id)
                ->whereBetween('date', [$startDate, $endDate])
                ->get()
                ->keyBy(function($item) {
                    return $item->date->format('Y-m-d');
                });

            // Fetch leave applications for the month
            $leaveDays = DB::table('leave_application_days')
                ->join('leave_applications', 'leave_application_days.leave_application_id', '=', 'leave_applications.id')
                ->join('leave_types', 'leave_applications.leave_type_id', '=', 'leave_types.id')
                ->where('leave_applications.user_id', $user->id)
                ->whereBetween('leave_application_days.leave_date', [$startDate, $endDate])
                ->select('leave_application_days.leave_date', 'leave_applications.status', 'leave_types.name as type_name')
                ->get()
                ->keyBy('leave_date');

            // Fetch approved public holiday applications for the employee
            $approvedHolidayApplications = PublicHolidayApplication::where('payroll_id', $employee->payroll_id)
                ->where('status', 'approved')
                ->pluck('public_holiday_id')
                ->toArray();

            // Fetch public holidays
            $publicHolidays = PublicHoliday::whereBetween('date', [$startDate, $endDate])
                ->where('status', 'active')
                ->get()
                ->filter(function($holiday) use ($approvedHolidayApplications) {
                    // If it is flexible, the employee must have an approved application for it.
                    if ($holiday->type === 'flexible') {
                        return in_array($holiday->id, $approvedHolidayApplications);
                    }
                    return true;
                })
                ->keyBy(function($item) {
                    return Carbon::parse($item->date)->format('Y-m-d');
                });

            // Fetch manual punch corrections
            $manualPunches = ManualPunch::where('employee_payroll_id', $employee->payroll_id)
                ->whereBetween('date', [$startDate, $endDate])
                ->get()
                ->keyBy(function($item) {
                    return $item->date->format('Y-m-d');
                });

            // Fetch month end review status
            $reviewRequest = AttendanceReview::where('user_id', $user->id)
                ->where('month', $month)
                ->where('year', $year)
                ->first();

            // Prepare calendar days array
            $days = [];
            $currentDate = $startDate->copy();
            while ($currentDate->lte($endDate)) {
                $dateStr = $currentDate->toDateString();
                
                $record = $attendanceRecords->get($dateStr);
                $raw = $rawAttendances->get($dateStr);
                $leave = $leaveDays->get($dateStr);
                $holiday = $publicHolidays->get($dateStr);
                $punch = $manualPunches->get($dateStr);

                $days[] = [
                    'date' => $dateStr,
                    'attendance' => $record ? [
                        'status' => $record->status,
                        'check_in_time' => $record->check_in_time,
                        'check_out_time' => $record->check_out_time,
                        'total_hours' => $record->total_hours,
                    ] : ($raw ? [
                        'status' => $raw->status,
                        'check_in_time' => $raw->check_in_time,
                        'check_out_time' => $raw->check_out_time,
                        'total_hours' => $raw->total_hours,
                    ] : null),
                    'leave' => $leave ? [
                        'status' => $leave->status,
                        'type_name' => $leave->type_name,
                    ] : null,
                    'holiday' => $holiday ? [
                        'name' => $holiday->name,
                    ] : null,
                    'correction' => $punch ? [
                        'status' => $punch->status,
                        'punch_in_time' => $punch->punch_in_time ? Carbon::parse($punch->punch_in_time)->format('H:i') : null,
                        'punch_out_time' => $punch->punch_out_time ? Carbon::parse($punch->punch_out_time)->format('H:i') : null,
                        'reason' => $punch->reason,
                    ] : null,
                ];

                $currentDate->addDay();
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'month' => $month,
                    'year' => $year,
                    'days' => $days,
                    'review_request' => $reviewRequest ? [
                        'status' => $reviewRequest->status,
                        'notes' => $reviewRequest->notes,
                    ] : null,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error building monthly summary: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to build monthly summary: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Fetch list of correction requests submitted by the logged-in employee.
     */
    public function correctionsList(Request $request): JsonResponse
    {
        try {
            $user = $this->getAuthenticatedUser();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
            }

            $employee = $this->getEmployee($user);
            if (!$employee) {
                return response()->json(['success' => false, 'message' => 'Employee profile not found.'], 404);
            }

            $corrections = ManualPunch::where('employee_payroll_id', $employee->payroll_id)
                ->orderBy('date', 'desc')
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'date' => $item->date->toDateString(),
                        'punch_in_time' => $item->punch_in_time ? Carbon::parse($item->punch_in_time)->format('H:i') : null,
                        'punch_out_time' => $item->punch_out_time ? Carbon::parse($item->punch_out_time)->format('H:i') : null,
                        'reason' => $item->reason,
                        'status' => $item->status,
                        'rejection_reason' => $item->rejection_reason,
                        'created_at' => $item->created_at->toDateTimeString(),
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $corrections
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching correction list: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to fetch correction requests.'], 500);
        }
    }

    /**
     * Submit a missed punch correction request.
     */
    public function storeCorrection(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'date' => 'required|date|before_or_equal:today',
                'punch_in_time' => 'nullable|date_format:H:i',
                'punch_out_time' => 'nullable|date_format:H:i',
                'reason' => 'required|string|max:500',
            ]);

            if (!$request->filled('punch_in_time') && !$request->filled('punch_out_time')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Either punch in or punch out time must be filled.'
                ], 422);
            }

            $user = $this->getAuthenticatedUser();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
            }

            $employee = $this->getEmployee($user);
            if (!$employee) {
                return response()->json(['success' => false, 'message' => 'Employee profile not found.'], 404);
            }

            // Check if there is already a manual punch request for this date
            $exists = ManualPunch::where('employee_payroll_id', $employee->payroll_id)
                ->whereDate('date', $request->date)
                ->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'A correction request for this date already exists.'
                ], 400);
            }

            // Find assigned shift
            $shift = Shift::first(); // Fallback default shift

            $manualPunch = ManualPunch::create([
                'employee_payroll_id' => $employee->payroll_id,
                'employee_id' => $employee->employee_id,
                'employee_email' => $employee->email,
                'date' => $request->date,
                'punch_in_time' => $request->punch_in_time,
                'punch_out_time' => $request->punch_out_time,
                'reason' => $request->reason,
                'shift_id' => $shift ? $shift->id : null,
                'added_by' => $user->id,
                'status' => 'pending',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Correction request submitted successfully.',
                'data' => [
                    'id' => $manualPunch->id,
                    'status' => $manualPunch->status,
                ]
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error storing correction request: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to submit correction request: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Submit monthly review request.
     */
    public function storeReview(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'month' => 'required|integer|between:1,12',
                'year' => 'required|integer|min:2020',
                'notes' => 'nullable|string|max:500',
            ]);

            $user = $this->getAuthenticatedUser();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
            }

            $reviewRequest = AttendanceReview::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'month' => $request->month,
                    'year' => $request->year,
                ],
                [
                    'employee_payroll_id' => $user->payroll_id,
                    'status' => 'pending',
                    'notes' => $request->notes,
                    'reviewed_by' => null
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Monthly attendance review request submitted successfully.',
                'data' => [
                    'status' => $reviewRequest->status,
                ]
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error storing monthly review: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to submit monthly review request: ' . $e->getMessage()], 500);
        }
    }
}
