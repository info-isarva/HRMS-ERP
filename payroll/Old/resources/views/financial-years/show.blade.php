@extends('layouts.master')

@section('content')
<style>
    .detail-card {
        border: none;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        border-radius: 12px;
        margin-bottom: 2rem;
    }
    
    .fy-header {
        background: linear-gradient(135deg, #007bff, #6610f2);
        color: white;
        border-radius: 12px 12px 0 0;
        padding: 2rem;
    }
    
    .stat-box {
        background: linear-gradient(135deg, #f8f9fa, #e9ecef);
        border-radius: 8px;
        padding: 1.5rem;
        text-align: center;
        margin-bottom: 1rem;
    }
    
    .stat-number {
        font-size: 2rem;
        font-weight: 700;
        color: #007bff;
    }
    
    .stat-label {
        color: #6c757d;
        font-size: 0.9rem;
        font-weight: 600;
    }
    
    .quarter-card {
        border: 2px solid #e9ecef;
        border-radius: 8px;
        padding: 1rem;
        margin-bottom: 1rem;
        transition: all 0.3s ease;
    }
    
    .quarter-card.current {
        border-color: #28a745;
        background: linear-gradient(135deg, #d4edda, #c3e6cb);
    }
    
    .quarter-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    
    .report-item {
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 1rem;
        margin-bottom: 0.5rem;
        transition: all 0.3s ease;
    }
    
    .report-item:hover {
        background-color: #f8f9fa;
        border-color: #007bff;
    }
    
    .progress-circle {
        position: relative;
        width: 100px;
        height: 100px;
        margin: 0 auto;
    }
    
    .progress-circle svg {
        width: 100%;
        height: 100%;
        transform: rotate(-90deg);
    }
    
    .progress-circle .circle-bg {
        fill: none;
        stroke: #e9ecef;
        stroke-width: 8;
    }
    
    .progress-circle .circle-progress {
        fill: none;
        stroke: #007bff;
        stroke-width: 8;
        stroke-linecap: round;
        stroke-dasharray: 283;
        transition: stroke-dashoffset 0.5s ease;
    }
    
    .progress-text {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        font-size: 1.2rem;
        font-weight: 700;
        color: #007bff;
    }
    
    .tab-content {
        padding: 2rem 0;
    }
    
    .nav-pills .nav-link {
        border-radius: 25px;
        padding: 0.75rem 1.5rem;
        margin: 0 0.25rem;
    }
    
    .nav-pills .nav-link.active {
        background: linear-gradient(135deg, #007bff, #6610f2);
    }
</style>

<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Financial Year Details</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('financial-years.index') }}">Financial Years</a></li>
                        <li class="breadcrumb-item active">{{ $financialYear->name }}</li>
                    </ul>
                </div>
                <div class="col-auto">
                    <div class="btn-group">
                        @if(!$financialYear->is_current && !$financialYear->is_closed)
                        <button class="btn btn-success" onclick="setAsCurrent({{ $financialYear->id }})">
                            <i class="fas fa-check"></i> Set as Current
                        </button>
                        @endif
                        
                        @if(!$financialYear->is_closed && $financialYear->canBeClosed())
                        <button class="btn btn-warning" onclick="closeFY({{ $financialYear->id }})">
                            <i class="fas fa-lock"></i> Close FY
                        </button>
                        @endif
                        
                        <button class="btn btn-primary" onclick="generateReport()">
                            <i class="fas fa-file-alt"></i> Generate Report
                        </button>
                        
                        <a href="{{ route('financial-years.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Financial Year Header -->
        <div class="card detail-card">
            <div class="fy-header">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h1 class="mb-2">{{ $financialYear->name }}</h1>
                        <p class="mb-1 opacity-75">
                            <i class="fas fa-calendar me-2"></i>
                            {{ $financialYear->start_date->format('d M Y') }} - {{ $financialYear->end_date->format('d M Y') }}
                        </p>
                        <p class="mb-0 opacity-75">
                            <i class="fas fa-clock me-2"></i>
                            {{ $financialYear->getDurationInDays() }} days
                            @if($financialYear->description)
                                | {{ $financialYear->description }}
                            @endif
                        </p>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <div class="d-flex justify-content-md-end gap-2">
                            <span class="badge fs-6 px-3 py-2 
                                @if($financialYear->is_current) bg-success
                                @elseif($financialYear->is_closed) bg-secondary
                                @else bg-info @endif">
                                @if($financialYear->is_current) Current
                                @elseif($financialYear->is_closed) Closed
                                @else Open @endif
                            </span>
                        </div>
                        
                        @if(!$financialYear->is_closed)
                        <div class="progress-circle mt-3">
                            <svg>
                                <circle class="circle-bg" cx="50" cy="50" r="45"></circle>
                                <circle class="circle-progress" cx="50" cy="50" r="45" 
                                        style="stroke-dashoffset: {{ 283 - (283 * $statistics['basic_info']['progress_percentage'] / 100) }}"></circle>
                            </svg>
                            <div class="progress-text">{{ $statistics['basic_info']['progress_percentage'] }}%</div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Overview -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stat-box">
                    <div class="stat-number">{{ number_format($statistics['payroll_summary']['total_gross']) }}</div>
                    <div class="stat-label">Total Gross Pay</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-box">
                    <div class="stat-number">{{ number_format($statistics['payroll_summary']['total_net']) }}</div>
                    <div class="stat-label">Total Net Pay</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-box">
                    <div class="stat-number">{{ $statistics['employee_summary']['active_employees'] }}</div>
                    <div class="stat-label">Active Employees</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-box">
                    <div class="stat-number">{{ $statistics['payroll_summary']['total_payslips'] }}</div>
                    <div class="stat-label">Total Payslips</div>
                </div>
            </div>
        </div>

        <!-- Detailed Information Tabs -->
        <div class="card detail-card">
            <div class="card-body">
                <ul class="nav nav-pills nav-justified mb-3" id="fyTabs">
                    <li class="nav-item">
                        <a class="nav-link active" id="overview-tab" data-bs-toggle="pill" href="#overview">
                            <i class="fas fa-chart-line me-2"></i>Overview
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="quarters-tab" data-bs-toggle="pill" href="#quarters">
                            <i class="fas fa-calendar-check me-2"></i>Quarters
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="employees-tab" data-bs-toggle="pill" href="#employees">
                            <i class="fas fa-users me-2"></i>Employees
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="reports-tab" data-bs-toggle="pill" href="#reports">
                            <i class="fas fa-file-alt me-2"></i>Reports
                        </a>
                    </li>
                </ul>

                <div class="tab-content">
                    <!-- Overview Tab -->
                    <div class="tab-pane fade show active" id="overview">
                        <div class="row">
                            <div class="col-md-6">
                                <h5 class="mb-3">Payroll Summary</h5>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <tr>
                                            <td>Total Gross Pay</td>
                                            <td class="text-end">{{ get_currency_symbol() }}{{ number_format($statistics['payroll_summary']['total_gross']) }}</td>
                                        </tr>
                                        <tr>
                                            <td>Total Deductions</td>
                                            <td class="text-end">{{ get_currency_symbol() }}{{ number_format($statistics['payroll_summary']['total_deductions']) }}</td>
                                        </tr>
                                        <tr>
                                            <td>Total Net Pay</td>
                                            <td class="text-end">{{ get_currency_symbol() }}{{ number_format($statistics['payroll_summary']['total_net']) }}</td>
                                        </tr>
                                        <tr>
                                            <td>Average Monthly Gross</td>
                                            <td class="text-end">{{ get_currency_symbol() }}{{ number_format($statistics['payroll_summary']['average_monthly_gross']) }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h5 class="mb-3">Employee Summary</h5>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <tr>
                                            <td>Active Employees</td>
                                            <td class="text-end">{{ $statistics['employee_summary']['active_employees'] }}</td>
                                        </tr>
                                        <tr>
                                            <td>Employees Joined</td>
                                            <td class="text-end text-success">+{{ $statistics['employee_summary']['employees_joined'] }}</td>
                                        </tr>
                                        <tr>
                                            <td>Employees Resigned</td>
                                            <td class="text-end text-danger">-{{ $statistics['employee_summary']['employees_resigned'] }}</td>
                                        </tr>
                                        <tr>
                                            <td>Net Change</td>
                                            <td class="text-end {{ $statistics['employee_summary']['net_change'] >= 0 ? 'text-success' : 'text-danger' }}">
                                                {{ $statistics['employee_summary']['net_change'] >= 0 ? '+' : '' }}{{ $statistics['employee_summary']['net_change'] }}
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quarters Tab -->
                    <div class="tab-pane fade" id="quarters">
                        <div class="row">
                            @foreach($statistics['quarters'] as $quarter)
                            <div class="col-md-6 col-lg-3">
                                <div class="quarter-card {{ $quarter['is_current'] ? 'current' : '' }}">
                                    <h6 class="mb-2">
                                        {{ $quarter['name'] }}
                                        @if($quarter['is_current'])
                                            <span class="badge bg-success ms-2">Current</span>
                                        @endif
                                    </h6>
                                    <p class="mb-1 text-muted">
                                        {{ $quarter['start_date']->format('d M') }} - {{ $quarter['end_date']->format('d M Y') }}
                                    </p>
                                    <small class="text-muted">
                                        {{ $quarter['start_date']->diffInDays($quarter['end_date']) + 1 }} days
                                    </small>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Employees Tab -->
                    <div class="tab-pane fade" id="employees">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-body text-center">
                                        <h3 class="text-primary">{{ $statistics['employee_summary']['active_employees'] }}</h3>
                                        <p class="mb-0">Active Employees</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-body text-center">
                                        <h3 class="text-success">+{{ $statistics['employee_summary']['employees_joined'] }}</h3>
                                        <p class="mb-0">Joined This FY</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-body text-center">
                                        <h3 class="text-danger">-{{ $statistics['employee_summary']['employees_resigned'] }}</h3>
                                        <p class="mb-0">Resigned This FY</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Reports Tab -->
                    <div class="tab-pane fade" id="reports">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">Generated Reports</h5>
                            <div class="btn-group">
                                <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown">
                                    <i class="fas fa-plus"></i> Generate Report
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="#" onclick="generateReport('annual_summary', 'pdf')">Annual Summary (PDF)</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="generateReport('annual_summary', 'excel')">Annual Summary (Excel)</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="generateReport('payroll_summary', 'pdf')">Payroll Summary (PDF)</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="generateReport('department_summary', 'excel')">Department Summary (Excel)</a></li>
                                </ul>
                            </div>
                        </div>

                        @forelse($reports as $report)
                        <div class="report-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1">{{ $report->report_name }}</h6>
                                    <small class="text-muted">
                                        Generated {{ $report->generated_at->format('d M Y H:i') }}
                                        @if($report->file_size)
                                            | {{ $report->formatted_file_size }}
                                        @endif
                                    </small>
                                </div>
                                <div>
                                    <span class="badge {{ $report->status_badge_class }}">{{ ucfirst($report->status) }}</span>
                                    @if($report->status === 'completed' && $report->fileExists())
                                        <a href="{{ $report->getDownloadUrl() }}" class="btn btn-sm btn-outline-primary ms-2">
                                            <i class="fas fa-download"></i> Download
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-4">
                            <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                            <h5>No Reports Generated</h5>
                            <p class="text-muted">Generate your first report using the dropdown above</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
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

function generateReport(reportType = 'annual_summary', format = 'pdf') {
    toastr.info('Generating report...');
    
    fetch(`/financial-years/{{ $financialYear->id }}/reports/generate`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            report_type: reportType,
            format: format
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            toastr.success(data.message);
            setTimeout(() => {
                if (data.download_url) {
                    window.open(data.download_url, '_blank');
                }
                location.reload();
            }, 1000);
        } else {
            toastr.error(data.message);
        }
    })
    .catch(error => {
        toastr.error('Failed to generate report: ' + error.message);
    });
}
</script>
@endsection
