@extends('layouts.app')

@section('title', 'Activity Logs - HRMS')

@push('styles')
<style>
    .header-gradient {
        background: linear-gradient(135deg, #2563eb 0%, #7c3aed 100%);
    }
    .card-hover {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border: 1px solid #e5e7eb;
        background: white;
    }
    .card-hover:hover {
        transform: translateY(-2px);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        border-color: #d1d5db;
    }
    .pulse-animation {
        animation: pulse 2s infinite;
    }
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.8; }
    }
    .filter-card {
        background: white;
        border: 1px solid #e5e7eb;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }
    .table-row-hover:hover {
        background: linear-gradient(90deg, rgba(59, 130, 246, 0.05) 0%, rgba(147, 51, 234, 0.05) 100%);
        transform: scale(1.002);
        transition: all 0.2s ease;
    }
    .security-badge {
        background: linear-gradient(45deg, #dc2626 0%, #ef4444 100%);
        color: white;
        font-family: 'Courier New', monospace;
        box-shadow: 0 4px 6px -1px rgba(220, 38, 38, 0.3);
    }
    .ip-badge {
        background: linear-gradient(45deg, #2563eb 0%, #3b82f6 100%);
        color: white;
        font-family: 'Courier New', monospace;
        box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.3);
    }
    .server-ip-badge {
        background: linear-gradient(45deg, #7c3aed 0%, #8b5cf6 100%);
        color: white;
        font-family: 'Courier New', monospace;
        box-shadow: 0 4px 6px -1px rgba(124, 58, 237, 0.3);
    }
    .stats-card {
        background: white;
        border: 1px solid #e5e7eb;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }
    .activity-icon {
        transition: all 0.3s ease;
    }
    .activity-icon:hover {
        transform: rotate(10deg) scale(1.1);
    }
    .text-primary {
        color: #2563eb;
    }
    .text-secondary {
        color: #7c3aed;
    }
    .shine-effect {
        position: relative;
        overflow: hidden;
    }
    .shine-effect::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(37, 99, 235, 0.1), transparent);
        animation: shine 3s infinite;
    }
    @keyframes shine {
        0% { left: -100%; }
        100% { left: 100%; }
    }
    .security-alert {
        background: linear-gradient(45deg, #dc2626 0%, #b91c1c 100%);
        color: white;
        animation: glow 2s ease-in-out infinite alternate;
    }
    @keyframes glow {
        from { box-shadow: 0 0 5px rgba(220, 38, 38, 0.5); }
        to { box-shadow: 0 0 20px rgba(220, 38, 38, 0.8); }
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="container mx-auto px-4 py-8 max-w-full">
        <!-- Header Section -->
        <div class="header-gradient rounded-2xl shadow-xl p-8 mb-8 text-white shine-effect">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center backdrop-blur-sm border border-white/30">
                        <i class="fas fa-shield-alt text-3xl text-white"></i>
                    </div>
                    <div>
                        <h1 class="text-4xl font-bold mb-2">Activity Logs</h1>
                        <p class="text-white/90 text-lg">
                            <i class="fas fa-history mr-2"></i>
                            System monitoring and activity tracking
                        </p>
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-sm text-white/70 mb-1">
                        <i class="fas fa-user-shield mr-1"></i>
                        Super Admin Access
                    </div>
                    <div class="text-2xl font-bold text-white">{{ auth()->user()->name }}</div>
                    <div class="text-xs text-white/70">
                        <i class="fas fa-clock mr-1"></i>
                        {{ now()->format('Y-m-d H:i:s') }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="stats-card rounded-xl p-6 card-hover border-l-4 border-blue-600">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Activities</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_activities']) }}</p>
                    </div>
                    <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg flex items-center justify-center activity-icon">
                        <i class="fas fa-database text-white"></i>
                    </div>
                </div>
            </div>

            <div class="stats-card rounded-xl p-6 card-hover border-l-4 border-purple-600">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Today's Events</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['today_activities']) }}</p>
                    </div>
                    <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg flex items-center justify-center activity-icon">
                        <i class="fas fa-calendar-day text-white"></i>
                    </div>
                </div>
            </div>

            <div class="stats-card rounded-xl p-6 card-hover border-l-4 border-indigo-600">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Weekly Activity</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['week_activities']) }}</p>
                    </div>
                    <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-lg flex items-center justify-center activity-icon">
                        <i class="fas fa-chart-line text-white"></i>
                    </div>
                </div>
            </div>

            <div class="stats-card rounded-xl p-6 card-hover border-l-4 border-emerald-600">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Active Users</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['unique_users']) }}</p>
                    </div>
                    <div class="w-12 h-12 bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-lg flex items-center justify-center activity-icon">
                        <i class="fas fa-users text-white"></i>
                    </div>
                </div>
            </div>
        </div>
        <!-- Filters Section -->
        <div class="filter-card rounded-2xl shadow-lg p-6 mb-8">
            <h2 class="text-xl font-semibold text-gray-900 mb-4 flex items-center">
                <i class="fas fa-filter mr-2 text-blue-600"></i>
                Activity Filters
            </h2>
            
            <form method="GET" action="{{ route('activity-logs.index') }}" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                    <!-- Search -->
                    <div class="relative">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-search mr-1"></i>Search Query
                        </label>
                        <div class="relative">
                            <input type="text" name="search" value="{{ request('search') }}" 
                                   placeholder="Search activities..." 
                                   class="w-full pl-10 pr-4 py-2 bg-white border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all text-gray-900 placeholder-gray-500">
                            <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        </div>
                    </div>

                    <!-- Log Name -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-tags mr-1"></i>Log Category
                        </label>
                        <select name="log_name" class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all text-gray-900">
                            <option value="">All Categories</option>
                            @foreach($logNames as $logName)
                                <option value="{{ $logName }}" {{ request('log_name') == $logName ? 'selected' : '' }}>
                                    {{ ucfirst(str_replace('_', ' ', $logName)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Event Type -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-bolt mr-1"></i>Event Type
                        </label>
                        <select name="event" class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all text-gray-900">
                            <option value="">All Events</option>
                            @foreach($events as $event)
                                <option value="{{ $event }}" {{ request('event') == $event ? 'selected' : '' }}>
                                    {{ ucfirst($event) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- User -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-user mr-1"></i>User
                        </label>
                        <select name="causer_id" class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all text-gray-900">
                            <option value="">All Users</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ request('causer_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }} ({{ $user->email }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Date From -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-calendar-alt mr-1"></i>From Date
                        </label>
                        <input type="date" name="date_from" value="{{ request('date_from') }}" 
                               class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all text-gray-900">
                    </div>

                    <!-- Date To -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-calendar-alt mr-1"></i>To Date
                        </label>
                        <input type="date" name="date_to" value="{{ request('date_to') }}" 
                               class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all text-gray-900">
                    </div>
                </div>

                <div class="flex flex-wrap gap-3 pt-4">
                    <button type="submit" class="px-6 py-2 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-lg hover:from-blue-700 hover:to-purple-700 transition-all shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                        <i class="fas fa-search mr-2"></i>Apply Filters
                    </button>
                    <a href="{{ route('activity-logs.index') }}" class="px-6 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-all">
                        <i class="fas fa-times mr-2"></i>Clear Filters
                    </a>
                    <a href="{{ route('activity-logs.export', request()->all()) }}" class="px-6 py-2 bg-gradient-to-r from-emerald-600 to-green-600 text-white rounded-lg hover:from-emerald-700 hover:to-green-700 transition-all">
                        <i class="fas fa-download mr-2"></i>Export CSV
                    </a>
                    <button type="button" onclick="showCleanupModal()" class="px-6 py-2 bg-gradient-to-r from-red-600 to-pink-600 text-white rounded-lg hover:from-red-700 hover:to-pink-700 transition-all security-alert">
                        <i class="fas fa-trash mr-2"></i>Cleanup Old Logs
                    </button>
                </div>
            </form>
        </div>

        <!-- Activity Logs Table -->
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden border border-gray-200">
            <div class="p-6 bg-gradient-to-r from-blue-50 to-purple-50 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-semibold text-gray-900">
                        <i class="fas fa-list-ul mr-2 text-blue-600"></i>
                        Activity Logs
                    </h2>
                    <div class="flex items-center space-x-2">
                        <div class="w-3 h-3 bg-green-500 rounded-full pulse-animation"></div>
                        <span class="text-sm text-gray-600">
                            <i class="fas fa-wifi mr-1"></i>
                            Live Updates
                        </span>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <i class="fas fa-cog mr-1"></i>Activity
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <i class="fas fa-user mr-1"></i>User
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <i class="fas fa-tag mr-1"></i>Type
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <i class="fas fa-network-wired mr-1"></i>Network
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <i class="fas fa-clock mr-1"></i>Timestamp
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <i class="fas fa-search mr-1"></i>Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($activities as $activity)
                            <tr class="table-row-hover">
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 rounded-full flex items-center justify-center mr-3 activity-icon border
                                            {{ $activity->event == 'created' ? 'bg-green-50 text-green-600 border-green-200' : 
                                               ($activity->event == 'updated' ? 'bg-blue-50 text-blue-600 border-blue-200' : 
                                               ($activity->event == 'deleted' ? 'bg-red-50 text-red-600 border-red-200' : 'bg-gray-50 text-gray-600 border-gray-200')) }}">
                                            <i class="fas {{ $activity->event == 'created' ? 'fa-plus' : 
                                                           ($activity->event == 'updated' ? 'fa-edit' : 
                                                           ($activity->event == 'deleted' ? 'fa-trash' : 'fa-eye')) }} text-sm"></i>
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">{{ $activity->description }}</div>
                                            <div class="text-xs text-gray-500">{{ ucfirst(str_replace('_', ' ', $activity->log_name)) }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    @if($activity->causer)
                                        <div class="flex items-center">
                                            <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center mr-3 border border-blue-200">
                                                <span class="text-white font-semibold text-xs">{{ strtoupper(substr($activity->causer->name, 0, 1)) }}</span>
                                            </div>
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">{{ $activity->causer->name }}</div>
                                                <div class="text-xs text-gray-500">{{ $activity->causer->email }}</div>
                                            </div>
                                        </div>
                                    @else
                                        <div class="flex items-center">
                                            <div class="w-8 h-8 bg-gradient-to-br from-gray-500 to-gray-600 rounded-full flex items-center justify-center mr-3 border border-gray-200">
                                                <i class="fas fa-robot text-red text-xs"></i>
                                            </div>
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">SYSTEM</div>
                                                <div class="text-xs text-gray-500">Automated Process</div>
                                            </div>
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border
                                        {{ $activity->event == 'created' ? 'bg-green-100 text-green-800 border-green-200' : 
                                           ($activity->event == 'updated' ? 'bg-blue-100 text-blue-800 border-blue-200' : 
                                           ($activity->event == 'deleted' ? 'bg-red-100 text-red-800 border-red-200' : 'bg-gray-100 text-gray-800 border-gray-200')) }}">
                                        {{ ucfirst($activity->event) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    @php
                                        $clientIp = $activity->properties['ip'] ?? $activity->properties['client_ip'] ?? null;
                                        $serverIp = $activity->properties['server_ip'] ?? null;
                                        $hasValidClientIp = $clientIp && $clientIp !== 'N/A' && $clientIp !== 'unknown';
                                        $hasValidServerIp = $serverIp && $serverIp !== 'unknown' && $serverIp !== 'N/A';
                                    @endphp
                                    
                                    @if($hasValidClientIp || $hasValidServerIp)
                                        <div class="space-y-2">
                                            @if($hasValidClientIp)
                                                <div class="flex items-center space-x-2">
                                                    <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium ip-badge">
                                                        <i class="fas fa-user mr-1"></i>
                                                        {{ $clientIp }}
                                                    </span>
                                                    <span class="text-xs text-gray-500">Client</span>
                                                </div>
                                            @endif
                                            
                                            @if($hasValidServerIp)
                                                <div class="flex items-center space-x-2">
                                                    <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium server-ip-badge">
                                                        <i class="fas fa-server mr-1"></i>
                                                        {{ $serverIp }}
                                                    </span>
                                                    <span class="text-xs text-gray-500">Server</span>
                                                </div>
                                            @endif
                                        </div>
                                    @else
                                        <div class="flex items-center space-x-2">
                                            <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-gray-100 text-gray-800 border border-gray-200">
                                                <i class="fas fa-home mr-1"></i>
                                                Local Access
                                            </span>
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900">{{ $activity->created_at->format('M d, Y') }}</div>
                                    <div class="text-xs text-gray-500">{{ $activity->created_at->format('H:i:s') }}</div>
                                    <div class="text-xs text-gray-400">{{ $activity->created_at->diffForHumans() }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <a href="{{ route('activity-logs.show', $activity->id) }}" 
                                       class="inline-flex items-center px-3 py-1.5 bg-gradient-to-r from-blue-600 to-purple-600 text-white text-xs rounded-lg hover:from-blue-700 hover:to-purple-700 transition-all shadow-md hover:shadow-lg transform hover:-translate-y-0.5 border border-blue-200">
                                        <i class="fas fa-search mr-1"></i>View Details
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center">
                                        <i class="fas fa-history text-4xl text-gray-400 mb-4"></i>
                                        <h3 class="text-lg font-medium text-gray-900 mb-2">No Activity Logs Found</h3>
                                        <p class="text-gray-500">No activities match your current filters.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($activities->hasPages())
                <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
                    {{ $activities->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Cleanup Modal -->
<div id="cleanupModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-2xl p-8 max-w-md w-full mx-4 transform transition-all border border-gray-200 shadow-xl">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-semibold text-gray-900 flex items-center">
                <i class="fas fa-exclamation-triangle text-red-500 mr-2"></i>
                Activity Log Cleanup
            </h3>
            <button onclick="hideCleanupModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
            <div class="flex items-center mb-2">
                <i class="fas fa-exclamation-circle text-red-500 mr-2"></i>
                <span class="text-red-700 font-medium">Warning</span>
            </div>
            <p class="text-sm text-red-600">This action will permanently delete activity logs and cannot be undone.</p>
        </div>
        
        <form id="cleanupForm" onsubmit="cleanupLogs(event)">
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-calendar-alt mr-1"></i>Delete logs older than:
                </label>
                <div class="flex items-center space-x-2">
                    <input type="number" id="cleanupDays" name="days" min="1" max="365" value="90" 
                           class="flex-1 px-3 py-2 bg-white border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 text-gray-900">
                    <span class="text-sm text-gray-600">days</span>
                </div>
            </div>
            
            <div class="flex space-x-3">
                <button type="submit" class="flex-1 px-4 py-2 bg-gradient-to-r from-red-600 to-red-700 text-white rounded-lg hover:from-red-700 hover:to-red-800 transition-all security-alert">
                    <i class="fas fa-trash mr-2"></i>Delete Logs
                </button>
                <button type="button" onclick="hideCleanupModal()" class="flex-1 px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-all">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function showCleanupModal() {
    document.getElementById('cleanupModal').classList.remove('hidden');
    document.getElementById('cleanupModal').classList.add('flex');
}

function hideCleanupModal() {
    document.getElementById('cleanupModal').classList.add('hidden');
    document.getElementById('cleanupModal').classList.remove('flex');
}

function cleanupLogs(event) {
    event.preventDefault();
    
    const days = document.getElementById('cleanupDays').value;
    
    if (confirm(`Are you sure you want to delete all activity logs older than ${days} days? This action cannot be undone.`)) {
        fetch('{{ route("activity-logs.cleanup") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ days: parseInt(days) })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
            hideCleanupModal();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while cleaning up logs.');
            hideCleanupModal();
        });
    }
}

// Auto-refresh every 30 seconds
setInterval(function() {
    if (!document.querySelector('input:focus') && !document.querySelector('select:focus')) {
        location.reload();
    }
}, 30000);
</script>
@endpush
@endsection
