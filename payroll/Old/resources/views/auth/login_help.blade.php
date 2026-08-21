@extends('layouts.app')
@section('content')
    <div class="main-wrapper">
        <div class="account-content">
            <div class="container">
                <!-- Account Logo -->
                <div class="account-logo">
                    <a href="#"><img src="{{ URL::to('assets/images/photo_defaults.png') }}" alt="HRMS" style="width:10rem"></a>
                </div>
                <!-- /Account Logo -->
                <div class="account-box">
                    <div class="account-wrapper">
                        <h3 class="account-title">Login Help</h3>
                        <p class="account-subtitle">How to access the system</p>
                        
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Login Instructions</h5>
                                
                                <div class="mb-4">
                                    <h6>Login Options:</h6>
                                    <p>You can log in using either:</p>
                                    <ul>
                                        <li>Your email address (e.g., <code>example@company.com</code>)</li>
                                        <li>Your employee ID (e.g., <code>EMP001</code>)</li>
                                    </ul>
                                </div>
                                
                                <div class="mb-4">
                                    <h6>Password Format:</h6>
                                    <p>For new users, your default password is:</p>
                                    <code>[employee_id_without_special_chars][FIRST_4_CHARS_OF_NAME]</code>
                                    <p class="mt-2">Where:</p>
                                    <ul>
                                        <li><code>[employee_id_without_special_chars]</code> is your Employee ID with hyphens and special characters removed</li>
                                        <li><code>[FIRST_4_CHARS_OF_NAME]</code> are the first 4 characters of your name (UPPERCASE, without spaces or special characters)</li>
                                    </ul>
                                    
                                    <div class="alert alert-info mt-3">
                                        <h6>Examples:</h6>
                                        <p>For employee "RAVI HOOGAR" with ID "DRI-020":<br>
                                        Password would be: <code>DRI020RAVI</code></p>
                                        
                                        <p>For employee "JOHN DOE" with ID "EMP-001":<br>
                                        Password would be: <code>EMP001JOHN</code></p>
                                    </div>
                                </div>
                                
                                <div class="alert alert-warning">
                                    <i class="fa fa-exclamation-triangle"></i> Please change your password after first login.
                                </div>
                                
                                <div class="text-center mt-4">
                                    <a href="{{ route('password.calculator') }}" class="btn btn-info me-2">Password Calculator</a>
                                    <a href="{{ route('login') }}" class="btn btn-primary">Back to Login</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
