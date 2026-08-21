@extends('layouts.app')

@section('title', 'Apply for Leave - HRMS')
@section('page-title', 'Apply for Leave')

@section('content')
<div class="container mx-auto max-w-full px-4 py-4 sm:py-6">
    <!-- Header Section -->
    <div class="bg-gradient-to-r from-emerald-600 via-teal-600 to-cyan-600 rounded-2xl p-4 sm:p-8 text-white relative overflow-hidden">
        <div class="absolute inset-0 opacity-10">
            <div class="absolute -top-4 -right-4 w-32 h-32 bg-white rounded-full"></div>
            <div class="absolute top-10 -right-8 w-20 h-20 bg-white rounded-full"></div>
            <div class="absolute -bottom-6 -left-6 w-24 h-24 bg-white rounded-full"></div>
        </div>
        <div class="relative">
            <div class="flex flex-col sm:flex-row items-center justify-between">
                <div class="mb-4 sm:mb-0">
                    <h1 class="text-xl sm:text-2xl lg:text-4xl font-bold mb-2 sm:mb-3 flex items-center">
                        <i class="fas fa-plus-circle mr-2 sm:mr-4 text-lg sm:text-xl lg:text-2xl"></i>
                        Apply for Leave
                    </h1>
                    <p class="text-emerald-100 text-sm lg:text-lg">Submit your leave application with all required details</p>
                </div>
                <div class="hidden lg:block">
                    <div class="w-24 h-24 sm:w-36 sm:h-36 bg-white bg-opacity-15 rounded-full flex items-center justify-center">
                        <i class="fas fa-calendar-plus text-3xl sm:text-5xl text-white"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Leave Balance Summary -->
    <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
        <div class="p-4 sm:p-6 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-indigo-50 cursor-pointer" onclick="toggleLeaveBalance()">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center">
                <div class="mb-3 sm:mb-0">
                    <h3 class="text-base sm:text-lg lg:text-xl font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-calculator text-blue-600 mr-2 sm:mr-3 text-sm sm:text-base lg:text-lg"></i>
                        Available Leave Balance
                    </h3>
                    <p class="text-gray-600 mt-1 sm:mt-2 text-xs sm:text-sm lg:text-base">Your current leave balance for financial year {{ $currentFinancialYear }}</p>
                </div>
                <div class="flex flex-col sm:flex-row items-start sm:items-center space-y-2 sm:space-y-0 sm:space-x-3">
                    @if(isset($dataSource))
                        <span class="px-2 sm:px-3 py-1 rounded-full text-xs font-medium {{ $dataSource === 'payroll_api' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                            <i class="fas {{ $dataSource === 'payroll_api' ? 'fa-cloud' : 'fa-database' }} mr-1"></i>
                            {{ $dataSource === 'payroll_api' ? 'Payroll API' : 'Local Fallback' }}
                        </span>
                    @endif
                    <button type="button" class="text-blue-600 hover:text-blue-800 transition-colors">
                        <i class="fas fa-chevron-down transition-transform" id="balance-toggle-icon"></i>
                    </button>
                </div>
            </div>
        </div>

        <div class="p-4 sm:p-6" id="leave-balance-details">
            @if(count($availableLeaveTypes) > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
                    @foreach($availableLeaveTypes as $leaveType)
                        <div class="bg-gradient-to-br from-white to-gray-50 rounded-lg border border-gray-200 p-3 sm:p-4 shadow-sm">
                            <div class="flex items-center justify-between mb-2">
                                <h4 class="font-semibold text-gray-800 text-xs sm:text-sm lg:text-base">{{ $leaveType->name }}</h4>
                                <span class="text-xs px-2 py-1 bg-blue-100 text-blue-800 rounded-full">{{ $leaveType->code }}</span>
                            </div>
                            <div class="flex items-center justify-between text-xs lg:text-sm">
                                <span class="text-gray-600">Allocated:</span>
                                <span class="font-medium text-gray-800">{{ $leaveBalances[$leaveType->id]['allocated'] }} days</span>
                            </div>
                            <div class="flex items-center justify-between text-xs lg:text-sm">
                                <span class="text-gray-600">Used:</span>
                                <span class="font-medium text-gray-800">{{ $leaveBalances[$leaveType->id]['used'] }} days</span>
                            </div>
                            <div class="h-2 bg-gray-200 rounded-full mt-2 overflow-hidden">
                                @php
                                    $allocated = $leaveBalances[$leaveType->id]['allocated'];
                                    $used = $leaveBalances[$leaveType->id]['used'];
                                    
                                    // Handle division by zero case
                                    if ($allocated > 0) {
                                        $percentage = ($used / $allocated) * 100;
                                    } else {
                                        // If no days allocated, show full bar if there are used days, empty otherwise
                                        $percentage = $used > 0 ? 100 : 0;
                                    }
                                    
                                    $colorClass = $percentage > 75 ? 'bg-red-500' : ($percentage > 50 ? 'bg-yellow-500' : 'bg-green-500');
                                @endphp
                                <div class="{{ $colorClass }}" style="width: {{ min($percentage, 100) }}%; height: 100%;"></div>
                            </div>
                            <div class="flex items-center justify-between mt-2">
                                <span class="text-xs sm:text-sm font-medium text-gray-600">Balance:</span>
                                <span class="font-bold text-sm sm:text-base lg:text-lg {{ $leaveBalances[$leaveType->id]['balance'] <= 0 ? 'text-red-600' : 'text-green-600' }}">
                                    {{ $leaveBalances[$leaveType->id]['balance'] }} days
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="bg-yellow-50 rounded-lg p-4 border border-yellow-200">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-triangle text-yellow-500 mr-2"></i>
                        <p class="text-yellow-700">No leave types are available for your department. Please contact your administrator.</p>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- LOP Summary (only show if user has LOP history) - Compact Version -->
    @if($lopSummary)
        <div class="bg-gradient-to-r from-orange-50 to-red-50 border border-orange-200 rounded-xl p-3 sm:p-4 shadow-sm">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between">
                <div class="flex items-center mb-3 sm:mb-0">
                    <div class="bg-orange-100 rounded-full p-1.5 sm:p-2 mr-2 sm:mr-3">
                        <i class="fas fa-exclamation-triangle text-orange-600 text-sm sm:text-lg"></i>
                    </div>
                    <div>
                        <h4 class="font-semibold text-orange-900 text-xs sm:text-sm lg:text-base">LOP Alert</h4>
                        <p class="text-xs sm:text-sm text-orange-700">You have <span class="font-bold">{{ $lopSummary['total_lop_days'] }} LOP days</span> this year</p>
                    </div>
                </div>
                
                <div class="flex flex-col sm:flex-row items-start sm:items-center space-y-2 sm:space-y-0 sm:space-x-4">
                    <!-- Quick LOP Stats -->
                    @foreach($lopSummary['lop_by_leave_type'] as $leaveType => $lopDays)
                        <div class="text-center">
                            <div class="text-xs text-orange-600">{{ $leaveType }}</div>
                            <div class="font-bold text-orange-800">{{ $lopDays }}</div>
                        </div>
                    @endforeach
                    
                    <!-- Expandable Details Button -->
                    <button type="button" onclick="toggleLOPDetails()" class="text-orange-600 hover:text-orange-800 transition-colors">
                        <i class="fas fa-chevron-down" id="lop-toggle-icon"></i>
                    </button>
                </div>
            </div>
            
            <!-- Expandable LOP Details -->
            <div id="lop-details" class="mt-3 sm:mt-4 pt-3 sm:pt-4 border-t border-orange-200 hidden">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4 text-sm">
                    <div>
                        <h5 class="font-medium text-orange-800 mb-2 text-xs sm:text-sm">Recent LOP Applications:</h5>
                        @foreach($lopSummary['recent_lop_applications'] as $application)
                            <div class="flex justify-between items-center text-xs text-orange-700 mb-1 p-2 bg-orange-100 rounded">
                                <div>
                                    <span class="font-medium">{{ $application->leaveType->name ?? 'Unknown' }}</span>
                                    <br>
                                    <span class="text-orange-600">{{ $application->start_date->format('M d') }} - {{ $application->end_date->format('M d, Y') }}</span>
                                </div>
                                <span class="font-bold text-red-600">{{ $application->lop_days }} LOP</span>
                            </div>
                        @endforeach
                    </div>
                    <div>
                        <h5 class="font-medium text-orange-800 mb-2 text-xs sm:text-sm">Important Notice:</h5>
                        <div class="p-2 sm:p-3 bg-orange-100 rounded-lg">
                            <div class="flex items-start text-orange-800 text-xs">
                                <i class="fas fa-info-circle mr-2 mt-0.5 flex-shrink-0"></i>
                                <span><strong>Reminder:</strong> LOP days are unpaid and reduce your salary. Plan your leaves to avoid future LOP.</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
            function toggleLOPDetails() {
                const details = document.getElementById('lop-details');
                const icon = document.getElementById('lop-toggle-icon');
                
                if (details.classList.contains('hidden')) {
                    details.classList.remove('hidden');
                    icon.classList.remove('fa-chevron-down');
                    icon.classList.add('fa-chevron-up');
                } else {
                    details.classList.add('hidden');
                    icon.classList.remove('fa-chevron-up');
                    icon.classList.add('fa-chevron-down');
                }
            }

            function toggleLeaveBalance() {
                const details = document.getElementById('leave-balance-details');
                const icon = document.getElementById('balance-toggle-icon');
                
                if (details.classList.contains('hidden')) {
                    details.classList.remove('hidden');
                    icon.classList.remove('fa-chevron-down');
                    icon.classList.add('fa-chevron-up');
                } else {
                    details.classList.add('hidden');
                    icon.classList.remove('fa-chevron-up');
                    icon.classList.add('fa-chevron-down');
                }
            }

            function toggleApplicationForm() {
                const formContent = document.getElementById('application-form-content');
                const icon = document.getElementById('form-toggle-icon');
                
                if (formContent.classList.contains('hidden')) {
                    formContent.classList.remove('hidden');
                    icon.classList.remove('fa-chevron-down');
                    icon.classList.add('fa-chevron-up');
                } else {
                    formContent.classList.add('hidden');
                    icon.classList.remove('fa-chevron-up');
                    icon.classList.add('fa-chevron-down');
                }
            }

            // Initialize page - only show Leave Application Form by default
            document.addEventListener('DOMContentLoaded', function() {
                // Leave balance starts collapsed
                const balanceDetails = document.getElementById('leave-balance-details');
                const balanceIcon = document.getElementById('balance-toggle-icon');
                if (balanceDetails && balanceIcon) {
                    balanceDetails.classList.add('hidden');
                    balanceIcon.classList.remove('fa-chevron-up');
                    balanceIcon.classList.add('fa-chevron-down');
                }

                // Application form starts expanded
                const formContent = document.getElementById('application-form-content');
                if (formContent) {
                    formContent.classList.remove('hidden');
                }
            });
        </script>
    @endif

    <!-- Application Form -->
    <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
        <div class="p-4 sm:p-6 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-white cursor-pointer" onclick="toggleApplicationForm()">
            <div class="flex justify-between items-center">
                <div>
                    <h3 class="text-base sm:text-lg lg:text-xl font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-file-alt text-emerald-600 mr-2 sm:mr-3 text-sm sm:text-base lg:text-lg"></i>
                        Leave Application Form
                    </h3>
                    <p class="text-gray-600 mt-1 sm:mt-2 text-xs sm:text-sm lg:text-base">Please fill in all the required information for your leave request</p>
                </div>
                <button type="button" class="text-emerald-600 hover:text-emerald-800 transition-colors">
                    <i class="fas fa-chevron-up transition-transform" id="form-toggle-icon"></i>
                </button>
            </div>
        </div>

        <form method="POST" action="{{ route('leaves.store') }}" class="p-4 sm:p-6 space-y-4 sm:space-y-6" id="application-form-content">
            @csrf
            <input type="hidden" name="custom_half_days" id="custom_half_days" value="">
            
            <!-- Success Message -->
            @if(session('success'))
                <div class="bg-green-50 border border-green-200 rounded-lg p-3 sm:p-4">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle text-green-500 mr-2"></i>
                        <p class="text-green-800 font-medium">{{ session('success') }}</p>
                    </div>
                </div>
            @endif
            
            <!-- Error Messages -->
            @if ($errors->any() || session('error'))
                <div class="bg-red-50 border border-red-200 rounded-lg p-3 sm:p-4">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle text-red-500 mr-2"></i>
                        <h4 class="text-red-800 font-medium">Please fix the following errors:</h4>
                    </div>
                    <ul class="mt-2 text-sm text-red-700 space-y-1">
                        @if(session('error'))
                            <li>• {{ session('error') }}</li>
                        @endif
                        @foreach ($errors->all() as $error)
                            <li>• {{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- LOP Warning -->
            @if(session('lop_warning'))
                @php $lopData = session('lop_warning'); @endphp
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 sm:p-4 mb-4">
                    <div class="flex items-start">
                        <i class="fas fa-exclamation-triangle text-yellow-500 mr-2 sm:mr-3 mt-1"></i>
                        <div class="flex-1">
                            <h4 class="text-yellow-800 font-medium mb-2">Loss of Pay (LOP) Warning</h4>
                            <div class="text-sm text-yellow-700 mb-3">
                                <p class="mb-2">You are requesting <strong>{{ $lopData['total_days'] }} days</strong> of {{ $lopData['leave_type_name'] }}, but you only have <strong>{{ $lopData['available_balance'] }} days</strong> available.</p>
                                
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mt-3 p-3 bg-yellow-100 rounded">
                                    <div class="text-center">
                                        <div class="text-lg font-bold text-green-600">{{ $lopData['paid_days'] }}</div>
                                        <div class="text-xs">Paid Days</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-lg font-bold text-red-600">{{ $lopData['lop_days'] }}</div>
                                        <div class="text-xs">LOP Days</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-lg font-bold text-gray-700">{{ $lopData['total_days'] }}</div>
                                        <div class="text-xs">Total Days</div>
                                    </div>
                                </div>
                                
                                <p class="mt-2 font-medium">
                                    <strong>{{ $lopData['lop_days'] }} days will be Loss of Pay (LOP)</strong> - these days will be unpaid.
                                </p>
                            </div>
                            
                            <div class="flex items-center mt-3">
                                <input type="checkbox" id="acknowledge_lop" name="lop_acknowledged" value="1" 
                                       class="w-4 h-4 text-yellow-600 bg-gray-100 border-gray-300 rounded focus:ring-yellow-500">
                                <label for="acknowledge_lop" class="ml-2 text-sm text-yellow-700 font-medium cursor-pointer">
                                    I understand and acknowledge that {{ $lopData['lop_days'] }} days will be Loss of Pay (unpaid)
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Leave Type -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-tag text-emerald-500 mr-2 text-xs sm:text-sm"></i>
                        Leave Type
                    </label>
                    <select name="leave_type_id" id="leave_type_id" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors duration-200" required>
                        <option value="">Select Leave Type</option>
                        @foreach($availableLeaveTypes as $leaveType)
                            <option value="{{ $leaveType->id }}" data-balance="{{ $leaveBalances[$leaveType->id]['balance'] }}" {{ old('leave_type_id') == $leaveType->id ? 'selected' : '' }}>
                                {{ $leaveType->name }} ({{ $leaveBalances[$leaveType->id]['balance'] }} days remaining)
                            </option>
                        @endforeach
                    </select>
                    <p id="balance_warning" class="mt-2 text-sm text-red-600 hidden">Warning: You have insufficient leave balance for this leave type.</p>
                </div>

                <!-- Leave Duration -->
                <div>
                    <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-clock text-emerald-500 mr-2 text-xs sm:text-sm"></i>
                        Leave Duration
                    </label>
                    <select id="duration_type" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors duration-200">
                        <option value="single">Single Day</option>
                        <option value="multiple" {{ old('end_date') && old('end_date') != old('start_date') ? 'selected' : '' }}>Multiple Days</option>
                    </select>
                </div>
            </div>

            <!-- Date Selection -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-calendar-alt text-emerald-500 mr-2 text-xs sm:text-sm"></i>
                        Start Date
                    </label>
                    <input type="date" name="start_date" id="start_date" value="{{ old('start_date') }}" 
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors duration-200" 
                           required>
                </div>

                <div id="end_date_container" style="display: none;">
                    <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-calendar-alt text-emerald-500 mr-2 text-xs sm:text-sm"></i>
                        End Date
                    </label>
                    <input type="date" name="end_date" id="end_date" value="{{ old('end_date') }}" 
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors duration-200">
                </div>
            </div>

            <!-- Week Off Information -->
            @php
                $payrollService = new \App\Services\PayrollLeaveService();
                $weekOffConfig = $payrollService->getEmployeeWeekOffConfiguration(auth()->user());
                $weekOffPattern = $weekOffConfig['week_off_pattern'] ?? 'Saturday, Sunday';
            @endphp
            <div class="bg-gradient-to-r from-violet-50 to-purple-50 border border-violet-200 rounded-lg p-3 mt-2">
                <div class="flex items-center gap-2 sm:gap-3">
                    <div class="bg-violet-100 rounded-full p-1.5 sm:p-2">
                        <i class="fas fa-calendar-week text-violet-600"></i>
                    </div>
                    <div>
                        <span class="font-medium text-violet-900 text-xs sm:text-sm lg:text-base">Week-off Days:</span>
                        <span class="text-violet-800 ml-2 text-xs sm:text-sm">{{ $weekOffPattern }}</span>
                        <span class="text-xs text-violet-600 bg-violet-100 px-2 py-1 rounded-full ml-2">(auto-excluded)</span>
                    </div>
                </div>
            </div>

            <!-- Custom Day Selection for Multiple Days -->
            <div id="custom_days_container" class="hidden">
                <div class="bg-gradient-to-r from-yellow-50 to-orange-50 p-3 sm:p-4 rounded-lg border border-yellow-200 mb-4">
                    <h4 class="font-medium text-gray-900 mb-3 text-sm sm:text-base">Customize Individual Days</h4>
                    <p class="text-xs sm:text-sm text-gray-600 mb-3">
                        Customize each working day in your leave period. You can select full day or half day (morning/afternoon) for each individual day. 
                        Public holidays will be automatically excluded from your leave count.
                    </p>
                    <div id="days_list" class="space-y-2">
                        <!-- Dynamic days will be populated here -->
                    </div>
                </div>
            </div>

            <!-- Days Counter with Detailed Breakdown -->
            <div class="bg-blue-50 p-3 sm:p-4 rounded-lg border border-blue-100">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center">
                        <i class="fas fa-calculator text-blue-600 mr-2 text-base sm:text-lg"></i>
                        <span class="text-blue-800 font-medium text-sm sm:text-base">Leave Days Calculation:</span>
                    </div>
                    <span id="days_counter" class="text-base sm:text-lg font-bold text-blue-700">-</span>
                </div>
                
                <div id="days_breakdown" class="hidden">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Full Days:</span>
                            <span id="full_days_count" class="font-medium">0</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Half Days:</span>
                            <span id="half_days_count" class="font-medium">0</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Public Holidays:</span>
                            <span id="holidays_count" class="font-medium text-green-600">0</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600 font-medium">Total Deducted:</span>
                            <span id="deducted_days" class="font-bold text-blue-600">0</span>
                        </div>
                    </div>
                    <div class="mt-2 p-2 bg-white rounded text-xs">
                        <div class="flex items-center text-green-600 mb-1">
                            <i class="fas fa-info-circle mr-1"></i>
                            <span>Public holidays are automatically excluded and won't count against your leave balance.</span>
                        </div>
                        <div class="text-gray-600">
                            Weekend days are always excluded from leave calculations.
                        </div>
                    </div>
                </div>
                
                <p class="text-sm text-blue-600 mt-2">
                    <span class="font-medium">Final leave days to be deducted: </span>
                    <span id="final_deduction" class="font-bold">0</span> days
                </p>
            </div>

            <!-- Single Day Half-Day Selection -->
            <div id="single_day_half_day_container" class="hidden">
                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 p-3 sm:p-4 rounded-lg border border-blue-200 mb-4">
                    <h4 class="font-medium text-gray-900 mb-3 text-sm sm:text-base flex items-center">
                        <i class="fas fa-sun text-blue-600 mr-2 text-xs sm:text-sm"></i>
                        Day Type Selection
                    </h4>
                    <p class="text-xs sm:text-sm text-gray-600 mb-3">
                        Choose whether you want to take the full day or half day (morning/afternoon) leave.
                    </p>
                    <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-4">
                        <label class="flex items-center">
                            <input type="radio" name="single_day_type" value="full_day" class="mr-2" checked>
                            <span class="text-sm font-medium">Full Day</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="single_day_type" value="first_half" class="mr-2">
                            <span class="text-sm font-medium">First Half (Morning)</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="single_day_type" value="second_half" class="mr-2">
                            <span class="text-sm font-medium">Second Half (Afternoon)</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Hidden field to store calculated days from days_counter -->
            <input type="hidden" name="calculated_total_days" id="calculated_total_days" value="0">

            <!-- Reason -->
            <div>
                <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-comment text-emerald-500 mr-2 text-xs sm:text-sm"></i>
                    Reason for Leave
                </label>
                <textarea name="reason" id="reason" rows="4" placeholder="Please provide a detailed reason for your leave request..." 
                          class="w-full px-3 sm:px-4 py-2 sm:py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors duration-200 resize-none" 
                          required>{{ old('reason') }}</textarea>
                <p class="mt-2 text-xs sm:text-sm text-gray-500">Provide a clear and detailed explanation for your leave request (minimum 10 characters)</p>
            </div>

            <!-- Emergency Contact (Optional) -->
            <div class="bg-gray-50 rounded-lg p-3 sm:p-4">
                <h4 class="font-medium text-gray-900 mb-3 text-sm sm:text-base flex items-center">
                    <i class="fas fa-phone text-emerald-500 mr-2 text-xs sm:text-sm"></i>
                    Emergency Contact (Optional)
                </h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">Contact Name</label>
                        <input type="text" name="emergency_contact_name" value="{{ old('emergency_contact_name') }}" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors duration-200" 
                               placeholder="Full Name">
                    </div>
                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">Contact Number</label>
                        <input type="tel" name="emergency_contact_phone" value="{{ old('emergency_contact_phone') }}" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors duration-200" 
                               placeholder="+1 (555) 123-4567">
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row justify-between items-center space-y-4 sm:space-y-0 pt-4 sm:pt-6 border-t border-gray-200">
                <a href="{{ route('leaves.index') }}" 
                   class="text-gray-600 hover:text-gray-800 font-medium flex items-center space-x-2 transition-colors duration-200 text-sm sm:text-base">
                    <i class="fas fa-arrow-left"></i>
                    <span>Back to Applications</span>
                </a>
                
                <div class="flex space-x-3 sm:space-x-4">
                    <button type="submit" id="submit-button"
                            class="bg-gradient-to-r from-emerald-600 to-teal-600 text-white px-6 sm:px-8 py-2 sm:py-3 rounded-lg font-medium hover:from-emerald-700 hover:to-teal-700 transition-all duration-200 shadow-lg hover:shadow-xl flex items-center space-x-2 text-sm sm:text-base">
                        <i class="fas fa-paper-plane"></i>
                        <span>Submit Application</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const durationType = document.getElementById('duration_type');
        const endDateContainer = document.getElementById('end_date_container');

        const startDateInput = document.getElementById('start_date');
        const endDateInput = document.getElementById('end_date');

        const leaveTypeSelect = document.getElementById('leave_type_id');
        const balanceWarning = document.getElementById('balance_warning');
        const daysCounter = document.getElementById('days_counter');

        // Set minimum date to today
        const today = new Date().toISOString().split('T')[0];
        startDateInput.setAttribute('min', today);
        endDateInput.setAttribute('min', today);

        // Handle duration type change
        durationType.addEventListener('change', function() {
            const customDaysContainer = document.getElementById('custom_days_container');
            const singleDayHalfDayContainer = document.getElementById('single_day_half_day_container');
            
            if (this.value === 'single') {
                endDateContainer.style.display = 'none';
                customDaysContainer.classList.add('hidden');
                singleDayHalfDayContainer.classList.remove('hidden');
                endDateInput.value = startDateInput.value;
            } else {
                endDateContainer.style.display = 'block';
                customDaysContainer.classList.remove('hidden');
                singleDayHalfDayContainer.classList.add('hidden');
            }
            updateDaysEstimate();
        });

        // Update end date minimum when start date changes
        startDateInput.addEventListener('change', function() {
            endDateInput.setAttribute('min', this.value);
            if (durationType.value === 'single') {
                endDateInput.value = this.value;
            }
            updateDaysEstimate();
        });

        // Update days estimate on change
        endDateInput.addEventListener('change', updateDaysEstimate);
        
        // Add event listeners for single day half-day radio buttons
        document.querySelectorAll('input[name="single_day_type"]').forEach(radio => {
            radio.addEventListener('change', () => {
                updateDaysEstimate();
                // Update hidden field immediately when selection changes
                collectAndSubmitCustomHalfDays();
            });
        });
        
        // Check leave balance when leave type changes
        leaveTypeSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption && selectedOption.getAttribute('data-balance')) {
                const balance = parseFloat(selectedOption.getAttribute('data-balance'));
                if (balance <= 0) {
                    balanceWarning.classList.remove('hidden');
                } else {
                    balanceWarning.classList.add('hidden');
                }
            } else {
                balanceWarning.classList.add('hidden');
            }
            updateDaysEstimate();
        });

        // Enhanced days calculator with public holiday and custom day support
        async function updateDaysEstimate() {
            const customDaysContainer = document.getElementById('custom_days_container');
            const daysBreakdown = document.getElementById('days_breakdown');
            const daysList = document.getElementById('days_list');
            
            if (!startDateInput.value || !endDateInput.value) {
                daysCounter.textContent = '-';
                daysBreakdown.classList.add('hidden');
                return;
            }

            const start = new Date(startDateInput.value);
            const end = new Date(endDateInput.value);
            
            // If dates are invalid
            if (isNaN(start.getTime()) || isNaN(end.getTime())) {
                daysCounter.textContent = '-';
                daysBreakdown.classList.add('hidden');
                return;
            }
            
            try {
                // Fetch leave calculation from backend
                const calculation = await fetchLeaveCalculation(
                    startDateInput.value, 
                    endDateInput.value,
                    getCustomHalfDays()
                );
                
                // Update UI with calculation results
                updateCalculationDisplay(calculation);
                
                // Generate custom days list for multiple day leaves
                if (durationType.value === 'multiple') {
                    generateCustomDaysList(calculation.leave_days);
                }
                
            } catch (error) {
                console.error('Error calculating leave days:', error);
                
                // Check if it's an overlap error
                if (error.status === 422 && error.data && error.data.error) {
                    showOverlapError(error.data.message);
                } else {
                    // Fallback to simple calculation
                    fallbackCalculation();
                }
            }
        }
        
        // Fetch leave calculation from server
        async function fetchLeaveCalculation(startDate, endDate, halfDayDates = {}) {
            const leaveTypeId = leaveTypeSelect.value || null;
            
            const response = await fetch('{{ route("leaves.calculate") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    start_date: startDate,
                    end_date: endDate,
                    half_day_dates: halfDayDates,
                    leave_type_id: leaveTypeId
                })
            });
            
            if (!response.ok) {
                const errorData = await response.json();
                const error = new Error('Failed to calculate leave days');
                error.status = response.status;
                error.data = errorData;
                throw error;
            }
            
            return await response.json();
        }
        
        // Get custom half days from form
        function getCustomHalfDays() {
            const halfDays = {};
            
            if (durationType.value === 'single') {
                // Handle single day half-day selection
                const singleDayType = document.querySelector('input[name="single_day_type"]:checked');
                if (singleDayType && singleDayType.value !== 'full_day') {
                    halfDays[startDateInput.value] = singleDayType.value;
                }
            } else {
                // Handle multiple day individual selections
                document.querySelectorAll('.custom-day-select').forEach(select => {
                   
                    if (select.value !== 'full_day') {
                         console.log(select.value);
                        halfDays[select.dataset.date] = select.value;
                    }
                });
            }
            
            return halfDays;
        }
        
        // Update calculation display
        function updateCalculationDisplay(calculation) {
            clearOverlapError(); // Clear any previous overlap errors
            clearLOPWarning(); // Clear any previous LOP warnings
            
            const daysBreakdown = document.getElementById('days_breakdown');
            const breakdown = calculation.breakdown;
            
            document.getElementById('full_days_count').textContent = breakdown.full_days;
            document.getElementById('half_days_count').textContent = breakdown.half_days;
            document.getElementById('holidays_count').textContent = breakdown.public_holidays;
            document.getElementById('deducted_days').textContent = calculation.total_days.toFixed(1);
            document.getElementById('final_deduction').textContent = calculation.total_days.toFixed(1);
            
            // Update submit button state and display based on total days
            if (calculation.total_days <= 0) {
                updateSubmitButtonState(true); // Disable button
                daysCounter.innerHTML = '<span class="text-red-600">0 days</span> <span class="text-xs">(Cannot apply leave for weekends/holidays only)</span>';
                daysBreakdown.classList.add('hidden');
            } else {
                updateSubmitButtonState(false); // Enable button
                
                // Check for LOP and show warning if needed
                if (calculation.has_lop) {
                    showLOPWarning(calculation);
                } else {
                    daysCounter.textContent = calculation.total_days.toFixed(1) + ' days';
                    daysBreakdown.classList.remove('hidden');
                    // Check against available balance for standard warning
                    checkLeaveBalance(calculation.total_days);
                }
            }
            
            // CRITICAL: Update hidden field with calculated days value
            document.getElementById('calculated_total_days').value = calculation.total_days.toFixed(1);
        }
        
        // Generate custom days list for UI
        function generateCustomDaysList(leaveDays) {
            const daysList = document.getElementById('days_list');
            daysList.innerHTML = '';
            
            leaveDays.forEach(day => {
                const dayDiv = document.createElement('div');
                dayDiv.className = `flex items-center justify-between p-3 rounded-lg border ${
                    day.is_public_holiday ? 'bg-green-50 border-green-200' : 'bg-white border-gray-200'
                }`;
                
                const dateStr = new Date(day.leave_date).toLocaleDateString('en-US', {
                    weekday: 'short',
                    month: 'short', 
                    day: 'numeric'
                });
                
                dayDiv.innerHTML = `
                    <div class="flex items-center space-x-3">
                        <span class="font-medium text-gray-700">${dateStr}</span>
                        ${day.is_public_holiday ? 
                            '<span class="text-xs px-2 py-1 bg-green-100 text-green-700 rounded">Public Holiday</span>' : 
                            ''
                        }
                        ${day.notes ? 
                            `<span class="text-xs text-gray-500">${day.notes}</span>` : 
                            ''
                        }
                    </div>
                    ${!day.is_public_holiday ? `
                        <select class="custom-day-select w-48 px-4 py-2 text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors duration-200" 
                                data-date="${day.leave_date}">
                            <option value="full_day" ${day.day_type === 'full_day' ? 'selected' : ''}>Full Day</option>
                            <option value="first_half" ${day.day_type === 'first_half' ? 'selected' : ''}>First Half</option>
                            <option value="second_half" ${day.day_type === 'second_half' ? 'selected' : ''}>Second Half</option>
                        </select>
                    ` : `
                        <span class="text-sm text-gray-500">Not counted</span>
                    `}
                `;
                
                daysList.appendChild(dayDiv);
            });
            
            // Add event listeners to custom day selects
            document.querySelectorAll('.custom-day-select').forEach(select => {
                select.addEventListener('change', () => {
                    updateDaysEstimate();
                    // Update hidden field immediately when selection changes
                    collectAndSubmitCustomHalfDays();
                });
            });
        }
        
        // Check leave balance
        function checkLeaveBalance(totalDays) {
            if (leaveTypeSelect.selectedIndex > 0) {
                const selectedOption = leaveTypeSelect.options[leaveTypeSelect.selectedIndex];
                const balance = parseFloat(selectedOption.getAttribute('data-balance'));
                
                if (totalDays > balance) {
                    daysCounter.innerHTML = `<span class="text-red-600">${totalDays.toFixed(1)} days</span> <span class="text-xs">(exceeds balance)</span>`;
                    balanceWarning.classList.remove('hidden');
                } else {
                    balanceWarning.classList.add('hidden');
                }
            }
        }
        
        // Show overlap error to user
        function showOverlapError(message) {
            const daysCounter = document.getElementById('days_counter');
            const daysBreakdown = document.getElementById('days_breakdown');
            
            daysCounter.innerHTML = '<span class="text-red-600 text-sm">Overlap Detected!</span>';
            daysBreakdown.classList.add('hidden');
            
            // Show error message in the form
            let errorDiv = document.getElementById('overlap-error');
            if (!errorDiv) {
                errorDiv = document.createElement('div');
                errorDiv.id = 'overlap-error';
                errorDiv.className = 'bg-red-50 border border-red-200 rounded-lg p-4 mb-4';
                
                const daysSection = document.querySelector('#days_counter').closest('.bg-blue-50');
                daysSection.parentNode.insertBefore(errorDiv, daysSection);
            }
            
            errorDiv.innerHTML = `
                <div class="flex items-center">
                    <i class="fas fa-exclamation-triangle text-red-500 mr-2"></i>
                    <div>
                        <h4 class="text-red-800 font-medium">Leave Overlap Detected</h4>
                        <p class="text-red-700 text-sm mt-1">${message}</p>
                    </div>
                </div>
            `;
            
            errorDiv.style.display = 'block';
        }
        
        // Clear overlap error
        function clearOverlapError() {
            const errorDiv = document.getElementById('overlap-error');
            if (errorDiv) {
                errorDiv.style.display = 'none';
            }
        }
        
        // Show LOP warning to user
        function showLOPWarning(calculation) {
            const lopCalculation = calculation.lop_calculation || calculation;
            
            // Update days counter with LOP info
            daysCounter.innerHTML = `
                <span class="text-orange-600">${calculation.total_days.toFixed(1)} days</span> 
                <span class="text-xs text-gray-600">(${lopCalculation.paid_days.toFixed(1)} paid + ${lopCalculation.lop_days.toFixed(1)} LOP)</span>
            `;
            
            // Show breakdown
            const daysBreakdown = document.getElementById('days_breakdown');
            daysBreakdown.classList.remove('hidden');
            
            // Update hidden field with calculated days value
            document.getElementById('calculated_total_days').value = calculation.total_days.toFixed(1);
            
            // Show LOP warning in the form
            let lopDiv = document.getElementById('lop-warning');
            if (!lopDiv) {
                lopDiv = document.createElement('div');
                lopDiv.id = 'lop-warning';
                lopDiv.className = 'bg-orange-50 border border-orange-200 rounded-lg p-4 mb-4';
                
                const daysSection = document.querySelector('#days_counter').closest('.bg-blue-50');
                daysSection.parentNode.insertBefore(lopDiv, daysSection);
            }
            
            const leaveTypeName = leaveTypeSelect.options[leaveTypeSelect.selectedIndex]?.text?.split(' (')[0] || 'Unknown';
            
            lopDiv.innerHTML = `
                <div class="flex items-start">
                    <i class="fas fa-exclamation-triangle text-orange-500 mr-3 mt-1"></i>
                    <div>
                        <h4 class="text-orange-800 font-medium mb-2">Loss of Pay (LOP) Alert</h4>
                        <p class="text-orange-700 text-sm mb-3">
                            You are requesting <strong>${calculation.total_days.toFixed(1)} days</strong> of ${leaveTypeName}, but you only have 
                            <strong>${lopCalculation.available_balance.toFixed(1)} days</strong> available.
                        </p>
                        <div class="grid grid-cols-3 gap-3 p-3 bg-orange-100 rounded text-center text-sm">
                            <div>
                                <div class="font-bold text-green-600">${lopCalculation.paid_days.toFixed(1)}</div>
                                <div class="text-xs">Paid Days</div>
                            </div>
                            <div>
                                <div class="font-bold text-red-600">${lopCalculation.lop_days.toFixed(1)}</div>
                                <div class="text-xs">LOP Days</div>
                            </div>
                            <div>
                                <div class="font-bold text-gray-700">${calculation.total_days.toFixed(1)}</div>
                                <div class="text-xs">Total Days</div>
                            </div>
                        </div>
                        <p class="text-orange-700 text-sm font-medium mt-2">
                            <strong>${lopCalculation.lop_days.toFixed(1)} days will be unpaid (Loss of Pay)</strong>
                        </p>
                    </div>
                </div>
            `;
            
            lopDiv.style.display = 'block';
        }
        
        // Clear LOP warning
        function clearLOPWarning() {
            const lopDiv = document.getElementById('lop-warning');
            if (lopDiv) {
                lopDiv.style.display = 'none';
            }
        }
        
        // Fallback simple calculation
        function fallbackCalculation() {
            clearOverlapError();
            clearLOPWarning();
            
            let days = 0;
            const start = new Date(startDateInput.value);
            const end = new Date(endDateInput.value);
            const current = new Date(start);
            
            // Get the employee's week-off days from PHP variable
            @php
                $weekOffConfig = (new \App\Services\PayrollLeaveService())->getEmployeeWeekOffConfiguration(auth()->user());
                $weekOffDays = json_encode($weekOffConfig['week_off_days'] ?? [0, 6]);
            @endphp
            const weekOffDays = {!! $weekOffDays !!};
            
            while (current <= end) {
                const dayOfWeek = current.getDay();
                if (!weekOffDays.includes(dayOfWeek)) {
                    days += 1;
                }
                current.setDate(current.getDate() + 1);
            }
            
            if (days <= 0) {
                daysCounter.innerHTML = '<span class="text-red-600">0 days</span> <span class="text-xs">(Cannot apply leave for weekends/holidays only)</span>';
                updateSubmitButtonState(true); // Disable button
            } else {
                daysCounter.textContent = days.toFixed(1) + ' days';
                updateSubmitButtonState(false); // Enable button
                checkLeaveBalance(days);
            }
            
            // Update hidden field with calculated days value
            document.getElementById('calculated_total_days').value = days.toFixed(1);
        }

        // Function to disable/enable submit button
        function updateSubmitButtonState(isDisabled) {
            const submitButton = document.getElementById('submit-button');
            if (submitButton) {
                submitButton.disabled = isDisabled;
                if (isDisabled) {
                    submitButton.classList.add('opacity-50', 'cursor-not-allowed', 'pointer-events-none');
                } else {
                    submitButton.classList.remove('opacity-50', 'cursor-not-allowed', 'pointer-events-none');
                }
            }
        }

        // Form validation and data collection
        const form = document.getElementById('application-form-content');
        if (form) {
            form.addEventListener('submit', function(e) {
            e.preventDefault(); // Always prevent default first
            
            const leaveType = leaveTypeSelect.value;
            const reason = document.getElementById('reason').value.trim();
            const calculatedDays = parseFloat(document.getElementById('calculated_total_days').value) || 0;
            
            // Prevent submission if total days is 0
            if (calculatedDays <= 0) {
                alert('Cannot submit leave application for 0 days. The selected dates only include weekends or holidays. Please select valid working days.');
                return;
            }
            
            if (!leaveType) {
                e.preventDefault();
                alert('Please select a leave type');
                leaveTypeSelect.focus();
                return;
            }
            
            if (reason.length < 10) {
                e.preventDefault();
                alert('Please provide a detailed reason (at least 10 characters)');
                document.getElementById('reason').focus();
                return;
            }
            
            // Check if LOP warning is shown and acknowledgment is required
            const lopWarning = document.getElementById('lop-warning');
            const lopAcknowledge = document.getElementById('acknowledge_lop');
            
            if (lopWarning && lopWarning.style.display !== 'none' && lopAcknowledge && !lopAcknowledge.checked) {
                e.preventDefault();
                alert('Please acknowledge the Loss of Pay (LOP) warning before submitting your application.');
                lopAcknowledge.focus();
                return;
            }
            
            // CRITICAL FIX: Collect custom half-day data before form submission
            collectAndSubmitCustomHalfDays();
            
            // Double check calculated days before final submission
            const finalCalculatedDays = parseFloat(document.getElementById('calculated_total_days').value) || 0;
            if (finalCalculatedDays <= 0) {
                alert('Cannot submit leave application for 0 days. Please select valid working days.');
                return;
            }
            
            // If all validations pass, submit the form
            form.submit();
        });
        }
        
        // NEW FUNCTION: Collect custom half-day selections and add to form
        function collectAndSubmitCustomHalfDays() {
            const customHalfDays = {};
            
            // Collect all custom day selections (for multiple days)
            document.querySelectorAll('.custom-day-select').forEach(select => {
                const date = select.dataset.date;
                const dayType = select.value;
                
                console.log('Found custom-day-select:', date, dayType);
                if (dayType !== 'full_day') {
                    customHalfDays[date] = dayType;
                }
            });

            console.log('After collecting custom-day-select, customHalfDays:', customHalfDays);
            
            // Collect single day half-day selection
            const singleDayType = document.querySelector('input[name="single_day_type"]:checked');
            if (singleDayType && singleDayType.value !== 'full_day') {
                customHalfDays[startDateInput.value] = singleDayType.value;
                console.log('Added single day half-day:', startDateInput.value, singleDayType.value);
            }
            
            console.log('Final customHalfDays object:', customHalfDays);
            
            // Only set the field if there are actual half days
            const customHalfDaysField = document.getElementById('custom_half_days');
            if (Object.keys(customHalfDays).length > 0) {
                customHalfDaysField.value = JSON.stringify(customHalfDays);
                console.log('Set custom_half_days field to:', customHalfDaysField.value);
            } else {
                // Remove the field if no half days
                customHalfDaysField.value = '';
                customHalfDaysField.removeAttribute('name');
                console.log('No half days found, cleared field');
            }
            
            console.log('Submitting custom half days:', customHalfDays);
        }

        // Initialize form state
        durationType.dispatchEvent(new Event('change'));
        if (leaveTypeSelect.selectedIndex > 0) {
            leaveTypeSelect.dispatchEvent(new Event('change'));
        }
        updateDaysEstimate();
    });
</script>
@endsection