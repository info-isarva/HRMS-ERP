@extends('layouts.master')

@section('title', 'Notification Management')

@section('style')
<style>
    /* Page Header Card */
    .page-header-card {
        background: white;
        border-radius: 1rem;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
        overflow: hidden;
        margin-bottom: 2rem;
    }

    .page-header-gradient {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 2rem 1.5rem;
        position: relative;
        color: white;
    }

    .page-header-pattern {
        position: absolute;
        inset: 0;
        background: rgba(0, 0, 0, 0.04);
    }

    .page-header-circle-1,
    .page-header-circle-2 {
        position: absolute;
        background: rgba(255, 255, 255, 0.08);
        border-radius: 50%;
    }
    .page-header-circle-1 { top: -1rem; right: -1rem; width: 6rem; height: 6rem; }
    .page-header-circle-2 { bottom: -1rem; left: -1rem; width: 8rem; height: 8rem; }

    .page-header-icon-box {
        width: 4rem;
        height: 4rem;
        background: rgba(255, 255, 255, 0.16);
        border: 1px solid rgba(255, 255, 255, 0.22);
        border-radius: 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .page-header-title {
        font-size: 1.75rem;
        font-weight: 700;
        color: white;
        margin-bottom: 0.25rem;
    }
    .page-header-subtitle { color: rgba(255,255,255,0.9); margin: 0; }

    /* Modern Settings Card */
    .settings-card {
        background: white;
        border-radius: 1rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
        overflow: visible;
        border: 1px solid #e5e7eb;
        margin-bottom: 1.5rem;
    }

    .settings-card .card-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        border-radius: 1rem 1rem 0 0 !important;
        padding: 1.5rem;
    }

    .settings-card .card-header h5 {
        font-weight: 600;
        margin: 0;
        display: flex;
        align-items: center;
        font-size: 1.1rem;
    }

    .settings-card .card-header i {
        margin-right: 0.5rem;
        opacity: 0.9;
    }

    .settings-card .card-body {
        padding: 2rem;
    }

    /* Modern Table Card */
    .table-card {
        background: white;
        border-radius: 1rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
        overflow: visible;
        border: 1px solid #e5e7eb;
        padding: 1rem;
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
    
    .dataTables_wrapper .dataTables_paginate .paginate_button.disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
    
    /* Action Buttons */
    .btn-action {
        width: 32px;
        height: 32px;
        border-radius: 0.375rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: none;
        transition: all 0.2s ease;
        font-size: 0.875rem;
        margin: 0 2px;
    }
    
    .btn-action:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    }
    
    .btn-action-view {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
    }
    
    .btn-action-edit {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        color: white;
    }
    
    .btn-action-activate {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
    }
    
    .btn-action-deactivate {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: white;
    }
    
    .btn-action-analytics {
        background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
        color: white;
    }
    
    .btn-action-delete {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
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
    
    .bg-success {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
    }
    
    .bg-danger {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
    }

    .bg-warning,
    .bg-warning.text-dark {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%) !important;
        color: white !important;
    }

    .bg-info,
    .bg-info.text-dark {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%) !important;
        color: white !important;
    }

    .bg-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
        color: white !important;
    }

    .bg-secondary {
        background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%) !important;
        color: white !important;
    }

    .badge-secondary {
        background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%) !important;
        color: white !important;
    }

    /* Add responsive styles for mobile */
    @media (max-width: 768px) {
        .table-card {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .table {
            min-width: 600px;
        }

        .btn {
            font-size: 0.875rem;
            padding: 0.5rem 1rem;
        }
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
    }

    .btn-outline-secondary {
        border: 1px solid #d1d5db;
        color: #6b7280;
    }

    .btn-outline-secondary:hover {
        background: #f9fafb;
        color: #374151;
        border-color: #9ca3af;
    }

    .btn-outline-primary {
        border: 1px solid #667eea;
        color: #667eea;
    }

    .btn-outline-primary:hover {
        background: #667eea;
        color: white;
    }

    /* Form Styling */
    .form-control {
        border-radius: 0.5rem;
        border: 1px solid #d1d5db;
        padding: 0.75rem 1rem;
        transition: all 0.2s ease;
        font-size: 0.875rem;
    }

    .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .page-header-gradient {
            padding: 1.5rem 1rem;
        }

        .settings-card .card-body {
            padding: 1.5rem;
        }

        .btn {
            width: 100%;
            margin-bottom: 0.5rem;
        }
    }
