@extends('layouts.master')
@section('content')
    <div class="page-wrapper">
        <div class="content container-fluid">
            <!-- Page Header -->
            <div class="page-header">
                <div class="row">
                    <div class="col-sm-12">
                        <h3 class="page-title">Digital Personal Data Protection (DPDP) Act Consent</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item active">Privacy Policy</li>
                        </ul>
                    </div>
                </div>
            </div>
            <!-- /Page Header -->

            <div class="row justify-content-center">
                <div class="col-md-10">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h4 class="card-title mb-0 text-white">Action Required: Data Privacy Consent</h4>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-warning">
                                <i class="fa fa-info-circle"></i> To continue using the HRMS platform, you must read and accept our updated Digital Personal Data Protection (DPDP) policy.
                            </div>

                            <div class="policy-content border p-4 mb-4" style="height: 400px; overflow-y: auto; background-color: #f9f9f9;">
                                <h5>1. Introduction</h5>
                                <p>In accordance with the Digital Personal Data Protection (DPDP) Act, we are committed to protecting your personal data and respecting your privacy. This policy outlines how we collect, use, and safeguard your information.</p>
                                
                                <h5>2. Data We Collect</h5>
                                <p>We collect personal information necessary for your employment, including but not limited to:</p>
                                <ul>
                                    <li>Identification details (Name, DOB, Government IDs)</li>
                                    <li>Contact information</li>
                                    <li>Financial details for payroll processing</li>
                                    <li>Employment history and performance records</li>
                                    <li>Attendance and leave data</li>
                                </ul>

                                <h5>3. Purpose of Collection</h5>
                                <p>Your data is collected strictly for employment-related purposes, including payroll processing, benefits administration, performance evaluation, and compliance with legal obligations.</p>

                                <h5>4. Your Rights</h5>
                                <p>Under the DPDP Act, you have the right to:</p>
                                <ul>
                                    <li>Access your personal data</li>
                                    <li>Request correction of inaccurate data</li>
                                    <li>Request erasure of data (subject to legal retention requirements)</li>
                                    <li>Withdraw consent (which may impact your employment processing)</li>
                                </ul>

                                <h5>5. Data Security</h5>
                                <p>We implement robust technical and organizational measures to protect your data against unauthorized access, alteration, or destruction.</p>

                                <h5>6. Acknowledgment</h5>
                                <p>By clicking "I Agree" below, you acknowledge that you have read, understood, and consent to the collection and processing of your personal data as described in this policy.</p>
                            </div>

                            <form action="{{ route('compliance.dpdp.accept') }}" method="POST">
                                @csrf
                                <div class="form-group mb-4">
                                    <div class="form-check">
                                        <input class="form-check-input @error('accept_terms') is-invalid @enderror" type="checkbox" name="accept_terms" id="accept_terms" value="1" required>
                                        <label class="form-check-label" for="accept_terms" style="font-size: 16px;">
                                            <strong>I have read and I agree to the Digital Personal Data Protection (DPDP) Policy.</strong>
                                        </label>
                                        @error('accept_terms')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                    <small class="text-muted d-block mt-2">
                                        * Note: Your IP address ({{ request()->ip() }}) and timestamp will be recorded as part of your digital consent signature.
                                    </small>
                                </div>

                                <div class="text-center mt-4">
                                    <button type="submit" name="reject" value="1" class="btn btn-outline-danger btn-lg px-4 me-3" formnovalidate>I Reject</button>
                                    <button type="submit" class="btn btn-primary btn-lg px-5">I Agree & Continue</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
