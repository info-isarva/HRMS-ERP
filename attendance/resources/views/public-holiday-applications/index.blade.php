@extends('layouts.app')

@section('title', 'Apply Public Leave - HRMS')
@section('page-title', 'Apply Public Leave')

@section('content')
<div class="p-6 space-y-6">
        
        <!-- Flash Messages -->
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

        @if(session('info'))
            <div class="bg-blue-50 border-l-4 border-blue-400 p-4 rounded-r-md shadow-sm">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-info-circle text-blue-400 text-lg"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-blue-800">{{ session('info') }}</p>
                    </div>
                </div>
            </div>
        @endif

        @if(isset($error))
            <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded-r-md shadow-sm">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-circle text-red-400 text-lg"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-red-800">{{ $error }}</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Error Messages -->
        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 rounded-md p-4">
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

        <!-- Header Card - Mobile Optimized -->
        <div class="bg-white shadow-xl rounded-lg border border-gray-200 overflow-hidden mb-6">
            <div class="bg-gradient-to-r from-blue-600 to-purple-600 px-4 py-6 lg:px-8 lg:py-8">
                <div class="flex flex-col space-y-4 lg:flex-row lg:items-center lg:justify-between lg:space-y-0">
                    <!-- Left side -->
                    <div class="flex flex-col sm:flex-row items-center space-y-4 sm:space-y-0 sm:space-x-4 text-center lg:text-left">
                        <div class="w-12 h-12 lg:w-20 lg:h-20 bg-white rounded-xl flex items-center justify-center shadow-lg flex-shrink-0">
                            <i class="fas fa-calendar-check text-blue-600 text-xl lg:text-3xl"></i>
                        </div>
                        <div class="flex-1">
                            <h1 class="text-2xl lg:text-4xl font-bold text-white mb-2">Apply Public Leave</h1>
                            <p class="text-blue-100 text-sm lg:text-lg font-medium leading-relaxed">Apply for flexible public holidays assigned to your department</p>
                        </div>
                    </div>

                    <!-- Right side -->
                    <div class="flex items-center justify-center lg:justify-end space-x-4 flex-shrink-0">
                        <div class="text-center">
                            <p class="text-blue-200 text-xs lg:text-sm font-semibold uppercase tracking-wide mb-1">Department</p>
                            <p class="text-xl lg:text-3xl font-bold text-white">{{ $department ? $department->name : 'Not Assigned' }}</p>
                        </div>
                        <div class="w-12 h-12 lg:w-20 lg:h-20 bg-white rounded-xl flex items-center justify-center shadow-lg flex-shrink-0">
                            <i class="fas fa-building text-purple-600 text-xl lg:text-3xl"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards - Clean and Functional -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Fixed Holidays Card -->
            <div class="bg-white shadow-lg rounded-lg border border-gray-200 p-6 hover:shadow-xl transition-shadow duration-200">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center">
                            <i class="fas fa-calendar-alt text-white text-lg"></i>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-600">Fixed Holidays</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $fixedHolidays->count() }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Flexible Holidays Card -->
            <div class="bg-white shadow-lg rounded-lg border border-gray-200 p-6 hover:shadow-xl transition-shadow duration-200">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-green-500 rounded-lg flex items-center justify-center">
                            <i class="fas fa-calendar-plus text-white text-lg"></i>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-600">Flexible Holidays</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $flexibleHolidays->count() }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Applied Card -->
            <div class="bg-white shadow-lg rounded-lg border border-gray-200 p-6 hover:shadow-xl transition-shadow duration-200">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-orange-500 rounded-lg flex items-center justify-center">
                            <i class="fas fa-check-circle text-white text-lg"></i>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-600">Applied</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $userApplications->count() }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Remaining Card -->
            <div class="bg-white shadow-lg rounded-lg border border-gray-200 p-6 hover:shadow-xl transition-shadow duration-200">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-purple-500 rounded-lg flex items-center justify-center">
                            <i class="fas fa-hourglass-half text-white text-lg"></i>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-600">Remaining</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $remainingApplications }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Application History -->
        @if($userApplications->count() > 0)
        <div class="bg-white shadow-xl rounded-2xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-200 bg-gradient-to-r from-purple-50 to-pink-50">
                <h2 class="text-xl font-bold text-gray-900 flex items-center">
                    <i class="fas fa-check-circle text-green-600 mr-3"></i>
                    Your Applied Holidays
                </h2>
                <p class="text-gray-700 mt-2">
                    All flexible public holiday applications are automatically approved. You can change your selection for future holidays.
                </p>
            </div>

            <div class="p-6">
                <div class="space-y-4">                @foreach($userApplications as $application)
                    <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow duration-200 {{ $application->publicHoliday->date->isPast() ? 'bg-gray-50' : 'bg-white' }}">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <div class="flex items-center space-x-4 mb-2">
                                    <h4 class="font-semibold text-gray-900 text-base">{{ $application->publicHoliday->name }}</h4>
                                    <span class="bg-gray-100 text-gray-800 px-3 py-1 rounded-full text-sm font-medium">
                                        {{ $application->publicHoliday->formatted_date }}
                                    </span>
                                    <!-- Flexible public holidays are auto-approved -->
                                    <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-medium">
                                        <i class="fas fa-check mr-1"></i>Approved
                                    </span>
                                    @if($application->publicHoliday->date->isPast())
                                        <span class="bg-gray-100 text-gray-600 px-3 py-1 rounded-full text-xs font-medium">
                                            <i class="fas fa-history mr-1"></i>Past
                                        </span>
                                    @else
                                        <span class="bg-blue-100 text-blue-600 px-3 py-1 rounded-full text-xs font-medium">
                                            <i class="fas fa-clock mr-1"></i>Upcoming
                                        </span>
                                    @endif
                                </div>
                                    @if($application->reason)
                                        <p class="text-sm text-gray-600 mb-2">
                                            <strong class="text-gray-800">Reason:</strong> {{ $application->reason }}
                                        </p>
                                    @endif
                                    <p class="text-xs text-gray-500">
                                        Applied on: {{ $application->applied_at->format('M d, Y \a\t H:i A') }}
                                        @if($application->approved_at)
                                            | Approved on: {{ $application->approved_at->format('M d, Y \a\t H:i A') }}
                                        @endif
                                    </p>
                                </div>
                                
                                {{-- Allow changing selection only for future holidays --}}
                                @if($application->publicHoliday->date->isFuture())
                                    <form action="{{ route('public-holiday-applications.cancel', $application->id) }}" 
                                          method="POST" 
                                          class="ml-4"
                                          onsubmit="return confirm('Are you sure you want to change this holiday selection? You can select a different holiday after removing this one.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="bg-blue-100 text-blue-700 px-4 py-2 rounded-lg hover:bg-blue-200 transition-colors duration-200 text-sm font-medium">
                                            <i class="fas fa-edit mr-1"></i>Change
                                        </button>
                                    </form>
                                @else
                                    <div class="ml-4">
                                        <span class="bg-gray-100 text-gray-600 px-4 py-2 rounded-lg text-sm font-medium">
                                            <i class="fas fa-lock mr-1"></i>Past Holiday
                                        </span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <!-- Main Content: Flexible (Left) and Fixed (Right) Side by Side -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Left Side: Apply for Flexible Public Holidays -->
            <div class="lg:col-span-1">
                @if($remainingApplications > 0)
                    <div class="bg-white shadow-lg rounded-xl border border-gray-300 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-300 bg-green-50">
                            <h2 class="text-xl font-bold text-gray-900 flex items-center">
                                <i class="fas fa-plus-circle text-green-600 mr-3 text-xl"></i>
                                Apply for Flexible Public Holidays
                            </h2>
                            <p class="text-gray-800 mt-2 font-medium">
                                You can select up to <span class="font-bold text-green-700 text-lg">{{ $remainingApplications }}</span> more flexible public holiday(s).
                                <span class="block text-sm text-green-700 mt-1">Applications are automatically approved. You can change selections for future holidays.</span>
                            </p>
                        </div>

                        <form action="{{ route('public-holiday-applications.store') }}" method="POST" class="p-6">
                            @csrf
                            
                            @if($flexibleHolidays->whereNotIn('id', $appliedHolidayIds)->count() > 0)
                                <div class="space-y-6">
                                    <!-- Flexible Holidays Selection -->
                                    <div>
                                        <label class="block text-lg font-bold text-gray-900 mb-4">
                                            Select Flexible Public Holidays (0/{{ $remainingApplications }} selected)
                                        </label>
                                        <div class="space-y-4" id="flexible-holidays">
                                            @foreach($flexibleHolidays->whereNotIn('id', $appliedHolidayIds) as $holiday)
                                                <div class="holiday-card group relative border border-gray-200 rounded-xl cursor-pointer transition-all duration-300 bg-white hover:shadow-lg"
                                                     data-holiday-id="{{ $holiday->id }}">
                                                    
                                                    <!-- Subtle color accent line -->
                                                    <div class="absolute left-0 top-0 bottom-0 w-1 rounded-l-xl transition-all duration-300" 
                                                         style="background: {{ $holiday->color ?? '#10b981' }};"></div>
                                                    
                                                    <input type="checkbox" 
                                                           name="holiday_ids[]" 
                                                           value="{{ $holiday->id }}" 
                                                           class="holiday-checkbox sr-only"
                                                           id="holiday_{{ $holiday->id }}">
                                                    
                                                    <label for="holiday_{{ $holiday->id }}" class="cursor-pointer block p-6 pl-8">
                                                        <!-- Header with title and checkbox -->
                                                        <div class="flex items-center justify-between mb-3">
                                                            <div class="flex items-center space-x-3">
                                                                <!-- Minimal color dot -->
                                                                <div class="w-2 h-2 rounded-full transition-all duration-300" 
                                                                     style="background: {{ $holiday->color ?? '#10b981' }};"></div>
                                                                
                                                                <!-- Title -->
                                                                <h4 class="font-semibold text-lg text-gray-900">
                                                                    {{ $holiday->name }}
                                                                </h4>
                                                            </div>
                                                            
                                                            <!-- Clean checkbox -->
                                                            <div class="checkbox-indicator w-5 h-5 border-2 border-gray-300 rounded bg-white flex items-center justify-center transition-all duration-300">
                                                                <i class="fas fa-check text-white text-xs hidden check-icon"></i>
                                                            </div>
                                                        </div>
                                                        
                                                        <!-- Description -->
                                                        <p class="text-gray-600 mb-4 text-sm leading-relaxed">
                                                            {{ $holiday->description ?? 'Flexible public holiday - application required' }}
                                                        </p>
                                                        
                                                        <!-- Date and day info -->
                                                        <div class="flex items-center justify-between">
                                                            <div class="flex items-center space-x-3">
                                                                <!-- Clean date display -->
                                                                <span class="bg-gray-50 text-gray-700 px-3 py-1 rounded-lg text-sm font-medium border">
                                                                    {{ $holiday->formatted_date }}
                                                                </span>
                                                                
                                                                <!-- Day -->
                                                                <span class="text-gray-500 text-sm font-medium">
                                                                    {{ $holiday->day_of_week }}
                                                                </span>
                                                            </div>
                                                            
                                                            <!-- Selection status -->
                                                            <div class="selection-status opacity-0 transition-all duration-300">
                                                                <span class="text-xs font-medium text-green-600">✓ Selected</span>
                                                            </div>
                                                        </div>
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                    <!-- Reason -->
                                    <div>
                                        <label for="reason" class="block text-lg font-bold text-gray-900 mb-2">
                                            Reason (Optional)
                                        </label>
                                        <textarea name="reason" 
                                                  id="reason" 
                                                  rows="3" 
                                                  class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 text-gray-900 focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200 font-medium"
                                                  placeholder="Please provide a reason for your application..."></textarea>
                                    </div>

                                    <!-- Submit Button -->
                                    <div class="flex justify-end">
                                        <button type="submit" 
                                                id="submit-btn"
                                                disabled
                                                class="bg-green-600 text-white px-8 py-3 rounded-lg font-bold text-lg hover:bg-green-700 focus:ring-4 focus:ring-green-200 transition-all duration-200 disabled:bg-gray-400 disabled:cursor-not-allowed flex items-center shadow-lg">
                                            <i class="fas fa-check-circle mr-2"></i>
                                            Apply & Auto-Approve
                                        </button>
                                    </div>
                                </div>
                            @else
                                <div class="text-center py-8">
                                    <div class="w-16 h-16 bg-gray-200 rounded-full flex items-center justify-center mx-auto mb-4">
                                        <i class="fas fa-calendar-times text-gray-500 text-2xl"></i>
                                    </div>
                                    <h3 class="text-xl font-bold text-gray-900 mb-2">No Flexible Holidays Available</h3>
                                    <p class="text-gray-700 font-medium">You have already applied for all available flexible public holidays.</p>
                                </div>
                            @endif
                        </form>
                    </div>
                @else
                    <div class="bg-white shadow-lg rounded-xl border border-gray-300 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-300 bg-blue-50">
                            <h2 class="text-xl font-bold text-gray-900 flex items-center">
                                <i class="fas fa-check-circle text-blue-600 mr-3 text-xl"></i>
                                All Holidays Applied
                            </h2>
                        </div>
                        <div class="p-6 text-center">
                            <div class="w-16 h-16 bg-blue-200 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-calendar-check text-blue-600 text-2xl"></i>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900 mb-2">Maximum Applications Reached</h3>
                            <p class="text-gray-700 font-medium mb-4">You have applied for all your allowed flexible public holidays this year.</p>
                            @if($userApplications->filter(function($app) { return $app->publicHoliday->date->isFuture(); })->count() > 0)
                                <p class="text-sm text-blue-600">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    You can still change your selections for future holidays in the "Your Applied Holidays" section below.
                                </p>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            <!-- Right Side: Fixed Public Holidays (View Only) -->
            <div class="lg:col-span-1">
                <div class="bg-white shadow-lg rounded-xl border border-gray-300 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-300 bg-blue-50">
                        <h2 class="text-xl font-bold text-gray-900 flex items-center">
                            <i class="fas fa-calendar-alt text-blue-600 mr-3 text-xl"></i>
                            Fixed Public Holidays
                        </h2>
                        <p class="text-gray-800 mt-2 font-medium">
                            These are fixed public holidays assigned to your department. No application required.
                        </p>
                    </div>

                    @if($fixedHolidays->count() > 0)
                        <div class="p-6">
                            <div class="space-y-4" id="fixed-holidays">
                                @foreach($fixedHolidays as $holiday)
                                    <div class="holiday-card group relative border border-gray-200 rounded-xl transition-all duration-300 bg-white hover:shadow-lg"
                                         data-holiday-id="{{ $holiday->id }}">
                                        
                                        <!-- Subtle color accent line -->
                                        <div class="absolute left-0 top-0 bottom-0 w-1 rounded-l-xl transition-all duration-300" 
                                             style="background: {{ $holiday->color ?? '#3b82f6' }};"></div>
                                        
                                        <div class="p-6 pl-8">
                                            <!-- Header with title and fixed indicator -->
                                            <div class="flex items-center justify-between mb-3">
                                                <div class="flex items-center space-x-3">
                                                    <!-- Minimal color dot -->
                                                    <div class="w-2 h-2 rounded-full transition-all duration-300" 
                                                         style="background: {{ $holiday->color ?? '#3b82f6' }};"></div>
                                                    
                                                    <!-- Title -->
                                                    <h4 class="font-semibold text-lg text-gray-900">
                                                        {{ $holiday->name }}
                                                    </h4>
                                                </div>
                                                
                                                <!-- Fixed indicator -->
                                                <div class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-xs font-medium flex items-center">
                                                    <i class="fas fa-lock mr-1"></i>
                                                    Fixed
                                                </div>
                                            </div>
                                            
                                            <!-- Description -->
                                            <p class="text-gray-600 mb-4 text-sm leading-relaxed">
                                                {{ $holiday->description ?? 'Fixed public holiday - no application required' }}
                                            </p>
                                            
                                            <!-- Date and day info -->
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center space-x-3">
                                                    <!-- Clean date display -->
                                                    <span class="bg-gray-50 text-gray-700 px-3 py-1 rounded-lg text-sm font-medium border">
                                                        {{ $holiday->formatted_date }}
                                                    </span>
                                                    
                                                    <!-- Day -->
                                                    <span class="text-gray-500 text-sm font-medium">
                                                        {{ $holiday->day_of_week }}
                                                    </span>
                                                </div>
                                                
                                                <!-- Auto-approved status -->
                                                <div class="selection-status">
                                                    <span class="text-xs font-medium text-green-600">✓ Auto-Approved</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="text-center py-12">
                            <div class="w-16 h-16 bg-gray-200 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-calendar text-gray-500 text-2xl"></i>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900 mb-2">No Fixed Holidays</h3>
                            <p class="text-gray-700 font-medium">No fixed public holidays are assigned to your department.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

    </div>