</style>
@endsection

@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <!-- Modern Page Header -->
                <div class="page-header-card">
                    <div class="page-header-gradient">
                        <div class="page-header-pattern"></div>
                        <div class="page-header-circle-1"></div>
                        <div class="page-header-circle-2"></div>
                        <div class="d-flex align-items-center">
                            <div class="page-header-icon-box">
                                <i class="fas fa-cog fa-lg"></i>
                            </div>
                            <div class="ms-3">
                                <h1 class="page-header-title">Notification Management</h1>
                                <p class="page-header-subtitle">Create and manage manual notifications for your employees</p>
                            </div>
                        </div>
                    </div>
                    <div class="p-3 d-flex justify-content-between align-items-center">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                                <li class="breadcrumb-item active">Notifications</li>
                            </ol>
                        </nav>
                        <div>
                            <a href="{{ route('manual-notifications.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Create Notification
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Filter Section -->
                <div class="settings-card">
                    <div class="card-header">
                        <h5><i class="fas fa-filter me-2"></i>Filters & Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="row align-items-end">
                            <div class="col-md-3 mb-3 mb-md-0">
                                <label class="font-weight-600 text-muted mb-2" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">
                                    <i class="fas fa-search me-1"></i> Search
                                </label>
                                <input type="text" class="form-control" id="search_filter" name="search_filter" placeholder="Search title or message">
                            </div>
                            <div class="col-md-2 mb-3 mb-md-0">
                                <label class="font-weight-600 text-muted mb-2" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">
                                    <i class="fas fa-toggle-on me-1"></i> Status
                                </label>
                                <select class="form-control form-select" id="status_filter">
                                    <option value="">All Status</option>
                                    <option value="draft">Draft</option>
                                    <option value="scheduled">Scheduled</option>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>
                            <div class="col-md-2 mb-3 mb-md-0">
                                <label class="font-weight-600 text-muted mb-2" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">
                                    <i class="fas fa-exclamation-triangle me-1"></i> Priority
                                </label>
                                <select class="form-control form-select" id="priority_filter">
                                    <option value="">All Priorities</option>
                                    <option value="low">Low</option>
                                    <option value="medium">Medium</option>
                                    <option value="high">High</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3 mb-md-0">
                                <label class="font-weight-600 text-muted mb-2" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">
                                    <i class="fas fa-plus me-1"></i> Actions
                                </label>
                                <div class="d-flex">
                                    <!-- <a href="{{ route('manual-notifications.create') }}" class="btn btn-primary me-2">
                                        <i class="fas fa-plus"></i> Create Notification
                                    </a> -->
                                    <button type="button" class="btn btn-outline-secondary" onclick="location.reload()">
                                        <i class="fas fa-sync"></i> Refresh
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Modern Table Card -->
        <div class="table-card">
            <table class="table table-hover" id="notificationDataList" style="width: 100%">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Title</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Target</th>
                        <th>Schedule</th>
                        <th>Created By</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<!-- Analytics Modal -->
<div class="modal fade" id="analyticsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="border-radius: 1rem; border: none;">
            <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 1rem 1rem 0 0; border: none;">
                <h5 class="modal-title" style="font-weight: 600;"><i class="fas fa-chart-bar me-2"></i>Notification Analytics</h5>
                <button type="button" class="btn-close btn-close-white" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="analyticsContent">
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.notification-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1rem;
}

.notification-icon.bg-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.notification-icon.bg-success {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
}

.notification-icon.bg-warning {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
}

.notification-icon.bg-info {
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
}

.notification-icon.bg-danger {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
}

.badge {
    font-size: 0.75rem;
}

