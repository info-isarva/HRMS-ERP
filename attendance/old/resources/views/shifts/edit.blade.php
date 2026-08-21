@extends('layouts.app')

@section('title', 'Edit Shift - HRMS')

@section('page-title', 'Edit Shift')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50">
    <div class="mx-auto p-6 space-y-6">
        <!-- Header card (gradient) -->
        <div class="bg-white/80 backdrop-blur-sm overflow-hidden shadow-xl rounded-2xl border border-white/20">
            <div class="bg-gradient-to-r from-indigo-600 via-purple-600 to-blue-600 px-8 py-12 relative overflow-hidden">
                <!-- Background Pattern -->
                <div class="absolute inset-0 bg-black/10"></div>
                <div class="absolute -top-4 -right-4 w-24 h-24 bg-white/10 rounded-full"></div>
                <div class="absolute -bottom-4 -left-4 w-32 h-32 bg-white/10 rounded-full"></div>

                <div class="relative flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-16 h-16 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center border border-white/30">
                                <i class="fas fa-edit text-white text-2xl"></i>
                            </div>
                        </div>
                        <div class="ml-6">
                            <h1 class="text-3xl font-bold text-white mb-2">Edit Shift</h1>
                            <p class="text-indigo-100 text-lg">
                                Update shift timing details for "{{ $shift->name }}"
                            </p>
                        </div>
                    </div>

                    <div class="hidden md:flex items-center">
                        <a href="{{ route('shifts.index') }}" class="inline-flex items-center px-6 py-3 bg-white/20 backdrop-blur-sm text-white font-semibold rounded-xl shadow-lg hover:bg-white/30 transition-all duration-300 border border-white/30">
                            <i class="fas fa-arrow-left mr-2"></i> Back to Shifts
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

        <!-- Form -->
        <div class="bg-white/80 backdrop-blur-sm shadow-xl rounded-2xl overflow-hidden border border-white/20">
            <div class="px-8 py-6 border-b border-gray-200/50 bg-gradient-to-r from-gray-50/50 to-blue-50/30">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center">
                        <i class="fas fa-clock text-white"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">Shift Information</h2>
                        <p class="text-gray-600">Update the details for this shift</p>
                    </div>
                </div>
            </div>

            <form action="{{ route('shifts.update', $shift) }}" method="POST" class="p-8 space-y-8">
                @csrf
                @method('PUT')

                <!-- Name -->
                <div class="space-y-2">
                    <label for="name" class="block text-sm font-semibold text-gray-700 flex items-center">
                        <i class="fas fa-tag mr-2 text-indigo-500"></i>
                        Shift Name <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="text"
                               id="name"
                               name="name"
                               value="{{ old('name', $shift->name) }}"
                               class="w-full pl-4 pr-4 py-4 text-lg border-2 border-gray-200 rounded-xl focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all duration-300 bg-gray-50/50"
                               placeholder="e.g., Morning Shift, Night Shift, etc."
                               required>
                        <div class="absolute right-4 top-1/2 transform -translate-y-1/2">
                            <i class="fas fa-edit text-gray-400"></i>
                        </div>
                    </div>
                    @error('name')
                        <p class="mt-2 text-sm text-red-600 flex items-center">
                            <i class="fas fa-exclamation-triangle mr-1"></i>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <!-- Start Time -->
                    <div class="space-y-2">
                        <label for="start_time" class="block text-sm font-semibold text-gray-700 flex items-center">
                            <i class="fas fa-play mr-2 text-orange-500"></i>
                            Start Time <span class="text-red-500">*</span>
                        </label>
                        <div class="space-y-2">
                            <div class="relative">
                                <input type="text"
                                       id="start_time_display"
                                       readonly
                                       value="{{ old('start_time') ? date('g:i A', strtotime(old('start_time'))) : date('g:i A', strtotime($shift->start_time)) }}"
                                       onclick="openClockPicker('start_time')"
                                       class="w-full pl-4 pr-12 py-4 text-lg border-2 border-gray-200 rounded-xl focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all duration-300 bg-white cursor-pointer"
                                       placeholder="Click to select time">
                                <input type="hidden"
                                       id="start_time"
                                       name="start_time"
                                       value="{{ old('start_time', $shift->start_time) }}">
                                <div class="absolute right-4 top-1/2 transform -translate-y-1/2 pointer-events-none">
                                    <i class="fas fa-clock text-gray-400"></i>
                                </div>
                            </div>
                            <!-- Quick Time Presets -->
                            <div class="flex flex-wrap gap-2">
                                <button type="button" onclick="setTime('start_time', '08:00')"
                                        class="px-4 py-2 text-sm bg-orange-100 hover:bg-orange-200 text-orange-700 rounded-lg transition-colors duration-200 font-medium">
                                    8:00 AM
                                </button>
                                <button type="button" onclick="setTime('start_time', '09:00')"
                                        class="px-4 py-2 text-sm bg-orange-100 hover:bg-orange-200 text-orange-700 rounded-lg transition-colors duration-200 font-medium">
                                    9:00 AM
                                </button>
                                <button type="button" onclick="setTime('start_time', '10:00')"
                                        class="px-4 py-2 text-sm bg-orange-100 hover:bg-orange-200 text-orange-700 rounded-lg transition-colors duration-200 font-medium">
                                    10:00 AM
                                </button>
                            </div>
                        </div>
                        @error('start_time')
                            <p class="mt-2 text-sm text-red-600 flex items-center">
                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- End Time -->
                    <div class="space-y-2">
                        <label for="end_time" class="block text-sm font-semibold text-gray-700 flex items-center">
                            <i class="fas fa-stop mr-2 text-purple-500"></i>
                            End Time <span class="text-red-500">*</span>
                        </label>
                        <div class="space-y-2">
                            <div class="relative">
                                <input type="text"
                                       id="end_time_display"
                                       readonly
                                       value="{{ old('end_time') ? date('g:i A', strtotime(old('end_time'))) : date('g:i A', strtotime($shift->end_time)) }}"
                                       onclick="openClockPicker('end_time')"
                                       class="w-full pl-4 pr-12 py-4 text-lg border-2 border-gray-200 rounded-xl focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all duration-300 bg-white cursor-pointer"
                                       placeholder="Click to select time">
                                <input type="hidden"
                                       id="end_time"
                                       name="end_time"
                                       value="{{ old('end_time', $shift->end_time) }}">
                                <div class="absolute right-4 top-1/2 transform -translate-y-1/2 pointer-events-none">
                                    <i class="fas fa-clock text-gray-400"></i>
                                </div>
                            </div>
                            <!-- Quick Time Presets -->
                            <div class="flex flex-wrap gap-2">
                                <button type="button" onclick="setTime('end_time', '17:00')"
                                        class="px-4 py-2 text-sm bg-purple-100 hover:bg-purple-200 text-purple-700 rounded-lg transition-colors duration-200 font-medium">
                                    5:00 PM
                                </button>
                                <button type="button" onclick="setTime('end_time', '18:00')"
                                        class="px-4 py-2 text-sm bg-purple-100 hover:bg-purple-200 text-purple-700 rounded-lg transition-colors duration-200 font-medium">
                                    6:00 PM
                                </button>
                                <button type="button" onclick="setTime('end_time', '19:00')"
                                        class="px-4 py-2 text-sm bg-purple-100 hover:bg-purple-200 text-purple-700 rounded-lg transition-colors duration-200 font-medium">
                                    7:00 PM
                                </button>
                            </div>
                        </div>
                        @error('end_time')
                            <p class="mt-2 text-sm text-red-600 flex items-center">
                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Duration Display -->
                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-gray-700 flex items-center">
                            <i class="fas fa-clock mr-2 text-blue-500"></i>
                            Shift Duration
                        </label>
                        <div class="flex items-center justify-center">
                            <div class="inline-flex items-center space-x-2 bg-gradient-to-r from-blue-100 to-indigo-100 px-4 py-3 rounded-lg border border-blue-200/50 w-full justify-center">
                                <i class="fas fa-clock text-blue-600 text-sm"></i>
                                <span class="text-sm text-gray-600">Duration:</span>
                                <span id="shiftDuration" class="text-sm font-semibold text-indigo-600">8hrs</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="space-y-2">
                    <label for="description" class="block text-sm font-semibold text-gray-700 flex items-center">
                        <i class="fas fa-file-alt mr-2 text-green-500"></i>
                        Description
                    </label>
                    <div class="relative">
                        <textarea id="description"
                                  name="description"
                                  rows="4"
                                  class="w-full pl-4 pr-4 py-4 text-lg border-2 border-gray-200 rounded-xl focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all duration-300 bg-gray-50/50 resize-none"
                                  placeholder="Enter description for this shift...">{{ old('description', $shift->description) }}</textarea>
                        <div class="absolute right-4 top-4">
                            <i class="fas fa-align-left text-gray-400"></i>
                        </div>
                    </div>
                    @error('description')
                        <p class="mt-2 text-sm text-red-600 flex items-center">
                            <i class="fas fa-exclamation-triangle mr-1"></i>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-between pt-8 border-t border-gray-200/50">
                    <a href="{{ route('shifts.index') }}"
                       class="inline-flex items-center px-8 py-4 border-2 border-gray-300 rounded-xl shadow-sm text-sm font-semibold text-gray-700 bg-white/80 hover:bg-gray-50 transition-all duration-300 hover:scale-105">
                        <i class="fas fa-times mr-2"></i>
                        Cancel
                    </a>
                    <button type="submit"
                            class="inline-flex items-center px-8 py-4 border border-transparent rounded-xl shadow-lg text-sm font-semibold text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 transition-all duration-300 transform hover:scale-105">
                        <i class="fas fa-save mr-2"></i>
                        Update Shift
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Clock Picker Modal -->
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
                    <button id="amButton" class="px-4 py-2 bg-indigo-600 text-white font-medium">AM</button>
                    <button id="pmButton" class="px-4 py-2 bg-gray-100 text-gray-700 font-medium">PM</button>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex space-x-3">
                <button onclick="setCurrentTime()" class="flex-1 bg-green-100 hover:bg-green-200 text-green-700 font-semibold py-3 px-4 rounded-xl transition-colors duration-200">
                    <i class="fas fa-clock mr-2"></i>
                    Now
                </button>
                <button onclick="closeClockPicker()" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-3 px-4 rounded-xl transition-colors duration-200">
                    Cancel
                </button>
                <button onclick="confirmTimeSelection()" class="flex-1 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-semibold py-3 px-4 rounded-xl transition-all duration-200 transform hover:scale-105">
                    Set Time
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

