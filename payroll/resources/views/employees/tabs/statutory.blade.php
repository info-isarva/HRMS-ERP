    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Statutory Components</h5>
        <div class="form-check form-switch">
            <input type="checkbox" class="form-check-input" id="selectAllCheckboxStatutory" onchange="toggleAllStatutoryComponents(this)">
            <label class="form-check-label fw-semibold" for="selectAllCheckboxStatutory">Enable All</label>
        </div>
    </div>

    <!-- CTC Summary Card (Mirrored from Salary Tab) -->
    <div class="card mb-3 shadow-sm bg-light">
        <div class="card-body">
            <h5 class="card-title">Salary Overview</h5>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-bold">Annual CTC</label>
                    <div class="input-group">
                        <span class="input-group-text">₹</span>
                        <input type="number" class="form-control" id="annual_ctc_statutory" placeholder="0.00" readonly>
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Monthly CTC</label>
                    <div class="input-group">
                        <span class="input-group-text">₹</span>
                        <input type="number" class="form-control" id="monthly_ctc_statutory" placeholder="0.00" readonly>
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold text-success">Net Payable (Monthly)</label>
                    <div class="input-group">
                        <span class="input-group-text text-success fw-bold">₹</span>
                        <input type="text" class="form-control text-success fw-bold" id="net_payable_statutory" readonly placeholder="0.00">
                    </div>
                </div>
            </div>
        </div>
    </div>

    @foreach($statutoryComponents->where('status', '1') as $index => $component)
        @php
            // Get existing component value if editing
            $employeeComponent = isset($employee) 
                ? $employee->statutoryComponents->firstWhere('statutory_component_id', $component->id)
                : null;
            
            $isChecked = old("statutory_components.{$index}.enabled", $employeeComponent ? '1' : '');
            $value = old("statutory_components.{$index}.value", $employeeComponent->value ?? '');
            
            // Get EPF option for EPF components (component ID 1)
            $epfOption = '';
            if ($component->id == 1) { // EPF component
                $epfOption = old("statutory_components.{$index}.epf_option", $employeeComponent->epf_option ?? 'restrict_15000');
            }
        @endphp

        <div class="card mb-3 shadow-sm statutory-card">
            <div class="card-body">
                <div class="form-check form-switch mb-3">
                    <input type="checkbox"
                           class="form-check-input component-checkbox-statutory"
                           id="statutory_checkbox_{{ $index }}"
                           name="statutory_components[{{ $index }}][enabled]"
                           value="1" {{ $isChecked ? 'checked' : '' }}
                           onchange="toggleStatutoryRow({{ $index }})">
                        <label class="form-check-label fw-semibold" for="statutory_checkbox_{{ $index }}">
                            {{ $component->short_name }}
                        </label>
                </div>

                <div id="statutory_row_{{ $index }}" class="row g-3 align-items-center" 
                    style="{{ $isChecked ? '' : 'display: none;' }}">
                    <input type="hidden" name="statutory_components[{{ $index }}][statutory_component_id]" 
                        value="{{ $component->id }}">

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Component</label>
                        <input type="text" class="form-control bg-light" value="{{ $component->name }}" readonly>
                    </div>

                    @if($component->id == 1) {{-- EPF Component --}}
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">EPF Calculation Option <span class="text-danger" id="epf_option_required_{{ $index }}" style="{{ $isChecked ? '' : 'display: none;' }}">*</span></label>
                            <select name="statutory_components[{{ $index }}][epf_option]"
                                    class="form-control @error("statutory_components.{$index}.epf_option") is-invalid @enderror"
                                    id="epf_option_{{ $index }}"
                                    onchange="toggleEPFValueField({{ $index }})">
                                <option value="restrict_15000" {{ $epfOption == 'restrict_15000' ? 'selected' : '' }}>
                                    Restrict to ₹15,000 (Traditional)
                                </option>
                                <option value="12_percent" {{ $epfOption == '12_percent' ? 'selected' : '' }}>
                                    12% without restriction
                                </option>
                                <option value="manual_value" {{ $epfOption == 'manual_value' ? 'selected' : '' }}>
                                    Manual value
                                </option>
                            </select>
                            @error("statutory_components.{$index}.epf_option")
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-2">
                            <label class="form-label fw-semibold" id="epf_value_label_{{ $index }}">
                                @if($epfOption == 'manual_value')
                                    Manual EPF Amount (₹) <span class="text-danger" id="epf_value_required_{{ $index }}">*</span>
                                @else
                                    Reference Value (₹)
                                @endif
                            </label>
                            <input type="number"
                                   name="statutory_components[{{ $index }}][value]"
                                   class="form-control component-value-statutory @error("statutory_components.{$index}.value") is-invalid @enderror"
                                   value="{{ $value }}"
                                   id="epf_value_{{ $index }}"
                                   placeholder="@if($epfOption == 'manual_value') Enter manual EPF amount @else Reference value (optional) @endif">
                            @error("statutory_components.{$index}.value")
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted" id="epf_help_{{ $index }}">
                                @if($epfOption == 'manual_value')
                                    This amount will be deducted as EPF
                                @else
                                    EPF will be calculated automatically based on salary components
                                @endif
                            </small>
                        </div>

                        <div class="col-md-3 mt-3">
                            <div class="form-check">
                                <input type="checkbox" 
                                       class="form-check-input" 
                                       name="statutory_components[{{ $index }}][full_amount_deduct_from_ctc]" 
                                       id="full_amount_deduct_{{ $index }}"
                                       value="1"
                                       {{ old("statutory_components.{$index}.full_amount_deduct_from_ctc", $employeeComponent->full_amount_deduct_from_ctc ?? false) ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold text-danger" for="full_amount_deduct_{{ $index }}">
                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                    Full Amount Deduct from Employee CTC
                                </label>
                                <div class="form-text text-muted">
                                    <small><strong>Warning:</strong> When enabled, both employee and employer EPF portions (24% total) will be deducted from employee's salary instead of the standard 12%.</small>
                                </div>
                            </div>
                        </div>

                        <!-- EPF Calculation Components Information -->
                        <div class="col-md-12 mt-3">
                            <div class="alert alert-info py-2">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>EPF Calculation Components:</strong> EPF is calculated on Basic Salary (BA) + Dearness Allowance (DA) + Other Allowances (OA). 
                                <em>Excludes: HRA, Conveyance, Medical, Special Allowances, Overtime, and Bonus payments.</em>
                            </div>
                        </div>
                    @else
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Value (₹) <span class="text-danger" id="statutory_value_required_{{ $index }}" style="{{ $isChecked ? '' : 'display: none;' }}">*</span></label>
                            <input type="number" step="0.01"
                                   name="statutory_components[{{ $index }}][value]"
                                   class="form-control component-value-statutory @error("statutory_components.{$index}.value") is-invalid @enderror"
                                   value="{{ $value }}"
                                   data-calc-type="{{ $component->calculation_type ?? 'flat_amount' }}"
                                   data-calc-value="{{ $component->calculation_value ?? 0 }}">
                            @error("statutory_components.{$index}.value")
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endforeach
</div>

<style>
/* Robust Toggle Switch Styles - SCOPED to statutory tab */
#statutory-tab-content .form-switch .form-check-input[type="checkbox"] {
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
#statutory-tab-content .form-switch .form-check-input[type="checkbox"]::after {
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
#statutory-tab-content .form-switch .form-check-input[type="checkbox"]:checked {
    background-color: #0d6efd !important; /* Active Blue */
    border-color: #0d6efd !important;
}

/* Move Knob when Checked */
#statutory-tab-content .form-switch .form-check-input[type="checkbox"]:checked::after {
    left: 22px !important; /* Move to the right */
    background-color: #ffffff !important;
}

