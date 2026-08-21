@extends('layouts.master')
@section('title', 'Permission Management')
@section('content')

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
        margin-bottom: 0.5rem;
    }

    .page-header-subtitle {
        font-size: 1rem;
        opacity: 0.9;
        margin-bottom: 0;
    }

    /* Content Cards */
    .content-card {
        background: white;
        border-radius: 1rem;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.06);
        border: none;
        margin-bottom: 2rem;
    }

    .content-card .card-header {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-bottom: 1px solid #dee2e6;
        border-radius: 1rem 1rem 0 0 !important;
        padding: 1.5rem;
        border: none;
    }

    .content-card .card-body {
        padding: 2rem;
    }

    /* Stats Cards */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: white;
        border-radius: 0.75rem;
        padding: 1.5rem;
        text-align: center;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        border: 1px solid #e9ecef;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .stat-value {
        font-size: 2rem;
        font-weight: 700;
        color: #495057;
        margin-bottom: 0.5rem;
    }

    .stat-label {
        font-size: 0.875rem;
        color: #6c757d;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* Table Styling */
    .table-card {
        background: white;
        border-radius: 1rem;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.06);
        overflow: hidden;
        border: none;
    }

    .table thead th {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border: none;
        font-weight: 600;
        color: #495057;
        padding: 1rem;
        font-size: 0.875rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .table tbody td {
        padding: 1rem;
        border: none;
        border-bottom: 1px solid #f1f3f4;
        vertical-align: middle;
    }

    .table tbody tr:hover {
        background-color: #f8f9fa;
    }

    /* Badge Styling */
    .badge {
        font-size: 0.75rem;
        padding: 0.375rem 0.75rem;
        border-radius: 0.5rem;
        font-weight: 500;
    }

    .route-badge {
        font-size: 0.7rem;
        margin: 0.125rem;
        padding: 0.25rem 0.5rem;
    }

    /* Button Styling */
    .btn-modern {
        border-radius: 0.5rem;
        padding: 0.5rem 1.5rem;
        font-weight: 500;
        transition: all 0.2s ease;
    }

    .btn-modern:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .add-btn {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        border: none;
        color: white;
        padding: 0.75rem 1.5rem;
        border-radius: 0.75rem;
        font-weight: 600;
        box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
    }

    .add-btn:hover {
        background: linear-gradient(135deg, #218838 0%, #1aa085 100%);
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(40, 167, 69, 0.4);
    }

    /* Modal Styling */
    .modal-content {
        border-radius: 1rem;
        border: none;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    }

    .modal-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 1rem 1rem 0 0;
        border-bottom: none;
        padding: 1.5rem;
    }

    .modal-header .close {
        color: white;
        opacity: 0.8;
    }

    .modal-header .close:hover {
        color: white;
        opacity: 1;
    }

    .modal-body {
        padding: 2rem;
    }

    .modal-footer {
        border-top: 1px solid #e9ecef;
        padding: 1.5rem;
        border-radius: 0 0 1rem 1rem;
    }

    /* Form Styling */
    .form-control {
        border-radius: 0.5rem;
        border: 1px solid #ced4da;
        padding: 0.75rem 1rem;
    }

    .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }

    .form-label {
        font-weight: 600;
        color: #495057;
        margin-bottom: 0.5rem;
    }

    /* Alert Styling */
    .alert {
        border-radius: 0.75rem;
        border: none;
        padding: 1.5rem;
    }

    .alert-info {
        background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%);
        color: #0c5460;
    }
    /* Unified alert styles (aligned with Departments page gradient + modern badges) */
    .alert-inline-wrapper {
        animation: fadeInScale 0.35s ease;
    }
    @keyframes fadeInScale { from {opacity:0; transform: translateY(-6px);} to {opacity:1; transform: translateY(0);} }
    .alert-modern-success { background: linear-gradient(135deg,#10b981 0%, #059669 100%); color:#fff; }
    .alert-modern-danger { background: linear-gradient(135deg,#ef4444 0%, #dc2626 100%); color:#fff; }
    .alert-modern-warning { background: linear-gradient(135deg,#f59e0b 0%, #d97706 100%); color:#fff; }
    .alert-modern-info { background: linear-gradient(135deg,#667eea 0%, #764ba2 100%); color:#fff; }
    .alert-modern-success .close,
    .alert-modern-danger .close,
    .alert-modern-warning .close,
    .alert-modern-info .close { color:#fff; opacity:0.9; }
    .alert-modern-success .close:hover,
    .alert-modern-danger .close:hover,
    .alert-modern-warning .close:hover,
    .alert-modern-info .close:hover { opacity:1; }

    /* Select2 Bootstrap 4 integration - Fixed styling for permissions */
    .select2-container {
        /* width: 100% !important; */
        z-index: 9999 !important;
    }

    .select2-container--default .select2-selection--multiple {
        border: 1px solid #ced4da !important;
        border-radius: 0.5rem !important;
        min-height: 38px !important;
        font-size: 0.875rem !important;
        background-color: #fff !important;
        padding: 2px 4px !important;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__rendered {
        padding: 0 !important;
        margin: 0 !important;
        display: flex !important;
        flex-wrap: wrap !important;
        align-items: center !important;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background-color: #667eea !important;
        border: 1px solid #667eea !important;
        color: white !important;
        padding: 2px 8px !important;
        margin: 2px !important;
        border-radius: 0.375rem !important;
        font-size: 0.75rem !important;
        display: inline-flex !important;
        align-items: center !important;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
        color: white !important;
        margin-right: 5px !important;
        font-weight: bold !important;
        cursor: pointer !important;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
        background-color: rgba(255, 255, 255, 0.2) !important;
        border-radius: 2px !important;
    }

    .select2-container--default .select2-selection--multiple .select2-search--inline .select2-search__field {
        margin: 2px !important;
        padding: 0 !important;
        border: none !important;
        outline: none !important;
        background: transparent !important;
    }

    /* Dropdown styling */
    .select2-dropdown {
    border: 1px solid #ced4da !important;
    border-radius: 0.5rem !important;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075) !important;
    background-color: #fff !important;
    z-index: 99999 !important;
    max-width: 98vw !important;
    min-width: 220px !important;
    overflow-x: auto !important;
    left: 0 !important;
    right: 0 !important;
    }

    .select2-container--default .select2-results__option {
        padding: 8px 12px !important;
        background-color: #fff !important;
        color: #495057 !important;
    }

    .select2-container--default .select2-results__option--highlighted {
        background-color: #667eea !important;
        color: white !important;
    }

    .select2-container--default .select2-results__option--selected {
        background-color: #e3f2fd !important;
        color: #495057 !important;
    }

    .select2-container--default .select2-search--dropdown {
        padding: 8px !important;
        background-color: #f8f9fa !important;
        border-bottom: 1px solid #dee2e6 !important;
    }

    .select2-container--default .select2-search--dropdown .select2-search__field {
        border: 1px solid #ced4da !important;
        border-radius: 0.375rem !important;
        padding: 0.375rem 0.75rem !important;
        background-color: #fff !important;
        width: 100% !important;
    }

    /* Modal specific fixes */
    .modal .select2-container {
        z-index: 1060 !important;
    }

    .modal .select2-dropdown {
    z-index: 1070 !important;
    max-width: 96vw !important;
    min-width: 220px !important;
    left: 2vw !important;
    right: 2vw !important;
    overflow-x: auto !important;
    }

    /* Permission card styling */
    .permission-card {
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    }

    .permission-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }

    /* Loading state */
    .btn:disabled {
        opacity: 0.7;
        cursor: not-allowed;
    }

    /* Suggestion buttons */
    .suggestion-btn {
        transition: all 0.2s ease-in-out;
        border-radius: 0.5rem;
    }

    .suggestion-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    /* Debug styling */
    .debug-info {
        max-height: 200px;
        overflow-y: auto;
    }

    /* DataTables Custom Styling */
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter,
    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_paginate {
        padding: 1rem;
    }

    .dataTables_wrapper .dataTables_filter input {
        border: 2px solid #e5e7eb;
        border-radius: 0.5rem;
        padding: 0.5rem 1rem;
        margin-left: 0.5rem;
    }

    .dataTables_wrapper .dataTables_filter input:focus {
        border-color: #667eea;
        outline: none;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .dataTables_wrapper .dataTables_length select {
        border: 2px solid #e5e7eb !important;
        border-radius: 0.5rem !important;
        padding: 0.375rem 2rem 0.375rem 0.75rem !important;
        margin: 0 0.5rem !important;
        background-color: white !important;
        color: #495057 !important;
        font-size: 0.875rem !important;
        line-height: 1.5 !important;
        height: auto !important;
        min-height: 38px !important;
    }

    .dataTables_wrapper .dataTables_length select:focus {
        border-color: #667eea !important;
        outline: none !important;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1) !important;
    }

    .dataTables_wrapper .dataTables_length select option {
        padding: 0.375rem 0.75rem !important;
        background-color: white !important;
        color: #495057 !important;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button {
        padding: 0.375rem 0.75rem;
        margin: 0 2px;
        border-radius: 0.375rem;
        border: 1px solid #e5e7eb;
        background: white;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white !important;
        border-color: #667eea;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white !important;
        border-color: #667eea;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    /* Route Names Wrapper */
    .route-names-wrapper {
        max-width: 250px;
        display: flex;
        flex-wrap: wrap;
        gap: 2px;
        align-items: flex-start;
    }
    
    .route-names-wrapper .route-badge {
        flex-shrink: 0;
        white-space: nowrap;
        font-size: 0.7rem;
        padding: 0.25rem 0.5rem;
        margin: 0;
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
                <div class="page-header-icon-box me-3">
                    <i class="fas fa-shield-alt fa-lg"></i>
                </div>
                <div>
                    <h1 class="page-header-title">Permission Management</h1>
                    <p class="page-header-subtitle">Configure and manage system permissions for secure access control</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Overview -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value text-primary">{{ count($permissions) }}</div>
            <div class="stat-label">Total Permissions</div>
        </div>
        <div class="stat-card">
            <div class="stat-value text-success">{{ count($availableRoutes) }}</div>
            <div class="stat-label">Available Routes</div>
        </div>
        <div class="stat-card">
            <div class="stat-value text-warning">{{ \App\Models\Permission::getAllUsedRouteNames()->count() }}</div>
            <div class="stat-label">Assigned Routes</div>
        </div>
        <div class="stat-card">
            <div class="stat-value text-info">{{ collect(\Route::getRoutes())->map(fn($route) => $route->getName())->filter()->count() }}</div>
            <div class="stat-label">Total Routes</div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="row">
        <div class="col-12">
            <div class="content-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0"><i class="fas fa-list-ul me-2"></i>Permissions Overview</h5>
                        <small class="text-muted">Manage route-based permissions and access control</small>
                    </div>
                    <button type="button" class="btn add-btn" data-toggle="modal" data-target="#addPermissionModal">
                        <i class="fa fa-plus me-2"></i>Add Permission
                    </button>
                </div>
                <div class="card-body p-0">
                    <!-- Inline Alerts (injected dynamically) -->
                    <div id="permissionAlerts" class="px-4 pt-4"></div>
                    <!-- Info Panel -->
                    <div class="alert alert-info mx-4 mt-4 mb-0" style="border-radius: 0.75rem;">
                        <div class="d-flex align-items-start">
                            <i class="fas fa-info-circle fa-lg me-3 mt-1"></i>
                            <div>
                                <h6 class="alert-heading mb-2">Permission Management Guide</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <p class="mb-1"><strong>Route-based permissions:</strong> Each permission can protect one or more Laravel routes</p>
                                        <p class="mb-1"><strong>No duplicates:</strong> Each route can only be assigned to one permission</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="mb-1"><strong>Smart grouping:</strong> Group related routes (like GET + POST) under one permission</p>
                                        <p class="mb-0"><strong>Access control:</strong> Permissions control what users can do in the system</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Permissions Table -->
                    <div class="table-responsive p-4">
                        <div class="table-responsive p-4" style="overflow-x:auto;">
                            <table class="table table-hover datatable" style="min-width:900px;">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Display Name</th>
                                    <th>Permission Name</th>
                                    <th><i class="fas fa-route me-1"></i><strong>Route Names</strong></th>
                                    <th class="d-none d-md-table-cell">Module</th>
                                    <th class="d-none d-md-table-cell">Action</th>
                                    <th>Status</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($permissions as $permission)
                                <tr>
                                    <td>{{ $permission->id }}</td>
                                    <td>
                                        <a href="#" onclick="editPermission({{ $permission->id }}); return false;" class="text-primary font-weight-medium" style="text-decoration: none;">
                                            {{ $permission->display_name }}
                                        </a>
                                    </td>
                                    <td><code>{{ $permission->name }}</code></td>
                                    <td>
                                        @php
                                            $routes = [];
                                            $routeNames = $permission->route_names;
                                            
                                            // Ensure route_names is always an array
                                            if (is_string($routeNames)) {
                                                $routeNames = json_decode($routeNames, true) ?: [];
                                            } elseif (!is_array($routeNames)) {
                                                $routeNames = [];
                                            }
                                            
                                            if(!empty($routeNames) && count($routeNames) > 0) {
                                                $routes = $routeNames;
                                            } elseif($permission->route_name) {
                                                $routes = [$permission->route_name];
                                            }
                                        @endphp
                                        
                                        @if(count($routes) > 0)
                                            <div class="route-names-wrapper">
                                                @foreach($routes as $route)
                                                    <span class="badge bg-info text-dark route-badge me-1 mb-1">{{ $route }}</span>
                                                @endforeach
                                            </div>
                                            @if(count($routes) > 1)
                                                <small class="text-success d-block mt-1">
                                                    <i class="fa fa-link"></i> {{ count($routes) }} routes grouped
                                                </small>
                                            @endif
                                        @else
                                            <span class="badge bg-warning text-dark">No Route Assigned</span>
                                            <small class="text-muted d-block">This permission is not route-protected</small>
                                        @endif
                                    </td>
                                    <td class="d-none d-md-table-cell"><span class="badge bg-secondary">{{ $permission->module }}</span></td>
                                    <td class="d-none d-md-table-cell">{{ $permission->action }}</td>
                                    <td>
                                        @if($permission->is_active)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-danger">Inactive</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <div class="dropdown dropdown-action">
                                            <a href="#" class="action-icon dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                                                <i class="material-icons">more_vert</i>
                                            </a>
                                            <div class="dropdown-menu dropdown-menu-right">
                                                <a class="dropdown-item" href="#" onclick="editPermission({{ $permission->id }})">
                                                    <i class="fa fa-pencil m-r-5"></i> Edit
                                                </a>
                                                <a class="dropdown-item" href="#" onclick="deletePermission({{ $permission->id }})">
                                                    <i class="fa fa-trash m-r-5"></i> Delete
                                                </a>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center">No permissions found</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
</div>

<!-- Add Permission Modal -->
<div class="modal fade" id="addPermissionModal" tabindex="-1" role="dialog" aria-labelledby="addPermissionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addPermissionModalLabel">Add New Permission</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="addPermissionForm">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="display_name" class="form-label">Display Name</label>
                                <input type="text" class="form-control" id="display_name" name="display_name" required>
                                <small class="text-muted">User-friendly name (e.g., "View Employee List")</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">Permission Name</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                                <small class="text-muted">System name (e.g., "employees.view")</small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Route Suggestions -->
                    <!-- @if(isset($routeSuggestions) && count($routeSuggestions) > 0)
                    <div class="mb-3">
                        <label class="form-label">Route Suggestions (Common Patterns)</label>
                        <div class="row">
                            @foreach(array_slice($routeSuggestions, 0, 2) as $suggestion)
                            <div class="col-xl-6 col-md-6 mb-2">
                                <div class="card border-info">
                                    <div class="card-body p-2">
                                        <h6 class="card-title mb-1 text-primary">
                                            <i class="fa fa-lightbulb-o me-1"></i>{{ $suggestion['label'] }}
                                        </h6>
                                        <p class="card-text small mb-2">{{ $suggestion['description'] }}</p>
                                        <div class="mb-2">
                                            @foreach($suggestion['routes'] as $route)
                                                <span class="badge bg-secondary route-badge me-1">{{ $route }}</span>
                                            @endforeach
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-primary suggestion-btn" 
                                                onclick="useSuggestion({{ json_encode($suggestion['routes']) }}, '{{ $suggestion['label'] }}')">
                                            <i class="fa fa-magic me-1"></i>Use This Pattern
                                        </button>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif -->
                    
                    <!-- Route Selection -->
                    <div class="mb-3">
                        <label for="route_names" class="form-label">
                            <i class="fa fa-route"></i> Route Names 
                            <span class="badge bg-info text-dark">{{ count($availableRoutes) }} available</span>
                        </label>
                        
                        <select class="form-control form-select select2-route-names" id="route_names" name="route_names[]" multiple="multiple" required style="width: 100%;">
                            @if(count($availableRoutes) > 0)
                                @foreach($availableRoutes as $route)
                                    <option value="{{ $route }}">{{ $route }}</option>
                                @endforeach
                            @else
                                <option disabled>No available routes - all routes are already assigned to permissions</option>
                            @endif
                        </select>
                        <small class="text-muted mt-1 d-block">
                            <i class="fa fa-info-circle"></i> 
                            Select multiple routes to group under this permission. Only unused routes are shown.
                            @if(count($availableRoutes) == 0)
                                <br><span class="text-warning"><i class="fa fa-exclamation-triangle"></i> All routes are currently assigned to existing permissions.</span>
                            @endif
                        </small>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="module" class="form-label">Module</label>
                                <input type="text" class="form-control" id="module" name="module" required>
                                <small class="text-muted">e.g., employees, users, payroll</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="action" class="form-label">Action</label>
                                <input type="text" class="form-control" id="action" name="action" required>
                                <small class="text-muted">e.g., view, create, edit, delete</small>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="2"></textarea>
                        <small class="text-muted">Optional description of what this permission allows</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="savePermission()">Add Permission</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Permission Modal -->
<div class="modal fade" id="editPermissionModal" tabindex="-1" role="dialog" aria-labelledby="editPermissionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editPermissionModalLabel">Edit Permission</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="editPermissionForm">
                    @csrf
                    <input type="hidden" id="edit_id" name="id">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_display_name" class="form-label">Display Name</label>
                                <input type="text" class="form-control" id="edit_display_name" name="display_name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_name" class="form-label">Permission Name</label>
                                <input type="text" class="form-control" id="edit_name" name="name" required>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_route_names" class="form-label">
                            <i class="fa fa-route"></i> Route Names
                        </label>
                        
                        <select class="form-control form-select select2-route-names-edit" id="edit_route_names" name="route_names[]" multiple="multiple" required style="width: 100%;">
                            <!-- Options will be populated dynamically -->
                        </select>
                        <small class="text-muted mt-1 d-block">
                            <i class="fa fa-info-circle"></i> Current routes (highlighted in blue) + available routes are shown.
                        </small>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_module" class="form-label">Module</label>
                                <input type="text" class="form-control" id="edit_module" name="module" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_action" class="form-label">Action</label>
                                <input type="text" class="form-control" id="edit_action" name="action" required>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_description" class="form-label">Description</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="2"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="updatePermission()">Update Permission</button>
            </div>
        </div>
    </div>
</div>
@endsection
@section('script')
<script>
// Ensure DOM is ready and all libraries are loaded
$(document).ready(function() {
    console.log('DOM Ready - Initializing Select2 and DataTables');
    console.log('jQuery version:', $.fn.jquery);
    console.log('Select2 available:', typeof $.fn.select2 !== 'undefined');
    console.log('DataTable available:', typeof $.fn.DataTable !== 'undefined');
    
    // Initialize DataTable first
    initializeDataTable();
    
    // Debug: Log available routes from server
    var availableRoutes = @json($availableRoutes);
    console.log('Available routes from server:', availableRoutes.length);
    console.log('Employee routes available:', availableRoutes.filter(route => route.includes('employees')));
    
    // Delay initialization to ensure everything is loaded
    setTimeout(function() {
        initializeSelect2();
    }, 500);
    
    // Modal event handlers for proper Select2 initialization
    $('#addPermissionModal').on('shown.bs.modal', function () {
        // Reset form and clear alerts
        document.getElementById('addPermissionForm').reset();
        clearAlerts();
        setTimeout(function() {
            if ($('.select2-route-names').hasClass('select2-hidden-accessible')) {
                $('.select2-route-names').select2('destroy');
            }
            $('.select2-route-names').select2({
                placeholder: 'Search and select routes...',
                allowClear: true,
                width: '100%',
                tags: false,
                dropdownParent: $('#addPermissionModal'),
                dropdownAutoWidth: true,
                dropdownCssClass: 'select2-dropdown-permission',
                selectionCssClass: 'select2-selection-permission',
                templateResult: function(option) {
                    if (!option.id) return option.text;
                    var optionText = option.text;
                    if (optionText.includes('(current)')) {
                        return $('<span style="background-color: #e3f2fd; padding: 2px 6px; border-radius: 3px;"><i class="fas fa-check-circle text-primary me-2"></i>' + optionText + '</span>');
                    } else {
                        return $('<span><i class="fas fa-route me-2" style="color: #007bff;"></i>' + optionText + '</span>');
                    }
                },
                templateSelection: function(option) {
                    if (!option.id) return option.text;
                    var optionText = option.text.replace(' (current)', '');
                    return $('<span><i class="fas fa-route me-1" style="color: white;"></i>' + optionText + '</span>');
                },
                adaptDropdownCssClass: function() {
                    return 'select2-dropdown-permission';
                }
            });
        }, 150);
    });

    $('#editPermissionModal').on('shown.bs.modal', function () {
        setTimeout(function() {
            if ($('.select2-route-names-edit').hasClass('select2-hidden-accessible')) {
                $('.select2-route-names-edit').select2('destroy');
            }
            $('.select2-route-names-edit').select2({
                placeholder: 'Search and select routes...',
                allowClear: true,
                width: '100%',
                tags: false,
                dropdownParent: $('#editPermissionModal'),
                dropdownAutoWidth: true,
                dropdownCssClass: 'select2-dropdown-permission',
                selectionCssClass: 'select2-selection-permission',
                templateResult: function(option) {
                    if (!option.id) return option.text;
                    var optionText = option.text;
                    if (optionText.includes('(current)')) {
                        return $('<span style="background-color: #e3f2fd; padding: 2px 6px; border-radius: 3px;"><i class="fas fa-check-circle text-primary me-2"></i>' + optionText + '</span>');
                    } else {
                        return $('<span><i class="fas fa-route me-2" style="color: #007bff;"></i>' + optionText + '</span>');
                    }
                },
                templateSelection: function(option) {
                    if (!option.id) return option.text;
                    var optionText = option.text.replace(' (current)', '');
                    return $('<span><i class="fas fa-route me-1" style="color: white;"></i>' + optionText + '</span>');
                },
                adaptDropdownCssClass: function() {
                    return 'select2-dropdown-permission';
                }
            });
        }, 150);
    });
});

function initializeSelect2() {
    console.log('Initializing Select2 instances...');
    
    // Only initialize if elements exist and are not already initialized
    if ($('.select2-route-names').length && !$('.select2-route-names').hasClass('select2-hidden-accessible')) {
        $('.select2-route-names').select2({
            placeholder: 'Search and select routes...',
            allowClear: true,
            width: '100%',
            tags: false,
            dropdownParent: $('#addPermissionModal'),
            dropdownAutoWidth: true,
            dropdownCssClass: 'select2-dropdown-permission',
            selectionCssClass: 'select2-selection-permission'
        });
    }
    
    if ($('.select2-route-names-edit').length && !$('.select2-route-names-edit').hasClass('select2-hidden-accessible')) {
        $('.select2-route-names-edit').select2({
            placeholder: 'Search and select routes...',
            allowClear: true,
            width: '100%',
            tags: false,
            dropdownParent: $('#editPermissionModal'),
            dropdownAutoWidth: true,
            dropdownCssClass: 'select2-dropdown-permission',
            selectionCssClass: 'select2-selection-permission'
        });
    }
    
    console.log('Select2 initialization completed');
}

function initializeDataTable() {
    console.log('Initializing DataTable...');
    
    if ($('.datatable').length && typeof $.fn.DataTable !== 'undefined') {
        $('.datatable').each(function() {
            var $table = $(this);
            
            // Check if DataTable is already initialized
            if ($.fn.DataTable.isDataTable($table)) {
                console.log('DataTable already initialized, destroying and reinitializing...');
                $table.DataTable().destroy();
            }
            
            // Initialize DataTable
            $table.DataTable({
                "pageLength": 10,
                "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
                "order": [[0, "asc"]], // Sort by ID column
                "columnDefs": [
                    { "orderable": false, "targets": [7] }, // Disable sorting on Actions column
                    { "searchable": false, "targets": [7] }  // Exclude Actions from search
                ],
                "language": {
                    "search": "_INPUT_",
                    "searchPlaceholder": "Search permissions...",
                    "lengthMenu": "Show _MENU_ permissions",
                    "info": "Showing _START_ to _END_ of _TOTAL_ permissions",
                    "infoEmpty": "No permissions to display",
                    "infoFiltered": "(filtered from _MAX_ total permissions)",
                    "zeroRecords": "No matching permissions found",
                    "emptyTable": "No permissions available",
                    "paginate": {
                        "first": "First",
                        "last": "Last",
                        "next": "Next",
                        "previous": "Previous"
                    }
                },
                "dom": '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>'
            });
        });
        
        console.log('DataTable initialization completed');
    } else {
        console.log('DataTable not available or no .datatable elements found');
    }
}

function useSuggestion(routes, label) {
    console.log('Using suggestion:', routes, label);
    
    // Set values using Select2
    $('.select2-route-names').val(routes).trigger('change');
    
    // Auto-fill form fields
    const nameParts = label.toLowerCase().split(' - ');
    if (nameParts.length > 1) {
        const module = nameParts[0].replace(/\s+/g, '_');
        const action = nameParts[1].replace(/\s+/g, '_').replace('/', '_');
        
        document.getElementById('module').value = module;
        document.getElementById('action').value = action;
        document.getElementById('name').value = module + '.' + action;
        document.getElementById('display_name').value = label;
    }
}

function savePermission() {
    const form = document.getElementById('addPermissionForm');
    const formData = new FormData(form);
    
    // Get selected routes using Select2
    const selectedRoutes = $('.select2-route-names').val();
    console.log('Selected routes for save:', selectedRoutes);
    
    if (!selectedRoutes || selectedRoutes.length === 0) {
        showAlert('Please select at least one route for this permission.', 'warning');
        return;
    }
    
    // Show loading state
    const saveBtn = document.querySelector('#addPermissionModal .btn-primary');
    const originalText = saveBtn.textContent;
    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Saving...';
    saveBtn.disabled = true;
    
    fetch('{{ route("permissions.save") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(data.message, 'success');
            $('#addPermissionModal').modal('hide');
            setTimeout(() => location.reload(), 1500);
        } else {
            showAlert('Error: ' + data.message, 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('An error occurred while saving the permission.', 'danger');
    })
    .finally(() => {
        saveBtn.innerHTML = originalText;
        saveBtn.disabled = false;
    });
}

function editPermission(id) {
    showAlert('Loading permission data...', 'info');
    
    fetch(`{{ url('permissions/get') }}/${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const permission = data.permission;
                
                // Fill form fields
                document.getElementById('edit_id').value = permission.id;
                document.getElementById('edit_display_name').value = permission.display_name;
                document.getElementById('edit_name').value = permission.name;
                document.getElementById('edit_module').value = permission.module;
                document.getElementById('edit_action').value = permission.action;
                document.getElementById('edit_description').value = permission.description || '';
                
                // Handle route names
                const currentRoutes = permission.route_names || (permission.route_name ? [permission.route_name] : []);
                console.log('Current routes:', currentRoutes);
                
                // Use fresh available routes from API response
                const availableRoutes = data.availableRoutes || [];
                console.log('Fresh available routes from API:', availableRoutes);
                
                // Clear and populate Select2
                const $select = $('.select2-route-names-edit');
                $select.empty();
                
                // Add current routes first (these will always be shown and selected)
                currentRoutes.forEach(route => {
                    const option = new Option(route + ' (current)', route, false, true);
                    $select.append(option);
                });
                
                // Add available routes (not currently assigned to any other permission)
                availableRoutes.forEach(route => {
                    if (!currentRoutes.includes(route)) {
                        const option = new Option(route, route, false, false);
                        $select.append(option);
                    }
                });
                
                $select.trigger('change');
                
                $('#editPermissionModal').modal('show');
                clearAlerts();
            } else {
                showAlert('Error loading permission: ' + data.message, 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('An error occurred while loading the permission.', 'danger');
        });
}
window.editPermission = editPermission;

function updatePermission() {
    const form = document.getElementById('editPermissionForm');
    const formData = new FormData(form);
    
    const selectedRoutes = $('.select2-route-names-edit').val();
    console.log('Selected routes for update:', selectedRoutes);
    
    if (!selectedRoutes || selectedRoutes.length === 0) {
        showAlert('Please select at least one route for this permission.', 'warning');
        return;
    }
    
    const updateBtn = document.querySelector('#editPermissionModal .btn-primary');
    const originalText = updateBtn.textContent;
    updateBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Updating...';
    updateBtn.disabled = true;
    
    fetch('{{ route("permissions.update") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(data.message, 'success');
            $('#editPermissionModal').modal('hide');
            setTimeout(() => location.reload(), 1500);
        } else {
            showAlert('Error: ' + data.message, 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('An error occurred while updating the permission.', 'danger');
    })
    .finally(() => {
        updateBtn.innerHTML = originalText;
        updateBtn.disabled = false;
    });
}

function deletePermission(id) {
    if (confirm('Are you sure you want to delete this permission? This action cannot be undone.')) {
        const formData = new FormData();
        formData.append('id', id);
        
        showAlert('Deleting permission...', 'info');
        
        fetch('{{ route("permissions.delete") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert(data.message, 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showAlert('Error: ' + data.message, 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('An error occurred while deleting the permission.', 'danger');
        });
    }
}

function showAlert(message, type = 'info') {
    clearAlerts();
    const map = {
        success: { cls: 'alert-modern-success', icon: 'fas fa-check-circle' },
        danger: { cls: 'alert-modern-danger', icon: 'fas fa-exclamation-triangle' },
        warning: { cls: 'alert-modern-warning', icon: 'fas fa-exclamation-circle' },
        info: { cls: 'alert-modern-info', icon: 'fas fa-info-circle' }
    };
    const meta = map[type] || map.info;
    const $container = $('#permissionAlerts');
    if (!$container.length) {
        // Fallback: inject at top of main content card
        $('.content-card .card-body').first().prepend('<div id="permissionAlerts" class="px-4 pt-4"></div>');
    }
    const html = `
        <div class="alert ${meta.cls} alert-dismissible fade show alert-inline-wrapper permission-alert" role="alert">
            <div class="d-flex align-items-center">
                <i class="${meta.icon} me-2"></i>
                <div class="flex-grow-1">${message}</div>
                <button type="button" class="close ms-2" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        </div>`;
    $('#permissionAlerts').html(html);
    if (type !== 'danger') {
        setTimeout(() => { 
            $('#permissionAlerts .permission-alert').alert('close'); 
        }, 4000);
    }
}

function clearAlerts() {
    $('#permissionAlerts').empty();
}
</script>
@endsection