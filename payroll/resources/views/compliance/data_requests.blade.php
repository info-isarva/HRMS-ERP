@extends('layouts.master')
@section('title', 'Data Privacy Requests')

@section('content')
<style>
    /* Page Header Card */
    .page-header-card {
        background: white;
        border-radius: 1rem;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        margin-bottom: 2rem;
    }
    
    .page-header-gradient {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 2.5rem 2rem;
        position: relative;
    }
    
    .page-header-pattern {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.05);
    }
    
    .page-header-circle-1 {
        position: absolute;
        top: -1rem;
        right: -1rem;
        width: 6rem;
        height: 6rem;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
    }
    
    .page-header-circle-2 {
        position: absolute;
        bottom: -1rem;
        left: -1rem;
        width: 8rem;
        height: 8rem;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
    }
    
    .page-header-icon-box {
        width: 4rem;
        height: 4rem;
        background: rgba(255, 255, 255, 0.2);
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .page-header-title {
        font-size: 1.875rem;
        font-weight: 700;
        color: white;
        margin-bottom: 0.5rem;
    }
    
    .page-header-subtitle {
        font-size: 1rem;
        color: rgba(255, 255, 255, 0.9);
        margin: 0;
    }
    
    .page-header-stats {
        text-align: right;
    }
    
    .page-header-stats-label {
        font-size: 0.875rem;
        color: rgba(255, 255, 255, 0.9);
        margin-bottom: 0.25rem;
    }
    
    .page-header-stats-value {
        font-size: 1.875rem;
        font-weight: 700;
        color: white;
    }
    
    .page-header-stats-icon {
        width: 5rem;
        height: 5rem;
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    /* Modern Filter Card */
    .filter-card {
        background: white;
        border-radius: 1rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
        padding: 1.5rem;
        margin-bottom: 2rem;
        border: 1px solid #e5e7eb;
    }
    
    .filter-card .form-control,
    .filter-card .form-control:focus {
        border: 2px solid #e5e7eb;
        border-radius: 0.5rem;
        padding: 0.625rem 1rem;
        font-size: 0.875rem;
        transition: all 0.2s ease;
    }
    
    .filter-card .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }
    
    /* Button Styling */
    .btn {
        border-radius: 0.5rem;
        padding: 0.75rem 2rem;
        font-weight: 500;
        transition: all 0.2s ease;
        border: none;
        font-size: 0.875rem;
    }

    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        background: linear-gradient(135deg, #5a67d8 0%, #6b46c1 100%);
        color: white;
    }

    .btn-secondary {
        background: #6c757d;
        border: none;
        color: white;
    }

    .btn-secondary:hover {
        background: #5a6268;
        transform: translateY(-2px);
        color: white;
    }
    
    /* Modern Table Card */
    .table-card {
        background: white;
        border-radius: 1rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
        overflow: hidden;
        border: 1px solid #e5e7eb;
    }
    
    .table-card .table {
        margin-bottom: 0;
        width: 100% !important;
    }
    
    .table thead th {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white !important;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
        border: none;
        padding: 1rem 0.75rem;
        white-space: nowrap;
    }
    
    .table tbody tr {
        transition: background-color 0.2s ease;
        border-bottom: 1px solid #f3f4f6;
    }
    
    .table tbody tr:hover {
        background: #f9fafb !important;
    }
    
    .table tbody td {
        padding: 1rem 0.75rem;
        vertical-align: middle;
        font-size: 0.875rem;
        color: #374151;
    }
    
    /* DataTables Custom Styling */
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter,
    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_paginate {
        padding: 1rem;
    }
    
    .dataTables_wrapper .dataTables_filter input {
        border: 2px solid #e5e7eb;
        border-radius: 0.5rem;
        padding: 0.5rem 1rem;
        margin-left: 0.5rem;
    }
    
    .dataTables_wrapper .dataTables_filter input:focus {
        border-color: #667eea;
        outline: none;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }
    
    .dataTables_wrapper .dataTables_length select {
        border: 2px solid #e5e7eb;
        border-radius: 0.5rem;
        padding: 0.375rem 2rem 0.375rem 0.75rem;
        margin: 0 0.5rem;
    }
    
    .dataTables_wrapper .dataTables_paginate .paginate_button {
        padding: 0.375rem 0.75rem;
        margin: 0 2px;
        border-radius: 0.375rem;
        border: 1px solid #e5e7eb;
        background: white;
    }
    
    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white !important;
        border-color: #667eea;
    }
    
    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white !important;
        border-color: #667eea;
    }
    
    /* Employee Avatar */
    .employee-info {
        display: flex;
        align-items: center;
    }
    
    .employee-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 600;
        margin-right: 0.75rem;
        font-size: 0.875rem;
    }
    
    .employee-details .employee-name {
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 2px;
    }
    
    .employee-details .employee-id {
        font-size: 0.75rem;
        color: #6b7280;
    }

    /* Search Input with Icon */
    .search-wrapper {
        position: relative;
    }
    
    .search-wrapper i {
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: #9ca3af;
        z-index: 10;
        pointer-events: none;
    }
    
    .search-wrapper .form-control {
        padding-left: 2.75rem !important;
    }
    
    /* Modern Badges */
    .badge {
        padding: 0.375rem 0.75rem;
        font-weight: 500;
        border-radius: 0.375rem;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .badge-payroll {
        background: #e0f2fe;
        color: #0369a1;
    }
    
    .badge-attendance {
        background: #fef3c7;
        color: #b45309;
    }
    
    .badge-pending {
        background: #fee2e2;
        color: #991b1b;
    }
    
    .badge-resolved {
        background: #d1fae5;
        color: #065f46;
    }
</style>

<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header Card -->
        <div class="page-header-card">
            <div class="page-header-gradient">
                <div class="page-header-pattern"></div>
                <div class="page-header-circle-1"></div>
                <div class="page-header-circle-2"></div>
                <div class="row align-items-center">
                    <div class="col-12 col-md-6 d-flex align-items-center">
                        <div class="page-header-icon-box me-3">
                            <i class="fas fa-shield-alt text-white fa-2x"></i>
                        </div>
                        <div>
                            <h1 class="page-header-title">Data Privacy Requests</h1>
                            <p class="page-header-subtitle">Manage DPDP Act compliance, correction, and deletion requests</p>
                        </div>
                    </div>
                    <div class="col-12 col-md-6 mt-3 mt-md-0">
                        <div class="row no-gutters justify-content-md-end text-white">
                            <div class="col-4 text-center px-2">
                                <div class="page-header-stats-label">Total</div>
                                <div class="page-header-stats-value">{{ $requests->count() }}</div>
                            </div>
                            <div class="col-4 text-center px-2 border-left border-right border-white-50">
                                <div class="page-header-stats-label">Pending</div>
                                <div class="page-header-stats-value">{{ $requests->where('status', 'pending')->count() }}</div>
                            </div>
                            <div class="col-4 text-center px-2">
                                <div class="page-header-stats-label">Resolved</div>
                                <div class="page-header-stats-value">{{ $requests->where('status', 'resolved')->count() }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /Page Header Card -->

        <!-- Modern Filter Card -->
        <div class="filter-card">
            <form method="GET" action="{{ route('compliance.data-requests') }}">
                <div class="row align-items-end">
                    <div class="col-xl-4 col-lg-4 col-md-6 col-12 mb-3">
                        <label class="font-weight-600 text-muted mb-2" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">
                            <i class="fas fa-search me-1"></i> Search
                        </label>
                        <div class="search-wrapper">
                            <i class="fas fa-search"></i>
                            <input type="text" id="searchInput" name="search" class="form-control" 
                                   placeholder="Search by Email, Name, ID, or Details"
                                   value="{{ $filters['search'] ?? '' }}">
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-4 col-md-6 col-12 mb-3">
                        <label class="font-weight-600 text-muted mb-2" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">
                            <i class="fas fa-filter me-1"></i> Status
                        </label>
                        <select name="status" class="form-control form-select">
                            <option value="">All Statuses</option>
                            <option value="pending" {{ ($filters['status'] ?? '') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="resolved" {{ ($filters['status'] ?? '') == 'resolved' ? 'selected' : '' }}>Resolved</option>
                        </select>
                    </div>
                    <div class="col-xl-3 col-lg-4 col-md-6 col-12 mb-3">
                        <label class="font-weight-600 text-muted mb-2" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">
                            <i class="fas fa-laptop me-1"></i> Source System
                        </label>
                        <select name="source_system" class="form-control form-select">
                            <option value="">All Systems</option>
                            <option value="payroll" {{ ($filters['source_system'] ?? '') == 'payroll' ? 'selected' : '' }}>Payroll</option>
                            <option value="attendance" {{ ($filters['source_system'] ?? '') == 'attendance' ? 'selected' : '' }}>Attendance</option>
                        </select>
                    </div>
                    <div class="col-xl-2 col-lg-12 col-md-12 col-12 mb-3">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary w-50 p-2">Search</button>
                            <a href="{{ route('compliance.data-requests') }}" class="btn btn-secondary w-50 p-2 text-center d-flex align-items-center justify-content-center">Reset</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="table-card">
                    <div class="table-responsive">
                        <table class="table table-striped custom-table mb-0 datatable">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Employee Details</th>
                                    <th>Source System</th>
                                    <th>Request Type</th>
                                    <th>Details</th>
                                    <th>Status</th>
                                    <th class="text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($requests as $req)
                                <tr>
                                    <td>{{ $req->created_at->format('d M Y') }}</td>
                                    <td>
                                        @if($req->employee)
                                            <div class="employee-info">
                                                <div class="employee-avatar">
                                                    {{ strtoupper(substr($req->employee->name, 0, 2)) }}
                                                </div>
                                                <div class="employee-details">
                                                    <div class="employee-name">{{ $req->employee->name }}</div>
                                                    <div class="employee-id">ID: {{ $req->employee->employee_id }}</div>
                                                    <div class="text-muted small">{{ $req->user_email }}</div>
                                                </div>
                                            </div>
                                        @else
                                            <div class="employee-info">
                                                <div class="employee-avatar bg-secondary">
                                                    ?
                                                </div>
                                                <div class="employee-details">
                                                    <div class="employee-name">Unknown User</div>
                                                    <div class="text-muted small">{{ $req->user_email }}</div>
                                                </div>
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge {{ $req->source_system == 'payroll' ? 'badge-payroll' : 'badge-attendance' }}">
                                            {{ ucfirst($req->source_system) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="font-weight-500">{{ $req->request_type }}</span>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-outline-secondary view-request-btn"
                                                data-employee="{{ $req->employee ? $req->employee->name : 'Unknown User' }}"
                                                data-id="{{ $req->employee ? $req->employee->employee_id : 'N/A' }}"
                                                data-email="{{ $req->user_email }}"
                                                data-source="{{ ucfirst($req->source_system) }}"
                                                data-type="{{ $req->request_type }}"
                                                data-date="{{ $req->created_at->format('d M Y, h:i A') }}"
                                                data-status="{{ ucfirst($req->status) }}"
                                                data-details="{{ $req->details }}">
                                            <i class="fa fa-eye"></i> View Details
                                        </button>
                                    </td>
                                    <td>
                                        @if($req->status == 'pending')
                                            <span class="badge badge-pending"><i class="fa fa-hand-paper-o"></i> Pending</span>
                                        @elseif($req->status == 'resolved')
                                            <span class="badge badge-resolved"><i class="fa fa-check"></i> Resolved</span>
                                        @else
                                            <span class="badge badge-danger"><i class="fa fa-times"></i> {{ ucfirst($req->status) }}</span>
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        @if($req->status == 'pending')
                                        <form action="{{ route('compliance.data-requests.resolve', $req->id) }}" method="POST" style="display:inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-primary" onclick="return confirm('Are you sure you want to mark this request as resolved? Make sure you have processed the data change.')">
                                                <i class="fa fa-check"></i> Mark Resolved
                                            </button>
                                        </form>
                                        @else
                                        <span class="text-muted small">No action needed</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- View Request Details Modal -->
<div class="modal custom-modal fade" id="view_details_modal" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Privacy Request Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body pb-0">
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label class="text-muted small font-weight-bold">Employee Name</label>
                        <input type="text" id="modal_employee" class="form-control bg-light" readonly>
                    </div>
                    <div class="col-md-6 form-group">
                        <label class="text-muted small font-weight-bold">Employee ID</label>
                        <input type="text" id="modal_id" class="form-control bg-light" readonly>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-12 form-group">
                        <label class="text-muted small font-weight-bold">Email Address</label>
                        <input type="text" id="modal_email" class="form-control bg-light" readonly>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-6 form-group">
                        <label class="text-muted small font-weight-bold">System Source</label>
                        <input type="text" id="modal_source" class="form-control bg-light" readonly>
                    </div>
                    <div class="col-md-6 form-group">
                        <label class="text-muted small font-weight-bold">Request Type</label>
                        <input type="text" id="modal_type" class="form-control bg-light" readonly>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-6 form-group">
                        <label class="text-muted small font-weight-bold">Requested On</label>
                        <input type="text" id="modal_date" class="form-control bg-light" readonly>
                    </div>
                    <div class="col-md-6 form-group">
                        <label class="text-muted small font-weight-bold">Status</label>
                        <input type="text" id="modal_status" class="form-control bg-light" readonly>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-12 form-group">
                        <label class="text-muted small font-weight-bold">Details of Request</label>
                        <textarea id="modal_details" class="form-control bg-light" rows="5" readonly></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    $(document).ready(function(){
        $('.view-request-btn').on('click', function() {
            var employee = $(this).data('employee');
            var id = $(this).data('id');
            var email = $(this).data('email');
            var source = $(this).data('source');
            var type = $(this).data('type');
            var date = $(this).data('date');
            var status = $(this).data('status');
            var details = $(this).data('details');

            $('#modal_employee').val(employee);
            $('#modal_id').val(id);
            $('#modal_email').val(email);
            $('#modal_source').val(source);
            $('#modal_type').val(type);
            $('#modal_date').val(date);
            $('#modal_status').val(status);
            $('#modal_details').val(details);

            $('#view_details_modal').modal('show');
        });
    });
</script>
@endsection
