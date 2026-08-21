<header id="main-header" class="bg-white border-b border-gray-200 fixed top-0 z-50 main-header-wrapper" style="right: 0;">
    <div class="h-16 flex items-center justify-between px-6">
    <!-- Mobile menu button -->
    <button id="mobile-menu-toggle" onclick="toggleMobileSidebar()" class="lg:hidden p-2 rounded-lg text-gray-500 hover:text-gray-700 hover:bg-gray-100 transition-colors">
        <i id="mobile-menu-icon" class="fas fa-bars text-lg"></i>
    </button>
    
    <!-- Page Title -->
    <div class="flex items-center">
        <h2 class="text-lg sm:text-xl font-semibold text-gray-900">@yield('page-title', 'Dashboard')</h2>
        @if(isset($breadcrumbs))
            <nav class="ml-2 sm:ml-4 text-xs sm:text-sm text-gray-500">
                <ol class="flex items-center space-x-1 sm:space-x-2">
                    @foreach($breadcrumbs as $breadcrumb)
                        <li class="flex items-center">
                            @if(!$loop->first)
                                <i class="fas fa-chevron-right text-xs mr-1 sm:mr-2"></i>
                            @endif
                            @if($loop->last)
                                <span class="text-gray-900">{{ $breadcrumb['title'] }}</span>
                            @else
                                <a href="{{ $breadcrumb['url'] }}" class="hover:text-blue-600 transition-colors">{{ $breadcrumb['title'] }}</a>
                            @endif
                        </li>
                    @endforeach
                </ol>
            </nav>
        @endif
    </div>
    
    <!-- Header Actions -->
    <div class="flex items-center space-x-4">
        <!-- Search -->
        <!-- Live Attendance Status Widget -->
        <!-- Animated Current Time & Quick Links -->
        <div class="hidden lg:flex items-center space-x-3">
            <!-- Birthday Celebration - Compact -->
            @if(isset($birthdayEmployees) && $birthdayEmployees->count() > 0)
            <div class="flex items-center px-2 py-1 bg-gradient-to-r from-pink-100 to-orange-100 border border-pink-200 rounded-lg shadow-sm relative overflow-hidden" title="Happy Birthday {{ $birthdayEmployees->first()->name }}!">
                <i class="fas fa-birthday-cake text-pink-500 text-sm animate-bounce"></i>
                <span id="birthday-name" class="ml-1 text-xs bg-pink-200 text-pink-800 px-1.5 py-0.5 rounded-full animate-pulse">{{ $birthdayEmployees->first()->name }}</span>
                <!-- Compact firework particles -->
                <div class="absolute inset-0 pointer-events-none">
                    <div class="firework firework-1" style="width: 2px; height: 2px;"></div>
                    <div class="firework firework-2" style="width: 2px; height: 2px;"></div>
                </div>
            </div>
            @endif

            <!-- Compact Time Widget -->
            <div class="flex items-center px-2 py-1 rounded-lg bg-gradient-to-r from-blue-50 to-cyan-50 border border-blue-100 shadow-sm">
                <i class="fas fa-clock text-blue-500 text-sm mr-1"></i>
                <span id="live-clock" class="font-mono text-sm tracking-wide text-blue-700 font-semibold"></span>
                <span class="text-xs mx-1 text-blue-300">:</span>
                <span id="live-seconds" class="font-mono text-xs tracking-wide text-blue-500 font-medium"></span>
                <span class="text-gray-300 text-xs mx-1">|</span>
                <span id="live-date" class="font-medium text-emerald-600 text-xs"></span>
            </div>
            <style>
            @keyframes spin-slow { 100% { transform: rotate(360deg); } }
            .animate-spin-slow { animation: spin-slow 8s linear infinite; }
            
            /* Compact firework animations */
            @keyframes firework-explode {
                0% { opacity: 0; transform: scale(0); }
                50% { opacity: 1; transform: scale(1); }
                100% { opacity: 0; transform: scale(1.5); }
            }
            .firework {
                position: absolute;
                width: 3px;
                height: 3px;
                background: radial-gradient(circle, #ff6b6b, #ffd93d, #6bcf7f);
                border-radius: 50%;
                animation: firework-explode 2s infinite ease-out;
            }
            .firework-1 { top: 15%; left: 25%; animation-delay: 0s; }
            .firework-2 { top: 25%; right: 20%; animation-delay: 0.7s; }

            /* Custom Tooltip Animations */
            .group:hover .group-hover\:opacity-100 {
                animation: tooltip-fade-in-down 0.3s ease-out forwards;
            }
            @keyframes tooltip-fade-in-down {
                0% {
                    opacity: 0;
                    transform: translateX(-50%) translateY(-5px);
                }
                100% {
                    opacity: 1;
                    transform: translateX(-50%) translateY(0);
                }
            }
            </style>
            <!-- Quick Links - Icons with custom tooltips -->
            <div class="flex space-x-2">
                <div class="relative group">
                    <a href="{{ route('leaves.create') }}" class="flex items-center justify-center w-8 h-8 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 hover:shadow-lg hover:shadow-blue-200/50 transition-all duration-200 hover:scale-110 shadow-sm">
                        <i class="fas fa-plus-circle"></i>
                    </a>
                    <!-- Custom Tooltip -->
                    <div class="absolute top-full left-1/2 transform -translate-x-1/2 mt-2 px-3 py-2 bg-gray-900 text-white text-sm rounded-lg shadow-lg opacity-0 group-hover:opacity-100 transition-all duration-300 pointer-events-none whitespace-nowrap z-50 max-w-xs">
                        <div class="flex items-center space-x-2">
                            <i class="fas fa-plus-circle text-blue-400"></i>
                            <span>Apply Leave</span>
                        </div>
                        <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 border-4 border-transparent border-b-gray-900"></div>
                    </div>
                </div>

                <div class="relative group">
                    <a href="{{ route('leaves.index') }}" class="flex items-center justify-center w-8 h-8 bg-green-100 text-green-700 rounded-lg hover:bg-green-200 hover:shadow-lg hover:shadow-green-200/50 transition-all duration-200 hover:scale-110 shadow-sm">
                        <i class="fas fa-list-alt"></i>
                    </a>
                    <!-- Custom Tooltip -->
                    <div class="absolute top-full left-1/2 transform -translate-x-1/2 mt-2 px-3 py-2 bg-gray-900 text-white text-sm rounded-lg shadow-lg opacity-0 group-hover:opacity-100 transition-all duration-300 pointer-events-none whitespace-nowrap z-50 max-w-xs">
                        <div class="flex items-center space-x-2">
                            <i class="fas fa-list-alt text-green-400"></i>
                            <span>Leave Status</span>
                        </div>
                        <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 border-4 border-transparent border-b-gray-900"></div>
                    </div>
                </div>

                <div class="relative group">
                    <a href="{{ route('public-holiday-applications.index') }}" class="flex items-center justify-center w-8 h-8 bg-yellow-100 text-yellow-700 rounded-lg hover:bg-yellow-200 hover:shadow-lg hover:shadow-yellow-200/50 transition-all duration-200 hover:scale-110 shadow-sm">
                        <i class="fas fa-calendar-day"></i>
                    </a>
                    <!-- Custom Tooltip -->
                    <div class="absolute top-full left-1/2 transform -translate-x-1/2 mt-2 px-3 py-2 bg-gray-900 text-white text-sm rounded-lg shadow-lg opacity-0 group-hover:opacity-100 transition-all duration-300 pointer-events-none whitespace-nowrap z-50 max-w-xs">
                        <div class="flex items-center space-x-2">
                            <i class="fas fa-calendar-day text-yellow-400"></i>
                            <span>Public Holidays</span>
                        </div>
                        <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 border-4 border-transparent border-b-gray-900"></div>
                    </div>
                </div>
            </div>
        </div>
        <script>
        // Live clock and date - compact format
        function updateClock() {
            const now = new Date();
            const h = String(now.getHours()).padStart(2, '0');
            const m = String(now.getMinutes()).padStart(2, '0');
            const s = String(now.getSeconds()).padStart(2, '0');
            document.getElementById('live-clock').textContent = `${h}:${m}`;
            document.getElementById('live-seconds').textContent = s;
            // Compact date: e.g. 11 Oct 2025
            const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
            const dateStr = `${now.getDate()} ${months[now.getMonth()]} ${now.getFullYear()}`;
            document.getElementById('live-date').textContent = dateStr;
        }
        setInterval(updateClock, 1000);
        updateClock();

        // Birthday name cycling
        @if(isset($birthdayEmployees) && $birthdayEmployees->count() > 1)
        const birthdayNames = @json($birthdayEmployees->pluck('name'));
        let currentIndex = 0;
        const birthdayNameElement = document.getElementById('birthday-name');
        if (birthdayNameElement) {
            setInterval(() => {
                currentIndex = (currentIndex + 1) % birthdayNames.length;
                birthdayNameElement.textContent = birthdayNames[currentIndex];
            }, 3000); // Change every 3 seconds
        }
        @endif
        </script>
        
        <!-- Financial Year Switcher (Admin/Super Admin only) -->
        @if(auth()->user()->isAdmin() || auth()->user()->isSuperAdmin())
        @php
            $allFys = \App\Models\FinancialYear::orderBy('start_date', 'desc')->get();
            $sessionFyId = session('selected_financial_year_id');
        @endphp
        <div class="hidden sm:flex items-center">
            <form action="{{ route('financial-years.switch') }}" method="POST" class="flex items-center">
                @csrf
                <div class="relative group">
                    <label class="absolute -top-3.5 left-0 text-[10px] font-bold text-gray-400 uppercase tracking-tighter">Financial Year</label>
                    <select name="fy_id" onchange="this.form.submit()" class="bg-indigo-50 border border-indigo-100 text-indigo-700 text-xs rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-2.5 pr-8 py-1.5 appearance-none cursor-pointer hover:bg-white transition-all font-bold shadow-sm">
                        <option value="default" {{ !$sessionFyId ? 'selected' : '' }}>Default (Active)</option>
                        @foreach($allFys as $fy)
                            <option value="{{ $fy->id }}" {{ $sessionFyId == $fy->id ? 'selected' : '' }}>
                                {{ $fy->name }}
                            </option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-indigo-400">
                        <i class="fas fa-calendar-alt text-xs"></i>
                    </div>
                </div>
            </form>
        </div>
        @endif

        <!-- Notifications -->
        <div class="relative" x-data="{ open: false }">
            <button @click="open = !open" id="notificationDropdown" class="relative p-2 rounded-lg text-gray-500 hover:text-gray-700 hover:bg-gray-100 transition-colors">
                <i class="fas fa-bell text-lg"></i>
                <span id="notification-badge" class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center" style="display: none;"></span>
                <span id="notification-count" class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full min-w-5 h-5 flex items-center justify-center px-1" style="display: none;">0</span>
            </button>
            
            <!-- Notification Dropdown -->
            <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95" class="absolute right-0 mt-2 w-96 bg-white rounded-lg shadow-xl border border-gray-200 z-50" style="display: none;">
                <!-- Notification Header -->
                <div class="px-4 py-3 bg-gradient-to-r from-blue-600 to-purple-600 rounded-t-lg flex items-center justify-between">
                    <h3 class="text-white font-semibold">Notifications</h3>
                    <button id="mark-all-read" class="text-white text-sm hover:underline">Mark all as read</button>
                </div>
                
                <!-- Notification List -->
                <div id="notification-list" class="max-h-96 overflow-y-auto">
                    <div class="flex items-center justify-center py-8">
                        <div class="text-center">
                            <i class="fas fa-spinner fa-spin text-2xl text-gray-400 mb-2"></i>
                            <p class="text-gray-500 text-sm">Loading notifications...</p>
                        </div>
                    </div>
                </div>
                
                <!-- Footer -->
                <div class="px-4 py-3 bg-gray-50 rounded-b-lg border-t border-gray-200">
                    <a href="{{ route('notifications.all') }}" class="text-center block text-blue-600 hover:text-blue-700 text-sm font-medium">View All Notifications</a>
                </div>
            </div>
        </div>
        
        <!-- Profile Dropdown -->
        <div class="relative" x-data="{ open: false }">
            <button @click="open = !open" class="flex items-center p-2 rounded-lg hover:bg-gray-100 transition-colors">
                <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center">
                    <span class="text-white font-semibold text-sm">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</span>
                </div>
                <i class="fas fa-chevron-down ml-2 text-gray-400 text-sm"></i>
            </button>
            
            <!-- Dropdown Menu -->
            <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95" class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-50">
                <div class="px-4 py-2 border-b border-gray-200">
                    <p class="text-sm font-medium text-gray-900">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-gray-500">{{ auth()->user()->email }}</p>
                </div>
                
                <a href="{{ route('profile.show') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors">
                    <i class="fas fa-user-circle mr-3 text-gray-400"></i>
                    Profile
                </a>
                
                <!-- <a href="#" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors">
                    <i class="fas fa-cog mr-3 text-gray-400"></i>
                    Settings
                </a> -->
                
                <!-- <a href="#" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors">
                    <i class="fas fa-question-circle mr-3 text-gray-400"></i>
                    Help
                </a> -->
                
                <div class="border-t border-gray-200 my-1"></div>
                
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="flex items-center w-full px-4 py-2 text-sm text-red-700 hover:bg-red-50 transition-colors">
                        <i class="fas fa-sign-out-alt mr-3 text-red-500"></i>
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </div>
    </div>
    @include('components.demo-banner')
</header>

<!-- Notification Styles -->
<style>
.notification-item {
    padding: 1rem;
    border-bottom: 1px solid #e5e7eb;
    cursor: pointer;
    transition: background-color 0.2s;
}

.notification-item:hover {
    background-color: #f9fafb;
}

.notification-item.unread {
    background-color: #eff6ff;
    border-left: 3px solid #3b82f6;
}

.notification-item:last-child {
    border-bottom: none;
}

.notification-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-center;
    color: white;
    font-size: 1rem;
}

