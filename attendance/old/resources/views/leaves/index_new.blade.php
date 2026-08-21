@extends('layouts.app')

@section('title', 'Leave Management - HRMS')
@section('page-title', 'Leave Management')

@section('content')
<div class="p-6 space-y-6">
    <!-- Success Message -->
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 rounded-lg p-4 flex items-center">
            <i class="fas fa-check-circle text-green-500 mr-3"></i>
            <div>
                <h4 class="text-green-800 font-medium">Success!</h4>
                <p class="text-green-700">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    <!-- Header Section -->
    <div class="bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 rounded-xl p-6 text-white relative overflow-hidden">
        <div class="absolute inset-0 opacity-10">
            <div class="absolute -top-4 -right-4 w-24 h-24 bg-white rounded-full"></div>
            <div class="absolute top-10 -right-8 w-16 h-16 bg-white rounded-full"></div>
            <div class="absolute -bottom-6 -left-6 w-20 h-20 bg-white rounded-full"></div>
        </div>
        <div class="relative">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-2 flex items-center">
                        <i class="fas fa-calendar-alt mr-3"></i>
                        Leave Management
                    </h1>
                    <p class="text-indigo-100 text-sm">
                        @if(auth()->user()->isStaff())
                            Manage your leave requests and track their status
                        @else
                            Review and manage all employee leave applications
                        @endif
                    </p>
                </div>
                <div class="hidden lg:block">
                    <div class="w-20 h-20 bg-white bg-opacity-15 rounded-full flex items-center justify-center">
                        <i class="fas fa-users text-2xl text-white"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-100 hover:shadow-md transition-all duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Applications</p>
                    <p class="text-2xl font-bold text-indigo-600">{{ $leaves->count() }}</p>
                </div>
                <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-file-alt text-indigo-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-100 hover:shadow-md transition-all duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Pending</p>
                    <p class="text-2xl font-bold text-yellow-600">{{ $leaves->where('status', 'pending')->count() }}</p>
                </div>
                <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-clock text-yellow-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-100 hover:shadow-md transition-all duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Approved</p>
                    <p class="text-2xl font-bold text-green-600">{{ $leaves->where('status', 'approved')->count() }}</p>
                </div>
                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-check-circle text-green-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-100 hover:shadow-md transition-all duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Rejected</p>
                    <p class="text-2xl font-bold text-red-600">{{ $leaves->where('status', 'rejected')->count() }}</p>
                </div>
                <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-times-circle text-red-600"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Bar -->
    <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-100">
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
            <div class="flex flex-col sm:flex-row gap-3 w-full lg:w-auto">
                <!-- Search Bar -->
                <div class="relative flex-1 lg:w-80">
                    <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <input type="text" id="searchInput" placeholder="Search applications..." 
                           class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                </div>
                
                <!-- Filter Dropdown -->
                <div class="flex-shrink-0">
                    <select id="statusFilter" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>
            </div>

            @if(auth()->user()->isStaff())
                <div class="w-full lg:w-auto">
                    <a href="{{ route('leaves.create') }}" 
                       class="w-full lg:w-auto bg-gradient-to-r from-indigo-600 to-purple-600 text-white px-4 py-2 rounded-lg font-medium hover:from-indigo-700 hover:to-purple-700 transition-all duration-200 shadow-sm hover:shadow-md flex items-center justify-center gap-2 text-sm">
                        <i class="fas fa-plus"></i>
                        <span>Apply for Leave</span>
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- Leave Applications Table -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                <i class="fas fa-list-ul text-indigo-600 mr-2"></i>
                Applications List
            </h3>
        </div>
        
        @if($leaves->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-user text-gray-400"></i>
                                    <span>Employee</span>
                                </div>
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-calendar text-gray-400"></i>
                                    <span>Duration</span>
                                </div>
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-tag text-gray-400"></i>
                                    <span>Type</span>
                                </div>
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-calendar-day text-gray-400"></i>
                                    <span>Days</span>
                                </div>
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-info-circle text-gray-400"></i>
                                    <span>Status</span>
                                </div>
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-comment text-gray-400"></i>
                                    <span>Reason</span>
                                </div>
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-cog text-gray-400"></i>
                                    <span>Actions</span>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($leaves as $leave)
                            <tr class="hover:bg-gray-50 transition-colors duration-150 leave-row">
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center flex-shrink-0">
                                            <span class="text-white font-bold text-xs">{{ strtoupper(substr($leave->user->name, 0, 2)) }}</span>
                                        </div>
                                        <div class="ml-3 min-w-0">
                                            <div class="text-sm font-medium text-gray-900 truncate">{{ $leave->user->name }}</div>
                                            <div class="text-xs text-gray-500 truncate">{{ $leave->user->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        @php
                                            $startDate = is_string($leave->start_date) ? \Carbon\Carbon::parse($leave->start_date) : $leave->start_date;
                                            $endDate = is_string($leave->end_date) ? \Carbon\Carbon::parse($leave->end_date) : $leave->end_date;
                                        @endphp
                                        <div class="font-medium">{{ $startDate->format('M d, Y') }}</div>
                                        <div class="text-xs text-gray-500">to {{ $endDate->format('M d, Y') }}</div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium whitespace-nowrap
                                        @if($leave->leave_type === 'sick') bg-red-100 text-red-800
                                        @elseif($leave->leave_type === 'casual') bg-blue-100 text-blue-800
                                        @elseif($leave->leave_type === 'annual') bg-green-100 text-green-800
                                        @else bg-gray-100 text-gray-800
                                        @endif">
                                        {{ ucfirst($leave->leave_type ?? 'Casual') }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900 flex items-center">
                                        <i class="fas fa-calendar-check text-indigo-500 mr-1"></i>
                                        <span>{{ $leave->total_days }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium whitespace-nowrap leave-status
                                        @if($leave->status === 'approved') bg-green-100 text-green-800
                                        @elseif($leave->status === 'rejected') bg-red-100 text-red-800
                                        @else bg-yellow-100 text-yellow-800
                                        @endif">
                                        <i class="fas 
                                            @if($leave->status === 'approved') fa-check-circle
                                            @elseif($leave->status === 'rejected') fa-times-circle
                                            @else fa-clock
                                            @endif mr-1"></i>
                                        {{ ucfirst($leave->status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 max-w-xs">
                                    <div class="text-sm text-gray-900 truncate" title="{{ $leave->reason }}">
                                        {{ $leave->reason }}
                                    </div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm font-medium">
                                    <div class="flex items-center gap-1">
                                        <button class="text-indigo-600 hover:text-indigo-900 p-1 rounded hover:bg-indigo-50 transition-colors duration-150" 
                                                title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        
                                        @if($leave->status === 'pending' && (auth()->user()->isAdmin() || auth()->user()->isSuperAdmin()))
                                            <button class="text-green-600 hover:text-green-900 p-1 rounded hover:bg-green-50 transition-colors duration-150"
                                                    title="Approve">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button class="text-red-600 hover:text-red-900 p-1 rounded hover:bg-red-50 transition-colors duration-150"
                                                    title="Reject">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        @endif
                                        
                                        @if($leave->status === 'pending' && $leave->user_id === auth()->id())
                                            <button class="text-blue-600 hover:text-blue-900 p-1 rounded hover:bg-blue-50 transition-colors duration-150"
                                                    title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="text-red-600 hover:text-red-900 p-1 rounded hover:bg-red-50 transition-colors duration-150"
                                                    title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-12">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-calendar-times text-gray-400 text-2xl"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No Leave Applications</h3>
                <p class="text-gray-500 mb-4">
                    @if(auth()->user()->isStaff())
                        You haven't applied for any leave yet.
                    @else
                        No leave applications found in the system.
                    @endif
                </p>
                @if(auth()->user()->isStaff())
                    <a href="{{ route('leaves.create') }}" 
                       class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white px-4 py-2 rounded-lg font-medium hover:from-indigo-700 hover:to-purple-700 transition-all duration-200 shadow-sm hover:shadow-md inline-flex items-center gap-2">
                        <i class="fas fa-plus"></i>
                        <span>Apply for Leave</span>
                    </a>
                @endif
            </div>
        @endif
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Search functionality
    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');
    const rows = document.querySelectorAll('.leave-row');
    
    function filterTable() {
        const searchTerm = searchInput.value.toLowerCase();
        const statusValue = statusFilter.value.toLowerCase();
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            const statusElement = row.querySelector('.leave-status');
            const status = statusElement ? statusElement.textContent.toLowerCase() : '';
            
            const matchesSearch = text.includes(searchTerm);
            const matchesStatus = !statusValue || status.includes(statusValue);
            
            if (matchesSearch && matchesStatus) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }
    
    searchInput.addEventListener('input', filterTable);
    statusFilter.addEventListener('change', filterTable);
});
</script>
@endsection
