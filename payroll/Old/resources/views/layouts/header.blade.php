<header id="main-header" class="bg-white border-b border-gray-200 h-16 flex items-center justify-between px-4 sm:px-6 fixed top-0 z-50 main-header-wrapper" style="right: 0;">
    <!-- Mobile menu button -->
    <button id="mobile-menu-toggle" onclick="toggleMobileSidebar()" class="lg:hidden p-2 rounded-lg text-gray-500 hover:text-gray-700 hover:bg-gray-100 transition-colors">
        <i id="mobile-menu-icon" class="fas fa-bars text-lg"></i>
    </button>
    
    <!-- Page Title & Breadcrumbs - Improved for mobile -->
    <div class="flex items-center min-w-0 flex-1 lg:flex-none mx-2 sm:mx-4">
        <div class="min-w-0 max-w-full">
            <!-- Main Title - Truncates on mobile -->
            <h2 class="text-lg sm:text-xl font-semibold text-gray-900 truncate max-w-[180px] xs:max-w-[220px] sm:max-w-none">
                @yield('page-title', 'Dashboard')
            </h2>
            
            <!-- Breadcrumbs - Hidden on mobile, shown on tablet and up -->
            @if(isset($breadcrumbs))
                <nav class="hidden sm:block ms-0 sm:ms-4 text-sm text-gray-500">
                    <ol class="flex items-center space-x-2 flex-wrap">
                        @foreach($breadcrumbs as $breadcrumb)
                            <li class="flex items-center max-w-[120px] sm:max-w-none truncate">
                                @if(!$loop->first)
                                    <i class="fas fa-chevron-right text-xs me-2 flex-shrink-0"></i>
                                @endif
                                @if($loop->last)
                                    <span class="text-gray-900 truncate">{{ $breadcrumb['title'] }}</span>
                                @else
                                    <a href="{{ $breadcrumb['url'] }}" class="hover:text-blue-600 transition-colors truncate" title="{{ $breadcrumb['title'] }}">
                                        {{ $breadcrumb['title'] }}
                                    </a>
                                @endif
                            </li>
                        @endforeach
                    </ol>
                </nav>
            @endif
        </div>
    </div>
    
    <!-- Header Actions -->
    <div class="flex items-center space-x-2 sm:space-x-4 flex-shrink-0">
        <!-- Mobile Search Button (Hidden on desktop) -->
        <button id="mobile-search-toggle" class="lg:hidden p-2 rounded-lg text-gray-500 hover:text-gray-700 hover:bg-gray-100 transition-colors">
            <i class="fas fa-search text-lg"></i>
        </button>

        <!-- Live Attendance Status Widget -->
        <!-- Animated Current Time & Quick Links -->
        <div class="hidden xs:flex items-center space-x-3 sm:space-x-6">
            <!-- Animated Time - Responsive version -->
            <div class="hidden sm:flex items-center justify-center gap-2 sm:gap-3 px-2 sm:px-3 py-1 rounded-xl bg-gradient-to-r from-blue-50 to-cyan-50 border border-blue-100 shadow-sm">
                <div class="flex items-center gap-1">
                    <i class="fas fa-clock text-blue-500" style="font-size:1.1em;"></i>
                    <span id="live-clock" class="font-mono text-sm sm:text-base tracking-wide text-blue-700 font-semibold">--:--:--</span>
                </div>
                <span class="text-gray-300 text-lg font-light hidden md:inline">|</span>
                <div class="flex items-center gap-1 hidden md:flex">
                    <i class="fas fa-calendar-alt text-emerald-500" style="font-size:1.1em;"></i>
                    <span id="live-date" class="font-medium text-emerald-600 text-xs sm:text-sm">-- --- ----</span>
                </div>
            </div>

            <!-- Mobile Compact Time (Shows only time on small screens) -->
            <div class="flex sm:hidden items-center justify-center gap-1 px-2 py-1 rounded-lg bg-gradient-to-r from-blue-50 to-cyan-50 border border-blue-100 shadow-sm">
                <i class="fas fa-clock text-blue-500 text-sm"></i>
                <span id="mobile-live-clock" class="font-mono text-xs text-blue-700 font-semibold">--:--</span>
            </div>
            
            <style>
            @keyframes spin-slow { 100% { transform: rotate(360deg); } }
            .animate-spin-slow { animation: spin-slow 8s linear infinite; }
            </style>
            <!-- Quick Links -->
            <div class="hidden md:flex space-x-3">
               
            </div>
        </div>

        <!-- Mobile-only time (for very small screens when other time is hidden) -->
        <div class="xs:hidden flex items-center">
            <div class="flex items-center gap-1 px-2 py-1 rounded-lg bg-gradient-to-r from-blue-50 to-cyan-50 border border-blue-100 shadow-sm">
                <i class="fas fa-clock text-blue-500 text-xs"></i>
                <span id="xs-live-clock" class="font-mono text-xs text-blue-700 font-semibold">--:--</span>
            </div>
        </div>
        
        <!-- Notifications -->
        <!-- <div class="relative">
            <button class="p-2 rounded-lg text-gray-500 hover:text-gray-700 hover:bg-gray-100 transition-colors relative">
                <i class="fas fa-bell text-lg"></i>
                <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">3</span>
            </button>
        </div> -->
        
        <!-- Profile Dropdown -->
        <div class="relative" x-data="{ open: false }">
            <button @click="open = !open" class="flex items-center p-1 sm:p-2 rounded-lg hover:bg-gray-100 transition-colors">
                <div class="w-7 h-7 sm:w-8 sm:h-8 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center flex-shrink-0">
                    <span class="text-white font-semibold text-xs sm:text-sm">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</span>
                </div>
                <i class="fas fa-chevron-down ms-1 sm:ms-2 text-gray-400 text-xs sm:text-sm hidden sm:block"></i>
            </button>
            
            <!-- Dropdown Menu -->
            <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95" class="absolute right-0 mt-2 w-48 sm:w-56 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-50">
                <div class="px-4 py-2 border-b border-gray-200">
                    <p class="text-sm font-medium text-gray-900 truncate">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-gray-500 truncate">{{ auth()->user()->email }}</p>
                </div>
                
                <a class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors">
                    <i class="fas fa-user-circle me-3 text-gray-400"></i>
                    <span class="truncate">Profile</span>
                </a>
                
                <!-- <a href="#" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors">
                    <i class="fas fa-cog me-3 text-gray-400"></i>
                    <span class="truncate">Settings</span>
                </a> -->
                
                <a href="#" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors">
                    <i class="fas fa-question-circle me-3 text-gray-400"></i>
                    <span class="truncate">Help</span>
                </a>
                
                <div class="border-t border-gray-200 my-1"></div>
                
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="flex items-center w-full px-4 py-2 text-sm text-red-700 hover:bg-red-50 transition-colors">
                        <i class="fas fa-sign-out-alt me-3 text-red-500"></i>
                        <span class="truncate">Logout</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>

