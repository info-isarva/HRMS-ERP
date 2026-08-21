@extends('layouts.app')

@section('page-title', 'All Notifications')

@section('content')
<div class="p-6">
    <!-- Header -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6 overflow-hidden">
        <div class="bg-gradient-to-r from-blue-600 to-purple-600 px-6 py-8 text-white">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="bg-white/20 p-4 rounded-xl">
                        <i class="fas fa-bell text-3xl"></i>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold">All Notifications</h1>
                        <p class="text-blue-100 mt-1">View all your notifications in one place</p>
                    </div>
                </div>
                @if(count($notifications) > 0)
                    <button type="button" class="bg-white/20 hover:bg-white/30 text-white px-6 py-2 rounded-lg transition-colors flex items-center space-x-2" id="mark-all-read-btn">
                        <i class="fas fa-check"></i>
                        <span>Mark All as Read</span>
                    </button>
                @endif
            </div>
        </div>
        
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
            <nav class="flex items-center text-sm text-gray-600">
                <a href="{{ route('dashboard') }}" class="hover:text-blue-600 transition-colors">Dashboard</a>
                <i class="fas fa-chevron-right mx-2 text-xs"></i>
                <span class="text-gray-900 font-medium">Notifications</span>
            </nav>
        </div>
    </div>

    <!-- Notifications List -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        @if(count($notifications) > 0)
            <div class="divide-y divide-gray-200">
                @foreach($notifications as $notification)
                    @php
                        $detailUrl = null;
                        if ($notification['type'] === 'manual') {
                            $detailUrl = route('notifications.show', $notification['id']);
                        } elseif (isset($notification['action_url']) && $notification['action_url']) {
                            $detailUrl = $notification['action_url'];
                        }
                    @endphp
                    <a href="{{ $detailUrl ?? '#' }}" class="block hover:bg-gray-50 transition-colors notification-item-link">
                        <div class="p-6 notification-item {{ !$notification['is_read'] ? 'bg-blue-50 border-l-4 border-blue-500' : '' }}" data-id="{{ $notification['id'] }}" data-url="{{ $detailUrl ?? '#' }}">
                            <div class="flex items-start space-x-4">
                                <!-- Icon/Avatar -->
                                <div class="flex-shrink-0">
                                    @if(isset($notification['profile_image']) && $notification['profile_image'])
                                        @php
                                            $imageUrl = strpos($notification['profile_image'], 'assets/') !== false 
                                                ? url($notification['profile_image'])
                                                : url('/assets/employee_profile_image/' . $notification['profile_image']);
                                        @endphp
                                        <img src="{{ $imageUrl }}" class="w-14 h-14 rounded-full object-cover border-2 border-white shadow-md" alt="">
                                    @else
                                        <div class="w-14 h-14 rounded-full flex items-center justify-center text-white text-xl shadow-md bg-gradient-to-br {{ $notification['color'] === 'primary' ? 'from-blue-500 to-purple-600' : ($notification['color'] === 'success' ? 'from-green-500 to-emerald-600' : ($notification['color'] === 'warning' ? 'from-yellow-500 to-orange-600' : ($notification['color'] === 'info' ? 'from-cyan-500 to-blue-600' : 'from-red-500 to-pink-600'))) }}">
                                            <i class="fas {{ $notification['icon'] }}"></i>
                                        </div>
                                    @endif
                                </div>
                                
                                <!-- Content -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between mb-2">
                                        <h3 class="text-lg font-semibold text-gray-900">{{ $notification['title'] }}</h3>
                                        <span class="text-sm text-gray-500 whitespace-nowrap ml-4">
                                            {{ \Carbon\Carbon::parse($notification['created_at'])->diffForHumans() }}
                                        </span>
                                    </div>
                                    <p class="text-gray-700 mb-3">{{ $notification['message'] }}</p>
                                    
                                    @if(isset($notification['employee_name']) || isset($notification['department']))
                                        <div class="flex flex-wrap gap-2">
                                            @if(isset($notification['employee_name']))
                                                <span class="px-3 py-1 bg-gray-100 text-gray-700 text-sm rounded-full">{{ $notification['employee_name'] }}</span>
                                            @endif
                                            @if(isset($notification['employee_id']))
                                                <span class="px-3 py-1 bg-gray-100 text-gray-700 text-sm rounded-full">ID: {{ $notification['employee_id'] }}</span>
                                            @endif
                                            @if(isset($notification['department']))
                                                <span class="px-3 py-1 bg-gray-100 text-gray-700 text-sm rounded-full">{{ $notification['department'] }}</span>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @else
            <div class="text-center py-16">
                <div class="inline-flex items-center justify-center w-24 h-24 bg-gray-100 rounded-full mb-4">
                    <i class="fas fa-bell-slash text-4xl text-gray-400"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">No Notifications</h3>
                <p class="text-gray-600">You don't have any notifications at the moment.</p>
            </div>
        @endif
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // Mark all as read functionality
    $('#mark-all-read-btn').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const $btn = $(this);
        $btn.prop('disabled', true);
        const originalHtml = $btn.html();
        $btn.html('<i class="fas fa-spinner fa-spin"></i><span class="ml-2">Marking...</span>');
        
        $.ajax({
            url: '{{ route("notifications.mark-all-read") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    $('.notification-item').removeClass('bg-blue-50 border-l-4 border-blue-500');
                    $btn.fadeOut();
                    
                    // Show success message if available
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: 'All notifications marked as read',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    }
                }
            },
            error: function(xhr) {
                console.error('Error marking notifications as read:', xhr);
                $btn.prop('disabled', false);
                $btn.html(originalHtml);
            }
        });
    });
    
    // Individual notification click handler
    $('.notification-item').on('click', function(e) {
        if (e.target.tagName === 'A' || e.target.closest('a')) {
            return;
        }
        
        e.preventDefault();
        e.stopPropagation();
        
        const notificationId = $(this).data('id');
        const notificationUrl = $(this).data('url');
        const $item = $(this);
        
        if ($item.hasClass('bg-blue-50')) {
            $.ajax({
                url: '{{ route("notifications.mark-read") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    notification_id: notificationId
                },
                success: function() {
                    $item.removeClass('bg-blue-50 border-l-4 border-blue-500');
                    
                    if (notificationUrl && notificationUrl !== '#') {
                        window.location.href = notificationUrl;
                    }
                },
                error: function() {
                    if (notificationUrl && notificationUrl !== '#') {
                        window.location.href = notificationUrl;
                    }
                }
            });
        } else {
            if (notificationUrl && notificationUrl !== '#') {
                window.location.href = notificationUrl;
            }
        }
    });
});
</script>
@endsection
