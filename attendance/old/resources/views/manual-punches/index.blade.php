@extends('layouts.app')

@section('title', 'Manual Punch Entry - HRMS')

@section('page-title', 'Manual Punch Entry')

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
                                <i class="fas fa-user-clock text-white text-2xl"></i>
                            </div>
                        </div>
                        <div class="ml-6">
                            <h1 class="text-3xl font-bold text-white mb-2">Manual Punch Entry</h1>
                            <p class="text-indigo-100 text-lg">
                                Manage missing biometric punches for employees
                            </p>
                        </div>
                    </div>

                    <div class="hidden md:flex items-center">
                        <a href="{{ route('manual-punches.create') }}" class="inline-flex items-center px-6 py-3 bg-white/20 backdrop-blur-sm text-white font-semibold rounded-xl shadow-lg hover:bg-white/30 transition-all duration-300 border border-white/30">
                            <i class="fas fa-plus mr-2"></i> Add Manual Punch
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Success Message -->
        @if(session('success'))
            <div class="bg-green-50/80 backdrop-blur-sm border border-green-200/50 rounded-2xl p-6 shadow-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center">
                            <i class="fas fa-check-circle text-green-600"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-green-700">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Filters -->
        <div class="bg-white/80 backdrop-blur-sm shadow-xl rounded-2xl overflow-hidden border border-white/20">
            <div class="px-8 py-6 border-b border-gray-200/50 bg-gradient-to-r from-gray-50/50 to-blue-50/30">
                <h2 class="text-xl font-bold text-gray-900 flex items-center">
                    <i class="fas fa-filter mr-2 text-indigo-600"></i>
                    Filters
                </h2>
            </div>
            <form method="GET" action="{{ route('manual-punches.index') }}" class="p-8">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-user mr-1 text-indigo-500"></i> Employee
                        </label>
                        <select name="employee_payroll_id" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500">
                            <option value="">All Employees</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->payroll_id }}" {{ (request()->filled('employee_payroll_id') && request('employee_payroll_id') == $employee->payroll_id) ? 'selected' : '' }}>
                                    {{ $employee->name }} ({{ $employee->employee_id }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-calendar-alt mr-1 text-orange-500"></i> Start Date
                        </label>
                        <input type="date" name="start_date" value="{{ $startDate }}" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-calendar-check mr-1 text-purple-500"></i> End Date
                        </label>
                        <input type="date" name="end_date" value="{{ $endDate }}" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-info-circle mr-1 text-blue-500"></i> Status
                        </label>
                        <select name="status" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500">
                            <option value="">All Status</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                    </div>
                </div>

                <div class="flex items-center gap-4 mt-6">
                    <button type="submit" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold rounded-xl shadow-lg hover:from-indigo-700 hover:to-purple-700 transition-all duration-300">
                        <i class="fas fa-search mr-2"></i> Apply Filters
                    </button>
                    <a href="{{ route('manual-punches.index') }}" class="inline-flex items-center px-6 py-3 border-2 border-gray-300 rounded-xl text-gray-700 font-semibold hover:bg-gray-50 transition-all duration-300">
                        <i class="fas fa-redo mr-2"></i> Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Manual Punches Table -->
        <div class="bg-white/80 backdrop-blur-sm shadow-xl rounded-2xl overflow-hidden border border-white/20">
            <div class="px-8 py-6 border-b border-gray-200/50 bg-gradient-to-r from-gray-50/50 to-blue-50/30">
                <h2 class="text-xl font-bold text-gray-900 flex items-center">
                    <i class="fas fa-list mr-2 text-indigo-600"></i>
                    Manual Punch Records ({{ $manualPunches->total() }})
                </h2>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gradient-to-r from-gray-100 to-blue-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Employee</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Punch In</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Punch Out</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Shift</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Added By</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($manualPunches as $punch)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-lg flex items-center justify-center text-white font-bold mr-3">
                                            {{ substr($punch->employee->name ?? 'U', 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="text-sm font-semibold text-gray-900">{{ $punch->employee->name ?? 'Unknown' }}</div>
                                            <div class="text-xs text-gray-500">{{ $punch->employee_id }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm font-medium text-gray-900">{{ \Carbon\Carbon::parse($punch->date)->format('d M Y') }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($punch->punch_in_time)
                                        <span class="inline-flex items-center px-3 py-1 rounded-lg text-sm font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-sign-in-alt mr-1"></i> {{ $punch->punch_in_formatted }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($punch->punch_out_time)
                                        <span class="inline-flex items-center px-3 py-1 rounded-lg text-sm font-medium bg-red-100 text-red-800">
                                            <i class="fas fa-sign-out-alt mr-1"></i> {{ $punch->punch_out_formatted }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm text-gray-700">{{ $punch->shift->name ?? '-' }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm text-gray-700">{{ $punch->addedBy->name ?? 'System' }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($punch->status === 'approved')
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                            <i class="fas fa-check-circle mr-1"></i> Approved
                                        </span>
                                    @elseif($punch->status === 'pending')
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">
                                            <i class="fas fa-clock mr-1"></i> Pending
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800">
                                            <i class="fas fa-times-circle mr-1"></i> Rejected
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex items-center space-x-2">
                                        @if($punch->status === 'pending')
                                            <button type="button" 
                                                onclick="openReviewModal({
                                                    id: '{{ $punch->id }}',
                                                    name: '{{ $punch->employee->name ?? 'Unknown' }}',
                                                    code: '{{ $punch->employee_id }}',
                                                    date: '{{ \Carbon\Carbon::parse($punch->date)->format('d M Y') }}',
                                                    in: '{{ $punch->punch_in_formatted }}',
                                                    out: '{{ $punch->punch_out_formatted }}',
                                                    reason: '{{ addslashes($punch->reason) }}'
                                                })"
                                                class="bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg px-3 py-1.5 text-xs font-semibold shadow transition-colors flex items-center">
                                                <i class="fas fa-eye mr-1"></i> Review Request
                                            </button>
                                        @else
                                            <a href="{{ route('manual-punches.edit', $punch) }}" class="text-indigo-600 hover:text-indigo-900 transition-colors" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('manual-punches.destroy', $punch) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this manual punch?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900 transition-colors" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                            <i class="fas fa-inbox text-gray-400 text-2xl"></i>
                                        </div>
                                        <p class="text-gray-500 font-medium">No manual punches found</p>
                                        <a href="{{ route('manual-punches.create') }}" class="mt-4 inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                                            <i class="fas fa-plus mr-2"></i> Add First Manual Punch
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($manualPunches->hasPages())
                <div class="px-8 py-6 border-t border-gray-200/50">
                    {{ $manualPunches->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal: Admin Review Request -->
<div id="review-modal" class="fixed inset-0 z-50 overflow-y-auto hidden">
    <div class="flex items-center justify-center min-h-screen px-4 text-center">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-slate-900 bg-opacity-40 transition-opacity" onclick="closeReviewModal()"></div>
        
        <!-- Modal Content Box -->
        <div class="inline-block align-middle bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-4 text-white">
                <h3 class="text-lg font-bold flex items-center">
                    <i class="fas fa-user-clock mr-3"></i>Review Correction Request
                </h3>
            </div>
            
            <div class="p-6 space-y-4">
                <!-- Employee Info -->
                <div class="bg-slate-50 p-4 rounded-xl space-y-2 border border-slate-100">
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-500">Employee:</span>
                        <span class="font-bold text-slate-800" id="modal-employee-name">...</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-500">Employee Code:</span>
                        <span class="font-semibold text-slate-700" id="modal-employee-code">...</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-500">Requested Date:</span>
                        <span class="font-semibold text-slate-700" id="modal-request-date">...</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-500">Requested Punch In:</span>
                        <span class="font-mono text-emerald-600 font-semibold" id="modal-punch-in">...</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-500">Requested Punch Out:</span>
                        <span class="font-mono text-rose-600 font-semibold" id="modal-punch-out">...</span>
                    </div>
                </div>

                <!-- Employee Note -->
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Employee Reason / Note</label>
                    <div class="bg-indigo-50/50 border border-indigo-100 p-4 rounded-xl text-slate-700 text-sm italic" id="modal-employee-reason">
                        ...
                    </div>
                </div>

                <!-- Rejection Remarks input -->
                <div class="border-t border-slate-100 pt-4 space-y-2">
                    <label class="block text-sm font-semibold text-slate-700">Rejection Remarks (Required if rejecting)</label>
                    <textarea id="modal-rejection-remarks" rows="2" placeholder="Explain why the request is being rejected..." class="w-full border border-slate-200 rounded-xl p-3 text-sm focus:outline-none focus:ring-2 focus:ring-rose-500"></textarea>
                    <p id="rejection-warning" class="text-rose-500 text-xs hidden"><i class="fas fa-exclamation-circle mr-1"></i>Please enter rejection remarks to reject this request.</p>
                </div>
            </div>
            
            <div class="bg-slate-50 px-6 py-4 flex justify-end space-x-3 rounded-b-2xl">
                <!-- Cancel -->
                <button type="button" onclick="closeReviewModal()" class="bg-white hover:bg-slate-100 border border-slate-200 text-slate-700 rounded-lg px-4 py-2 text-sm font-medium transition-colors">
                    Close
                </button>
                
                <!-- Reject Action -->
                <form id="reject-form" method="POST" action="" class="inline">
                    @csrf
                    <input type="hidden" name="rejection_reason" id="reject-remarks-hidden">
                    <button type="button" onclick="submitRejection()" class="bg-rose-500 hover:bg-rose-600 text-white rounded-lg px-4 py-2 text-sm font-semibold shadow transition-colors">
                        <i class="fas fa-times mr-1"></i> Reject Request
                    </button>
                </form>

                <!-- Approve Action -->
                <form id="approve-form" method="POST" action="" class="inline">
                    @csrf
                    <button type="submit" class="bg-emerald-500 hover:bg-emerald-600 text-white rounded-lg px-4 py-2 text-sm font-semibold shadow transition-colors">
                        <i class="fas fa-check mr-1"></i> Approve Request
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    let activePunchId = null;

    function openReviewModal(data) {
        activePunchId = data.id;
        document.getElementById('modal-employee-name').innerText = data.name;
        document.getElementById('modal-employee-code').innerText = data.code;
        document.getElementById('modal-request-date').innerText = data.date;
        document.getElementById('modal-punch-in').innerText = data.in || '--:--';
        document.getElementById('modal-punch-out').innerText = data.out || '--:--';
        document.getElementById('modal-employee-reason').innerText = data.reason || 'No note provided.';
        
        // Clear textarea & warning
        document.getElementById('modal-rejection-remarks').value = '';
        document.getElementById('rejection-warning').classList.add('hidden');

        // Update form action URLs
        document.getElementById('approve-form').action = `/manual-punches/${data.id}/approve`;
        document.getElementById('reject-form').action = `/manual-punches/${data.id}/reject`;

        document.getElementById('review-modal').classList.remove('hidden');
    }

    function closeReviewModal() {
        document.getElementById('review-modal').classList.add('hidden');
    }

    function submitRejection() {
        const remarks = document.getElementById('modal-rejection-remarks').value.trim();
        const warning = document.getElementById('rejection-warning');
        
        if (!remarks) {
            warning.classList.remove('hidden');
            return;
        }

        warning.classList.add('hidden');
        document.getElementById('reject-remarks-hidden').value = remarks;
        document.getElementById('reject-form').submit();
    }
</script>
@endsection
