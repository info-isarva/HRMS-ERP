@extends('layouts.app')

@section('title', 'Leave Types Management - HRMS')

@section('page-title', 'Leave Types Management')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50">
    <div class="max-w-full mx-auto p-6 space-y-6">
    <!-- Header -->
    <div class="mb-8">
        <div class="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-2xl p-8 text-white relative overflow-hidden">
            <div class="absolute inset-0 opacity-10">
                <div class="absolute -top-4 -right-4 w-24 h-24 bg-white rounded-full"></div>
                <div class="absolute top-10 -right-8 w-16 h-16 bg-white rounded-full"></div>
                <div class="absolute -bottom-6 -left-6 w-20 h-20 bg-white rounded-full"></div>
            </div>
            <div class="relative">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold mb-2 flex items-center">
                            <i class="fas fa-clipboard-list mr-3"></i>
                            Leave Types Management
                        </h1>
                        <p class="text-indigo-100 text-lg">
                            Configure and manage different types of leaves with department assignments
                        </p>
                    </div>
                    <div class="hidden lg:block">
                        <div class="w-32 h-32 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                            <i class="fas fa-cogs text-4xl text-white"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 rounded-md p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-check-circle text-green-400"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 rounded-md p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-circle text-red-400"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800">There were errors:</h3>
                    <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <!-- Filters and Actions -->
    <div class="bg-white rounded-xl shadow-sm p-6 mb-8 border border-gray-200">
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center space-y-4 lg:space-y-0">
            <div class="flex flex-col sm:flex-row items-start sm:items-center space-y-3 sm:space-y-0 sm:space-x-4">
                <!-- Financial Year Filter -->
                <form method="GET" class="flex items-center space-x-2">
                    <label for="financial_year" class="text-sm font-medium text-gray-700 whitespace-nowrap">Financial Year:</label>
                    <select name="financial_year" id="financial_year" 
                            class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm min-w-[140px]"
                            onchange="this.form.submit()">
                        @foreach($financialYears as $year)
                            <option value="{{ $year }}" {{ $selectedYear == $year ? 'selected' : '' }}>
                                {{ $year }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>

            <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-4">
                <!-- Sync Departments -->
                <form method="POST" action="{{ route('leave-types.sync-departments') }}" class="inline">
                    @csrf
                    <button type="submit" 
                            class="inline-flex items-center px-4 py-2 border border-blue-300 rounded-lg shadow-sm text-sm font-medium text-blue-700 bg-blue-50 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                        <i class="fas fa-sync mr-2"></i>
                        Sync Departments
                    </button>
                </form>

                <!-- Add New Leave Type -->
                <a href="{{ route('leave-types.create') }}" 
                   class="inline-flex items-center px-6 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold rounded-lg shadow hover:from-indigo-700 hover:to-purple-700 transition">
                    <i class="fas fa-plus mr-2"></i>
                    Add Leave Type
                </a>
            </div>
        </div>
    </div>

    <!-- Leave Types List -->
    @if($leaveTypes->count() > 0)
        <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
            @foreach($leaveTypes as $leaveType)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-lg transition-shadow duration-300">
                    <div class="p-6">
                        <!-- Header -->
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex items-center">
                                <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-calendar-alt text-white text-lg"></i>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-lg font-semibold text-gray-900">{{ $leaveType->name }}</h3>
                                    <p class="text-sm text-gray-500">{{ $leaveType->code }}</p>
                                </div>
                            </div>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $leaveType->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $leaveType->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>

                        <!-- Description -->
                        @if($leaveType->description)
                            <p class="text-gray-600 text-sm mb-4 line-clamp-2">{{ $leaveType->description }}</p>
                        @endif

                        <!-- Details -->
                        <div class="space-y-3 mb-6">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-500">Days Allowed:</span>
                                <span class="text-lg font-semibold text-indigo-600">{{ $leaveType->days_count }}</span>
                            </div>
                            
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-500">Departments:</span>
                                <span class="text-sm font-medium text-gray-900">{{ $leaveType->departments->count() }}</span>
                            </div>

                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-500">Financial Year:</span>
                                <span class="text-sm font-medium text-gray-900">{{ $leaveType->financial_year }}</span>
                            </div>
                        </div>

                        <!-- Departments List -->
                        @if($leaveType->departments->count() > 0)
                            <div class="mb-4">
                                <span class="text-xs font-medium text-gray-700 uppercase tracking-wider">Assigned Departments:</span>
                                <div class="mt-2 flex flex-wrap gap-1">
                                    @foreach($leaveType->departments->take(3) as $department)
                                        <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ $department->code }}
                                        </span>
                                    @endforeach
                                    @if($leaveType->departments->count() > 3)
                                        <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-gray-100 text-gray-600">
                                            +{{ $leaveType->departments->count() - 3 }} more
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endif

                        <!-- Actions -->
                        <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                            <div class="flex items-center space-x-2">
                                <a href="{{ route('leave-types.show', $leaveType) }}" 
                                   class="text-blue-600 hover:text-blue-900 p-2 rounded-lg hover:bg-blue-100 transition" 
                                   title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('leave-types.edit', $leaveType) }}" 
                                   class="text-green-600 hover:text-green-900 p-2 rounded-lg hover:bg-green-100 transition" 
                                   title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </div>
                            
                            @if($leaveType->leaveApplications()->count() == 0)
                                <form action="{{ route('leave-types.destroy', $leaveType) }}" 
                                      method="POST" 
                                      class="inline" 
                                      onsubmit="return confirm('Are you sure you want to delete this leave type?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="text-red-600 hover:text-red-900 p-2 rounded-lg hover:bg-red-100 transition" 
                                            title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            @else
                                <span class="text-gray-400 p-2" title="Cannot delete - has applications">
                                    <i class="fas fa-lock"></i>
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        @if($leaveTypes->hasPages())
            <div class="mt-8">
                {{ $leaveTypes->links() }}
            </div>
        @endif
    @else
        <div class="text-center py-12">
            <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-clipboard-list text-gray-400 text-2xl"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No Leave Types Found</h3>
            <p class="text-gray-500 mb-6">No leave types found for the selected financial year.</p>
            <a href="{{ route('leave-types.create') }}" 
               class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold rounded-lg shadow hover:from-indigo-700 hover:to-purple-700 transition">
                <i class="fas fa-plus mr-2"></i>
                Add First Leave Type
            </a>
        </div>
    @endif
</div>
@endsection
