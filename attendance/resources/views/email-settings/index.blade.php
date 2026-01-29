@extends('layouts.app')

@section('title', 'Email Settings')

@section('page-title', 'Email Settings')

@section('styles')
<style>
    .toggle-switch {
        position: relative;
        display: inline-block;
        width: 80px;
        height: 44px;
    }

    .toggle-switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .toggle-slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ef4444;
        transition: .4s;
        border-radius: 44px;
        box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .toggle-slider:before {
        position: absolute;
        content: "";
        height: 36px;
        width: 36px;
        left: 4px;
        bottom: 4px;
        background-color: white;
        transition: .4s;
        border-radius: 50%;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }

    input:checked + .toggle-slider {
        background-color: #10b981;
        box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    input:checked + .toggle-slider:before {
        transform: translateX(36px);
    }

    .setting-card {
        transition: all 0.3s ease-in-out;
        background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
    }

    .setting-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
    }

    .status-indicator {
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.7; }
    }
</style>
@endsection

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50">
    <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-8 mb-8">
        <!-- Main Settings Card - Full Width -->
        <div class="setting-card rounded-3xl shadow-xl border border-gray-200 overflow-hidden">
                <!-- Header -->
                <div class="bg-gradient-to-r from-blue-600 to-indigo-700 px-8 py-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                                <i class="fas fa-envelope text-white text-lg"></i>
                            </div>
                            <div class="ml-4">
                                <h2 class="text-2xl font-bold text-white">Email Settings</h2>
                            </div>
                        </div>
                        <div class="hidden md:block">
                            <div class="text-right">
                                <div class="text-blue-100 text-sm">System Status</div>
                                <div class="flex items-center mt-2">
                                    <div class="w-3 h-3 rounded-full {{ $settings->emails_enabled ? 'bg-green-400' : 'bg-red-400' }} status-indicator mr-2"></div>
                                    <span class="text-white font-medium">
                                        {{ $settings->emails_enabled ? 'Active' : 'Inactive' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Settings Form -->
                <div class="p-8">
                    <form method="POST" action="{{ route('email-settings.update') }}" class="space-y-8">
                        @csrf
                        @method('PUT')

                        <!-- Master Control Section -->
                        <div class="bg-gradient-to-r from-gray-50 to-blue-50 rounded-2xl p-8 border border-gray-200">
                            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                                <!-- Left Content -->
                                <div class="flex-1">
                                    <div class="flex items-center mb-4">
                                        <div class="w-12 h-12 bg-gradient-to-br from-blue-100 to-indigo-100 text-blue-600 rounded-xl flex items-center justify-center mr-4">
                                            <i class="fas fa-envelope text-xl"></i>
                                        </div>
                                        <div>
                                            <h3 class="text-xl font-semibold text-gray-900">Master Email Control</h3>
                                            <p class="text-gray-600">Enable or disable all email notifications system-wide</p>
                                        </div>
                                    </div>

                                    <div class="space-y-4">
                                        <h4 class="text-lg font-semibold text-gray-900">
                                            Status: {{ $settings->emails_enabled ? '🟢 Enabled' : '🔴 Disabled' }}
                                        </h4>
                                        <p class="text-gray-700 leading-relaxed">
                                            {{ $settings->emails_enabled
                                                ? 'All email notifications are currently active. Users will receive emails for leave applications, approvals, rejections, and other system notifications.'
                                                : 'All email notifications are currently disabled. No emails will be sent for any system activities.' }}
                                        </p>

                                        <!-- Feature List -->
                                        <div class="grid md:grid-cols-2 gap-3 mt-4">
                                            <div class="flex items-center space-x-2">
                                                <i class="fas fa-check-circle text-green-500 text-sm"></i>
                                                <span class="text-gray-700 text-sm">Leave Applications</span>
                                            </div>
                                            <div class="flex items-center space-x-2">
                                                <i class="fas fa-check-circle text-green-500 text-sm"></i>
                                                <span class="text-gray-700 text-sm">Approval Notifications</span>
                                            </div>
                                            <div class="flex items-center space-x-2">
                                                <i class="fas fa-check-circle text-green-500 text-sm"></i>
                                                <span class="text-gray-700 text-sm">Rejection Alerts</span>
                                            </div>
                                            <div class="flex items-center space-x-2">
                                                <i class="fas fa-check-circle text-green-500 text-sm"></i>
                                                <span class="text-gray-700 text-sm">System Updates</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Right Content - Toggle -->
                                <div class="lg:flex-shrink-0 lg:text-center">
                                    <div class="mb-4">
                                        <label class="toggle-switch block mx-auto">
                                            <input type="checkbox" name="emails_enabled" value="1" {{ $settings->emails_enabled ? 'checked' : '' }}>
                                            <span class="toggle-slider"></span>
                                        </label>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-sm font-medium text-gray-500 mb-2">Click to {{ $settings->emails_enabled ? 'Disable' : 'Enable' }}</div>
                                        <div class="inline-flex items-center px-4 py-2 rounded-full {{ $settings->emails_enabled ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            <i class="fas {{ $settings->emails_enabled ? 'fa-check-circle' : 'fa-times-circle' }} mr-2"></i>
                                            {{ $settings->emails_enabled ? 'Emails Active' : 'Emails Disabled' }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex flex-col sm:flex-row justify-center items-center space-y-4 sm:space-y-0 sm:space-x-6 pt-6 border-t border-gray-200">
                            <a href="{{ route('dashboard') }}"
                               class="w-full sm:w-auto px-6 py-3 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition duration-300 font-medium text-center">
                                <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
                            </a>
                            <button type="submit"
                                    class="w-full sm:w-auto px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-lg hover:from-blue-700 hover:to-purple-700 transition duration-300 font-medium">
                                <i class="fas fa-save mr-2"></i>Save Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Info Cards Grid -->
            <div class="grid md:grid-cols-3 gap-6 mt-8">
                <!-- Immediate Effect -->
                <div class="bg-white rounded-xl p-6 shadow-lg border border-gray-100 hover:shadow-xl transition-shadow duration-300">
                    <div class="w-10 h-10 bg-gradient-to-br from-green-100 to-emerald-100 text-green-600 rounded-lg flex items-center justify-center mb-3">
                        <i class="fas fa-bolt"></i>
                    </div>
                    <h4 class="text-lg font-bold text-gray-900 mb-2">Immediate Effect</h4>
                    <p class="text-gray-600 text-sm">Changes take effect instantly across the entire system. No restart required.</p>
                </div>

                <!-- Super Admin Only -->
                <div class="bg-white rounded-xl p-6 shadow-lg border border-gray-100 hover:shadow-xl transition-shadow duration-300">
                    <div class="w-10 h-10 bg-gradient-to-br from-purple-100 to-pink-100 text-purple-600 rounded-lg flex items-center justify-center mb-3">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h4 class="text-lg font-bold text-gray-900 mb-2">Super Admin Control</h4>
                    <p class="text-gray-600 text-sm">This powerful setting is restricted to super administrators only for security.</p>
                </div>

                <!-- System Wide -->
                <div class="bg-white rounded-xl p-6 shadow-lg border border-gray-100 hover:shadow-xl transition-shadow duration-300">
                    <div class="w-10 h-10 bg-gradient-to-br from-blue-100 to-indigo-100 text-blue-600 rounded-lg flex items-center justify-center mb-3">
                        <i class="fas fa-globe"></i>
                    </div>
                    <h4 class="text-lg font-bold text-gray-900 mb-2">System Wide Impact</h4>
                    <p class="text-gray-600 text-sm">Controls all automated emails including leave requests, approvals, and notifications.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection