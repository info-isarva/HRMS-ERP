<div class="card mb-4 card card-info card-outline" id="leave-allocations-tab-content">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fas fa-calendar-check me-2"></i>Leave Allocations
            <span class="badge bg-info text-dark ms-2" id="financial-year-badge"></span>
        </h5>
        <div class="card-tools">
            <button type="button" class="btn btn-sm btn-outline-primary" id="sync-leave-types" title="Sync with Attendance System">
                <i class="fas fa-sync"></i> Sync Leave Types
            </button>
            <button type="button" class="btn btn-sm btn-outline-info" id="test-api-connection" title="Test API Connection">
                <i class="fas fa-plug"></i> Test API
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="p-3">
            
            <!-- Department and Joining Date Info -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle"></i> Leave Allocation Information</h6>
                        <p class="mb-1"><strong>Department:</strong> <span id="selected-department-name">-</span></p>
                        <p class="mb-1"><strong>Joining Date:</strong> <span id="selected-joining-date">-</span></p>
                        <p class="mb-0"><strong>Financial Year:</strong> <span id="selected-financial-year">-</span></p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="alert alert-secondary" id="pro-rating-summary" style="display: none;">
                        <h6><i class="fas fa-calculator"></i> Pro-Rating Summary</h6>
                        <p class="mb-1"><strong>Leave Types:</strong> <span id="summary-leave-types">0</span></p>
                        <p class="mb-1"><strong>Total Original Days:</strong> <span id="summary-original-days">0</span></p>
                        <p class="mb-1"><strong>Total Allocated Days:</strong> <span id="summary-allocated-days">0</span></p>
                        <p class="mb-0"><strong>Pro-Rating Factor:</strong> <span id="summary-pro-rating-factor">100%</span></p>
                    </div>
                </div>
            </div>

            <!-- Week Off Configuration -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">
                                <i class="fas fa-calendar-week me-2"></i>Week Off Configuration
                                <small class="text-muted ms-2">Select days that are considered as weekly offs</small>
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label class="form-label">Select Week Off Days:</label>
                                        <div class="week-off-checkboxes d-flex flex-wrap">
                                            <div class="form-check form-check-inline me-3">
                                                <input class="form-check-input week-off-checkbox" type="checkbox" id="sunday" name="week_offs[]" value="0" checked>
                                                <label class="form-check-label" for="sunday">
                                                    <i class="fas fa-sun text-warning me-1"></i>Sunday
                                                </label>
                                            </div>
                                            <div class="form-check form-check-inline me-3">
                                                <input class="form-check-input week-off-checkbox" type="checkbox" id="monday" name="week_offs[]" value="1">
                                                <label class="form-check-label" for="monday">
                                                    <i class="fas fa-calendar-day text-primary me-1"></i>Monday
                                                </label>
                                            </div>
                                            <div class="form-check form-check-inline me-3">
                                                <input class="form-check-input week-off-checkbox" type="checkbox" id="tuesday" name="week_offs[]" value="2">
                                                <label class="form-check-label" for="tuesday">
                                                    <i class="fas fa-calendar-day text-primary me-1"></i>Tuesday
                                                </label>
                                            </div>
                                            <div class="form-check form-check-inline me-3">
                                                <input class="form-check-input week-off-checkbox" type="checkbox" id="wednesday" name="week_offs[]" value="3">
                                                <label class="form-check-label" for="wednesday">
                                                    <i class="fas fa-calendar-day text-primary me-1"></i>Wednesday
                                                </label>
                                            </div>
                                            <div class="form-check form-check-inline me-3">
                                                <input class="form-check-input week-off-checkbox" type="checkbox" id="thursday" name="week_offs[]" value="4">
                                                <label class="form-check-label" for="thursday">
                                                    <i class="fas fa-calendar-day text-primary me-1"></i>Thursday
                                                </label>
                                            </div>
                                            <div class="form-check form-check-inline me-3">
                                                <input class="form-check-input week-off-checkbox" type="checkbox" id="friday" name="week_offs[]" value="5">
                                                <label class="form-check-label" for="friday">
                                                    <i class="fas fa-calendar-day text-primary me-1"></i>Friday
                                                </label>
                                            </div>
                                            <div class="form-check form-check-inline me-3">
                                                <input class="form-check-input week-off-checkbox" type="checkbox" id="saturday" name="week_offs[]" value="6">
                                                <label class="form-check-label" for="saturday">
                                                    <i class="fas fa-moon text-info me-1"></i>Saturday
                                                </label>
                                            </div>
                                        </div>
                                        <small class="form-text text-muted">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Week off days will not be counted as working days. At least one day must be selected.
                                        </small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="week-off-summary">
                                        <label class="form-label">Summary:</label>
                                        <div class="alert alert-light p-2">
                                            <small>
                                                <strong>Selected Days:</strong> <span id="selected-week-offs-count">1</span><br>
                                                <strong>Working Days/Week:</strong> <span id="working-days-per-week">6</span><br>
                                                <strong>Pattern:</strong> <span id="week-off-pattern">Sunday</span>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Loading State -->
            <div id="leave-loading" class="text-center" style="display: none;">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
                <p class="mt-2">Loading leave types...</p>
            </div>

            <!-- No Department Selected State -->
            <div id="no-department-selected" class="alert alert-warning">
                <h6><i class="fas fa-exclamation-triangle"></i> Department Required</h6>
                <p class="mb-0">Please select a department and joining date in the Basic Details tab to load available leave types.</p>
            </div>

            <!-- No Leave Types Available -->
            <div id="no-leave-types" class="alert alert-info" style="display: none;">
                <h6><i class="fas fa-info-circle"></i> No Leave Types</h6>
                <p class="mb-0">No leave types are available for the selected department.</p>
            </div>

            <!-- Leave Allocations Table -->
            <div id="leave-allocations-container" style="display: none;">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th width="20%">Leave Type</th>
                                <th width="10%">Code</th>
                                <th width="15%">Original Days</th>
                                <th width="15%">Allocated Days</th>
                                <th width="10%">Override</th>
                                <th width="15%">Override Days</th>
                                <th width="15%">Final Days</th>
                            </tr>
                        </thead>
                        <tbody id="leave-allocations-tbody">
                            <!-- Dynamic content will be inserted here -->
                        </tbody>
                        <tfoot class="thead-light">
                            <tr>
                                <th colspan="2">Total</th>
                                <th><span id="total-original-days">0</span></th>
                                <th><span id="total-allocated-days">0</span></th>
                                <th>-</th>
                                <th><span id="total-override-days">0</span></th>
                                <th><span id="total-final-days">0</span></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Pro-Rating Explanation -->
                <div id="pro-rating-explanation" class="mt-3" style="display: none;">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h6><i class="fas fa-info-circle text-info"></i> Pro-Rating Calculation</h6>
                            <p class="mb-2">Leave allocations have been pro-rated based on the joining date:</p>
                            <ul class="mb-0">
                                <li><strong>Joining Date:</strong> <span id="pro-rating-joining-date"></span></li>
                                <li><strong>Financial Year:</strong> <span id="pro-rating-fy-start"></span> to <span id="pro-rating-fy-end"></span></li>
                                <li><strong>Remaining Months:</strong> <span id="pro-rating-remaining-months"></span> out of <span id="pro-rating-total-months"></span></li>
                                <li><strong>Pro-Rating Factor:</strong> <span id="pro-rating-percentage"></span>%</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Error Messages -->
            <div id="leave-error-container" class="alert alert-danger" style="display: none;">
                <h6><i class="fas fa-exclamation-circle"></i> Error</h6>
                <p id="leave-error-message" class="mb-0"></p>
            </div>

        </div>
    </div>
