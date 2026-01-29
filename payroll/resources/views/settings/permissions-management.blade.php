@extends('layouts.master')
@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Permission Management</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('settings.index') }}">Settings</a></li>
                        <li class="breadcrumb-item active">Permission Management</li>
                    </ul>
                </div>
                <div class="col-auto float-end ms-auto">
                    <button type="button" class="btn add-btn" data-toggle="modal" data-target="#add_permission">
                        <i class="fa fa-plus"></i> Add Permission
                    </button>
                </div>
            </div>
        </div>

        <!-- Permission Management Card -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">System Permissions</h4>
                        <p class="text-muted">Manage system permissions dynamically. These permissions can be assigned to users.</p>
                    </div>
                    <div class="card-body">
                        <!-- Module Filter -->
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <select class="form-control form-select" id="moduleFilter">
                                    <option value="">All Modules</option>
                                    @foreach($modules as $module)
                                        <option value="{{ $module }}">{{ ucfirst($module) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-8 text-end">
                                <span class="badge bg-info text-dark">Total Permissions: {{ $permissions->count() }}</span>
                            </div>
                        </div>

                        <!-- Permissions Table -->
                        <div class="table-responsive">
                            <table class="table table-striped custom-table" id="permissionsTable">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Module</th>
                                        <th>Permission Name</th>
                                        <th>Action</th>
                                        <th>Display Name</th>
                                        <th>Description</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($permissions as $permission)
                                    <tr data-module="{{ $permission->module }}">
                                        <td>{{ $permission->id }}</td>
                                        <td>
                                            <span class="badge bg-primary">{{ ucfirst($permission->module) }}</span>
                                        </td>
                                        <td><code>{{ $permission->name }}</code></td>
                                        <td>{{ $permission->action }}</td>
                                        <td>{{ $permission->display_name }}</td>
                                        <td>{{ $permission->description ?? '-' }}</td>
                                        <td>
                                            @if($permission->is_active)
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-danger">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="dropdown">
                                                <a href="#" class="action-icon dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                                                    <i class="material-icons">more_vert</i>
                                                </a>
                                                <div class="dropdown-menu dropdown-menu-right">
                                                    <a class="dropdown-item" href="#" onclick="editPermission({{ $permission->id }})">
                                                        <i class="fa fa-edit m-r-5"></i> Edit
                                                    </a>
                                                    <a class="dropdown-item" href="#" onclick="deletePermission({{ $permission->id }}, '{{ $permission->name }}')">
                                                        <i class="fa fa-trash-o m-r-5"></i> Delete
                                                    </a>
                                                </div>
                                            </div>
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
    </div>
</div>

<!-- Add Permission Modal -->
<div id="add_permission" class="modal custom-modal fade" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Permission</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="addPermissionForm">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Module <span class="text-danger">*</span></label>
                                <input list="modulesList" class="form-control" name="module" required>
                                <datalist id="modulesList">
                                    @foreach($modules as $module)
                                        <option value="{{ $module }}">
                                    @endforeach
                                </datalist>
                                <small class="form-text text-muted">e.g., employees, payroll, reports</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Action <span class="text-danger">*</span></label>
                                <input class="form-control" type="text" name="action" required>
                                <small class="form-text text-muted">e.g., view, create, edit, delete</small>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Permission Name <span class="text-danger">*</span></label>
                                <input class="form-control" type="text" name="name" required>
                                <small class="form-text text-muted">Format: module.action (e.g., employees.view)</small>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Display Name <span class="text-danger">*</span></label>
                                <input class="form-control" type="text" name="display_name" required>
                                <small class="form-text text-muted">User-friendly name (e.g., View Employees)</small>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Description</label>
                                <textarea class="form-control" name="description" rows="3"></textarea>
                                <small class="form-text text-muted">Optional description of what this permission allows</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Permission</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Permission Modal -->
<div id="edit_permission" class="modal custom-modal fade" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Permission</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editPermissionForm">
                @csrf
                <input type="hidden" name="id" id="edit_permission_id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Module <span class="text-danger">*</span></label>
                                <input list="modulesList2" class="form-control" name="module" id="edit_module" required>
                                <datalist id="modulesList2">
                                    @foreach($modules as $module)
                                        <option value="{{ $module }}">
                                    @endforeach
                                </datalist>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Action <span class="text-danger">*</span></label>
                                <input class="form-control" type="text" name="action" id="edit_action" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Permission Name <span class="text-danger">*</span></label>
                                <input class="form-control" type="text" name="name" id="edit_name" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Display Name <span class="text-danger">*</span></label>
                                <input class="form-control" type="text" name="display_name" id="edit_display_name" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Description</label>
                                <textarea class="form-control" name="description" id="edit_description" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Permission</button>
                </div>
            </form>
        </div>
    </div>
</div>

@section('script')
<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#permissionsTable').DataTable({
        "pageLength": 25,
        "order": [[1, 'asc'], [2, 'asc']],
        "columnDefs": [
            { "orderable": false, "targets": 7 }
        ]
    });

    // Module filter
    $('#moduleFilter').on('change', function() {
        var module = $(this).val();
        if (module === '') {
            $('tbody tr').show();
        } else {
            $('tbody tr').hide();
            $('tbody tr[data-module="' + module + '"]').show();
        }
    });

    // Auto-generate permission name
    $('input[name="module"], input[name="action"]').on('input', function() {
        var module = $('input[name="module"]').val();
        var action = $('input[name="action"]').val();
        if (module && action) {
            $('input[name="name"]').val(module + '.' + action);
        }
    });

    // Add permission form
    $('#addPermissionForm').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: '{{ route("permissions.save") }}',
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    $('#add_permission').modal('hide');
                    location.reload();
                } else {
                    toastr.error(response.message);
                }
            },
            error: function(xhr) {
                var errors = xhr.responseJSON.errors;
                var errorMessage = 'Validation failed:\n';
                for (var field in errors) {
                    errorMessage += '- ' + errors[field][0] + '\n';
                }
                toastr.error(errorMessage);
            }
        });
    });

    // Edit permission form
    $('#editPermissionForm').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: '{{ route("permissions.update") }}',
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    $('#edit_permission').modal('hide');
                    location.reload();
                } else {
                    toastr.error(response.message);
                }
            },
            error: function(xhr) {
                var errors = xhr.responseJSON.errors;
                var errorMessage = 'Validation failed:\n';
                for (var field in errors) {
                    errorMessage += '- ' + errors[field][0] + '\n';
                }
                toastr.error(errorMessage);
            }
        });
    });
});

function editPermission(id) {
    $.ajax({
        url: '{{ url("permissions/get") }}/' + id,
        method: 'GET',
        success: function(response) {
            if (response.success) {
                var permission = response.permission;
                $('#edit_permission_id').val(permission.id);
                $('#edit_module').val(permission.module);
                $('#edit_action').val(permission.action);
                $('#edit_name').val(permission.name);
                $('#edit_display_name').val(permission.display_name);
                $('#edit_description').val(permission.description);
                $('#edit_permission').modal('show');
            } else {
                toastr.error(response.message);
            }
        }
    });
}

function deletePermission(id, name) {
    Swal.fire({
        title: 'Are you sure?',
        text: 'Delete permission "' + name + '"? This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '{{ route("permissions.delete") }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    id: id
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        location.reload();
                    } else {
                        toastr.error(response.message);
                    }
                }
            });
        }
    });
}
</script>
@endsection
@endsection