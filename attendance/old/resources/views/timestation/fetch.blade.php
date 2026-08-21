@extends('layouts.app')

@section('title', 'Fetch TimeStation Attendance - HRMS')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 p-6">
    <div class="max-w-full mx-auto space-y-6">
        <!-- Header -->
        <div class="bg-white/80 backdrop-blur-sm shadow-xl rounded-2xl border border-white/20 overflow-hidden">
            <div class="bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 px-8 py-10 relative">
                <div class="relative flex flex-col md:flex-row items-center justify-between gap-6">
                    <div class="flex items-center">
                        <div class="w-16 h-16 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center border border-white/30 shadow-inner">
                            <i class="fas fa-cloud-download-alt text-white text-3xl"></i>
                        </div>
                        <div class="ml-6 text-white">
                            <h1 class="text-3xl font-bold">TimeStation Fetcher</h1>
                            <p class="text-indigo-100 italic">Sync and process attendance with long-shift rules</p>
                        </div>
                    </div>

                    <form action="{{ route('timestation.fetch.process') }}" method="POST" class="flex flex-wrap items-center gap-4 bg-white/10 p-4 rounded-2xl backdrop-blur-md border border-white/20">
                        @csrf
                        <div class="flex flex-col">
                            <label class="text-xs font-bold text-white uppercase mb-1 ml-1 opacity-80">Select Month</label>
                            <input type="month" name="month_year" value="{{ $monthYear }}" class="px-4 py-2 rounded-xl bg-white/90 border-0 focus:ring-2 focus:ring-white outline-none font-semibold text-indigo-900 shadow-sm" onchange="window.location.href='{{ route('timestation.fetch.index') }}?month_year=' + this.value">
                        </div>
                        <button type="submit" class="mt-5 px-6 py-2 bg-white text-indigo-600 font-bold rounded-xl shadow-lg hover:bg-indigo-50 transition-all flex items-center gap-2 transform hover:scale-105 active:scale-95 {{ $isLocked ?? false ? 'opacity-50 cursor-not-allowed' : '' }}" {{ $isLocked ?? false ? 'disabled' : '' }}>
                            <i class="fas fa-sync-alt"></i> Fetch & Process
                        </button>
                    </form>
                </div>
            </div>

            <!-- Stats Bar with Filters -->
            <div class="px-8 py-4 bg-slate-50 border-t border-gray-100">
                <div class="flex flex-wrap gap-4 items-center text-sm mb-4">
                    <div class="flex items-center gap-2">
                        <span class="text-gray-500 font-medium">Month:</span>
                        <span class="font-bold text-indigo-600">{{ Carbon\Carbon::parse($monthYear)->format('F Y') }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-gray-500 font-medium">Proposed Records:</span>
                        <span class="font-bold text-gray-900" id="totalRecords">{{ $proposed->count() }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-gray-500 font-medium">Overrides:</span>
                        <span class="font-bold text-orange-600">{{ $proposed->where('is_overridden', true)->count() }}</span>
                    </div>
                    @if($isLocked ?? false)
                    <div class="flex items-center gap-2 bg-red-100 px-3 py-1 rounded-lg">
                        <i class="fas fa-lock text-red-600"></i>
                        <span class="text-red-700 font-bold text-xs uppercase">Month Locked</span>
                    </div>
                    @endif
                    
                    <div class="ml-auto">
                        @if(!($isLocked ?? false))
                        <button type="button" onclick="showFinalizeWarning()" class="px-6 py-2 bg-gradient-to-r from-emerald-500 to-teal-600 text-white font-bold rounded-xl shadow-lg hover:shadow-emerald-200 transition-all transform hover:scale-105">
                            <i class="fas fa-check-double mr-2"></i> Finalize Month
                        </button>
                        @else
                        <div class="px-6 py-2 bg-gray-300 text-gray-600 font-bold rounded-xl cursor-not-allowed">
                            <i class="fas fa-lock mr-2"></i> Month Finalized
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Search and Filter Bar -->
                <div class="flex flex-wrap gap-3 items-center">
                    <div class="flex-1 min-w-[200px]">
                        <input type="text" id="searchEmployee" placeholder="Search by employee name or ID..." class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500 outline-none text-sm">
                    </div>
                    <select id="filterStatus" class="px-4 py-2 pr-10 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500 outline-none text-sm font-medium">
                        <option value="">All Statuses</option>
                        <option value="present">Present</option>
                        <option value="compoff">Comp Off</option>
                        <option value="absent">Absent</option>
                        <option value="late">Late</option>
                    </select>
                    <select id="filterOverride" class="px-4 py-2 pr-10 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500 outline-none text-sm font-medium">
                        <option value="">All Records</option>
                        <option value="overridden">Overridden Only</option>
                        <option value="original">Original Only</option>
                    </select>
                    <button onclick="resetFilters()" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg transition-colors text-sm">
                        <i class="fas fa-redo mr-1"></i> Reset
                    </button>
                </div>
            </div>
        </div>

        <!-- Proposed Records Table -->
        <div class="bg-white/80 backdrop-blur-sm shadow-xl rounded-2xl border border-white/20 overflow-hidden">
            <div class="p-0 overflow-x-auto">
                <table class="w-full text-left border-collapse" id="attendanceTable">
                    <thead>
                        <tr class="bg-slate-50 border-b border-gray-100">
                            <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Employee</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">In / Out</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Total Hrs</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Source Status</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Notes</th>
                            @if(!($isLocked ?? false))
                            <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Actions</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($proposed as $row)
                            <tr class="hover:bg-blue-50/30 transition-colors {{ $row->is_overridden ? 'bg-orange-50/20' : '' }} attendance-row" 
                                data-employee="{{ strtolower($row->employee->name ?? '') }} {{ $row->employee_payroll_id }}"
                                data-status="{{ $row->status }}"
                                data-override="{{ $row->is_overridden ? 'overridden' : 'original' }}">
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold border-2 border-white shadow-sm">
                                            {{ substr($row->employee->name ?? '?', 0, 1) }}
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm font-bold text-gray-900">{{ $row->employee->name ?? 'Unknown' }}</p>
                                            <p class="text-xs text-gray-500">ID: {{ $row->employee_payroll_id }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-sm font-semibold text-gray-700">{{ $row->date->format('d M, Y') }}</p>
                                    <p class="text-xs text-gray-400">{{ $row->date->format('l') }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <span class="px-2 py-1 bg-green-50 text-green-700 text-xs font-bold rounded">{{ $row->check_in ?? '--:--' }}</span>
                                        <span class="text-gray-300">→</span>
                                        <span class="px-2 py-1 bg-red-50 text-red-700 text-xs font-bold rounded">{{ $row->check_out ?? '--:--' }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 font-bold text-gray-700">
                                    {{ $row->total_hours }} hrs
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 bg-{{ $row->status == 'present' ? 'green' : ($row->status == 'compoff' ? 'indigo' : 'red') }}-100 text-{{ $row->status == 'present' ? 'green' : ($row->status == 'compoff' ? 'indigo' : 'red') }}-700 text-xs font-bold rounded-full uppercase tracking-tighter">
                                        {{ $row->status }}
                                    </span>
                                    @if($row->is_overridden)
                                        <div class="text-[10px] text-orange-600 font-bold mt-1 flex items-center gap-1">
                                            <i class="fas fa-exclamation-triangle"></i> OVERRIDDEN
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-xs text-gray-400 uppercase">
                                    {{ $row->source_status ?? 'Unknown' }}
                                </td>
                                <td class="px-6 py-4 max-w-xs">
                                    <p class="text-xs text-gray-600 truncate" title="{{ $row->notes }}">{{ $row->notes ?? '--' }}</p>
                                </td>
                                @if(!($isLocked ?? false))
                                <td class="px-6 py-4">
                                    <button onclick="openOverrideModal({{ $row }})" class="p-2 text-indigo-600 hover:bg-indigo-100 rounded-lg transition-colors" title="Override Status">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $isLocked ?? false ? '7' : '8' }}" class="px-6 py-12 text-center text-gray-400 italic">
                                    No data available. Use the fetcher above to sync with TimeStation.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Finalize Warning Modal -->
<div id="finalizeModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden transform transition-all">
        <div class="bg-gradient-to-r from-red-600 to-orange-600 p-6">
            <h3 class="text-2xl font-bold text-white flex items-center gap-3">
                <i class="fas fa-exclamation-triangle"></i> Finalize Month - Warning
            </h3>
        </div>
        <div class="p-8 space-y-6">
            <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-r-lg">
                <p class="text-red-800 font-semibold mb-2">⚠️ This action cannot be undone!</p>
                <p class="text-red-700 text-sm">Once finalized, this month's attendance will be:</p>
                <ul class="list-disc list-inside text-red-700 text-sm mt-2 space-y-1">
                    <li>Moved to the main attendance table</li>
                    <li>Sent to "Process Attendance" for payroll</li>
                    <li><strong>Locked and cannot be edited</strong></li>
                </ul>
            </div>
            
            <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-r-lg">
                <p class="text-blue-800 font-semibold mb-2">📊 Summary:</p>
                <div class="grid grid-cols-2 gap-3 text-sm">
                    <div>
                        <span class="text-blue-600">Month:</span>
                        <span class="font-bold text-blue-900 ml-2">{{ Carbon\Carbon::parse($monthYear)->format('F Y') }}</span>
                    </div>
                    <div>
                        <span class="text-blue-600">Records:</span>
                        <span class="font-bold text-blue-900 ml-2">{{ $proposed->count() }}</span>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4">
                <button type="button" onclick="closeFinalizeModal()" class="px-6 py-3 text-gray-600 font-bold hover:bg-gray-100 rounded-xl transition-all">
                    Cancel
                </button>
                <form action="{{ route('timestation.fetch.finalize') }}" method="POST" class="inline">
                    @csrf
                    <input type="hidden" name="month_year" value="{{ $monthYear }}">
                    <button type="submit" class="px-8 py-3 bg-gradient-to-r from-red-600 to-orange-600 text-white font-bold rounded-xl shadow-lg hover:shadow-red-200 transition-all">
                        <i class="fas fa-check-double mr-2"></i> Yes, Finalize Month
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Override Modal -->
<div id="overrideModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden transform transition-all">
        <div class="bg-gradient-to-r from-indigo-600 to-purple-600 p-6">
            <h3 class="text-xl font-bold text-white flex items-center gap-2">
                <i class="fas fa-user-edit"></i> Override Attendance
            </h3>
        </div>
        <form id="overrideForm" class="p-8 space-y-6">
            <input type="hidden" id="overrideId">
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">New Status</label>
                <select id="newStatus" class="w-full px-4 py-3 rounded-xl border border-gray-200 outline-none focus:ring-2 focus:ring-indigo-500 font-semibold">
                    <option value="present">Present</option>
                    <option value="compoff">Comp Off</option>
                    <option value="absent">Absent</option>
                    <option value="late">Late Arrival</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Notes / Rationale</label>
                <textarea id="overrideNotes" rows="3" class="w-full px-4 py-3 rounded-xl border border-gray-200 outline-none focus:ring-2 focus:ring-indigo-500 text-sm" placeholder="Why is this being overridden?"></textarea>
            </div>
            <div class="flex items-center justify-end gap-3">
                <button type="button" onclick="closeOverrideModal()" class="px-6 py-2 text-gray-500 font-bold hover:bg-gray-50 rounded-xl transition-all">Cancel</button>
                <button type="submit" class="px-8 py-2 bg-indigo-600 text-white font-bold rounded-xl shadow-lg hover:bg-indigo-700 transition-all">Save Changes</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
// Finalize Modal
function showFinalizeWarning() {
    document.getElementById('finalizeModal').classList.remove('hidden');
    document.getElementById('finalizeModal').classList.add('flex');
}

function closeFinalizeModal() {
    document.getElementById('finalizeModal').classList.add('hidden');
    document.getElementById('finalizeModal').classList.remove('flex');
}

// Override Modal
function openOverrideModal(row) {
    document.getElementById('overrideId').value = row.id;
    document.getElementById('newStatus').value = row.status;
    document.getElementById('overrideNotes').value = row.notes || '';
    document.getElementById('overrideModal').classList.remove('hidden');
    document.getElementById('overrideModal').classList.add('flex');
}

function closeOverrideModal() {
    document.getElementById('overrideModal').classList.add('hidden');
    document.getElementById('overrideModal').classList.remove('flex');
}

document.getElementById('overrideForm').onsubmit = function(e) {
    e.preventDefault();
    const id = document.getElementById('overrideId').value;
    const status = document.getElementById('newStatus').value;
    const notes = document.getElementById('overrideNotes').value;

    fetch("{{ route('timestation.fetch.override') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ id, status, notes })
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            location.reload();
        } else {
            alert('Error updating status');
        }
    });
};

// Search and Filter Functionality
const searchInput = document.getElementById('searchEmployee');
const statusFilter = document.getElementById('filterStatus');
const overrideFilter = document.getElementById('filterOverride');
const rows = document.querySelectorAll('.attendance-row');

function applyFilters() {
    const searchTerm = searchInput.value.toLowerCase();
    const statusValue = statusFilter.value.toLowerCase();
    const overrideValue = overrideFilter.value;
    
    let visibleCount = 0;
    
    rows.forEach(row => {
        const employeeData = row.dataset.employee;
        const status = row.dataset.status;
        const override = row.dataset.override;
        
        const matchesSearch = employeeData.includes(searchTerm);
        const matchesStatus = !statusValue || status === statusValue;
        const matchesOverride = !overrideValue || override === overrideValue;
        
        if (matchesSearch && matchesStatus && matchesOverride) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });
    
    // Update total count
    document.getElementById('totalRecords').textContent = visibleCount;
}

function resetFilters() {
    searchInput.value = '';
    statusFilter.value = '';
    overrideFilter.value = '';
    applyFilters();
}

searchInput.addEventListener('input', applyFilters);
statusFilter.addEventListener('change', applyFilters);
overrideFilter.addEventListener('change', applyFilters);
</script>
@endpush
@endsection
