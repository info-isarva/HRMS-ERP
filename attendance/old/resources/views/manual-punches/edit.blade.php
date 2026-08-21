@extends('layouts.app')

@section('title', 'Edit Manual Punch - HRMS')

@section('page-title', 'Edit Manual Punch')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50">
    <div class="mx-auto p-6 space-y-6">
        <!-- Header -->
        <div class="bg-white/80 backdrop-blur-sm overflow-hidden shadow-xl rounded-2xl border border-white/20">
            <div class="bg-gradient-to-r from-indigo-600 via-purple-600 to-blue-600 px-8 py-12 relative overflow-hidden">
                <div class="absolute inset-0 bg-black/10"></div>

                <div class="relative flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-16 h-16 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center border border-white/30">
                                <i class="fas fa-edit text-white text-2xl"></i>
                            </div>
                        </div>
                        <div class="ml-6">
                            <h1 class="text-3xl font-bold text-white mb-2">Edit Manual Punch</h1>
                            <p class="text-indigo-100 text-lg">
                                Update punch information for {{ $manualPunch->employee->name ?? 'Employee' }}
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
                        <h3 class="text-sm font-medium text-red-800">There were errors:</h3>
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
                <h2 class="text-xl font-bold text-gray-900 flex items-center">
                    <i class="fas fa-user-clock text-indigo-600 mr-2"></i>
                    Punch Information
                </h2>
            </div>

            <form action="{{ route('manual-punches.update', $manualPunch) }}" method="POST" class="p-8 space-y-8">
                @csrf
                @method('PUT')

                <!-- Employee Info (Read-only) -->
                <div class="p-6 bg-gray-50 rounded-xl">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-lg flex items-center justify-center text-white font-bold mr-4">
                            {{ substr($manualPunch->employee->name ?? 'U', 0, 1) }}
                        </div>
                        <div>
                            <div class="font-semibold text-gray-900">{{ $manualPunch->employee->name ?? 'Unknown' }}</div>
                            <div class="text-sm text-gray-500">{{ $manualPunch->employee_id }}</div>
                        </div>
                    </div>
                </div>

                <!-- Date -->
                <div class="space-y-2">
                    <label for="date" class="block text-sm font-semibold text-gray-700 flex items-center">
                        <i class="fas fa-calendar mr-2 text-orange-500"></i>
                        Date <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="date" id="date" 
                           class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500"
                           required value="{{ old('date', $manualPunch->date->format('Y-m-d')) }}">
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
                                   value="{{ $manualPunch->punch_in_time ? \Carbon\Carbon::parse($manualPunch->punch_in_time)->format('g:i A') : '' }}"
                                   class="w-full pl-4 pr-12 py-4 text-lg border-2 border-gray-200 rounded-xl focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500 bg-white cursor-pointer"
                                   placeholder="Click to select time">
                            <input type="hidden"
                                   id="punch_in_time"
                                   name="punch_in_time"
                                   value="{{ old('punch_in_time', $manualPunch->punch_in_time ? \Carbon\Carbon::parse($manualPunch->punch_in_time)->format('H:i') : '') }}">
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
                                   value="{{ $manualPunch->punch_out_time ? \Carbon\Carbon::parse($manualPunch->punch_out_time)->format('g:i A') : '' }}"
                                   class="w-full pl-4 pr-12 py-4 text-lg border-2 border-gray-200 rounded-xl focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500 bg-white cursor-pointer"
                                   placeholder="Click to select time">
                            <input type="hidden"
                                   id="punch_out_time"
                                   name="punch_out_time"
                                   value="{{ old('punch_out_time', $manualPunch->punch_out_time ? \Carbon\Carbon::parse($manualPunch->punch_out_time)->format('H:i') : '') }}">
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
                              class="w-full pl-4 pr-4 py-4 text-lg border-2 border-gray-200 rounded-xl focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500 bg-gray-50/50 resize-none"
                              placeholder="Enter reason..."
                              required>{{ old('reason', $manualPunch->reason) }}</textarea>
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-between pt-8 border-t border-gray-200/50">
                    <a href="{{ route('manual-punches.index') }}"
                       class="inline-flex items-center px-8 py-4 border-2 border-gray-300 rounded-xl text-gray-700 font-semibold hover:bg-gray-50 transition-all">
                        <i class="fas fa-times mr-2"></i> Cancel
                    </a>
                    <button type="submit"
                            class="inline-flex items-center px-8 py-4 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold rounded-xl hover:from-indigo-700 hover:to-purple-700 transition-all">
                        <i class="fas fa-save mr-2"></i> Update Punch
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Clock Picker Modal (same as create.blade.php) -->
<div id="clockPickerModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full">
        <div class="bg-gradient-to-r from-indigo-600  to-purple-600 text-white p-6">
            <div class="flex items-center justify-between">
                <h3 class="text-xl font-bold flex items-center">
                    <i class="fas fa-clock mr-3"></i> Select Time
                </h3>
                <button onclick="closeClockPicker()" class="text-white hover:text-gray-200">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <div class="mt-4 text-center">
                <div id="selectedTimeDisplay" class="text-3xl font-bold">--:--</div>
                <div id="selectedPeriodDisplay" class="text-lg opacity-90">AM</div>
            </div>
        </div>

        <div class="p-6">
            <div class="relative w-64 h-64 mx-auto mb-6">
                <div id="clockFace" class="w-full h-full rounded-full bg-gradient-to-br from-gray-50 to-gray-100 border-4 border-gray-200 relative cursor-pointer shadow-lg">
                    <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-4 h-4 bg-indigo-600 rounded-full z-10"></div>
                    <div id="hourHand" class="absolute top-1/2 left-1/2 w-1 bg-indigo-600 transform -translate-x-1/2 -translate-y-1/2 origin-bottom" style="height: 40%;"></div>
                    <div id="minuteHand" class="absolute top-1/2 left-1/2 w-1 bg-purple-600 transform -translate-x-1/2 -translate-y-1/2 origin-bottom" style="height: 60%;"></div>
                </div>
            </div>

            <div class="flex items-center justify-center space-x-4 mb-6">
                <input type="number" id="hourInput" min="1" max="12" class="w-16 text-center text-xl font-bold border-2 border-gray-200 rounded-lg py-2" value="9">
                <span>:</span>
                <input type="number" id="minuteInput" min="0" max="59" class="w-16 text-center text-xl font-bold border-2 border-gray-200 rounded-lg py-2" value="0">
                <div class="flex border-2 border-gray-200 rounded-lg overflow-hidden">
                    <button id="amButton" type="button" class="px-4 py-2 bg-indigo-600 text-white">AM</button>
                    <button id="pmButton" type="button" class="px-4 py-2 bg-gray-100 text-gray-700">PM</button>
                </div>
            </div>

            <div class="flex space-x-3">
                <button type="button" onclick="setCurrentTime()" class="flex-1 bg-green-100 hover:bg-green-200 text-green-700 font-semibold py-3 rounded-xl">Now</button>
                <button type="button" onclick="confirmTimeSelection()" class="flex-1 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold py-3 rounded-xl">Set Time</button>
            </div>
        </div>
    </div>
