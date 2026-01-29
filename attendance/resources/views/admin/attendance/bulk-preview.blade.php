<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Dashboard - HRMS</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
    
    @stack('styles')
</head>
<body class="bg-gray-50">
    <div class="min-h-screen">
@php
    // Debug: Check what data we have
    \Log::info('Bulk attendance view data', [
        'flexible_holidays_count' => isset($flexibleHolidays) ? count($flexibleHolidays) : 0,
        'flexible_applications_count' => isset($flexibleHolidayApplications) ? count($flexibleHolidayApplications) : 0,
        'leave_applications_count' => isset($leaveApplicationsByEmail) ? count($leaveApplicationsByEmail) : 0,
        'leave_emails' => isset($leaveApplicationsByEmail) ? array_keys($leaveApplicationsByEmail->toArray()) : [],
        'month' => $month,
        'year' => $year
    ]);
@endphp
<div class="container-fluid mx-auto px-4 py-4">
    <!-- Enhanced Modern Header with Gradient and Better Button Padding -->
    <div class="bg-gradient-to-r from-blue-600 to-indigo-700 shadow-lg mb-5 px-6 py-5 rounded-lg">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div class="text-white">
                <h1 class="text-2xl font-bold flex items-center">
                    <svg class="w-7 h-7 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    Attendance Dashboard
                </h1>
                <div class="flex flex-wrap items-center gap-3 mt-2">
                    <p class="text-base bg-white/20 rounded-md px-4 py-1 inline-block">
                        {{ DateTime::createFromFormat('!m', $month)->format('F') }} {{ $year }}
                    </p>
                    <div class="bg-white/10 rounded-md px-3 py-1 flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-sm font-medium">{{ $daysInMonth }} days</span>
                    </div>
                </div>
            </div>
            <div class="flex flex-wrap gap-4">
                <a href="{{ route('admin.attendance.index') }}" class="bg-white text-blue-700 text-sm font-medium py-3 px-6 rounded-lg inline-flex items-center transition-all shadow-sm hover:bg-blue-50">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back
                </a>
                @if(isset($isLocked) && !$isLocked)
                {{-- Regenerate button hidden per user request --}}
                {{-- <button id="btn-regenerate" class="bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white text-sm font-medium py-3 px-6 rounded-lg inline-flex items-center transition-all shadow-md">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    Regenerate
                </button> --}}
                @endif
            </div>
        </div>
    </div>

    <!-- Attendance Policy Info Section -->
    @if(isset($attendancePolicy) && $attendancePolicy)
    <div class="px-4 mb-4">
        <div class="bg-gradient-to-r from-indigo-50 to-purple-50 rounded-lg border border-indigo-200 shadow-sm p-4">
            <div class="flex items-center mb-3">
                <svg class="w-5 h-5 text-indigo-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <h4 class="text-base font-semibold text-indigo-900">Active Policy: {{ $attendancePolicy->name }}</h4>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
                <div class="bg-white rounded-md px-3 py-2 border border-indigo-100">
                    <div class="text-xs text-gray-600">Late Grace</div>
                    <div class="font-bold text-indigo-700">{{ $attendancePolicy->late_arrival_grace_minutes }} min</div>
                </div>
                <div class="bg-white rounded-md px-3 py-2 border border-indigo-100">
                    <div class="text-xs text-gray-600">Half-Day (Late)</div>
                    <div class="font-bold text-orange-700">{{ $attendancePolicy->half_day_late_threshold_minutes }} min</div>
                </div>
                <div class="bg-white rounded-md px-3 py-2 border border-indigo-100">
                    <div class="text-xs text-gray-600">Absent</div>
                    <div class="font-bold text-red-700">{{ $attendancePolicy->absent_threshold_minutes }} min</div>
                </div>
                <div class="bg-white rounded-md px-3 py-2 border border-indigo-100">
                    <div class="text-xs text-gray-600">Min Work Hours</div>
                    <div class="font-bold text-green-700">{{ $attendancePolicy->minimum_work_hours_for_present }} hrs</div>
                </div>
            </div>
        </div>
    </div>
    @endif



    <!-- Enhanced Legend Section with Better Visibility -->
    <div class="px-4 mb-4">
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-4">
            <h4 class="text-base font-medium mb-3 text-gray-700 border-b pb-2">Legend:</h4>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:flex md:flex-wrap items-center md:gap-6 gap-4 text-sm">
                <div class="flex items-center bg-gray-50 rounded-lg px-3 py-2 shadow-sm border border-gray-100">
                    <span class="w-8 h-8 rounded-md bg-green-500 flex items-center justify-center text-white font-bold mr-3 shadow-sm">P</span>
                    <span class="font-medium">Present</span>
                </div>
                <div class="flex items-center bg-gray-50 rounded-lg px-3 py-2 shadow-sm border border-gray-100">
                    <span class="w-8 h-8 rounded-md bg-yellow-500 flex items-center justify-center text-white font-bold mr-3 shadow-sm">L</span>
                    <span class="font-medium">Late</span>
                </div>
                <div class="flex items-center bg-gray-50 rounded-lg px-3 py-2 shadow-sm border border-gray-100">
                    <span class="w-8 h-8 rounded-md bg-blue-500 flex items-center justify-center text-white font-bold mr-3 shadow-sm text-xs">HD</span>
                    <span class="font-medium">Half Day</span>
                </div>
                <div class="flex items-center bg-gray-50 rounded-lg px-3 py-2 shadow-sm border border-gray-100">
                    <span class="w-8 h-8 rounded-md bg-orange-400 flex items-center justify-center text-white font-bold mr-3 shadow-sm text-xs">ED</span>
                    <span class="font-medium">Early Departure</span>
                </div>
                <div class="flex items-center bg-gray-50 rounded-lg px-3 py-2 shadow-sm border border-gray-100">
                    <span class="w-8 h-8 rounded-md bg-red-500 flex items-center justify-center text-white font-bold mr-3 shadow-sm text-xs">CL</span>
                    <div class="flex flex-col">
                        <span class="font-medium">Leave Types</span>
                        <span class="text-xs text-gray-600">(CL, SL, ML, etc.)</span>
                    </div>
                </div>
                <div class="flex items-center bg-gray-50 rounded-lg px-3 py-2 shadow-sm border border-gray-100">
                    <span class="w-8 h-8 rounded-md bg-red-700 border-2 border-yellow-400 flex items-center justify-center text-white font-bold mr-3 shadow-sm text-xs relative">
                        LOP
                        <span class="absolute -top-1 -right-1 w-3 h-3 bg-yellow-400 rounded-full border border-yellow-600 text-xs flex items-center justify-center">!</span>
                    </span>
                    <div class="flex flex-col">
                        <span class="font-medium">LOP (Loss of Pay)</span>
                        <span class="text-xs text-gray-600">Unpaid leave days</span>
                    </div>
                </div>
                <div class="flex items-center bg-gray-50 rounded-lg px-3 py-2 shadow-sm border border-gray-100">
                    <span class="w-8 h-8 rounded-md bg-orange-500 border border-orange-700 flex items-center justify-center text-white font-bold mr-3 shadow-sm">PH</span>
                    <span class="font-medium">Fixed Public Holiday</span>
                </div>
                <div class="flex items-center bg-gray-50 rounded-lg px-3 py-2 shadow-sm border border-gray-100">
                    <span class="w-8 h-8 rounded-md bg-purple-600 border border-purple-800 flex items-center justify-center text-white font-bold mr-3 shadow-sm text-xs">FH</span>
                    <span class="font-medium">Flexible Public Holiday</span>
                </div>
                <div class="flex items-center bg-gray-50 rounded-lg px-3 py-2 shadow-sm border border-gray-100">
                    <span class="w-8 h-8 rounded-md bg-gray-500 flex items-center justify-center text-white font-bold mr-3 shadow-sm">W</span>
                    <span class="font-medium">Week-off (Personalized)</span>
                </div>
               
            </div>
        </div>
    </div>

    <!-- Dynamic Leave Types Display -->
    @if(isset($leaveApplicationsByEmail) && count($leaveApplicationsByEmail) > 0)
    <div class="px-4 mb-4">
        <div class="bg-green-50 rounded-lg border border-green-200 shadow-sm p-4">
            <h5 class="text-sm font-semibold text-green-800 mb-3 border-b border-green-300 pb-2">
                <svg class="inline w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V7.414A2 2 0 0015.414 6L12 2.586A2 2 0 0010.586 2H6zm5 6a1 1 0 10-2 0v3.586l-1.293-1.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 11.586V8z" clip-rule="evenodd"></path>
                </svg>
                Active Leave Types This Month:
            </h5>
            <div class="flex flex-wrap gap-2">
                @php
                    $activeLeaveTypes = collect();
                    foreach($leaveApplicationsByEmail as $emailLeaves) {
                        foreach($emailLeaves as $leave) {
                            if($leave->leaveType) {
                                $activeLeaveTypes->put($leave->leaveType->code, $leave->leaveType->name);
                            }
                        }
                    }
                    $activeLeaveTypes = $activeLeaveTypes->unique();
                @endphp
                @if($activeLeaveTypes->count() > 0)
                    @foreach($activeLeaveTypes as $code => $name)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 border border-red-200">
                            <span class="font-bold bg-red-500 text-white px-2 py-1 rounded-full mr-2 text-xs">{{ $code }}</span>
                            {{ $name }}
                        </span>
                    @endforeach
                @else
                    <span class="text-sm text-green-700 italic">No leave applications found for this month.</span>
                @endif
            </div>
        </div>
    </div>
    @endif

    <!-- Full-width Attendance Table with enhanced styling and smaller text -->
    <div class="w-full overflow-x-auto px-4">
        <table class="w-full divide-y divide-gray-200 border border-gray-200 rounded-lg shadow-sm text-xs md:text-sm">
            <thead>
                <tr class="bg-gradient-to-r from-gray-100 to-gray-200">
                    <th class="sticky left-0 z-10 px-2 py-2 text-left text-xs font-medium text-gray-700 uppercase tracking-wider bg-gradient-to-r from-blue-50 to-indigo-50 min-w-[140px] border-b-2 border-gray-300">
                        Employee
                    </th>
                    @foreach($calendarDates as $dateInfo)
                    <th class="px-1 py-2 text-center text-xs font-medium text-gray-700 uppercase tracking-wider border-r border-b-2 border-gray-300
                        @if($dateInfo['is_weekend']) bg-gray-200 @endif
                        @if(isset($dateInfo['is_fixed_holiday']) && $dateInfo['is_fixed_holiday']) bg-orange-100 @endif
                        @if(isset($dateInfo['is_flexible_holiday']) && $dateInfo['is_flexible_holiday']) bg-purple-100 @endif" 
                        title="{{ $dateInfo['date']->format('l, F j, Y') }} 
                        {{ isset($dateInfo['fixed_holiday_name']) && $dateInfo['fixed_holiday_name'] ? '- Fixed: ' . $dateInfo['fixed_holiday_name'] : '' }}
                        {{ isset($dateInfo['flexible_holiday_name']) && $dateInfo['flexible_holiday_name'] ? '- Flexible: ' . $dateInfo['flexible_holiday_name'] : '' }}">
                        <div class="text-xs font-bold">{{ $dateInfo['day'] }}</div>
                        <div class="text-[10px] text-gray-600">{{ $dateInfo['day_name'] }}</div>
                    </th>
                    @endforeach
                    <th class="px-2 py-2 text-xs font-medium text-green-700 uppercase tracking-wider bg-green-100 border-b-2 border-green-300">P</th>
                    <th class="px-2 py-2 text-xs font-medium text-red-700 uppercase tracking-wider bg-red-100 border-b-2 border-red-300">L</th>
                    <th class="px-2 py-2 text-xs font-medium text-orange-700 uppercase tracking-wider bg-orange-100 border-b-2 border-orange-300">PH</th>
                    <th class="px-2 py-2 text-xs font-medium text-purple-700 uppercase tracking-wider bg-purple-100 border-b-2 border-purple-300">FH</th>
                    <th class="px-2 py-2 text-xs font-medium text-gray-700 uppercase tracking-wider bg-gray-200 border-b-2 border-gray-400">W</th>
                    <th class="px-2 py-2 text-xs font-medium text-red-800 uppercase tracking-wider bg-red-200 border-b-2 border-red-400" title="Loss of Pay">LOP</th>
                    <th class="px-2 py-2 text-xs font-medium text-blue-800 uppercase tracking-wider bg-blue-200 border-b-2 border-blue-400" title="Salary Days (Total Days - LOP)">Salary Days</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($employees as $employee)
                <tr class="hover:bg-gray-50 {{ $employee->status === 'Left' ? 'bg-gray-100' : '' }}" data-employee-lop-days="{{ $employee->payroll_id && isset($lopTotalsByPayrollId[$employee->payroll_id]) ? $lopTotalsByPayrollId[$employee->payroll_id] : 0 }}">
                    <td class="sticky left-0 z-10 px-2 py-1 bg-white whitespace-nowrap border-r">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-8 w-8 rounded-md bg-blue-600 flex items-center justify-center text-white text-xs font-bold shadow-sm">
                                {{ substr($employee->name, 0, 2) }}
                            </div>
                            <div class="ml-2">
                                <div class="text-xs font-semibold text-gray-900">{{ $employee->name }}</div>
                                <div class="text-[10px] bg-gray-100 text-gray-700 px-1 py-0.5 rounded-full inline-block">{{ $employee->department->name ?? '-' }}</div>
                                @if($employee->status === 'Left')
                                    <div class="text-[9px] bg-red-100 text-red-700 px-2 py-0.5 rounded-full inline-block mt-0.5 font-medium">{{ strtoupper($employee->status) }}</div>
                                @elseif($employee->status === 'Probation Period')
                                    <div class="text-[9px] bg-yellow-100 text-yellow-700 px-2 py-0.5 rounded-full inline-block mt-0.5 font-medium">{{ strtoupper($employee->status) }}</div>
                                @elseif($employee->status === 'Onboard')
                                    <div class="text-[9px] bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full inline-block mt-0.5 font-medium">{{ strtoupper($employee->status) }}</div>
                                @elseif($employee->status === 'Active')
                                    <div class="text-[9px] bg-green-100 text-green-700 px-2 py-0.5 rounded-full inline-block mt-0.5 font-medium">{{ strtoupper($employee->status) }}</div>
                                @else
                                    <div class="text-[9px] bg-gray-100 text-gray-700 px-2 py-0.5 rounded-full inline-block mt-0.5 font-medium">{{ strtoupper($employee->status) }}</div>
                                @endif
                                @if($employee->payroll_id && isset($weekOffConfigurationsByPayrollId) && isset($weekOffConfigurationsByPayrollId[$employee->payroll_id]))
                                    @php
                                        $weekOffConfig = $weekOffConfigurationsByPayrollId[$employee->payroll_id];
                                        $weekOffPattern = $weekOffConfig['week_off_pattern'] ?? 'Custom';
                                    @endphp
                                    <div class="text-[9px] bg-blue-100 text-blue-700 px-2 py-1 rounded-full inline-block mt-0.5" title="Week-off: {{ $weekOffPattern }}">
                                        {{ $weekOffPattern }}
                                    </div>
                                @else
                                    <div class="text-[9px] bg-purple-100 text-purple-700 px-2 py-1 rounded-full inline-block mt-0.5" title="Default weekend (Sat-Sun)">
                                        Sat-Sun
                                    </div>
                                @endif
                            </div>
                        </div>
                    </td>
                    
                    @php
                        $presentCount = 0;
                        $leaveCount = 0;
                        $fixedHolidayCount = 0;
                        $flexibleHolidayCount = 0;
                        $weekendCount = 0;
                        $absentCount = 0;
                        $lopCount = 0;
                        // Use employee payroll_id to get attendance records
                        $userRecords = ($employee->payroll_id && isset($attendanceRecords[$employee->payroll_id])) 
                            ? $attendanceRecords[$employee->payroll_id] 
                            : collect();
                        
                        // Get LOP total for this employee using payroll_id
                        $employeeLopDays = 0;
                        if ($employee->payroll_id && isset($lopTotalsByPayrollId[$employee->payroll_id])) {
                            $employeeLopDays = $lopTotalsByPayrollId[$employee->payroll_id];
                        }
                        
                        // Calculate Salary Days = Total Days in Month - LOP Days (will be updated after counting records)
                        $salaryDays = $daysInMonth - $employeeLopDays;
                        
                        // Get leave applications for this employee to determine LOP days per application
                        $employeeLeaves = [];
                        if ($employee->payroll_id && isset($leaveApplicationsByPayrollId[$employee->payroll_id])) {
                            foreach ($leaveApplicationsByPayrollId[$employee->payroll_id] as $leave) {
                                if ($leave->has_lop && $leave->lop_days > 0) {
                                    $employeeLeaves[] = $leave;
                                }
                            }
                        }
                    @endphp

                    @foreach($calendarDates as $dateInfo)
                        @php
                            $date = $dateInfo['date']->format('Y-m-d');
                            $record = $userRecords->firstWhere(function($r) use ($date) {
                                return $r->date->format('Y-m-d') === $date;
                            });
                            
                            $status = 'present';
                            $foundLeaveApp = null;
                            
                            // Check if it's a week-off day for this specific employee using payroll API data
                            $isWeekOff = false;
                            if ($employee->payroll_id && isset($weekOffConfigurationsByPayrollId) && isset($weekOffConfigurationsByPayrollId[$employee->payroll_id])) {
                                $weekOffConfig = $weekOffConfigurationsByPayrollId[$employee->payroll_id];
                                $dayOfWeek = $dateInfo['date']->dayOfWeek; // 0 = Sunday, 1 = Monday, etc.
                                
                                if (isset($weekOffConfig['week_off_days']) && in_array($dayOfWeek, $weekOffConfig['week_off_days'])) {
                                    $isWeekOff = true;
                                }
                            } else {
                                // Fallback to default weekend if no payroll configuration found
                                $isWeekOff = $dateInfo['is_weekend'];
                            }
                            
                            if ($isWeekOff) {
                                $status = 'weekend';
                            } elseif (isset($dateInfo['is_fixed_holiday']) && $dateInfo['is_fixed_holiday']) {
                                $status = 'fixed_holiday';
                            } elseif (isset($dateInfo['is_flexible_holiday']) && $dateInfo['is_flexible_holiday']) {
                                // Check if this employee has applied for this flexible holiday using email matching
                                $hasFlexibleHolidayApplication = false;
                                if ($employee->email && isset($flexibleHolidayApplications[strtolower($employee->email)])) {
                                    $employeeFlexibleApps = $flexibleHolidayApplications[strtolower($employee->email)];
                                    foreach ($employeeFlexibleApps as $holidayApp) {
                                        if ($holidayApp->publicHoliday && 
                                            $holidayApp->publicHoliday->date->format('Y-m-d') === $dateInfo['date']->format('Y-m-d') &&
                                            $holidayApp->status === 'approved') {
                                            $hasFlexibleHolidayApplication = true;
                                            \Log::info('Found flexible holiday application match', [
                                                'employee_email' => $employee->email,
                                                'date' => $dateInfo['date']->format('Y-m-d'),
                                                'holiday_name' => $holidayApp->publicHoliday->name ?? 'Unknown'
                                            ]);
                                            break;
                                        }
                                    }
                                }
                                if ($hasFlexibleHolidayApplication) {
                                    $status = 'flexible_holiday';
                                }
                            } else {
                                // Check for leave applications using email matching
                                $dateStr = $dateInfo['date']->format('Y-m-d');
                                $currentDate = \Carbon\Carbon::parse($dateStr);
                                
                                if ($employee->email && isset($leaveApplicationsByEmail[strtolower($employee->email)])) {
                                    $employeeLeaves = $leaveApplicationsByEmail[strtolower($employee->email)];
                                    foreach ($employeeLeaves as $leaveApp) {
                                        $leaveStartDate = \Carbon\Carbon::parse($leaveApp->start_date);
                                        $leaveEndDate = \Carbon\Carbon::parse($leaveApp->end_date);
                                        
                                        if ($currentDate->between($leaveStartDate, $leaveEndDate, true)) {
                                            $status = 'absent';
                                            $foundLeaveApp = $leaveApp;
                                            \Log::info('Found leave application match', [
                                                'employee_email' => $employee->email,
                                                'date' => $dateStr,
                                                'leave_type' => $leaveApp->leaveType->name ?? 'Unknown',
                                                'leave_code' => $leaveApp->leaveType->code ?? 'N/A'
                                            ]);
                                            break;
                                        }
                                    }
                                }
                            }
                            
                            // Use database record status only if it exists and is not a week-off related status
                            // or if it's been manually overridden
                            if ($record) {
                                // If the calculated status is 'weekend' (from API), always use it
                                // This ensures week-off days are correctly shown based on API data
                                if ($status !== 'weekend') {
                                    $status = $record->status;
                                }
                                // If the record has been manually overridden, respect that override
                                // unless it's overriding a week-off status with a non-week-off status
                                elseif ($record->is_override && $record->status !== 'weekend') {
                                    $status = $record->status;
                                }
                            }
                            
                            $leaveType = '';
                            $isModified = false;
                            $hasOriginal = false;
                            $recordId = '';
                            
                            $leaveCode = '';
                            $lopInfo = '';
                            $hasLOP = false;
                            if ($record) {
                                $leaveType = $record->leaveType ? $record->leaveType->name : '';
                                $leaveCode = $record->leaveType ? $record->leaveType->code : '';
                                $isModified = $record->is_override;
                                $hasOriginal = !is_null($record->original_status);
                                $recordId = $record->id;
                                // Check for LOP in the record's leave application
                                if ($record->leaveApplication && $record->leaveApplication->has_lop && $record->leaveApplication->lop_days > 0) {
                                    // Calculate if current date is in the last N days of this leave application
                                    $leaveStartDate = \Carbon\Carbon::parse($record->leaveApplication->start_date);
                                    $leaveEndDate = \Carbon\Carbon::parse($record->leaveApplication->end_date);
                                    $currentDate = \Carbon\Carbon::parse($date);
                                    
                                    // Check if current date is within the leave period
                                    if ($currentDate >= $leaveStartDate && $currentDate <= $leaveEndDate) {
                                        // Calculate total days in this leave application
                                        $totalLeaveDays = $leaveStartDate->diffInDays($leaveEndDate) + 1;
                                        
                                        // Calculate which day number this is from the start (1, 2, 3, etc.)
                                        $dayNumberFromStart = $leaveStartDate->diffInDays($currentDate) + 1;
                                        
                                        // LOP days are the LAST N days, so check if this day is in the LOP range
                                        $lopStartDay = $totalLeaveDays - $record->leaveApplication->lop_days + 1;
                                        
                                        if ($dayNumberFromStart >= $lopStartDay) {
                                            $hasLOP = true;
                                            $lopInfo = 'LOP Day (' . ($dayNumberFromStart - $lopStartDay + 1) . ' of ' . number_format($record->leaveApplication->lop_days, 1) . ')';
                                        }
                                    }
                                }
                            } else if ($status === 'absent' && $foundLeaveApp) {
                                $leaveType = $foundLeaveApp->leaveType ? $foundLeaveApp->leaveType->name : 
                                           ($foundLeaveApp->leave_type ?: 'Approved Leave');
                                $leaveCode = $foundLeaveApp->leaveType ? $foundLeaveApp->leaveType->code : 'A';
                                // Check if this date is a LOP day for this specific leave application
                                if ($foundLeaveApp->has_lop && $foundLeaveApp->lop_days > 0) {
                                    // Calculate if current date is in the last N days of this leave application
                                    $leaveStartDate = \Carbon\Carbon::parse($foundLeaveApp->start_date);
                                    $leaveEndDate = \Carbon\Carbon::parse($foundLeaveApp->end_date);
                                    $currentDate = \Carbon\Carbon::parse($date);
                                    
                                    // Check if current date is within the leave period
                                    if ($currentDate >= $leaveStartDate && $currentDate <= $leaveEndDate) {
                                        // Calculate total days in this leave application
                                        $totalLeaveDays = $leaveStartDate->diffInDays($leaveEndDate) + 1;
                                        
                                        // Calculate which day number this is from the start (1, 2, 3, etc.)
                                        $dayNumberFromStart = $leaveStartDate->diffInDays($currentDate) + 1;
                                        
                                        // LOP days are the LAST N days, so check if this day is in the LOP range
                                        $lopStartDay = $totalLeaveDays - $foundLeaveApp->lop_days + 1;
                                        
                                        if ($dayNumberFromStart >= $lopStartDay) {
                                            $hasLOP = true;
                                            $lopInfo = 'LOP Day (' . ($dayNumberFromStart - $lopStartDay + 1) . ' of ' . number_format($foundLeaveApp->lop_days, 1) . ')';
                                        }
                                    }
                                }
                            }
                            
                            if ($status === 'present') $presentCount++;
                            elseif ($status === 'leave') $leaveCount++;
                            elseif ($status === 'fixed_holiday') $fixedHolidayCount++;
                            elseif ($status === 'flexible_holiday') $flexibleHolidayCount++;
                            elseif ($status === 'public_holiday') {
                                // Handle legacy public_holiday status - check holiday type from record
                                if ($record && $record->publicHoliday) {
                                    if ($record->publicHoliday->type === 'fixed') {
                                        $fixedHolidayCount++;
                                    } else {
                                        $flexibleHolidayCount++;
                                    }
                                } else {
                                    $fixedHolidayCount++; // Default to fixed if unknown
                                }
                            }
                            elseif ($status === 'weekend') $weekendCount++;
                            elseif ($status === 'absent') {
                                $absentCount++;
                                // Check if this is unauthorized absence (LOP) - absent without approved leave
                                if ($record && is_null($record->leave_type_id) && is_null($record->public_holiday_id) && !$hasLOP) {
                                    $lopCount++; // Unauthorized absence = LOP
                                }
                            }
                            elseif ($status === 'lop') $lopCount++;
                            elseif ($status === 'late') $presentCount++; // Count late as present for salary
                            elseif ($status === 'half_day') $presentCount += 0.5; // Half day counts as 0.5
                            elseif ($status === 'early_departure') $presentCount++; // Count early departure as present
                        @endphp
                        
                        <td class="text-center p-1 border-r">
                            @php
                                // Determine if this is unauthorized absence (LOP without leave application)
                                $isUnauthorizedLOP = ($status === 'absent' && !$leaveCode && $record && is_null($record->leave_type_id) && is_null($record->public_holiday_id));
                            @endphp
                            <div class="flex justify-center items-center w-8 h-8 mx-auto rounded-md text-xs font-bold {{ isset($isLocked) && $isLocked ? 'cursor-not-allowed opacity-80' : 'cursor-pointer hover:scale-105' }} transition-all shadow-sm
                                @if($status === 'present') bg-green-500 text-white @endif
                                @if($status === 'late') bg-yellow-500 text-white @endif
                                @if($status === 'half_day') bg-blue-500 text-white @endif
                                @if($status === 'early_departure') bg-orange-400 text-white @endif
                                @if($status === 'absent' && $isUnauthorizedLOP) bg-red-800 text-white border-2 border-yellow-500 @endif
                                @if($status === 'absent' && !$isUnauthorizedLOP && $hasLOP) bg-red-600 text-white border-2 border-yellow-400 @endif
                                @if($status === 'absent' && !$isUnauthorizedLOP && !$hasLOP) bg-red-500 text-white @endif
                                @if($status === 'fixed_holiday') bg-orange-500 text-white border border-orange-700 @endif
                                @if($status === 'flexible_holiday') bg-purple-600 text-white border border-purple-800 @endif
                                @if($status === 'public_holiday' && $record && $record->publicHoliday)
                                    @if($record->publicHoliday->type === 'fixed') bg-orange-500 text-white border border-orange-700
                                    @else bg-purple-600 text-white border border-purple-800 @endif
                                @endif
                                @if($status === 'public_holiday' && (!$record || !$record->publicHoliday)) bg-orange-500 text-white border border-orange-700 @endif
                                @if($status === 'weekend') bg-gray-500 text-white @endif
                                @if($status === 'lop') bg-red-800 text-white border-2 border-yellow-500 @endif
                                @if($isModified) relative overflow-hidden @endif"
                                data-payroll-id="{{ $employee->payroll_id }}"
                                data-employee-email="{{ $employee->email }}"
                                data-employee_id="{{ $employee->employee_id }}"
                                data-date="{{ $date }}"
                                data-status="{{ $status }}"
                                data-leave-type="{{ $leaveType }}"
                                data-record-id="{{ $record ? $record->id : '' }}"
                                data-has-original="{{ $hasOriginal ? '1' : '0' }}"
                                data-locked="{{ isset($isLocked) && $isLocked ? '1' : '0' }}"
                                data-has-lop="{{ $hasLOP ? '1' : '0' }}"
                                title="{{ $isUnauthorizedLOP ? 'LOP - Unauthorized Absence (No punch, no approved leave)' : (ucfirst($status) . ($leaveType ? ' - ' . $leaveType : '') . ($lopInfo ? ' - ' . $lopInfo : '') . ($hasLOP && !$isUnauthorizedLOP ? ' - Exceeds leave balance' : '')) }}{{ $isModified ? ' (Modified)' : '' }}{{ isset($isLocked) && $isLocked ? ' (Locked)' : '' }}{{ $status === 'weekend' && $employee->payroll_id && isset($weekOffConfigurationsByPayrollId) && isset($weekOffConfigurationsByPayrollId[$employee->payroll_id]) ? ' (Custom: ' . ($weekOffConfigurationsByPayrollId[$employee->payroll_id]['week_off_pattern'] ?? 'Custom') . ')' : '' }}">
                                @if($status === 'present') P @endif
                                @if($status === 'late') L @endif
                                @if($status === 'half_day') HD @endif
                                @if($status === 'early_departure') ED @endif
                                @if($status === 'absent' && $isUnauthorizedLOP) LOP @endif
                                @if($status === 'absent' && !$isUnauthorizedLOP) {{ $leaveCode ?: 'A' }} @endif
                                @if($status === 'fixed_holiday') PH @endif
                                @if($status === 'flexible_holiday') FH @endif
                                @if($status === 'public_holiday' && $record && $record->publicHoliday)
                                    @if($record->publicHoliday->type === 'fixed') PH @else FH @endif
                                @endif
                                @if($status === 'public_holiday' && (!$record || !$record->publicHoliday)) PH @endif
                                @if($status === 'weekend') W @endif
                                @if($status === 'lop') LOP @endif
                                @if($isModified)
                                <span class="absolute top-0 right-0 w-0 h-0 border-l-[8px] border-l-transparent border-b-[8px] border-b-blue-500"></span>
                                @endif
                                @if($hasLOP && !$isUnauthorizedLOP)
                                <span class="absolute -top-1 -right-1 w-3 h-3 bg-yellow-400 rounded-full border border-yellow-600 text-[8px] flex items-center justify-center font-bold" title="{{ $lopInfo }}">!</span>
                                @endif
                            </div>
                        </td>
                    @endforeach
                    
                    @php
                        // Calculate total LOP days = LOP from leave applications + LOP status records
                        $totalLopDays = $employeeLopDays + $lopCount;
                        // Recalculate salary days with both sources of LOP
                        $salaryDays = $daysInMonth - $totalLopDays;
                    @endphp
                    
                    <td class="text-center py-1 px-2 bg-green-50 border-r">
                        <span class="text-xs font-bold text-green-700 bg-green-100 rounded-full py-1 px-2 shadow-md border border-green-200 inline-flex items-center justify-center min-w-[1.8rem]">{{ $presentCount }}</span>
                    </td>
                    <td class="text-center py-1 px-2 bg-red-50 border-r">
                        <span class="text-xs font-bold text-red-700 bg-red-100 rounded-full py-1 px-2 shadow-md border border-red-200 inline-flex items-center justify-center min-w-[1.8rem]">{{ $absentCount }}</span>
                    </td>
                    <td class="text-center py-1 px-2 bg-orange-50 border-r">
                        <span class="text-xs font-bold text-orange-700 bg-orange-100 rounded-full py-1 px-2 shadow-md border border-orange-200 inline-flex items-center justify-center min-w-[1.8rem]">{{ $fixedHolidayCount }}</span>
                    </td>
                    <td class="text-center py-1 px-2 bg-purple-50 border-r">
                        <span class="text-xs font-bold text-purple-700 bg-purple-100 rounded-full py-1 px-2 shadow-md border border-purple-200 inline-flex items-center justify-center min-w-[1.8rem]">{{ $flexibleHolidayCount }}</span>
                    </td>
                    <td class="text-center py-1 px-2 bg-gray-50 border-r">
                        <span class="text-xs font-bold text-gray-700 bg-gray-100 rounded-full py-1 px-2 shadow-md border border-gray-200 inline-flex items-center justify-center min-w-[1.8rem]">{{ $weekendCount }}</span>
                    </td>
                    <td class="text-center py-1 px-2 bg-red-50 border-r">
                        @if($totalLopDays > 0)
                        <span class="text-xs font-bold text-red-800 bg-red-200 rounded-full py-1 px-2 shadow-md border border-red-300 inline-flex items-center justify-center min-w-[1.8rem]" title="Total LOP: {{ number_format($employeeLopDays, 1) }} from leaves + {{ $lopCount }} from status = {{ number_format($totalLopDays, 1) }} days">{{ number_format($totalLopDays, 1) }}</span>
                        @else
                        <span class="text-xs font-bold text-gray-500 bg-gray-100 rounded-full py-1 px-2 shadow-md border border-gray-200 inline-flex items-center justify-center min-w-[1.8rem]">0</span>
                        @endif
                    </td>
                    <td class="text-center py-1 px-2 bg-blue-50">
                        <span class="text-xs font-bold text-blue-800 bg-blue-200 rounded-full py-1 px-2 shadow-md border border-blue-300 inline-flex items-center justify-center min-w-[2rem]" title="Salary Days: {{ $daysInMonth }} total days - {{ number_format($totalLopDays, 1) }} total LOP days = {{ number_format($salaryDays, 1) }} salary days">{{ number_format($salaryDays, 1) }}</span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Enhanced Pagination -->
    @if(isset($employees) && $employees->count() > 0)
    <div class="px-4 mt-5">
        <div class="bg-white rounded-lg shadow-sm px-5 py-4 border border-gray-200">
            {{ $employees->appends(['month' => $month, 'year' => $year])->links() }}
        </div>
    </div>
    @endif

    <!-- Modern Action Buttons with Better Padding -->
    <div class="px-4 mt-8 mb-10">
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-6">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div class="text-sm text-gray-600 bg-blue-50 px-4 py-3 rounded-lg">
                    <span class="inline-flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span class="font-medium">Click on any attendance cell to edit it</span>
                    </span>
                </div>
                <div class="flex flex-wrap gap-4">
                    @if(isset($isLocked) && !$isLocked)
                    <button id="btn-save" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-all shadow-md flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Save Changes
                    </button>
                    <button id="btn-lock" class="px-6 py-3 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-all shadow-md flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                        Save & Lock
                    </button>
                    @elseif(isset($isLocked) && $isLocked)
                    <span class="inline-flex items-center px-5 py-3 rounded-lg text-sm font-medium bg-red-100 text-red-800 border border-red-200">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path>
                        </svg>
                        Locked for Payroll
                    </span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Attendance Modal -->
