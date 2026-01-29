@extends('layouts.app')

@section('title', 'Leave Application Details - HRMS')
@section('page-title', 'Leave Application Details')

@section('content')
<div class="p-6 space-y-6">
    <!-- Header Section -->
    <div class="bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 rounded-2xl p-8 text-white relative overflow-hidden">
        <div class="absolute inset-0 opacity-10">
            <div class="absolute -top-4 -right-4 w-32 h-32 bg-white rounded-full"></div>
            <div class="absolute top-10 -right-8 w-20 h-20 bg-white rounded-full"></div>
            <div class="absolute -bottom-6 -left-6 w-24 h-24 bg-white rounded-full"></div>
        </div>
        <div class="relative">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold mb-3 flex items-center">
                        <i class="fas fa-file-alt mr-2 sm:mr-4 text-lg sm:text-xl lg:text-2xl"></i>
                        Leave Application Details
                    </h1>
                    <p class="text-blue-100 text-sm sm:text-base lg:text-lg">View detailed information about this leave request</p>
                </div>
                <div class="hidden lg:block">
                    <div class="w-36 h-36 bg-white bg-opacity-15 rounded-full flex items-center justify-center">
                        <i class="fas fa-clipboard-list text-5xl text-white"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 rounded-lg p-4 shadow-sm">
            <div class="flex items-center">
                <i class="fas fa-check-circle text-green-500 mr-2 text-lg"></i>
                <p class="text-green-800 font-medium">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-50 border border-red-200 rounded-lg p-4 shadow-sm">
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle text-red-500 mr-2 text-lg"></i>
                <p class="text-red-800 font-medium">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    <!-- Leave Application Details -->
    <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-white">
            <div class="flex justify-between items-center">
                <h3 class="text-lg sm:text-xl font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-info-circle text-blue-600 mr-2 sm:mr-3 text-sm sm:text-base lg:text-lg"></i>
                    Application Information
                </h3>
                <span class="px-4 py-2 rounded-full text-sm font-medium
                    @if($leave->status === 'approved') bg-green-100 text-green-800
                    @elseif($leave->status === 'approved_by_manager') bg-blue-100 text-blue-800
                    @elseif($leave->status === 'forwarded_to_manager') bg-purple-100 text-purple-800
                    @elseif($leave->status === 'rejected') bg-red-100 text-red-800
                    @elseif($leave->status === 'cancelled') bg-gray-100 text-gray-800
                    @else bg-amber-100 text-amber-800
                    @endif">
                    <i class="fas 
                        @if($leave->status === 'approved') fa-check-circle
                        @elseif($leave->status === 'approved_by_manager') fa-user-check
                        @elseif($leave->status === 'forwarded_to_manager') fa-share
                        @elseif($leave->status === 'rejected') fa-times-circle
                        @elseif($leave->status === 'cancelled') fa-ban
                        @else fa-clock
                        @endif mr-1"></i>
                    {{ str_replace('_', ' ', ucfirst($leave->status)) }}
                </span>
            </div>
        </div>

        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Application Details Section -->
            <div class="space-y-6">
                <div class="bg-gray-50 rounded-lg p-6 border border-gray-100">
                    <h4 class="text-base sm:text-lg font-medium text-gray-900 mb-4 border-b pb-3">Leave Details</h4>
                    
                    <div class="grid grid-cols-2 gap-6">
                        <div class="mb-2">
                            <p class="text-sm text-gray-500 mb-2">Leave Type</p>
                            <p class="font-medium text-gray-900">{{ $leave->leaveType->name ?? ucfirst($leave->leave_type) }}</p>
                        </div>
                        <div class="mb-2">
                            <p class="text-sm text-gray-500 mb-2">Leave Code</p>
                            <p class="font-medium text-gray-900">{{ $leave->leaveType->code ?? 'N/A' }}</p>
                        </div>
                        <div class="mb-2">
                            <p class="text-sm text-gray-500 mb-2">Total Days</p>
                            <p class="font-medium text-gray-900">{{ $leave->total_days }} {{ Str::plural('day', $leave->total_days) }}</p>
                        </div>
                        <div class="mb-2">
                            <p class="text-sm text-gray-500 mb-2">Financial Year</p>
                            <p class="font-medium text-gray-900">{{ $leave->financial_year }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 rounded-lg p-6 border border-gray-100">
                    <h4 class="text-base sm:text-lg font-medium text-gray-900 mb-4 border-b pb-3">Date Information</h4>
                    
                    <div class="grid grid-cols-2 gap-6">
                        <div class="mb-2">
                            <p class="text-sm text-gray-500 mb-2">Start Date</p>
                            <p class="font-medium text-gray-900">{{ $leave->start_date->format('M d, Y') }}</p>
                        </div>
                        <div class="mb-2">
                            <p class="text-sm text-gray-500 mb-2">End Date</p>
                            <p class="font-medium text-gray-900">{{ $leave->end_date->format('M d, Y') }}</p>
                        </div>
                        @if($leave->start_date->eq($leave->end_date))
                            <div class="mb-2">
                                <p class="text-sm text-gray-500 mb-2">Leave Duration</p>
                                <p class="font-medium text-gray-900">Single Day</p>
                            </div>
                        @else
                            <div class="mb-2">
                                <p class="text-sm text-gray-500 mb-2">Leave Duration</p>
                                <p class="font-medium text-gray-900">{{ $leave->start_date->diffInDays($leave->end_date) + 1 }} Calendar Days</p>
                            </div>
                        @endif
                        <div class="mb-2">
                            <p class="text-sm text-gray-500 mb-2">Working Days</p>
                            <p class="font-medium text-gray-900 text-sm sm:text-base lg:text-lg text-blue-600">{{ $leave->total_days }} {{ Str::plural('day', $leave->total_days) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Detailed Leave Breakdown -->
                @if($leave->leaveDays && $leave->leaveDays->count() > 0)
                    <div class="bg-gradient-to-br from-emerald-50 to-teal-50 rounded-lg p-6 border border-emerald-200">
                        <h4 class="text-base sm:text-lg font-medium text-gray-900 mb-4 border-b pb-3 flex items-center">
                            <i class="fas fa-calendar-day text-emerald-600 mr-2 sm:mr-3 text-sm sm:text-base"></i>
                            Daily Leave Breakdown
                        </h4>
                        
                        @php
                            $fullDays = $leave->leaveDays->where('day_type', 'full_day')->where('exclude_from_calculation', false)->count();
                            $halfDays = $leave->leaveDays->whereIn('day_type', ['first_half', 'second_half'])->where('exclude_from_calculation', false)->count();
                            $publicHolidays = $leave->leaveDays->where('is_public_holiday', true)->count();
                        @endphp
                        
                        <div class="grid grid-cols-3 gap-4 mb-4">
                            <div class="text-center p-3 bg-white rounded-lg border border-emerald-200">
                                <p class="text-lg sm:text-2xl font-bold text-emerald-600">{{ $fullDays }}</p>
                                <p class="text-xs sm:text-sm text-gray-600">Full Days</p>
                            </div>
                            <div class="text-center p-3 bg-white rounded-lg border border-emerald-200">
                                <p class="text-lg sm:text-2xl font-bold text-yellow-600">{{ $halfDays }}</p>
                                <p class="text-xs sm:text-sm text-gray-600">Half Days</p>
                            </div>
                            <div class="text-center p-3 bg-white rounded-lg border border-emerald-200">
                                <p class="text-lg sm:text-2xl font-bold text-green-600">{{ $publicHolidays }}</p>
                                <p class="text-xs sm:text-sm text-gray-600">Public Holidays</p>
                            </div>
                        </div>

                        <div class="space-y-2 max-h-60 overflow-y-auto">
                            @foreach($leave->leaveDays as $day)
                                <div class="flex items-center justify-between p-3 rounded-lg border {{ $day->is_public_holiday ? 'bg-green-50 border-green-200' : 'bg-white border-gray-200' }}">
                                    <div class="flex items-center space-x-3">
                                        <span class="font-medium text-gray-700">
                                            {{ \Carbon\Carbon::parse($day->leave_date)->format('D, M j') }}
                                        </span>
                                        @if($day->is_public_holiday)
                                            <span class="text-xs px-2 py-1 bg-green-100 text-green-700 rounded">Public Holiday</span>
                                        @endif
                                        @if($day->notes)
                                            <span class="text-xs text-gray-500">{{ $day->notes }}</span>
                                        @endif
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        @if(!$day->is_public_holiday)
                                            <span class="px-2 py-1 text-xs font-medium rounded
                                                @if($day->day_type === 'full_day') bg-blue-100 text-blue-700
                                                @elseif($day->day_type === 'first_half') bg-yellow-100 text-yellow-700
                                                @elseif($day->day_type === 'second_half') bg-orange-100 text-orange-700
                                                @endif">
                                                {{ $day->formatted_day_type }}
                                            </span>
                                            <span class="font-medium text-gray-900">{{ $day->days_count }} day{{ $day->days_count != 1 ? 's' : '' }}</span>
                                        @else
                                            <span class="text-sm text-gray-500">Not counted</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        <div class="mt-4 p-3 bg-white rounded-lg border border-emerald-200">
                            <div class="flex justify-between items-center">
                                <span class="font-medium text-gray-700">Total Leave Days Deducted:</span>
                                <span class="font-bold text-base sm:text-lg text-emerald-600">{{ $leave->total_days }} {{ Str::plural('day', $leave->total_days) }}</span>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="bg-gray-50 rounded-lg p-6 border border-gray-100">
                    <h4 class="text-base sm:text-lg font-medium text-gray-900 mb-4 border-b pb-3">Application Status</h4>
                    
                    <div class="grid grid-cols-2 gap-6">
                        <div class="mb-2">
                            <p class="text-sm text-gray-500 mb-2">Applied On</p>
                            <p class="font-medium text-gray-900">{{ $leave->created_at->format('M d, Y h:i A') }}</p>
                        </div>
                        <div class="mb-2">
                            <p class="text-sm text-gray-500 mb-2">Status</p>
                            <p class="font-medium 
                                @if($leave->status === 'approved') text-green-600
                                @elseif($leave->status === 'approved_by_manager') text-blue-600
                                @elseif($leave->status === 'forwarded_to_manager') text-purple-600
                                @elseif($leave->status === 'rejected') text-red-600
                                @elseif($leave->status === 'cancelled') text-gray-600
                                @else text-amber-600
                                @endif">
                                {{ str_replace('_', ' ', ucfirst($leave->status)) }}
                            </p>
                        </div>
                        
                        @if($leave->status === 'forwarded_to_manager' && $leave->forwarded_by)
                            <div class="mb-2">
                                <p class="text-sm text-gray-500 mb-2">Forwarded By</p>
                                <p class="font-medium text-gray-900">
                                    {{ $leave->forwardedBy->name ?? 'N/A' }}
                                    <span class="text-sm text-gray-500 ml-1">
                                        ({{ $leave->forwarded_at ? \Carbon\Carbon::parse($leave->forwarded_at)->format('M d, Y h:i A') : 'N/A' }})
                                    </span>
                                </p>
                            </div>
                        @endif
                        
                        @if($leave->status === 'approved_by_manager' || $leave->status === 'approved')
                            @if($leave->manager_approved_by)
                            <div class="mb-2">
                                <p class="text-sm text-gray-500 mb-2">Manager Approval</p>
                                <p class="font-medium text-gray-900">
                                    {{ $leave->managerApprovedBy->name ?? 'N/A' }}
                                    <span class="text-sm text-gray-500 ml-1">
                                        ({{ $leave->manager_approved_at ? \Carbon\Carbon::parse($leave->manager_approved_at)->format('M d, Y h:i A') : 'N/A' }})
                                    </span>
                                </p>
                            </div>
                            @endif
                        @endif
                        
                        @if($leave->status === 'approved')
                            @if($leave->hr_approved_by)
                            <div class="mb-2">
                                <p class="text-sm text-gray-500 mb-2">HR Approval</p>
                                <p class="font-medium text-gray-900">
                                    {{ $leave->hrApprovedBy->name ?? 'N/A' }}
                                    <span class="text-sm text-gray-500 ml-1">
                                        ({{ $leave->hr_approved_at ? \Carbon\Carbon::parse($leave->hr_approved_at)->format('M d, Y h:i A') : 'N/A' }})
                                    </span>
                                </p>
                            </div>
                            @endif
                        @endif
                        
                        @if($leave->status === 'rejected' && $leave->rejected_by)
                            <div class="mb-2">
                                <p class="text-sm text-gray-500 mb-2">Rejected By</p>
                                <p class="font-medium text-gray-900">
                                    {{ $leave->rejectedBy->name ?? 'N/A' }}
                                    <span class="text-sm text-gray-500 ml-1">
                                        ({{ $leave->rejected_at ? \Carbon\Carbon::parse($leave->rejected_at)->format('M d, Y h:i A') : 'N/A' }})
                                    </span>
                                </p>
                            </div>
                        @endif
                    </div>

                    @if($leave->status === 'rejected' && $leave->rejection_reason)
                        <div class="mt-5 p-4 bg-red-50 rounded-lg border border-red-100">
                            <p class="text-sm text-gray-500 mb-2">Rejection Reason</p>
                            <p class="text-red-700">{{ $leave->rejection_reason }}</p>
                        </div>
                    @endif
                    
                    @if($leave->status === 'forwarded_to_manager' && $leave->forwarding_note)
                        <div class="mt-5 p-4 bg-purple-50 rounded-lg border border-purple-100">
                            <p class="text-sm text-gray-500 mb-2">Forwarding Note</p>
                            <p class="text-purple-700">{{ $leave->forwarding_note }}</p>
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- Employee & Reason Section -->
            <div class="space-y-6">
                <div class="bg-gray-50 rounded-lg p-6 border border-gray-100">
                    <h4 class="text-base sm:text-lg font-medium text-gray-900 mb-4 border-b pb-3">Employee Information</h4>
                    
                    @php
                        // Get employee data from employees table using the relationship
                        $employee = $leave->employee;
                    @endphp
                    
                    <div class="flex items-center mb-5">
                        <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center text-white font-bold text-lg">
                            @if($employee)
                                {{ strtoupper(substr($employee->name, 0, 2)) }}
                            @elseif($leave->user)
                                {{ strtoupper(substr($leave->user->name, 0, 2)) }}
                            @else
                                --
                            @endif
                        </div>
                        <div class="ml-4">
                            <h5 class="font-medium text-gray-900">{{ $employee ? $employee->name : ($leave->user ? $leave->user->name : 'Unknown User') }}</h5>
                            <p class="text-sm text-gray-500">{{ $leave->user ? $leave->user->email : 'No email available' }}</p>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-6">
                        <div class="mb-2">
                            <p class="text-sm text-gray-500 mb-2">Employee ID</p>
                            <p class="font-medium text-gray-900">{{ $employee ? $employee->employee_id : 'N/A' }}</p>
                        </div>
                        <div class="mb-2">
                            <p class="text-sm text-gray-500 mb-2">Department</p>
                            @php
                                $departmentName = 'N/A';
                                if ($employee && $employee->payroll_department_id) {
                                    // Use the payrollDepartment relationship
                                    $department = $employee->payrollDepartment;
                                    $departmentName = $department ? $department->name : 'Department not found';
                                }
                            @endphp
                            <p class="font-medium text-gray-900">{{ $departmentName }}</p>
                        </div>
                        <div class="mb-2">
                            <p class="text-sm text-gray-500 mb-2">Role</p>
                            <p class="font-medium text-gray-900">{{ $leave->user ? ucwords(str_replace('_', ' ', $leave->user->role)) : 'N/A' }}</p>
                        </div>
                        <div class="mb-2">
                            <p class="text-sm text-gray-500 mb-2">Payroll ID</p>
                            <p class="font-medium text-gray-900">{{ $employee ? $employee->payroll_id : 'N/A' }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 rounded-lg p-6 border border-gray-100">
                    <h4 class="text-base sm:text-lg font-medium text-gray-900 mb-4 border-b pb-3">Reason for Leave</h4>
                    <p class="text-gray-700 whitespace-pre-line p-2">{{ $leave->reason }}</p>
                </div>

                @if($leave->emergency_contact_name || $leave->emergency_contact_phone)
                    <div class="bg-yellow-50 rounded-lg p-6 border border-yellow-100">
                        <h4 class="text-base sm:text-lg font-medium text-gray-900 mb-4 border-b pb-3 flex items-center">
                            <i class="fas fa-phone-alt text-yellow-600 mr-2 sm:mr-3 text-sm sm:text-base"></i>
                            Emergency Contact
                        </h4>
                        
                        <div class="grid grid-cols-2 gap-6">
                            @if($leave->emergency_contact_name)
                                <div class="mb-2">
                                    <p class="text-sm text-gray-500 mb-2">Name</p>
                                    <p class="font-medium text-gray-900">{{ $leave->emergency_contact_name }}</p>
                                </div>
                            @endif
                            
                            @if($leave->emergency_contact_phone)
                                <div class="mb-2">
                                    <p class="text-sm text-gray-500 mb-2">Phone</p>
                                    <p class="font-medium text-gray-900">{{ $leave->emergency_contact_phone }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Activity Timeline -->
                @php
                    $activities = [
                        [
                            'description' => 'Leave application created',
                            'timestamp' => $leave->created_at,
                            'causer' => $leave->user->name ?? 'Unknown User'
                        ],
                    ];

                    if ($leave->forwarded_at) {
                        $activities[] = [
                            'description' => 'Leave application forwarded to manager',
                            'timestamp' => $leave->forwarded_at,
                            'causer' => $leave->forwardedBy->name ?? 'Unknown User'
                        ];
                    }

                    if ($leave->manager_approved_at) {
                        $activities[] = [
                            'description' => 'Leave application approved by manager',
                            'timestamp' => $leave->manager_approved_at,
                            'causer' => $leave->managerApprovedBy->name ?? 'Unknown User'
                        ];
                    }

                    if ($leave->hr_approved_at) {
                        $activities[] = [
                            'description' => 'Leave application approved by HR',
                            'timestamp' => $leave->hr_approved_at,
                            'causer' => $leave->hrApprovedBy->name ?? 'Unknown User'
                        ];
                    }

                    if ($leave->rejected_at) {
                        $activities[] = [
                            'description' => 'Leave application rejected',
                            'timestamp' => $leave->rejected_at,
                            'causer' => $leave->rejectedBy->name ?? 'Unknown User'
                        ];
                    }

                    usort($activities, fn($a, $b) => $b['timestamp']->timestamp <=> $a['timestamp']->timestamp);
                @endphp

                @if(count($activities) > 0)
                    <div class="bg-blue-50 rounded-lg p-6 border border-blue-100">
                        <h4 class="text-base sm:text-lg font-medium text-gray-900 mb-4 border-b pb-3 flex items-center">
                            <i class="fas fa-history text-blue-600 mr-2 sm:mr-3 text-sm sm:text-base"></i>
                            Activity Timeline
                        </h4>
                        
                        <div class="px-4 py-2">
                            @foreach($activities as $activity)
                                <div class="flex items-start mb-5 last:mb-0">
                                    <div class="w-6 h-6 rounded-full bg-blue-500 flex items-center justify-center shrink-0 mt-1">
                                        <div class="w-3 h-3 bg-white rounded-full"></div>
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <p class="font-medium text-gray-900 mb-1">{{ $activity['description'] }}</p>
                                        <p class="text-gray-500 mb-1">{{ $activity['timestamp']->format('M d, Y h:i A') }}</p>
                                        <p class="text-gray-500">by {{ $activity['causer'] }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="p-6 border-t border-gray-200 bg-gray-50">
            <div class="flex flex-col sm:flex-row justify-between items-center space-y-4 sm:space-y-0">
                <a href="{{ route('leaves.index') }}" 
                   class="text-gray-600 hover:text-gray-800 font-medium flex items-center space-x-2 transition-colors duration-200 text-sm sm:text-base">
                    <i class="fas fa-arrow-left"></i>
                    <span>Back to Applications</span>
                </a>
                
                <div class="flex flex-wrap gap-3">
                    @if($leave->status === 'pending' && $leave->user_id === auth()->id())
                        <!-- <a href="{{ route('leaves.edit', $leave) }}" 
                           class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm sm:text-base">
                            <i class="fas fa-edit mr-2"></i>
                            Edit Application
                        </a>
                         -->
                        <form action="{{ route('leaves.cancel', $leave) }}" method="POST" onsubmit="return confirm('Are you sure you want to cancel this leave application?');" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors text-sm sm:text-base">
                                <i class="fas fa-times-circle mr-2"></i>
                                Cancel Application
                            </button>
                        </form>
                    @endif
                    
                    {{-- HR/Admin actions --}}
                    @if(auth()->user()->isAdmin() || auth()->user()->isSuperAdmin())
                        {{-- Forward pending leaves to manager --}}
                        @if($leave->status === 'pending')
                            <button type="button" 
                                    onclick="showForwardModal()"
                                    class="inline-flex items-center px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors text-sm sm:text-base">
                                <i class="fas fa-share mr-2"></i>
                                Forward to Manager
                            </button>
                        @endif
                        
                        {{-- HR can approve pending or manager-approved leaves --}}
                        @if($leave->status === 'pending' || $leave->status === 'approved_by_manager')
                            <form action="{{ route('leaves.approve', $leave) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" 
                                        class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors text-sm sm:text-base">
                                    <i class="fas fa-check-circle mr-2"></i>
                                    Approve as HR
                                </button>
                            </form>
                        @endif
                        
                        {{-- HR can reject pending, forwarded or manager-approved leaves --}}
                        @if(in_array($leave->status, ['pending', 'forwarded_to_manager', 'approved_by_manager']))
                            <button type="button" 
                                    onclick="showRejectModal()"
                                    class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors text-sm sm:text-base">
                                <i class="fas fa-times-circle mr-2"></i>
                                Reject Leave
                            </button>
                        @endif
                    @endif
                    
                    {{-- Manager approval buttons (using policy for proper authorization) --}}
                    @can('approve', $leave)
                        @if(!auth()->user()->isAdmin() && !auth()->user()->isSuperAdmin())
                            <form action="{{ route('leaves.manager-approve', $leave) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" 
                                        class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm sm:text-base">
                                    <i class="fas fa-user-check mr-2"></i>
                                    Approve as Manager
                                </button>
                            </form>
                        @endif
                    @endcan
                    
                    @can('reject', $leave)
                        @if(!auth()->user()->isAdmin() && !auth()->user()->isSuperAdmin())
                            <button type="button" 
                                    onclick="showRejectModal()"
                                    class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors text-sm sm:text-base">
                                <i class="fas fa-times-circle mr-2"></i>
                                Reject Leave
                            </button>
                        @endif
                    @endcan
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Rejection Modal -->
<div id="rejectModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 items-center justify-center hidden">
    <div class="bg-white rounded-xl shadow-xl max-w-lg w-full mx-4 overflow-hidden">
        <div class="bg-red-600 text-white px-4 sm:px-6 py-3 sm:py-4">
            <h3 class="text-lg sm:text-xl font-bold">Reject Leave Application</h3>
        </div>
        
        <form action="{{ route('leaves.reject', $leave) }}" method="POST">
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
<div id="forwardModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 items-center justify-center hidden p-4">
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
        
        <form action="{{ route('leaves.forward', $leave) }}" method="POST">
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
                        @php
                            $reportingManager = null;
                            // Use the employee relationship instead of manual query
                            $employee = $leave->employee;
                            if ($employee && $employee->reporting_manager_payroll_id) {
                                // Look up the manager in employees table using reporting_manager_payroll_id
                                $reportingManager = \App\Models\Employee::where('payroll_id', $employee->reporting_manager_payroll_id)->first();
                            }
                        @endphp
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-3 mt-2">
                            <div class="flex items-center space-x-2">
                                <i class="fas fa-user-tie text-gray-500 text-xs"></i>
                                <span class="text-xs font-medium text-gray-600">Forwarding to:</span>
                                <span class="text-sm font-semibold text-gray-800">
                                    {{ $reportingManager ? $reportingManager->name : 'Manager not found' }}
                                    @if($reportingManager && $reportingManager->email)
                                        <span class="text-xs text-gray-500 ml-1">({{ $reportingManager->email }})</span>
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Note field -->
                <div>
                    <label for="forwarding_note" class="block text-sm font-medium text-gray-700 mb-2">
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
            <div class="px-6 py-4 bg-gray-50 flex justify-end space-x-3 border-t border-gray-100">
                <button type="button" onclick="hideForwardModal()" 
                        class="px-4 py-2 text-sm border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100 transition-all duration-200 flex items-center">
                    <i class="fas fa-times mr-2 text-xs"></i>
                    Cancel
                </button>
                <button type="submit" 
                        class="px-4 py-2 text-sm bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-all duration-200 flex items-center shadow-sm">
                    <i class="fas fa-paper-plane mr-2 text-xs text-white"></i>
                    Forward to Manager
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function showRejectModal() {
        const modal = document.getElementById('rejectModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
    
    function hideRejectModal() {
        const modal = document.getElementById('rejectModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
    
    function showForwardModal() {
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
@endsection
