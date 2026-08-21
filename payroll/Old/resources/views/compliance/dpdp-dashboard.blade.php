@extends('layouts.master')
@section('content')
    <div class="page-wrapper">
        <div class="content container-fluid">
            <!-- Page Header -->
            <div class="page-header">
                <div class="row">
                    <div class="col-sm-12">
                        <h3 class="page-title">DPDP Privacy Dashboard</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Data Privacy</li>
                        </ul>
                    </div>
                </div>
            </div>
            <!-- /Page Header -->

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <strong>Success!</strong> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0">Your Data Subject Rights</h4>
                        </div>
                        <div class="card-body">
                            <p>Under the Digital Personal Data Protection (DPDP) Act, you have specific rights regarding your personal data held by the company.</p>
                            
                            <div class="list-group mb-4">
                                <div class="list-group-item">
                                    <div class="row align-items-center">
                                        <div class="col">
                                            <h5 class="mb-1">Right to Access (Data Export)</h5>
                                            <p class="mb-0 text-muted">Request a copy of all personal data we hold about you.</p>
                                        </div>
                                        <div class="col-auto">
                                            <form action="{{ route('compliance.dpdp.export') }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-primary"><i class="fa fa-download"></i> Request Data Export</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <div class="list-group-item">
                                    <div class="row align-items-center">
                                        <div class="col">
                                            <h5 class="mb-1">Right to Correction</h5>
                                            <p class="mb-0 text-muted">Update any inaccurate or incomplete personal data.</p>
                                        </div>
                                        <div class="col-auto">
                                            <a href="{{ route('profile_user') }}" class="btn btn-outline-secondary"><i class="fa fa-pencil"></i> Update Profile</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="list-group-item">
                                    <div class="row align-items-center">
                                        <div class="col">
                                            <h5 class="mb-1">Right to Erasure (Deletion)</h5>
                                            <p class="mb-0 text-muted">Request deletion of your data. Note: Certain employment records must be retained by law.</p>
                                        </div>
                                        <div class="col-auto">
                                            <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteDataModal"><i class="fa fa-trash-o"></i> Request Deletion</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0">Consent Status</h4>
                        </div>
                        <div class="card-body">
                            @if($consent)
                                <div class="text-center mb-3">
                                    <i class="fa fa-check-circle text-success" style="font-size: 48px;"></i>
                                    <h5 class="mt-2">Policy Accepted</h5>
                                </div>
                                <ul class="list-unstyled">
                                    <li class="mb-2"><strong>Date:</strong> {{ $consent->accepted_at->format('d M Y, h:i A') }}</li>
                                    <li class="mb-2"><strong>IP Address:</strong> {{ $consent->ip_address }}</li>
                                </ul>
                                <hr>
                                <div class="text-center">
                                    <a href="#" class="text-danger"><small>Withdraw Consent</small></a>
                                    <p class="text-muted mt-2" style="font-size: 11px;">Withdrawing consent may affect your employment status as data processing is required for HR operations.</p>
                                </div>
                            @else
                                <div class="text-center mb-3">
                                    <i class="fa fa-times-circle text-danger" style="font-size: 48px;"></i>
                                    <h5 class="mt-2">Action Required</h5>
                                    <p>You have not accepted the current privacy policy.</p>
                                    <a href="{{ route('compliance.dpdp.policy') }}" class="btn btn-primary">Review Policy</a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Data Modal -->
    <div class="modal fade" id="deleteDataModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Request Data Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to request deletion of your personal data?</p>
                    <div class="alert alert-warning">
                        <strong>Important:</strong> 
                        <ul>
                            <li>Active employees cannot delete core HR records required for payroll and compliance.</li>
                            <li>If you are resigning, this will trigger the offboarding process.</li>
                        </ul>
                    </div>
                    <form>
                        <div class="form-group">
                            <label>Reason for deletion (optional)</label>
                            <textarea class="form-control" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal" onclick="alert('Deletion request submitted to HR for review.')">Submit Request</button>
                </div>
            </div>
        </div>
    </div>
@endsection
