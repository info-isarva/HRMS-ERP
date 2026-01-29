<style>
/* Robust Toggle Switch Styles - SCOPED to salary tab */
#salary-tab-content .form-switch .form-check-input[type="checkbox"] {
    appearance: none !important;
    -webkit-appearance: none !important;
    background-color: #dfe1e4 !important;
    background-image: none !important; /* Remove Bootstrap checkmark */
    border: none !important;
    border-radius: 20px !important; /* Pill shape */
    width: 40px !important;
    height: 20px !important;
    position: relative;
    cursor: pointer;
    box-shadow: inset 0 0 1px rgba(0,0,0,0.2);
    transition: background-color 0.2s ease;
    margin-top: 0.15em;
    overflow: visible !important; /* Ensure knob isn't clipped */
}

/* The Toggle Knob */
#salary-tab-content .form-switch .form-check-input[type="checkbox"]::after {
    content: "" !important;
    display: block !important;
    position: absolute;
    top: 2px;
    left: 2px;
    width: 16px;
    height: 16px;
    background-color: #ffffff !important;
    border-radius: 50%; /* Circle */
    transition: all 0.2s cubic-bezier(0.4, 0.0, 0.2, 1);
    box-shadow: 0 1px 2px rgba(0,0,0,0.2);
    z-index: 2;
}

/* Checked State */
#salary-tab-content .form-switch .form-check-input[type="checkbox"]:checked {
    background-color: #0d6efd !important; /* Active Blue */
    border-color: #0d6efd !important;
}

/* Move Knob when Checked */
#salary-tab-content .form-switch .form-check-input[type="checkbox"]:checked::after {
    left: 22px !important; /* Move to the right */
    background-color: #ffffff !important;
}

/* Hover State */
#salary-tab-content .form-switch .form-check-input[type="checkbox"]:hover {
    background-color: #c9cbcd !important;
}
#salary-tab-content .form-switch .form-check-input[type="checkbox"]:checked:hover {
    background-color: #dc3545 !important; /* Hover on Active is Red */
    border-color: #dc3545 !important;
}

/* Focus State */
#salary-tab-content .form-switch .form-check-input[type="checkbox"]:focus {
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25); /* Blue Focus */
    outline: none;
}

/* Label Alignment */
#salary-tab-content label.form-check-label {
    margin-left: 8px;
    vertical-align: top;
    line-height: 24px; /* Align text with the toggle */
}
</style>

