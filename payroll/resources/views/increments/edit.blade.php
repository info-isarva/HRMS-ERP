@extends('layouts.master')
@section('title', 'Edit Increment / Promotion')
@section('content')

<div class="page-wrapper">
    <div class="content container-fluid">
    
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Edit Increment / Promotion</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('increments.index') }}">Increments</a></li>
                        <li class="breadcrumb-item active">Edit</li>
                    </ul>
                </div>
            </div>
        </div>

        <form action="{{ route('increments.update', $increment->id) }}" method="POST" id="incrementForm">
            @csrf
            @method('PUT')
            
            <!-- Hidden inputs for calculated structure -->
            <input type="hidden" name="current_salary_structure" id="input_current_structure">
            <input type="hidden" name="new_salary_structure" id="input_new_structure">

            <!-- Top Controls -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <label class="mb-0">Employee <span class="text-danger">*</span></label>
                                            <a href="#" id="view_history_link" class="text-primary" style="font-size: 0.85rem;" data-toggle="modal" data-target="#history_modal">
                                                <i class="fa fa-history me-1"></i> View History
                                            </a>
                                        </div>
                                        <select class="select2 form-control" name="employee_id" id="employee_id" style="width: 100%;" required>
                                            <option value="">Select Employee</option>
                                            @foreach($employees as $emp)
                                                <option value="{{ $emp->id }}" {{ $increment->employee_id == $emp->id ? 'selected' : '' }}>{{ $emp->name }} ({{ $emp->unique_id }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Type <span class="text-danger">*</span></label>
                                        <select class="select2 form-control" name="type" id="type" required>
                                            <option value="increment" {{ $increment->type == 'increment' ? 'selected' : '' }}>Increment Only</option>
                                            <option value="promotion" {{ $increment->type == 'promotion' ? 'selected' : '' }}>Promotion Only</option>
                                            <option value="both" {{ $increment->type == 'both' ? 'selected' : '' }}>Increment & Promotion</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Effective Date <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" name="effective_date" value="{{ $increment->effective_date->format('Y-m-d') }}" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Comparison Section -->
            <div class="row">
                <!-- Current Details -->
                <div class="col-md-6">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-light border-bottom">
                            <h5 class="card-title mb-0"><i class="fas fa-file-invoice-dollar me-2 text-primary"></i>Current Salary Structure</h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3 g-3">
                                <div class="col-md-6">
                                    <label class="form-label text-muted fw-semibold">Designation</label>
                                    <input type="text" class="form-control bg-white" id="current_designation" readonly>
                                    <input type="hidden" name="previous_designation_id" id="previous_designation_id">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted fw-semibold">Current Annual CTC</label>
                                    <input type="text" class="form-control fw-bold bg-white" id="current_ctc_display" readonly>
                                    <input type="hidden" id="current_ctc_val" name="previous_ctc">
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-sm table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Component</th>
                                            <th class="text-end">Monthly Value</th>
                                        </tr>
                                    </thead>
                                    <tbody id="current_structure_body">
                                        <tr><td colspan="2" class="text-center text-muted py-3">Select an employee to view details</td></tr>
                                    </tbody>
                                    <tfoot class="table-success">
                                        <tr>
                                            <th>Net Monthly Pay</th>
                                            <th class="text-end" id="current_net_pay">₹ 0.00</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Revised Details -->
                <div class="col-md-6">
                    <div class="card shadow border-success">
                        <div class="card-header bg-success text-white border-0">
                            <h5 class="card-title mb-0 text-white"><i class="fas fa-chart-line me-2"></i>Revised Salary Structure</h5>
                        </div>
                        <div class="card-body">
                            
                            <!-- Promotion Field -->
                            <div class="form-group mb-3" id="new_designation_group" style="display:none;">
                                <label class="form-label fw-semibold">New Designation <span class="text-danger">*</span></label>
                                <select class="select2 form-control" name="new_designation_id" id="new_designation_id">
                                    <option value="">Select Designation</option>
                                    @foreach($designations as $des)
                                        <option value="{{ $des->id }}">{{ $des->position }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Increment Controls -->
                            <div class="card mb-3 border-0 shadow-sm">
                                <div class="card-body p-3" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 8px;">
                                    <label class="fw-bold mb-2 text-white"><i class="fas fa-calculator me-2"></i>Increment Mode</label>
                                    
                                    <div class="d-flex mb-2">
                                        <div class="form-check me-3">
                                            <input class="form-check-input increment-mode" type="radio" name="increment_mode" id="mode_percentage" value="percentage" checked>
                                            <label class="form-check-label text-white" for="mode_percentage">Percentage (%)</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input increment-mode" type="radio" name="increment_mode" id="mode_flat" value="flat">
                                            <label class="form-check-label text-white" for="mode_flat">Flat Amount</label>
                                        </div>
                                    </div>

                                    <div id="percentage_options" class="mb-2">
                                        <div class="d-flex">
                                            <div class="form-check me-3">
                                                <input class="form-check-input percentage-basis" type="radio" name="percentage_basis" id="basis_ctc" value="ctc" checked>
                                                <label class="form-check-label text-white" for="basis_ctc">On CTC</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input percentage-basis" type="radio" name="percentage_basis" id="basis_basic" value="basic">
                                                <label class="form-check-label text-white" for="basis_basic">On Basic</label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="input-group">
                                        <input type="number" step="0.01" class="form-control" id="increment_value" placeholder="Enter value">
                                        <button class="btn btn-light fw-bold" type="button" id="btn_apply_increment"><i class="fas fa-check me-1"></i>Apply</button>
                                    </div>
                                    <small class="text-white-50 d-block mt-1" id="increment_hint">Enter percentage value (e.g. 10 for 10%)</small>
                                </div>
                            </div>

                            <!-- New CTC Display -->
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold text-success"><i class="fas fa-rupee-sign me-1"></i>New Annual CTC</label>
                                <input type="number" step="0.01" class="form-control form-control-lg border-success shadow-sm" name="new_ctc" id="new_ctc" placeholder="Enter or calculate new CTC" required>
                            </div>

                            <!-- Preview Table -->
                            <div class="table-responsive">
                                <table class="table table-sm table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Component</th>
                                            <th class="text-end">New Monthly Value</th>
                                        </tr>
                                    </thead>
                                    <tbody id="new_structure_body">
                                        <tr><td colspan="2" class="text-center text-muted py-3">Structure will update automatically</td></tr>
                                    </tbody>
                                    <tfoot class="table-success">
                                        <tr>
                                            <th>Net Monthly Pay</th>
                                            <th class="text-end fw-bold text-success" id="new_net_pay">₹ 0.00</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            <div class="submit-section pb-5 text-center">
                <button class="btn btn-primary btn-lg px-5 shadow" type="submit">
                    <i class="fas fa-paper-plane me-2"></i>Submit Increment / Promotion
                </button>
                <a href="{{ route('increments.index') }}" class="btn btn-outline-secondary btn-lg px-5 ms-2">
                    <i class="fas fa-times me-2"></i>Cancel
                </a>
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
                        if(!$('#new_ctc').val()) { // Only if empty
                            $('#new_ctc').val(res.data.annual_ctc);
                            recalculateNewStructure(res.data.annual_ctc); 
                        }
                    }
                },
                error: function() {
                    alert('Error fetching employee details');
                }
            });
        });
        
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
            $('#current_ctc_display').val('₹ ' + parseFloat(data.annual_ctc).toFixed(2));
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
                         earningsRows += `<tr><td>${name}</td><td class="text-right">₹ ${val}</td></tr>`;
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
                    
                    statutoryRows += `<tr><td>${name}</td><td class="text-right">₹ ${val}</td></tr>`;
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
            $('#current_net_pay').text('₹ ' + netPay.toFixed(2));
            
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
                    earningsRows += `<tr><td>${comp.name}</td><td class="text-right fw-bold">₹ ${val.toFixed(2)}</td></tr>`;
                    
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
                statutoryRows += `<tr><td>${comp.name}</td><td class="text-right fw-bold">₹ ${val.toFixed(2)}</td></tr>`;
                
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
            $('#new_net_pay').text('₹ ' + netPay.toFixed(2));
            
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
            currentEmployeeData = null;
        }
        
        // Auto-load employee data for edit mode
        @if(isset($increment))
            // Trigger employee change to load data
            setTimeout(function() {
                $('#employee_id').trigger('change');
                // Set new CTC after employee data is loaded
                setTimeout(function() {
                    $('#new_ctc').val({{ $increment->new_ctc }});
                    @if($increment->type == 'promotion' || $increment->type == 'both')
                        $('#type').trigger('change');
                        $('#new_designation_id').val({{ $increment->new_designation_id ?? 'null' }}).trigger('change');
                    @endif
                    recalculateNewStructure({{ $increment->new_ctc }});
                }, 800);
            }, 300);
        @endif
    });
</script>
@endsection
