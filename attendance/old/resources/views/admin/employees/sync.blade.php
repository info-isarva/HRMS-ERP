@extends('layouts.app')

@section('title', 'Employee Synchronization - HRMS')
@section('page-title', 'Employee Synchronization')

@section('content')
<div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 space-y-8 mb-8">
    <!-- Header Section -->
    <div class="bg-white rounded-lg shadow-sm mb-4 mt-4">
        <div class="px-5 py-4 border-b border-gray-100 flex justify-between items-center">
            <div class="flex items-center">
                <i class="fas fa-users text-2xl text-indigo-600 mr-3"></i>
                <h2 class="font-semibold text-xl text-gray-800">Employee Synchronization</h2>
            </div>
            <form action="{{ route('admin.employees.sync.sync') }}" method="POST">
                @csrf
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded font-medium flex items-center transition-colors">
                    <i class="fas fa-sync-alt mr-2"></i> Sync Employees
                </button>
            </form>
        </div>
        
        <div class="p-4">
            <p class="text-sm text-gray-600">Manage and sync employees from the Payroll API</p>
        </div>
    </div>
    
    <div class="bg-white shadow rounded-lg">

    <!-- Success/Error Messages -->
    @if (session('success'))
        <div class="bg-green-50 border-l-4 border-green-500 p-4 m-4">
            <div class="flex items-center">
                <i class="fas fa-check-circle text-green-500 mr-2"></i>
                <p class="text-green-800">{{ session('success') }}</p>
            </div>
        </div>
    @endif
    
    @if (session('error'))
        <div class="bg-red-50 border-l-4 border-red-500 p-4 m-4">
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle text-red-500 mr-2"></i>
                <p class="text-red-800">{{ session('error') }}</p>
            </div>
        </div>
    @endif
    
    <div class="px-6 py-4">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center">
                <i class="fas fa-sync-alt text-indigo-600 mr-2"></i>
                <h2 class="text-lg font-medium">Employee Sync</h2>
            </div>
            
            <div class="flex items-center">
                <!-- <label class="inline-flex items-center">
                    <input type="checkbox" name="force" value="1" class="form-checkbox h-4 w-4 text-indigo-600">
                    <span class="ml-2 text-sm text-gray-600">Force Update</span>
                </label> -->
            </div>
        </div>
                
                <p class="mb-6 text-gray-700">Synchronize employees from the Payroll API to keep your local employee database up to date.</p>
                
                <div class="grid grid-cols-3 gap-4 mb-6">
                    <div class="bg-blue-50 p-4 rounded-md border border-blue-100">
                        <div class="flex flex-col items-center">
                            <div class="text-blue-600 text-2xl mb-1">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="text-sm text-gray-500">Total Employees</div>
                            <div class="text-2xl font-medium text-gray-900 mt-1">{{ $totalEmployees }}</div>
                        </div>
                    </div>
                    <div class="bg-green-50 p-4 rounded-md border border-green-100">
                        <div class="flex flex-col items-center">
                            <div class="text-green-600 text-2xl mb-1">
                                <i class="fas fa-user"></i>
                            </div>
                            <div class="text-sm text-gray-500">Regular Employees</div>
                            <div class="text-2xl font-medium text-gray-900 mt-1">{{ $regularEmployees }}</div>
                        </div>
                    </div>
                    <div class="bg-purple-50 p-4 rounded-md border border-purple-100">
                        <div class="flex flex-col items-center">
                            <div class="text-purple-600 text-2xl mb-1">
                                <i class="fas fa-user-shield"></i>
                            </div>
                            <div class="text-sm text-gray-500">Admin Users</div>
                            <div class="text-2xl font-medium text-gray-900 mt-1">{{ $totalEmployees - $regularEmployees }}</div>
                        </div>
                    </div>
                </div>
                
                    <div class="flex items-center mb-3">
                        <i class="fas fa-history text-indigo-600 mr-2"></i>
                        <h3 class="font-medium text-gray-800">Recently Added/Updated Employees</h3>
                    </div>
                    
                    @if($employees->count() > 0)
                        <div class="border rounded-md overflow-hidden">
                            <table class="w-full">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach($employees as $employee)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-2">
                                            <div class="flex items-center">
                                                <div class="w-8 h-8 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center">
                                                    {{ strtoupper(substr($employee->name, 0, 2)) }}
                                                </div>
                                                <div class="ml-3">
                                                    <div class="text-sm font-medium text-gray-900">{{ $employee->name }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-2 text-sm text-gray-600">{{ $employee->email }}</td>
                                        <td class="px-4 py-2 text-sm text-gray-600">{{ $employee->department ? $employee->department->name : 'N/A' }}</td>
                                        <td class="px-4 py-2">
                                            @if($employee->role == 'super_admin')
                                                <span class="px-2 py-1 inline-flex text-xs font-medium rounded-full bg-red-100 text-red-800">
                                                    Super Admin
                                                </span>
                                            @elseif($employee->role == 'admin')
                                                <span class="px-2 py-1 inline-flex text-xs font-medium rounded-full bg-blue-100 text-blue-800">
                                                    Admin
                                                </span>
                                            @else
                                                <span class="px-2 py-1 inline-flex text-xs font-medium rounded-full bg-green-100 text-green-800">
                                                    Employee
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-2 text-sm text-gray-500">
                                            {{ $employee->created_at->format('d M Y') }}
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="bg-gray-50 rounded-lg p-6 text-center">
                            <div class="text-4xl text-gray-300 mb-2">
                                <i class="fas fa-users"></i>
                            </div>
                            <h3 class="text-gray-700 mb-1">No Employees Found</h3>
                            <p class="text-sm text-gray-500">There are no employees in the system yet.</p>
                        </div>
                    @endif
                </div>
            </div>
        
            <div class="mt-4 bg-white rounded-lg shadow-sm">
                <div class="px-5 py-3 border-b border-gray-100">
                    <h3 class="font-medium text-gray-800">
                        <i class="fas fa-info-circle text-indigo-600 mr-2"></i> Synchronization Info
                    </h3>
                </div>
                
                <div class="p-4 space-y-4">
                    <div>
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Process Details</h4>
                        <ul class="space-y-1 pl-5 list-disc text-xs text-gray-600">
                            <li class="ml-3">Adds new employees from the Payroll API</li>
                            <li class="ml-3">Updates existing employees with latest information</li>
                            <li class="ml-3">Preserves admin and super_admin users</li>
                            <li class="ml-3">Sets department based on API data</li>
                            <li class="ml-3">Sets default reporting manager ID to 1 if not specified</li>
                        </ul>
                    </div>
                    
                    @if($latestActivity)
                    <div>
                        <h4 class="text-sm font-medium text-gray-700 mb-2 flex items-center">
                            <i class="fas fa-history text-indigo-600 mr-1"></i>
                            Last Sync Activity
                        </h4>
                        <div class="bg-blue-50 border-l-4 border-blue-400 p-3 rounded-r">
                            <p class="text-xs text-gray-700">{{ $latestActivity['message'] }}</p>
                            <p class="text-xs text-gray-500 mt-1 flex items-center">
                                <i class="far fa-clock mr-1"></i>
                                {{ $latestActivity['time']->diffForHumans() }}
                            </p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
