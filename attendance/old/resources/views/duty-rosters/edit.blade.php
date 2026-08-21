@extends('layouts.app')

@section('title', 'Edit Duty Roster - HRMS')

@section('page-title', 'Edit Duty Roster')

@section('content')
<div class="p-6 space-y-6">
    <!-- Header card (gradient) -->
    <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-200">
        <div class="bg-gradient-to-r from-blue-600 to-indigo-700 px-8 py-8">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                        <i class="fas fa-calendar-edit text-white text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h1 class="text-2xl font-bold text-white">Edit Duty Roster</h1>
                        <p class="text-blue-100 text-sm mt-2">Update employee shift assignment</p>
                    </div>
                </div>

                <div class="hidden md:flex items-center">
                    <a href="{{ route('duty-rosters.index') }}" class="inline-flex items-center px-4 py-3 bg-white text-indigo-700 font-semibold rounded-lg shadow-md hover:bg-gray-100 transition">
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
        <form action="{{ route('duty-rosters.update', $dutyRoster) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h2 class="text-lg font-semibold text-gray-900">Duty Roster Information</h2>
            </div>

            <div class="p-6 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Employee -->
                    <div>
                        <label for="employee_payroll_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Employee <span class="text-red-500">*</span>
                        </label>
                        <select id="employee_payroll_id"
                                name="employee_payroll_id"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                                required>
                            <option value="">Select Employee</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->payroll_id }}" {{ old('employee_payroll_id', $dutyRoster->employee_payroll_id) == $employee->payroll_id ? 'selected' : '' }}>
                                    {{ $employee->name }} ({{ $employee->payroll_id }})
                                </option>
                            @endforeach
                        </select>
                        @error('employee_payroll_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Shift -->
                    <div>
                        <label for="shift_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Shift <span class="text-red-500">*</span>
                        </label>
                        <select id="shift_id"
                                name="shift_id"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                                required>
                            <option value="">Select Shift</option>
                            @foreach($shifts as $shift)
                                <option value="{{ $shift->id }}" {{ old('shift_id', $dutyRoster->shift_id) == $shift->id ? 'selected' : '' }}>
                                    {{ $shift->name }} ({{ $shift->start_time }} - {{ $shift->end_time }})
                                </option>
                            @endforeach
                        </select>
                        @error('shift_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Date -->
                    <div>
                        <label for="date" class="block text-sm font-medium text-gray-700 mb-2">
                            Date <span class="text-red-500">*</span>
                        </label>
                        <input type="date"
                               id="date"
                               name="date"
                               value="{{ old('date', $dutyRoster->date) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                               required>
                        @error('date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Shift Preview -->
                <div id="shift-preview" class="bg-gray-50 rounded-lg p-4 {{ $dutyRoster->shift ? '' : 'hidden' }}">
                    <h3 class="text-sm font-medium text-gray-700 mb-2">Shift Details</h3>
                    <div id="shift-details" class="text-sm text-gray-600">
                        @if($dutyRoster->shift)
                            Time: {{ $dutyRoster->shift->start_time }} - {{ $dutyRoster->shift->end_time }}
                        @endif
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="bg-gray-50 px-6 py-4 flex items-center justify-between">
                <a href="{{ route('duty-rosters.index') }}"
                   class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <i class="fas fa-times mr-2"></i>
                    Cancel
                </a>
                <button type="submit"
                        class="inline-flex items-center px-6 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <i class="fas fa-save mr-2"></i>
                    Update Duty Roster
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('shift_id').addEventListener('change', function() {
    const shiftId = this.value;
    const preview = document.getElementById('shift-preview');
    const details = document.getElementById('shift-details');

    if (shiftId) {
        // Find the selected shift option
        const option = this.options[this.selectedIndex];
        const text = option.text;
        const timeMatch = text.match(/\(([^)]+)\)/);

        if (timeMatch) {
            details.textContent = `Time: ${timeMatch[1]}`;
            preview.classList.remove('hidden');
        }
    } else {
        preview.classList.add('hidden');
    }
});
</script>
@endsection