<div class="p-3" id="salary-tab-content">



    <!-- Salary Overview (Banner Style) -->
    <div class="bg-light p-4 rounded-3 mb-4">
         <div class="row g-4 align-items-center">
            <div class="col-md-3">
                <label class="form-label small text-uppercase text-muted fw-bold mb-1">Annual CTC</label>
                <div class="input-group shadow-sm rounded-3 bg-white">
                    <span class="input-group-text bg-transparent border-0 text-muted ps-3">₹</span>
                    <input type="number" step="0.01" 
                           name="basic[annual_ctc]" 
                           id="annual_ctc" 
                           class="form-control form-control-lg border-0 fw-bold custom-ctc-input" 
                           value="{{ old('basic.annual_ctc', $employee->annual_ctc ?? '') }}" 
                           placeholder="0.00"
                           oninput="window.calculateSalaryStructure()">
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label small text-uppercase text-muted fw-bold mb-1">Monthly CTC</label>
                <div class="input-group shadow-sm rounded-3 bg-white">
                    <span class="input-group-text bg-transparent border-0 text-muted ps-3">₹</span>
                    <input type="number" step="0.01" 
                           name="basic[monthly_ctc]" 
                           id="monthly_ctc" 
                           class="form-control form-control-lg border-0 fw-bold custom-ctc-input" 
                           value="{{ old('basic.monthly_ctc', $employee->monthly_ctc ?? '') }}" 
                           placeholder="0.00"
                           oninput="window.calculateSalaryStructure()">
                </div>
            </div>
            <div class="col-md-2">
                <!-- Spacer -->
            </div>
            <div class="col-md-4 text-end">
                <label class="form-label small text-uppercase text-success fw-bold mb-0 d-block">Net Payable / Month</label>
                <div class="d-flex align-items-center justify-content-end gap-2">
                     <input type="text" 
                            readonly 
                            id="net_payable" 
                            class="form-control border-0 bg-transparent text-success fw-bolder text-end p-0 shadow-none" 
                            style="font-size: 2rem; height: auto;"
                            value="0.00">
                </div>
            </div>
        </div>
    </div>

    <style>
        .modern-tabs {
            border-bottom: 2px solid #e9ecef;
        }
        .modern-tabs .nav-link {
            border: none;
            border-bottom: 2px solid transparent;
            color: #6c757d;
            font-weight: 500;
            padding: 12px 20px;
            margin-bottom: -2px;
            transition: all 0.2s ease;
        }
        .modern-tabs .nav-link:hover {
            color: #495057;
            border-color: #dee2e6;
        }
        .modern-tabs .nav-link.active {
            color: #0d6efd;
            border-bottom: 2px solid #0d6efd;
            background: transparent;
        }
        .table-modern th {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #adb5bd;
            border-bottom: 1px solid #e9ecef;
            padding-bottom: 10px;
        }
        .table-modern td {
            padding: 10px 10px; /* Reduced vertical padding */
            border-bottom: 1px solid #f8f9fa;
        }
        .table-modern tr:last-child td {
            border-bottom: none;
        }
        .form-control-modern {
            background-color: #f8f9fa;
            border: 1px solid transparent;
            font-weight: 600;
            color: #495057;
            transition: all 0.2s;
        }
        .form-control-modern:focus {
            background-color: #fff;
            border-color: #80bdff;
            box-shadow: 0 0 0 0.2rem rgba(0,123,255,.1);
        }
        /* Tightens the gap */
        .col-name { width: 35%; }
        .col-input { width: 65%; }
    </style>

    <!-- Tabs Navigation -->
    <ul class="nav nav-tabs modern-tabs mb-4 px-2" id="salarySubTabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="earnings-tab" data-toggle="tab" href="#earnings-pane" role="tab" aria-controls="earnings-pane" aria-selected="true">
                Salary Components
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="statutory-tab" data-toggle="tab" href="#statutory-pane" role="tab" aria-controls="statutory-pane" aria-selected="false">
                Statutory Components
            </a>
        </li>
    </ul>

    <!-- Tabs Content -->
    <div class="tab-content" id="salarySubTabsContent">
        
        <!-- Earnings Tab Pane -->
        <div class="tab-pane fade show active" id="earnings-pane" role="tabpanel" aria-labelledby="earnings-tab">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-modern mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 50px;" class="text-center">Active</th>
                                    <th class="col-name">Component Description</th>
                                    <th class="col-input text-end">Monthly Amount (₹)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($salaryComponents->where('status', '1') as $index => $component)
                                    @php
                                        $employeeComponent = isset($employee) 
                                            ? $employee->salaryComponents->firstWhere('salary_component_id', $component->id)
                                            : null;
                                        
                                        $isChecked = old("salary_components.{$index}.enabled", $employeeComponent ? '1' : '');
                                        $value = old("salary_components.{$index}.value", $employeeComponent->value ?? '');
                                    @endphp
                                    <tr>
                                        <td class="text-center align-middle">
                                            <div class="form-check form-switch d-flex justify-content-center">
                                                <input type="checkbox"
                                                       class="form-check-input component-checkbox-salary"
                                                       id="salary_checkbox_{{ $index }}"
                                                       name="salary_components[{{ $index }}][enabled]"
                                                       value="1" {{ $isChecked ? 'checked' : '' }}
                                                       onchange="toggleSalaryRow({{ $index }})">
                                            </div>
                                        </td>
                                        <td class="align-middle">
                                            <label class="mb-0 fw-bold text-dark cursor-pointer" for="salary_checkbox_{{ $index }}">
                                                {{ $component->name }}
                                            </label>
                                            <div class="small text-muted">{{ $component->short_name }}</div>
                                        </td>
                                        <td class="align-middle">
                                            <div id="salary_row_{{ $index }}" style="{{ $isChecked ? '' : 'display: block;' }}">
                                                <input type="hidden" name="salary_components[{{ $index }}][salary_component_id]" value="{{ $component->id }}">
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text bg-transparent border-0 text-muted px-1">₹</span>
                                                    <input type="number" step="0.01"
                                                           name="salary_components[{{ $index }}][value]"
                                                           class="form-control form-control-modern component-value-salary text-end"
                                                           value="{{ $value }}"
                                                           data-type="{{ $component->type }}"
                                                           data-short-name="{{ strtolower($component->short_name) }}"
                                                           data-calc-type="{{ $component->calculation_type ?? 'flat_amount' }}"
                                                           data-calc-value="{{ $component->calculation_value ?? 0 }}"
                                                           data-is-residual="{{ $component->is_residual ? 'true' : 'false' }}"
                                                           placeholder="0.00"
                                                           oninput="window.handleComponentChange(this)">
                                                </div>
                                                 @error("salary_components.{$index}.value")
                                                    <div class="text-danger small text-end" style="font-size: 0.7rem;">{{ $message }}</div>
                                                 @enderror
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-light bg-opacity-50">
                                <tr>
                                    <td colspan="2" class="text-end text-muted small text-uppercase fw-bold pt-3">Total Earnings</td>
                                    <td class="text-end fw-bold text-dark pt-3 fs-5" id="total_earnings_display">0.00</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statutory Tab Pane -->
        <div class="tab-pane fade" id="statutory-pane" role="tabpanel" aria-labelledby="statutory-tab">
             <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h6 class="text-dark fw-bold mb-0">Statutory Components</h6>
                    <div class="form-check form-switch mb-0">
                        <input type="checkbox" class="form-check-input" id="selectAllCheckboxStatutory" onchange="toggleAllStatutoryComponents(this)">
                        <label class="form-check-label small text-muted" for="selectAllCheckboxStatutory">Select All</label>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-modern mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 50px;" class="text-center">Active</th>
                                    <th class="col-name">Component Description</th>
                                    <th class="col-input text-end">Monthly Amount (₹)</th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach($statutoryComponents->where('status', '1') as $index => $component)
                                @php
                                    $employeeComponent = isset($employee) 
                                        ? $employee->statutoryComponents->firstWhere('statutory_component_id', $component->id)
                                        : null;
                                    
                                    $isChecked = old("statutory_components.{$index}.enabled", $employeeComponent ? '1' : '');
                                    $value = old("statutory_components.{$index}.value", $employeeComponent->value ?? '');
                                    
                                    $epfOption = '';
                                    if (stripos($component->short_name, 'pf') !== false || stripos($component->short_name, 'epf') !== false) { 
                                        $epfOption = old("statutory_components.{$index}.epf_option", $employeeComponent->epf_option ?? 'restrict_15000');
                                    }
                                    
                                    $isPF = stripos($component->short_name, 'pf') !== false || stripos($component->short_name, 'epf') !== false;
                                @endphp

                                <tr class="{{ $isPF ? 'bg-light' : '' }}">
                                    <td class="text-center align-middle">
                                        <div class="form-check form-switch d-flex justify-content-center">
                                            <input type="checkbox"
                                                   class="form-check-input component-checkbox-statutory"
                                                   id="statutory_checkbox_{{ $index }}"
                                                   name="statutory_components[{{ $index }}][enabled]"
                                                   value="1" {{ $isChecked ? 'checked' : '' }}
                                                   onchange="toggleStatutoryRow({{ $index }})">
                                        </div>
                                    </td>
                                    <td class="align-middle">
                                        <label class="mb-0 fw-bold text-dark cursor-pointer" for="statutory_checkbox_{{ $index }}">
                                            {{ $component->name }}
                                        </label>
                                        <div class="small text-muted">{{ $component->short_name }}</div>

                                        {{-- Nested PF Controls --}}
                                        @if($isPF)
                                            <div id="statutory_row_options_{{ $index }}" style="{{ $isChecked ? '' : 'display: block;' }}" class="mt-2 text-muted">
                                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                                    <select name="statutory_components[{{ $index }}][epf_option]"
                                                            class="form-select form-select-sm pf-calc-type border-0 bg-white shadow-sm"
                                                            id="epf_option_{{ $index }}"
                                                            onchange="toggleEPFValueField({{ $index }})"
                                                            style="width: auto; font-size: 0.8rem;">
                                                        <option value="restrict_15000" {{ $epfOption == 'restrict_15000' ? 'selected' : '' }}>Restrict 15k</option>
                                                        <option value="12_percent" {{ $epfOption == '12_percent' ? 'selected' : '' }}>Actual 12%</option>
                                                        <option value="manual_value" {{ $epfOption == 'manual_value' ? 'selected' : '' }}>Manual</option>
                                                    </select>
                                                    
                                                    <div class="form-check">
                                                        <input type="checkbox" 
                                                               class="form-check-input pf-include-ctc" 
                                                               name="statutory_components[{{ $index }}][full_amount_deduct_from_ctc]" 
                                                               id="full_amount_deduct_{{ $index }}"
                                                               value="1"
                                                               {{ old("statutory_components.{$index}.full_amount_deduct_from_ctc", $employeeComponent->full_amount_deduct_from_ctc ?? false) ? 'checked' : '' }}
                                                               onchange="window.calculateSalaryStructure()">
                                                        <label class="form-check-label small text-danger fw-bold" for="full_amount_deduct_{{ $index }}">
                                                            Full Deduct
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                        
                                         {{-- Hints for Auto --}}
                                         <div class="small text-info mt-1" style="{{ $isChecked ? '' : 'display: block;' }}" id="statutory_hint_{{ $index }}">
                                             @if(stripos($component->short_name, 'esi') !== false)
                                                 <i class="fas fa-magic me-1"></i> Auto (Gross &le; 21k)
                                             @endif
                                             @if(stripos($component->short_name, 'pt') !== false)
                                                 <i class="fas fa-magic me-1"></i> Auto (State Slab)
                                             @endif
                                         </div>
                                    </td>
                                    <td class="align-middle">
                                        <div id="statutory_row_{{ $index }}" style="{{ $isChecked ? '' : 'display: block;' }}">
                                            <input type="hidden" name="statutory_components[{{ $index }}][statutory_component_id]" value="{{ $component->id }}">
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text bg-transparent border-0 text-muted px-1">₹</span>
                                                <input type="number" step="0.01"
                                                       name="statutory_components[{{ $index }}][value]"
                                                       class="form-control form-control-modern component-value-salary component-value-statutory text-end"
                                                       value="{{ $value }}"
                                                       id="{{ $isPF ? 'epf_value_'.$index : '' }}"
                                                       data-type="{{ $component->type }}"
                                                       data-short-name="{{ strtolower($component->short_name) }}"
                                                       data-calc-type="{{ $component->calculation_type ?? 'flat_amount' }}"
                                                       data-calc-value="{{ $component->calculation_value ?? 0 }}"
                                                       placeholder="0.00"
                                                       oninput="window.handleComponentChange(this)">
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                            <tfoot class="bg-light bg-opacity-50">
                                 <tr>
                                    <td colspan="2" class="text-end text-muted small text-uppercase fw-bold pt-3">Total Deductions</td>
                                    <td class="text-end fw-bold text-danger pt-3 fs-5" id="total_deductions_display">0.00</td>
                                </tr>
                            </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>


