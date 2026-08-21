@extends('layouts.master')
@section('title', 'Manage Document Types')
@section('content')
<style>
    /* Page Header Card */
    .page-header-card { background: white; border-radius: 1rem; box-shadow: 0 10px 25px rgba(0,0,0,0.1); overflow: hidden; margin-bottom: 2rem; }
    .page-header-gradient { background: linear-gradient(135deg,#667eea 0%,#764ba2 100%); padding: 2.5rem 2rem; position: relative; }
    .page-header-pattern { position: absolute; inset: 0; background: rgba(0,0,0,0.05); }
    .page-header-circle-1 { position: absolute; top: -1rem; right: -1rem; width:6rem; height:6rem; background: rgba(255,255,255,0.1); border-radius:50%; }
    .page-header-circle-2 { position:absolute; bottom:-1rem; left:-1rem; width:8rem; height:8rem; background: rgba(255,255,255,0.1); border-radius:50%; }
    .page-header-icon-box { width:4rem; height:4rem; background: rgba(255,255,255,0.2); border:1px solid rgba(255,255,255,0.3); border-radius:1rem; display:flex; align-items:center; justify-content:center; }
    .page-header-title { font-size:1.875rem; font-weight:700; color:white; margin-bottom:0.5rem; }
    .page-header-subtitle { font-size:1rem; color: rgba(255,255,255,0.9); margin:0; }

    /* Table Card */
    .table-card { background:white; border-radius:1rem; box-shadow:0 4px 6px rgba(0,0,0,0.07); border:1px solid #e5e7eb; overflow:hidden; margin-bottom:2rem; }
    .table-card-header { background: linear-gradient(135deg,#667eea 0%,#764ba2 100%); padding:1.25rem 1.5rem; border-bottom:none; }
    .table-card-header h4{ color:white; font-weight:600; margin:0; font-size:1.125rem; }
    .table-card-body { padding:0 0.5rem; }

    .dataTables_wrapper { overflow: visible !important; }
    .dataTables_wrapper .dataTables_length, .dataTables_wrapper .dataTables_filter, .dataTables_wrapper .dataTables_info, .dataTables_wrapper .dataTables_paginate { padding:1rem 1.25rem; color:#6b7280; }
    .dataTables_wrapper .dataTables_length select { border:1px solid #d1d5db; border-radius:0.5rem; padding:0.5rem 1.75rem 0.5rem 0.75rem; font-size:0.875rem; background-position: calc(100% - 0.6rem) center; background-repeat:no-repeat; -webkit-appearance: menulist-button; appearance: menulist-button; overflow:visible; }
    @media (max-width:575px){ .dataTables_wrapper .dataTables_length select{ padding-right:1rem; } }

    .table thead th { background: linear-gradient(135deg,#667eea 0%,#764ba2 100%); color:white; font-weight:600; border:none; padding:1rem 0.75rem; font-size:0.875rem; text-transform:uppercase; letter-spacing:0.5px; }
    .table thead th:first-child, .table tbody td:first-child { width:64px; max-width:64px; text-align:center; padding-left:0.5rem; padding-right:0.5rem; white-space:nowrap; }
    .table tbody td { padding:0.75rem 0.5rem; border-bottom:1px solid #f3f4f6; vertical-align:middle; font-size:0.875rem; }

    .badge-modern{ padding:0.375rem 0.75rem; font-weight:500; border-radius:0.375rem; font-size:0.75rem; text-transform:uppercase; letter-spacing:0.5px; }
    .badge-modern.bg-success{ background: linear-gradient(135deg,#10b981 0%,#059669 100%); color:white; }
    .badge-modern.bg-danger{ background: linear-gradient(135deg,#ef4444 0%,#dc2626 100%); color:white; }

    .btn-action{ padding:0.5rem 1rem; border-radius:0.5rem; font-weight:500; font-size:0.875rem; border:none; cursor:pointer; transition:all 0.2s; text-decoration:none; display:inline-flex; align-items:center; justify-content:center; min-width:80px; }
    .btn-action-edit{ background: linear-gradient(135deg,#3b82f6 0%,#1d4ed8 100%); color:white; }
    .btn-action-delete{ background: linear-gradient(135deg,#ef4444 0%,#dc2626 100%); color:white; }

    /* Update modal header styles */
    .modal-content {
        border-radius: 1rem;
        border: none;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        overflow: hidden;
    }

    .modal-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-bottom: none;
        padding: 1.5rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .modal-header .modal-title {
        font-weight: bold;
        font-size: 1.25rem;
    }

    .modal-header .close {
        color: white;
        text-shadow: none;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 50%;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        margin: 0;
        border: none;
        transition: all 0.3s ease;
        opacity: 0.8;
    }

    .modal-header .close:hover {
        background: rgba(255, 255, 255, 0.3);
        opacity: 1;
        transform: rotate(90deg);
    }
    
    .modal-header .close span {
        display: block;
        line-height: 1;
        padding-bottom: 2px;
        font-size: 1.5rem;
        font-weight: 300;
        margin-left: 0;
    }
    
    .modal-body {
        padding: 2rem;
    }

    /* Global Button Styles */
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

    /* Fix Select2 height and width inside input-group to match other fields */
    .input-group > .select2-container--default {
        flex: 1 1 auto;
        width: 1% !important;
    }
    .input-group > .select2-container--default .select2-selection--multiple {
        min-height: 44px !important;
        border: 1px solid #d1d5db !important;
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
        border-radius: 0 0.5rem 0.5rem 0 !important;
        padding-top: 4px;
    }
    .input-group-text {
        background-color: #f9fafb;
        color: #6b7280;
        border: 1px solid #d1d5db;
        border-radius: 0.5rem 0 0 0.5rem;
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
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <div class="page-header-icon-box">
                            <i class="fas fa-file-alt fa-lg" style="color: rgba(255,255,255,0.9);"></i>
                        </div>
                        <div class="ms-3">
                            <h1 class="page-header-title">Document Types</h1>
                            <p class="page-header-subtitle">Manage document types used across the system</p>
                        </div>
                    </div>
                    <!-- Stats Section -->
                    <div class="d-flex align-items-center text-end d-none d-md-flex">
                        <div class="page-header-stats me-3 text-white">
                            <p class="page-header-stats-label mb-0" style="opacity: 0.9; font-size: 0.875rem;">Total Document Types</p>
                            <p class="page-header-stats-value mb-0 fw-bold" style="font-size: 1.75rem;">{{ $documentTypes->count() }}</p>
                        </div>
                        <div class="page-header-stats-icon p-2 rounded" style="background: rgba(255,255,255,0.2); width: auto; height: auto;">
                            <i class="fas fa-file-invoice text-white" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="p-3 d-flex justify-content-between align-items-center flex-wrap gap-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item">Masters</li>
                        <li class="breadcrumb-item active">Document Types</li>
                    </ol>
                </nav>
                <div class="d-flex gap-2">
                    <a href="#" class="btn btn-primary" data-toggle="modal" data-target="#add_document_type">
                        <i class="fas fa-plus me-2"></i> Add Document Type
                    </a>
                </div>
            </div>
        </div>
        <!-- /Modern Page Header -->

        <div class="table-card">
            <div class="table-card-header">
                <h4><i class="fas fa-list me-2"></i>Document Type List</h4>
            </div>
            <div class="table-card-body">
                <div class="table-responsive">
                    <table class="table table-striped custom-table datatable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Document Name</th>
                                <th>Location</th>
                                <th>Status</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($documentTypes as $documentType)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $documentType->document_name }}</td>
                                <td>{{ $documentType->location_name }}</td>
                                <td>
                                    <span class="badge badge-modern badge-{{ $documentType->status ? 'success' : 'danger' }}">{{ $documentType->status ? 'Active' : 'Inactive' }}</span>
                                </td>
                                <td class="text-end">
                                    <button class="btn-action btn-action-edit me-2 documentTypeEdit" data-id="{{ $documentType->id }}"><i class="fas fa-edit me-1"></i> Edit</button>
                                    <button class="btn-action btn-action-delete documentTypeDelete" data-id="{{ $documentType->id }}" data-toggle="modal" data-target="#delete_document_type"><i class="fas fa-trash me-1"></i> Delete</button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Add Document Type Modal -->
    <div id="add_document_type" class="modal custom-modal fade" role="dialog">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i> Add Document Type</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('form/document-type/save') }}" method="POST">
                        @csrf
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <div class="row">
                            <div class="col-md-12">
                                <h5 class="mb-3 text-uppercase bg-light p-2 rounded"><i class="fas fa-info-circle me-2 text-primary"></i>Document Type Details</h5>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label">Document Name <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-file-alt"></i></span>
                                        <input type="text" class="form-control @error('document_name') is-invalid @enderror" name="document_name" placeholder="Enter document name" value="{{ old('document_name') }}" required>
                                    </div>
                                    @error('document_name')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label">Location <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                                        <select class="form-control select2-multi @error('location_id') is-invalid @enderror" name="location_id[]" id="add_location_id" multiple="multiple" data-placeholder="Select Locations" required style="width: 100%;">
                                            <option value="0">All</option>
                                            @foreach($locations as $location)
                                                <option value="{{ $location->id }}" {{ in_array($location->id, (array)old('location_id')) ? 'selected' : '' }}>{{ $location->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @error('location_id')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label">Status <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-check-circle"></i></span>
                                        <select class="form-control form-select @error('status') is-invalid @enderror" name="status" required>
                                            <option value="1" {{ old('status') == '1' ? 'selected' : '' }}>Active</option>
                                            <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Inactive</option>
                                        </select>
                                    </div>
                                    @error('status')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="submit-section">
                            <button type="submit" class="btn btn-primary submit-btn">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- /Add Document Type Modal -->
    
    <!-- Edit Document Type Modal -->
    <div id="edit_document_type_modal" class="modal custom-modal fade" role="dialog">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-edit me-2"></i> Edit Document Type</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="editForm" method="POST" action="{{ route('form/document-type/update') }}">
                        @csrf
                        <input type="hidden" name="id" id="edit_id">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <div class="row">
                            <div class="col-md-12">
                                <h5 class="mb-3 text-uppercase bg-light p-2 rounded"><i class="fas fa-info-circle me-2 text-primary"></i>Document Type Details</h5>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label">Document Name <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-file-alt"></i></span>
                                        <input type="text" class="form-control @error('document_name') is-invalid @enderror" name="document_name" id="edit_document_name" placeholder="Enter document name" value="{{ old('document_name') }}" required>
                                    </div>
                                    @error('document_name')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label">Location <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                                        <select class="form-control select2-multi @error('location_id') is-invalid @enderror" name="location_id[]" id="edit_location_id" multiple="multiple" data-placeholder="Select Locations" required style="width: 100%;">
                                            <option value="0">All</option>
                                            @foreach($locations as $location)
                                                <option value="{{ $location->id }}">{{ $location->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @error('location_id')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label">Status <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-check-circle"></i></span>
                                        <select class="form-control form-select @error('status') is-invalid @enderror" name="status" id="edit_status" required>
                                            <option value="1">Active</option>
                                            <option value="0">Inactive</option>
                                        </select>
                                    </div>
                                    @error('status')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="submit-section">
                            <button type="submit" class="btn btn-primary submit-btn">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- /Edit Document Type Modal -->

    <!-- Delete Document Type Modal -->
    <div id="delete_document_type" class="modal custom-modal fade" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-trash-alt me-2"></i>Delete Document Type</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center">
                    <div class="mb-4">
                        <i class="fas fa-exclamation-triangle text-warning" style="font-size:3rem"></i>
                    </div>
                    <h4 class="mb-3">Are you sure?</h4>
                    <p class="text-muted mb-4">This action cannot be undone. This will permanently delete the document type.</p>
                    <div class="d-flex justify-content-center gap-3">
                        <form id="deleteForm" method="POST" action="{{ route('form/document-type/delete') }}" class="d-inline">
                            @csrf
                            <input type="hidden" name="id" id="delete_id">
                            <button type="submit" class="btn btn-action btn-action-delete me-3"><i class="fas fa-trash me-1"></i> Delete</button>
                        </form>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times me-1"></i> Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /Delete Document Type Modal -->

</div>
@section('script')
<script>
    const baseUrl = "{{ url('/') }}";
    $(document).ready(function () {
        $(document).on('click', '.documentTypeEdit', function (e) {
            e.preventDefault();
            var id = $(this).data('id');

            $.ajax({
                url: `${baseUrl}/form/document-type/get/${id}`,
                type: 'GET',
                success: function (response) {
                    $('#edit_id').val(response.id);
                    $('#edit_document_name').val(response.document_name);
                    
                    // Handle multiselect for location_id
                    if (response.location_id) {
                        let locationIds = response.location_id;
                        if (typeof locationIds === 'string') {
                            try {
                                locationIds = JSON.parse(locationIds);
                            } catch (e) {
                                locationIds = locationIds.split(',');
                            }
                        }
                        $('#edit_location_id').val(locationIds).trigger('change');
                    } else {
                        $('#edit_location_id').val([]).trigger('change');
                    }

                    const statusValue = response.status ? '1' : '0';
                    $('#edit_status').val(statusValue);

                    $('#edit_document_type_modal').modal('show');
                },
                error: function (xhr, status, error) {
                    console.error('Error fetching document type:', error);
                    alert('Could not fetch document type data.');
                }
            });
        });

        $(document).on('click', '.documentTypeDelete', function (e) {
            e.preventDefault();
            var id = $(this).data('id');
            $('#delete_id').val(id);
        });

        // Auto-show modal if there are validation errors
        @if ($errors->any())
            @if (old('id'))
                // Edit operation failed - show edit modal
                setTimeout(function() {
                    $('#edit_id').val('{{ old('id') }}');
                    $('#edit_document_name').val('{{ old('document_name') }}');
                    $('#edit_status').val('{{ old('status') }}');
                    $('#edit_location_id').val(@json(old('location_id'))).trigger('change');
                    $('#edit_document_type_modal').modal('show');
                }, 100);
            @else
                // Add operation failed - show add modal
                $('#add_document_type').modal('show');
            @endif
        @endif

        // Initialize Select2 with a more robust method
        function initSelect2(element) {
            $(element).select2({
                width: '100%',
                dropdownParent: $(element).closest('.modal')
            });
        }

        // Initialize on page load
        $('.select2-multi').each(function() {
            initSelect2(this);
        });

        // Re-initialize when modals are shown (to fix any rendering issues)
        $('.modal').on('shown.bs.modal', function() {
            $(this).find('.select2-multi').each(function() {
                initSelect2(this);
            });
        });
        
        // Robust normalization for DataTables length <select>
        (function(){
            function normalizeDataTableLengthSelects() {
                $('select[name$="_length"]').each(function () {
                    var $s = $(this);
                    $s.removeClass('custom-select custom-select-sm form-control form-control-smn form-control-sm');
                    $s.closest('.dataTables_length').css({ 'overflow': 'visible' });
                    $s.css({ 'padding-right': '', 'padding-left': '' });
                });
            }

            $(document).on('init.dt draw.dt', function (e, settings) {
                normalizeDataTableLengthSelects();
            });

            var observer = new MutationObserver(function (mutations) {
                mutations.forEach(function (m) {
                    m.addedNodes.forEach(function (node) {
                        if (node.nodeType !== 1) return;
                        if (node.matches && node.matches('select[name$="_length"]')) {
                            normalizeDataTableLengthSelects();
                        } else if ($(node).find('select[name$="_length"]').length) {
                            normalizeDataTableLengthSelects();
                        }
                    });
                });
            });
            observer.observe(document.body, { childList: true, subtree: true });

            for (let i = 0; i < 6; i++) { setTimeout(normalizeDataTableLengthSelects, i * 300); }
            setTimeout(function(){ observer.disconnect(); }, 10000);
        })();
    });
</script>
@endsection

@endsection
