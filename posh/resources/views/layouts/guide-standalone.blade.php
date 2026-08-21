<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'User Guide') — {{ config('posh.product_name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Outfit:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'system-ui', 'sans-serif'],
                        display: ['Outfit', 'Inter', 'system-ui', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            DEFAULT: '#059669',
                            dark: '#064e3b',
                            light: '#ecfdf5',
                            muted: '#6ee7b7',
                        },
                    },
                },
            },
        };
    </script>
    @stack('styles')
</head>
<body class="font-sans antialiased text-gray-700 docs-page-bg">
    <header class="fixed top-0 left-0 right-0 z-50 border-b border-gray-100 bg-white/95 backdrop-blur-md shadow-sm">
        <div class="mx-auto flex h-14 max-w-[1400px] items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
            <div class="flex items-center gap-3 min-w-0">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700">
                    <i class="fas fa-shield-halved text-sm"></i>
                </span>
                <div class="min-w-0 hidden sm:block">
                    <p class="text-sm font-bold text-gray-900 truncate font-display">{{ config('posh.product_name') }}</p>
                    <p class="text-[10px] text-emerald-600 uppercase tracking-[0.15em] font-semibold">User guide</p>
                </div>
            </div>
            <a href="{{ route('dashboard') }}"
                class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 px-4 py-2 text-sm font-semibold text-white shadow-md shadow-emerald-500/25 transition hover:from-emerald-700 hover:to-teal-700 shrink-0">
                <i class="fas fa-arrow-left text-xs"></i>
                <span class="hidden sm:inline">Back to</span> portal
            </a>
        </div>
    </header>

    @yield('body')

    <footer class="border-t border-gray-100 bg-white mt-16">
        <div class="mx-auto max-w-[1400px] px-4 sm:px-6 lg:px-8 py-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-sm text-gray-500">
            <p class="flex items-center gap-2">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-100 text-emerald-700"><i class="fas fa-shield-halved text-xs"></i></span>
                &copy; {{ date('Y') }} {{ config('posh.product_name') }} — Workplace safety guide
            </p>
            <a href="{{ route('dashboard') }}" class="font-semibold text-emerald-600 hover:text-emerald-800 transition">
                <i class="fas fa-arrow-left mr-1"></i> Return to portal
            </a>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
