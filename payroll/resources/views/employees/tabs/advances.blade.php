@php
    $employee = $employee ?? null; // Ensure employee variable is available
    
    // Calculate minimum allowed month based on completed payrolls
    $minAllowedMonth = date('Y-m'); // Default to current month
    $latestCompleted = \App\Models\EmployeePayrollAttendancePayoutMonthStatus::where('status', 'completed')
        ->orderByDesc('payout_year')
        ->orderByDesc('payout_month')
        ->first();
        
    if ($latestCompleted) {
        $lastCompletedDate = \Carbon\Carbon::createFromDate($latestCompleted->payout_year, $latestCompleted->payout_month, 1);
        $minAllowedMonth = $lastCompletedDate->addMonth()->format('Y-m');
    }
@endphp

@if($employee)
    <div class="row">
    <div class="col-md-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                <h5 class="card-title mb-0 font-weight-bold text-dark">Employee Advances</h5>
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addAdvanceModal">
                    <i class="fa fa-plus-circle mr-1"></i> Add Advance
                </button>
            </div>
            <div class="card-body p-0">
                @if(isset($employee) && $employee->advances && $employee->advances->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover table-center mb-0" id="advancesTable">
                            <thead class="thead-light">
                                <tr>
                                    <th>Start Date</th>
                                    <th>Tenure</th>
                                    <th>Advance Amount</th>
                                    <th>Monthly Deduction</th>
                                    <th>Paid / Remaining</th>
                                    <th>Status</th>
                                    <th class="text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($employee->advances as $advance)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($advance->start_date)->format('M Y') }}</td>
                                    <td>{{ $advance->tenure_months }} Months</td>
                                    <td>₹{{ number_format($advance->advance_amount, 2) }}</td>
                                    <td>₹{{ number_format($advance->monthly_deduction, 2) }}</td>
                                    <td>
                                        <div class="d-flex flex-column small">
                                            <span class="text-success">Paid: ₹{{ number_format($advance->total_deducted ?? 0, 2) }}</span>
                                            <span class="text-danger">Left: ₹{{ number_format($advance->remaining_amount ?? 0, 2) }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        @if($advance->status == 'active')
                                            <span class="badge badge-success px-2 py-1">Active</span>
                                        @elseif($advance->status == 'completed')
                                            <span class="badge badge-info px-2 py-1">Completed</span>
                                        @elseif($advance->status == 'cancelled')
                                            <span class="badge badge-secondary px-2 py-1">Cancelled</span>
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        <div class="dropdown dropdown-action">
                                            <a href="#" class="action-icon dropdown-toggle" data-toggle="dropdown" aria-expanded="false"><i class="material-icons">more_vert</i></a>
                                            <div class="dropdown-menu dropdown-menu-right">
                                                <a class="dropdown-item" href="#" onclick="viewAdvanceDetails({{ $advance->id }})"><i class="fa fa-eye m-r-5"></i> View Details</a>
                                                @if($advance->status == 'active')
                                                    <a class="dropdown-item" href="#" onclick="editAdvance({{ $advance->id }})"><i class="fa fa-pencil m-r-5"></i> Edit</a>
                                                    <a class="dropdown-item text-danger" href="#" onclick="closeAdvance({{ $advance->id }})"><i class="fa fa-ban m-r-5"></i> Stop/Cancel</a>
                                                @endif
                                                <a class="dropdown-item" href="#" onclick="deleteAdvance({{ $advance->id }})"><i class="fa fa-trash-o m-r-5"></i> Delete</a>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <div class="mb-3">
                            <i class="fa fa-money-bill-alt fa-3x text-muted opacity-50"></i>
                        </div>
                        <h5 class="text-muted">No Advances Found</h5>
                        <p class="text-muted small mb-0">This employee has no active or past advance records.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('advance_modals')
{{-- Add Advance Modal --}}
<div class="modal fade" id="addAdvanceModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0">
            <div class="modal-header text-white" style="background: linear-gradient(to right, #6a11cb 0%, #2575fc 100%);">
                <h5 class="modal-title font-weight-bold">Manage Advances - {{ $employee->name ?? 'Employee' }}</h5>
                <button type="button" class="close text-white opacity-1" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="addAdvanceForm">
                <div class="modal-body p-4">
                    <div class="alert alert-danger d-none" id="addAdvanceError"></div>
                    @csrf
                    <input type="hidden" name="employee_id" value="{{ $employee->id ?? '' }}">
                    
                    <h6 class="text-primary font-weight-bold mb-3"><i class="fa fa-plus-circle mr-1"></i> Add New Advance for {{ $employee->first_name ?? '' }}</h6>

                    <div class="row">
                        {{-- Advance Amount --}}
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold text-dark">
                                    <i class="fa fa-money text-success mr-1"></i> Advance Amount <span class="text-danger">*</span>
                                </label>
                                <input type="number" class="form-control form-control-lg" name="advance_amount" id="add_advance_amount" step="0.01" min="1" placeholder="1000" required>
                            </div>
                        </div>

                        {{-- Tenure --}}
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold text-dark">
                                    <i class="fa fa-calendar text-primary mr-1"></i> Tenure (Months) <span class="text-danger">*</span>
                                </label>
                                <input type="number" class="form-control form-control-lg" name="tenure_months" id="add_tenure_months" min="1" max="60" value="1" placeholder="2" required>
                            </div>
                        </div>

                        {{-- Start Month --}}
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold text-dark">
                                    <i class="fa fa-play text-info mr-1"></i> Start Month <span class="text-danger">*</span>
                                </label>
                                <input type="month" class="form-control form-control-lg" name="start_date" id="add_start_date" min="{{ $minAllowedMonth }}" value="{{ $minAllowedMonth }}" required>
                            </div>
                        </div>

                        {{-- Notes --}}
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold text-dark">
                                    <i class="fa fa-sticky-note text-warning mr-1"></i> Notes (Optional)
                                </label>
                                <textarea class="form-control" name="notes" rows="1" placeholder="Optional notes about the advance..."></textarea>
                            </div>
                        </div>

                        {{-- Auto Calculated Deduction --}}
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold text-success">
                                    <i class="fa fa-calculator mr-1"></i> Monthly Deduction (Auto Calculated)
                                </label>
                                <input type="text" class="form-control bg-light" id="add_calculated_deduction" placeholder="₹0.00" readonly>
                            </div>
                        </div>

                        {{-- Auto Calculated End Date --}}
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold text-info">
                                    <i class="fa fa-calendar-check-o mr-1"></i> End Month (Auto Calculated)
                                </label>
                                <input type="text" class="form-control bg-light" id="add_expected_end_date" placeholder="-" readonly>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer bg-white border-top-0 pt-0 pb-4 px-4">
                    <button type="submit" class="btn btn-primary font-weight-bold px-4" id="btnAddAdvance">
                        <i class="fa fa-save mr-1"></i> Save Advance
                    </button>
                    <button type="button" class="btn btn-outline-secondary font-weight-bold px-4" data-dismiss="modal">
                        <i class="fa fa-times mr-1"></i> Cancel Edit
                    </button>
                    <div class="ml-auto text-muted small">
                         <i class="fa fa-info-circle text-info"></i> Fields marked with * are required
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Advance Modal --}}
<div class="modal fade" id="editAdvanceModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">Edit Advance</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editAdvanceForm">
                <div class="modal-body">
                    <div class="alert alert-danger d-none" id="editAdvanceError"></div>
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="advance_id" id="edit_advance_id">
                    
                    <div class="form-group">
                        <label>Advance Amount <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">₹</span>
                            </div>
                            <input type="number" class="form-control" name="advance_amount" id="edit_advance_amount" step="0.01" min="1" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Start Month <span class="text-danger">*</span></label>
                                <input type="month" class="form-control" name="start_date" id="edit_start_date" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tenure (Months) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="tenure_months" id="edit_tenure_months" min="1" max="60" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group bg-light p-3 rounded">
                        <label class="mb-0 text-muted small text-uppercase font-weight-bold">Estimated Monthly Deduction</label>
                        <h4 class="mb-0 text-info font-weight-bold" id="edit_calculated_deduction">₹0.00</h4>
                    </div>
                    
                    <div class="form-group">
                        <label>Notes / Reason</label>
                        <textarea class="form-control" name="notes" id="edit_notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-info" id="btnUpdateAdvance">Update Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- View Advance Details Modal --}}
