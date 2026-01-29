<x-guest-layout>
    <div class="container login-container">
        <div class="row g-0">
            <!-- Left side branding and features -->
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
                    <h1>Enterprise Workspace</h1>
                    <p>Reset your password securely and regain access to your apps</p>
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

                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <div>
                            <h5>Enterprise Security</h5>
                            <p>Your data is protected with industry-leading encryption</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right side form -->
            <div class="col-lg-6 login-right">
                <div class="login-logo">
                    <div class="logo-img mb-2 text-center">
                        <img src="{{ asset('images/photo_defaults.jpg') }}" alt="Divya Roopa Infracon Logo" class="img-fluid" style="max-height: 80px;">
                    </div>
                    <p class="text-muted mt-2">Reset Password</p>
                </div>

                <div class="login-form">
                    @if(session('status'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        {{ session('status') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    @endif

                    <form method="POST" action="{{ route('password.email') }}">
                        @csrf

                        <div class="mb-4 text-center">
                            <h3>Forgot Your Password?</h3>
                            <p class="text-muted">We’ll send a password reset link to your email</p>
                        </div>

                        <div class="input-group">
                            <span class="input-icon">
                                <i class="fas fa-envelope"></i>
                            </span>
                            <input 
                                id="email" 
                                type="email" 
                                class="form-control @error('email') is-invalid @enderror" 
                                name="email" 
                                value="{{ old('email') }}" 
                                placeholder="Enter your company email"
                                required 
                                autofocus
                            >
                        </div>
                        @error('email')
                        <span class="error-message" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror

                        <button type="submit" class="btn btn-login w-100 mt-4">
                            <span class="login-text">Send Reset Link</span>
                            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                        </button>

                        <div class="text-center mt-3">
                            <a href="{{ route('login') }}" class="text-decoration-none">
                                <i class="fas fa-arrow-left me-1"></i> Back to Login
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>
