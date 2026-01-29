<?php

namespace App\Http\Controllers;

use App\Models\Overtime;
use App\Models\Employee;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class OvertimeController extends Controller
{
    /**
     * Display the overtime management interface.
     */
    public function index()
    {
        $currentMonth = now()->month;
        $currentYear = now()->year;

        return view('overtime.index', compact('currentMonth', 'currentYear'));
    }

    /**
     * Get overtime data for a specific month/year.
     */
    public function getData(Request $request): JsonResponse
    {
        $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020|max:' . (now()->year + 1)
        ]);

        $month = $request->month;
        $year = $request->year;

        // Get all employees
        $employees = Employee::select('payroll_id', 'name')->get();

        $overtimeData = [];

        foreach ($employees as $employee) {
            // If the overtime table doesn't exist yet (migrations not run), fall back to calculated values
            if (!Schema::hasTable('overtimes')) {
                $calculatedOvertime = $this->calculateOvertimeFromAttendance($employee->payroll_id, $month, $year);
                $overtimeData[] = [
                    'employee_payroll_id' => $employee->payroll_id,
                    'employee_name' => $employee->name,
                    'overtime_hours' => $calculatedOvertime,
                    'is_locked' => false,
                    'source' => 'attendance_calculation'
                ];
            } else {
                // Check if there's already an overtime record for this employee/month
                $existingOvertime = Overtime::where('employee_payroll_id', $employee->payroll_id)
                    ->where('month', $month)
                    ->where('year', $year)
                    ->first();

                if ($existingOvertime) {
                    // Use existing overtime record
                    $overtimeData[] = [
                        'employee_payroll_id' => $employee->payroll_id,
                        'employee_name' => $employee->name,
                        'overtime_hours' => (float) $existingOvertime->overtime_hours,
                        'calculated_ot_hours' => (float) $existingOvertime->calculated_ot_hours,
                        'approved_ot_hours' => $existingOvertime->approved_ot_hours ? (float) $existingOvertime->approved_ot_hours : null,
                        'is_manually_overridden' => $existingOvertime->is_manually_overridden,
                        'approval_status' => $existingOvertime->approval_status,
                        'is_locked' => $existingOvertime->is_locked,
                        'remarks' => $existingOvertime->remarks,
                        'source' => 'overtime_table'
                    ];
                } else {
                    // Calculate from attendance records
                    $calculatedOvertime = $this->calculateOvertimeFromAttendance($employee->payroll_id, $month, $year);

                    $overtimeData[] = [
                        'employee_payroll_id' => $employee->payroll_id,
                        'employee_name' => $employee->name,
                        'overtime_hours' => $calculatedOvertime,
                        'calculated_ot_hours' => $calculatedOvertime,
                        'approved_ot_hours' => null,
                        'is_manually_overridden' => false,
                        'approval_status' => 'pending',
                        'is_locked' => false,
                        'remarks' => null,
                        'source' => 'attendance_calculation'
                    ];
                }
            }
        }

        return response()->json([
            'success' => true,
            'data' => $overtimeData
        ]);
    }

    /**
     * Save overtime data (unlocked).
     */
    public function save(Request $request): JsonResponse
    {
        $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020|max:' . (now()->year + 1),
            'overtime_data' => 'required|array',
            'overtime_data.*.employee_payroll_id' => 'required|string',
            'overtime_data.*.overtime_hours' => 'required|numeric|min:0|max:999.99'
        ]);

        $month = $request->month;
        $year = $request->year;
        $userId = Auth::id();

        // Ensure overtime table exists before attempting to save
        if (!Schema::hasTable('overtimes')) {
            return response()->json([
                'success' => false,
                'message' => 'Overtime storage table not found. Please run database migrations.'
            ], 500);
        }

        DB::beginTransaction();
        try {
            foreach ($request->overtime_data as $data) {
                $overtime = Overtime::where('employee_payroll_id', $data['employee_payroll_id'])
                    ->where('month', $month)
                    ->where('year', $year)
                    ->first();

                // Check if this is a manual override (approved_ot_hours different from calculated)
                $isOverride = false;
                $originalCalculated = null;
                
                if ($overtime) {
                    // If approved hours are being set and different from calculated
                    if (isset($data['approved_ot_hours']) && $data['approved_ot_hours'] != $overtime->calculated_ot_hours) {
                        $isOverride = true;
                        $originalCalculated = $overtime->calculated_ot_hours;
                    }
                }

                Overtime::updateOrCreate(
                    [
                        'employee_payroll_id' => $data['employee_payroll_id'],
                        'month' => $month,
                        'year' => $year
                    ],
                    [
                        'overtime_hours' => $data['overtime_hours'],
                        'approved_ot_hours' => $data['approved_ot_hours'] ?? null,
                        'is_manually_overridden' => $isOverride,
                        'original_calculated_hours' => $isOverride ? $originalCalculated : null,
                        'overridden_by' => $isOverride ? $userId : null,
                        'overridden_at' => $isOverride ? now() : null,
                        'remarks' => $data['remarks'] ?? null,
                        'is_locked' => false,
                        'locked_at' => null,
                        'updated_by' => $userId,
                        'created_by' => $userId
                    ]
                );
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Overtime data saved successfully.'
            ]);

        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'success' => false,
                'message' => 'Failed to save overtime data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Save and lock overtime data.
     */
    public function lock(Request $request): JsonResponse
    {
        $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020|max:' . (now()->year + 1),
            'overtime_data' => 'required|array',
            'overtime_data.*.employee_payroll_id' => 'required|string',
            'overtime_data.*.overtime_hours' => 'required|numeric|min:0|max:999.99'
        ]);

        $month = $request->month;
        $year = $request->year;
        $userId = Auth::id();

        // Ensure overtime table exists before attempting to lock
        if (!Schema::hasTable('overtimes')) {
            return response()->json([
                'success' => false,
                'message' => 'Overtime storage table not found. Please run database migrations.'
            ], 500);
        }

        // Check if any records are already locked
        $lockedRecords = Overtime::where('month', $month)
            ->where('year', $year)
            ->where('is_locked', true)
            ->count();

        if ($lockedRecords > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Some records for this month/year are already locked and cannot be modified.'
            ], 400);
        }

        DB::beginTransaction();
        try {
            foreach ($request->overtime_data as $data) {
                $overtime = Overtime::where('employee_payroll_id', $data['employee_payroll_id'])
                    ->where('month', $month)
                    ->where('year', $year)
                    ->first();

                // Check if this is a manual override
                $isOverride = false;
                $originalCalculated = null;
                
                if ($overtime && isset($data['approved_ot_hours']) && $data['approved_ot_hours'] != $overtime->calculated_ot_hours) {
                    $isOverride = true;
                    $originalCalculated = $overtime->calculated_ot_hours;
                }

                Overtime::updateOrCreate(
                    [
                        'employee_payroll_id' => $data['employee_payroll_id'],
                        'month' => $month,
                        'year' => $year
                    ],
                    [
                        'overtime_hours' => $data['overtime_hours'],
                        'approved_ot_hours' => $data['approved_ot_hours'] ?? null,
                        'is_manually_overridden' => $isOverride,
                        'original_calculated_hours' => $isOverride ? $originalCalculated : null,
                        'overridden_by' => $isOverride ? $userId : null,
                        'overridden_at' => $isOverride ? now() : null,
                        'approval_status' => 'approved',
                        'approved_by' => $userId,
                        'approved_at' => now(),
                        'remarks' => $data['remarks'] ?? null,
                        'is_locked' => true,
                        'locked_at' => now(),
                        'updated_by' => $userId,
                        'created_by' => $userId
                    ]
                );
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Overtime data saved and locked successfully. This data will now be available in the payroll API.'
            ]);

        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'success' => false,
                'message' => 'Failed to save and lock overtime data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Approve overtime for an employee
     */
    public function approve(Request $request): JsonResponse
    {
        $request->validate([
            'employee_payroll_id' => 'required|string',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020|max:' . (now()->year + 1),
            'approved_ot_hours' => 'nullable|numeric|min:0|max:999.99',
            'remarks' => 'nullable|string|max:500'
        ]);

        $overtime = Overtime::where('employee_payroll_id', $request->employee_payroll_id)
            ->where('month', $request->month)
            ->where('year', $request->year)
            ->first();

        if (!$overtime) {
            return response()->json([
                'success' => false,
                'message' => 'Overtime record not found.'
            ], 404);
        }

        if ($overtime->is_locked) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot approve locked overtime record.'
            ], 400);
        }

        $overtime->approval_status = 'approved';
        $overtime->approved_by = Auth::id();
        $overtime->approved_at = now();
        
        if ($request->has('approved_ot_hours')) {
            $overtime->approved_ot_hours = $request->approved_ot_hours;
            $overtime->overtime_hours = $request->approved_ot_hours;
            
            if ($request->approved_ot_hours != $overtime->calculated_ot_hours) {
                $overtime->is_manually_overridden = true;
                $overtime->original_calculated_hours = $overtime->calculated_ot_hours;
                $overtime->overridden_by = Auth::id();
                $overtime->overridden_at = now();
            }
        } else {
            $overtime->overtime_hours = $overtime->calculated_ot_hours;
        }
        
        if ($request->has('remarks')) {
            $overtime->remarks = $request->remarks;
        }
        
        $overtime->save();

        return response()->json([
            'success' => true,
            'message' => 'Overtime approved successfully.'
        ]);
    }

    /**
     * API endpoint to get overtime data for payroll integration.
     */
    public function apiData(Request $request): JsonResponse
    {
        // Check for API key or token authentication
        // You may need to adjust this based on your existing API auth pattern

        $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020|max:' . (now()->year + 1)
        ]);

        $month = $request->month;
        $year = $request->year;

        // Get only locked overtime records
        if (!Schema::hasTable('overtimes')) {
            return response()->json([
                'success' => true,
                'month' => $month,
                'year' => $year,
                'total_records' => 0,
                'data' => []
            ]);
        }

        $overtimeRecords = Overtime::with('employee')
            ->locked()
            ->forMonth($month, $year)
            ->get();

        $apiData = $overtimeRecords->map(function ($record) {
            return [
                'employee_payroll_id' => $record->employee_payroll_id,
                'employee_name' => $record->employee->name ?? 'Unknown',
                'month' => $record->month,
                'year' => $record->year,
                'overtime_hours' => (float) $record->overtime_hours,
                'locked_at' => $record->locked_at->toISOString()
            ];
        });

        return response()->json([
            'success' => true,
            'month' => $month,
            'year' => $year,
            'total_records' => $apiData->count(),
            'data' => $apiData
        ]);
    }

    /**
     * Calculate overtime hours from attendance records for a specific employee/month/year.
     */
    private function calculateOvertimeFromAttendance(string $employeePayrollId, int $month, int $year): float
    {
        // Get attendance records for the month
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $totalOvertime = Attendance::where('employee_payroll_id', $employeePayrollId)
            ->whereBetween('date', [$startDate, $endDate])
            ->sum('overtime_hours');

        // Ensure we always return a float, even if no records exist
        return round((float) $totalOvertime, 2);
    }
}
