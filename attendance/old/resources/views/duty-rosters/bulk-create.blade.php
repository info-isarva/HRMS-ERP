@extends('layouts.app')

@section('title', 'Bulk Duty Roster Assignment - HRMS')

@section('page-title', 'Bulk Duty Roster Assignment')

@section('content')
<div class="p-6 space-y-6">
    <!-- Header card (gradient) -->
    <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-200">
        <div class="bg-gradient-to-r from-green-600 to-emerald-700 px-8 py-8">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                        <i class="fas fa-users text-white text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h1 class="text-2xl font-bold text-white">Bulk Duty Roster Assignment</h1>
                        <p class="text-green-100 text-sm mt-2">Assign shifts to multiple employees for date ranges</p>
                    </div>
                </div>

                <div class="hidden md:flex items-center">
                    <a href="{{ route('duty-rosters.index') }}" class="inline-flex items-center px-4 py-3 bg-white text-emerald-700 font-semibold rounded-lg shadow-md hover:bg-gray-100 transition">
                        <i class="fas fa-arrow-left mr-2"></i> Back to Duty Rosters
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
        <form action="{{ route('duty-rosters.bulk-store') }}" method="POST">
            @csrf

            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h2 class="text-lg font-semibold text-gray-900">Bulk Assignment Details</h2>
            </div>

            <div class="p-6 space-y-6">
                <!-- Step 1: Select Department (Optional) -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Step 1: Filter by Department (Optional)
                    </label>
                    <select id="department_filter"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors">
                        <option value="">All Departments</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->api_department_id }}">{{ $department->name }}</option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Select a department to filter employees, or leave empty for all</p>
                </div>

                <!-- Step 2: Select Employees -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Step 2: Select Employees <span class="text-red-500">*</span>
                    </label>
                    <div class="border border-gray-200 rounded-lg p-4 max-h-64 overflow-y-auto">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-sm text-gray-600">Select employees to assign:</span>
                            <div class="flex space-x-2">
                                <button type="button" id="select_all" class="text-xs bg-emerald-100 text-emerald-700 px-2 py-1 rounded hover:bg-emerald-200">
                                    Select All
                                </button>
                                <button type="button" id="select_none" class="text-xs bg-gray-100 text-gray-700 px-2 py-1 rounded hover:bg-gray-200">
                                    Select None
                                </button>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2" id="employees_list">
                            @foreach($employees as $employee)
                                <div class="employee-item flex items-center p-2 border border-gray-200 rounded {{ $employee->payroll_department_id ? 'department-' . $employee->payroll_department_id : '' }}">
                                    <input type="checkbox"
                                           id="emp_{{ $employee->payroll_id }}"
                                           name="employees[]"
                                           value="{{ $employee->payroll_id }}"
                                           class="mr-2 employee-checkbox">
                                    <label for="emp_{{ $employee->payroll_id }}" class="text-sm cursor-pointer flex-1">
                                        <div class="font-medium">{{ $employee->name }}</div>
                                        <div class="text-xs text-gray-500">{{ $employee->payroll_id }}</div>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @error('employees')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Step 3: Select Shift -->
                <div>
                    <label for="shift_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Step 3: Select Shift <span class="text-red-500">*</span>
                    </label>
                    <select id="shift_id"
                            name="shift_id"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors"
                            required>
                        <option value="">Choose a shift</option>
                        @foreach($shifts as $shift)
                            <option value="{{ $shift->id }}">
                                {{ $shift->name }} ({{ $shift->start_time }} - {{ $shift->end_time }})
                            </option>
                        @endforeach
                    </select>
                    @error('shift_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Step 4: Select Date Range -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">
                            Step 4: Start Date <span class="text-red-500">*</span>
                        </label>
                        <input type="date"
                               id="start_date"
                               name="start_date"
                               value="{{ old('start_date', date('Y-m-d')) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors"
                               required>
                        @error('start_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">
                            End Date <span class="text-red-500">*</span>
                        </label>
                        <input type="date"
                               id="end_date"
                               name="end_date"
                               value="{{ old('end_date', date('Y-m-d', strtotime('+6 days'))) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors"
                               required>
                        @error('end_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Assignment Preview -->
                <div id="assignment_preview" class="bg-emerald-50 rounded-lg p-4 hidden">
                    <h3 class="text-sm font-medium text-emerald-800 mb-2">Assignment Preview</h3>
                    <div id="preview_content" class="text-sm text-emerald-700"></div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="bg-gray-50 px-6 py-4 flex items-center justify-between">
                <a href="{{ route('duty-rosters.index') }}"
                   class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500">
                    <i class="fas fa-times mr-2"></i>
                    Cancel
                </a>
                <button type="submit"
                        class="inline-flex items-center px-6 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500">
                    <i class="fas fa-users mr-2"></i>
                    Assign Shifts
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const departmentFilter = document.getElementById('department_filter');
    const employeesList = document.getElementById('employees_list');
    const selectAllBtn = document.getElementById('select_all');
    const selectNoneBtn = document.getElementById('select_none');
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    const shiftSelect = document.getElementById('shift_id');
    const previewDiv = document.getElementById('assignment_preview');
    const previewContent = document.getElementById('preview_content');

    // Department filtering
    departmentFilter.addEventListener('change', function() {
        const selectedDept = this.value;
        const employeeItems = employeesList.querySelectorAll('.employee-item');

        employeeItems.forEach(item => {
            if (!selectedDept) {
                item.style.display = 'flex';
            } else {
                const itemDept = item.classList.contains('department-' + selectedDept);
                item.style.display = itemDept ? 'flex' : 'none';
            }
        });
    });

    // Select All / None functionality
    selectAllBtn.addEventListener('click', function() {
        const checkboxes = document.querySelectorAll('.employee-checkbox');
        checkboxes.forEach(cb => cb.checked = true);
        updatePreview();
    });

    selectNoneBtn.addEventListener('click', function() {
        const checkboxes = document.querySelectorAll('.employee-checkbox');
        checkboxes.forEach(cb => cb.checked = false);
        updatePreview();
    });

    // Update preview when selections change
    function updatePreview() {
        const selectedEmployees = document.querySelectorAll('.employee-checkbox:checked');
        const startDate = startDateInput.value;
        const endDate = endDateInput.value;
        const selectedShift = shiftSelect.options[shiftSelect.selectedIndex];

        if (selectedEmployees.length > 0 && startDate && endDate && selectedShift.value) {
            const start = new Date(startDate);
            const end = new Date(endDate);
            const daysDiff = Math.ceil((end - start) / (1000 * 60 * 60 * 24)) + 1;

            previewContent.innerHTML = `
                <strong>${selectedEmployees.length} employees</strong> will be assigned
                <strong>${selectedShift.text}</strong> for
                <strong>${daysDiff} days</strong> (${startDate} to ${endDate})<br>
                Total assignments: <strong>${selectedEmployees.length * daysDiff}</strong>
            `;
            previewDiv.classList.remove('hidden');
        } else {
            previewDiv.classList.add('hidden');
        }
    }

    // Add event listeners for preview updates
    document.querySelectorAll('.employee-checkbox').forEach(cb => {
        cb.addEventListener('change', updatePreview);
    });
    startDateInput.addEventListener('change', updatePreview);
    endDateInput.addEventListener('change', updatePreview);
    shiftSelect.addEventListener('change', updatePreview);
});
</script>
@endsection