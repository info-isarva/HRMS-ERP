@extends('layouts.master')

@section('title', 'Incentive Reports')

@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
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
                                <i class="fa fa-gift fa-lg"></i>
                            </div>
                            <div class="ms-3">
                                <h1 class="page-header-title">Incentive Reports</h1>
                                <p class="page-header-subtitle">View and analyze incentive payout records</p>
                            </div>
                        </div>
                    </div>
                    <div class="p-3 d-flex justify-content-between align-items-center">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                                <li class="breadcrumb-item active">Incentive Reports</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
        <!-- /Page Header -->

        <!-- Filter Card -->
        <div class="settings-card">
            <div class="card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white !important;">
                <h4 class="card-title mb-0">
                    <i class="fa fa-filter me-2"></i>Filter Options
                </h4>
            </div>
            <div class="card-body">
                <form action="" method="GET">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label>Month</label>
                            <select class="form-control form-select" name="month">
                                @foreach($months as $key => $value)
                                    <option value="{{ $key }}" {{ $key == $month ? 'selected' : '' }}>{{ $value }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label>Year</label>
                            <select class="form-control form-select" name="year">
                                @foreach($years as $yr)
                                    <option value="{{ $yr }}" {{ $yr == $year ? 'selected' : '' }}>{{ $yr }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label>Employee</label>
                            <select class="form-control form-select select2-employee" name="employee_id">
                                <option value="">All Employees</option>
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}" {{ $emp->id == $employee_id ? 'selected' : '' }}>{{ $emp->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fa fa-search"></i> Search
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Summary Stats -->
        <div class="row">
            <div class="col-md-4">
                <div class="metric-card">
                    <div class="card-body">
                        <div class="dash-widget-header">
                            <span class="dash-widget-icon text-primary">
                                <i class="fa fa-calendar"></i>
                            </span>
                            <div class="dash-count">
                                <h3 class="text-primary">{{ number_format($total_incentive_days, 2) }}</h3>
                            </div>
                            <div class="dash-title">Total Incentive Days</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="metric-card">
                    <div class="card-body">
                        <div class="dash-widget-header">
                            <span class="dash-widget-icon text-success">
                                <i class="fa fa-money-bill-wave"></i>
                            </span>
                            <div class="dash-count">
                                <h3 class="text-success">{{ number_format($total_incentive_amount, 2) }}</h3>
                            </div>
                            <div class="dash-title">Total Incentive Amount</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="metric-card">
                    <div class="card-body">
                        <div class="dash-widget-header">
                            <span class="dash-widget-icon text-info">
                                <i class="fa fa-calculator"></i>
                            </span>
                            <div class="dash-count">
                                <h3 class="text-info">{{ number_format($avg_incentive_days, 2) }}</h3>
                            </div>
                            <div class="dash-title">Average Incentive Days</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Results Table -->
        <div class="row">
            <div class="col-md-12">
                <div class="settings-card">
                    <div class="card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white !important;">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="card-title mb-0 fw-bold">
                                <i class="fa fa-table me-2"></i>Incentive Records
                            </h4>
                            <div>
                                <a href="{{ route('incentive.reports.export', request()->query()) }}" class="btn btn-outline-light btn-sm" target="_blank">
                                    <i class="fa fa-file-pdf-o me-1"></i> Export to PDF
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped custom-table mb-0 datatable">
                                <thead>
                                    <tr>
                                        <th>Employee ID</th>
                                        <th>Employee Name</th>
                                        <th>Month</th>
                                        <th>Year</th>
                                        <th class="text-end">Incentive Days</th>
                                        <th class="text-end">Rate/Day</th>
                                        <th class="text-end">Total Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($incentive_records as $record)
                                        <tr>
                                            <td>{{ $record->employee_code }}</td>
                                            <td>{{ $record->employee_name }}</td>
                                            <td>{{ $months[(int)$record->payout_month] }}</td>
                                            <td>{{ $record->payout_year }}</td>
                                            <td class="text-end">{{ number_format($record->incentive_days, 2) }}</td>
                                            <td class="text-end">{{ number_format($record->incentive_rate, 2) }}</td>
                                            <td class="text-end">{{ number_format($record->total_amount, 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center">No incentive records found</td>
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
</div>

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

    .settings-card .card-header h4,
    .settings-card .card-header h5 {
        font-weight: 600;
        margin: 0;
        display: flex;
        align-items: center;
        font-size: 1.1rem;
        color: white !important;
    }

    .settings-card .card-header i {
        margin-right: 0.5rem;
        opacity: 0.9;
    }

    .settings-card .card-body {
        padding: 2rem;
    }

    /* Metric Cards Styling */
    .metric-card {
        background: white;
        border-radius: 1rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
        border: 1px solid #e5e7eb;
        margin-bottom: 1.5rem;
        transition: all 0.3s ease;
    }

    .metric-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
    }

    .metric-card .card-body {
        padding: 2rem;
        text-align: center;
    }

    .dash-widget-icon {
        width: 3rem;
        height: 3rem;
        background: rgba(102, 126, 234, 0.1);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1rem;
        font-size: 1.25rem;
    }

    .dash-widget-icon.text-success { background: rgba(34, 197, 94, 0.1); color: #22c55e; }
    .dash-widget-icon.text-danger { background: rgba(239, 68, 68, 0.1); color: #ef4444; }
    .dash-widget-icon.text-warning { background: rgba(245, 158, 11, 0.1); color: #f59e0b; }
    .dash-widget-icon.text-info { background: rgba(59, 130, 246, 0.1); color: #3b82f6; }
    .dash-widget-icon.text-primary { background: rgba(102, 126, 234, 0.1); color: #667eea; }

    .dash-count h3 {
        font-size: 1.75rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }

    .dash-title {
        color: #6b7280;
        font-size: 0.875rem;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* Table Styling */
    .settings-card .table {
        background: white;
        margin-bottom: 0;
    }

    .settings-card .table thead th {
        background: #f8f9fa;
        border-bottom: 2px solid #e5e7eb;
        color: #374151;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-size: 0.75rem;
        padding: 1rem 0.75rem;
    }

    .settings-card .table tbody td {
        padding: 0.75rem;
        border-bottom: 1px solid #e5e7eb;
        color: #374151;
    }

    .settings-card .table tbody tr:hover {
        background: #f8f9fa;
    }

    /* Enhanced Gradient Backgrounds */
    .bg-gradient-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    .bg-gradient-success {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    }
    .bg-gradient-warning {
        background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);
    }
    .bg-gradient-danger {
        background: linear-gradient(135deg, #dc3545 0%, #e74c3c 100%);
    }
    .bg-gradient-info {
        background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
    }
    .bg-gradient-dark {
        background: linear-gradient(135deg, #343a40 0%, #23272b 100%);
    }

    /* Modern Button Styling */
    .btn {
        border-radius: 0.5rem;
        font-weight: 600;
        padding: 0.5rem 1.5rem;
        transition: all 0.2s ease;
    }

    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }

    /* Form Styling */
    .form-control, .select2-container--default .select2-selection--single {
        border-radius: 0.5rem;
        border: 2px solid #e9ecef;
        font-weight: 500;
    }

    .form-control:focus, .select2-container--default.select2-container--focus .select2-selection--single {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }

    /* Select2 Styling */
    .select2-container--default .select2-selection--single {
        height: calc(2.25rem + 2px);
        padding: 0.375rem 0.75rem;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #495057;
        line-height: 1.5;
        padding-left: 0;
        padding-right: 20px;
    }

    /* Icon Styling */
    .fas, .fa {
        font-weight: 900;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .card-body {
            padding: 1rem;
        }

        .h3, .h4 {
            font-size: 1.5rem;
        }

        .dash-widget-icon {
            width: 2.5rem;
            height: 2.5rem;
            font-size: 1rem;
        }

        .dash-count h3 {
            font-size: 1.5rem;
        }
    }

    /* Print Styling */
    @media print {
        .card {
            box-shadow: none !important;
            border: 1px solid #dee2e6 !important;
        }

        .btn {
            display: none !important;
        }

        .hover-row:hover {
            transform: none !important;
            box-shadow: none !important;
        }
    }
</style>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        if ($.fn.select2) {
            $('.select2-employee').select2({ width: '100%' });
        }

        if ($.fn.DataTable) {
            $('.datatable').DataTable({
                paging: true,
                searching: true,
                ordering: true,
                info: true,
                autoWidth: false,
                responsive: true
            });
        }
    });
</script>
@endsection