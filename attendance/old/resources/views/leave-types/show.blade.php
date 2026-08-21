@extends('layouts.app')

@section('title', 'Leave Type Details - HRMS')

@section('page-title', 'Leave Type Details')

@section('content')
<div class="p-6 space-y-6">
    <!-- Header card (gradient) -->
    <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-200">
        <div class="bg-gradient-to-r from-blue-600 to-indigo-700 px-8 py-8">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                        <i class="fas fa-calendar-alt text-white text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h1 class="text-2xl font-bold text-white">{{ $leaveType->name }}</h1>
                        <p class="text-blue-100 text-sm mt-2">Leave type details and assigned departments</p>
                    </div>
                </div>

                <div class="hidden md:flex items-center space-x-3">
                    <a href="{{ route('leave-types.edit', $leaveType) }}" class="inline-flex items-center px-4 py-3 bg-white text-indigo-700 font-semibold rounded-lg shadow-md hover:bg-gray-100 transition">
                        <i class="fas fa-edit mr-2"></i> Edit Leave Type
                    </a>
                    <a href="{{ route('leave-types.index') }}" class="inline-flex items-center px-4 py-3 border border-white text-white font-semibold rounded-lg shadow-md hover:bg-white hover:text-indigo-700 transition">
                        <i class="fas fa-arrow-left mr-2"></i> Back to Leave Types
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Details -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Basic Information -->
            <div class="bg-white shadow-lg rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-indigo-50 to-purple-50">
                    <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-info-circle text-indigo-600 mr-2"></i>
                        Basic Information
                    </h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Leave Type Name</label>
                            <p class="text-lg font-semibold text-gray-900">{{ $leaveType->name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Leave Code</label>
                            <p class="text-lg font-semibold text-gray-900">{{ $leaveType->code }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Days Allowed</label>
                            <p class="text-lg font-semibold text-indigo-600">{{ $leaveType->days_count }} days</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Financial Year</label>
                            <p class="text-lg font-semibold text-gray-900">{{ $leaveType->financial_year }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Status</label>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $leaveType->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                <i class="fas fa-circle text-xs mr-2"></i>
                                {{ $leaveType->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Created</label>
                            <p class="text-sm text-gray-700">{{ $leaveType->created_at->format('M d, Y \a\t g:i A') }}</p>
                        </div>
                    </div>
                    
                    @if($leaveType->description)
                        <div class="mt-6">
                            <label class="block text-sm font-medium text-gray-500 mb-2">Description</label>
                            <p class="text-gray-700 leading-relaxed">{{ $leaveType->description }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Assigned Departments -->
            <div class="bg-white shadow-lg rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-indigo-50">
                    <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-building text-blue-600 mr-2"></i>
                        Assigned Departments
                        <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            {{ $leaveType->departments->count() }}
                        </span>
                    </h2>
                </div>
                <div class="p-6">
                    @if($leaveType->departments->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($leaveType->departments as $department)
                                <div class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                                    <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-building text-white text-sm"></i>
                                    </div>
                                    <div class="ml-3 flex-1">
                                        <p class="text-sm font-medium text-gray-900">{{ $department->name }}</p>
                                        <p class="text-xs text-gray-500">{{ $department->code }}</p>
                                        @if($department->description)
                                            <p class="text-xs text-gray-400 mt-1">{{ Str::limit($department->description, 50) }}</p>
                                        @endif
                                    </div>
                                    <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium {{ $department->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $department->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <i class="fas fa-building text-gray-400 text-3xl mb-4"></i>
                            <p class="text-gray-500">No departments assigned to this leave type</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="lg:col-span-1 space-y-6">
            <!-- Quick Stats -->
            <div class="bg-white shadow-lg rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-green-50 to-emerald-50">
                    <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-chart-bar text-green-600 mr-2"></i>
                        Quick Stats
                    </h2>
                </div>
                <div class="p-6 space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Departments Assigned</span>
                        <span class="text-lg font-semibold text-gray-900">{{ $leaveType->departments->count() }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Total Applications</span>
                        <span class="text-lg font-semibold text-gray-900">{{ $leaveType->leaveApplications->count() }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Days per Employee</span>
                        <span class="text-lg font-semibold text-indigo-600">{{ $leaveType->days_count }}</span>
                    </div>
                </div>
            </div>

            <!-- Recent Applications -->
            <div class="bg-white shadow-lg rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-purple-50 to-pink-50">
                    <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-clock text-purple-600 mr-2"></i>
                        Recent Applications
                    </h2>
                </div>
                <div class="p-6">
                    @if($leaveType->leaveApplications->count() > 0)
                        <div class="space-y-3">
                            @foreach($leaveType->leaveApplications->take(5) as $application)
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 truncate">{{ $application->user->name }}</p>
                                        <p class="text-xs text-gray-500">{{ $application->start_date->format('M d') }} - {{ $application->end_date->format('M d, Y') }}</p>
                                    </div>
                                    <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium 
                                        {{ $application->status === 'approved' ? 'bg-green-100 text-green-800' : 
                                           ($application->status === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                        {{ ucfirst($application->status) }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                        
                        @if($leaveType->leaveApplications->count() > 5)
                            <div class="mt-4 text-center">
                                <p class="text-sm text-gray-500">{{ $leaveType->leaveApplications->count() - 5 }} more applications...</p>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-6">
                            <i class="fas fa-calendar-times text-gray-400 text-2xl mb-2"></i>
                            <p class="text-sm text-gray-500">No applications yet</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Actions -->
            <div class="bg-white shadow-lg rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-gray-100">
                    <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-cogs text-gray-600 mr-2"></i>
                        Actions
                    </h2>
                </div>
                <div class="p-6 space-y-3">
                    <a href="{{ route('leave-types.edit', $leaveType) }}" 
                       class="w-full inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <i class="fas fa-edit mr-2"></i>
                        Edit Leave Type
                    </a>
                    
                    @if($leaveType->leaveApplications->count() == 0)
                        <form action="{{ route('leave-types.destroy', $leaveType) }}" 
                              method="POST" 
                              onsubmit="return confirm('Are you sure you want to delete this leave type? This action cannot be undone.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="w-full inline-flex items-center justify-center px-4 py-2 border border-red-300 rounded-md shadow-sm text-sm font-medium text-red-700 bg-red-50 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                <i class="fas fa-trash mr-2"></i>
                                Delete Leave Type
                            </button>
                        </form>
                    @else
                        <div class="text-center p-3 bg-gray-50 rounded-md">
                            <i class="fas fa-lock text-gray-400 mb-1"></i>
                            <p class="text-xs text-gray-500">Cannot delete - has applications</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
