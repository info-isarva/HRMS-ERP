@extends('layouts.master')
@section('title', 'Add Salary Hold')
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
        top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(0, 0, 0, 0.05);
    }
    
    .page-header-circle-1 {
        position: absolute;
        top: -1rem; right: -1rem;
        width: 6rem; height: 6rem;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
    }
    
    .page-header-circle-2 {
        position: absolute;
        bottom: -1rem; left: -1rem;
        width: 8rem; height: 8rem;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
    }
    
    .page-header-icon-box {
        width: 4rem; height: 4rem;
        background: rgba(255, 255, 255, 0.2);
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: 1rem;
        display: flex; align-items: center; justify-content: center;
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
    
    /* Form Card */
    .form-card {
        background: white;
        border-radius: 1rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
        padding: 2rem;
        border: 1px solid #e5e7eb;
    }

    .form-section-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: #374151;
        margin-bottom: 1.5rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid #f3f4f6;
    }

    .form-control, .form-select {
        border-radius: 0.5rem;
        padding: 0.75rem 1rem;
        border: 1px solid #d1d5db;
    }

    .form-control:focus, .form-select:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }
    
    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        padding: 0.75rem 2rem;
        border-radius: 0.5rem;
        font-weight: 500;
    }
    
    .btn-primary:hover {
        background: linear-gradient(135deg, #5a67d8 0%, #6b46c1 100%);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
    }

    .btn-light {
        background: #f3f4f6;
        border: 1px solid #e5e7eb;
        color: #4b5563;
        padding: 0.75rem 2rem;
        border-radius: 0.5rem;
        font-weight: 500;
    }
    
    .btn-light:hover {
        background: #e5e7eb;
        color: #1f2937;
    }
</style>

<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header-card">
            <div class="page-header-gradient">
                <div class="page-header-pattern"></div>
                <div class="page-header-circle-1"></div>
                <div class="page-header-circle-2"></div>
                <div class="d-flex align-items-center">
                    <div class="page-header-icon-box">
                        <i class="fas fa-plus fa-lg" style="color: rgba(255,255,255,0.9);"></i>
                    </div>
                    <div class="ms-3">
                        <h1 class="page-header-title">Add Salary Hold</h1>
                        <p class="page-header-subtitle">Create a new salary hold for an employee</p>
                    </div>
                </div>
            </div>
            <div class="p-3 d-flex justify-content-between align-items-center">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('hold-salary.index') }}">Hold Salary</a></li>
                        <li class="breadcrumb-item active">Add Hold</li>
                    </ol>
                </nav>
                <a href="{{ route('hold-salary.index') }}" class="btn btn-light">
                    <i class="fas fa-arrow-left me-2"></i> Back to List
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="form-card">
                    <form action="{{ route('hold-salary.store') }}" method="POST">
                        @csrf
                        
                        <h5 class="form-section-title">Hold Details</h5>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Employee <span class="text-danger">*</span></label>
                                <select name="employee_id" class="form-select select2" required>
                                    <option value="">Select Employee</option>
                                    @foreach($employees as $emp)
                                        <option value="{{ $emp->id }}">{{ $emp->name }} ({{ $emp->employee_id }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Hold Type <span class="text-danger">*</span></label>
                                <select name="hold_type" id="hold_type" class="form-select" required>
                                    <option value="month">For One Month</option>
                                    <option value="indefinite">Indefinite (Till Manual Release)</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3" id="month_wrapper">
                                <label class="form-label">Payout Month <span class="text-danger">*</span></label>
                                <input type="month" name="payout_month_year" class="form-control" value="{{ date('Y-m') }}" required>
                                <div class="form-text">The salary for this specific month will be held.</div>
                            </div>

                            <div class="col-md-12 mb-3">
                                <label class="form-label">Remarks</label>
                                <textarea name="remarks" class="form-control" rows="4" placeholder="Enter reason for holding salary..."></textarea>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <a href="{{ route('hold-salary.index') }}" class="btn btn-light me-2">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i> Save Hold
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Select2
        $('.select2').select2({
            width: '100%',
            placeholder: "Select Employee",
            allowClear: true
        });

        // Toggle Month Input based on Hold Type
        const holdTypeSelect = document.getElementById('hold_type');
        const monthWrapper = document.getElementById('month_wrapper');
        const monthInput = monthWrapper.querySelector('input');

        function toggleMonthInput() {
            if (holdTypeSelect.value === 'month') {
                monthWrapper.style.display = 'block';
                monthInput.setAttribute('required', 'required');
            } else {
                monthWrapper.style.display = 'none';
                monthInput.removeAttribute('required');
            }
        }

        holdTypeSelect.addEventListener('change', toggleMonthInput);
        toggleMonthInput(); // Initial check
    });
</script>
@endsection
