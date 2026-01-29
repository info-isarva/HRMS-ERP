@extends('layouts.app')

@section('title', 'Shift Details - HRMS')

@section('page-title', 'Shift Details')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50">
    <div class="mx-auto p-6 space-y-6">
        <!-- Header card (gradient) -->
        <div class="bg-white/80 backdrop-blur-sm overflow-hidden shadow-xl rounded-2xl border border-white/20">
            <div class="bg-gradient-to-r from-indigo-600 via-purple-600 to-blue-600 px-8 py-12 relative overflow-hidden">
                <!-- Background Pattern -->
                <div class="absolute inset-0 bg-black/10"></div>
                <div class="absolute -top-4 -right-4 w-24 h-24 bg-white/10 rounded-full"></div>
                <div class="absolute top-10 -right-8 w-16 h-16 bg-white/10 rounded-full"></div>
                <div class="absolute -bottom-6 -left-6 w-20 h-20 bg-white/10 rounded-full"></div>

                <div class="relative flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-16 h-16 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center border border-white/30">
                                <i class="fas fa-clock text-white text-2xl"></i>
                            </div>
                        </div>
                        <div class="ml-6">
                            <h1 class="text-3xl font-bold text-white mb-2">{{ $shift->name }}</h1>
                            <p class="text-indigo-100 text-lg">
                                Shift details and assigned duty rosters
                            </p>
                        </div>
                    </div>

                    <div class="hidden md:flex items-center space-x-3">
                        <a href="{{ route('shifts.edit', $shift) }}" class="inline-flex items-center px-6 py-3 bg-white/20 backdrop-blur-sm text-white font-semibold rounded-xl shadow-lg hover:bg-white/30 transition-all duration-300 border border-white/30">
                            <i class="fas fa-edit mr-2"></i> Edit Shift
                        </a>
                        <a href="{{ route('shifts.index') }}" class="inline-flex items-center px-6 py-3 bg-white/20 backdrop-blur-sm text-white font-semibold rounded-xl shadow-lg hover:bg-white/30 transition-all duration-300 border border-white/30">
                            <i class="fas fa-arrow-left mr-2"></i> Back to Shifts
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Shift Information Cards -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Basic Information -->
            <div class="bg-white/80 backdrop-blur-sm shadow-xl rounded-2xl border border-white/20 p-8">
                <div class="flex items-center space-x-4 mb-6">
                    <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center">
                        <i class="fas fa-info-circle text-white"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">Basic Information</h2>
                        <p class="text-gray-600">Core details of this shift</p>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="p-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl border border-blue-100">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-tag text-blue-600"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-600">Shift Name</p>
                                    <p class="text-lg font-bold text-gray-900">{{ $shift->name }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="p-4 bg-gradient-to-r from-purple-50 to-pink-50 rounded-xl border border-purple-100">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-calendar-alt text-purple-600"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-600">Time Range</p>
                                    <p class="text-lg font-bold text-gray-900">{{ $shift->start_time }} - {{ $shift->end_time }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="p-4 bg-gradient-to-r from-orange-50 to-yellow-50 rounded-xl border border-orange-100">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-clock text-orange-600"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-600">Total Duration</p>
                                    <p class="text-lg font-bold text-gray-900" id="total-duration">Calculating...</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($shift->description)
                        <div class="p-4 bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl border border-green-100">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-file-alt text-green-600"></i>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-600">Description</p>
                                    <p class="text-gray-900 mt-1">{{ $shift->description }}</p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Shift Statistics -->
            <div class="bg-white/80 backdrop-blur-sm shadow-xl rounded-2xl border border-white/20 p-8">
                <div class="flex items-center space-x-4 mb-6">
                    <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center">
                        <i class="fas fa-chart-bar text-white"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">Shift Statistics</h2>
                        <p class="text-gray-600">Current usage and metrics</p>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="p-4 bg-gradient-to-r from-emerald-50 to-green-50 rounded-xl border border-emerald-100">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-emerald-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-users text-emerald-600"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-600">Active Duty Rosters</p>
                                    <p class="text-2xl font-bold text-emerald-600">{{ $shift->dutyRosters->count() }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-emerald-100 text-emerald-800">
                                    Active
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="p-4 bg-gradient-to-r from-indigo-50 to-blue-50 rounded-xl border border-indigo-100">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-calendar-check text-indigo-600"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-600">Status</p>
                                    <p class="text-lg font-bold text-indigo-600">Operational</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Duty Rosters Section -->
        <div class="bg-white/80 backdrop-blur-sm shadow-xl rounded-2xl border border-white/20 overflow-hidden">
            <div class="px-8 py-6 border-b border-gray-200/50 bg-gradient-to-r from-gray-50/50 to-blue-50/30 flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-pink-600 rounded-xl flex items-center justify-center">
                        <i class="fas fa-users-cog text-white"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">Duty Rosters</h2>
                        <p class="text-gray-600">Employees assigned to this shift</p>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <span class="inline-flex items-center px-4 py-2 rounded-xl text-sm font-semibold bg-purple-100 text-purple-800">
                        {{ $shift->dutyRosters->count() }} Assigned
                    </span>
                    <a href="{{ route('duty-rosters.create') }}" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 text-white font-semibold rounded-xl shadow-lg hover:from-purple-700 hover:to-pink-700 transition-all duration-300 transform hover:scale-105">
                        <i class="fas fa-plus mr-2"></i> Add Duty Roster
                    </a>
                </div>
            </div>

            <div class="p-8">
                @if($shift->dutyRosters->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200/50">
                            <thead class="bg-gray-50/50">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Employee</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white/50 divide-y divide-gray-200/30">
                                @foreach($shift->dutyRosters as $roster)
                                    <tr class="hover:bg-gray-50/50 transition-colors duration-200">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-lg flex items-center justify-center">
                                                    <span class="text-white font-bold text-sm">{{ strtoupper(substr($roster->employee->name ?? 'N', 0, 1)) }}</span>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-semibold text-gray-900">{{ $roster->employee->name ?? 'Unknown' }}</div>
                                                    <div class="text-sm text-gray-500">{{ $roster->employee_payroll_id }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                                                    <i class="fas fa-calendar-day text-green-600 text-xs"></i>
                                                </div>
                                                <div class="text-sm text-gray-900 font-medium">{{ \Carbon\Carbon::parse($roster->date)->format('M d, Y') }}</div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex items-center space-x-3">
                                                <a href="{{ route('duty-rosters.edit', $roster) }}" class="flex items-center justify-center w-8 h-8 bg-blue-100 hover:bg-blue-200 text-blue-600 rounded-lg transition-all duration-200 hover:scale-110" title="Edit">
                                                    <i class="fas fa-edit text-xs"></i>
                                                </a>
                                                <form action="{{ route('duty-rosters.destroy', $roster) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this duty roster?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="flex items-center justify-center w-8 h-8 bg-red-100 hover:bg-red-200 text-red-600 rounded-lg transition-all duration-200 hover:scale-110" title="Delete">
                                                        <i class="fas fa-trash text-xs"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-12">
                        <div class="w-24 h-24 bg-gradient-to-br from-gray-100 to-gray-200 rounded-full flex items-center justify-center mx-auto mb-6">
                            <i class="fas fa-users text-gray-400 text-3xl"></i>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-2">No Duty Rosters</h3>
                        <p class="text-gray-500 mb-8 text-lg">No employees are assigned to this shift yet.</p>
                        <a href="{{ route('duty-rosters.create') }}" class="inline-flex items-center px-8 py-4 bg-gradient-to-r from-purple-600 to-pink-600 text-white font-semibold rounded-xl shadow-lg hover:from-purple-700 hover:to-pink-700 transition-all duration-300 transform hover:scale-105">
                            <i class="fas fa-plus mr-3"></i> Add First Duty Roster
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Calculate and display total duration
    const startTime = '{{ $shift->start_time }}';
    const endTime = '{{ $shift->end_time }}';
    const durationElement = document.getElementById('total-duration');

    if (startTime && endTime) {
        const duration = calculateDuration(startTime, endTime);
        durationElement.textContent = duration;
    } else {
        durationElement.textContent = 'N/A';
    }
});

function calculateDuration(startTime, endTime) {
    // Parse times (assuming HH:MM format)
    const [startHour, startMinute] = startTime.split(':').map(Number);
    const [endHour, endMinute] = endTime.split(':').map(Number);

    // Convert to minutes since midnight
    const startMinutes = startHour * 60 + startMinute;
    let endMinutes = endHour * 60 + endMinute;

    // Handle overnight shifts
    if (endMinutes < startMinutes) {
        endMinutes += 24 * 60; // Add 24 hours
    }

    const durationMinutes = endMinutes - startMinutes;
    const hours = Math.floor(durationMinutes / 60);
    const minutes = durationMinutes % 60;

    if (hours === 0) {
        return `${minutes}mins`;
    } else if (minutes === 0) {
        return `${hours}hrs`;
    } else {
        return `${hours}hrs ${minutes}mins`;
    }
}
</script>
@endsection