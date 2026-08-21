@extends('layouts.master')
@section('title', 'POSH ICC Board Members')

@section('content')
@include('compliance.posh._deprecated-banner')
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
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
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
        color: white;
    }

    .btn-secondary {
        background: #6c757d;
        border: none;
        color: white;
    }

    .btn-secondary:hover {
        background: #5a6268;
        transform: translateY(-2px);
        color: white;
    }
    
    /* Modern Table Card */
    .table-card {
        background: white;
        border-radius: 1rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
        overflow: hidden;
        border: 1px solid #e5e7eb;
        margin-bottom: 2rem;
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
    
    /* Employee Avatar */
    .employee-info {
        display: flex;
        align-items: center;
    }
    
    .employee-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 600;
        margin-right: 0.75rem;
        font-size: 0.875rem;
    }
    
    .employee-details .employee-name {
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 2px;
    }
    
    .employee-details .employee-id {
        font-size: 0.75rem;
        color: #6b7280;
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
    
    .role-presiding {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
    }
    
    .role-secretary {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: white;
    }
    
    .role-internal {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
    }
    
    .role-external {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        color: white;
    }

    /* Action Buttons */
    .action-buttons {
        display: flex;
        gap: 0.5rem;
    }
    
    .btn-action {
        width: 32px;
        height: 32px;
        border-radius: 0.375rem;
        display: flex;
        align-items: center;
        justify-content: center;
        border: none;
        transition: all 0.2s ease;
        font-size: 0.875rem;
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

    /* Select2 Premium Styling */
    .select2-container--default .select2-selection--single {
        border: 2px solid #e5e7eb !important;
        border-radius: 0.5rem !important;
        height: 42px !important;
        display: flex !important;
        align-items: center !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 40px !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #374151 !important;
        font-size: 0.875rem !important;
    }
    .select2-dropdown {
        border: 2px solid #e5e7eb !important;
        border-radius: 0.5rem !important;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08) !important;
    }
</style>

<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Modern Page Header -->
        <div class="page-header-card">
            <div class="page-header-gradient">
                <div class="page-header-pattern"></div>
                <div class="page-header-circle-1"></div>
                <div class="page-header-circle-2"></div>
                <div class="d-flex justify-content-between align-items-center position-relative">
                    <div class="d-flex align-items-center">
                        <div class="page-header-icon-box">
                            <i class="fas fa-users-cog fa-lg" style="color: rgba(255,255,255,0.9);"></i>
                        </div>
                        <div class="ms-3">
                            <h1 class="page-header-title" style="margin-left: 1rem;">ICC Committee</h1>
                            <p class="page-header-subtitle" style="margin-left: 1rem;">Configure committee board members, roles, and support contacts for POSH compliance</p>
                        </div>
                    </div>
                    <div class="ml-auto">
                        <button class="btn btn-light font-weight-bold" data-toggle="modal" data-target="#addMemberModal" style="border-radius: 0.5rem; color: #764ba2; background: white;">
                            <i class="fas fa-plus me-2"></i> Add Committee Member
                        </button>
                    </div>
                </div>
            </div>
            <div class="p-3 d-flex justify-content-between align-items-center flex-wrap gap-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">POSH ICC Board</li>
                    </ol>
                </nav>
            </div>
        </div>

        <!-- Committee Members Table Card -->
        <div class="table-card">
            <div class="table-responsive">
                <table class="table table-hover desktop-table" id="iccBoardTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Member</th>
                            <th>Role on Committee</th>
                            <th>Department & Designation</th>
                            <th>Contact Information</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($members as $key => $member)
                            <tr>
                                <td class="font-weight-600 text-muted">{{ $key + 1 }}</td>
                                <td>
                                    <div class="employee-info">
                                        <div class="employee-avatar">
                                            {{ strtoupper(substr($member->employee->name ?? 'N', 0, 1)) }}
                                        </div>
                                        <div class="employee-details">
                                            <div class="employee-name">{{ $member->employee->name ?? 'Unknown Employee' }}</div>
                                            <div class="employee-id">ID: {{ $member->employee->employee_id ?? 'N/A' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @php
                                        $roleClass = 'role-internal';
                                        if (str_contains(strtolower($member->icc_role), 'presiding')) {
                                            $roleClass = 'role-presiding';
                                        } elseif (str_contains(strtolower($member->icc_role), 'secretary')) {
                                            $roleClass = 'role-secretary';
                                        } elseif (str_contains(strtolower($member->icc_role), 'external')) {
                                            $roleClass = 'role-external';
                                        }
                                    @endphp
                                    <span class="badge {{ $roleClass }}">
                                        {{ $member->icc_role }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-warning text-dark mb-1">
                                        {{ $member->employee->designationObj->position ?? ($member->employee->designation ?? 'N/A') }}
                                    </span>
                                    <br>
                                    <span class="badge bg-info text-white">
                                        {{ $member->employee->departmentObj->department ?? ($member->employee->department ?? 'N/A') }}
                                    </span>
                                </td>
                                <td>
                                    <div style="font-size: 0.875rem;">
                                        <div><i class="fas fa-envelope mr-1 text-muted"></i> {{ $member->email ?? 'N/A' }}</div>
                                        <div><i class="fas fa-phone mr-1 text-muted"></i> {{ $member->contact_number ?? 'N/A' }}</div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="action-buttons justify-content-center">
                                        <button class="btn-action btn-action-edit" data-toggle="modal" data-target="#editMemberModal{{ $member->id }}" title="Edit Member">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form action="{{ route('compliance.posh.icc-board.delete', $member->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to remove this member from the committee?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-action btn-action-delete" title="Delete Member">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>

                            <!-- Edit Member Modal -->
                            <div class="modal fade" id="editMemberModal{{ $member->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered" role="document">
                                    <div class="modal-content" style="border-radius: 1rem; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.15);">
                                        <div class="modal-header text-white" style="border-top-left-radius: 1rem; border-top-right-radius: 1rem; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                            <h5 class="modal-title font-weight-bold">Edit Committee Member</h5>
                                            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <form action="{{ route('compliance.posh.icc-board.update', $member->id) }}" method="POST">
                                            @csrf
                                            <div class="modal-body p-4">
                                                <div class="form-group mb-3">
                                                    <label class="font-weight-bold mb-2">Employee</label>
                                                    <input type="text" class="form-control" value="{{ $member->employee->name }} (ID: {{ $member->employee->employee_id }})" disabled style="border-radius: 0.5rem; background-color: #f8fafc; border: 2px solid #e5e7eb;">
                                                </div>

                                                <div class="form-group mb-3">
                                                    <label for="icc_role_{{ $member->id }}" class="font-weight-bold mb-2">Role on Committee</label>
                                                    <select class="form-control form-select" id="icc_role_{{ $member->id }}" name="icc_role" required style="border-radius: 0.5rem; border: 2px solid #e5e7eb;">
                                                        <option value="Presiding Officer" {{ $member->icc_role === 'Presiding Officer' ? 'selected' : '' }}>Presiding Officer (Must be a woman at senior level)</option>
                                                        <option value="Member Secretary" {{ $member->icc_role === 'Member Secretary' ? 'selected' : '' }}>Member Secretary</option>
                                                        <option value="Internal Member" {{ $member->icc_role === 'Internal Member' ? 'selected' : '' }}>Internal Member</option>
                                                        <option value="External Member" {{ $member->icc_role === 'External Member' ? 'selected' : '' }}>External Member (NGO/Independent third-party)</option>
                                                    </select>
                                                </div>

                                                <div class="form-group mb-3">
                                                    <label for="contact_number_{{ $member->id }}" class="font-weight-bold mb-2">Contact Number</label>
                                                    <input type="text" class="form-control" id="contact_number_{{ $member->id }}" name="contact_number" value="{{ $member->contact_number }}" placeholder="Enter direct phone number" style="border-radius: 0.5rem; border: 2px solid #e5e7eb;">
                                                </div>

                                                <div class="form-group mb-3">
                                                    <label for="email_{{ $member->id }}" class="font-weight-bold mb-2">Contact Email</label>
                                                    <input type="email" class="form-control" id="email_{{ $member->id }}" name="email" value="{{ $member->email }}" placeholder="Enter direct email address" style="border-radius: 0.5rem; border: 2px solid #e5e7eb;">
                                                </div>
                                            </div>
                                            <div class="modal-footer" style="border-top: 1px solid #f1f5f9;">
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal" style="border-radius: 0.5rem;">Close</button>
                                                <button type="submit" class="btn btn-primary" style="border-radius: 0.5rem; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;">Save Changes</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <div class="mb-3"><i class="fas fa-users-slash fa-3x" style="color: #cbd5e1;"></i></div>
                                    <div class="font-weight-bold">No Committee Members Defined</div>
                                    <div>Add members to set up the Internal Complaints Committee (ICC).</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Member Modal -->
<div class="modal fade" id="addMemberModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 1rem; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.15);">
            <div class="modal-header text-white" style="border-top-left-radius: 1rem; border-top-right-radius: 1rem; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <h5 class="modal-title font-weight-bold">Add Committee Member</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('compliance.posh.icc-board.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="form-group mb-3">
                        <label for="employee_id" class="font-weight-bold mb-2">Select Employee</label>
                        <select class="form-control select2 form-select" id="employee_id" name="employee_id" required style="width: 100%; border-radius: 0.5rem; border: 2px solid #e5e7eb;">
                            <option value="">-- Choose Employee --</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->name }} (ID: {{ $emp->employee_id }} - {{ $emp->designationObj->position ?? $emp->designation }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label for="icc_role" class="font-weight-bold mb-2">Role on Committee</label>
                        <select class="form-control form-select" id="icc_role" name="icc_role" required style="border-radius: 0.5rem; border: 2px solid #e5e7eb;">
                            <option value="Internal Member">Internal Member</option>
                            <option value="Presiding Officer">Presiding Officer (Must be a woman at senior level)</option>
                            <option value="Member Secretary">Member Secretary</option>
                            <option value="External Member">External Member (NGO/Independent third-party)</option>
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label for="contact_number" class="font-weight-bold mb-2">Contact Number (Optional)</label>
                        <input type="text" class="form-control" id="contact_number" name="contact_number" placeholder="Defaults to employee contact details" style="border-radius: 0.5rem; border: 2px solid #e5e7eb;">
                    </div>

                    <div class="form-group mb-3">
                        <label for="email" class="font-weight-bold mb-2">Contact Email (Optional)</label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="Defaults to employee email address" style="border-radius: 0.5rem; border: 2px solid #e5e7eb;">
                    </div>
                </div>
                <div class="modal-footer" style="border-top: 1px solid #f1f5f9;">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal" style="border-radius: 0.5rem;">Close</button>
                    <button type="submit" class="btn btn-primary" style="border-radius: 0.5rem; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;">Add Member</button>
                </div>
            </form>
        </div>
    </div>
</div>

@section('script')
<script>
    $(document).ready(function() {
        // Initialize select2 when modal is shown to fix width and z-index issues
        $('#addMemberModal').on('shown.bs.modal', function () {
            if ($.fn.select2) {
                $('#employee_id').select2({
                    dropdownParent: $('#addMemberModal'),
                    width: '100%'
                });
            }
        });
    });
</script>
@endsection
