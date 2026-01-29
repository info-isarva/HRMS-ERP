@extends('layouts.master')

@section('title', 'Notification Details')

@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row">
                <div class="col-sm-12">
                    <h3 class="page-title">Notification Details</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('notifications.all') }}">Notifications</a></li>
                        <li class="breadcrumb-item active">Details</li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- /Page Header -->

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-gradient-primary text-white">
                        <div class="row align-items-center">
                            <div class="col">
                                <h5 class="card-title mb-0">
                                    <i class="fas {{ $notification->icon }} me-2"></i>
                                    {{ $notification->title }}
                                </h5>
                            </div>
                            <div class="col-auto">
                                <span class="badge bg-{{ $notification->priority == 'high' ? 'danger' : ($notification->priority == 'medium' ? 'warning' : 'info') }}">
                                    {{ ucfirst($notification->priority) }} Priority
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Notification Message -->
                        <div class="mb-4">
                            <h6 class="text-muted mb-3">Message</h6>
                            <div class="notification-message bg-light p-3 rounded">
                                {!! nl2br(e($notification->message)) !!}
                            </div>
                        </div>

                        <!-- Notification Details -->
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-muted mb-3">Details</h6>
                                <table class="table table-borderless">
                                    <tr>
                                        <td class="text-muted" width="40%">Priority:</td>
                                        <td>
                                            <span class="badge bg-{{ $notification->priority == 'high' ? 'danger' : ($notification->priority == 'medium' ? 'warning' : 'info') }}">
                                                {{ ucfirst($notification->priority) }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Start Date:</td>
                                        <td>{{ \Carbon\Carbon::parse($notification->start_date)->format('M d, Y') }}</td>
                                    </tr>
                                    @if($notification->end_date)
                                    <tr>
                                        <td class="text-muted">End Date:</td>
                                        <td>{{ \Carbon\Carbon::parse($notification->end_date)->format('M d, Y') }}</td>
                                    </tr>
                                    @endif
                                    <tr>
                                        <td class="text-muted">Status:</td>
                                        <td>
                                            @if($notification->status == 'active')
                                                <span class="badge bg-success">Active</span>
                                            @elseif($notification->status == 'inactive')
                                                <span class="badge bg-secondary">Inactive</span>
                                            @elseif($notification->status == 'scheduled')
                                                <span class="badge bg-primary">Scheduled</span>
                                            @elseif($notification->status == 'draft')
                                                <span class="badge bg-warning">Draft</span>
                                            @elseif($notification->status == 'cancelled')
                                                <span class="badge bg-danger">Cancelled</span>
                                            @else
                                                <span class="badge bg-info">{{ ucfirst($notification->status) }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Show in Header:</td>
                                        <td>
                                            @if($notification->show_in_header)
                                                <span class="badge bg-success">Yes</span>
                                            @else
                                                <span class="badge bg-secondary">No</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @if($notification->recurrence_interval > 1)
                                    <tr>
                                        <td class="text-muted">Recurrence:</td>
                                        <td>Every {{ $notification->recurrence_interval }} days</td>
                                    </tr>
                                    @endif
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted mb-3">Targeting</h6>
                                <div class="targeting-info">
                                    @if($notification->target_type == 'all')
                                        <div class="alert alert-info">
                                            <i class="fas fa-users me-2"></i>
                                            This notification is sent to all employees.
                                        </div>
                                    @elseif($notification->target_type == 'department')
                                        <div class="alert alert-warning">
                                            <i class="fas fa-building me-2"></i>
                                            <strong>Target Departments:</strong>
                                            <div class="mt-2">
                                                @foreach($departmentNames as $deptName)
                                                    <span class="badge bg-primary me-1 mb-1">{{ $deptName }}</span>
                                                @endforeach
                                            </div>
                                        </div>
                                    @elseif($notification->target_type == 'employees')
                                        <div class="alert alert-success">
                                            <i class="fas fa-user me-2"></i>
                                            <strong>Specific Employees:</strong>
                                            <div class="mt-2">
                                                @foreach($employeeNames as $empName)
                                                    <span class="badge bg-success me-1 mb-1">{{ $empName }}</span>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="mt-4 pt-3 border-top">
                            <div class="d-flex justify-content-between">
                                <a href="{{ route('notifications.all') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Back to All Notifications
                                </a>
                                <div>
                                    @if(in_array($notification->status, ['draft', 'scheduled']))
                                        <a href="{{ route('manual-notifications.edit', $notification) }}" class="btn btn-warning me-2">
                                            <i class="fas fa-edit me-2"></i>Edit Notification
                                        </a>
                                    @endif
                                    @if(isset($notification->action_url) && $notification->action_url)
                                    <a href="{{ $notification->action_url }}" class="btn btn-primary" target="_blank">
                                        <i class="fas fa-external-link-alt me-2"></i>Take Action
                                    </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.notification-message {
    font-size: 1.1rem;
    line-height: 1.6;
}

.targeting-info .badge {
    font-size: 0.85rem;
}

@media (max-width: 768px) {
    .d-flex.justify-content-between {
        flex-direction: column;
        gap: 0.5rem;
    }

    .d-flex.justify-content-between .btn {
        width: 100%;
    }
}
</style>
@endsection