<script>
let currentTimeInput = null;
let selectedHour = 9;
let selectedMinute = 0;
let selectedPeriod = 'AM';

function setTime(inputId, timeValue) {
    console.log('setTime called with:', inputId, timeValue);
    const hiddenInput = document.getElementById(inputId);
    const displayInput = document.getElementById(inputId + '_display');

    console.log('Hidden input:', hiddenInput);
    console.log('Display input:', displayInput);

    if (hiddenInput && displayInput) {
        hiddenInput.value = timeValue;

        // Convert to 12-hour format for display
        const [hours, minutes] = timeValue.split(':');
        const hour = parseInt(hours);
        const displayHour = hour === 0 ? 12 : hour > 12 ? hour - 12 : hour;
        const period = hour >= 12 ? 'PM' : 'AM';
        const displayTime = `${displayHour}:${minutes} ${period}`;

        displayInput.value = displayTime;
        console.log('Set display to:', displayTime);
        console.log('Display input value after setting:', displayInput.value);

        // Check value after a short delay
        setTimeout(() => {
            console.log('Display input value after delay:', displayInput.value);
        }, 100);

        // Add visual feedback
        displayInput.classList.add('ring-4', 'ring-green-400/50', 'border-green-500', 'bg-green-50');
        setTimeout(() => {
            displayInput.classList.remove('ring-4', 'ring-green-400/50', 'border-green-500', 'bg-green-50');
        }, 800);

        // Update duration display
        updateDuration();
    } else {
        console.error('Could not find inputs for:', inputId);
    }
}

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

    // Update UI
    updateClockDisplay();
    updateTimeInputs();

    // Show modal
    document.getElementById('clockPickerModal').classList.remove('hidden');
}

