<div class="p-3" id="ot-incentive-tab-content">
    <h5 class="mb-3">OT & Incentives</h5>

    <!-- OT Section -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header">
            <div class="form-check form-switch">
                <input type="hidden" name="basic[ot_status]" value="no">
                <input type="checkbox" class="form-check-input" id="ot_status" name="basic[ot_status]" value="yes" 
                    {{ old('basic.ot_status', $employee->ot_status ?? 'no') == 'yes' ? 'checked' : '' }}
                    onchange="toggleOtInput()">
                <label class="form-check-label fw-semibold" for="ot_status">Enable Overtime (OT)</label>
            </div>
        </div>
        <div class="card-body" id="otInputSection" style="{{ old('basic.ot_status', $employee->ot_status ?? 'no') == 'yes' ? '' : 'display: none;' }}">
            <div class="row">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">OT Per Hour ({{ get_currency_symbol() }})</label>
                    <input type="number" name="basic[ot_per_hour]" class="form-control @error('basic.ot_per_hour') is-invalid @enderror" 
                        value="{{ old('basic.ot_per_hour', $employee->ot_per_hour ?? '') }}"
                        step="0.01" min="0">
                    @error('basic.ot_per_hour')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    <!-- Incentive Section -->
    <div class="card shadow-sm">
        <div class="card-header">
            <div class="form-check form-switch">
                <input type="hidden" name="basic[incentive_status]" value="no">
                <input type="checkbox" class="form-check-input" id="incentive_status" name="basic[incentive_status]" value="yes" 
                    {{ old('basic.incentive_status', $employee->incentive_status ?? 'no') == 'yes' ? 'checked' : '' }}
                    onchange="toggleIncentiveInput()">
                <label class="form-check-label fw-semibold" for="incentive_status">Enable Monthly Incentive</label>
            </div>
        </div>
        <div class="card-body" id="incentiveInputSection" style="{{ old('basic.incentive_status', $employee->incentive_status ?? 'no') == 'yes' ? '' : 'display: none;' }}">
            <div class="row">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Incentive Amount ({{ get_currency_symbol() }})</label>
                    <input type="number" name="basic[incentive_per_month]" class="form-control @error('basic.incentive_per_month') is-invalid @enderror" 
                        value="{{ old('basic.incentive_per_month', $employee->incentive_per_month ?? '') }}"
                        step="0.01" min="0">
                    @error('basic.incentive_per_month')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Robust Toggle Switch Styles - SCOPED to ot-incentive tab */
#ot-incentive-tab-content .form-switch .form-check-input[type="checkbox"] {
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
#ot-incentive-tab-content .form-switch .form-check-input[type="checkbox"]::after {
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
#ot-incentive-tab-content .form-switch .form-check-input[type="checkbox"]:checked {
    background-color: #0d6efd !important; /* Active Blue */
    border-color: #0d6efd !important;
}

/* Move Knob when Checked */
#ot-incentive-tab-content .form-switch .form-check-input[type="checkbox"]:checked::after {
    left: 22px !important; /* Move to the right */
    background-color: #ffffff !important;
}

/* Hover State */
#ot-incentive-tab-content .form-switch .form-check-input[type="checkbox"]:hover {
    background-color: #c9cbcd !important;
}
#ot-incentive-tab-content .form-switch .form-check-input[type="checkbox"]:checked:hover {
    background-color: #dc3545 !important; /* Hover on Active is Red */
    border-color: #dc3545 !important;
}

/* Focus State */
#ot-incentive-tab-content .form-switch .form-check-input[type="checkbox"]:focus {
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25); /* Blue Focus */
    outline: none;
}

/* Label Alignment */
#ot-incentive-tab-content label.form-check-label {
    margin-left: 8px;
    vertical-align: top;
    line-height: 24px; /* Align text with the toggle */
}
</style>

<script>
    function toggleOtInput() {
        const otCheckbox = document.getElementById('ot_status');
        const otSection = document.getElementById('otInputSection');
        if (otCheckbox.checked) {
            otSection.style.display = '';
        } else {
            otSection.style.display = 'none';
            document.querySelector('input[name="basic[ot_per_hour]"]').value = '';
        }
    }

    function toggleIncentiveInput() {
        const incentiveCheckbox = document.getElementById('incentive_status');
        const incentiveSection = document.getElementById('incentiveInputSection');
        if (incentiveCheckbox.checked) {
            incentiveSection.style.display = '';
        } else {
            incentiveSection.style.display = 'none';
            document.querySelector('input[name="basic[incentive_per_month]"]').value = '';
        }
    }
</script>