@extends('layouts.app')

@section('title', 'API Connection Test - HRMS')
@section('page-title', 'API Connection Test')

@section('content')
<div class="container mx-auto px-4">
    <!-- Header Section -->
    <div class="bg-white rounded-lg shadow-sm mb-4">
        <div class="px-5 py-4 border-b border-gray-100 flex justify-between items-center">
            <div class="flex items-center">
                <i class="fas fa-plug text-2xl text-indigo-600 mr-3"></i>
                <h2 class="font-semibold text-xl text-gray-800">API Connection Test</h2>
            </div>
            <span class="py-1 px-3 rounded bg-indigo-100 text-indigo-800 text-xs font-medium">
                <i class="fas fa-server mr-1"></i> Payroll API
            </span>
        </div>
        
        <div class="p-4">
            <p class="text-sm text-gray-600">Check and verify your Payroll API connection status</p>
        </div>
    </div>
    
    <!-- Connection Status Card -->
    <div class="bg-white rounded-lg shadow-sm mb-4">
        <div class="px-5 py-3 border-b border-gray-100">
            <h3 class="font-medium text-gray-800">
                <i class="fas fa-wifi text-indigo-600 mr-2"></i> Connection Status
            </h3>
        </div>
        
        <div class="p-4">
            <div class="flex flex-col md:flex-row items-center mb-4">
                @if($isConnected)
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mr-4 mb-3 md:mb-0">
                        <i class="fas fa-check-circle text-green-600 text-3xl"></i>
                    </div>
                    <div>
                        <p class="text-xl font-medium text-green-600 mb-1">Connected Successfully</p>
                        <p class="text-sm text-gray-600">The API connection is working properly and ready for data synchronization</p>
                        <p class="mt-2 inline-flex items-center text-xs text-green-600 font-medium">
                            <i class="fas fa-shield-alt mr-1"></i>
                            Secure connection established
                        </p>
                    </div>
                @else
                    <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mr-4 mb-3 md:mb-0">
                        <i class="fas fa-times-circle text-red-600 text-3xl"></i>
                    </div>
                    <div>
                        <p class="text-xl font-medium text-red-600 mb-1">Connection Failed</p>
                        <p class="text-sm text-gray-600">Unable to establish a connection with the Payroll API</p>
                        <p class="mt-2 inline-flex items-center text-xs text-red-600 font-medium">
                            <i class="fas fa-exclamation-triangle mr-1"></i>
                            Please check your configuration
                        </p>
                    </div>
                @endif
            </div>
            
            <div class="border-t border-gray-100 pt-4 mt-2">
                <h3 class="text-sm font-medium text-gray-700 mb-3">
                    <i class="fas fa-info-circle text-indigo-600 mr-1"></i>
                    Connection Details
                </h3>
                
                @if($isConnected)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div class="bg-gray-50 p-3 rounded border border-gray-100">
                            <p class="text-xs text-gray-500 mb-1">Connection Status</p>
                            <p class="text-sm font-medium text-green-600 flex items-center">
                                <i class="fas fa-circle text-xs mr-1"></i>
                                Connected
                            </p>
                        </div>
                        <div class="bg-gray-50 p-3 rounded border border-gray-100">
                            <p class="text-xs text-gray-500 mb-1">Authentication</p>
                            <p class="text-sm font-medium text-green-600">JWT Token Active</p>
                        </div>
                    </div>
                    
                    <div class="bg-green-50 rounded p-3 border border-green-100">
                        <div class="flex">
                            <div class="flex-shrink-0 mt-0.5">
                                <i class="fas fa-info-circle text-green-600"></i>
                            </div>
                            <div class="ml-3">
                                <h4 class="text-sm font-medium text-green-800">API connection is working properly</h4>
                                <div class="mt-1 text-xs text-green-700">
                                    <p>The connection to the Payroll API has been successfully established. You can now proceed with data synchronization.</p>
                                </div>
                                <div class="mt-3">
                                    <div class="flex space-x-3">
                                        <a href="{{ route('admin.departments.sync') }}" class="inline-flex items-center px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-medium rounded transition-colors">
                                            <i class="fas fa-building mr-1"></i>
                                            Sync Departments
                                        </a>
                                        <a href="{{ route('admin.employees.sync') }}" class="inline-flex items-center px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-medium rounded transition-colors">
                                            <i class="fas fa-users mr-1"></i>
                                            Sync Employees
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div class="bg-gray-50 p-3 rounded border border-gray-100">
                            <p class="text-xs text-gray-500 mb-1">Connection Status</p>
                            <p class="text-sm font-medium text-red-600 flex items-center">
                                <i class="fas fa-circle text-xs mr-1"></i>
                                Disconnected
                            </p>
                        </div>
                        <div class="bg-gray-50 p-3 rounded border border-gray-100">
                            <p class="text-xs text-gray-500 mb-1">Authentication</p>
                            <p class="text-sm font-medium text-red-600">Token Not Available</p>
                        </div>
                    </div>
                    
                    <div class="bg-red-50 rounded p-3 border border-red-100">
                        <div class="flex">
                            <div class="flex-shrink-0 mt-0.5">
                                <i class="fas fa-exclamation-triangle text-red-600"></i>
                            </div>
                            <div class="ml-3">
                                <h4 class="text-sm font-medium text-red-800">API connection failed</h4>
                                <div class="mt-1 text-xs text-red-700">
                                    <p class="mb-1">Please check the following possible issues:</p>
                                    <ul class="list-disc pl-4 space-y-0.5">
                                        <li>API URL configuration in .env file</li>
                                        <li>JWT token validity</li>
                                        <li>Authentication credentials</li>
                                        <li>Network connectivity to the API server</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Troubleshooting Card -->
    <div class="bg-white rounded-lg shadow-sm mb-4">
        <div class="px-5 py-3 border-b border-gray-100">
            <h3 class="font-medium text-gray-800">
                <i class="fas fa-tools text-indigo-600 mr-2"></i> Troubleshooting
            </h3>
        </div>
        
        <div class="p-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <h4 class="text-sm font-medium text-gray-700 mb-3 flex items-center">
                        <i class="fas fa-exclamation-circle text-amber-500 mr-1"></i>
                        Common Issues
                    </h4>
                    
                    <div class="space-y-3">
                        <div class="flex">
                            <div class="flex-shrink-0 mt-0.5 text-amber-500">
                                <i class="fas fa-key text-xs"></i>
                            </div>
                            <div class="ml-2">
                                <p class="text-xs text-gray-700"><span class="font-medium">Invalid JWT Token:</span> Ensure the JWT token in your configuration is valid and has not expired.</p>
                            </div>
                        </div>
                        
                        <div class="flex">
                            <div class="flex-shrink-0 mt-0.5 text-amber-500">
                                <i class="fas fa-link text-xs"></i>
                            </div>
                            <div class="ml-2">
                                <p class="text-xs text-gray-700"><span class="font-medium">Incorrect API URL:</span> Verify the API URL is correct in your .env file.</p>
                            </div>
                        </div>
                        
                        <div class="flex">
                            <div class="flex-shrink-0 mt-0.5 text-amber-500">
                                <i class="fas fa-network-wired text-xs"></i>
                            </div>
                            <div class="ml-2">
                                <p class="text-xs text-gray-700"><span class="font-medium">Network Issues:</span> Check if your server can reach the API endpoint.</p>
                            </div>
                        </div>
                        
                        <div class="flex">
                            <div class="flex-shrink-0 mt-0.5 text-amber-500">
                                <i class="fas fa-user-lock text-xs"></i>
                            </div>
                            <div class="ml-2">
                                <p class="text-xs text-gray-700"><span class="font-medium">Invalid Credentials:</span> Verify the email and password used for authentication.</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div>
                    <h4 class="text-sm font-medium text-gray-700 mb-3 flex items-center">
                        <i class="fas fa-cog text-indigo-600 mr-1"></i>
                        Configuration Settings
                    </h4>
                    
                    <p class="text-xs text-gray-700 mb-2">Review the following settings in your <code>.env</code> file:</p>
                    
                    <div class="bg-gray-50 p-3 rounded border border-gray-200 font-mono text-xs text-gray-700 mb-4">
                        <p>PAYROLL_API_URL=https://payrolldev.isarva.in/api</p>
                        <p>PAYROLL_API_EMAIL=your_email@example.com</p>
                        <p>PAYROLL_API_JWT_TOKEN=your_jwt_token</p>
                    </div>
                    
                    <h4 class="text-sm font-medium text-gray-700 mb-2 flex items-center">
                        <i class="fas fa-file-alt text-indigo-600 mr-1"></i>
                        Check Logs
                    </h4>
                    
                    <p class="text-xs text-gray-700">For detailed error information, check the Laravel logs at <code>storage/logs/laravel.log</code>.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
