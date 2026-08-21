<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceRecord;
use App\Models\AttendanceReview;
use App\Models\Employee;
use App\Models\ManualPunch;
use App\Models\LeaveApplication;
use App\Models\PublicHoliday;
use App\Models\PublicHolidayApplication;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SelfAttendanceController extends Controller
{
    /**
     * Display employee self-attendance page
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $employee = Employee::where('payroll_id', $user->payroll_id)->first();

        if (!$employee) {
            return redirect()->route('dashboard')->with('error', 'Employee record not found for your account.');
        }

        $today = Carbon::today();
        
        // Find today's self attendance status
        $todayAttendance = Attendance::where('employee_payroll_id', $employee->payroll_id)
            ->whereDate('date', $today)
            ->first();

        // Selected month and year for calendar
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

        // Also fetch from raw attendances table to get exact check-in/out times if record doesn't exist in attendance_records
        $rawAttendances = Attendance::where('employee_payroll_id', $employee->payroll_id)
            ->whereBetween('date', [$startDate, $endDate])
            ->get()
            ->keyBy(function($item) {
                return $item->date->format('Y-m-d');
            });

        // Fetch leave applications for the month (only active ones, i.e., approved or pending)
        $leaveDays = DB::table('leave_application_days')
            ->join('leave_applications', 'leave_application_days.leave_application_id', '=', 'leave_applications.id')
            ->join('leave_types', 'leave_applications.leave_type_id', '=', 'leave_types.id')
            ->where('leave_applications.user_id', $user->id)
            ->whereIn('leave_applications.status', ['approved', 'pending'])
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

        // Fetch manual punch corrections raised by employee
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

        // Fetch week-off configurations for the employee
        $payrollApiService = app(\App\Services\PayrollApiService::class);
        $weekOffDays = [];
        try {
            $weekOffConfig = $payrollApiService->getEmployeeWeekOffByEmail($employee->email);
            if ($weekOffConfig && isset($weekOffConfig['week_off_days'])) {
                $weekOffDays = $weekOffConfig['week_off_days'];
            }
        } catch (\Exception $e) {
            Log::error('Failed to fetch week-off for employee ' . $employee->email . ': ' . $e->getMessage());
        }

        if (empty($weekOffDays)) {
            $weekOffDays = [0]; // Sunday by default if no configuration is found
        }

        return view('self-attendance.index', compact(
            'employee',
            'todayAttendance',
            'month',
            'year',
            'attendanceRecords',
            'rawAttendances',
            'leaveDays',
            'publicHolidays',
            'manualPunches',
            'reviewRequest',
            'weekOffDays'
        ));
    }

    /**
     * Handle employee check-in
     */
    public function checkIn(Request $request)
    {
        $user = Auth::user();
        $employee = Employee::where('payroll_id', $user->payroll_id)->first();

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
                'check_in_location_name' => $request->input('location_name', 'Self Attendance Check-In'),
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

        Log::info('Employee checked in successfully', [
            'employee' => $employee->payroll_id,
            'time' => $time,
            'ip' => $ip
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Checked in successfully at ' . Carbon::now()->format('g:i A')
        ]);
    }

    /**
     * Handle employee check-out
     */
    public function checkOut(Request $request)
    {
        $user = Auth::user();
        $employee = Employee::where('payroll_id', $user->payroll_id)->first();

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
        $attendance->check_out_location_name = $request->input('location_name', 'Self Attendance Check-Out');
        
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
                'employee_id' => $employee->employee_id,
                'employee_email' => $employee->email,
                'user_id' => $user->id,
                'check_in_time' => $attendance->check_in_time,
                'check_out_time' => $time,
                'total_hours' => $totalHours,
                'status' => $attendance->status,
                'data_source' => 'hybrid',
                'month' => $today->month,
                'year' => $today->year,
                'has_biometric_data' => false,
                'is_locked' => false
            ]
        );

        Log::info('Employee checked out successfully', [
            'employee' => $employee->payroll_id,
            'time' => $time,
            'total_hours' => $totalHours
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Checked out successfully at ' . Carbon::now()->format('g:i A') . '. Total hours: ' . $totalHours
        ]);
    }

    /**
     * Submit a missed punch correction request
     */
    public function storeCorrection(Request $request)
    {
        $request->validate([
            'date' => 'required|date|before_or_equal:today',
            'punch_in_time' => 'nullable|date_format:H:i',
            'punch_out_time' => 'nullable|date_format:H:i',
            'reason' => 'required|string|max:500',
        ]);

        if (!$request->filled('punch_in_time') && !$request->filled('punch_out_time')) {
            return back()->withErrors(['punch_time' => 'Either punch in or punch out time must be filled.']);
        }

        $user = Auth::user();
        $employee = Employee::where('payroll_id', $user->payroll_id)->first();

        if (!$employee) {
            return back()->with('error', 'Employee record not found.');
        }

        // Restrict punch correction for locked months
        $carbonDate = Carbon::parse($request->date);
        $isLocked = AttendanceRecord::forMonthYear($carbonDate->month, $carbonDate->year)
            ->where('is_locked', true)
            ->exists();
        if ($isLocked) {
            return back()->with('error', 'Attendance for this month is locked. You cannot submit correction requests.');
        }

        // Check if there is already a manual punch request for this date
        $exists = ManualPunch::where('employee_payroll_id', $employee->payroll_id)
            ->whereDate('date', $request->date)
            ->exists();

        if ($exists) {
            return back()->with('error', 'A correction request for this date already exists.');
        }

        // Find assigned shift
        $shift = Shift::first(); // Fallback default shift

        ManualPunch::create([
            'employee_payroll_id' => $employee->payroll_id,
            'employee_id' => $employee->employee_id,
            'employee_email' => $employee->email,
            'date' => $request->date,
            'punch_in_time' => $request->punch_in_time,
            'punch_out_time' => $request->punch_out_time,
            'reason' => $request->reason,
            'shift_id' => $shift ? $shift->id : null,
            'added_by' => $user->id,
            'status' => 'pending', // Pending manager/HR approval
        ]);

        return back()->with('success', 'Correction request submitted successfully and is pending approval.');
    }

    /**
     * Submit monthly review request
     */
    public function storeReview(Request $request)
    {
        $request->validate([
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer|min:2020',
            'notes' => 'nullable|string|max:500',
        ]);

        $user = Auth::user();
        
        AttendanceReview::updateOrCreate(
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

        return back()->with('success', 'Monthly attendance review request submitted successfully.');
    }

    /**
     * Display admin view for self-attendance logs
     */
    public function adminIndex(Request $request)
    {
        // Filter by date (default to today)
        $date = $request->input('date', Carbon::today()->toDateString());

        $query = Attendance::whereIn('source', ['self_attendance', 'manual_correction'])->with('employee');
        if ($date) {
            $query->whereDate('date', $date);
        }

        // Filter by employee
        if ($request->filled('employee_payroll_id')) {
            $query->where('employee_payroll_id', $request->employee_payroll_id);
        }

        $logs = $query->orderBy('date', 'desc')
            ->orderBy('check_in_time', 'desc')
            ->paginate(25);

        $employees = Employee::orderBy('name')->get();

        return view('self-attendance.admin-logs', compact('logs', 'employees', 'date'));
    }
}
