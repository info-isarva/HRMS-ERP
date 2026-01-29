@extends('layouts.app')

@section('title', 'Dashboard - HRMS')
@section('page-title', 'Dashboard')

@section('content')
<!-- Chart.js CDN for visualizations -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns"></script>

<script>
    // Pass PHP data to JavaScript
    const leaveTrendsData = @json($leaveTrendsData ?? []);
    const leaveTypeData = @json($leaveTypeData ?? []);
    const monthlyLeaveData = @json($monthlyLeaveData ?? []);
    const approvalStatusData = @json($approvalStatusData ?? []);
</script>

<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50">
    <div class="max-w-full mx-auto p-6 space-y-6">
    <!-- Welcome Header -->
    <div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-2xl p-4 md:p-8 text-white relative overflow-hidden">
        <div class="absolute inset-0 opacity-10">
            <div class="absolute -top-4 -right-4 w-16 h-16 md:w-24 md:h-24 bg-white rounded-full"></div>
            <div class="absolute top-20 -right-8 w-12 h-12 md:w-16 md:h-16 bg-white rounded-full"></div>
            <div class="absolute -bottom-4 -left-4 w-16 h-16 md:w-20 md:h-20 bg-white rounded-full"></div>
        </div>
        <div class="relative">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
                <div class="flex-1">
                    <h1 class="text-2xl md:text-3xl font-bold mb-2">Welcome back, {{ auth()->user()->name }}! 👋</h1>
                    <p class="text-blue-100 text-base md:text-lg">Ready to manage your work efficiently today?</p>
                </div>
                <div class="flex flex-col sm:flex-row items-center space-y-3 sm:space-y-0 sm:space-x-4">
                    <!-- Toggle Buttons - Made more visible -->
                    <div class="bg-white rounded-lg p-1 flex items-center shadow-lg w-full sm:w-auto">
                        <button id="normalViewBtn" class="px-3 md:px-4 py-2 text-sm font-medium rounded-md transition-all duration-200 bg-blue-600 text-white hover:bg-blue-700 flex-1 sm:flex-none">
                            <i class="fas fa-th-large mr-2"></i><span class="hidden sm:inline">Dashboard</span>
                        </button>
                        <button id="visualizationViewBtn" class="px-3 md:px-4 py-2 text-sm font-medium rounded-md transition-all duration-200 bg-slate-100 text-slate-600 hover:bg-slate-200 hover:text-slate-800 flex-1 sm:flex-none">
                            <i class="fas fa-chart-bar mr-2"></i><span class="hidden sm:inline">Analytics</span>
                        </button>
                    </div>
                    <div class="hidden md:flex w-32 h-32 bg-white bg-opacity-20 rounded-full items-center justify-center">
                        <i class="fas fa-chart-line text-4xl text-white"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Normal Dashboard View --> 
    <div id="normalView" class="space-y-6">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        @if(auth()->user()->isAdmin() || auth()->user()->isSuperAdmin())
            <!-- Leave Analytics Card - Admin/Super Admin Only -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div style="background-color: #4f46e5;" class="px-4 py-2 flex items-center justify-between">
                    <div class="flex items-center">
                        <i class="fas fa-chart-line text-white text-sm mr-2"></i>
                        <h3 style="color: white;" class="font-medium text-sm">Leave Analytics</h3>
                    </div>
                    <span style="color: #c7d2fe;" class="text-xs">{{ date('Y') }}</span>
                </div>
                
                <div class="p-4 space-y-3">
                    <!-- Top Users Row -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-2 h-2 bg-red-500 rounded-full mr-2"></div>
                            <span class="text-gray-700 text-xs font-medium">Most Leave</span>
                        </div>
                        <div class="flex items-center space-x-1">
                            @forelse($topLeaveUsers->take(2) as $user)
                                <div class="flex items-center bg-red-50 px-2 py-1 rounded text-xs">
                                    <span class="text-gray-700 truncate max-w-16">{{ Str::limit($user->user->name ?? 'N/A', 8) }}</span>
                                    <span class="text-red-600 font-bold ml-1">{{ number_format($user->total_leave_days, 0) }}</span>
                                </div>
                            @empty
                                <span class="text-gray-400 text-xs">No data</span>
                            @endforelse
                        </div>
                    </div>
                    
                    <!-- Bottom Users Row -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
                            <span class="text-gray-700 text-xs font-medium">Least Leave</span>
                        </div>
                        <div class="flex items-center space-x-1">
                            @forelse($leastLeaveUsers->take(2) as $user)
                                <div class="flex items-center bg-green-50 px-2 py-1 rounded text-xs">
                                    <span class="text-gray-700 truncate max-w-16">{{ Str::limit($user->user->name ?? 'N/A', 8) }}</span>
                                    <span class="text-green-600 font-bold ml-1">{{ number_format($user->total_leave_days, 0) }}</span>
                                </div>
                            @empty
                                <span class="text-gray-400 text-xs">No data</span>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        @else
            <!-- Upcoming Leaves Card - For Regular Employees -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div style="background-color: #7c3aed;" class="px-4 py-2 flex items-center justify-between">
                    <div class="flex items-center">
                        <i class="fas fa-calendar-check text-white text-sm mr-2"></i>
                        <h3 style="color: white;" class="font-medium text-sm">Upcoming Leaves</h3>
                    </div>
                    <span style="color: #ddd6fe;" class="text-xs">{{ now()->format('M d') }}</span>
                </div>
                
                <div class="p-4 space-y-3">
                    @php
                        // Get upcoming approved leaves for this user
                        $user = auth()->user();
                        $today = now()->startOfDay();
                        
                        // Get approved and public holiday leaves from today onwards
                        $upcomingLeaves = \App\Models\LeaveApplication::where('user_id', $user->id)
                            ->whereIn('status', ['approved', 'approved_by_manager'])
                            ->where('start_date', '>=', $today)
                            ->orderBy('start_date')
                            ->take(2)
                            ->get();
                        
                        // Get employee record for this user
                        $employee = \App\Models\Employee::where('email', $user->email)->first();
                        
                        // Get fixed public holidays assigned to employee's department
                        $upcomingPublicLeaves = collect();
                        if ($employee && $employee->department_id) {
                            $upcomingPublicLeaves = \App\Models\PublicHoliday::whereHas('departments', function($q) use ($employee) {
                                $q->where('department_id', $employee->department_id);
                            })
                            ->where('type', 'fixed')
                            ->where('date', '>=', $today)
                            ->orderBy('date')
                            ->take(2)
                            ->get();
                        }
                    @endphp
                    
                    @if($upcomingLeaves->count() > 0 || $upcomingPublicLeaves->count() > 0)
                        <!-- Approved Leaves -->
                        @forelse($upcomingLeaves as $leave)
                            <div class="flex items-start space-x-3 border-l-3 border-indigo-500 pl-3">
                                <div class="flex-shrink-0 mt-0.5">
                                    <i class="fas fa-tree text-indigo-500 text-sm"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-semibold text-gray-700">{{ $leave->leaveType->name ?? 'Leave' }}</p>
                                    <p class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($leave->start_date)->format('M d') }} - {{ \Carbon\Carbon::parse($leave->end_date)->format('M d') }}</p>
                                </div>
                                <span class="text-xs font-bold text-indigo-600">{{ $leave->total_days }} day{{ $leave->total_days != 1 ? 's' : '' }}</span>
                            </div>
                        @empty
                        @endforelse
                        
                        <!-- Public Holiday Leaves (Fixed Holidays) -->
                        @forelse($upcomingPublicLeaves as $holiday)
                            <div class="flex items-start space-x-3 border-l-3 border-green-500 pl-3">
                                <div class="flex-shrink-0 mt-0.5">
                                    <i class="fas fa-star text-green-500 text-sm"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-semibold text-gray-700">{{ $holiday->name ?? 'Public Holiday' }}</p>
                                    <p class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($holiday->date)->format('M d, Y') }}</p>
                                </div>
                            </div>
                        @empty
                        @endforelse
                    @else
                        <div class="flex flex-col items-center justify-center py-6 text-center">
                            <i class="fas fa-calendar-check text-gray-300 text-3xl mb-2"></i>
                            <p class="text-xs text-gray-500">No upcoming leaves scheduled</p>
                        </div>
                    @endif
                </div>
            </div>
        @endif
        
        <!-- Leave Status Card -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div style="background-color: #059669;" class="px-4 py-2 flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-calendar-day text-white text-sm mr-2"></i>
                    <h3 style="color: white;" class="font-medium text-sm">Leave Status</h3>
                </div>
                <span style="color: #86efac;" class="text-xs">{{ now()->format('M d') }}</span>
            </div>
            
            <div class="p-4 space-y-3">
                <!-- Today Row -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-2 h-2 bg-orange-500 rounded-full mr-2"></div>
                        <span class="text-gray-700 text-xs font-medium">Today</span>
                        <span class="bg-orange-500 text-white px-2 py-0.5 rounded-full text-xs ml-2">{{ $employeesOnLeaveToday->count() }}</span>
                    </div>
                    <div class="flex items-center space-x-1">
                        @forelse($employeesOnLeaveToday->take(2) as $leave)
                            <div class="flex items-center bg-orange-50 px-2 py-1 rounded text-xs">
                                <div class="w-4 h-4 bg-orange-500 rounded-full flex items-center justify-center mr-1">
                                    <span class="text-white text-xs font-bold">{{ strtoupper(substr($leave->user->name ?? 'N', 0, 1)) }}</span>
                                </div>
                                <span class="text-gray-700 truncate max-w-12">{{ Str::limit($leave->user->name ?? 'N/A', 6) }}</span>
                            </div>
                        @empty
                            <span class="text-green-600 text-xs font-medium">All present</span>
                        @endforelse
                        @if($employeesOnLeaveToday->count() > 2)
                            <span class="text-gray-500 text-xs">+{{ $employeesOnLeaveToday->count() - 2 }}</span>
                        @endif
                    </div>
                </div>
                
                <!-- Tomorrow Row -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-2 h-2 bg-purple-500 rounded-full mr-2"></div>
                        <span class="text-gray-700 text-xs font-medium">Tomorrow</span>
                        <span class="bg-purple-500 text-white px-2 py-0.5 rounded-full text-xs ml-2">{{ $employeesOnLeaveTomorrow->count() }}</span>
                    </div>
                    <div class="flex items-center space-x-1">
                        @forelse($employeesOnLeaveTomorrow->take(2) as $leave)
                            <div class="flex items-center bg-purple-50 px-2 py-1 rounded text-xs">
                                <div class="w-4 h-4 bg-purple-500 rounded-full flex items-center justify-center mr-1">
                                    <span class="text-white text-xs font-bold">{{ strtoupper(substr($leave->user->name ?? 'N', 0, 1)) }}</span>
                                </div>
                                <span class="text-gray-700 truncate max-w-12">{{ Str::limit($leave->user->name ?? 'N/A', 6) }}</span>
                            </div>
                        @empty
                            <span class="text-green-600 text-xs font-medium">No leaves</span>
                        @endforelse
                        @if($employeesOnLeaveTomorrow->count() > 2)
                            <span class="text-gray-500 text-xs">+{{ $employeesOnLeaveTomorrow->count() - 2 }}</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Modern Card Group -->
        <style>
        .modern-card {
            position: relative;
            background: #fff;
            border-radius: 1.25rem;
            box-shadow: 0 4px 24px 0 rgba(80,112,255,0.07), 0 1.5px 6px 0 rgba(80,112,255,0.03);
            overflow: hidden;
            transition: box-shadow 0.2s;
        }
        .modern-card:hover {
            box-shadow: 0 8px 32px 0 rgba(80,112,255,0.13), 0 3px 12px 0 rgba(80,112,255,0.07);
        }
        .modern-card-bar {
            position: absolute;
            left: 0; top: 0; bottom: 0;
            width: 6px;
            background: linear-gradient(180deg, #6366f1 0%, #06b6d4 100%);
            border-radius: 1.25rem 0 0 1.25rem;
        }
        
        /* Custom Thin Scrollbar for System Activity */
        .system-activity-scroll {
            scrollbar-width: thin;
            scrollbar-color: #cbd5e1 transparent;
        }
        
        .system-activity-scroll::-webkit-scrollbar {
            width: 1px;
        }
        
        .system-activity-scroll::-webkit-scrollbar-track {
            background: transparent;
        }
        
        .system-activity-scroll::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 0.5px;
            transition: background 0.3s ease;
        }
        
        .system-activity-scroll::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
        
        .system-activity-scroll::-webkit-scrollbar-thumb:active {
            background: #64748b;
        }
        </style>
        
        @if(auth()->user()->isSuperAdmin())
            <!-- Super Admin System Stats -->
            <div class="modern-card p-6">
                <div class="modern-card-bar"></div>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Users</p>
                        <p class="text-3xl font-bold text-gray-900">{{ \App\Models\User::count() }}</p>
                        <p class="text-sm text-green-600 flex items-center mt-1">
                            <i class="fas fa-users text-xs mr-1"></i>
                            System users
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-users text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>
            <div class="modern-card p-6">
                <div class="modern-card-bar" style="background:linear-gradient(180deg,#f59e42 0%,#fbbf24 100%)"></div>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Pending Approvals</p>
                        <p class="text-3xl font-bold text-gray-900">{{ \App\Models\LeaveApplication::where('status', 'pending')->count() }}</p>
                        <p class="text-sm text-yellow-600 flex items-center mt-1">
                            <i class="fas fa-clock text-xs mr-1"></i>
                            System-wide
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-hourglass-half text-yellow-600 text-xl"></i>
                    </div>
                </div>
            </div>
            <div class="modern-card p-6">
                <div class="modern-card-bar" style="background:linear-gradient(180deg,#22c55e 0%,#4ade80 100%)"></div>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">All Leave Applications</p>
                        <p class="text-3xl font-bold text-gray-900">{{ \App\Models\LeaveApplication::count() }}</p>
                        <p class="text-sm text-green-600 flex items-center mt-1">
                            <i class="fas fa-check text-xs mr-1"></i>
                            System-wide total
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-file-alt text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>
            <div class="modern-card p-6">
                <div class="modern-card-bar" style="background:linear-gradient(180deg,#6366f1 0%,#a78bfa 100%)"></div>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Active Employees</p>
                        <p class="text-3xl font-bold text-gray-900">{{ $activeEmployeesCount ?? \App\Models\User::where('role', 'staff')->count() }}</p>
                        <p class="text-sm text-blue-600 flex items-center mt-1">
                            <i class="fas fa-user-check text-xs mr-1"></i>
                            Staff members
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-user-check text-purple-600 text-xl"></i>
                    </div>
                </div>
            </div>
        @else
            <!-- Regular User Leave Stats -->
            <div class="modern-card p-6">
                <div class="modern-card-bar"></div>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Leaves</p>
                        <p class="text-3xl font-bold text-gray-900">{{ auth()->user()->leaveApplications()->count() }}</p>
                        <p class="text-sm text-green-600 flex items-center mt-1">
                            <i class="fas fa-arrow-up text-xs mr-1"></i>
                            +{{ auth()->user()->leaveApplications()->whereMonth('created_at', now()->month)->count() }} this month
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-calendar-check text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>
            <div class="modern-card p-6">
                <div class="modern-card-bar" style="background:linear-gradient(180deg,#f59e42 0%,#fbbf24 100%)"></div>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Pending Approval</p>
                        <p class="text-3xl font-bold text-gray-900">{{ auth()->user()->leaveApplications()->where('status', 'pending')->count() }}</p>
                        <p class="text-sm text-yellow-600 flex items-center mt-1">
                            <i class="fas fa-clock text-xs mr-1"></i>
                            Awaiting review
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-hourglass-half text-yellow-600 text-xl"></i>
                    </div>
                </div>
            </div>
            <div class="modern-card p-6">
                <div class="modern-card-bar" style="background:linear-gradient(180deg,#22c55e 0%,#4ade80 100%)"></div>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Approved</p>
                        <p class="text-3xl font-bold text-gray-900">{{ auth()->user()->leaveApplications()->where('status', 'approved')->count() }}</p>
                        <p class="text-sm text-green-600 flex items-center mt-1">
                            <i class="fas fa-check text-xs mr-1"></i>
                            All time
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-check-circle text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>
            <div class="modern-card p-6">
                <div class="modern-card-bar" style="background:linear-gradient(180deg,#6366f1 0%,#a78bfa 100%)"></div>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Leave Balance</p>
                        <p class="text-3xl font-bold text-gray-900">{{ number_format($leaveBalance, 1) }}</p>
                        <p class="text-sm text-blue-600 flex items-center mt-1">
                            <i class="fas fa-info-circle text-xs mr-1"></i>
                            Paid leave days remaining
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-battery-three-quarters text-purple-600 text-xl"></i>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Quick Actions & Recent Activity -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Quick Actions Modern Card -->
        <div class="lg:col-span-1">
            <div class="modern-card p-6">
                <div class="modern-card-bar"></div>
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-bolt text-blue-600 mr-2"></i>
                    Quick Actions
                </h3>
                <div class="space-y-3">
                    @if (auth()->user()->isStaff())
                        <a href="{{ route('leaves.create') }}" class="flex items-center p-4 bg-gradient-to-r from-blue-50 to-purple-50 rounded-lg hover:from-blue-100 hover:to-purple-100 transition-all duration-200 group">
                            <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform duration-200">
                                <i class="fas fa-plus text-white"></i>
                            </div>
                            <div class="ml-3">
                                <p class="font-medium text-gray-900">Apply for Leave</p>
                                <p class="text-sm text-gray-500">Submit a new leave request</p>
                            </div>
                        </a>
                    @endif
                    <a href="{{ route('leaves.index') }}" class="flex items-center p-4 bg-gradient-to-r from-green-50 to-blue-50 rounded-lg hover:from-green-100 hover:to-blue-100 transition-all duration-200 group">
                        <div class="w-10 h-10 bg-green-600 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform duration-200">
                            <i class="fas fa-list text-white"></i>
                        </div>
                        <div class="ml-3">
                            <p class="font-medium text-gray-900">View My Leaves</p>
                            <p class="text-sm text-gray-500">Check your leave history</p>
                        </div>
                    </a>
                    @if (auth()->user()->isAdmin() || auth()->user()->isSuperAdmin())
                        <a href="{{ route('public-holidays.index') }}" class="flex items-center p-4 bg-gradient-to-r from-purple-50 to-pink-50 rounded-lg hover:from-purple-100 hover:to-pink-100 transition-all duration-200 group">
                            <div class="w-10 h-10 bg-purple-600 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform duration-200">
                                <i class="fas fa-calendar-day text-white"></i>
                            </div>
                            <div class="ml-3">
                                <p class="font-medium text-gray-900">Manage Holidays</p>
                                <p class="text-sm text-gray-500">Configure public holidays</p>
                            </div>
                        </a>
                    @endif
                    <a href="{{ route('profile.show') }}" class="flex items-center p-4 bg-gradient-to-r from-indigo-50 to-blue-50 rounded-lg hover:from-indigo-100 hover:to-blue-100 transition-all duration-200 group">
                        <div class="w-10 h-10 bg-indigo-600 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform duration-200">
                            <i class="fas fa-user-cog text-white"></i>
                        </div>
                        <div class="ml-3">
                            <p class="font-medium text-gray-900">Profile Settings</p>
                            <p class="text-sm text-gray-500">Update your profile</p>
                        </div>
                    </a>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="lg:col-span-2">
            <div class="modern-card p-6">
                <div class="modern-card-bar" style="background:linear-gradient(180deg,#22c55e 0%,#4ade80 100%)"></div>
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-clock text-green-600 mr-2"></i>
                    @if(auth()->user()->isSuperAdmin())
                        System Activity
                    @else
                        Recent Activity
                    @endif
                </h3>
                <div class="space-y-4 max-h-64 overflow-y-auto system-activity-scroll">
                    @if(auth()->user()->isSuperAdmin())
                        @php
                            // Show system-wide recent leave applications for super admin
                            $recentSystemLeaves = \App\Models\LeaveApplication::with(['user', 'leaveType'])->latest()->limit(5)->get();
                        @endphp
                        
                        @forelse($recentSystemLeaves as $leave)
                            <div class="flex items-center p-4 bg-gray-50 rounded-lg">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center
                                    @if($leave->status === 'approved') bg-green-100
                                    @elseif($leave->status === 'rejected') bg-red-100
                                    @else bg-yellow-100
                                    @endif">
                                    <i class="fas 
                                        @if($leave->status === 'approved') fa-check text-green-600
                                        @elseif($leave->status === 'rejected') fa-times text-red-600
                                        @else fa-clock text-yellow-600
                                        @endif"></i>
                                </div>
                                <div class="ml-4 flex-1">
                                    <p class="font-medium text-gray-900">{{ ucfirst($leave->leaveType->name ?? 'General Leave') }} - {{ $leave->user->name ?? 'Unknown' }}</p>
                                    <p class="text-sm text-gray-500">
                                        @if($leave->start_date && $leave->end_date)
                                            @php
                                                $startDate = is_string($leave->start_date) ? \Carbon\Carbon::parse($leave->start_date) : $leave->start_date;
                                                $endDate = is_string($leave->end_date) ? \Carbon\Carbon::parse($leave->end_date) : $leave->end_date;
                                            @endphp
                                            {{ $startDate->format('M d') }} - {{ $endDate->format('M d, Y') }} • 
                                        @endif
                                        <span class="
                                            @if($leave->status === 'approved') text-green-600
                                            @elseif($leave->status === 'rejected') text-red-600
                                            @else text-yellow-600
                                            @endif font-medium">
                                            {{ ucfirst($leave->status) }}
                                        </span>
                                    </p>
                                </div>
                                <div class="text-xs text-gray-400">
                                    @php
                                        $createdAt = is_string($leave->created_at) ? \Carbon\Carbon::parse($leave->created_at) : $leave->created_at;
                                    @endphp
                                    {{ $createdAt->diffForHumans() }}
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-8">
                                <i class="fas fa-inbox text-gray-300 text-4xl mb-4"></i>
                                <p class="text-gray-500">No recent system activity</p>
                                <p class="text-sm text-gray-400">Leave applications will appear here</p>
                            </div>
                        @endforelse
                    @else
                        @php
                            $recentLeaves = auth()->user()->leaveApplications()->with('leaveType')->latest()->limit(5)->get();
                        @endphp
                        
                        @forelse($recentLeaves as $leave)
                            <div class="flex items-center p-4 bg-gray-50 rounded-lg">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center
                                    @if($leave->status === 'approved') bg-green-100
                                    @elseif($leave->status === 'rejected') bg-red-100
                                    @else bg-yellow-100
                                    @endif">
                                    <i class="fas 
                                        @if($leave->status === 'approved') fa-check text-green-600
                                        @elseif($leave->status === 'rejected') fa-times text-red-600
                                        @else fa-clock text-yellow-600
                                        @endif"></i>
                                </div>
                                <div class="ml-4 flex-1">
                                    <p class="font-medium text-gray-900">{{ ucfirst($leave->leaveType->name ?? 'General Leave') }} Request</p>
                                    <p class="text-sm text-gray-500">
                                        @if($leave->start_date && $leave->end_date)
                                            @php
                                                $startDate = is_string($leave->start_date) ? \Carbon\Carbon::parse($leave->start_date) : $leave->start_date;
                                                $endDate = is_string($leave->end_date) ? \Carbon\Carbon::parse($leave->end_date) : $leave->end_date;
                                            @endphp
                                            {{ $startDate->format('M d') }} - {{ $endDate->format('M d, Y') }} • 
                                        @endif
                                        <span class="
                                            @if($leave->status === 'approved') text-green-600
                                            @elseif($leave->status === 'rejected') text-red-600
                                            @else text-yellow-600
                                            @endif font-medium">
                                            {{ ucfirst($leave->status) }}
                                        </span>
                                    </p>
                                </div>
                                <div class="text-xs text-gray-400">
                                    @php
                                        $createdAt = is_string($leave->created_at) ? \Carbon\Carbon::parse($leave->created_at) : $leave->created_at;
                                    @endphp
                                    {{ $createdAt->diffForHumans() }}
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-8">
                                <i class="fas fa-inbox text-gray-300 text-4xl mb-4"></i>
                                <p class="text-gray-500">No recent activity</p>
                                <p class="text-sm text-gray-400">Your leave requests will appear here</p>
                            </div>
                        @endforelse
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Account Info -->
    <div class="modern-card p-6">
        <div class="modern-card-bar" style="background:linear-gradient(180deg,#6366f1 0%,#a78bfa 100%)"></div>
        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
            <i class="fas fa-user-circle text-indigo-600 mr-2"></i>
            @if(auth()->user()->isSuperAdmin())
                System Overview
            @else
                Account Information
            @endif
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @if(auth()->user()->isSuperAdmin())
                <!-- Super Admin System Info -->
                <div class="text-center p-4 bg-gray-50 rounded-lg">
                    <div class="w-16 h-16 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-crown text-white text-2xl"></i>
                    </div>
                    <h4 class="font-medium text-gray-900">{{ auth()->user()->name }}</h4>
                    <p class="text-sm text-gray-500">{{ auth()->user()->email }}</p>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 mt-2">
                        Super Admin
                    </span>
                </div>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Total Users:</span>
                        <span class="font-medium text-gray-900">{{ \App\Models\User::count() }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Active Staff:</span>
                        <span class="font-medium text-gray-900">{{ \App\Models\User::where('role', 'staff')->count() }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Total Leave Apps:</span>
                        <span class="font-medium text-gray-900">{{ \App\Models\LeaveApplication::count() }}</span>
                    </div>
                </div>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Pending Approvals:</span>
                        <span class="font-medium text-gray-900">{{ \App\Models\LeaveApplication::where('status', 'pending')->count() }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">This Month:</span>
                        <span class="font-medium text-gray-900">{{ \App\Models\LeaveApplication::whereMonth('created_at', now()->month)->count() }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">System Status:</span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            Operational
                        </span>
                    </div>
                </div>
            @else
                <!-- Regular User Account Info -->
                <div class="text-center p-4 bg-gray-50 rounded-lg">
                    <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center mx-auto mb-3">
                        <span class="text-white font-bold text-xl">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</span>
                    </div>
                    <h4 class="font-medium text-gray-900">{{ auth()->user()->name }}</h4>
                    <p class="text-sm text-gray-500">{{ auth()->user()->email }}</p>
                </div>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Role:</span>
                        <span class="font-medium text-gray-900 capitalize">{{ auth()->user()->role }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Financial Year:</span>
                        <span class="font-medium text-gray-900">{{ auth()->user()->financial_year }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Member Since:</span>
                        <span class="font-medium text-gray-900">
                            @php
                                $createdAt = is_string(auth()->user()->created_at) ? \Carbon\Carbon::parse(auth()->user()->created_at) : auth()->user()->created_at;
                            @endphp
                            {{ $createdAt->format('M Y') }}
                        </span>
                    </div>
                </div>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Total Requests:</span>
                        <span class="font-medium text-gray-900">{{ auth()->user()->leaveApplications()->count() }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">This Month:</span>
                        <span class="font-medium text-gray-900">{{ auth()->user()->leaveApplications()->whereMonth('created_at', now()->month)->count() }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Status:</span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            Active
                        </span>
                    </div>
                </div>
            @endif
        </div>
    </div>
    </div>

    <!-- Visualization View -->
    <div id="visualizationView" class="space-y-6 hidden">
    <!-- Global Analytics Filter -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between space-y-3 md:space-y-0">
            <div class="flex items-center">
                <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center mr-3">
                    <i class="fas fa-filter text-indigo-600"></i>
                </div>
                <div>
                    <h1 class="text-lg font-bold text-gray-900">Analytics Dashboard</h1>
                    <p class="text-xs text-gray-500">Filter all visualizations by employee and year</p>
                </div>
            </div>
            <div class="flex items-center space-x-3">
                <select id="employeeFilter" class="h-10 rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 w-full md:w-64 px-3 shadow-sm transition-all hover:border-indigo-400">
                    @if(auth()->user()->isAdmin() || auth()->user()->isSuperAdmin())
                        <option value="all" selected>-- All Employees --</option>
                    @endif
                    @foreach($allUsers as $user)
                        <option value="{{ $user->id }}" {{ (!auth()->user()->isAdmin() && !auth()->user()->isSuperAdmin() && $allUsers->count() == 1) ? 'selected' : '' }}>{{ $user->name }}</option>
                    @endforeach
                </select>
                <select id="yearFilter" class="h-10 rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 w-28 px-3 shadow-sm transition-all hover:border-indigo-400">
                    @for($y = date('Y'); $y >= date('Y') - 5; $y--)
                        <option value="{{ $y }}">{{ $y }}</option>
                    @endfor
                </select>
            </div>
        </div>
    </div>

    <!-- Charts Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 md:gap-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 md:p-6 lg:col-span-2">
            <div class="mb-4">
                <h3 class="text-base md:text-lg font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-user-clock text-indigo-600 mr-2"></i>
                    Monthly Leave Accumulation
                    <span class="ml-2 text-xs font-normal text-gray-400 analytic-year-label">({{ date('Y') }})</span>
                </h3>
                <p class="text-xs text-gray-500 mt-1">Represents the total number of <b>approved leave days</b> taken in each month.</p>
            </div>
            <div class="relative h-72 md:h-96" id="employeeChartContainer">
                <canvas id="employeeMonthlyLeaveChart" class="hidden"></canvas>
                <div id="employeeChartPlaceholder" class="absolute inset-0 flex flex-col items-center justify-center bg-gray-50 bg-opacity-50 rounded-lg">
                    <i class="fas fa-chart-bar text-gray-300 text-5xl mb-3"></i>
                    <p class="text-gray-500 font-medium">{{ (auth()->user()->isAdmin() || auth()->user()->isSuperAdmin()) ? 'Loading organization patterns...' : 'Loading your leave patterns...' }}</p>
                </div>
                <div id="employeeChartLoading" class="hidden absolute inset-0 flex flex-col items-center justify-center bg-white bg-opacity-80 rounded-lg z-10">
                    <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-indigo-600"></div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 md:p-6">
            <div class="mb-4">
                <h3 class="text-base md:text-lg font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-chart-line text-blue-600 mr-2"></i>
                    <span>Leave Trends</span>
                    <span class="ml-2 text-xs font-normal text-gray-400 analytic-year-label">({{ date('Y') }})</span>
                </h3>
                <p class="text-xs text-gray-500 mt-1">Shows the total <b>volume of applications</b> (Approved, Pending, or Rejected) active in each month.</p>
            </div>
            <div class="relative h-64 md:h-80">
                <canvas id="leaveTrendsChart"></canvas>
            </div>
        </div>

        <!-- Leave Distribution by Type -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 md:p-6">
            <h3 class="text-base md:text-lg font-semibold text-gray-900 mb-3 md:mb-4 flex items-center">
                <i class="fas fa-chart-pie text-green-600 mr-2"></i>
                <span>Leave Distribution</span>
                <span class="ml-2 text-xs font-normal text-gray-400 analytic-year-label">({{ date('Y') }})</span>
            </h3>
            <div class="relative h-64 md:h-80">
                <canvas id="leaveTypeChart"></canvas>
            </div>
        </div>

        <!-- Monthly Leave Status Breakdown -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 md:p-6">
            <h3 class="text-base md:text-lg font-semibold text-gray-900 mb-3 md:mb-4 flex items-center">
                <i class="fas fa-calendar-alt text-purple-600 mr-2"></i>
                <span>Monthly Status Breakdown</span>
                <span class="ml-2 text-xs font-normal text-gray-400 analytic-year-label">({{ date('Y') }})</span>
            </h3>
            <div class="relative h-72 md:h-96">
                <canvas id="monthlyLeaveChart"></canvas>
            </div>
        </div>

        <!-- Approval Status Overview -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 md:p-6">
            <h3 class="text-base md:text-lg font-semibold text-gray-900 mb-3 md:mb-4 flex items-center">
                <i class="fas fa-check-circle text-teal-600 mr-2"></i>
                <span>Status Overview</span>
                <span class="ml-2 text-xs font-normal text-gray-400 analytic-year-label">({{ date('Y') }})</span>
            </h3>
            <div class="relative h-64 md:h-80">
                <canvas id="approvalStatusChart"></canvas>
            </div>
        </div>
    </div>
        <!-- 3D Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
            {{-- <!-- 3D Leave Balance Visualization -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-cube text-red-600 mr-2"></i>
                    3D Leave Balance Overview
                </h3>
                <div class="relative h-80 bg-gradient-to-br from-blue-50 to-purple-50 rounded-lg flex items-center justify-center">
                    <div class="text-center">
                        <div class="w-32 h-32 mx-auto mb-4 relative">
                            <div class="absolute inset-0 bg-gradient-to-br from-blue-400 to-purple-600 rounded-lg transform rotate-45 animate-pulse"></div>
                            <div class="absolute inset-2 bg-white rounded-lg transform rotate-45 flex items-center justify-center">
                                <i class="fas fa-balance-scale text-3xl text-gray-700"></i>
                            </div>
                        </div>
                        <p class="text-gray-600 font-medium">Interactive 3D Balance Chart</p>
                        <p class="text-sm text-gray-500">Coming Soon</p>
                    </div>
                </div>
            </div>

            <!-- 3D Timeline Visualization -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-timeline text-cyan-600 mr-2"></i>
                    3D Timeline Analysis
                </h3>
                <div class="relative h-80 bg-gradient-to-br from-cyan-50 to-blue-50 rounded-lg flex items-center justify-center">
                    <div class="text-center">
                        <div class="w-32 h-32 mx-auto mb-4 relative">
                            <div class="absolute inset-0 bg-gradient-to-br from-cyan-400 to-blue-600 rounded-full animate-spin" style="animation-duration: 8s;"></div>
                            <div class="absolute inset-4 bg-white rounded-full flex items-center justify-center">
                                <i class="fas fa-clock text-3xl text-gray-700"></i>
                            </div>
                        </div>
                        <p class="text-gray-600 font-medium">3D Timeline Chart</p>
                        <p class="text-sm text-gray-500">Interactive Visualization</p>
                    </div>
                </div>
            </div> --}}
        </div>
    </div>
</div>

<script>
// Global Chart Instances
let leaveTrendsChart = null;
let leaveTypeChart = null;
let monthlyLeaveChart = null;
let approvalStatusChart = null;
let employeeMonthlyChart = null;

// View Toggle Functionality
document.getElementById('normalViewBtn').addEventListener('click', function() {
    document.getElementById('normalView').classList.remove('hidden');
    document.getElementById('visualizationView').classList.add('hidden');
    this.classList.replace('bg-slate-100', 'bg-blue-600');
    this.classList.replace('text-slate-600', 'text-white');
    const analyticsBtn = document.getElementById('visualizationViewBtn');
    analyticsBtn.classList.replace('bg-blue-600', 'bg-slate-100');
    analyticsBtn.classList.replace('text-white', 'text-slate-600');
});

document.getElementById('visualizationViewBtn').addEventListener('click', function() {
    document.getElementById('normalView').classList.add('hidden');
    document.getElementById('visualizationView').classList.remove('hidden');
    this.classList.replace('bg-slate-100', 'bg-blue-600');
    this.classList.replace('text-slate-600', 'text-white');
    const dashboardBtn = document.getElementById('normalViewBtn');
    dashboardBtn.classList.replace('bg-blue-600', 'bg-slate-100');
    dashboardBtn.classList.replace('text-white', 'text-slate-600');
    
    // Trigger initial load of all analytics
    updateAllAnalytics();
});

function updateAllAnalytics() {
    const userId = document.getElementById('employeeFilter').value;
    const year = document.getElementById('yearFilter').value;
    const loading = document.getElementById('employeeChartLoading');
    const trackerCanvas = document.getElementById('employeeMonthlyLeaveChart');
    const placeholder = document.getElementById('employeeChartPlaceholder');

    // Update UI Year Labels
    document.querySelectorAll('.analytic-year-label').forEach(el => el.textContent = `(${year})`);

    loading.classList.remove('hidden');
    if (placeholder) placeholder.classList.add('hidden');
    if (trackerCanvas) trackerCanvas.classList.remove('hidden');

    fetch(`/api/analytics/employee-monthly-leaves?user_id=${userId}&year=${year}`)
        .then(response => response.json())
        .then(data => {
            loading.classList.add('hidden');
            if (data.success) {
                // Use correct keys from API response
                renderTrackerChart(data.approvedDays, data.labels);
                renderTrendsChart(data.requestVolume, data.labels);
                renderDistributionChart(data.distribution);
                renderStatusMonthlyChart(data.statusMonthly, data.labels);
                renderStatusOverviewChart(data.statusOverview);
            }
        })
        .catch(err => {
            console.error('Analytics Fetch Error:', err);
            loading.classList.add('hidden');
        });
}

// Global Chart Options for consistency
const chartFont = { family: "'Inter', 'system-ui', '-apple-system', 'sans-serif'", size: 12 };
const commonOptions = {
    responsive: true,
    maintainAspectRatio: false,
    animation: {
        duration: 1500,
        easing: 'easeOutQuart'
    },
    plugins: {
        legend: {
            display: false,
            position: 'bottom',
            labels: { font: chartFont, usePointStyle: true, padding: 20 }
        },
        tooltip: {
            backgroundColor: 'rgba(17, 24, 39, 0.9)',
            padding: 12,
            cornerRadius: 8,
            titleFont: { ...chartFont, weight: 'bold', size: 13 },
            bodyFont: chartFont,
            usePointStyle: true
        }
    },
    scales: {
        y: {
            beginAtZero: true,
            grid: { color: 'rgba(243, 244, 246, 1)', drawBorder: false },
            ticks: { font: chartFont, color: '#6b7280', padding: 8 }
        },
        x: {
            grid: { display: false },
            ticks: { font: chartFont, color: '#6b7280', padding: 8 }
        }
    }
};

function renderTrackerChart(data, labels) {
    const ctx = document.getElementById('employeeMonthlyLeaveChart').getContext('2d');
    if (employeeMonthlyChart) employeeMonthlyChart.destroy();

    const gradient = ctx.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, '#4f46e5'); // Indigo-600
    gradient.addColorStop(1, '#10b981'); // Teal-500

    employeeMonthlyChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Leaves Taken',
                data: data,
                backgroundColor: gradient,
                hoverBackgroundColor: '#4338ca',
                borderRadius: 8,
                maxBarThickness: 45
            }]
        },
        options: {
            ...commonOptions,
            plugins: {
                ...commonOptions.plugins,
                tooltip: {
                    ...commonOptions.plugins.tooltip,
                    callbacks: {
                        label: (context) => ` ${context.parsed.y} Days Taken`
                    }
                }
            },
            scales: {
                ...commonOptions.scales,
                y: { ...commonOptions.scales.y, ticks: { ...commonOptions.scales.y.ticks, stepSize: 1 } }
            }
        }
    });
}

function renderTrendsChart(data, labels) {
    const ctx = document.getElementById('leaveTrendsChart').getContext('2d');
    if (leaveTrendsChart) leaveTrendsChart.destroy();

    const gradient = ctx.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, 'rgba(59, 130, 246, 0.2)');
    gradient.addColorStop(1, 'rgba(59, 130, 246, 0.01)');

    leaveTrendsChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Application Volume',
                data: data,
                borderColor: '#3b82f6',
                backgroundColor: gradient,
                fill: true,
                tension: 0.4,
                pointRadius: 4,
                pointBackgroundColor: '#fff',
                pointBorderWidth: 2,
                pointHoverRadius: 6
            }]
        },
        options: {
            ...commonOptions,
            plugins: {
                ...commonOptions.plugins,
                tooltip: {
                    ...commonOptions.plugins.tooltip,
                    callbacks: {
                        label: (context) => ` ${context.parsed.y} Applications Active`
                    }
                }
            }
        }
    });
}

