@extends('layouts.master')
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
        inset: 0;
        background: rgba(0, 0, 0, 0.05);
    }
    
    .page-header-circle-1 {
        position: absolute;
        top: -1rem;
        right: -1rem;
        width: 6rem;
        height: 6rem;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
    }
    
    .page-header-circle-2 {
        position: absolute;
        bottom: -1rem;
        left: -1rem;
        width: 8rem;
        height: 8rem;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
    }
    
    .page-header-icon-box {
        width: 4rem;
        height: 4rem;
        background: rgba(255, 255, 255, 0.2);
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
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
    
    /* Modern Card */
    .modern-card {
        background: white;
        border-radius: 1rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
        border: 1px solid #e5e7eb;
        overflow: hidden;
        margin-bottom: 2rem;
        height: 100%;
    }
    
    /* Flex Container */
    .sync-cards-container {
        display: flex;
        gap: 1.5rem;
        flex-wrap: wrap;
    }
    
    .sync-cards-container > div {
        flex: 1;
        min-width: 450px;
    }
    
    .modern-card-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 1.25rem 1.5rem;
        border-bottom: none;
    }
    
    .modern-card-header h4 {
        color: white;
        font-weight: 600;
        margin: 0;
        font-size: 1.125rem;
    }
    
    .modern-card-body {
        padding: 1.5rem;
    }
    
    /* Status Table */
    .status-table {
        margin-bottom: 0;
    }
    
    .status-table td {
        padding: 1rem 0.75rem;
        border-bottom: 1px solid #f3f4f6;
        font-size: 0.875rem;
    }
    
    .status-table td:first-child {
        font-weight: 500;
        color: #6b7280;
        width: 60%;
    }
    
    .status-table td:last-child {
        font-weight: 600;
        color: #1f2937;
    }
    
    .status-table tr:last-child td {
        border-bottom: none;
    }
    
    /* Modern Badges */
    .badge-modern {
        padding: 0.375rem 0.75rem;
        font-weight: 500;
        border-radius: 0.375rem;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .badge-modern.bg-success {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
    }
    
    .badge-modern.bg-danger {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
    }
    
    .badge-modern.bg-warning text-dark {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: white;
    }
    
    /* Info Boxes */
    .info-box {
        border-radius: 0.75rem;
        padding: 1.25rem;
        margin-bottom: 1rem;
        border-left: 4px solid;
    }
    
    .info-box.info-box-info {
        background: #eff6ff;
        border-color: #3b82f6;
    }
    
    .info-box.info-box-warning {
        background: #fffbeb;
        border-color: #f59e0b;
    }
    
    .info-box h5 {
        font-size: 1rem;
        font-weight: 600;
        margin-bottom: 0.75rem;
        color: #1f2937;
    }
    
    .info-box p {
        margin-bottom: 0.5rem;
        font-size: 0.875rem;
        color: #4b5563;
    }
    
    .info-box ul {
        margin-bottom: 0;
        padding-left: 1.25rem;
        font-size: 0.875rem;
        color: #4b5563;
    }
    
    .info-box ul li {
        margin-bottom: 0.375rem;
    }
    
    /* Alert Styling */
    .alert-modern {
        border-radius: 0.75rem;
        padding: 1rem 1.25rem;
        border: none;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        margin-bottom: 1.5rem;
    }
    
    .alert-modern.alert-success {
        background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
        color: #065f46;
    }
    
    .alert-modern.alert-danger {
        background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
        color: #991b1b;
    }
    
    .alert-modern.alert-warning {
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        color: #92400e;
    }
</style>

<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Modern Page Header -->
        <div class="page-header-card">
            <div class="page-header-gradient">
                <!-- Background Patterns -->
                <div class="page-header-pattern"></div>
                <div class="page-header-circle-1"></div>
                <div class="page-header-circle-2"></div>
                
                <div class="position-relative">
                    <div class="row align-items-center">
                        <div class="col-lg-8">
                            <div class="d-flex align-items-center">
                                <div class="page-header-icon-box me-4">
                                    <i class="fas fa-sync-alt text-white" style="font-size: 1.5rem;"></i>
                                </div>
                                <div>
                                    <h1 class="page-header-title">User Synchronization</h1>
                                    <p class="page-header-subtitle">
                                        Sync users between Payroll and Attendance modules
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 d-flex align-items-center justify-content-end">
                            <a href="{{ route('users.sync.all') }}" class="btn btn-light btn-lg">
                                <i class="fa fa-sync me-2"></i> Sync All Users
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /Modern Page Header -->

        <!-- Alerts -->
        @if(session('success'))
            <div class="alert alert-modern alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i>
                <strong>Success!</strong> {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-modern alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle me-2"></i>
                <strong>Error!</strong> {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        @if(session('warning'))
            <div class="alert alert-modern alert-warning alert-dismissible fade show">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Warning!</strong> {{ session('warning') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <div class="sync-cards-container">
            <!-- User Sync Status Card -->
            <div>
                <div class="modern-card">
                    <div class="modern-card-header">
                        <h4><i class="fas fa-chart-bar me-2"></i>User Sync Status</h4>
                    </div>
                    <div class="modern-card-body">
                        <table class="table status-table">
                            <tbody>
                                <tr>
                                    <td><i class="fas fa-users me-2 text-primary"></i>Total Users (with employee links)</td>
                                    <td><strong>{{ $userCount }}</strong></td>
                                </tr>
                                <tr>
                                    <td><i class="fas fa-cog me-2 text-info"></i>API Configuration</td>
                                    <td>
                                        @if($apiConfigured)
                                            <span class="badge badge-modern bg-success">Configured</span>
                                        @else
                                            <span class="badge badge-modern bg-danger">Not Configured</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><i class="fas fa-toggle-on me-2 text-success"></i>Auto-Sync Enabled</td>
                                    <td>
                                        @if($syncEnabled)
                                            <span class="badge badge-modern bg-success">Enabled</span>
                                        @else
                                            <span class="badge badge-modern bg-warning text-dark">Disabled</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><i class="fas fa-clock me-2 text-warning"></i>Last Manual Sync</td>
                                    <td>
                                        @if($lastSyncDate)
                                            {{ $lastSyncDate }}
                                        @else
                                            <em class="text-muted">Never</em>
                                        @endif
                                    </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Synchronization Information Card -->
            <div>
                <div class="modern-card">
                    <div class="modern-card-header">
                        <h4><i class="fas fa-info-circle me-2"></i>Synchronization Information</h4>
                    </div>
                    <div class="modern-card-body">
                        <div class="info-box info-box-info">
                            <h5><i class="fas fa-cogs me-2"></i>How Synchronization Works</h5>
                            <p>Users created or updated in the payroll module are automatically synchronized to the attendance module.</p>
                            <ul>
                                <li>New users are created in attendance when added to payroll</li>
                                <li>Changes to names, departments, positions, and roles are synced</li>
                                <li>Password changes are synced to maintain single login</li>
                                <li>User deletions are also synced</li>
                            </ul>
                        </div>
                        
                        <div class="info-box info-box-warning">
                            <h5><i class="fas fa-sync-alt me-2"></i>Manual Sync</h5>
                            <p>Use the "Sync All Users" button to manually synchronize all users to the attendance module. This is useful if:</p>
                            <ul>
                                <li>You notice inconsistencies between systems</li>
                                <li>Users were added/updated while the auto-sync was disabled</li>
                                <li>After system maintenance or updates</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