@endsection

@push('styles')
<style>
    /* Modern Holiday Card Styles */
    .holiday-card {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        transform-origin: center;
        position: relative;
        overflow: hidden;
    }
    
    .holiday-card:hover {
        transform: translateY(-4px) scale(1.02);
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    }
    
    .holiday-card.selected {
        transform: translateY(-4px) scale(1.02);
        box-shadow: 0 25px 50px -12px rgba(16, 185, 129, 0.4);
        border-color: #10b981 !important;
    }
    
    .holiday-card.selected .selection-status {
        opacity: 1 !important;
        transform: scale(1);
    }
    
    /* Enhanced Checkbox Styles */
    .checkbox-indicator {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }
    
    .holiday-card.selected .checkbox-indicator {
        background: linear-gradient(135deg, #10b981, #059669) !important;
        border-color: #10b981 !important;
        transform: scale(1.1);
        box-shadow: 0 8px 25px rgba(16, 185, 129, 0.4) !important;
    }
    
    .holiday-card.selected .check-icon {
        display: block !important;
        animation: checkScale 0.3s ease-out;
    }
    
    @keyframes checkScale {
        0% {
            transform: scale(0) rotate(0deg);
        }
        50% {
            transform: scale(1.3) rotate(180deg);
        }
        100% {
            transform: scale(1) rotate(360deg);
        }
    }
    
    /* Color indicator animations */
    .holiday-card:hover .w-2.h-2.rounded-full {
        box-shadow: 0 0 20px currentColor;
        transform: scale(1.2);
    }
    
    /* Selection status animations */
    .selection-status {
        opacity: 0;
        transform: scale(0.8);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    /* Enhanced button styles */
    #submit-btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none;
    }
    
    #submit-btn:not(:disabled):hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(16, 185, 129, 0.4);
    }
    
    /* Modern date and day cards */
    .holiday-card .px-3.py-1.rounded-lg,
    .holiday-card .px-3.py-2.rounded-lg {
        transition: all 0.2s ease;
    }
    
    .holiday-card:hover .px-3.py-1.rounded-lg {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }
    
    /* Fixed holiday cards enhancements */
    .group:hover .w-2.h-2.rounded-full {
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0%, 100% {
            opacity: 1;
        }
        50% {
            opacity: 0.8;
            transform: scale(1.1);
        }
    }
    
    /* Gradient overlays */
    .holiday-card .absolute.inset-0 {
        background: linear-gradient(45deg, rgba(255,255,255,0.1), rgba(255,255,255,0.05));
    }
    
    /* Responsive text improvements */
    .holiday-card h4 {
        line-height: 1.3;
        word-break: break-word;
    }
    
    /* Enhanced hover states for all cards */
    .group:hover {
        z-index: 10;
    }
    
    /* Color-coded borders with gradients */
    .holiday-card[style*="border-left"],
    .group[style*="border-left"] {
        border-left-width: 6px !important;
        border-radius: 16px !important;
    }
    
    /* Custom scrollbar for holiday sections */
    .space-y-4::-webkit-scrollbar {
        width: 6px;
    }
    
    .space-y-4::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 3px;
    }
    
    .space-y-4::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 3px;
    }
    
    .space-y-4::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const holidayCards = document.querySelectorAll('.holiday-card');
    const submitBtn = document.getElementById('submit-btn');
    const maxSelections = {{ $remainingApplications }};
    let selectedCount = 0;
    
    // Count initially selected checkboxes
    document.querySelectorAll('.holiday-checkbox:checked').forEach(checkbox => {
        selectedCount++;
        toggleCardSelection(checkbox.closest('.holiday-card'), true, false);
    });
    
    // Update button state on page load
    if (submitBtn) {
        submitBtn.disabled = selectedCount === 0;
    }

    holidayCards.forEach(card => {
        card.addEventListener('click', function(e) {
            // Prevent double triggering when clicking on label or checkbox
            if (e.target.tagName === 'LABEL' || e.target.tagName === 'INPUT' || e.target.closest('label')) {
                return;
            }
            
            const checkbox = this.querySelector('.holiday-checkbox');
            const isSelected = checkbox.checked;
            
            if (!isSelected && selectedCount >= maxSelections) {
                alert(`You can only select up to ${maxSelections} holidays.`);
                return;
            }
            
            checkbox.checked = !isSelected;
            toggleCardSelection(this, checkbox.checked, true);
        });
        
        // Handle label clicks
        const label = card.querySelector('label');
        if (label) {
            label.addEventListener('click', function(e) {
                e.preventDefault(); // Prevent default label behavior
                const holidayCard = this.closest('.holiday-card');
                const checkbox = holidayCard.querySelector('.holiday-checkbox');
                const isSelected = checkbox.checked;
                
                if (!isSelected && selectedCount >= maxSelections) {
                    alert(`You can only select up to ${maxSelections} holidays.`);
                    return;
                }
                
                checkbox.checked = !isSelected;
                toggleCardSelection(holidayCard, checkbox.checked, true);
            });
        }
        
        // Handle checkbox clicks directly
        const checkbox = card.querySelector('.holiday-checkbox');
        if (checkbox) {
            checkbox.addEventListener('change', function() {
                if (!this.checked && selectedCount >= maxSelections) {
                    alert(`You can only select up to ${maxSelections} holidays.`);
                    this.checked = false;
                    return;
                }
                
                toggleCardSelection(this.closest('.holiday-card'), this.checked, true);
            });
        }
    });
    
    function toggleCardSelection(card, isSelected, updateCounter = true) {
        if (isSelected) {
            card.classList.add('selected');
            card.querySelector('.checkbox-indicator').style.backgroundColor = '#10b981';
            card.querySelector('.check-icon').style.display = 'block';
            card.querySelector('.selection-status').style.opacity = '1';
            if (updateCounter) selectedCount++;
        } else {
            card.classList.remove('selected');
            card.querySelector('.checkbox-indicator').style.backgroundColor = '';
            card.querySelector('.check-icon').style.display = 'none';
            card.querySelector('.selection-status').style.opacity = '0';
            if (updateCounter) selectedCount--;
        }
        
        // Update submit button state
        if (submitBtn) {
            submitBtn.disabled = selectedCount === 0;
        }
        
        // Update remaining count display
        updateRemainingCount();
    }
    
    function updateRemainingCount() {
        // Update the instruction text to show selection count
        const instructionElement = document.querySelector('.px-6.py-4.border-b.border-gray-300.bg-green-50 p');
        if (instructionElement) {
            instructionElement.innerHTML = `You can select up to <span class="font-bold text-green-700 text-lg">${maxSelections}</span> flexible public holiday(s). <span class="block text-sm text-green-700 mt-1">Applications are automatically approved. You can change selections for future holidays.</span>`;
            
            if (selectedCount > 0) {
                instructionElement.innerHTML = `You have selected <span class="font-bold text-green-700 text-lg">${selectedCount}</span> of <span class="font-bold text-green-700 text-lg">${maxSelections}</span> allowed flexible public holiday(s). <span class="block text-sm text-green-700 mt-1">Applications are automatically approved. You can change selections for future holidays.</span>`;
            }
        }
        
        // Update the heading if present
        const selectionHeading = document.querySelector('label.block.text-lg.font-bold.text-gray-900.mb-4');
        if (selectionHeading) {
            selectionHeading.textContent = `Select Flexible Public Holidays (${selectedCount}/${maxSelections} selected)`;
        }
    }
    
    // Initialize the count display
    updateRemainingCount();
});
</script>
@endpush