</div>

<!-- Hidden input to store leave allocations data -->
<input type="hidden" name="leave_allocations" id="leave-allocations-data" value="">

<!-- Hidden input to store week off data -->
<input type="hidden" name="week_offs" id="week-offs-data" value="">

<style>
/* Robust Toggle Switch Styles - SCOPED to this tab only */
#leave-allocations-tab-content .form-switch .form-check-input[type="checkbox"] {
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
#leave-allocations-tab-content .form-switch .form-check-input[type="checkbox"]::after {
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
#leave-allocations-tab-content .form-switch .form-check-input[type="checkbox"]:checked {
    background-color: #0d6efd !important; /* Active Blue */
    border-color: #0d6efd !important;
}

/* Move Knob when Checked */
#leave-allocations-tab-content .form-switch .form-check-input[type="checkbox"]:checked::after {
    left: 22px !important; /* Move to the right */
    background-color: #ffffff !important;
}

/* Hover State */
#leave-allocations-tab-content .form-switch .form-check-input[type="checkbox"]:hover {
    background-color: #c9cbcd !important;
}
#leave-allocations-tab-content .form-switch .form-check-input[type="checkbox"]:checked:hover {
    background-color: #dc3545 !important; /* Hover on Active is Red */
    border-color: #dc3545 !important;
}

/* Focus State */
#leave-allocations-tab-content .form-switch .form-check-input[type="checkbox"]:focus {
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25); /* Blue Focus */
    outline: none;
}

/* Label Alignment */
#leave-allocations-tab-content label.form-check-label {
    margin-left: 8px;
    vertical-align: top;
    line-height: 24px; /* Align text with the toggle */
}

/* Week off specific horizontal alignment */
.week-off-checkboxes .form-check-inline {
    display: inline-flex;
    align-items: center;
}

/* Leave allocation specific styles */
.leave-allocation-row {
    transition: background-color 0.2s;
}

.leave-allocation-row:hover {
    background-color: #f8f9fa;
}

.override-toggle {
    cursor: pointer;
}

.override-days-input {
    max-width: 100px;
}

.pro-rated-badge {
    font-size: 0.7em;
}

.original-days {
    text-decoration: line-through;
    color: #6c757d;
    font-size: 0.9em;
}

.final-days {
    font-weight: bold;
    color: #28a745;
}

.manual-override {
    color: #dc3545;
    font-weight: bold;
}

.sync-status {
    position: absolute;
    top: 10px;
    right: 10px;
    z-index: 1000;
}

/* Week off specific styles */
.week-off-checkboxes .form-check {
    margin-bottom: 10px;
}

.week-off-checkboxes .form-check-label {
    cursor: pointer;
    font-weight: 500;
    transition: color 0.2s;
}

.week-off-checkboxes .form-check-input:checked + .form-check-label {
    color: #28a745;
    font-weight: 600;
}

.week-off-checkboxes .form-check-input:not(:checked) + .form-check-label {
    color: #6c757d;
}

.week-off-summary .alert {
    border: 1px solid #dee2e6;
    background-color: #f8f9fa;
}

.week-off-checkboxes .form-check-label i {
    width: 16px;
    text-align: center;
}

@media (max-width: 768px) {
    .week-off-checkboxes {
        flex-direction: column;
    }
    
    .week-off-checkboxes .form-check-inline {
        margin-right: 0;
        margin-bottom: 8px;
    }
}
</style>