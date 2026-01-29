<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\EmployeeBasicDetail;
use App\Models\EmployeeAdvance;
use App\Models\EmployeeAdvanceDeduction;
use App\Services\ActivityLogService;

class AdvanceController extends Controller
{
    /**
     * Get advances for a specific employee
     */
    public function getEmployeeAdvances($employeeId)
    {
        try {
            $employee = EmployeeBasicDetail::with(['advances.deductions'])
                ->findOrFail($employeeId);

            $advances = $employee->advances->map(function ($advance) {
                return [
                    'id' => $advance->id,
                    'advance_amount' => $advance->advance_amount,
                    'tenure_months' => $advance->tenure_months,
                    'monthly_deduction' => $advance->monthly_deduction,
                    'start_date' => $advance->start_date->format('Y-m-d'),
                    'end_date' => $advance->end_date->format('Y-m-d'),
                    'total_deducted' => $advance->total_deducted,
                    'remaining_amount' => $advance->remaining_amount,
                    'status' => $advance->status,
                    'notes' => $advance->notes,
                    'created_at' => $advance->created_at->format('d M Y'),
                    'deduction_count' => $advance->deductions->count()
                ];
            });

            return response()->json([
                'success' => true,
                'employee' => [
                    'id' => $employee->id,
                    'name' => $employee->name,
                    'employee_id' => $employee->employee_id,
                    'designation' => $employee->designation
                ],
                'advances' => $advances
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch employee advances.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get the partial view for managing employee advances
     */
    public function getAdvancesPartialView($employeeId)
    {
        try {
            $employee = EmployeeBasicDetail::with(['advances.deductions'])
                ->findOrFail($employeeId);

            return view('payroll.advances._advance_modal_content', compact('employee'))->render();
        } catch (\Exception $e) {
            return response('<div class="alert alert-danger">Failed to load employee data: ' . $e->getMessage() . '</div>', 500);
        }
    }

    /**
     * Add a new advance for an employee
     */
    public function addAdvance(Request $request)
    {

       // print_r("hi employee");
        $request->validate([
            'employee_id' => 'required|exists:employee_basic_details,id',
            'advance_amount' => 'required|numeric|min:1',
            'tenure_months' => 'required|integer|min:1|max:60',
            'start_date' => 'required|string', // Expected format: YYYY-MM
            'notes' => 'nullable|string|max:500'
        ]);

        try {
            DB::beginTransaction();

            $employee = EmployeeBasicDetail::find($request->employee_id);
            $advanceAmount = $request->advance_amount;
            $tenureMonths = $request->tenure_months;
            $monthlyDeduction = round($advanceAmount / $tenureMonths, 2);

            // Parse month input (YYYY-MM) to first day of month
            $startDate = Carbon::createFromFormat('Y-m', $request->start_date)->startOfMonth();
            
            // Check against last completed payroll
            $latestCompleted = \App\Models\EmployeePayrollAttendancePayoutMonthStatus::where('status', 'completed')
                ->orderByDesc('payout_year')
                ->orderByDesc('payout_month')
                ->first();
                
            if ($latestCompleted) {
                $lastCompletedDate = Carbon::createFromDate($latestCompleted->payout_year, $latestCompleted->payout_month, 1)->endOfMonth();
                if ($startDate->lte($lastCompletedDate)) {
                    throw new \Exception('Cannot create advance for a month where payroll is already completed. Please select a future month.');
                }
            }
            
            $currentMonth = Carbon::now()->startOfMonth();

            // Validation removed to allow backdated advances for historical payroll processing
            /*
            if ($startDate->lt($currentMonth)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Advance start month cannot be in the past. Please select the current or a future month.'
                ], 422);
            }
            */

            $endDate = $startDate->copy()->addMonthsNoOverflow($tenureMonths - 1)->endOfMonth();

            $advance = EmployeeAdvance::create([
                'employee_id' => $request->employee_id,
                'advance_amount' => $advanceAmount,
                'tenure_months' => $tenureMonths,
                'monthly_deduction' => $monthlyDeduction,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'notes' => $request->notes,
                'status' => 'active',
                'created_by' => Auth::id(),
            ]);

            // Log the activity
            ActivityLogService::log(
                'employee_advance_create',
                'Created employee advance',
                "Created advance of ₹{$advanceAmount} for {$employee->name} ({$employee->employee_id})",
                [
                    'employee_id' => $employee->id,
                    'employee_name' => $employee->name,
                    'employee_code' => $employee->employee_id,
                    'advance_id' => $advance->id,
                    'advance_amount' => $advanceAmount,
                    'tenure_months' => $tenureMonths,
                    'monthly_deduction' => $monthlyDeduction,
                    'start_date' => $startDate->format('Y-m-d'),
                    'end_date' => $endDate->format('Y-m-d'),
                    'notes' => $request->notes
                ]
            );

            \Log::info('Advance created successfully. ID: ' . $advance->id . ' for Employee: ' . $request->employee_id);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Advance added successfully!',
                'advance' => [
                    'id' => $advance->id,
                    'advance_amount' => $advance->advance_amount,
                    'monthly_deduction' => $advance->monthly_deduction,
                    'tenure_months' => $advance->tenure_months,
                    'start_date' => $advance->start_date->format('Y-m-d'),
                    'end_date' => $advance->end_date->format('Y-m-d')
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Failed to add advance: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update advance status (cancel, reactivate, etc.)
     */
    public function updateAdvanceStatus(Request $request, $advanceId)
    {
        $request->validate([
            'status' => 'required|in:active,cancelled,completed'
        ]);

        try {
            $advance = EmployeeAdvance::with('employee')->findOrFail($advanceId);
            $oldStatus = $advance->status;
            
            $advance->update([
                'status' => $request->status,
                'updated_by' => Auth::id()
            ]);

            // Log the activity
            ActivityLogService::log(
                'employee_advance_status_update',
                'Updated advance status',
                "Updated advance status for {$advance->employee->name} from {$oldStatus} to {$request->status}",
                [
                    'employee_id' => $advance->employee->id,
                    'employee_name' => $advance->employee->name,
                    'employee_code' => $advance->employee->employee_id,
                    'advance_id' => $advance->id,
                    'advance_amount' => $advance->advance_amount,
                    'old_status' => $oldStatus,
                    'new_status' => $request->status
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Advance status updated successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update advance status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Override advance deduction for a specific month
     */
    public function overrideDeduction(Request $request)
    {
        $request->validate([
            'advance_id' => 'required|exists:employee_advances,id',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020',
            'override_amount' => 'required|numeric|min:0',
            'reason' => 'nullable|string|max:255'
        ]);

        try {
            DB::beginTransaction();

            $advance = EmployeeAdvance::findOrFail($request->advance_id);
            $month = $request->month;
            $year = $request->year;
            $overrideAmount = $request->override_amount;

            // Check if deduction already exists for this month
            $existingDeduction = EmployeeAdvanceDeduction::where('advance_id', $advance->id)
                ->where('month', $month)
                ->where('year', $year)
                ->first();

            if ($existingDeduction) {
                // Update existing deduction
                $existingDeduction->update([
                    'amount' => $overrideAmount,
                    'is_override' => true,
                    'override_reason' => $request->reason,
                    'updated_by' => Auth::id()
                ]);
            } else {
                // Create new override deduction
                EmployeeAdvanceDeduction::create([
                    'advance_id' => $advance->id,
                    'month' => $month,
                    'year' => $year,
                    'amount' => $overrideAmount,
                    'is_override' => true,
                    'override_reason' => $request->reason,
                    'created_by' => Auth::id()
                ]);
            }

            DB::commit();

            // Log the activity for override deduction
            ActivityLogService::log(
                'employee_advance_deduction_override',
                'Override advance deduction',
                "Override deduction for advance ID {$advance->id} - Amount: ₹{$overrideAmount} for {$month}/{$year}",
                [
                    'employee_id' => $advance->employee->id,
                    'employee_name' => $advance->employee->name,
                    'employee_code' => $advance->employee->employee_id,
                    'advance_id' => $advance->id,
                    'month' => $month,
                    'year' => $year,
                    'override_amount' => $overrideAmount,
                    'reason' => $request->reason,
                    'is_new' => !$existingDeduction
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Advance deduction override saved successfully!'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Failed to save override: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get advance report data
     */
    public function getAdvanceReport(Request $request)
    {
        try {
            $month = $request->get('month', Carbon::now()->month);
            $year = $request->get('year', Carbon::now()->year);
            $employeeId = $request->get('employee_id');

            $query = EmployeeAdvanceDeduction::with(['advance.employee'])
                ->where('month', $month)
                ->where('year', $year);

            if ($employeeId) {
                $query->whereHas('advance', function($q) use ($employeeId) {
                    $q->where('employee_id', $employeeId);
                });
            }

            $deductions = $query->get();

            $reportData = $deductions->map(function ($deduction) {
                return [
                    'employee_id' => $deduction->advance->employee->employee_id,
                    'employee_name' => $deduction->advance->employee->name,
                    'advance_id' => $deduction->advance->id,
                    'advance_amount' => $deduction->advance->advance_amount,
                    'monthly_deduction' => $deduction->advance->monthly_deduction,
                    'deduction_amount' => $deduction->amount,
                    'is_override' => $deduction->is_override,
                    'override_reason' => $deduction->override_reason,
                    'remaining_amount' => $deduction->advance->remaining_amount,
                    'status' => $deduction->advance->status
                ];
            });

            return response()->json([
                'success' => true,
                'month' => $month,
                'year' => $year,
                'total_deductions' => $deductions->sum('amount'),
                'deduction_count' => $deductions->count(),
                'data' => $reportData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate report: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get details of a single advance for editing
     */
    public function getAdvanceDetails($advanceId)
    {
        try {
            $advance = EmployeeAdvance::findOrFail($advanceId);

            // Check if the advance has any deductions. If so, it cannot be edited.
            if ($advance->deductions()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This advance cannot be edited because deductions have already been made.',
                    'can_edit' => false
                ], 403);
            }

            return response()->json([
                'success' => true,
                'can_edit' => true,
                'advance' => [
                    'id' => $advance->id,
                    'advance_amount' => $advance->advance_amount,
                    'tenure_months' => $advance->tenure_months,
                    'start_date' => $advance->start_date->format('Y-m-d'),
                    'notes' => $advance->notes,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch advance details.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update an existing advance
     */
    public function updateAdvance(Request $request, $advanceId)
    {
        $request->validate([
            'advance_amount' => 'required|numeric|min:1',
            'tenure_months' => 'required|integer|min:1|max:60',
            'start_date' => 'required|string', // Expected format: YYYY-MM
            'notes' => 'nullable|string|max:500'
        ]);

        try {
            DB::beginTransaction();

            $advance = EmployeeAdvance::findOrFail($advanceId);

            // Security check: ensure no deductions have been made
            if ($advance->deductions()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot update advance as deductions have already started.'
                ], 403);
            }

            $advanceAmount = $request->advance_amount;
            $tenureMonths = $request->tenure_months;
            $monthlyDeduction = round($advanceAmount / $tenureMonths, 2);

            $startDate = Carbon::createFromFormat('Y-m', $request->start_date)->startOfMonth();
            $endDate = $startDate->copy()->addMonthsNoOverflow($tenureMonths - 1)->endOfMonth();

            $advance->update([
                'advance_amount' => $advanceAmount,
                'tenure_months' => $tenureMonths,
                'monthly_deduction' => $monthlyDeduction,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'notes' => $request->notes,
                'status' => 'active', // Reset status if it was changed
                'updated_by' => Auth::id(),
            ]);

            // Log the activity
            ActivityLogService::log(
                'employee_advance_update',
                'Updated employee advance',
                "Updated advance for {$advance->employee->name} ({$advance->employee->employee_id}) - Amount: ₹{$advanceAmount}",
                [
                    'employee_id' => $advance->employee->id,
                    'employee_name' => $advance->employee->name,
                    'employee_code' => $advance->employee->employee_id,
                    'advance_id' => $advance->id,
                    'advance_amount' => $advanceAmount,
                    'tenure_months' => $tenureMonths,
                    'monthly_deduction' => $monthlyDeduction,
                    'start_date' => $startDate->format('Y-m-d'),
                    'end_date' => $endDate->format('Y-m-d'),
                    'notes' => $request->notes
                ]
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Advance updated successfully!'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update advance: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Close an advance manually
     */
    public function closeAdvance($advanceId)
    {
        try {
            DB::beginTransaction();

            $advance = EmployeeAdvance::findOrFail($advanceId);

            // Check if advance is already closed/cancelled
            if ($advance->status === 'cancelled') {
                return response()->json([
                    'success' => false,
                    'message' => 'Advance is already cancelled.'
                ], 400);
            }

            if ($advance->status === 'completed') {
                return response()->json([
                    'success' => false,
                    'message' => 'Advance is already completed.'
                ], 400);
            }

            // Update the advance status to cancelled (since 'closed' is not allowed)
            $advance->update([
                'status' => 'cancelled',
                'updated_by' => Auth::id(),
            ]);

            // Log the activity
            ActivityLogService::log(
                'employee_advance_close',
                'Closed employee advance',
                "Closed advance for {$advance->employee->name} ({$advance->employee->employee_id}) - Amount: ₹{$advance->advance_amount}",
                [
                    'employee_id' => $advance->employee->id,
                    'employee_name' => $advance->employee->name,
                    'employee_code' => $advance->employee->employee_id,
                    'advance_id' => $advance->id,
                    'advance_amount' => $advance->advance_amount,
                    'remaining_amount' => $advance->remaining_amount,
                    'previous_status' => $advance->getOriginal('status')
                ]
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Advance cancelled successfully!'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel advance: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Soft delete an advance
     */
    public function deleteAdvance($advanceId)
    {
        try {
            DB::beginTransaction();

            $advance = EmployeeAdvance::findOrFail($advanceId);

            // Check if any deductions have been made
            $deductionCount = $advance->deductions()->count();
            if ($deductionCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete advance as deductions have already been made. Consider closing it instead.'
                ], 403);
            }

            // Soft delete the advance
            $advance->delete();

            // Log the activity
            ActivityLogService::log(
                'employee_advance_delete',
                'Deleted employee advance',
                "Deleted advance for {$advance->employee->name} ({$advance->employee->employee_id}) - Amount: ₹{$advance->advance_amount}",
                [
                    'employee_id' => $advance->employee->id,
                    'employee_name' => $advance->employee->name,
                    'employee_code' => $advance->employee->employee_id,
                    'advance_id' => $advance->id,
                    'advance_amount' => $advance->advance_amount,
                    'remaining_amount' => $advance->remaining_amount,
                    'status' => $advance->status
                ]
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Advance deleted successfully!'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete advance: ' . $e->getMessage()
            ], 500);
        }
    }
    /**
     * Get detailed history of an advance
     */
    public function getAdvanceHistory($advanceId)
    {
        try {
            $advance = EmployeeAdvance::with(['employee', 'deductions'])
                ->findOrFail($advanceId);

            $deductions = $advance->deductions->sortBy('year')->sortBy('month')->map(function ($deduction) {
                return [
                    'id' => $deduction->id,
                    'month_year' => Carbon::createFromDate($deduction->year, $deduction->month, 1)->format('M Y'),
                    'amount' => $deduction->amount,
                    'is_override' => $deduction->is_override,
                    'override_reason' => $deduction->override_reason,
                    'created_at' => $deduction->created_at->format('d M Y h:i A')
                ];
            })->values();

            // Calculate expected end date if active
            $expectedEndDate = $advance->start_date->copy()->addMonths($advance->tenure_months - 1)->endOfMonth()->format('d M Y');

            return response()->json([
                'success' => true,
                'advance' => [
                    'id' => $advance->id,
                    'amount' => $advance->advance_amount,
                    'tenure' => $advance->tenure_months,
                    'monthly_deduction' => $advance->monthly_deduction,
                    'total_paid' => $advance->total_deducted,
                    'remaining' => $advance->remaining_amount,
                    'start_date' => $advance->start_date->format('d M Y'),
                    'expected_end_date' => $expectedEndDate,
                    'status' => $advance->status,
                    'notes' => $advance->notes,
                    'employee_name' => $advance->employee->name,
                    'employee_code' => $advance->employee->employee_id,
                ],
                'deductions' => $deductions
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch advance history.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