<div class="modal fade" id="viewAdvanceModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title">Advance Details & History</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
                {{-- Summary Cards --}}
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card bg-light border-0 h-100 mb-0">
                            <div class="card-body text-center p-3">
                                <h6 class="text-muted text-uppercase small font-weight-bold mb-2">Total Advance</h6>
                                <h3 class="font-weight-bold text-dark mb-0" id="view_total_amount">₹0.00</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-success-light border-0 h-100 mb-0">
                            <div class="card-body text-center p-3">
                                <h6 class="text-success text-uppercase small font-weight-bold mb-2">Amount Paid</h6>
                                <h3 class="font-weight-bold text-success mb-0" id="view_paid_amount">₹0.00</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-danger-light border-0 h-100 mb-0">
                            <div class="card-body text-center p-3">
                                <h6 class="text-danger text-uppercase small font-weight-bold mb-2">Remaining</h6>
                                <h3 class="font-weight-bold text-danger mb-0" id="view_remaining_amount">₹0.00</h3>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="font-weight-bold text-dark">Repayment Progress</span>
                        <span class="font-weight-bold text-primary" id="view_progress_text">0%</span>
                    </div>
                    <div class="progress" style="height: 10px;">
                        <div class="progress-bar bg-success" id="view_progress_bar" role="progressbar" style="width: 0%"></div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td class="text-muted">Start Month:</td>
                                <td class="font-weight-bold text-right" id="view_start_date">-</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Tenure:</td>
                                <td class="font-weight-bold text-right" id="view_tenure">-</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td class="text-muted">Expected End:</td>
                                <td class="font-weight-bold text-right" id="view_expected_end">-</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Monthly EMI:</td>
                                <td class="font-weight-bold text-right" id="view_monthly_emi">-</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-12">
                         <h6 class="font-weight-bold border-bottom pb-2 mb-3">Advance Status</h6>
                         <div class="d-flex justify-content-between align-items-center">
                             <div id="view_status"></div>
                             <div class="text-muted small">
                                 Notes: <span id="view_notes" class="font-italic"></span>
                             </div>
                         </div>
                    </div>
                </div>

                <h6 class="font-weight-bold border-bottom pb-2 mb-3">Deduction History</h6>
                <div class="table-responsive" style="max-height: 250px; overflow-y: auto;">
                    <table class="table table-striped table-sm mb-0">
                        <thead class="thead-dark sticky-top">
                            <tr>
                                <th>#</th>
                                <th>Month</th>
                                <th>Deducted</th>
                                <th>Type</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody id="view_history_body">
                            {{-- Rows populated via JS --}}
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endpush

