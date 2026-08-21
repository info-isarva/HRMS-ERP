<div class="p-3" id="salaryComponents">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Salary Components</h5>
        <div class="form-check form-switch">
            <input type="checkbox" class="form-check-input" id="selectAllCheckboxSalary" onchange="toggleAllSalaryComponents(this)">
            <label class="form-check-label fw-semibold" for="selectAllCheckboxSalary">Enable All</label>
        </div>
    </div>

    @foreach($salaryComponents as $index => $component)
        @php
            $isChecked = old("salary_components.{$index}.enabled");
            $value = old("salary_components.{$index}.value");
        @endphp

        <div class="card mb-3 shadow-sm salary-card">
            <div class="card-body">
                <div class="form-check form-switch mb-3">
                    <input type="checkbox"
                           class="form-check-input component-checkbox-salary"
                           id="salary_checkbox_{{ $index }}"
                           name="salary_components[{{ $index }}][enabled]"
                           value="1" {{ $isChecked ? 'checked' : '' }}
                           onchange="toggleSalaryRow({{ $index }})">
                    <label class="form-check-label fw-semibold" for="salary_checkbox_{{ $index }}">
                        {{ $component->short_name }}
                    </label>
                </div>

                <div id="salary_row_{{ $index }}" class="row g-3 align-items-center" style="{{ $isChecked ? '' : 'display: none;' }}">
                    <input type="hidden" name="salary_components[{{ $index }}][salary_component_id]" value="{{ $component->id }}">

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Component</label>
                        <input type="text" class="form-control bg-light" value="{{ $component->name }}" readonly>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Value (₹)</label>
                        <input type="number"
                               name="salary_components[{{ $index }}][value]"
                               class="form-control component-value-salary @error("salary_components.{$index}.value") is-invalid @enderror"
                               placeholder="Enter value"
                               value="{{ $value }}">
                        @error("salary_components.{$index}.value")
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<script>
    function toggleAllSalaryComponents(mainCheckbox) {
        const checkboxes = document.querySelectorAll('.component-checkbox-salary');

        checkboxes.forEach((checkbox, i) => {
            const row = document.getElementById('salary_row_' + i);
            const valueInput = row.querySelector('.component-value-salary');

            checkbox.checked = mainCheckbox.checked;

            if (mainCheckbox.checked) {
                row.style.display = 'flex';
                valueInput.removeAttribute('readonly');
            } else {
                row.style.display = 'none';
                valueInput.value = ''; // or ''
                valueInput.setAttribute('readonly', 'readonly');
            }
        });
    }

    function toggleSalaryRow(index) {
        const checkbox = document.getElementById('salary_checkbox_' + index);
        const row = document.getElementById('salary_row_' + index);
        const valueInput = row.querySelector('.component-value-salary');

        if (checkbox.checked) {
            row.style.display = 'flex';
            valueInput.removeAttribute('readonly');
        } else {
            row.style.display = 'none';
            valueInput.value = ''; // or ''
            valueInput.setAttribute('readonly', 'readonly');
        }
    }
</script>

