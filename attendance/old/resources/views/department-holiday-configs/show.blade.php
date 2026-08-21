@extends('layouts.app')

@section('title', 'Department Holiday Configuration Details - HRMS')
@section('page-title', 'Department Holiday Configuration Details')

@section('content')
<div class="container mx-auto max-w-full px-4 sm:px-6 lg:px-8 space-y-8 py-8">
    <!-- Error Message -->
    @if(isset($errorMessage))
        <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded-r-md shadow-sm">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-circle text-red-400 text-lg"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-red-800">{{ $errorMessage }}</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Header with gradient background -->
    <div class="mb-8">
        <div class="flex items-center justify-between bg-gradient-to-r from-indigo-600 to-blue-600 px-8 py-10 rounded-lg shadow-lg">
            <div class="flex items-center space-x-4">
                <div class="w-16 h-16 bg-gradient-to-br from-indigo-500 to-blue-500 rounded-full flex items-center justify-center shadow-lg">
                    <i class="fas fa-cogs text-white text-2xl"></i>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-white">
                        {{ $departmentHolidayConfig->department ? $departmentHolidayConfig->department->name : 'Department Not Found' }}
                    </h1>
                    <p class="text-blue-100 mt-2">Holiday configuration for financial year {{ $departmentHolidayConfig->financial_year }}</p>
                </div>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('holiday-department-configs.edit', $departmentHolidayConfig) }}" 
                   class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-white/20 hover:bg-white/30 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <i class="fas fa-edit mr-2"></i>
                    Edit
                </a>
                <a href="{{ route('holiday-department-configs.index') }}" 
                   class="inline-flex items-center px-4 py-2 border border-white/30 rounded-md shadow-sm text-sm font-medium text-white bg-white/20 hover:bg-white/30 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Back to Configurations
                </a>
            </div>
        </div>
    </div>

    <!-- Configuration Information -->
    <div class="bg-white shadow-sm rounded-lg overflow-hidden mb-6">
        <div class="px-6 py-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Left Column -->
                <div class="space-y-6">
                    <!-- Basic Information -->
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center space-x-3">
                            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-info-circle text-blue-600"></i>
                            </div>
                            <span>Basic Information</span>
                        </h3>
                        @if($departmentHolidayConfig->department)
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Department Name</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $departmentHolidayConfig->department->name }}</p>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Department Code</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $departmentHolidayConfig->department->code }}</p>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Financial Year</label>
                                    <p class="mt-1 text-sm text-gray-900">FY {{ $departmentHolidayConfig->financial_year }}</p>
                                </div>
                                
                                @if($departmentHolidayConfig->department->description)
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Description</label>
                                        <p class="mt-1 text-sm text-gray-900">{{ $departmentHolidayConfig->department->description }}</p>
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="bg-red-50 border border-red-200 rounded-md p-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-exclamation-triangle text-red-400"></i>
                                    </div>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium text-red-800">Department Not Found</h3>
                                        <p class="mt-1 text-sm text-red-700">
                                            The department associated with this configuration no longer exists or has been removed.
                                            <br>Department ID: {{ $departmentHolidayConfig->department_id }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="space-y-4 mt-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Financial Year</label>
                                    <p class="mt-1 text-sm text-gray-900">FY {{ $departmentHolidayConfig->financial_year }}</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Right Column -->
                <div class="space-y-6">
                    <!-- Holiday Statistics -->
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center space-x-3">
                            <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-chart-bar text-green-600"></i>
                            </div>
                            <span>Holiday Statistics</span>
                        </h3>
                        <div class="space-y-4">
                            <div class="bg-blue-50 rounded-lg p-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <label class="block text-sm font-medium text-blue-700">Total Holidays per Employee</label>
                                        <p class="text-2xl font-bold text-blue-900">{{ $departmentHolidayConfig->allowed_holidays }}</p>
                                    </div>
                                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                                        <i class="fas fa-calendar-plus text-blue-600 text-xl"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div class="bg-indigo-50 rounded-lg p-4">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <label class="block text-sm font-medium text-indigo-700">Fixed Holidays</label>
                                            <p class="text-xl font-bold text-indigo-900">{{ $departmentHolidayConfig->fixed_public_holidays ?? 0 }}</p>
                                        </div>
                                        <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center">
                                            <i class="fas fa-calendar-check text-indigo-600"></i>
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-purple-50 rounded-lg p-4">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <label class="block text-sm font-medium text-purple-700">Flexible Holidays</label>
                                            <p class="text-xl font-bold text-purple-900">{{ $departmentHolidayConfig->flexible_public_holidays ?? 0 }}</p>
                                        </div>
                                        <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center">
                                            <i class="fas fa-calendar-alt text-purple-600"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="bg-orange-50 rounded-lg p-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <label class="block text-sm font-medium text-orange-700">Used Holidays</label>
                                        <p class="text-2xl font-bold text-orange-900">{{ $departmentHolidayConfig->used_holidays }}</p>
                                    </div>
                                    <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center">
                                        <i class="fas fa-calendar-check text-orange-600 text-xl"></i>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="bg-green-50 rounded-lg p-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <label class="block text-sm font-medium text-green-700">Remaining Holidays</label>
                                        <p class="text-2xl font-bold text-green-900">{{ $departmentHolidayConfig->remaining_holidays }}</p>
                                    </div>
                                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                                        <i class="fas fa-calendar text-green-600 text-xl"></i>
                                    </div>
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Usage Percentage</label>
                                <div class="mt-2">
                                    <div class="flex items-center justify-between text-sm">
                                        <span>{{ $departmentHolidayConfig->usage_percentage }}% used</span>
                                        <span>{{ $departmentHolidayConfig->used_holidays }}/{{ $departmentHolidayConfig->allowed_holidays }}</span>
                                    </div>
                                    <div class="mt-1 w-full bg-gray-200 rounded-full h-2">
                                        <div class="bg-gradient-to-r from-blue-500 to-blue-600 h-2 rounded-full" 
                                             style="width: {{ $departmentHolidayConfig->usage_percentage }}%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Status & Properties -->
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Status & Properties</h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Status</label>
                                <div class="mt-1">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $departmentHolidayConfig->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        <i class="fas fa-circle text-xs mr-1"></i>
                                        {{ $departmentHolidayConfig->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </div>
                            </div>
                            
                            @if($departmentHolidayConfig->department)
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Department Status</label>
                                    <p class="mt-1 text-sm text-gray-900">
                                        @if($departmentHolidayConfig->department->is_active)
                                            <span class="inline-flex items-center text-green-600">
                                                <i class="fas fa-check-circle mr-1"></i>
                                                Active Department
                                            </span>
                                        @else
                                            <span class="inline-flex items-center text-red-600">
                                                <i class="fas fa-times-circle mr-1"></i>
                                                Inactive Department
                                            </span>
                                        @endif
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Audit Information -->
    <div class="bg-white shadow-sm rounded-lg overflow-hidden mb-6">
        <div class="px-6 py-6">
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center space-x-3">
                    <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-history text-yellow-600"></i>
                    </div>
                    <span>Audit Information</span>
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Creation Details</h4>
                        <div class="space-y-2 text-sm">
                            <div>
                                <span class="text-gray-600">Created by:</span>
                                <span class="font-medium">{{ $departmentHolidayConfig->creator->name ?? 'Unknown' }}</span>
                            </div>
                            <div>
                                <span class="text-gray-600">Created on:</span>
                                <span class="font-medium">{{ $departmentHolidayConfig->created_at->format('M d, Y g:i A') }}</span>
                            </div>
                        </div>
                    </div>
                    
                    @if($departmentHolidayConfig->updated_at && $departmentHolidayConfig->updated_at != $departmentHolidayConfig->created_at)
                        <div>
                            <h4 class="text-sm font-medium text-gray-700 mb-2">Last Update</h4>
                            <div class="space-y-2 text-sm">
                                <div>
                                    <span class="text-gray-600">Updated by:</span>
                                    <span class="font-medium">{{ $departmentHolidayConfig->updater->name ?? 'Unknown' }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-600">Updated on:</span>
                                    <span class="font-medium">{{ $departmentHolidayConfig->updated_at->format('M d, Y g:i A') }}</span>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="bg-white shadow-sm rounded-lg overflow-hidden">
        <div class="px-6 py-6">
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center space-x-3">
                    <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-tasks text-red-600"></i>
                    </div>
                    <span>Actions</span>
                </h3>
                <div class="flex flex-wrap gap-3">
                    @if($departmentHolidayConfig->department)
                        <a href="{{ route('holiday-department-configs.edit', $departmentHolidayConfig) }}" 
                           class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <i class="fas fa-edit mr-2"></i>
                            Edit Configuration
                        </a>
                        
                        @if($departmentHolidayConfig->used_holidays == 0)
                            <form method="POST" action="{{ route('holiday-department-configs.destroy', $departmentHolidayConfig) }}" 
                                  onsubmit="return confirm('Are you sure you want to delete this configuration? This action cannot be undone.')" 
                                  class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="inline-flex items-center px-4 py-2 border border-red-300 rounded-md shadow-sm text-sm font-medium text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                    <i class="fas fa-trash mr-2"></i>
                                    Delete Configuration
                                </button>
                            </form>
                        @else
                            <button type="button" 
                                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-400 bg-gray-50 cursor-not-allowed" 
                                    disabled
                                    title="Cannot delete configuration with used holidays">
                                <i class="fas fa-ban mr-2"></i>
                                Cannot Delete ({{ $departmentHolidayConfig->used_holidays }} holidays used)
                            </button>
                        @endif
                    @else
                        <div class="inline-flex items-center px-4 py-2 border border-red-300 rounded-md shadow-sm text-sm font-medium text-red-700 bg-red-50">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            Configuration cannot be modified - Department missing
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
