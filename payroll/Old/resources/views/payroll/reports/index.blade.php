@extends('layouts.master')

@section('title', 'Payroll Reports')

@section('content')

<style>
    /* Page Header Card */
    .page-header-card { background: white; border-radius: 1rem; box-shadow: 0 10px 25px rgba(0,0,0,0.1); overflow: hidden; margin-bottom: 2rem; }
    .page-header-gradient { background: linear-gradient(135deg,#667eea 0%,#764ba2 100%); padding: 2.5rem 2rem; position: relative; }
    .page-header-pattern { position: absolute; inset: 0; background: rgba(0,0,0,0.05); }
    .page-header-circle-1 { position: absolute; top: -1rem; right: -1rem; width:6rem; height:6rem; background: rgba(255,255,255,0.1); border-radius:50%; }
    .page-header-circle-2 { position:absolute; bottom:-1rem; left:-1rem; width:8rem; height:8rem; background: rgba(255,255,255,0.1); border-radius:50%; }
    .page-header-icon-box { width:4rem; height:4rem; background: rgba(255,255,255,0.2); border:1px solid rgba(255,255,255,0.3); border-radius:1rem; display:flex; align-items:center; justify-content:center; }
    .page-header-title { font-size:1.875rem; font-weight:700; color:white; margin-bottom:0.5rem; }
    .page-header-subtitle { font-size:1rem; color: rgba(255,255,255,0.9); margin:0; }

    /* Modern Card Styles */
    .modern-card { background: white; border-radius: 1rem; box-shadow: 0 4px 6px rgba(0,0,0,0.07); border: 1px solid #e5e7eb; overflow: hidden; }
    .modern-card-header { background: linear-gradient(135deg,#667eea 0%,#764ba2 100%); padding: 1.25rem 1.5rem; border-bottom: none; }
    .modern-card-header h4 { color: white; font-weight: 600; margin: 0; font-size: 1.125rem; }
    .modern-card-body { padding: 1.5rem; }

    /* Button Styles */
    .btn-modern { padding: 0.75rem 2rem; border-radius: 0.5rem; font-weight: 500; font-size: 1rem; border: none; cursor: pointer; transition: all 0.2s; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; }
    .btn-modern-primary { background: linear-gradient(135deg,#667eea 0%,#764ba2 100%); color: white; }
    .btn-modern-primary:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4); }
    .btn-modern-secondary { background: #f8f9fa; color: #6b7280; border: 1px solid #e5e7eb; }
    .btn-modern-secondary:hover { background: #e9ecef; }

    /* Form Elements */
    .modern-input { width: 100%; padding: 0.5rem 0.75rem; border: 2px solid #e5e7eb; border-radius: 0.5rem; font-size: 0.875rem; transition: all 0.2s; }
    .modern-input:focus { outline: none; border-color: #667eea; box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1); }

    /* Summary Cards */
    .summary-card { background: white; border-radius: 1rem; box-shadow: 0 4px 6px rgba(0,0,0,0.07); border: 1px solid #e5e7eb; padding: 1.5rem; margin-bottom: 1rem; }
    .summary-card-icon { width: 3rem; height: 3rem; border-radius: 0.75rem; display: flex; align-items: center; justify-content: center; margin-bottom: 1rem; }
    .summary-card-title { font-size: 0.875rem; font-weight: 500; color: #6b7280; margin-bottom: 0.5rem; text-transform: uppercase; letter-spacing: 0.5px; }
    .summary-card-value { font-size: 1.5rem; font-weight: 700; color: #1f2937; margin: 0; }

    /* Form Group Styling */
    .form-group-modern { margin-bottom: 1.5rem; }
    .form-label-modern { font-weight: 600; color: #374151; margin-bottom: 0.5rem; display: block; }

    /* Select2 Customization */
    .select2-container--default .select2-selection--multiple { border-radius: 0.5rem; border: 2px solid #e5e7eb; min-height: 38px; }
    .select2-container--default.select2-container--focus .select2-selection--multiple { border-color: #667eea; box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1); }
    .select2-container--default .select2-selection--multiple .select2-selection__choice { background: linear-gradient(135deg,#667eea 0%,#764ba2 100%); color: white; border-radius: 0.25rem; }
    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove { color: white; }

    /* Radio Button Styling */
    .radio-modern { margin-right: 1.5rem; }
    .radio-modern input[type="radio"] { display: none; }
    .radio-modern label { position: relative; padding-left: 2rem; cursor: pointer; font-weight: 500; color: #374151; }
    .radio-modern label:before { content: ''; position: absolute; left: 0; top: 0.125rem; width: 1.25rem; height: 1.25rem; border: 2px solid #d1d5db; border-radius: 50%; background: white; transition: all 0.2s; }
    .radio-modern input[type="radio"]:checked + label:before { border-color: #667eea; background: #667eea; }
    .radio-modern input[type="radio"]:checked + label:before::after { content: ''; position: absolute; top: 0.25rem; left: 0.25rem; width: 0.5rem; height: 0.5rem; background: white; border-radius: 50%; }
</style>

<div class="page-wrapper">
    <div class="content container-fluid">

        <!-- Page Header -->
        <div class="page-header-card mb-4">
            <div class="page-header-gradient">
                <div class="page-header-pattern"></div>
                <div class="page-header-circle-1"></div>
                <div class="page-header-circle-2"></div>
                <div class="position-relative">
                    <div class="row align-items-center">
                        <div class="col-lg-8">
                            <div class="d-flex align-items-center">
                                <div class="page-header-icon-box me-4">
                                    <i class="fas fa-chart-bar text-white" style="font-size:1.5rem;"></i>
                                </div>
                                <div>
                                    <h1 class="page-header-title">Payroll Reports</h1>
                                    <p class="page-header-subtitle">Generate comprehensive payroll reports with advanced filtering options</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="summary-card">
                    <div class="summary-card-icon" style="background: linear-gradient(135deg, #667eea, #764ba2);">
                        <i class="fa fa-calendar-check fa-lg text-white"></i>
                    </div>
                    <h6 class="summary-card-title">Available Months</h6>
                    <h4 class="summary-card-value">{{ $completedMonths->count() }}</h4>
                </div>
            </div>
            <div class="col-md-3">
                <div class="summary-card">
                    <div class="summary-card-icon" style="background: linear-gradient(135deg, #10b981, #059669);">
                        <i class="fa fa-users fa-lg text-white"></i>
                    </div>
                    <h6 class="summary-card-title">Total Employees</h6>
                    <h4 class="summary-card-value">{{ $employees->count() }}</h4>
                </div>
            </div>
            <div class="col-md-3">
                <div class="summary-card">
                    <div class="summary-card-icon" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
                        <i class="fa fa-chart-line fa-lg text-white"></i>
                    </div>
                    <h6 class="summary-card-title">Report Types</h6>
                    <h4 class="summary-card-value">2</h4>
                </div>
            </div>
            <div class="col-md-3">
                <div class="summary-card">
                    <div class="summary-card-icon" style="background: linear-gradient(135deg, #3b82f6, #1d4ed8);">
                        <i class="fa fa-file-export fa-lg text-white"></i>
                    </div>
                    <h6 class="summary-card-title">Export Formats</h6>
                    <h4 class="summary-card-value">Multiple</h4>
                </div>
            </div>
        </div>

        <!-- Report Generation Form -->
        <div class="modern-card">
            <div class="modern-card-header">
                <h4><i class="fas fa-file-invoice-dollar me-2"></i> Generate Payroll Report</h4>
            </div>
            <div class="modern-card-body">
                <form action="{{ route('payroll.reports.generate') }}" method="GET" id="reportForm">

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group-modern">
                                <label class="form-label-modern">
                                    <i class="fa fa-calendar-alt me-2 text-primary"></i>Select Month(s)
                                </label>
                                <select class="form-control form-select select2 select-multiple" name="months[]" id="months" multiple="multiple" data-placeholder="Select Month(s)" required>
                                    @foreach($completedMonths as $month)
                                        <option value="{{ $month['value'] }}">{{ $month['label'] }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted mt-1 d-block">Choose one or more months to include in the report</small>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group-modern">
                                <label class="form-label-modern">
                                    <i class="fa fa-users me-2 text-success"></i>Employee Filter (Optional)
                                </label>
                                <select class="form-control form-select select2 select-multiple"
                                        name="employees[]"
                                        id="employees"
                                        multiple="multiple"
                                        data-placeholder="Select Employee(s)">
                                    @foreach($employees as $employee)
                                        <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted mt-1 d-block">Leave empty to include all employees</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group-modern">
                                <label class="form-label-modern">
                                    <i class="fa fa-eye me-2 text-info"></i>Report View Type
                                </label>
                                <div class="d-flex align-items-center">
                                    <div class="radio-modern">
                                        <input class="form-check-input" type="radio" name="view_type"
                                               id="view_type_consolidated" value="consolidated" checked>
                                        <label class="form-check-label" for="view_type_consolidated">
                                            Consolidated View (All Months Combined)
                                        </label>
                                    </div>
                                </div>
                                <small class="text-muted mt-1 d-block">View combines data from all selected months into a single report</small>
                            </div>
                        </div>
                    </div>

                    <div class="text-end mt-4">
                        <button type="button" class="btn-modern btn-modern-secondary me-3" id="resetForm">
                            <i class="fas fa-redo me-2"></i> Reset Form
                        </button>
                        <button type="submit" class="btn-modern btn-modern-primary">
                            <i class="fas fa-chart-bar me-2"></i> Generate Report
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    .select2-container--default .select2-selection--multiple .select2-selection__placeholder {
        color: #6c757d;
        width: 100%;
    }
</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    $(document).ready(function() {
        $('.select-multiple').select2({
            placeholder: function() {
                return $(this).data('placeholder');
            },
            allowClear: true
        });

        // Reset button functionality
        $("#resetForm").click(function() {
            $("#reportForm")[0].reset();
            $('.select-multiple').val(null).trigger('change');
        });
    });
</script>

@endsection




   