function closeClockPicker() {
    document.getElementById('clockPickerModal').classList.add('hidden');
    currentTimeInput = null;
}

function updateClockDisplay() {
    // Update time display
    document.getElementById('selectedTimeDisplay').textContent =
        `${selectedHour.toString().padStart(2, '0')}:${selectedMinute.toString().padStart(2, '0')}`;
    document.getElementById('selectedPeriodDisplay').textContent = selectedPeriod;

    // Update period buttons
    const amButton = document.getElementById('amButton');
    const pmButton = document.getElementById('pmButton');
    if (selectedPeriod === 'AM') {
        amButton.className = 'px-4 py-2 bg-indigo-600 text-white font-medium';
        pmButton.className = 'px-4 py-2 bg-gray-100 text-gray-700 font-medium';
    } else {
        amButton.className = 'px-4 py-2 bg-gray-100 text-gray-700 font-medium';
        pmButton.className = 'px-4 py-2 bg-indigo-600 text-white font-medium';
    }

    // Update clock hands
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
    selectedMinute = Math.round(now.getMinutes() / 5) * 5; // Round to nearest 5 minutes
    selectedPeriod = now.getHours() >= 12 ? 'PM' : 'AM';

    updateClockDisplay();
    updateTimeInputs();
}

function updateDuration() {
    const startTimeInput = document.getElementById('start_time');
    const endTimeInput = document.getElementById('end_time');
    const durationElement = document.getElementById('shiftDuration');

    if (startTimeInput && endTimeInput && durationElement && startTimeInput.value && endTimeInput.value) {
        const startTime = startTimeInput.value;
        const endTime = endTimeInput.value;

        // Parse times
        const [startHours, startMinutes] = startTime.split(':').map(Number);
        const [endHours, endMinutes] = endTime.split(':').map(Number);

        // Convert to minutes since midnight
        const startTotalMinutes = startHours * 60 + startMinutes;
        const endTotalMinutes = endHours * 60 + endMinutes;

        // Calculate duration
        let durationMinutes = endTotalMinutes - startTotalMinutes;

        // Handle overnight shifts (if end time is next day)
        if (durationMinutes < 0) {
            durationMinutes += 24 * 60; // Add 24 hours
        }

        // Convert to hours and minutes
        const hours = Math.floor(durationMinutes / 60);
        const minutes = durationMinutes % 60;

        // Format display
        let durationText = '';
        if (hours > 0) {
            durationText += `${hours}hr${hours > 1 ? 's' : ''}`;
        }
        if (minutes > 0) {
            if (hours > 0) durationText += ' ';
            durationText += `${minutes}min${minutes > 1 ? 's' : ''}`;
        }
        if (hours === 0 && minutes === 0) {
            durationText = '0hrs';
        }

        durationElement.textContent = durationText;
        console.log('Duration updated:', durationText);
    }
}