<!-- Mobile Search Overlay (Hidden by default) -->
<div id="mobile-search-overlay" class="fixed inset-0 bg-white z-50 hidden lg:hidden">
    <div class="flex items-center p-4 border-b border-gray-200">
        <button id="mobile-search-close" class="p-2 me-3 text-gray-500">
            <i class="fas fa-arrow-left text-lg"></i>
        </button>
        <div class="flex-1 relative">
            <input type="text" placeholder="Search..." class="w-full px-4 py-3 ps-10 bg-gray-100 border-0 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
            <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
        </div>
    </div>
    <!-- Search results would go here -->
</div>

<!-- Alpine.js -->
<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

<script>
// Live clock and date with mobile optimizations
function updateClock() {
    const now = new Date();
    const h = String(now.getHours()).padStart(2, '0');
    const m = String(now.getMinutes()).padStart(2, '0');
    const s = String(now.getSeconds()).padStart(2, '0');
    
    // Update all clock elements
    const liveClock = document.getElementById('live-clock');
    const mobileLiveClock = document.getElementById('mobile-live-clock');
    const xsLiveClock = document.getElementById('xs-live-clock');
    
    if (liveClock) liveClock.textContent = `${h}:${m}:${s}`;
    if (mobileLiveClock) mobileLiveClock.textContent = `${h}:${m}`;
    if (xsLiveClock) xsLiveClock.textContent = `${h}:${m}`;
    
    // Date: e.g. 11 Oct 2025, Sat
    const days = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
    const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    const dateStr = `${now.getDate()} ${months[now.getMonth()]} ${now.getFullYear()}, ${days[now.getDay()]}`;
    
    const liveDate = document.getElementById('live-date');
    if (liveDate) liveDate.textContent = dateStr;
}

setInterval(updateClock, 1000);
updateClock();

// Mobile search functionality
document.addEventListener('DOMContentLoaded', function() {
    const mobileSearchToggle = document.getElementById('mobile-search-toggle');
    const mobileSearchOverlay = document.getElementById('mobile-search-overlay');
    const mobileSearchClose = document.getElementById('mobile-search-close');
    
    if (mobileSearchToggle && mobileSearchOverlay && mobileSearchClose) {
        mobileSearchToggle.addEventListener('click', function() {
            mobileSearchOverlay.classList.remove('hidden');
            mobileSearchOverlay.querySelector('input').focus();
        });
        
        mobileSearchClose.addEventListener('click', function() {
            mobileSearchOverlay.classList.add('hidden');
        });
        
        // Close search when pressing escape
        mobileSearchOverlay.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                mobileSearchOverlay.classList.add('hidden');
            }
        });
    }
});

// Optional: Add resize observer to handle very small screens
function handleHeaderResize() {
    const header = document.getElementById('main-header');
    const pageTitle = header.querySelector('h2');
    
    // Adjust title max-width based on available space
    const headerWidth = header.offsetWidth;
    if (headerWidth < 400) {
        pageTitle.classList.add('max-w-[120px]');
        pageTitle.classList.remove('max-w-[180px]', 'max-w-[220px]');
    } else if (headerWidth < 500) {
        pageTitle.classList.add('max-w-[180px]');
        pageTitle.classList.remove('max-w-[120px]', 'max-w-[220px]');
    } else {
        pageTitle.classList.add('max-w-[220px]');
        pageTitle.classList.remove('max-w-[120px]', 'max-w-[180px]');
    }
}

// Initialize and observe resize
if (typeof ResizeObserver !== 'undefined') {
    const resizeObserver = new ResizeObserver(handleHeaderResize);
    const header = document.getElementById('main-header');
    if (header) {
        resizeObserver.observe(header);
    }
}
</script>

<style>
/* Additional responsive utilities */
@media (max-width: 475px) {
    #main-header {
        padding-left: 0.75rem;
        padding-right: 0.75rem;
    }
    
    .main-header-wrapper .flex-1 {
        margin-left: 0.5rem;
        margin-right: 0.5rem;
    }
}

@media (max-width: 380px) {
    #main-header {
        padding-left: 0.5rem;
        padding-right: 0.5rem;
    }
    
    .main-header-wrapper .flex-1 {
        margin-left: 0.25rem;
        margin-right: 0.25rem;
    }
    
    /* Hide time on extra small screens if needed */
    .xs\:hidden {
        display: none !important;
    }
}

/* Ensure text truncation works properly */
.truncate {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

/* Smooth transitions for all interactive elements */
#main-header * {
    transition: all 0.2s ease-in-out;
}
</style>