function renderDistributionChart(data) {
    const ctx = document.getElementById('leaveTypeChart').getContext('2d');
    if (leaveTypeChart) leaveTypeChart.destroy();

    const hasData = Object.keys(data).length > 0;
    const labels = hasData ? Object.keys(data) : ['No Data'];
    const values = hasData ? Object.values(data) : [1];
    const colors = hasData ? ['#4f46e5', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899'] : ['#f3f4f6'];

    leaveTypeChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: values,
                backgroundColor: colors,
                borderWidth: 0,
                hoverOffset: 15
            }]
        },
        options: {
            ...commonOptions,
            cutout: '65%',
            plugins: {
                ...commonOptions.plugins,
                legend: { display: true, position: 'bottom' },
                tooltip: {
                    ...commonOptions.plugins.tooltip,
                    callbacks: {
                        label: (context) => hasData ? ` ${context.label}: ${context.parsed} Days` : ' No leave data available'
                    }
                }
            },
            scales: { x: { display: false }, y: { display: false } }
        }
    });
}

function renderStatusMonthlyChart(data, labels) {
    const ctx = document.getElementById('monthlyLeaveChart').getContext('2d');
    if (monthlyLeaveChart) monthlyLeaveChart.destroy();

    monthlyLeaveChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                { label: 'Approved', data: data.approved, backgroundColor: '#10b981', borderRadius: 4 },
                { label: 'Pending', data: data.pending, backgroundColor: '#f59e0b', borderRadius: 4 },
                { label: 'Rejected', data: data.rejected, backgroundColor: '#ef4444', borderRadius: 4 }
            ]
        },
        options: {
            ...commonOptions,
            plugins: {
                ...commonOptions.plugins,
                legend: { display: true, position: 'top', align: 'end' }
            },
            scales: {
                x: { ...commonOptions.scales.x, stacked: true },
                y: { ...commonOptions.scales.y, stacked: true }
            }
        }
    });
}

