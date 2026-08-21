@extends('layouts.app')

@section('title', 'POSH Compliance Portal - HRMS')
@section('page-title', 'POSH Compliance')

@section('content')
@if(config('posh.legacy_enabled'))
<div class="mx-6 mt-6 p-4 rounded-lg bg-amber-50 border border-amber-200 text-amber-900 text-sm">
    <strong>Legacy POSH (deprecated)</strong> — Use <a href="{{ config('posh.workspace_url') }}{{ config('posh.coming_soon_path') }}" class="underline font-semibold" target="_blank" rel="noopener">{{ config('posh.product_name') }}</a> from the HRMS workspace when available.
</div>
@endif
<div class="p-6 space-y-6">
    <!-- Header Section -->
    <div class="rounded-2xl p-8 text-white relative overflow-hidden shadow-xl" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
        <div class="absolute inset-0 opacity-10">
            <div class="absolute -top-4 -right-4 w-32 h-32 bg-white rounded-full"></div>
            <div class="absolute top-10 -right-8 w-20 h-20 bg-white rounded-full"></div>
            <div class="absolute -bottom-6 -left-6 w-24 h-24 bg-white rounded-full"></div>
        </div>
        <div class="relative">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-4xl font-bold mb-3 flex items-center">
                        <i class="fas fa-users-shield mr-4"></i>
                        POSH Safety Portal
                    </h1>
                    <p class="text-blue-100 text-lg">Prevention of Sexual Harassment (POSH) compliance and confidential redressal portal.</p>
                </div>
                <div class="hidden lg:block">
                    <div class="w-36 h-36 bg-white bg-opacity-15 rounded-full flex items-center justify-center">
                        <i class="fas fa-balance-scale text-5xl text-white"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-xl shadow-sm" role="alert">
            <p class="font-bold">Success</p>
            <p>{{ session('success') }}</p>
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-xl shadow-sm" role="alert">
            <p class="font-bold">Error</p>
            <p>{{ session('error') }}</p>
        </div>
    @endif
    @if($errors->any())
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-xl shadow-sm" role="alert">
            <p class="font-bold">Validation Errors</p>
            <ul class="list-disc list-inside text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Main Content Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left Side: ICC Board Info -->
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-bold text-gray-900 flex items-center">
                        <i class="fas fa-users text-indigo-600 mr-3"></i>
                        ICC Committee Board
                    </h3>
                    <p class="text-gray-500 text-xs mt-1">Designated Internal Complaints Committee contacts</p>
                </div>
                <div class="p-6 space-y-4">
                    @forelse($iccMembers as $member)
                        <div class="p-4 border border-gray-100 rounded-xl bg-gray-50 space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="font-semibold text-gray-900 text-sm">{{ $member['name'] }}</span>
                                <span class="px-2 py-0.5 rounded text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-100">
                                    {{ $member['role'] }}
                                </span>
                            </div>
                            <div class="text-xs text-gray-600">{{ $member['designation'] }} ({{ $member['department'] }})</div>
                            <div class="text-xs text-gray-500 space-y-1 pt-1 border-t border-gray-100">
                                <div><i class="fas fa-envelope mr-1"></i> {{ $member['email'] }}</div>
                                <div><i class="fas fa-phone mr-1"></i> {{ $member['contact_number'] }}</div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 text-gray-500">
                            <i class="fas fa-users-slash text-3xl mb-2 text-gray-300"></i>
                            <div class="font-semibold">No Board Members Configured</div>
                            <div class="text-xs">Please contact your HR administrator.</div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Right Side: Grievance Redressal / Case Tracking -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 flex items-center">
                            <i class="fas fa-shield-alt text-indigo-600 mr-3"></i>
                            My Grievances
                        </h3>
                        <p class="text-gray-500 text-xs mt-1">Track and manage your submitted complaints</p>
                    </div>
                    <button onclick="openComplaintModal()" class="px-4 py-2 bg-gradient-to-r from-red-600 to-pink-600 text-white rounded-lg text-sm font-semibold hover:from-red-700 hover:to-pink-700 shadow-md">
                        <i class="fas fa-plus mr-2"></i> File Complaint
                    </button>
                </div>
                
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Case ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Respondent</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Filed Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($complaints as $c)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-indigo-600">{{ $c['complaint_number'] }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <div class="font-semibold">{{ $c['respondent_name'] }}</div>
                                            <div class="text-xs text-gray-500">{{ $c['respondent_department'] ?? 'N/A' }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ \Carbon\Carbon::parse($c['created_at'])->format('d M Y') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            @php
                                                $statusClass = 'bg-yellow-100 text-yellow-800 border-yellow-200';
                                                $statusLabel = 'Pending';
                                                if($c['status'] === 'under_investigation') {
                                                    $statusClass = 'bg-blue-100 text-blue-800 border-blue-200';
                                                    $statusLabel = 'Under Investigation';
                                                } elseif($c['status'] === 'resolved') {
                                                    $statusClass = 'bg-green-100 text-green-800 border-green-200';
                                                    $statusLabel = 'Resolved';
                                                } elseif($c['status'] === 'dismissed') {
                                                    $statusClass = 'bg-gray-100 text-gray-800 border-gray-200';
                                                    $statusLabel = 'Dismissed';
                                                }
                                            @endphp
                                            <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full border {{ $statusClass }}">
                                                {{ $statusLabel }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <button onclick="viewComplaintDetails({{ $c['id'] }})" class="text-indigo-600 hover:text-indigo-900 font-semibold">
                                                View Details
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                            <i class="fas fa-folder-open text-4xl text-gray-300 mb-2"></i>
                                            <div class="font-semibold">No complaints logged</div>
                                            <div class="text-xs">Your grievance record is clean. Click 'File Complaint' if you need support.</div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- File Complaint Modal -->
<div id="complaintModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Modal Backdrop -->
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeComplaintModal()"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <!-- Modal Wrapper -->
        <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-100">
            <div class="bg-gradient-to-r from-red-600 to-pink-600 px-6 py-4 text-white flex items-center justify-between">
                <h3 class="text-lg font-bold flex items-center">
                    <i class="fas fa-shield-alt mr-3"></i>
                    File Confidential Complaint
                </h3>
                <button onclick="closeComplaintModal()" class="text-white hover:text-gray-200">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form action="{{ route('compliance.posh.complaint.store') }}" method="POST" class="p-6 space-y-4">
                @csrf
                
                <div>
                    <label class="flex items-center space-x-3 bg-red-50 p-3 rounded-lg border border-red-100 cursor-pointer">
                        <input type="checkbox" name="is_anonymous" value="1" class="h-4 w-4 text-red-600 border-gray-300 rounded focus:ring-red-500">
                        <div>
                            <span class="text-sm font-semibold text-red-900 block">File Anonymously</span>
                            <span class="text-xs text-red-700">Identity details will be masked from ICC board case logs.</span>
                        </div>
                    </label>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="incident_date" class="block text-sm font-semibold text-gray-700 mb-1">Incident Date</label>
                        <input type="date" id="incident_date" name="incident_date" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label for="incident_location" class="block text-sm font-semibold text-gray-700 mb-1">Incident Location</label>
                        <input type="text" id="incident_location" name="incident_location" required placeholder="e.g. Office Cafeteria" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="respondent_name" class="block text-sm font-semibold text-gray-700 mb-1">Respondent Name</label>
                        <input type="text" id="respondent_name" name="respondent_name" required placeholder="Name of accused" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label for="respondent_department" class="block text-sm font-semibold text-gray-700 mb-1">Respondent Dept (Optional)</label>
                        <input type="text" id="respondent_department" name="respondent_department" placeholder="e.g. Sales" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                </div>

                <div>
                    <label for="description" class="block text-sm font-semibold text-gray-700 mb-1">Detailed Description</label>
                    <textarea id="description" name="description" rows="5" required placeholder="Describe the incident(s) in detail. Include times, specific actions, or witnesses if any." class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                </div>

                <div class="pt-4 border-t border-gray-100 flex justify-end space-x-3">
                    <button type="button" onclick="closeComplaintModal()" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 font-semibold text-sm">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2 bg-gradient-to-r from-red-600 to-pink-600 text-white rounded-lg hover:from-red-700 hover:to-pink-700 font-semibold text-sm shadow-md">
                        Submit Secure Complaint
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Details Modal -->
<div id="detailsModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeDetailsModal()"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <!-- Modal Content -->
        <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-gray-100">
            <div class="bg-indigo-600 px-6 py-4 text-white flex items-center justify-between">
                <h3 class="text-lg font-bold flex items-center" id="detailModalTitle">
                    <i class="fas fa-folder-open mr-3"></i>
                    Complaint File details
                </h3>
                <button onclick="closeDetailsModal()" class="text-white hover:text-gray-200">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="p-6 space-y-6 max-h-[80vh] overflow-y-auto">
                <div class="grid grid-cols-2 gap-4 bg-gray-50 p-4 rounded-xl border border-gray-100">
                    <div>
                        <div class="text-xs text-gray-500 font-bold uppercase">Incident Date</div>
                        <div class="text-sm font-semibold text-gray-900" id="detailIncidentDate">N/A</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 font-bold uppercase">Incident Location</div>
                        <div class="text-sm font-semibold text-gray-900" id="detailIncidentLocation">N/A</div>
                    </div>
                    <div class="mt-2">
                        <div class="text-xs text-gray-500 font-bold uppercase">Respondent (Accused)</div>
                        <div class="text-sm font-semibold text-gray-900" id="detailRespondentName">N/A</div>
                    </div>
                    <div class="mt-2">
                        <div class="text-xs text-gray-500 font-bold uppercase">Respondent Department</div>
                        <div class="text-sm font-semibold text-gray-900" id="detailRespondentDept">N/A</div>
                    </div>
                </div>

                <div>
                    <div class="text-xs text-gray-500 font-bold uppercase mb-1">Grievance Description</div>
                    <div class="bg-gray-50 p-4 rounded-xl border border-gray-100 text-sm text-gray-700 whitespace-pre-wrap leading-relaxed" id="detailDescription">N/A</div>
                </div>

                <div id="detailResolutionContainer" class="hidden">
                    <div class="text-xs text-green-700 font-bold uppercase mb-1">Final Resolution / Decision</div>
                    <div class="bg-green-50 p-4 rounded-xl border border-green-100 text-sm text-green-800 font-semibold" id="detailResolutionSummary">N/A</div>
                </div>

                <!-- Case Timeline Logs -->
                <div>
                    <h4 class="text-sm font-bold text-gray-900 mb-3 flex items-center">
                        <i class="fas fa-history mr-2 text-indigo-600"></i>
                        Case File Investigation Timeline
                    </h4>
                    
                    <div class="border-l-2 border-gray-200 pl-4 space-y-4 ml-2" id="detailLogsTimeline">
                        <!-- Loaded dynamically -->
                    </div>
                </div>
            </div>
            
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end">
                <button type="button" onclick="closeDetailsModal()" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-semibold text-sm shadow-md">
                    Close Details File
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function openComplaintModal() {
        document.getElementById('complaintModal').classList.remove('hidden');
    }
    
    function closeComplaintModal() {
        document.getElementById('complaintModal').classList.add('hidden');
    }

    function closeDetailsModal() {
        document.getElementById('detailsModal').classList.add('hidden');
    }

    function viewComplaintDetails(id) {
        // Show loading state or modal
        var modal = document.getElementById('detailsModal');
        
        fetch('/compliance/posh/complaint/' + id)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('detailModalTitle').innerHTML = '<i class="fas fa-folder-open mr-3"></i> Case File: ' + data.complaint.complaint_number;
                    document.getElementById('detailIncidentDate').innerText = formatDate(data.complaint.incident_date);
                    document.getElementById('detailIncidentLocation').innerText = data.complaint.incident_location;
                    document.getElementById('detailRespondentName').innerText = data.complaint.respondent_name;
                    document.getElementById('detailRespondentDept').innerText = data.complaint.respondent_department || 'N/A';
                    document.getElementById('detailDescription').innerText = data.complaint.description;

                    // Resolution details
                    var resolutionContainer = document.getElementById('detailResolutionContainer');
                    if (data.complaint.resolution_summary) {
                        resolutionContainer.classList.remove('hidden');
                        document.getElementById('detailResolutionSummary').innerText = data.complaint.resolution_summary;
                    } else {
                        resolutionContainer.classList.add('hidden');
                    }

                    // Logs Timeline
                    var timeline = document.getElementById('detailLogsTimeline');
                    timeline.innerHTML = '';

                    // Add initial filing log
                    var initialLog = document.createElement('div');
                    initialLog.className = 'relative pl-2';
                    initialLog.innerHTML = `
                        <div class="absolute -left-[21px] top-1.5 w-2.5 h-2.5 rounded-full bg-indigo-600 border border-white"></div>
                        <div class="text-xs font-bold text-gray-800">Complaint Logged</div>
                        <div class="text-[10px] text-gray-400">${formatDateTime(data.complaint.created_at)}</div>
                        <p class="text-xs text-gray-600 mt-1">Complaint registered securely inside the portal.</p>
                    `;
                    timeline.appendChild(initialLog);

                    // Add subsequent logs
                    data.logs.forEach(log => {
                        var logItem = document.createElement('div');
                        logItem.className = 'relative pl-2 pt-2';
                        
                        var typeLabel = 'Investigation Step';
                        var typeColor = 'bg-indigo-600';
                        if (log.action_type === 'status_change') {
                            typeLabel = 'Status Updated';
                            typeColor = 'bg-blue-600';
                        } else if (log.action_type === 'meeting_minutes') {
                            typeLabel = 'Committee Meeting Held';
                            typeColor = 'bg-green-600';
                        }

                        var attachmentHtml = '';
                        if (log.attachment_path) {
                            var assetUrl = '/storage/' + log.attachment_path;
                            attachmentHtml = `
                                <div class="mt-1">
                                    <a href="${assetUrl}" target="_blank" class="inline-flex items-center text-[11px] text-indigo-600 font-bold hover:underline">
                                        <i class="fas fa-paperclip mr-1"></i> ${log.original_filename || 'Attachment'}
                                    </a>
                                </div>
                            `;
                        }

                        logItem.innerHTML = `
                            <div class="absolute -left-[21px] top-3 w-2.5 h-2.5 rounded-full ${typeColor} border border-white"></div>
                            <div class="text-xs font-bold text-gray-800">${typeLabel}</div>
                            <div class="text-[10px] text-gray-400">${formatDateTime(log.created_at)}</div>
                            <p class="text-xs text-gray-600 mt-1">${log.notes || ''}</p>
                            ${attachmentHtml}
                        `;
                        timeline.appendChild(logItem);
                    });

                    modal.classList.remove('hidden');
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(err => {
                console.error(err);
                alert('Communication error with the server.');
            });
    }

    function formatDate(dateStr) {
        if (!dateStr) return 'N/A';
        var d = new Date(dateStr);
        return d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
    }

    function formatDateTime(dateStr) {
        if (!dateStr) return 'N/A';
        var d = new Date(dateStr);
        return d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) + ', ' + 
               d.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true });
    }
</script>
@endsection
