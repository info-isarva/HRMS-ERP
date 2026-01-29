@extends('layouts.master')
@section('title', 'Salary Settings')
@section('content')
<style>
    /* Page Header Card */
    .page-header-card {
        background: white;
        border-radius: 1rem;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
        overflow: hidden;
        margin-bottom: 2rem;
    }

    .page-header-gradient {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 2rem 1.5rem;
        position: relative;
        color: white;
    }

    .page-header-pattern {
        position: absolute;
        inset: 0;
        background: rgba(0, 0, 0, 0.04);
    }

    .page-header-circle-1,
    .page-header-circle-2 {
        position: absolute;
        background: rgba(255, 255, 255, 0.08);
        border-radius: 50%;
    }
    .page-header-circle-1 { top: -1rem; right: -1rem; width: 6rem; height: 6rem; }
    .page-header-circle-2 { bottom: -1rem; left: -1rem; width: 8rem; height: 8rem; }

    .page-header-icon-box {
        width: 4rem;
        height: 4rem;
        background: rgba(255, 255, 255, 0.16);
        border: 1px solid rgba(255, 255, 255, 0.22);
        border-radius: 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .page-header-title {
        font-size: 1.75rem;
        font-weight: 700;
        color: white;
        margin-bottom: 0.25rem;
    }
    .page-header-subtitle { color: rgba(255,255,255,0.9); margin: 0; }

    /* Modern Settings Card */
    .settings-card {
        background: white;
        border-radius: 1rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
        overflow: visible;
        border: 1px solid #e5e7eb;
        margin-bottom: 1.5rem;
    }

    .settings-card .card-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        border-radius: 1rem 1rem 0 0 !important;
        padding: 1.5rem;
    }

    .settings-card .card-header h5 {
        font-weight: 600;
        margin: 0;
        display: flex;
        align-items: center;
        font-size: 1.1rem;
        color: white;
    }

    .settings-card .card-header i {
        margin-right: 0.5rem;
        opacity: 0.9;
    }

    .settings-card .card-body {
        padding: 2rem;
    }

    /* Form Styling */
    .form-label {
        font-weight: 600;
        color: #374151;
        margin-bottom: 0.5rem;
        font-size: 0.875rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .form-control, .form-select {
        border: 2px solid #e5e7eb;
        border-radius: 0.5rem;
        padding: 0.75rem 1rem;
        font-size: 0.875rem;
        transition: all 0.2s ease;
        background: white;
    }

    .form-control:focus, .form-select:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        outline: none;
    }

    /* Button Styling */
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

    .btn-danger {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
    }
    
    .btn-danger:hover {
        transform: translateY(-2px);
         box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
         background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
    }

    /* Switch Styling */
    .form-check-input:checked {
        background-color: #667eea;
        border-color: #667eea;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .page-header-gradient {
            padding: 1.5rem 1rem;
        }
        .settings-card .card-body {
            padding: 1.5rem;
        }
    }
