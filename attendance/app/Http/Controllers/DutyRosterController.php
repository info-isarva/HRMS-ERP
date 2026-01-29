<?php

namespace App\Http\Controllers;

use App\Models\DutyRoster;
use App\Models\Employee;
use App\Models\Shift;
use Illuminate\Http\Request;

class DutyRosterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = DutyRoster::with(['employee', 'shift']);

        // Handle date filtering based on date range
        if ($request->has('start_date') && $request->has('end_date') && $request->start_date && $request->end_date) {
            // Date range filter
            $query->whereBetween('date', [$request->start_date, $request->end_date]);
        } elseif ($request->has('start_date') && $request->start_date) {
            // Single date filter
            $query->where('date', $request->start_date);
        } elseif ($request->has('date') && $request->date) {
            // Legacy date filter
            $query->where('date', $request->date);
        }

        // Employee filter
        if ($request->has('employee_payroll_id') && $request->employee_payroll_id) {
            $query->where('employee_payroll_id', $request->employee_payroll_id);
        }

        $dutyRosters = $query->orderBy('date', 'desc')->paginate(20);

        $employees = Employee::all();
        $shifts = Shift::all();

        return view('duty-rosters.index', compact('dutyRosters', 'employees', 'shifts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $employees = Employee::all();
        $shifts = Shift::all();
        return view('duty-rosters.create', compact('employees', 'shifts'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'employee_payroll_id' => 'required|string|exists:employees,payroll_id',
            'shift_id' => 'required|exists:shifts,id',
            'date' => 'required|date',
        ]);

        // Check if already exists
        $exists = DutyRoster::where('employee_payroll_id', $request->employee_payroll_id)
                            ->where('date', $request->date)
                            ->exists();

        if ($exists) {
            return back()->withErrors(['date' => 'Duty roster already exists for this employee on this date.']);
        }

        DutyRoster::create($request->only(['employee_payroll_id', 'shift_id', 'date']));

        return redirect()->route('duty-rosters.index')->with('success', 'Duty roster created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(DutyRoster $dutyRoster)
    {
        $dutyRoster->load(['employee', 'shift']);
        return view('duty-rosters.show', compact('dutyRoster'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DutyRoster $dutyRoster)
    {
        $employees = Employee::all();
        $shifts = Shift::all();
        return view('duty-rosters.edit', compact('dutyRoster', 'employees', 'shifts'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DutyRoster $dutyRoster)
    {
        $request->validate([
            'employee_payroll_id' => 'required|string|exists:employees,payroll_id',
            'shift_id' => 'required|exists:shifts,id',
            'date' => 'required|date',
        ]);

        // Check if another roster exists for same employee and date
        $exists = DutyRoster::where('employee_payroll_id', $request->employee_payroll_id)
                            ->where('date', $request->date)
                            ->where('id', '!=', $dutyRoster->id)
                            ->exists();

        if ($exists) {
            return back()->withErrors(['date' => 'Duty roster already exists for this employee on this date.']);
        }

        $dutyRoster->update($request->only(['employee_payroll_id', 'shift_id', 'date']));

        return redirect()->route('duty-rosters.index')->with('success', 'Duty roster updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DutyRoster $dutyRoster)
    {
        $dutyRoster->delete();

        return redirect()->route('duty-rosters.index')->with('success', 'Duty roster deleted successfully.');
    }

    /**
     * Show the bulk assignment form.
     */
    public function bulkCreate()
    {
        $employees = Employee::all();
        $shifts = Shift::all();
        $departments = \App\Models\Department::all();

        return view('duty-rosters.bulk-create', compact('employees', 'shifts', 'departments'));
    }

    /**
     * Store bulk duty roster assignments.
     */
    public function bulkStore(Request $request)
    {
        $request->validate([
            'employees' => 'required|array|min:1',
            'employees.*' => 'exists:employees,payroll_id',
            'shift_id' => 'required|exists:shifts,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $employees = $request->employees;
        $shiftId = $request->shift_id;
        $startDate = \Carbon\Carbon::parse($request->start_date);
        $endDate = \Carbon\Carbon::parse($request->end_date);

        $created = 0;
        $skipped = 0;
        $errors = [];

        // Loop through each employee
        foreach ($employees as $employeePayrollId) {
            // Loop through each date in the range
            for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
                $dateString = $date->format('Y-m-d');

                // Check if assignment already exists
                $exists = DutyRoster::where('employee_payroll_id', $employeePayrollId)
                                    ->where('date', $dateString)
                                    ->exists();

                if (!$exists) {
                    try {
                        DutyRoster::create([
                            'employee_payroll_id' => $employeePayrollId,
                            'shift_id' => $shiftId,
                            'date' => $dateString,
                        ]);
                        $created++;
                    } catch (\Exception $e) {
                        $errors[] = "Failed to assign shift to employee {$employeePayrollId} on {$dateString}: " . $e->getMessage();
                    }
                } else {
                    $skipped++;
                }
            }
        }

        $message = "Bulk assignment completed: {$created} assignments created";
        if ($skipped > 0) {
            $message .= ", {$skipped} skipped (already existed)";
        }
        if (!empty($errors)) {
            $message .= ". Errors: " . implode('; ', $errors);
        }

        return redirect()->route('duty-rosters.index')->with('success', $message);
    }

    /**
     * Copy previous week's duty roster to target week.
     */
    public function copyWeek(Request $request)
    {
        $targetWeek = $request->target_week ? \Carbon\Carbon::parse($request->target_week) : now();
        $targetWeekStart = $targetWeek->copy()->startOfWeek(); // Monday

        // Get previous week (7 days before target week start)
        $previousWeekStart = $targetWeekStart->copy()->subDays(7);
        $previousWeekEnd = $previousWeekStart->copy()->addDays(6);

        // Get all duty rosters from previous week
        $previousWeekRosters = DutyRoster::whereBetween('date', [
            $previousWeekStart->format('Y-m-d'),
            $previousWeekEnd->format('Y-m-d')
        ])->get();

        $copied = 0;
        $skipped = 0;

        foreach ($previousWeekRosters as $roster) {
            // Calculate corresponding date in target week
            $sourceDate = \Carbon\Carbon::parse($roster->date);
            $dayOfWeek = $sourceDate->dayOfWeek; // 0 = Sunday, 1 = Monday, etc.
            $targetDate = $targetWeekStart->copy()->addDays($dayOfWeek === 0 ? 6 : $dayOfWeek - 1);

            // Check if assignment already exists for target date
            $exists = DutyRoster::where('employee_payroll_id', $roster->employee_payroll_id)
                                ->where('date', $targetDate->format('Y-m-d'))
                                ->exists();

            if (!$exists) {
                try {
                    DutyRoster::create([
                        'employee_payroll_id' => $roster->employee_payroll_id,
                        'shift_id' => $roster->shift_id,
                        'date' => $targetDate->format('Y-m-d'),
                    ]);
                    $copied++;
                } catch (\Exception $e) {
                    // Skip errors for individual assignments
                }
            } else {
                $skipped++;
            }
        }

        $message = "Week copy completed: {$copied} assignments copied";
        if ($skipped > 0) {
            $message .= ", {$skipped} skipped (already existed)";
        }

        return redirect()->route('duty-rosters.index', ['date' => $targetWeekStart->format('Y-m-d')])
                        ->with('success', $message);
    }

    /**
     * Clear all duty roster assignments for target week.
     */
    public function clearWeek(Request $request)
    {
        $targetWeek = $request->target_week ? \Carbon\Carbon::parse($request->target_week) : now();
        $targetWeekStart = $targetWeek->copy()->startOfWeek(); // Monday
        $targetWeekEnd = $targetWeekStart->copy()->addDays(6); // Sunday

        $deleted = DutyRoster::whereBetween('date', [
            $targetWeekStart->format('Y-m-d'),
            $targetWeekEnd->format('Y-m-d')
        ])->delete();

        return redirect()->route('duty-rosters.index', ['date' => $targetWeekStart->format('Y-m-d')])
                        ->with('success', "Week cleared: {$deleted} assignments removed");
    }
}
