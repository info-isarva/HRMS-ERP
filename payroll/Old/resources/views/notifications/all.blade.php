@extends('layouts.master')

@section('title', 'All Notifications')

@section('style')
<style>
    /* Page Header Card */
    .page-header-card {
        background: white;
        border-radius: 1rem;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
        overflow: hidden;
        margin-bottom: 2rem;
    }

    .page-header-gradient {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 2rem 1.5rem;
        position: relative;
        color: white;
    }

    .page-header-pattern {
        position: absolute;
        inset: 0;
        background: rgba(0, 0, 0, 0.04);
    }

    .page-header-circle-1,
    .page-header-circle-2 {
        position: absolute;
        background: rgba(255, 255, 255, 0.08);
        border-radius: 50%;
    }
    .page-header-circle-1 { top: -1rem; right: -1rem; width: 6rem; height: 6rem; }
    .page-header-circle-2 { bottom: -1rem; left: -1rem; width: 8rem; height: 8rem; }

    .page-header-icon-box {
        width: 4rem;
        height: 4rem;
        background: rgba(255, 255, 255, 0.16);
        border: 1px solid rgba(255, 255, 255, 0.22);
        border-radius: 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .page-header-title {
        font-size: 1.75rem;
        font-weight: 700;
        color: white;
        margin-bottom: 0.25rem;
    }
    .page-header-subtitle { color: rgba(255,255,255,0.9); margin: 0; }

    /* Modern Settings Card */
    .settings-card {
        background: white;
        border-radius: 1rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
        overflow: visible;
        border: 1px solid #e5e7eb;
        margin-bottom: 1.5rem;
    }

    .settings-card .card-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        border-radius: 1rem 1rem 0 0 !important;
        padding: 1.5rem;
    }

    .settings-card .card-header h5 {
        font-weight: 600;
        margin: 0;
        display: flex;
        align-items: center;
        font-size: 1.1rem;
    }

    .settings-card .card-header i {
        margin-right: 0.5rem;
        opacity: 0.9;
    }

    .settings-card .card-body {
        padding: 2rem;
    }

    /* Button Styling */
    .btn {
        border-radius: 0.5rem;
        padding: 0.75rem 2rem;
        font-weight: 500;
        transition: all 0.2s ease;
        border: none;
        font-size: 0.875rem;
    }

    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        background: linear-gradient(135deg, #5a67d8 0%, #6b46c1 100%);
    }

    .btn-outline-secondary {
        border: 1px solid #d1d5db;
        color: #6b7280;
    }

    .btn-outline-secondary:hover {
        background: #f9fafb;
        border-color: #9ca3af;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .page-header-gradient {
            padding: 1.5rem 1rem;
        }

        .settings-card .card-body {
            padding: 1.5rem;
        }

        .btn {
            width: 100%;
            margin-bottom: 0.5rem;
        }
    }
</style>
@endsection

@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <!-- Modern Page Header -->
                <div class="page-header-card">
                    <div class="page-header-gradient">
                        <div class="page-header-pattern"></div>
                        <div class="page-header-circle-1"></div>
                        <div class="page-header-circle-2"></div>
                        <div class="d-flex align-items-center">
                            <div class="page-header-icon-box">
                                <i class="fas fa-bell fa-lg"></i>
                            </div>
                            <div class="ms-3">
                                <h1 class="page-header-title">All Notifications</h1>
                                <p class="page-header-subtitle">View all your notifications in one place</p>
                            </div>
                        </div>
                    </div>
                    <div class="p-3 d-flex justify-content-between align-items-center">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                                <li class="breadcrumb-item active">Notifications</li>
                            </ol>
                        </nav>
                        <div>
                            @if(count($notifications) > 0)
                                <button type="button" class="btn btn-primary btn-sm" id="mark-all-read-btn">
                                    <i class="fas fa-check"></i> Mark All as Read
                                </button>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="settings-card">
                    <div class="card-header">
                        <h5><i class="fas fa-list me-2"></i>Notifications</h5>
                    </div>
                    <div class="card-body p-0">
                        @if(count($notifications) > 0)
                            <div class="notification-list-container">
                                @foreach($notifications as $notification)
                                    @php
                                        $detailUrl = null;
                                        if ($notification['type'] === 'manual') {
                                            $detailUrl = route('notifications.show', $notification['id']);
                                        } elseif (isset($notification['action_url']) && $notification['action_url']) {
                                            $detailUrl = $notification['action_url'];
                                        }
                                    @endphp
                                    <a href="{{ $detailUrl ?? '#' }}" class="notification-item-link">
                                        <div class="notification-item-full {{ !$notification['is_read'] ? 'unread' : '' }}" data-id="{{ $notification['id'] }}" data-url="{{ $detailUrl ?? '#' }}">
                                            <div class="notification-content-wrapper">
                                                <div class="notification-icon-wrapper">
                                                    @if(isset($notification['profile_image']) && $notification['profile_image'])
                                                        @php
                                                            $imageUrl = strpos($notification['profile_image'], 'assets/') !== false 
                                                                ? url($notification['profile_image'])
                                                                : url('/assets/employee_profile_image/' . $notification['profile_image']);
                                                        @endphp
                                                        <img src="{{ $imageUrl }}" class="notification-avatar-full" alt="">
                                                    @else
                                                        <div class="notification-icon-full bg-{{ $notification['color'] }}">
                                                            <i class="fas {{ $notification['icon'] }}"></i>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="notification-content-full">
                                                    <div class="notification-header-full">
                                                        <h6 class="notification-title-full">{{ $notification['title'] }}</h6>
                                                        <span class="notification-time-full">
                                                            {{ \Carbon\Carbon::parse($notification['created_at'])->diffForHumans() }}
                                                        </span>
                                                    </div>
                                                    <p class="notification-message-full">{{ $notification['message'] }}</p>
                                                    @if(isset($notification['employee_name']) || isset($notification['department']))
                                                        <div class="notification-meta">
                                                            @if(isset($notification['employee_name']))
                                                                <span class="badge bg-light text-dark">{{ $notification['employee_name'] }}</span>
                                                            @endif
                                                            @if(isset($notification['employee_id']))
                                                                <span class="badge bg-light text-dark">ID: {{ $notification['employee_id'] }}</span>
                                                            @endif
                                                            @if(isset($notification['department']))
                                                                <span class="badge bg-light text-dark">{{ $notification['department'] }}</span>
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
                            <div class="text-center py-5">
                                <i class="fas fa-bell-slash fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No Notifications</h5>
                                <p class="text-muted">You don't have any notifications at the moment.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.notification-list-container {
    max-height: none;
}

