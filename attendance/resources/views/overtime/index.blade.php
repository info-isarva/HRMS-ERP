@extends('layouts.app')

@section('title', 'Overtime Management')

@section('page-title', 'Overtime Management')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50">
    <div class="max-w-full mx-auto p-6 space-y-6">

        <!-- Header -->
        <div class="mb-8">
            <div class="bg-gradient-to-r from-purple-600 to-pink-600 rounded-2xl p-8 text-white relative overflow-hidden">
                <div class="absolute inset-0 opacity-10">
                    <div class="absolute -top-4 -right-4 w-24 h-24 bg-white rounded-full"></div>
                    <div class="absolute top-10 -right-8 w-16 h-16 bg-white rounded-full"></div>
                    <div class="absolute -bottom-6 -left-6 w-20 h-20 bg-white rounded-full"></div>
                </div>
                <div class="relative">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-3xl font-bold mb-2 flex items-center">
                                <i class="fas fa-clock mr-3"></i>
                                Overtime Management
                            </h1>
                            <p class="text-purple-100 text-lg">
                                Review, adjust, and finalize employee overtime data
                            </p>
                        </div>
                        <div class="hidden lg:block">
                            <div class="w-20 h-20 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                                <i class="fas fa-clock text-2xl text-white"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Select Month & Year</h3>
            </div>

            <form id="filterForm" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Month</label>
                    <select id="monthSelect" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 text-sm">
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ $m == $currentMonth ? 'selected' : '' }}>
                                {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                            </option>
                        @endfor
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Year</label>
                    <select id="yearSelect" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 text-sm">
                        @for($y = 2023; $y <= 2027; $y++)
                            <option value="{{ $y }}" {{ $y == $currentYear ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>

                <div class="flex items-end">
                    <button type="button" onclick="loadOvertimeData()" class="bg-gradient-to-r from-purple-600 to-pink-600 text-white font-semibold py-2 px-6 rounded-lg hover:from-purple-700 hover:to-pink-700 transition-all duration-300">
                        <i class="fas fa-search mr-2"></i>
                        Load Data
                    </button>
                </div>
            </form>
        </div>

        <!-- Overtime Table -->
        <div id="overtimeTableContainer" class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 overflow-hidden" style="display: none;">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">Overtime Records</h3>
                    <div class="flex items-center space-x-2">
                        <button onclick="saveOvertime()" class="bg-blue-500 hover:bg-blue-600 text-white text-sm font-medium py-2 px-3 rounded-lg transition-colors duration-300">
                            <i class="fas fa-save mr-1"></i>
                            Save
                        </button>
                        <button onclick="lockOvertime()" class="bg-red-500 hover:bg-red-600 text-white text-sm font-medium py-2 px-3 rounded-lg transition-colors duration-300">
                            <i class="fas fa-lock mr-1"></i>
                            Save & Lock
                        </button>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Calculated OT (hrs)</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Adjusted OT (hrs)</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Source</th>
                        </tr>
                    </thead>
                    <tbody id="overtimeTableBody" class="bg-white divide-y divide-gray-200">
                        <!-- Data will be loaded here -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Loading Indicator -->
        <div id="loadingIndicator" class="hidden flex items-center justify-center py-12">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-purple-600"></div>
            <span class="ml-3 text-gray-600">Loading overtime data...</span>
        </div>

        <!-- No Data Message -->
        <div id="noDataMessage" class="hidden bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-12 text-center">
            <i class="fas fa-clock text-4xl text-gray-400 mb-4"></i>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No overtime data found</h3>
            <p class="text-gray-500">Select a month and year to load overtime records.</p>
        </div>
    </div>
</div>

<!-- Lock Confirmation Modal -->
<div id="lockModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full">
        <div class="p-6">
            <div class="flex items-center mb-4">
                <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center mr-3">
                    <i class="fas fa-exclamation-triangle text-red-600"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900">Confirm Lock</h3>
            </div>

            <div class="mb-6">
                <p class="text-gray-600 text-sm mb-4">
                    Once locked, the overtime data will be sent to the payroll system and cannot be modified again. Are you sure you want to proceed?
                </p>
            </div>

            <div class="flex items-center justify-end space-x-3">
                <button type="button" onclick="closeLockModal()" class="px-4 py-2 text-gray-600 hover:text-gray-800 font-medium">
                    Cancel
                </button>
                <button type="button" onclick="confirmLock()" class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-300">
                    <i class="fas fa-lock mr-2"></i>
                    Lock & Send to Payroll
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let currentMonth = {{ $currentMonth }};
let currentYear = {{ $currentYear }};
let overtimeData = [];

function loadOvertimeData() {
    const month = document.getElementById('monthSelect').value;
    const year = document.getElementById('yearSelect').value;

    currentMonth = month;
    currentYear = year;

    showLoading();

    fetch(`/overtime/data?month=${month}&year=${year}`)
        .then(response => response.json())
        .then(data => {
            hideLoading();

            if (data.success) {
                overtimeData = data.data;
                renderOvertimeTable();
            } else {
                showError('Failed to load overtime data: ' + data.message);
            }
        })
        .catch(error => {
            hideLoading();
            showError('Network error: ' + error.message);
        });
}

function renderOvertimeTable() {
    const tbody = document.getElementById('overtimeTableBody');

    if (overtimeData.length === 0) {
        document.getElementById('overtimeTableContainer').style.display = 'none';
        document.getElementById('noDataMessage').style.display = 'block';
        return;
    }

    document.getElementById('overtimeTableContainer').style.display = 'block';
    document.getElementById('noDataMessage').style.display = 'none';

    tbody.innerHTML = overtimeData.map(employee => {
        // Defensive coercion: overtime_hours may be null, object or non-numeric.
        const rawOvertime = employee && employee.overtime_hours;
        let overtimeHours = Number(rawOvertime);
        if (typeof rawOvertime === 'string' && rawOvertime.trim() === '') {
            overtimeHours = 0;
        }
        if (!Number.isFinite(overtimeHours)) {
            overtimeHours = 0;
        }

        const otDisplay = Number.isFinite(overtimeHours) ? overtimeHours.toFixed(2) : '0.00';

        // Defensive name/initials handling
        const name = (employee && employee.employee_name) ? String(employee.employee_name) : '';
        const initials = name.split(' ').filter(Boolean).map(n => n[0]).join('').toUpperCase() || ((employee && employee.employee_payroll_id) ? String(employee.employee_payroll_id).slice(0,2).toUpperCase() : '');

        return `
        <tr class="hover:bg-gray-50 transition-colors duration-200">
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                    <div class="flex-shrink-0 h-10 w-10">
                        <div class="h-10 w-10 rounded-full bg-gradient-to-br from-purple-500 to-pink-600 flex items-center justify-center">
                            <span class="text-white font-medium text-sm">
                                ${initials}
                            </span>
                        </div>
                    </div>
                    <div class="ml-4">
                        <div class="text-sm font-medium text-gray-900">${employee.employee_payroll_id}</div>
                        <div class="text-sm text-gray-500">${employee.employee_name}</div>
                    </div>
                </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                ${otDisplay}h
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <input type="number"
                       step="0.01"
                       min="0"
                       max="999.99"
                       class="w-20 px-2 py-1 border border-gray-300 rounded text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 overtime-input"
                       data-employee-id="${employee.employee_payroll_id}"
                       value="${otDisplay}"
                       ${employee.is_locked ? 'disabled' : ''}>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                ${employee.is_locked ?
                    '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800"><i class="fas fa-lock mr-1"></i>Locked</span>' :
                    '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800"><i class="fas fa-unlock mr-1"></i>Editable</span>'
                }
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    ${employee.source === 'overtime_table' ? 'Manual Override' : 'Biometric Calculation'}
                </span>
            </td>
        </tr>
    `;}).join('');
}

function saveOvertime() {
    const overtimeInputs = document.querySelectorAll('.overtime-input:not([disabled])');
    const data = Array.from(overtimeInputs).map(input => ({
        employee_payroll_id: input.dataset.employeeId,
        overtime_hours: parseFloat(input.value) || 0
    }));

    if (data.length === 0) {
        showError('No data to save');
        return;
    }

    fetch('/overtime/save', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            month: currentMonth,
            year: currentYear,
            overtime_data: data
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccess(data.message);
            loadOvertimeData(); // Reload to show updated status
        } else {
            showError(data.message);
        }
    })
    .catch(error => {
        showError('Network error: ' + error.message);
    });
}

function lockOvertime() {
    document.getElementById('lockModal').classList.remove('hidden');
}

function closeLockModal() {
    document.getElementById('lockModal').classList.add('hidden');
}

function confirmLock() {
    closeLockModal();

    const overtimeInputs = document.querySelectorAll('.overtime-input:not([disabled])');
    const data = Array.from(overtimeInputs).map(input => ({
        employee_payroll_id: input.dataset.employeeId,
        overtime_hours: parseFloat(input.value) || 0
    }));

    if (data.length === 0) {
        showError('No data to lock');
        return;
    }

    fetch('/overtime/lock', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            month: currentMonth,
            year: currentYear,
            overtime_data: data
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccess(data.message);
            loadOvertimeData(); // Reload to show locked status
        } else {
            showError(data.message);
        }
    })
    .catch(error => {
        showError('Network error: ' + error.message);
    });
}

function showLoading() {
    document.getElementById('loadingIndicator').classList.remove('hidden');
    document.getElementById('overtimeTableContainer').style.display = 'none';
    document.getElementById('noDataMessage').style.display = 'none';
}

function hideLoading() {
    document.getElementById('loadingIndicator').classList.add('hidden');
}

function showSuccess(message) {
    // You can implement a toast notification here
    alert('Success: ' + message);
}

function showError(message) {
    // You can implement a toast notification here
    alert('Error: ' + message);
}

// Load data on page load
document.addEventListener('DOMContentLoaded', function() {
    loadOvertimeData();
});
</script>
@endpush
@endsection