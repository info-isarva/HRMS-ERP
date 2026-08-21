@extends('layouts.app')

@section('title', 'Edit Attendance Policy')

@section('page-title', 'Edit Policy: ' . $policy->name)

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50">
    <div class="max-w-full mx-auto p-6 space-y-6">

        <!-- Header -->
        <div class="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-2xl p-6 text-white mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-1">Edit Attendance Policy</h1>
                    <p class="text-indigo-100">{{ $policy->name }}</p>
                </div>
                <a href="{{ route('attendance-policies.index') }}" 
                   class="bg-white text-indigo-600 px-4 py-2 rounded-lg font-semibold hover:bg-indigo-50 transition-all duration-300">
                    <i class="fas fa-arrow-left mr-2"></i> Back to Policies
                </a>
            </div>
        </div>

        <form action="{{ route('attendance-policies.update', $policy->id) }}" method="POST">
            @csrf
            @method('PUT')

            <!-- Basic Information -->
            <div class="bg-white rounded-2xl shadow-lg p-6 mb-6">
                <h3 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-info-circle text-indigo-600 mr-2"></i>
                    Basic Information
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Policy Name</label>
                        <input type="text" name="name" value="{{ old('name', $policy->name) }}" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        @error('name')<span class="text-red-500 text-sm">{{ $message }}</span>@enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                        <textarea name="description" rows="2"
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">{{ old('description', $policy->description) }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Grace Periods & Time Tracking -->
            <div class="bg-white rounded-2xl shadow-lg p-6 mb-6">
                <h3 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-clock text-green-600 mr-2"></i>
                    Time Tracking Rules (in minutes)
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Late Arrival Grace</label>
                        <input type="number" name="late_arrival_grace_minutes" value="{{ old('late_arrival_grace_minutes', $policy->late_arrival_grace_minutes) }}" min="0" max="60" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        <p class="text-xs text-gray-500 mt-1">No penalty if late within this time</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Early Departure Grace</label>
                        <input type="number" name="early_departure_grace_minutes" value="{{ old('early_departure_grace_minutes', $policy->early_departure_grace_minutes) }}" min="0" max="60" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        <p class="text-xs text-gray-500 mt-1">No penalty if leaving within this time</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Early Arrival Buffer</label>
                        <input type="number" name="early_arrival_grace_minutes" value="{{ old('early_arrival_grace_minutes', $policy->early_arrival_grace_minutes) }}" min="0" max="120" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        <p class="text-xs text-gray-500 mt-1">How early employees can check-in</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Late Departure Buffer</label>
                        <input type="number" name="late_departure_grace_minutes" value="{{ old('late_departure_grace_minutes', $policy->late_departure_grace_minutes) }}" min="0" max="120" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        <p class="text-xs text-gray-500 mt-1">How late employees can check-out</p>
                    </div>
                </div>
            </div>

            <!-- Half Day & Absent Thresholds -->
            <div class="bg-white rounded-2xl shadow-lg p-6 mb-6">
                <h3 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-user-clock text-orange-600 mr-2"></i>
                    Attendance Status Thresholds
                </h3>
                <p class="text-sm text-gray-600 mb-4">Define when an employee is marked as Half Day or Absent based on late arrival or early departure</p>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Half Day - Late Threshold (min)</label>
                        <input type="number" name="half_day_late_threshold_minutes" value="{{ old('half_day_late_threshold_minutes', $policy->half_day_late_threshold_minutes) }}" min="0" max="480" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        <p class="text-xs text-gray-500 mt-1">e.g., 120 = 2 hours late → half day</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Half Day - Early Leave (min)</label>
                        <input type="number" name="half_day_early_departure_threshold_minutes" value="{{ old('half_day_early_departure_threshold_minutes', $policy->half_day_early_departure_threshold_minutes) }}" min="0" max="480" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        <p class="text-xs text-gray-500 mt-1">e.g., 120 = leave 2 hours early → half day</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Half Day Min Hours</label>
                        <input type="number" name="half_day_minimum_hours" value="{{ old('half_day_minimum_hours', $policy->half_day_minimum_hours) }}" min="1" max="12" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        <p class="text-xs text-gray-500 mt-1">Minimum hours to count as half day</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Absent Threshold (min)</label>
                        <input type="number" name="absent_threshold_minutes" value="{{ old('absent_threshold_minutes', $policy->absent_threshold_minutes) }}" min="0" max="720" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        <p class="text-xs text-gray-500 mt-1">e.g., 240 = 4 hours late → absent</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Min Work Hours (Present)</label>
                        <input type="number" name="minimum_work_hours_for_present" value="{{ old('minimum_work_hours_for_present', $policy->minimum_work_hours_for_present) }}" min="1" max="12" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        <p class="text-xs text-gray-500 mt-1">Minimum hours to mark as present</p>
                    </div>
                </div>
            </div>

            <!-- Hidden fields for removed features with default values -->
            <input type="hidden" name="enable_overtime" value="0">
            <input type="hidden" name="overtime_start_after_minutes" value="0">
            <input type="hidden" name="overtime_multiplier" value="1.5">
            <input type="hidden" name="maximum_overtime_hours_per_day" value="0">
            <input type="hidden" name="require_approval_for_overtime" value="0">
            <input type="hidden" name="weekend_overtime_applies" value="0">
            <input type="hidden" name="weekend_overtime_multiplier" value="2">
            <input type="hidden" name="holiday_overtime_multiplier" value="2">
            <input type="hidden" name="deduct_undertime_from_salary" value="0">
            <input type="hidden" name="allow_undertime_adjustment" value="0">
            <input type="hidden" name="round_check_in_time" value="0">
            <input type="hidden" name="check_in_rounding_minutes" value="5">
            <input type="hidden" name="round_check_out_time" value="0">
            <input type="hidden" name="check_out_rounding_minutes" value="5">
            <input type="hidden" name="track_consecutive_late" value="0">
            <input type="hidden" name="consecutive_late_limit" value="3">
            <input type="hidden" name="consecutive_late_action" value="warning">
            <input type="hidden" name="track_monthly_late_minutes" value="0">
            <input type="hidden" name="monthly_late_minutes_warning_threshold" value="60">
            <input type="hidden" name="monthly_late_minutes_penalty_threshold" value="120">
            <input type="hidden" name="monthly_late_penalty_type" value="warning">
            <input type="hidden" name="deduct_break_time" value="0">
            <input type="hidden" name="break_duration_minutes" value="0">
            <input type="hidden" name="allow_flexible_timing" value="0">
            <input type="hidden" name="flexible_buffer_minutes" value="0">
            <input type="hidden" name="notify_on_late_arrival" value="0">
            <input type="hidden" name="notify_on_early_departure" value="0">
            <input type="hidden" name="notify_manager_on_violation" value="0">

            <!-- Submit Buttons -->
            <div class="flex gap-4">
                <button type="submit" 
                        class="flex-1 bg-gradient-to-r from-indigo-600 to-purple-600 text-white px-6 py-3 rounded-xl font-semibold hover:from-indigo-700 hover:to-purple-700 transition-all duration-300">
                    <i class="fas fa-save mr-2"></i>
                    Save Policy Configuration
                </button>
                <a href="{{ route('attendance-policies.index') }}" 
                   class="px-6 py-3 bg-gray-300 text-gray-700 rounded-xl font-semibold hover:bg-gray-400 transition-all duration-300">
                    Cancel
                </a>
            </div>
        </form>

    </div>
</div>
@endsection
