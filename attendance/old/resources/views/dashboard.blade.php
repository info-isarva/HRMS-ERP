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
    @php
        $isRosterViewer = auth()->user()->isAdmin() || auth()->user()->isSuperAdmin() || auth()->user()->role === 'hr';
    @endphp
    <style>
        .dashboard-pair-card {
            height: 11.75rem;
            display: flex;
            flex-direction: column;
        }
        .dashboard-pair-card .card-body {
            flex: 1;
            min-height: 0;
            display: flex;
            flex-direction: column;
        }
        .leave-status-scroll {
            flex: 1;
            min-height: 0;
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: #94a3b8 #f1f5f9;
            position: relative;
        }
        .leave-status-scroll::-webkit-scrollbar { width: 6px; }
        .leave-status-scroll::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 9999px; }
        .leave-status-scroll::-webkit-scrollbar-thumb { background: #94a3b8; border-radius: 9999px; }
        .leave-status-scroll-wrap { position: relative; }
        .leave-status-scroll-wrap::after {
            content: '';
            position: absolute;
            left: 0; right: 0; bottom: 0;
            height: 2rem;
            background: linear-gradient(to bottom, transparent, rgba(255,255,255,0.95));
            pointer-events: none;
        }
        .scroll-more-pill {
            position: absolute;
            bottom: 0.35rem;
            left: 50%;
            transform: translateX(-50%);
            z-index: 2;
            font-size: 0.65rem;
            font-weight: 600;
            color: #64748b;
            background: rgba(255,255,255,0.95);
            border: 1px solid #e2e8f0;
            border-radius: 9999px;
            padding: 0.15rem 0.55rem;
            pointer-events: none;
        }
        /* Green calendar badge — animation ONLY here */
        .roster-calendar-badge {
            width: 3.25rem;
            height: 3.75rem;
            border-radius: 0.65rem;
            background: linear-gradient(180deg, #22c55e 0%, #16a34a 100%);
            box-shadow: 0 4px 14px rgba(34, 197, 94, 0.45);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            animation: calendarGlow 2.4s ease-in-out infinite;
            position: relative;
        }
        .roster-calendar-badge::before {
            content: '';
            position: absolute;
            inset: -3px;
            border-radius: 0.75rem;
            border: 2px solid rgba(34, 197, 94, 0.35);
            animation: calendarRing 2.4s ease-in-out infinite;
            pointer-events: none;
        }
        .roster-calendar-badge .cal-month {
            font-size: 0.55rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: rgba(255,255,255,0.92);
            line-height: 1;
            margin-bottom: 0.15rem;
            animation: dayNudge 2.4s ease-in-out infinite;
        }
        .roster-calendar-badge .cal-day {
            font-size: 1.35rem;
            font-weight: 800;
            color: #fff;
            line-height: 1;
            animation: numberPop 2.4s ease-in-out infinite;
        }
        @keyframes calendarGlow {
            0%, 100% { box-shadow: 0 4px 14px rgba(34, 197, 94, 0.45); }
            50% { box-shadow: 0 6px 22px rgba(34, 197, 94, 0.65); }
        }
        @keyframes calendarRing {
            0%, 100% { opacity: 0.35; transform: scale(1); }
            50% { opacity: 0.75; transform: scale(1.06); }
        }
        @keyframes dayNudge {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-1px); }
        }
        @keyframes numberPop {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.08); }
        }
        .roster-date-static {
            color: #374151;
            font-weight: 600;
            font-size: 0.875rem;
            opacity: 1;
        }
        .available-leave-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.65rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 700;
            background: #fef2f2;
            color: #b91c1c;
            border: 1px solid #fecaca;
            animation: availableLeavePulse 1.6s ease-in-out infinite;
        }
        @keyframes availableLeavePulse {
            0%, 100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.25); }
            50% { transform: scale(1.04); box-shadow: 0 0 0 4px rgba(239, 68, 68, 0); }
        }
    </style>

    {{-- ADMIN / SUPER_ADMIN / HR: Roster FIRST --}}
    @if($canViewLeaveRoster ?? false)
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 bg-gray-50">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-center gap-4 min-w-0">
                    <div class="roster-calendar-badge" aria-hidden="true">
                        <span class="cal-month">{{ $leaveRosterDateCarbon->format('M') }}</span>
                        <span class="cal-day">{{ $leaveRosterDateCarbon->format('d') }}</span>
                    </div>
                    <div class="min-w-0">
                        <h3 class="text-gray-900 font-semibold text-base">Employees on Leave</h3>
                        <p class="roster-date-static mt-0.5">{{ $leaveRosterDateCarbon->format('l, F j, Y') }}</p>
                    </div>
                </div>
                <form method="GET" action="{{ route('dashboard') }}" id="roster-date-form" class="flex-shrink-0">
                    <label for="leave_date" class="sr-only">Select date</label>
                    <input type="date" name="leave_date" id="leave_date" value="{{ $leaveRosterDate }}"
                        onchange="document.getElementById('roster-date-form').submit()"
                        class="h-10 rounded-lg border border-gray-300 bg-white text-gray-800 text-sm px-4 py-2 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                </form>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs text-gray-500 uppercase bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-5 py-3">Employee</th>
                        <th class="px-5 py-3">Department</th>
                        <th class="px-5 py-3">Leave Type</th>
                        <th class="px-5 py-3">Available Leave</th>
                        <th class="px-5 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($leavesOnSelectedDate as $leave)
                        @php $balance = $rosterLeaveBalances[$leave->user_id] ?? null; @endphp
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-5 py-3 whitespace-nowrap">
                                <div class="font-medium text-gray-900">{{ $leave->user->name ?? 'N/A' }}</div>
                                <div class="text-xs text-gray-500">{{ $leave->user->email ?? '' }}</div>
                            </td>
                            <td class="px-5 py-3 text-gray-600">{{ $leave->user->department->name ?? '—' }}</td>
                            <td class="px-5 py-3">
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ $leave->leaveType->name ?? 'Leave' }}
                                </span>
                            </td>
                            <td class="px-5 py-3">
                                @if($balance !== null)
                                    <span class="available-leave-badge">{{ number_format($balance, 1) }} days</span>
                                @else
                                    <span class="text-gray-400 text-xs">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-3">
                                @php
                                    $statusColors = [
                                        'approved' => 'bg-green-100 text-green-800',
                                        'approved_by_manager' => 'bg-teal-100 text-teal-800',
                                        'pending' => 'bg-yellow-100 text-yellow-800',
                                        'forwarded_to_manager' => 'bg-orange-100 text-orange-800',
                                    ];
                                    $statusClass = $statusColors[$leave->status] ?? 'bg-gray-100 text-gray-800';
                                @endphp
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $statusClass }}">
                                    {{ str_replace('_', ' ', ucfirst($leave->status)) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-10 text-center text-gray-500">
                                <i class="fas fa-user-check text-3xl text-gray-300 mb-2"></i>
                                <p class="text-sm">No employees on leave for this date.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- SECOND: Public Holidays + Leave Status (admin/hr) OR Upcoming Leaves + Leave Status (staff) --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        @if($isRosterViewer)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden dashboard-pair-card">
                <div style="background-color: #4f46e5;" class="px-4 py-2 flex items-center justify-between flex-shrink-0">
                    <div class="flex items-center">
                        <i class="fas fa-calendar-day text-white text-sm mr-2"></i>
                        <h3 style="color: white;" class="font-medium text-sm">Upcoming Public Holidays</h3>
                    </div>
                    <a href="{{ route('public-holidays.index') }}" class="text-xs font-medium text-indigo-100 hover:text-white transition-colors">
                        View all <i class="fas fa-arrow-right ml-1 text-[10px]"></i>
                    </a>
                </div>
                <div class="card-body p-3">
                    <ul class="space-y-2 overflow-y-auto flex-1 min-h-0">
                        @forelse($upcomingPublicHolidays->take(4) as $holiday)
                            <li class="flex items-center gap-3 px-2 py-2 rounded-lg border-l-4 border-indigo-500 bg-indigo-50/60">
                                <div class="w-7 h-7 bg-white rounded-full flex items-center justify-center text-indigo-600 shadow-sm flex-shrink-0">
                                    <i class="fas fa-star text-[10px]"></i>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs font-bold text-gray-800 truncate">{{ $holiday->name }}</p>
                                    <p class="text-[10px] text-gray-500">{{ \Carbon\Carbon::parse($holiday->date)->format('l, M d, Y') }}</p>
                                </div>
                            </li>
                        @empty
                            <li class="text-center py-6 text-gray-400 text-xs">No upcoming public holidays</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        @else
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden dashboard-pair-card">
                <div style="background-color: #7c3aed;" class="px-4 py-2 flex items-center justify-between flex-shrink-0">
                    <div class="flex items-center">
                        <i class="fas fa-calendar-check text-white text-sm mr-2"></i>
                        <h3 style="color: white;" class="font-medium text-sm">Upcoming Leaves</h3>
                    </div>
                    <span style="color: #ddd6fe;" class="text-xs">{{ now()->format('M d') }}</span>
                </div>
                <div class="card-body p-3 overflow-y-auto">
                    @php
                        $user = auth()->user();
                        $todayDash = now()->startOfDay();
                        $upcomingLeaves = \App\Models\LeaveApplication::where('user_id', $user->id)
                            ->whereIn('status', ['approved', 'approved_by_manager'])
                            ->where('start_date', '>=', $todayDash)
                            ->orderBy('start_date')
                            ->take(4)
                            ->get();
                        $employee = \App\Models\Employee::where('email', $user->email)->first();
                        $upcomingPublicLeaves = collect();
                        if ($employee && $employee->department_id) {
                            $upcomingPublicLeaves = \App\Models\PublicHoliday::whereHas('departments', function($q) use ($employee) {
                                $q->where('department_id', $employee->department_id);
                            })
                            ->where('type', 'fixed')
                            ->where('date', '>=', $todayDash)
                            ->orderBy('date')
                            ->take(2)
                            ->get();
                        }
                    @endphp
                    <ul class="space-y-2">
                        @forelse($upcomingLeaves as $leave)
                            <li class="flex items-start gap-2 border-l-3 border-indigo-500 pl-2 py-1">
                                <i class="fas fa-tree text-indigo-500 text-xs mt-0.5"></i>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-semibold text-gray-700 truncate">{{ $leave->leaveType->name ?? 'Leave' }}</p>
                                    <p class="text-[10px] text-gray-500">{{ \Carbon\Carbon::parse($leave->start_date)->format('M d') }} – {{ \Carbon\Carbon::parse($leave->end_date)->format('M d') }}</p>
                                </div>
                                <span class="text-[10px] font-bold text-indigo-600">{{ $leave->total_days }}d</span>
                            </li>
                        @empty
                            @if($upcomingPublicLeaves->isEmpty())
                                <li class="text-center py-6 text-gray-400 text-xs">No upcoming leaves scheduled</li>
                            @endif
                        @endforelse
                        @foreach($upcomingPublicLeaves as $holiday)
                            <li class="flex items-start gap-2 border-l-3 border-green-500 pl-2 py-1">
                                <i class="fas fa-star text-green-500 text-xs mt-0.5"></i>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-semibold text-gray-700 truncate">{{ $holiday->name ?? 'Public Holiday' }}</p>
                                    <p class="text-[10px] text-gray-500">{{ \Carbon\Carbon::parse($holiday->date)->format('M d, Y') }}</p>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden dashboard-pair-card">
            <div style="background-color: #059669;" class="px-4 py-2 flex items-center justify-between flex-shrink-0">
                <div class="flex items-center">
                    <i class="fas fa-calendar-day text-white text-sm mr-2"></i>
                    <h3 style="color: white;" class="font-medium text-sm">Leave Status</h3>
                </div>
                <span style="color: #86efac;" class="text-xs">{{ now()->format('M d') }}</span>
            </div>
            <div class="card-body p-3">
                <div class="leave-status-scroll-wrap flex-1 min-h-0 flex flex-col">
                    <div class="leave-status-scroll space-y-3 pr-1" id="leaveStatusScroll">
                        <div>
                            <div class="flex items-center mb-1.5">
                                <div class="w-2 h-2 bg-orange-500 rounded-full mr-2"></div>
                                <span class="text-gray-700 text-xs font-semibold">Today</span>
                                <span class="bg-orange-500 text-white px-2 py-0.5 rounded-full text-[10px] ml-2">{{ $employeesOnLeaveToday->count() }}</span>
                            </div>
                            <ul class="space-y-1.5">
                                @forelse($employeesOnLeaveToday as $leave)
                                    <li class="flex items-center gap-2 bg-orange-50 px-2 py-1.5 rounded text-xs">
                                        <div class="w-5 h-5 bg-orange-500 rounded-full flex items-center justify-center flex-shrink-0">
                                            <span class="text-white text-[9px] font-bold">{{ strtoupper(substr($leave->user->name ?? 'N', 0, 1)) }}</span>
                                        </div>
                                        <span class="text-gray-700 truncate flex-1">{{ $leave->user->name ?? 'N/A' }}</span>
                                        <span class="text-orange-600 text-[10px] font-medium">{{ $leave->leaveType->name ?? 'Leave' }}</span>
                                    </li>
                                @empty
                                    <li class="text-green-600 text-xs font-medium px-1">All present</li>
                                @endforelse
                            </ul>
                        </div>
                        <div>
                            <div class="flex items-center mb-1.5">
                                <div class="w-2 h-2 bg-purple-500 rounded-full mr-2"></div>
                                <span class="text-gray-700 text-xs font-semibold">Tomorrow</span>
                                <span class="bg-purple-500 text-white px-2 py-0.5 rounded-full text-[10px] ml-2">{{ $employeesOnLeaveTomorrow->count() }}</span>
                            </div>
                            <ul class="space-y-1.5">
                                @forelse($employeesOnLeaveTomorrow as $leave)
                                    <li class="flex items-center gap-2 bg-purple-50 px-2 py-1.5 rounded text-xs">
                                        <div class="w-5 h-5 bg-purple-500 rounded-full flex items-center justify-center flex-shrink-0">
                                            <span class="text-white text-[9px] font-bold">{{ strtoupper(substr($leave->user->name ?? 'N', 0, 1)) }}</span>
                                        </div>
                                        <span class="text-gray-700 truncate flex-1">{{ $leave->user->name ?? 'N/A' }}</span>
                                        <span class="text-purple-600 text-[10px] font-medium">{{ $leave->leaveType->name ?? 'Leave' }}</span>
                                    </li>
                                @empty
                                    <li class="text-green-600 text-xs font-medium px-1">No leaves scheduled</li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                    <span class="scroll-more-pill" id="leaveStatusScrollHint" style="display:none;">Scroll for more</span>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const el = document.getElementById('leaveStatusScroll');
            const hint = document.getElementById('leaveStatusScrollHint');
            if (el && hint && el.scrollHeight > el.clientHeight + 4) {
                hint.style.display = 'block';
            }
        });
    </script>

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
    <!-- Charts Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 md:gap-6">
        <!-- Leave Trends Over Time -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 md:p-6">
            <h3 class="text-base md:text-lg font-semibold text-gray-900 mb-3 md:mb-4 flex items-center">
                <i class="fas fa-chart-line text-blue-600 mr-2"></i>
                <span class="hidden sm:inline">Leave Trends (Last 6 Months)</span>
                <span class="sm:hidden">Leave Trends</span>
            </h3>
            <div class="relative h-64 md:h-80">
                <canvas id="leaveTrendsChart"></canvas>
            </div>
        </div>

        <!-- Leave Distribution by Type -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 md:p-6">
            <h3 class="text-base md:text-lg font-semibold text-gray-900 mb-3 md:mb-4 flex items-center">
                <i class="fas fa-chart-pie text-green-600 mr-2"></i>
                <span class="hidden sm:inline">Leave Distribution by Type</span>
                <span class="sm:hidden">Leave Types</span>
            </h3>
            <div class="relative h-64 md:h-80">
                <canvas id="leaveTypeChart"></canvas>
            </div>
        </div>

        <!-- Monthly Leave Applications -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 md:p-6">
            <h3 class="text-base md:text-lg font-semibold text-gray-900 mb-3 md:mb-4 flex items-center">
                <i class="fas fa-calendar-alt text-purple-600 mr-2"></i>
                <span class="hidden sm:inline">Monthly Leave Applications (Last 6 Months)</span>
                <span class="sm:hidden">Monthly Leaves</span>
            </h3>
            <div class="relative h-72 md:h-96">
                <canvas id="monthlyLeaveChart"></canvas>
            </div>
        </div>

        <!-- Approval Status Overview -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 md:p-6">
            <h3 class="text-base md:text-lg font-semibold text-gray-900 mb-3 md:mb-4 flex items-center">
                <i class="fas fa-check-circle text-teal-600 mr-2"></i>
                <span class="hidden sm:inline">Approval Status Overview</span>
                <span class="sm:hidden">Approval Status</span>
            </h3>
            <div class="relative h-64 md:h-80">
                <canvas id="approvalStatusChart"></canvas>
            </div>
        </div>
    </div>        <!-- 3D Charts Section -->
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
// View Toggle Functionality
document.getElementById('normalViewBtn').addEventListener('click', function() {
    document.getElementById('normalView').classList.remove('hidden');
    document.getElementById('visualizationView').classList.add('hidden');
    // Set active state for Dashboard button
    this.classList.remove('bg-slate-100', 'text-slate-600', 'hover:bg-slate-200', 'hover:text-slate-800');
    this.classList.add('bg-blue-600', 'text-white', 'hover:bg-blue-700');
    // Set inactive state for Analytics button
    const analyticsBtn = document.getElementById('visualizationViewBtn');
    analyticsBtn.classList.remove('bg-blue-600', 'text-white', 'hover:bg-blue-700');
    analyticsBtn.classList.add('bg-slate-100', 'text-slate-600', 'hover:bg-slate-200', 'hover:text-slate-800');
});

