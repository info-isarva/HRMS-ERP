@extends('layouts.app')

@section('title', 'Add Department Holiday Configuration - HRMS')
@section('page-title', 'Add Department Holiday Configuration')

@section('content')
<div class="container mx-auto max-w-full px-4 py-6">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between bg-gradient-to-r from-indigo-600 to-blue-600 px-8 py-10 rounded-lg shadow-lg">
            <div class="flex items-center space-x-4">
                <div class="w-16 h-16 bg-gradient-to-br from-indigo-500 to-blue-500 rounded-full flex items-center justify-center shadow-lg">
                    <i class="fas fa-cogs text-white text-2xl"></i>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-white">Add Department Holiday Configuration</h1>
                    <p class="text-blue-100 mt-2">Configure holiday quota for a department</p>
                </div>
            </div>
            <a href="{{ route('holiday-department-configs.index') }}" class="inline-flex items-center px-4 py-2 border border-white/30 rounded-md shadow-sm text-sm font-medium text-white bg-white/20 hover:bg-white/30 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to Configurations
            </a>
        </div>
    </div>

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

    <!-- Form -->
    <form method="POST" action="{{ route('holiday-department-configs.store') }}" class="bg-white shadow-sm rounded-lg overflow-hidden">
        @csrf
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
                    <!-- Department -->
                    <div>
                        <label for="department_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Department <span class="text-red-500">*</span>
                        </label>
                        <select id="department_id" name="department_id" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('department_id') border-red-500 @enderror"
                                required>
                            <option value="">Select Department</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}" {{ old('department_id') == $department->id ? 'selected' : '' }}>
                                    {{ $department->name }} ({{ $department->code }})
                                </option>
                            @endforeach
                        </select>
                        @error('department_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Financial Year -->
                    <div>
                        <label for="financial_year" class="block text-sm font-medium text-gray-700 mb-2">
                            Financial Year <span class="text-red-500">*</span>
                        </label>
                        <select id="financial_year" name="financial_year" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('financial_year') border-red-500 @enderror"
                                required>
                            @foreach($financialYears as $year => $label)
                                <option value="{{ $year }}" {{ old('financial_year', $currentYear) == $year ? 'selected' : '' }}>
                                    FY {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('financial_year')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Total Public Holidays per Employee -->
                    <div>
                        <label for="allowed_holidays" class="block text-sm font-medium text-gray-700 mb-2">
                            Total Public Holidays per Employee <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="number" id="allowed_holidays" name="allowed_holidays" 
                                   value="{{ old('allowed_holidays', 15) }}" 
                                   min="0" max="50"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('allowed_holidays') border-red-500 @enderror"
                                   placeholder="Enter total number of holidays per employee" required>
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <i class="fas fa-calendar-alt text-gray-400"></i>
                            </div>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">
                            <i class="fas fa-info-circle mr-1"></i>
                            This is the total number of public holidays each employee in this department can receive per financial year.
                        </p>
                        @error('allowed_holidays')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Validation Info -->
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
                                   value="{{ old('fixed_public_holidays', 8) }}" 
                                   min="0"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('fixed_public_holidays') border-red-500 @enderror"
                                   placeholder="Enter number of fixed holidays" required>
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
                                   value="{{ old('flexible_public_holidays', 7) }}" 
                                   min="0"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('flexible_public_holidays') border-red-500 @enderror"
                                   placeholder="Enter number of flexible holidays" required>
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
                </div>
            </div>

            <!-- Additional Information -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center space-x-3">
                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-info-circle text-green-600"></i>
                    </div>
                    <span>Additional Information</span>
                </h3>
                <div class="bg-blue-50 border border-blue-200 rounded-md p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-info-circle text-blue-400"></i>
                        </div>
                        <div class="ml-3">
                            <h4 class="text-sm font-medium text-blue-800">Configuration Notes</h4>
                            <ul class="mt-2 text-sm text-blue-700 list-disc list-inside">
                                <li>This sets the maximum public holidays each employee in the department can receive</li>
                                <li>Fixed holidays are mandatory for all employees (e.g., National holidays)</li>
                                <li>Flexible holidays can be chosen by employees based on their preferences</li>
                                <li>Fixed + Flexible must equal Total Public Holidays per Employee</li>
                                <li>When you assign a public holiday to this department, it counts against the respective quota</li>
                                <li>Public holidays assigned will be automatically tracked per employee</li>
                                <li>You can modify the configuration later if needed</li>
                                <li>The configuration will be active by default</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="bg-gray-50 px-6 py-4 flex items-center justify-between">
            <a href="{{ route('holiday-department-configs.index') }}" 
               class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <i class="fas fa-times mr-2"></i>
                Cancel
            </a>
            <button type="submit" 
                    class="inline-flex items-center px-6 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <i class="fas fa-plus mr-2"></i>
                Create Configuration
            </button>
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
        
        if (total === 0 && fixed === 0 && flexible === 0) {
            calculationDisplay.textContent = 'Enter values to see calculation';
        } else {
            calculationDisplay.textContent = `${fixed} + ${flexible} = ${sum}`;
        }
        
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
