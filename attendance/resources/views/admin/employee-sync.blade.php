@extends('layouts.app')

@section('title', 'Employee Synchronization - HRMS')
@section('page-title', 'Employee Synchronization')

@section('content')
<div class="p-4 sm:p-6 space-y-4 sm:space-y-6">
    <!-- Header Section -->
    <div class="bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 rounded-xl sm:rounded-2xl p-4 sm:p-6 lg:p-8 text-white relative overflow-hidden">
        <div class="absolute inset-0 opacity-10">
            <div class="absolute -top-4 -right-4 w-20 h-20 sm:w-32 sm:h-32 bg-white rounded-full"></div>
            <div class="absolute top-10 -right-8 w-16 h-16 sm:w-20 sm:h-20 bg-white rounded-full"></div>
            <div class="absolute -bottom-6 -left-6 w-16 h-16 sm:w-24 sm:h-24 bg-white rounded-full"></div>
        </div>
        <div class="relative">
            <div class="flex flex-col lg:flex-row items-center justify-between">
                <div class="w-full lg:w-auto mb-4 lg:mb-0">
                    <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold mb-2 sm:mb-3 flex items-center">
                        <i class="fas fa-sync-alt mr-2 sm:mr-4 text-lg sm:text-xl"></i>
                        <span class="text-lg sm:text-xl lg:text-2xl">Employee Synchronization</span>
                    </h1>
                    <p class="text-blue-100 text-sm sm:text-base lg:text-lg">Synchronize employees from the payroll system</p>
                </div>
                <div class="hidden lg:block">
                    <div class="w-24 h-24 sm:w-32 sm:h-32 lg:w-36 lg:h-36 bg-white bg-opacity-15 rounded-full flex items-center justify-center">
                        <i class="fas fa-user-plus text-3xl sm:text-4xl lg:text-5xl text-white"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Messages -->
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 rounded-lg p-3 sm:p-4 shadow-sm">
            <div class="flex items-start sm:items-center">
                <i class="fas fa-check-circle text-green-500 mr-2 text-base sm:text-lg flex-shrink-0 mt-0.5 sm:mt-0"></i>
                <p class="text-green-800 font-medium text-sm sm:text-base">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-50 border border-red-200 rounded-lg p-3 sm:p-4 shadow-sm">
            <div class="flex items-start sm:items-center">
                <i class="fas fa-exclamation-circle text-red-500 mr-2 text-base sm:text-lg flex-shrink-0 mt-0.5 sm:mt-0"></i>
                <p class="text-red-800 font-medium text-sm sm:text-base">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    <!-- API Connection Status -->
    <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
        <div class="p-4 sm:p-6 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-white">
            <h3 class="text-lg sm:text-xl font-semibold text-gray-900 flex items-center">
                <i class="fas fa-plug text-blue-600 mr-2 sm:mr-3 text-sm sm:text-base"></i>
                <span class="text-sm sm:text-base lg:text-lg">Payroll API Connection Status</span>
            </h3>
        </div>

        <div class="p-4 sm:p-6">
            <div class="flex flex-col sm:flex-row items-start sm:items-center space-y-3 sm:space-y-0 sm:space-x-4">
                <div class="flex-shrink-0">
                    @if($apiConnected)
                        <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-green-100 flex items-center justify-center">
                            <i class="fas fa-check text-green-600 text-lg sm:text-2xl"></i>
                        </div>
                    @else
                        <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-red-100 flex items-center justify-center">
                            <i class="fas fa-times text-red-600 text-lg sm:text-2xl"></i>
                        </div>
                    @endif
                </div>

                <div class="flex-1">
                    <h4 class="text-base sm:text-lg font-medium">
                        @if($apiConnected)
                            <span class="text-green-600">Connected</span>
                        @else
                            <span class="text-red-600">Disconnected</span>
                        @endif
                    </h4>
                    <p class="text-gray-500 text-sm sm:text-base mt-1">
                        @if($apiConnected)
                            Successfully connected to the payroll API. Employee data is available for synchronization.
                        @else
                            Unable to connect to the payroll API. Please check your credentials and try again.
                        @endif
                    </p>
                </div>
            </div>

            <div class="mt-4 sm:mt-6">
                <a href="{{ route('admin.employee-sync.test') }}" class="inline-flex items-center px-3 sm:px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors text-sm sm:text-base">
                    <i class="fas fa-network-wired mr-2 text-sm sm:text-base"></i>
                    Test Connection
                </a>
            </div>
        </div>
    </div>

    <!-- Sync Statistics -->
    <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
        <div class="p-4 sm:p-6 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-white">
            <h3 class="text-lg sm:text-xl font-semibold text-gray-900 flex items-center">
                <i class="fas fa-chart-bar text-blue-600 mr-2 sm:mr-3 text-sm sm:text-base"></i>
                <span class="text-sm sm:text-base lg:text-lg">Synchronization Statistics</span>
            </h3>
        </div>

        <div class="p-4 sm:p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
                <div class="bg-blue-50 border border-blue-100 rounded-lg p-4 sm:p-5">
                    <h4 class="text-base sm:text-lg font-medium text-gray-900 mb-2">API Employees</h4>
                    <div class="flex items-center">
                        <div class="w-12 h-12 sm:w-16 sm:h-16 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-cloud-download-alt text-blue-600 text-lg sm:text-2xl"></i>
                        </div>
                        <div class="ml-3 sm:ml-4">
                            <p class="text-2xl sm:text-3xl font-bold text-blue-600">{{ $stats['api_employees_count'] }}</p>
                            <p class="text-gray-500 text-sm sm:text-base">Employees in payroll system</p>
                        </div>
                    </div>
                </div>

                <div class="bg-green-50 border border-green-100 rounded-lg p-4 sm:p-5">
                    <h4 class="text-base sm:text-lg font-medium text-gray-900 mb-2">System Employees</h4>
                    <div class="flex items-center">
                        <div class="w-12 h-12 sm:w-16 sm:h-16 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-users text-green-600 text-lg sm:text-2xl"></i>
                        </div>
                        <div class="ml-3 sm:ml-4">
                            <p class="text-2xl sm:text-3xl font-bold text-green-600">{{ $stats['system_employees_count'] }}</p>
                            <p class="text-gray-500 text-sm sm:text-base">Employees in HRMS</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sync Actions -->
    <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
        <div class="p-4 sm:p-6 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-white">
            <h3 class="text-lg sm:text-xl font-semibold text-gray-900 flex items-center">
                <i class="fas fa-cog text-blue-600 mr-2 sm:mr-3 text-sm sm:text-base"></i>
                <span class="text-sm sm:text-base lg:text-lg">Synchronization Actions</span>
            </h3>
        </div>

        <div class="p-4 sm:p-6">
            <div class="space-y-4">
                <p class="text-gray-700 text-sm sm:text-base">
                    Comprehensive employee synchronization ensures complete data consistency between payroll and attendance systems.
                    This process will create, update, and optionally remove employees based on the payroll system data.
                </p>

                <!-- Sync Status Information -->
                @if(isset($stats['sync_needed']) && $stats['sync_needed'])
                    <div class="bg-orange-50 border border-orange-200 rounded-lg p-3 sm:p-4">
                        <div class="flex items-start sm:items-center">
                            <i class="fas fa-exclamation-triangle text-orange-600 mr-2 text-base sm:text-lg flex-shrink-0 mt-0.5 sm:mt-0"></i>
                            <div>
                                <p class="text-orange-800 font-medium text-sm sm:text-base">Sync Recommended</p>
                                <p class="text-orange-700 text-xs sm:text-sm mt-1">
                                    Employee count mismatch detected ({{ abs($stats['difference']) }} employees).
                                    Last sync: {{ $stats['last_sync'] ?? 'Never' }}
                                </p>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="bg-green-50 border border-green-200 rounded-lg p-3 sm:p-4">
                        <div class="flex items-start sm:items-center">
                            <i class="fas fa-check-circle text-green-600 mr-2 text-base sm:text-lg flex-shrink-0 mt-0.5 sm:mt-0"></i>
                            <div>
                                <p class="text-green-800 font-medium text-sm sm:text-base">Systems in Sync</p>
                                <p class="text-green-700 text-xs sm:text-sm mt-1">
                                    Employee counts match. Last sync: {{ $stats['last_sync'] ?? 'Never' }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 sm:p-4">
                    <p class="text-blue-800 text-sm sm:text-base">
                        <i class="fas fa-info-circle mr-2 text-sm sm:text-base"></i>
                        <span class="font-medium">Note:</span>
                        The synchronization preserves all existing employee data while ensuring consistency with the payroll system.
                    </p>
                </div>

                <form action="{{ route('admin.employee-sync.sync') }}" method="POST" class="mt-4 sm:mt-6 space-y-4">
                    @csrf
                    <div class="space-y-3">
                        <div class="flex items-center">
                            <input type="checkbox" id="force_update" name="force_update" value="1" class="h-4 w-4 sm:h-5 sm:w-5 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                            <label for="force_update" class="ml-2 text-gray-700 text-sm sm:text-base">
                                Force update all employees (even if no changes detected)
                            </label>
                        </div>

                        <div class="flex items-center">
                            <input type="checkbox" id="delete_extra" name="delete_extra" value="1" checked class="h-4 w-4 sm:h-5 sm:w-5 text-red-600 rounded border-gray-300 focus:ring-red-500">
                            <label for="delete_extra" class="ml-2 text-gray-700 text-sm sm:text-base">
                                <span class="text-red-600 font-medium">Remove extra employees</span> not found in payroll system
                            </label>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-3">
                        <button type="submit" class="inline-flex items-center justify-center px-4 sm:px-5 py-2 sm:py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors text-sm sm:text-base">
                            <i class="fas fa-sync-alt mr-2 text-sm sm:text-base"></i>
                            Start Comprehensive Sync
                        </button>

                        <a href="{{ route('admin.employee-sync.test') }}" class="inline-flex items-center justify-center px-4 sm:px-5 py-2 sm:py-2.5 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition-colors text-sm sm:text-base">
                            <i class="fas fa-network-wired mr-2 text-sm sm:text-base"></i>
                            Test API Connection
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Sample Data -->
    @if($apiConnected && count($apiEmployees) > 0)
    <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
        <div class="p-4 sm:p-6 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-white">
            <h3 class="text-lg sm:text-xl font-semibold text-gray-900 flex items-center">
                <i class="fas fa-table text-blue-600 mr-2 sm:mr-3 text-sm sm:text-base"></i>
                <span class="text-sm sm:text-base lg:text-lg">Sample API Data ({{ min(5, count($apiEmployees)) }} of {{ count($apiEmployees) }})</span>
            </h3>
        </div>

        <div class="p-4 sm:p-6 overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Employee ID
                        </th>
                        <th scope="col" class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Name
                        </th>
                        <th scope="col" class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Designation
                        </th>
                        <th scope="col" class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Department
                        </th>
                        <th scope="col" class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Join Date
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach(array_slice($apiEmployees, 0, 5) as $employee)
                    <tr>
                        <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-xs sm:text-sm font-medium text-gray-900">
                            {{ $employee['employee_id'] ?? 'N/A' }}
                        </td>
                        <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-xs sm:text-sm text-gray-800">
                            {{ $employee['name'] ?? 'N/A' }}
                        </td>
                        <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-xs sm:text-sm text-gray-500">
                            {{ $employee['designation'] ?? 'N/A' }}
                        </td>
                        <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-xs sm:text-sm text-gray-500">
                            {{ $employee['department'] ?? 'N/A' }}
                        </td>
                        <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-xs sm:text-sm text-gray-500">
                            {{ $employee['date_of_joining'] ?? 'N/A' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

</div>
@endsection