</div>

<script>
// Clock picker logic (same as create.blade.php)
let currentTimeInput = null;
let selectedHour = 9;
let selectedMinute = 0;
let selectedPeriod = 'AM';

function openClockPicker(inputId) {
    currentTimeInput = inputId;
    const input = document.getElementById(inputId);
    const currentValue = input.value || '09:00';
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
}

function updateClockDisplay() {
    document.getElementById('selectedTimeDisplay').textContent = `${selectedHour.toString().padStart(2, '0')}:${selectedMinute.toString().padStart(2, '0')}`;
    document.getElementById('selectedPeriodDisplay').textContent = selectedPeriod;
    document.getElementById('amButton').className = selectedPeriod === 'AM' ? 'px-4 py-2 bg-indigo-600 text-white' : 'px-4 py-2 bg-gray-100 text-gray-700';
    document.getElementById('pmButton').className = selectedPeriod === 'PM' ? 'px-4 py-2 bg-indigo-600 text-white' : 'px-4 py-2 bg-gray-100 text-gray-700';
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
    let hour24 = selectedHour;
    if (selectedPeriod === 'PM' && selectedHour !== 12) hour24 += 12;
    else if (selectedPeriod === 'AM' && selectedHour === 12) hour24 = 0;
    const timeValue = `${hour24.toString().padStart(2, '0')}:${selectedMinute.toString().padStart(2, '0')}`;
    document.getElementById(currentTimeInput).value = timeValue;
    document.getElementById(currentTimeInput + '_display').value = `${selectedHour}:${selectedMinute.toString().padStart(2, '0')} ${selectedPeriod}`;
    closeClockPicker();
}

document.addEventListener('DOMContentLoaded', function() {
    ['hourInput', 'minuteInput'].forEach(id => {
        document.getElementById(id).addEventListener('input', function() {
            selectedHour = id === 'hourInput' ? Math.max(1, Math.min(12, parseInt(this.value) || 1)) : selectedHour;
            selectedMinute = id === 'minuteInput' ? Math.max(0, Math.min(59, parseInt(this.value) || 0)) : selectedMinute;
            updateClockDisplay();
        });
    });
    
    document.getElementById('amButton').addEventListener('click', () => { selectedPeriod = 'AM'; updateClockDisplay(); });
    document.getElementById('pmButton').addEventListener('click', () => { selectedPeriod = 'PM'; updateClockDisplay(); });
    
    document.getElementById('clockFace').addEventListener('click', function(e) {
        const rect = this.getBoundingClientRect();
        const x = e.clientX - (rect.left + rect.width / 2);
        const y = e.clientY - (rect.top + rect.height / 2);
        const distance = Math.sqrt(x * x + y * y);
        if (distance < 20) return;
        const angle = (Math.atan2(y, x) * (180 / Math.PI) + 90 + 360) % 360;
        if (distance / (rect.width / 2) > 0.6) {
            selectedHour = (Math.round(angle / 30) % 12) || 12;
        } else {
            selectedMinute = Math.round(Math.round(angle / 6) % 60 / 5) * 5;
        }
        updateClockDisplay();
        updateTimeInputs();
    });
});
</script>
@endsection
