@extends('layouts.app')

@section('title', 'Department Synchronization - HRMS')
@section('page-title', 'Department Synchronization')

@section('content')
<div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 space-y-8 mb-8">
    <!-- Header Section -->
    <div class="bg-white rounded-lg shadow-sm mb-4 mt-4">
        <div class="px-4 py-4 border-b border-gray-100">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center">
                    <i class="fas fa-building text-2xl text-indigo-600 mr-3"></i>
                    <h2 class="font-semibold text-xl text-gray-800">Department Synchronization</h2>
                </div>
            </div>
            
            <!-- Comprehensive Sync Form -->
            <form action="{{ route('admin.department-sync.sync') }}" method="POST" class="space-y-4">
                @csrf
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <p class="text-blue-800">
                        <i class="fas fa-info-circle mr-2"></i>
                        <span class="font-medium">Note:</span> 
                        Comprehensive department synchronization ensures complete data consistency between payroll and attendance systems.
                    </p>
                </div>
                
                <div class="space-y-3">
                    <div class="flex items-center">
                        <input type="checkbox" id="force_update" name="force_update" value="1" class="h-5 w-5 text-indigo-600 rounded border-gray-300 focus:ring-indigo-500">
                        <label for="force_update" class="ml-2 text-gray-700">
                            Force update all departments (even if no changes detected)
                        </label>
                    </div>
                    
                    <div class="flex items-center">
                        <input type="checkbox" id="delete_extra" name="delete_extra" value="1" class="h-5 w-5 text-red-600 rounded border-gray-300 focus:ring-red-500">
                        <label for="delete_extra" class="ml-2 text-gray-700">
                            <span class="text-red-600 font-medium">Remove extra departments</span> not found in payroll system
                        </label>
                    </div>
                </div>
                
                <div class="flex space-x-3">
                    <button type="submit" class="inline-flex items-center px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition-colors">
                        <i class="fas fa-sync-alt mr-2"></i>
                        Start Comprehensive Sync
                    </button>
                </div>
            </form>
        </div>
        
        <div class="p-4">
            <p class="text-sm text-gray-600">Manage and sync departments from the Payroll API</p>
        </div>
    </div>
    
    <div class="bg-white shadow rounded-lg">

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="bg-green-50 border-l-4 border-green-500 p-4 m-4">
            <div class="flex items-center">
                <i class="fas fa-check-circle text-green-500 mr-2"></i>
                <p class="text-green-800">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-50 border-l-4 border-red-500 p-4 m-4">
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle text-red-500 mr-2"></i>
                <p class="text-red-800">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    <div class="px-6 py-4">
        <div class="flex items-center mb-4">
            <i class="fas fa-sync-alt text-indigo-600 mr-2"></i>
            <h2 class="text-lg font-medium">Department Sync</h2>
        </div>
        
        <p class="mb-6 text-gray-700">Synchronize departments from the Payroll API to keep your local department database up to date.</p>
        
        <div class="grid grid-cols-2 gap-4 mb-6">
            <div class="bg-indigo-50 p-4 rounded-md border border-indigo-100">
                <div class="flex flex-col items-center">
                    <div class="text-indigo-600 text-2xl mb-1">
                        <i class="fas fa-building"></i>
                    </div>
                    <div class="text-sm text-gray-500">Total Departments</div>
                    <div class="text-2xl font-medium text-gray-900 mt-1">{{ $totalDepartments }}</div>
                </div>
            </div>
            <div class="bg-purple-50 p-4 rounded-md border border-purple-100">
                <div class="flex flex-col items-center">
                    <div class="text-purple-600 text-2xl mb-1">
                        <i class="fas fa-history"></i>
                    </div>
                    <div class="text-sm text-gray-500">Last Sync</div>
                    <div class="text-2xl font-medium text-gray-900 mt-1">
                        @if($latestActivity)
                            {{ $latestActivity['time']->diffForHumans() }}
                        @else
                            No activity
                        @endif
                    </div>
                </div>
            </div>
        </div>
        
        <div class="flex items-center mb-3">
            <i class="fas fa-history text-indigo-600 mr-2"></i>
            <h3 class="font-medium text-gray-800">Recently Synced Departments</h3>
        </div>
        
        @if($departments->count() > 0)
            <div class="border rounded-md overflow-hidden">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">API ID</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Updated</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($departments as $department)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2">
                                    <div class="text-sm font-medium text-gray-900">{{ $department->name }}</div>
                                </td>
                                <td class="px-4 py-2 text-sm text-gray-600">
                                    {{ Str::limit($department->description, 50) }}
                                </td>
                                <td class="px-4 py-2 text-sm text-gray-600">
                                    {{ $department->api_department_id ?? 'N/A' }}
                                </td>
                                <td class="px-4 py-2">
                                    @if($department->is_active)
                                        <span class="px-2 py-1 inline-flex text-xs font-medium rounded-full bg-green-100 text-green-800">
                                            Active
                                        </span>
                                    @else
                                        <span class="px-2 py-1 inline-flex text-xs font-medium rounded-full bg-red-100 text-red-800">
                                            Inactive
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-2 text-sm text-gray-500">
                                    {{ $department->updated_at->diffForHumans() }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="bg-gray-50 rounded-lg p-6 text-center">
                <div class="text-4xl text-gray-300 mb-2">
                    <i class="fas fa-building"></i>
                </div>
                <h3 class="text-gray-700 mb-1">No Departments Found</h3>
                <p class="text-sm text-gray-500 mb-3">There are no departments in the system yet. Use the comprehensive sync form above to sync departments from payroll.</p>
            </div>
        @endif
    </div>

    <!-- About Department Sync -->
    <div class="mt-4 bg-white rounded-lg shadow-sm">
        <div class="px-5 py-3 border-b border-gray-100">
            <h3 class="font-medium text-gray-800">
                <i class="fas fa-info-circle text-indigo-600 mr-2"></i> About Department Sync
            </h3>
        </div>
        <div class="p-4">
            <p class="text-sm text-gray-600 mb-3">The department sync feature allows you to synchronize department data from the external payroll system to your HRMS.</p>
            
            <div class="bg-blue-50 rounded p-3 border border-blue-100 mb-3">
                <h4 class="text-sm font-medium text-gray-700 mb-2">How Comprehensive Sync Works</h4>
                <ul class="space-y-1 text-xs text-gray-600">
                    <li class="flex items-start">
                        <i class="fas fa-check-circle text-indigo-600 mt-0.5 mr-1 text-xs"></i>
                        <span>Creates new departments found in payroll but not in attendance</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check-circle text-indigo-600 mt-0.5 mr-1 text-xs"></i>
                        <span>Updates existing departments when "Force update" is enabled</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-exclamation-triangle text-amber-600 mt-0.5 mr-1 text-xs"></i>
                        <span>Removes extra departments when "Remove extra" is enabled (if no employees assigned)</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-shield-alt text-green-600 mt-0.5 mr-1 text-xs"></i>
                        <span>Departments with assigned employees are protected from deletion</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