/* Hover State */
#statutory-tab-content .form-switch .form-check-input[type="checkbox"]:hover {
    background-color: #c9cbcd !important;
}
#statutory-tab-content .form-switch .form-check-input[type="checkbox"]:checked:hover {
    background-color: #dc3545 !important; /* Hover on Active is Red */
    border-color: #dc3545 !important;
}

/* Focus State */
#statutory-tab-content .form-switch .form-check-input[type="checkbox"]:focus {
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25); /* Blue Focus */
    outline: none;
}

/* Label Alignment */
#statutory-tab-content label.form-check-label {
    margin-left: 8px;
    vertical-align: top;
    line-height: 24px; /* Align text with the toggle */
}
</style>

<script>
    if (typeof window.toggleAllStatutoryComponents === 'undefined') {
        window.toggleAllStatutoryComponents = function(mainCheckbox) {
            const checkboxes = document.querySelectorAll('.component-checkbox-statutory');

            checkboxes.forEach((checkbox, i) => {
                const idParts = checkbox.id.split('_');
                const index = idParts[idParts.length - 1];
                const row = document.getElementById('statutory_row_' + index);
                const valueInput = row.querySelector('.component-value-statutory');
                const epfOptionRequired = document.getElementById('epf_option_required_' + index);
                const statutoryValueRequired = document.getElementById('statutory_value_required_' + index);

                checkbox.checked = mainCheckbox.checked;

                if (mainCheckbox.checked) {
                    row.style.display = 'flex';
                    if(valueInput) valueInput.removeAttribute('readonly');
                    if (epfOptionRequired) epfOptionRequired.style.display = '';
                    if (statutoryValueRequired) statutoryValueRequired.style.display = '';
                } else {
                    row.style.display = 'none';
                    if(valueInput) {
                         valueInput.value = ''; 
                         valueInput.setAttribute('readonly', 'readonly');
                    }
                    if (epfOptionRequired) epfOptionRequired.style.display = 'none';
                    if (statutoryValueRequired) statutoryValueRequired.style.display = 'none';
                }
            });
        };
    }

    if (typeof window.toggleStatutoryRow === 'undefined') {
        window.toggleStatutoryRow = function(index) {
            const checkbox = document.getElementById('statutory_checkbox_' + index);
            const row = document.getElementById('statutory_row_' + index);
            const valueInput = row ? row.querySelector('.component-value-statutory') : null;
            const epfOptionRequired = document.getElementById('epf_option_required_' + index);
            const statutoryValueRequired = document.getElementById('statutory_value_required_' + index);

            if (checkbox.checked) {
                row.style.display = 'flex';
                if(valueInput) valueInput.removeAttribute('readonly');
                if (epfOptionRequired) epfOptionRequired.style.display = '';
                if (statutoryValueRequired) statutoryValueRequired.style.display = '';
            } else {
                row.style.display = 'none';
                if(valueInput) {
                    valueInput.value = '';
                    valueInput.setAttribute('readonly', 'readonly');
                }
                if (epfOptionRequired) epfOptionRequired.style.display = 'none';
                if (statutoryValueRequired) statutoryValueRequired.style.display = 'none';
            }
        };
    }

    if (typeof window.toggleEPFValueField === 'undefined') {
        window.toggleEPFValueField = function(index) {
            const epfOption = document.getElementById('epf_option_' + index);
            const valueLabel = document.getElementById('epf_value_label_' + index);
            const valueInput = document.getElementById('epf_value_' + index);
            const helpText = document.getElementById('epf_help_' + index);

            if (epfOption.value === 'manual_value') {
                valueLabel.innerHTML = 'Manual EPF Amount (₹) <span class="text-danger" id="epf_value_required_' + index + '">*</span>';
                valueInput.placeholder = 'Enter manual EPF amount';
                valueInput.required = true;
                helpText.textContent = 'This amount will be deducted as EPF';
            } else {
                valueLabel.innerHTML = 'Reference Value (₹)';
                valueInput.placeholder = 'Reference value (optional)';
                valueInput.required = false;
                helpText.textContent = 'EPF will be calculated automatically based on salary components';
            }
        };
    }

    // Global function to Auto-Calculate Statutory Components
    if (typeof window.calculateStatutoryComponents === 'undefined') {
        window.calculateStatutoryComponents = function() {
            // Find Basic Salary from Salary Tab
            let basicSalary = 0;
            let grossSalary = 0; // Assuming sum of all earnings
            
            const salaryInputs = document.querySelectorAll('.component-value-salary');
            salaryInputs.forEach(input => {
                const shortName = input.getAttribute('data-short-name');
                const val = parseFloat(input.value) || 0;
                
                if (shortName === 'basic') { // simple check, robust enough for now
                    basicSalary = val;
                }
                
                // If it's an earning (we'd need to check type, but checking parent row or logic from salary.blade is better)
                // For now, let's assume we can get Gross from somewhere or calculate it.
                // Let's rely on data-type
                const type = input.getAttribute('data-type');
                if (type === 'earning') {
                    grossSalary += val;
                }
            });

            // Iterate Statutory Components
            const statutoryInputs = document.querySelectorAll('.component-value-statutory');
            statutoryInputs.forEach(input => {
                const row = input.closest('.row');
                // Check enabled
                // Find checkbox
                 const checkboxId = row.parentElement.querySelector('.component-checkbox-statutory').id;
                 const checkbox = document.getElementById(checkboxId);
                 if (!checkbox || !checkbox.checked) return;

                // Check for manual override or specific logic
                // EPF has its own logic in the dropdown, usually handled separately/server-side, 
                // but if we want to preview it:
                 // Note: The EPF 'value' field is Reference Value unless Manual. 
                 
                 const calcType = input.getAttribute('data-calc-type');
                 const calcValue = parseFloat(input.getAttribute('data-calc-value')) || 0;

                 if (calcType === 'percentage_basic') {
                     const val = (basicSalary * calcValue) / 100;
                     input.value = val.toFixed(2);
                 } else if (calcType === 'percentage_gross') { // If we supported this
                      const val = (grossSalary * calcValue) / 100;
                      input.value = val.toFixed(2);
                 } else if (calcType === 'flat_amount') {
                     const val = calcValue;
                     if(val > 0) input.value = val.toFixed(2);
                 }
            });
        }
    }

    // Initialize EPF fields on page load
    document.addEventListener('DOMContentLoaded', function() {
        // Find all EPF option dropdowns and initialize them
        const epfSelects = document.querySelectorAll('select[name*="epf_option"]');
        epfSelects.forEach(function(select) {
            const indexMatch = select.id.match(/epf_option_(\d+)/);
            if (indexMatch) {
                toggleEPFValueField(indexMatch[1]);
            }
        });

        // Hook into Main Calc Button from Salary Tab (since it triggers updateStatutoryMirrors)
        const calculateBtn = document.getElementById('btn_calculate_ctc');
        if(calculateBtn) {
            // We append our listener to the existing one
            calculateBtn.addEventListener('click', function() {
                // Wait for salary calc to finish (synchronous usually, but good to be safe with timing)
                setTimeout(() => {
                    if(window.calculateStatutoryComponents) window.calculateStatutoryComponents();
                    if(window.calculateNetPayable) window.calculateNetPayable(); // Recalc net after statutory changes
                }, 100);
            });
        }
    });
</script>