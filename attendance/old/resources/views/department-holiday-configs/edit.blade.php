@extends('layouts.app')

@section('title', 'Edit Department Holiday Configuration - HRMS')
@section('page-title', 'Edit Department Holiday Configuration')

@section('content')
<div class="container mx-auto max-w-full px-4 py-6">
    <!-- Header with gradient background -->
    <div class="mb-8">
        <div class="flex items-center justify-between bg-gradient-to-r from-indigo-600 to-blue-600 px-8 py-10 rounded-lg shadow-lg">
            <div class="flex items-center space-x-4">
                <div class="w-16 h-16 bg-gradient-to-br from-indigo-500 to-blue-500 rounded-full flex items-center justify-center shadow-lg">
                    <i class="fas fa-cogs text-white text-2xl"></i>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-white">Edit Department Holiday Configuration</h1>
                    <p class="text-blue-100 mt-2">
                        Update holiday quota for {{ $departmentHolidayConfig->department ? $departmentHolidayConfig->department->name : 'Department Not Found' }}
                    </p>
                </div>
            </div>
            <a href="{{ route('holiday-department-configs.index') }}" 
               class="inline-flex items-center px-4 py-2 border border-white/30 rounded-md shadow-sm text-sm font-medium text-white bg-white/20 hover:bg-white/30 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to Configurations
            </a>
        </div>
    </div>

    <!-- Error Message -->
    @if(isset($errorMessage))
        <div class="mb-6 bg-red-50 border-l-4 border-red-400 p-4 rounded-r-md shadow-sm">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-circle text-red-400 text-lg"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-red-800">{{ $errorMessage }}</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Alert Messages -->
    @if ($errors->any())
        <div class="mb-6 bg-red-50 border border-red-200 rounded-md p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-circle text-red-400"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800">There were errors with your submission:</h3>
                    <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <!-- Sub-header with background -->
    <div class="bg-gray-100 p-4 rounded-md shadow-sm mb-6">
        <h2 class="text-xl font-semibold text-gray-800">Update holiday quota for {{ $departmentHolidayConfig->department ? $departmentHolidayConfig->department->name : 'Department Not Found' }}</h2>
    </div>

    <!-- Form -->
    <form method="POST" action="{{ route('holiday-department-configs.update', $departmentHolidayConfig) }}" class="bg-white shadow-sm rounded-lg overflow-hidden">
        @csrf
        @method('PUT')
        <div class="px-6 py-8 space-y-6">
            <!-- Basic Information -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center space-x-3">
                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-info-circle text-blue-600"></i>
                    </div>
                    <span>Configuration Details</span>
                </h3>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Department (Read-only) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Department</label>
                        <div class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50">
                            <div class="flex items-center">
                                <i class="fas fa-building text-gray-400 mr-2"></i>
                                @if($departmentHolidayConfig->department)
                                    <span class="text-gray-900">{{ $departmentHolidayConfig->department->name }} ({{ $departmentHolidayConfig->department->code }})</span>
                                @else
                                    <span class="text-red-600">Department Not Found (ID: {{ $departmentHolidayConfig->department_id }})</span>
                                @endif
                            </div>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Department cannot be changed after creation.</p>
                    </div>

                    <!-- Financial Year (Read-only) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Financial Year</label>
                        <div class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50">
                            <div class="flex items-center">
                                <i class="fas fa-calendar text-gray-400 mr-2"></i>
                                <span class="text-gray-900">FY {{ $departmentHolidayConfig->financial_year }}</span>
                            </div>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Financial year cannot be changed after creation.</p>
                    </div>

                    <!-- Total Public Holidays per Employee -->
                    <div>
                        <label for="allowed_holidays" class="block text-sm font-medium text-gray-700 mb-2">
                            Total Public Holidays per Employee <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="number" id="allowed_holidays" name="allowed_holidays" 
                                   value="{{ old('allowed_holidays', $departmentHolidayConfig->allowed_holidays) }}" 
                                   min="{{ $departmentHolidayConfig->used_holidays }}" max="50"
                                   class="w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 {{ $errors->has('allowed_holidays') ? 'border-red-500' : 'border-gray-300' }}"
                                   {{ !$departmentHolidayConfig->department ? 'disabled' : '' }}
                                   required>
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <i class="fas fa-calendar-alt text-gray-400"></i>
                            </div>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">
                            <i class="fas fa-info-circle mr-1"></i>
                            @if($departmentHolidayConfig->department)
                                Minimum: {{ $departmentHolidayConfig->used_holidays }} (already used), Maximum: 50
                            @else
                                Cannot edit - Department no longer exists
                            @endif
                        </p>
                        @error('allowed_holidays')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Calculation Status -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Calculation Status</label>
                        <div class="p-3 bg-blue-50 border border-blue-200 rounded-md">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-calculator text-blue-400"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-blue-800">
                                        <span id="calculation-display" class="font-medium">Enter values to see calculation</span>
                                        <br>
                                        <span class="text-xs text-blue-600">Fixed + Flexible holidays must equal the Total Public Holidays per Employee</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Fixed Public Holidays -->
                    <div>
                        <label for="fixed_public_holidays" class="block text-sm font-medium text-gray-700 mb-2">
                            Fixed Public Holidays <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="number" id="fixed_public_holidays" name="fixed_public_holidays" 
                                   value="{{ old('fixed_public_holidays', $departmentHolidayConfig->fixed_public_holidays ?? 0) }}" 
                                   min="0"
                                   class="w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 {{ $errors->has('fixed_public_holidays') ? 'border-red-500' : 'border-gray-300' }}"
                                   {{ !$departmentHolidayConfig->department ? 'disabled' : '' }}
                                   required>
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <i class="fas fa-calendar-check text-gray-400"></i>
                            </div>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">
                            <i class="fas fa-info-circle mr-1"></i>
                            Fixed holidays that all employees must observe (e.g., National holidays)
                        </p>
                        @error('fixed_public_holidays')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Flexible Public Holidays -->
                    <div>
                        <label for="flexible_public_holidays" class="block text-sm font-medium text-gray-700 mb-2">
                            Flexible Public Holidays <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="number" id="flexible_public_holidays" name="flexible_public_holidays" 
                                   value="{{ old('flexible_public_holidays', $departmentHolidayConfig->flexible_public_holidays ?? 0) }}" 
                                   min="0"
                                   class="w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 {{ $errors->has('flexible_public_holidays') ? 'border-red-500' : 'border-gray-300' }}"
                                   {{ !$departmentHolidayConfig->department ? 'disabled' : '' }}
                                   required>
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <i class="fas fa-calendar-plus text-gray-400"></i>
                            </div>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">
                            <i class="fas fa-info-circle mr-1"></i>
                            Flexible holidays that employees can choose (e.g., Regional/Religious holidays)
                        </p>
                        @error('flexible_public_holidays')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Status -->
                    <div>
                        <label for="is_active" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select id="is_active" name="is_active" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                {{ !$departmentHolidayConfig->department ? 'disabled' : '' }}>
                            <option value="1" {{ old('is_active', $departmentHolidayConfig->is_active) ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ !old('is_active', $departmentHolidayConfig->is_active) ? 'selected' : '' }}>Inactive</option>
                        </select>
                        <p class="mt-1 text-xs text-gray-500">
                            @if($departmentHolidayConfig->department)
                                Inactive configurations will not be available for new holiday assignments.
                            @else
                                Cannot edit - Department no longer exists
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <!-- Current Statistics -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center space-x-3">
                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-chart-bar text-green-600"></i>
                    </div>
                    <span>Current Statistics</span>
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-blue-50 rounded-lg p-4">
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                                <i class="fas fa-calendar text-blue-600"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-blue-700">Currently Allowed</p>
                                <p class="text-2xl font-bold text-blue-900">{{ $departmentHolidayConfig->allowed_holidays }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-orange-50 rounded-lg p-4">
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-orange-100 rounded-full flex items-center justify-center mr-3">
                                <i class="fas fa-calendar-check text-orange-600"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-orange-700">Used Holidays</p>
                                <p class="text-2xl font-bold text-orange-900">{{ $departmentHolidayConfig->used_holidays }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-green-50 rounded-lg p-4">
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center mr-3">
                                <i class="fas fa-calendar-plus text-green-600"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-green-700">Remaining</p>
                                <p class="text-2xl font-bold text-green-900">{{ $departmentHolidayConfig->remaining_holidays }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Warning Message -->
            @if($departmentHolidayConfig->used_holidays > 0)
                <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                        </div>
                        <div class="ml-3">
                            <h4 class="text-sm font-medium text-yellow-800">Important Notes</h4>
                            <div class="mt-2 text-sm text-yellow-700">
                                <ul class="list-disc list-inside">
                                    <li>This department has already used {{ $departmentHolidayConfig->used_holidays }} holidays</li>
                                    <li>You cannot set allowed holidays below the currently used count</li>
                                    <li>Reducing allowed holidays will affect future holiday assignments</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Form Actions -->
        <div class="bg-gray-50 px-6 py-4 flex items-center justify-between">
            <a href="{{ route('holiday-department-configs.show', $departmentHolidayConfig) }}" 
               class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <i class="fas fa-times mr-2"></i>
                Cancel
            </a>
            @if($departmentHolidayConfig->department)
                <button type="submit" 
                        class="inline-flex items-center px-6 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <i class="fas fa-save mr-2"></i>
                    Update Configuration
                </button>
            @else
                <button type="button" 
                        class="inline-flex items-center px-6 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-gray-400 cursor-not-allowed" 
                        disabled>
                    <i class="fas fa-ban mr-2"></i>
                    Cannot Update
                </button>
            @endif
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const totalInput = document.getElementById('allowed_holidays');
    const fixedInput = document.getElementById('fixed_public_holidays');
    const flexibleInput = document.getElementById('flexible_public_holidays');
    const calculationDisplay = document.getElementById('calculation-display');
    
    function updateCalculation() {
        const total = parseInt(totalInput.value) || 0;
        const fixed = parseInt(fixedInput.value) || 0;
        const flexible = parseInt(flexibleInput.value) || 0;
        const sum = fixed + flexible;
        
        calculationDisplay.textContent = `${fixed} + ${flexible} = ${sum}`;
        
        // Update styling based on validation
        const isValid = sum === total;
        const parent = calculationDisplay.closest('.bg-blue-50');
        
        if (isValid && total > 0) {
            parent.classList.remove('bg-red-50', 'border-red-200', 'bg-blue-50', 'border-blue-200');
            parent.classList.add('bg-green-50', 'border-green-200');
            calculationDisplay.classList.remove('text-red-800', 'text-blue-800');
            calculationDisplay.classList.add('text-green-800');
        } else if (!isValid && total > 0) {
            parent.classList.remove('bg-green-50', 'border-green-200', 'bg-blue-50', 'border-blue-200');
            parent.classList.add('bg-red-50', 'border-red-200');
            calculationDisplay.classList.remove('text-green-800', 'text-blue-800');
            calculationDisplay.classList.add('text-red-800');
        } else {
            parent.classList.remove('bg-green-50', 'border-green-200', 'bg-red-50', 'border-red-200');
            parent.classList.add('bg-blue-50', 'border-blue-200');
            calculationDisplay.classList.remove('text-green-800', 'text-red-800');
            calculationDisplay.classList.add('text-blue-800');
        }
    }
    
    // Auto-update flexible when total or fixed changes
    function autoUpdateFlexible() {
        const total = parseInt(totalInput.value) || 0;
        const fixed = parseInt(fixedInput.value) || 0;
        const remaining = total - fixed;
        
        if (remaining >= 0) {
            flexibleInput.value = remaining;
        }
        updateCalculation();
    }
    
    totalInput.addEventListener('input', autoUpdateFlexible);
    fixedInput.addEventListener('input', autoUpdateFlexible);
    flexibleInput.addEventListener('input', updateCalculation);
    
    // Initial calculation
    updateCalculation();
});
</script>
@endsection
