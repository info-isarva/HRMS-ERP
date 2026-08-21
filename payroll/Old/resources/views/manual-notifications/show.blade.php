@extends('layouts.master')

@section('title', 'Notification Details')

@section('style')
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
    }

    .btn-outline-secondary {
        border: 1px solid #d1d5db;
        color: #6b7280;
    }

    .btn-outline-secondary:hover {
        background: #f9fafb;
        border-color: #9ca3af;
    }

    .btn-outline-primary {
        border: 1px solid #667eea;
        color: #667eea;
    }

    .btn-outline-primary:hover {
        background: #667eea;
        color: white;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .page-header-gradient {
            padding: 1.5rem 1rem;
        }

        .btn {
            width: 100%;
            margin-bottom: 0.5rem;
        }
    }
</style>
@endsection

@section('content')
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
                        <i class="fas fa-bell fa-lg"></i>
                    </div>
                    <div class="ms-3">
                        <h1 class="page-header-title">Notification Details</h1>
                        <p class="page-header-subtitle">View detailed information about this notification</p>
                    </div>
                </div>
            </div>
            <div class="p-3 d-flex justify-content-between align-items-center">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('manual-notifications.index') }}">Notifications</a></li>
                        <li class="breadcrumb-item active">Details</li>
                    </ol>
                </nav>
                <div>
                    <a href="{{ route('manual-notifications.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Main Details -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <div class="row align-items-center">
                            <div class="col">
                                <div class="d-flex align-items-center">
                                    <div class="notification-icon bg-{{ $manualNotification->color }} me-3">
                                        <i class="fas {{ $manualNotification->icon }}"></i>
                                    </div>
                                    <div>
                                        <h4 class="mb-0" style="color:white;">{{ $manualNotification->title }}</h4>
                                        <span class="badge badge-{{ 
                                            $manualNotification->status == 'active' ? 'success' : 
                                            ($manualNotification->status == 'scheduled' ? 'primary' : 
                                            ($manualNotification->status == 'inactive' ? 'secondary' : 
                                            ($manualNotification->status == 'cancelled' ? 'danger' : 'warning'))) 
                                        }}">
                                            {{ ucfirst($manualNotification->status) }}
                                        </span>
                                        <span class="badge badge-{{ $manualNotification->priority == 'high' ? 'danger' : ($manualNotification->priority == 'medium' ? 'warning' : 'info') }}">
                                            {{ ucfirst($manualNotification->priority) }} Priority
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto">
                                <div class="btn-group">
                                    @if(in_array($manualNotification->status, ['draft', 'scheduled', 'active', 'inactive']))
                                        <a href="{{ route('manual-notifications.edit', $manualNotification) }}" class="btn btn-outline-primary">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                    @endif
                                    
                                    @if($manualNotification->status == 'scheduled')
                                        <form method="POST" action="{{ route('manual-notifications.activate', $manualNotification) }}" style="display: inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-success">
                                                <i class="fas fa-play"></i> Activate
                                            </button>
                                        </form>
                                    @endif
                                    
                                    @if($manualNotification->status == 'active')
                                        <form method="POST" action="{{ route('manual-notifications.deactivate', $manualNotification) }}" style="display: inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-warning">
                                                <i class="fas fa-pause"></i> Deactivate
                                            </button>
                                        </form>
                                    @endif
                                    
                                    @if($manualNotification->status == 'inactive')
                                        <form method="POST" action="{{ route('manual-notifications.activate', $manualNotification) }}" style="display: inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-success">
                                                <i class="fas fa-play"></i> Reactivate
                                            </button>
                                        </form>
                                    @endif
                                    
                                    <a href="{{ route('manual-notifications.index') }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-arrow-left"></i> Back
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label class="font-weight-bold">Message:</label>
                            <div class="bg-light p-3 rounded">
                                {{ $manualNotification->message }}
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold">Target Type:</label>
                                    <p class="mb-0">
                                        @if($manualNotification->target_type == 'all')
                                            <span class="badge bg-info text-dark">All Employees</span>
                                        @elseif($manualNotification->target_type == 'department')
                                            <span class="badge bg-primary">Specific Departments</span>
                                        @else
                                            <span class="badge bg-success">Specific Employees</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold">Recurrence:</label>
                                    <p class="mb-0">
                                        @if($manualNotification->recurrence_type == 'once')
                                            <span class="badge bg-secondary">Show Once</span>
                                        @else
                                            <span class="badge bg-info text-dark">
                                                Every {{ $manualNotification->recurrence_interval }} 
                                                {{ ucfirst($manualNotification->recurrence_type) }}
                                            </span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        @if($manualNotification->target_type == 'department' && $manualNotification->target_departments)
                            <div class="form-group">
                                <label class="font-weight-bold">Target Departments:</label>
                                <div>
                                    @foreach($manualNotification->target_departments as $department)
                                        <span class="badge bg-primary me-1 mb-1">{{ $department }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        
                        @if($manualNotification->target_type == 'specific_employees' && $manualNotification->target_employees)
                            <div class="form-group">
                                <label class="font-weight-bold">Target Employees:</label>
                                <div>
                                    @foreach($manualNotification->target_employees as $employeeId)
                                        @php
                                            $employee = DB::table('employee_basic_details')->where('id', $employeeId)->first();
                                        @endphp
                                        @if($employee)
                                            <span class="badge bg-success me-1 mb-1">
                                                {{ $employee->name }} ({{ $employee->employee_id }})
                                            </span>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        
                        @if($manualNotification->recurrence_type == 'weekly' && $manualNotification->recurrence_days)
                            <div class="form-group">
                                <label class="font-weight-bold">Recurrence Days:</label>
                                <div>
                                    @foreach($manualNotification->recurrence_days as $day)
                                        <span class="badge bg-info text-dark me-1">{{ ucfirst($day) }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold">Schedule:</label>
                                    <div>
                                        <div class="d-flex align-items-center mb-1">
                                            <i class="fas fa-play text-success me-2"></i>
                                            <span>Start: {{ $manualNotification->start_date->format('M d, Y H:i A') }}</span>
                                        </div>
                                        @if($manualNotification->end_date)
                                            <div class="d-flex align-items-center mb-1">
                                                <i class="fas fa-stop text-danger me-2"></i>
                                                <span>End: {{ $manualNotification->end_date->format('M d, Y H:i A') }}</span>
                                            </div>
                                        @endif
                                        @if($manualNotification->recurrence_end_date)
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-calendar-times text-warning me-2"></i>
                                                <span>Recurrence End: {{ $manualNotification->recurrence_end_date->format('M d, Y') }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold">Settings:</label>
                                    <div>
                                        <div class="d-flex align-items-center mb-1">
                                            <i class="fas fa-{{ $manualNotification->show_in_header ? 'check text-success' : 'times text-danger' }} me-2"></i>
                                            <span>Show in Header</span>
                                        </div>
                                        <div class="d-flex align-items-center mb-1">
                                            <i class="fas fa-{{ $manualNotification->send_email ? 'check text-success' : 'times text-danger' }} me-2"></i>
                                            <span>Send Email</span>
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-{{ $manualNotification->send_sms ? 'check text-success' : 'times text-danger' }} me-2"></i>
                                            <span>Send SMS</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold">Created By:</label>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm me-2">
                                            <span class="avatar-title rounded-circle bg-primary">
                                                {{ substr($manualNotification->creator->name ?? 'Unknown', 0, 1) }}
                                            </span>
                                        </div>
                                        <div>
                                            <div class="font-weight-medium">{{ $manualNotification->creator->name ?? 'Unknown' }}</div>
                                            <small class="text-muted">{{ $manualNotification->created_at->format('M d, Y H:i A') }}</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @if($manualNotification->updated_by && $manualNotification->updater)
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold">Last Updated By:</label>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm me-2">
                                                <span class="avatar-title rounded-circle bg-secondary">
                                                    {{ substr($manualNotification->updater->name, 0, 1) }}
                                                </span>
                                            </div>
                                            <div>
                                                <div class="font-weight-medium">{{ $manualNotification->updater->name }}</div>
                                                <small class="text-muted">{{ $manualNotification->updated_at->format('M d, Y H:i A') }}</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Statistics -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Statistics</h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="border rounded p-3 mb-3">
                                    <h3 class="text-primary mb-0">{{ $totalTargetUsers }}</h3>
                                    <small class="text-muted">Targeted Users</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="border rounded p-3 mb-3">
                                    <h3 class="text-success mb-0">{{ $totalReads }}</h3>
                                    <small class="text-muted">Total Reads</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="border rounded p-3 mb-3">
                                    <h3 class="text-info mb-0">{{ $readPercentage }}%</h3>
                                    <small class="text-muted">Read Rate</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="border rounded p-3 mb-3">
                                    <h3 class="text-warning mb-0">{{ $totalTargetUsers - $totalReads }}</h3>
                                    <small class="text-muted">Unread</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="progress mb-3" style="height: 10px;">
                            <div class="progress-bar bg-success" role="progressbar" 
                                 style="width: {{ $readPercentage }}%" 
                                 aria-valuenow="{{ $readPercentage }}" aria-valuemin="0" aria-valuemax="100">
                            </div>
                        </div>
                        
                        <button type="button" class="btn btn-outline-info btn-block" onclick="showDetailedAnalytics({{ $manualNotification->id }})">
                            <i class="fas fa-chart-bar"></i> View Detailed Analytics
                        </button>
                    </div>
                </div>
                
                <!-- Recent Reads -->
                @if($manualNotification->reads->count() > 0)
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Recent Reads</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="list-group list-group-flush">
                                @foreach($manualNotification->reads->take(5) as $read)
                                    <div class="list-group-item d-flex align-items-center">
                                        <div class="avatar avatar-sm me-3">
                                            <span class="avatar-title rounded-circle bg-primary">
                                                {{ substr($read->user->name ?? 'U', 0, 1) }}
                                            </span>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="font-weight-medium">{{ $read->user->name ?? 'Unknown User' }}</div>
                                            <small class="text-muted">{{ $read->read_at->format('M d, Y H:i A') }}</small>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            @if($manualNotification->reads->count() > 5)
                                <div class="card-footer text-center">
                                    <small class="text-muted">And {{ $manualNotification->reads->count() - 5 }} more...</small>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Analytics Modal -->
<div class="modal fade" id="analyticsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detailed Analytics</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="analyticsContent">
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.notification-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.25rem;
}

.notification-icon.bg-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.notification-icon.bg-success {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
}

.notification-icon.bg-warning {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
}

.notification-icon.bg-info {
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
}

.notification-icon.bg-danger {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
}

.avatar {
    width: 32px;
    height: 32px;
}

.avatar-sm {
    width: 28px;
    height: 28px;
}

.avatar-title {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
    font-weight: 600;
    color: white;
}

.badge {
    font-size: 0.75rem;
}
</style>

<script>
function showDetailedAnalytics(notificationId) {
    $('#analyticsModal').modal('show');
    $('#analyticsContent').html(`
        <div class="text-center">
            <div class="spinner-border" role="status">
                <span class="sr-only">Loading...</span>
            </div>
        </div>
    `);
    
    fetch(`/manual-notifications/${notificationId}/analytics`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayAnalytics(data.analytics);
            } else {
                $('#analyticsContent').html('<div class="alert alert-danger">Failed to load analytics</div>');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            $('#analyticsContent').html('<div class="alert alert-danger">Error loading analytics</div>');
        });
}

function displayAnalytics(analytics) {
    const content = `
        <div class="row">
            <div class="col-md-3">
                <div class="card bg-primary text-white text-center">
                    <div class="card-body">
                        <h4 class="mb-0">${analytics.total_targeted}</h4>
                        <small>Targeted Users</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white text-center">
                    <div class="card-body">
                        <h4 class="mb-0">${analytics.total_reads}</h4>
                        <small>Total Reads</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white text-center">
                    <div class="card-body">
                        <h4 class="mb-0">${analytics.read_percentage}%</h4>
                        <small>Read Rate</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white text-center">
                    <div class="card-body">
                        <h4 class="mb-0">${analytics.unread_count}</h4>
                        <small>Unread</small>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mt-4">
            <div class="col-md-6">
                <h6>Reads by Day</h6>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Reads</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${Object.entries(analytics.reads_by_day).map(([date, count]) => 
                                `<tr><td>${date}</td><td>${count}</td></tr>`
                            ).join('')}
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="col-md-6">
                <h6>Reads by Department</h6>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Department</th>
                                <th>Reads</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${Object.entries(analytics.reads_by_department).map(([dept, count]) => 
                                `<tr><td>${dept}</td><td>${count}</td></tr>`
                            ).join('')}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    `;
    
    $('#analyticsContent').html(content);
}
</script>
@endsection