<div class="p-3" id="statutoryComponents">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Statutory Components</h5>
        <div class="toggle-container">
            <button type="button" class="btn btn-outline-primary" id="selectAllToggle" onclick="toggleAllStatutoryComponents(this)">
                Enable All
            </button>
        </div>
    </div>

    @foreach($statutoryComponents as $index => $component)
        @php
            $isChecked = old("statutory_components.{$index}.enabled");
            $value = old("statutory_components.{$index}.value");
        @endphp

        <div class="card mb-3 shadow-sm statutory-card">
            <div class="card-body">
                <div class="d-flex justify-content-between mb-3">
                    <label class="fw-semibold" for="toggle_{{ $index }}">
                        {{ $component->short_name }}
                    </label>
                    <div class="toggle-button-container">
                        <button type="button" 
                               class="btn {{ $isChecked ? 'btn-success' : 'btn-outline-secondary' }} toggle-button"
                               id="toggle_{{ $index }}"
                               data-index="{{ $index }}"
                               data-status="{{ $isChecked ? 'on' : 'off' }}"
                               onclick="toggleStatutoryRow({{ $index }})">
                            {{ $isChecked ? 'Enabled' : 'Disabled' }}
                        </button>
                        <input type="hidden"
                               class="component-toggle-value"
                               id="checkbox_{{ $index }}"
                               name="statutory_components[{{ $index }}][enabled]"
                               value="{{ $isChecked ? '1' : '0' }}">
                    </div>
                </div>

                <div id="row_{{ $index }}" class="row g-3 align-items-center" style="{{ $isChecked ? '' : 'display: none;' }}">
                    <input type="hidden" name="statutory_components[{{ $index }}][statutory_component_id]" value="{{ $component->id }}">

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Component</label>
                        <input type="text" class="form-control bg-light" value="{{ $component->name }}" readonly>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Value (₹)</label>
                        <input type="number"
                               name="statutory_components[{{ $index }}][value]"
                               class="form-control component-value @error("statutory_components.{$index}.value") is-invalid @enderror"
                               placeholder="Enter value"
                               value="{{ $value }}">
                        @error("statutory_components.{$index}.value")
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<script>
    let allEnabled = false;

    function toggleAllStatutoryComponents(button) {
        // Toggle the state
        allEnabled = !allEnabled;
        
        // Update button text and style
        if (allEnabled) {
            button.classList.remove('btn-outline-primary');
            button.classList.add('btn-primary');
            button.innerText = 'Disable All';
        } else {
            button.classList.remove('btn-primary');
            button.classList.add('btn-outline-primary');
            button.innerText = 'Enable All';
        }

        // Update all toggle buttons
        const toggleButtons = document.querySelectorAll('.toggle-button');
        const hiddenInputs = document.querySelectorAll('.component-toggle-value');

        toggleButtons.forEach((button, i) => {
            const row = document.getElementById('row_' + i);
            const valueInput = row.querySelector('.component-value');
            const hiddenInput = document.getElementById('checkbox_' + i);

            // Update button appearance
            if (allEnabled) {
                button.classList.remove('btn-outline-secondary');
                button.classList.add('btn-success');
                button.innerText = 'Enabled';
                button.dataset.status = 'on';
                hiddenInput.value = '1';
            } else {
                button.classList.remove('btn-success');
                button.classList.add('btn-outline-secondary');
                button.innerText = 'Disabled';
                button.dataset.status = 'off';
                hiddenInput.value = '0';
            }

            // Show/hide the row
            if (allEnabled) {
                row.style.display = 'flex';
                valueInput.removeAttribute('readonly');
            } else {
                row.style.display = 'none';
                valueInput.value = '';
                valueInput.setAttribute('readonly', 'readonly');
            }
        });
    }

    function toggleStatutoryRow(index) {
        const toggleButton = document.getElementById('toggle_' + index);
        const hiddenInput = document.getElementById('checkbox_' + index);
        const row = document.getElementById('row_' + index);
        const valueInput = row.querySelector('.component-value');
        
        // Get current status and toggle it
        const currentStatus = toggleButton.dataset.status;
        const newStatus = currentStatus === 'on' ? 'off' : 'on';
        
        // Update button appearance
        if (newStatus === 'on') {
            toggleButton.classList.remove('btn-outline-secondary');
            toggleButton.classList.add('btn-success');
            toggleButton.innerText = 'Enabled';
            hiddenInput.value = '1';
        } else {
            toggleButton.classList.remove('btn-success');
            toggleButton.classList.add('btn-outline-secondary');
            toggleButton.innerText = 'Disabled';
            hiddenInput.value = '0';
        }
        
        // Update data attribute
        toggleButton.dataset.status = newStatus;
        
        // Show/hide the row
        if (newStatus === 'on') {
            row.style.display = 'flex';
            valueInput.removeAttribute('readonly');
        } else {
            row.style.display = 'none';
            valueInput.value = '';
            valueInput.setAttribute('readonly', 'readonly');
        }
        
        // Check if all buttons are now enabled or disabled to update the main toggle
        updateMainToggleButton();
    }
    
    function updateMainToggleButton() {
        const toggleButtons = document.querySelectorAll('.toggle-button');
        const mainToggle = document.getElementById('selectAllToggle');
        let allOn = true;
        
        toggleButtons.forEach(button => {
            if (button.dataset.status === 'off') {
                allOn = false;
            }
        });
        
        // Update main toggle button based on the state of all individual toggles
        if (allOn) {
            allEnabled = true;
            mainToggle.classList.remove('btn-outline-primary');
            mainToggle.classList.add('btn-primary');
            mainToggle.innerText = 'Disable All';
        } else {
            allEnabled = false;
            mainToggle.classList.remove('btn-primary');
            mainToggle.classList.add('btn-outline-primary');
            mainToggle.innerText = 'Enable All';
        }
    }
    
    // Initialize the main toggle button based on initial state
    document.addEventListener('DOMContentLoaded', function() {
        updateMainToggleButton();
    });
</script>