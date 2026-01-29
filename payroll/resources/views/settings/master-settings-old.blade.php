@extends('layouts.master')
@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-sm-12">
                    <h3 class="page-title">Master Settings</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Master Settings</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Permission Management Card -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Permission Management</h4>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">Manage system permissions and access controls</p>
                        <div class="text-center">
                            <a href="{{ route('permissions.manage') }}" class="btn btn-primary">
                                <i class="fas fa-shield-alt"></i> Manage Permissions
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Role Management Card -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Role Management</h4>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">Manage user roles and permissions</p>
                        <div class="text-center">
                            <a href="{{ route('roles/permissions/page') }}" class="btn btn-primary">
                                <i class="fas fa-users-cog"></i> Manage Roles
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Company Settings Card -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Company Settings</h4>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">Configure company information and settings</p>
                        <div class="text-center">
                            <a href="{{ route('company/settings/page') }}" class="btn btn-primary">
                                <i class="fas fa-building"></i> Company Settings
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Department Management Card -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Department Management</h4>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">Manage organizational departments</p>
                        <div class="text-center">
                            <a href="{{ route('form/department/manage') }}" class="btn btn-primary">
                                <i class="fas fa-sitemap"></i> Manage Departments
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- More settings cards can be added here -->
        </div>
    </div>
</div>
@endsection