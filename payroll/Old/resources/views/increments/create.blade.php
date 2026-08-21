@extends('layouts.master')
@section('title', 'New Increment / Promotion')
@section('content')

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
        color: white;
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
        background-color: #fff;
    }

    .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }
    
    .form-control[readonly] {
        background-color: #f9fafb;
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
        margin-top: 0.5rem;
    }

    /* Select2 Styling */
    .select2-container {
        width: 100% !important;
    }
    
    .select2-container .select2-selection--single {
        border-radius: 0.5rem !important;
        border: 1px solid #d1d5db !important;
        height: 45px !important; /* Fixed height matching manual-notifications */
        padding: 0.375rem 0.75rem !important;
        display: flex !important;
        align-items: center !important;
    }
    
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: normal !important;
        padding-left: 0 !important;
        color: #374151 !important;
    }
    
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 43px !important;
        top: 1px !important;
        right: 1px !important;
    }

    /* Table Styling */
    .table thead th {
        border-bottom: 2px solid #e5e7eb;
        color: #374151;
        font-weight: 600;
        background-color: #f9fafb;
    }
    
    .table td {
        vertical-align: middle;
        color: #4b5563;
    }
    
    /* Result Cards Integration */
    .result-container {
        background: #f9fafb;
        border-radius: 0.75rem;
        border: 1px solid #e5e7eb;
        padding: 1.5rem;
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
                <div class="d-flex align-items-center">
                    <div class="page-header-icon-box">
                        <i class="fas fa-level-up-alt fa-lg" style="color: white; font-size: 1.75rem;"></i>
                    </div>
                    <div class="ms-3" style="margin-left: 1.5rem;">
                        <h1 class="page-header-title">Increment & Promotion</h1>
                        <p class="page-header-subtitle">Manage employee salary revisions and designations</p>
                    </div>
                </div>
            </div>
            <div class="p-3 d-flex justify-content-between align-items-center">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('increments.index') }}">Increments</a></li>
                        <li class="breadcrumb-item active">Create</li>
                    </ol>
                </nav>
                <div>
                     <a href="{{ route('increments.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to List
                    </a>
                </div>
            </div>
        </div>

        <form action="{{ route('increments.store') }}" method="POST" id="incrementForm">
            @csrf
            
            <input type="hidden" name="current_salary_structure" id="input_current_structure">
            <input type="hidden" name="new_salary_structure" id="input_new_structure">

            <!-- Main Content Card -->
            <div class="settings-card">
                <div class="card-header">
                    <h5><i class="fas fa-user-edit"></i> Increment Details</h5>
                </div>
                <div class="card-body">
                    
                    <!-- Basic Selection -->
                    <div class="row mb-4">
                        <div class="col-12 mb-3">
                            <h6 class="section-header">Employee & Type</h6>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <div class="d-flex justify-content-between align-items-center">
                                    <label class="form-label mb-0">Employee <span class="text-danger">*</span></label>
                                    <a href="#" id="view_history_link" class="text-primary small" style="display: none;" data-toggle="modal" data-target="#history_modal">
                                        <i class="fa fa-history me-1"></i> View History
                                    </a>
                                </div>
                                <div class="mb-2"></div> <!-- Spacer to match other labels' bottom margin -->
                                <select class="select2 form-control" name="employee_id" id="employee_id" required>
                                    <option value="">Select Employee</option>
                                    @foreach($employees as $emp)
                                        <option value="{{ $emp->id }}">{{ $emp->name }} ({{ $emp->unique_id }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label">Action Type <span class="text-danger">*</span></label>
                                <select class="select2 form-control" name="type" id="type" required>
                                    <option value="increment">Increment Only</option>
                                    <option value="promotion">Promotion Only</option>
                                    <option value="both">Increment & Promotion</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label">Effective Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="effective_date" value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>
                    </div>

                    <!-- Comparison Section -->
                    <div class="row">
                        <!-- Current Details -->
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <h6 class="section-header text-primary"><i class="fas fa-file-invoice-dollar me-2"></i>Current Structure</h6>
                            </div>
                            
                            <div class="result-container mb-4">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <div class="form-group mb-0">
                                            <label class="form-label text-muted small">Designation</label>
                                            <input type="text" class="form-control bg-white" id="current_designation" readonly style="font-weight: 500;">
                                            <input type="hidden" name="previous_designation_id" id="previous_designation_id">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-0">
                                            <label class="form-label text-muted small">Current Annual CTC</label>
                                            <input type="text" class="form-control bg-white" id="current_ctc_display" readonly style="font-weight: 700;">
                                            <input type="hidden" id="current_ctc_val" name="previous_ctc">
                                        </div>
                                    </div>
                                </div>

                                <div class="table-responsive bg-white rounded border">
                                    <table class="table table-sm table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th class="ps-3">Component</th>
                                                <th class="text-end pe-3">Monthly Value</th>
                                            </tr>
                                        </thead>
                                        <tbody id="current_structure_body">
                                            <tr><td colspan="2" class="text-center text-muted py-4">Select an employee to view details</td></tr>
                                        </tbody>
                                        <tfoot class="bg-light">
                                            <tr>
                                                <th class="ps-3">Net Monthly Pay</th>
                                                <th class="text-end pe-3" id="current_net_pay">{{ get_currency_symbol() }} 0.00</th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Revised Details -->
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <h6 class="section-header text-success"><i class="fas fa-chart-line me-2"></i>Revised Structure</h6>
                            </div>
                            
                            <!-- Controls Container -->
                            <div class="p-3 mb-4 border rounded" style="background-color: #f0fdf4; border-color: #bbf7d0 !important;">
                                <!-- Promotion Field -->
                                <div class="form-group mb-3" id="new_designation_group" style="display:none;">
                                    <label class="form-label">New Designation <span class="text-danger">*</span></label>
                                    <select class="select2 form-control" name="new_designation_id" id="new_designation_id">
                                        <option value="">Select Designation</option>
                                        @foreach($designations as $des)
                                            <option value="{{ $des->id }}">{{ $des->position }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Increment Inputs -->
                                <label class="form-label">Calculate Increment</label>
                                <div class="d-flex align-items-center mb-3 flex-wrap gap-3">
                                    <div class="form-check me-3">
                                        <input class="form-check-input increment-mode" type="radio" name="increment_mode" id="mode_percentage" value="percentage" checked>
                                        <label class="form-check-label" for="mode_percentage">Percentage (%)</label>
                                    </div>
                                    <div class="form-check me-3">
                                        <input class="form-check-input increment-mode" type="radio" name="increment_mode" id="mode_flat" value="flat">
                                        <label class="form-check-label" for="mode_flat">Flat Amount</label>
                                    </div>
                                    
                                    <div id="percentage_options" class="d-flex ms-2 border-start ps-3">
                                        <div class="form-check me-3">
                                            <input class="form-check-input percentage-basis" type="radio" name="percentage_basis" id="basis_ctc" value="ctc" checked>
                                            <label class="form-check-label" for="basis_ctc">On CTC</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input percentage-basis" type="radio" name="percentage_basis" id="basis_basic" value="basic">
                                            <label class="form-check-label" for="basis_basic">On Basic</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="input-group mb-2">
                                    <input type="number" step="0.01" class="form-control" id="increment_value" placeholder="Enter value">
                                    <button class="btn btn-primary" type="button" id="btn_apply_increment">
                                        <i class="fas fa-check me-1"></i> Apply
                                    </button>
                                </div>
                                <small class="text-muted" id="increment_hint">Enter percentage value (e.g. 10 for 10%)</small>
                            </div>

                            <div class="result-container" style="background: #fff; border-color: #22c55e;">
                                <div class="form-group mb-3">
                                    <label class="form-label text-success">New Annual CTC</label>
                                    <input type="number" step="0.01" class="form-control form-control-lg border-success" name="new_ctc" id="new_ctc" placeholder="Enter or calculate new CTC" required style="font-weight: 700; color: #15803d;">
                                </div>

                                <div class="table-responsive bg-white rounded border border-success">
                                    <table class="table table-sm table-hover mb-0">
                                        <thead class="bg-success text-white">
                                            <tr>
                                                <th class="ps-3 text-white">Component</th>
                                                <th class="text-end pe-3 text-white">New Monthly Value</th>
                                            </tr>
                                        </thead>
                                        <tbody id="new_structure_body">
                                            <tr><td colspan="2" class="text-center text-muted py-4">Structure will update automatically</td></tr>
                                        </tbody>
                                        <tfoot class="bg-light">
                                            <tr>
                                                <th class="ps-3 text-success">Net Monthly Pay</th>
                                                <th class="text-end pe-3 text-success fw-bold" id="new_net_pay">{{ get_currency_symbol() }} 0.00</th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Inline History -->
                    <div class="row mt-4" id="inline_history_row" style="display: none;">
                        <div class="col-12">
                            <h6 class="section-header text-muted"><i class="fa fa-history me-2"></i>Recent History</h6>
                            <div class="border rounded p-0 overflow-hidden" id="inline_history_container">
                                <!-- History loads here -->
                            </div>
                        </div>
                    </div>

                    <div class="row mt-5">
                        <div class="col-12 text-end">
                            <button type="submit" class="btn btn-primary btn-lg px-5">
                                <i class="fas fa-paper-plane me-2"></i> Submit
                            </button>
                            <a href="{{ route('increments.index') }}" class="btn btn-outline-secondary btn-lg px-4 ms-2">
                                Cancel
                            </a>
                        </div>
                    </div>

                </div>
            </div>
        </form>
    </div>
</div>

<!-- History Modal -->
<div class="modal custom-modal fade" id="history_modal" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white">
                    <i class="fa fa-history me-2"></i>
                    Increment History
                </h5>
                <button type="button" class="btn-close btn-close-white" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="history_modal_body">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- History Modal -->
<div class="modal custom-modal fade" id="history_modal" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white">
                    <i class="fa fa-history me-2"></i>
                    Increment History
                </h5>
                <button type="button" class="btn-close btn-close-white" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="history_modal_body">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
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
        // Initialize Select2 for all select2 elements
        $('.select2').select2({
            placeholder: function() {
                return $(this).data('placeholder') || 'Select an option';
            },
            allowClear: true
        });

        let currentEmployeeData = null;
        let salaryComponentsMaster = []; // Will store the rules from the employee data
        let statutoryComponentsMaster = []; // Will store statutory component rules

        // -- 1. Fetch Employee Data --
        $('#employee_id').change(function() {
            const id = $(this).val();
            if(!id) {
                $('#view_history_link').hide();
                resetForm(); 
                return;
            }

            // Show history link
            $('#view_history_link').show();

            $.ajax({
                url: '/increments/get-employee/' + id,
                type: 'GET',
                beforeSend: function() {
                    $('#current_structure_body').html('<tr><td colspan="2" class="text-center">Loading...</td></tr>');
                },
                success: function(res) {
                    if(res.success) {
                        currentEmployeeData = res.data;
                        populateCurrentDetails(res.data);
                        // Auto-populate new CTC with current for starters
                        $('#new_ctc').val(res.data.annual_ctc);
                        recalculateNewStructure(res.data.annual_ctc); 
                        
                        // Load Inline History
                        loadInlineHistory(id);
                    }
                },
                error: function() {
                    alert('Error fetching employee details');
                }
            });
        });
        
        function loadInlineHistory(employeeId) {
            $('#inline_history_row').show();
            $('#inline_history_container').html('<div class="text-center py-4"><div class="spinner-border text-secondary" role="status"><span class="sr-only">Loading...</span></div></div>');
            
            $.ajax({
                url: '{{ url("increments/history") }}/' + employeeId,
                type: 'GET',
                success: function(response) {
                    $('#inline_history_container').html(response);
                },
                error: function() {
                    $('#inline_history_container').html('<div class="alert alert-warning text-center">Failed to load history data.</div>');
                }
            });
        }
        
        // Handle history modal opening (Optional keep if user wants detailed view or just remove link if redundant)
        // User asked "Under this", so inline is preferred. We can keep the link or remove it.
        // Let's keep the link functional as a fallback or quick view, but the inline one is primary now.
        $('#history_modal').on('show.bs.modal', function (e) {
            var employeeId = $('#employee_id').val();
            if(employeeId) {
                // Reuse logic or dedicated load
                // loadHistory(employeeId); // Function was removed/renamed? No.
            }
        });
        
        /* 
        function loadHistory(employeeId) {
             // ...
        }
        */
        
        // Handle history modal opening
        $('#history_modal').on('show.bs.modal', function (e) {
            var employeeId = $('#employee_id').val();
            if(employeeId) {
                loadHistory(employeeId);
            }
        });
        
        function loadHistory(employeeId) {
            $('#history_modal_body').html('<div class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div></div>');
            
            $.ajax({
                url: '{{ url("increments/history") }}/' + employeeId,
                type: 'GET',
                success: function(response) {
                    $('#history_modal_body').html(response);
                },
                error: function() {
                    $('#history_modal_body').html('<div class="alert alert-danger">Failed to load history data.</div>');
                }
            });
        }

        // -- 2. UI Controls --
        $('#type').change(function() {
            const val = $(this).val();
            if(val === 'promotion' || val === 'both') {
                $('#new_designation_group').show();
                $('#new_designation_id').prop('required', true);
                // Re-initialize Select2 for the new designation dropdown
                $('#new_designation_id').select2({
                    placeholder: 'Select Designation',
                    allowClear: true
                });
            } else {
                $('#new_designation_group').hide();
                $('#new_designation_id').prop('required', false);
            }
        });

        $('.increment-mode').change(function() {
            if($(this).val() === 'percentage') {
                $('#percentage_options').show();
                $('#increment_hint').text('Enter percentage value (e.g. 10 for 10%)');
            } else {
                $('#percentage_options').hide();
                $('#increment_hint').text('Enter flat amount to add to Annual CTC');
            }
        });

        // -- 3. Apply Increment Logic --
        $('#btn_apply_increment').click(function() {
            if(!currentEmployeeData) {
                alert('Please select an employee first.');
                return;
            }

            const currentCTC = parseFloat(currentEmployeeData.annual_ctc) || 0;
            const mode = $('input[name="increment_mode"]:checked').val();
            const val = parseFloat($('#increment_value').val()) || 0;

            if(val <= 0) {
                alert('Please enter a valid positive value.');
                return;
            }

            let newCTC = currentCTC;

            if(mode === 'flat') {
                newCTC = currentCTC + val;
            } else {
                // Percentage
                const basis = $('input[name="percentage_basis"]:checked').val();
                if(basis === 'ctc') {
                    newCTC = currentCTC + (currentCTC * val / 100);
                } else {
                    // Percentage on Basic
                    // We need to find current Basic Annual
                    const basicComp = currentEmployeeData.salary_components.find(c => {
                         // Check raw component or relation
                         const sName = c.salary_component ? c.salary_component.short_name.toLowerCase() : '';
                         return sName === 'ba' || sName === 'basic' || sName.includes('basic');
                    });
                    
                    if(basicComp) {
                        const currentBasicAnnual = parseFloat(basicComp.value) * 12; // stored value is monthly usually
                        const increase = currentBasicAnnual * val / 100;
                        newCTC = currentCTC + increase;
                    } else {
                        alert('Could not identify Basic salary component to calculate on.');
                        return;
                    }
                }
            }

            $('#new_ctc').val(newCTC.toFixed(2));
            recalculateNewStructure(newCTC);
        });
        
        // Manual Change of New CTC
        $('#new_ctc').on('change', function() {
            const val = parseFloat($(this).val()) || 0;
            if(val > 0) recalculateNewStructure(val);
        });

        function populateCurrentDetails(data) {
            // Use designation_obj as Laravel serializes it with snake_case
            $('#current_designation').val(data.designation_obj ? data.designation_obj.position : '-');
            $('#previous_designation_id').val(data.designation);
            $('#current_ctc_display').val('{{ get_currency_symbol() }} ' + parseFloat(data.annual_ctc).toFixed(2));
            $('#current_ctc_val').val(data.annual_ctc);

            let rows = '';
            salaryComponentsMaster = [];
            statutoryComponentsMaster = [];

            // Process Salary Components (Earnings)
            let earningsRows = '';
            if(data.salary_components && data.salary_components.length > 0) {
                data.salary_components.forEach(sc => {
                    const compMaster = sc.salary_component || {};
                    const name = compMaster.name || 'Unknown';
                    const val = parseFloat(sc.value).toFixed(2);
                    
                    // Store for calculation logic
                    salaryComponentsMaster.push({
                        id: sc.salary_component_id,
                        name: name,
                        short_name: compMaster.short_name,
                        type: compMaster.type, // earning/deduction
                        calc_type: compMaster.calculation_type, // percentage_ctc, etc.
                        calc_value: parseFloat(compMaster.calculation_value) || 0,
                        is_residual: compMaster.is_residual == 1
                    });
                    
                    // Only show Earnings
                    if(compMaster.type === 'earning') {
                         earningsRows += `<tr><td>${name}</td><td class="text-right">{{ get_currency_symbol() }} ${val}</td></tr>`;
                    }
                });
            }

            // Process Statutory Components
            let statutoryRows = '';
            if(data.statutory_components && data.statutory_components.length > 0) {
                data.statutory_components.forEach(sc => {
                    const compMaster = sc.statutory_component || {};
                    const name = compMaster.name || 'Unknown';
                    const val = parseFloat(sc.value).toFixed(2);
                    
                    // Store for calculation logic
                    statutoryComponentsMaster.push({
                        id: sc.statutory_component_id,
                        name: name,
                        short_name: compMaster.short_name,
                        type: compMaster.type,
                        calc_type: compMaster.calculation_type,
                        calc_value: parseFloat(compMaster.calculation_value) || 0,
                        current_value: parseFloat(sc.value) || 0, // Store employee's current value
                        epf_option: sc.epf_option || 'restrict_15000',
                        full_amount_deduct_from_ctc: sc.full_amount_deduct_from_ctc || 0
                    });
                    
                    statutoryRows += `<tr><td>${name}</td><td class="text-right">{{ get_currency_symbol() }} ${val}</td></tr>`;
                });
            }

            // Build combined table with sections
            if(earningsRows) {
                rows += '<tr class="table-primary"><th colspan="2">Earnings</th></tr>' + earningsRows;
            }
            if(statutoryRows) {
                rows += '<tr class="table-warning"><th colspan="2">Statutory Components</th></tr>' + statutoryRows;
            }
            if(!earningsRows && !statutoryRows) {
                rows = '<tr><td colspan="2" class="text-center text-muted">No components found</td></tr>';
            }

            $('#current_structure_body').html(rows);
            
            // Calculate and display net monthly pay
            let totalEarnings = 0;
            let totalDeductions = 0;
            
            data.salary_components.forEach(sc => {
                const compMaster = sc.salary_component || {};
                if(compMaster.type === 'earning') {
                    totalEarnings += parseFloat(sc.value) || 0;
                }
            });
            
            data.statutory_components.forEach(sc => {
                totalDeductions += parseFloat(sc.value) || 0;
            });
            
            const netPay = totalEarnings - totalDeductions;
            $('#current_net_pay').text('{{ get_currency_symbol() }} ' + netPay.toFixed(2));
            
            // Save current structure JSON
            $('#input_current_structure').val(JSON.stringify({
                salary: data.salary_components,
                statutory: data.statutory_components
            }));
        }

        function recalculateNewStructure(newAnnualCTC) {
            if(!newAnnualCTC || salaryComponentsMaster.length === 0) return;

            const monthlyCTC = newAnnualCTC / 12;
            let newStructure = [];
            let fixedEarnings = 0;
            let basicValue = 0;
            let grossEarnings = 0;
            let residualComp = null;

            // --- PASS 1: Fixed Earnings ---
            salaryComponentsMaster.forEach(comp => {
                let monthlyVal = 0;
                let isCalculated = false;

                if(comp.is_residual) {
                    residualComp = comp;
                    return; // Skip for now
                }

                if(comp.type === 'earning') {
                    if(comp.calc_type === 'flat_amount') {
                        monthlyVal = comp.calc_value;
                        isCalculated = true;
                    } else if(comp.calc_type === 'percentage_ctc') {
                        monthlyVal = (monthlyCTC * comp.calc_value) / 100;
                        isCalculated = true;
                    }
                    
                    // Store for next pass
                    comp._tempVal = monthlyVal;
                    if(isCalculated) fixedEarnings += monthlyVal;
                    
                    // Identify Basic
                    const s = (comp.short_name || '').toLowerCase();
                    if(s === 'ba' || s === 'basic' || s.includes('basic')) {
                        basicValue = monthlyVal;
                    }
                }
            });

            // --- PASS 2: Dependent Earnings (on Basic) ---
            salaryComponentsMaster.forEach(comp => {
                if(!comp.is_residual && comp.type === 'earning' && comp.calc_type === 'percentage_basic') {
                    const monthlyVal = (basicValue * comp.calc_value) / 100;
                    comp._tempVal = monthlyVal;
                    fixedEarnings += monthlyVal;
                }
            });

            // --- PASS 3: Residual ---
            if(residualComp) {
                // Simple Residual: CTC - Fixed Earnings
                let resVal = Math.max(0, monthlyCTC - fixedEarnings);
                residualComp._tempVal = resVal;
            }

            // Calculate total gross earnings for statutory calculations
            salaryComponentsMaster.forEach(comp => {
                if(comp.type === 'earning') {
                    grossEarnings += comp._tempVal || 0;
                }
            });

            // --- PASS 4: Calculate Statutory Components ---
            statutoryComponentsMaster.forEach(comp => {
                let monthlyVal = 0;
                
                // Check calculation type
                if(comp.calc_type === 'percentage_basic') {
                    monthlyVal = (basicValue * comp.calc_value) / 100;
                } else if(comp.calc_type === 'percentage_gross') {
                    monthlyVal = (grossEarnings * comp.calc_value) / 100;
                } else if(comp.calc_type === 'flat_amount') {
                    // For flat_amount, use employee's current value since master calc_value is often 0
                    monthlyVal = comp.current_value || comp.calc_value || 0;
                }
                
                // Special handling for EPF (assuming component ID 1 or short_name contains 'epf')
                const isEPF = (comp.short_name || '').toLowerCase().includes('epf');
                if(isEPF && comp.epf_option) {
                    if(comp.epf_option === 'restrict_15000') {
                        // EPF on minimum of (Basic + DA + OA, 15000)
                        const epfBase = Math.min(basicValue, 15000);
                        monthlyVal = (epfBase * 12) / 100; // 12% employee contribution
                        if(comp.full_amount_deduct_from_ctc) {
                            monthlyVal = (epfBase * 24) / 100; // Both employee + employer
                        }
                    } else if(comp.epf_option === '12_percent') {
                        monthlyVal = (basicValue * 12) / 100;
                        if(comp.full_amount_deduct_from_ctc) {
                            monthlyVal = (basicValue * 24) / 100;
                        }
                    }
                    // For manual_value, use the stored value (already in monthlyVal from calc_type)
                }
                
                comp._tempVal = monthlyVal;
            });

            // --- Render New Structure ---
            let rows = '';
            let newSalaryStructure = [];
            let newStatutoryStructure = [];

            // Earnings Section
            let earningsRows = '';
            salaryComponentsMaster.forEach(comp => {
                if(comp.type === 'earning') {
                    const val = comp._tempVal || 0;
                    earningsRows += `<tr><td>${comp.name}</td><td class="text-right fw-bold">{{ get_currency_symbol() }} ${val.toFixed(2)}</td></tr>`;
                    
                    newSalaryStructure.push({
                        salary_component_id: comp.id,
                        value: val.toFixed(2) // Monthly value
                    });
                }
            });

            // Statutory Section
            let statutoryRows = '';
            statutoryComponentsMaster.forEach(comp => {
                const val = comp._tempVal || 0;
                statutoryRows += `<tr><td>${comp.name}</td><td class="text-right fw-bold">{{ get_currency_symbol() }} ${val.toFixed(2)}</td></tr>`;
                
                newStatutoryStructure.push({
                    statutory_component_id: comp.id,
                    value: val.toFixed(2),
                    epf_option: comp.epf_option,
                    full_amount_deduct_from_ctc: comp.full_amount_deduct_from_ctc
                });
            });

            // Build combined table
            if(earningsRows) {
                rows += '<tr class="table-primary"><th colspan="2">Earnings</th></tr>' + earningsRows;
            }
            if(statutoryRows) {
                rows += '<tr class="table-warning"><th colspan="2">Statutory Components</th></tr>' + statutoryRows;
            }

            $('#new_structure_body').html(rows);
            
            // Calculate and display net monthly pay
            let totalEarnings = 0;
            let totalDeductions = 0;
            
            salaryComponentsMaster.forEach(comp => {
                if(comp.type === 'earning') {
                    totalEarnings += comp._tempVal || 0;
                }
            });
            
            statutoryComponentsMaster.forEach(comp => {
                totalDeductions += comp._tempVal || 0;
            });
            
            const netPay = totalEarnings - totalDeductions;
            $('#new_net_pay').text('{{ get_currency_symbol() }} ' + netPay.toFixed(2));
            
            $('#input_new_structure').val(JSON.stringify({
                salary: newSalaryStructure,
                statutory: newStatutoryStructure
            }));
        }

        function resetForm() {
            $('#current_designation').val('');
            $('#current_ctc_display').val('');
            $('#current_structure_body').html('<tr><td colspan="2" class="text-center text-muted">Select an employee to view details</td></tr>');
            $('#new_ctc').val('');
            $('#new_structure_body').html('<tr><td colspan="2" class="text-center text-muted">Structure will update automatically</td></tr>');
            $('#inline_history_row').hide();
            $('#inline_history_container').empty();
            currentEmployeeData = null;
        }
    });
</script>
@endsection