<script>
    // Ensure scripts run only after DOM is loaded
    document.addEventListener('DOMContentLoaded', function() {
        // ... (existing listeners) ...

        // View Advance Details Function (Global)
        window.viewAdvanceDetails = function(id) {
            // Show loading state/spinner if desired
            $('#viewAdvanceModal').modal('show');
            $('#view_history_body').html('<tr><td colspan="5" class="text-center py-3"><i class="fa fa-spinner fa-spin"></i> Loading details...</td></tr>');
            
            // Reset fields
            $('#view_total_amount').text('...');
            $('#view_paid_amount').text('...');
            $('#view_remaining_amount').text('...');
            
            fetch(`{{ url('advance') }}/${id}/history`)
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        const adv = data.advance;
                        const deductions = data.deductions;
                        
                        // Populate Cards
                        $('#view_total_amount').text('₹' + Number(adv.amount).toLocaleString('en-IN', { minimumFractionDigits: 2 }));
                        $('#view_paid_amount').text('₹' + Number(adv.total_paid || 0).toLocaleString('en-IN', { minimumFractionDigits: 2 }));
                        $('#view_remaining_amount').text('₹' + Number(adv.remaining || 0).toLocaleString('en-IN', { minimumFractionDigits: 2 }));
                        
                        // Progress Bar
                        const percent = adv.amount > 0 ? ((adv.total_paid / adv.amount) * 100).toFixed(1) : 0;
                        $('#view_progress_bar').css('width', percent + '%');
                        $('#view_progress_text').text(percent + '% Paid');
                        
                        // Details
                        $('#view_start_date').text(adv.start_date);
                        $('#view_tenure').text(adv.tenure + ' Months');
                        $('#view_expected_end').text(adv.expected_end_date);
                        $('#view_monthly_emi').text('₹' + Number(adv.monthly_deduction).toLocaleString('en-IN', { minimumFractionDigits: 2 }));
                        
                        let statusBadge = '';
                        if(adv.status === 'active') statusBadge = '<span class="badge badge-success">Active</span>';
                        else if(adv.status === 'completed') statusBadge = '<span class="badge badge-info">Completed</span>';
                        else statusBadge = '<span class="badge badge-secondary">' + adv.status + '</span>';
                        $('#view_status').html(statusBadge);
                        
                        $('#view_notes').text(adv.notes || 'N/A');
                        
                        // History Table
                        let rows = '';
                        if(deductions.length > 0) {
                            deductions.forEach((d, index) => {
                                let type = d.is_override ? '<span class="text-warning" title="'+(d.override_reason || 'Manual')+'">Manual/Override</span>' : '<span class="text-success">Auto-Deduction</span>';
                                
                                rows += `
                                    <tr>
                                        <td>${index + 1}</td>
                                        <td class="font-weight-bold">${d.month_year}</td>
                                        <td class="text-danger font-weight-bold">-₹${Number(d.amount).toFixed(2)}</td>
                                        <td>${type}</td>
                                        <td class="small text-muted">${d.created_at}</td>
                                    </tr>
                                `;
                            });
                        } else {
                            rows = '<tr><td colspan="5" class="text-center text-muted py-3">No deductions processed yet.</td></tr>';
                        }
                        $('#view_history_body').html(rows);
                        
                    } else {
                        toastr.error('Failed to load details.');
                        $('#viewAdvanceModal').modal('hide');
                    }
                })
                .catch(err => {
                    console.error(err);
                    toastr.error('Error loading details.');
                    $('#view_history_body').html('<tr><td colspan="5" class="text-center text-danger">Error loading data.</td></tr>');
                });
        };


        
        // --- Calculation Logic ---
        function calculateDeduction(amountVal, tenureVal, displayElement) {
            console.log('calculateDeduction called with:', amountVal, tenureVal, displayElement);
            const amount = parseFloat(amountVal) || 0;
            const tenure = parseInt(tenureVal) || 1;
            
            console.log('Parsed values:', amount, tenure);
            
            if (tenure > 0) {
                const deduction = amount / tenure;
                console.log('Calculated deduction:', deduction);
                
                if (displayElement.tagName === 'INPUT') {
                    displayElement.value = '₹' + deduction.toFixed(2);
                } else {
                    displayElement.innerText = '₹' + deduction.toFixed(2);
                }
            } else {
                console.log('Tenure is 0 or invalid, setting to 0.00');
                if (displayElement.tagName === 'INPUT') {
                    displayElement.value = '₹0.00';
                } else {
                    displayElement.innerText = '₹0.00';
                }
            }
        }
        
        function calculateEndDate(startVal, tenureVal, displayElement) {
            console.log('calculateEndDate called with:', startVal, tenureVal, displayElement);
            const tenure = parseInt(tenureVal) || 1;
            
            if (startVal && tenure > 0) {
                const [year, month] = startVal.split('-').map(Number);
                const startDate = new Date(year, month - 1, 1);
                
                startDate.setMonth(startDate.getMonth() + tenure - 1);
                
                const endMonth = startDate.toLocaleString('default', { month: 'long' });
                const endYear = startDate.getFullYear();
                
                const resultStr = `${endMonth} ${endYear}`;
                console.log('Calculated End Date:', resultStr);
                
                if (displayElement.tagName === 'INPUT') {
                    displayElement.value = resultStr;
                } else {
                    displayElement.innerText = resultStr;
                }
            } else {
                console.log('Invalid start date or tenure');
                if (displayElement.tagName === 'INPUT') {
                    displayElement.value = '-';
                } else {
                    displayElement.innerText = '-';
                }
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            console.log('Advance Modal Script Loaded');
            
            // Vanilla JS Event Delegation for maximum robustness
            document.body.addEventListener('input', function(e) {
                const target = e.target;
                // console.log('Input Event on:', target.id); // Uncomment if too noisy
                
                // Add Advance Calculation
                if (target.id === 'add_advance_amount' || target.id === 'add_tenure_months') {
                    console.log('Matched Add Advance fields');
                    const amount = document.getElementById('add_advance_amount').value;
                    const tenure = document.getElementById('add_tenure_months').value;
                    const display = document.getElementById('add_calculated_deduction');
                    
                    if(!display) console.error('Display element add_calculated_deduction not found!');
                    
                    calculateDeduction(amount, tenure, display);
                }

                if (target.id === 'add_start_date' || target.id === 'add_tenure_months') {
                    console.log('Matched Add Advance Date fields');
                    const start = document.getElementById('add_start_date').value;
                    const tenure = document.getElementById('add_tenure_months').value;
                    const display = document.getElementById('add_expected_end_date');
                    
                    if(!display) console.error('Display element add_expected_end_date not found!');
                    
                    calculateEndDate(start, tenure, display);
                }

                // Edit Advance Calculation
                if (target.id === 'edit_advance_amount' || target.id === 'edit_tenure_months') {
                    console.log('Matched Edit Advance fields');
                    const amount = document.getElementById('edit_advance_amount').value;
                    const tenure = document.getElementById('edit_tenure_months').value;
                    const display = document.getElementById('edit_calculated_deduction');
                    calculateDeduction(amount, tenure, display);
                }
            });
            
            // --- Add Advance Submit ---
            const addForm = document.getElementById('addAdvanceForm');
            if(addForm) {
                addForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    console.log('Submitting Add Advance Form');
                    const btn = document.getElementById('btnAddAdvance');
                    const errorDiv = document.getElementById('addAdvanceError');
                    
                    btn.disabled = true;
                    btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Saving...';
                    errorDiv.classList.add('d-none');

                    const formData = new FormData(this);
                    
                    fetch('{{ route("advance.add") }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        console.log('Save Response:', data);
                        if (data.success) {
                            toastr.success(data.message);
                            $('#addAdvanceModal').modal('hide');
                            setTimeout(() => location.reload(), 1000);
                        } else {
                            throw new Error(data.message || 'Failed to add advance');
                        }
                    })
                    .catch(error => {
                        console.error('Save Error:', error);
                        errorDiv.innerText = error.message;
                        errorDiv.classList.remove('d-none');
                        btn.disabled = false;
                        btn.innerHTML = 'Save Advance';
                    });
                });
            }

            // --- Edit Advance Submit ---
            const editForm = document.getElementById('editAdvanceForm');
            if(editForm) {
                editForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    console.log('Submitting Edit Advance Form');
                    const btn = document.getElementById('btnUpdateAdvance');
                    const errorDiv = document.getElementById('editAdvanceError');
                    const advanceId = document.getElementById('edit_advance_id').value;
                    
                    btn.disabled = true;
                    btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Updating...';
                    errorDiv.classList.add('d-none');

                    const formData = new FormData(this);

                    fetch(`{{ url('advance') }}/${advanceId}/update`, { 
                        method: 'POST', 
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        console.log('Update Response:', data);
                        if (data.success) {
                            toastr.success(data.message);
                            $('#editAdvanceModal').modal('hide');
                            setTimeout(() => location.reload(), 1000);
                        } else {
                            throw new Error(data.message || 'Failed to update advance');
                        }
                    })
                    .catch(error => {
                        console.error('Update Error:', error);
                        errorDiv.innerText = error.message;
                        errorDiv.classList.remove('d-none');
                        btn.disabled = false;
                        btn.innerHTML = 'Update Changes';
                    });
                });
            }
        });

    // --- Global Functions (exposed to window) ---
    
    // Edit Advance
    window.editAdvance = function(id) {
        // Show loader or disable button logic here if needed
        const url = `{{ url('advance') }}/${id}/details`;
        
        // Use jquery for easy modal manipulation as it fits the theme
        $.get(url, function(data) {
            if(data.success) {
                const adv = data.advance;
                
                $('#edit_advance_id').val(adv.id);
                $('#edit_advance_amount').val(adv.advance_amount);
                $('#edit_tenure_months').val(adv.tenure_months);
                
                // Format start_date to YYYY-MM for the input
                // Assuming API returns YYYY-MM-DD
                const startMonth = adv.start_date.substring(0, 7); 
                $('#edit_start_date').val(startMonth);
                
                $('#edit_notes').val(adv.notes);
                
                // Trigger calculation
                const event = new Event('input');
                document.getElementById('edit_advance_amount').dispatchEvent(event);
                
                $('#editAdvanceModal').modal('show');
            } else {
                toastr.error(data.message || 'Could not fetch details');
            }
        }).fail(function(err) {
            toastr.error('Error fetching advance details');
        });
    };

    // Close/Cancel Advance
    window.closeAdvance = function(id) {
        if(confirm('Are you sure you want to stop/cancel this advance? This will mark it as cancelled.')) {
            $.ajax({
                url: `{{ url('advance') }}/${id}/close`,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(data) {
                    if(data.success) {
                        toastr.success(data.message);
                        location.reload();
                    } else {
                        toastr.error(data.message);
                    }
                },
                error: function(err) {
                    toastr.error('Failed to cancel advance');
                }
            });
        }
    };

    // Delete Advance
    window.deleteAdvance = function(id) {
        if(confirm('Are you sure you want to permanently delete this advance record? This cannot be undone.')) {
            $.ajax({
                url: `{{ url('advance') }}/${id}/delete`,
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(data) {
                    if(data.success) {
                        toastr.success(data.message);
                        location.reload();
                    } else {
                        toastr.error(data.message);
                    }
                },
                error: function(err) {
                    toastr.error('Failed to delete advance');
                }
            });
        }
    };
    });
</script>
@endif