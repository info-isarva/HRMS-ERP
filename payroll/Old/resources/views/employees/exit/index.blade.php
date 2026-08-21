@extends('layouts.master')
@section('title', 'Exit Employee List')
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
        top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(0, 0, 0, 0.05);
    }
    
    .page-header-circle-1 {
        position: absolute;
        top: -1rem; right: -1rem;
        width: 6rem; height: 6rem;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
    }
    
    .page-header-circle-2 {
        position: absolute;
        bottom: -1rem; left: -1rem;
        width: 8rem; height: 8rem;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
    }
    
    .page-header-icon-box {
        width: 4rem; height: 4rem;
        background: rgba(255, 255, 255, 0.2);
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: 1rem;
        display: flex; align-items: center; justify-content: center;
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
        width: 5rem; height: 5rem;
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 1rem;
        display: flex; align-items: center; justify-content: center;
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
    
    .dataTables_wrapper .dataTables_paginate .paginate_button.disabled {
        opacity: 0.5;
        cursor: not-allowed;
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
    
    .employee-details .employee-name a {
        color: #667eea;
        text-decoration: none;
        transition: all 0.2s ease;
    }
    
    .employee-details .employee-name a:hover {
        color: #764ba2;
        text-decoration: underline;
    }
    
    .employee-details .employee-id {
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
    
    .badge-success {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
    }
    
    .badge-danger {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
    }
    
    .badge-warning {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: white;
    }
    
    .badge-dark {
         background: linear-gradient(135deg, #4b5563 0%, #374151 100%);
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
    
    .btn-action-pdf {
        background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
        color: white;
    }
    
    .btn-action-refresh {
         background: linear-gradient(135deg, #10b981 0%, #059669 100%);
         color: white;
    }

    .btn-action-delete {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
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
    
    /* Mobile Responsive Cards */
    @media (max-width: 768px) {
        /* Mobile Card Layout */
        .desktop-table {
            display: none !important;
        }
        
        .mobile-employee-cards {
            display: block !important;
        }
        
        .employee-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 0.75rem;
            margin-bottom: 1rem;
            padding: 1rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            transition: all 0.2s ease;
        }
        
        .employee-card-header {
            display: flex;
            align-items: center;
            margin-bottom: 0.75rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid #f3f4f6;
        }
        
        .employee-card-avatar {
            width: 45px;
            height: 45px;
            font-size: 1rem;
            margin-right: 1rem;
        }
        
        .employee-card-info h6 {
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 0.25rem;
            font-size: 1rem;
        }
        
         .employee-card-info h6 a {
            color: #667eea;
            text-decoration: none;
        }
        
        .employee-card-info .employee-id {
            font-size: 0.8125rem;
            color: #6b7280;
        }
        
        .employee-card-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.75rem;
            margin-bottom: 1rem;
        }
        
        .employee-card-field {
            display: flex;
            flex-direction: column;
        }
        
        .employee-card-label {
            font-size: 0.75rem;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.25rem;
            font-weight: 500;
        }
        
        .employee-card-value {
            font-size: 0.875rem;
            color: #1f2937;
            font-weight: 500;
        }
        
        .employee-card-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 0.75rem;
            border-top: 1px solid #f3f4f6;
        }
        
        .employee-card-buttons {
            display: flex;
            gap: 0.5rem;
        }
        
        .employee-card-buttons .btn-action {
            width: 36px;
            height: 36px;
            font-size: 0.875rem;
        }
        
        /* Stats in header */
        .page-header-stats {
            text-align: left;
            margin-top: 1rem;
        }
        
        /* Filter layout mobile */
        .filter-card .row > [class*="col-"] {
             margin-bottom: 1rem;
        }
        
        .filter-card .btn-group-responsive {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
    }
    
    @media (min-width: 769px) {
        .mobile-employee-cards {
            display: none !important;
        }
        
        .desktop-table {
            display: table !important;
        }
    }
</style>

<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Modern Page Header -->
        <div class="page-header-card">
            <div class="page-header-gradient">
                <div class="page-header-pattern"></div>
                <div class="page-header-circle-1"></div>
                <div class="page-header-circle-2"></div>
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <div class="page-header-icon-box">
                            <i class="fas fa-sign-out-alt fa-lg" style="color: rgba(255,255,255,0.9);"></i>
                        </div>
                        <div class="ms-3">
                            <h1 class="page-header-title">Exit Employees</h1>
                            <p class="page-header-subtitle">Manage resignations, terminations, and clearances</p>
                        </div>
                    </div>
                    <!-- Stats Section -->
                    <div class="d-flex align-items-center text-end d-none d-md-flex">
                        <div class="page-header-stats me-3 text-white">
                            <p class="page-header-stats-label mb-0" style="opacity: 0.9; font-size: 0.875rem;">Total Exits</p>
                            <p class="page-header-stats-value mb-0 fw-bold" id="exit-count" style="font-size: 1.75rem;">{{ $exitRequests->count() }}</p>
                        </div>
                        <div class="page-header-stats-icon p-2 rounded" style="background: rgba(255,255,255,0.2); width: auto; height: auto;">
                            <i class="fas fa-user-times text-white" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="p-3 d-flex justify-content-between align-items-center flex-wrap gap-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('employees.index') }}">Employees</a></li>
                        <li class="breadcrumb-item active">Exit</li>
                    </ol>
                </nav>
                <div class="d-flex gap-2">
                    <a href="{{ route('exit-employees.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i> Initiate Exit
                    </a>
                </div>
            </div>
        </div>

        <!-- Modern Filter Card -->
        <div class="filter-card">
            <form action="{{ route('exit-employees.index') }}" method="GET">
                <div class="row align-items-end">
                    <div class="col-xl-3 col-lg-4 col-md-6 col-12 mb-3">
                        <label class="font-weight-600 text-muted mb-2" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">
                            <i class="fas fa-search me-1"></i> Search
                        </label>
                        <div class="search-wrapper">
                            <i class="fas fa-search"></i>
                            <input type="text" id="searchInput" name="employee_name" class="form-control" 
                                   placeholder="Name or ID" value="{{ request('employee_name') }}">
                        </div>
                    </div>
                    <div class="col-xl-2 col-lg-4 col-md-6 col-12 mb-3">
                        <label class="font-weight-600 text-muted mb-2" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">
                            <i class="fas fa-toggle-on me-1"></i> Status
                        </label>
                        <select class="form-control" name="status">
                            <option value="">All Status</option>
                            <option value="Pending" {{ request('status') == 'Pending' ? 'selected' : '' }}>Pending</option>
                            <option value="Approved" {{ request('status') == 'Approved' ? 'selected' : '' }}>Approved</option>
                            <option value="Rejected" {{ request('status') == 'Rejected' ? 'selected' : '' }}>Rejected</option>
                            <option value="Completed" {{ request('status') == 'Completed' ? 'selected' : '' }}>Completed</option>
                        </select>
                    </div>
                    <div class="col-xl-2 col-lg-4 col-md-6 col-12 mb-3">
                        <label class="font-weight-600 text-muted mb-2" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">
                            <i class="far fa-calendar-alt me-1"></i> From Date
                        </label>
                         <input type="date" class="form-control" name="date_from" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-xl-2 col-lg-4 col-md-6 col-12 mb-3">
                         <label class="font-weight-600 text-muted mb-2" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">
                            <i class="far fa-calendar-alt me-1"></i> To Date
                        </label>
                        <input type="date" class="form-control" name="date_to" value="{{ request('date_to') }}">
                    </div>
                    
                    <div class="col-xl-3 col-lg-8 col-md-12 col-12 mb-3">
                        <div class="btn-group-responsive d-flex flex-wrap flex-md-nowrap gap-2 justify-content-start">
                             <button type="submit" class="btn btn-primary flex-fill me-md-2 me-0 mb-2 mb-md-0" style="min-width: 120px;">
                                <i class="fas fa-search me-2"></i> Search
                            </button>
                             <a href="{{ route('exit-employees.index') }}" class="btn btn-secondary flex-fill" style="min-width: 100px;">
                                <i class="fas fa-redo me-2"></i> Reset
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Modern Table Card -->
        <div class="table-card">
            @if($exitRequests->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover desktop-table" id="exitEmployeesTable" style="width:100%">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Exit Type</th>
                                <th>Resignation Date</th>
                                <th>Last Working Day</th>
                                <th>Status</th>
                                <th>Notice Period</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($exitRequests as $request)
                                <tr>
                                    <td>
                                        <div class="employee-info">
                                            <div class="employee-avatar">
                                                {{ $request->employee ? strtoupper(substr($request->employee->name, 0, 1)) : '?' }}
                                            </div>
                                            <div class="employee-details">
                                                <div class="employee-name">
                                                    {{ $request->employee->name ?? 'Unknown' }}
                                                </div>
                                                <div class="employee-id">ID: {{ $request->employee->employee_id ?? '-' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $request->exit_type }}</td>
                                    <td>{{ $request->resignation_date ? $request->resignation_date->format('d M Y') : '-' }}</td>
                                    <td>{{ $request->last_working_day ? $request->last_working_day->format('d M Y') : '-' }}</td>
                                    <td>
                                        <span class="badge badge-pill 
                                            {{ $request->status == 'Approved' ? 'badge-success' : 
                                               ($request->status == 'Rejected' ? 'badge-danger' : 
                                               ($request->status == 'Completed' ? 'badge-dark' : 'badge-warning')) }}">
                                            {{ $request->status }}
                                        </span>
                                    </td>
                                    <td>{{ $request->notice_period_days }} Days</td>
                                    <td class="text-center">
                                        <div class="action-buttons justify-content-center">
                                            <a href="{{ route('exit-employees.edit', $request->id) }}" 
                                               class="btn-action btn-action-edit"
                                               title="Edit / Process"
                                               data-toggle="tooltip">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            
                                            @if($request->employee)
                                                <a href="{{ route('employee.experience-letter', $request->emp_id) }}" 
                                                   class="btn-action btn-action-pdf"
                                                   target="_blank"
                                                   title="Experience Letter"
                                                   data-toggle="tooltip">
                                                   <i class="fas fa-file-pdf"></i>
                                                </a>
                                            @endif

                                            @if($request->status == 'Completed' || ($request->employee && ($request->employee->status_name == 'Resigned' || $request->employee->status_name == 'Left'))) 
                                                <a href="#" class="btn-action btn-action-refresh rehire-btn" 
                                                   data-toggle="modal" 
                                                   data-target="#rehire_employee" 
                                                   data-emp-id="{{ $request->emp_id }}" 
                                                   data-emp-name="{{ $request->employee->name }}"
                                                   title="Rehire"
                                                   data-toggle="tooltip">
                                                   <i class="fas fa-redo"></i>
                                                </a>
                                            @endif
                                            
                                            <form action="{{ route('exit-employees.destroy', $request->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this exit request?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn-action btn-action-delete" title="Delete" data-toggle="tooltip">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Card View -->
                <div class="mobile-employee-cards" style="display: none;">
                    @foreach($exitRequests as $request)
                    <div class="employee-card" data-employee-name="{{ strtolower($request->employee->name ?? '') }}" data-employee-id="{{ strtolower($request->employee->employee_id ?? '') }}">
                        <div class="employee-card-header">
                            <div class="employee-card-avatar">
                                {{ $request->employee ? strtoupper(substr($request->employee->name, 0, 1)) : '?' }}
                            </div>
                            <div class="employee-card-info">
                                <h6 class="mb-0">{{ $request->employee->name ?? 'Unknown' }}</h6>
                                <div class="employee-id">ID: {{ $request->employee->employee_id ?? '-' }}</div>
                            </div>
                        </div>
                        
                        <div class="employee-card-details">
                            <div class="employee-card-field">
                                <div class="employee-card-label">Type</div>
                                <div class="employee-card-value">{{ $request->exit_type }}</div>
                            </div>
                            <div class="employee-card-field">
                                <div class="employee-card-label">Status</div>
                                <div class="employee-card-value">
                                    <span class="badge badge-pill 
                                        {{ $request->status == 'Approved' ? 'badge-success' : 
                                           ($request->status == 'Rejected' ? 'badge-danger' : 
                                           ($request->status == 'Completed' ? 'badge-dark' : 'badge-warning')) }}">
                                        {{ $request->status }}
                                    </span>
                                </div>
                            </div>
                            <div class="employee-card-field">
                                <div class="employee-card-label">Resignation</div>
                                <div class="employee-card-value">{{ $request->resignation_date ? $request->resignation_date->format('d M Y') : '-' }}</div>
                            </div>
                            <div class="employee-card-field">
                                <div class="employee-card-label">Last Day</div>
                                <div class="employee-card-value">{{ $request->last_working_day ? $request->last_working_day->format('d M Y') : '-' }}</div>
                            </div>
                        </div>
                        
                        <div class="employee-card-actions">
                            <div class="employee-card-buttons w-100 justify-content-between">
                                <a href="{{ route('exit-employees.edit', $request->id) }}" class="btn-action btn-action-edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @if($request->employee)
                                <a href="{{ route('employee.experience-letter', $request->emp_id) }}" target="_blank" class="btn-action btn-action-pdf">
                                    <i class="fas fa-file-pdf"></i>
                                </a>
                                @endif
                                @if($request->status == 'Completed' || ($request->employee && ($request->employee->status_name == 'Resigned' || $request->employee->status_name == 'Left'))) 
                                <a href="#" class="btn-action btn-action-refresh rehire-btn" data-toggle="modal" data-target="#rehire_employee" data-emp-id="{{ $request->emp_id }}" data-emp-name="{{ $request->employee->name }}">
                                    <i class="fas fa-redo"></i>
                                </a>
                                @endif
                                <form action="{{ route('exit-employees.destroy', $request->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this exit request?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-action btn-action-delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

            @else
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="fas fa-user-slash"></i>
                    </div>
                    <h3 class="empty-state-title">No Exit Requests Found</h3>
                    <p class="empty-state-text">There are no exit requests matching your criteria.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Rehire Modal -->
@include('employees.exit.rehire_modal')

@endsection

@section('script')
<script>
    $(document).ready(function() {
        // Initialize tooltips
        $('[data-toggle="tooltip"]').tooltip();

        // Initialize DataTable
        var table = $('#exitEmployeesTable').DataTable({
            "responsive": true,
            "pageLength": 10,
            "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
            "order": [[2, "desc"]], // Sort by Resignation Date desc
            "language": {
                "search": "_INPUT_",
                "searchPlaceholder": "Search...",
                "lengthMenu": "Show _MENU_ entries",
                "paginate": {
                    "first": "First",
                    "last": "Last",
                    "next": "Next",
                    "previous": "Previous"
                }
            },
            "dom": '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
            "drawCallback": function(settings) {
                var info = this.api().page.info();
                $('#exit-count').text(info.recordsDisplay);
                 $('[data-toggle="tooltip"]').tooltip();
            }
        });

        // Sync Search Input
        $('#searchInput').on('keyup', function() {
            if (window.innerWidth <= 768) {
                applyMobileFiltering(this.value);
            } else if (table) {
                table.search(this.value).draw();
            }
        });

        // Pass data to Rehire Modal
        $(document).on('click', '.rehire-btn', function() {
            var empId = $(this).data('emp-id');
            var empName = $(this).data('emp-name');
            $('#rehire_emp_id').val(empId);
            $('#rehire_emp_name').text(empName);
        });

        // Mobile Layout Handler
        function checkMobileLayout() {
            var isMobile = window.innerWidth <= 768;
            if (isMobile) {
                $('.mobile-employee-cards').show();
                $('.desktop-table').hide();
                if(table) table.destroy();
                applyMobileFiltering($('#searchInput').val());
            } else {
                $('.mobile-employee-cards').hide();
                $('.desktop-table').show();
                if (!$.fn.DataTable.isDataTable('#exitEmployeesTable')) {
                     // Re-init logic if needed, or simple reload
                     location.reload(); // Simplest way to ensure DataTables restores correctly after destroy
                }
            }
        }

        // Mobile Filtering Logic
        function applyMobileFiltering(term) {
            term = term.toLowerCase();
            $('.employee-card').each(function() {
                var name = $(this).data('employee-name').toString();
                var id = $(this).data('employee-id').toString();
                if (name.includes(term) || id.includes(term)) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
            $('#exit-count').text($('.employee-card:visible').length);
        }

        // Window Resize
        $(window).on('resize', function() {
            // Simple debounce can be added here
            // checkMobileLayout(); 
        });
        
        // Initial Check (Optional, CSS handles display but JS handles logic)
        // checkMobileLayout();
    });
</script>
@endsection
