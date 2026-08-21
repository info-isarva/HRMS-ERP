@php
    use App\Helpers\FinancialYearHelper;
    use Carbon\Carbon;
    
    $currentFY = FinancialYearHelper::getCurrentFinancialYear();
    $allFYs = FinancialYearHelper::getAllFinancialYears();
    $selectedFY = FinancialYearHelper::getSelectedFinancialYear();
    
    // Calculate Display Label based on preference: Selected > Current > Fallback
    $displayFY = $selectedFY ?? $currentFY;
    $displayLabel = null;
    
    if ($displayFY) {
        $start = data_get($displayFY, 'start_date');
        $end = data_get($displayFY, 'end_date');
        
        if ($start && $end) {
            // Format as "2024-25"
            try {
                $displayLabel = Carbon::parse($start)->format('Y') . '-' . Carbon::parse($end)->format('y');
            } catch (\Exception $e) {
                // Ignore parsing errors
            }
        }
        
        // Fallback to name if dates invalid
        if (!$displayLabel) {
            $displayLabel = data_get($displayFY, 'year_name');
        }
    }
    
    // Ultimate fallback if no FY found
    if (!$displayLabel) {
        $displayLabel = (date('n') < 4) 
            ? ((date('Y') - 1) . '-' . date('y')) 
            : (date('Y') . '-' . (date('y') + 1));
    }
@endphp

<div class="financial-year-switcher d-flex align-items-center">
    <div class="d-flex align-items-center">
        <i class="fas fa-calendar-alt text-white me-2" style="font-size: 16px;"></i>
        <span class="text-white font-weight-medium me-2" style="font-size: 15px;">FY:</span>
        
        <div class="dropdown">
            <button class="btn btn-outline-light dropdown-toggle" 
                    type="button" 
                    id="fyDropdown" 
                    data-toggle="dropdown" 
                    aria-haspopup="true" 
                    aria-expanded="false"
                    style="min-width: 160px; font-size: 14px; font-weight: 500; padding: 8px 15px; border: 2px solid rgba(255,255,255,0.5); background: rgba(255,255,255,0.15);">
                <div class="d-flex justify-content-between align-items-center w-100">
                    <span>{{ $displayLabel }}</span>
                    @if($selectedFY && $currentFY)
                        @if($selectedFY->id === $currentFY->id)
                            <span class="badge bg-success ms-2" style="font-size: 10px; color: white !important;">Current</span>
                        @else
                            <span class="badge bg-warning text-dark ms-2" style="font-size: 10px;">Historical</span>
                        @endif
                    @endif
                </div>
            </button>
            
            <div class="dropdown-menu dropdown-menu-right shadow-lg" 
                 aria-labelledby="fyDropdown" 
                 style="min-width: 280px; max-height: 400px; overflow-y: auto; border: none; border-radius: 8px;">
                
                <div class="dropdown-header bg-light" style="font-size: 13px; font-weight: 600; color: #495057; padding: 12px 20px;">
                    <i class="fas fa-calendar-alt me-2 text-primary"></i>Select Financial Year
                </div>
                <div class="dropdown-divider my-0"></div>
                
                @if($allFYs && $allFYs->count() > 0)
                    @foreach($allFYs as $fy)
                        <a class="dropdown-item fy-option {{ $selectedFY && $selectedFY->id === $fy->id ? 'active' : '' }}" 
                           href="#" 
                           data-fy-id="{{ $fy->id }}"
                           style="padding: 12px 20px; font-size: 14px; border-left: 4px solid transparent; {{ $selectedFY && $selectedFY->id === $fy->id ? 'border-left-color: #007bff; background-color: #f8f9fa; font-weight: 600;' : '' }}">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="font-weight-medium" style="color: #212529;">{{ $fy->year_name }}</div>
                                    <small class="text-muted">
                                        {{ \Carbon\Carbon::parse($fy->start_date)->format('M d, Y') }} - 
                                        {{ \Carbon\Carbon::parse($fy->end_date)->format('M d, Y') }}
                                    </small>
                                </div>
                                <div class="text-end">
                                    @if($currentFY && $fy->id === $currentFY->id)
                                        <span class="badge bg-success" style="color: white !important;">Current</span>
                                    @else
                                        <span class="badge bg-warning text-dark">Historical</span>
                                    @endif
                                    @if($selectedFY && $selectedFY->id === $fy->id)
                                        <i class="fas fa-check text-success ms-2"></i>
                                    @endif
                                </div>
                            </div>
                        </a>
                    @endforeach
                @else
                    <div class="dropdown-item-text text-center text-muted" style="padding: 20px;">
                        <i class="fas fa-exclamation-circle fa-2x mb-2"></i>
                        <div>No financial years found</div>
                        <small>Please contact administrator</small>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle financial year switching
    document.querySelectorAll('.fy-option').forEach(function(option) {
        option.addEventListener('click', function(e) {
            e.preventDefault();
            
            const fyId = this.getAttribute('data-fy-id');
            const currentSelectedId = {{ $selectedFY->id ?? 'null' }};
            
            if (fyId == currentSelectedId) {
                return; // Already selected
            }
            
            // Show loading state
            const button = document.getElementById('fyDropdown');
            const originalText = button.innerHTML;
            button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Switching...';
            button.disabled = true;
            
            // Make AJAX request to switch financial year
            fetch('{{ route("financial-year.switch") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    financial_year_id: fyId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message briefly
                    button.innerHTML = '<i class="fas fa-check me-2"></i>Switched!';
                    button.classList.remove('btn-outline-light');
                    button.classList.add('btn-success');
                    
                    // Reload page after short delay
                    setTimeout(() => {
                        window.location.reload();
                    }, 800);
                } else {
                    throw new Error(data.message || 'Failed to switch financial year');
                }
            })
            .catch(error => {
                console.error('Error switching financial year:', error);
                
                // Show error state
                button.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i>Error';
                button.classList.remove('btn-outline-light');
                button.classList.add('btn-danger');
                
                // Reset after delay
                setTimeout(() => {
                    button.innerHTML = originalText;
                    button.classList.remove('btn-danger');
                    button.classList.add('btn-outline-light');
                    button.disabled = false;
                }, 2000);
                
                // Show error message
                if (typeof toastr !== 'undefined') {
                    toastr.error('Failed to switch financial year. Please try again.');
                } else {
                    alert('Failed to switch financial year. Please try again.');
                }
            });
        });
    });
});
</script>

