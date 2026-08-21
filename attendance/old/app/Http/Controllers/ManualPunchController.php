<?php

namespace App\Http\Controllers;

use App\Models\ManualPunch;
use App\Models\Employee;
use App\Models\Shift;
use App\Models\DutyRoster;
use App\Models\AttendanceRecord;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ManualPunchController extends Controller
{
    /**
     * Display a listing of manual punches
     */
    public function index(Request $request)
    {
        $query = ManualPunch::with(['employee', 'shift', 'addedBy']);

        // Filter by employee
        if ($request->filled('employee_payroll_id')) {
            $query->where('employee_payroll_id', $request->employee_payroll_id);
        }

        // Default to current month if no dates are provided
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());

        $query->whereDate('date', '>=', $startDate);
        $query->whereDate('date', '<=', $endDate);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Order by status: pending (1st), approved (2nd), rejected (3rd), then by date/created_at
        $manualPunches = $query->orderByRaw("FIELD(status, 'pending', 'approved', 'rejected') ASC")
                              ->orderBy('date', 'desc')
                              ->orderBy('created_at', 'desc')
                              ->paginate(20);

        $employees = Employee::where('exclude_from_payroll', 0)
                            ->orderBy('name')
                            ->get();

        return view('manual-punches.index', compact('manualPunches', 'employees', 'startDate', 'endDate'));
    }

    /**
     * Show the form for creating a new manual punch
     */
    public function create()
    {
        $employees = Employee::where('exclude_from_payroll', 0)
                            ->orderBy('name')
                            ->get();

        return view('manual-punches.create', compact('employees'));
    }

    /**
     * Store a newly created manual punch
     */
    public function store(Request $request)
    {
        $request->validate([
            'employee_payroll_ids' => 'required|array',
            'employee_payroll_ids.*' => 'required|exists:employees,payroll_id',
            'date' => 'required|date',
            'punch_in_time' => 'nullable|date_format:H:i',
            'punch_out_time' => 'nullable|date_format:H:i',
            'reason' => 'required|string|max:500',
        ], [
            'employee_payroll_ids.required' => 'Please select at least one employee',
            'punch_in_time.required_without' => 'Either punch in or punch out time is required',
        ]);

        // Validate at least one time is provided
        if (!$request->filled('punch_in_time') && !$request->filled('punch_out_time')) {
            return back()->withErrors(['time' => 'Please provide at least punch in or punch out time'])->withInput();
        }

        // Check if biometric data already exists for this month/year
        $date = Carbon::parse($request->date);
        $month = $date->month;
        $year = $date->year;

        // Check 1: Check if attendance records have been processed with biometric data
        $biometricProcessed = AttendanceRecord::where('month', $month)
            ->where('year', $year)
            ->where('has_biometric_data', true)
            ->exists();

        // Check 2: Check if raw biometric data exists in attendances table (even if not processed yet)
        $biometricImported = \App\Models\Attendance::whereYear('date', $year)
            ->whereMonth('date', $month)
            ->exists();

        if ($biometricProcessed || $biometricImported) {
            return back()->withErrors([
                'date' => 'Cannot create manual punch for ' . $date->format('F Y') . '. Biometric attendance data has already been imported for this month. Manual punches must be created BEFORE importing biometric data.'
            ])->withInput();
        }


        $createdCount = 0;

        foreach ($request->employee_payroll_ids as $payrollId) {
            $employee = Employee::where('payroll_id', $payrollId)->first();
            
            if (!$employee) {
                continue;
            }

            // Get assigned shift for this employee on this date
            $shift = $this->findEmployeeShift($payrollId, $request->date);

            ManualPunch::create([
                'employee_payroll_id' => $payrollId,
                'employee_id' => $employee->employee_id,
                'employee_email' => $employee->email,
                'date' => $request->date,
                'punch_in_time' => $request->punch_in_time,
                'punch_out_time' => $request->punch_out_time,
                'reason' => $request->reason,
                'shift_id' => $shift ? $shift->id : null,
                'added_by' => Auth::id(),
                'status' => 'approved', // Auto-approve by default
            ]);

            $createdCount++;

            // Log the action
            Log::info('Manual punch created', [
                'employee_payroll_id' => $payrollId,
                'date' => $request->date,
                'punch_in' => $request->punch_in_time,
                'punch_out' => $request->punch_out_time,
                'added_by' => Auth::id(),
            ]);
        }

        return redirect()->route('manual-punches.index')
                        ->with('success', "Successfully added manual punch for {$createdCount} employee(s). Remember to import biometric data next to process attendance.");
    }

    /**
     * Show the form for editing the specified manual punch
     */
    public function edit(ManualPunch $manualPunch)
    {
        $employees = Employee::where('exclude_from_payroll', 0)
                            ->orderBy('name')
                            ->get();

        return view('manual-punches.edit', compact('manualPunch', 'employees'));
    }

    /**
     * Update the specified manual punch
     */
    public function update(Request $request, ManualPunch $manualPunch)
    {
        $request->validate([
            'date' => 'required|date',
            'punch_in_time' => 'nullable|date_format:H:i',
            'punch_out_time' => 'nullable|date_format:H:i',
            'reason' => 'required|string|max:500',
        ]);

        // Validate at least one time is provided
        if (!$request->filled('punch_in_time') && !$request->filled('punch_out_time')) {
            return back()->withErrors(['time' => 'Please provide at least punch in or punch out time'])->withInput();
        }

        $manualPunch->update([
            'date' => $request->date,
            'punch_in_time' => $request->punch_in_time,
            'punch_out_time' => $request->punch_out_time,
            'reason' => $request->reason,
        ]);

        // Log the action
        Log::info('Manual punch updated', [
            'id' => $manualPunch->id,
            'employee_payroll_id' => $manualPunch->employee_payroll_id,
            'date' => $request->date,
            'updated_by' => Auth::id(),
        ]);

        return redirect()->route('manual-punches.index')
                        ->with('success', 'Manual punch updated successfully.');
    }

    /**
     * Remove the specified manual punch
     */
    public function destroy(ManualPunch $manualPunch)
    {
        // Log before deletion
        Log::info('Manual punch deleted', [
            'id' => $manualPunch->id,
            'employee_payroll_id' => $manualPunch->employee_payroll_id,
            'date' => $manualPunch->date,
            'deleted_by' => Auth::id(),
        ]);

        $manualPunch->delete();

        return redirect()->route('manual-punches.index')
                        ->with('success', 'Manual punch deleted successfully.');
    }

    /**
     * Get employee shift for a specific date (AJAX)
     */
    public function getEmployeeShift(Request $request)
    {
        $payrollId = $request->input('payroll_id');
        $date = $request->input('date');

        if (!$payrollId || !$date) {
            return response()->json(['error' => 'Missing parameters'], 400);
        }

        $shift = $this->findEmployeeShift($payrollId, $date);

        if ($shift) {
            return response()->json([
                'success' => true,
                'shift' => [
                    'id' => $shift->id,
                    'name' => $shift->name,
                    'start_time' => $shift->start_time,
                    'end_time' => $shift->end_time,
                    'start_time_formatted' => Carbon::parse($shift->start_time)->format('g:i A'),
                    'end_time_formatted' => Carbon::parse($shift->end_time)->format('g:i A'),
                ],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No shift assigned for this employee on this date',
        ]);
    }

    /**
     * Helper: Find employee's shift for a specific date
     */
    private function findEmployeeShift($payrollId, $date)
    {
        // Find from duty roster
        $roster = DutyRoster::where('employee_payroll_id', $payrollId)
            ->where('date', $date)
            ->first();

        if ($roster && $roster->shift_id) {
            return Shift::find($roster->shift_id);
        }

        // Try to find employee's default/recent shift (within last 30 days)
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

    /**
     * Approve a pending manual punch correction request
     */
    public function approveCorrection($id)
    {
        $punch = ManualPunch::findOrFail($id);
        
        if ($punch->status !== 'pending') {
            return back()->with('error', 'This request has already been processed.');
        }

        DB::beginTransaction();
        try {
            $punch->status = 'approved';
            $punch->approved_by = Auth::id();
            $punch->save();

            // Find employee
            $employee = Employee::where('payroll_id', $punch->employee_payroll_id)->first();
            $user = \App\Models\User::where('payroll_id', $punch->employee_payroll_id)->first();

            // Calculate total hours
            $totalHours = 0;
            if ($punch->punch_in_time && $punch->punch_out_time) {
                $in = strtotime($punch->punch_in_time);
                $out = strtotime($punch->punch_out_time);
                if ($out > $in) {
                    $totalHours = round(($out - $in) / 3600, 2);
                }
            }

            // Sync with attendances table
            $attendance = Attendance::updateOrCreate(
                [
                    'employee_payroll_id' => $punch->employee_payroll_id,
                    'date' => $punch->date->toDateString(),
                ],
                [
                    'check_in_time' => $punch->punch_in_time ? Carbon::parse($punch->punch_in_time)->format('H:i:s') : null,
                    'check_out_time' => $punch->punch_out_time ? Carbon::parse($punch->punch_out_time)->format('H:i:s') : null,
                    'total_hours' => $totalHours,
                    'status' => $totalHours > 0 ? 'present' : 'absent',
                    'shift_id' => $punch->shift_id,
                    'source' => 'manual_correction'
                ]
            );

            // Sync with attendance_records table
            AttendanceRecord::updateOrCreate(
                [
                    'payroll_id' => $punch->employee_payroll_id,
                    'date' => $punch->date->toDateString(),
                ],
                [
                    'employee_id' => $employee ? $employee->employee_id : null,
                    'employee_email' => $employee ? $employee->email : null,
                    'user_id' => $user ? $user->id : null,
                    'status' => $attendance->status,
                    'check_in_time' => $attendance->check_in_time,
                    'check_out_time' => $attendance->check_out_time,
                    'total_hours' => $totalHours,
                    'data_source' => 'manual',
                    'shift_id' => $punch->shift_id,
                    'month' => $punch->date->month,
                    'year' => $punch->date->year,
                    'is_locked' => false
                ]
            );

            DB::commit();
            return back()->with('success', 'Missed punch correction approved successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to approve manual punch', ['error' => $e->getMessage()]);
            return back()->with('error', 'Error approving correction: ' . $e->getMessage());
        }
    }

    /**
     * Reject a pending manual punch correction request
     */
    public function rejectCorrection(Request $request, $id)
    {
        $punch = ManualPunch::findOrFail($id);
        
        if ($punch->status !== 'pending') {
            return back()->with('error', 'This request has already been processed.');
        }

        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $punch->status = 'rejected';
        $punch->approved_by = Auth::id();
        $punch->rejection_reason = $request->rejection_reason;
        $punch->save();

        return back()->with('success', 'Missed punch correction request rejected.');
    }
}
