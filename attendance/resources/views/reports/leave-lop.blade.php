@extends('layouts.app')

@section('title', 'LOP Report - HRMS')
@section('page-title', 'Loss of Pay (LOP) Report')

@section('content')
<div class="p-6 space-y-6">
    <!-- Header Card -->
    <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-200">
        <div class="bg-gradient-to-r from-orange-600 to-red-700 px-8 py-10">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                            <i class="fas fa-hand-holding-usd text-white text-xl"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold text-white">Loss of Pay (LOP) Report</h1>
                        <p class="text-orange-100 text-xs sm:text-sm lg:text-base mt-2">View and analyze leave applications involving Loss of Pay (LOP).</p>
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
        <form method="GET" action="{{ route('reports.leave-lop') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
            <div class="md:col-span-2">
                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search Employee</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-user-search text-gray-400"></i>
                    </div>
                    <input type="text" name="search" id="search" value="{{ $search ?? '' }}" placeholder="Name or email..."
                        class="w-full pl-10 rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
            </div>
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
            <div class="flex space-x-2">
                <button type="submit" class="flex-1 bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors shadow-sm">
                    <i class="fas fa-search mr-1"></i> Filter
                </button>
                <a href="{{ request()->fullUrlWithQuery(['format' => 'pdf']) }}" target="_blank" class="flex-1 bg-rose-600 text-white px-4 py-2 rounded-lg hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-rose-500 transition-colors shadow-sm text-center">
                    <i class="fas fa-file-pdf mr-1"></i> PDF
                </a>
                <a href="{{ route('reports.leave-lop') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Results Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
            <h3 class="font-bold text-gray-800">Leaves with LOP</h3>
            <span class="bg-orange-100 text-orange-700 text-xs font-semibold px-2.5 py-0.5 rounded-full">
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
                        <th scope="col" class="px-6 py-3 text-center">Total Days</th>
                        <th scope="col" class="px-6 py-3 text-center text-green-600">Paid Days</th>
                        <th scope="col" class="px-6 py-3 text-center text-red-600">LOP Days</th>
                        <th scope="col" class="px-6 py-3">Status</th>
                        <th scope="col" class="px-6 py-3">Reason</th>
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
                        <td class="px-6 py-4 font-semibold text-gray-900 text-center">
                            {{ floatval($leave->total_days) }}
                        </td>
                        <td class="px-6 py-4 font-semibold text-green-700 text-center">
                            {{ floatval($leave->paid_days) }}
                        </td>
                        <td class="px-6 py-4 font-bold text-red-700 text-center">
                            {{ floatval($leave->lop_days) }}
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $leave->status === 'approved' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                {{ ucfirst(str_replace('_', ' ', $leave->status)) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-xs text-gray-600">
                            <div class="max-w-xs truncate" title="{{ $leave->reason }}">
                                {{ $leave->reason }}
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                         <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                            <div class="flex flex-col items-center justify-center">
                                <i class="fas fa-file-invoice-dollar text-4xl text-gray-300 mb-3"></i>
                                <p>No LOP records found matching the criteria.</p>
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
