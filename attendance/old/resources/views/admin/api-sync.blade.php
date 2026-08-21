@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">API Synchronization Dashboard</h1>
        <div class="space-x-2">
            <a href="{{ route('admin.api-sync.test') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors">
                <i class="fas fa-plug mr-2"></i>
                Test API Connection
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
            <p>{{ session('success') }}</p>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
            <p>{{ session('error') }}</p>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <!-- API Connection Status -->
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="p-6">
                <h2 class="text-xl font-semibold text-gray-700 mb-4">API Connection</h2>
                
                <div class="flex items-center">
                    @if($apiConnected)
                        <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center mr-4">
                            <i class="fas fa-check-circle text-green-500 text-xl"></i>
                        </div>
                        <div>
                            <p class="text-lg font-medium text-green-600">Connected</p>
                            <p class="text-sm text-gray-500">API authentication successful</p>
                        </div>
                    @else
                        <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center mr-4">
                            <i class="fas fa-times-circle text-red-500 text-xl"></i>
                        </div>
                        <div>
                            <p class="text-lg font-medium text-red-600">Disconnected</p>
                            <p class="text-sm text-gray-500">API authentication failed</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sync Stats -->
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="p-6">
                <h2 class="text-xl font-semibold text-gray-700 mb-4">Synchronization Stats</h2>
                
                <div class="grid grid-cols-2 gap-4">
                    <div class="p-3 bg-purple-50 rounded-md">
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">API Departments</p>
                        <p class="text-2xl font-bold text-purple-600">{{ $stats['api_departments_count'] }}</p>
                    </div>
                    <div class="p-3 bg-purple-50 rounded-md">
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">System Departments</p>
                        <p class="text-2xl font-bold text-purple-600">{{ $stats['system_departments_count'] }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sync Options -->
    <div class="bg-white shadow-md rounded-lg overflow-hidden mb-6">
        <div class="p-6">
            <h2 class="text-xl font-semibold text-gray-700 mb-4">Synchronization Options</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Department Sync -->
                <div class="border rounded-lg p-6 flex flex-col items-center text-center">
                    <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mb-4">
                        <i class="fas fa-building text-purple-600 text-xl"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-800 mb-2">Department Sync</h3>
                    <p class="text-gray-600 mb-4">Synchronize department data from the payroll API.</p>
                    <a href="{{ route('admin.departments.sync') }}" class="mt-auto bg-purple-500 hover:bg-purple-600 text-white py-2 px-4 rounded w-full">
                        Manage Department Sync
                    </a>
                </div>
                
                <!-- Employee Sync -->
                <div class="border rounded-lg p-6 flex flex-col items-center text-center">
                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mb-4">
                        <i class="fas fa-users text-blue-600 text-xl"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-800 mb-2">Employee Sync</h3>
                    <p class="text-gray-600 mb-4">Synchronize employee data from the payroll API.</p>
                    <a href="{{ route('admin.employees.sync') }}" class="mt-auto bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded w-full">
                        Manage Employee Sync
                    </a>
                </div>
            </div>
            
            <!-- Advanced Sync Options -->
            <div class="mt-6 border-t pt-6">
                <h3 class="text-lg font-medium text-gray-700 mb-4">Advanced Sync Options</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Department Force Sync -->
                    <div class="border rounded-lg p-5 flex flex-col">
                        <div class="flex items-center mb-3">
                            <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center mr-3">
                                <i class="fas fa-sync-alt text-red-600"></i>
                            </div>
                            <h4 class="text-base font-medium text-gray-800">Force Department Sync</h4>
                        </div>
                        <p class="text-sm text-gray-600 mb-4">Remove all existing departments and replace with data from the API.</p>
                        <form action="{{ route('admin.departments.sync.sync') }}" method="POST" class="mt-auto" 
                              onsubmit="return confirm('Are you sure you want to force sync departments? This will remove all existing departments and replace them with data from the API.')">
                            @csrf
                            <input type="hidden" name="force" value="1">
                            <button type="submit" class="bg-red-500 hover:bg-red-600 text-white py-2 px-4 rounded w-full text-sm">
                                Force Department Sync
                            </button>
                        </form>
                    </div>
                    
                    <!-- Employee Force Sync -->
                    <div class="border rounded-lg p-5 flex flex-col">
                        <div class="flex items-center mb-3">
                            <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center mr-3">
                                <i class="fas fa-user-sync text-red-600"></i>
                            </div>
                            <h4 class="text-base font-medium text-gray-800">Force Employee Sync</h4>
                        </div>
                        <p class="text-sm text-gray-600 mb-4">Update all employees including admins with data from the API.</p>
                        <form action="{{ route('admin.employees.sync.sync') }}" method="POST" class="mt-auto" 
                              onsubmit="return confirm('Are you sure you want to force sync employees? This will update all employees including admin users.')">
                            @csrf
                            <input type="hidden" name="force" value="1">
                            <button type="submit" class="bg-red-500 hover:bg-red-600 text-white py-2 px-4 rounded w-full text-sm">
                                Force Employee Sync
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- About API Sync -->
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="p-6">
            <h2 class="text-xl font-semibold text-gray-700 mb-4">About API Synchronization</h2>
            <div class="prose prose-sm max-w-none">
                <p>This system synchronizes data from the payroll API to your HRMS database. Here's how it works:</p>
                
                <h3>Sync Types</h3>
                <ul>
                    <li><strong>Regular Sync:</strong> Updates existing data and adds new entries without removing anything.</li>
                    <li><strong>Force Sync:</strong> For departments - replaces all data. For employees - updates all including admins.</li>
                </ul>
                
                <h3>Department Data</h3>
                <p>The following department information is synchronized:</p>
                <ul>
                    <li>Department Name</li>
                    <li>Department Code</li>
                    <li>Department Description</li>
                    <li>API Department ID (for reference)</li>
                </ul>
                
                <h3>Employee Data</h3>
                <p>The following employee information is synchronized:</p>
                <ul>
                    <li>Name</li>
                    <li>Email</li>
                    <li>Department (linked to local department)</li>
                    <li>Employee ID</li>
                    <li>Reporting Manager (defaults to ID 1 if not specified)</li>
                </ul>
                
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mt-4">
                    <p class="text-yellow-700">
                        <strong>Warning:</strong> Force syncs should be used with caution. Department force sync will remove existing departments, which may affect employee assignments.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
