@extends('layouts.master')

@section('title', 'Combined OT & Holiday Pay Reports')

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
                                <i class="fa fa-chart-pie fa-lg"></i>
                            </div>
                            <div class="ms-3">
                                <h1 class="page-header-title">Combined OT & Holiday Pay Reports</h1>
                                <p class="page-header-subtitle">Comprehensive view of overtime and holiday payout records</p>
                            </div>
                        </div>
                    </div>
                    <div class="p-3 d-flex justify-content-between align-items-center">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                                <li class="breadcrumb-item active">Combined OT & Holiday Pay Reports</li>
                            </ol>
                        </nav>
                        <div>
                            <a href="{{ route('combined.reports.export', request()->query()) }}" class="btn btn-success btn-lg" target="_blank" style="background-color: #28a745; border-color: #28a745; font-weight: bold;">
                                <i class="fa fa-file-pdf-o me-1"></i> Export to PDF
                            </a>
                        </div>
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

        <!-- Holiday Payout Section -->
        <div class="row">
            <div class="col-md-12">
                <div class="settings-card">
                    <div class="card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white !important;">
                        <h4 class="card-title mb-0 fw-bold">
                            <i class="fa fa-sun-o me-2"></i>Holiday Pay
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped custom-table mb-0">
                                <thead>
                                    <tr>
                                        <th>Employee Name</th>
                                        <th>Holiday Work Days</th>
                                        <th>Total Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($holiday_payout_records as $record)
                                        <tr>
                                            <td>{{ $record->employee_name }}</td>
                                            <td>{{ number_format($record->holiday_work_days, 2) }}</td>
                                            <td>{{ number_format($record->holiday_amount, 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center">No holiday payout records found</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Overtime Section -->
        <div class="row">
            <div class="col-md-12">
                <div class="settings-card">
                    <div class="card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white !important;">
                        <h4 class="card-title mb-0 fw-bold">
                            <i class="fa fa-clock me-2"></i>OT Hours
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped custom-table mb-0">
                                <thead>
                                    <tr>
                                        <th>Employee Name</th>
                                        <th>OT Hours</th>
                                        <th>Total Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($overtime_records as $record)
                                        <tr>
                                            <td>{{ $record->employee_name }}</td>
                                            <td>{{ number_format($record->ot_hours, 2) }}</td>
                                            <td>{{ number_format($record->ot_amount, 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center">No overtime records found</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Consolidated Section -->
        <div class="row">
            <div class="col-md-12">
                <div class="settings-card">
                    <div class="card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white !important;">
                        <h4 class="card-title mb-0 fw-bold">
                            <i class="fa fa-calculator me-2"></i>Consolidated
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped custom-table mb-0">
                                <thead>
                                    <tr>
                                        <th>Employee Name</th>
                                        <th>Total Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($consolidated_records as $record)
                                        <tr>
                                            <td>{{ $record->employee_name }}</td>
                                            <td>{{ number_format($record->consolidated_amount, 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="text-center">No consolidated records found</td>
                                        </tr>
                                    @endforelse
                                    <tr>
                                        <td><strong>Total</strong></td>
                                        <td><strong>{{ number_format($total_consolidated, 2) }}</strong></td>
                                    </tr>
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
    });
</script>
@endsection