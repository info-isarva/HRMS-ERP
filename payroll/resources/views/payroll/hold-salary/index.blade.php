@extends('layouts.master')
@section('title', 'Hold Salary Management')
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
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%;);
        color: white;
    }
    
    .badge-warning {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: white;
    }
    
    .badge-info {
         background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
         color: white;
    }
    
    /* Action Buttons */
    .action-buttons {
        display: flex;
        gap: 0.5rem;
        justify-content: center;
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
    
    .btn-action-release {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
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
</style>

<div class="page-wrapper">
    <div class="content container-fluid">
        
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Modern Page Header -->
        <div class="page-header-card">
            <div class="page-header-gradient">
                <div class="page-header-pattern"></div>
                <div class="page-header-circle-1"></div>
                <div class="page-header-circle-2"></div>
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <div class="page-header-icon-box">
                            <i class="fas fa-hand-holding-usd fa-lg" style="color: rgba(255,255,255,0.9);"></i>
                        </div>
                        <div class="ms-3">
                            <h1 class="page-header-title">Hold Salary</h1>
                            <p class="page-header-subtitle">Manage employee salary holds and releases</p>
                        </div>
                    </div>
                    <!-- Stats Section -->
                    <div class="d-flex align-items-center text-end d-none d-md-flex">
                        <div class="page-header-stats me-3 text-white">
                            <p class="page-header-stats-label mb-0" style="opacity: 0.9; font-size: 0.875rem;">Active Holds</p>
                            <p class="page-header-stats-value mb-0 fw-bold" style="font-size: 1.75rem;">{{ $activeHolds->count() }}</p>
                        </div>
                        <div class="page-header-stats-icon p-2 rounded" style="background: rgba(255,255,255,0.2); width: auto; height: auto;">
                            <i class="fas fa-pause-circle text-white" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="p-3 d-flex justify-content-between align-items-center flex-wrap gap-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('payroll.index') }}">Payroll</a></li>
                        <li class="breadcrumb-item active">Hold Salary</li>
                    </ol>
                </nav>
                <div class="d-flex gap-2">
                    <a href="{{ route('hold-salary.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i> Add Hold
                    </a>
                </div>
            </div>
        </div>

        <!-- Modern Filter Card -->
        <div class="filter-card">
            <form action="{{ route('hold-salary.index') }}" method="GET">
                <div class="row align-items-end">
                    <div class="col-xl-3 col-lg-4 col-md-6 col-12 mb-3">
                        <label class="font-weight-600 text-muted mb-2" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">
                            <i class="fas fa-search me-1"></i> Search
                        </label>
                        <div class="search-wrapper">
                            <i class="fas fa-search"></i>
                            <input type="text" name="employee_name" class="form-control" 
                                   placeholder="Name or ID" value="{{ request('employee_name') }}">
                        </div>
                    </div>
                    <div class="col-xl-2 col-lg-4 col-md-6 col-12 mb-3">
                        <label class="font-weight-600 text-muted mb-2" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">
                            <i class="fas fa-toggle-on me-1"></i> Status
                        </label>
                        <select class="form-control" name="status">
                            <option value="">All Status</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="released" {{ request('status') == 'released' ? 'selected' : '' }}>Released</option>
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
                        <div class="d-flex gap-2">
                             <button type="submit" class="btn btn-primary flex-fill">
                                <i class="fas fa-search me-2"></i> Search
                            </button>
                             <a href="{{ route('hold-salary.index') }}" class="btn btn-secondary flex-fill">
                                <i class="fas fa-redo me-2"></i> Reset
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Modern Table Card -->
        <div class="table-card">
            @if($heldSalaries->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover" style="width:100%">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Type</th>
                                <th>Hold Month</th>
                                <th>Held On</th>
                                <th>Status</th>
                                <th>Remarks</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($heldSalaries as $hold)
                                <tr>
                                    <td>
                                        <div class="employee-info">
                                            <div class="employee-avatar">
                                                {{ $hold->employee ? strtoupper(substr($hold->employee->name, 0, 1)) : '?' }}
                                            </div>
                                            <div class="employee-details">
                                                <div class="employee-name">
                                                    {{ $hold->employee ? $hold->employee->name : 'Unknown' }}
                                                </div>
                                                <div class="employee-id">ID: {{ $hold->employee ? $hold->employee->employee_id : '-' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($hold->hold_type == 'month')
                                            <span class="badge badge-info text-white">One Month</span>
                                        @else
                                            <span class="badge badge-warning text-white">Indefinite</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($hold->payout_month)
                                            {{ \Carbon\Carbon::createFromDate($hold->payout_year, $hold->payout_month, 1)->format('M Y') }}
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>{{ $hold->created_at->format('d M Y') }}</td>
                                    <td>
                                        @if($hold->status == 'active')
                                            <span class="badge badge-danger">Active</span>
                                        @else
                                            <span class="badge badge-success">Released</span>
                                            <div class="small text-muted mt-1">{{ $hold->released_at ? \Carbon\Carbon::parse($hold->released_at)->format('d M Y') : '' }}</div>
                                        @endif
                                    </td>
                                    <td style="max-width: 250px; white-space: normal;">{{ Str::limit($hold->remarks, 50) }}</td>
                                    <td class="text-center">
                                        @if($hold->status == 'active')
                                        <div class="action-buttons">
                                            <a href="{{ route('hold-salary.edit', $hold->id) }}" 
                                               class="btn-action btn-primary text-white" 
                                               title="Edit Hold">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="{{ route('hold-salary.release-form', $hold->id) }}" 
                                               class="btn-action btn-action-release" 
                                               title="Release Salary">
                                                <i class="fas fa-check"></i>
                                            </a>
                                        </div>
                                        @else
                                            <span class="text-muted"><i class="fas fa-check-circle text-success"></i> Released</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-3">
                    {{ $heldSalaries->links() }}
                </div>
            @else
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="fas fa-folder-open"></i>
                    </div>
                    <h3 class="empty-state-title">No Salary Holds Found</h3>
                    <p class="empty-state-text">There are no hold records matching your criteria.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

