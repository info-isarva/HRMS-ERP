@extends('layouts.app')

@section('title', 'Reports Dashboard - HRMS')
@section('page-title', 'Reports Management')

@section('content')
<div class="p-6 space-y-6">
    <!-- Header Card -->
    <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-200">
        <div class="bg-gradient-to-r from-cyan-600 to-teal-700 px-8 py-10">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                            <i class="fas fa-chart-line text-white text-xl"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold text-white">Reports Dashboard</h1>
                        <p class="text-cyan-100 text-xs sm:text-sm lg:text-base mt-2">
                            Access and analyze comprehensive attendance and leave reports
                        </p>
                    </div>
                </div>
                <div class="hidden md:flex items-center">
                    <div class="w-16 h-16 bg-white bg-opacity-10 rounded-full flex items-center justify-center">
                        <i class="fas fa-chart-pie text-white text-2xl"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Reports Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        
        <!-- Approved Leaves Report -->
        <a href="{{ route('reports.leave-approved') }}" class="block group">
            <div class="bg-white rounded-xl shadow-sm hover:shadow-lg transition-all duration-300 border border-gray-100 overflow-hidden h-full">
                <div class="p-6">
                    <div class="flex items-start justify-between mb-4">
                        <div class="bg-gradient-to-br from-green-500 to-emerald-600 w-12 h-12 rounded-lg flex items-center justify-center shadow-md transform group-hover:scale-110 transition-transform duration-300">
                            <i class="fas fa-check-circle text-white text-xl"></i>
                        </div>
                        <span class="bg-green-100 text-green-700 text-xs px-2 py-1 rounded-full font-medium">Operational</span>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 group-hover:text-green-600 transition-colors mb-2">Approved Leaves</h3>
                    <p class="text-sm text-gray-500 mb-4 line-clamp-2">
                        View comprehensive list of employees on approved leave within a specific date range.
                    </p>
                </div>
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-between group-hover:bg-green-50 transition-colors">
                    <span class="text-sm font-medium text-gray-600 group-hover:text-green-700">View Report</span>
                    <i class="fas fa-arrow-right text-gray-400 group-hover:text-green-600 transform group-hover:translate-x-1 transition-all"></i>
                </div>
            </div>
        </a>

        <!-- Rejected Leaves Report -->
        <a href="{{ route('reports.leave-rejected') }}" class="block group">
            <div class="bg-white rounded-xl shadow-sm hover:shadow-lg transition-all duration-300 border border-gray-100 overflow-hidden h-full">
                <div class="p-6">
                    <div class="flex items-start justify-between mb-4">
                        <div class="bg-gradient-to-br from-red-500 to-pink-600 w-12 h-12 rounded-lg flex items-center justify-center shadow-md transform group-hover:scale-110 transition-transform duration-300">
                            <i class="fas fa-times-circle text-white text-xl"></i>
                        </div>
                         <span class="bg-red-100 text-red-700 text-xs px-2 py-1 rounded-full font-medium">Operational</span>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 group-hover:text-red-600 transition-colors mb-2">Rejected Leaves</h3>
                    <p class="text-sm text-gray-500 mb-4 line-clamp-2">
                        Analyze rejected leave applications and review rejection reasons.
                    </p>
                </div>
                 <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-between group-hover:bg-red-50 transition-colors">
                    <span class="text-sm font-medium text-gray-600 group-hover:text-red-700">View Report</span>
                    <i class="fas fa-arrow-right text-gray-400 group-hover:text-red-600 transform group-hover:translate-x-1 transition-all"></i>
                </div>
            </div>
        </a>

        <!-- Employee Leave Status Report -->
        <a href="{{ route('reports.employee-leave-status') }}" class="block group">
             <div class="bg-white rounded-xl shadow-sm hover:shadow-lg transition-all duration-300 border border-gray-100 overflow-hidden h-full">
                <div class="p-6">
                    <div class="flex items-start justify-between mb-4">
                         <div class="bg-gradient-to-br from-blue-500 to-indigo-600 w-12 h-12 rounded-lg flex items-center justify-center shadow-md transform group-hover:scale-110 transition-transform duration-300">
                            <i class="fas fa-chart-pie text-white text-xl"></i>
                        </div>
                        <span class="bg-blue-100 text-blue-700 text-xs px-2 py-1 rounded-full font-medium">Analytics</span>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 group-hover:text-blue-600 transition-colors mb-2">All Employee Leave Status</h3>
                    <p class="text-sm text-gray-500 mb-4 line-clamp-2">
                         Overview of total available leave balance and total leaves taken for each employee.
                    </p>
                </div>
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-between group-hover:bg-blue-50 transition-colors">
                    <span class="text-sm font-medium text-gray-600 group-hover:text-blue-700">View Report</span>
                    <i class="fas fa-arrow-right text-gray-400 group-hover:text-blue-600 transform group-hover:translate-x-1 transition-all"></i>
                </div>
            </div>
        <!-- Employee Monthly Leave Report -->
        <a href="{{ route('reports.employee-monthly') }}" class="block group">
            <div class="bg-white rounded-xl shadow-sm hover:shadow-lg transition-all duration-300 border border-gray-100 overflow-hidden h-full">
                <div class="p-6">
                    <div class="flex items-start justify-between mb-4">
                        <div class="bg-gradient-to-br from-teal-500 to-emerald-600 w-12 h-12 rounded-lg flex items-center justify-center shadow-md transform group-hover:scale-110 transition-transform duration-300">
                            <i class="fas fa-calendar-alt text-white text-xl"></i>
                        </div>
                        <span class="bg-teal-100 text-teal-700 text-xs px-2 py-1 rounded-full font-medium">Monthly Track</span>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 group-hover:text-teal-600 transition-colors mb-2">Employee Monthly Leaves</h3>
                    <p class="text-sm text-gray-500 mb-4 line-clamp-2">
                        Detailed monthly breakdown of leave applications for individual employees.
                    </p>
                </div>
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-between group-hover:bg-teal-50 transition-colors">
                    <span class="text-sm font-medium text-gray-600 group-hover:text-teal-700">View Report</span>
                    <i class="fas fa-arrow-right text-gray-400 group-hover:text-teal-600 transform group-hover:translate-x-1 transition-all"></i>
                </div>
            </div>
        </a>

    </div>
</div>
@endsection
