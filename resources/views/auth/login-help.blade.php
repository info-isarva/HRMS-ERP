<x-guest-layout>
    <div class="container login-container">
        <div class="row g-0">
            <div class="col-lg-6 login-left">
                <div class="app-icons">
                    <div class="app-icon payroll">
                        <i class="fas fa-money-check-alt"></i>
                    </div>
                    <div class="app-icon attendance">
                        <i class="fas fa-fingerprint"></i>
                    </div>
                </div>
                
                <div class="welcome-text">
                    <h1>Login Help</h1>
                    <p>How to access your enterprise applications</p>
                </div>
                
                <div class="features">
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <div>
                            <h5>Payroll System</h5>
                            <p>Manage employee compensation and benefits</p>
                        </div>
                    </div>
                    
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-user-clock"></i>
                        </div>
                        <div>
                            <h5>Attendance Tracker</h5>
                            <p>Monitor employee time and attendance</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6 login-right">
                <div class="login-logo">
                    <div class="logo-img mb-2 text-center">
                        <img src="{{ asset('images/logo_image.1749279521.svg') }}" alt="Divya Roopa Infracon Logo" class="img-fluid" style="max-height: 80px;">
                    </div>
                    <p class="text-muted mt-2">Workspace Portal</p>
                </div>
                
                <div class="login-form">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h4 class="card-title mb-4">Login Instructions</h4>
                            
                            <div class="mb-4">
                                <h5>Login Options</h5>
                                <p>You can log in using either:</p>
                                <ul>
                                    <li>Your email address (e.g., <code>example@company.com</code>)</li>
                                    <li>Your employee ID (e.g., <code>EMP001</code> or <code>DRI-020</code>)</li>
                                </ul>
                            </div>
                            
                            <div class="mb-4">
                                <h5>Password Format</h5>
                                <p>For new users, your default password is:</p>
                                <div class="code-block">
                                    <code>[employee_id_without_special_chars][FIRST_4_CHARS_OF_NAME]</code>
                                </div>
                                <p class="mt-2">Where:</p>
                                <ul>
                                    <li><code>[employee_id_without_special_chars]</code> is your Employee ID with hyphens and special characters removed</li>
                                    <li><code>[FIRST_4_CHARS_OF_NAME]</code> are the first 4 characters of your name (UPPERCASE, without spaces or special characters)</li>
                                </ul>
                                
                                <div class="alert alert-info mt-3">
                                    <h6 class="mb-2">Examples:</h6>
                                    <p class="mb-1">Employee "RAVI HOOGAR" with ID "DRI-020":<br>
                                    Password would be: <code>DRI020RAVI</code></p>
                                    
                                    <p class="mb-0">Employee "JOHN DOE" with ID "EMP-001":<br>
                                    Password would be: <code>EMP001JOHN</code></p>
                                </div>
                            </div>
                            
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i> Please change your password after first login.
                            </div>
                            
                            <div class="text-center mt-4">
                                <a href="{{ route('login') }}" class="btn btn-primary">
                                    <i class="fas fa-arrow-left me-2"></i> Back to Login
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>
