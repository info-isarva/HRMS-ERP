@extends('layouts.app')

@section('title', 'Approved Leaves - HRMS')
@section('page-title', 'Approved Leaves Report')

@section('content')
@php
    $today = \Carbon\Carbon::today()->toDateString();
    $quickDates = [
        'yesterday' => \Carbon\Carbon::yesterday()->toDateString(),
        'today' => $today,
        'tomorrow' => \Carbon\Carbon::tomorrow()->toDateString(),
        'day_after' => \Carbon\Carbon::today()->addDays(2)->toDateString(),
    ];
    $isQuickActive = function ($date) use ($startDate, $endDate) {
        return $startDate === $date && $endDate === $date;
    };
@endphp
<div class="p-6 space-y-6">
    <!-- Header Card -->
    <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-200">
        <div class="bg-gradient-to-r from-green-600 to-emerald-700 px-8 py-10">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                            <i class="fas fa-check-circle text-white text-xl"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold text-white">Approved Leaves Report</h1>
                        <p class="text-green-100 text-xs sm:text-sm lg:text-base mt-2">View comprehensive list of employees on approved leave within a specific date range.</p>
                    </div>
                </div>
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

        {{-- Quick date select --}}
        <div class="flex flex-wrap gap-2 mb-5">
            @foreach([
                'yesterday' => 'Yesterday',
                'today' => 'Today',
                'tomorrow' => 'Tomorrow',
                'day_after' => 'Day After Tomorrow',
            ] as $key => $label)
                <a href="{{ route('reports.leave-approved', array_filter([
                    'start_date' => $quickDates[$key],
                    'end_date' => $quickDates[$key],
                    'employee_name' => $employeeName ?? null,
                ])) }}"
                    class="px-3 py-1.5 rounded-lg text-sm font-medium border transition-colors {{ $isQuickActive($quickDates[$key]) ? 'bg-indigo-600 text-white border-indigo-600 shadow-sm' : 'bg-white text-gray-600 border-gray-200 hover:border-indigo-300 hover:text-indigo-700' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        <form method="GET" action="{{ route('reports.leave-approved') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
            <div>
                <label for="employee_name" class="block text-sm font-medium text-gray-700 mb-1">Employee Name</label>
                <input type="text" name="employee_name" id="employee_name" value="{{ $employeeName ?? '' }}"
                    placeholder="Search by name or email"
                    class="w-full h-10 px-3 rounded-lg border border-gray-300 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
            </div>
            <div>
                <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">From Date</label>
                <input type="date" name="start_date" id="start_date" value="{{ $startDate ?? $today }}"
                    class="w-full h-10 px-3 rounded-lg border border-gray-300 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
            </div>
            <div>
                <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">To Date</label>
                <input type="date" name="end_date" id="end_date" value="{{ $endDate ?? $today }}"
                    class="w-full h-10 px-3 rounded-lg border border-gray-300 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
            </div>
            <div class="md:col-span-2 flex flex-wrap gap-2">
                <button type="submit" class="flex-1 min-w-[8rem] h-10 bg-indigo-600 text-white px-4 rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors shadow-sm text-sm font-medium">
                    <i class="fas fa-search mr-1"></i> Filter
                </button>
                <a href="{{ request()->fullUrlWithQuery(['format' => 'pdf']) }}" target="_blank" class="flex-1 min-w-[8rem] h-10 inline-flex items-center justify-center bg-rose-600 text-white px-4 rounded-lg hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-rose-500 transition-colors shadow-sm text-sm font-medium">
                    <i class="fas fa-file-pdf mr-1"></i> PDF
                </a>
                <a href="{{ route('reports.leave-approved') }}" class="h-10 px-4 inline-flex items-center bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors text-sm font-medium">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Results Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
            <h3 class="font-bold text-gray-800">Approved Leaves</h3>
            <span class="bg-green-100 text-green-700 text-xs font-semibold px-2.5 py-0.5 rounded-full">
                {{ $leaves->count() }} Records
            </span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs text-gray-500 uppercase bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th scope="col" class="px-6 py-3">Employee</th>
                        <th scope="col" class="px-6 py-3">Leave Type</th>
                        <th scope="col" class="px-6 py-3">Period</th>
                        <th scope="col" class="px-6 py-3">Days</th>
                        <th scope="col" class="px-6 py-3">Reason</th>
                        <th scope="col" class="px-6 py-3">Approval Details</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($leaves as $leave)
                    <tr class="bg-white hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="h-8 w-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold mr-3">
                                    {{ strtoupper(substr($leave->user->name ?? '?', 0, 1)) }}
                                </div>
                                <div>
                                    <div class="font-medium text-gray-900">{{ $leave->user->name ?? 'N/A' }}</div>
                                    <div class="text-xs text-gray-500">{{ $leave->user->email ?? '' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                {{ $leave->leaveType->name ?? 'Unknown' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-gray-900">{{ $leave->start_date->format('d M Y') }}</div>
                            <div class="text-xs text-gray-500">to {{ $leave->end_date->format('d M Y') }}</div>
                        </td>
                        <td class="px-6 py-4 font-semibold text-gray-900">
                            {{ $leave->total_days }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="truncate max-w-xs text-gray-600" title="{{ $leave->reason }}">
                                {{ $leave->reason }}
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-xs">
                                <div class="mb-1"><span class="text-gray-500">Mgr:</span> {{ $leave->managerApprovedBy->name ?? '-' }}</div>
                                <div><span class="text-gray-500">HR:</span> {{ $leave->hrApprovedBy->name ?? '-' }}</div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                         <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                            <div class="flex flex-col items-center justify-center">
                                <i class="fas fa-calendar-times text-4xl text-gray-300 mb-3"></i>
                                <p>No approved leaves found matching the criteria.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
