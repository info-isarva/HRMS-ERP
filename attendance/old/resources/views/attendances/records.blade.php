@extends('layouts.app')

@section('title', 'Attendance Records')

@section('page-title', 'Attendance Records')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50">
    <div class="max-w-full mx-auto p-6 space-y-6">

        <!-- Header -->
        <div class="mb-8">
            <div class="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-2xl p-8 text-white relative overflow-hidden">
                <div class="absolute inset-0 opacity-10">
                    <div class="absolute -top-4 -right-4 w-24 h-24 bg-white rounded-full"></div>
                    <div class="absolute top-10 -right-8 w-16 h-16 bg-white rounded-full"></div>
                    <div class="absolute -bottom-6 -left-6 w-20 h-20 bg-white rounded-full"></div>
                </div>
                <div class="relative">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-3xl font-bold mb-2 flex items-center">
                                <i class="fas fa-list mr-3"></i>
                                Attendance Records
                            </h1>
                            <p class="text-indigo-100 text-lg">
                                View and manage biometric attendance data
                            </p>
                        </div>
                        <div class="flex items-center space-x-3">
                            <a href="{{ route('attendance.export') . '?' . request()->getQueryString() }}"
                               class="bg-white bg-opacity-20 hover:bg-opacity-30 text-white font-semibold py-2 px-4 rounded-xl transition-all duration-300 flex items-center">
                                <i class="fas fa-download mr-2"></i>
                                Export
                            </a>
                            <div class="hidden lg:block">
                                <div class="w-20 h-20 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                                    <i class="fas fa-list text-2xl text-white"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Success/Error Messages -->
        @if(session('success'))
            <div class="bg-green-50 border border-green-200 rounded-xl p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-check-circle text-green-400 text-xl"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-circle text-red-400 text-xl"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                    </div>
                </div>
            </div>
        @endif

        @if(session('warning'))
            <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-triangle text-yellow-400 text-xl"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-yellow-800">{{ session('warning') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Statistics Overview -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
            <div class="bg-white/80 backdrop-blur-sm rounded-xl p-4 text-center">
                <div class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_records']) }}</div>
                <div class="text-sm text-gray-600">Total Records</div>
            </div>
            <div class="bg-white/80 backdrop-blur-sm rounded-xl p-4 text-center">
                <div class="text-2xl font-bold text-green-600">{{ number_format($stats['present_today']) }}</div>
                <div class="text-sm text-gray-600">Present Today</div>
            </div>
            <div class="bg-white/80 backdrop-blur-sm rounded-xl p-4 text-center">
                <div class="text-2xl font-bold text-yellow-600">{{ number_format($stats['late_today']) }}</div>
                <div class="text-sm text-gray-600">Late Today</div>
            </div>
            <div class="bg-white/80 backdrop-blur-sm rounded-xl p-4 text-center">
                <div class="text-2xl font-bold text-red-600">{{ number_format($stats['absent_today']) }}</div>
                <div class="text-sm text-gray-600">Absent Today</div>
            </div>
            <div class="bg-white/80 backdrop-blur-sm rounded-xl p-4 text-center">
                <div class="text-2xl font-bold text-blue-600">{{ $stats['by_source']['biometric_excel'] ?? 0 }}</div>
                <div class="text-sm text-gray-600">From Excel</div>
            </div>
        </div>

        <!-- Filters and Actions -->
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Filters & Search</h3>
                <div class="flex items-center space-x-2">
                    <button onclick="clearFilters()" class="text-gray-600 hover:text-gray-800 text-sm font-medium">
                        <i class="fas fa-times mr-1"></i>Clear All
                    </button>
                    <button onclick="toggleFilters()" class="lg:hidden text-gray-600 hover:text-gray-800">
                        <i class="fas fa-filter"></i>
                    </button>
                </div>
            </div>

            <form method="GET" action="{{ route('attendance.records') }}" id="filterForm" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4">
                    <!-- Employee Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Employee</label>
                        <select name="employee_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                            <option value="" {{ !request()->filled('employee_id') ? 'selected' : '' }}>All Employees</option>
                            @foreach($employees as $employee)
                            <option value="{{ $employee->payroll_id }}"
                                    {{ request()->filled('employee_id') && (string) request('employee_id') === (string) $employee->payroll_id ? 'selected' : '' }}>
                                {{ $employee->payroll_id }} - {{ $employee->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Date From -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Date From</label>
                        <input type="date" name="date_from" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                               value="{{ request('date_from') }}">
                    </div>

                    <!-- Date To -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Date To</label>
                        <input type="date" name="date_to" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                               value="{{ request('date_to') }}">
                    </div>

                    <!-- Status Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                            <option value="">All Status</option>
                            <option value="present" {{ request('status') == 'present' ? 'selected' : '' }}>Present</option>
                            <option value="absent" {{ request('status') == 'absent' ? 'selected' : '' }}>Absent</option>
                            <option value="late" {{ request('status') == 'late' ? 'selected' : '' }}>Late</option>
                            <option value="early_departure" {{ request('status') == 'early_departure' ? 'selected' : '' }}>Early Departure</option>
                            <option value="half_day" {{ request('status') == 'half_day' ? 'selected' : '' }}>Half Day</option>
                            <option value="overtime" {{ request('status') == 'overtime' ? 'selected' : '' }}>Overtime</option>
                        </select>
                    </div>

                    <!-- Source Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Source</label>
                        <select name="source" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                            <option value="">All Sources</option>
                            <option value="biometric_excel" {{ request('source') == 'biometric_excel' ? 'selected' : '' }}>Biometric Excel</option>
                            <option value="biometric_device" {{ request('source') == 'biometric_device' ? 'selected' : '' }}>Biometric Device</option>
                            <option value="manual" {{ request('source') == 'manual' ? 'selected' : '' }}>Manual</option>
                        </select>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-end space-x-2">
                        <button type="submit" class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold py-2 px-4 rounded-lg hover:from-indigo-700 hover:to-purple-700 transition-all duration-300">
                            <i class="fas fa-search mr-1"></i>
                            Filter
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Records Table -->
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">Attendance Records</h3>
                    <div class="flex items-center space-x-2">
                        <button onclick="toggleBulkDelete()" class="bg-red-500 hover:bg-red-600 text-white text-sm font-medium py-2 px-3 rounded-lg transition-colors duration-300">
                            <i class="fas fa-trash mr-1"></i>
                            Bulk Delete
                        </button>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Check In</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Check Out</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Hours</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Scheduled</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Late/Early</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">OT/UT</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Shift</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Source</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Processed</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($attendances as $attendance)
                        <tr class="hover:bg-gray-50 transition-colors duration-200">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center">
                                            <span class="text-white font-medium text-sm">
                                                {{ strtoupper(substr($attendance->employee->name ?? 'N/A', 0, 2)) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $attendance->employee_payroll_id }}</div>
                                        <div class="text-sm text-gray-500">{{ $attendance->employee->name ?? 'N/A' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $aDate = $attendance->date;
                                    $formattedDate = '-';
                                    $formattedDay = '-';

                                    if ($aDate) {
                                        if ($aDate instanceof \Illuminate\Support\Carbon) {
                                            $formattedDate = $aDate->format('M d, Y');
                                            $formattedDay = $aDate->format('l');
                                        } elseif (is_string($aDate)) {
                                            try {
                                                $carbonDate = \Illuminate\Support\Carbon::parse($aDate);
                                                $formattedDate = $carbonDate->format('M d, Y');
                                                $formattedDay = $carbonDate->format('l');
                                            } catch (\Exception $e) {
                                                $formattedDate = $aDate;
                                            }
                                        }
                                    }
                                @endphp
                                <div class="text-sm text-gray-900">{{ $formattedDate }}</div>
                                <div class="text-sm text-gray-500">{{ $formattedDay }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                @php
                                    $ci = $attendance->check_in_time;
                                    $formattedCheckIn = '-';

                                    if ($ci) {
                                        if (is_string($ci)) {
                                            // Extract H:i from time string (e.g., "09:28:00" -> "09:28")
                                            $formattedCheckIn = strlen($ci) >= 5 ? substr($ci, 0, 5) : $ci;
                                        } elseif ($ci instanceof \Illuminate\Support\Carbon) {
                                            $formattedCheckIn = $ci->format('H:i');
                                        } else {
                                            $formattedCheckIn = (string)$ci;
                                        }
                                    }
                                @endphp
                                {{ $formattedCheckIn }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                @php
                                    $co = $attendance->check_out_time;
                                    $formattedCheckOut = '-';

                                    if ($co) {
                                        if (is_string($co)) {
                                            // Extract H:i from time string (e.g., "18:40:18" -> "18:40")
                                            $formattedCheckOut = strlen($co) >= 5 ? substr($co, 0, 5) : $co;
                                        } elseif ($co instanceof \Illuminate\Support\Carbon) {
                                            $formattedCheckOut = $co->format('H:i');
                                        } else {
                                            $formattedCheckOut = (string)$co;
                                        }
                                    }
                                @endphp
                                {{ $formattedCheckOut }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($attendance->total_hours)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ number_format($attendance->total_hours, 2) }}h
                                    </span>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                @if($attendance->scheduled_start_time && $attendance->scheduled_end_time)
                                    {{ substr($attendance->scheduled_start_time, 0, 5) }} - {{ substr($attendance->scheduled_end_time, 0, 5) }}
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                @php
                                    $late = $attendance->late_arrival_minutes ?? 0;
                                    $early = $attendance->early_departure_minutes ?? 0;
                                @endphp
                                @if($late > 0)
                                    <span class="text-red-600 font-medium">{{ $late }}m late</span>
                                @elseif($early > 0)
                                    <span class="text-orange-600 font-medium">{{ $early }}m early</span>
                                @else
                                    <span class="text-green-600">On time</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                @php
                                    $ot = $attendance->overtime_hours ?? 0;
                                    $ut = $attendance->undertime_hours ?? 0;
                                @endphp
                                @if($ot > 0)
                                    <span class="text-purple-600 font-medium">+{{ number_format($ot, 2) }}h OT</span>
                                @elseif($ut > 0)
                                    <span class="text-blue-600 font-medium">-{{ number_format($ut, 2) }}h UT</span>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @switch($attendance->status)
                                    @case('present')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-check-circle mr-1"></i>Present
                                        </span>
                                        @break
                                    @case('absent')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            <i class="fas fa-times-circle mr-1"></i>Absent
                                        </span>
                                        @break
                                    @case('late')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            <i class="fas fa-clock mr-1"></i>Late
                                        </span>
                                        @break
                                    @case('early_departure')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                            <i class="fas fa-sign-out-alt mr-1"></i>Early Departure
                                        </span>
                                        @break
                                    @case('half_day')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            <i class="fas fa-adjust mr-1"></i>Half Day
                                        </span>
                                        @break
                                    @case('overtime')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                            <i class="fas fa-plus-circle mr-1"></i>Overtime
                                        </span>
                                        @break
                                    @default
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            {{ ucfirst(str_replace('_', ' ', $attendance->status)) }}
                                        </span>
                                @endswitch
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $attendance->shift->name ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                    {{ ucfirst(str_replace('_', ' ', $attendance->source)) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $attendance->processed_at ? $attendance->processed_at->diffForHumans() : '-' }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="12" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <i class="fas fa-inbox text-4xl text-gray-400 mb-4"></i>
                                    <h3 class="text-lg font-medium text-gray-900 mb-2">No attendance records found</h3>
                                    <p class="text-gray-500">Try adjusting your filters or upload some data first.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($attendances->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                {{ $attendances->appends(request()->query())->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Bulk Delete Modal -->
<div id="bulkDeleteModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full">
        <div class="p-6">
            <div class="flex items-center mb-4">
                <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center mr-3">
                    <i class="fas fa-exclamation-triangle text-red-600"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900">Bulk Delete Records</h3>
            </div>

            <div class="mb-6">
                <p class="text-gray-600 text-sm mb-4">
                    This action cannot be undone. All attendance records within the specified date range will be permanently deleted.
                </p>

                <form action="{{ route('attendance.bulk-delete') }}" method="POST">
                    @csrf
                    @method('DELETE')

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Date From</label>
                            <input type="date" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500" name="date_from" required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Date To</label>
                            <input type="date" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500" name="date_to" required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Source (Optional)</label>
                            <select class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500" name="source">
                                <option value="">All Sources</option>
                                <option value="biometric_excel">Biometric Excel Only</option>
                                <option value="biometric_device">Biometric Device Only</option>
                                <option value="manual">Manual Only</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex items-center justify-end space-x-3 mt-6">
                        <button type="button" onclick="toggleBulkDelete()" class="px-4 py-2 text-gray-600 hover:text-gray-800 font-medium">
                            Cancel
                        </button>
                        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-300">
                            <i class="fas fa-trash mr-2"></i>
                            Delete Records
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Filter functions
function clearFilters() {
    document.querySelectorAll('input, select').forEach(element => {
        if (element.name) {
            element.value = '';
        }
    });
    document.getElementById('filterForm').submit();
}

function toggleFilters() {
    // Mobile filter toggle functionality can be added here
}

// Bulk delete modal
function toggleBulkDelete() {
    const modal = document.getElementById('bulkDeleteModal');
    modal.classList.toggle('hidden');
}

// Auto-submit on filter change (with debounce)
let filterTimeout;
document.querySelectorAll('select[name="employee_id"], select[name="status"], select[name="source"]').forEach(select => {
    select.addEventListener('change', function() {
        clearTimeout(filterTimeout);
        filterTimeout = setTimeout(() => {
            document.getElementById('filterForm').submit();
        }, 500);
    });
});

// Close modal when clicking outside
document.getElementById('bulkDeleteModal').addEventListener('click', function(e) {
    if (e.target === this) {
        toggleBulkDelete();
    }
});
</script>
@endpush
@endsection