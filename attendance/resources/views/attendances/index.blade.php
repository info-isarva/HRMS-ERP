@extends('layouts.app')

@section('title', 'Biometric Attendance Management')

@section('page-title', 'Biometric Attendance')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50">
    <div class="max-w-full mx-auto p-6 space-y-6">

        <!-- Header -->
        <div class="mb-8">
            <div class="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-2xl p-8 text-white relative overflow-hidden">
                <div class="absolute inset-0 opacity-10">
                    <div class="absolute -top-4 -right-4 w-24 h-24 bg-white rounded-full"></div>
                    <div class="absolute top-10 -right-8 w-16 h-16 bg-white rounded-full"></div>
                    <div class="absolute -bottom-6 -left-6 w-20 h-20 bg-white rounded-full"></div>
                </div>
                <div class="relative">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-3xl font-bold mb-2 flex items-center">
                                <i class="fas fa-fingerprint mr-3"></i>
                                Biometric Attendance
                            </h1>
                            <p class="text-indigo-100 text-lg">
                                Upload and manage biometric attendance data
                            </p>
                        </div>
                        <div class="hidden lg:block">
                            <div class="w-32 h-32 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                                <i class="fas fa-fingerprint text-4xl text-white"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Success/Error Messages -->
        @if(session('success'))
            <div class="bg-green-50 border border-green-200 rounded-xl p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-check-circle text-green-400 text-xl"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-circle text-red-400 text-xl"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                    </div>
                </div>
            </div>
        @endif

        @if(session('warning'))
            <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-triangle text-yellow-400 text-xl"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-yellow-800">{{ session('warning') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total Employees -->
            <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-6 hover:shadow-xl transition-all duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-1">Total Employees</p>
                        <p class="text-3xl font-bold text-gray-900">{{ number_format($stats['total_employees']) }}</p>
                    </div>
                    <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center">
                        <i class="fas fa-users text-white text-xl"></i>
                    </div>
                </div>
                <div class="mt-4">
                    <div class="flex items-center text-sm">
                        <i class="fas fa-arrow-up text-green-500 mr-1"></i>
                        <span class="text-green-600 font-medium">Active workforce</span>
                    </div>
                </div>
            </div>

            <!-- Today's Records -->
            <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-6 hover:shadow-xl transition-all duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-1">Today's Records</p>
                        <p class="text-3xl font-bold text-gray-900">{{ number_format($stats['today_attendance']) }}</p>
                    </div>
                    <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-green-600 rounded-xl flex items-center justify-center">
                        <i class="fas fa-calendar-check text-white text-xl"></i>
                    </div>
                </div>
                <div class="mt-4">
                    <div class="flex items-center text-sm">
                        <i class="fas fa-clock text-blue-500 mr-1"></i>
                        <span class="text-blue-600 font-medium">Real-time data</span>
                    </div>
                </div>
            </div>

            <!-- Recent Uploads -->
            <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-6 hover:shadow-xl transition-all duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-1">Recent Uploads</p>
                        <p class="text-3xl font-bold text-gray-900">{{ $stats['recent_uploads']->sum('records') ?? 0 }}</p>
                    </div>
                    <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl flex items-center justify-center">
                        <i class="fas fa-upload text-white text-xl"></i>
                    </div>
                </div>
                <div class="mt-4">
                    <div class="flex items-center text-sm">
                        <i class="fas fa-history text-orange-500 mr-1"></i>
                        <span class="text-orange-600 font-medium">{{ $stats['recent_uploads']->count() }} sessions</span>
                    </div>
                </div>
            </div>

            <!-- Upload Sessions -->
            <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-6 hover:shadow-xl transition-all duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-1">Upload Sessions</p>
                        <p class="text-3xl font-bold text-gray-900">{{ $stats['recent_uploads']->count() }}</p>
                    </div>
                    <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-xl flex items-center justify-center">
                        <i class="fas fa-history text-white text-xl"></i>
                    </div>
                </div>
                <div class="mt-4">
                    <div class="flex items-center text-sm">
                        <i class="fas fa-chart-line text-indigo-500 mr-1"></i>
                        <span class="text-indigo-600 font-medium">Activity tracking</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Upload Sections -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Excel Upload Section -->
                <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-6">
                    <div class="flex items-center mb-6">
                        <div class="w-10 h-10 bg-gradient-to-br from-green-500 to-green-600 rounded-xl flex items-center justify-center mr-3">
                            <i class="fas fa-file-excel text-white"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900">Upload Excel File</h3>
                            <p class="text-gray-600 text-sm">Import attendance records from Excel spreadsheets</p>
                        </div>
                    </div>

                    <form action="{{ route('attendance.upload') }}" method="POST" enctype="multipart/form-data" id="uploadForm">
                        @csrf

                        <!-- File Upload -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Select Excel File</label>
                            <div class="relative">
                                <input type="file" name="excel_file" id="excel_file" accept=".xlsx,.xls,.csv"
                                       class="hidden" required>
                                <label for="excel_file" class="flex items-center justify-center w-full h-32 border-2 border-dashed border-gray-300 rounded-xl cursor-pointer hover:border-green-400 hover:bg-green-50 transition-all duration-300">
                                    <div class="text-center">
                                        <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 mb-2"></i>
                                        <p class="text-gray-600 font-medium">Click to upload Excel file</p>
                                        <p class="text-gray-500 text-sm">Supported: .xlsx, .xls, .csv (Max: 50MB)</p>
                                    </div>
                                </label>
                            </div>
                            <div id="fileInfo" class="mt-2 hidden">
                                <div class="flex items-center text-sm text-green-600">
                                    <i class="fas fa-check-circle mr-2"></i>
                                    <span id="fileName"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Format Information -->
                        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-4 mb-6">
                            <div class="flex items-start">
                                <i class="fas fa-info-circle text-blue-500 mt-1 mr-3"></i>
                                <div>
                                    <h4 class="font-semibold text-gray-900 mb-2">Expected Format</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm text-gray-700">
                                        <div><strong>payroll_id</strong> or <strong>employee_id</strong>: Employee identifier</div>
                                        <div><strong>date</strong> or <strong>attendance_date</strong>: Date (YYYY-MM-DD)</div>
                                        <div><strong>check_in_time</strong> or <strong>check_in</strong>: Check-in time</div>
                                        <div><strong>check_out_time</strong> or <strong>check_out</strong>: Check-out time</div>
                                        <div><strong>shift_name</strong> (optional): Shift name</div>
                                        <div><strong>department</strong> (optional): Department name</div>
                                        <div><strong>notes</strong> (optional): Additional notes</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Upload Button -->
                        <button type="submit" id="uploadBtn"
                                class="w-full bg-gradient-to-r from-green-600 to-green-700 text-white font-semibold py-3 px-6 rounded-xl hover:from-green-700 hover:to-green-800 transition-all duration-300 flex items-center justify-center">
                            <i class="fas fa-upload mr-2"></i>
                            Upload Excel & Process
                        </button>
                    </form>
                </div>

                <!-- Biometric Device Upload Section -->
                <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-6">
                    <div class="flex items-center mb-6">
                        <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center mr-3">
                            <i class="fas fa-fingerprint text-white"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900">Upload Biometric Device File</h3>
                            <p class="text-gray-600 text-sm">Import attendance from biometric machines (pendrive data)</p>
                        </div>
                    </div>

                    <form action="{{ route('attendance.upload-biometric') }}" method="POST" enctype="multipart/form-data" id="biometricForm">
                        @csrf

                        <!-- Device Format Selection -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Device Type / Format</label>
                            <select name="device_format" id="device_format" required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
                                <option value="">-- Select Device Format --</option>
                                @foreach($formats as $key => $parser)
                                    <option value="{{ $key }}">{{ $parser->getFormatName() }} ({{ implode(', ', array_map(function($ext) { return '.' . $ext; }, $parser->getSupportedExtensions())) }})</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- File Upload -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Select Biometric File</label>
                            <div class="relative">
                                <input type="file" name="biometric_file" id="biometric_file" 
                                       accept=".dat,.att,.csv,.txt,.log"
                                       class="hidden" required>
                                <label for="biometric_file" class="flex items-center justify-center w-full h-32 border-2 border-dashed border-gray-300 rounded-xl cursor-pointer hover:border-indigo-400 hover:bg-indigo-50 transition-all duration-300">
                                    <div class="text-center">
                                        <i class="fas fa-usb text-3xl text-gray-400 mb-2"></i>
                                        <p class="text-gray-600 font-medium">Click to upload biometric file</p>
                                        <p class="text-gray-500 text-sm">From pendrive: .dat, .att, .csv, .txt, .log</p>
                                    </div>
                                </label>
                            </div>
                            <div id="biometricFileInfo" class="mt-2 hidden">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center text-sm text-indigo-600">
                                        <i class="fas fa-check-circle mr-2"></i>
                                        <span id="biometricFileName"></span>
                                    </div>
                                    <button type="button" id="autoDetectBtn" 
                                            class="text-xs bg-blue-100 text-blue-700 px-3 py-1 rounded-lg hover:bg-blue-200 transition-colors">
                                        <i class="fas fa-magic mr-1"></i> Auto-detect Format
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Device Format Info -->
                        <div class="bg-gradient-to-r from-purple-50 to-indigo-50 rounded-xl p-4 mb-6">
                            <div class="flex items-start">
                                <i class="fas fa-info-circle text-purple-500 mt-1 mr-3"></i>
                                <div>
                                    <h4 class="font-semibold text-gray-900 mb-2">Supported Devices</h4>
                                    <ul class="text-sm text-gray-700 space-y-1">
                                        <li><strong>ZKTeco:</strong> .dat, .att files (tab-separated)</li>
                                        <li><strong>eSSL:</strong> .csv files (employee ID, date, time)</li>
                                        <li><strong>Realtime:</strong> .txt, .log files (space-separated)</li>
                                        <li><strong>Generic CSV:</strong> Any CSV with attendance data</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Upload Button -->
                        <button type="submit" id="biometricUploadBtn"
                                class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold py-3 px-6 rounded-xl hover:from-indigo-700 hover:to-purple-700 transition-all duration-300 flex items-center justify-center">
                            <i class="fas fa-fingerprint mr-2"></i>
                            Upload Biometric Data & Process
                        </button>
                    </form>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">

                <!-- Quick Actions -->
                <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-6">
                    <div class="flex items-center mb-4">
                        <div class="w-8 h-8 bg-gradient-to-br from-green-500 to-green-600 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-bolt text-white text-sm"></i>
                        </div>
                        <h4 class="font-bold text-gray-900">Quick Actions</h4>
                    </div>

                    <div class="space-y-3">
                        <a href="{{ route('attendance.template') }}"
                           class="flex items-center w-full bg-gradient-to-r from-blue-500 to-blue-600 text-white font-medium py-3 px-4 rounded-xl hover:from-blue-600 hover:to-blue-700 transition-all duration-300">
                            <i class="fas fa-download mr-3"></i>
                            Download Template
                        </a>

                        <a href="{{ route('attendance.records') }}"
                           class="flex items-center w-full bg-gradient-to-r from-indigo-500 to-indigo-600 text-white font-medium py-3 px-4 rounded-xl hover:from-indigo-600 hover:to-indigo-700 transition-all duration-300">
                            <i class="fas fa-list mr-3"></i>
                            View Records
                        </a>

                        <a href="{{ route('attendance.export') }}"
                           class="flex items-center w-full bg-gradient-to-r from-green-500 to-green-600 text-white font-medium py-3 px-4 rounded-xl hover:from-green-600 hover:to-green-700 transition-all duration-300">
                            <i class="fas fa-file-export mr-3"></i>
                            Export Data
                        </a>
                    </div>
                </div>

                <!-- Recent Uploads -->
                @if($stats['recent_uploads']->count() > 0)
                <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-6">
                    <div class="flex items-center mb-4">
                        <div class="w-8 h-8 bg-gradient-to-br from-orange-500 to-orange-600 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-history text-white text-sm"></i>
                        </div>
                        <h4 class="font-bold text-gray-900">Recent Uploads</h4>
                    </div>

                    <div class="space-y-3">
                        @foreach($stats['recent_uploads']->take(5) as $upload)
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900">{{ $upload->processed_at->format('M d, H:i') }}</p>
                                <p class="text-xs text-gray-500">{{ $upload->processed_at->format('Y') }}</p>
                            </div>
                            <div class="bg-indigo-100 text-indigo-800 text-xs font-semibold px-2 py-1 rounded-full">
                                {{ number_format($upload->records) }}
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Help Card -->
                <div class="bg-gradient-to-br from-indigo-50 to-purple-50 rounded-2xl p-6 border border-indigo-100">
                    <div class="flex items-center mb-3">
                        <i class="fas fa-question-circle text-indigo-500 text-xl mr-3"></i>
                        <h4 class="font-bold text-gray-900">Need Help?</h4>
                    </div>
                    <p class="text-sm text-gray-600 mb-3">
                        Upload Excel files containing biometric attendance data. The system will automatically process and validate the records.
                    </p>
                    <div class="text-xs text-gray-500">
                        <strong>Tip:</strong> Use the template for correct formatting
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Excel file upload handling
document.getElementById('excel_file').addEventListener('change', function(e) {
    const file = e.target.files[0];
    const fileInfo = document.getElementById('fileInfo');
    const fileName = document.getElementById('fileName');

    if (file) {
        fileName.textContent = file.name + ' (' + (file.size / 1024 / 1024).toFixed(2) + ' MB)';
        fileInfo.classList.remove('hidden');
    } else {
        fileInfo.classList.add('hidden');
    }
});

// Biometric file upload handling
document.getElementById('biometric_file').addEventListener('change', function(e) {
    const file = e.target.files[0];
    const fileInfo = document.getElementById('biometricFileInfo');
    const fileName = document.getElementById('biometricFileName');

    if (file) {
        fileName.textContent = file.name + ' (' + (file.size / 1024 / 1024).toFixed(2) + ' MB)';
        fileInfo.classList.remove('hidden');
    } else {
        fileInfo.classList.add('hidden');
    }
});

// Auto-detect format
document.getElementById('autoDetectBtn')?.addEventListener('click', async function() {
    const fileInput = document.getElementById('biometric_file');
    const formatSelect = document.getElementById('device_format');
    const btn = this;

    if (!fileInput.files || fileInput.files.length === 0) {
        alert('Please select a file first');
        return;
    }

    const originalHtml = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Detecting...';
    btn.disabled = true;

    const formData = new FormData();
    formData.append('file', fileInput.files[0]);
    formData.append('_token', '{{ csrf_token() }}');

    try {
        const response = await fetch('{{ route('attendance.detect-format') }}', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            formatSelect.value = result.format;
            alert('Format detected: ' + result.format_name);
        } else {
            alert(result.message || 'Could not detect format automatically');
        }
    } catch (error) {
        alert('Error detecting format: ' + error.message);
    } finally {
        btn.innerHTML = originalHtml;
        btn.disabled = false;
    }
});

// Drag and drop functionality for Excel upload
const uploadArea = document.querySelector('label[for="excel_file"]');
const fileInput = document.getElementById('excel_file');

['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
    uploadArea?.addEventListener(eventName, preventDefaults, false);
});

function preventDefaults(e) {
    e.preventDefault();
    e.stopPropagation();
}

['dragenter', 'dragover'].forEach(eventName => {
    uploadArea?.addEventListener(eventName, highlight, false);
});

['dragleave', 'drop'].forEach(eventName => {
    uploadArea?.addEventListener(eventName, unhighlight, false);
});

function highlight(e) {
    uploadArea.classList.add('border-green-500', 'bg-green-50');
}

function unhighlight(e) {
    uploadArea.classList.remove('border-green-500', 'bg-green-50');
}

uploadArea?.addEventListener('drop', handleDrop, false);

function handleDrop(e) {
    const dt = e.dataTransfer;
    const files = dt.files;

    if (files.length > 0) {
        fileInput.files = files;
        fileInput.dispatchEvent(new Event('change'));
    }
}

// Drag and drop for biometric upload
const biometricUploadArea = document.querySelector('label[for="biometric_file"]');
const biometricFileInput = document.getElementById('biometric_file');

['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
    biometricUploadArea?.addEventListener(eventName, preventDefaults, false);
});

