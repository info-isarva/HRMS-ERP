@php
    // Define routes for each step
    $stepRoutes = [
        1 => route('payroll.index'),
        2 => isset($month) && isset($year) ? route('payroll.attendance', ['month' => $month, 'year' => $year, 'location_id' => request('location_id')]) : null,
        3 => isset($month) && isset($year) ? route('payroll.salary-breakdown', ['month' => $month, 'year' => $year, 'location_id' => request('location_id')]) : null,
        4 => isset($month) && isset($year) ? route('payroll.comparison', ['month' => $month, 'year' => $year, 'location_id' => request('location_id')]) : null,
        5 => isset($month) && isset($year) ? route('payroll.finalize', ['month' => $month, 'year' => $year, 'location_id' => request('location_id')]) : null,
    ];

    // Define step access conditions
    $stepAccessible = [
        1 => true, // Always accessible
        2 => isset($month) && isset($year), // Only if month/year selected
        3 => isset($month) && isset($year) && isset($attendanceSaved) && $attendanceSaved, // Only if attendance saved
        4 => isset($month) && isset($year) && isset($attendanceSaved) && $attendanceSaved, // Only if attendance saved
        5 => false, // Never clickable - use Finalize button instead
    ];

    $steps = [
        1 => ['title' => 'Select Payout Month', 'status' => 'pending', 'route' => $stepRoutes[1], 'accessible' => $stepAccessible[1]],
        2 => ['title' => 'Add Attendance', 'status' => 'pending', 'route' => $stepRoutes[2], 'accessible' => $stepAccessible[2]],
        3 => ['title' => 'Review Salary', 'status' => 'pending', 'route' => $stepRoutes[3], 'accessible' => $stepAccessible[3]],
        4 => ['title' => 'Compare Payroll', 'status' => 'pending', 'route' => $stepRoutes[4], 'accessible' => $stepAccessible[4]],
        5 => ['title' => 'Submit Payroll', 'status' => 'pending', 'route' => $stepRoutes[5], 'accessible' => $stepAccessible[5]],
    ];

    // If salary is finalized, mark all steps as completed and accessible
    if (isset($isFinalized) && $isFinalized) {
        foreach ($steps as $stepNumber => $step) {
            $steps[$stepNumber]['status'] = 'completed';
            // Step 5 is never clickable, but set accessible to true when finalized to avoid opacity
            $steps[$stepNumber]['accessible'] = true;
        }
    } else {
        foreach ($steps as $stepNumber => $step) {
            if ($stepNumber < $currentStep) {
                $steps[$stepNumber]['status'] = 'completed';
                // Completed steps should remain accessible (user can go back)
                $steps[$stepNumber]['accessible'] = true;
            } elseif ($stepNumber == $currentStep) {
                $steps[$stepNumber]['status'] = 'active';
            } else {
                $steps[$stepNumber]['status'] = 'pending';
                // Future steps use the original accessibility rules
            }
        }
    }
@endphp


<div class="steps-container">
    <div class="d-flex justify-content-between position-relative">
        @foreach($steps as $stepNumber => $step)
        <div class="step-item text-center flex-fill">
            @php
                // Determine if step is clickable
                // Step 5 is never clickable, others are clickable if accessible
                $isClickable = $step['route'] && $step['accessible'] && $stepNumber != 5;
            @endphp
            
            @if($isClickable)
                <a href="{{ $step['route'] }}" class="step-link" title="Click to go to {{ $step['title'] }}">
            @elseif(!$step['accessible'])
                <div class="step-link step-disabled" title="Complete previous steps first">
            @else
                <div class="step-link">
            @endif
            
            <div class="step-icon-wrapper">
                <div class="step-icon 
                    @if($step['status'] === 'completed') bg-success text-white
                    @elseif($step['status'] === 'active') bg-primary text-white
                    @else bg-light text-muted @endif
                    @if($isClickable) clickable 
                    @elseif(!$step['accessible']) disabled @endif">
                    @if($step['status'] === 'completed')
                        <i class="fa fa-check"></i>
                    @elseif(!$step['accessible'])
                        <i class="fa fa-lock"></i>
                    @else
                        {{ $stepNumber }}
                    @endif
                </div>
            </div>
            <div class="step-label mt-2 
                @if($step['status'] === 'active') font-weight-bold text-primary 
                @elseif(!$step['accessible']) text-muted @endif">
                {{ $step['title'] }}
            </div>
            
            @if($isClickable)
                </a>
            @else
                </div>
            @endif
        </div>
        @endforeach
        <!-- Custom Progress Line -->
        <div class="progress-line-custom">
            @php
                // Calculate progress more precisely based on 5 steps
                if (isset($isFinalized) && $isFinalized) {
                    $progressWidth = 100; // When finalized, show complete progress to last step
                } else {
                    // Progress between steps: 0%, 25%, 50%, 75%, 100%
                    $progressWidth = ($currentStep - 1) * 25; // Divide by 4 since we have 4 connections between 5 steps
                }
            @endphp
            <div class="progress-fill" style="width: {{ $progressWidth }}%;"></div>
        </div>
    </div>