</style>

    <div class="page-wrapper">
        <div class="content container-fluid">
            
            <!-- Modern Page Header -->
            <div class="page-header-card">
                <div class="page-header-gradient">
                    <div class="page-header-pattern"></div>
                    <div class="page-header-circle-1"></div>
                    <div class="page-header-circle-2"></div>
                    <div class="d-flex align-items-center">
                        <div class="page-header-icon-box">
                            <i class="fas fa-money-check-alt fa-lg"></i>
                        </div>
                        <div class="ms-3">
                            <h1 class="page-header-title">Salary Settings</h1>
                            <p class="page-header-subtitle">Configure salary components and statutory deductions</p>
                        </div>
                    </div>
                </div>
                <div class="p-3 d-flex justify-content-between align-items-center">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="#">Settings</a></li>
                            <li class="breadcrumb-item active">Salary Settings</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <form action="{{ route('salary/settings/save') }}" method="post">
                        @csrf
                        
                        <!-- Dynamic Earning Components Settings -->
                        @if(isset($salaryComponents) && $salaryComponents->count() > 0)
                        <div class="settings-card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5><i class="fas fa-cogs me-2"></i>Salary Components Configuration</h5>
                            </div>
                            <div class="card-body">
                                @foreach($salaryComponents as $component)
                                <div class="row align-items-end mb-4 border-bottom pb-3">
                                    <div class="col-md-4">
                                        <label class="form-label">{{ $component->name }} ({{ $component->short_name }})</label>
                                        <input type="hidden" name="components[{{ $component->id }}][id]" value="{{ $component->id }}">
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group mb-0">
                                            <label class="form-label small text-muted">Calculation Type</label>
                                            <select class="form-select" name="components[{{ $component->id }}][calculation_type]">
                                                <option value="flat_amount" {{ $component->calculation_type == 'flat_amount' ? 'selected' : '' }}>Flat Amount</option>
                                                <option value="percentage_ctc" {{ $component->calculation_type == 'percentage_ctc' ? 'selected' : '' }}>Percentage of CTC</option>
                                                <option value="percentage_basic" {{ $component->calculation_type == 'percentage_basic' ? 'selected' : '' }}>Percentage of Basic</option>
                                                <option value="residual" {{ $component->is_residual ? 'selected' : '' }}>Residual / Balancing Figure</option> 
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group mb-0">
                                            <label class="form-label small text-muted">Value / Percentage</label>
                                            <input type="text" class="form-control" 
                                                   name="components[{{ $component->id }}][calculation_value]" 
                                                   value="{{ $component->calculation_value }}" 
                                                   placeholder="Enter value or %">
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                        
                        <!-- Provident Fund Settings -->
                        <div class="settings-card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5><i class="fas fa-piggy-bank me-2"></i>Provident Fund Settings</h5>
                                <div class="form-check form-switch custom-switch">
                                    <input class="form-check-input" type="checkbox" id="switch_pf" checked>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label class="form-label">Employee Share (%)</label>
                                            <input class="form-control" type="text" name="pf_employee_share" value="{{ $settings['salary_pf_employee_share'] ?? '' }}" placeholder="Enter employee share">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label class="form-label">Organization Share (%)</label>
                                            <input class="form-control" type="text" name="pf_employer_share" value="{{ $settings['salary_pf_employer_share'] ?? '' }}" placeholder="Enter organization share">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- ESI Settings -->
                        <div class="settings-card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5><i class="fas fa-file-medical me-2"></i>ESI Settings</h5>
                                <div class="form-check form-switch custom-switch">
                                    <input class="form-check-input" type="checkbox" id="switch_esi">
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label class="form-label">Employee Share (%)</label>
                                            <input class="form-control" type="text" name="esi_employee_share" value="{{ $settings['salary_esi_employee_share'] ?? '' }}" placeholder="Enter employee share">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label class="form-label">Organization Share (%)</label>
                                            <input class="form-control" type="text" name="esi_employer_share" value="{{ $settings['salary_esi_employer_share'] ?? '' }}" placeholder="Enter organization share">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- TDS Settings -->
                        <div class="settings-card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5><i class="fas fa-file-invoice-dollar me-2"></i>TDS <small class="text-white-50 text-sm ms-2">(Annual Salary)</small></h5>
                                <div class="form-check form-switch custom-switch">
                                    <input class="form-check-input" type="checkbox" id="switch_tds">
                                </div>
                            </div>
                            <div class="card-body">
                                <div id="tds_rows_container">
                                    <div class="row g-3 align-items-end mb-3 tds-row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="form-label">Salary From</label>
                                                <input class="form-control" type="number" name="tds_slabs[][from]" placeholder="From">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="form-label">Salary To</label>
                                                <input class="form-control" type="number" name="tds_slabs[][to]" placeholder="To">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label class="form-label">%</label>
                                                <input class="form-control" type="number" step="0.01" name="tds_slabs[][percentage]" placeholder="%">
                                            </div>
                                        </div>
                                        <div class="col-md-1">
                                            <div class="form-group">
                                                <label class="form-label d-block">&nbsp;</label>
                                                <button class="btn btn-danger btn-icon remove-tds-row d-flex align-items-center justify-content-center mx-auto" type="button" style="width: 42px; height: 42px; padding: 0; border-radius: 50%;"><i class="fas fa-trash-alt"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-2 ms-auto">
                                        <button class="btn btn-primary w-100" type="button" id="add_tds_row_btn"><i class="fa fa-plus"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Submit Button -->
                        <div class="row mb-5">
                            <div class="col-12">
                                <div class="d-flex justify-content-end">
                                    <button class="btn btn-primary btn-lg" type="submit">
                                        <i class="fas fa-save me-2"></i> Save Salary Settings
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const container = document.getElementById('tds_rows_container');
        const addBtn = document.getElementById('add_tds_row_btn');

        // Add Row
        if(addBtn && container) {
            addBtn.addEventListener('click', function() {
                const rows = container.querySelectorAll('.tds-row');
                if(rows.length > 0) {
                    const lastRow = rows[rows.length - 1];
                    const newRow = lastRow.cloneNode(true);
                    
                    // Clear inputs
                    newRow.querySelectorAll('input').forEach(input => input.value = '');
                    
                    container.appendChild(newRow);
                }
            });
        }

        // Remove Row (Event Delegation)
        if(container) {
            container.addEventListener('click', function(e) {
                // Check if the clicked element or its parent is the delete button
                const deleteBtn = e.target.closest('.remove-tds-row');
                if (deleteBtn) {
                    const rows = container.querySelectorAll('.tds-row');
                    if (rows.length > 1) {
                        deleteBtn.closest('.tds-row').remove();
                    } else {
                        // Optional: Clear the input of the last remaining row if user deletes it
                        deleteBtn.closest('.tds-row').querySelectorAll('input').forEach(input => input.value = '');
                        // alert('At least one row is required.'); 
                    }
                }
            });
        }
    });
</script>