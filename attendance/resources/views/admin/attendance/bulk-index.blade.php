@extends('layouts.app')

@section('title', 'Process Attendance - HRMS')
@section('page-title', 'Process Attendance')

@section('content')
<div class="p-6 space-y-6">
        <!-- Success/Error Messages -->
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
            <div class="bg-gradient-to-r from-indigo-600 to-blue-700 px-8 py-10 relative overflow-hidden">
                <!-- Background Pattern -->
                <div class="absolute inset-0 opacity-10">
                    <div class="absolute -top-4 -right-4 w-24 h-24 bg-white rounded-full"></div>
                    <div class="absolute top-20 -right-8 w-16 h-16 bg-white rounded-full"></div>
                    <div class="absolute -bottom-4 -left-4 w-20 h-20 bg-white rounded-full"></div>
                </div>
                
                <div class="relative flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-16 h-16 bg-white bg-opacity-20 backdrop-blur-sm rounded-2xl flex items-center justify-center border border-white/30">
                                <i class="fas fa-calendar-week text-white text-2xl"></i>
                            </div>
                        </div>
                        <div class="ml-6">
                            <h1 class="text-3xl font-bold text-white mb-2">Process Attendance</h1>
                            <p class="text-blue-100 text-lg">
                                Efficiently manage attendance records for all employees
                            </p>
                        </div>
                    </div>
                    <div class="hidden lg:flex items-center space-x-4">
                        <a href="{{ route('dashboard') }}" class="bg-white/20 hover:bg-white/30 backdrop-blur-sm text-white font-semibold py-3 px-6 rounded-xl border border-white/30 transition-all duration-200 flex items-center group">
                            <i class="fas fa-arrow-left mr-2 group-hover:-translate-x-1 transition-transform duration-200"></i>
                            Back to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile Back Button -->
        <div class="lg:hidden">
            <a href="{{ route('dashboard') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors duration-200">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to Dashboard
            </a>
        </div>

        <!-- Main Content Card -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="bg-gradient-to-r from-gray-50 to-white px-8 py-6 border-b border-gray-200">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-indigo-100 rounded-xl flex items-center justify-center">
                        <i class="fas fa-calendar-alt text-indigo-600"></i>
                    </div>
                    <div class="ml-4">
                        <h2 class="text-xl font-bold text-gray-900">Select Period</h2>
                        <p class="text-gray-600 text-sm">Choose the month and year to manage attendance records</p>
                    </div>
                </div>
            </div>
            
            <div class="p-8">
                @if(isset($pendingLeaves) && count($pendingLeaves) > 0)
                    <div class="mb-8 bg-yellow-50 border-l-4 border-yellow-400 rounded-r-xl shadow-sm overflow-hidden">
                        <div class="p-4">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-exclamation-triangle text-yellow-400 text-xl"></i>
                                </div>
                                <div class="ml-3 w-full">
                                    <h3 class="text-lg font-medium text-yellow-800 mb-2">Pending Leaves Detected</h3>
                                    <p class="text-yellow-700">There are <strong>{{ count($pendingLeaves) }}</strong> pending leave applications for the selected month. Please review and take action on these leaves before processing attendance.</p>
                                    
                                    <!-- Pending Leaves Summary -->
                                    <div class="mt-4 bg-white bg-opacity-50 rounded-lg p-3 border border-yellow-200">
                                        <h4 class="text-sm font-semibold text-yellow-800 mb-2">Pending Leave Applications:</h4>
                                        <div class="space-y-2">
                                            @foreach($pendingLeaves as $leave)
                                                <div class="flex items-center justify-between text-sm">
                                                    <span class="text-gray-600">{{ $leave->employee->name }}</span>
                                                    <div class="flex items-center space-x-4">
                                                        <span class="text-gray-500">{{ $leave->start_date->format('d M Y') }}</span>
                                                        <a href="{{ route('leaves.show', $leave) }}" class="text-blue-600 hover:text-blue-700 font-medium">
                                                            Review
                                                            <i class="fas fa-arrow-right ml-1 text-xs"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                    <!-- Action Button -->
                                    <div class="mt-4">
                                        <a href="{{ route('leaves.index', ['status' => 'pending']) }}" 
                                           class="inline-flex items-center px-4 py-2 bg-yellow-100 hover:bg-yellow-200 text-yellow-800 text-sm font-medium rounded-lg transition-colors duration-200">
                                            <i class="fas fa-tasks mr-2"></i>
                                            Manage Pending Leaves
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                            <div class="p-8">
                @if(session('pendingLeaves'))
                    <div class="mb-8 bg-yellow-50 border-l-4 border-yellow-400 rounded-r-xl shadow-sm overflow-hidden">
                        <div class="p-4">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-exclamation-triangle text-yellow-400 text-xl"></i>
                                </div>
                                <div class="ml-3 w-full">
                                    <h3 class="text-lg font-medium text-yellow-800 mb-2">Action Required: Pending Leaves</h3>
                                    <p class="text-yellow-700">{{ session('error') }}</p>
                                    
                                    <!-- Pending Leaves Summary -->
                                    <div class="mt-4 bg-white bg-opacity-50 rounded-lg p-3 border border-yellow-200">
                                        <h4 class="text-sm font-semibold text-yellow-800 mb-2">Pending Leave Applications:</h4>
                                        <div class="space-y-2">
                                            @foreach(session('pendingLeaves') as $leave)
                                                <div class="flex items-center justify-between text-sm">
                                                    <span class="text-gray-600">{{ $leave->employee->name }}</span>
                                                    <div class="flex items-center space-x-4">
                                                        <span class="text-gray-500">{{ $leave->start_date->format('d M Y') }}</span>
                                                        <a href="{{ route('leaves.show', $leave) }}" class="text-blue-600 hover:text-blue-700 font-medium">
                                                            Review
                                                            <i class="fas fa-arrow-right ml-1 text-xs"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                    <!-- Action Button -->
                                    <div class="mt-4">
                                        <a href="{{ route('leaves.index', ['status' => 'pending']) }}" 
                                           class="inline-flex items-center px-4 py-2 bg-yellow-100 hover:bg-yellow-200 text-yellow-800 text-sm font-medium rounded-lg transition-colors duration-200">
                                            <i class="fas fa-tasks mr-2"></i>
                                            Manage Pending Leaves
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <form action="{{ route('admin.attendance.preview') }}" method="GET" class="space-y-8">
                    <!-- Form Fields -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label for="month" class="block text-sm font-semibold text-gray-700 flex items-center">
                                <i class="fas fa-calendar-alt text-indigo-500 mr-2"></i>
                                Select Month
                            </label>
                            <div class="relative">
                                <select id="month" name="month" class="block w-full pl-4 pr-10 py-4 text-base border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-300 bg-gray-50/50 hover:border-indigo-300">
                                    @foreach($months as $key => $month)
                                        <option value="{{ $key }}" {{ $key == now()->month ? 'selected' : '' }}>{{ $month }}</option>
                                    @endforeach
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none">
                                    <i class="fas fa-chevron-down text-gray-400"></i>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label for="year" class="block text-sm font-semibold text-gray-700 flex items-center">
                                <i class="fas fa-calendar text-indigo-500 mr-2"></i>
                                Select Year
                            </label>
                            <div class="relative">
                                <select id="year" name="year" class="block w-full pl-4 pr-10 py-4 text-base border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-300 bg-gray-50/50 hover:border-indigo-300">
                                    @foreach($years as $year)
                                        <option value="{{ $year }}" {{ $year == now()->year ? 'selected' : '' }}>{{ $year }}</option>
                                    @endforeach
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none">
                                    <i class="fas fa-chevron-down text-gray-400"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pt-6 border-t border-gray-200">
                        <a href="{{ route('admin.attendance.test-payroll-api') }}" 
                           class="inline-flex items-center px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-xl border border-gray-300 transition-all duration-200 hover:shadow-md group" 
                           target="_blank">
                            <i class="fas fa-plug mr-2 group-hover:text-indigo-600"></i>
                            Test Payroll API
                        </a>
                        
                        <button type="submit" class="inline-flex items-center px-8 py-3 bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-700 hover:to-blue-700 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200">
                            <i class="fas fa-search mr-2"></i>
                            Preview Attendance
                            <i class="fas fa-arrow-right ml-2"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Information Card -->
        <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-2xl border border-blue-200 shadow-sm overflow-hidden">
            <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-4">
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center">
                        <i class="fas fa-info-circle text-white"></i>
                    </div>
                            <h3 class="ml-3 text-lg font-bold text-white">How to Use Process Attendance</h3>
                </div>
            </div>
            
            <div class="p-6">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="space-y-4">
                        <div class="flex items-start space-x-3">
                            <div class="w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                                <span class="text-blue-600 font-bold text-xs">1</span>
                            </div>
                            <p class="text-gray-700 text-sm">Select month and year to view and manage attendance for all employees</p>
                        </div>
                        
                        <div class="flex items-start space-x-3">
                            <div class="w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                                <span class="text-blue-600 font-bold text-xs">2</span>
                            </div>
                            <p class="text-gray-700 text-sm">Review automatically populated attendance data from leave applications and public holidays</p>
                        </div>
                        
                        <div class="flex items-start space-x-3">
                            <div class="w-6 h-6 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                                <i class="fas fa-star text-green-600 text-xs"></i>
                            </div>
                            <p class="text-gray-700 text-sm"><span class="font-semibold text-green-700">NEW:</span> Individual week-off patterns are now loaded from payroll system (no more universal Sat-Sun)</p>
                        </div>
                    </div>
                    
                    <div class="space-y-4">
                        <div class="flex items-start space-x-3">
                            <div class="w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                                <span class="text-blue-600 font-bold text-xs">3</span>
                            </div>
                            <p class="text-gray-700 text-sm">Make adjustments if needed by clicking on individual attendance cells</p>
                        </div>
                        
                        <div class="flex items-start space-x-3">
                            <div class="w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                                <span class="text-blue-600 font-bold text-xs">4</span>
                            </div>
                            <p class="text-gray-700 text-sm">Save your changes to keep them in the system</p>
                        </div>
                        
                        <div class="flex items-start space-x-3">
                            <div class="w-6 h-6 bg-amber-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                                <i class="fas fa-lock text-amber-600 text-xs"></i>
                            </div>
                            <p class="text-gray-700 text-sm">Lock and submit to payroll when ready <span class="text-amber-600 font-semibold">(cannot be undone)</span></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
