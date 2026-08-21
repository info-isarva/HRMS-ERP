@extends('layouts.master')
@section('title', 'User Management')
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
    
    .page-header-stats {
        text-align: right;
    }
    
    .page-header-stats-label {
        font-size: 0.875rem;
        color: rgba(255, 255, 255, 0.9);
        margin-bottom: 0.25rem;
    }
    
    .page-header-stats-value {
        font-size: 1.875rem;
        font-weight: 700;
        color: white;
    }
    
    .page-header-stats-icon {
        width: 5rem;
        height: 5rem;
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    /* Modern Filter Card */
    .filter-card {
        background: white;
        border-radius: 1rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
        padding: 1.5rem;
        margin-bottom: 2rem;
        border: 1px solid #e5e7eb;
    }
    
    .filter-card .form-control,
    .filter-card .form-control:focus {
        border: 2px solid #e5e7eb;
        border-radius: 0.5rem;
        padding: 0.625rem 1rem;
        font-size: 0.875rem;
        transition: all 0.2s ease;
    }
    
    .filter-card .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }
    
    .filter-card .select {
        border: 2px solid #e5e7eb !important;
        border-radius: 0.5rem !important;
        padding: 0.625rem 1rem !important;
        font-size: 0.875rem !important;
        width: 100% !important;
        height: auto !important;
        background-color: white !important;
    }
    
    .filter-card .select:focus {
        border-color: #667eea !important;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1) !important;
        outline: none !important;
    }
    
    .filter-card select.form-control {
        border: 2px solid #e5e7eb !important;
        border-radius: 0.5rem !important;
        padding: 0.625rem 1rem !important;
        font-size: 0.875rem !important;
        height: auto !important;
        min-height: 42px !important;
        background-color: white !important;
    }
    
    .filter-card select.form-control:focus {
        border-color: #667eea !important;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1) !important;
        outline: none !important;
    }
    
    .filter-card .btn {
        border-radius: 0.5rem;
        padding: 0.625rem 1.5rem;
        font-weight: 500;
        transition: all 0.2s ease;
    }
    
    .filter-card .btn-success {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
    }
    
    .filter-card .btn-success:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
    }
    
    /* Modern Table Card */
    .table-card {
        background: white;
        border-radius: 1rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
        overflow: visible;
        border: 1px solid #e5e7eb;
        padding: 1rem;
    }
    
    .table-card .table {
        margin-bottom: 0;
        width: 100% !important;
    }
    
    .table thead th {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white !important;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
        border: none;
        padding: 1rem 0.75rem;
        white-space: nowrap;
    }
    
    .table tbody tr {
        transition: background-color 0.2s ease;
        border-bottom: 1px solid #f3f4f6;
    }
    
    .table tbody tr:hover {
        background: #f9fafb !important;
    }
    
    .table tbody td {
        padding: 1rem 0.75rem;
        vertical-align: middle;
        font-size: 0.875rem;
        color: #374151;
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
        border: 2px solid #e5e7eb;
        border-radius: 0.5rem;
        padding: 0.375rem 2rem 0.375rem 0.75rem;
        margin: 0 0.5rem;
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
    
    /* Action Buttons */
    .btn-action {
        width: 32px;
        height: 32px;
        border-radius: 0.375rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: none;
        transition: all 0.2s ease;
        font-size: 0.875rem;
        margin: 0 2px;
    }
    
    .btn-action:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    }
    
    .btn-action-edit {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        color: white;
    }
    
    .btn-action-delete {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
    }
    
    /* Modern Badges */
    .badge {
        padding: 0.375rem 0.75rem;
        font-weight: 500;
        border-radius: 0.375rem;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .bg-success {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
    }
    
    .bg-danger {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
    }

    /* Add responsive styles for mobile */
    @media (max-width: 768px) {
        .table-card {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .table {
            min-width: 600px;
        }

        .page-header-card {
            padding: 1rem;
        }

        .filter-card .row > [class*="col-"] {
            margin-bottom: 1rem;
        }

        .filter-card .row > [class*="col-"]:last-child {
            margin-bottom: 0;
        }

        .btn {
            font-size: 0.875rem;
            padding: 0.5rem 1rem;
        }
    }

    /* Add responsive styles for medium screens */
    @media (min-width: 769px) and (max-width: 1550px) {
        .table-card {
            padding: 1rem;
            border: 1px solid #ddd;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .table {
            min-width: 100%;
            font-size: 0.85rem;
        }

        .table thead th {
            font-size: 0.8rem;
            padding: 0.5rem 0.25rem;
            white-space: nowrap;
        }

        .table tbody td {
            font-size: 0.8rem;
            padding: 0.5rem 0.25rem;
            white-space: nowrap;
        }

        .filter-card {
            padding: 1rem;
        }

        .filter-card .form-control {
            font-size: 0.85rem;
            padding: 0.4rem 0.6rem;
        }

        .btn {
            font-size: 0.85rem;
            padding: 0.4rem 0.8rem;
        }

        .page-header-card {
            padding: 1rem;
        }

        .page-header-title {
            font-size: 1.4rem;
        }

        .page-header-subtitle {
            font-size: 0.85rem;
        }

        .page-header-stats-icon {
            width: 4rem;
            height: 4rem;
        }

        .page-header-stats-icon i {
            font-size: 1.5rem;
        }
    }
</style>

    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <!-- Page Content -->
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
                                        <i class="fas fa-user-shield text-white" style="font-size: 1.5rem;"></i>
                                    </div>
                                    <div>
                                        <h1 class="page-header-title">User Management</h1>
                                        <p class="page-header-subtitle">
                                            Manage system users and their access permissions
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4 d-none d-lg-flex align-items-center justify-content-end">
                                <div class="page-header-stats me-4">
                                    <p class="page-header-stats-label mb-1">Total Users</p>
                                    <p class="page-header-stats-value mb-0" id="user-count">0</p>
                                </div>
                                <div class="page-header-stats-icon">
                                    <i class="fas fa-users text-white" style="font-size: 2rem;"></i>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Action Buttons -->
                       <!--  @if (Auth::user()->hasPermission('user_management.add'))
                            <div class="row mt-4">
                                <div class="col">
                                    <a href="#" class="btn btn-light btn-lg" data-toggle="modal" data-target="#add_user">
                                        <i class="fa fa-plus me-2"></i> Add User
                                    </a>
                                </div>
                            </div>
                        @endif -->
                    </div>
                </div>
            </div>
            <!-- /Modern Page Header -->

            <!-- Modern Filter Card -->
            <div class="filter-card">
                <div class="row align-items-end">
                    <div class="col-md-3 mb-3 mb-md-0">
                        <label class="font-weight-600 text-muted mb-2" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">
                            <i class="fas fa-user me-1"></i> User Name
                        </label>
                        <input type="text" class="form-control" id="user_name" name="user_name" placeholder="Search by user name">
                    </div>
                    <div class="col-md-3 mb-3 mb-md-0">
                        <label class="font-weight-600 text-muted mb-2" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">
                            <i class="fas fa-user-tag me-1"></i> Role Name
                        </label>
                        <select class="form-control form-select" id="type_role"> 
                            <option value="">All Roles</option>
                            @foreach ($role_name as $name)
                                <option value="{{ $name->role_name }}">{{ $name->role_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-3 mb-md-0">
                        <label class="font-weight-600 text-muted mb-2" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">
                            <i class="fas fa-toggle-on me-1"></i> Status
                        </label>
                        <select class="form-control form-select" id="type_status"> 
                            <option value="">All Status</option>
                            @foreach ($status_user as $status )
                                <option value="{{ $status->status_name }}">{{ $status->status_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-3 mb-md-0">
                        <button type="button" class="btn btn-success btn-block btn_search">
                            <i class="fas fa-search me-2"></i> Search
                        </button>  
                    </div>
                </div>
            </div>

            <!-- Modern Table Card -->
            <div class="table-card">
                <table class="table table-hover" id="userDataList" style="width: 100%">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Name</th>
                            <th>User ID</th>
                            <th>Email</th>
                            {{-- <th>Position</th> --}}
                            <th>Phone</th>
                            <th>Join Date</th>
                            <th>Last Login</th>
                            <th>Role</th>
                            <th>Status</th>
                            {{-- <th>Departement</th> --}}
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
        <!-- /Page Content -->

        <!-- Add User Modal -->
        <div id="add_user" class="modal custom-modal fade" role="dialog">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add New User</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form action="{{ route('user/add/save') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="row"> 
                                <div class="col-sm-12"> 
                                    <div class="form-group">
                                        <label>Full Name <span class="text-danger">*</span></label>
                                        <input class="form-control @error('name') is-invalid @enderror" type="text" name="name" value="{{ old('name') }}" placeholder="Enter Full Name" required>
                                        @error('name')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row"> 
                                <div class="col-sm-6"> 
                                    <div class="form-group">
                                        <label>Email Address <span class="text-danger">*</span></label>
                                        <input class="form-control @error('email') is-invalid @enderror" type="email" name="email" value="{{ old('email') }}" placeholder="Enter Email" required>
                                        @error('email')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-sm-6"> 
                                    <div class="form-group">
                                        <label>Phone</label>
                                        <input class="form-control @error('phone') is-invalid @enderror" type="tel" name="phone" value="{{ old('phone') }}" placeholder="Enter Phone">
                                        @error('phone')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row"> 
                                <div class="col-sm-6"> 
                                    <div class="form-group">
                                        <label>Department <span class="text-danger">*</span></label>
                                        <select class="select form-control @error('department') is-invalid @enderror" name="department" required>
                                            <option value="" disabled selected>--Select Department--</option>
                                            @foreach ($department as $dept)
                                                <option value="{{ $dept->id }}" {{ old('department') == $dept->id ? 'selected' : '' }}>{{ $dept->department }}</option>
                                            @endforeach
                                        </select>
                                        @error('department')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-sm-6"> 
                                    <div class="form-group">
                                        <label>Designation <span class="text-danger">*</span></label>
                                        <select class="select form-control @error('designation') is-invalid @enderror" name="designation" required>
                                            <option value="" disabled selected>--Select Designation--</option>
                                            @foreach ($position as $pos)
                                                <option value="{{ $pos->id }}" {{ old('designation') == $pos->id ? 'selected' : '' }}>{{ $pos->position }}</option>
                                            @endforeach
                                        </select>
                                        @error('designation')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row"> 
                                <div class="col-sm-6"> 
                                    <div class="form-group">
                                        <label>Role Name</label>
                                        <select class="select form-control" name="role_name">
                                            <option value="" disabled selected>--Select Role--</option>
                                            @foreach ($role_name as $role)
                                                <option value="{{ $role->role_name }}" {{ old('role_name') == $role->role_name ? 'selected' : '' }}>{{ $role->role_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-6"> 
                                    <div class="form-group">
                                        <label>Status <span class="text-danger">*</span></label>
                                        <select class="select form-control @error('status') is-invalid @enderror" name="status" required>
                                            <option value="" disabled selected>--Select Status--</option>
                                            @foreach ($status_user as $status)
                                                <option value="{{ $status->status_name }}" {{ old('status') == $status->status_name ? 'selected' : '' }}>{{ $status->status_name }}</option>
                                            @endforeach
                                        </select>
                                        @error('status')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row"> 
                                <div class="col-sm-6"> 
                                    <div class="form-group">
                                        <label>Photo</label>
                                        <input class="form-control @error('image') is-invalid @enderror" type="file" name="image" accept="image/*">
                                        @error('image')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-sm-6"> 
                                    <div class="form-group">
                                        <label>Password <span class="text-danger">*</span></label>
                                        <input type="password" class="form-control @error('password') is-invalid @enderror" name="password" placeholder="Enter Password" required>
                                        @error('password')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row"> 
                                <div class="col-sm-6"> 
                                    <div class="form-group">
                                        <label>Confirm Password <span class="text-danger">*</span></label>
                                        <input type="password" class="form-control" name="password_confirmation" placeholder="Confirm Password" required>
                                    </div>
                                </div>
                            </div>
                            <div class="submit-section">
                                <button type="submit" class="btn btn-primary submit-btn">Create User & Sync</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- /Add User Modal -->
				
        <!-- Edit User Modal -->
        <div id="edit_user" class="modal custom-modal fade" role="dialog">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            Edit User
                            <span id="employee_id_header" class="badge bg-info text-dark ms-2" style="display:none;">
                                Employee ID: <span id="employee_id_display_header"></span>
                            </span>
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <!-- Employee User Warning Message -->
                        <div id="employee_warning" class="alert alert-info" style="display:none;">
                            <i class="fa fa-info-circle"></i>
                            <strong>Employee User:</strong> 
                            <span id="employee_warning_message">This user was created from an employee. Most fields can only be updated in the Employee module.</span>
                            <br><small>Only the password can be changed here. To update name, email, phone, department, or role, please edit the employee record.</small>
                        </div>
                        
                        <form action="{{ route('user/update/sync') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="user_id" id="e_id">
                            <!-- Hidden field to ensure status is always submitted for employee users -->
                            <input type="hidden" name="status_backup" id="e_status_backup">
                            <div class="row"> 
                                <div class="col-sm-12"> 
                                    <div class="form-group">
                                        <label>Full Name <span class="text-danger">*</span></label>
                                        <input class="form-control" type="text" name="name" id="e_name" required>
                                    </div>
                                </div>
                            </div>
                            <div class="row"> 
                                <div class="col-sm-6"> 
                                    <div class="form-group">
                                        <label>Email <span class="text-danger">*</span></label>
                                        <input class="form-control" type="email" name="email" id="e_email" required>
                                    </div>
                                </div>
                                <div class="col-sm-6"> 
                                    <div class="form-group">
                                        <label>Phone</label>
                                        <input class="form-control" type="tel" name="phone" id="e_phone" placeholder="Enter Phone">
                                    </div>
                                </div>
                            </div>
                            <div class="row"> 
                                <div class="col-sm-6"> 
                                    <div class="form-group">
                                        <label>Department <span class="text-danger">*</span></label>
                                        <select class="select form-control" name="department" id="e_department" required>
                                            <option value="">--Select Department--</option>
                                            @foreach ($department as $dept)
                                                <option value="{{ $dept->id }}">{{ $dept->department }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-6"> 
                                    <div class="form-group">
                                        <label>Designation <span class="text-danger">*</span></label>
                                        <select class="select form-control" name="designation" id="e_designation" required>
                                            <option value="">--Select Designation--</option>
                                            @foreach ($position as $pos)
                                                <option value="{{ $pos->id }}">{{ $pos->position }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row"> 
                                <div class="col-sm-6"> 
                                    <div class="form-group">
                                        <label>Role Name</label>
                                        <select class="select form-control" name="role_name" id="e_role_name">
                                            <option value="">--Select Role--</option>
                                            @foreach ($role_name as $role)
                                                <option value="{{ $role->role_name }}">{{ $role->role_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-6"> 
                                    <div class="form-group">
                                        <label>Status <span class="text-danger">*</span></label>
                                        <select class="select form-control" name="status" id="e_status" required>
                                            @foreach ($status_user as $status)
                                                <option value="{{ $status->status_name }}">{{ $status->status_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row"> 
                                <div class="col-sm-6"> 
                                    <div class="form-group">
                                        <label>New Password (Leave blank to keep current password)</label>
                                        <input class="form-control" type="password" name="password" id="e_password" placeholder="Enter new password">
                                        <div class="form-text">Minimum 8 characters required.</div>
                                    </div>
                                </div>
                                <div class="col-sm-6"> 
                                    <div class="form-group">
                                        <label>Confirm New Password</label>
                                        <input class="form-control" type="password" name="password_confirmation" id="e_password_confirmation" placeholder="Confirm new password">
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Photo upload section - only for manually created users -->
                            <div class="row" id="photo_upload_section" style="display: none;"> 
                                <div class="col-sm-6"> 
                                    <div class="form-group">
                                        <label>Profile Photo</label>
                                        <input class="form-control" type="file" name="images" id="e_image" accept="image/*">
                                        <input type="hidden" name="hidden_image" id="e_hidden_image">
                                        <div class="form-text">Upload a new photo to replace the current one.</div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label>Current Photo</label>
                                        <div id="current_photo_preview" style="margin-top: 8px;">
                                            <img id="current_photo_img" src="" alt="Current Photo" style="width: 60px; height: 60px; object-fit: cover; border-radius: 50%; border: 2px solid #ddd;">
                                            <div id="no_photo_text" style="color: #666; font-style: italic;">No photo uploaded</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="submit-section">
                                <button type="submit" class="btn btn-primary submit-btn">Update & Sync</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- /Edit User Modal -->
				
        <!-- Delete User Modal -->
        <div class="modal custom-modal fade" id="delete_user" role="dialog">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body">
                        <div class="form-header">
                            <h3>Delete User</h3>
                            <p>Are you sure you want to delete this user? This will also remove the user from the attendance system.</p>
                        </div>
                        <div class="modal-btn delete-action">
                            <form action="{{ route('user/delete/sync') }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="user_id" class="e_id" value="">
                                <input type="hidden" name="avatar" id="e_avatar" value="">
                                <div class="row">
                                    <div class="col-6">
                                        <button type="submit" class="btn btn-primary continue-btn submit-btn">Delete & Sync</button>
                                    </div>
                                    <div class="col-6">
                                        <a href="javascript:void(0);" data-dismiss="modal" class="btn btn-primary cancel-btn">Cancel</a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /Delete User Modal -->
    </div>
    <!-- /Page Wrapper -->
@section('script')

    <script type="text/javascript">
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            
            // Function to render null/undefined values as empty strings
            function renderEmptyAsBlank(data, type) {
                if (type === 'display') {
                    return data === null || data === undefined || data === 'null' || data === 'undefined' ? '' : data;
                }
                return data;
            }
            
            const table = $('#userDataList').DataTable({
                lengthMenu: [
                    [10, 25, 50, 100, 150],
                    [10, 25, 50, 100, 150]
                ],
                buttons: ['pageLength'],
                pageLength: 10,
                order: [[5, 'desc']],
                processing: true,
                serverSide: true,
                ordering: true,
                searching: true,
                ajax: {
                    url: "{{ route('get-users-data') }}",
                    type: "POST",
                    dataType: 'json',
                    data: function(d) {
                        d.user_name = $('#user_name').val();
                        d.type_role = $('#type_role').val();
                        d.type_status = $('#type_status').val();
                        d._token = $('meta[name="csrf-token"]').attr('content');
                    },
                    error: function(xhr, error, thrown) {
                        console.log('DataTables error:', error);
                        console.log('Server response:', xhr.responseText);
                        
                        // Show a user-friendly error message
                        $('#userDataList_processing').hide();
                        $('#userDataList tbody').html('<tr><td colspan="10" class="text-center">Error loading data. Please try refreshing the page.</td></tr>');
                    }
                },
                columns: [
                    { data: 'no', orderable: false },
                    { 
                        data: 'name',
                        render: function(data, type, row) {
                            if (type === 'display' && data) {
                                // Create a temporary DOM element to parse HTML
                                const temp = document.createElement('div');
                                temp.innerHTML = data;
                                const links = temp.querySelectorAll('a');
                                
                                // Replace each link with its content (removing the href wrapper)
                                links.forEach(link => {
                                    while(link.firstChild) {
                                        link.parentNode.insertBefore(link.firstChild, link);
                                    }
                                    link.parentNode.removeChild(link);
                                });
                                
                                return temp.innerHTML;
                            }
                            return data;
                        }
                    },
                    { data: 'user_id' },
                    { data: 'email' },
                    // { data: 'position' },
                    { 
                        data: 'phone_number',
                        render: function(data, type, row) {
                            // Extract the actual phone value from the HTML
                            let phoneText = '';
                            if (data) {
                                // Parse the HTML to extract the phone number
                                const tempDiv = document.createElement('div');
                                tempDiv.innerHTML = data;
                                const phoneSpan = tempDiv.querySelector('.phone_number');
                                if (phoneSpan) {
                                    // Use data-raw-phone attribute if available, otherwise use text content
                                    phoneText = phoneSpan.getAttribute('data-raw-phone') || phoneSpan.textContent;
                                }
                            }
                            return renderEmptyAsBlank(phoneText, type);
                        }
                    },
                    { data: 'join_date' },
                    { data: 'last_login' },
                    { data: 'role_name' },
                    {
                        data: 'status',
                        render: function(data, type, row) {
                            if (type === 'display') {
                                // Server sends HTML dropdown. Extract just the status text from the span.status_s element
                                let statusText = data || 'Unknown';

                                // If the status contains HTML tags, extract text from the status_s span
                                if (/</.test(statusText)) {
                                    const tmp = document.createElement('div');
                                    tmp.innerHTML = statusText;
                                    const statusSpan = tmp.querySelector('.status_s');
                                    if (statusSpan) {
                                        statusText = statusSpan.textContent || statusSpan.innerText || 'Unknown';
                                    } else {
                                        // Fallback: extract all text content if span not found
                                        statusText = (tmp.textContent || tmp.innerText || '').trim() || 'Unknown';
                                    }
                                }

                                statusText = statusText.trim();

                                let statusLower = statusText.toLowerCase();

                                // Predefined mappings for common statuses (case-insensitive)
                                const statusMappings = {
                                    'active': 'bg-success',
                                    'employed': 'bg-success',
                                    'current': 'bg-success',
                                    'working': 'bg-success',

                                    'inactive': 'bg-danger',
                                    'terminated': 'bg-danger',
                                    'resigned': 'bg-danger',
                                    'retired': 'bg-danger',
                                    'dismissed': 'bg-danger',
                                    'fired': 'bg-danger',

                                    'probation': 'bg-warning text-dark',
                                    'probation period': 'bg-warning text-dark',
                                    'training': 'bg-warning text-dark',
                                    'trial': 'bg-warning text-dark',
                                    'intern': 'bg-warning text-dark',
                                    'apprentice': 'bg-warning text-dark',
                                    'onboarding': 'bg-warning text-dark',

                                    'on leave': 'bg-info text-dark',
                                    'vacation': 'bg-info text-dark',
                                    'holiday': 'bg-info text-dark',
                                    'absent': 'bg-info text-dark',
                                    'maternity': 'bg-info text-dark',
                                    'paternity': 'bg-info text-dark',

                                    'contract': 'bg-primary',
                                    'temporary': 'bg-primary',
                                    'part-time': 'bg-primary',
                                    'suspended': 'bg-primary'
                                };

                                // First check for exact matches
                                let badgeClass = statusMappings[statusLower];

                                // If no exact match, check for partial keyword matches
                                if (!badgeClass) {
                                    if (statusLower.indexOf('active') !== -1 || statusLower.indexOf('employed') !== -1 ||
                                        statusLower.indexOf('current') !== -1 || statusLower.indexOf('working') !== -1) {
                                        badgeClass = 'bg-success';
                                    } else if (statusLower.indexOf('inactive') !== -1 || statusLower.indexOf('terminated') !== -1 ||
                                               statusLower.indexOf('resigned') !== -1 || statusLower.indexOf('retired') !== -1 ||
                                               statusLower.indexOf('dismissed') !== -1 || statusLower.indexOf('fired') !== -1 ||
                                               statusLower.indexOf('left') !== -1) {
                                        badgeClass = 'bg-danger';
                                    } else if (statusLower.indexOf('probation') !== -1 || statusLower.indexOf('training') !== -1 ||
                                               statusLower.indexOf('trial') !== -1 || statusLower.indexOf('intern') !== -1 ||
                                               statusLower.indexOf('apprentice') !== -1 || statusLower.indexOf('onboarding') !== -1) {
                                        badgeClass = 'bg-warning text-dark';
                                    } else if (statusLower.indexOf('leave') !== -1 || statusLower.indexOf('vacation') !== -1 ||
                                               statusLower.indexOf('holiday') !== -1 || statusLower.indexOf('absent') !== -1 ||
                                               statusLower.indexOf('maternity') !== -1 || statusLower.indexOf('paternity') !== -1) {
                                        badgeClass = 'bg-info text-dark';
                                    } else if (statusLower.indexOf('contract') !== -1 || statusLower.indexOf('temporary') !== -1 ||
                                               statusLower.indexOf('part-time') !== -1 || statusLower.indexOf('suspended') !== -1) {
                                        badgeClass = 'bg-primary';
                                    }
                                }

                                if (!badgeClass) badgeClass = 'bg-secondary';

                                return '<span class="badge ' + badgeClass + '">' + statusText + '</span>';
                            }
                            return data;
                        }
                    },
                    // { data: 'department' },
                    { data: 'action', orderable: false }
                ],
                // Add error handling and styling
                "drawCallback": function(settings) {
                    console.log("DataTable draw complete");
                    
                    // Update user count in header
                    var info = this.api().page.info();
                    $('#user-count').text(info.recordsDisplay);
                    
                    // Reinitialize tooltips after table redraw
                    $('[data-toggle="tooltip"]').tooltip();
                },
                "initComplete": function(settings, json) {
                    console.log("DataTable initialization complete");
                    
                    // Set initial user count
                    var info = this.api().page.info();
                    $('#user-count').text(info.recordsDisplay);
                }
            });
    
            $('.btn_search').on('click', function() {
                table.draw();
            });
        });
    </script>
    <script>
        // Initialize Select2 on modals when they are shown
        $(document).ready(function() {
            // Initialize Select2 for Add User Modal
            $('#add_user').on('shown.bs.modal', function () {
                console.log('Add User modal shown - initializing Select2');
                
                // Destroy any existing Select2 instances first
                $(this).find('.select.select2-hidden-accessible').each(function() {
                    $(this).select2('destroy');
                });
                
                // Initialize Select2 with proper settings
                if (typeof $.fn.select2 === 'function') {
                    $(this).find('.select').each(function() {
                        console.log('Initializing Select2 for:', $(this).attr('name'));
                        $(this).select2({
                            dropdownParent: $('#add_user'),
                            width: '100%',
                            minimumResultsForSearch: -1,
                            placeholder: $(this).find('option:first').text()
                        });
                    });
                }
            });

            // Initialize Select2 for Edit User Modal
            $('#edit_user').on('shown.bs.modal', function () {
                console.log('Edit User modal shown - initializing Select2');
                
                // Destroy any existing Select2 instances first
                $(this).find('.select.select2-hidden-accessible').each(function() {
                    $(this).select2('destroy');
                });
                
                // Initialize Select2 with proper settings
                if (typeof $.fn.select2 === 'function') {
                    $(this).find('.select').each(function() {
                        console.log('Initializing Select2 for:', $(this).attr('name'));
                        $(this).select2({
                            dropdownParent: $('#edit_user'),
                            width: '100%',
                            minimumResultsForSearch: -1,
                            placeholder: $(this).find('option:first').text()
                        });
                    });
                }
            });

            // Clean up Select2 when modals are hidden
            $('#add_user, #edit_user').on('hidden.bs.modal', function () {
                console.log('Modal hidden - cleaning up Select2');
                $(this).find('.select.select2-hidden-accessible').each(function() {
                    $(this).select2('destroy');
                });
            });
        });

        $(document).on('click', '.userUpdate', function() {
            const _this = $(this).closest('tr');
            const userId = _this.find('.user_id').text();
            const phoneNumber = _this.find('.phone_number').text().trim();
            const avatarData = _this.find('.avatar').data('avatar');
            
            console.log('User ID:', userId);
            console.log('Phone from table cell:', phoneNumber);
            console.log('Avatar data:', avatarData);
            
            // Set basic form fields from table data first (for immediate display)
            $('#e_id').val(userId);
            $('#e_name').val(_this.find('.name').text().trim());
            $('#e_email').val(_this.find('.email').text());
            $('#e_role_name').val(_this.find('.role_name').text()).change();
            $('#e_phone').val(phoneNumber);
            $('#e_department').val(_this.find('.department').text()).change();
            // Position was renamed to Designation in the UI but still corresponds to position field in DB
            $('#e_designation').val(_this.find('.position').text()).change();
            
            // Fix: Status is inside a badge, not status_s class. fallback to badge text.
            let statusText = _this.find('.badge').text().trim();
            if (!statusText) statusText = _this.find('.status_s').text().trim();
            $('#e_status').val(statusText).change();
            
            $('#e_hidden_image').val(avatarData);
            
            // Reset modal to default state
            $('#employee_id_header').hide();
            $('#employee_id_display_header').text('');
            $('#employee_warning').hide();
            $('#photo_upload_section').hide();
            
            // Reset field readonly states
            $('#e_name').prop('readonly', false).removeClass('readonly-field');
            $('#e_email').prop('readonly', false).removeClass('readonly-field');
            $('#e_phone').prop('readonly', false).removeClass('readonly-field');
            $('#e_department').prop('disabled', false).removeClass('readonly-field');
            $('#e_designation').prop('disabled', false).removeClass('readonly-field');
            $('#e_status').prop('disabled', false).removeClass('readonly-field');
            $('#e_role_name').prop('disabled', false).removeClass('readonly-field');
            
            // Fetch additional user details including employee data if available
            $.ajax({
                url: "{{ route('get-user-details') }}",
                type: "GET",
                data: { user_id: userId },
                success: function(response) {
                    console.log('Response from getUserDetails:', response);
                    
                    // Update phone from the correct source
                    if (response.phone !== undefined) {
                        $('#e_phone').val(response.phone);
                        console.log('Updated phone from response:', response.phone);
                    } else {
                        // If phone is undefined in response, try using the value from the table
                        const phoneFromTable = _this.find('.phone_number').attr('data-raw-phone') || 
                                              _this.find('.phone_number').text().trim();
                        if (phoneFromTable) {
                            $('#e_phone').val(phoneFromTable);
                            console.log('Using phone from table:', phoneFromTable);
                        }
                    }
                    
                    // Debug info about this user
                    console.log('Is employee:', response.is_employee);
                    if (response.is_employee) {
                        console.log('Employee ID:', response.employee_id);
                    }
                    
                    // Update designation/position
                    if (response.designation !== undefined) {
                        $('#e_designation').val(response.designation).change();
                        console.log('Updated designation from response:', response.designation);
                    }
                    
                    // Update department
                    if (response.department !== undefined) {
                        $('#e_department').val(response.department).change();
                        console.log('Updated department from response:', response.department);
                    }

                    // Update status from server response (most reliable)
                    if (response.status !== undefined) {
                        $('#e_status').val(response.status).change();
                        console.log('Updated status from response:', response.status);
                    }
                    
                    // Update role from server response
                    if (response.role_name !== undefined) {
                        $('#e_role_name').val(response.role_name).change();
                    }
                    
                    // If user is an employee-converted user, show warning and restrictions
                    if (response.is_employee_converted) {
                        // Show employee warning message
                        $('#employee_warning_message').text(response.employee_message || 'This user was created from an employee. Most fields can only be updated in the Employee module.');
                        $('#employee_warning').show();
                        
                        // Show employee ID in header
                        $('#employee_id_display_header').text(response.employee_id || 'N/A');
                        $('#employee_id_header').show();
                        
                        // Make employee-controlled fields readonly
                        const readonlyFields = response.readonly_fields || ['name', 'email', 'phone', 'department', 'position', 'status', 'role_name'];
                        
                        readonlyFields.forEach(function(field) {
                            switch(field) {
                                case 'name':
                                    $('#e_name').prop('readonly', true).addClass('readonly-field');
                                    break;
                                case 'email':
                                    $('#e_email').prop('readonly', true).addClass('readonly-field');
                                    break;
                                case 'phone':
                                    $('#e_phone').prop('readonly', true).addClass('readonly-field');
                                    break;
                                case 'department':
                                    $('#e_department').prop('disabled', true).addClass('readonly-field');
                                    break;
                                case 'position':
                                    $('#e_designation').prop('disabled', true).addClass('readonly-field');
                                    break;
                                case 'status':
                                    // For employee users, make the select readonly but ensure value is still submitted
                                    $('#e_status').addClass('readonly-field');
                                    $('#e_status').find('option:not(:selected)').prop('disabled', true);
                                    // Also set the backup hidden field
                                    $('#e_status_backup').val($('#e_status').val());
                                    break;
                                case 'role_name':
                                    $('#e_role_name').prop('disabled', true).addClass('readonly-field');
                                    break;
                            }
                        });
                        
                        // Hide photo upload section for employee users
                        $('#photo_upload_section').hide();
                        
                        console.log('Employee user detected - restrictions applied');
                    } else {
                        // Hide employee warning for regular users
                        $('#employee_warning').hide();
                        // Show photo upload section for manually created users
                        $('#photo_upload_section').show();
                        
                        // Set up current photo preview
                        if (avatarData && avatarData !== 'photo_defaults.jpg' && avatarData !== '') {
                            let imageUrl;
                            if (avatarData.includes('assets/')) {
                                imageUrl = "{{ url('/') }}/" + avatarData;
                            } else {
                                imageUrl = "{{ url('/assets/employee_profile_image/') }}/" + avatarData;
                            }
                            $('#current_photo_img').attr('src', imageUrl).show();
                            $('#no_photo_text').hide();
                        } else {
                            $('#current_photo_img').hide();
                            $('#no_photo_text').show();
                        }
                        
                        console.log('Regular user - all fields editable, photo upload shown');
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Error fetching user details:", error);
                    console.error("Response:", xhr.responseText);
                    
                    // For regular users (fallback if API fails), show photo upload
                    $('#photo_upload_section').show();
                    if (avatarData && avatarData !== 'photo_defaults.jpg' && avatarData !== '') {
                        let imageUrl;
                        if (avatarData.includes('assets/')) {
                            imageUrl = "{{ url('/') }}/" + avatarData;
                        } else {
                            imageUrl = "{{ url('/assets/employee_profile_image/') }}/" + avatarData;
                        }
                        $('#current_photo_img').attr('src', imageUrl).show();
                        $('#no_photo_text').hide();
                    } else {
                        $('#current_photo_img').hide();
                        $('#no_photo_text').show();
                    }
                }
            });
        });
    
        $(document).on('click', '.userDelete', function() {
            const _this = $(this).closest('tr');
            $('.e_id').val(_this.find('.user_id').text());
            $('#e_avatar').val(_this.find('.avatar').data('avatar'));
        });

        // Clear password fields when edit modal is hidden
        $('#edit_user').on('hidden.bs.modal', function () {
            $('#e_password').val('');
            $('#e_password_confirmation').val('');
            $('#employee_id_header').hide();
            $('#photo_upload_section').hide();
            $('#e_image').val('');
            $('#e_hidden_image').val('');
            $('#current_photo_img').hide();
            $('#no_photo_text').show();
        });
        
        // Add file input change handler for photo preview
        $('#e_image').on('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // Validate file type
                const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                if (!validTypes.includes(file.type)) {
                    alert('Please select a valid image file (JPEG, PNG, or GIF)');
                    $(this).val('');
                    return;
                }
                
                // Validate file size (2MB)
                if (file.size > 2048 * 1024) {
                    alert('File size must be less than 2MB');
                    $(this).val('');
                    return;
                }
                
                // Show preview
                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#current_photo_img').attr('src', e.target.result).show();
                    $('#no_photo_text').hide();
                };
                reader.readAsDataURL(file);
                
                console.log('File selected for upload:', file.name, 'Size:', file.size, 'Type:', file.type);
            } else {
                // Reset to original state if no file selected
                const avatarData = $('#e_hidden_image').val();
                if (avatarData && avatarData !== 'photo_defaults.jpg' && avatarData !== '') {
                    let imageUrl;
                    if (avatarData.includes('assets/')) {
                        imageUrl = "{{ url('/') }}/" + avatarData;
                    } else {
                        imageUrl = "{{ url('/assets/employee_profile_image/') }}/" + avatarData;
                    }
                    $('#current_photo_img').attr('src', imageUrl).show();
                    $('#no_photo_text').hide();
                } else {
                    $('#current_photo_img').hide();
                    $('#no_photo_text').show();
                }
            }
        });
    </script>
    
    <style>
        .readonly-field {
            background-color: #f8f9fa !important;
            border-color: #e9ecef !important;
            opacity: 0.8;
            cursor: not-allowed;
        }
        
        .readonly-field:focus {
            background-color: #f8f9fa !important;
            border-color: #e9ecef !important;
            box-shadow: none !important;
        }
        
        #employee_id_header {
            font-size: 0.8em;
            vertical-align: middle;
        }
        
        #photo_upload_section {
            border-top: 1px solid #e9ecef;
            padding-top: 15px;
            margin-top: 10px;
        }
        
        #current_photo_preview {
            text-align: center;
        }
        
        #current_photo_img {
            display: none;
        }
        
        #no_photo_text {
            color: #6c757d;
            font-style: italic;
            font-size: 14px;
        }
        
        /* Styles for readonly fields in employee user edit */
        .readonly-field {
            background-color: #f8f9fa !important;
            color: #6c757d !important;
            cursor: not-allowed !important;
            opacity: 0.8;
        }
        
        .readonly-field:focus {
            background-color: #f8f9fa !important;
            border-color: #ced4da !important;
            box-shadow: none !important;
        }
        
        /* Employee warning alert styling */
        .alert-info {
            border-left: 4px solid #17a2b8;
        }
        
        /* Employee badge in modal header */
        .bg-info text-dark {
            background-color: #17a2b8;
        }
    </style>

@endsection
@endsection
