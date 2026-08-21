@extends('layouts.app')

@section('title', 'Employee Directory - HRMS')
@section('page-title', 'Employee Directory')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50">
    <div class="max-w-full mx-auto p-6 space-y-6">
        <!-- Header Card -->
        <div class="bg-white/80 backdrop-blur-sm overflow-hidden shadow-xl rounded-2xl border border-white/20">
            <div class="bg-gradient-to-r from-indigo-600 via-purple-600 to-blue-600 px-8 py-12 relative overflow-hidden">
                <!-- Background Pattern -->
                <div class="absolute inset-0 bg-black/10"></div>
                <div class="absolute -top-4 -right-4 w-24 h-24 bg-white/10 rounded-full"></div>
                <div class="absolute -bottom-4 -left-4 w-32 h-32 bg-white/10 rounded-full"></div>
                
                <div class="relative flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-16 h-16 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center border border-white/30">
                                <i class="fas fa-users text-white text-2xl"></i>
                            </div>
                        </div>
                        <div class="ml-6">
                            <h1 class="text-3xl font-bold text-white mb-2">Employee Directory</h1>
                            <p class="text-indigo-100 text-lg">
                                Discover and connect with our talented team members
                            </p>
                        </div>
                    </div>
                    <div class="hidden lg:flex items-center space-x-4">
                        <div class="text-right">
                            <p class="text-white/90 text-sm">Total Members</p>
                            <p class="text-3xl font-bold text-white" id="employee-count">{{ $employees->count() }}</p>
                        </div>
                        <div class="w-20 h-20 bg-white/10 backdrop-blur-sm rounded-2xl flex items-center justify-center border border-white/20">
                            <i class="fas fa-user-friends text-white text-3xl"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Advanced Search and Filters -->
        <div class="bg-white/80 backdrop-blur-sm shadow-xl rounded-2xl border border-white/20 p-8">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                <!-- Search Bar -->
                <div class="flex-1 max-w-2xl">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400 text-lg"></i>
                        </div>
                        <input type="text" 
                               id="employee-search" 
                               class="block w-full pl-12 pr-4 py-4 text-lg border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-300 bg-gray-50/50" 
                               placeholder="Search employees by name or email...">
                    </div>
                </div>
                
                <!-- Filter Buttons -->
                <div class="flex flex-wrap gap-3">
                    <!-- Department Filter -->
                    <div class="relative">
                        <select id="department-filter" 
                                class="appearance-none bg-white/80 border-2 border-gray-200 rounded-xl px-6 py-3 pr-10 text-sm font-medium text-gray-700 hover:border-indigo-300 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-300">
                            <option value="">All Departments</option>
                            @foreach($employees->pluck('department')->filter()->unique('id') as $department)
                                <option value="{{ $department->id ?? '' }}">{{ $department->name ?? 'Department ' . $department->id }}</option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none">
                            <i class="fas fa-chevron-down text-gray-400 text-sm"></i>
                        </div>
                    </div>
                    
                    <!-- Status Filter -->
                    <div class="relative">
                        <select id="status-filter" 
                                class="appearance-none bg-white/80 border-2 border-gray-200 rounded-xl px-6 py-3 pr-10 text-sm font-medium text-gray-700 hover:border-indigo-300 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-300">
                            <option value="">All Status</option>
                            @foreach($employees->pluck('status')->filter()->unique() as $status)
                                <option value="{{ $status }}">{{ $status }}</option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none">
                            <i class="fas fa-chevron-down text-gray-400 text-sm"></i>
                        </div>
                    </div>
                    
                    <!-- Joining Date Filter -->
                    <div class="relative">
                        <select id="date-filter" 
                                class="appearance-none bg-white/80 border-2 border-gray-200 rounded-xl px-6 py-3 pr-10 text-sm font-medium text-gray-700 hover:border-indigo-300 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-300">
                            <option value="">All Time</option>
                            <option value="this-month">This Month</option>
                            <option value="last-3-months">Last 3 Months</option>
                            <option value="this-year">This Year</option>
                            <option value="last-year">Last Year</option>
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none">
                            <i class="fas fa-chevron-down text-gray-400 text-sm"></i>
                        </div>
                    </div>
                    
                    <!-- View Toggle -->
                    <div class="flex bg-gray-100 rounded-xl p-1">
                        <button id="grid-view" class="px-4 py-2 text-sm font-medium text-gray-700 rounded-lg bg-white shadow-sm transition-all duration-300">
                            <i class="fas fa-th-large mr-2"></i>Grid
                        </button>
                        <button id="list-view" class="px-4 py-2 text-sm font-medium text-gray-700 rounded-lg transition-all duration-300">
                            <i class="fas fa-list mr-2"></i>List
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Quick Stats -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-8 pt-6 border-t border-gray-200/50">
                <div class="text-center">
                    <p class="text-2xl font-bold text-indigo-600" id="total-count">{{ $employees->count() }}</p>
                    <p class="text-sm text-gray-600">Total</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-bold text-green-600" id="active-count">{{ $employees->where('status', 'Active')->count() }}</p>
                    <p class="text-sm text-gray-600">Active</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-bold text-yellow-600" id="probation-count">{{ $employees->where('status', 'Probation Period')->count() }}</p>
                    <p class="text-sm text-gray-600">Probation</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-bold text-red-600" id="left-count">{{ $employees->where('status', 'Left')->count() }}</p>
                    <p class="text-sm text-gray-600">Left</p>
                </div>
            </div>
        </div>

        <!-- Employee Grid/List Container -->
        <div class="bg-white/80 backdrop-blur-sm shadow-xl rounded-2xl border border-white/20 overflow-hidden w-full">
            <div class="px-8 py-6 border-b border-gray-200/50 bg-gradient-to-r from-gray-50 to-white w-full">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-bold text-gray-900 flex items-center">
                        <i class="fas fa-address-book text-indigo-500 mr-3"></i>
                        Team Directory
                    </h2>
                    <div class="text-sm text-gray-600">
                        <span id="showing-count">{{ $employees->count() }}</span> of {{ $employees->count() }} employees
                    </div>
                </div>
            </div>
            
            @if($employees->count() > 0)
                <!-- Grid View -->
                <div id="employees-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 p-8">
                    @foreach($employees as $employee)
                        <div class="employee-card bg-white rounded-2xl border border-gray-200 p-6 hover:shadow-2xl hover:scale-105 transition-all duration-500 transform hover:border-indigo-300 group cursor-pointer shadow-lg"
                             data-name="{{ strtolower($employee->name) }}"
                             data-email="{{ strtolower($employee->email ?? '') }}"
                             data-status="{{ $employee->status }}"
                             data-department="{{ $employee->department_id ?? '' }}"
                             data-joined="{{ $employee->date_of_joining ? $employee->date_of_joining->format('Y-m') : '' }}">
                            
                            <!-- Employee Avatar -->
                            <div class="flex flex-col items-center text-center">
                                <div class="relative mb-4">
                                    <div class="w-20 h-20 bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500 rounded-2xl flex items-center justify-center shadow-lg group-hover:shadow-xl transition-all duration-300 transform group-hover:rotate-3">
                                        <span class="text-white font-bold text-xl tracking-wider">
                                            {{ strtoupper(substr($employee->name, 0, 1)) }}{{ strtoupper(substr(explode(' ', $employee->name)[1] ?? $employee->name[1] ?? '', 0, 1)) }}
                                        </span>
                                    </div>
                                    <!-- Online Status Indicator -->
                                    <div class="absolute -bottom-1 -right-1 w-6 h-6 bg-green-400 border-3 border-white rounded-full shadow-sm"></div>
                                </div>
                                
                                <!-- Employee Info -->
                                <h3 class="text-lg font-bold text-gray-900 mb-2 group-hover:text-indigo-700 transition-colors duration-300">
                                    {{ $employee->name }}
                                </h3>
                                
                                <!-- Status Badge -->
                                <div class="mb-4">
                                    @if($employee->status === 'Active')
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold tracking-wide bg-green-500 text-white shadow-md">
                                            <i class="fas fa-check-circle mr-1"></i>
                                            Active
                                        </span>
                                    @elseif($employee->status === 'Probation Period')
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold tracking-wide bg-yellow-500 text-white shadow-md">
                                            <i class="fas fa-clock mr-1"></i>
                                            Probation Period
                                        </span>
                                    @elseif($employee->status === 'Left')
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold tracking-wide bg-red-500 text-white shadow-md">
                                            <i class="fas fa-sign-out-alt mr-1"></i>
                                            Left
                                        </span>
                                    @elseif($employee->status === 'Onboard')
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold tracking-wide bg-blue-500 text-white shadow-md">
                                            <i class="fas fa-user-plus mr-1"></i>
                                            Onboard
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold tracking-wide bg-gray-500 text-white shadow-md">
                                            <i class="fas fa-question-circle mr-1"></i>
                                            {{ $employee->status ?? 'Unknown' }}
                                        </span>
                                    @endif
                                    
                                    @if($employee->designation)
                                        <div class="mt-2">
                                            <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-blue-100 text-blue-800">
                                                {{ $employee->designation }}
                                            </span>
                                        </div>
                                    @endif
                                </div>
                                
                                <!-- Employee Details -->
                                <div class="w-full space-y-3 text-sm">
                                    <!-- Email -->
                                    <div class="flex items-center justify-center text-gray-700 bg-gray-100 rounded-lg py-2 px-3 border">
                                        <i class="fas fa-envelope mr-2 text-indigo-500"></i>
                                        <span class="truncate font-medium">
                                            @if($employee->email && $employee->email !== 'No email provided')
                                                {{ $employee->email }}
                                            @else
                                                N/A
                                            @endif
                                        </span>
                                    </div>
                                    
                                    <!-- Department -->
                                    <div class="flex items-center justify-center text-gray-700 bg-purple-50 rounded-lg py-2 px-3 border border-purple-200">
                                        <i class="fas fa-building mr-2 text-purple-500"></i>
                                        <span class="font-medium">{{ $employee->department->name ?? 'Department ' . ($employee->department_id ?? 'N/A') }}</span>
                                    </div>
                                    
                                    <!-- Financial Year -->
                                    <div class="flex items-center justify-center text-gray-700 bg-green-50 rounded-lg py-2 px-3 border border-green-200">
                                        <i class="fas fa-calendar-alt mr-2 text-green-500"></i>
                                        <span class="font-medium">{{ $employee->financial_year ?? 'N/A' }}</span>
                                    </div>
                                    
                                    <!-- Join Date -->
                                    <div class="flex items-center justify-center text-gray-700 bg-orange-50 rounded-lg py-2 px-3 border border-orange-200">
                                        <i class="fas fa-clock mr-2 text-orange-500"></i>
                                        <span class="font-medium">
                                            @if($employee->date_of_joining)
                                                Joined {{ $employee->date_of_joining->format('M Y') }}
                                            @else
                                                Join Date: N/A
                                            @endif
                                        </span>
                                    </div>
                                    
                                    <!-- Employee ID -->
                                    <div class="flex items-center justify-center text-gray-700 bg-blue-50 rounded-lg py-2 px-3 border border-blue-200">
                                        <i class="fas fa-id-card mr-2 text-blue-500"></i>
                                        <span class="font-medium">ID: {{ $employee->employee_id ?? 'N/A' }}</span>
                                    </div>
                                    
                                    <!-- Phone -->
                                    @if($employee->phone)
                                    <div class="flex items-center justify-center text-gray-700 bg-yellow-50 rounded-lg py-2 px-3 border border-yellow-200">
                                        <i class="fas fa-phone mr-2 text-yellow-500"></i>
                                        <span class="font-medium">{{ $employee->phone }}</span>
                                    </div>
                                    @endif
                                    
                                    <!-- Date of Birth -->
                                    @if($employee->additional_data && isset($employee->additional_data['date_of_birth']) && $employee->additional_data['date_of_birth'])
                                    <div class="flex items-center justify-center text-gray-700 bg-pink-50 rounded-lg py-2 px-3 border border-pink-200">
                                        <i class="fas fa-birthday-cake mr-2 text-pink-500"></i>
                                        <span class="font-medium">DOB: {{ \Carbon\Carbon::parse($employee->additional_data['date_of_birth'])->format('M d, Y') }}</span>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                
                <!-- List View (Hidden by default) -->
                <div id="employees-list" class="hidden">
                    <div class="overflow-x-auto w-full">
                        <table class="w-full table-auto divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-2/5">Employee</th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/6">Status</th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/4">Department</th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/6">Contact</th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/6">Join Date</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200 w-full">
                                @foreach($employees as $employee)
                                    <tr class="employee-row hover:bg-gray-50 transition-colors duration-200 w-full"
                                        data-name="{{ strtolower($employee->name) }}"
                                        data-email="{{ strtolower($employee->email ?? '') }}"
                                        data-status="{{ $employee->status }}"
                                        data-department="{{ $employee->department_id ?? '' }}"
                                        data-joined="{{ $employee->date_of_joining ? $employee->date_of_joining->format('Y-m') : '' }}">
                                        <td class="px-6 py-4 whitespace-nowrap w-2/5">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-12 w-12">
                                                    <div class="h-12 w-12 bg-gradient-to-br from-indigo-500 to-purple-500 rounded-xl flex items-center justify-center">
                                                        <span class="text-white font-semibold text-sm">
                                                            {{ strtoupper(substr($employee->name, 0, 1)) }}{{ strtoupper(substr(explode(' ', $employee->name)[1] ?? $employee->name[1] ?? '', 0, 1)) }}
                                                        </span>
                                                    </div>
                                                </div>                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">{{ $employee->name }}</div>
                                    <div class="text-sm text-gray-500">
                                        @if($employee->email && $employee->email !== 'No email provided')
                                            {{ $employee->email }}
                                        @else
                                            Email: N/A
                                        @endif
                                    </div>
                                    <div class="text-sm text-gray-400">ID: {{ $employee->employee_id ?? 'N/A' }}</div>
                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap w-1/6">
                                            @if($employee->status === 'Active')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-green-500 text-white shadow-sm">
                                                    <i class="fas fa-check-circle mr-1"></i>
                                                    Active
                                                </span>
                                            @elseif($employee->status === 'Probation Period')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-yellow-500 text-white shadow-sm">
                                                    <i class="fas fa-clock mr-1"></i>
                                                    Probation Period
                                                </span>
                                            @elseif($employee->status === 'Left')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-500 text-white shadow-sm">
                                                    <i class="fas fa-sign-out-alt mr-1"></i>
                                                    Left
                                                </span>
                                            @elseif($employee->status === 'Onboard')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-500 text-white shadow-sm">
                                                    <i class="fas fa-user-plus mr-1"></i>
                                                    Onboard
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-gray-500 text-white shadow-sm">
                                                    <i class="fas fa-question-circle mr-1"></i>
                                                    {{ $employee->status ?? 'Unknown' }}
                                                </span>
                                            @endif
                                            
                                            @if($employee->designation)
                                                <div class="mt-1">
                                                    <span class="text-xs text-gray-500">{{ $employee->designation }}</span>
                                                </div>
                                            @endif
                                        </td>                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 w-1/4">
                            {{ $employee->department->name ?? 'Department ' . ($employee->department_id ?? 'N/A') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 w-1/6">
                            <div>{{ $employee->phone ?? 'N/A' }}</div>
                            @if($employee->additional_data && isset($employee->additional_data['date_of_birth']) && $employee->additional_data['date_of_birth'])
                                <div class="text-xs text-gray-500 mt-1">DOB: {{ \Carbon\Carbon::parse($employee->additional_data['date_of_birth'])->format('M d, Y') }}</div>
                            @endif
                        </td><td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 w-1/6">
                            @if($employee->date_of_joining)
                                {{ $employee->date_of_joining->format('M d, Y') }}
                            @else
                                N/A
                            @endif
                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- No Results Message -->
                <div id="no-results" class="hidden text-center py-16">
                    <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-search text-gray-400 text-3xl"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No employees found</h3>
                    <p class="text-gray-500 text-sm">Try adjusting your search criteria or filters.</p>
                </div>
            @else
                <!-- Empty State -->
                <div class="text-center py-16">
                    <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-users text-gray-400 text-3xl"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No employees found</h3>
                    <p class="text-gray-500 text-sm">There are currently no employees in the directory.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .font-inter {
        font-family: 'Inter', sans-serif;
    }
    
    /* Custom scrollbar */
    ::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }
    
    ::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 10px;
    }
    
    ::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 10px;
    }
    
    ::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }
    
    /* Table full width styling */
    #employees-list table {
        width: 100% !important;
        table-layout: fixed;
    }
    
    #employees-list {
        width: 100%;
    }
    
    /* Animation for cards */
    @keyframes slideInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .employee-card {
        animation: slideInUp 0.6s ease-out;
    }
    
    /* Staggered animation */
    .employee-card:nth-child(1) { animation-delay: 0.1s; }
    .employee-card:nth-child(2) { animation-delay: 0.2s; }
    .employee-card:nth-child(3) { animation-delay: 0.3s; }
    .employee-card:nth-child(4) { animation-delay: 0.4s; }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('employee-search');
    const departmentFilter = document.getElementById('department-filter');
    const statusFilter = document.getElementById('status-filter');
    const dateFilter = document.getElementById('date-filter');
    const gridView = document.getElementById('grid-view');
    const listView = document.getElementById('list-view');
    const employeesGrid = document.getElementById('employees-grid');
    const employeesList = document.getElementById('employees-list');
    const noResults = document.getElementById('no-results');
    const showingCount = document.getElementById('showing-count');
    
    let currentView = 'grid';
    
    // View toggle functionality
    gridView.addEventListener('click', function() {
        currentView = 'grid';
        employeesGrid.classList.remove('hidden');
        employeesList.classList.add('hidden');
        gridView.classList.add('bg-white', 'shadow-sm');
        listView.classList.remove('bg-white', 'shadow-sm');
    });
    
    listView.addEventListener('click', function() {
        currentView = 'list';
        employeesGrid.classList.add('hidden');
        employeesList.classList.remove('hidden');
        listView.classList.add('bg-white', 'shadow-sm');
        gridView.classList.remove('bg-white', 'shadow-sm');
    });
    
    // Filter functionality
    function filterEmployees() {
        const searchTerm = searchInput.value.toLowerCase();
        const selectedDepartment = departmentFilter.value;
        const selectedStatus = statusFilter.value;
        const selectedDate = dateFilter.value;
        
        const cards = document.querySelectorAll('.employee-card');
        const rows = document.querySelectorAll('.employee-row');
        
        let visibleCount = 0;
        
        // Filter cards (grid view)
        cards.forEach(card => {
            const name = card.dataset.name;
            const email = card.dataset.email;
            const status = card.dataset.status;
            const department = card.dataset.department;
            const joined = card.dataset.joined;
            
            let show = true;
            
            // Search filter
            if (searchTerm && !name.includes(searchTerm) && !email.includes(searchTerm)) {
                show = false;
            }
            
            // Department filter
            if (selectedDepartment && department !== selectedDepartment) {
                show = false;
            }
            
            // Status filter
            if (selectedStatus && status !== selectedStatus) {
                show = false;
            }
            
            // Date filter
            if (selectedDate) {
                const currentDate = new Date();
                const joinedDate = new Date(joined + '-01');
                
                switch(selectedDate) {
                    case 'this-month':
                        if (joinedDate.getFullYear() !== currentDate.getFullYear() || 
                            joinedDate.getMonth() !== currentDate.getMonth()) {
                            show = false;
                        }
                        break;
                    case 'last-3-months':
                        const threeMonthsAgo = new Date();
                        threeMonthsAgo.setMonth(threeMonthsAgo.getMonth() - 3);
                        if (joinedDate < threeMonthsAgo) {
                            show = false;
                        }
                        break;
                    case 'this-year':
                        if (joinedDate.getFullYear() !== currentDate.getFullYear()) {
                            show = false;
                        }
                        break;
                    case 'last-year':
                        if (joinedDate.getFullYear() !== currentDate.getFullYear() - 1) {
                            show = false;
                        }
                        break;
                }
            }
            
            if (show) {
                card.style.display = 'block';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });
        
        // Filter rows (list view)
        rows.forEach(row => {
            const name = row.dataset.name;
            const email = row.dataset.email;
            const status = row.dataset.status;
            const department = row.dataset.department;
            const joined = row.dataset.joined;
            
            let show = true;
            
            // Apply same filters as above
            if (searchTerm && !name.includes(searchTerm) && !email.includes(searchTerm)) {
                show = false;
            }
            
            if (selectedDepartment && department !== selectedDepartment) {
                show = false;
            }
            
            if (selectedStatus && status !== selectedStatus) {
                show = false;
            }
            
            if (selectedDate) {
                const currentDate = new Date();
                const joinedDate = new Date(joined + '-01');
                
                switch(selectedDate) {
                    case 'this-month':
                        if (joinedDate.getFullYear() !== currentDate.getFullYear() || 
                            joinedDate.getMonth() !== currentDate.getMonth()) {
                            show = false;
                        }
                        break;
                    case 'last-3-months':
                        const threeMonthsAgo = new Date();
                        threeMonthsAgo.setMonth(threeMonthsAgo.getMonth() - 3);
                        if (joinedDate < threeMonthsAgo) {
                            show = false;
                        }
                        break;
                    case 'this-year':
                        if (joinedDate.getFullYear() !== currentDate.getFullYear()) {
                            show = false;
                        }
                        break;
                    case 'last-year':
                        if (joinedDate.getFullYear() !== currentDate.getFullYear() - 1) {
                            show = false;
                        }
                        break;
                }
            }
            
            row.style.display = show ? 'table-row' : 'none';
        });
        
        // Update showing count
        showingCount.textContent = visibleCount;
        
        // Show/hide no results message
        if (visibleCount === 0) {
            noResults.classList.remove('hidden');
            employeesGrid.classList.add('hidden');
            employeesList.classList.add('hidden');
        } else {
            noResults.classList.add('hidden');
            if (currentView === 'grid') {
                employeesGrid.classList.remove('hidden');
            } else {
                employeesList.classList.remove('hidden');
            }
        }
        
        // Update stats
        updateStats();
    }
    
    function updateStats() {
        const visibleCards = document.querySelectorAll('.employee-card:not([style*="display: none"])');
        let activeCount = 0, probationCount = 0, leftCount = 0;
        
        visibleCards.forEach(card => {
            const status = card.dataset.status;
            if (status === 'Active') activeCount++;
            else if (status === 'Probation Period') probationCount++;
            else if (status === 'Left') leftCount++;
        });
        
        document.getElementById('total-count').textContent = visibleCards.length;
        document.getElementById('active-count').textContent = activeCount;
        document.getElementById('probation-count').textContent = probationCount;
        document.getElementById('left-count').textContent = leftCount;
    }
    
    // Event listeners
    searchInput.addEventListener('input', filterEmployees);
    departmentFilter.addEventListener('change', filterEmployees);
    statusFilter.addEventListener('change', filterEmployees);
    dateFilter.addEventListener('change', filterEmployees);
    
    // Add smooth animations on load
    setTimeout(() => {
        document.querySelectorAll('.employee-card').forEach((card, index) => {
            card.style.animationDelay = `${index * 0.1}s`;
        });
    }, 100);
});
</script>
@endpush
