@extends('layouts.app')

@section('title', 'Duty Roster - HRMS')

@section('page-title', 'Duty Roster')

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
                            <i class="fas fa-calendar-alt mr-3"></i>
                            Duty Roster
                        </h1>
                        <p class="text-indigo-100 text-lg">
                            Manage employee shift assignments
                        </p>
                    </div>
                    <div class="hidden lg:block">
                        <div class="w-32 h-32 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                            <i class="fas fa-calendar-alt text-4xl text-white"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 rounded-md p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-check-circle text-green-400"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 rounded-md p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-circle text-red-400"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800">There were errors:</h3>
                    <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <!-- Filters and Actions -->
    <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-6 mb-8">
        <div class="space-y-6">
            <!-- Filter Options -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                <!-- Date Range -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Date Range</label>
                    <div class="flex items-center space-x-2">
                        <input type="date" id="startDate" name="start_date"
                               class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                               onchange="applyFilters()">
                        <span class="text-gray-500">to</span>
                        <input type="date" id="endDate" name="end_date"
                               class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                               onchange="applyFilters()">
                    </div>
                </div>

                <!-- Employee Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Employee</label>
                    <select name="employee_payroll_id" id="employee_payroll_id"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                            onchange="applyFilters()">
                        <option value="">All Employees</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->payroll_id }}" {{ request('employee_payroll_id') == $employee->payroll_id ? 'selected' : '' }}>
                                {{ $employee->name }} ({{ $employee->payroll_id }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Quick Filters -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Quick Filters</label>
                    <div class="flex flex-wrap gap-2">
                        <button type="button" onclick="setToday()" class="px-4 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors duration-200">Today</button>
                        <button type="button" onclick="setThisWeek()" class="px-4 py-2 text-sm bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors duration-200">This Week</button>
                        <button type="button" onclick="setThisMonth()" class="px-4 py-2 text-sm bg-purple-600 hover:bg-purple-700 text-white font-medium rounded-lg transition-colors duration-200">This Month</button>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-4 sm:space-y-0">
                <div class="flex items-center space-x-4">
                    <button type="button" onclick="clearFilters()" class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-lg shadow transition">
                        <i class="fas fa-times mr-2"></i>
                        Clear Filters
                    </button>
                </div>

                <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-4">
                    <!-- Clear Current Week -->
                    <form method="POST" action="{{ route('duty-rosters.clear-week') }}" class="inline">
                        @csrf
                        <input type="hidden" name="target_week" value="{{ request('date', date('Y-m-d')) }}">
                        <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-red-600 to-red-700 text-white font-semibold rounded-lg shadow hover:from-red-700 hover:to-red-800 transition"
                                onclick="return confirm('This will clear all roster assignments for the current week. Continue?')">
                            <i class="fas fa-trash mr-2"></i>
                            Clear Week
                        </button>
                    </form>

                    <!-- Copy Previous Week -->
                    <form method="POST" action="{{ route('duty-rosters.copy-week') }}" class="inline">
                        @csrf
                        <input type="hidden" name="target_week" value="{{ request('date', date('Y-m-d')) }}">
                        <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold rounded-lg shadow hover:from-blue-700 hover:to-blue-800 transition"
                                onclick="return confirm('This will copy last week\'s roster to the current week. Continue?')">
                            <i class="fas fa-copy mr-2"></i>
                            Copy Last Week
                        </button>
                    </form>

                    <!-- Bulk Assignment -->
                    <a href="{{ route('duty-rosters.bulk-create') }}"
                       class="inline-flex items-center px-6 py-2 bg-gradient-to-r from-green-600 to-emerald-600 text-white font-semibold rounded-lg shadow hover:from-green-700 hover:to-emerald-700 transition">
                        <i class="fas fa-users mr-2"></i>
                        Bulk Assign
                    </a>

                    <!-- Add Single Duty Roster -->
                    <a href="{{ route('duty-rosters.create') }}"
                       class="inline-flex items-center px-6 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold rounded-lg shadow hover:from-indigo-700 hover:to-purple-700 transition">
                        <i class="fas fa-plus mr-2"></i>
                        Add Single
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Duty Rosters List -->
    @if($dutyRosters->count() > 0)
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 overflow-hidden">
            <!-- Table Header with Summary -->
            <div class="bg-gradient-to-r from-indigo-500 to-purple-600 px-6 py-4">
                <div class="flex items-center justify-between text-white">
                    <div>
                        <h3 class="text-lg font-semibold">Duty Roster Assignments</h3>
                        <p class="text-indigo-100 text-sm">Total: {{ $dutyRosters->count() }} assignments</p>
                    </div>
                    <div class="text-right">
                        <div class="text-sm text-indigo-100">
                            @if(request('start_date') && request('end_date'))
                                {{ \Carbon\Carbon::parse(request('start_date'))->format('M d, Y') }} - {{ \Carbon\Carbon::parse(request('end_date'))->format('M d, Y') }}
                            @else
                                Current Period
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50/80">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Employee</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Shift Details</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Date & Time</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @foreach($dutyRosters as $roster)
                            <tr class="hover:bg-indigo-50/30 transition-colors duration-200">
                                <!-- Employee Column -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center flex-shrink-0">
                                            <span class="text-white font-semibold text-sm">{{ strtoupper(substr($roster->employee->name ?? 'U', 0, 2)) }}</span>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div class="text-sm font-semibold text-gray-900 truncate">{{ $roster->employee->name ?? 'Unknown Employee' }}</div>
                                            <div class="text-sm text-gray-500 flex items-center">
                                                <i class="fas fa-id-badge mr-1 text-xs"></i>
                                                ID: {{ $roster->employee_payroll_id }}
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Shift Details Column -->
                                <td class="px-6 py-4">
                                    <div class="space-y-1">
                                        <div class="flex items-center space-x-2">
                                            <div class="w-6 h-6 bg-blue-500 rounded-md flex items-center justify-center">
                                                <i class="fas fa-clock text-white text-xs"></i>
                                            </div>
                                            <span class="text-sm font-semibold text-gray-900">{{ $roster->shift->name }}</span>
                                        </div>
                                        <p class="text-sm text-gray-600 ml-8">{{ $roster->shift->description }}</p>
                                        <div class="flex items-center space-x-2 ml-8 text-sm">
                                            <i class="fas fa-play text-green-500 text-xs"></i>
                                            <span class="font-medium text-gray-900">{{ $roster->shift->start_time }}</span>
                                            <span class="text-gray-400">to</span>
                                            <i class="fas fa-stop text-red-500 text-xs"></i>
                                            <span class="font-medium text-gray-900">{{ $roster->shift->end_time }}</span>
                                        </div>
                                    </div>
                                </td>

                                <!-- Date & Time Column -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="space-y-1">
                                        <div class="text-sm font-semibold text-gray-900">{{ \Carbon\Carbon::parse($roster->date)->format('M d, Y') }}</div>
                                        <div class="text-sm text-gray-500">{{ \Carbon\Carbon::parse($roster->date)->format('l') }}</div>
                                        <div class="text-xs text-gray-400">
                                            Assigned: {{ \Carbon\Carbon::parse($roster->created_at)->format('M d, Y') }}
                                        </div>
                                    </div>
                                </td>

                                <!-- Status Column -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        @if($roster->date >= date('Y-m-d'))
                                            bg-green-100 text-green-800
                                        @elseif($roster->date == date('Y-m-d'))
                                            bg-blue-100 text-blue-800
                                        @else
                                            bg-gray-100 text-gray-800
                                        @endif">
                                        @if($roster->date > date('Y-m-d'))
                                            <i class="fas fa-calendar-plus mr-1"></i>
                                            Upcoming
                                        @elseif($roster->date == date('Y-m-d'))
                                            <i class="fas fa-calendar-day mr-1"></i>
                                            Today
                                        @else
                                            <i class="fas fa-calendar-check mr-1"></i>
                                            Completed
                                        @endif
                                    </span>
                                </td>

                                <!-- Actions Column -->
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex items-center space-x-2">
                                        <a href="{{ route('duty-rosters.edit', $roster) }}"
                                           class="inline-flex items-center px-3 py-1.5 bg-green-500 hover:bg-green-600 text-white text-sm font-medium rounded-lg transition-colors duration-200 shadow-sm"
                                           title="Edit Roster">
                                            <i class="fas fa-edit mr-1"></i>
                                            Edit
                                        </a>
                                        <form action="{{ route('duty-rosters.destroy', $roster) }}"
                                              method="POST"
                                              class="inline"
                                              onsubmit="return confirm('Are you sure you want to delete this duty roster?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="inline-flex items-center px-3 py-1.5 bg-red-500 hover:bg-red-600 text-white text-sm font-medium rounded-lg transition-colors duration-200 shadow-sm"
                                                    title="Delete Roster">
                                                <i class="fas fa-trash mr-1"></i>
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($dutyRosters->hasPages())
                <div class="bg-gray-50/80 px-6 py-4 border-t border-gray-200">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-700">
                            Showing {{ $dutyRosters->firstItem() }} to {{ $dutyRosters->lastItem() }} of {{ $dutyRosters->total() }} results
                        </div>
                        <div class="flex justify-center">
                            {{ $dutyRosters->links() }}
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @else
        <div class="text-center py-12">
            <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-calendar-alt text-gray-400 text-2xl"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No Duty Rosters Found</h3>
            <p class="text-gray-500 mb-6">No duty rosters found for the selected filters.</p>
            <a href="{{ route('duty-rosters.create') }}"
               class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold rounded-lg shadow hover:from-indigo-700 hover:to-purple-700 transition">
                <i class="fas fa-plus mr-2"></i>
                Add First Duty Roster
            </a>
        </div>
    @endif
</div>

<script>
function setToday() {
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('startDate').value = today;
    document.getElementById('endDate').value = today;
    applyFilters();
}

function setThisWeek() {
    const today = new Date();
    const startOfWeek = new Date(today);
    startOfWeek.setDate(today.getDate() - today.getDay());
    const endOfWeek = new Date(startOfWeek);
    endOfWeek.setDate(startOfWeek.getDate() + 6);

    document.getElementById('startDate').value = startOfWeek.toISOString().split('T')[0];
    document.getElementById('endDate').value = endOfWeek.toISOString().split('T')[0];
    applyFilters();
}

function setThisMonth() {
    const today = new Date();
    const startOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);
    const endOfMonth = new Date(today.getFullYear(), today.getMonth() + 1, 0);

    document.getElementById('startDate').value = startOfMonth.toISOString().split('T')[0];
    document.getElementById('endDate').value = endOfMonth.toISOString().split('T')[0];
    applyFilters();
}

function applyFilters() {
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;
    const employeeId = document.getElementById('employee_payroll_id').value;

    let url = new URL(window.location);

    // Clear existing params
    url.searchParams.delete('start_date');
    url.searchParams.delete('end_date');
    url.searchParams.delete('employee_payroll_id');

    // Add new params
    if (startDate) url.searchParams.set('start_date', startDate);
    if (endDate) url.searchParams.set('end_date', endDate);
    if (employeeId) url.searchParams.set('employee_payroll_id', employeeId);

    window.location.href = url.toString();
}

function clearFilters() {
    const url = new URL(window.location);
    url.searchParams.delete('start_date');
    url.searchParams.delete('end_date');
    url.searchParams.delete('employee_payroll_id');

    // Reset form fields
    document.getElementById('startDate').value = '';
    document.getElementById('endDate').value = '';
    document.getElementById('employee_payroll_id').value = '';

    window.location.href = url.toString();
}

// Initialize date inputs with current values from URL params
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const startDate = urlParams.get('start_date');
    const endDate = urlParams.get('end_date');

    if (startDate) document.getElementById('startDate').value = startDate;
    if (endDate) document.getElementById('endDate').value = endDate;
});
</script>
@endsection