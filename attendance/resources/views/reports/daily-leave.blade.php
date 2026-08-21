@extends('layouts.app')

@section('title', 'Daily Leave Schedule - HRMS')
@section('page-title', 'Daily Leave Schedule Report')

@section('content')
<div class="p-6 space-y-6">
    <!-- Header Card -->
    <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-200">
        <div class="bg-gradient-to-r from-yellow-600 to-amber-700 px-8 py-10">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                            <i class="fas fa-calendar-alt text-white text-xl"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold text-white">Daily Leave Schedule</h1>
                        <p class="text-amber-100 text-xs sm:text-sm lg:text-base mt-2">Track all active leave applications for specific dates, including pending and approved ones.</p>
                    </div>
                </div>
                <!-- Back Button -->
                <div class="flex items-center">
                    <a href="{{ route('reports.index') }}" class="inline-flex items-center px-4 py-2 bg-white bg-opacity-20 hover:bg-opacity-30 text-white rounded-lg transition-colors font-medium text-sm border border-white border-opacity-30">
                        <i class="fas fa-arrow-left mr-2"></i> Back
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
            <i class="fas fa-filter text-indigo-500 mr-2"></i> Filter Options
        </h2>
        <form method="GET" action="{{ route('reports.daily-leave') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            <div>
                <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">From Date</label>
                <input type="date" name="start_date" id="start_date" value="{{ $startDate ?? '' }}" 
                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div>
                <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">To Date</label>
                <input type="date" name="end_date" id="end_date" value="{{ $endDate ?? '' }}" 
                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div>
                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search Employee</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                    <input type="text" name="search" id="search" value="{{ $search ?? '' }}" 
                        placeholder="Name or Email"
                        class="pl-10 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
            </div>
            <div class="flex space-x-2">
                <button type="submit" class="flex-1 bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors shadow-sm font-medium">
                    Show Results
                </button>
                <a href="{{ route('reports.daily-leave') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors font-medium text-center">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Results Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
            <h3 class="font-bold text-gray-800">Leave Applications</h3>
            <div class="flex items-center space-x-3">
                <a href="{{ route('reports.daily-leave.pdf', request()->query()) }}" class="inline-flex items-center px-3 py-1.5 bg-red-600 text-white text-xs font-semibold rounded-lg hover:bg-red-700 transition-colors shadow-sm">
                    <i class="fas fa-file-pdf mr-1.5"></i> Export PDF
                </a>
                <span class="bg-amber-100 text-amber-700 text-xs font-semibold px-2.5 py-1 rounded-full border border-amber-200">
                    {{ $leaves->count() }} Records Found
                </span>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs text-gray-500 uppercase bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th scope="col" class="px-6 py-3">Employee</th>
                        <th scope="col" class="px-6 py-3">Leave Type</th>
                        <th scope="col" class="px-6 py-3">Duration</th>
                        <th scope="col" class="px-6 py-3">Days</th>
                        <th scope="col" class="px-6 py-3">Status</th>
                        <th scope="col" class="px-6 py-3">Reason</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($leaves as $leave)
                    <tr class="bg-white hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="h-9 w-9 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold mr-3 shadow-sm">
                                    {{ strtoupper(substr($leave->user->name ?? '?', 0, 1)) }}
                                </div>
                                <div>
                                    <div class="font-semibold text-gray-900">{{ $leave->user->name ?? 'N/A' }}</div>
                                    <div class="text-xs text-gray-500">{{ $leave->user->email ?? '' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 border border-indigo-200">
                                {{ $leave->leaveType->name ?? 'Unknown' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="font-medium text-gray-900">{{ $leave->start_date->format('d M Y') }}</div>
                            <div class="text-xs text-gray-500">to {{ $leave->end_date->format('d M Y') }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-col">
                                <span class="font-bold text-gray-900">{{ $leave->total_days }} days</span>
                                @if($leave->has_lop)
                                    <span class="text-[10px] text-red-600 font-medium">(Inc. {{ $leave->lop_days }} LOP)</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            @php
                                $statusColors = [
                                    'pending' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                                    'forwarded_to_manager' => 'bg-purple-100 text-purple-800 border-purple-200',
                                    'approved_by_manager' => 'bg-blue-100 text-blue-800 border-blue-200',
                                    'approved' => 'bg-green-100 text-green-800 border-green-200',
                                    'rejected' => 'bg-red-100 text-red-800 border-red-200',
                                    'cancelled' => 'bg-gray-100 text-gray-800 border-gray-200',
                                ];
                                $statusNames = [
                                    'pending' => 'Pending',
                                    'forwarded_to_manager' => 'Forwarded',
                                    'approved_by_manager' => 'Mgr Approved',
                                    'approved' => 'Full Approved',
                                    'rejected' => 'Rejected',
                                    'cancelled' => 'Cancelled',
                                ];
                                $colorClass = $statusColors[$leave->status] ?? 'bg-gray-100 text-gray-800 border-gray-200';
                                $statusName = $statusNames[$leave->status] ?? ucfirst($leave->status);
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $colorClass }}">
                                {{ $statusName }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="truncate max-w-xs text-gray-600 italic" title="{{ $leave->reason }}">
                                "{{ $leave->reason }}"
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                         <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                            <div class="flex flex-col items-center justify-center">
                                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                    <i class="fas fa-calendar-times text-2xl text-gray-400"></i>
                                </div>
                                <h4 class="text-lg font-medium text-gray-900 mb-1">No Leave Applications Found</h4>
                                <p class="text-sm text-gray-500">There are no active leave applications for the selected date range.</p>
                                @if($startDate || $search)
                                    <a href="{{ route('reports.daily-leave') }}" class="mt-4 text-indigo-600 hover:text-indigo-800 font-medium text-sm">Clear Filters</a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($leaves->isNotEmpty())
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 text-xs text-gray-500 italic">
            * This report includes all leave applications except rejected ones for the selected period.
        </div>
        @endif
    </div>
</div>
@endsection
