@extends('layouts.app')

@section('title', 'Duty Roster Details - HRMS')

@section('page-title', 'Duty Roster Details')

@section('content')
<div class="p-6 space-y-6">
    <!-- Header card (gradient) -->
    <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-200">
        <div class="bg-gradient-to-r from-blue-600 to-indigo-700 px-8 py-8">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                        <i class="fas fa-calendar-check text-white text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h1 class="text-2xl font-bold text-white">Duty Roster Details</h1>
                        <p class="text-blue-100 text-sm mt-2">Employee shift assignment information</p>
                    </div>
                </div>

                <div class="hidden md:flex items-center space-x-3">
                    <a href="{{ route('duty-rosters.edit', $dutyRoster) }}" class="inline-flex items-center px-4 py-3 bg-white text-indigo-700 font-semibold rounded-lg shadow-md hover:bg-gray-100 transition">
                        <i class="fas fa-edit mr-2"></i> Edit Roster
                    </a>
                    <a href="{{ route('duty-rosters.index') }}" class="inline-flex items-center px-4 py-3 bg-white text-indigo-700 font-semibold rounded-lg shadow-md hover:bg-gray-100 transition">
                        <i class="fas fa-arrow-left mr-2"></i> Back to Duty Rosters
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Duty Roster Information -->
    <div class="bg-white shadow-lg rounded-xl overflow-hidden border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h2 class="text-lg font-semibold text-gray-900">Duty Roster Information</h2>
        </div>

        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Employee</label>
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center flex-shrink-0">
                            <span class="text-white font-semibold text-sm">{{ strtoupper(substr($dutyRoster->employee->name ?? 'U', 0, 2)) }}</span>
                        </div>
                        <div class="ml-3">
                            <p class="text-lg font-semibold text-gray-900">{{ $dutyRoster->employee->name ?? 'Unknown Employee' }}</p>
                            <p class="text-sm text-gray-500">{{ $dutyRoster->employee_payroll_id }}</p>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Shift</label>
                    <p class="text-lg font-semibold text-gray-900">{{ $dutyRoster->shift->name }}</p>
                    <p class="text-sm text-gray-500">{{ $dutyRoster->shift->start_time }} - {{ $dutyRoster->shift->end_time }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Date</label>
                    <p class="text-lg font-semibold text-gray-900">{{ \Carbon\Carbon::parse($dutyRoster->date)->format('M d, Y') }}</p>
                    <p class="text-sm text-gray-500">{{ \Carbon\Carbon::parse($dutyRoster->date)->format('l') }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Time Range</label>
                    <p class="text-lg font-semibold text-gray-900">{{ $dutyRoster->shift->start_time }} - {{ $dutyRoster->shift->end_time }}</p>
                </div>
            </div>

            @if($dutyRoster->shift->description)
                <div class="mt-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Shift Description</label>
                    <p class="text-gray-900">{{ $dutyRoster->shift->description }}</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Related Information -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Employee Details -->
        <div class="bg-white shadow-lg rounded-xl overflow-hidden border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h2 class="text-lg font-semibold text-gray-900">Employee Details</h2>
            </div>
            <div class="p-6">
                @if($dutyRoster->employee)
                    <div class="space-y-3">
                        <div>
                            <span class="text-sm text-gray-500">Name:</span>
                            <span class="text-sm font-medium text-gray-900">{{ $dutyRoster->employee->name }}</span>
                        </div>
                        <div>
                            <span class="text-sm text-gray-500">Payroll ID:</span>
                            <span class="text-sm font-medium text-gray-900">{{ $dutyRoster->employee->payroll_id }}</span>
                        </div>
                        <div>
                            <span class="text-sm text-gray-500">Email:</span>
                            <span class="text-sm font-medium text-gray-900">{{ $dutyRoster->employee->email ?: 'Not provided' }}</span>
                        </div>
                        <div>
                            <span class="text-sm text-gray-500">Department:</span>
                            <span class="text-sm font-medium text-gray-900">{{ $dutyRoster->employee->department->name ?? 'Unknown' }}</span>
                        </div>
                    </div>
                @else
                    <p class="text-gray-500">Employee information not available</p>
                @endif
            </div>
        </div>

        <!-- Shift Details -->
        <div class="bg-white shadow-lg rounded-xl overflow-hidden border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h2 class="text-lg font-semibold text-gray-900">Shift Details</h2>
            </div>
            <div class="p-6">
                <div class="space-y-3">
                    <div>
                        <span class="text-sm text-gray-500">Name:</span>
                        <span class="text-sm font-medium text-gray-900">{{ $dutyRoster->shift->name }}</span>
                    </div>
                    <div>
                        <span class="text-sm text-gray-500">Start Time:</span>
                        <span class="text-sm font-medium text-gray-900">{{ $dutyRoster->shift->start_time }}</span>
                    </div>
                    <div>
                        <span class="text-sm text-gray-500">End Time:</span>
                        <span class="text-sm font-medium text-gray-900">{{ $dutyRoster->shift->end_time }}</span>
                    </div>
                    <div>
                        <span class="text-sm text-gray-500">Total Rosters:</span>
                        <span class="text-sm font-medium text-gray-900">{{ $dutyRoster->shift->dutyRosters->count() }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection