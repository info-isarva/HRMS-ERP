@extends('layouts.app')

@section('title', 'Add Manual Punch - HRMS')

@section('page-title', 'Add Manual Punch')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50">
    <div class="mx-auto p-6 space-y-6">
        <!-- Header -->
        <div class="bg-white/80 backdrop-blur-sm overflow-hidden shadow-xl rounded-2xl border border-white/20">
            <div class="bg-gradient-to-r from-indigo-600 via-purple-600 to-blue-600 px-8 py-12 relative overflow-hidden">
                <div class="absolute inset-0 bg-black/10"></div>
                <div class="absolute -top-4 -right-4 w-24 h-24 bg-white/10 rounded-full"></div>
                <div class="absolute -bottom-4 -left-4 w-32 h-32 bg-white/10 rounded-full"></div>

                <div class="relative flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-16 h-16 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center border border-white/30">
                                <i class="fas fa-plus text-white text-2xl"></i>
                            </div>
                        </div>
                        <div class="ml-6">
                            <h1 class="text-3xl font-bold text-white mb-2">Add Manual Punch</h1>
                            <p class="text-indigo-100 text-lg">
                                Add missing punch in/out for employee(s)
                            </p>
                        </div>
                    </div>

                    <div class="hidden md:flex items-center">
                        <a href="{{ route('manual-punches.index') }}" class="inline-flex items-center px-6 py-3 bg-white/20 backdrop-blur-sm text-white font-semibold rounded-xl shadow-lg hover:bg-white/30 transition-all duration-300 border border-white/30">
                            <i class="fas fa-arrow-left mr-2"></i> Back to List
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Error Messages -->
        @if($errors->any())
            <div class="bg-red-50/80 backdrop-blur-sm border border-red-200/50 rounded-2xl p-6 shadow-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-red-100 rounded-xl flex items-center justify-center">
                            <i class="fas fa-exclamation-circle text-red-600"></i>
                        </div>
                    </div>
                    <div class="ml-4">
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

        <!-- Workflow Info Alert -->
        <div class="bg-blue-50/80 backdrop-blur-sm border-2 border-blue-300 rounded-2xl p-6 shadow-lg">
            <div class="flex">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                        <i class="fas fa-info-circle text-blue-600 text-xl"></i>
                    </div>
                </div>
                <div class="ml-4 flex-1">
                    <h3 class="text-lg font-bold text-blue-900 mb-2 flex items-center">
                        <i class="fas fa-lightbulb mr-2 text-yellow-500"></i>
                        Important: Manual Punch Workflow
                    </h3>
                    <p class="text-sm text-blue-800 mb-3">
                        Manual punches must be created <strong>before</strong> importing biometric data. Follow this order:
                    </p>
                    <ol class="text-sm text-blue-800 space-y-2 list-decimal list-inside mb-3">
                        <li><strong>First:</strong> Create manual punches for employees with missing/incorrect punches</li>
                        <li><strong>Second:</strong> Import biometric data from device (system will merge manual + biometric)</li>
                        <li><strong>Third:</strong> Process attendance under Bulk Attendance</li>
                    </ol>
                    <div class="bg-blue-100/50 border-l-4 border-blue-500 p-3 rounded">
                        <p class="text-sm text-blue-900 flex items-start">
                            <i class="fas fa-exclamation-triangle mr-2 mt-0.5 text-yellow-600"></i>
                            <span><strong>Important:</strong> Once biometric data is imported for a month, manual punches cannot be added for that month.</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>


        <!-- Form -->
        <div class="bg-white/80 backdrop-blur-sm shadow-xl rounded-2xl overflow-hidden border border-white/20">
            <div class="px-8 py-6 border-b border-gray-200/50 bg-gradient-to-r from-gray-50/50 to-blue-50/30">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center">
                        <i class="fas fa-user-clock text-white"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">Punch Information</h2>
                        <p class="text-gray-600">Select employee(s) and enter punch details</p>
                    </div>
                </div>
            </div>

            <form action="{{ route('manual-punches.store') }}" method="POST" class="p-8 space-y-8" id="manualPunchForm">
                @csrf

                <!-- Employee Selection -->
                <div class="space-y-2">
                    <label for="employee_payroll_ids" class="block text-sm font-semibold text-gray-700 flex items-center">
                        <i class="fas fa-users mr-2 text-indigo-500"></i>
                        Select Employee(s) <span class="text-red-500">*</span>
                    </label>
                    <select name="employee_payroll_ids[]" id="employee_payroll_ids" 
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500"
                            multiple required>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->payroll_id }}">
                                {{ $employee->name }} ({{ $employee->employee_id }})
                            </option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-500 mt-1">
                        <i class="fas fa-info-circle mr-1"></i>
                        Hold Ctrl (Windows) or Cmd (Mac) to select multiple employees
                    </p>
                </div>

                <!-- Date -->
                <div class="space-y-2">
                    <label for="date" class="block text-sm font-semibold text-gray-700 flex items-center">
                        <i class="fas fa-calendar mr-2 text-orange-500"></i>
                        Date <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="date" id="date" 
                           class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500"
                           required value="{{ old('date', now()->format('Y-m-d')) }}">
                </div>

                <!-- Shift Information Display -->
                <div id="shiftInfo" class="hidden p-6 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl border-2 border-blue-200">
                    <div class="flex items-center mb-3">
                        <i class="fas fa-clock text-indigo-600 text-lg mr-2"></i>
                        <h3 class="text-lg font-bold text-indigo-900">Assigned Shift</h3>
                    </div>
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <span class="text-xs text-gray-600 block">Shift Name</span>
                            <span id="shiftName" class="text-sm font-semibold text-gray-900">-</span>
                        </div>
                        <div>
                            <span class="text-xs text-gray-600 block">Start Time</span>
                            <span id="shiftStart" class="text-sm font-semibold text-green-700">-</span>
                        </div>
                        <div>
                            <span class="text-xs text-gray-600 block">End Time</span>
                            <span id="shiftEnd" class="text-sm font-semibold text-red-700">-</span>
                        </div>
                    </div>
                </div>

                <!-- Punch Times -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Punch In -->
                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-gray-700 flex items-center">
                            <i class="fas fa-sign-in-alt mr-2 text-green-500"></i>
                            Punch In Time
                        </label>
                        <div class="relative">
                            <input type="text"
                                   id="punch_in_time_display"
                                   readonly
                                   onclick="openClockPicker('punch_in_time')"
                                   class="w-full pl-4 pr-12 py-4 text-lg border-2 border-gray-200 rounded-xl focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all duration-300 bg-white cursor-pointer"
                                   placeholder="Click to select time">
                            <input type="hidden"
                                   id="punch_in_time"
                                   name="punch_in_time">
                            <div class="absolute right-4 top-1/2 transform -translate-y-1/2 pointer-events-none">
                                <i class="fas fa-clock text-gray-400"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Punch Out -->
                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-gray-700 flex items-center">
                            <i class="fas fa-sign-out-alt mr-2 text-red-500"></i>
                            Punch Out Time
                        </label>
                        <div class="relative">
                            <input type="text"
                                   id="punch_out_time_display"
                                   readonly
                                   onclick="openClockPicker('punch_out_time')"
                                   class="w-full pl-4 pr-12 py-4 text-lg border-2 border-gray-200 rounded-xl focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all duration-300 bg-white cursor-pointer"
                                   placeholder="Click to select time">
                            <input type="hidden"
                                   id="punch_out_time"
                                   name="punch_out_time">
                            <div class="absolute right-4 top-1/2 transform -translate-y-1/2 pointer-events-none">
                                <i class="fas fa-clock text-gray-400"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Reason -->
                <div class="space-y-2">
                    <label for="reason" class="block text-sm font-semibold text-gray-700 flex items-center">
                        <i class="fas fa-comment-alt mr-2 text-purple-500"></i>
                        Reason <span class="text-red-500">*</span>
                    </label>
                    <textarea id="reason"
                              name="reason"
                              rows="4"
                              class="w-full pl-4 pr-4 py-4 text-lg border-2 border-gray-200 rounded-xl focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all duration-300 bg-gray-50/50 resize-none"
                              placeholder="Enter reason for manual punch entry..."
                              required>{{ old('reason') }}</textarea>
                    <p class="text-xs text-gray-500">
                        <i class="fas fa-info-circle mr-1"></i>
                        Example: Biometric device malfunction, finger not detected, etc.
                    </p>
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-between pt-8 border-t border-gray-200/50">
                    <a href="{{ route('manual-punches.index') }}"
                       class="inline-flex items-center px-8 py-4 border-2 border-gray-300 rounded-xl shadow-sm text-sm font-semibold text-gray-700 bg-white/80 hover:bg-gray-50 transition-all duration-300 hover:scale-105">
                        <i class="fas fa-times mr-2"></i>
                        Cancel
                    </a>
                    <button type="submit"
                            class="inline-flex items-center px-8 py-4 border border-transparent rounded-xl shadow-lg text-sm font-semibold text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 transition-all duration-300 transform hover:scale-105">
                        <i class="fas fa-save mr-2"></i>
                        Save Manual Punch
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Clock Picker Modal (from shift master) -->
<div id="clockPickerModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full max-h-[90vh] overflow-hidden">
        <!-- Header -->
        <div class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white p-6">
            <div class="flex items-center justify-between">
                <h3 class="text-xl font-bold flex items-center">
                    <i class="fas fa-clock mr-3"></i>
                    Select Time
                </h3>
                <button onclick="closeClockPicker()" class="text-white hover:text-gray-200 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <div class="mt-4 text-center">
                <div id="selectedTimeDisplay" class="text-3xl font-bold">--:--</div>
                <div id="selectedPeriodDisplay" class="text-lg opacity-90">AM</div>
            </div>
        </div>

        <!-- Clock Face -->
        <div class="p-6">
            <div class="text-center mb-4">
                <p class="text-sm text-gray-600">Click outer ring for hours, inner area for minutes</p>
            </div>
            <div class="relative w-64 h-64 mx-auto mb-6">
                <!-- Clock Circle -->
                <div id="clockFace" class="w-full h-full rounded-full bg-gradient-to-br from-gray-50 to-gray-100 border-4 border-gray-200 relative cursor-pointer shadow-lg">
                    <!-- Center Dot -->
                    <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-4 h-4 bg-indigo-600 rounded-full z-10"></div>

                    <!-- Hour Markers -->
                    <div class="absolute inset-0">
                        @for($i = 0; $i < 12; $i++)
                            <div class="absolute w-1 h-6 bg-gray-400 transform -translate-x-1/2 -translate-y-1/2 origin-bottom"
                                 style="top: 16px; left: 50%; transform: rotate({{ $i * 30 }}deg) translateX(-50%) translateY(-50%); transform-origin: 50% 112px;">
                            </div>
                        @endfor
                    </div>

                    <!-- Minute Markers -->
                    <div class="absolute inset-0">
                        @for($i = 0; $i < 60; $i++)
                            @if($i % 5 != 0)
                                <div class="absolute w-0.5 h-2 bg-gray-300 transform -translate-x-1/2 -translate-y-1/2 origin-bottom"
                                     style="top: 12px; left: 50%; transform: rotate({{ $i * 6 }}deg) translateX(-50%) translateY(-50%); transform-origin: 50% 116px;">
                                </div>
                            @endif
                        @endfor
                    </div>

                    <!-- Hour Hand -->
                    <div id="hourHand" class="absolute top-1/2 left-1/2 w-1 bg-indigo-600 transform -translate-x-1/2 -translate-y-1/2 origin-bottom transition-transform duration-200" style="height: 40%;"></div>

                    <!-- Minute Hand -->
                    <div id="minuteHand" class="absolute top-1/2 left-1/2 w-1 bg-purple-600 transform -translate-x-1/2 -translate-y-1/2 origin-bottom transition-transform duration-200" style="height: 60%;"></div>
                </div>
            </div>

            <!-- Time Input -->
            <div class="flex items-center justify-center space-x-4 mb-6">
                <div class="flex items-center space-x-2">
                    <div class="flex flex-col items-center">
                        <input type="number" id="hourInput" min="1" max="12" class="w-16 text-center text-xl font-bold border-2 border-gray-200 rounded-lg py-2 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200" value="9">
                        <label class="text-xs text-gray-500 mt-1">Hour</label>
                    </div>
                    <span class="text-gray-500 font-medium">:</span>
                    <div class="flex flex-col items-center">
                        <input type="number" id="minuteInput" min="0" max="59" step="5" class="w-16 text-center text-xl font-bold border-2 border-gray-200 rounded-lg py-2 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200" value="0">
                        <label class="text-xs text-gray-500 mt-1">Minute</label>
                    </div>
                </div>
                <div class="flex rounded-lg overflow-hidden border-2 border-gray-200">
                    <button id="amButton" type="button" class="px-4 py-2 bg-indigo-600 text-white font-medium">AM</button>
                    <button id="pmButton" type="button" class="px-4 py-2 bg-gray-100 text-gray-700 font-medium">PM</button>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex space-x-3">
                <button type="button" onclick="setCurrentTime()" class="flex-1 bg-green-100 hover:bg-green-200 text-green-700 font-semibold py-3 px-4 rounded-xl transition-colors duration-200">
                    <i class="fas fa-clock mr-2"></i>
                    Now
                </button>
                <button type="button" onclick="closeClockPicker()" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-3 px-4 rounded-xl transition-colors duration-200">
                    Cancel
                </button>
                <button type="button" onclick="confirmTimeSelection()" class="flex-1 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-semibold py-3 px-4 rounded-xl transition-all duration-200 transform hover:scale-105">
                    Set Time
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<script>
// Initialize Select2 for employee dropdown
$(document).ready(function() {
    $('#employee_payroll_ids').select2({
        placeholder: "Select employee(s)",
        allowClear: true,
        width: '100%'
    });

    // Load shift info when employee and date are selected
    $('#employee_payroll_ids, #date').on('change', function() {
        loadShiftInfo();
    });
});

function loadShiftInfo() {
    const payrollIds = $('#employee_payroll_ids').val();
    const date = $('#date').val();

    if (payrollIds && payrollIds.length === 1 && date) {
        $.ajax({
            url: '/manual-punches/get-employee-shift',
            method: 'GET',
            data: {
                payroll_id: payrollIds[0],
                date: date
            },
            success: function(response) {
                if (response.success && response.shift) {
                    $('#shiftName').text(response.shift.name);
                    $('#shiftStart').text(response.shift.start_time_formatted);
                    $('#shiftEnd').text(response.shift.end_time_formatted);
                    $('#shiftInfo').removeClass('hidden');
                } else {
                    $('#shiftInfo').addClass('hidden');
                }
            },
            error: function() {
                $('#shiftInfo').addClass('hidden');
            }
        });
    } else {
        $('#shiftInfo').addClass('hidden');
    }
}

// Clock Picker Logic (from shift master)
let currentTimeInput = null;
let selectedHour = 9;
let selectedMinute = 0;
let selectedPeriod = 'AM';

function openClockPicker(inputId) {
    currentTimeInput = inputId;
    const input = document.getElementById(inputId);
    const currentValue = input.value || '09:00';

    // Parse current time
    const [hours, minutes] = currentValue.split(':');
    let hour = parseInt(hours);
    selectedPeriod = hour >= 12 ? 'PM' : 'AM';
    selectedHour = hour === 0 ? 12 : hour > 12 ? hour - 12 : hour;
    selectedMinute = parseInt(minutes) || 0;

    updateClockDisplay();
    updateTimeInputs();

    document.getElementById('clockPickerModal').classList.remove('hidden');
}

function closeClockPicker() {
    document.getElementById('clockPickerModal').classList.add('hidden');
    currentTimeInput = null;
}

function updateClockDisplay() {
    document.getElementById('selectedTimeDisplay').textContent =
        `${selectedHour.toString().padStart(2, '0')}:${selectedMinute.toString().padStart(2, '0')}`;
    document.getElementById('selectedPeriodDisplay').textContent = selectedPeriod;

    const amButton = document.getElementById('amButton');
    const pmButton = document.getElementById('pmButton');
    if (selectedPeriod === 'AM') {
        amButton.className = 'px-4 py-2 bg-indigo-600 text-white font-medium';
        pmButton.className = 'px-4 py-2 bg-gray-100 text-gray-700 font-medium';
    } else {
        amButton.className = 'px-4 py-2 bg-gray-100 text-gray-700 font-medium';
        pmButton.className = 'px-4 py-2 bg-indigo-600 text-white font-medium';
    }

    const hourAngle = (selectedHour % 12) * 30 + (selectedMinute / 60) * 30;
    const minuteAngle = selectedMinute * 6;

    document.getElementById('hourHand').style.transform = `translate(-50%, -100%) rotate(${hourAngle}deg)`;
    document.getElementById('minuteHand').style.transform = `translate(-50%, -100%) rotate(${minuteAngle}deg)`;
}

function updateTimeInputs() {
    document.getElementById('hourInput').value = selectedHour;
    document.getElementById('minuteInput').value = selectedMinute;
}

function setCurrentTime() {
    const now = new Date();
    selectedHour = now.getHours() % 12 || 12;
    selectedMinute = Math.round(now.getMinutes() / 5) * 5;
    selectedPeriod = now.getHours() >= 12 ? 'PM' : 'AM';

    updateClockDisplay();
    updateTimeInputs();
}

function confirmTimeSelection() {
    if (!currentTimeInput) return;

    // Convert to 24-hour format
    let hour24 = selectedHour;
    if (selectedPeriod === 'PM' && selectedHour !== 12) {
        hour24 += 12;
    } else if (selectedPeriod === 'AM' && selectedHour === 12) {
        hour24 = 0;
    }

    const timeValue = `${hour24.toString().padStart(2, '0')}:${selectedMinute.toString().padStart(2, '0')}`;
    
    const hiddenInput = document.getElementById(currentTimeInput);
    const displayInput = document.getElementById(currentTimeInput + '_display');
    
    hiddenInput.value = timeValue;
    displayInput.value = `${selectedHour}:${selectedMinute.toString().padStart(2, '0')} ${selectedPeriod}`;
    
    closeClockPicker();
}

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('clockFace').addEventListener('click', function(e) {
        const rect = this.getBoundingClientRect();
        const centerX = rect.left + rect.width / 2;
        const centerY = rect.top + rect.height / 2;

        const x = e.clientX - centerX;
        const y = e.clientY - centerY;

        const distance = Math.sqrt(x * x + y * y);
        const maxRadius = rect.width / 2;

        if (distance < 20) return;

        const angle = Math.atan2(y, x) * (180 / Math.PI) + 90;
        const normalizedAngle = angle < 0 ? angle + 360 : angle;

        const relativeDistance = distance / maxRadius;

        if (relativeDistance > 0.6) {
            const hourIndex = Math.round(normalizedAngle / 30) % 12;
            selectedHour = hourIndex === 0 ? 12 : hourIndex;
        } else {
            const minuteIndex = Math.round(normalizedAngle / 6) % 60;
            selectedMinute = Math.round(minuteIndex / 5) * 5;
        }

        updateClockDisplay();
        updateTimeInputs();
    });

    document.getElementById('hourInput').addEventListener('input', function() {
        selectedHour = Math.max(1, Math.min(12, parseInt(this.value) || 1));
        updateClockDisplay();
    });

    document.getElementById('minuteInput').addEventListener('input', function() {
        selectedMinute = Math.max(0, Math.min(59, parseInt(this.value) || 0));
        updateClockDisplay();
    });

    document.getElementById('amButton').addEventListener('click', function() {
        selectedPeriod = 'AM';
        updateClockDisplay();
    });

    document.getElementById('pmButton').addEventListener('click', function() {
        selectedPeriod = 'PM';
        updateClockDisplay();
    });

    document.getElementById('clockPickerModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeClockPicker();
        }
    });
});
</script>
@endsection
