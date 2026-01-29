@extends('layouts.app')

@section('title', 'Leave Management - HRMS')
@section('page-title', 'Leave Management')

@section('content')
<div class="p-6 space-y-6">
        <!-- Success Message -->
        @if(session('success'))
            <div class="bg-green-50 border-l-4 border-green-400 p-4 rounded-r-md shadow-sm">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-check-circle text-green-400 text-lg"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Error Message -->
        @if(session('error'))
            <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded-r-md shadow-sm">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-circle text-red-400 text-lg"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Header Card -->
        <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-200">
            <div class="bg-gradient-to-r from-blue-600 to-indigo-700 px-8 py-10">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                                <i class="fas fa-calendar-alt text-white text-xl"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold text-white">Leave Management</h1>
                            <p class="text-blue-100 text-xs sm:text-sm lg:text-base mt-2">
                                @if(auth()->user()->isStaff())
                                    Manage your leave requests and track their status
                                @else
                                    Review and manage all employee leave applications
                                @endif
                            </p>
                        </div>
                    </div>
                    <div class="hidden md:flex items-center">
                        <div class="w-16 h-16 bg-white bg-opacity-10 rounded-full flex items-center justify-center">
                            <i class="fas fa-users text-white text-2xl"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="flex flex-wrap gap-4 justify-center lg:justify-start">
            <div class="flex-1 min-w-48 max-w-md bg-white rounded-xl shadow hover:shadow-lg transition-shadow duration-300 p-6 border-l-4 border-indigo-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 font-medium text-xs sm:text-sm">Total Applications</p>
                        <p class="text-2xl sm:text-3xl font-bold text-indigo-700 mt-1">{{ $leaves->count() }}</p>
                    </div>
                    <div class="w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-file-alt text-indigo-500 text-xl"></i>
                    </div>
                </div>
            </div>
            <div class="flex-1 min-w-48 max-w-md bg-white rounded-xl shadow hover:shadow-lg transition-shadow duration-300 p-6 border-l-4 border-yellow-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 font-medium text-xs sm:text-sm">Pending</p>
                        <p class="text-2xl sm:text-3xl font-bold text-yellow-600 mt-1">{{ $leaves->where('status', 'pending')->count() }}</p>
                    </div>
                    <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-clock text-yellow-500 text-xl"></i>
                    </div>
                </div>
            </div>
            <div class="flex-1 min-w-48 max-w-md bg-white rounded-xl shadow hover:shadow-lg transition-shadow duration-300 p-6 border-l-4 border-purple-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 font-medium text-xs sm:text-sm">Forwarded</p>
                        <p class="text-2xl sm:text-3xl font-bold text-purple-600 mt-1">{{ $leaves->where('status', 'forwarded_to_manager')->count() }}</p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-share text-purple-500 text-xl"></i>
                    </div>
                </div>
            </div>
            <div class="flex-1 min-w-48 max-w-md bg-white rounded-xl shadow hover:shadow-lg transition-shadow duration-300 p-6 border-l-4 border-green-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 font-medium text-xs sm:text-sm">Approved</p>
                        <p class="text-2xl sm:text-3xl font-bold text-green-600 mt-1">{{ $leaves->where('status', 'approved')->count() }}</p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-check-circle text-green-500 text-xl"></i>
                    </div>
                </div>
            </div>
            <div class="flex-1 min-w-48 max-w-md bg-white rounded-xl shadow hover:shadow-lg transition-shadow duration-300 p-6 border-l-4 border-red-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 font-medium text-xs sm:text-sm">Rejected</p>
                        <p class="text-2xl sm:text-3xl font-bold text-red-600 mt-1">{{ $leaves->where('status', 'rejected')->count() }}</p>
                    </div>
                    <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-times-circle text-red-500 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search & Filter -->
        <div class="bg-white rounded-2xl shadow p-6 border border-gray-100">
            <div class="flex flex-col md:flex-row md:items-center gap-4">
                <div class="flex-1 md:flex-2 relative">
                    <input type="text" id="searchInput" placeholder="Search applications..." 
                           class="w-full pl-10 pr-4 py-2 sm:py-3 text-sm sm:text-base border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 bg-gray-50 transition-colors" />
                    <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400">
                        <i class="fas fa-search"></i>
                    </span>
                </div>
                <select id="statusFilter" class="flex-1 w-full px-3 sm:px-4 py-2 sm:py-3 text-sm sm:text-base border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 bg-gray-50 transition-colors">
                    <option value="">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="forwarded_to_manager">Forwarded to Manager</option>
                    <option value="approved_by_manager">Manager Approved</option>
                    <option value="approved">HR Approved</option>
                    <option value="rejected">Rejected</option>
                </select>
                <select id="dateFilter" class="flex-1 w-full px-3 sm:px-4 py-2 sm:py-3 text-sm sm:text-base border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 bg-gray-50 transition-colors">
                    <option value="">All Dates</option>
                    <option value="today">Today</option>
                    <option value="week">This Week</option>
                    <option value="month">This Month</option>
                    <option value="year">This Year</option>
                </select>
                @php
                    // Check if user has employee record (super admins don't have employee records)
                    $hasEmployeeRecord = auth()->user()->payroll_id 
                        ? \App\Models\Employee::where('payroll_id', auth()->user()->payroll_id)->exists()
                        : \App\Models\Employee::where('email', auth()->user()->email)->exists();
                @endphp
                @if($hasEmployeeRecord)
                    <a href="{{ route('leaves.create') }}" class="w-full md:w-auto inline-flex items-center justify-center px-4 sm:px-6 py-2 sm:py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold text-sm sm:text-base rounded-lg shadow-md hover:from-indigo-700 hover:to-purple-700 transition-all duration-300">
                        <i class="fas fa-plus mr-2"></i> Apply for Leave
                    </a>
                @endif
            </div>
        </div>

        <!-- Leave Applications Table/Card -->
        <div class="bg-white rounded-2xl shadow border border-gray-100 overflow-hidden">
            <div class="px-4 sm:px-6 py-3 sm:py-4 border-b bg-gradient-to-r from-gray-50 to-gray-100">
                <h3 class="text-lg sm:text-xl font-bold text-gray-800 flex items-center">
                    <i class="fas fa-list-ul text-indigo-600 mr-2 text-sm sm:text-base"></i>
                    Leave Applications
                </h3>
            </div>
            @if($leaves->count() > 0)
                <!-- Desktop Table -->
                <div class="hidden lg:block overflow-x-auto">
                    <table class="w-full text-xs sm:text-sm">
                        <thead class="bg-gray-50 border-b">
                            <tr>
                                <th class="px-6 py-4 text-left font-semibold text-gray-500 uppercase">Employee</th>
                                <th class="px-6 py-4 text-left font-semibold text-gray-500 uppercase">Duration</th>
                                <th class="px-6 py-4 text-left font-semibold text-gray-500 uppercase">Type</th>
                                <th class="px-6 py-4 text-left font-semibold text-gray-500 uppercase">Days</th>
                                <th class="px-6 py-4 text-left font-semibold text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-4 text-left font-semibold text-gray-500 uppercase">Reason</th>
                                <th class="px-6 py-4 text-left font-semibold text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @foreach($leaves as $leave)
                                @php
                                    $reportingManager = null;
                                    // Use the employee relationship instead of manual query
                                    $employee = $leave->employee;
                                    if ($employee && $employee->reporting_manager_payroll_id) {
                                        // Look up the manager in employees table using reporting_manager_payroll_id
                                        $reportingManager = \App\Models\Employee::where('payroll_id', $employee->reporting_manager_payroll_id)->first();
                                    }
                                    
                                    // Define dates for data attributes
                                    $startDate = is_string($leave->start_date) ? \Carbon\Carbon::parse($leave->start_date) : $leave->start_date;
                                    $endDate = is_string($leave->end_date) ? \Carbon\Carbon::parse($leave->end_date) : $leave->end_date;
                                @endphp
                                <tr class="hover:bg-indigo-50 transition leave-row" 
                                    data-leave-id="{{ $leave->id }}"
                                    data-manager-name="{{ $reportingManager ? $reportingManager->name : 'Manager not found' }}"
                                    data-manager-email="{{ $reportingManager ? $reportingManager->email : '' }}"
                                    data-leave-status="{{ $leave->status }}"
                                    data-start-date="{{ $startDate->format('Y-m-d') }}"
                                    data-end-date="{{ $endDate->format('Y-m-d') }}">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center text-white font-bold text-lg">
                                                @if($employee)
                                                    {{ strtoupper(substr($employee->name ?? 'NA', 0, 2)) }}
                                                @elseif($leave->user)
                                                    {{ strtoupper(substr($leave->user->name ?? 'NA', 0, 2)) }}
                                                @else
                                                    NA
                                                @endif
                                            </div>
                                            <div class="ml-4">
                                                <div class="font-semibold text-gray-900">{{ $employee ? $employee->name : ($leave->user->name ?? 'User Not Found') }}</div>
                                                <div class="text-xs text-gray-500">
                                                    {{ $leave->user->email ?? 'No email available' }}
                                                    @if($employee && $employee->employee_id)
                                                        <span class="ml-2 text-blue-600">ID: {{ $employee->employee_id }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-medium">{{ $startDate->format('M d, Y') }}</div>
                                        <div class="text-xs text-gray-500">to {{ $endDate->format('M d, Y') }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        @php
                                            $leaveTypeColors = [
                                                'sick leave' => 'bg-red-100 text-red-700',
                                                'casual leave' => 'bg-blue-100 text-blue-700',
                                                'earned leave' => 'bg-green-100 text-green-700',
                                                'maternity leave' => 'bg-pink-100 text-pink-700',
                                                'paternity leave' => 'bg-purple-100 text-purple-700',
                                                'default' => 'bg-gray-100 text-gray-700',
                                            ];
                                            $leaveTypeName = strtolower($leave->leaveType->name ?? $leave->leave_type);
                                            $leaveTypeClass = $leaveTypeColors[$leaveTypeName] ?? $leaveTypeColors['default'];
                                        @endphp
                                        <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold {{ $leaveTypeClass }}">
                                            {{ $leave->leaveType->name ?? ucfirst($leave->leave_type) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 font-semibold text-indigo-700">
                                        <i class="fas fa-calendar-check mr-1 text-indigo-400"></i>
                                        {{ $leave->total_days }} days
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="leave-status inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold
                                            @if($leave->status === 'approved') bg-green-100 text-green-700
                                            @elseif($leave->status === 'rejected') bg-red-100 text-red-700
                                            @elseif($leave->status === 'forwarded_to_manager') bg-purple-100 text-purple-700
                                            @elseif($leave->status === 'approved_by_manager') bg-blue-100 text-blue-700
                                            @else bg-yellow-100 text-yellow-700
                                            @endif">
                                            <i class="fas 
                                                @if($leave->status === 'approved') fa-check-circle
                                                @elseif($leave->status === 'rejected') fa-times-circle
                                                @elseif($leave->status === 'forwarded_to_manager') fa-share
                                                @elseif($leave->status === 'approved_by_manager') fa-user-check
                                                @else fa-clock
                                                @endif mr-1"></i>
                                            {{ ucfirst(str_replace('_', ' ', $leave->status)) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 max-w-xs">
                                        <div class="truncate" title="{{ $leave->reason }}">
                                            {{ $leave->reason }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-2">
                                            <a href="{{ route('leaves.show', $leave) }}" class="text-indigo-600 hover:text-indigo-900 p-2 rounded-lg hover:bg-indigo-100 transition" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            {{-- HR/Admin actions --}}
                                            @if(auth()->user()->isAdmin() || auth()->user()->isSuperAdmin())
                                                {{-- Forward pending leaves to manager --}}
                                                @if($leave->status === 'pending')
                                                    <button onclick="showForwardModal('{{ $leave->id }}')" class="text-purple-600 hover:text-purple-900 p-2 rounded-lg hover:bg-purple-100 transition" title="Forward to Manager">
                                                        <i class="fas fa-share"></i>
                                                    </button>
                                                @endif
                                                
                                                {{-- HR can approve pending or manager-approved leaves --}}
                                                @if($leave->status === 'pending' || $leave->status === 'approved_by_manager')
                                                    <form action="{{ route('leaves.approve', $leave) }}" method="POST" class="inline">
                                                        @csrf
                                                        <button type="submit" class="text-green-600 hover:text-green-900 p-2 rounded-lg hover:bg-green-100 transition" title="Approve as HR">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                                
                                                {{-- HR can reject pending, forwarded or manager-approved leaves --}}
                                                @if(in_array($leave->status, ['pending', 'forwarded_to_manager', 'approved_by_manager']))
                                                    <button onclick="showRejectModal('{{ $leave->id }}')" class="text-red-600 hover:text-red-900 p-2 rounded-lg hover:bg-red-100 transition" title="Reject">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                @endif
                                            @endif
                                            
                                            {{-- Manager approval buttons (using policy for proper authorization) --}}
                                            @can('approve', $leave)
                                                @if(!auth()->user()->isAdmin() && !auth()->user()->isSuperAdmin())
                                                    <form action="{{ route('leaves.manager-approve', $leave) }}" method="POST" class="inline">
                                                        @csrf
                                                        <button type="submit" class="text-blue-600 hover:text-blue-900 p-2 rounded-lg hover:bg-blue-100 transition" title="Approve as Manager">
                                                            <i class="fas fa-user-check"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            @endcan
                                            
                                            @can('reject', $leave)
                                                @if(!auth()->user()->isAdmin() && !auth()->user()->isSuperAdmin())
                                                    <button onclick="showRejectModal('{{ $leave->id }}')" class="text-red-600 hover:text-red-900 p-2 rounded-lg hover:bg-red-100 transition" title="Reject">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                @endif
                                            @endcan
                                            @if($leave->status === 'pending' && $leave->user_id === auth()->id())
                                                <form action="{{ route('leaves.cancel', $leave) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to cancel this leave application?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900 p-2 rounded-lg hover:bg-red-100 transition" title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <!-- Mobile Card View -->
                <div class="lg:hidden space-y-4 p-4">
                    @foreach($leaves as $leave)
                        @php
                            // Use the employee relationship instead of manual query
                            $mobileEmployee = $leave->employee;
                        @endphp
                        @php
                            // Define dates for data attributes in mobile view
                            $mobileStartDate = is_string($leave->start_date) ? \Carbon\Carbon::parse($leave->start_date) : $leave->start_date;
                            $mobileEndDate = is_string($leave->end_date) ? \Carbon\Carbon::parse($leave->end_date) : $leave->end_date;
                        @endphp
                        <div class="bg-white border border-gray-200 rounded-xl p-6 leave-row hover:shadow-lg transition-shadow duration-300"
                             data-leave-status="{{ $leave->status }}"
                             data-start-date="{{ $mobileStartDate->format('Y-m-d') }}"
                             data-end-date="{{ $mobileEndDate->format('Y-m-d') }}">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center text-white font-bold text-lg">
                                        @if($mobileEmployee)
                                            {{ strtoupper(substr($mobileEmployee->name ?? 'NA', 0, 2)) }}
                                        @elseif($leave->user)
                                            {{ strtoupper(substr($leave->user->name ?? 'NA', 0, 2)) }}
                                        @else
                                            NA
                                        @endif
                                    </div>
                                    <div class="ml-4">
                                        <div class="font-semibold text-gray-900">{{ $mobileEmployee ? $mobileEmployee->name : ($leave->user->name ?? 'User Not Found') }}</div>
                                        <div class="text-xs text-gray-500">
                                            {{ $leave->user->email ?? 'No email available' }}
                                            @if($mobileEmployee && $mobileEmployee->employee_id)
                                                <span class="ml-2 text-blue-600">ID: {{ $mobileEmployee->employee_id }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <span class="leave-status inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold
                                    @if($leave->status === 'approved') bg-green-100 text-green-700
                                    @elseif($leave->status === 'rejected') bg-red-100 text-red-700
                                    @elseif($leave->status === 'forwarded_to_manager') bg-purple-100 text-purple-700
                                    @elseif($leave->status === 'approved_by_manager') bg-blue-100 text-blue-700
                                    @else bg-yellow-100 text-yellow-700
                                    @endif">
                                    {{ ucfirst(str_replace('_', ' ', $leave->status)) }}
                                </span>
                            </div>
                            <div class="grid grid-cols-2 gap-4 text-sm mb-4">
                                <div>
                                    <span class="text-gray-400 text-xs block mb-1">Duration:</span>
                                    @php
                                        $startDate = is_string($leave->start_date) ? \Carbon\Carbon::parse($leave->start_date) : $leave->start_date;
                                        $endDate = is_string($leave->end_date) ? \Carbon\Carbon::parse($leave->end_date) : $leave->end_date;
                                    @endphp
                                    <div class="font-medium">{{ $startDate->format('M d') }} - {{ $endDate->format('M d, Y') }}</div>
                                </div>
                                <div>
                                    <span class="text-gray-400 text-xs block mb-1">Days:</span>
                                    <div class="font-medium">{{ $leave->total_days }}</div>
                                </div>
                                <div>
                                    <span class="text-gray-400 text-xs block mb-1">Type:</span>
                                    <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold
                                        @if($leave->leave_type === 'sick') bg-red-100 text-red-700
                                        @elseif($leave->leave_type === 'casual') bg-blue-100 text-blue-700
                                        @elseif($leave->leave_type === 'annual') bg-green-100 text-green-700
                                        @else bg-gray-100 text-gray-700
                                        @endif">
                                        {{ ucfirst($leave->leave_type ?? 'Casual') }}
                                    </span>
                                </div>
                                <div>
                                    <span class="text-gray-400 text-xs block mb-1">Actions:</span>
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('leaves.show', $leave) }}" class="text-indigo-600 hover:text-indigo-900 p-2 rounded hover:bg-indigo-100 transition" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        {{-- HR can approve any pending or manager-approved leave --}}
                                        @if(($leave->status === 'pending' || $leave->status === 'approved_by_manager') && (auth()->user()->isAdmin() || auth()->user()->isSuperAdmin()))
                                            {{-- Forward pending leaves to manager --}}
                                            @if($leave->status === 'pending')
                                                <button onclick="showForwardModal('{{ $leave->id }}')" class="text-purple-600 hover:text-purple-900 p-2 rounded hover:bg-purple-100 transition" title="Forward to Manager">
                                                    <i class="fas fa-share"></i>
                                                </button>
                                            @endif
                                            <form action="{{ route('leaves.approve', $leave) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="text-green-600 hover:text-green-900 p-2 rounded hover:bg-green-100 transition" title="Approve as HR">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                            <button onclick="showRejectModal('{{ $leave->id }}')" class="text-red-600 hover:text-red-900 p-2 rounded hover:bg-red-100 transition" title="Reject">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        @endif
                                        
                                        {{-- Manager approval buttons (using policy for proper authorization) --}}
                                        @can('approve', $leave)
                                            @if(!auth()->user()->isAdmin() && !auth()->user()->isSuperAdmin())
                                                <form action="{{ route('leaves.manager-approve', $leave) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" class="text-blue-600 hover:text-blue-900 p-2 rounded hover:bg-blue-100 transition" title="Approve as Manager">
                                                        <i class="fas fa-user-check"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        @endcan
                                        
                                        @can('reject', $leave)
                                            @if(!auth()->user()->isAdmin() && !auth()->user()->isSuperAdmin())
                                                <button onclick="showRejectModal('{{ $leave->id }}')" class="text-red-600 hover:text-red-900 p-2 rounded hover:bg-red-100 transition" title="Reject">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            @endif
                                        @endcan
                                        @if($leave->status === 'pending' && $leave->user_id === auth()->id())
                                            <form action="{{ route('leaves.cancel', $leave) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to cancel this leave application?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900 p-2 rounded hover:bg-red-100 transition" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="border-t pt-4">
                                <span class="text-gray-400 text-xs block mb-1">Reason:</span>
                                <p class="text-sm text-gray-900">{{ $leave->reason }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-12 sm:py-16">
                    <div class="w-16 h-16 sm:w-20 sm:h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4 sm:mb-5 shadow">
                        <i class="fas fa-calendar-times text-gray-400 text-2xl sm:text-3xl"></i>
                    </div>
                    <h3 class="text-lg sm:text-2xl font-bold text-gray-800 mb-2">No Leave Applications</h3>
                    <p class="text-gray-500 mb-4 sm:mb-6 text-sm sm:text-base">
                        @if($hasEmployeeRecord)
                            You haven't applied for any leaves yet. Ready to take some time off?
                        @else
                            No leave applications found in the system.
                        @endif
                    </p>
                    @if($hasEmployeeRecord)
                        <a href="{{ route('leaves.create') }}" class="inline-flex items-center px-4 sm:px-6 py-2 sm:py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold text-sm sm:text-base rounded-lg shadow hover:from-indigo-700 hover:to-purple-700 transition">
                            <i class="fas fa-plus mr-2"></i>
                            Apply for Leave
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');
    const dateFilter = document.getElementById('dateFilter');
    const rows = document.querySelectorAll('.leave-row');
    function filterTable() {
        const searchTerm = searchInput.value.toLowerCase();
        const statusValue = statusFilter.value.toLowerCase();
        const dateValue = dateFilter.value.toLowerCase();
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            const status = row.dataset.leaveStatus || '';
            const matchesSearch = text.includes(searchTerm);
            // Use exact matching with the actual status value from data attribute
            const matchesStatus = !statusValue || status === statusValue;
            
            // Date filtering
            let matchesDate = true;
                    if (dateValue) {
                const startDateStr = row.dataset.startDate;
                const endDateStr = row.dataset.endDate;
                if (startDateStr && endDateStr) {
                    // Get current date in YYYY-MM-DD format (local timezone)
                    const now = new Date();
                    const year = now.getFullYear();
                    const month = String(now.getMonth() + 1).padStart(2, '0');
                    const day = String(now.getDate()).padStart(2, '0');
                    const todayFormatted = `${year}-${month}-${day}`;
                    
                    // Use the raw YYYY-MM-DD strings from the dataset
                    const startDateFormatted = startDateStr;
                    const endDateFormatted = endDateStr;
                    
                    console.log('Date comparison values:', {
                        row: row.textContent.substring(0, 30) + '...',
                        startDateStr,
                        endDateStr,
                        todayFormatted
                    });                    if (dateValue === 'today') {
                        // Show leaves that include today's date
                        matchesDate = (startDateFormatted <= todayFormatted && todayFormatted <= endDateFormatted);
                        
                        // Debug the date comparison
                        console.log('Today filter debug:', {
                            startDate: startDateFormatted,
                            endDate: endDateFormatted,
                            today: todayFormatted,
                            isMatch: matchesDate,
                            startComparison: startDateFormatted <= todayFormatted,
                            endComparison: todayFormatted <= endDateFormatted
                        });
                    } else if (dateValue === 'week') {
                        // Calculate week start (Sunday) and end (Saturday)
                        const now = new Date();
                        const weekStart = new Date(now);
                        weekStart.setDate(now.getDate() - now.getDay()); // Go to Sunday
                        const weekEnd = new Date(weekStart);
                        weekEnd.setDate(weekStart.getDate() + 6); // Go to Saturday
                        
                        // Format dates as YYYY-MM-DD strings
                        const weekStartFormatted = `${weekStart.getFullYear()}-${String(weekStart.getMonth() + 1).padStart(2, '0')}-${String(weekStart.getDate()).padStart(2, '0')}`;
                        const weekEndFormatted = `${weekEnd.getFullYear()}-${String(weekEnd.getMonth() + 1).padStart(2, '0')}-${String(weekEnd.getDate()).padStart(2, '0')}`;
                        
                        // Leave overlaps with current week if:
                        // 1. Leave ends on or after week start AND
                        // 2. Leave starts on or before week end
                        matchesDate = (endDateFormatted >= weekStartFormatted && startDateFormatted <= weekEndFormatted);
                    } else if (dateValue === 'month') {
                        const now = new Date();
                        const year = now.getFullYear();
                        const month = now.getMonth();
                        
                        // Create first day of month
                        const monthStart = new Date(year, month, 1);
                        // Create last day of month
                        const monthEnd = new Date(year, month + 1, 0);
                        
                        // Format dates as YYYY-MM-DD strings
                        const monthStartFormatted = `${monthStart.getFullYear()}-${String(monthStart.getMonth() + 1).padStart(2, '0')}-${String(monthStart.getDate()).padStart(2, '0')}`;
                        const monthEndFormatted = `${monthEnd.getFullYear()}-${String(monthEnd.getMonth() + 1).padStart(2, '0')}-${String(monthEnd.getDate()).padStart(2, '0')}`;
                        
                        // Leave overlaps with current month
                        matchesDate = (endDateFormatted >= monthStartFormatted && startDateFormatted <= monthEndFormatted);
                    } else if (dateValue === 'year') {
                        const now = new Date();
                        const year = now.getFullYear();
                        
                        // Create first and last day of year
                        const yearStartFormatted = `${year}-01-01`;
                        const yearEndFormatted = `${year}-12-31`;
                        
                        // Leave overlaps with current year
                        matchesDate = (endDateFormatted >= yearStartFormatted && startDateFormatted <= yearEndFormatted);
                        
                        // Leave overlaps with current year
                        matchesDate = (endDateFormatted >= yearStartFormatted && startDateFormatted <= yearEndFormatted);
                    }
                }
            }
            
            if (matchesSearch && matchesStatus && matchesDate) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }
    searchInput.addEventListener('input', filterTable);
    statusFilter.addEventListener('change', filterTable);
    dateFilter.addEventListener('change', filterTable);
});

function showRejectModal(leaveId) {
    const form = document.getElementById('rejectForm');
    form.action = "/leaves/" + leaveId + "/reject";
    const modal = document.getElementById('rejectModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function hideRejectModal() {
    const modal = document.getElementById('rejectModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function showForwardModal(leaveId) {
    const form = document.getElementById('forwardForm');
    form.action = "/leaves/" + leaveId + "/forward";
    
    // Find the leave row to get manager info
    const leaveRow = document.querySelector(`[data-leave-id="${leaveId}"]`);
    const managerNameElement = document.querySelector('.manager-name');
    
    if (leaveRow) {
        const managerName = leaveRow.dataset.managerName || 'Manager not found';
        const managerEmail = leaveRow.dataset.managerEmail || '';
        
        if (managerNameElement) {
            managerNameElement.textContent = managerName;
            if (managerEmail) {
                managerNameElement.innerHTML = `${managerName} <span class="text-xs text-gray-500 ml-1">(${managerEmail})</span>`;
            }
        }
    } else {
        if (managerNameElement) {
            managerNameElement.textContent = 'Loading...';
        }
    }
    
    const modal = document.getElementById('forwardModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function hideForwardModal() {
    const modal = document.getElementById('forwardModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}
</script>

<!-- Rejection Modal -->
<div id="rejectModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center">
    <div class="bg-white rounded-xl shadow-xl max-w-lg w-full mx-auto mt-20 overflow-hidden">
        <div class="bg-red-600 text-white px-4 sm:px-6 py-3 sm:py-4 flex justify-between items-center">
            <h3 class="text-lg sm:text-xl font-bold">Reject Leave Application</h3>
            <button type="button" onclick="hideRejectModal()" class="text-white hover:text-gray-200">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <form action="" method="POST" id="rejectForm">
            @csrf
            <div class="p-4 sm:p-6">
                <p class="mb-4 text-gray-600 text-sm sm:text-base">Please provide a reason for rejecting this leave application.</p>
                
                <div>
                    <label for="rejection_reason" class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">Rejection Reason</label>
                    <textarea name="rejection_reason" id="rejection_reason" rows="4" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors text-sm sm:text-base"
                        placeholder="Enter reason for rejection" required></textarea>
                    <p class="mt-1 text-xs text-gray-500">This reason will be visible to the employee.</p>
                </div>
                <input type="hidden" id="rejectLeaveId" name="leave_id" value="">
            </div>
            
            <div class="px-4 sm:px-6 py-3 sm:py-4 bg-gray-50 flex justify-end space-x-3">
                <button type="button" onclick="hideRejectModal()" 
                        class="px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100 transition-colors">
                    Cancel
                </button>
                <button type="submit" 
                        class="px-3 sm:px-4 py-2 text-sm sm:text-base bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                    Confirm Rejection
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Forward Modal -->
<div id="forwardModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl max-w-md w-full mx-auto overflow-hidden transform transition-all">
        <!-- Header -->
        <div class="bg-purple-600 px-4 sm:px-6 py-3 sm:py-4 flex justify-between items-center">
            <div class="flex items-center">
                <div class="w-6 h-6 sm:w-8 sm:h-8 bg-white bg-opacity-20 rounded-full flex items-center justify-center mr-2 sm:mr-3">
                    <i class="fas fa-share text-xs sm:text-sm text-white"></i>
                </div>
                <h3 class="text-base sm:text-lg font-semibold text-white">Forward to Manager</h3>
            </div>
            <button type="button" onclick="hideForwardModal()" class="text-white hover:text-purple-200 transition-colors p-1 rounded hover:bg-purple-700">
                <i class="fas fa-times text-white"></i>
            </button>
        </div>
        
        <form action="" method="POST" id="forwardForm">
            @csrf
            <div class="p-4 sm:p-6">
                <!-- Info message -->
                <div class="flex items-start space-x-3 mb-4">
                    <div class="flex-shrink-0">
                        <div class="w-6 h-6 sm:w-8 sm:h-8 bg-blue-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-info text-blue-600 text-xs sm:text-sm"></i>
                        </div>
                    </div>
                    <div>
                        <p class="text-xs sm:text-sm text-gray-700 leading-relaxed mb-2">This leave application will be forwarded to the employee's reporting manager for review and approval.</p>
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-3 mt-2">
                            <div class="flex items-center space-x-2">
                                <i class="fas fa-user-tie text-gray-500 text-xs"></i>
                                <span class="text-xs font-medium text-gray-600">Forwarding to:</span>
                                <span class="text-sm font-semibold text-gray-800 manager-name">Loading...</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Note field -->
                <div>
                    <label for="forwarding_note" class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-sticky-note text-gray-400 mr-1"></i>
                        Note to Manager (Optional)
                    </label>
                    <textarea name="forwarding_note" id="forwarding_note" rows="3" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200 text-sm"
                        placeholder="Add any context or instructions for the manager..."></textarea>
                    <p class="mt-2 text-xs text-gray-500 flex items-center">
                        <i class="fas fa-eye text-gray-400 mr-1"></i>
                        This note will be visible to the manager.
                    </p>
                </div>
            </div>
            
            <!-- Footer -->
            <div class="px-4 sm:px-6 py-3 sm:py-4 bg-gray-50 flex justify-end space-x-3 border-t border-gray-100">
                <button type="button" onclick="hideForwardModal()" 
                        class="px-3 sm:px-4 py-2 text-xs sm:text-sm border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100 transition-all duration-200 flex items-center">
                    <i class="fas fa-times mr-2 text-xs"></i>
                    Cancel
                </button>
                <button type="submit" 
                        class="px-3 sm:px-4 py-2 text-xs sm:text-sm bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-all duration-200 flex items-center shadow-sm">
                    <i class="fas fa-paper-plane mr-2 text-xs text-white"></i>
                    Forward to Manager
                </button>
            </div>
        </form>
    </div>
</div>
@endsection