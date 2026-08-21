@extends('layouts.master')
@section('title', 'Manage Salary Components')
@section('content')
<style>
    /* Page Header Card */
    .page-header-card {
        background: white;
        border-radius: 1rem;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        margin-bottom: 2rem;
    }

    .page-header-gradient {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 2.5rem 2rem;
        position: relative;
    }

    .page-header-pattern {
        position: absolute;
        inset: 0;
        background: rgba(0, 0, 0, 0.05);
    }

    .page-header-circle-1 {
        position: absolute;
        top: -1rem;
        right: -1rem;
        width: 6rem;
        height: 6rem;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
    }

    .page-header-circle-2 {
        position: absolute;
        bottom: -1rem;
        left: -1rem;
        width: 8rem;
        height: 8rem;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
    }

    .page-header-icon-box {
        width: 4rem;
        height: 4rem;
        background: rgba(255, 255, 255, 0.2);
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .page-header-title {
        font-size: 1.875rem;
        font-weight: 700;
        color: white;
        margin-bottom: 0.5rem;
    }

    .page-header-subtitle {
        font-size: 1rem;
        color: rgba(255, 255, 255, 0.9);
        margin: 0;
    }

    /* Table Card */
    .table-card {
        background: white;
        border-radius: 1rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
        border: 1px solid #e5e7eb;
        overflow: hidden;
        margin-bottom: 2rem;
    }

    .table-card-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 1.25rem 1.5rem;
        border-bottom: none;
    }

    .table-card-header h4 {
        color: white;
        font-weight: 600;
        margin: 0;
        font-size: 1.125rem;
    }

    .table-card-body {
        padding: 0 0.5rem;
    }

    /* DataTable Styling */
    /* Allow some overflow so native select caret isn't clipped by parent containers */
    .dataTables_wrapper {
        overflow: visible !important;
    }

    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter,
    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_paginate {
        padding: 1rem 1.25rem;
        color: #6b7280;
    }

    .dataTables_wrapper .dataTables_filter input {
        border: 1px solid #d1d5db;
        border-radius: 0.5rem;
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
    }

    /* Make sure the native select caret has room and isn't clipped */
    .dataTables_wrapper .dataTables_length select {
        border: 1px solid #d1d5db;
        border-radius: 0.5rem;
        /* extra right padding so the caret sits inside the control */
        padding: 0.5rem 1.75rem 0.5rem 0.75rem;
        font-size: 0.875rem;
        background-position: calc(100% - 0.6rem) center;
        background-repeat: no-repeat;
        -webkit-appearance: menulist-button;
        appearance: menulist-button;
        overflow: visible;
    }

    /* Reduce right padding on very small screens so control fits */
    @media (max-width: 575px) {
        .dataTables_wrapper .dataTables_length select {
            padding-right: 1rem;
        }
    }

    /* Fix first column width and padding so row numbers are fully visible */
    .table thead th:first-child,
    .table tbody td:first-child {
        width: 64px;
        max-width: 64px;
        text-align: center;
        padding-left: 0.5rem;
        padding-right: 0.5rem;
        white-space: nowrap;
    }

    /* Slightly reduce default cell padding to avoid clipping on small tables */
    .table tbody td {
        padding: 0.75rem 0.5rem;
        border-bottom: 1px solid #f3f4f6;
        vertical-align: middle;
        font-size: 0.875rem;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button {
        padding: 0.5rem 0.75rem;
        margin: 0 0.125rem;
        border: 1px solid #d1d5db;
        border-radius: 0.5rem;
        background: white;
        color: #6b7280;
        font-size: 0.875rem;
        transition: all 0.2s;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background: #f3f4f6;
        border-color: #9ca3af;
        color: #374151;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-color: #667eea;
        color: white;
    }

    /* Table Header */
    .table thead th {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        font-weight: 600;
        border: none;
        padding: 1rem 0.75rem;
        font-size: 0.875rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* Table Body */
    .table tbody td {
        padding: 1rem 0.75rem;
        border-bottom: 1px solid #f3f4f6;
        vertical-align: middle;
        font-size: 0.875rem;
    }

    .table tbody tr:hover {
        background: #f9fafb;
    }

    /* Modern Badges */
    .badge-modern {
        padding: 0.375rem 0.75rem;
        font-weight: 500;
        border-radius: 0.375rem;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .badge-modern.bg-success {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
    }

    .badge-modern.bg-danger {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
    }

    .badge-modern.bg-warning text-dark {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: white;
    }

    /* Action Buttons */
    .btn-action {
        padding: 0.5rem 1rem;
        border-radius: 0.5rem;
        font-weight: 500;
        font-size: 0.875rem;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 80px;
    }

    .btn-action-edit {
        background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
        color: white;
    }

    .btn-action-edit:hover {
        background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(59, 130, 246, 0.3);
    }

    .btn-action-delete {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
    }

    .btn-action-delete:hover {
        background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(239, 68, 68, 0.3);
    }

    /* Modal Styling */
    .modal-content {
        border-radius: 1rem;
        border: none;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }

    .modal-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 1rem 1rem 0 0;
        border-bottom: none;
        padding: 1.5rem;
    }

    .modal-header .close {
        color: white;
        opacity: 0.8;
    }

    .modal-header .close:hover {
        color: white;
        opacity: 1;
    }

    .modal-body {
        padding: 2rem;
    }

    .modal-footer {
        border-top: 1px solid #e5e7eb;
        padding: 1.5rem 2rem;
    }

    /* Form Styling */
    .form-control {
        border: 1px solid #d1d5db;
        border-radius: 0.5rem;
        padding: 0.75rem;
        font-size: 0.875rem;
    }

    .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    /* Global Button Styles */
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
        color: white;
    }

    /* Fix Select2 height and styling to match modern form-control */
    .select2-container--default .select2-selection--multiple {
        min-height: 44px !important;
        border: 1px solid #d1d5db !important;
        border-radius: 0.5rem !important;
        padding-top: 4px;
        transition: border-color .15s ease-in-out,box-shadow .15s ease-in-out;
    }
    .select2-container--default.select2-container--focus .select2-selection--multiple {
        border-color: #667eea !important;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1) !important;
    }
    .input-group > .select2-container--default {
        flex: 1 1 auto;
        width: 1% !important;
    }
    .input-group > .select2-container--default .select2-selection--multiple {
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
        border-radius: 0 0.5rem 0.5rem 0 !important;
    }
</style>
<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Modern Page Header -->
        <div class="page-header-card">
            <div class="page-header-gradient">
                <!-- Background Patterns -->
                <div class="page-header-pattern"></div>
                <div class="page-header-circle-1"></div>
                <div class="page-header-circle-2"></div>

                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <div class="page-header-icon-box">
                            <i class="fas fa-money-bill-wave fa-lg" style="color: rgba(255,255,255,0.9);"></i>
                        </div>
                        <div class="ms-3">
                            <h1 class="page-header-title">Salary Components</h1>
                            <p class="page-header-subtitle">Manage salary components and their configurations</p>
                        </div>
                    </div>
                    <!-- Stats Section -->
                    <div class="d-flex align-items-center text-end d-none d-md-flex">
                        <div class="page-header-stats me-3 text-white">
                            <p class="page-header-stats-label mb-0" style="opacity: 0.9; font-size: 0.875rem;">Total Components</p>
                            <p class="page-header-stats-value mb-0 fw-bold" style="font-size: 1.75rem;">{{ $components->count() }}</p>
                        </div>
                        <div class="page-header-stats-icon p-2 rounded" style="background: rgba(255,255,255,0.2); width: auto; height: auto;">
                            <i class="fas fa-coins text-white" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="p-3 d-flex justify-content-between align-items-center flex-wrap gap-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                         <li class="breadcrumb-item">Salary Settings</li>
                        <li class="breadcrumb-item active">Salary Components</li>
                    </ol>
                </nav>
                <div class="d-flex gap-2">
                    <a href="#" class="btn btn-primary" data-toggle="modal" data-target="#add_salary_component">
                        <i class="fas fa-plus me-2"></i> Add Component
                    </a>
                </div>
            </div>
        </div>
        <!-- /Modern Page Header -->

        <!-- Salary Components Table -->
        <div class="table-card">
            <div class="table-card-header">
                <h4><i class="fas fa-list me-2"></i>Salary Components List</h4>
            </div>
            <div class="table-card-body">
                <div class="table-responsive">
                    <table class="table table-striped custom-table datatable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Component Name</th>
                                <th>Short Code</th>
                                <th>Location</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($components as $component)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $component->name }}</td>
                                <td><code>{{ $component->short_name }}</code></td>
                                <td>{{ $component->location_name }}</td>
                                <td>
                                    <span class="badge badge-modern badge-{{ $component->type === 'earning' ? 'success' : 'danger' }}">
                                        {{ ucfirst($component->type) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-modern badge-{{ $component->status ? 'success' : 'warning' }}">
                                        {{ $component->status ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <button class="btn-action btn-action-edit me-2 salaryComponentEdit" data-id="{{ $component->id }}">
                                        <i class="fas fa-edit me-1"></i> Edit
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <!-- /Salary Components Table -->
    </div>
    </div>
    <!-- Add Salary Component Modal -->
    <div id="add_salary_component" class="modal custom-modal fade" role="dialog">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Add Salary Component</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('form/salary-component/save') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Component Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Short Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="short_name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Type <span class="text-danger">*</span></label>
                                    <select class="form-control form-select" name="type" required>
                                        <option value="earning">Earning</option>
                                        <option value="deduction">Deduction</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Calculation Type</label>
                                    <select class="form-control form-select" name="calculation_type" id="add_calculation_type">
                                        <option value="flat_amount">Flat Amount</option>
                                        <option value="percentage_ctc">Percentage of CTC</option>
                                        <option value="percentage_basic">Percentage of Basic</option>
                                        <option value="residual">Residual / Balance</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6" id="add_value_container">
                                <div class="form-group">
                                    <label>Value</label>
                                    <input type="number" step="0.01" class="form-control" name="calculation_value">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Location <span class="text-danger">*</span></label>
                                    <select class="form-control select2-multi" name="location_id[]" id="add_location_id" multiple="multiple" data-placeholder="Select Locations" required style="width: 100%;">
                                        <option value="0">All</option>
                                        @foreach($locations as $location)
                                            <option value="{{ $location->id }}">{{ $location->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Status <span class="text-danger">*</span></label>
                                    <select class="form-control form-select" name="status" required>
                                        <option value="1">Active</option>
                                        <option value="0">Inactive</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="submit-section">
                            <button type="submit" class="btn btn-primary submit-btn">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- /Add Salary Component Modal -->
    <!-- Edit Salary Component Modal -->
    <div id="edit_salary_component" class="modal custom-modal fade" role="dialog">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit Salary Component</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="editForm" method="POST" action="{{ route('form/salary-component/update') }}">
                        @csrf
                        <input type="hidden" name="id" id="edit_id">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Component Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="name" id="edit_name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Short Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="short_name" id="edit_short_name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Type <span class="text-danger">*</span></label>
                                    <select class="form-control form-select" name="type" id="edit_type" required>
                                        <option value="earning">Earning</option>
                                        <option value="deduction">Deduction</option>
                                    </select>
                                </div>
                            </div>
                             <div class="col-md-6">
                                <div class="form-group">
                                    <label>Calculation Type</label>
                                    <select class="form-control form-select" name="calculation_type" id="edit_calculation_type">
                                        <option value="flat_amount">Flat Amount</option>
                                        <option value="percentage_ctc">Percentage of CTC</option>
                                        <option value="percentage_basic">Percentage of Basic</option>
                                        <option value="residual">Residual / Balance</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6" id="edit_value_container">
                                <div class="form-group">
                                    <label>Value</label>
                                    <input type="number" step="0.01" class="form-control" name="calculation_value" id="edit_calculation_value">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Location <span class="text-danger">*</span></label>
                                    <select class="form-control select2-multi" name="location_id[]" id="edit_location_id" multiple="multiple" data-placeholder="Select Locations" required style="width: 100%;">
                                        <option value="0">All</option>
                                        @foreach($locations as $location)
                                            <option value="{{ $location->id }}">{{ $location->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Status <span class="text-danger">*</span></label>
                                    <select class="form-control form-select" name="status" id="edit_status" required>
                                        <option value="1">Active</option>
                                        <option value="0">Inactive</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="submit-section">
                            <button type="submit" class="btn btn-primary submit-btn">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    </div>
</div>

@section('script')
<script>
    const baseUrl = "{{ url('/') }}"; 
    $(document).ready(function () {
        
        // Handle Calculation Type Change (Add Modal)
        $('#add_calculation_type').change(function() {
            if($(this).val() === 'residual') {
                $('#add_value_container').hide();
            } else {
                $('#add_value_container').show();
            }
        });

        // Handle Calculation Type Change (Edit Modal)
        $('#edit_calculation_type').change(function() {
            if($(this).val() === 'residual') {
                $('#edit_value_container').hide();
            } else {
                $('#edit_value_container').show();
            }
        });

        $(document).on('click', '.salaryComponentEdit', function (e) {
            e.preventDefault();
            var id = $(this).data('id');

            $.ajax({
                url: `${baseUrl}/form/salary-component/get/${id}`,
                type: 'GET',
                success: function (response) {
                    $('#edit_id').val(response.id);
                    $('#edit_name').val(response.name);
                    $('#edit_short_name').val(response.short_name);
                    $('#edit_type').val(response.type);
                    
                    // Handle multiselect for location_id
                    if (response.location_id) {
                        let locationIds = response.location_id;
                        if (typeof locationIds === 'string') {
                            try {
                                locationIds = JSON.parse(locationIds);
                            } catch (e) {
                                locationIds = locationIds.split(',');
                            }
                        }
                        $('#edit_location_id').val(locationIds).trigger('change');
                    } else {
                        $('#edit_location_id').val([]).trigger('change');
                    }
                    const statusValue = response.status ? '1' : '0';
                    $('#edit_status').val(statusValue);
                    
                    // Populate new fields
                    $('#edit_calculation_type').val(response.calculation_type || 'flat_amount').trigger('change');
                    $('#edit_calculation_value').val(response.calculation_value);

                    $('#edit_salary_component').modal('show');
                },
                error: function () {
                    alert('Could not fetch component data.');
                }
            });
        });

        // Initialize Select2 with a more robust method
        function initSelect2(element) {
            $(element).select2({
                width: '100%',
                dropdownParent: $(element).closest('.modal')
            });
        }

        // Initialize on page load
        $('.select2-multi').each(function() {
            initSelect2(this);
        });

        // Re-initialize when modals are shown (to fix any rendering issues)
        $('.modal').on('shown.bs.modal', function() {
            $(this).find('.select2-multi').each(function() {
                initSelect2(this);
            });
        });
        
        // Robust normalization for DataTables length <select>
        (function(){
            function normalizeDataTableLengthSelects() {
                $('select[name$="_length"]').each(function () {
                    var $s = $(this);
                    // remove unwanted bootstrap/dataTables classes
                    $s.removeClass('custom-select custom-select-sm form-control form-control-smn form-control-sm');
                    // also ensure parent wrapper isn't forcing overflow hidden
                    $s.closest('.dataTables_length').css({ 'overflow': 'visible' });
                    // reset any padding overrides
                    $s.css({ 'padding-right': '', 'padding-left': '' });
                });
            }

            // run on events that DataTables emits
            $(document).on('init.dt draw.dt', function (e, settings) {
                normalizeDataTableLengthSelects();
            });

            // MutationObserver to catch controls added after init
            var observer = new MutationObserver(function (mutations) {
                mutations.forEach(function (m) {
                    m.addedNodes.forEach(function (node) {
                        if (node.nodeType !== 1) return;
                        if (node.matches && node.matches('select[name$="_length"]')) {
                            normalizeDataTableLengthSelects();
                        } else if ($(node).find('select[name$="_length"]').length) {
                            normalizeDataTableLengthSelects();
                        }
                    });
                });
            });
            observer.observe(document.body, { childList: true, subtree: true });

            // safety retries for frameworks that reapply classes shortly after init
            for (let i = 0; i < 6; i++) {
                setTimeout(normalizeDataTableLengthSelects, i * 300);
            }

            // disconnect observer after 10s to avoid permanent overhead
            setTimeout(function(){ observer.disconnect(); }, 10000);
        })();
    });
</script>
@endsection

@endsection