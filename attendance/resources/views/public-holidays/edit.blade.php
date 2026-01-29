@extends('layouts.app')

@section('title', 'Edit Public Holiday - HRMS')
@section('page-title', 'Edit Public Holiday')

@push('head')
    <!-- Custom Department Selector Styling -->
    <style>
        .department-selector {
            max-height: 300px;
            overflow-y: auto;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            background: white;
        }
        
        .department-selector::-webkit-scrollbar {
            width: 8px;
        }
        
        .department-selector::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 4px;
        }
        
        .department-selector::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
        
        .department-selector::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
        
        .department-item {
            transition: all 0.2s ease;
        }
        
        .department-item:hover {
            background-color: #f8fafc;
            transform: translateX(2px);
        }
        
        .department-item.selected {
            background-color: #eff6ff;
            border-left: 4px solid #3b82f6;
        }
        
        .search-highlight {
            background-color: #fef3c7;
            font-weight: 600;
        }
        
        .select-all-btn {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            transition: all 0.3s ease;
        }
        
        .select-all-btn:hover {
            background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }
        
        .quick-filters {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
            margin-bottom: 1rem;
        }
        
        .quick-filter {
            padding: 0.25rem 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 9999px;
            background: white;
            color: #6b7280;
            font-size: 0.75rem;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .quick-filter:hover, .quick-filter.active {
            background: #3b82f6;
            color: white;
            border-color: #3b82f6;
        }
    </style>
@endpush

@section('content')
<div class="container mx-auto max-w-full px-4 py-4 sm:py-6">
    <!-- Header -->
    <div class="mb-6 sm:mb-8">
        <div class="flex flex-col sm:flex-row items-center justify-between bg-gradient-to-r from-indigo-600 to-blue-600 px-4 py-6 sm:px-8 sm:py-10 rounded-lg shadow-lg w-full">
            <div class="flex items-center space-x-3 sm:space-x-4 mb-4 sm:mb-0">
                <div class="w-12 h-12 sm:w-16 sm:h-16 bg-gradient-to-br from-indigo-500 to-blue-500 rounded-full flex items-center justify-center shadow-lg">
                    <i class="fas fa-calendar-alt text-white text-xl sm:text-2xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-white">Edit Public Holiday</h1>
                    <p class="text-blue-100 mt-1 sm:mt-2 text-sm sm:text-base">Modify the details of the public holiday</p>
                </div>
            </div>
            <div class="flex flex-col sm:flex-row items-center space-y-2 sm:space-y-0 sm:space-x-3">
                <a href="{{ route('public-holidays.show', $publicHoliday) }}" class="inline-flex items-center px-3 py-2 sm:px-4 sm:py-2 border border-white/30 rounded-md shadow-sm text-sm font-medium text-white bg-white/20 hover:bg-white/30 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <i class="fas fa-eye mr-2"></i>
                    View
                </a>
                <a href="{{ route('public-holidays.index') }}" class="inline-flex items-center px-3 py-2 sm:px-4 sm:py-2 border border-white/30 rounded-md shadow-sm text-sm font-medium text-white bg-white/20 hover:bg-white/30 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Back to Holidays
                </a>
            </div>
        </div>
    </div>

    <!-- Alert Messages -->
    @if ($errors->any())
        <div class="mb-4 sm:mb-6 bg-red-50 border border-red-200 rounded-md p-3 sm:p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-circle text-red-400"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800">There were errors with your submission:</h3>
                    <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <!-- Form -->
    <form method="POST" action="{{ route('public-holidays.update', $publicHoliday) }}" class="bg-white shadow-sm rounded-lg overflow-hidden">
        @csrf
        @method('PUT')
        
        <div class="px-4 py-6 sm:px-6 sm:py-8 space-y-4 sm:space-y-6">
            <!-- Basic Information -->
            <div>
                <h3 class="text-base sm:text-lg font-medium text-gray-900 mb-3 sm:mb-4 flex items-center space-x-2 sm:space-x-3">
                    <div class="w-6 h-6 sm:w-8 sm:h-8 bg-blue-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-info-circle text-blue-600 text-sm sm:text-base"></i>
                    </div>
                    <span>Basic Information</span>
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
                    <!-- Holiday Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                            Holiday Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="name" name="name" value="{{ old('name', $publicHoliday->name) }}" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('name') border-red-500 @enderror"
                               placeholder="e.g., Independence Day" required>
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Date -->
                    <div>
                        <label for="date" class="block text-sm font-medium text-gray-700 mb-2">
                            Holiday Date <span class="text-red-500">*</span>
                        </label>
                        <input type="date" id="date" name="date" value="{{ old('date', $publicHoliday->date->format('Y-m-d')) }}" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('date') border-red-500 @enderror"
                               required>
                        @error('date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Financial Year (Read-only) -->
                    <div>
                        <label for="financial_year_display" class="block text-sm font-medium text-gray-700 mb-2">
                            Financial Year
                        </label>
                        <input type="text" id="financial_year_display" value="FY {{ $publicHoliday->financial_year }}" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-gray-50 text-gray-500"
                               readonly>
                        <p class="mt-1 text-xs text-gray-500">Financial year cannot be changed after creation.</p>
                    </div>

                    <!-- Holiday Type (Read-only) -->
                    <div>
                        <label for="type_display" class="block text-sm font-medium text-gray-700 mb-2">
                            Holiday Type
                        </label>
                        <div class="flex items-center space-x-3">
                            <input type="text" id="type_display" value="{{ ucfirst($publicHoliday->type) }}" 
                                   class="flex-1 px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-gray-50 text-gray-500"
                                   readonly>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $publicHoliday->type === 'fixed' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                                {{ ucfirst($publicHoliday->type) }}
                            </span>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">
                            <i class="fas fa-info-circle mr-1"></i>
                            Holiday type cannot be changed after creation.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Department Selection -->
            <div>
                <h3 class="text-base sm:text-lg font-medium text-gray-900 mb-3 sm:mb-4 flex items-center space-x-2 sm:space-x-3">
                    <div class="w-6 h-6 sm:w-8 sm:h-8 bg-green-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-building text-green-600 text-sm sm:text-base"></i>
                    </div>
                    <span>Department Assignment</span>
                </h3>
                <div class="space-y-3 sm:space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2 sm:mb-3">
                            Select Departments <span class="text-red-500">*</span>
                        </label>
                        
                        <!-- Search and Quick Actions -->
                        <div class="space-y-2 sm:space-y-3 mb-3 sm:mb-4">
                            <!-- Search Bar -->
                            <div class="flex flex-col sm:flex-row items-stretch sm:items-center space-y-2 sm:space-y-0 sm:space-x-3">
                                <div class="flex-1 relative">
                                    <input type="text" id="departmentSearch" 
                                           class="w-full px-3 py-2 sm:px-4 sm:py-2 pl-8 sm:pl-10 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                                           placeholder="Search departments...">
                                    <div class="absolute inset-y-0 left-0 pl-2 sm:pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-search text-gray-400 text-sm"></i>
                                    </div>
                                </div>
                                <button type="button" id="selectAllAvailable" class="px-3 py-2 sm:px-4 sm:py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors">
                                    <i class="fas fa-check-all mr-1"></i>
                                    All Available
                                </button>
                                <button type="button" id="clearAllSelections" class="px-3 py-2 sm:px-4 sm:py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                                    <i class="fas fa-times mr-1"></i>
                                    Clear
                                </button>
                            </div>
                            
                            <!-- Selection Summary -->
                            <div class="flex flex-col sm:flex-row items-center justify-between text-sm bg-blue-50 px-3 py-2 sm:px-4 sm:py-2 rounded-lg space-y-1 sm:space-y-0">
                                <span class="text-blue-700">
                                    <span id="selectedCount" class="font-semibold">0</span> departments selected
                                </span>
                                <span class="text-blue-600">
                                    <span id="totalEmployees" class="font-semibold">0</span> employees affected
                                </span>
                            </div>
                        </div>
                        
                        <!-- Compact Department Grid -->
                        <div class="border border-gray-200 rounded-lg bg-white max-h-64 sm:max-h-80 overflow-y-auto">
                            <div id="departmentGrid" class="p-3 sm:p-4 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-2 sm:gap-3">
                                @foreach($departments as $department)
                                    @php
                                        $config = $department->departmentHolidayConfigs->first();
                                        $hasConfig = $config !== null;
                                        $isSelected = in_array($department->id, $selectedDepartments);
                                        
                                        // For fixed holidays, check if we have quota
                                        $fixedHolidaysUsed = $hasConfig ? 
                                            \App\Models\PublicHoliday::whereHas('departments', function($query) use ($department) {
                                                $query->where('departments.id', $department->id);
                                            })
                                            ->where('financial_year', $publicHoliday->financial_year)
                                            ->where('type', 'fixed')
                                            ->where('status', 'active')
                                            ->where('id', '!=', $publicHoliday->id) // Exclude current holiday
                                            ->count() : 0;
                                        
                                        $flexibleHolidaysUsed = $hasConfig ? 
                                            \App\Models\PublicHoliday::whereHas('departments', function($query) use ($department) {
                                                $query->where('departments.id', $department->id);
                                            })
                                            ->where('financial_year', $publicHoliday->financial_year)
                                            ->where('type', 'flexible')
                                            ->where('status', 'active')
                                            ->where('id', '!=', $publicHoliday->id) // Exclude current holiday
                                            ->count() : 0;
                                        
                                        $remainingFixed = $hasConfig ? max(0, $config->fixed_public_holidays - $fixedHolidaysUsed) : 0;
                                        $remainingFlexible = $hasConfig ? 'Unlimited' : 'No config';
                                        
                                        // For edit form, if it's a fixed holiday, check availability
                                        $isAvailable = $hasConfig && ($publicHoliday->type === 'flexible' || $remainingFixed > 0);
                                    @endphp
                                    <div class="department-item relative {{ !$hasConfig ? 'opacity-50' : '' }} {{ $isSelected ? 'selected' : '' }}" 
                                         data-department-name="{{ strtolower($department->name) }}"
                                         data-department-code="{{ strtolower($department->code) }}"
                                         data-available="{{ $isAvailable ? 'true' : 'false' }}"
                                         data-has-config="{{ $hasConfig ? 'true' : 'false' }}"
                                         data-remaining-fixed="{{ $remainingFixed }}"
                                         data-remaining-flexible="unlimited"
                                         data-used-fixed="{{ $fixedHolidaysUsed }}"
                                         data-used-flexible="{{ $flexibleHolidaysUsed }}"
                                         data-total-fixed="{{ $hasConfig ? $config->fixed_public_holidays : 0 }}"
                                         data-total-flexible="{{ $hasConfig ? $config->flexible_public_holidays : 0 }}">
                                        <label class="block p-2 sm:p-3 border border-gray-200 rounded-lg hover:border-blue-300 hover:bg-blue-50 transition-all cursor-pointer {{ !$hasConfig ? 'cursor-not-allowed' : '' }}">
                                            <div class="flex items-start space-x-2">
                                                <input type="checkbox" 
                                                       name="departments[]" 
                                                       value="{{ $department->id }}"
                                                       class="dept-checkbox mt-1 h-3 w-3 sm:h-4 sm:w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                                       {{ $isSelected ? 'checked' : '' }}
                                                       {{ !$hasConfig ? 'disabled' : '' }}
                                                       data-employees="{{ $hasConfig ? $config->employee_count : 0 }}">
                                                <div class="flex-1 min-w-0">
                                                    <div class="font-medium text-xs sm:text-sm text-gray-900 leading-tight department-name">
                                                        {{ $department->name }}
                                                    </div>
                                                    <div class="text-xs text-gray-500 mt-1 department-code">
                                                        {{ $department->code }}
                                                    </div>
                                                    <div class="flex items-center justify-between mt-2">
                                                        <span class="text-xs text-blue-600">
                                                            <i class="fas fa-users text-xs mr-1"></i>{{ $hasConfig ? $config->employee_count : 0 }}
                                                        </span>
                                                        @if($hasConfig)
                                                            <span class="quota-info inline-flex items-center px-1.5 py-0.5 sm:px-2 sm:py-0.5 rounded-full text-xs font-medium 
                                                                {{ $publicHoliday->type === 'flexible' ? 'bg-blue-100 text-blue-700' : 
                                                                   ($remainingFixed > 0 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700') }}">
                                                                <i class="fas {{ $publicHoliday->type === 'flexible' || $remainingFixed > 0 ? 'fa-check' : 'fa-times' }} text-xs mr-1"></i>
                                                                <span class="quota-text">
                                                                    {{ $publicHoliday->type === 'flexible' ? 'Unlimited' : "{$remainingFixed}/{$config->fixed_public_holidays}" }}
                                                                </span>
                                                            </span>
                                                        @else
                                                            <span class="inline-flex items-center px-1.5 py-0.5 sm:px-2 sm:py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">
                                                                <i class="fas fa-times text-xs mr-1"></i>No Config
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                            
                            <!-- No Results Message -->
                            <div id="noResultsMessage" class="hidden text-center py-6 sm:py-8 text-gray-500">
                                <i class="fas fa-search text-gray-400 text-xl sm:text-2xl mb-2"></i>
                                <p class="text-sm">No departments found matching your search.</p>
                            </div>
                        </div>
                        
                        <!-- Help Text -->
                        <div class="mt-3 p-3 sm:p-4 bg-blue-50 border border-blue-200 rounded-lg">
                            <p class="text-sm text-blue-700">
                                <i class="fas fa-info-circle mr-2 text-sm"></i>
                                <strong>Per-Employee Quota:</strong> Each employee in selected departments will get this holiday added to their quota. Only departments with remaining holiday quota per employee are available for selection.
                            </p>
                        </div>
                        
                        @error('departments')
                            <p class="mt-2 text-sm text-red-600 flex items-center">
                                <i class="fas fa-exclamation-circle mr-1"></i>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Holiday Details -->
            <div>
                <h3 class="text-base sm:text-lg font-medium text-gray-900 mb-3 sm:mb-4 flex items-center space-x-2 sm:space-x-3">
                    <div class="w-6 h-6 sm:w-8 sm:h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-list-alt text-yellow-600 text-sm sm:text-base"></i>
                    </div>
                    <span>Holiday Details</span>
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
                    <!-- Description -->
                    <div class="lg:col-span-2">
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                            Description
                        </label>
                        <textarea id="description" name="description" rows="3" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('description') border-red-500 @enderror"
                                  placeholder="Brief description of the holiday...">{{ old('description', $publicHoliday->description) }}</textarea>
                        @error('description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Status -->
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                            Status <span class="text-red-500">*</span>
                        </label>
                        <select id="status" name="status" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('status') border-red-500 @enderror"
                                required>
                            <option value="active" {{ old('status', $publicHoliday->status) == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status', $publicHoliday->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                        @error('status')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Color -->
                    <div>
                        <label for="color" class="block text-sm font-medium text-gray-700 mb-2">
                            Color <span class="text-red-500">*</span>
                        </label>
                        <div class="flex items-center space-x-3">
                            <input type="color" id="color" name="color" value="{{ old('color', $publicHoliday->color) }}" 
                                   class="h-10 w-16 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('color') border-red-500 @enderror"
                                   required>
                            <input type="text" id="color-hex" value="{{ old('color', $publicHoliday->color) }}" 
                                   class="flex-1 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="#3b82f6" readonly>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">This color will be used to highlight the holiday in calendars and displays.</p>
                        @error('color')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Additional Options -->
            <div>
                <h3 class="text-base sm:text-lg font-medium text-gray-900 mb-3 sm:mb-4 flex items-center space-x-2 sm:space-x-3">
                    <div class="w-6 h-6 sm:w-8 sm:h-8 bg-purple-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-cogs text-purple-600 text-sm sm:text-base"></i>
                    </div>
                    <span>Additional Options</span>
                </h3>
                <div class="space-y-3 sm:space-y-4">
                    <!-- National Holiday -->
                    <div class="flex items-center">
                        <input type="checkbox" id="is_national" name="is_national" value="1" 
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                               {{ old('is_national', $publicHoliday->is_national) ? 'checked' : '' }}>
                        <label for="is_national" class="ml-2 block text-sm text-gray-700">
                            National Holiday
                        </label>
                    </div>
                    <p class="text-xs text-gray-500 ml-6">Check if this is a national holiday that applies to all regions.</p>
                </div>
            </div>

            <!-- Audit Information -->
            <div>
                <h3 class="text-base sm:text-lg font-medium text-gray-900 mb-3 sm:mb-4 flex items-center space-x-2 sm:space-x-3">
                    <div class="w-6 h-6 sm:w-8 sm:h-8 bg-gray-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-history text-gray-600 text-sm sm:text-base"></i>
                    </div>
                    <span>Audit Information</span>
                </h3>
                <div class="bg-gray-50 rounded-md p-3 sm:p-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4 text-sm">
                        <div>
                            <span class="text-gray-600">Created by:</span>
                            <span class="font-medium">{{ $publicHoliday->creator->name ?? 'Unknown' }}</span>
                        </div>
                        <div>
                            <span class="text-gray-600">Created on:</span>
                            <span class="font-medium">{{ $publicHoliday->created_at->format('M d, Y g:i A') }}</span>
                        </div>
                        @if($publicHoliday->updated_at && $publicHoliday->updated_at != $publicHoliday->created_at)
                            <div>
                                <span class="text-gray-600">Last updated by:</span>
                                <span class="font-medium">{{ $publicHoliday->updater->name ?? 'Unknown' }}</span>
                            </div>
                            <div>
                                <span class="text-gray-600">Last updated on:</span>
                                <span class="font-medium">{{ $publicHoliday->updated_at->format('M d, Y g:i A') }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:py-4 flex flex-col sm:flex-row items-stretch sm:items-center justify-between space-y-3 sm:space-y-0">
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center space-y-2 sm:space-y-0 sm:space-x-3">
                <a href="{{ route('public-holidays.index') }}" 
                   class="inline-flex items-center justify-center px-3 py-2 sm:px-4 sm:py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <i class="fas fa-times mr-2"></i>
                    Cancel
                </a>
                <a href="{{ route('public-holidays.show', $publicHoliday) }}" 
                   class="inline-flex items-center justify-center px-3 py-2 sm:px-4 sm:py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <i class="fas fa-eye mr-2"></i>
                    View Holiday
                </a>
            </div>
            <button type="submit" 
                    class="inline-flex items-center justify-center px-4 py-2 sm:px-6 sm:py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <i class="fas fa-save mr-2"></i>
                Update Holiday
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
    // Color picker synchronization
    document.addEventListener('DOMContentLoaded', function() {
        const colorPicker = document.getElementById('color');
        const colorHex = document.getElementById('color-hex');
        
        colorPicker.addEventListener('input', function() {
            colorHex.value = this.value;
        });
        
        colorHex.addEventListener('input', function() {
            const hexValue = this.value;
            if (/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/.test(hexValue)) {
                colorPicker.value = hexValue;
            }
        });
        
        // Initialize department selection
        initializeDepartmentSelector();
    });
    
    function initializeDepartmentSelector() {
        const searchInput = document.getElementById('departmentSearch');
        const selectAllBtn = document.getElementById('selectAllAvailable');
        const clearAllBtn = document.getElementById('clearAllSelections');
        const departmentItems = document.querySelectorAll('.department-item');
        const checkboxes = document.querySelectorAll('.dept-checkbox');
        let currentFilter = 'all';
        
        // Search functionality
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase().trim();
            filterDepartments(searchTerm, currentFilter);
        });
        
        // Select all available departments
        selectAllBtn.addEventListener('click', function() {
            const availableCheckboxes = document.querySelectorAll('.dept-checkbox:not(:disabled)');
            const visibleCheckboxes = Array.from(availableCheckboxes).filter(cb => 
                !cb.closest('.department-item').classList.contains('hidden')
            );
            
            visibleCheckboxes.forEach(checkbox => {
                checkbox.checked = true;
                checkbox.closest('.department-item').classList.add('selected');
            });
            
            updateCounters();
        });
        
        // Clear all selections
        clearAllBtn.addEventListener('click', function() {
            checkboxes.forEach(checkbox => {
                checkbox.checked = false;
                checkbox.closest('.department-item').classList.remove('selected');
            });
            updateCounters();
        });
        
        // Checkbox change handlers
        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const departmentItem = this.closest('.department-item');
                if (this.checked) {
                    departmentItem.classList.add('selected');
                } else {
                    departmentItem.classList.remove('selected');
                }
                updateCounters();
            });
        });
        
        // Initialize counters
        updateCounters();
    }
    
    function filterDepartments(searchTerm, filter = 'all') {
        const departmentItems = document.querySelectorAll('.department-item');
        const noResultsMessage = document.getElementById('noResultsMessage');
        let visibleCount = 0;
        
        departmentItems.forEach(item => {
            const name = item.dataset.departmentName;
            const code = item.dataset.departmentCode;
            const isAvailable = item.dataset.available === 'true';
            const checkbox = item.querySelector('.dept-checkbox');
            const isSelected = checkbox.checked;
            
            let matchesSearch = !searchTerm || name.includes(searchTerm) || code.includes(searchTerm);
            let matchesFilter = true;
            
            switch (filter) {
                case 'available':
                    matchesFilter = isAvailable;
                    break;
                case 'selected':
                    matchesFilter = isSelected;
                    break;
                case 'all':
                default:
                    matchesFilter = true;
                    break;
            }
            
            if (matchesSearch && matchesFilter) {
                item.classList.remove('hidden');
                visibleCount++;
                
                // Highlight search terms
                if (searchTerm) {
                    highlightSearchTerm(item, searchTerm);
                } else {
                    removeHighlights(item);
                }
            } else {
                item.classList.add('hidden');
                removeHighlights(item);
            }
        });
        
        // Show/hide no results message
        if (visibleCount === 0) {
            noResultsMessage.classList.remove('hidden');
        } else {
            noResultsMessage.classList.add('hidden');
        }
    }
    
    function highlightSearchTerm(item, searchTerm) {
        const nameElement = item.querySelector('.department-name');
        const codeElement = item.querySelector('.department-code');
        
        [nameElement, codeElement].forEach(element => {
            if (element) {
                const text = element.textContent;
                const regex = new RegExp(`(${searchTerm})`, 'gi');
                element.innerHTML = text.replace(regex, '<span class="search-highlight">$1</span>');
            }
        });
    }
    
    function removeHighlights(item) {
        const highlightedElements = item.querySelectorAll('.search-highlight');
        highlightedElements.forEach(element => {
            element.outerHTML = element.textContent;
        });
    }
    
    function updateCounters() {
        const selectedCheckboxes = document.querySelectorAll('.dept-checkbox:checked:not(:disabled)');
        const totalEmployees = Array.from(selectedCheckboxes).reduce((sum, checkbox) => {
            return sum + parseInt(checkbox.dataset.employees || 0);
        }, 0);
        
        document.getElementById('selectedCount').textContent = selectedCheckboxes.length;
        document.getElementById('totalEmployees').textContent = totalEmployees.toLocaleString();
    }
</script>
@endpush
@endsection