.table th {
    border-top: none;
    font-weight: 600;
    color: #1f2937;
}
</style>
@endsection
@section('script')

    <script type="text/javascript">
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            
            const table = $('#notificationDataList').DataTable({
                lengthMenu: [
                    [10, 25, 50, 100, 150],
                    [10, 25, 50, 100, 150]
                ],
                buttons: ['pageLength'],
                pageLength: 10,
                order: [[6, 'desc']],
                processing: true,
                serverSide: true,
                ordering: true,
                searching: true,
                ajax: {
                    url: "/get-manual-notifications-data",
                    type: "POST",
                    dataType: 'json',
                    data: function(d) {
                        d.status = $('#status_filter').val();
                        d.priority = $('#priority_filter').val();
                        d.search = { value: $('#search_filter').val() };
                        d._token = $('meta[name="csrf-token"]').attr('content');
                        return d;
                    },
                    error: function(xhr, error, thrown) {
                        console.log('DataTables error:', error);
                        console.log('Server response:', xhr.responseText);
                        
                        // Show a user-friendly error message
                        $('#notificationDataList_processing').hide();
                        $('#notificationDataList tbody').html('<tr><td colspan="8" class="text-center">Error loading data. Please try refreshing the page.</td></tr>');
                    }
                },
                columns: [
                    { data: 'no', orderable: false },
                    { data: 'title', orderable: false },
                    { data: 'priority', orderable: true },
                    { data: 'status', orderable: true },
                    { data: 'target', orderable: false },
                    { data: 'schedule', orderable: false },
                    { data: 'creator', orderable: true },
                    { data: 'action', orderable: false }
                ],
                // Add error handling and styling
                "drawCallback": function(settings) {
                    console.log("DataTable draw complete");
                    
                    // Reinitialize tooltips after table redraw
                    $('[data-toggle="tooltip"]').tooltip();
                },
                "initComplete": function(settings, json) {
                    console.log("DataTable initialization complete");
                }
            });
    
            // Add search functionality
            $('#search_filter').on('keyup', function() {
                table.draw();
            });
            
            $('#status_filter, #priority_filter').on('change', function() {
                table.draw();
            });
        });
        
        // Show analytics modal
        function showAnalytics(notificationId) {
            $('#analyticsModal').modal('show');
            $('#analyticsContent').html('<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div></div>');
            
            $.ajax({
                url: '/manual-notifications/' + notificationId + '/analytics',
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        let analytics = response.analytics;
                        let html = `
                            <div class="row g-3 mb-4">
                                <div class="col-md-3 col-6">
                                    <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                        <div class="card-body text-center text-white p-4">
                                            <i class="fas fa-users fa-2x mb-3" style="opacity: 0.8;"></i>
                                            <h2 class="mb-2 fw-bold">${analytics.total_targeted}</h2>
                                            <p class="mb-0 small" style="opacity: 0.9;">Total Targeted</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 col-6">
                                    <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                                        <div class="card-body text-center text-white p-4">
                                            <i class="fas fa-check-circle fa-2x mb-3" style="opacity: 0.8;"></i>
                                            <h2 class="mb-2 fw-bold">${analytics.total_reads}</h2>
                                            <p class="mb-0 small" style="opacity: 0.9;">Total Reads</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 col-6">
                                    <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                                        <div class="card-body text-center text-white p-4">
                                            <i class="fas fa-clock fa-2x mb-3" style="opacity: 0.8;"></i>
                                            <h2 class="mb-2 fw-bold">${analytics.unread_count}</h2>
                                            <p class="mb-0 small" style="opacity: 0.9;">Unread</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 col-6">
                                    <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);">
                                        <div class="card-body text-center text-white p-4">
                                            <i class="fas fa-chart-line fa-2x mb-3" style="opacity: 0.8;"></i>
                                            <h2 class="mb-2 fw-bold">${analytics.read_percentage}%</h2>
                                            <p class="mb-0 small" style="opacity: 0.9;">Read Rate</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card border-0 shadow-sm">
                                <div class="card-body p-4">
                                    <h6 class="mb-3 text-muted fw-semibold">
                                        <i class="fas fa-chart-bar me-2"></i>Read Progress
                                    </h6>
                                    <div class="progress" style="height: 30px; border-radius: 1rem;">
                                        <div class="progress-bar" role="progressbar" 
                                             style="width: ${analytics.read_percentage}%; background: linear-gradient(90deg, #10b981 0%, #059669 100%); font-weight: 600; font-size: 14px;" 
                                             aria-valuenow="${analytics.read_percentage}" 
                                             aria-valuemin="0" 
                                             aria-valuemax="100">
                                            ${analytics.read_percentage}% Read
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between mt-2">
                                        <small class="text-muted"><i class="fas fa-check-circle text-success me-1"></i>${analytics.total_reads} Read</small>
                                        <small class="text-muted"><i class="fas fa-clock text-warning me-1"></i>${analytics.unread_count} Pending</small>
                                    </div>
                                </div>
                            </div>
                        `;
                        $('#analyticsContent').html(html);
                    } else {
                        $('#analyticsContent').html('<div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i>Failed to load analytics</div>');
                    }
                },
                error: function() {
                    $('#analyticsContent').html('<div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i>Error loading analytics data</div>');
                }
            });
        }
    </script>
@endsection