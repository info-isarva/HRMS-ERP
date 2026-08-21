@extends('layouts.app')

@section('page-title', 'Notification Details')

@section('content')
<div class="p-4 sm:p-6">
    <!-- Back Button -->
    <div class="mb-4 sm:mb-6">
        <a href="{{ route('notifications.all') }}" class="inline-flex items-center text-sm sm:text-base text-blue-600 hover:text-blue-700 transition-colors">
            <i class="fas fa-arrow-left mr-2"></i>
            <span>Back to All Notifications</span>
        </a>
    </div>

    <!-- Notification Detail -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <!-- Header -->
        <div class="bg-gradient-to-r from-blue-600 to-purple-600 px-4 py-6 sm:px-8 sm:py-10 text-white">
            <div class="flex flex-col sm:flex-row sm:items-start gap-4 sm:gap-6">
                @if(isset($notification['profile_image']) && $notification['profile_image'])
                    @php
                        $imageUrl = strpos($notification['profile_image'], 'assets/') !== false 
                            ? url($notification['profile_image'])
                            : url('/assets/employee_profile_image/' . $notification['profile_image']);
                    @endphp
                    <img src="{{ $imageUrl }}" class="w-14 h-14 sm:w-20 sm:h-20 rounded-full object-cover border-4 border-white shadow-lg flex-shrink-0" alt="">
                @else
                    <div class="w-14 h-14 sm:w-20 sm:h-20 rounded-full flex items-center justify-center text-white text-xl sm:text-2xl shadow-lg bg-white/20 border-4 border-white flex-shrink-0 leading-none">
                        <i class="fas {{ $notification['icon'] ?? 'fa-bell' }} leading-none"></i>
                    </div>
                @endif
                
                <div class="flex-1 min-w-0">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between sm:gap-4">
                        <div class="min-w-0">
                            <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold mb-2 break-words leading-snug">{{ $notification['title'] ?? 'Notification' }}</h1>
                            <p class="text-blue-100 text-sm sm:text-base">
                                <i class="fas fa-clock mr-2"></i>
                                {{ isset($notification['created_at']) ? \Carbon\Carbon::parse($notification['created_at'])->format('F j, Y \a\t g:i A') : '' }}
                            </p>
                        </div>
                        <span class="inline-flex self-start px-3 py-1.5 sm:px-4 sm:py-2 bg-white/20 rounded-lg text-xs sm:text-sm font-medium whitespace-nowrap">
                            {{ ucfirst($notification['priority'] ?? 'medium') }} Priority
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div class="p-4 sm:p-8">
            <!-- Message -->
            <div class="mb-6 sm:mb-8">
                <h2 class="text-lg sm:text-xl font-semibold text-gray-900 mb-3 sm:mb-4">Message</h2>
                <div class="bg-gray-50 rounded-lg p-4 sm:p-6 text-sm sm:text-base text-gray-700 leading-relaxed break-words">
                    {{ $notification['message'] ?? 'No message content' }}
                </div>
            </div>

            <!-- Meta Information -->
            @if(isset($notification['employee_name']) || isset($notification['department']) || isset($notification['employee_id']))
                <div class="mb-6 sm:mb-8">
                    <h2 class="text-lg sm:text-xl font-semibold text-gray-900 mb-3 sm:mb-4">Details</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 sm:gap-4">
                        @if(isset($notification['employee_name']))
                            <div class="bg-gray-50 rounded-lg p-4">
                                <div class="text-sm text-gray-600 mb-1">Employee Name</div>
                                <div class="font-semibold text-gray-900 break-words">{{ $notification['employee_name'] }}</div>
                            </div>
                        @endif
                        
                        @if(isset($notification['employee_id']))
                            <div class="bg-gray-50 rounded-lg p-4">
                                <div class="text-sm text-gray-600 mb-1">Employee ID</div>
                                <div class="font-semibold text-gray-900 break-words">{{ $notification['employee_id'] }}</div>
                            </div>
                        @endif
                        
                        @if(isset($notification['department']))
                            <div class="bg-gray-50 rounded-lg p-4">
                                <div class="text-sm text-gray-600 mb-1">Department</div>
                                <div class="font-semibold text-gray-900 break-words">{{ $notification['department'] }}</div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Additional Content -->
            @if(isset($notification['content']) && $notification['content'])
                <div class="mb-6 sm:mb-8">
                    <h2 class="text-lg sm:text-xl font-semibold text-gray-900 mb-3 sm:mb-4">Additional Information</h2>
                    <div class="prose max-w-none text-sm sm:text-base break-words">
                        {!! nl2br(e($notification['content'])) !!}
                    </div>
                </div>
            @endif

            <!-- Actions -->
            @if(isset($notification['action_url']) && $notification['action_url'])
                <div class="flex flex-col sm:flex-row sm:items-center gap-3 sm:gap-4 pt-4 sm:pt-6 border-t border-gray-200">
                    <a href="{{ $notification['action_url'] }}" class="inline-flex items-center justify-center px-5 py-3 sm:px-6 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors shadow-sm text-sm sm:text-base">
                        <i class="fas fa-external-link-alt mr-2"></i>
                        View Related Item
                    </a>
                    <a href="{{ route('notifications.all') }}" class="inline-flex items-center justify-center px-5 py-3 sm:px-6 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors text-sm sm:text-base">
                        <i class="fas fa-list mr-2"></i>
                        All Notifications
                    </a>
                </div>
            @else
                <div class="flex items-center pt-4 sm:pt-6 border-t border-gray-200">
                    <a href="{{ route('notifications.all') }}" class="inline-flex items-center justify-center w-full sm:w-auto px-5 py-3 sm:px-6 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors text-sm sm:text-base">
                        <i class="fas fa-list mr-2"></i>
                        All Notifications
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
