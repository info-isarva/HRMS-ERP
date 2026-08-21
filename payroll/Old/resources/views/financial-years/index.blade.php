@extends('layouts.master')

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
        inset: 0;
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
    
    .filter-card .btn {
        border-radius: 0.5rem;
        padding: 0.625rem 1.5rem;
        font-weight: 500;
        transition: all 0.2s ease;
    }
    
    .filter-card .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
    }
    
    .filter-card .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
    }
    
    .filter-card .btn-secondary {
        background: #6c757d;
        border: none;
    }
    
    .filter-card .btn-secondary:hover {
        background: #5a6268;
        transform: translateY(-2px);
    }
    
    /* Modern Table Card */
    .table-card {
        background: white;
        border-radius: 1rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
        overflow: visible;
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
    
    .dataTables_wrapper .dataTables_paginate .paginate_button.disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
    
    /* Financial Year Info */
    .fy-info {
        display: flex;
        align-items: center;
    }
    
    .fy-avatar {
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
    
    .fy-details .fy-name {
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 2px;
    }
    
    .fy-details .fy-duration {
        font-size: 0.75rem;
        color: #6b7280;
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
    
    .bg-warning text-dark {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: white;
    }
    
    .bg-info text-dark {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        color: white;
    }
    
    /* Action Buttons */
    .action-buttons {
        display: flex;
        gap: 0.5rem;
    }
    
    .btn-action {
        width: 32px;
        height: 32px;
        border-radius: 0.375rem;
        display: flex;
        align-items: center;
        justify-content: center;
        border: none;
        transition: all 0.2s ease;
        font-size: 0.875rem;
    }
    
    .btn-action:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    }
    
    .btn-action-edit {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        color: white;
    }
    
    .btn-action-delete {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
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
    
    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
    }
    
    .empty-state-icon {
        font-size: 4rem;
        color: #d1d5db;
        margin-bottom: 1rem;
    }
    
    .empty-state-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #4b5563;
        margin-bottom: 0.5rem;
    }
    
    .empty-state-text {
        color: #6b7280;
    }
</style>

<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Modern Page Header -->
        <div class="page-header-card">
            <div class="page-header-gradient">
                <!-- Background Patterns -->
                <div class="page-header-pattern"></div>
                <div class="page-header-circle-1"></div>
                <div class="page-header-circle-2"></div>
                
                <div class="position-relative">
                    <div class="row align-items-center">
                        <div class="col-lg-8">
                            <div class="d-flex align-items-center">
                                <div class="page-header-icon-box me-4">
                                    <i class="fas fa-calendar-alt text-white" style="font-size: 1.5rem;"></i>
                                </div>
                                <div>
                                    <h1 class="page-header-title">Financial Year Management</h1>
                                    <p class="page-header-subtitle">
                                        Manage and track all financial year information
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 d-none d-lg-flex align-items-center justify-content-end">
                            <div class="page-header-stats me-4">
                                <p class="page-header-stats-label mb-1">Total FYs</p>
                                <p class="page-header-stats-value mb-0" id="fy-count">{{ $financialYears->total() }}</p>
                            </div>
                            <div class="page-header-stats-icon">
                                <i class="fas fa-calendar-check text-white" style="font-size: 2rem;"></i>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="row mt-4">
                        <div class="col">
                            <a href="{{ route('financial-years.create') }}" class="btn btn-light btn-lg">
                                <i class="fa fa-plus me-2"></i> Create Financial Year
                            </a>
                            <a href="{{ route('financial-years.settings') }}" class="btn btn-outline-light btn-lg ms-2">
                                <i class="fas fa-cog me-2"></i> Settings
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modern Filter Card -->
        <div class="filter-card">
            <form method="GET" action="{{ route('financial-years.index') }}">
                <div class="row align-items-end">
                    <div class="col-md-3 mb-3 mb-md-0">
                        <label class="font-weight-600 text-muted mb-2" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">
                            <i class="fas fa-search me-1"></i> Search
                        </label>
                        <div class="search-wrapper">
                            <i class="fas fa-search"></i>
                            <input type="text" id="searchInput" name="search" class="form-control" 
                                   placeholder="Search by name"
                                   value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-3 mb-3 mb-md-0">
                        <label class="font-weight-600 text-muted mb-2" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">
                            <i class="fas fa-toggle-on me-1"></i> Status
                        </label>
                        <select name="status" class="form-control form-select">
                            <option value="">All Status</option>
                            <option value="current" {{ request('status') == 'current' ? 'selected' : '' }}>Current</option>
                            <option value="open" {{ request('status') == 'open' ? 'selected' : '' }}>Open</option>
                            <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Closed</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3 mb-md-0">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search me-2"></i> Search
                        </button>
                        <a href="{{ route('financial-years.index') }}" class="btn btn-secondary ms-2">
                            <i class="fas fa-redo me-2"></i> Reset
                        </a>
                    </div>
                    <div class="col-md-3 mb-3 mb-md-0">
                        <button type="button" class="btn btn-warning" onclick="runMaintenance()">
                            <i class="fas fa-tools me-2"></i> Maintenance
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Modern Table Card -->
        <div class="table-card">
            @if($financialYears->count() > 0)
            <table class="table table-hover" id="fyTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Financial Year</th>
                        <th>Duration</th>
                        <th>Status</th>
                        <th>Progress</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($financialYears as $key => $fy)
                    <tr>
                        <td class="font-weight-600 text-muted">{{ $key + 1 }}</td>
                        <td>
                            <div class="fy-info">
                                <div class="fy-avatar">
                                    {{ strtoupper(substr($fy->name, 0, 1)) }}
                                </div>
                                <div class="fy-details">
                                    <div class="fy-name">{{ $fy->name }}</div>
                                    <div class="fy-duration">{{ $fy->getDurationInDays() }} days total</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <strong>{{ $fy->start_date->format('d M Y') }}</strong><br>
                            <small class="text-muted">to {{ $fy->end_date->format('d M Y') }}</small>
                        </td>
                        <td>
                            <span class="badge 
                                @if($fy->is_current) bg-success
                                @elseif($fy->is_closed) bg-secondary
                                @else bg-info text-dark @endif">
                                <i class="fas fa-circle me-1" style="font-size: 0.5rem;"></i>
                                @if($fy->is_current) Current
                                @elseif($fy->is_closed) Closed
                                @else Open @endif
                            </span>
                            @if($fy->is_closed && $fy->closed_at)
                            <br><small class="text-muted">{{ $fy->closed_at->format('d M Y') }}</small>
                            @endif
                        </td>
                        <td>
                            @if(!$fy->is_closed)
                            <div class="d-flex align-items-center">
                                <div class="me-2" style="min-width: 60px;">
                                    <small class="font-weight-bold">{{ $fy->getProgressPercentage() }}%</small>
                                </div>
                                <div class="progress flex-grow-1" style="height: 6px; width: 80px;">
                                    <div class="progress-bar bg-success" style="width: {{ $fy->getProgressPercentage() }}%"></div>
                                </div>
                            </div>
                            <small class="text-muted">{{ $fy->getRemainingDays() }} days left</small>
                            @else
                            <span class="text-muted">Completed</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="action-buttons justify-content-center">
                                <a href="{{ route('financial-years.show', $fy) }}" 
                                   class="btn-action btn-action-edit"
                                   title="View Details"
                                   data-toggle="tooltip">
                                    <i class="fas fa-eye"></i>
                                </a>
                                
                                @if(!$fy->is_current && !$fy->is_closed)
                                <button class="btn-action btn-action-edit" onclick="setAsCurrent({{ $fy->id }})"
                                        title="Set Current"
                                        data-toggle="tooltip">
                                    <i class="fas fa-check"></i>
                                </button>
                                @endif
                                
                                @if(!$fy->is_closed && $fy->canBeClosed())
                                <button class="btn-action btn-action-edit" onclick="closeFY({{ $fy->id }})"
                                        title="Close"
                                        data-toggle="tooltip">
                                    <i class="fas fa-lock"></i>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <h3 class="empty-state-title">No Financial Years Found</h3>
                <p class="empty-state-text">Create your first financial year to get started with payroll management.</p>
                <a href="{{ route('financial-years.create') }}" class="btn btn-primary mt-3">
                    <i class="fa fa-plus me-2"></i> Create Financial Year
                </a>
            </div>
            @endif
        </div>
    </div>
</div>

@endsection

@section('script')
<script>
    $(document).ready(function() {
        // Initialize tooltips
        $('[data-toggle="tooltip"]').tooltip();
        
        // Initialize DataTable
        var table = $('#fyTable').DataTable({
            "pageLength": 10,
            "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            "order": [[1, "asc"]], // Sort by financial year name
            "columnDefs": [
                { "orderable": false, "targets": [0, 5] }, // Disable sorting on # and Actions columns
                { "searchable": false, "targets": [0, 5] }  // Exclude # and Actions from search
            ],
            "language": {
                "search": "_INPUT_",
                "searchPlaceholder": "Search financial years...",
                "lengthMenu": "Show _MENU_ financial years",
                "info": "Showing _START_ to _END_ of _TOTAL_ financial years",
                "infoEmpty": "No financial years to display",
                "infoFiltered": "(filtered from _MAX_ total financial years)",
                "zeroRecords": "No matching financial years found",
                "emptyTable": "No financial years available",
                "paginate": {
                    "first": "First",
                    "last": "Last",
                    "next": "Next",
                    "previous": "Previous"
                }
            },
            "dom": '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
            "drawCallback": function(settings) {
                // Update fy count in header
                var info = this.api().page.info();
                $('#fy-count').text(info.recordsDisplay);
                
                // Reinitialize tooltips after table redraw
                $('[data-toggle="tooltip"]').tooltip();
            }
        });
        
        // Sync the filter card search with DataTable search
        $('#searchInput').on('keyup', function() {
            table.search(this.value).draw();
        });
        
        // Keep DataTables client-side search bound to the filter input
        $('#searchInput').on('keyup', function() {
            table.search(this.value).draw();
        });

        // Make selects auto-submit the filter form (URL-based filtering)
        $('select[name="status"]').on('change', function() {
            // Submit the surrounding form to apply server-side (URL) filters
            $(this).closest('form').submit();
        });

        // Handle filter form submission normally (let it reload the page with GET params)
        $('.filter-card form').on('submit', function() {
            // No JS interception here — allow normal GET submission so server-side filters apply
            return true;
        });

        // Reset filters — navigate to the base index URL to clear query params
        $('.filter-card .btn-secondary').on('click', function(e) {
            e.preventDefault();
            window.location.href = '{{ route('financial-years.index') }}';
        });
        
        // Confirmation for actions
        $(document).on('click', '.btn-action', function() {
            // Smooth scroll to top when clicking action buttons
            $('html, body').animate({
                scrollTop: 0
            }, 300);
        });
    });

    function setAsCurrent(fyId) {
        if (confirm('Are you sure you want to set this as the current financial year?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/financial-years/${fyId}/set-current`;
            
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            form.appendChild(csrfToken);
            
            document.body.appendChild(form);
            form.submit();
        }
    }

    function closeFY(fyId) {
        if (confirm('Are you sure you want to close this financial year? This action cannot be undone.')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/financial-years/${fyId}/close`;
            
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            form.appendChild(csrfToken);
            
            document.body.appendChild(form);
            form.submit();
        }
    }

    function runMaintenance() {
        if (confirm('Run financial year maintenance tasks? This will auto-close expired years and create upcoming years.')) {
            fetch('/financial-years/maintenance/run', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    toastr.success(data.message);
                    setTimeout(() => location.reload(), 1000);
                } else {
                    toastr.error(data.message);
                }
            })
            .catch(error => {
                toastr.error('Maintenance failed: ' + error.message);
            });
        }
    }
</script>
@endsection
