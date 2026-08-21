<?php

namespace App\Http\Controllers;

use App\Models\AttendancePolicy;
use Illuminate\Http\Request;

class AttendancePolicyController extends Controller
{
    /**
     * Display all attendance policies
     */
    public function index()
    {
        $policies = AttendancePolicy::orderBy('is_active', 'desc')
                                     ->orderBy('created_at', 'desc')
                                     ->get();
        
        $activePolicy = AttendancePolicy::getActivePolicy();
        
        return view('attendance-policies.index', compact('policies', 'activePolicy'));
    }

    /**
     * Show the form for editing a policy
     */
    public function edit($id)
    {
        $policy = AttendancePolicy::findOrFail($id);
        return view('attendance-policies.edit', compact('policy'));
    }

    /**
     * Update the specified policy
     */
    public function update(Request $request, $id)
    {
        $policy = AttendancePolicy::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'late_arrival_grace_minutes' => 'required|integer|min:0|max:60',
            'early_departure_grace_minutes' => 'required|integer|min:0|max:60',
            'early_arrival_grace_minutes' => 'required|integer|min:0|max:120',
            'late_departure_grace_minutes' => 'required|integer|min:0|max:120',
            'half_day_late_threshold_minutes' => 'required|integer|min:0|max:480',
            'half_day_early_departure_threshold_minutes' => 'required|integer|min:0|max:480',
            'half_day_minimum_hours' => 'required|integer|min:1|max:12',
            'absent_threshold_minutes' => 'required|integer|min:0|max:720',
            'minimum_work_hours_for_present' => 'required|integer|min:1|max:12',
            'enable_overtime' => 'boolean',
            'overtime_start_after_minutes' => 'required|integer|min:0|max:120',
            'overtime_multiplier' => 'required|numeric|min:1|max:5',
            'maximum_overtime_hours_per_day' => 'required|integer|min:0|max:12',
            'require_approval_for_overtime' => 'boolean',
            'deduct_undertime_from_salary' => 'boolean',
            'allow_undertime_adjustment' => 'boolean',
            'weekend_overtime_applies' => 'boolean',
            'weekend_overtime_multiplier' => 'required|numeric|min:1|max:5',
            'holiday_overtime_multiplier' => 'required|numeric|min:1|max:5',
            'round_check_in_time' => 'boolean',
            'check_in_rounding_minutes' => 'required|integer|min:1|max:60',
            'round_check_out_time' => 'boolean',
            'check_out_rounding_minutes' => 'required|integer|min:1|max:60',
            'track_consecutive_late' => 'boolean',
            'consecutive_late_limit' => 'required|integer|min:1|max:10',
            'consecutive_late_action' => 'required|in:warning,half_day,absent',
            'track_monthly_late_minutes' => 'boolean',
            'monthly_late_minutes_warning_threshold' => 'required|integer|min:0|max:300',
            'monthly_late_minutes_penalty_threshold' => 'required|integer|min:0|max:600',
            'monthly_late_penalty_type' => 'required|in:warning,half_day,full_day',
            'deduct_break_time' => 'boolean',
            'break_duration_minutes' => 'required|integer|min:0|max:180',
            'allow_flexible_timing' => 'boolean',
            'flexible_buffer_minutes' => 'required|integer|min:0|max:240',
            'notify_on_late_arrival' => 'boolean',
            'notify_on_early_departure' => 'boolean',
            'notify_manager_on_violation' => 'boolean',
        ]);

        // Handle checkboxes that might not be present in request
        $validated['enable_overtime'] = $request->has('enable_overtime');
        $validated['require_approval_for_overtime'] = $request->has('require_approval_for_overtime');
        $validated['deduct_undertime_from_salary'] = $request->has('deduct_undertime_from_salary');
        $validated['allow_undertime_adjustment'] = $request->has('allow_undertime_adjustment');
        $validated['weekend_overtime_applies'] = $request->has('weekend_overtime_applies');
        $validated['round_check_in_time'] = $request->has('round_check_in_time');
        $validated['round_check_out_time'] = $request->has('round_check_out_time');
        $validated['track_consecutive_late'] = $request->has('track_consecutive_late');
        $validated['track_monthly_late_minutes'] = $request->has('track_monthly_late_minutes');
        $validated['deduct_break_time'] = $request->has('deduct_break_time');
        $validated['allow_flexible_timing'] = $request->has('allow_flexible_timing');
        $validated['notify_on_late_arrival'] = $request->has('notify_on_late_arrival');
        $validated['notify_on_early_departure'] = $request->has('notify_on_early_departure');
        $validated['notify_manager_on_violation'] = $request->has('notify_manager_on_violation');

        $policy->update($validated);

        return redirect()->route('attendance-policies.index')
                        ->with('success', 'Attendance policy updated successfully!');
    }

    /**
     * Activate a specific policy
     */
    public function activate($id)
    {
        // Deactivate all policies
        AttendancePolicy::query()->update(['is_active' => false]);

        // Activate the selected policy
        $policy = AttendancePolicy::findOrFail($id);
        $policy->update(['is_active' => true]);

        return redirect()->route('attendance-policies.index')
                        ->with('success', "Policy '{$policy->name}' has been activated!");
    }

    /**
     * Create a new policy
     */
    public function create()
    {
        return view('attendance-policies.create');
    }

    /**
     * Store a new policy
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'late_arrival_grace_minutes' => 'required|integer|min:0|max:60',
            'early_departure_grace_minutes' => 'required|integer|min:0|max:60',
            // ... all other fields same as update validation
        ]);

        // Handle checkboxes
        $validated['enable_overtime'] = $request->has('enable_overtime');
        $validated['is_active'] = false; // New policies are inactive by default

        AttendancePolicy::create($validated);

        return redirect()->route('attendance-policies.index')
                        ->with('success', 'New attendance policy created successfully!');
    }

    /**
     * Delete a policy
     */
    public function destroy($id)
    {
        $policy = AttendancePolicy::findOrFail($id);

        if ($policy->is_active) {
            return redirect()->route('attendance-policies.index')
                           ->with('error', 'Cannot delete an active policy. Please activate another policy first.');
        }

        $policy->delete();

        return redirect()->route('attendance-policies.index')
                        ->with('success', 'Policy deleted successfully!');
    }
}