.notification-item-link {
    text-decoration: none;
    color: inherit;
    display: block;
    transition: all 0.2s ease;
}

.notification-item-link:hover .notification-item-full {
    background: #f8f9fa;
}

.notification-item-link:hover {
    text-decoration: none;
    color: inherit;
}

.notification-item-full {
    padding: 1.5rem;
    border-bottom: 1px solid #f0f0f0;
    cursor: pointer;
    transition: all 0.2s ease;
}

.notification-item-full:hover {
    background: #f8f9fa;
}

.notification-item-full.unread {
    background: #f0f7ff;
    border-left: 4px solid #007bff;
}

.notification-item-full.unread:hover {
    background: #e3f2ff;
}

.notification-item-full:last-child {
    border-bottom: none;
}

.notification-content-wrapper {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
}

.notification-icon-wrapper {
    flex-shrink: 0;
}

.notification-avatar-full {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid white;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.notification-icon-full {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    color: white;
}

.notification-icon-full.bg-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.notification-icon-full.bg-success {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
}

.notification-icon-full.bg-warning {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
}

.notification-icon-full.bg-info {
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
}

.notification-icon-full.bg-danger {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
}

.notification-content-full {
    flex: 1;
    min-width: 0;
}

.notification-header-full {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.5rem;
}

.notification-title-full {
    font-weight: 600;
    color: #1f2937;
    margin: 0;
    font-size: 1rem;
}

.notification-time-full {
    font-size: 0.875rem;
    color: #9ca3af;
    white-space: nowrap;
}

.notification-message-full {
    color: #6b7280;
    margin-bottom: 0.5rem;
    line-height: 1.5;
}

.notification-meta {
    margin-top: 0.75rem;
}

.notification-meta .badge {
    margin-right: 0.5rem;
    margin-bottom: 0.25rem;
}

@media (max-width: 768px) {
    .notification-header-full {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.25rem;
    }
    
    .notification-time-full {
        white-space: normal;
    }
}
</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // Mark all as read functionality
    $('#mark-all-read-btn').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        $.ajax({
            url: '{{ route("notifications.mark-all-read") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    // Mark all items visually as read
                    $('.notification-item-full.unread').removeClass('unread');
                    
                    // Hide the mark all as read button
                    $('#mark-all-read-btn').fadeOut();
                    
                    // Show success message
                    toastr.success('All notifications marked as read');
                }
            },
            error: function(xhr) {
                console.error('Error marking notifications as read:', xhr);
                toastr.error('Failed to mark notifications as read');
            }
        });
    });
    
    // Individual notification click handler
    $('.notification-item-full').on('click', function(e) {
        // Prevent navigation if clicking on the link itself
        if (e.target.tagName === 'A' || e.target.closest('a')) {
            return;
        }
        
        e.preventDefault();
        e.stopPropagation();
        
        var notificationId = $(this).data('id');
        var notificationUrl = $(this).data('url');
        var $item = $(this);
        
        // Mark as read if unread
        if ($item.hasClass('unread')) {
            $.ajax({
                url: '{{ route("notifications.mark-read") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    notification_id: notificationId
                },
                success: function() {
                    $item.removeClass('unread');
                    
                    // Check if there are any unread notifications left
                    if ($('.notification-item-full.unread').length === 0) {
                        $('#mark-all-read-btn').fadeOut();
                    }
                    
                    // Navigate to detail view if URL exists
                    if (notificationUrl && notificationUrl !== '#') {
                        window.location.href = notificationUrl;
                    }
                },
                error: function() {
                    // Navigate anyway even if marking as read fails
                    if (notificationUrl && notificationUrl !== '#') {
                        window.location.href = notificationUrl;
                    }
                }
            });
        } else {
            // Navigate to detail view if already read
            if (notificationUrl && notificationUrl !== '#') {
                window.location.href = notificationUrl;
            }
        }
    });
    
    // Handle link clicks normally
    $('.notification-item-link').on('click', function(e) {
        // If clicking on action buttons inside, let default behavior happen
        if (e.target.tagName === 'A' || e.target.closest('a')) {
            return true;
        }
        
        // Otherwise, handle our custom logic
        e.preventDefault();
        $(this).find('.notification-item-full').trigger('click');
    });
});
</script>
@endsection