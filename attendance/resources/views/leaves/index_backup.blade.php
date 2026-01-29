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
    <div class="bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 rounded-2xl p-6 text-white relative overflow-hidden">
        <div class="absolute inset-0 opacity-10">
            <div class="absolute -top-4 -right-4 w-24 h-24 bg-white rounded-full"></div>
            <div class="absolute top-10 -right-8 w-16 h-16 bg-white rounded-full"></div>
            <div class="absolute -bottom-6 -left-6 w-20 h-20 bg-white rounded-full"></div>
        </div>
        <div class="relative">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold mb-2 flex items-center">
                        <i class="fas fa-calendar-alt mr-3"></i>
                        Leave Management
                    </h1>
                    <p class="text-indigo-100 text-base">
                        @if(auth()->user()->isStaff())
                            Manage your leave requests and track their status
                        @else
                            Review and manage all employee leave applications
                        @endif
                    </p>
                </div>
                <div class="hidden lg:block">
                    <div class="w-24 h-24 bg-white bg-opacity-15 rounded-full flex items-center justify-center">
                        <i class="fas fa-users text-3xl text-white"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white rounded-xl p-6 shadow-lg border border-gray-100 hover:shadow-xl transition-all duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Applications</p>
                    <p class="text-3xl font-bold text-indigo-600">{{ $leaves->count() }}</p>
                </div>
                <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-file-alt text-indigo-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl p-6 shadow-lg border border-gray-100 hover:shadow-xl transition-all duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Pending</p>
                    <p class="text-3xl font-bold text-yellow-600">{{ $leaves->where('status', 'pending')->count() }}</p>
                </div>
                <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-clock text-yellow-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl p-6 shadow-lg border border-gray-100 hover:shadow-xl transition-all duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Approved</p>
                    <p class="text-3xl font-bold text-green-600">{{ $leaves->where('status', 'approved')->count() }}</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-check-circle text-green-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl p-6 shadow-lg border border-gray-100 hover:shadow-xl transition-all duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Rejected</p>
                    <p class="text-3xl font-bold text-red-600">{{ $leaves->where('status', 'rejected')->count() }}</p>
                </div>
                <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-times-circle text-red-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Bar -->
    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-3 sm:space-y-0">
            <div class="flex flex-col sm:flex-row items-start sm:items-center space-y-3 sm:space-y-0 sm:space-x-4 w-full sm:w-auto">
                <!-- Search Bar -->
                <div class="relative w-full sm:w-64">
                    <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <input type="text" placeholder="Search applications..." 
                           class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                </div>
                
                <!-- Filter Dropdown -->
                <div class="w-full sm:w-auto">
                    <select class="w-full sm:w-auto px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>
            </div>

           
            @if(auth()->user()->isStaff())
                <div class="w-full sm:w-auto">
                    <a href="{{ route('leaves.create') }}" 
                       class="w-full sm:w-auto bg-gradient-to-r from-indigo-600 to-purple-600 text-white px-4 py-2 rounded-lg font-medium hover:from-indigo-700 hover:to-purple-700 transition-all duration-200 shadow-md hover:shadow-lg flex items-center justify-center space-x-2 text-sm">
                        <i class="fas fa-plus"></i>
                        <span>Apply for Leave</span>
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- Leave Applications Table -->
    <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-xl font-semibold text-gray-900 flex items-center">
                <i class="fas fa-list-ul text-indigo-600 mr-3"></i>
                Applications List
            </h3>
        </div>
        
        @if($leaves->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-user text-gray-400"></i>
                                    <span>Employee</span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-calendar text-gray-400"></i>
                                    <span>Duration</span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-tag text-gray-400"></i>
                                    <span>Type</span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-calendar-day text-gray-400"></i>
                                    <span>Days</span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-info-circle text-gray-400"></i>
                                    <span>Status</span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-comment text-gray-400"></i>
                                    <span>Reason</span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-cog text-gray-400"></i>
                                    <span>Actions</span>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($leaves as $leave)
                            <tr class="hover:bg-gray-50 transition-colors duration-150">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center">
                                            <span class="text-white font-bold text-sm">{{ strtoupper(substr($leave->user->name, 0, 2)) }}</span>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $leave->user->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $leave->user->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        @php
                                            $startDate = is_string($leave->start_date) ? \Carbon\Carbon::parse($leave->start_date) : $leave->start_date;
                                            $endDate = is_string($leave->end_date) ? \Carbon\Carbon::parse($leave->end_date) : $leave->end_date;
                                        @endphp
                                        <div class="font-medium">{{ $startDate->format('M d, Y') }}</div>
                                        <div class="text-gray-500">to {{ $endDate->format('M d, Y') }}</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        @if($leave->leave_type === 'sick') bg-red-100 text-red-800
                                        @elseif($leave->leave_type === 'casual') bg-blue-100 text-blue-800
                                        @elseif($leave->leave_type === 'annual') bg-green-100 text-green-800
                                        @else bg-gray-100 text-gray-800
                                        @endif">
                                        {{ ucfirst($leave->leave_type ?? 'General') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900 flex items-center">
                                        <i class="fas fa-calendar-check text-indigo-500 mr-2"></i>
                                        {{ $leave->total_days }} {{ $leave->total_days == 1 ? 'day' : 'days' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
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
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900 max-w-xs truncate" title="{{ $leave->reason }}">
                                        {{ $leave->reason }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex items-center space-x-2">
                                        <button class="text-indigo-600 hover:text-indigo-900 p-2 rounded-lg hover:bg-indigo-50 transition-colors duration-150" 
                                                title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        
                                        @if($leave->status === 'pending' && (auth()->user()->isAdmin() || auth()->user()->isSuperAdmin()))
                                            <button class="text-green-600 hover:text-green-900 p-2 rounded-lg hover:bg-green-50 transition-colors duration-150"
                                                    title="Approve">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button class="text-red-600 hover:text-red-900 p-2 rounded-lg hover:bg-red-50 transition-colors duration-150"
                                                    title="Reject">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        @endif
                                        
                                        @if($leave->status === 'pending' && $leave->user_id === auth()->id())
                                            <button class="text-blue-600 hover:text-blue-900 p-2 rounded-lg hover:bg-blue-50 transition-colors duration-150"
                                                    title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="text-red-600 hover:text-red-900 p-2 rounded-lg hover:bg-red-50 transition-colors duration-150"
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
            <div class="text-center py-16">
                <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-calendar-times text-gray-400 text-3xl"></i>
                </div>
                <h3 class="text-xl font-medium text-gray-900 mb-2">No Leave Applications</h3>
                <p class="text-gray-500 mb-6">
                    @if(auth()->user()->isStaff())
                        You haven't applied for any leave yet.
                    @else
                        No leave applications found in the system.
                    @endif
                </p>
                @if(auth()->user()->isStaff())
                    <a href="{{ route('leaves.create') }}" 
                       class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white px-6 py-3 rounded-lg font-medium hover:from-indigo-700 hover:to-purple-700 transition-all duration-200 shadow-lg hover:shadow-xl inline-flex items-center space-x-2">
                        <i class="fas fa-plus"></i>
                        <span>Apply for Leave</span>
                    </a>
                @endif
            </div>
        @endif
    </div>
</div>

<!-- Additional Styling -->
<style>
    .table-hover tbody tr:hover {
        background-color: #f8fafc;
        transform: translateY(-1px);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }
    
    .fade-in {
        animation: fadeIn 0.5s ease-in;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

<script>
    // Add smooth transitions and interactions
    document.addEventListener('DOMContentLoaded', function() {
        // Add fade-in animation to table rows
        const tableRows = document.querySelectorAll('tbody tr');
        tableRows.forEach((row, index) => {
            row.style.animationDelay = `${index * 0.1}s`;
            row.classList.add('fade-in');
        });
        
        // Add search functionality
        const searchInput = document.querySelector('input[placeholder="Search applications..."]');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const filter = this.value.toLowerCase();
                const rows = document.querySelectorAll('tbody tr');
                
                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    row.style.display = text.includes(filter) ? '' : 'none';
                });
            });
        }
        
        // Add filter functionality
        const filterSelect = document.querySelector('select');
        if (filterSelect) {
            filterSelect.addEventListener('change', function() {
                const filter = this.value;
                const rows = document.querySelectorAll('tbody tr');
                
                rows.forEach(row => {
                    if (!filter) {
                        row.style.display = '';
                    } else {
                        const statusBadge = row.querySelector('.inline-flex.items-center.px-3.py-1');
                        const status = statusBadge ? statusBadge.textContent.trim().toLowerCase() : '';
                        row.style.display = status.includes(filter) ? '' : 'none';
                    }
                });
            });
        }
    });
</script>
@endsection