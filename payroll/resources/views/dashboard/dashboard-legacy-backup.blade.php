@extends('layouts.master')

@section('title', 'Dashboard - Analytics')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.css">
<style>
    /* Enhanced Dashboard Styles */
    .dashboard-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        border-radius: 15px;
        color: white;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        overflow: hidden;
        position: relative;
    }
    
    .dashboard-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255,255,255,0.1);
        transform: translateX(-100%);
        transition: transform 0.6s ease;
    }
    
    .dashboard-card:hover::before {
        transform: translateX(100%);
    }
    
    .dashboard-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(0,0,0,0.1);
    }
    
    .dashboard-card.card-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
    .dashboard-card.card-success { background: linear-gradient(135deg, #56d364 0%, #28a745 100%); }
    .dashboard-card.card-warning { background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%); }
    .dashboard-card.card-danger { background: linear-gradient(135deg, #dc3545 0%, #e91e63 100%); }
    .dashboard-card.card-info { background: linear-gradient(135deg, #17a2b8 0%, #007bff 100%); }
    .dashboard-card.card-purple { background: linear-gradient(135deg, #6f42c1 0%, #e83e8c 100%); }
    
    .stat-icon {
        font-size: 2.5rem;
        opacity: 0.8;
        margin-bottom: 10px;
    }
    
    .stat-number {
        font-size: 2.2rem;
        font-weight: 700;
        margin: 0;
    }
    
    .stat-label {
        font-size: 0.9rem;
        opacity: 0.9;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .analytics-card {
        background: white;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        border: none;
        transition: transform 0.3s ease;
    }
    
    .analytics-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.12);
    }
    
    .chart-container {
        position: relative;
        height: 300px;
        padding: 20px;
    }
    
    .chart-container-large {
        height: 400px;
    }
    
    .progress-circular {
        position: relative;
        width: 120px;
        height: 120px;
        margin: 20px auto;
    }
    
    .progress-circular svg {
        transform: rotate(-90deg);
        width: 100%;
        height: 100%;
    }
    
    .progress-circular circle {
        fill: none;
        stroke-width: 8;
        stroke-linecap: round;
        transition: stroke-dasharray 0.6s ease;
    }
    
    .progress-circular .bg {
        stroke: #e9ecef;
    }
    
    .progress-circular .fg {
        stroke: #007bff;
    }
    
    .progress-value {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        font-size: 1.5rem;
        font-weight: 600;
        color: #495057;
    }
    
    .event-item {
        padding: 12px 0;
        border-bottom: 1px solid #e9ecef;
        transition: all 0.3s ease;
    }
    
    .event-item:hover {
        background-color: #f8f9fa;
        padding-left: 10px;
    }
    
    .event-item:last-child {
        border-bottom: none;
    }
    
    .trend-indicator {
        display: inline-flex;
        align-items: center;
        font-size: 0.875rem;
        font-weight: 500;
        padding: 2px 8px;
        border-radius: 12px;
    }
    
    .trend-up {
        color: #28a745;
        background-color: rgba(40, 167, 69, 0.1);
    }
    
    .trend-down {
        color: #dc3545;
        background-color: rgba(220, 53, 69, 0.1);
    }
    
    .trend-neutral {
        color: #6c757d;
        background-color: rgba(108, 117, 125, 0.1);
    }
    
    .department-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 0;
        border-bottom: 1px solid #f1f3f4;
    }
    
    .department-item:last-child {
        border-bottom: none;
    }
    
    .department-color {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        margin-right: 10px;
    }
    
    .financial-metric {
        text-align: center;
        padding: 20px;
        border-radius: 10px;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        margin-bottom: 15px;
    }
    
    .financial-amount {
        font-size: 1.8rem;
        font-weight: 700;
        color: #495057;
        margin: 0;
    }
    
    .financial-label {
        font-size: 0.875rem;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-top: 5px;
    }
    
    .page-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 30px;
        border-radius: 15px;
        margin-bottom: 30px;
    }
    
    .section-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #495057;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid #e9ecef;
    }
    
    @media (max-width: 768px) {
        .chart-container {
            height: 250px;
            padding: 10px;
        }
        
        .chart-container-large {
            height: 300px;
        }
        
        .stat-number {
            font-size: 1.8rem;
        }
        
        .stat-icon {
            font-size: 2rem;
        }
    }
</style>
@endpush

@section('content')

    <?php  
        $hour   = date ("G");
        $minute = date ("i");
        $second = date ("s");
        $msg = " Today is " . date ("l, M. d, Y.");

        if ($hour == 00 && $hour <= 9 && $minute <= 59 && $second <= 59) {
            $greet = "Good Morning,";
        } else if ($hour >= 10 && $hour <= 11 && $minute <= 59 && $second <= 59) {
            $greet = "Good Day,";
        } else if ($hour >= 12 && $hour <= 15 && $minute <= 59 && $second <= 59) {
            $greet = "Good Afternoon,";
        } else if ($hour >= 16 && $hour <= 23 && $minute <= 59 && $second <= 59) {
            $greet = "Good Evening,";
        } else {
            $greet = "Welcome,";
        }
    ?>

    <div class="page-wrapper">
        <!-- Page Content -->
        <div class="content container-fluid">
            <!-- Page Header -->
            <div class="page-header">
                <div class="row">
                    <div class="col-sm-12">
                        <h3 class="page-title">{{ $greet }} Welcome, {{ Session::get('name') }}!</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item active">Dashboard</li>
                        </ul>
                    </div>
                </div>
            </div>
            <!-- /Page Header -->

            <!-- Employee Stats Widgets -->
            <div class="row">
                <div class="col-md-6 col-sm-6 col-lg-6 col-xl-3">
                    <div class="card dash-widget">
                        <div class="card-body">
                            <span class="dash-widget-icon"><span class="material-icons">group</span></span>
                            <div class="dash-widget-info">
                                <h3>{{ $employeeCount }}</h3>
                                <span>Total Employees</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-sm-6 col-lg-6 col-xl-3">
                    <div class="card dash-widget">
                        <div class="card-body">
                            <span class="dash-widget-icon bg-success"><span class="material-icons">person_outline</span></span>
                            <div class="dash-widget-info">
                                <h3>{{ $activeCount }}</h3>
                                <span>Active Employees</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-sm-6 col-lg-6 col-xl-3">
                    <div class="card dash-widget">
                        <div class="card-body">
                            <span class="dash-widget-icon bg-danger"><span class="material-icons">person_remove</span></span>
                            <div class="dash-widget-info">
                                <h3>{{ $resignedCount }}</h3>
                                <span>Resigned Employees</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-sm-6 col-lg-6 col-xl-3">
                    <div class="card dash-widget">
                        <div class="card-body">
                            <span class="dash-widget-icon bg-info"><span class="material-icons">person_add</span></span>
                            <div class="dash-widget-info">
                                <h3>{{ $recentJoinings }}</h3>
                                <span>Recent Joinings</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payroll Stats Widgets -->
            <div class="row">
                <div class="col-md-6 col-sm-6 col-lg-6 col-xl-4">
                    <div class="card dash-widget">
                        <div class="card-body">
                            <span class="dash-widget-icon bg-success"><span class="material-icons">check_circle</span></span>
                            <div class="dash-widget-info">
                                <h3>{{ $completedPayrolls }}</h3>
                                <span>Completed Payrolls</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-sm-6 col-lg-6 col-xl-4">
                    <div class="card dash-widget">
                        <div class="card-body">
                            <span class="dash-widget-icon bg-warning"><span class="material-icons">update</span></span>
                            <div class="dash-widget-info">
                                <h3>{{ $inProgressPayrolls }}</h3>
                                <span>In-Progress Payrolls</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-sm-6 col-lg-6 col-xl-4">
                    <div class="card dash-widget">
                        <div class="card-body">
                            <span class="dash-widget-icon bg-info"><span class="material-icons">receipt</span></span>
                            <div class="dash-widget-info">
                                <h3>{{ $completedPayrolls + $inProgressPayrolls }}</h3>
                                <span>Total Payroll Cycles</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Department Stats -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0">Department Statistics</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                @foreach($departmentCounts as $dept)
                                <div class="col-md-3 mb-3">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <h5>{{ $dept['name'] }}</h5>
                                            <h3>{{ $dept['count'] }}</h3>
                                            <p class="mb-0">Employees</p>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Payroll Cycles -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0">Recent Payroll Cycles</h4>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped custom-table mb-0">
                                    <thead>
                                        <tr>
                                            <th>Month</th>
                                            <th>Year</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($payrollData->take(5) as $payroll)
                                            <tr>
                                                <td>
                                                    @php
                                                        $monthNames = [
                                                            1 => 'January', 2 => 'February', 3 => 'March',
                                                            4 => 'April', 5 => 'May', 6 => 'June',
                                                            7 => 'July', 8 => 'August', 9 => 'September',
                                                            10 => 'October', 11 => 'November', 12 => 'December'
                                                        ];
                                                        echo $monthNames[$payroll->payout_month] ?? $payroll->payout_month;
                                                    @endphp
                                                </td>
                                                <td>{{ $payroll->payout_year }}</td>
                                                <td>
                                                    @if($payroll->status == 'completed')
                                                        <span class="badge bg-success">Completed</span>
                                                    @elseif($payroll->status == 'progress')
                                                        <span class="badge bg-warning">In Progress</span>
                                                    @else
                                                        <span class="badge bg-secondary">{{ ucfirst($payroll->status) }}</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Birthday Alerts -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0">Upcoming Events</h4>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                <span class="material-icons me-2">cake</span> {{ $upcomingBirthdays }} employee(s) have birthdays in the next 30 days
                            </div>
                            
                            @if(count($upcomingBirthdayEmployees) > 0)
                                <div class="table-responsive mt-3">
                                    <table class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>Employee ID</th>
                                                <th>Employee Name</th>
                                                <th>Birthday Date</th>
                                                <th>Days Left</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($upcomingBirthdayEmployees as $employee)
                                                <tr>
                                                    <td>{{ $employee->employee_id }}</td>
                                                    <td>{{ $employee->name }}</td>
                                                    <td>{{ date('d F', strtotime($employee->date_of_birth)) }}</td>
                                                    <td>
                                                        @php
                                                            $birthday = new DateTime(date('Y-') . date('m-d', strtotime($employee->date_of_birth)));
                                                            $today = new DateTime();
                                                            
                                                            // If birthday already passed this year, use next year's date
                                                            if($birthday < $today) {
                                                                $birthday->modify('+1 year');
                                                            }
                                                            
                                                            $diff = $today->diff($birthday);
                                                            echo $diff->days . ' day(s)';
                                                        @endphp
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center mt-3">
                                    <p>No upcoming birthdays in the next 30 days.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
        <!-- /Page Content -->
    </div>

    <style>
        /* Make sure Material Icons are included in the head of your document */
        @import url('https://fonts.googleapis.com/icon?family=Material+Icons');
        
        .material-icons {
            vertical-align: middle;
            line-height: inherit;
        }
        
        .dash-widget-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 60px;
            height: 60px;
            background-color: #f5f5f5;
            border-radius: 50%;
            margin-right: 15px;
            color: #777;
            font-size: 30px;
        }
        
        .bg-success {
            background-color: #28a745 !important;
            color: white !important;
        }
        
        .bg-danger {
            background-color: #dc3545 !important;
            color: white !important;
        }
        
        .bg-warning {
            background-color: #ffc107 !important;
            color: #343a40 !important;
        }
        
        .bg-info {
            background-color: #17a2b8 !important;
            color: white !important;
        }
        
        .badge {
            display: inline-block;
            padding: 0.25em 0.4em;
            font-size: 75%;
            font-weight: 700;
            line-height: 1;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            border-radius: 0.25rem;
            color: #fff;
        }
        
        .alert-info .material-icons {
            vertical-align: middle;
            margin-right: 5px;
        }
        
        .me-2 {
            margin-right: 0.5rem !important;
        }
    </style>
@endsection