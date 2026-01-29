@extends('layouts.app')

@section('content')
<div class="container mx-auto py-8">
    <h1 class="text-2xl font-bold mb-6">Database Management</h1>
    
    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif
    
    @if (session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Database Statistics</h2>
            <div class="space-y-2">
                <div class="flex justify-between">
                    <span>Users:</span>
                    <span class="font-bold">{{ $stats['users'] }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Departments:</span>
                    <span class="font-bold">{{ $stats['departments'] }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Leave Applications:</span>
                    <span class="font-bold">{{ $stats['leave_applications'] }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Public Holidays:</span>
                    <span class="font-bold">{{ $stats['public_holidays'] }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Holiday Applications:</span>
                    <span class="font-bold">{{ $stats['public_holiday_applications'] }}</span>
                </div>
            </div>
            
            @if ($latestActivity)
            <div class="mt-4 pt-4 border-t">
                <h3 class="text-md font-semibold">Latest System Activity</h3>
                <p class="text-sm text-gray-600">{{ $latestActivity['message'] }}</p>
                <p class="text-xs text-gray-500">{{ $latestActivity['time']->diffForHumans() }}</p>
            </div>
            @endif
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Management Actions</h2>
            
            <div class="space-y-4">
                <div class="bg-gray-100 p-4 rounded-lg">
                    <h3 class="font-medium mb-2">Flush Database</h3>
                    <p class="text-sm text-gray-600 mb-3">
                        This will delete all data except admin users. Use this to clear the database for testing or reset purposes.
                    </p>
                    <form action="{{ route('admin.database.flush') }}" method="POST" onsubmit="return confirm('Are you sure you want to flush the database? This will remove all data except admin users.')">
                        @csrf
                        <button type="submit" class="bg-red-500 hover:bg-red-600 text-white py-2 px-4 rounded">
                            Flush Database
                        </button>
                    </form>
                </div>
                
                <div class="bg-gray-100 p-4 rounded-lg">
                    <h3 class="font-medium mb-2">Flush and Sync from API</h3>
                    <p class="text-sm text-gray-600 mb-3">
                        This will clear all data except admin users and then sync fresh departments from the API.
                    </p>
                    <form action="{{ route('admin.database.flush-sync') }}" method="POST" onsubmit="return confirm('Are you sure you want to flush the database and sync from API? This will remove all data except admin users.')">
                        @csrf
                        <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded">
                            Flush and Sync
                        </button>
                    </form>
                </div>
                
                <div class="bg-gray-100 p-4 rounded-lg">
                    <h3 class="font-medium mb-2">Only Sync Departments</h3>
                    <p class="text-sm text-gray-600 mb-3">
                        Sync departments from the API without flushing the database.
                    </p>
                    <a href="{{ route('admin.departments.sync') }}" 
                       class="inline-block bg-green-500 hover:bg-green-600 text-white py-2 px-4 rounded">
                        Sync Departments
                    </a>
                </div>
                
                <div class="bg-gray-100 p-4 rounded-lg">
                    <h3 class="font-medium mb-2">Only Sync Employees</h3>
                    <p class="text-sm text-gray-600 mb-3">
                        Sync employees from the API without flushing the database.
                    </p>
                    <form action="{{ route('admin.database.sync-employees') }}" method="POST">
                        @csrf
                        <button type="submit" class="bg-indigo-500 hover:bg-indigo-600 text-white py-2 px-4 rounded">
                            Sync Employees
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
