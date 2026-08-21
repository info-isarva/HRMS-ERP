<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <title>Sign in — {{ config('posh.product_name') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { inter: ['Inter', 'sans-serif'] },
                },
            },
        };
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .login-bg {
            background:
                radial-gradient(ellipse 80% 60% at 20% 10%, rgba(99, 102, 241, 0.18) 0%, transparent 55%),
                radial-gradient(ellipse 70% 50% at 85% 90%, rgba(139, 92, 246, 0.14) 0%, transparent 55%),
                linear-gradient(160deg, #eef2ff 0%, #f5f3ff 45%, #faf5ff 100%);
        }
        .login-card {
            box-shadow: 0 24px 60px rgba(79, 70, 229, 0.14), 0 4px 16px rgba(15, 23, 42, 0.06);
        }
        .login-input:focus {
            outline: none;
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
        }
        .login-btn {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            box-shadow: 0 8px 24px rgba(79, 70, 229, 0.35);
            transition: transform 0.15s ease, box-shadow 0.15s ease, filter 0.15s ease;
        }
        .login-btn:hover {
            filter: brightness(1.05);
            box-shadow: 0 12px 28px rgba(79, 70, 229, 0.42);
            transform: translateY(-1px);
        }
        .login-btn:active { transform: translateY(0); }
    </style>
</head>
<body class="login-bg min-h-screen flex items-center justify-center px-4 py-10">
    <div class="w-full max-w-[420px]">
        <div class="login-card rounded-2xl bg-white border border-white/80 overflow-hidden">
            <div class="px-8 pt-8 pb-6 text-center border-b border-slate-100">
                <span class="inline-flex h-14 w-14 items-center justify-center rounded-2xl bg-indigo-100 text-indigo-600 mb-4">
                    <i class="fas fa-shield-halved text-2xl"></i>
                </span>
                <h1 class="text-xl font-bold text-slate-900 tracking-tight">{{ config('posh.product_name') }}</h1>
                <p class="text-sm text-slate-500 mt-1.5">Sign in to your workplace compliance portal</p>
            </div>

            <div class="px-8 py-7">
                @if($errors->any())
                    <div class="mb-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800 flex gap-2 items-start">
                        <i class="fas fa-circle-exclamation mt-0.5 shrink-0"></i>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label for="email" class="block text-xs font-semibold uppercase tracking-wide text-slate-500 mb-2">Email</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400 pointer-events-none">
                                <i class="fas fa-envelope text-sm"></i>
                            </span>
                            <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                                autocomplete="email"
                                placeholder="you@company.com"
                                class="login-input w-full rounded-xl border border-slate-200 bg-slate-50/50 pl-10 pr-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400">
                        </div>
                    </div>

                    <div>
                        <label for="password" class="block text-xs font-semibold uppercase tracking-wide text-slate-500 mb-2">Password</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400 pointer-events-none">
                                <i class="fas fa-lock text-sm"></i>
                            </span>
                            <input type="password" id="password" name="password" required
                                autocomplete="current-password"
                                placeholder="Enter your password"
                                class="login-input w-full rounded-xl border border-slate-200 bg-slate-50/50 pl-10 pr-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400">
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-0.5">
                        <label class="inline-flex items-center gap-2.5 cursor-pointer select-none">
                            <input type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}
                                class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 focus:ring-offset-0">
                            <span class="text-sm text-slate-600">Remember me</span>
                        </label>
                    </div>

                    <button type="submit" class="login-btn w-full rounded-xl py-3 text-sm font-semibold text-white mt-1">
                        Sign in
                    </button>
                </form>
            </div>
        </div>

        @if(config('posh.workspace_url'))
            <p class="mt-6 text-center text-sm text-slate-600">
                ERP client?
                <a href="{{ config('posh.workspace_url') }}" class="font-semibold text-indigo-600 hover:text-indigo-800 transition">
                    Sign in via Workspace SSO
                </a>
            </p>
        @endif
    </div>
</body>
</html>
