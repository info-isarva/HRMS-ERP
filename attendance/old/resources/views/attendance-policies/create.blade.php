@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50">
    <div class="max-w-full mx-auto p-6 space-y-6">
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <!-- Header -->
            <div class="bg-gradient-to-r from-purple-600 to-indigo-600 px-6 py-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-2xl font-bold text-white">Create New Attendance Policy</h2>
                    <a href="{{ route('attendance-policies.index') }}" class="text-white hover:text-gray-200 transition-colors">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Policies
                    </a>
                </div>
            </div>

            <!-- Form -->
            <form action="{{ route('attendance-policies.store') }}" method="POST" class="p-6">
                @csrf

            <!-- Basic Information -->
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Basic Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Policy Name *</label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('name') border-red-500 @enderror">
                        @error('name')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                        <input type="text" name="description" id="description" value="{{ old('description') }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    </div>
                </div>
            </div>

            <!-- Grace Periods -->
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Grace Periods</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="late_arrival_grace_minutes" class="block text-sm font-medium text-gray-700 mb-2">Late Arrival Grace (minutes)</label>
                        <input type="number" name="late_arrival_grace_minutes" id="late_arrival_grace_minutes" value="{{ old('late_arrival_grace_minutes', 10) }}" min="0"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <p class="text-xs text-gray-500 mt-1">Grace period for late arrivals</p>
                    </div>
                    <div>
                        <label for="early_departure_grace_minutes" class="block text-sm font-medium text-gray-700 mb-2">Early Departure Grace (minutes)</label>
                        <input type="number" name="early_departure_grace_minutes" id="early_departure_grace_minutes" value="{{ old('early_departure_grace_minutes', 10) }}" min="0"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <p class="text-xs text-gray-500 mt-1">Grace period for early departures</p>
                    </div>
                    <div>
                        <label for="partial_day_grace_minutes" class="block text-sm font-medium text-gray-700 mb-2">Partial Day Grace (minutes)</label>
                        <input type="number" name="partial_day_grace_minutes" id="partial_day_grace_minutes" value="{{ old('partial_day_grace_minutes', 15) }}" min="0"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <p class="text-xs text-gray-500 mt-1">Grace period for partial day attendance</p>
                    </div>
                    <div>
                        <label for="grace_period_notification_minutes" class="block text-sm font-medium text-gray-700 mb-2">Grace Period Notification (minutes)</label>
                        <input type="number" name="grace_period_notification_minutes" id="grace_period_notification_minutes" value="{{ old('grace_period_notification_minutes', 5) }}" min="0"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <p class="text-xs text-gray-500 mt-1">When to send notification before grace period ends</p>
                    </div>
                </div>
            </div>

            <!-- Half Day & Absent Thresholds -->
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Half Day & Absent Thresholds</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="half_day_late_threshold_minutes" class="block text-sm font-medium text-gray-700 mb-2">Half Day Late Threshold (minutes)</label>
                        <input type="number" name="half_day_late_threshold_minutes" id="half_day_late_threshold_minutes" value="{{ old('half_day_late_threshold_minutes', 120) }}" min="0"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <p class="text-xs text-gray-500 mt-1">Minutes late before marking as half day</p>
                    </div>
                    <div>
                        <label for="half_day_early_threshold_minutes" class="block text-sm font-medium text-gray-700 mb-2">Half Day Early Threshold (minutes)</label>
                        <input type="number" name="half_day_early_threshold_minutes" id="half_day_early_threshold_minutes" value="{{ old('half_day_early_threshold_minutes', 120) }}" min="0"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <p class="text-xs text-gray-500 mt-1">Minutes early departure before marking as half day</p>
                    </div>
                    <div>
                        <label for="half_day_hours_threshold" class="block text-sm font-medium text-gray-700 mb-2">Half Day Hours Threshold</label>
                        <input type="number" name="half_day_hours_threshold" id="half_day_hours_threshold" value="{{ old('half_day_hours_threshold', 4) }}" min="0" step="0.5"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <p class="text-xs text-gray-500 mt-1">Minimum hours required for half day</p>
                    </div>
                    <div>
                        <label for="absent_threshold_minutes" class="block text-sm font-medium text-gray-700 mb-2">Absent Threshold (minutes)</label>
                        <input type="number" name="absent_threshold_minutes" id="absent_threshold_minutes" value="{{ old('absent_threshold_minutes', 240) }}" min="0"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <p class="text-xs text-gray-500 mt-1">Minutes late before marking as absent</p>
                    </div>
                    <div>
                        <label for="minimum_hours_full_day" class="block text-sm font-medium text-gray-700 mb-2">Minimum Hours for Full Day</label>
                        <input type="number" name="minimum_hours_full_day" id="minimum_hours_full_day" value="{{ old('minimum_hours_full_day', 8) }}" min="0" step="0.5"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <p class="text-xs text-gray-500 mt-1">Minimum hours required for full day attendance</p>
                    </div>
                </div>
            </div>

            <!-- Overtime Rules -->
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Overtime Rules</h3>
                <div class="mb-4">
                    <label class="flex items-center">
                        <input type="checkbox" name="enable_overtime" value="1" {{ old('enable_overtime', true) ? 'checked' : '' }}
                            class="rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                        <span class="ml-2 text-sm font-medium text-gray-700">Enable Overtime Calculation</span>
                    </label>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="overtime_start_after_minutes" class="block text-sm font-medium text-gray-700 mb-2">OT Start After (minutes)</label>
                        <input type="number" name="overtime_start_after_minutes" id="overtime_start_after_minutes" value="{{ old('overtime_start_after_minutes', 30) }}" min="0"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <p class="text-xs text-gray-500 mt-1">Minutes after shift end before OT starts</p>
                    </div>
                    <div>
                        <label for="overtime_multiplier" class="block text-sm font-medium text-gray-700 mb-2">OT Multiplier</label>
                        <input type="number" name="overtime_multiplier" id="overtime_multiplier" value="{{ old('overtime_multiplier', 1.5) }}" min="1" step="0.1"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <p class="text-xs text-gray-500 mt-1">Regular day OT multiplier (e.g., 1.5x)</p>
                    </div>
                    <div>
                        <label for="weekend_overtime_multiplier" class="block text-sm font-medium text-gray-700 mb-2">Weekend OT Multiplier</label>
                        <input type="number" name="weekend_overtime_multiplier" id="weekend_overtime_multiplier" value="{{ old('weekend_overtime_multiplier', 2.0) }}" min="1" step="0.1"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <p class="text-xs text-gray-500 mt-1">Weekend OT multiplier (e.g., 2.0x)</p>
                    </div>
                    <div>
                        <label for="holiday_overtime_multiplier" class="block text-sm font-medium text-gray-700 mb-2">Holiday OT Multiplier</label>
                        <input type="number" name="holiday_overtime_multiplier" id="holiday_overtime_multiplier" value="{{ old('holiday_overtime_multiplier', 2.5) }}" min="1" step="0.1"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <p class="text-xs text-gray-500 mt-1">Holiday OT multiplier (e.g., 2.5x)</p>
                    </div>
                    <div>
                        <label for="maximum_overtime_hours_per_day" class="block text-sm font-medium text-gray-700 mb-2">Max OT Hours Per Day</label>
                        <input type="number" name="maximum_overtime_hours_per_day" id="maximum_overtime_hours_per_day" value="{{ old('maximum_overtime_hours_per_day', 4) }}" min="0" step="0.5"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <p class="text-xs text-gray-500 mt-1">Maximum allowed OT hours per day</p>
                    </div>
                    <div>
                        <label for="maximum_overtime_hours_per_month" class="block text-sm font-medium text-gray-700 mb-2">Max OT Hours Per Month</label>
                        <input type="number" name="maximum_overtime_hours_per_month" id="maximum_overtime_hours_per_month" value="{{ old('maximum_overtime_hours_per_month', 60) }}" min="0" step="1"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <p class="text-xs text-gray-500 mt-1">Maximum allowed OT hours per month</p>
                    </div>
                </div>
                <div class="mt-4 space-y-2">
                    <label class="flex items-center">
                        <input type="checkbox" name="require_overtime_approval" value="1" {{ old('require_overtime_approval') ? 'checked' : '' }}
                            class="rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                        <span class="ml-2 text-sm font-medium text-gray-700">Require Overtime Approval</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" name="auto_approve_weekend_overtime" value="1" {{ old('auto_approve_weekend_overtime') ? 'checked' : '' }}
                            class="rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                        <span class="ml-2 text-sm font-medium text-gray-700">Auto-approve Weekend Overtime</span>
                    </label>
                </div>
            </div>

            <!-- Additional Settings -->
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Additional Settings</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="undertime_threshold_minutes" class="block text-sm font-medium text-gray-700 mb-2">Undertime Threshold (minutes)</label>
                        <input type="number" name="undertime_threshold_minutes" id="undertime_threshold_minutes" value="{{ old('undertime_threshold_minutes', 15) }}" min="0"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <p class="text-xs text-gray-500 mt-1">Threshold for tracking undertime</p>
                    </div>
                    <div>
                        <label for="round_check_in_time" class="block text-sm font-medium text-gray-700 mb-2">Round Check-in Time (minutes)</label>
                        <input type="number" name="round_check_in_time" id="round_check_in_time" value="{{ old('round_check_in_time', 0) }}" min="0"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <p class="text-xs text-gray-500 mt-1">Round check-in to nearest X minutes (0 = no rounding)</p>
                    </div>
                    <div>
                        <label for="round_check_out_time" class="block text-sm font-medium text-gray-700 mb-2">Round Check-out Time (minutes)</label>
                        <input type="number" name="round_check_out_time" id="round_check_out_time" value="{{ old('round_check_out_time', 0) }}" min="0"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <p class="text-xs text-gray-500 mt-1">Round check-out to nearest X minutes (0 = no rounding)</p>
                    </div>
                    <div>
                        <label for="consecutive_late_limit" class="block text-sm font-medium text-gray-700 mb-2">Consecutive Late Limit</label>
                        <input type="number" name="consecutive_late_limit" id="consecutive_late_limit" value="{{ old('consecutive_late_limit', 3) }}" min="0"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <p class="text-xs text-gray-500 mt-1">Alert after X consecutive late arrivals (0 = disabled)</p>
                    </div>
                    <div>
                        <label for="monthly_late_minutes_limit" class="block text-sm font-medium text-gray-700 mb-2">Monthly Late Minutes Limit</label>
                        <input type="number" name="monthly_late_minutes_limit" id="monthly_late_minutes_limit" value="{{ old('monthly_late_minutes_limit', 120) }}" min="0"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <p class="text-xs text-gray-500 mt-1">Total late minutes allowed per month (0 = unlimited)</p>
                    </div>
                    <div>
                        <label for="break_time_deduction_minutes" class="block text-sm font-medium text-gray-700 mb-2">Break Time Deduction (minutes)</label>
                        <input type="number" name="break_time_deduction_minutes" id="break_time_deduction_minutes" value="{{ old('break_time_deduction_minutes', 30) }}" min="0"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <p class="text-xs text-gray-500 mt-1">Minutes to deduct for break time</p>
                    </div>
                    <div>
                        <label for="flexible_timing_buffer_minutes" class="block text-sm font-medium text-gray-700 mb-2">Flexible Timing Buffer (minutes)</label>
                        <input type="number" name="flexible_timing_buffer_minutes" id="flexible_timing_buffer_minutes" value="{{ old('flexible_timing_buffer_minutes', 0) }}" min="0"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <p class="text-xs text-gray-500 mt-1">Buffer time for flexible timing (0 = disabled)</p>
                    </div>
                </div>
                <div class="mt-4 space-y-2">
                    <label class="flex items-center">
                        <input type="checkbox" name="track_consecutive_late" value="1" {{ old('track_consecutive_late', true) ? 'checked' : '' }}
                            class="rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                        <span class="ml-2 text-sm font-medium text-gray-700">Track Consecutive Late Arrivals</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" name="track_monthly_late_minutes" value="1" {{ old('track_monthly_late_minutes', true) ? 'checked' : '' }}
                            class="rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                        <span class="ml-2 text-sm font-medium text-gray-700">Track Monthly Late Minutes Cumulative</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" name="deduct_break_time" value="1" {{ old('deduct_break_time') ? 'checked' : '' }}
                            class="rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                        <span class="ml-2 text-sm font-medium text-gray-700">Deduct Break Time from Total Hours</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" name="allow_flexible_timing" value="1" {{ old('allow_flexible_timing') ? 'checked' : '' }}
                            class="rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                        <span class="ml-2 text-sm font-medium text-gray-700">Allow Flexible Timing</span>
                    </label>
                </div>
            </div>

            <!-- Notification Settings -->
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Notification Settings</h3>
                <div class="space-y-2">
                    <label class="flex items-center">
                        <input type="checkbox" name="notify_on_late_arrival" value="1" {{ old('notify_on_late_arrival', true) ? 'checked' : '' }}
                            class="rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                        <span class="ml-2 text-sm font-medium text-gray-700">Notify on Late Arrival</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" name="notify_on_early_departure" value="1" {{ old('notify_on_early_departure', true) ? 'checked' : '' }}
                            class="rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                        <span class="ml-2 text-sm font-medium text-gray-700">Notify on Early Departure</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" name="notify_on_overtime" value="1" {{ old('notify_on_overtime') ? 'checked' : '' }}
                            class="rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                        <span class="ml-2 text-sm font-medium text-gray-700">Notify on Overtime</span>
                    </label>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-end space-x-4 pt-6 border-t">
                <a href="{{ route('attendance-policies.index') }}" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2 bg-gradient-to-r from-purple-600 to-indigo-600 text-white rounded-lg hover:from-purple-700 hover:to-indigo-700 transition-colors">
                    <i class="fas fa-save mr-2"></i>Create Policy
                </button>
            </div>
        </form>
    </div>
    </div>
</div>
@endsection
