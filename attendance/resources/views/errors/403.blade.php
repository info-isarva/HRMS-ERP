@extends('layouts.app')

@section('title', 'Access Denied - 403')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <!-- Main Error Card -->
        <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden">
            <!-- Header Section with Gradient -->
            <div class="bg-gradient-to-r from-red-500 to-pink-600 px-8 py-12 text-center relative overflow-hidden">
                <!-- Background Pattern -->
                <div class="absolute inset-0 opacity-10">
                    <div class="absolute top-0 left-0 w-32 h-32 bg-white rounded-full -translate-x-16 -translate-y-16"></div>
                    <div class="absolute top-0 right-0 w-24 h-24 bg-white rounded-full translate-x-12 -translate-y-12"></div>
                    <div class="absolute bottom-0 left-1/2 w-20 h-20 bg-white rounded-full -translate-x-10 translate-y-10"></div>
                </div>

                <!-- Error Icon -->
                <div class="relative z-10 mx-auto w-20 h-20 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center mb-6 shadow-lg">
                    <svg class="w-10 h-10 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                    </svg>
                </div>

                <!-- Error Code -->
                <h1 class="relative z-10 text-6xl font-bold text-white mb-2 tracking-tight">403</h1>
                <h2 class="relative z-10 text-xl font-semibold text-white/90 mb-4">Access Denied</h2>
            </div>

            <!-- Content Section -->
            <div class="px-8 py-8">
                <!-- Error Message -->
                <div class="text-center mb-8">
                    <p class="text-slate-600 text-lg leading-relaxed mb-6">
                        You don't have permission to access this page. Please contact your administrator if you believe this is an error.
                    </p>
                </div>

                <!-- Required Permission Info (if available) -->
                @if(isset($exception) && method_exists($exception, 'getMessage'))
                    <div class="bg-red-50 border border-red-200 rounded-xl p-6 mb-8">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-sm font-semibold text-red-800 mb-1">
                                    Required Permission
                                </h3>
                                <p class="text-sm text-red-700 leading-relaxed">
                                    {{ $exception->getMessage() }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Action Buttons -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
                    <a href="{{ route('dashboard') }}"
                       class="inline-flex items-center justify-center px-6 py-4 border border-transparent text-base font-semibold rounded-xl text-white bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transform hover:scale-105 transition-all duration-200 shadow-lg hover:shadow-xl">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        Go to Dashboard
                    </a>

                    <button onclick="history.back()"
                            class="inline-flex items-center justify-center px-6 py-4 border-2 border-slate-300 text-base font-semibold rounded-xl text-slate-700 bg-white hover:bg-slate-50 hover:border-slate-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transform hover:scale-105 transition-all duration-200 shadow-md hover:shadow-lg">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Go Back
                    </button>
                </div>

                <!-- Additional Help -->
                <div class="text-center border-t border-slate-200 pt-6">
                    <div class="flex items-center justify-center mb-3">
                        <svg class="w-5 h-5 text-slate-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="text-sm font-medium text-slate-600">Need Help?</span>
                    </div>
                    <p class="text-sm text-slate-500 leading-relaxed">
                        Contact the system administrator or your manager for assistance.
                    </p>
                </div>
            </div>
        </div>

        <!-- Footer Branding -->
        <div class="text-center mt-8">
            <p class="text-sm text-slate-400">
                © {{ date('Y') }} HRMS Attendance System
            </p>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* Smooth animations */
    .animate-fade-in {
        animation: fadeIn 0.8s ease-out;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(30px) scale(0.95);
        }
        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    /* Apply fade-in animation */
    .max-w-lg {
        animation: fadeIn 0.8s ease-out;
    }

    /* Subtle hover effects */
    .hover-lift:hover {
        transform: translateY(-2px);
    }

    /* Gradient text effect for error code */
    .gradient-text {
        background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
</style>
@endpush