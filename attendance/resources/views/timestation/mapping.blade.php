@extends('layouts.app')

@section('title', 'TimeStation Mapping')

@section('content')
<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">TimeStation User Mapping</h1>
            <p class="text-gray-600">Link TimeStation users to HRMS employees for attendance synchronization.</p>
        </div>
        <div class="space-x-2">
            <button onclick="syncNow()" id="syncBtn" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">
                <i class="fas fa-sync-alt mr-2"></i> Sync Now
            </button>
        </div>
    </div>

    <!-- Stats / Info -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <!-- Unmapped Users -->
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-yellow-500">
            <h2 class="text-lg font-semibold mb-4 text-gray-800 flex justify-between">
                <span>Unmapped TimeStation Users</span>
                <span class="bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded-full" id="unmappedCount">0 Pending</span>
            </h2>
            <div id="unmappedList" class="space-y-3 max-h-96 overflow-y-auto">
                <div class="text-center text-gray-500 py-4">Loading...</div>
            </div>
        </div>

        <!-- Mapped Users -->
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-500">
            <h2 class="text-lg font-semibold mb-4 text-gray-800">Mapped Users</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">TimeStation Name</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Mapped To</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($mappings as $mapping)
                        <tr>
                            <td class="px-3 py-2 text-sm text-gray-900">{{ $mapping->ts_name ?: $mapping->ts_user_id }}</td>
                            <td class="px-3 py-2 text-sm text-gray-600">
                                @if($mapping->is_ignored)
                                    <span class="text-red-500 italic">Ignored</span>
                                @elseif($mapping->employee)
                                    {{ $mapping->employee->name }} ({{ $mapping->employee->employee_id }})
                                @else
                                    <span class="text-red-400">Invalid Link</span>
                                @endif
                            </td>
                            <td class="px-3 py-2">
                                <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">Linked</span>
                            </td>
                        </tr>
                        @endforeach
                        @if($mappings->isEmpty())
                        <tr>
                            <td colspan="3" class="px-3 py-4 text-center text-gray-500 text-sm">No mappings found.</td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div id="mappingModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modalTitle">Map User</h3>
            <div class="mt-2 py-3">
                <p class="text-sm text-gray-500 mb-4">
                    Select the HRMS employee that corresponds to <strong id="modalTsName"></strong>.
                </p>
                
                <input type="hidden" id="modalTsId">
                <input type="hidden" id="modalRawName">
                
                <!-- Search Box -->
                <div class="relative">
                    <input type="text" id="employeeSearch" 
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" 
                        placeholder="Search employee by name or ID...">
                    <div id="searchResults" class="absolute z-10 w-full bg-white border mt-1 rounded-md shadow-lg hidden max-h-40 overflow-y-auto">
                        <!-- Results -->
                    </div>
                </div>
                
                <div id="selectedEmployee" class="mt-4 p-3 bg-blue-50 text-blue-800 rounded hidden">
                    Selected: <span id="selectedEmployeeName" class="font-bold"></span>
                    <input type="hidden" id="selectedPayrollId">
                </div>
            </div>
            <div class="flex justify-end space-x-3 mt-4">
                <button onclick="closeModal()" class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300">Cancel</button>
                <button onclick="ignoreUser()" class="px-4 py-2 bg-red-100 text-red-700 rounded hover:bg-red-200">Ignore User</button>
                <button onclick="saveMapping()" id="saveBtn" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50" disabled>Confirm Link</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', loadUnmapped);

    function loadUnmapped() {
        fetch('/timestation/unmapped')
            .then(r => r.json())
            .then(data => {
                const container = document.getElementById('unmappedList');
                document.getElementById('unmappedCount').textContent = data.length + ' Pending';
                
                if (data.length === 0) {
                    container.innerHTML = '<div class="text-center text-gray-500 py-4">All users mapped!</div>';
                    return;
                }

                container.innerHTML = data.map(u => `
                    <div class="flex justify-between items-center p-3 bg-gray-50 rounded border border-gray-200 hover:bg-gray-100 transition">
                        <div>
                            <div class="font-medium text-gray-800">${u.name}</div>
                            <div class="text-xs text-gray-500">ID: ${u.ts_user_id} | Last Seen: ${u.last_seen}</div>
                        </div>
                        <button onclick="openModal('${u.ts_user_id}', '${u.name.replace(/'/g, "\\'")}')" 
                            class="px-3 py-1 bg-white border border-blue-500 text-blue-600 rounded text-sm hover:bg-blue-50">
                            Map
                        </button>
                    </div>
                `).join('');
            });
    }

    // Modal Logic
    function openModal(id, name) {
        document.getElementById('modalTsId').value = id;
        document.getElementById('modalRawName').value = name;
        document.getElementById('modalTsName').textContent = name;
        document.getElementById('mappingModal').classList.remove('hidden');
        document.getElementById('employeeSearch').value = '';
        document.getElementById('selectedEmployee').classList.add('hidden');
        document.getElementById('saveBtn').disabled = true;
    }

    function closeModal() {
        document.getElementById('mappingModal').classList.add('hidden');
    }

    // Search Logic
    let debounceTimer;
    document.getElementById('employeeSearch').addEventListener('input', function(e) {
        clearTimeout(debounceTimer);
        const term = e.target.value;
        if (term.length < 2) {
            document.getElementById('searchResults').classList.add('hidden');
            return;
        }

        debounceTimer = setTimeout(() => {
            fetch(`/timestation/search-employees?term=${term}`)
                .then(r => r.json())
                .then(data => {
                    const results = document.getElementById('searchResults');
                    results.innerHTML = data.map(emp => `
                        <div onclick="selectEmployee(${emp.payroll_id}, '${emp.name} (${emp.employee_id})')" 
                            class="p-2 hover:bg-blue-50 cursor-pointer border-b">
                            <div class="font-bold">${emp.name}</div>
                            <div class="text-xs text-gray-500">${emp.employee_id}</div>
                        </div>
                    `).join('');
                    results.classList.remove('hidden');
                });
        }, 300);
    });

    function selectEmployee(payrollId, name) {
        document.getElementById('selectedPayrollId').value = payrollId;
        document.getElementById('selectedEmployeeName').textContent = name;
        document.getElementById('selectedEmployee').classList.remove('hidden');
        document.getElementById('searchResults').classList.add('hidden');
        document.getElementById('saveBtn').disabled = false;
    }

    function saveMapping() {
        const tsId = document.getElementById('modalTsId').value;
        const payrollId = document.getElementById('selectedPayrollId').value;
        const tsName = document.getElementById('modalRawName').value;

        fetch('/timestation/map', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                ts_user_id: tsId,
                employee_payroll_id: payrollId,
                ts_name: tsName
            })
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                closeModal();
                loadUnmapped();
                location.reload(); // Refresh to show in table
            }
        });
    }

    function ignoreUser() {
        if(!confirm('Are you sure you want to ignore this user? Logs will be skipped.')) return;
        
        const tsId = document.getElementById('modalTsId').value;
        const tsName = document.getElementById('modalRawName').value;

        fetch('/timestation/ignore', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ ts_user_id: tsId, ts_name: tsName })
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                closeModal();
                loadUnmapped();
                location.reload();
            }
        });
    }

    function syncNow() {
        const btn = document.getElementById('syncBtn');
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Syncing...';

        fetch('/timestation/sync-now', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(r => r.json())
        .then(res => {
            alert(res.success ? `Synced ${res.count} records!` : `Error: ${res.error}`);
            loadUnmapped();
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = originalText;
        });
    }
</script>
@endpush
@endsection
