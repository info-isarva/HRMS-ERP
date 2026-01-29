@extends('layouts.app')

@section('title', 'API Connection Test - HRMS')
@section('page-title', 'API Connection Test')

@section('content')
<div class="p-6 space-y-6">
    <!-- Header Section -->
    <div class="bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 rounded-2xl p-8 text-white relative overflow-hidden">
        <div class="absolute inset-0 opacity-10">
            <div class="absolute -top-4 -right-4 w-32 h-32 bg-white rounded-full"></div>
            <div class="absolute top-10 -right-8 w-20 h-20 bg-white rounded-full"></div>
            <div class="absolute -bottom-6 -left-6 w-24 h-24 bg-white rounded-full"></div>
        </div>
        <div class="relative">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-4xl font-bold mb-3 flex items-center">
                        <i class="fas fa-network-wired mr-4"></i>
                        API Connection Test
                    </h1>
                    <p class="text-blue-100 text-lg">Test connection to the payroll system API</p>
                </div>
                <div class="hidden lg:block">
                    <div class="w-36 h-36 bg-white bg-opacity-15 rounded-full flex items-center justify-center">
                        <i class="fas fa-plug text-5xl text-white"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Connection Status -->
    <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-white">
            <h3 class="text-xl font-semibold text-gray-900 flex items-center">
                <i class="fas fa-plug text-blue-600 mr-3"></i>
                Connection Status
            </h3>
        </div>

        <div class="p-6">
            <div class="flex items-center space-x-4">
                <div class="flex-shrink-0">
                    @if($isConnected)
                        <div class="w-16 h-16 rounded-full bg-green-100 flex items-center justify-center">
                            <i class="fas fa-check text-green-600 text-3xl"></i>
                        </div>
                    @else
                        <div class="w-16 h-16 rounded-full bg-red-100 flex items-center justify-center">
                            <i class="fas fa-times text-red-600 text-3xl"></i>
                        </div>
                    @endif
                </div>

                <div>
                    <h4 class="text-xl font-medium">
                        @if($isConnected)
                            <span class="text-green-600">Connected Successfully</span>
                        @else
                            <span class="text-red-600">Connection Failed</span>
                        @endif
                    </h4>
                    <p class="text-gray-500 mt-2">
                        @if($isConnected)
                            The system was able to connect to the Payroll API and retrieve an authentication token.
                        @else
                            The system was unable to connect to the Payroll API. Please check your credentials and network settings.
                        @endif
                    </p>
                </div>
            </div>

            <div class="mt-8">
                <h4 class="font-medium text-gray-900 mb-2">Connection Details</h4>
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-100">
                        <dt class="text-sm text-gray-500">API Base URL</dt>
                        <dd class="font-medium mt-1">{{ config('external_api.payroll_api.base_url') }}</dd>
                    </div>
                    
                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-100">
                        <dt class="text-sm text-gray-500">Authentication Email</dt>
                        <dd class="font-medium mt-1">{{ config('external_api.payroll_api.email') }}</dd>
                    </div>
                    
                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-100 md:col-span-2">
                        <dt class="text-sm text-gray-500">JWT Token (Static)</dt>
                        <dd class="font-medium mt-1 break-all">
                            <span class="bg-gray-100 text-gray-800 p-1 rounded text-xs">{{ substr(config('external_api.payroll_api.jwt_token'), 0, 20) }}...{{ substr(config('external_api.payroll_api.jwt_token'), -10) }}</span>
                        </dd>
                    </div>
                    
                    @if($isConnected)
                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-100 md:col-span-2">
                        <dt class="text-sm text-gray-500">Authentication Token (Dynamic)</dt>
                        <dd class="font-medium mt-1 break-all">
                            <span class="bg-gray-100 text-gray-800 p-1 rounded">{{ $token }}</span>
                        </dd>
                    </div>
                    @endif
                </dl>
            </div>

            <div class="mt-8 flex space-x-4">
                <a href="{{ route('admin.employee-sync') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Back to Sync Dashboard
                </a>
                
                <button type="button" onclick="location.reload()" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
                    <i class="fas fa-redo-alt mr-2"></i>
                    Test Again
                </button>
            </div>
        </div>
    </div>

    @if(!$isConnected)
    <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-white">
            <h3 class="text-xl font-semibold text-gray-900 flex items-center">
                <i class="fas fa-exclamation-triangle text-amber-600 mr-3"></i>
                Troubleshooting
            </h3>
        </div>

        <div class="p-6">
            <ul class="list-disc pl-5 space-y-2 text-gray-700">
                <li>Check that the API base URL is correct</li>
                <li>Ensure the API credentials are valid</li>
                <li>Verify that the payroll system API is online and accessible</li>
                <li>Check if there are any firewalls or network restrictions</li>
                <li>Ensure the .env file contains the correct configuration</li>
                <li>Try restarting the web server</li>
            </ul>
            
            <div class="mt-4 bg-blue-50 p-4 rounded-lg border border-blue-100">
                <p class="text-blue-800">
                    <i class="fas fa-info-circle mr-2"></i>
                    <span class="font-medium">Tip:</span> 
                    You can update the API configuration in the .env file or directly in the config/external_api.php file.
                </p>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