@endsection

@push('styles')
<style>

    
    /* Hover effects for form elements */
    select:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }
    
    /* Custom focus styles */
    select:focus {
        transform: translateY(-1px);
        box-shadow: 0 4px 20px rgba(99, 102, 241, 0.3);
    }
    
    /* Button hover animations */
    .hover-lift:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    }
    

</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Form validation and UX improvements
    const form = document.querySelector('form');
    const monthSelect = document.getElementById('month');
    const yearSelect = document.getElementById('year');
    const submitBtn = form.querySelector('button[type="submit"]');
    
    // Add loading state to submit button
    form.addEventListener('submit', function() {
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Loading...';
        submitBtn.disabled = true;
    });
    
    // Add subtle animations on select change
    [monthSelect, yearSelect].forEach(select => {
        select.addEventListener('change', function() {
            this.style.transform = 'scale(1.02)';
            setTimeout(() => {
                this.style.transform = 'scale(1)';
            }, 150);
        });
    });
    
    // Add hover effects to info cards
    const infoItems = document.querySelectorAll('.flex.items-start.space-x-3');
    infoItems.forEach(item => {
        item.addEventListener('mouseenter', function() {
            this.style.transform = 'translateX(4px)';
            this.style.transition = 'transform 0.2s ease';
        });
        
        item.addEventListener('mouseleave', function() {
            this.style.transform = 'translateX(0)';
        });
    });
});
</script>
@endpush
