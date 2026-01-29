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
    $hour = date("G");
    $greet = $hour < 10 ? "Good Morning" : ($hour < 16 ? "Good Afternoon" : "Good Evening");
?>

<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Enhanced Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2 class="mb-1">{{ $greet }}, {{ Session::get('name') }}! 👋</h2>
                    <p class="mb-0 opacity-75">Here's what's happening in your HR system today</p>
                </div>
                <div class="col-md-4 text-md-right">
                    <div class="h5 mb-0">{{ date('l, F j, Y') }}</div>
                    <div class="small opacity-75">{{ date('h:i A') }}</div>
                </div>
            </div>
        </div>

        <!-- Key Metrics Row -->
        <div class="row mb-4">
            <div class="col-xl-2 col-lg-4 col-md-6 col-sm-6">
                <div class="card dashboard-card card-primary">
                    <div class="card-body text-center">
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <h3 class="stat-number">{{ $employeeCount }}</h3>
                        <div class="stat-label">Total Employees</div>
                        @if(isset($employeeAnalytics['growth_rate']))
                            <div class="trend-indicator trend-{{ $employeeAnalytics['growth_rate'] >= 0 ? 'up' : 'down' }} mt-2">
                                <i class="fas fa-arrow-{{ $employeeAnalytics['growth_rate'] >= 0 ? 'up' : 'down' }} me-1"></i>
                                {{ abs($employeeAnalytics['growth_rate']) }}%
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            
            <div class="col-xl-2 col-lg-4 col-md-6 col-sm-6">
                <div class="card dashboard-card card-success">
                    <div class="card-body text-center">
                        <div class="stat-icon">
                            <i class="fas fa-user-check"></i>
                        </div>
                        <h3 class="stat-number">{{ $activeCount }}</h3>
                        <div class="stat-label">Active Employees</div>
                        <div class="trend-indicator trend-neutral mt-2">
                            {{ $employeeCount > 0 ? round(($activeCount / $employeeCount) * 100, 1) : 0 }}%
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-2 col-lg-4 col-md-6 col-sm-6">
                <div class="card dashboard-card card-warning">
                    <div class="card-body text-center">
                        <div class="stat-icon">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <h3 class="stat-number">{{ $payrollAnalytics['completed_payrolls'] }}</h3>
                        <div class="stat-label">Completed Payrolls</div>
                        <div class="trend-indicator trend-success mt-2">
                            This Year
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-2 col-lg-4 col-md-6 col-sm-6">
                <div class="card dashboard-card card-info">
                    <div class="card-body text-center">
                        <div class="stat-icon">
                            <i class="fas fa-birthday-cake"></i>
                        </div>
                        <h3 class="stat-number">{{ $upcomingEvents['birthdays_count'] }}</h3>
                        <div class="stat-label">Upcoming Birthdays</div>
                        <div class="trend-indicator trend-neutral mt-2">
                            Next 30 Days
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-2 col-lg-4 col-md-6 col-sm-6">
                <div class="card dashboard-card card-purple">
                    <div class="card-body text-center">
                        <div class="stat-icon">
                            <i class="fas fa-award"></i>
                        </div>
                        <h3 class="stat-number">{{ $upcomingEvents['anniversaries_count'] }}</h3>
                        <div class="stat-label">Work Anniversaries</div>
                        <div class="trend-indicator trend-neutral mt-2">
                            Next 30 Days
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-2 col-lg-4 col-md-6 col-sm-6">
                <div class="card dashboard-card card-danger">
                    <div class="card-body text-center">
                        <div class="stat-icon">
                            <i class="fas fa-user-times"></i>
                        </div>
                        <h3 class="stat-number">{{ isset($employeeAnalytics['turnover_rate']) ? $employeeAnalytics['turnover_rate'] : 0 }}%</h3>
                        <div class="stat-label">Turnover Rate</div>
                        <div class="trend-indicator trend-{{ $employeeAnalytics['turnover_rate'] <= 10 ? 'up' : 'down' }} mt-2">
                            This Year
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Analytics Row 1 -->
        <div class="row mb-4">
            <!-- Employee Trends Chart -->
            <div class="col-xl-8 col-lg-7">
                <div class="card analytics-card">
                    <div class="card-header bg-transparent">
                        <h5 class="section-title mb-0">Employee Trends Analysis</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container chart-container-large">
                            <canvas id="employeeTrendsChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Department Distribution -->
            <div class="col-xl-4 col-lg-5">
                <div class="card analytics-card">
                    <div class="card-header bg-transparent">
                        <h5 class="section-title mb-0">Department Distribution</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="departmentChart"></canvas>
                        </div>
                        <div class="mt-3">
                            @foreach($departmentAnalytics['department_counts'] as $index => $dept)
                                <div class="department-item">
                                    <div class="d-flex align-items-center">
                                        <div class="department-color" style="background-color: {{ ['#667eea', '#56d364', '#ffc107', '#dc3545', '#17a2b8', '#6f42c1', '#fd7e14', '#e91e63'][$index % 8] }};"></div>
                                        <span>{{ $dept['name'] }}</span>
                                    </div>
                                    <div>
                                        <span class="badge bg-light text-dark">{{ $dept['count'] }}</span>
                                        <small class="text-muted">({{ $dept['percentage'] }}%)</small>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Analytics Row 2 -->
        <div class="row mb-4">
            <!-- Financial Overview -->
            <div class="col-xl-6 col-lg-6">
                <div class="card analytics-card">
                    <div class="card-header bg-transparent">
                        <h5 class="section-title mb-0">Financial Overview</h5>
                    </div>
                    <div class="card-body">
                        @if($financialOverview['current_month'])
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="financial-metric">
                                        <h4 class="financial-amount">₹{{ number_format($financialOverview['current_month']->total_gross ?? 0) }}</h4>
                                        <div class="financial-label">Gross Pay</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="financial-metric">
                                        <h4 class="financial-amount">₹{{ number_format($financialOverview['current_month']->total_deductions ?? 0) }}</h4>
                                        <div class="financial-label">Deductions</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="financial-metric">
                                        <h4 class="financial-amount">₹{{ number_format($financialOverview['current_month']->total_net ?? 0) }}</h4>
                                        <div class="financial-label">Net Pay</div>
                                    </div>
                                </div>
                            </div>
                        @endif
                        
                        <div class="chart-container">
                            <canvas id="financialTrendsChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Age & Experience Distribution -->
            <div class="col-xl-6 col-lg-6">
                <div class="card analytics-card">
                    <div class="card-header bg-transparent">
                        <h5 class="section-title mb-0">Workforce Demographics</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-center mb-3">Age Distribution</h6>
                                <div class="chart-container">
                                    <canvas id="ageDistributionChart"></canvas>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-center mb-3">Experience Distribution</h6>
                                <div class="chart-container">
                                    <canvas id="experienceDistributionChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Analytics Row 3 -->
        <div class="row mb-4">
            <!-- OT & Incentive Analytics -->
            <div class="col-xl-8 col-lg-7">
                <div class="card analytics-card">
                    <div class="card-header bg-transparent">
                        <h5 class="section-title mb-0">OT & Incentive Analytics</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container chart-container-large">
                            <canvas id="otIncentiveChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Attendance Overview -->
            <div class="col-xl-4 col-lg-5">
                <div class="card analytics-card">
                    <div class="card-header bg-transparent">
                        <h5 class="section-title mb-0">Attendance Overview</h5>
                    </div>
                    <div class="card-body text-center">
                        <div class="progress-circular">
                            <svg viewBox="0 0 120 120">
                                <circle class="bg" cx="60" cy="60" r="54"></circle>
                                <circle class="fg" cx="60" cy="60" r="54" 
                                    stroke-dasharray="{{ $attendanceOverview['attendance_percentage'] * 3.39 }} 339"
                                    stroke-dashoffset="0"></circle>
                            </svg>
                            <div class="progress-value">{{ $attendanceOverview['attendance_percentage'] }}%</div>
                        </div>
                        <h6 class="mt-3">Current Month Attendance</h6>
                        <p class="text-muted small">
                            Average: {{ round($attendanceOverview['current_month_data']->avg_attendance ?? 0, 1) }} / 
                            {{ round($attendanceOverview['current_month_data']->avg_working_days ?? 0, 1) }} days
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activities & Events -->
        <div class="row">
            <!-- Recent Activities -->
            <div class="col-xl-4 col-lg-4">
                <div class="card analytics-card">
                    <div class="card-header bg-transparent">
                        <h5 class="section-title mb-0">Recent Activities</h5>
                    </div>
                    <div class="card-body">
                        <div class="tab-content">
                            <h6 class="text-success">
                                <i class="fas fa-user-plus me-2"></i>
                                Recent Joinings ({{ $recentActivities['joinings_count'] }})
                            </h6>
                            @forelse($recentActivities['recent_joinings'] as $joining)
                                <div class="event-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong>{{ $joining->name }}</strong>
                                            <br><small class="text-muted">{{ $joining->employee_id }}</small>
                                        </div>
                                        <small class="text-muted">{{ \Carbon\Carbon::parse($joining->date_of_joining)->format('M d') }}</small>
                                    </div>
                                </div>
                            @empty
                                <p class="text-muted small">No recent joinings</p>
                            @endforelse
                            
                            @if($recentActivities['resignations_count'] > 0)
                                <h6 class="text-warning mt-4">
                                    <i class="fas fa-user-minus me-2"></i>
                                    Recent Resignations ({{ $recentActivities['resignations_count'] }})
                                </h6>
                                @foreach($recentActivities['recent_resignations'] as $resignation)
                                    <div class="event-item">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong>{{ $resignation->name }}</strong>
                                                <br><small class="text-muted">{{ $resignation->employee_id }}</small>
                                            </div>
                                            <small class="text-muted">{{ \Carbon\Carbon::parse($resignation->date_of_resignation)->format('M d') }}</small>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Upcoming Birthdays -->
            <div class="col-xl-4 col-lg-4">
                <div class="card analytics-card">
                    <div class="card-header bg-transparent">
                        <h5 class="section-title mb-0">
                            <i class="fas fa-birthday-cake text-warning me-2"></i>
                            Upcoming Birthdays
                        </h5>
                    </div>
                    <div class="card-body">
                        @forelse($upcomingEvents['upcoming_birthdays'] as $birthday)
                            <div class="event-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>{{ $birthday->name }}</strong>
                                        <br><small class="text-muted">{{ $birthday->employee_id }}</small>
                                    </div>
                                    <small class="text-muted">{{ \Carbon\Carbon::parse($birthday->date_of_birth)->format('M d') }}</small>
                                </div>
                            </div>
                        @empty
                            <p class="text-muted small text-center">No upcoming birthdays in the next 30 days</p>
                        @endforelse
                    </div>
                </div>
            </div>
            
            <!-- Work Anniversaries -->
            <div class="col-xl-4 col-lg-4">
                <div class="card analytics-card">
                    <div class="card-header bg-transparent">
                        <h5 class="section-title mb-0">
                            <i class="fas fa-award text-purple me-2"></i>
                            Work Anniversaries
                        </h5>
                    </div>
                    <div class="card-body">
                        @forelse($upcomingEvents['work_anniversaries'] as $anniversary)
                            <div class="event-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>{{ $anniversary->name }}</strong>
                                        <br><small class="text-muted">{{ $anniversary->employee_id }} - {{ $anniversary->years_of_service }} years</small>
                                    </div>
                                    <small class="text-muted">{{ \Carbon\Carbon::parse($anniversary->date_of_joining)->format('M d') }}</small>
                                </div>
                            </div>
                        @empty
                            <p class="text-muted small text-center">No upcoming anniversaries in the next 30 days</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