</div>
</div>

<script>
    // Define global functions first
    if (typeof window.toggleSalaryRow === 'undefined') {
        window.toggleSalaryRow = function(index) {
            const checkbox = document.getElementById('salary_checkbox_' + index);
            const row = document.getElementById('salary_row_' + index); // This is the DIV inside the TD
            const valueInput = row ? row.querySelector('.component-value-salary') : null;
            const requiredSpan = document.getElementById('salary_required_' + index);

            if (!checkbox || !row) return;

            if (checkbox.checked) {
                row.style.display = 'block'; // Changed from flex to block for inside TD
                if(valueInput) valueInput.removeAttribute('readonly');
                if(requiredSpan) requiredSpan.style.display = '';
            } else {
                row.style.display = 'none';
                if(valueInput) {
                    valueInput.value = '';
                    valueInput.setAttribute('readonly', 'readonly');
                }
                if(requiredSpan) requiredSpan.style.display = 'none';
            }
            if (typeof calculateNetPayable === 'function') {
                calculateNetPayable();
            }
        };
    }

    if (typeof window.toggleAllStatutoryComponents === 'undefined') {
        window.toggleAllStatutoryComponents = function(mainCheckbox) {
            const checkboxes = document.querySelectorAll('.component-checkbox-statutory');
            checkboxes.forEach((checkbox, i) => {
                // Determine index from ID
                const idParts = checkbox.id.split('_');
                const index = idParts[idParts.length - 1]; 
                
                const row = document.getElementById('statutory_row_' + index);
                const optionsDiv = document.getElementById('statutory_row_options_' + index);
                const hintDiv = document.getElementById('statutory_hint_' + index);
                
                checkbox.checked = mainCheckbox.checked;
                if (row) {
                    row.style.display = mainCheckbox.checked ? 'block' : 'none';
                    const valueInput = row.querySelector('input[type="number"]');
                    if(valueInput && !mainCheckbox.checked) valueInput.value = '';
                }
                if(optionsDiv) optionsDiv.style.display = mainCheckbox.checked ? 'block' : 'none';
                if(hintDiv) hintDiv.style.display = mainCheckbox.checked ? 'block' : 'none';
            });
            window.calculateSalaryStructure();
        };
    }

    if (typeof window.toggleStatutoryRow === 'undefined') {
        window.toggleStatutoryRow = function(index) {
            const checkbox = document.getElementById('statutory_checkbox_' + index);
            const row = document.getElementById('statutory_row_' + index);
            const optionsDiv = document.getElementById('statutory_row_options_' + index);
            const hintDiv = document.getElementById('statutory_hint_' + index);
            
            if (!checkbox || !row) return;
            
            const isChecked = checkbox.checked;
            row.style.display = isChecked ? 'block' : 'none';
            if(optionsDiv) optionsDiv.style.display = isChecked ? 'block' : 'none';
            if(hintDiv) hintDiv.style.display = isChecked ? 'block' : 'none';
            
            if(!isChecked) {
                 const valueInput = row.querySelector('input[type="number"]');
                 if(valueInput) valueInput.value = '';
            }
            window.calculateSalaryStructure();
        };
    }

    if (typeof window.toggleAllSalaryComponents === 'undefined') {
        window.toggleAllSalaryComponents = function(mainCheckbox) {
            const checkboxes = document.querySelectorAll('.component-checkbox-salary');
            checkboxes.forEach((checkbox, i) => {
                const idParts = checkbox.id.split('_');
                const index = idParts[idParts.length - 1]; 
                const row = document.getElementById('salary_row_' + index);
                const valueInput = row ? row.querySelector('.component-value-salary') : null;
                const requiredSpan = document.getElementById('salary_required_' + index);

                checkbox.checked = mainCheckbox.checked;

                if (row) {
                    if (mainCheckbox.checked) {
                        row.style.display = 'block';
                        if(valueInput) valueInput.removeAttribute('readonly');
                        if(requiredSpan) requiredSpan.style.display = '';
                    } else {
                        row.style.display = 'none';
                        if(valueInput) {
                            valueInput.value = ''; 
                            valueInput.setAttribute('readonly', 'readonly');
                        }
                        if(requiredSpan) requiredSpan.style.display = 'none';
                    }
                }
            });
            window.calculateSalaryStructure(); // Trigger calc
        };
    }

    if (typeof window.toggleEPFValueField === 'undefined') {
        window.toggleEPFValueField = function(index) {
             // Logic handled by calculator mostly, but for UI feedback:
             const epfOption = document.getElementById('epf_option_' + index);
             const valueInput = document.getElementById('epf_value_' + index);
             const label = document.getElementById('epf_value_label_' + index);
             
             if(epfOption.value === 'manual_value') {
                 label.innerHTML = 'Manual Value (₹)';
                 valueInput.placeholder = 'Enter Amount';
             } else {
                 label.innerHTML = 'Value (₹)';
                 valueInput.placeholder = 'Auto-calc';
             }
             window.calculateSalaryStructure();
        };
    }

    // Define Global Calculation Function
    if (typeof window.calculateNetPayable === 'undefined') {
        window.calculateNetPayable = function() {
            let totalEarnings = 0;
            let totalDeductions = 0;
            
            // Calculate from ALL inputs marked as component-value-salary (which now includes statutory)
            const inputs = document.querySelectorAll('.component-value-salary');
            inputs.forEach(input => {
                const row = input.closest('tr') || input.closest('.row'); // Support both table row and div row (fallback)
                if (row && row.style.display !== 'none') {
                    const check = input.closest('tr') ? 
                        input.closest('tr').querySelector('input[type="checkbox"]:checked') : 
                        input.closest('.list-group-item').querySelector('input[type="checkbox"]:checked');
                        
                    // Double check if enabled (redundant but safe)
                    // Actually, toggleRow logic hides the input parent div, so checking style.display is good.
                    // The input parent IS the div with style display.
                    // In table: The input is inside a div with id `salary_row_X` which gets hidden.
                    
                    const container = input.closest('[id^="salary_row_"]') || input.closest('[id^="statutory_row_"]');
                    if(container && container.style.display !== 'none') {
                         const val = parseFloat(input.value) || 0;
                         const type = input.getAttribute('data-type');
                         
                         if (type === 'earning') {
                             totalEarnings += val;
                         } else if (type === 'deduction') {
                             totalDeductions += val;
                         }
                    }
                }
            });
            
            const net = totalEarnings - totalDeductions;
            
            // Update Salary Tab Input
            const netInputSalary = document.getElementById('net_payable');
            if (netInputSalary) netInputSalary.value = net.toFixed(2);
            
            // Update Table Footers
            const totalEarnDisplay = document.getElementById('total_earnings_display');
            if(totalEarnDisplay) totalEarnDisplay.innerText = totalEarnings.toFixed(2);
            
            const totalDedDisplay = document.getElementById('total_deductions_display');
            if(totalDedDisplay) totalDedDisplay.innerText = totalDeductions.toFixed(2);
        }
    }

    // CTC Calculation Logic
    document.addEventListener('DOMContentLoaded', function() {
        const annualInput = document.getElementById('annual_ctc');
        const monthlyInput = document.getElementById('monthly_ctc');
        const calculateBtn = document.getElementById('btn_calculate_ctc');

        // Sync Annual <-> Monthly
        if (annualInput) {
            annualInput.addEventListener('input', function() {
                if (this.value) {
                    monthlyInput.value = (parseFloat(this.value) / 12).toFixed(2);
                } else {
                    monthlyInput.value = '';
                }
            });
        }

        if (monthlyInput) {
            monthlyInput.addEventListener('input', function() {
                if (this.value) {
                    annualInput.value = (parseFloat(this.value) * 12).toFixed(2);
                } else {
                    annualInput.value = '';
                }
            });
        }

    // --- Global Helper: Get Components ---
    if (typeof window.getComponents === 'undefined') {
        window.getComponents = function() {
            const components = [];
             document.querySelectorAll('.component-value-salary').forEach(input => {
                // Support Table (tr) or List/Grid (.row or .list-group-item)
                const row = input.closest('tr') || input.closest('.list-group-item') || input.closest('.row');
                if(!row) return;

                let checkbox = row.querySelector('input[type="checkbox"][id^="salary_checkbox_"]') || 
                               row.querySelector('input[type="checkbox"][id^="statutory_checkbox_"]');
                
                if (!checkbox || !checkbox.checked) return;

                const label = document.querySelector(`label[for="${checkbox.id}"]`);
                const name = label ? label.innerText.split('(')[0].trim().toLowerCase() : 'unknown';
                const shortName = input.getAttribute('data-short-name') || '';
                
                // Parse PF Settings
                let pfType = '12_percent';
                let pfInclude = false;
                if(shortName.includes('pf') || shortName.includes('epf')) {
                    const typeSelect = row.querySelector('.pf-calc-type');
                    if(typeSelect) pfType = typeSelect.value;
                    const includeCheck = row.querySelector('.pf-include-ctc');
                    if(includeCheck) pfInclude = includeCheck.checked;
                }

                components.push({
                    input: input,
                    name: name,
                    shortName: shortName,
                    type: input.getAttribute('data-type'), 
                    calcType: input.getAttribute('data-calc-type'),
                    calcValue: parseFloat(input.getAttribute('data-calc-value')) || 0,
                    isResidual: input.getAttribute('data-is-residual') === 'true',
                    pfType: pfType,
                    pfInclude: pfInclude
                });
            });
            return components;
        };
    }

    // --- Reverse Calculation: Components -> CTC ---
    if (typeof window.recalculateCTC === 'undefined') {
        window.recalculateCTC = function() {
            // Check if triggered by manual user interaction to avoid loops if needed
            // For now, straightforward calc
            
            const components = window.getComponents();
            let grossEarnings = 0;
            let basicValue = 0;
            
            // Sum Earnings
            components.forEach(comp => {
                if (comp.type === 'earning') {
                    const val = parseFloat(comp.input.value) || 0;
                    grossEarnings += val;
                    if (comp.shortName === 'basic' || comp.shortName === 'ba') {
                        basicValue = val;
                    }
                }
            });

            // Update Statutory (Auto-Logic on Manual Change)
            let totalDeductions = 0;
            let employerPF = 0;
            let employerESI = 0;

            components.forEach(comp => {
                if (comp.type === 'deduction') {
                    let newVal = 0;
                    const shortName = comp.shortName;

                    if (shortName.includes('pf') || shortName.includes('epf')) {
                        const mode = comp.pfType || 'restrict_15000'; 
                        let basis = basicValue; 
                        // Find DA if exists
                        const daComp = components.find(c => c.shortName === 'da');
                        if(daComp) basis += (parseFloat(daComp.input.value) || 0);

                        // Find OA if exists
                        const oaComp = components.find(c => c.shortName === 'oa' || c.shortName === 'other allowances');
                        if(oaComp) basis += (parseFloat(oaComp.input.value) || 0);

                        if (mode === 'manual_value') {
                            newVal = parseFloat(comp.input.value) || 0;
                            // For manual, we can't easily guess Employer Share unless we assume 1:1 or defaults
                            // We'll assume Employer Share is same as entered Value (capped at standard?) 
                            // Or better: Calculate Standard Employer Share regardless of Manual Employee Entry?
                            // Let's stick to: Employer Share = Standard Calc based on Earnings.
                             const stdCap = basis > 15000 ? 15000 : basis;
                             employerPF = Math.round(stdCap * 0.12);
                        } else {
                            if (mode === '12_percent') {
                                newVal = Math.round(basis * 0.12);
                            } else { 
                                const cap = basis > 15000 ? 15000 : basis;
                                newVal = Math.round(cap * 0.12);
                            }
                            employerPF = newVal;
                            
                            // If Full Deduct is checked, Employee Deduct = 12% + 12% (Employer)
                            if (comp.pfInclude) {
                                newVal += employerPF;
                            }
                        }
                        
                        if (mode !== 'manual' && document.activeElement !== comp.input) {
                            comp.input.value = newVal === 0 ? '' : newVal;
                        }
                    } else if (shortName.includes('esi')) {
                        if (grossEarnings <= 21000 && grossEarnings > 0) {
                            newVal = Math.ceil(grossEarnings * 0.0075);
                            employerESI = Math.ceil(grossEarnings * 0.0325);
                        } else {
                            newVal = 0;
                            employerESI = 0;
                        }
                         if (document.activeElement !== comp.input) {
                            comp.input.value = newVal === 0 ? '' : newVal;
                        }
                    } else {
                         newVal = parseFloat(comp.input.value) || 0;
                    }
                    totalDeductions += newVal;
                }
            });

            // Update CTC
            // User requirement: CTC should not change when deductions change. 
            // Treating CTC as equal to Gross Earnings in this context.
            const monthlyCTC = grossEarnings; 
            // const monthlyCTC = grossEarnings + employerPF + employerESI;
            const annualCTC = monthlyCTC * 12;

            const monthInput = document.getElementById('monthly_ctc');
            const yearInput = document.getElementById('annual_ctc');

            // Set flag to prevent Top-Down Calc infinite loop if listeners exist
            window.isReverseCalculating = true;

            if (monthInput) monthInput.value = monthlyCTC.toFixed(2);
            if (yearInput) yearInput.value = annualCTC.toFixed(2);
            
            // Release flag after short delay or immediately if synchronous
            setTimeout(() => { window.isReverseCalculating = false; }, 100);

            // Update Totals
            if(window.calculateNetPayable) window.calculateNetPayable();
        }
    }

    // --- Handler for Manual Component Input ---
    if (typeof window.handleComponentChange === 'undefined') {
        window.handleComponentChange = function(input) {
            // Mark as manually overridden to prevent Formula overwrites
            input.setAttribute('data-manual-override', 'true');
            
            // Always recalculate CTC from components (Bottom-Up) when a manual change happens
            // This ensures the CTC input reflects the true sum of the components
            window.recalculateCTC();
        };
    }

    // --- Main Auto-Calculation Logic (Top-Down) ---
    window.calculateSalaryStructure = function() {
        // Prevent if triggered by Reverse Calc
        if (window.isReverseCalculating) return;
        
        // If CTC is cleared, do nothing (or clear field? no).
        const monthlyInput = document.getElementById('monthly_ctc');
        if (!monthlyInput || !monthlyInput.value) return; 
        
        const monthlyCTC = parseFloat(monthlyInput.value);
        if (isNaN(monthlyCTC) || monthlyCTC <= 0) return;

        const components = window.getComponents();

                // --- REF A: PASS 1: Identify & Fixed Earnings ---
                let fixedEarnings = 0;
                let basicValue = 0;
                let daValue = 0; 
                let otherAllowancesValue = 0;
                
                let residualComponent = null;

                components.forEach(c => {
                    const s = c.shortName.toLowerCase();
                    const isBasic = s.includes('basic') || s === 'ba' || s === 'basic salary';
                    
                    if(c.type === 'earning') {
                        let val = 0;
                        let isOverridden = false;

                        // PRIORITY: Manual Override
                        if (c.input.hasAttribute('data-manual-override') && c.input.getAttribute('data-manual-override') === 'true') {
                            val = parseFloat(c.input.value) || 0;
                            isOverridden = true;
                        }

                        if(c.isResidual && !isOverridden) {
                            residualComponent = c;
                        } else {
                            if (!isOverridden) {
                                // Formulars
                                if(c.calcType === 'flat_amount') val = c.calcValue;
                                else if(c.calcType === 'percentage_ctc') val = (monthlyCTC * c.calcValue) / 100;
                                else if(c.calcType === 'percentage_basic') val = 0; // Wait for Pass 2
                                else val = parseFloat(c.input.value) || 0; // Fallback
                            }
                            
                            c.tempValue = val; 
                        }

                        if(isBasic) basicValue = c.tempValue || 0;
                    }
                });

                // --- PASS 2: Dependent Earnings (% of Basic) ---
                components.forEach(c => {
                    if(c.type === 'earning' && !c.isResidual && c.calcType === 'percentage_basic') {
                        c.tempValue = (basicValue * c.calcValue) / 100;
                    }
                    
                    const s = c.shortName.toLowerCase();
                    if(s.includes('da') || s === 'dearness allowance') daValue = c.tempValue || 0;
                    if(s.includes('oa') || s === 'other allowance') otherAllowancesValue = c.tempValue || 0;
                    
                    if(c.type === 'earning' && !c.isResidual) {
                        fixedEarnings += c.tempValue;
                    }
                });
                
                // --- PASS 3: Residual (Moved before Statutory) ---
                let residualValue = 0;
                // Preliminary Residual Calculation (ignoring potential CTC deductions for now)
                if(residualComponent) {
                    residualValue = Math.max(0, monthlyCTC - fixedEarnings);
                    residualComponent.tempValue = residualValue;
                    
                    // Update variables that might be used in Statutory Logic
                    const s = residualComponent.shortName.toLowerCase();
                    if(s.includes('oa') || s === 'other allowance') otherAllowancesValue = residualValue;
                    // If residual is basic/da (unlikely), update them too? Assuming OA usually.
                }
                
                let finalGross = fixedEarnings + residualValue;

                // --- PASS 4: Employer Statutory (CTC Costs) ---
                let employerPF = 0;
                let employerESIC = 0;
                
                // Recalculate PF Basis with Residual now included
                let pfBasis = (basicValue || 0) + (daValue || 0) + (otherAllowancesValue || 0);
                
                // 4a. Employer PF
                const pfComponent = components.find(c => c.shortName.toLowerCase().includes('pf') || c.shortName.includes('epf'));
                if(pfComponent) {
                   if(pfComponent.pfType === 'restrict_15000') employerPF = Math.min(pfBasis, 15000) * 0.12;
                   else if(pfComponent.pfType === 'manual_value') employerPF = parseFloat(pfComponent.input.value) || 0; 
                   else employerPF = pfBasis * 0.12;
                }

                // 4b. Employer ESIC 
                if(finalGross <= 21000 && finalGross > 0) {
                     employerESIC = finalGross * 0.0325; 
                }
                
                // --- PASS 5: Refine Residual (If Statutory Deducted from CTC) ---
                let ctcDeduction = 0;
                /* 
                if(pfComponent && pfComponent.pfInclude) {
                    ctcDeduction += employerPF;
                }
                */
                
                // If there IS a deduction from CTC, we need to recalculate Residual?
                // User scenario implies Gross should equal CTC even if Full Deduct is ON.
                // So we disable the reduction of Residual for now.
                
                if(ctcDeduction > 0 && residualComponent) {
                     residualValue = Math.max(0, monthlyCTC - fixedEarnings - ctcDeduction);
                     residualComponent.tempValue = residualValue;
                     finalGross = fixedEarnings + residualValue;
                }

                // --- PASS 5: Update DOM & Employee Deductions ---
                components.forEach(c => {
                    let val = 0;
                    const s = c.shortName.toLowerCase();

                    if(c.type === 'earning') {
                        val = c.tempValue;
                    } 
                    else if(c.type === 'deduction') {
                        if(s.includes('pf') || s.includes('epf')) {
                             if(c.pfType === 'manual_value') val = parseFloat(c.input.value) || 0;
                             else {
                                 // Standard: Employee Share matched Employer Share (12%)
                                 // If "Full Deduct" (pfInclude) is ON, User wants to see 24% (Both Shares) as deduction here?
                                 // User example: "EPF value is 15000*24% = 3600"
                                 val = employerPF; 
                                 if(c.pfInclude) val += employerPF; // Add Employer Share to display total 24%
                             }
                        }
                        else if(s.includes('esi')) {
                             if(finalGross <= 21000 && finalGross > 0) val = finalGross * 0.0075;
                             else val = 0;
                        }
                        else if(s.includes('pt')) {
                             if(finalGross > 20000) val = 200; // Updated limit
                             else if(finalGross > 15000) val = 150;
                             else val = 0;
                        }
                        else if(s.includes('lwf')) {
                             val = parseFloat(c.input.value) || 0; // Manual LWF
                        }
                    }
                    
                    // User Request: Don't show "0.00", show empty for zero.
                    // UX Fix: Don't overwrite value while user is typing (Active Element check)
                    if (document.activeElement !== c.input) {
                        if (val == 0) c.input.value = '';
                        else c.input.value = val.toFixed(2);
                    }
                }); 

            if(window.calculateNetPayable) window.calculateNetPayable();
        };

        // Attach Listeners
        if (calculateBtn) {
            calculateBtn.addEventListener('click', window.calculateSalaryStructure);
        }
        
        // Auto-Calculate on Input Changes (Delegation)
        document.body.addEventListener('change', function(e) {
             if(e.target.matches('.pf-calc-type') || e.target.matches('.pf-include-ctc') || e.target.matches('.component-checkbox-salary')) {
                 window.calculateSalaryStructure();
             }
        });
        
        // Auto-Calculate on CTC Change
        if(monthlyInput) {
            monthlyInput.addEventListener('blur', window.calculateSalaryStructure); // Use blur to avoid jitter while typing
        }

        
        // Listen for value changes on both tabs (using delegation)
        document.body.addEventListener('input', function(e) {
            if (e.target.classList.contains('component-value-salary') || e.target.classList.contains('component-value-statutory')) {
                if(window.calculateNetPayable) window.calculateNetPayable();
            }
        });

        // Initialize calculations on load
        setTimeout(function() {
             if(window.calculateNetPayable) window.calculateNetPayable();
             // Only run structure calc if CTC is present and > 0, otherwise it might overwrite manual values with 0
             // But usually for Edit, we want to respect existing values.
             // calculateSalaryStructure recalculates components based on CTC. 
             // IF we run it, it might reset manual changes if not careful.
             // However, calculateNetPayable only sums up existing DOM values. Safe to run.
        }, 500);
    });
</script>