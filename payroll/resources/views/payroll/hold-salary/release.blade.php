@extends('layouts.master')
@section('title', 'Release Salary')
@section('content')

<style>
    .page-header-card {
        background: white;
        border-radius: 1rem;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        margin-bottom: 2rem;
    }
    
    .page-header-gradient {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%); /* Green theme for Release */
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

    .form-control {
        border-radius: 0.5rem;
        padding: 0.75rem 1rem;
        border: 1px solid #d1d5db;
    }

    .form-control:focus {
        border-color: #10b981;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
    }
    
    .btn-success {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        border: none;
        padding: 0.75rem 2rem;
        border-radius: 0.5rem;
        font-weight: 500;
    }
    
    .btn-success:hover {
        background: linear-gradient(135deg, #34d399 0%, #047857 100%);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
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

    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
        padding: 1.5rem;
        background: #f9fafb;
        border-radius: 0.75rem;
    }
    
    .info-item label {
        display: block;
        font-size: 0.75rem;
        text-transform: uppercase;
        color: #6b7280;
        margin-bottom: 0.25rem;
        font-weight: 600;
    }
    
    .info-item span {
        font-weight: 600;
        color: #1f2937;
        font-size: 1rem;
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
                        <i class="fas fa-check-circle fa-lg" style="color: rgba(255,255,255,0.9);"></i>
                    </div>
                    <div class="ms-3">
                        <h1 class="page-header-title">Release Salary</h1>
                        <p class="page-header-subtitle">Process salary release for {{ $hold->employee->name }}</p>
                    </div>
                </div>
            </div>
            <div class="p-3 d-flex justify-content-between align-items-center">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('hold-salary.index') }}">Hold Salary</a></li>
                        <li class="breadcrumb-item active">Release</li>
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
                    <h5 class="form-section-title">Current Hold Status</h5>
                    
                    <div class="info-grid">
                        <div class="info-item">
                            <label>Employee Name</label>
                            <span>{{ $hold->employee->name }}</span>
                        </div>
                        <div class="info-item">
                            <label>Employee ID</label>
                            <span>{{ $hold->employee->employee_id }}</span>
                        </div>
                        <div class="info-item">
                            <label>Hold Type</label>
                            <span>
                                @if($hold->hold_type == 'month')
                                    One Month ({{ \Carbon\Carbon::createFromDate($hold->payout_year, $hold->payout_month, 1)->format('M Y') }})
                                @else
                                    Indefinite
                                @endif
                            </span>
                        </div>
                        <div class="info-item">
                            <label>Held Since</label>
                            <span>{{ $hold->created_at->format('d M Y') }}</span>
                        </div>
                    </div>

                    <form action="{{ route('hold-salary.release', $hold->id) }}" method="POST">
                        @csrf
                        
                        <h5 class="form-section-title">Release Details</h5>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Release Target Month <span class="text-danger">*</span></label>
                                <input type="month" name="release_month_year" id="release_month_year" class="form-control" 
                                       value="{{ $hold->hold_type == 'month' ? $hold->payout_year . '-' . str_pad($hold->payout_month, 2, '0', STR_PAD_LEFT) : date('Y-m') }}" 
                                       required 
                                       @if($hold->hold_type == 'month')
                                         min="{{ $hold->payout_year }}-{{ str_pad($hold->payout_month, 2, '0', STR_PAD_LEFT) }}"
                                         max="{{ $hold->payout_year }}-{{ str_pad($hold->payout_month, 2, '0', STR_PAD_LEFT) }}"
                                         title="One Month hold must be released in the same month."
                                       @endif
                                >
                                <div class="form-text">Arrears will be added to this payroll month.</div>
                            </div>

                            @if($hold->hold_type == 'indefinite')
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Calculate Till Month (For Estimation)</label>
                                <input type="month" id="calculate_till_month" class="form-control" value="{{ date('Y-m') }}">
                                <div class="form-text text-primary" id="calculation_result">
                                    <i class="fas fa-calculator me-1"></i> Select a month to estimate arrears.
                                </div>
                            </div>
                            @endif

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Release Amount <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">₹</span>
                                    <input type="number" step="0.01" name="amount" id="release_amount" class="form-control" placeholder="Enter amount to release">
                                </div>
                                <div class="form-text">Leave blank to release actual salary based on structure.</div>
                            </div>

                            <div class="col-md-12 mb-3">
                                <label class="form-label">Release Remarks</label>
                                <textarea name="remarks" class="form-control" rows="3" placeholder="Enter remarks for this release..."></textarea>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <a href="{{ route('hold-salary.index') }}" class="btn btn-light me-2">Cancel</a>
                            <button type="submit" class="btn btn-success" onclick="return confirm('Are you sure you want to release this salary? This will add arrears to the selected payroll month.');">
                                <i class="fas fa-check-circle me-2"></i> Confirm Release
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const holdType = "{{ $hold->hold_type }}";
        const holdStartYear = {{ $hold->payout_year }};
        const holdStartMonth = {{ $hold->payout_month }}; // 1-12
        const currentGross = {{ $currentMonthlyGross ?? 0 }};
        
        const calcInput = document.getElementById('calculate_till_month');
        const resultDiv = document.getElementById('calculation_result');
        const amountInput = document.getElementById('release_amount');

        if (holdType === 'indefinite' && calcInput) {
            function updateCalculation() {
                if (!calcInput.value) return;
                
                const [y, m] = calcInput.value.split('-').map(Number);
                
                // Calculate difference in months
                // Start: holdStartYear, holdStartMonth
                // End: y, m
                // Formula: (EndYear - StartYear) * 12 + (EndMonth - StartMonth) + 1 (inclusive)
                
                let monthsDiff = (y - holdStartYear) * 12 + (m - holdStartMonth) + 1;
                
                if (monthsDiff < 1) {
                    resultDiv.innerHTML = '<span class="text-danger">Invalid Date Range</span>';
                    return;
                }
                
                let estimated = (currentGross * monthsDiff).toFixed(2);
                
                resultDiv.innerHTML = `
                    <span class="fw-bold text-dark">
                        ${monthsDiff} Month(s) × ₹${currentGross.toLocaleString()} ≈ ₹${parseFloat(estimated).toLocaleString()}
                    </span>
                `;
                
                // Optional: Auto-fill amount? maybe not force it, just suggest
                // amountInput.value = estimated; 
            }
            
            calcInput.addEventListener('change', updateCalculation);
            updateCalculation(); // run info on load
        }
    });
</script>