function confirmTimeSelection() {
    console.log('confirmTimeSelection called');
    console.log('currentTimeInput:', currentTimeInput);
    console.log('selectedHour:', selectedHour, 'selectedMinute:', selectedMinute, 'selectedPeriod:', selectedPeriod);

    if (!currentTimeInput) {
        console.error('No currentTimeInput set!');
        return;
    }

    // Convert to 24-hour format
    let hour24 = selectedHour;
    if (selectedPeriod === 'PM' && selectedHour !== 12) {
        hour24 += 12;
    } else if (selectedPeriod === 'AM' && selectedHour === 12) {
        hour24 = 0;
    }

    const timeValue = `${hour24.toString().padStart(2, '0')}:${selectedMinute.toString().padStart(2, '0')}`;
    console.log('Final timeValue:', timeValue);

    setTime(currentTimeInput, timeValue);
    closeClockPicker();
}

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    // Initialize display inputs based on hidden inputs
    initializeDisplayInputs();

    // Add form submission handler to ensure correct time format
    const form = document.querySelector('form[action*="/shifts/"]');
    if (form) {
        form.addEventListener('submit', function(e) {
            // Ensure hidden inputs are in correct H:i format before submission
            const startTimeInput = document.getElementById('start_time');
            const endTimeInput = document.getElementById('end_time');

            if (startTimeInput && startTimeInput.value) {
                const formattedStart = ensureHIFormat(startTimeInput.value);
                startTimeInput.value = formattedStart;
                console.log('Form submit - start_time formatted to:', formattedStart);
            }

            if (endTimeInput && endTimeInput.value) {
                const formattedEnd = ensureHIFormat(endTimeInput.value);
                endTimeInput.value = formattedEnd;
                console.log('Form submit - end_time formatted to:', formattedEnd);
            }
        });
    }

    // Clock face click handler
    document.getElementById('clockFace').addEventListener('click', function(e) {
        const rect = this.getBoundingClientRect();
        const centerX = rect.left + rect.width / 2;
        const centerY = rect.top + rect.height / 2;

        const x = e.clientX - centerX;
        const y = e.clientY - centerY;

        const distance = Math.sqrt(x * x + y * y);
        const maxRadius = rect.width / 2;

        // If click is too close to center, ignore
        if (distance < 20) return;

        const angle = Math.atan2(y, x) * (180 / Math.PI) + 90;
        const normalizedAngle = angle < 0 ? angle + 360 : angle;

        // Determine if it's closer to outer edge (hours) or inner area (minutes)
        const relativeDistance = distance / maxRadius;

        if (relativeDistance > 0.6) {
            // Outer area - select hours
            const hourIndex = Math.round(normalizedAngle / 30) % 12;
            selectedHour = hourIndex === 0 ? 12 : hourIndex;
            console.log('Hour selected:', selectedHour, 'angle:', normalizedAngle);
        } else {
            // Inner area - select minutes
            const minuteIndex = Math.round(normalizedAngle / 6) % 60;
            selectedMinute = Math.round(minuteIndex / 5) * 5; // Round to nearest 5 minutes
            console.log('Minute selected:', selectedMinute, 'angle:', normalizedAngle);
        }

        updateClockDisplay();
        updateTimeInputs();
    });

    // Clock picker input handlers
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

    // Close modal when clicking outside
    document.getElementById('clockPickerModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeClockPicker();
        }
    });
});

