@extends('layouts.master')
@section('title', 'Confidential POSH Complaints')

@section('content')
@include('compliance.posh._deprecated-banner')
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
        margin-bottom: 2rem;
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

    .badge-info {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        color: white;
    }

    .badge-secondary {
        background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
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

    .btn-group-responsive {
        display: flex;
        gap: 0.5rem;
    }

    @media (max-width: 767px) {
        .btn-group-responsive {
            flex-direction: column;
        }
        .btn-group-responsive .btn {
            width: 100%;
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
                <div class="d-flex justify-content-between align-items-center position-relative">
                    <div class="d-flex align-items-center">
                        <div class="page-header-icon-box">
                            <i class="fas fa-shield-alt fa-lg" style="color: rgba(255,255,255,0.9);"></i>
                        </div>
                        <div class="ms-3">
                            <h1 class="page-header-title" style="margin-left: 1rem;">POSH Grievances</h1>
                            <p class="page-header-subtitle" style="margin-left: 1rem;">Confidential case files, incident logs, and resolution management</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="p-3 d-flex justify-content-between align-items-center flex-wrap gap-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">POSH Complaints</li>
                    </ol>
                </nav>
            </div>
        </div>

        <!-- Modern Filter Card -->
        <div class="filter-card">
            <form method="GET" action="{{ route('compliance.posh.complaints.index') }}">
                <div class="row align-items-end">
                    <div class="col-xl-4 col-lg-4 col-md-6 col-12 mb-3">
                        <label class="font-weight-600 text-muted mb-2" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">
                            <i class="fas fa-search me-1"></i> Search
                        </label>
                        <div class="search-wrapper">
                            <i class="fas fa-search"></i>
                            <input type="text" id="searchInput" name="search" class="form-control" 
                                   placeholder="Search by Case ID, respondent..."
                                   value="{{ $filters['search'] ?? '' }}">
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-4 col-md-6 col-12 mb-3">
                        <label class="font-weight-600 text-muted mb-2" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">
                            <i class="fas fa-toggle-on me-1"></i> Case Status
                        </label>
                        <select name="status" class="form-control form-select" onchange="this.form.submit()">
                            <option value="">All Statuses</option>
                            <option value="pending" {{ ($filters['status'] ?? '') === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="under_investigation" {{ ($filters['status'] ?? '') === 'under_investigation' ? 'selected' : '' }}>Under Investigation</option>
                            <option value="resolved" {{ ($filters['status'] ?? '') === 'resolved' ? 'selected' : '' }}>Resolved</option>
                            <option value="dismissed" {{ ($filters['status'] ?? '') === 'dismissed' ? 'selected' : '' }}>Dismissed</option>
                        </select>
                    </div>
                    <div class="col-xl-5 col-lg-4 col-md-12 col-12 mb-3">
                        <div class="btn-group-responsive d-flex justify-content-start">
                            <button type="submit" class="btn btn-primary flex-fill">
                                <i class="fas fa-search me-2"></i> Search
                            </button>
                            <a href="{{ route('compliance.posh.complaints.index') }}" class="btn btn-secondary flex-fill" style="min-width: 120px;">
                                <i class="fas fa-redo me-2"></i> Reset
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Modern Table Card -->
        <div class="table-card">
            <div class="table-responsive">
                <table class="table table-hover desktop-table" id="complaintsTable">
                    <thead>
                        <tr>
                            <th>Case Number</th>
                            <th>Complainant</th>
                            <th>Respondent Name</th>
                            <th>Incident Date</th>
                            <th>Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($complaints as $complaint)
                            <tr>
                                <td class="font-weight-bold text-primary">{{ $complaint->complaint_number }}</td>
                                <td>
                                    @if($complaint->is_anonymous)
                                        <span class="badge badge-danger">
                                            <i class="fas fa-user-secret mr-1"></i> Anonymous
                                        </span>
                                    @else
                                        <span class="font-weight-600 text-slate-800">{{ $complaint->complainant_name ?? ($complaint->employee->name ?? 'Unknown') }}</span>
                                        <br>
                                        <small class="text-muted">ID: {{ $complaint->employee->employee_id ?? 'N/A' }}</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="font-weight-600 text-slate-800">{{ $complaint->respondent_name }}</span>
                                    <br>
                                    <small class="text-muted">{{ $complaint->respondent_department ?? 'N/A' }}</small>
                                </td>
                                <td>
                                    <i class="far fa-calendar text-muted me-2" style="font-size: 0.75rem;"></i>
                                    <span>{{ \Carbon\Carbon::parse($complaint->incident_date)->format('d M Y') }}</span>
                                </td>
                                <td>
                                    @php
                                        $statusClass = 'badge-danger';
                                        $statusLabel = 'Pending';
                                        if($complaint->status === 'under_investigation') {
                                            $statusClass = 'badge-info';
                                            $statusLabel = 'Under Investigation';
                                        } elseif($complaint->status === 'resolved') {
                                            $statusClass = 'badge-success';
                                            $statusLabel = 'Resolved';
                                        } elseif($complaint->status === 'dismissed') {
                                            $statusClass = 'badge-secondary';
                                            $statusLabel = 'Dismissed';
                                        }
                                    @endphp
                                    <span class="badge {{ $statusClass }}">
                                        <i class="fas fa-circle me-1" style="font-size: 0.5rem;"></i>
                                        {{ $statusLabel }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('compliance.posh.complaints.show', $complaint->id) }}" class="btn btn-sm btn-primary" style="padding: 0.5rem 1rem;">
                                        <i class="fas fa-folder-open me-1"></i> Manage Case
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <div class="mb-3"><i class="fas fa-shield-alt fa-3x" style="color: #cbd5e1;"></i></div>
                                    <div class="font-weight-bold">No Complaints Logged</div>
                                    <div>All quiet! No compliance incidents have been registered.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