</div>

{{-- @push('styles') --}}
<style>
    /* Progress bar styles - matching salary-breakdown design */
    .steps-container {
        padding: 20px 0;
        margin-bottom: 20px;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        position: relative;
    }

    .steps-container .d-flex {
        display: flex;
        justify-content: space-between;
        align-items: center;
        position: relative;
        padding: 0 40px;
    }

    .steps-container .step-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        flex: 1;
        position: relative;
        z-index: 2;
    }

    /* Step link styling */
    .steps-container .step-link {
        text-decoration: none;
        color: inherit;
        display: flex;
        flex-direction: column;
        align-items: center;
        width: 100%;
    }

    .steps-container .step-icon {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 18px;
        margin-bottom: 12px;
        position: relative;
        transition: all 0.3s ease;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    .steps-container .step-icon.clickable {
        cursor: pointer;
    }

    .steps-container .step-icon.clickable:hover {
        transform: scale(1.15) translateY(-3px);
        box-shadow: 0 6px 20px rgba(0,0,0,0.25);
    }

    .steps-container .step-icon.disabled {
        cursor: not-allowed;
        opacity: 0.5;
    }

    .steps-container .step-disabled {
        cursor: not-allowed;
    }

    .steps-container .step-disabled .step-icon:hover {
        transform: none;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    .steps-container .step-label {
        font-size: 15px;
        font-weight: 600;
        margin-bottom: 4px;
        color: #495057;
        transition: color 0.3s ease;
    }

    .steps-container .step-link:hover .step-label {
        color: #007bff;
    }

    .steps-container .step-icon.bg-success {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%) !important;
        color: white !important;
    }

    .steps-container .step-icon.bg-primary {
        background: linear-gradient(135deg, #007bff 0%, #6f42c1 100%) !important;
        color: white !important;
        transform: scale(1.1);
    }

    .steps-container .step-icon.bg-light {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%) !important;
        color: #6c757d !important;
        border: 2px solid #dee2e6;
    }

    .steps-container .step-label.font-weight-bold.text-primary {
        color: #007bff !important;
        font-weight: 700 !important;
    }

    /* Progress line background - only between step connection points */
    .progress-line-custom::before {
        content: '';
        position: absolute;
        top: 30px;
        left: 70px;
        width: calc(100% - 140px); /* Stop at the last step connection point */
        height: 4px;
        background: linear-gradient(135deg, #e9ecef 0%, #dee2e6 100%);
        border-radius: 2px;
        z-index: 1;
    }

    /* Progress line fill - matches the background line */
    .progress-fill {
        position: absolute;
        top: 30px;
        left: 70px;
        height: 4px;
        max-width: calc(100% - 140px); /* Never extend beyond the background line */
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        border-radius: 2px;
        z-index: 1;
        transition: width 0.6s ease;
    }

    .progress-line-custom {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        pointer-events: none;
    }

    /* Responsive progress steps */
    @media (max-width: 768px) {
        .steps-container {
            padding: 15px 0;
        }
        
        .steps-container .d-flex {
            padding: 0 20px;
        }
        
        .steps-container .step-icon {
            width: 50px;
            height: 50px;
            font-size: 16px;
        }
        
        .steps-container .step-label {
            font-size: 13px;
        }
        
        .progress-line-custom::before,
        .progress-fill {
            top: 25px;
            left: 45px;
            height: 3px;
        }
        
        .progress-line-custom::before {
            width: calc(100% - 90px);
        }
        
        .progress-fill {
            max-width: calc(100% - 90px);
        }
    }

    @media (max-width: 576px) {
        .steps-container .step-icon {
            width: 45px;
            height: 45px;
            font-size: 14px;
        }
        
        .steps-container .step-label {
            font-size: 11px;
            line-height: 1.2;
        }
        
        .progress-line-custom::before,
        .progress-fill {
            top: 22px;
            left: 35px;
            height: 3px;
        }
        
        .progress-line-custom::before {
            width: calc(100% - 70px);
        }
        
        .progress-fill {
            max-width: calc(100% - 70px);
        }
    }

    /* Hover effects for better UX */
    .steps-container .step-item:hover .step-icon.bg-light {
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(0,0,0,0.2);
    }

    .steps-container .step-item:hover .step-icon.bg-success,
    .steps-container .step-item:hover .step-icon.bg-primary {
        box-shadow: 0 6px 20px rgba(0,0,0,0.3);
    }
</style>
{{-- @endpush --}}