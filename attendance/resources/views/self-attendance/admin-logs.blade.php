@extends('layouts.app')

@section('title', 'Portal Punches Log - HRMS')
@section('page-title', 'Portal Punches Log')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 py-6 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto space-y-6">
        
        <!-- Header -->
        <div class="bg-white rounded-2xl shadow-lg border border-slate-100 p-6 flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 flex items-center">
                    <i class="fas fa-fingerprint text-indigo-600 mr-3"></i>
                    Portal Attendance Punches
                </h1>
                <p class="text-slate-500 text-sm mt-1">Logs from employee portal check-ins and check-outs (excluding biometric devices).</p>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-2xl shadow-md border border-slate-100 p-6">
            <form method="GET" action="{{ route('self-attendance.admin-logs') }}" class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">
                        <i class="fas fa-calendar-day mr-1 text-indigo-500"></i> Date
                    </label>
                    <input type="date" name="date" value="{{ $date }}" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">
                        <i class="fas fa-user mr-1 text-emerald-500"></i> Employee
                    </label>
                    <select name="employee_payroll_id" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500">
                        <option value="">All Employees</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->payroll_id }}" {{ (request()->filled('employee_payroll_id') && request('employee_payroll_id') == $emp->payroll_id) ? 'selected' : '' }}>
                                {{ $emp->name }} ({{ $emp->employee_id }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-end space-x-3">
                    <button type="submit" class="bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-700 hover:to-blue-700 text-white font-semibold rounded-xl px-6 py-3 shadow-lg hover:shadow-xl transition-all duration-200 flex-1">
                        <i class="fas fa-filter mr-2"></i> Filter
                    </button>
                    <a href="{{ route('self-attendance.admin-logs') }}" class="border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-semibold rounded-xl px-6 py-3 transition-all duration-200">
                        <i class="fas fa-undo"></i> Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Logs Table -->
        <div class="bg-white rounded-2xl shadow-xl border border-slate-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Employee</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Check In</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Check Out</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Total Hours</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-100">
                        @forelse($logs as $log)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-blue-600 rounded-xl flex items-center justify-center text-white font-bold mr-3 shadow-sm">
                                            {{ substr($log->employee->name ?? 'E', 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="text-sm font-semibold text-slate-800">{{ $log->employee->name ?? 'Unknown' }}</div>
                                            <div class="text-xs text-slate-400">Payroll ID: {{ $log->employee_payroll_id }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm font-medium text-slate-700">{{ \Carbon\Carbon::parse($log->date)->format('d M Y') }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    @if($log->check_in_time)
                                        <div class="space-y-1">
                                            <div class="text-sm font-semibold text-emerald-600 flex items-center">
                                                <i class="fas fa-sign-in-alt mr-1"></i> {{ \Carbon\Carbon::parse($log->check_in_time)->format('g:i A') }}
                                                @if($log->source === 'manual_correction' && empty($log->check_in_ip))
                                                    <span class="ml-2 px-1.5 py-0.5 text-[9px] font-bold rounded bg-amber-100 text-amber-800">Corrected</span>
                                                @endif
                                            </div>
                                            <div class="text-[10px] text-slate-500 flex flex-col space-y-0.5">
                                                <span>IP: {{ $log->check_in_ip }}</span>
                                                @if($log->check_in_latitude && $log->check_in_longitude)
                                                    <a href="https://www.google.com/maps/search/?api=1&query={{ $log->check_in_latitude }},{{ $log->check_in_longitude }}" target="_blank" class="text-blue-500 hover:underline flex items-center">
                                                        <i class="fas fa-map-marker-alt text-rose-500 mr-1"></i> View GPS Map
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-slate-400">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @if($log->check_out_time)
                                        <div class="space-y-1">
                                            <div class="text-sm font-semibold text-rose-600 flex items-center">
                                                <i class="fas fa-sign-out-alt mr-1"></i> {{ \Carbon\Carbon::parse($log->check_out_time)->format('g:i A') }}
                                                @if($log->source === 'manual_correction' && empty($log->check_out_ip))
                                                    <span class="ml-2 px-1.5 py-0.5 text-[9px] font-bold rounded bg-amber-100 text-amber-800">Corrected</span>
                                                @endif
                                            </div>
                                            <div class="text-[10px] text-slate-500 flex flex-col space-y-0.5">
                                                <span>IP: {{ $log->check_out_ip }}</span>
                                                @if($log->check_out_latitude && $log->check_out_longitude)
                                                    <a href="https://www.google.com/maps/search/?api=1&query={{ $log->check_out_latitude }},{{ $log->check_out_longitude }}" target="_blank" class="text-blue-500 hover:underline flex items-center">
                                                        <i class="fas fa-map-marker-alt text-rose-500 mr-1"></i> View GPS Map
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-slate-400 font-medium">Active (Not Checked Out)</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm font-semibold text-slate-700">{{ $log->total_hours ?? '--' }} hrs</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($log->status == 'present')
                                        <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-emerald-100 text-emerald-800">
                                            Present
                                        </span>
                                    @elseif($log->status == 'late')
                                        <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-amber-100 text-amber-800">
                                            Late
                                        </span>
                                    @elseif($log->status == 'half_day')
                                        <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-amber-200 text-amber-900">
                                            Half Day
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-slate-100 text-slate-800">
                                            {{ ucfirst($log->status) }}
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mb-4">
                                            <i class="fas fa-fingerprint text-slate-400 text-2xl"></i>
                                        </div>
                                        <p class="text-slate-500 font-medium">No portal punch logs found for this date.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($logs->hasPages())
                <div class="px-6 py-4 border-t border-slate-100">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>

    </div>
</div>
@endsection
