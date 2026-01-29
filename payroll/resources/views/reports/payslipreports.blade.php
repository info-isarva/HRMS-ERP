
@extends('layouts.master')
@section('content')
    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <!-- Page Content -->
        <div class="content container-fluid">

            <!-- Page Header -->
            <div class="page-header">
                <div class="row">
                    <div class="col-sm-12">
                        <h3 class="page-title">Payslip Reports</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="index.html">Dashboard</a></li>
                            <li class="breadcrumb-item active">Payslip Reports</li>
                        </ul>
                    </div>
                </div>
            </div>
            <!-- /Page Header -->
            
            <!-- Financial Year Info -->
            @if(isset($fyContext) && $fyContext['selectedFinancialYear'])
                <div class="card mb-4">
                    <div class="card-body py-3">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-calendar-alt text-primary me-2"></i>
                                    <span class="font-weight-medium">Viewing Financial Year: 
                                        <strong>{{ $fyContext['selectedFinancialYear']->year_name }}</strong>
                                    </span>
                                    @if(!$fyContext['isFinancialYearEditable'])
                                        <span class="badge bg-warning text-dark ms-2">Read-only (Historical Data)</span>
                                    @else
                                        <span class="badge bg-success ms-2">Current Year</span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-4 text-end">
                                <small class="text-muted">
                                    {{ \Carbon\Carbon::parse($fyContext['selectedFinancialYear']->start_date)->format('M d, Y') }} - 
                                    {{ \Carbon\Carbon::parse($fyContext['selectedFinancialYear']->end_date)->format('M d, Y') }}
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            
            <!-- Content Starts -->
            <!-- Search Filter -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Filter Payslip Reports</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('form/payslip/reports/page') }}">
                        <div class="row">
                            <div class="col-sm-6 col-md-3">  
                                <div class="form-group">
                                    <label>Employee Name</label>
                                    <input type="text" name="employee_name" class="form-control" 
                                           value="{{ request('employee_name') }}" placeholder="Enter employee name">
                                </div>
                            </div>
                            <div class="col-sm-6 col-md-3">  
                                <div class="form-group">
                                    <label>Payout Month</label>
                                    <select name="payout_month_year" class="form-control form-select" id="payout_month_year">
                                        <option value="">-- Select Month --</option>
                                        @if(isset($availableMonths))
                                            @foreach($availableMonths as $month)
                                                @php
                                                    $monthValue = str_pad($month['payout_month'], 2, '0', STR_PAD_LEFT) . '-' . $month['payout_year'];
                                                    $isSelected = request('payout_month_year') == $monthValue;
                                                @endphp
                                                <option value="{{ $monthValue }}" 
                                                        data-status="{{ $month['status'] ?? 'not_processed' }}"
                                                        {{ $isSelected ? 'selected' : '' }}>
                                                    {{ $month['label'] }}
                                                    @if(isset($month['status']))
                                                        @if($month['status'] === 'completed')
                                                            - Completed
                                                        @elseif($month['status'] === 'pending')
                                                            - Pending
                                                        @elseif($month['status'] === 'progress')
                                                            - In Progress
                                                        @else
                                                            - Not Processed
                                                        @endif
                                                    @endif
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-6 col-md-3">  
                                <div class="form-group">
                                    <label>Department</label>
                                    <select name="department" class="form-control form-select">
                                        <option value="">-- All Departments --</option>
                                        @if(isset($departments))
                                            @foreach($departments as $dept)
                                                <option value="{{ $dept->id }}" 
                                                        {{ request('department') == $dept->id ? 'selected' : '' }}>
                                                    {{ $dept->department_name }}
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-6 col-md-3">  
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <button type="submit" id="search-btn" class="btn btn-success btn-block">Search</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <!-- /Search Filter -->
            
            <div class="row">
                <div class="col-md-12">
                    <div class="table-responsive">
                        <table class="table table-striped custom-table mb-0 datatable">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Employee Name</th>
                                    <th>Paid Amount</th>
                                    <th>Payment Month</th>
                                    <th>Payment Year</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>1</td>
                                    <td>
                                        <h2 class="table-avatar">
                                            <a href="profile.html" class="avatar"><img alt="" src="assets/img/profiles/avatar-13.jpg"></a>
                                            <a href="profile.html">Bernardo Galaviz <span>Web Developer</span></a>
                                        </h2>
                                    </td>
                                    <td>$200</td>
                                    <td>Apr</td>
                                    <td>2019</td>
                                    <td class="text-center"> <a href="#" class="btn btn-sm btn-primary">PDF</a></td>
                                </tr>
                                <tr>
                                    <td>2</td>
                                    <td>
                                        <h2 class="table-avatar">
                                            <a href="profile.html" class="avatar"><img alt="" src="assets/img/profiles/avatar-12.jpg"></a>
                                            <a href="profile.html">Jeffrey Warden <span>Web Developer</span></a>
                                        </h2>
                                    </td>
                                    <td>$300</td>
                                    <td>Dec</td>
                                    <td>2020</td>
                                    <td class="text-center"> <a href="#" class="btn btn-sm btn-primary">PDF</a></td>
                                </tr>
                                <tr>
                                    <td>3</td>
                                    <td>
                                        <h2 class="table-avatar">
                                            <a href="profile.html" class="avatar"><img alt="" src="assets/img/profiles/avatar-02.jpg"></a>
                                            <a href="profile.html">John Doe <span>Web Designer</span></a>
                                        </h2>
                                    </td>
                                    <td>$400</td>
                                    <td>Jun</td>
                                    <td>2020</td>
                                    <td class="text-center"> <a href="#" class="btn btn-sm btn-primary">PDF</a></td>
                                </tr>
                                <tr>
                                    <td>4</td>
                                    <td>
                                        <h2 class="table-avatar">
                                            <a href="profile.html" class="avatar"><img alt="" src="assets/img/profiles/avatar-10.jpg"></a>
                                            <a href="profile.html">John Smith <span>Android Developer</span></a>
                                        </h2>
                                    </td>
                                    <td>$500</td>
                                    <td>Feb</td>
                                    <td>2020</td>
                                    <td class="text-center"> <a href="#" class="btn btn-sm btn-primary">PDF</a></td>
                                </tr>
                                <tr>
                                    <td>5</td>
                                    <td>
                                        <h2 class="table-avatar">
                                            <a href="profile.html" class="avatar"><img alt="" src="assets/img/profiles/avatar-05.jpg"></a>
                                            <a href="profile.html">Mike Litorus <span>IOS Developer</span></a>
                                        </h2>
                                    </td>
                                    <td>$600</td>
                                    <td>Jan</td>
                                    <td>2020</td>
                                    <td class="text-center"> <a href="#" class="btn btn-sm btn-primary">PDF</a></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!-- /Content End -->
        </div>
        <!-- /Page Content -->
    </div>
    <!-- /Page Wrapper -->

<script>
document.addEventListener('DOMContentLoaded', function() {
    const monthSelect = document.getElementById('payout_month_year');
    const searchBtn = document.getElementById('search-btn');
    
    function updateSearchButton() {
        if (!monthSelect.value) {
            searchBtn.textContent = 'Select Month to Search';
            searchBtn.className = 'btn btn-secondary btn-block';
            searchBtn.disabled = true;
            return;
        }
        
        const selectedOption = monthSelect.options[monthSelect.selectedIndex];
        const status = selectedOption.getAttribute('data-status');
        
        if (status === 'completed') {
            searchBtn.textContent = 'Search Reports';
            searchBtn.className = 'btn btn-success btn-block';
            searchBtn.disabled = false;
        } else {
            searchBtn.textContent = 'Payroll Not Completed';
            searchBtn.className = 'btn btn-warning btn-block';
            searchBtn.disabled = true;
        }
    }
    
    if (monthSelect) {
        monthSelect.addEventListener('change', updateSearchButton);
        updateSearchButton(); // Initial call
    }
});
</script>

@endsection