<style>
.financial-year-switcher {
    padding: 8px 15px;
    border-radius: 6px;
    background: rgba(255,255,255,0.15);
    transition: all 0.3s ease;
    border: 1px solid rgba(255,255,255,0.2);
}

.financial-year-switcher:hover {
    background: rgba(255,255,255,0.25);
    border-color: rgba(255,255,255,0.4);
}

.financial-year-switcher .btn-outline-light {
    color: #fff !important;
    border-color: rgba(255,255,255,0.5) !important;
    background: rgba(255,255,255,0.15) !important;
    transition: all 0.3s ease;
}

.financial-year-switcher .btn-outline-light:hover,
.financial-year-switcher .btn-outline-light:focus,
.financial-year-switcher .btn-outline-light.show {
    background: rgba(255,255,255,0.25) !important;
    border-color: rgba(255,255,255,0.7) !important;
    color: #fff !important;
    box-shadow: 0 0 0 0.2rem rgba(255,255,255,0.25);
}

.financial-year-switcher .dropdown-menu {
    border: none;
    border-radius: 8px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    margin-top: 8px;
}

.financial-year-switcher .dropdown-item {
    transition: all 0.1s ease;
}

.financial-year-switcher .dropdown-item {
    margin: 0;
}

.financial-year-switcher .dropdown-item:hover {
    background-color: #f8f9fa;
    border-left-color: #007bff !important;
}

.financial-year-switcher .dropdown-item.active {
    background-color: #e3f2fd;
    border-left-color: #007bff !important;
}

.financial-year-switcher .badge {
    font-size: 10px;
    padding: 3px 8px;
    border-radius: 12px;
}

/* Header integration - ensure proper spacing */
.header .nav-item.me-3 {
    margin-right: 1.5rem !important;
}

/* Responsive design */
@media (max-width: 992px) {
    .financial-year-switcher {
        padding: 6px 12px;
    }
    
    .financial-year-switcher span {
        font-size: 13px !important;
    }
    
    .financial-year-switcher .btn {
        min-width: 140px;
        font-size: 13px !important;
        padding: 6px 12px !important;
    }
    
    .financial-year-switcher .dropdown-menu {
        min-width: 260px;
    }
}

@media (max-width: 768px) {
    .financial-year-switcher {
        padding: 4px 8px;
    }
    
    .financial-year-switcher span {
        font-size: 12px !important;
    }
    
    .financial-year-switcher .btn {
        min-width: 120px;
        font-size: 12px !important;
        padding: 4px 8px !important;
    }
    
    .header .nav-item.me-3 {
        margin-right: 0.75rem !important;
    }
}

@media (max-width: 576px) {
    .financial-year-switcher span:first-child {
        display: none; /* Hide "Financial Year:" label on very small screens */
    }
    
    .financial-year-switcher .btn {
        min-width: 100px;
    }
    
    .financial-year-switcher .dropdown-menu {
        min-width: 240px;
    }
}
</style>