function renderStatusOverviewChart(data) {
    const ctx = document.getElementById('approvalStatusChart').getContext('2d');
    if (approvalStatusChart) approvalStatusChart.destroy();

    const total = data.approved + data.pending + data.rejected;
    const hasData = total > 0;

    approvalStatusChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Approved', 'Pending', 'Rejected'],
            datasets: [{
                data: hasData ? [data.approved, data.pending, data.rejected] : [1, 0, 0],
                backgroundColor: hasData ? ['#10b981', '#f59e0b', '#ef4444'] : ['#f3f4f6'],
                borderWidth: 0,
                hoverOffset: 10
            }]
        },
        options: {
            ...commonOptions,
            cutout: '75%',
            plugins: {
                ...commonOptions.plugins,
                legend: { display: true, position: 'bottom' },
                tooltip: {
                    ...commonOptions.plugins.tooltip,
                    callbacks: {
                        label: (context) => {
                            if (!hasData) return ' No data';
                            const val = context.parsed;
                            const perc = ((val / total) * 100).toFixed(1);
                            return ` ${context.label}: ${val} (${perc}%)`;
                        }
                    }
                }
            },
            scales: { x: { display: false }, y: { display: false } }
        }
    });
}

document.addEventListener('DOMContentLoaded', function() {
    const empFilter = document.getElementById('employeeFilter');
    const yrFilter = document.getElementById('yearFilter');
    if (empFilter) empFilter.addEventListener('change', updateAllAnalytics);
    if (yrFilter) yrFilter.addEventListener('change', updateAllAnalytics);
});
</script>
@endsection