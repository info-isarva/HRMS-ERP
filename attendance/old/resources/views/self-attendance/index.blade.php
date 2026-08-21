@extends('layouts.app')

@section('title', 'Self Attendance - HRMS')
@section('page-title', 'Self Attendance')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 py-6 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto space-y-6">
        
        <!-- Alerts for feedback -->
        @if(session('success'))
            <div class="bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 p-4 rounded-lg shadow-sm flex items-center justify-between" role="alert">
                <div class="flex items-center">
                    <i class="fas fa-check-circle text-emerald-500 text-lg mr-3"></i>
                    <span>{{ session('success') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700"><i class="fas fa-times"></i></button>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-rose-50 border-l-4 border-rose-500 text-rose-700 p-4 rounded-lg shadow-sm flex items-center justify-between" role="alert">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle text-rose-500 text-lg mr-3"></i>
                    <span>{{ session('error') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-700"><i class="fas fa-times"></i></button>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Left: Check In/Out Widget -->
            <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden transform hover:scale-[1.01] transition-all duration-300">
                <div class="bg-gradient-to-r from-blue-600 to-indigo-600 p-6 text-white text-center relative">
                    <div class="absolute top-2 right-2 opacity-10">
                        <i class="fas fa-fingerprint text-9xl"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-1">Punch Station</h3>
                    <p class="text-blue-100 text-sm">Self Attendance Logging Portal</p>
                    
                    <div class="mt-6 font-mono text-3xl font-bold tracking-widest" id="portal-live-clock">
                        00:00:00 AM
                    </div>
                    <p class="text-xs text-blue-200 mt-1" id="portal-live-date">...</p>
                    <script>
                        (function() {
                            function tick() {
                                const now = new Date();
                                let hours = now.getHours();
                                let minutes = now.getMinutes();
                                let seconds = now.getSeconds();
                                const ampm = hours >= 12 ? 'PM' : 'AM';
                                hours = hours % 12;
                                hours = hours ? hours : 12;
                                minutes = minutes < 10 ? '0' + minutes : minutes;
                                seconds = seconds < 10 ? '0' + seconds : seconds;
                                const clock = document.getElementById('portal-live-clock');
                                const dateEl = document.getElementById('portal-live-date');
                                if (clock) clock.innerText = `${hours}:${minutes}:${seconds} ${ampm}`;
                                if (dateEl) dateEl.innerText = now.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
                            }
                            setInterval(tick, 1000);
                            tick();
                        })();
                    </script>
                </div>
                
                <div class="p-6 space-y-6">
                    <!-- Status Badge -->
                    <div class="flex items-center justify-between bg-slate-50 p-4 rounded-xl">
                        <span class="text-slate-600 text-sm font-medium">Current Status:</span>
                        @if($todayAttendance)
                            @if($todayAttendance->check_out_time)
                                <span class="bg-slate-100 text-slate-700 text-xs font-semibold px-3 py-1.5 rounded-full flex items-center">
                                    <span class="w-2 h-2 rounded-full bg-slate-500 mr-2"></span>Checked Out
                                </span>
                            @else
                                <span class="bg-emerald-100 text-emerald-700 text-xs font-semibold px-3 py-1.5 rounded-full flex items-center animate-pulse">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500 mr-2"></span>Checked In
                                </span>
                            @endif
                        @else
                            <span class="bg-amber-100 text-amber-700 text-xs font-semibold px-3 py-1.5 rounded-full flex items-center">
                                <span class="w-2 h-2 rounded-full bg-amber-500 mr-2"></span>Not Checked In
                            </span>
                        @endif
                    </div>

                    <!-- Geolocation Tracker Status -->
                    <div class="border border-slate-100 rounded-xl p-4 text-xs space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">Device IP:</span>
                            <span class="font-medium text-slate-700">{{ request()->ip() }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">Location Status:</span>
                            <span id="location-badge" class="text-rose-600 font-semibold flex items-center">
                                <i class="fas fa-location-arrow mr-1"></i>Waiting for GPS...
                            </span>
                        </div>
                        <div id="gps-coords" class="hidden text-slate-400 font-mono text-[10px] text-right">
                            Lat: <span id="lat-val">0</span>, Lng: <span id="lng-val">0</span>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="space-y-3">
                        @if(!$todayAttendance)
                            <button onclick="handlePunch('check-in')" class="w-full bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white font-bold py-4 px-6 rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200 flex items-center justify-center">
                                <i class="fas fa-sign-in-alt mr-3 text-lg"></i>
                                CHECK IN NOW
                            </button>
                        @elseif(!$todayAttendance->check_out_time)
                            <div class="p-4 bg-emerald-50 text-emerald-800 rounded-xl text-xs space-y-1 mb-3 border border-emerald-100">
                                <p class="font-bold flex items-center">
                                    <i class="fas fa-clock mr-2 text-emerald-600"></i>Checked In At: {{ \Carbon\Carbon::parse($todayAttendance->check_in_time)->format('g:i A') }}
                                </p>
                                @if($todayAttendance->check_in_location_name)
                                    <p class="text-slate-500 truncate"><i class="fas fa-map-marker-alt mr-2 text-rose-500"></i>{{ $todayAttendance->check_in_location_name }}</p>
                                @endif
                            </div>
                            <button onclick="handlePunch('check-out')" class="w-full bg-gradient-to-r from-rose-500 to-red-600 hover:from-rose-600 hover:to-red-700 text-white font-bold py-4 px-6 rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200 flex items-center justify-center">
                                <i class="fas fa-sign-out-alt mr-3 text-lg"></i>
                                CHECK OUT NOW
                            </button>
                        @else
                            <div class="p-4 bg-slate-50 rounded-xl text-center border border-slate-200">
                                <i class="fas fa-check-circle text-emerald-500 text-4xl mb-2"></i>
                                <h4 class="font-semibold text-slate-800">Shift Completed Today!</h4>
                                <div class="text-xs text-slate-500 mt-2 space-y-1">
                                    <p>Check In: {{ \Carbon\Carbon::parse($todayAttendance->check_in_time)->format('g:i A') }}</p>
                                    <p>Check Out: {{ \Carbon\Carbon::parse($todayAttendance->check_out_time)->format('g:i A') }}</p>
                                    <p class="font-semibold text-slate-700">Total Hours Worked: {{ $todayAttendance->total_hours }}h</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Right: Calendar & Monthly View -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Month Filter and Reviews Panel -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 flex flex-col sm:flex-row items-center justify-between space-y-4 sm:space-y-0">
                    <form method="GET" action="{{ route('self-attendance.index') }}" class="flex items-center space-x-3 w-full sm:w-auto">
                        <select name="month" class="bg-slate-50 border border-slate-200 rounded-lg px-4 py-2 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @for($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>
                                    {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                </option>
                            @endfor
                        </select>
                        <select name="year" class="bg-slate-50 border border-slate-200 rounded-lg px-4 py-2 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @for($y = date('Y') - 3; $y <= date('Y'); $y++)
                                <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white rounded-lg px-4 py-2 text-sm font-medium transition-colors">
                            Filter
                        </button>
                    </form>

                    <div class="flex items-center space-x-3 w-full sm:w-auto justify-end">
                        @if($reviewRequest)
                            <div class="bg-slate-50 border border-slate-200 rounded-xl px-4 py-2 text-xs flex items-center">
                                <span class="text-slate-500 mr-2">Review Status:</span>
                                @if($reviewRequest->status == 'pending')
                                    <span class="text-amber-600 font-semibold"><i class="fas fa-clock mr-1"></i>Pending</span>
                                @elseif($reviewRequest->status == 'approved')
                                    <span class="text-emerald-600 font-semibold"><i class="fas fa-check-circle mr-1"></i>Reviewed</span>
                                @else
                                    <span class="text-rose-600 font-semibold"><i class="fas fa-times-circle mr-1"></i>Rejected</span>
                                @endif
                            </div>
                        @else
                            <button onclick="toggleModal('review-modal')" class="bg-gradient-to-r from-indigo-500 to-blue-600 hover:from-indigo-600 hover:to-blue-700 text-white rounded-xl px-4 py-2.5 text-xs font-semibold shadow-md transition-all duration-200 flex items-center">
                                <i class="fas fa-shield-halved mr-2"></i>Apply Monthly Review
                            </button>
                        @endif
                    </div>
                </div>

                <!-- Calendar Card -->
                <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-6">
                    <h3 class="text-lg font-bold text-slate-800 mb-6 flex items-center">
                        <i class="fas fa-calendar-alt text-blue-600 mr-3"></i>
                        Attendance Calendar - {{ date('F Y', mktime(0, 0, 0, $month, 1, $year)) }}
                    </h3>

                    <!-- Calendar Header -->
                    <div class="grid grid-cols-7 gap-2 mb-4 text-center">
                        @foreach(['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $dayName)
                            <span class="text-slate-400 text-xs font-semibold uppercase tracking-wider">{{ $dayName }}</span>
                        @endforeach
                    </div>

                    <!-- Calendar Grid -->
                    @php
                        $startDayOfWeek = \Carbon\Carbon::createFromDate($year, $month, 1)->dayOfWeek;
                        $daysInMonth = \Carbon\Carbon::createFromDate($year, $month, 1)->daysInMonth;
                    @endphp
                    <div class="grid grid-cols-7 gap-2">
                        <!-- Blank spaces before first day of month -->
                        @for($i = 0; $i < $startDayOfWeek; $i++)
                            <div class="min-h-[85px] bg-slate-50/50 rounded-xl border border-dashed border-slate-100"></div>
                        @endfor

                        <!-- Actual Days of Month -->
                        @for($day = 1; $day <= $daysInMonth; $day++)
                            @php
                                $dateStr = \Carbon\Carbon::createFromDate($year, $month, $day)->format('Y-m-d');
                                $record = $attendanceRecords[$dateStr] ?? null;
                                $rawAttendance = $rawAttendances[$dateStr] ?? null;
                                $leave = $leaveDays[$dateStr] ?? null;
                                $holiday = $publicHolidays[$dateStr] ?? null;
                                $correction = $manualPunches[$dateStr] ?? null;
                                
                                $dayOfWeek = \Carbon\Carbon::createFromDate($year, $month, $day)->dayOfWeek;
                                $isDynamicWeekOff = in_array($dayOfWeek, $weekOffDays);
                                $isToday = ($dateStr === date('Y-m-d'));
                                $isPast = ($dateStr < date('Y-m-d'));

                                $dayClass = "bg-white hover:bg-slate-50 border border-slate-100";
                                $statusText = "";
                                $statusColor = "text-slate-500";
                                $badgeClass = "bg-slate-100 text-slate-700";

                                $existingIn = null;
                                $existingOut = null;
                                $effectiveStatus = null;

                                if ($record) {
                                    $existingIn = $record->check_in_time ? \Carbon\Carbon::parse($record->check_in_time)->format('H:i') : null;
                                    $existingOut = $record->check_out_time ? \Carbon\Carbon::parse($record->check_out_time)->format('H:i') : null;
                                    $effectiveStatus = $record->status;
                                    
                                    if (($record->check_in_time && !$record->check_out_time) || (!$record->check_in_time && $record->check_out_time)) {
                                        if ($record->check_in_time && $isToday) {
                                            $effectiveStatus = 'present';
                                        } else {
                                            $effectiveStatus = 'pm';
                                        }
                                    }
                                } elseif ($rawAttendance) {
                                    $existingIn = $rawAttendance->check_in_time ? \Carbon\Carbon::parse($rawAttendance->check_in_time)->format('H:i') : null;
                                    $existingOut = $rawAttendance->check_out_time ? \Carbon\Carbon::parse($rawAttendance->check_out_time)->format('H:i') : null;
                                    $effectiveStatus = $rawAttendance->status;
                                    
                                    if (($rawAttendance->check_in_time && !$rawAttendance->check_out_time) || (!$rawAttendance->check_in_time && $rawAttendance->check_out_time)) {
                                        if ($rawAttendance->check_in_time && $isToday) {
                                            $effectiveStatus = 'present';
                                        } else {
                                            $effectiveStatus = 'pm';
                                        }
                                    }
                                }

                                if ($holiday) {
                                    $dayClass = "bg-purple-50/60 border border-purple-100 hover:bg-purple-100/50";
                                    $statusText = $holiday->name;
                                    $statusColor = "text-purple-700";
                                    $badgeClass = "bg-purple-100 text-purple-800";
                                } elseif ($leave) {
                                    $dayClass = "bg-sky-50/60 border border-sky-100 hover:bg-sky-100/50";
                                    $statusText = $leave->type_name;
                                    if ($leave->status == 'pending') {
                                        $statusText .= " (Pending)";
                                        $statusColor = "text-amber-600";
                                        $badgeClass = "bg-amber-100 text-amber-800";
                                    } else {
                                        $statusColor = "text-sky-700";
                                        $badgeClass = "bg-sky-100 text-sky-800";
                                    }
                                } elseif ($effectiveStatus) {
                                    if ($effectiveStatus == 'present' || $effectiveStatus == 'overtime') {
                                        $dayClass = "bg-emerald-50/40 border border-emerald-100 hover:bg-emerald-100/30";
                                        $statusText = ($effectiveStatus == 'present' && $isToday) ? 'Checked In' : ($effectiveStatus == 'overtime' ? 'Present (OT)' : 'Present');
                                        $statusColor = "text-emerald-700";
                                        $badgeClass = "bg-emerald-100 text-emerald-800";
                                    } elseif ($effectiveStatus == 'late') {
                                        $dayClass = "bg-amber-50/40 border border-amber-100 hover:bg-amber-100/30";
                                        $statusText = "Late Arrival";
                                        $statusColor = "text-amber-700";
                                        $badgeClass = "bg-amber-100 text-amber-800";
                                    } elseif ($effectiveStatus == 'half_day') {
                                        $dayClass = "bg-amber-50/40 border border-amber-100 hover:bg-amber-100/30";
                                        $statusText = "Half Day";
                                        $statusColor = "text-amber-800";
                                        $badgeClass = "bg-amber-200 text-amber-900";
                                    } elseif ($effectiveStatus == 'pm') {
                                        $dayClass = "bg-orange-50/40 border border-orange-100 hover:bg-orange-100/30";
                                        $statusText = "Punch Miss";
                                        $statusColor = "text-orange-700";
                                        $badgeClass = "bg-orange-100 text-orange-800";
                                    } elseif ($effectiveStatus == 'weekend') {
                                        $dayClass = "bg-slate-50/80 border border-slate-100 hover:bg-slate-100/50";
                                        $statusText = "Week-off";
                                        $statusColor = "text-slate-500";
                                        $badgeClass = "bg-slate-100 text-slate-800";
                                    } else {
                                        $dayClass = "bg-rose-50/30 border border-rose-100 hover:bg-rose-100/35";
                                        $statusText = "Absent";
                                        $statusColor = "text-rose-700";
                                        $badgeClass = "bg-rose-100 text-rose-800";
                                    }
                                } elseif ($isDynamicWeekOff) {
                                    $dayClass = "bg-slate-50/80 border border-slate-100 hover:bg-slate-100/50";
                                    $statusText = "Week-off";
                                    $statusColor = "text-slate-500";
                                    $badgeClass = "bg-slate-100 text-slate-800";
                                } elseif ($isPast) {
                                    $dayClass = "bg-rose-50/30 border border-rose-100 hover:bg-rose-100/35";
                                    $statusText = "Absent";
                                    $statusColor = "text-rose-700";
                                    $badgeClass = "bg-rose-100 text-rose-800";
                                }
                            @endphp

                            <div class="min-h-[95px] p-2 rounded-xl flex flex-col justify-between relative group transition-all duration-200 {{ $dayClass }} shadow-sm hover:shadow">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-bold text-slate-700">{{ $day }}</span>
                                    
                                    @if($correction)
                                        <span class="text-[9px] px-1.5 py-0.5 rounded-full font-bold
                                            @if($correction->status == 'pending') bg-amber-100 text-amber-800
                                            @elseif($correction->status == 'approved') bg-emerald-100 text-emerald-800
                                            @else bg-rose-100 text-rose-800 @endif"
                                            @if($correction->status == 'rejected' && $correction->rejection_reason)
                                                title="Reason for rejection: {{ $correction->rejection_reason }}"
                                            @endif>
                                            Correction: {{ ucfirst($correction->status) }}
                                            @if($correction->status == 'rejected' && $correction->rejection_reason)
                                                <i class="fas fa-info-circle ml-0.5" style="font-size: 8px;"></i>
                                            @endif
                                        </span>
                                    @endif
                                </div>

                                <div class="my-1.5">
                                    @if($statusText)
                                        <span class="text-[10px] font-semibold block truncate {{ $statusColor }}">
                                            {{ $statusText }}
                                        </span>
                                    @endif

                                    <!-- Punch Log Tooltip/Snippet -->
                                    @if($record && ($record->check_in_time || $record->check_out_time))
                                        <span class="text-[9px] text-slate-400 block font-mono">
                                            {{ $record->check_in_time ? \Carbon\Carbon::parse($record->check_in_time)->format('h:i') : '--' }} -
                                            {{ $record->check_out_time ? \Carbon\Carbon::parse($record->check_out_time)->format('h:i') : '--' }}
                                        </span>
                                    @elseif($rawAttendance && ($rawAttendance->check_in_time || $rawAttendance->check_out_time))
                                        <span class="text-[9px] text-slate-400 block font-mono">
                                            {{ $rawAttendance->check_in_time ? \Carbon\Carbon::parse($rawAttendance->check_in_time)->format('h:i') : '--' }} -
                                            {{ $rawAttendance->check_out_time ? \Carbon\Carbon::parse($rawAttendance->check_out_time)->format('h:i') : '--' }}
                                        </span>
                                    @endif
                                </div>

                                <!-- Action Buttons (visible on hover) -->
                                <div class="opacity-0 group-hover:opacity-100 transition-opacity absolute bottom-1 right-1 flex space-x-1">
                                    @if(\Carbon\Carbon::createFromDate($year, $month, $day)->isPast())
                                        <button onclick="openCorrectionModal('{{ $dateStr }}', '{{ $existingIn }}', '{{ $existingOut }}')" class="bg-blue-600 hover:bg-blue-700 text-white rounded-full p-1 shadow text-[9px] w-5 h-5 flex items-center justify-center" title="Raise missed punch correction">
                                            <i class="fas fa-tools"></i>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @endfor
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Modal: Missed Punch Correction -->
<div id="correction-modal" class="fixed inset-0 z-50 overflow-y-auto hidden">
    <div class="flex items-center justify-center min-h-screen px-4 text-center">
        <div class="fixed inset-0 bg-slate-900 bg-opacity-40 transition-opacity" onclick="toggleModal('correction-modal')"></div>
        <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form method="POST" action="{{ route('self-attendance.correction') }}">
                @csrf
                <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-4 text-white">
                    <h3 class="text-lg font-bold flex items-center">
                        <i class="fas fa-wrench mr-3"></i>Raise Attendance Correction
                    </h3>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Date</label>
                        <input type="date" name="date" id="correction-date" readonly class="w-full bg-slate-50 border border-slate-200 rounded-lg px-4 py-2.5 text-sm text-slate-600 focus:outline-none">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Punch In Time</label>
                            <input type="time" name="punch_in_time" class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Punch Out Time</label>
                            <input type="time" name="punch_out_time" class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Reason / Explanation</label>
                        <textarea name="reason" rows="3" required placeholder="Describe the reason for correction (e.g. missed punch, client meeting, outdoor duty)" class="w-full border border-slate-200 rounded-lg p-4 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>
                </div>
                <div class="bg-slate-50 px-6 py-4 flex justify-end space-x-3 rounded-b-2xl">
                    <button type="button" onclick="toggleModal('correction-modal')" class="bg-white hover:bg-slate-100 border border-slate-200 text-slate-700 rounded-lg px-4 py-2 text-sm font-medium transition-colors">
                        Cancel
                    </button>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white rounded-lg px-4 py-2 text-sm font-medium transition-colors">
                        Submit Correction
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Monthly Review Request -->
<div id="review-modal" class="fixed inset-0 z-50 overflow-y-auto hidden">
    <div class="flex items-center justify-center min-h-screen px-4 text-center">
        <div class="fixed inset-0 bg-slate-900 bg-opacity-40 transition-opacity" onclick="toggleModal('review-modal')"></div>
        <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full">
            <form method="POST" action="{{ route('self-attendance.review') }}">
                @csrf
                <input type="hidden" name="month" value="{{ $month }}">
                <input type="hidden" name="year" value="{{ $year }}">
                <div class="bg-gradient-to-r from-indigo-600 to-blue-600 px-6 py-4 text-white">
                    <h3 class="text-lg font-bold flex items-center">
                        <i class="fas fa-shield-halved mr-3"></i>Apply Monthly Attendance Review
                    </h3>
                </div>
                <div class="p-6 space-y-4">
                    <p class="text-xs text-slate-500">
                        Submit a request for reviewing your overall attendance for the month of <strong>{{ date('F Y', mktime(0, 0, 0, $month, 1, $year)) }}</strong>. Ensure all missing punches are corrected first.
                    </p>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Additional Notes</label>
                        <textarea name="notes" rows="4" placeholder="Any comments or summary for review..." class="w-full border border-slate-200 rounded-lg p-4 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                    </div>
                </div>
                <div class="bg-slate-50 px-6 py-4 flex justify-end space-x-3 rounded-b-2xl">
                    <button type="button" onclick="toggleModal('review-modal')" class="bg-white hover:bg-slate-100 border border-slate-200 text-slate-700 rounded-lg px-4 py-2 text-sm font-medium transition-colors">
                        Cancel
                    </button>
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg px-4 py-2 text-sm font-medium transition-colors">
                        Submit Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Geolocation and IP Fetching
    let userLat = null;
    let userLng = null;
    let locationName = "Self Attendance Logging";

    function initGeolocation() {
        const badge = document.getElementById('location-badge');
        const coordsDiv = document.getElementById('gps-coords');
        const latVal = document.getElementById('lat-val');
        const lngVal = document.getElementById('lng-val');

        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    userLat = position.coords.latitude;
                    userLng = position.coords.longitude;
                    
                    if (badge) {
                        badge.className = "text-emerald-600 font-semibold flex items-center";
                        badge.innerHTML = '<i class="fas fa-check-circle mr-1"></i>Location Acquired';
                    }
                    if (coordsDiv && latVal && lngVal) {
                        latVal.innerText = userLat.toFixed(6);
                        lngVal.innerText = userLng.toFixed(6);
                        coordsDiv.classList.remove('hidden');
                    }

                    // Try simple reverse geocoding using free nominatim API
                    fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${userLat}&lon=${userLng}`)
                        .then(res => res.json())
                        .then(data => {
                            if (data && data.display_name) {
                                locationName = data.display_name;
                            }
                        })
                        .catch(err => console.log('Geocoding error:', err));
                },
                (error) => {
                    console.warn('Geolocation error:', error);
                    if (badge) {
                        badge.className = "text-amber-600 font-semibold flex items-center";
                        badge.innerHTML = '<i class="fas fa-exclamation-triangle mr-1"></i>GPS Unavailable (IP Only)';
                    }
                },
                { enableHighAccuracy: true, timeout: 5000, maximumAge: 0 }
            );
        } else {
            if (badge) {
                badge.className = "text-amber-600 font-semibold flex items-center";
                badge.innerHTML = '<i class="fas fa-exclamation-triangle mr-1"></i>Browser lacks Geolocation';
            }
        }
    }

    document.addEventListener('DOMContentLoaded', initGeolocation);

    // Punch Handler (AJAX Check-in / Check-out)
    function handlePunch(action) {
        const url = action === 'check-in' ? "{{ route('self-attendance.check-in') }}" : "{{ route('self-attendance.check-out') }}";
        
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                latitude: userLat,
                longitude: userLng,
                location_name: locationName
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert(data.message || 'Error occurred during punch.');
            }
        })
        .catch(err => {
            console.error('Punch Request Error:', err);
            alert('Request failed. Check internet connection.');
        });
    }

    // Modal Control functions
    function toggleModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.toggle('hidden');
        }
    }

    function openCorrectionModal(date, checkInTime = '', checkOutTime = '') {
        const dateInput = document.getElementById('correction-date');
        if (dateInput) {
            dateInput.value = date;
        }

        const inInput = document.querySelector('#correction-modal input[name="punch_in_time"]');
        const outInput = document.querySelector('#correction-modal input[name="punch_out_time"]');
        if (inInput) {
            inInput.value = checkInTime;
        }
        if (outInput) {
            outInput.value = checkOutTime;
        }

        toggleModal('correction-modal');
    }
</script>
@endsection
