@extends('layouts.app')

@section('title', 'Edit Leave Type - HRMS')

@section('page-title', 'Edit Leave Type')

@section('content')
<div class="p-6 space-y-6">
    <!-- Header card (gradient) -->
    <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-200">
        <div class="bg-gradient-to-r from-blue-600 to-indigo-700 px-8 py-8">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                        <i class="fas fa-edit text-white text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h1 class="text-2xl font-bold text-white">Edit Leave Type</h1>
                        <p class="text-blue-100 text-sm mt-2">Update leave type details and department assignments</p>
                    </div>
                </div>

                <div class="hidden md:flex items-center">
                    <a href="{{ route('leave-types.index') }}" class="inline-flex items-center px-4 py-3 bg-white text-indigo-700 font-semibold rounded-lg shadow-md hover:bg-gray-100 transition">
                        <i class="fas fa-arrow-left mr-2"></i> Back to Leave Types
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Error Messages -->
    @if($errors->any())
        <div class="bg-red-50 border border-red-200 rounded-md p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-circle text-red-400"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800">There were errors with your submission:</h3>
                    <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <!-- Form -->
    <div class="bg-white shadow-lg rounded-xl overflow-hidden border border-gray-200">
        <form action="{{ route('leave-types.update', $leaveType) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h2 class="text-lg font-semibold text-gray-900">Leave Type Information</h2>
            </div>

            <div class="p-6 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                            Leave Type Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="name" 
                               name="name" 
                               value="{{ old('name', $leaveType->name) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                               placeholder="e.g., Casual Leave"
                               required>
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Code -->
                    <div>
                        <label for="code" class="block text-sm font-medium text-gray-700 mb-2">
                            Leave Code <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="code" 
                               name="code" 
                               value="{{ old('code', $leaveType->code) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                               placeholder="e.g., CL"
                               maxlength="10"
                               required>
                        @error('code')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Days Count -->
                    <div>
                        <label for="days_count" class="block text-sm font-medium text-gray-700 mb-2">
                            Days Allowed <span class="text-red-500">*</span>
                        </label>
                        <input type="number" 
                               id="days_count" 
                               name="days_count" 
                               value="{{ old('days_count', $leaveType->days_count) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                               placeholder="e.g., 10"
                               min="1"
                               max="365"
                               required>
                        @error('days_count')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Status -->
                    <div>
                        <label for="is_active" class="block text-sm font-medium text-gray-700 mb-2">
                            Status
                        </label>
                        <select id="is_active" 
                                name="is_active" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors">
                            <option value="1" {{ old('is_active', $leaveType->is_active) ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ !old('is_active', $leaveType->is_active) ? 'selected' : '' }}>Inactive</option>
                        </select>
                        @error('is_active')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Financial Year (Read-only) -->
                <div>
                    <label for="financial_year" class="block text-sm font-medium text-gray-700 mb-2">
                        Financial Year
                    </label>
                    <input type="text" 
                           id="financial_year" 
                           value="{{ $leaveType->financial_year }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-500"
                           readonly>
                    <p class="mt-1 text-xs text-gray-500">Financial year cannot be changed after creation</p>
                </div>

                <!-- Description -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                        Description
                    </label>
                    <textarea id="description" 
                              name="description" 
                              rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                              placeholder="Enter description for this leave type...">{{ old('description', $leaveType->description) }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Departments Selection -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Assign to Departments <span class="text-red-500">*</span>
                    </label>
                    <p class="text-sm text-gray-500 mb-4">Select departments where this leave type will be available</p>
                    
                    @if($departments->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 max-h-64 overflow-y-auto border border-gray-200 rounded-lg p-4">
                            @foreach($departments as $department)
                                @php
                                    $isChecked = in_array($department->id, old('departments', $leaveType->departments->pluck('id')->toArray()));
                                @endphp
                                <div class="department-item">
                                    <input type="checkbox" 
                                           id="dept_{{ $department->id }}" 
                                           name="departments[]" 
                                           value="{{ $department->id }}"
                                           class="sr-only"
                                           {{ $isChecked ? 'checked' : '' }}>
                                    <label for="dept_{{ $department->id }}" 
                                           class="flex items-center p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 hover:border-blue-300 transition-all duration-200">
                                        <div class="flex items-center flex-1">
                                            <div class="w-3 h-3 bg-white border-2 border-gray-300 rounded-sm mr-3 flex-shrink-0 transition-colors"></div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-medium text-gray-900 truncate">{{ $department->name }}</p>
                                                <p class="text-xs text-gray-500">{{ $department->code }}</p>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8 border border-gray-200 rounded-lg bg-gray-50">
                            <i class="fas fa-exclamation-triangle text-yellow-500 text-2xl mb-2"></i>
                            <p class="text-gray-600">No departments available. Please sync departments first.</p>
                        </div>
                    @endif
                    
                    @error('departments')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Form Actions -->
            <div class="bg-gray-50 px-6 py-4 flex items-center justify-between">
                <a href="{{ route('leave-types.index') }}" 
                   class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <i class="fas fa-times mr-2"></i>
                    Cancel
                </a>
                <button type="submit" 
                        class="inline-flex items-center px-6 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <i class="fas fa-save mr-2"></i>
                    Update Leave Type
                </button>
            </div>
        </form>
    </div>
</div>

<style>
/* Department selection styling */
.department-item input[type="checkbox"]:checked + label {
    background: linear-gradient(to right, #eff6ff, #f0f9ff);
    border-color: #3b82f6;
}

.department-item input[type="checkbox"]:checked + label .w-3 {
    background-color: #3b82f6;
    border-color: #3b82f6;
}

.department-item input[type="checkbox"]:checked + label .w-3::after {
    content: '';
    display: block;
    width: 4px;
    height: 8px;
    border: solid white;
    border-width: 0 2px 2px 0;
    transform: rotate(45deg);
    margin: -1px 0 0 1px;
}
</style>
@endsection
