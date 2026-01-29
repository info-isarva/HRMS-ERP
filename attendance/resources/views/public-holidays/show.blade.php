@extends('layouts.app')

@section('title', 'Public Holiday Details - HRMS')
@section('page-title', 'Public Holiday Details')

@section('content')
<div class="container mx-auto max-w-full px-4 py-4 sm:py-6">
    <!-- Header -->
    <div class="mb-6 sm:mb-8">
        <div class="flex flex-col sm:flex-row items-center justify-between bg-gradient-to-r from-indigo-600 to-blue-600 px-4 py-6 sm:px-8 sm:py-10 rounded-lg shadow-lg">
            <div class="flex items-center space-x-3 sm:space-x-4 mb-4 sm:mb-0">
                <div class="w-12 h-12 sm:w-16 sm:h-16 bg-gradient-to-br from-indigo-500 to-blue-500 rounded-full flex items-center justify-center shadow-lg">
                    <i class="fas fa-calendar-alt text-white text-xl sm:text-2xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-white">{{ $publicHoliday->name }}</h1>
                    <p class="text-blue-100 mt-1 sm:mt-2 text-sm sm:text-base">Holiday details for {{ $publicHoliday->date->format('F d, Y') }}</p>
                </div>
            </div>
            <div class="flex flex-col sm:flex-row items-center space-y-2 sm:space-y-0 sm:space-x-3">
                @can('update', $publicHoliday)
                    <a href="{{ route('public-holidays.edit', $publicHoliday) }}" 
                       class="inline-flex items-center px-3 py-2 sm:px-4 sm:py-2 border border-white/30 rounded-md shadow-sm text-sm font-medium text-white bg-white/20 hover:bg-white/30 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="fas fa-edit mr-2"></i>
                        Edit
                    </a>
                @endcan
                <a href="{{ route('public-holidays.index') }}" 
                   class="inline-flex items-center px-3 py-2 sm:px-4 sm:py-2 border border-white/30 rounded-md shadow-sm text-sm font-medium text-white bg-white/20 hover:bg-white/30 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Back to Holidays
                </a>
            </div>
        </div>
    </div>

    <!-- Holiday Information -->
    <div class="bg-white shadow-sm rounded-lg overflow-hidden mb-4 sm:mb-6">
        <div class="px-4 py-6 sm:px-6 sm:py-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 sm:gap-8">
                <!-- Left Column -->
                <div class="space-y-4 sm:space-y-6">
                    <!-- Basic Information -->
                    <div>
                        <h3 class="text-base sm:text-lg font-medium text-gray-900 mb-3 sm:mb-4 flex items-center space-x-2 sm:space-x-3">
                            <div class="w-6 h-6 sm:w-8 sm:h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-info-circle text-blue-600 text-sm sm:text-base"></i>
                            </div>
                            <span>Basic Information</span>
                        </h3>
                        <div class="space-y-3 sm:space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Holiday Name</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $publicHoliday->name }}</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Date</label>
                                <p class="mt-1 text-sm text-gray-900">
                                    {{ $publicHoliday->date->format('F d, Y') }}
                                    <span class="text-gray-500">({{ $publicHoliday->date->format('l') }})</span>
                                </p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Financial Year</label>
                                <p class="mt-1 text-sm text-gray-900">FY {{ $publicHoliday->financial_year }}</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Holiday Type</label>
                                <div class="mt-1">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $publicHoliday->type === 'fixed' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                                        {{ ucfirst($publicHoliday->type) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Description -->
                    @if($publicHoliday->description)
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Description</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $publicHoliday->description }}</p>
                        </div>
                    @endif
                </div>

                <!-- Right Column -->
                <div class="space-y-4 sm:space-y-6">
                    <!-- Status & Properties -->
                    <div>
                        <h3 class="text-base sm:text-lg font-medium text-gray-900 mb-3 sm:mb-4 flex items-center space-x-2 sm:space-x-3">
                            <div class="w-6 h-6 sm:w-8 sm:h-8 bg-green-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-info-circle text-green-600 text-sm sm:text-base"></i>
                            </div>
                            <span>Status & Properties</span>
                        </h3>
                        <div class="space-y-3 sm:space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Status</label>
                                <div class="mt-1">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $publicHoliday->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        <i class="fas fa-circle text-xs mr-1"></i>
                                        {{ ucfirst($publicHoliday->status) }}
                                    </span>
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700">National Holiday</label>
                                <p class="mt-1 text-sm text-gray-900">
                                    @if($publicHoliday->is_national)
                                        <span class="inline-flex items-center text-green-600">
                                            <i class="fas fa-check-circle mr-1"></i>
                                            Yes
                                        </span>
                                    @else
                                        <span class="inline-flex items-center text-gray-600">
                                            <i class="fas fa-times-circle mr-1"></i>
                                            No
                                        </span>
                                    @endif
                                </p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Color</label>
                                <div class="mt-1 flex items-center space-x-2">
                                    <div class="w-6 h-6 rounded-md border border-gray-300" style="background-color: {{ $publicHoliday->color }}"></div>
                                    <span class="text-sm text-gray-900">{{ $publicHoliday->color }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Time Information -->
                    <div>
                        <h3 class="text-base sm:text-lg font-medium text-gray-900 mb-3 sm:mb-4">Time Information</h3>
                        <div class="space-y-3 sm:space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Days Until Holiday</label>
                                <p class="mt-1 text-sm text-gray-900">
                                    @if($publicHoliday->date->isPast())
                                        <span class="text-gray-500">{{ $publicHoliday->date->diffForHumans() }}</span>
                                    @else
                                        <span class="text-blue-600 font-medium">{{ $publicHoliday->date->diffForHumans() }}</span>
                                    @endif
                                </p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Day of Week</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $publicHoliday->date->format('l') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Audit Information -->
    <div class="bg-white shadow-sm rounded-lg overflow-hidden mb-4 sm:mb-6">
        <div class="px-4 py-4 sm:px-6 sm:py-6">
            <h3 class="text-base sm:text-lg font-medium text-gray-900 mb-3 sm:mb-4 flex items-center space-x-2 sm:space-x-3">
                <div class="w-6 h-6 sm:w-8 sm:h-8 bg-gray-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-history text-gray-600 text-sm sm:text-base"></i>
                </div>
                <span>Audit Information</span>
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
                <div>
                    <h4 class="text-sm font-medium text-gray-700 mb-2">Creation Details</h4>
                    <div class="space-y-2 text-sm">
                        <div>
                            <span class="text-gray-600">Created by:</span>
                            <span class="font-medium">{{ $publicHoliday->creator->name ?? 'Unknown' }}</span>
                        </div>
                        <div>
                            <span class="text-gray-600">Created on:</span>
                            <span class="font-medium">{{ $publicHoliday->created_at->format('M d, Y g:i A') }}</span>
                        </div>
                    </div>
                </div>
                
                @if($publicHoliday->updated_at && $publicHoliday->updated_at != $publicHoliday->created_at)
                    <div>
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Last Update</h4>
                        <div class="space-y-2 text-sm">
                            <div>
                                <span class="text-gray-600">Updated by:</span>
                                <span class="font-medium">{{ $publicHoliday->updater->name ?? 'Unknown' }}</span>
                            </div>
                            <div>
                                <span class="text-gray-600">Updated on:</span>
                                <span class="font-medium">{{ $publicHoliday->updated_at->format('M d, Y g:i A') }}</span>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Actions -->
    @can('update', $publicHoliday)
        <div class="bg-white shadow-sm rounded-lg overflow-hidden">
            <div class="px-4 py-4 sm:px-6 sm:py-6">
                <h3 class="text-base sm:text-lg font-medium text-gray-900 mb-3 sm:mb-4 flex items-center space-x-2 sm:space-x-3">
                    <div class="w-6 h-6 sm:w-8 sm:h-8 bg-purple-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-cogs text-purple-600 text-sm sm:text-base"></i>
                    </div>
                    <span>Actions</span>
                </h3>
                <div class="flex flex-col sm:flex-row flex-wrap gap-2 sm:gap-3">
                    <a href="{{ route('public-holidays.edit', $publicHoliday) }}" 
                       class="inline-flex items-center justify-center px-3 py-2 sm:px-4 sm:py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="fas fa-edit mr-2"></i>
                        Edit Holiday
                    </a>
                    
                    <form method="POST" action="{{ route('public-holidays.toggle-status', $publicHoliday) }}" class="inline">
                        @csrf
                        @method('PATCH')
                        <button type="submit" 
                                class="inline-flex items-center justify-center px-3 py-2 sm:px-4 sm:py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            @if($publicHoliday->status === 'active')
                                <i class="fas fa-pause mr-2"></i>
                                Deactivate
                            @else
                                <i class="fas fa-play mr-2"></i>
                                Activate
                            @endif
                        </button>
                    </form>
                    
                    <form method="POST" action="{{ route('public-holidays.destroy', $publicHoliday) }}" 
                          onsubmit="return confirm('Are you sure you want to delete this holiday? This action cannot be undone.')" 
                          class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="inline-flex items-center justify-center px-3 py-2 sm:px-4 sm:py-2 border border-red-300 rounded-md shadow-sm text-sm font-medium text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                            <i class="fas fa-trash mr-2"></i>
                            Delete Holiday
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @endcan
</div>
@endsection