['dragenter', 'dragover'].forEach(eventName => {
    biometricUploadArea?.addEventListener(eventName, highlightBiometric, false);
});

['dragleave', 'drop'].forEach(eventName => {
    biometricUploadArea?.addEventListener(eventName, unhighlightBiometric, false);
});

function highlightBiometric(e) {
    biometricUploadArea.classList.add('border-indigo-500', 'bg-indigo-50');
}

function unhighlightBiometric(e) {
    biometricUploadArea.classList.remove('border-indigo-500', 'bg-indigo-50');
}

biometricUploadArea?.addEventListener('drop', handleBiometricDrop, false);

function handleBiometricDrop(e) {
    const dt = e.dataTransfer;
    const files = dt.files;

    if (files.length > 0) {
        biometricFileInput.files = files;
        biometricFileInput.dispatchEvent(new Event('change'));
    }
}

// Form submission with loading state for Excel
document.getElementById('uploadForm')?.addEventListener('submit', function(e) {
    const btn = document.getElementById('uploadBtn');
    const originalHtml = btn.innerHTML;

    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processing...';
    btn.disabled = true;
    btn.classList.add('opacity-75', 'cursor-not-allowed');

    // Re-enable after 30 seconds as fallback
    setTimeout(() => {
        btn.innerHTML = originalHtml;
        btn.disabled = false;
        btn.classList.remove('opacity-75', 'cursor-not-allowed');
    }, 30000);
});

// Form submission with loading state for Biometric
document.getElementById('biometricForm')?.addEventListener('submit', function(e) {
    const formatSelect = document.getElementById('device_format');
    if (!formatSelect.value) {
        e.preventDefault();
        alert('Please select device format');
        return;
    }

    const btn = document.getElementById('biometricUploadBtn');
    const originalHtml = btn.innerHTML;

    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processing Biometric Data...';
    btn.disabled = true;
    btn.classList.add('opacity-75', 'cursor-not-allowed');

    // Re-enable after 30 seconds as fallback
    setTimeout(() => {
        btn.innerHTML = originalHtml;
        btn.disabled = false;
        btn.classList.remove('opacity-75', 'cursor-not-allowed');
    }, 30000);
});
</script>
@endpush
@endsection