// Also call initializeDisplayInputs after a short delay to ensure DOM is fully loaded
setTimeout(initializeDisplayInputs, 100);

function ensureHIFormat(timeValue) {
    if (!timeValue) return '';

    // If already in H:i format, return as is
    if (/^\d{2}:\d{2}$/.test(timeValue)) {
        return timeValue;
    }

    // If in 12-hour format, convert to 24-hour
    if (timeValue.includes('AM') || timeValue.includes('PM')) {
        const [time, period] = timeValue.split(' ');
        const [hours, minutes] = time.split(':');
        let hour24 = parseInt(hours);

        if (period === 'PM' && hour24 !== 12) {
            hour24 += 12;
        } else if (period === 'AM' && hour24 === 12) {
            hour24 = 0;
        }

        return `${hour24.toString().padStart(2, '0')}:${minutes.padStart(2, '0')}`;
    }

    // Ensure proper H:i formatting
    const [hours, minutes] = timeValue.split(':');
    return `${hours.padStart(2, '0')}:${minutes.padStart(2, '0')}`;
}

function initializeDisplayInputs() {
    console.log('initializeDisplayInputs called');
    const timeInputs = ['start_time', 'end_time'];

    timeInputs.forEach(inputId => {
        const hiddenInput = document.getElementById(inputId);
        const displayInput = document.getElementById(inputId + '_display');

        console.log(`Processing ${inputId}:`);
        console.log('Hidden input element:', hiddenInput);
        console.log('Display input element:', displayInput);
        console.log('Hidden input value:', hiddenInput ? hiddenInput.value : 'null');
        console.log('Display input current value:', displayInput ? displayInput.value : 'null');

        if (hiddenInput && displayInput && hiddenInput.value) {
            // Ensure hidden input is in H:i format
            let timeValue = hiddenInput.value.trim();

            // If the value is in 12-hour format, convert it to 24-hour
            if (timeValue.includes('AM') || timeValue.includes('PM')) {
                // Parse 12-hour format and convert to 24-hour
                const [time, period] = timeValue.split(' ');
                const [hours, minutes] = time.split(':');
                let hour24 = parseInt(hours);

                if (period === 'PM' && hour24 !== 12) {
                    hour24 += 12;
                } else if (period === 'AM' && hour24 === 12) {
                    hour24 = 0;
                }

                timeValue = `${hour24.toString().padStart(2, '0')}:${minutes}`;
                hiddenInput.value = timeValue;
                console.log('Converted 12-hour to 24-hour format:', timeValue);
            }

            // Ensure it's in H:i format (pad with zeros if needed)
            if (/^\d{1,2}:\d{1,2}$/.test(timeValue)) {
                const [hours, minutes] = timeValue.split(':');
                const formattedTime = `${hours.padStart(2, '0')}:${minutes.padStart(2, '0')}`;
                hiddenInput.value = formattedTime;
                console.log('Formatted time to H:i:', formattedTime);
            }

            // Set display input to 12-hour format
            const [hours, minutes] = timeValue.split(':');
            const hour = parseInt(hours);
            const displayHour = hour === 0 ? 12 : hour > 12 ? hour - 12 : hour;
            const displayPeriod = hour >= 12 ? 'PM' : 'AM';
            const displayTime = `${displayHour}:${minutes} ${displayPeriod}`;

            displayInput.value = displayTime;
            console.log('Setting display to:', displayTime);
        } else {
            console.log('Skipping initialization for', inputId, '- condition not met');
        }
    });

    // Update duration after initialization
    updateDuration();
}
</script>