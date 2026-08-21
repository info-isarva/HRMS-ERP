<header id="main-header" class="bg-white border-b border-gray-200 h-16 flex items-center justify-between px-4 lg:px-6 fixed top-0 z-50 main-header-wrapper" style="right: 0;">
    <button type="button" id="mobile-menu-toggle" onclick="toggleMobileSidebar()" class="lg:hidden p-2 rounded-lg text-gray-500 hover:text-gray-700 hover:bg-gray-100 transition-colors">
        <i id="mobile-menu-icon" class="fas fa-bars text-lg"></i>
    </button>

    <div class="flex items-center min-w-0 flex-1 lg:flex-none">
        <h2 class="text-lg font-semibold text-blue-950 truncate">@yield('page-title', 'Dashboard')</h2>
    </div>

    <div class="flex items-center space-x-2 lg:space-x-3">
        <div class="hidden sm:flex items-center px-2 py-1 rounded-lg bg-gradient-to-r from-blue-50 to-cyan-50 border border-blue-100">
            <i class="fas fa-clock text-blue-500 text-sm mr-1"></i>
            <span id="live-clock" class="font-mono text-sm text-blue-700 font-semibold"></span>
        </div>

        @if(auth()->user()?->organization && ! auth()->user()->organization->usesNativeAuth())
            <a href="{{ config('posh.workspace_url') }}" target="_blank" rel="noopener"
               class="hidden sm:inline-flex items-center px-3 py-1.5 text-sm font-medium text-yellow-700 bg-yellow-50 border border-yellow-200 rounded-lg hover:bg-yellow-100 transition-colors">
                <i class="fas fa-th-large mr-2"></i> Workspace
            </a>
        @endif

        <form method="POST" action="{{ route('logout') }}" class="hidden sm:block">
            @csrf
            <button type="submit" class="text-xs font-medium text-slate-500 hover:text-indigo-600">Logout</button>
        </form>

        <div class="flex items-center pl-2 border-l border-gray-200">
            <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center">
                <span class="text-white font-semibold text-xs">{{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 2)) }}</span>
            </div>
        </div>
    </div>
</header>
<script>
(function() {
    function updateClock() {
        const now = new Date();
        const el = document.getElementById('live-clock');
        if (el) {
            el.textContent = String(now.getHours()).padStart(2,'0') + ':' + String(now.getMinutes()).padStart(2,'0');
        }
    }
    updateClock();
    setInterval(updateClock, 1000);
})();
</script>
