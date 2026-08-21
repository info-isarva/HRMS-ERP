@extends('layouts.master')
@section('title', 'DPDP Policy Distribution Auditing')

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
    
    /* Stats Section Restored to Top */
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
    
    /* Stats Grid */
    .stat-grid-row {
        margin-bottom: 2rem;
    }

    .stat-card {
        background: white;
        border-radius: 1rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
        border: 1px solid #e5e7eb;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
    }

    .stat-icon-box {
        width: 3.5rem;
        height: 3.5rem;
        border-radius: 0.75rem;
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
                            <i class="fas fa-user-shield fa-lg" style="color: rgba(255,255,255,0.9);"></i>
                        </div>
                        <div class="ms-3">
                            <h1 class="page-header-title" style="margin-left: 1rem;">DPDP Auditing</h1>
                            <p class="page-header-subtitle" style="margin-left: 1rem;">Track and audit employee digital policy acknowledgments for Digital Personal Data Protection Act compliance</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="p-3 d-flex justify-content-between align-items-center flex-wrap gap-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">DPDP Auditing</li>
                    </ol>
                </nav>
            </div>
        </div>

        <!-- Modern Stats Grid Section -->
        <div class="row stat-grid-row">
            <!-- Total Employees -->
            <div class="col-xl-4 col-md-6 mb-4 mb-xl-0">
                <div class="card stat-card h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon-box bg-light text-primary me-3">
                            <i class="fas fa-users fa-lg" style="color: #667eea;"></i>
                        </div>
                        <div>
                            <small class="text-muted text-uppercase font-weight-bold">Total Employees</small>
                            <h3 class="mb-0 font-weight-bold mt-1" style="color: #1f2937;">{{ $totalEmployees }}</h3>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Acknowledged -->
            <div class="col-xl-4 col-md-6 mb-4 mb-xl-0">
                <div class="card stat-card h-100" style="border-left: 4px solid #10b981;">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon-box bg-success-light text-success me-3" style="background-color: #f0fdf4;">
                            <i class="fas fa-check-circle fa-lg" style="color: #10b981;"></i>
                        </div>
                        <div>
                            <small class="text-muted text-uppercase font-weight-bold">Acknowledged</small>
                            <h3 class="mb-0 font-weight-bold mt-1" style="color: #10b981;">{{ $acknowledgedCount }}</h3>
                            <small class="text-success font-weight-bold">{{ $totalEmployees > 0 ? round(($acknowledgedCount / $totalEmployees) * 100, 1) : 0 }}% of staff</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pending -->
            <div class="col-xl-4 col-md-6 mb-4 mb-xl-0">
                <div class="card stat-card h-100" style="border-left: 4px solid #ef4444;">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon-box bg-danger-light text-danger me-3" style="background-color: #fef2f2;">
                            <i class="fas fa-clock fa-lg" style="color: #ef4444;"></i>
                        </div>
                        <div>
                            <small class="text-muted text-uppercase font-weight-bold">Pending Acknowledgment</small>
                            <h3 class="mb-0 font-weight-bold mt-1" style="color: #ef4444;">{{ $pendingCount }}</h3>
                            <small class="text-danger font-weight-bold">{{ $totalEmployees > 0 ? round(($pendingCount / $totalEmployees) * 100, 1) : 0 }}% of staff</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modern Filter Card -->
        <div class="filter-card">
            <form method="GET" action="{{ route('compliance.dpdp.policy-distribution') }}">
                <div class="row align-items-end">
                    <div class="col-xl-4 col-lg-4 col-md-6 col-12 mb-3">
                        <label class="font-weight-600 text-muted mb-2" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">
                            <i class="fas fa-search me-1"></i> Search
                        </label>
                        <div class="search-wrapper">
                            <i class="fas fa-search"></i>
                            <input type="text" id="searchInput" name="search" class="form-control" 
                                   placeholder="Search by name, ID, or email"
                                   value="{{ $filters['search'] ?? '' }}">
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-4 col-md-6 col-12 mb-3">
                        <label class="font-weight-600 text-muted mb-2" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">
                            <i class="fas fa-toggle-on me-1"></i> Consent Status
                        </label>
                        <select name="status" class="form-control form-select" onchange="this.form.submit()">
                            <option value="">All Statuses</option>
                            <option value="Acknowledged" {{ ($filters['status'] ?? '') === 'Acknowledged' ? 'selected' : '' }}>Acknowledged</option>
                            <option value="Pending" {{ ($filters['status'] ?? '') === 'Pending' ? 'selected' : '' }}>Pending</option>
                        </select>
                    </div>
                    <div class="col-xl-5 col-lg-4 col-md-12 col-12 mb-3">
                        <div class="btn-group-responsive d-flex justify-content-start">
                            <button type="submit" class="btn btn-primary flex-fill" style="min-width: 140px;">
                                <i class="fas fa-search me-2"></i> Search
                            </button>
                            <a href="{{ route('compliance.dpdp.policy-distribution') }}" class="btn btn-secondary flex-fill" style="min-width: 120px;">
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
                <table class="table table-hover desktop-table" id="dpdpTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Employee</th>
                            <th>Email Address</th>
                            <th>Consent Status</th>
                            <th>IP Address</th>
                            <th>Accepted At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data as $key => $row)
                        <tr>
                            <td class="font-weight-600 text-muted">{{ $key + 1 }}</td>
                            <td>
                                <div class="employee-info">
                                    <div class="employee-avatar">
                                        {{ strtoupper(substr($row->name, 0, 1)) }}
                                    </div>
                                    <div class="employee-details">
                                        <div class="employee-name">{{ $row->name }}</div>
                                        <div class="employee-id">ID: {{ $row->user_id }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <i class="fas fa-envelope text-muted me-2" style="font-size: 0.75rem;"></i>
                                <span>{{ $row->email ?? 'N/A' }}</span>
                            </td>
                            <td>
                                <span class="badge badge-{{ $row->status === 'Acknowledged' ? 'success' : 'danger' }}">
                                    <i class="fas fa-circle me-1" style="font-size: 0.5rem;"></i>
                                    {{ $row->status }}
                                </span>
                            </td>
                            <td>
                                <i class="fas fa-laptop text-muted me-2" style="font-size: 0.75rem;"></i>
                                <span>{{ $row->ip_address ?? '-' }}</span>
                            </td>
                            <td>
                                @if($row->accepted_at)
                                    <div style="font-size: 0.875rem;">
                                        <i class="far fa-calendar-alt text-muted me-2" style="font-size: 0.75rem;"></i>
                                        <span>{{ \Carbon\Carbon::parse($row->accepted_at)->format('d M Y, h:i A') }}</span>
                                    </div>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <div class="mb-3"><i class="fas fa-search-minus fa-3x" style="color: #cbd5e1;"></i></div>
                                <div class="font-weight-bold">No Records Found</div>
                                <div>Try modifying your search or filters.</div>
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