.notification-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid white;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.notification-empty {
    padding: 2rem;
    text-center;
    color: #9ca3af;
}

.notification-empty i {
    font-size: 2rem;
    margin-bottom: 0.5rem;
}
</style>

<!-- Notification JavaScript -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // Setup AJAX to include CSRF token from meta tag
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    
    // Fetch notifications on page load
    fetchNotifications();
    
    // Refresh notifications every 2 minutes
    setInterval(fetchNotifications, 120000);
    
    // Fetch notifications when dropdown is opened
    $('#notificationDropdown').on('click', function() {
        fetchNotifications();
    });
    
    // Mark all as read
    $('#mark-all-read').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const $btn = $(this);
        
        if ($btn.prop('disabled')) {
            return;
        }
        
        $btn.prop('disabled', true);
        const originalText = $btn.html();
        $btn.html('<i class="fas fa-spinner fa-spin"></i> Marking...');
        
        markAllAsRead($btn, originalText);
    });
    
    function fetchNotifications() {
        $.ajax({
            url: '{{ route("notifications.get") }}',
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    displayNotifications(response.notifications, response.unread_count);
                }
            },
            error: function(xhr) {
                console.error('Error fetching notifications:', xhr);
                $('#notification-list').html(
                    '<div class="notification-empty">' +
                    '<i class="fas fa-exclamation-triangle"></i>' +
                    '<p>Failed to load notifications</p>' +
                    '</div>'
                );
            }
        });
    }
    
    function displayNotifications(notifications, unreadCount) {
        const $notificationList = $('#notification-list');
        const $badge = $('#notification-badge');
        const $count = $('#notification-count');
        
        // Update badge
        if (unreadCount > 0) {
            $badge.hide();
            $count.text(unreadCount > 99 ? '99+' : unreadCount).show();
        } else {
            $badge.hide();
            $count.hide();
        }
        
        // Clear list
        $notificationList.empty();
        
        if (notifications.length === 0) {
            $notificationList.html(
                '<div class="notification-empty">' +
                '<i class="fas fa-bell-slash"></i>' +
                '<p>No notifications</p>' +
                '</div>'
            );
            return;
        }
        
        // Display notifications (max 5 in dropdown)
        notifications.slice(0, 5).forEach(function(notification) {
            const timeAgo = getTimeAgo(notification.created_at);
            const unreadClass = notification.is_read === false ? 'unread' : '';
            
            let iconHtml = '';
            if (notification.profile_image) {
                const imageUrl = notification.profile_image.includes('assets/') 
                    ? '{{ url("/") }}/' + notification.profile_image
                    : '{{ url("/assets/employee_profile_image/") }}/' + notification.profile_image;
                iconHtml = '<img src="' + imageUrl + '" class="notification-avatar" alt="">';
            } else {
                let bgClass = 'from-blue-500 to-purple-600';
                if (notification.color === 'success') bgClass = 'from-green-500 to-emerald-600';
                else if (notification.color === 'warning') bgClass = 'from-yellow-500 to-orange-600';
                else if (notification.color === 'info') bgClass = 'from-cyan-500 to-blue-600';
                else if (notification.color === 'danger') bgClass = 'from-red-500 to-pink-600';
                
                iconHtml = '<div class="notification-icon bg-gradient-to-br ' + bgClass + '">' +
                           '<i class="fas ' + notification.icon + '"></i>' +
                           '</div>';
            }
            
            const $item = $('<div>')
                .addClass('notification-item ' + unreadClass)
                .attr('data-id', notification.id)
                .html(
                    '<div class="flex items-start space-x-3">' +
                    iconHtml +
                    '<div class="flex-1 min-w-0">' +
                    '<div class="font-semibold text-gray-900 text-sm mb-1">' + notification.title + '</div>' +
                    '<div class="text-gray-600 text-sm mb-1 line-clamp-2">' + notification.message + '</div>' +
                    '<div class="text-gray-500 text-xs">' + timeAgo + '</div>' +
                    '</div>' +
                    '</div>'
                );
            
            // Add click handler
            $item.on('click', function() {
                markAsRead(notification.id);
                
                if (notification.type === 'manual') {
                    window.location.href = '{{ route("notifications.show", ":id") }}'.replace(':id', notification.id);
                } else if (notification.action_url) {
                    window.location.href = notification.action_url;
                }
            });
            
            $notificationList.append($item);
        });
    }
    
    function markAsRead(notificationId) {
        $.ajax({
            url: '{{ route("notifications.mark-read") }}',
            type: 'POST',
            data: {
                notification_id: notificationId
            },
            success: function(response) {
                if (response.success) {
                    fetchNotifications();
                }
            },
            error: function(xhr) {
                console.error('Error marking notification as read:', xhr);
            }
        });
    }
    
    function markAllAsRead($btn, originalText) {
        $.ajax({
            url: '{{ route("notifications.mark-all-read") }}',
            type: 'POST',
            data: {},
            success: function(response) {
                if (response.success) {
                    fetchNotifications();
                }
            },
            error: function(xhr) {
                console.error('Error marking all notifications as read:', xhr);
            },
            complete: function() {
                if ($btn && originalText) {
                    $btn.prop('disabled', false);
                    $btn.html(originalText);
                }
            }
        });
    }
    
    function getTimeAgo(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const seconds = Math.floor((now - date) / 1000);
        
        const intervals = {
            year: 31536000,
            month: 2592000,
            week: 604800,
            day: 86400,
            hour: 3600,
            minute: 60
        };
        
        for (const [unit, secondsInUnit] of Object.entries(intervals)) {
            const interval = Math.floor(seconds / secondsInUnit);
            if (interval >= 1) {
                return interval === 1 ? `1 ${unit} ago` : `${interval} ${unit}s ago`;
            }
        }
        
        return 'Just now';
    }
});
</script>

<!-- Alpine.js -->
<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>