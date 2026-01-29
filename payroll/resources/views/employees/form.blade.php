@extends('layouts.master')

@section('title', isset($employee) ? 'Edit Employee' : 'Add Employee')

@section('content')

<style>

    /* Odoo-like styling */

.card {

    border: 1px solid #dee2e6;

    border-radius: 0.25rem;

    margin-bottom: 1rem;

}



.card-header {

    background-color: #f8f9fa;

    border-bottom: 1px solid #dee2e6;

    padding: 0.75rem 1.25rem;

}



.form-control {

    border-radius: 0.2rem;

    border: 1px solid #ced4da;

}



.btn {

    border-radius: 0.2rem;

}



.nav-tabs .nav-link {

    border: 1px solid transparent;

    border-top-left-radius: 0.25rem;

    border-top-right-radius: 0.25rem;

}



.nav-tabs .nav-link.active {

    background-color: #fff;

    border-color: #dee2e6 #dee2e6 #fff;

}

    </style>
    <style>
        /* Select2 Styling to match form-control */
        .select2-container .select2-selection--single {
            height: 44px !important; /* Match typical form-control height */
            border: 1px solid #ced4da !important;
            border-radius: 0.2rem !important;
            padding: 0.375rem 0.75rem;
            display: flex;
            align-items: center;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #495057;
            line-height: normal;
            padding-left: 0;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 42px !important;
            right: 5px;
        }
        
        /* Fix interaction with invalid-feedback */
        .select2-container.is-invalid + .invalid-feedback {
            display: block;
        }

    /* Action buttons container */
    .action-buttons-container { 
        position: sticky; 
        bottom: 0; 
        background: white; 
        padding: 1rem 1.25rem; 
        border-top: 1px solid #e5e7eb; 
        box-shadow: 0 -2px 10px rgba(0,0,0,0.05);
        margin-top: 1.5rem;
        z-index: 10;
    }

    /* Button Styles */
    .btn-modern { padding: 0.75rem 2rem; border-radius: 0.5rem; font-weight: 500; font-size: 1rem; border: none; cursor: pointer; transition: all 0.2s; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; }
    .btn-modern-primary { background: linear-gradient(135deg,#667eea 0%,#764ba2 100%); color: white; }
    .btn-modern-primary:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4); color: white;}
    .btn-modern-secondary { background: linear-gradient(135deg,#6c757d 0%,#495057 100%); color: white; }
    .btn-modern-secondary:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(108, 117, 125, 0.4); color: white;}
    .btn-modern-success { background: linear-gradient(135deg,#10b981 0%,#059669 100%); color: white; }
    .btn-modern-success:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4); color: white;}
    .btn-modern-info { background: linear-gradient(135deg, #0dcaf0 0%, #0aa2c0 100%); color: white; } /* Cyan/Info Theme */
    .btn-modern-info:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(13, 202, 240, 0.4); color: white;}
    .btn-modern-light { background: #f8f9fa; color: #6b7280; border: 1px solid #e5e7eb; }
    .btn-modern-light:hover { background: #e9ecef; }
    .btn-modern-warning { background: linear-gradient(135deg,#f59e0b 0%,#d97706 100%); color: white; }
    .btn-modern-warning:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(245, 158, 11, 0.4); color: white;}


    @media (max-width: 768px) {
        .action-buttons-container { padding: 0.875rem 1rem; }
        .action-buttons-container .btn-modern { padding: 0.625rem 1.25rem; font-size: 0.875rem; }
    }
    </style>

<div class="page-wrapper">

    <div class="content container-fluid">

        <div class="page-header">

            <h2>{{ isset($employee) ? 'Edit' : 'Create' }} Employee</h2>

            

            <form method="POST" novalidate id="employeeForm"
                action="{{ isset($employee) ? route('employees.update', $employee->id) : route('employees.save') }}" enctype="multipart/form-data">

                @csrf

                @isset($employee) @method('PUT') @endisset

                <!-- BASIC DETAILS ABOVE THE TABS --->

                    <div class="" id="">

                        @include('employees.tabs.basic')

                    </div>



                <ul class="nav nav-tabs" id="employeeTabs" role="tablist">

                    {{-- <li class="nav-item">

                        <a class="nav-link active" id="basic-tab" data-toggle="tab" href="#basic">Basic Details</a>

                    </li> --}}

                    <li class="nav-item">

                        <a class="nav-link" id="personal-tab" data-toggle="tab" href="#personal">Personal Details</a>

                    </li>

                    <li class="nav-item">

                        <a class="nav-link" id="bank-tab" data-toggle="tab" href="#bank">Bank Details</a>

                    </li>                    

                    <li class="nav-item">

                        <a class="nav-link" id="salary-tab" data-toggle="tab" href="#salary">Salary Components</a>

                    </li>



                    <li class="nav-item">

                        <a class="nav-link" id="leave-allocations-tab" data-toggle="tab" href="#leave-allocations">Leave Allocations</a>

                    </li>

                    <li class="nav-item">

                        <a class="nav-link" id="ot-incentive-tab" data-toggle="tab" href="#ot-incentive">OT & Incentives</a>

                    </li>

                    
                    <li class="nav-item">
                        <a class="nav-link" id="permissions-tab" data-toggle="tab" href="#permissions">Permissions</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" id="advances-tab" data-toggle="tab" href="#advances">Advances</a>
                    </li>

                </ul>



                



                <div class="tab-content" id="employeeTabsContent">

                    

                    <!-- Basic Details Tab -->

                    {{-- <div class="tab-pane fade show active" id="basic">

                        @include('employees.tabs.basic')

                    </div> --}}



                    <!-- Personal Details Tab -->

                    <div class="tab-pane fade" id="personal">

                        @include('employees.tabs.personal')

                    </div>



                    <!-- Bank Details Tab -->

                    <div class="tab-pane fade" id="bank">

                        @include('employees.tabs.bank')

                    </div>



                    <!-- Salary Components Tab -->

                    <!-- Salary Components Tab -->
                    <div class="tab-pane fade" id="salary">
                        @include('employees.tabs.salary')
                    </div>

                    <!-- Leave Allocations Tab -->
                    <div class="tab-pane fade" id="leave-allocations">
                        @include('employees.tabs.leave-allocations')
                    </div>

                    <!-- OT and Other Incentive Tab -->
                    <div class="tab-pane fade" id="ot-incentive">
                        @include('employees.tabs.ot-incentive')
                    </div>
                   

                    <!-- Permissions Tab -->
                    <div class="tab-pane fade" id="permissions">
                        @include('employees.tabs.permissions_debug')
                    </div>

                    <!-- Advances Tab -->
                    <div class="tab-pane fade" id="advances">
                        @if(isset($employee))
                            @include('employees.tabs.advances', ['employee' => $employee])
                        @else
                            <div class="alert alert-info m-3">
                                <i class="fa fa-info-circle"></i> Please create/save the employee first to manage advances.
                            </div>
                        @endif
                    </div>

                    

                </div>



                <div class="action-buttons-container">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                         @if(isset($employee))
                            @if (Auth::user()->hasPermission('employees.joining_letter'))                 
                            <a class="btn-modern btn-modern-info me-2" href="{{ route('employee.joining-letter', $employee->id) }}" target="_blank" ><i class="fa fa-file-alt me-2"></i> Joining Letter</a>
                            @endif
                            @if (Auth::user()->hasPermission('employees.offer_letter'))
                            <a class="btn-modern btn-modern-warning me-2" href="{{ route('employee.offer-letter', $employee->id) }}" target="_blank" ><i class="fa fa-file-contract me-2"></i> Offer Letter</a>
                            @endif
                            @if($employee->status == 3)
                                @if (Auth::user()->hasPermission('employees.experience_letter'))    
                                <a class="btn-modern btn-modern-secondary me-2" href="{{ route('employee.experience-letter', $employee->id) }}" target="_blank" ><i class="fa fa-certificate me-2"></i> Experience Letter </a>
                                @endif
                            @endif
                        @endif
                        </div>
                        <button type="submit" class="btn-modern btn-modern-primary">
                            <i class="fa fa-save me-2"></i> {{ isset($employee) ? 'Update' : 'Create' }} Employee
                        </button>
                    </div>
                </div>

            </form>
            @stack('advance_modals')
        </div>

    </div>

</div>



<script>

    document.addEventListener('DOMContentLoaded', function() {
        
        // Custom Form Validation
        const form = document.querySelector('form');
        if (form) {
            form.addEventListener('submit', function(event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                    
                    form.classList.add('was-validated');
                    
                    // Find first invalid element
                    const firstInvalid = form.querySelector(':invalid');
                    if (firstInvalid) {
                        // Check if it's in a tab
                        const tabPane = firstInvalid.closest('.tab-pane');
                        if (tabPane) {
                            const tabId = tabPane.id;
                            const tabLink = document.querySelector(`a[href="#${tabId}"]`);
                            if (tabLink) {
                                try {
                                    $(tabLink).tab('show');
                                } catch(e) {
                                    tabLink.click();
                                }
                            }
                        }
                        
                        // Scroll to it
                        setTimeout(() => {
                            firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            firstInvalid.focus();
                        }, 100);
                    }
                }
            });
        }



        // Initialize Bootstrap tabs with FA4 compatibility
    $('#employeeTabs a').click(function(e) {

        e.preventDefault();

        $(this).tab('show');

    });



    // Show first tab by default

    $('#employeeTabs a:first').tab('show');

        // Initialize select2 for reporting manager dropdown
        $('.select2').select2({
            placeholder: "Select a reporting manager",
            allowClear: true,
            width: '100%'
        });

        // Handle tab switching and error focus

        @if($errors->any())

            let firstError = document.querySelector('.is-invalid');

            if (firstError) {

                let tabPane = firstError.closest('.tab-pane');

                if (tabPane) {

                    let tabLink = document.querySelector(`a[href="#${tabPane.id}"]`);

                    tabLink.click();

                }

            }

        @endif

    

        // Add dynamic rows for components

        function addComponentRow(containerId, templateId) {

            const container = document.querySelector(`#${containerId}`);

            const template = document.querySelector(`#${templateId}`);

            const clone = template.content.cloneNode(true);

            const index = container.children.length;

            

            clone.querySelectorAll('input, select').forEach(element => {

                const name = element.getAttribute('name').replace('0', index);

                element.setAttribute('name', name);

            });

            

            container.appendChild(clone);

        }

    

        window.addStatutoryRow = () => addComponentRow('statutoryComponents', 'statutoryTemplate');

        window.addSalaryRow = () => addComponentRow('salaryComponents', 'salaryTemplate');

        // Leave Allocation Management
        initializeLeaveAllocationManagement();
    });

    // Leave Allocation Management Functions
    function initializeLeaveAllocationManagement() {
        // Set financial year on page load
        @if(isset($currentFinancialYear) && $currentFinancialYear)
            document.getElementById('financial-year-badge').textContent = '{{ $currentFinancialYear }}';
            document.getElementById('selected-financial-year').textContent = '{{ $currentFinancialYear }}';
        @endif

        // Watch for department and joining date changes
        const departmentSelect = document.querySelector('select[name="basic[department]"]');
        const joiningDateInput = document.querySelector('input[name="basic[date_of_joining]"]');

        if (departmentSelect && joiningDateInput) {
            departmentSelect.addEventListener('change', function() {
                window.userTriggeredChange = true;
                checkAndLoadLeaveTypes();
            });
            joiningDateInput.addEventListener('change', function() {
                window.userTriggeredChange = true;
                checkAndLoadLeaveTypes();
            });
        }

        // Sync button click handler
        document.getElementById('sync-leave-types')?.addEventListener('click', syncLeaveTypes);
        
        // Test API button click handler
        document.getElementById('test-api-connection')?.addEventListener('click', testAPIConnection);

        // Load existing leave allocations if in edit mode
        @if(isset($employee) && isset($existingLeaveAllocations) && !empty($existingLeaveAllocations))
            loadExistingLeaveAllocations(@json($existingLeaveAllocations));
        @endif

        // Initial check when page loads
        checkAndLoadLeaveTypes();
    }

    function checkAndLoadLeaveTypes() {
        const departmentSelect = document.querySelector('select[name="basic[department]"]');
        const joiningDateInput = document.querySelector('input[name="basic[date_of_joining]"]');
        
        const departmentId = departmentSelect?.value;
        const joiningDate = joiningDateInput?.value;

        updateDepartmentInfo(departmentId, joiningDate);

        // Check if we're in edit mode and already have existing allocations
        // AND the department/joining date haven't changed from what was loaded
        const hasExistingAllocations = document.getElementById('leave-allocations-container').style.display === 'block' &&
                                     document.getElementById('leave-allocations-tbody').children.length > 0;
        
        // Check if this is the initial load with existing data (skip API call)
        // vs. a user-initiated change (should trigger API call)
        const isInitialLoad = hasExistingAllocations && !window.userTriggeredChange;
        
        if (isInitialLoad) {
            return;
        }

        // Reset the flag after use
        window.userTriggeredChange = false;

        if (departmentId && joiningDate) {
            loadLeaveTypesForDepartment(departmentId, joiningDate);
        } else {
            showNoDepartmentSelected();
        }
    }

    function updateDepartmentInfo(departmentId, joiningDate) {
        const departmentSelect = document.querySelector('select[name="basic[department]"]');
        const departmentName = departmentSelect?.options[departmentSelect.selectedIndex]?.text || '-';
        
        document.getElementById('selected-department-name').textContent = departmentName;
        document.getElementById('selected-joining-date').textContent = joiningDate || '-';
    }

    function loadLeaveTypesForDepartment(departmentId, joiningDate) {
        showLoading();

        fetch('{{ route("employees.leave-types.department") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                department_id: parseInt(departmentId),
                joining_date: joiningDate
            })
        })
        .then(response => response.json())
        .then(data => {
            hideLoading();
            
            if (data.success) {
                if (data.data.leave_types.length > 0) {
                    displayLeaveTypes(data.data);
                } else {
                    showNoLeaveTypes();
                }
            } else {
                showError(data.message || 'Failed to load leave types');
            }
        })
        .catch(error => {
            hideLoading();
            showError('Error loading leave types: ' + error.message);
        });
    }

    function displayLeaveTypes(data) {
        console.log('Displaying leave types data:', data);
        console.log('Pro-rating required:', data.pro_rating_required);
        
        hideAllStates();
        document.getElementById('leave-allocations-container').style.display = 'block';
        
        // Update financial year badge and info
        document.getElementById('financial-year-badge').textContent = data.financial_year;
        document.getElementById('selected-financial-year').textContent = data.financial_year;

        // Show pro-rating summary
        if (data.pro_rating_summary) {
            updateProRatingSummary(data.pro_rating_summary);
        }

        // Display leave types in table
        const tbody = document.getElementById('leave-allocations-tbody');
        tbody.innerHTML = '';

        let totalOriginal = 0, totalAllocated = 0, totalFinal = 0, totalOverride = 0;

        data.leave_types.forEach((leaveType, index) => {
            totalOriginal += parseFloat(leaveType.original_days);
            totalAllocated += parseFloat(leaveType.allocated_days);
            totalFinal += parseFloat(leaveType.effective_days);

            const row = createLeaveTypeRow(leaveType, index);
            tbody.appendChild(row);
        });

        // Update totals
        document.getElementById('total-original-days').textContent = totalOriginal.toFixed(2);
        document.getElementById('total-allocated-days').textContent = totalAllocated.toFixed(2);
        document.getElementById('total-final-days').textContent = totalFinal.toFixed(2);
        document.getElementById('total-override-days').textContent = totalOverride.toFixed(2);

        // Show pro-rating explanation if needed
        if (data.pro_rating_required) {
            showProRatingExplanation(data.leave_types[0]?.pro_rating_details);
        }

        // Update hidden input with data
        updateLeaveAllocationsData(data.leave_types);
    }

    function createLeaveTypeRow(leaveType, index) {
        console.log(`Creating row for ${leaveType.leave_type_name}:`, {
            original_days: leaveType.original_days,
            allocated_days: leaveType.allocated_days,
            is_pro_rated: leaveType.is_pro_rated,
            pro_rated_factor: leaveType.pro_rated_factor
        });
        
        const row = document.createElement('tr');
        row.className = 'leave-allocation-row';
        
        const isProRated = leaveType.is_pro_rated;
        const proRatedBadge = isProRated ? '<span class="badge bg-warning text-dark pro-rated-badge ms-1">Pro-rated</span>' : '';
        
        row.innerHTML = `
            <td>
                <strong>${leaveType.leave_type_name}</strong>
                ${proRatedBadge}
                <br>
                <small class="text-muted">${leaveType.description || ''}</small>
            </td>
            <td>
                <span class="badge bg-secondary">${leaveType.leave_type_code}</span>
            </td>
            <td>
                ${isProRated ? `<span class="original-days">${leaveType.original_days}</span>` : leaveType.original_days}
            </td>
            <td>
                <span class="allocated-days">${leaveType.allocated_days}</span>
            </td>
            <td>
                <div class="form-check">
                    <input type="checkbox" class="form-check-input override-toggle" 
                           id="override_${index}" data-index="${index}"
                           ${leaveType.is_manual_override ? 'checked' : ''}>
                    <label class="form-check-label" for="override_${index}">Enable</label>
                </div>
            </td>
            <td>
                <input type="number" step="0.5" min="0" max="365" 
                       class="form-control override-days-input" 
                       id="override_days_${index}" data-index="${index}"
                       value="${(leaveType.override_days !== null && leaveType.override_days !== undefined) ? leaveType.override_days : ''}"
                       ${!leaveType.is_manual_override ? 'disabled' : ''}>
            </td>
            <td>
                <span class="final-days ${leaveType.is_manual_override ? 'manual-override' : ''}" 
                      id="final_days_${index}">${leaveType.effective_days}</span>
            </td>
        `;

        // Add event listeners
        const overrideToggle = row.querySelector('.override-toggle');
        const overrideDaysInput = row.querySelector('.override-days-input');

        overrideToggle.addEventListener('change', function() {
            toggleOverride(index, this.checked);
        });

        overrideDaysInput.addEventListener('input', function() {
            updateOverrideDays(index, this.value);
        });

        return row;
    }

    function toggleOverride(index, enabled) {
        const overrideDaysInput = document.getElementById(`override_days_${index}`);
        const finalDaysSpan = document.getElementById(`final_days_${index}`);
        
        overrideDaysInput.disabled = !enabled;
        
        if (enabled) {
            overrideDaysInput.focus();
            finalDaysSpan.classList.add('manual-override');
        } else {
            overrideDaysInput.value = '';
            finalDaysSpan.classList.remove('manual-override');
            // Reset to allocated days
            const allocatedDays = getCurrentLeaveTypeData(index)?.allocated_days || 0;
            finalDaysSpan.textContent = allocatedDays;
        }

        updateLeaveAllocationsFromForm();
        updateTotals();
    }

    function updateOverrideDays(index, value) {
        const finalDaysSpan = document.getElementById(`final_days_${index}`);
        const allocatedDays = getCurrentLeaveTypeData(index)?.allocated_days || 0;
        
        if (value !== '' && value !== null && !isNaN(value) && parseFloat(value) >= 0) {
            finalDaysSpan.textContent = parseFloat(value).toFixed(2);
            finalDaysSpan.classList.add('manual-override');
        } else {
            finalDaysSpan.textContent = allocatedDays;
            finalDaysSpan.classList.remove('manual-override');
        }

        updateLeaveAllocationsFromForm();
        updateTotals();
    }

    function updateTotals() {
        let totalOriginal = 0, totalAllocated = 0, totalFinal = 0, totalOverride = 0;

        document.querySelectorAll('.leave-allocation-row').forEach((row, index) => {
            const leaveType = getCurrentLeaveTypeData(index);
            if (leaveType) {
                totalOriginal += parseFloat(leaveType.original_days);
                totalAllocated += parseFloat(leaveType.allocated_days);
                
                const finalDaysSpan = document.getElementById(`final_days_${index}`);
                const finalDays = parseFloat(finalDaysSpan.textContent);
                totalFinal += finalDays;

                const overrideDaysInput = document.getElementById(`override_days_${index}`);
                if (overrideDaysInput.value) {
                    totalOverride += parseFloat(overrideDaysInput.value);
                }
            }
        });

        document.getElementById('total-original-days').textContent = totalOriginal.toFixed(2);
        document.getElementById('total-allocated-days').textContent = totalAllocated.toFixed(2);
        document.getElementById('total-final-days').textContent = totalFinal.toFixed(2);
        document.getElementById('total-override-days').textContent = totalOverride.toFixed(2);
    }

    function getCurrentLeaveTypeData(index) {
        const hiddenInput = document.getElementById('leave-allocations-data');
        try {
            const data = JSON.parse(hiddenInput.value || '[]');
            return data[index];
        } catch (e) {
            return null;
        }
    }

    function updateLeaveAllocationsFromForm() {
        const leaveTypes = [];
        
        document.querySelectorAll('.leave-allocation-row').forEach((row, index) => {
            const originalData = getCurrentLeaveTypeData(index);
            if (!originalData) return;

            const overrideToggle = document.getElementById(`override_${index}`);
            const overrideDaysInput = document.getElementById(`override_days_${index}`);
            const finalDaysSpan = document.getElementById(`final_days_${index}`);

            const leaveType = {
                ...originalData,
                is_manual_override: overrideToggle.checked,
                override_days: overrideToggle.checked && overrideDaysInput.value ? parseFloat(overrideDaysInput.value) : null,
                effective_days: parseFloat(finalDaysSpan.textContent)
            };

            leaveTypes.push(leaveType);
        });

        updateLeaveAllocationsData(leaveTypes);
    }

    function updateLeaveAllocationsData(leaveTypes) {
        const hiddenInput = document.getElementById('leave-allocations-data');
        hiddenInput.value = JSON.stringify(leaveTypes);
    }

    function updateProRatingSummary(summary) {
        document.getElementById('pro-rating-summary').style.display = 'block';
        document.getElementById('summary-leave-types').textContent = summary.total_leave_types;
        document.getElementById('summary-original-days').textContent = summary.total_original_days.toFixed(2);
        document.getElementById('summary-allocated-days').textContent = summary.total_allocated_days.toFixed(2);
        document.getElementById('summary-pro-rating-factor').textContent = 
            summary.average_pro_rating_factor ? (summary.average_pro_rating_factor * 100).toFixed(1) + '%' : '100%';
    }

    function showProRatingExplanation(proRatingDetails) {
        if (!proRatingDetails) return;

        document.getElementById('pro-rating-explanation').style.display = 'block';
        document.getElementById('pro-rating-joining-date').textContent = proRatingDetails.joining_date;
        document.getElementById('pro-rating-fy-start').textContent = proRatingDetails.fy_start_date;
        document.getElementById('pro-rating-fy-end').textContent = proRatingDetails.fy_end_date;
        document.getElementById('pro-rating-remaining-months').textContent = proRatingDetails.remaining_months;
        document.getElementById('pro-rating-total-months').textContent = proRatingDetails.total_months;
        document.getElementById('pro-rating-percentage').textContent = (proRatingDetails.factor * 100).toFixed(1);
    }

    function syncLeaveTypes() {
        const button = document.getElementById('sync-leave-types');
        const originalText = button.innerHTML;
        
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Syncing...';
        button.disabled = true;

        fetch('{{ route("employees.leave-types.sync") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showSuccessMessage('Leave types synced successfully');
                // Reload leave types for current selection
                checkAndLoadLeaveTypes();
            } else {
                showError(data.message || 'Failed to sync leave types');
            }
        })
        .catch(error => {
            showError('Error syncing leave types: ' + error.message);
        })
        .finally(() => {
            button.innerHTML = originalText;
            button.disabled = false;
        });
    }

    function testAPIConnection() {
        const button = document.getElementById('test-api-connection');
        const originalText = button.innerHTML;
        
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Testing...';
        button.disabled = true;

        fetch('{{ route("employees.leave-types.test-api") }}', {
            method: 'GET'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showSuccessMessage('API connection successful');
            } else {
                showError(data.message || 'API connection failed');
            }
        })
        .catch(error => {
            showError('Error testing API: ' + error.message);
        })
        .finally(() => {
            button.innerHTML = originalText;
            button.disabled = false;
        });
    }

    // UI State Management Functions
    function showLoading() {
        hideAllStates();
        document.getElementById('leave-loading').style.display = 'block';
    }

    function hideLoading() {
        document.getElementById('leave-loading').style.display = 'none';
    }

    function showNoDepartmentSelected() {
        hideAllStates();
        document.getElementById('no-department-selected').style.display = 'block';
    }

    function showNoLeaveTypes() {
        hideAllStates();
        document.getElementById('no-leave-types').style.display = 'block';
    }

    function showError(message) {
        hideAllStates();
        document.getElementById('leave-error-message').textContent = message;
        document.getElementById('leave-error-container').style.display = 'block';
    }

    function hideAllStates() {
        document.getElementById('leave-loading').style.display = 'none';
        document.getElementById('no-department-selected').style.display = 'none';
        document.getElementById('no-leave-types').style.display = 'none';
        document.getElementById('leave-error-container').style.display = 'none';
        document.getElementById('leave-allocations-container').style.display = 'none';
        document.getElementById('pro-rating-explanation').style.display = 'none';
        document.getElementById('pro-rating-summary').style.display = 'none';
    }

    function showSuccessMessage(message) {
        // Create and show a temporary success message
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-success alert-dismissible fade show sync-status';
        alertDiv.innerHTML = `
            <i class="fas fa-check-circle"></i> ${message}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        `;
        
        document.querySelector('.card-body').appendChild(alertDiv);
        
        // Auto-remove after 3 seconds
        setTimeout(() => {
            alertDiv.remove();
        }, 3000);
    }

    // Load existing leave allocations for edit mode
    function loadExistingLeaveAllocations(existingAllocations) {
        if (!existingAllocations || existingAllocations.length === 0) {
            return;
        }

        // Show the allocations container
        document.getElementById('leave-allocations-container').style.display = 'block';
        
        // Process existing allocations and ensure effective_days is correct
        const processedData = existingAllocations.map(existing => {
            // Calculate effective days: override_days if manual override, otherwise allocated_days
            const effectiveDays = existing.is_manual_override && existing.override_days !== null 
                ? existing.override_days 
                : existing.allocated_days;
                
            return {
                ...existing,
                effective_days: effectiveDays,
                // Ensure original_days is set (fallback to allocated_days if not present)
                original_days: existing.original_days || existing.allocated_days
            };
        });
        
        // Update the form with processed data
        updateLeaveAllocationsData(processedData);
        renderLeaveAllocationsTable(processedData);
        
        // Update UI state
        updateTotals();
        showProRatingInfo(processedData);
    }

    function getCurrentLeaveAllocationsData() {
        const hiddenInput = document.getElementById('leave-allocations-data');
        try {
            return JSON.parse(hiddenInput.value || '[]');
        } catch (e) {
            return [];
        }
    }

    function renderLeaveAllocationsTable(leaveTypes) {
        const tbody = document.getElementById('leave-allocations-tbody');
        tbody.innerHTML = '';

        leaveTypes.forEach((leaveType, index) => {
            const row = createLeaveTypeRow(leaveType, index);
            tbody.appendChild(row);
        });

        updateTotals();
    }

    function showProRatingInfo(leaveTypes) {
        if (!leaveTypes || leaveTypes.length === 0) return;
        
        // Check if any leave types are pro-rated
        const proRatedTypes = leaveTypes.filter(lt => lt.is_pro_rated);
        if (proRatedTypes.length > 0) {
            // Show pro-rating summary
            const summary = {
                total_leave_types: leaveTypes.length,
                total_original_days: leaveTypes.reduce((sum, lt) => sum + (parseFloat(lt.original_days) || 0), 0),
                total_allocated_days: leaveTypes.reduce((sum, lt) => sum + (parseFloat(lt.allocated_days) || 0), 0),
                average_pro_rating_factor: proRatedTypes.length > 0 
                    ? proRatedTypes.reduce((sum, lt) => sum + (parseFloat(lt.pro_rated_factor) || 0), 0) / proRatedTypes.length 
                    : 1
            };
            
            updateProRatingSummary(summary);
            
            // Show pro-rating explanation for the first pro-rated type
            if (proRatedTypes[0].pro_rating_details) {
                showProRatingExplanation(proRatedTypes[0].pro_rating_details);
            }
        }
    }

    // Week Off Management Functions
    function initializeWeekOffManagement() {
        // Load existing week off data if in edit mode
        @if(isset($employee) && $employee->weekOff)
            loadExistingWeekOffs(@json($employee->weekOff->week_off_days));
        @endif

        // Set up event listeners for week off checkboxes
        const weekOffCheckboxes = document.querySelectorAll('.week-off-checkbox');
        weekOffCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', updateWeekOffSummary);
        });

        // Initial summary update
        updateWeekOffSummary();
    }

    function loadExistingWeekOffs(weekOffDays) {
        if (!weekOffDays || !Array.isArray(weekOffDays)) {
            return;
        }

        console.log('Loading existing week offs:', weekOffDays);

        // Clear all checkboxes first
        document.querySelectorAll('.week-off-checkbox').forEach(checkbox => {
            checkbox.checked = false;
        });

        // Check the appropriate boxes
        weekOffDays.forEach(dayNumber => {
            const checkbox = document.querySelector(`input[name="week_offs[]"][value="${dayNumber}"]`);
            if (checkbox) {
                checkbox.checked = true;
            }
        });

        updateWeekOffSummary();
    }

    function updateWeekOffSummary() {
        const checkedBoxes = document.querySelectorAll('.week-off-checkbox:checked');
        const selectedDays = Array.from(checkedBoxes).map(cb => parseInt(cb.value));
        const workingDays = 7 - checkedBoxes.length;

        // Update summary display
        document.getElementById('selected-week-offs-count').textContent = checkedBoxes.length;
        document.getElementById('working-days-per-week').textContent = workingDays;

        // Update pattern display
        const dayNames = selectedDays.map(dayNum => getDayName(dayNum));
        const pattern = dayNames.length > 0 ? dayNames.join(', ') : 'None';
        document.getElementById('week-off-pattern').textContent = pattern;

        // Update hidden input
        updateWeekOffData(selectedDays);

        // Validate at least one working day
        validateWeekOffSelection(checkedBoxes.length);
    }

    function getDayName(dayNumber) {
        const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        return days[dayNumber] || 'Unknown';
    }

    function updateWeekOffData(selectedDays) {
        const weekOffData = {
            week_off_days: selectedDays,
            working_days_per_week: 7 - selectedDays.length,
            week_off_pattern: selectedDays.map(day => getDayName(day)).join(', ')
        };

        document.getElementById('week-offs-data').value = JSON.stringify(weekOffData);
    }

    function validateWeekOffSelection(selectedCount) {
        const warningEl = document.querySelector('.week-off-warning');
        
        // Remove existing warning
        if (warningEl) {
            warningEl.remove();
        }

        // Check if all days are selected (no working days)
        if (selectedCount >= 7) {
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-warning mt-2 week-off-warning';
            alertDiv.innerHTML = '<i class="fas fa-exclamation-triangle me-1"></i>Warning: All days are selected as week offs. At least one working day is required.';
            
            document.querySelector('.week-off-checkboxes').parentNode.appendChild(alertDiv);
        }
    }

    // Initialize week off management when page loads
    document.addEventListener('DOMContentLoaded', function() {
        if (document.querySelector('.week-off-checkbox')) {
            initializeWeekOffManagement();
        }
        
        // Initialize advances form
        initializeAdvancesForm();
    });

    // Advances Form Management
    function initializeAdvancesForm() {
        const addAdvanceForm = document.getElementById('addAdvanceForm');
        if (addAdvanceForm && !addAdvanceForm.hasAttribute('data-initialized')) {
            addAdvanceForm.setAttribute('data-initialized', 'true');
            
            addAdvanceForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                
                fetch('{{ route("advance.add") }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Advance added successfully!');
                        // Use Bootstrap 4 modal hide method
                        if (typeof $ !== 'undefined' && $('#addAdvanceModal').length) {
                            $('#addAdvanceModal').modal('hide');
                        } else {
                            // Fallback for vanilla JS
                            const modal = document.getElementById('addAdvanceModal');
                            if (modal) {
                                const backdrop = document.querySelector('.modal-backdrop');
                                modal.style.display = 'none';
                                modal.classList.remove('show');
                                document.body.classList.remove('modal-open');
                                if (backdrop) backdrop.remove();
                            }
                        }
                        location.reload(); // Reload to show new advance
                    } else {
                        alert('Error: ' + (data.message || 'Failed to add advance'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while adding the advance');
                });
            });
        }
    }

    // Edit Advance Function
    function editAdvance(advanceId) {
        // This would open an edit modal - implement as needed
        alert('Edit advance functionality - implement as needed');
    }

    // Close Advance Function
    function closeAdvance(advanceId) {
        if (confirm('Are you sure you want to close this advance?')) {
            fetch(`/advance/${advanceId}/close`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Advance closed successfully!');
                    location.reload();
                } else {
                    alert('Error: ' + (data.message || 'Failed to close advance'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while closing the advance');
            });
        }
    }

    // Prevent Enter key from submitting the form
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Enter') {
            // Allow Enter in textareas
            if (event.target.tagName.toLowerCase() === 'textarea') {
                return;
            }
            
            // Allow Enter on buttons (submit or regular)
            if (event.target.tagName.toLowerCase() === 'button' || (event.target.tagName.toLowerCase() === 'input' && event.target.type === 'submit')) {
                return;
            }
            
            // Prevent default form submission for other inputs (text, number, etc.)
            event.preventDefault();
            return false;
        }
    });

    // --- Advance Calculation Logic (Migrated for Robustness) ---
    function calculateAdvanceDeduction(amountVal, tenureVal, displayElement) {
        console.log('calculateAdvanceDeduction:', amountVal, tenureVal);
        const amount = parseFloat(amountVal) || 0;
        const tenure = parseInt(tenureVal) || 1;
        
        if (tenure > 0) {
            const deduction = amount / tenure;
            if (displayElement.tagName === 'INPUT') {
                displayElement.value = '₹' + deduction.toFixed(2);
            } else {
                displayElement.innerText = '₹' + deduction.toFixed(2);
            }
        } else {
            if (displayElement.tagName === 'INPUT') {
                displayElement.value = '₹0.00';
            } else {
                displayElement.innerText = '₹0.00';
            }
        }
    }
    
    function calculateAdvanceEndDate(startVal, tenureVal, displayElement) {
        console.log('calculateAdvanceEndDate:', startVal, tenureVal);
        const tenure = parseInt(tenureVal) || 1;
        
        if (startVal && tenure > 0) {
            const [year, month] = startVal.split('-').map(Number);
            const startDate = new Date(year, month - 1, 1);
            
            startDate.setMonth(startDate.getMonth() + tenure - 1);
            
            const endMonth = startDate.toLocaleString('default', { month: 'long' }); // Full month name
            const endYear = startDate.getFullYear();
            
            const resultStr = `${endMonth} ${endYear}`;
            
            if (displayElement.tagName === 'INPUT') {
                displayElement.value = resultStr;
            } else {
                displayElement.innerText = resultStr;
            }
        } else {
            if (displayElement.tagName === 'INPUT') {
                displayElement.value = '-';
            } else {
                displayElement.innerText = '-';
            }
        }
    }

    // Global Event Delegation for Advance Modals
    document.body.addEventListener('input', function(e) {
        const target = e.target;
        
        // Add Advance Calculation
        if (target.id === 'add_advance_amount' || target.id === 'add_tenure_months') {
            const amount = document.getElementById('add_advance_amount').value;
            const tenure = document.getElementById('add_tenure_months').value;
            const display = document.getElementById('add_calculated_deduction');
            if(display) calculateAdvanceDeduction(amount, tenure, display);
        }

        if (target.id === 'add_start_date' || target.id === 'add_tenure_months') {
            const start = document.getElementById('add_start_date').value;
            const tenure = document.getElementById('add_tenure_months').value;
            const display = document.getElementById('add_expected_end_date');
            if(display) calculateAdvanceEndDate(start, tenure, display);
        }

        // Edit Advance Calculation
        if (target.id === 'edit_advance_amount' || target.id === 'edit_tenure_months') {
            const amount = document.getElementById('edit_advance_amount').value;
            const tenure = document.getElementById('edit_tenure_months').value;
            const display = document.getElementById('edit_calculated_deduction');
            if(display) calculateAdvanceDeduction(amount, tenure, display);
        }
    });

    </script>

@endsection