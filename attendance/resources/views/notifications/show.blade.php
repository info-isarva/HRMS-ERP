@extends('layouts.app')

@section('page-title', 'Notification Details')

@section('content')
<div class="p-6">
    <!-- Back Button -->
    <div class="mb-6">
        <a href="{{ route('notifications.all') }}" class="inline-flex items-center text-blue-600 hover:text-blue-700 transition-colors">
            <i class="fas fa-arrow-left mr-2"></i>
            <span>Back to All Notifications</span>
        </a>
    </div>

    <!-- Notification Detail -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <!-- Header -->
        <div class="bg-gradient-to-r from-blue-600 to-purple-600 px-8 py-10 text-white">
            <div class="flex items-start space-x-6">
                @if(isset($notification['profile_image']) && $notification['profile_image'])
                    @php
                        $imageUrl = strpos($notification['profile_image'], 'assets/') !== false 
                            ? url($notification['profile_image'])
                            : url('/assets/employee_profile_image/' . $notification['profile_image']);
                    @endphp
                    <img src="{{ $imageUrl }}" class="w-20 h-20 rounded-full object-cover border-4 border-white shadow-lg" alt="">
                @else
                    <div class="w-20 h-20 rounded-full flex items-center justify-center text-white text-2xl shadow-lg bg-white/20 border-4 border-white">
                        <i class="fas {{ $notification['icon'] ?? 'fa-bell' }}"></i>
                    </div>
                @endif
                
                <div class="flex-1">
                    <div class="flex items-start justify-between">
                        <div>
                            <h1 class="text-3xl font-bold mb-2">{{ $notification['title'] ?? 'Notification' }}</h1>
                            <p class="text-blue-100">
                                <i class="fas fa-clock mr-2"></i>
                                {{ isset($notification['created_at']) ? \Carbon\Carbon::parse($notification['created_at'])->format('F j, Y \a\t g:i A') : '' }}
                            </p>
                        </div>
                        <span class="px-4 py-2 bg-white/20 rounded-lg text-sm font-medium">
                            {{ ucfirst($notification['priority'] ?? 'medium') }} Priority
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div class="p-8">
            <!-- Message -->
            <div class="mb-8">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Message</h2>
                <div class="bg-gray-50 rounded-lg p-6 text-gray-700 leading-relaxed">
                    {{ $notification['message'] ?? 'No message content' }}
                </div>
            </div>

            <!-- Meta Information -->
            @if(isset($notification['employee_name']) || isset($notification['department']) || isset($notification['employee_id']))
                <div class="mb-8">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Details</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        @if(isset($notification['employee_name']))
                            <div class="bg-gray-50 rounded-lg p-4">
                                <div class="text-sm text-gray-600 mb-1">Employee Name</div>
                                <div class="font-semibold text-gray-900">{{ $notification['employee_name'] }}</div>
                            </div>
                        @endif
                        
                        @if(isset($notification['employee_id']))
                            <div class="bg-gray-50 rounded-lg p-4">
                                <div class="text-sm text-gray-600 mb-1">Employee ID</div>
                                <div class="font-semibold text-gray-900">{{ $notification['employee_id'] }}</div>
                            </div>
                        @endif
                        
                        @if(isset($notification['department']))
                            <div class="bg-gray-50 rounded-lg p-4">
                                <div class="text-sm text-gray-600 mb-1">Department</div>
                                <div class="font-semibold text-gray-900">{{ $notification['department'] }}</div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Additional Content -->
            @if(isset($notification['content']) && $notification['content'])
                <div class="mb-8">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Additional Information</h2>
                    <div class="prose max-w-none">
                        {!! nl2br(e($notification['content'])) !!}
                    </div>
                </div>
            @endif

            <!-- Actions -->
            @if(isset($notification['action_url']) && $notification['action_url'])
                <div class="flex items-center space-x-4 pt-6 border-t border-gray-200">
                    <a href="{{ $notification['action_url'] }}" class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors shadow-sm">
                        <i class="fas fa-external-link-alt mr-2"></i>
                        View Related Item
                    </a>
                    <a href="{{ route('notifications.all') }}" class="inline-flex items-center px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors">
                        <i class="fas fa-list mr-2"></i>
                        All Notifications
                    </a>
                </div>
            @else
                <div class="flex items-center pt-6 border-t border-gray-200">
                    <a href="{{ route('notifications.all') }}" class="inline-flex items-center px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors">
                        <i class="fas fa-list mr-2"></i>
                        All Notifications
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