document.getElementById('visualizationViewBtn').addEventListener('click', function() {
    document.getElementById('normalView').classList.add('hidden');
    document.getElementById('visualizationView').classList.remove('hidden');
    // Set active state for Analytics button
    this.classList.remove('bg-slate-100', 'text-slate-600', 'hover:bg-slate-200', 'hover:text-slate-800');
    this.classList.add('bg-blue-600', 'text-white', 'hover:bg-blue-700');
    // Set inactive state for Dashboard button
    const dashboardBtn = document.getElementById('normalViewBtn');
    dashboardBtn.classList.remove('bg-blue-600', 'text-white', 'hover:bg-blue-700');
    dashboardBtn.classList.add('bg-slate-100', 'text-slate-600', 'hover:bg-slate-200', 'hover:text-slate-800');
    initializeCharts();
});

// Initialize Charts
function initializeCharts() {
    // Generate last 6 months labels (current month + 5 previous)
    const generateMonthLabels = () => {
        const labels = [];
        const now = new Date();
        for (let i = 5; i >= 0; i--) {
            const date = new Date(now.getFullYear(), now.getMonth() - i, 1);
            labels.push(date.toLocaleDateString('en-US', { month: 'short', year: '2-digit' }));
        }
        return labels;
    };

    // Leave Trends Over Time - Modern Line Chart
    const leaveTrendsCtx = document.getElementById('leaveTrendsChart').getContext('2d');
    const leaveTrendsGradient = leaveTrendsCtx.createLinearGradient(0, 0, 0, 400);
    leaveTrendsGradient.addColorStop(0, 'rgba(59, 130, 246, 0.3)');
    leaveTrendsGradient.addColorStop(1, 'rgba(59, 130, 246, 0.05)');

    new Chart(leaveTrendsCtx, {
        type: 'line',
        data: {
            labels: generateMonthLabels(),
            datasets: [{
                label: 'Leave Applications',
                data: Object.values(leaveTrendsData), // Use dynamic data from PHP
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: leaveTrendsGradient,
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#ffffff',
                pointBorderColor: 'rgb(59, 130, 246)',
                pointBorderWidth: 3,
                pointRadius: 6,
                pointHoverRadius: 8,
                pointHoverBackgroundColor: 'rgb(59, 130, 246)',
                pointHoverBorderColor: '#ffffff',
                pointHoverBorderWidth: 3,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                intersect: false,
                mode: 'index'
            },
            plugins: {
                legend: {
                    display: false
                },
                title: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleColor: '#ffffff',
                    bodyColor: '#ffffff',
                    borderColor: 'rgb(59, 130, 246)',
                    borderWidth: 1,
                    cornerRadius: 8,
                    displayColors: false,
                    callbacks: {
                        title: function(context) {
                            return `Month: ${context[0].label}`;
                        },
                        label: function(context) {
                            return `Leave Applications: ${context.parsed.y}`;
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        color: '#6b7280',
                        font: {
                            size: 12,
                            weight: '500'
                        }
                    }
                },
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)',
                        lineWidth: 1
                    },
                    ticks: {
                        color: '#6b7280',
                        font: {
                            size: 12,
                            weight: '500'
                        },
                        callback: function(value) {
                            return value;
                        }
                    }
                }
            },
            elements: {
                point: {
                    hoverBorderWidth: 3
                }
            },
            animation: {
                duration: 2000,
                easing: 'easeInOutQuart'
            }
        }
    });

    // Leave Distribution by Type - Doughnut Chart
    const leaveTypeCtx = document.getElementById('leaveTypeChart').getContext('2d');
    new Chart(leaveTypeCtx, {
        type: 'doughnut',
        data: {
            labels: Object.keys(leaveTypeData), // Dynamic leave type names
            datasets: [{
                data: Object.values(leaveTypeData), // Dynamic leave type counts
                backgroundColor: [
                    'rgb(34, 197, 94)',
                    'rgb(239, 68, 68)',
                    'rgb(168, 85, 247)',
                    'rgb(236, 72, 153)',
                    'rgb(245, 158, 11)',
                    'rgb(59, 130, 246)',
                    'rgb(16, 185, 129)',
                    'rgb(251, 191, 36)',
                    'rgb(139, 69, 19)',
                    'rgb(107, 114, 128)'
                ],
                borderWidth: 3,
                borderColor: '#ffffff',
                hoverBorderWidth: 4,
                hoverBorderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true,
                        font: {
                            size: 12,
                            weight: '500'
                        }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleColor: '#ffffff',
                    bodyColor: '#ffffff',
                    borderColor: 'rgb(34, 197, 94)',
                    borderWidth: 1,
                    cornerRadius: 8,
                    callbacks: {
                        label: function(context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((context.parsed / total) * 100).toFixed(1);
                            return `${context.label}: ${context.parsed} (${percentage}%)`;
                        }
                    }
                }
            },
            animation: {
                duration: 2000,
                easing: 'easeInOutQuart'
            }
        }
    });

    // Monthly Leave Applications - Bar Chart
    const monthlyLeaveCtx = document.getElementById('monthlyLeaveChart').getContext('2d');
    new Chart(monthlyLeaveCtx, {
        type: 'bar',
        data: {
            labels: monthlyLeaveData.labels || ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [{
                label: 'Approved',
                data: monthlyLeaveData.approved || [8, 12, 10, 15, 18, 14],
                backgroundColor: 'rgba(34, 197, 94, 0.8)',
                borderColor: 'rgb(34, 197, 94)',
                borderWidth: 1
            }, {
                label: 'Pending',
                data: monthlyLeaveData.pending || [3, 5, 4, 7, 6, 8],
                backgroundColor: 'rgba(245, 158, 11, 0.8)',
                borderColor: 'rgb(245, 158, 11)',
                borderWidth: 1
            }, {
                label: 'Rejected',
                data: monthlyLeaveData.rejected || [1, 2, 1, 3, 2, 1],
                backgroundColor: 'rgba(239, 68, 68, 0.8)',
                borderColor: 'rgb(239, 68, 68)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                }
            },
            scales: {
                x: {
                    stacked: true,
                },
                y: {
                    stacked: true,
                    beginAtZero: true
                }
            }
        }
    });

    // Approval Status Overview - 3D Doughnut Chart
    const approvalStatusCtx = document.getElementById('approvalStatusChart').getContext('2d');

    // Create gradient backgrounds for 3D effect
    const approvedGradient = approvalStatusCtx.createLinearGradient(0, 0, 0, 400);
    approvedGradient.addColorStop(0, 'rgba(34, 197, 94, 0.9)');
    approvedGradient.addColorStop(0.5, 'rgba(34, 197, 94, 0.7)');
    approvedGradient.addColorStop(1, 'rgba(34, 197, 94, 1)');

    const pendingGradient = approvalStatusCtx.createLinearGradient(0, 0, 0, 400);
    pendingGradient.addColorStop(0, 'rgba(245, 158, 11, 0.9)');
    pendingGradient.addColorStop(0.5, 'rgba(245, 158, 11, 0.7)');
    pendingGradient.addColorStop(1, 'rgba(245, 158, 11, 1)');

    const rejectedGradient = approvalStatusCtx.createLinearGradient(0, 0, 0, 400);
    rejectedGradient.addColorStop(0, 'rgba(239, 68, 68, 0.9)');
    rejectedGradient.addColorStop(0.5, 'rgba(239, 68, 68, 0.7)');
    rejectedGradient.addColorStop(1, 'rgba(239, 68, 68, 1)');

    new Chart(approvalStatusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Approved', 'Pending', 'Rejected'],
            datasets: [{
                data: [
                    approvalStatusData.approved || 0,
                    approvalStatusData.pending || 0,
                    approvalStatusData.rejected || 0
                ],
                backgroundColor: [
                    approvedGradient,
                    pendingGradient,
                    rejectedGradient
                ],
                borderColor: [
                    'rgba(34, 197, 94, 1)',
                    'rgba(245, 158, 11, 1)',
                    'rgba(239, 68, 68, 1)'
                ],
                borderWidth: 4,
                hoverBackgroundColor: [
                    'rgba(34, 197, 94, 1)',
                    'rgba(245, 158, 11, 1)',
                    'rgba(239, 68, 68, 1)'
                ],
                hoverBorderColor: [
                    'rgba(255, 255, 255, 0.8)',
                    'rgba(255, 255, 255, 0.8)',
                    'rgba(255, 255, 255, 0.8)'
                ],
                hoverBorderWidth: 6,
                offset: [10, 10, 10], // Slight separation for 3D effect
                borderRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '60%', // Thick doughnut for 3D appearance
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true,
                        font: {
                            size: 14,
                            weight: '600'
                        },
                        generateLabels: function(chart) {
                            const data = chart.data;
                            return data.labels.map((label, i) => ({
                                text: `${label}: ${data.datasets[0].data[i]}`,
                                fillStyle: data.datasets[0].backgroundColor[i],
                                strokeStyle: data.datasets[0].borderColor[i],
                                lineWidth: 2,
                                hidden: false,
                                index: i
                            }));
                        }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.9)',
                    titleColor: '#ffffff',
                    bodyColor: '#ffffff',
                    borderColor: 'rgba(255, 255, 255, 0.3)',
                    borderWidth: 1,
                    cornerRadius: 12,
                    displayColors: true,
                    callbacks: {
                        label: function(context) {
                            const value = context.parsed;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                            return `${context.label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            },
            animation: {
                duration: 2500,
                easing: 'easeInOutQuart',
                animateScale: true,
                animateRotate: true
            },
            elements: {
                arc: {
                    borderRadius: 8,
                    borderAlign: 'inner'
                }
            }
        }
    });
}
</script>
@endsection