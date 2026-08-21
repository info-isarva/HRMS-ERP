@extends('layouts.master')
@section('title', isset($exitRequest) ? 'Process Exit' : 'Initiate Exit')
@section('content')

@section('style')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css" rel="stylesheet" />

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

    /* Form Styling */
    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-label {
        font-weight: 600;
        color: #374151;
        margin-bottom: 0.5rem;
        font-size: 0.875rem;
    }

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
        height: auto !important; /* Fix for select2 height issues if any */
    }
    
    /* Specific fix for textarea */
    textarea.form-control {
        line-height: 1.5;
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
        border-color: #9ca3af;
        color: #374151 !important;
    }

    /* Section Headers */
    .section-header {
        font-size: 1.1rem;
        font-weight: 600;
        color: #374151;
        margin-bottom: 1.5rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid #e5e7eb;
    }

    .form-row {
        margin-bottom: 2rem;
    }

    .form-row:last-child {
        margin-bottom: 0;
    }

    /* Select2 Styling Overrides */
    .select2-container .select2-selection--single {
        height: 48px !important; /* Matched to form-control height roughly */
        border-radius: 0.5rem !important;
        border: 1px solid #d1d5db !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 46px !important;
        padding-left: 1rem !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 46px !important;
    }
    
    /* Table Styling for Inner Cards */
    .table-striped tbody tr:nth-of-type(odd) {
        background-color: rgba(0,0,0,.02);
    }
    .card-title {
        font-size: 1.1rem; 
    }
    
    /* Inner Card Styling */
    .inner-card {
        border: none;
        border-radius: 1rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05); /* Softer shadow */
        overflow: hidden; /* Fixes the corner/border issue */
        margin-bottom: 1rem;
    }

    .card-header-inner {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); /* Match main theme */
        color: white; /* White text */
        border: none;
        padding: 1rem 1.25rem;
    }
    
    .card-header-inner .card-title {
        color: white !important;
        font-weight: 600;
        font-size: 1rem;
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
        
        <!-- Modern Page Header -->
        <div class="page-header-card">
            <div class="page-header-gradient">
                <div class="page-header-pattern"></div>
                <div class="page-header-circle-1"></div>
                <div class="page-header-circle-2"></div>
                <div class="d-flex align-items-center">
                    <div class="page-header-icon-box">
                        <i class="fas fa-door-open fa-lg"></i>
                    </div>
                    <div class="ms-3" style="margin-left: 1rem;">
                        <h1 class="page-header-title">{{ isset($exitRequest) ? 'Process Employee Exit' : 'Initiate Employee Exit' }}</h1>
                        <p class="page-header-subtitle">Manage employee resignations and full & final settlements</p>
                    </div>
                </div>
            </div>
            <div class="p-3 d-flex justify-content-between align-items-center">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('exit-employees.index') }}">Exit Employees</a></li>
                        <li class="breadcrumb-item active">{{ isset($exitRequest) ? 'Process' : 'Initiate' }}</li>
                    </ol>
                </nav>
                <div>
                    <a href="{{ route('exit-employees.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to List
                    </a>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="settings-card">
                    <div class="card-header">
                        <h5><i class="fas fa-file-invoice-dollar me-2"></i>Exit Details & Settlement</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ isset($exitRequest) ? route('exit-employees.update', $exitRequest->id) : route('exit-employees.store') }}" method="POST">
                            @csrf
                            @if(isset($exitRequest))
                                @method('PUT')
                            @endif

                            <!-- Basic Information -->
                            <div class="row mb-4">
                                <div class="col-12 mb-3">
                                    <h6 class="section-header">Basic Information</h6>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Employee <span class="text-danger">*</span></label>
                                        @if(isset($exitRequest))
                                            <input type="text" class="form-control" value="{{ $exitRequest->employee->name ?? 'Shared' }}" readonly>
                                            <input type="hidden" name="emp_id" value="{{ $exitRequest->emp_id }}">
                                        @else
                                            <select class="select form-control" name="emp_id" required>
                                                <option value="">Select Employee</option>
                                                @foreach($employees as $employee)
                                                    <option value="{{ $employee->id }}">{{ $employee->name }} ({{ $employee->employee_id }})</option>
                                                @endforeach
                                            </select>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Exit Type <span class="text-danger">*</span></label>
                                        <select class="select form-control" name="exit_type" required>
                                            <option value="Resignation" {{ (isset($exitRequest) && $exitRequest->exit_type == 'Resignation') ? 'selected' : '' }}>Resignation</option>
                                            <option value="Termination" {{ (isset($exitRequest) && $exitRequest->exit_type == 'Termination') ? 'selected' : '' }}>Termination</option>
                                            <option value="Absconding" {{ (isset($exitRequest) && $exitRequest->exit_type == 'Absconding') ? 'selected' : '' }}>Absconding</option>
                                            <option value="Retirement" {{ (isset($exitRequest) && $exitRequest->exit_type == 'Retirement') ? 'selected' : '' }}>Retirement</option>
                                            <option value="Other" {{ (isset($exitRequest) && $exitRequest->exit_type == 'Other') ? 'selected' : '' }}>Other</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Resignation Date <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" name="resignation_date" id="resignation_date" 
                                            value="{{ isset($exitRequest) ? ($exitRequest->resignation_date ? $exitRequest->resignation_date->format('Y-m-d') : '') : '' }}" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Last Working Day <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" name="last_working_day" id="last_working_day"
                                            value="{{ isset($exitRequest) ? ($exitRequest->last_working_day ? $exitRequest->last_working_day->format('Y-m-d') : '') : '' }}" required>
                                        <small class="form-text text-muted">Notice Period: <span id="notice_period_days" class="font-weight-bold">{{ isset($exitRequest) ? $exitRequest->notice_period_days : 0 }}</span> days</small>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="form-label">Reason <span class="text-danger">*</span></label>
                                        <textarea class="form-control" name="reason" rows="3" required placeholder="Enter reason for exit">{{ isset($exitRequest) ? $exitRequest->reason : '' }}</textarea>
                                    </div>
                                </div>
                                
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="form-label">Remarks</label>
                                        <textarea class="form-control" name="remarks" rows="2" placeholder="Additional remarks">{{ isset($exitRequest) ? $exitRequest->remarks : '' }}</textarea>
                                    </div>
                                </div>

                                <!-- Settlement Section -->
                                <div class="col-md-12 mt-4">
                                    <h6 class="section-header">Settlement Breakdown</h6>
                                </div>

                                <!-- Settlement Mode & Date -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Settlement Mode</label>
                                        <select class="select form-control" name="settlement_mode" id="settlement_mode">
                                            <option value="">Select Mode</option>
                                            <option value="immediate" {{ (isset($exitRequest) && $exitRequest->settlement_mode == 'immediate') ? 'selected' : '' }}>Immediate (FFS Now)</option>
                                            <option value="payroll" {{ (isset($exitRequest) && $exitRequest->settlement_mode == 'payroll') ? 'selected' : '' }}>During Payroll</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6" id="settlement_date_div" style="display: none;">
                                    <div class="form-group">
                                        <label class="form-label">Settlement Date</label>
                                        <input type="date" class="form-control" name="settlement_date" id="settlement_date"
                                            value="{{ isset($exitRequest) ? ($exitRequest->settlement_date ? $exitRequest->settlement_date->format('Y-m-d') : date('Y-m-d')) : date('Y-m-d') }}">
                                    </div>
                                </div>

                                <!-- Salary & Statutory Breakdowns -->
                                <div class="col-md-12 mt-2">
                                    <div class="row">
                                        <!-- Salary Components -->
                                        <div class="col-md-6">
                                            <div class="card inner-card">
                                                <div class="card-header card-header-inner py-2">
                                                    <h5 class="card-title mb-0">Salary Components</h5>
                                                </div>
                                                <div class="card-body p-0">
                                                    <div class="table-responsive">
                                                        <table class="table table-striped mb-0" id="salary_components_table">
                                                            <thead>
                                                                <tr>
                                                                    <th>Active</th>
                                                                    <th>Component</th>
                                                                    <th class="text-right">Monthly ({{ get_currency_symbol() }})</th>
                                                                    <th class="text-right">Prorated ({{ get_currency_symbol() }})</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <!-- Populated by JS -->
                                                            </tbody>
                                                            <tfoot>
                                                                <tr class="font-weight-bold">
                                                                    <th colspan="2">Total Earnings</th>
                                                                    <th class="text-right" id="total_monthly_gross">0.00</th>
                                                                    <th class="text-right" id="total_prorated_salary">0.00</th>
                                                                </tr>
                                                            </tfoot>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Statutory Components -->
                                        <div class="col-md-6">
                                            <div class="card inner-card">
                                                <div class="card-header card-header-inner py-2">
                                                    <h5 class="card-title mb-0">Statutory Components</h5>
                                                </div>
                                                <div class="card-body p-0">
                                                    <div class="table-responsive">
                                                        <table class="table table-striped mb-0" id="statutory_components_table">
                                                            <thead>
                                                                <tr>
                                                                    <th>Active</th>
                                                                    <th>Component</th>
                                                                    <th class="text-right">Monthly ({{ get_currency_symbol() }})</th>
                                                                    <th class="text-right">Prorated ({{ get_currency_symbol() }})</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <!-- Populated by JS -->
                                                            </tbody>
                                                            <tfoot>
                                                                <tr class="font-weight-bold">
                                                                    <th colspan="2">Total Statutory</th>
                                                                    <th class="text-right" id="total_monthly_statutory">0.00</th>
                                                                    <th class="text-right" id="total_prorated_statutory">0.00</th>
                                                                </tr>
                                                            </tfoot>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Summary Inputs (Read Only) -->
                                <div class="col-md-12 mt-3">
                                    <div class="card bg-light border-0">
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label class="form-label text-muted">Prorated Salary</label>
                                                        <input type="text" class="form-control font-weight-bold" id="prorated_salary" readonly value="{{ isset($exitRequest) ? $exitRequest->prorated_salary_amount : '0.00' }}" name="prorated_salary_amount">
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label class="form-label text-muted">Statutory Credit</label>
                                                        <input type="text" class="form-control font-weight-bold" id="prorated_statutory_credit" readonly value="{{ isset($exitRequest) ? $exitRequest->prorated_statutory_credit : '0.00' }}" name="prorated_statutory_credit">
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label class="form-label text-muted">Gross Deductions</label>
                                                        <input type="text" class="form-control font-weight-bold text-danger" id="prorated_deductions" name="prorated_statutory_debit" readonly value="{{ isset($exitRequest) ? $exitRequest->prorated_statutory_debit : '0.00' }}">
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label class="form-label text-muted">Pending Advance</label>
                                                        <input type="text" class="form-control font-weight-bold text-danger" name="pending_advance" id="pending_advance" value="{{ isset($exitRequest) ? $exitRequest->pending_advance : '0.00' }}" readonly>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Notice Pay -->
                                <div class="col-md-12 mt-3">
                                    <div class="card inner-card">
                                        <div class="card-header card-header-inner py-2">
                                            <h5 class="card-title mb-0">Notice Pay</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label class="form-label">Calculated Amount</label>
                                                        <input type="text" class="form-control" id="notice_pay_amount_calculated" readonly value="{{ isset($exitRequest) ? $exitRequest->notice_pay_amount_calculated : '0.00' }}" name="notice_pay_amount_calculated">
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label class="form-label">Override Amount</label>
                                                        <input type="number" step="0.01" class="form-control ffs-override" name="notice_pay_amount_override" id="notice_pay_amount_override" placeholder="Enter amount to override" value="{{ isset($exitRequest) ? $exitRequest->notice_pay_amount_override : '' }}">
                                                        <small class="text-muted">Positive = Earning, Negative = Deduction</small>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                     <div class="form-group">
                                                        <label class="form-label">Shortfall Days</label>
                                                        <input type="text" class="form-control" readonly id="notice_period_shortfall_days" name="notice_period_shortfall_days" value="{{ isset($exitRequest) ? $exitRequest->notice_period_shortfall_days : '' }}">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Leave Encashment -->
                                <div class="col-md-12 mt-3">
                                    <div class="card inner-card">
                                        <div class="card-header card-header-inner py-2">
                                            <h5 class="card-title mb-0">Leave Encashment</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label class="form-label">Calculated Days</label>
                                                        <input type="text" class="form-control" id="leave_encashment_days_calculated" readonly value="{{ isset($exitRequest) ? $exitRequest->leave_encashment_days_calculated : '' }}" name="leave_encashment_days_calculated">
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label class="form-label">Override Days</label>
                                                        <input type="number" step="0.5" class="form-control" name="leave_encashment_days_override" value="{{ isset($exitRequest) ? $exitRequest->leave_encashment_days_override : '' }}">
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label class="form-label">Calculated Amount</label>
                                                        <input type="text" class="form-control" id="leave_encashment_amount_calculated" readonly value="{{ isset($exitRequest) ? $exitRequest->leave_encashment_amount_calculated : '0.00' }}" name="leave_encashment_amount_calculated">
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label class="form-label">Override Amount</label>
                                                        <input type="number" step="0.01" class="form-control ffs-override" name="leave_encashment_amount_override" id="leave_encashment_amount_override" placeholder="Override Amount" value="{{ isset($exitRequest) ? $exitRequest->leave_encashment_amount_override : '' }}">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Gratuity -->
                                <div class="col-md-12 mt-3">
                                    <div class="card inner-card">
                                        <div class="card-header card-header-inner py-2">
                                            <h5 class="card-title mb-0">Gratuity</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label class="form-label">Tenure (Years)</label>
                                                        <input type="text" class="form-control" id="gratuity_tenure_years_calculated" readonly value="{{ isset($exitRequest) ? $exitRequest->gratuity_tenure_years_calculated : '' }}" name="gratuity_tenure_years_calculated">
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label class="form-label">Override Tenure</label>
                                                        <input type="number" step="0.1" class="form-control" name="gratuity_tenure_years_override" value="{{ isset($exitRequest) ? $exitRequest->gratuity_tenure_years_override : '' }}">
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label class="form-label">Calculated Amount</label>
                                                        <input type="text" class="form-control" id="gratuity_amount_calculated" readonly value="{{ isset($exitRequest) ? $exitRequest->gratuity_amount_calculated : '0.00' }}" name="gratuity_amount_calculated">
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label class="form-label">Override Amount</label>
                                                        <input type="number" step="0.01" class="form-control ffs-override" name="gratuity_amount_override" id="gratuity_amount_override" placeholder="Override Amount" value="{{ isset($exitRequest) ? $exitRequest->gratuity_amount_override : '' }}">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Bonus & Others -->
                                <div class="col-md-12 mt-3">
                                    <div class="card inner-card">
                                        <div class="card-header card-header-inner py-2">
                                            <h5 class="card-title mb-0">Bonus & Others</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label class="form-label">Bonus Amount</label>
                                                        <input type="number" step="0.01" class="form-control ffs-override" name="bonus_amount_override" id="bonus_amount_override" placeholder="Bonus Amount" value="{{ isset($exitRequest) ? $exitRequest->bonus_amount_override : '' }}">
                                                        <input type="hidden" name="bonus_amount_calculated" id="bonus_amount_calculated" value="{{ isset($exitRequest) ? $exitRequest->bonus_amount_calculated : '0.00' }}">
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label class="form-label">Other Earnings</label>
                                                        <input type="number" step="0.01" class="form-control ffs-override" name="other_earnings" id="other_earnings" placeholder="Other Earnings" value="{{ isset($exitRequest) ? $exitRequest->other_earnings : '' }}">
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label class="form-label">Other Deductions</label>
                                                        <input type="number" step="0.01" class="form-control ffs-override" name="other_deductions" id="other_deductions" placeholder="Other Deductions" value="{{ isset($exitRequest) ? $exitRequest->other_deductions : '' }}">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Final Net Pay -->
                                <div class="col-md-12 mt-4">
                                    <div class="card bg-success text-white shadow-lg" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); border: none;">
                                        <div class="card-body">
                                            <div class="row align-items-center">
                                                <div class="col-md-6">
                                                    <h3 class="text-white mb-0">Final Net Pay Estimate</h3>
                                                    <small class="text-white opacity-75" id="net_pay_help">Calculated based on selection.</small>
                                                    <span id="payroll_status_badge" class="badge badge-warning ml-2" style="display:none;"></span>
                                                </div>
                                                <div class="col-md-6 text-right">
                                                    <div class="form-group mb-0">
                                                        <input type="number" step="0.01" class="form-control form-control-lg text-right font-weight-bold" name="settlement_amount" id="settlement_amount" 
                                                            value="{{ isset($exitRequest) ? $exitRequest->settlement_amount : '0.00' }}" readonly style="font-size: 2rem; background: rgba(255,255,255,0.2); color: white; border: none; height: auto;">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-12 mt-3">
                                    <div class="form-group">
                                        <label class="form-label">Settlement Notes</label>
                                        <textarea class="form-control" name="settlement_notes" rows="2" placeholder="Notes regarding the settlement calculation">{{ isset($exitRequest) ? $exitRequest->settlement_notes : '' }}</textarea>
                                    </div>
                                </div>

                                @if(isset($exitRequest))
                                    <div class="col-12 mt-3">
                                        <h6 class="section-header">Status & Operations</h6>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">Status <span class="text-danger">*</span></label>
                                            <select class="select form-control" name="status" required>
                                                <option value="Pending" {{ $exitRequest->status == 'Pending' ? 'selected' : '' }}>Pending</option>
                                                <option value="Approved" {{ $exitRequest->status == 'Approved' ? 'selected' : '' }}>Approved</option>
                                                <option value="Rejected" {{ $exitRequest->status == 'Rejected' ? 'selected' : '' }}>Rejected</option>
                                                <option value="Completed" {{ $exitRequest->status == 'Completed' ? 'selected' : '' }}>Completed (Final Exit)</option>
                                            </select>
                                            <small class="text-danger">Marking as "Completed" will deactivate the employee login and set status to Left.</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <div class="form-check mt-4">
                                                <input class="form-check-input" type="checkbox" name="exit_interview_conducted" id="exit_interview_conducted" value="1" {{ $exitRequest->exit_interview_conducted ? 'checked' : '' }}>
                                                <label class="form-check-label font-weight-bold" for="exit_interview_conducted">
                                                    Exit Interview Conducted
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12" id="exit_interview_notes_div" style="{{ $exitRequest->exit_interview_conducted ? '' : 'display:none;' }}">
                                        <div class="form-group">
                                            <label class="form-label">Exit Interview Notes</label>
                                            <textarea class="form-control" name="exit_interview_notes" rows="3">{{ $exitRequest->exit_interview_notes }}</textarea>
                                        </div>
                                    </div>
                                @endif
                                
                            </div>

                            <div class="submit-section mt-4 mb-3 text-right">
                                <a href="{{ route('exit-employees.index') }}" class="btn btn-outline-secondary mr-2">
                                    <i class="fas fa-times me-1"></i> Cancel
                                </a>
                                <button class="btn btn-primary submit-btn">
                                    <i class="fas fa-save me-1"></i> {{ isset($exitRequest) ? 'Update Exit Request' : 'Initiate Exit' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('script')
<script>
    $(document).ready(function() {
        // Initialize Select2
        if($('.select').length > 0) {
            $('.select').select2({
                width: '100%'
            });
        }

        // Calculate Last Working Day based on Resignation Date + Notice Period
        $('#resignation_date').on('change', function() {
            var resignationDate = new Date($(this).val());
            var noticePeriod = {{ $noticePeriodDuration ?? 30 }};
            
            if (!isNaN(resignationDate.getTime())) {
                resignationDate.setDate(resignationDate.getDate() + noticePeriod);
                
                var day = ("0" + resignationDate.getDate()).slice(-2);
                var month = ("0" + (resignationDate.getMonth() + 1)).slice(-2);
                var year = resignationDate.getFullYear();
                
                $('#last_working_day').val(year + "-" + month + "-" + day).trigger('change');
                updateNoticePeriodDiff();
            }
        });

        $('#last_working_day').on('change', function() {
            updateNoticePeriodDiff();
        });

        function updateNoticePeriodDiff() {
            var resDate = new Date($('#resignation_date').val());
            var lwdDate = new Date($('#last_working_day').val());
            
            if (!isNaN(resDate.getTime()) && !isNaN(lwdDate.getTime())) {
                var diffTime = Math.abs(lwdDate - resDate);
                var diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)); 
                $('#notice_period_days').text(diffDays);
            }
        }

        // Toggle Interview Notes
        $('#exit_interview_conducted').on('change', function() {
            if ($(this).is(':checked')) {
                $('#exit_interview_notes_div').slideDown();
            } else {
                $('#exit_interview_notes_div').slideUp();
            }
        });

        // Settlement Mode Change
        $('#settlement_mode').on('change', function() {
            var mode = $(this).val();
            if (mode === 'immediate') {
                $('#settlement_date_div').show();
                $('#settlement_date').prop('required', true);
            } else {
                $('#settlement_date_div').hide();
                $('#settlement_date').prop('required', false);
            }
        });

        // Trigger on load
        $('#settlement_mode').trigger('change');

        // --- FFS Calculation Logic ---

        // Helper to parse float safely
        function parseFloatSafe(val) {
            var f = parseFloat(val);
            return isNaN(f) ? 0 : f;
        }

        // Calculate Final Net Pay based on current inputs (Calculated vs Overrides)
        function calculateFinalNetPay() {
            // formula: 
            // Earnings: Prorated Salary + Stat Credit + Leave + Notice (if +) + Gratuity + Bonus + Other Earnings
            // Deductions: Prorated Deductions + Stat Debit + Advance + Notice (if -) + Other Deductions
            
            var proratedSalary = parseFloatSafe($('#prorated_salary').val());
            var statCredit = parseFloatSafe($('#prorated_statutory_credit').val());
            var grossDeductions = parseFloatSafe($('#prorated_deductions').val()); // Includes stat debit
            var advance = parseFloatSafe($('#pending_advance').val());

            // Notice Pay
            var noticeVal = $('#notice_pay_amount_override').val();
            var noticeAmount = noticeVal !== "" ? parseFloatSafe(noticeVal) : parseFloatSafe($('#notice_pay_amount_calculated').val());

            // Leave Encashment
            var leaveVal = $('#leave_encashment_amount_override').val();
            var leaveAmount = leaveVal !== "" ? parseFloatSafe(leaveVal) : parseFloatSafe($('#leave_encashment_amount_calculated').val());

            // Gratuity
            var gratuityVal = $('#gratuity_amount_override').val();
            var gratuityAmount = gratuityVal !== "" ? parseFloatSafe(gratuityVal) : parseFloatSafe($('#gratuity_amount_calculated').val());

            // Bonus
            var bonusVal = $('#bonus_amount_override').val();
            var bonusAmount = bonusVal !== "" ? parseFloatSafe(bonusVal) : parseFloatSafe($('#bonus_amount_calculated').val());

            // Others
            var otherEarnings = parseFloatSafe($('#other_earnings').val());
            var otherDeductions = parseFloatSafe($('#other_deductions').val());

            // Total Calculation
            var totalEarnings = proratedSalary + statCredit + leaveAmount + gratuityAmount + bonusAmount + otherEarnings;
            var totalDeductions = grossDeductions + advance + otherDeductions;
            
            // Notice can be earning or deduction
            // Backend returns signed value. If we assume frontend input is signed:
            // Actually, backend returns signed. UI inputs usually absolute? 
            // Let's assume input matches sign logic (positive = earning, negative = deduction)
            // Or simpler: Just Add Notice Amount. If it's negative it reduces total.
            
            var netPay = (totalEarnings + noticeAmount) - totalDeductions;
            
            $('#settlement_amount').val(netPay.toFixed(2));
        }

        // Event Listeners for Overrides
        $('.ffs-override').on('input change', function() {
            calculateFinalNetPay();
        });

        // Calculate FFS from Backend
        function calculateFFS() {
            var empId = $('select[name="emp_id"]').val() || $('input[name="emp_id"]').val();
            var lwd = $('#last_working_day').val();

            if (empId && lwd) {
                // Show loading state?
                $('#payroll_status_badge').text('Calculating...').show().removeClass('badge-danger badge-success').addClass('badge-warning');

                $.ajax({
                    url: "{{ route('exit-employees.calculate-ffs') }}",
                    method: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        emp_id: empId,
                        last_working_day: lwd
                    },
                    success: function(response) {
                        if (response.success) {
                            // Populate Read-Only / Calculated Fields
                            
                            // 1. Detailed Breakdown Tables
                            var salaryTableBody = $('#salary_components_table tbody');
                            salaryTableBody.empty();
                            var totalMonthlyGross = 0;
                            var totalProratedSalary = 0;
                            
                            if (response.salary_breakdown && response.salary_breakdown.length > 0) {
                                $.each(response.salary_breakdown, function(index, comp) {
                                    var row = '<tr>' +
                                        '<td>' + (comp.monthly_amount > 0 ? '<span class="badge badge-success">Active</span>' : '-') + '</td>' +
                                        '<td>' + comp.name + ' (' + (comp.code || '') + ')</td>' +
                                        '<td class="text-right">{{ get_currency_symbol() }} ' + parseFloatSafe(comp.monthly_amount).toFixed(2) + '</td>' +
                                        '<td class="text-right">{{ get_currency_symbol() }} ' + parseFloatSafe(comp.prorated_amount).toFixed(2) + '</td>' +
                                        '</tr>';
                                    salaryTableBody.append(row);
                                    totalMonthlyGross += parseFloatSafe(comp.monthly_amount);
                                    totalProratedSalary += parseFloatSafe(comp.prorated_amount);
                                });
                            } else {
                                salaryTableBody.append('<tr><td colspan="4" class="text-center">No salary components found</td></tr>');
                            }
                            $('#total_monthly_gross').text(totalMonthlyGross.toFixed(2));
                            $('#total_prorated_salary').text(totalProratedSalary.toFixed(2));

                            var statutoryTableBody = $('#statutory_components_table tbody');
                            statutoryTableBody.empty();
                            var totalMonthlyStatutory = 0;
                            var totalProratedStatutory = 0;
                            
                            if (response.statutory_breakdown && response.statutory_breakdown.length > 0) {
                                $.each(response.statutory_breakdown, function(index, comp) {
                                    var descriptionHtml = comp.name + ' (' + (comp.code || '') + ')';
                                    if (comp.options && comp.options.length > 0) {
                                        descriptionHtml += '<br><small class="text-info">' + comp.options.join(' | ') + '</small>';
                                    }
                                    
                                    var row = '<tr>' +
                                        '<td>' + (comp.monthly_amount > 0 ? '<span class="badge badge-success">Active</span>' : '-') + '</td>' +
                                        '<td>' + descriptionHtml + '</td>' +
                                        '<td class="text-right">{{ get_currency_symbol() }} ' + parseFloatSafe(comp.monthly_amount).toFixed(2) + '</td>' +
                                        '<td class="text-right">{{ get_currency_symbol() }} ' + parseFloatSafe(comp.prorated_amount).toFixed(2) + '</td>' +
                                        '</tr>';
                                    statutoryTableBody.append(row);
                                    totalMonthlyStatutory += parseFloatSafe(comp.monthly_amount);
                                    totalProratedStatutory += parseFloatSafe(comp.prorated_amount);
                                });
                            } else {
                                statutoryTableBody.append('<tr><td colspan="4" class="text-center">No statutory components found</td></tr>');
                            }
                            $('#total_monthly_statutory').text(totalMonthlyStatutory.toFixed(2));
                            $('#total_prorated_statutory').text(totalProratedStatutory.toFixed(2));
                            
                            // Update Prorated Fields used in final calc
                            $('#prorated_salary').val(response.prorated_salary); 
                            $('#prorated_statutory_credit').val(response.prorated_statutory_credit);
                            
                            // Update Prorated Fields used in final calc
                            $('#prorated_salary').val(response.prorated_salary); 
                            $('#prorated_statutory_credit').val(response.prorated_statutory_credit);
                            
                            // Deductions Logic Correction:
                            // response.prorated_deductions ALREADY includes Statutory Deductions from the backend logic.
                            // So we just use it directly.
                            $('#prorated_deductions').val(response.prorated_deductions);
                            
                            $('#pending_advance').val(response.pending_advance);

                            // 2. Notice Pay
                            $('#notice_pay_amount_calculated').val(response.notice_pay_amount);
                            $('#notice_period_shortfall_days').val(response.notice_shortfall_days);

                            // 3. Leave Encashment
                            $('#leave_encashment_days_calculated').val(response.leave_balance); 
                            $('#leave_encashment_amount_calculated').val(response.leave_encashment_amount);

                            // 4. Gratuity
                            $('#gratuity_tenure_years_calculated').val(response.gratuity_tenure);
                            $('#gratuity_amount_calculated').val(response.gratuity_amount);

                            // 5. Bonus
                            $('#bonus_amount_calculated').val(response.bonus_amount);

                            // Update Status
                            if (response.lwd_month_status === 'Closed') {
                                $('#payroll_status_badge').text('Payroll Closed').removeClass('badge-warning badge-success').addClass('badge-danger').show();
                                alert('Warning: Payroll for the Last Working Day month is already closed.');
                            } else {
                                $('#payroll_status_badge').text('Payroll Open').removeClass('badge-warning badge-danger').addClass('badge-success').show();
                            }
                            
                            // Help Text
                            $('#net_pay_help').text('Based on ' + response.days_considered + ' days worked in LWD month. Monthly Net Pay: ' + response.monthly_net_pay);
                            
                            // Run local final calc to update the big green box
                            calculateFinalNetPay();

                        } else {
                             console.error(response.message);
                        }
                    },
                    error: function(xhr) {
                        console.error('Error calculating FFS');
                    }
                });
            }
        }

        // Trigger Calculation
        $('select[name="emp_id"], #last_working_day').on('change', function() {
            // Only calc if not just loaded with existing data? 
            // Actually, if dates change, we MUST recalc calculated values. 
            // Overrides should probably persist? Yes.
            calculateFFS();
        });

        // Initial Logic
        // If editing (values exist), calculateFinalNetPay to ensure totals are right based on stored values.
        if ($('input[name="emp_id"]').val()) {
             calculateFinalNetPay();
             // Optionally fetch latest calculated values in background if needed?
             // calculateFFS(); 
        }
    });
</script>

@endsection