<script>
    // Chart.js configuration
    Chart.defaults.font.family = '"Poppins", sans-serif';
    Chart.defaults.color = '#495057';
    
    const chartColors = {
        primary: '#667eea',
        success: '#56d364',
        warning: '#ffc107',
        danger: '#dc3545',
        info: '#17a2b8',
        purple: '#6f42c1',
        orange: '#fd7e14',
        pink: '#e91e63'
    };
    
    // Employee Trends Chart
    const employeeTrendsCtx = document.getElementById('employeeTrendsChart').getContext('2d');
    new Chart(employeeTrendsCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($employeeAnalytics['hiring_trends']->pluck('period')) !!},
            datasets: [{
                label: 'New Hires',
                data: {!! json_encode($employeeAnalytics['hiring_trends']->pluck('count')) !!},
                borderColor: chartColors.success,
                backgroundColor: chartColors.success + '20',
                borderWidth: 3,
                fill: true,
                tension: 0.4
            }, {
                label: 'Resignations',
                data: {!! json_encode($employeeAnalytics['resignation_trends']->pluck('count')) !!},
                borderColor: chartColors.danger,
                backgroundColor: chartColors.danger + '20',
                borderWidth: 3,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                }
            },
            scales: {
                x: {
                    display: true,
                    grid: {
                        display: false
                    }
                },
                y: {
                    display: true,
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0,0,0,0.05)'
                    }
                }
            },
            interaction: {
                mode: 'nearest',
                axis: 'x',
                intersect: false
            }
        }
    });
    
    // Department Distribution Chart
    const departmentCtx = document.getElementById('departmentChart').getContext('2d');
    new Chart(departmentCtx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($departmentAnalytics['department_counts']->pluck('name')) !!},
            datasets: [{
                data: {!! json_encode($departmentAnalytics['department_counts']->pluck('count')) !!},
                backgroundColor: [
                    chartColors.primary,
                    chartColors.success,
                    chartColors.warning,
                    chartColors.danger,
                    chartColors.info,
                    chartColors.purple,
                    chartColors.orange,
                    chartColors.pink
                ],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((context.parsed * 100) / total).toFixed(1);
                            return `${context.label}: ${context.parsed} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
    
    // Financial Trends Chart
    const financialCtx = document.getElementById('financialTrendsChart').getContext('2d');
    new Chart(financialCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($financialOverview['financial_trends']->map(function($item) { return \Carbon\Carbon::createFromDate($item->payout_year, $item->payout_month, 1)->format('M Y'); })) !!},
            datasets: [{
                label: 'Gross Pay',
                data: {!! json_encode($financialOverview['financial_trends']->pluck('total_gross')) !!},
                backgroundColor: chartColors.primary + '80',
                borderColor: chartColors.primary,
                borderWidth: 1
            }, {
                label: 'Net Pay',
                data: {!! json_encode($financialOverview['financial_trends']->pluck('total_net')) !!},
                backgroundColor: chartColors.success + '80',
                borderColor: chartColors.success,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    }
                },
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0,0,0,0.05)'
                    },
                    ticks: {
                        callback: function(value) {
                            return '₹' + (value / 1000).toFixed(0) + 'K';
                        }
                    }
                }
            }
        }
    });
    
    // Age Distribution Chart
    const ageCtx = document.getElementById('ageDistributionChart').getContext('2d');
    new Chart(ageCtx, {
        type: 'pie',
        data: {
            labels: {!! json_encode($employeeAnalytics['age_distribution']->pluck('age_group')) !!},
            datasets: [{
                data: {!! json_encode($employeeAnalytics['age_distribution']->pluck('count')) !!},
                backgroundColor: [
                    chartColors.primary,
                    chartColors.success,
                    chartColors.warning,
                    chartColors.danger,
                    chartColors.info
                ],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        boxWidth: 12,
                        fontSize: 10
                    }
                }
            }
        }
    });
    
    // Experience Distribution Chart
    const experienceCtx = document.getElementById('experienceDistributionChart').getContext('2d');
    new Chart(experienceCtx, {
        type: 'pie',
        data: {
            labels: {!! json_encode($employeeAnalytics['experience_distribution']->pluck('experience_group')) !!},
            datasets: [{
                data: {!! json_encode($employeeAnalytics['experience_distribution']->pluck('count')) !!},
                backgroundColor: [
                    chartColors.purple,
                    chartColors.orange,
                    chartColors.pink,
                    chartColors.info,
                    chartColors.success
                ],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        boxWidth: 12,
                        fontSize: 10
                    }
                }
            }
        }
    });
    
    // OT & Incentive Chart
    const otIncentiveCtx = document.getElementById('otIncentiveChart').getContext('2d');
    new Chart(otIncentiveCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($payrollAnalytics['ot_incentive_data']['ot_trends']->map(function($item) { return \Carbon\Carbon::createFromDate($item->payout_year, $item->payout_month, 1)->format('M Y'); })) !!},
            datasets: [{
                label: 'OT Amount',
                data: {!! json_encode($payrollAnalytics['ot_incentive_data']['ot_trends']->pluck('total_amount')) !!},
                borderColor: chartColors.warning,
                backgroundColor: chartColors.warning + '20',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                yAxisID: 'y'
            }, {
                label: 'Incentive Amount',
                data: {!! json_encode($payrollAnalytics['ot_incentive_data']['incentive_trends']->pluck('total_amount')) !!},
                borderColor: chartColors.purple,
                backgroundColor: chartColors.purple + '20',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                yAxisID: 'y'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    }
                },
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0,0,0,0.05)'
                    },
                    ticks: {
                        callback: function(value) {
                            return '₹' + (value / 1000).toFixed(0) + 'K';
                        }
                    }
                }
            }
        }
    });
    
    // Add some animation and interactivity
    document.addEventListener('DOMContentLoaded', function() {
        // Animate cards on scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };
        
        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);
        
        document.querySelectorAll('.analytics-card, .dashboard-card').forEach(card => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            observer.observe(card);
        });
    });
</script>
@endpush