<div id="edit-modal" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm hidden z-50">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-sm mx-auto overflow-hidden transition-all transform scale-95 opacity-0 modal-content">
            <!-- Modal Header -->
            <div class="bg-gradient-to-r from-blue-600 to-indigo-700 text-white px-5 py-4">
                <div class="flex justify-between items-center">
                    <h3 class="text-sm font-semibold" id="modal-employee-name"></h3>
                    <button type="button" id="modal-close" class="text-white/80 hover:text-white focus:outline-none transition-all">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
            
            <!-- Modal Body -->
            <div class="px-5 py-4">
                <div class="mb-4 bg-blue-50 p-3 rounded-lg">
                    <div class="flex items-center mb-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-600 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span class="text-xs font-medium text-blue-800" id="modal-date"></span>
                    </div>
                    <div class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-600 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="text-xs font-medium text-blue-800">Current: <span id="modal-current-status"></span></span>
                    </div>
                </div>
                
                <form id="attendance-form" class="space-y-3">
                    <input type="hidden" id="modal-payroll-id" name="payroll_id">
                    <input type="hidden" id="modal-employee-email" name="employee_email">
                    <input type="hidden" id="modal-employee-id" name="employee_id">
                    <input type="hidden" id="modal-date-value" name="date">
                    <input type="hidden" id="modal-record-id" name="record_id">
                    
                    <div>
                        <label for="modal-status" class="block text-xs font-medium text-gray-700 mb-1">Change Status To:</label>
                        <select id="modal-status" name="status" class="block w-full text-xs py-2 px-3 border border-gray-300 bg-white rounded-lg shadow-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                            <option value="present">Present (P)</option>
                            <option value="absent">Leave/Absent (L)</option>
                            <option value="fixed_holiday">Fixed Public Holiday (PH)</option>
                            <option value="flexible_holiday">Flexible Public Holiday (FH)</option>
                            <option value="weekend">Week-off (W)</option>
                            <option value="lop">Loss of Pay (LOP)</option>
                        </select>
                    </div>
                    
                    <div id="leave-type-container" class="hidden">
                        <label for="modal-leave-type" class="block text-xs font-medium text-gray-700 mb-1">Leave Type:</label>
                        <select id="modal-leave-type" name="leave_type_id" class="block w-full text-xs py-2 px-3 border border-gray-300 bg-white rounded-lg shadow-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                            @foreach($leaveTypes as $leaveType)
                                <option value="{{ $leaveType->id }}">{{ $leaveType->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>
            
            <!-- Modal Footer -->
            <div class="px-5 py-4 bg-gray-50 flex justify-between">
                <button type="button" id="btn-revert" class="items-center py-2 px-4 border border-gray-300 rounded-lg text-xs font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-1 focus:ring-blue-500 hidden">
                    Revert to Original
                </button>
                
                <button type="button" id="update-attendance" class="py-2 px-4 border border-transparent rounded-lg text-xs font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-1 focus:ring-blue-500">
                    Update
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Confirmation Modal -->
<div id="confirm-modal" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm hidden z-50">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md mx-auto overflow-hidden transition-all transform scale-95 opacity-0 modal-content">
            <!-- Modal Header -->
            <div class="bg-gradient-to-r from-blue-600 to-indigo-700 text-white px-5 py-4">
                <div class="flex justify-between items-center">
                    <h3 class="text-sm font-semibold" id="confirm-title">Confirm Action</h3>
                    <button type="button" id="confirm-close" class="text-white/80 hover:text-white focus:outline-none transition-all">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
            
            <!-- Modal Body -->
            <div class="px-5 py-4">
                <p class="text-sm text-gray-600" id="confirm-message"></p>
            </div>
            
            <!-- Modal Footer -->
            <div class="px-5 py-4 bg-gray-50 flex justify-end gap-2">
                <button type="button" id="confirm-cancel" class="py-2 px-4 border border-gray-300 rounded-lg text-xs font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-1 focus:ring-blue-500">
                    Cancel
                </button>
                <button type="button" id="confirm-ok" class="py-2 px-4 border border-transparent rounded-lg text-xs font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-1 focus:ring-blue-500">
                    Confirm
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Forms for bulk actions -->
<form id="save-form" action="{{ route('admin.attendance.save') }}" method="POST" class="hidden">
    @csrf
    <input type="hidden" name="month" value="{{ $month }}">
    <input type="hidden" name="year" value="{{ $year }}">
</form>

<form id="lock-form" action="{{ route('admin.attendance.lock') }}" method="POST" class="hidden">
    @csrf
    <input type="hidden" name="month" value="{{ $month }}">
    <input type="hidden" name="year" value="{{ $year }}">
</form>

<form id="regenerate-form" action="{{ route('admin.attendance.regenerate') }}" method="POST" class="hidden">
    @csrf
    <input type="hidden" name="month" value="{{ $month }}">
    <input type="hidden" name="year" value="{{ $year }}">
</form>

<!-- Loading Indicator -->
<div class="loader fixed inset-0 bg-black/40 backdrop-blur-sm z-[60] hidden">
    <div class="flex items-center justify-center h-full">
        <div class="inline-block h-12 w-12 animate-spin rounded-full border-4 border-solid border-white border-r-transparent align-[-0.125em]"></div>
    </div>
</div>

<!-- Include your JavaScript here -->
    </div>
    
    @stack('scripts')
</body>
</html>

<!-- jQuery for better cross-browser compatibility -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="{{ asset('js/bulk-attendance-progress.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM fully loaded');
        
        // Add a direct check for isLocked
        @if(isset($isLocked))
        console.log('isLocked from server: {{ $isLocked ? "true" : "false" }}');
        @else
        console.log('isLocked is not set in the view');
        @endif
        
        const editModal = document.getElementById('edit-modal');
        const confirmModal = document.getElementById('confirm-modal');
        console.log('Modals found:', {
            editModal: editModal ? true : false, 
            confirmModal: confirmModal ? true : false
        });
        
        if (!editModal) {
            console.error('Edit modal element not found in the DOM!');
        }
        
        const attendanceCells = document.querySelectorAll('[data-payroll-id]');
        console.log('Found attendance cells:', attendanceCells.length);
        
        // Get a sample cell to check data attributes
        if (attendanceCells.length > 0) {
            const sampleCell = attendanceCells[0];
            console.log('Sample cell data attributes:', {
                payrollId: sampleCell.getAttribute('data-payroll-id'),
                employeeEmail: sampleCell.getAttribute('data-employee-email'),
                employeeId: sampleCell.getAttribute('data-employee-id'),
                date: sampleCell.getAttribute('data-date'),
                status: sampleCell.getAttribute('data-status'),
                locked: sampleCell.getAttribute('data-locked'),
                className: sampleCell.className,
                tagName: sampleCell.tagName
            });
        }
        
        // Test if jQuery is working and can find cells
        const testCells = $('[data-payroll-id][data-date]');
        console.log('jQuery found cells with data-payroll-id and data-date:', testCells.length);
        
        // Add a visual indicator to first cell for testing
        if (testCells.length > 0) {
            console.log('Adding red border to first cell for testing');
            testCells.first().css('border', '2px solid red');
        }
        
        const modalEmployeeName = document.getElementById('modal-employee-name');
        const modalDate = document.getElementById('modal-date');
        const modalCurrentStatus = document.getElementById('modal-current-status');
        const modalEmployeeEmail = document.getElementById('modal-employee-email');
        const modalEmployeeId = document.getElementById('modal-employee-id');
        const modalDateValue = document.getElementById('modal-date-value');
        const modalRecordId = document.getElementById('modal-record-id');
        const modalStatus = document.getElementById('modal-status');
        const modalLeaveType = document.getElementById('modal-leave-type');
        const leaveTypeContainer = document.getElementById('leave-type-container');
        const btnRevert = document.getElementById('btn-revert');
        const attendanceForm = document.getElementById('attendance-form');
        const loader = document.querySelector('.loader');
        
        console.log('Modal elements found:', {
            modalEmployeeName: modalEmployeeName ? true : false,
            modalDate: modalDate ? true : false,
            modalCurrentStatus: modalCurrentStatus ? true : false,
            modalEmployeeEmail: modalEmployeeEmail ? true : false,
            modalEmployeeId: modalEmployeeId ? true : false,
            modalDateValue: modalDateValue ? true : false,
            modalRecordId: modalRecordId ? true : false,
            modalStatus: modalStatus ? true : false,
            modalLeaveType: modalLeaveType ? true : false,
            leaveTypeContainer: leaveTypeContainer ? true : false,
            btnRevert: btnRevert ? true : false,
            attendanceForm: attendanceForm ? true : false,
            loader: loader ? true : false
        });
        
        // Handle modal button events - using jQuery
        $('#update-attendance').on('click', function() {
            $('#attendance-form').trigger('submit');
        });
        
        // Handle save button - using new progress tracker
        $('#btn-save').on('click', function(e) {
            e.preventDefault();
            if (window.bulkAttendanceTracker) {
                window.bulkAttendanceTracker.handleSave();
            }
        });
        
        // Handle lock button - using new progress tracker
        $('#btn-lock').on('click', function(e) {
            e.preventDefault();
            if (window.bulkAttendanceTracker) {
                window.bulkAttendanceTracker.handleLock();
            }
        });
        
        // Initialize progress tracker
        $(document).ready(function() {
            if (typeof BulkAttendanceProgressTracker !== 'undefined') {
                window.bulkAttendanceTracker = new BulkAttendanceProgressTracker();
            }
        });
        
        // Handle regenerate button - using jQuery
        $('#btn-regenerate').on('click', function() {
            showConfirmModal(
                'Regenerate Attendance Records',
                'Are you sure you want to regenerate all attendance records for this month? This will reset all manual changes.',
                function() {
                    $('#regenerate-form').submit();
                }
            );
        });
        
        // Close modal buttons - using jQuery
        $('#modal-close').on('click', function() {
            closeModal('#edit-modal');
        });
        
        $('#confirm-cancel').on('click', function() {
            closeModal('#confirm-modal');
        });
        
        $('#confirm-close').on('click', function() {
            closeModal('#confirm-modal');
        });
        
        // Show modal with animation - using jQuery
        function showModal(modalId) {
            console.log('showModal called for', modalId);
            $(modalId).removeClass('hidden');
            console.log('Modal hidden class removed');
            
            setTimeout(() => {
                console.log('Inside setTimeout callback');
                const $modalContent = $(modalId).find('.modal-content');
                console.log('Modal content element:', $modalContent.length ? 'found' : 'not found');
                
                if ($modalContent.length) {
                    $modalContent.removeClass('scale-95 opacity-0').addClass('scale-100 opacity-100');
                    console.log('Modal animation classes updated');
                } else {
                    console.error('Modal content element not found');
                }
            }, 10);
        }
        
        // Close modal with animation - using jQuery
        function closeModal(modalId) {
            const $modalContent = $(modalId).find('.modal-content');
            if ($modalContent.length) {
                $modalContent.removeClass('scale-100 opacity-100').addClass('scale-95 opacity-0');
                setTimeout(() => {
                    $(modalId).addClass('hidden');
                }, 150);
            } else {
                console.error('Modal content element not found when closing');
                $(modalId).addClass('hidden');
            }
        }
        
        // Show confirmation modal - using jQuery
        function showConfirmModal(title, message, onConfirm) {
            $('#confirm-title').text(title);
            $('#confirm-message').text(message);
            
            // Remove any existing click handlers and add new one
            $('#confirm-ok').off('click').on('click', function() {
                onConfirm();
                closeModal('#confirm-modal');
            });
            
            showModal('#confirm-modal');
        }
        
        // Show success toast notification
        function showToast(message, type = 'success') {
            // Create toast element
            const toast = document.createElement('div');
            toast.className = `fixed bottom-4 right-4 flex items-center p-4 w-full max-w-xs rounded-lg shadow-lg z-50 transition-all duration-300 ${type === 'success' ? 'bg-green-500' : 'bg-red-500'}`;
            toast.innerHTML = `
                <div class="inline-flex flex-shrink-0 justify-center items-center w-8 h-8 rounded-lg ${type === 'success' ? 'bg-green-600' : 'bg-red-600'}">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        ${type === 'success' ? 
                            '<path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>' : 
                            '<path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>'}
                    </svg>
                </div>
                <div class="ml-3 text-sm font-normal text-white">${message}</div>
                <button type="button" class="ml-auto -mx-1.5 -my-1.5 text-white hover:text-gray-200 focus:outline-none">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                </button>
            `;
            
            // Add toast to DOM
            document.body.appendChild(toast);
            
            // Remove toast after 3 seconds
            setTimeout(() => {
                toast.classList.add('opacity-0', 'translate-y-2');
                setTimeout(() => {
                    document.body.removeChild(toast);
                }, 300);
            }, 3000);
            
            // Close on click
            toast.querySelector('button').addEventListener('click', () => {
                toast.classList.add('opacity-0', 'translate-y-2');
                setTimeout(() => {
                    document.body.removeChild(toast);
                }, 300);
            });
        }
        
        // Show edit modal when clicking on a cell - using jQuery for better compatibility  
        $(document).on('click', '[data-payroll-id][data-date]', function(e) {
            // Stop event propagation to prevent conflicts
            e.stopPropagation();
            
            // Debug to check if click event is firing
            console.log('Cell clicked', this);
            console.log('Element classes:', this.className);
            console.log('Element tag:', this.tagName);
            
            // Check if the attendance is locked using data attribute
            const isLocked = $(this).data('locked') === 1;
            if (isLocked) {
                console.log('Attendance is locked, edit not allowed');
                return; // Don't allow edits if locked
            }
                
            // Get attributes with jQuery
            const payrollId = $(this).data('payroll-id');
            const employeeEmail = $(this).data('employee-email');
            const employeeId = $(this).data('employee-id');
            const date = $(this).data('date');
            const status = $(this).data('status');
            const leaveType = $(this).data('leave-type');
            const recordId = $(this).data('record-id');
            const hasOriginal = $(this).data('has-original') === 1;
                
            // Debug all data attributes
            console.log('Cell data:', {
                payrollId, employeeEmail, employeeId, date, status, leaveType, recordId, hasOriginal
            });
                
            // Find user name from parent row - using jQuery
            const row = $(this).closest('tr');
            const userName = row.find('td:first-child .font-semibold').text().trim();
                
                console.log('Modal data:', {
                    userName, employeeEmail, employeeId, date, status, leaveType, recordId, hasOriginal
                });
                
                // Set modal values with jQuery
                $('#modal-employee-name').text(userName);
                $('#modal-date').text(formatDate(date));
                $('#modal-current-status').text(formatStatus(status) + (leaveType ? ' - ' + leaveType : ''));
                $('#modal-payroll-id').val(payrollId);
                $('#modal-employee-email').val(employeeEmail);
                $('#modal-employee-id').val(employeeId);
                $('#modal-date-value').val(date);
                $('#modal-record-id').val(recordId);
                $('#modal-status').val(status);
                
                // jQuery toggle leave type container visibility initially
                $('#leave-type-container').toggleClass('hidden', status !== 'absent');
                
                // jQuery show/hide revert button
                if (hasOriginal) {
                    $('#btn-revert').removeClass('hidden').addClass('inline-flex');
                } else {
                    $('#btn-revert').addClass('hidden').removeClass('inline-flex');
                }
                
                // Show the modal with animation using jQuery
                console.log('About to show modal with jQuery');
                try {
                    $('#edit-modal').removeClass('hidden');
                    setTimeout(() => {
                        $('.modal-content').removeClass('scale-95 opacity-0').addClass('scale-100 opacity-100');
                        console.log('Modal animation applied');
                    }, 10);
                } catch (error) {
                    console.error('Error showing modal with jQuery:', error);
                }
            });
            
        // Handle status change with jQuery
        $('#modal-status').on('change', function() {
            const status = $(this).val();
            // Show leave type container for both 'absent' and 'lop' statuses
            $('#leave-type-container').toggleClass('hidden', status !== 'absent' && status !== 'lop');
        });        // Function to toggle leave type container
        function toggleLeaveTypeContainer() {
            leaveTypeContainer.classList.toggle('hidden', this.value !== 'absent');
        }
        
        // Handle form submission - using jQuery
        $('#attendance-form').on('submit', function(e) {
            e.preventDefault();
            
            const payrollId = $('#modal-payroll-id').val();
            const employeeEmail = $('#modal-employee-email').val();
            const employeeId = $('#modal-employee-id').val();
            const date = $('#modal-date-value').val();
            const status = $('#modal-status').val();
            const leaveTypeId = (status === 'absent' || status === 'lop') ? $('#modal-leave-type').val() : null;
            
            // Show loading indicator - using jQuery
            $('.loader').removeClass('hidden');
            
            // Debug output to console
            const recordId = $('#modal-record-id').val();
            console.log('Sending update request:', {
                payroll_id: payrollId,
                employee_email: employeeEmail,
                employee_id: employeeId,
                date: date,
                status: status,
                leave_type_id: leaveTypeId,
                record_id: recordId
            });
            
            // Using jQuery AJAX for better compatibility
            $.ajax({
                url: '{{ route('admin.attendance.update-record') }}',
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                contentType: 'application/json',
                data: JSON.stringify({
                    payroll_id: payrollId,
                    employee_email: employeeEmail,
                    employee_id: employeeId,
                    date: date,
                    status: status,
                    leave_type_id: leaveTypeId,
                    record_id: recordId
                }),
                success: function(data) {
                    $('.loader').addClass('hidden');
                
                    if (data.success) {
                        console.log('Update successful:', data);
                        // Update the cell - using jQuery (use payroll_id instead of email)
                        const $cell = $(`[data-payroll-id="${payrollId}"][data-date="${date}"]`);
                        if ($cell.length) {
                            $cell.attr('data-status', status);
                            $cell.attr('data-leave-type', data.record.leave_type || '');
                            $cell.attr('data-record-id', data.record.id);
                            $cell.attr('data-has-original', data.record.has_original ? '1' : '0');
                            $cell.attr('data-has-lop', (status === 'lop' || data.record.has_lop) ? '1' : '0');
                            $cell.attr('title', formatStatus(status) + 
                                        (data.record.leave_type ? ' - ' + data.record.leave_type : '') + 
                                        (data.record.is_override ? ' (Modified)' : '') +
                                        ((status === 'lop' || data.record.has_lop) ? ' (LOP)' : ''));
                        
                            // Determine background classes based on status
                            let bgClasses = 'flex justify-center items-center w-8 h-8 mx-auto rounded-md text-xs font-bold cursor-pointer hover:scale-105 transition-all shadow-sm';
                            
                            if (status === 'present') {
                                bgClasses += ' bg-green-500 text-white';
                            } else if (status === 'absent' && (status === 'lop' || data.record.has_lop)) {
                                bgClasses += ' bg-red-700 text-white border-2 border-yellow-400';
                            } else if (status === 'lop') {
                                bgClasses += ' bg-red-700 text-white border-2 border-yellow-400';
                            } else if (status === 'absent') {
                                bgClasses += ' bg-red-500 text-white';
                            } else if (status === 'fixed_holiday') {
                                bgClasses += ' bg-orange-500 text-white border border-orange-700';
                            } else if (status === 'flexible_holiday') {
                                bgClasses += ' bg-purple-600 text-white border border-purple-800';
                            } else if (status === 'weekend') {
                                bgClasses += ' bg-gray-500 text-white';
                            }
                            
                            if (data.record.is_override) {
                                bgClasses += ' relative overflow-hidden';
                            }
                            
                            // Clear existing classes and set new ones
                            $cell.attr('class', bgClasses);
                            
                            // Update cell content
                            let cellContent = '';
                            if (status === 'present') {
                                cellContent = 'P';
                            } else if (status === 'lop') {
                                cellContent = data.record.leave_code || 'L';
                            } else if (status === 'absent') {
                                cellContent = data.record.leave_code || 'L';
                            } else if (status === 'fixed_holiday') {
                                cellContent = 'PH';
                            } else if (status === 'flexible_holiday') {
                                cellContent = 'FH';
                            } else if (status === 'weekend') {
                                cellContent = 'W';
                            }
                            
                            // Add modification indicator
                            if (data.record.is_override) {
                                cellContent += '<span class="absolute top-0 right-0 w-0 h-0 border-l-[8px] border-l-transparent border-b-[8px] border-b-blue-500"></span>';
                            }
                            
                            // Add LOP indicator
                            if (status === 'lop' || data.record.has_lop) {
                                cellContent += '<span class="absolute -top-1 -right-1 w-3 h-3 bg-yellow-400 rounded-full border border-yellow-600 text-xs flex items-center justify-center">!</span>';
                            }
                            
                            $cell.html(cellContent);
                            
                            // Recalculate counts for this employee row
                            recalculateRowCounts(payrollId);
                        }
                        
                        // Close the modal
                        closeModal('#edit-modal');
                        
                        // Show success toast
                        showToast('Attendance record updated successfully', 'success');
                    } else {
                        showToast(data.message || 'Error updating record', 'error');
                    }
                },
                error: function(xhr, status, error) {
                    $('.loader').addClass('hidden');
                    console.error('AJAX error:', xhr, status, error);
                    showToast('An error occurred. Please try again.', 'error');
                }
            });
        });
        
        // Handle revert button - using jQuery
        $('#btn-revert').on('click', function() {
            const recordId = $('#modal-record-id').val();
            
            // Show loading indicator
            $('.loader').removeClass('hidden');
            
            // Using jQuery AJAX for better compatibility
            $.ajax({
                url: '{{ route('admin.attendance.revert-record') }}',
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                contentType: 'application/json',
                data: JSON.stringify({
                    record_id: recordId
                }),
                success: function(data) {
                    $('.loader').addClass('hidden');
                
                    if (data.success) {
                        console.log('Revert successful:', data);
                        // Update the cell - using jQuery (use payroll_id instead of email)
                        const payrollId = $('#modal-payroll-id').val();
                        const date = $('#modal-date-value').val();
                        const $cell = $(`[data-payroll-id="${payrollId}"][data-date="${date}"]`);
                        
                        if ($cell.length) {
                            $cell.attr('data-status', data.record.status);
                            $cell.attr('data-leave-type', data.record.leave_type || '');
                            $cell.attr('data-has-original', '0');
                            $cell.attr('title', formatStatus(data.record.status) + 
                                        (data.record.leave_type ? ' - ' + data.record.leave_type : ''));
                        
                            // Update cell classes using jQuery
                            $cell.removeClass().addClass(`flex justify-center items-center w-8 h-8 mx-auto rounded-md text-xs font-bold cursor-pointer hover:scale-105 transition-all shadow-sm
                                ${data.record.status === 'present' ? 'bg-green-500 text-white' : ''}
                                ${data.record.status === 'absent' ? 'bg-red-500 text-white' : ''}
                                ${data.record.status === 'fixed_holiday' ? 'bg-orange-500 text-white border border-orange-700' : ''}
                                ${data.record.status === 'flexible_holiday' ? 'bg-purple-600 text-white border border-purple-800' : ''}
                                ${data.record.status === 'weekend' ? 'bg-gray-500 text-white' : ''}
                                ${data.record.status === 'leave' ? 'bg-red-500 text-white' : ''}`);
                            
                            // Update cell content
                            let cellContent = '';
                            if (data.record.status === 'present') {
                                cellContent = 'P';
                            } else if (data.record.status === 'absent') {
                                // For JavaScript updates, use generic 'A' since we don't have leave code context here
                                cellContent = 'A';
                            } else if (data.record.status === 'fixed_holiday') {
                                cellContent = 'PH';
                            } else if (data.record.status === 'flexible_holiday') {
                                cellContent = 'FH';
                            } else if (data.record.status === 'weekend') {
                                cellContent = 'W';
                            } else if (data.record.status === 'leave') { // For backward compatibility
                                cellContent = 'A';
                            }
                            
                            $cell.html(cellContent);
                        }
                        
                        // Recalculate counts for this employee row
                        recalculateRowCounts(payrollId);
                        
                        // Close the modal
                        closeModal('#edit-modal');
                        
                        // Show success toast
                        showToast('Attendance record reverted successfully', 'success');
                    } else {
                        showToast(data.message || 'Error reverting record', 'error');
                    }
                },
                error: function(xhr, status, error) {
                    $('.loader').addClass('hidden');
                    console.error('AJAX error:', xhr, status, error);
                    showToast('An error occurred. Please try again.', 'error');
                }
            });
        });
        
        // Helper function to format date
        function formatDate(dateString) {
            const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            return new Date(dateString).toLocaleDateString(undefined, options);
        }
        
        // Helper function to format status
        function formatStatus(status) {
            const statusMap = {
                'present': 'Present',
                'absent': 'Absent',
                'lop': 'Loss of Pay',
                'fixed_holiday': 'Fixed Holiday',
                'flexible_holiday': 'Flexible Holiday',
                'weekend': 'Weekend',
                'leave': 'Leave'
            };
            
            return statusMap[status] || status.charAt(0).toUpperCase() + status.slice(1).replace('_', ' ');
        }
        
        // Function to recalculate counts for a specific employee row
        function recalculateRowCounts(payrollId) {
            const $row = $(`[data-payroll-id="${payrollId}"]`).closest('tr');
            const $cells = $row.find('[data-payroll-id]');
            
            let presentCount = 0;
            let leaveCount = 0;
            let fixedHolidayCount = 0;
            let flexibleHolidayCount = 0;
            let weekendCount = 0;
            let lopCount = 0;
            
            // Count each status type
            $cells.each(function() {
                const status = $(this).attr('data-status');
                const hasLOP = $(this).attr('data-has-lop') === '1';
                
                switch(status) {
                    case 'present':
                        presentCount++;
                        break;
                    case 'absent':
                        if (hasLOP) {
                            lopCount++;
                        } else {
                            leaveCount++;
                        }
                        break;
                    case 'lop':
                        lopCount++;
                        break;
                    case 'fixed_holiday':
                        fixedHolidayCount++;
                        break;
                    case 'flexible_holiday':
                        flexibleHolidayCount++;
                        break;
                    case 'weekend':
                        weekendCount++;
                        break;
                }
            });
            
            // Get original LOP days from leave applications (embedded in row data)
            const originalLopDays = parseFloat($row.data('employee-lop-days') || 0);
            
            // Calculate total LOP days = original LOP from leaves + LOP status count
            const totalLopDays = originalLopDays + lopCount;
            
            // Calculate salary days
            const totalDaysInMonth = {{ $daysInMonth }};
            const salaryDays = totalDaysInMonth - totalLopDays;
            
            // Update the count cells in the row
            $row.find('td:nth-last-child(7) span').text(presentCount); // P column
            $row.find('td:nth-last-child(6) span').text(leaveCount); // L column
            $row.find('td:nth-last-child(5) span').text(fixedHolidayCount); // PH column
            $row.find('td:nth-last-child(4) span').text(flexibleHolidayCount); // FH column
            $row.find('td:nth-last-child(3) span').text(weekendCount); // W column
            $row.find('td:nth-last-child(2) span').text(totalLopDays.toFixed(1)); // LOP column
            $row.find('td:nth-last-child(1) span').text(salaryDays.toFixed(1)); // Salary Days column
            
            // Update titles for LOP and Salary Days columns
            $row.find('td:nth-last-child(2) span').attr('title', lopCount.toFixed(1) + ' LOP days this month');
            $row.find('td:nth-last-child(1) span').attr('title', 'Salary Days: ' + totalDaysInMonth + ' total days - ' + lopCount.toFixed(1) + ' LOP days = ' + salaryDays.toFixed(1) + ' salary days');
        }
    });
</script>