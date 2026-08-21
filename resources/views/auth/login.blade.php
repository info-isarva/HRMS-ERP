<x-guest-layout>
    <div class="login-card-container">
        <!-- Left Side: Content & Features (Swapped to Left) -->
        <div class="login-content-side">
            <div class="content-inner">
                <h2 class="content-title">Enterprise Workspace</h2>
                <p class="content-subtitle">
                    Secure access to your business applications
                </p>
                
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div>
                        <div style="font-weight: 600; font-size: 0.95rem;">Payroll System</div>
                        <div style="font-size: 0.8rem; opacity: 0.8;">Manage employee compensation and benefits</div>
                    </div>
                </div>

                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-user-clock"></i>
                    </div>
                    <div>
                        <div style="font-weight: 600; font-size: 0.95rem;">Attendance Tracker</div>
                        <div style="font-size: 0.8rem; opacity: 0.8;">Monitor employee time and attendance</div>
                    </div>
                </div>
                
                 <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <div>
                        <div style="font-weight: 600; font-size: 0.95rem;">Enterprise Security</div>
                        <div style="font-size: 0.8rem; opacity: 0.8;">Your data is protected with industry-leading encryption</div>
                    </div>
                </div>
                
                <div class="footer-copyright">
                   © {{ date('Y') }} <a href="https://isarvait.com" target="_blank">isarvait.com</a>
                </div>
            </div>
        </div>

        <!-- Right Side: Login Form (Swapped to Right) -->
        <div class="login-form-side">
            <div class="form-wrapper-width">
                <div style="text-align: center;">
                    <img src="{{ asset('images/logo_image.1749279521.svg') }}" alt="Logo" class="brand-logo">
                    <h3 class="form-title">Sign In to your Account</h3>
                </div>

                @if(session('status'))
                <div class="alert alert-success d-flex align-items-center mb-4" role="alert" style="background: #ecfdf5; border: 1px solid #d1fae5; color: #047857; padding: 10px; border-radius: 8px; font-size: 0.9rem;">
                     <i class="fas fa-check-circle me-2"></i> {{ session('status') }}
                </div>
                @endif

                @if($errors->any())
                <div class="alert alert-danger d-flex align-items-center mb-4" role="alert" style="background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c; padding: 10px; border-radius: 8px; font-size: 0.9rem;">
                     <i class="fas fa-exclamation-circle me-2"></i> {{ $errors->first() }}
                </div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <!-- Company Code -->
                    <div class="form-group">
                        <label for="company_code" class="form-label">Company Code</label>
                        <div class="input-icon-wrap">
                            <i class="fas fa-building input-icon"></i>
                            <input id="company_code" type="text" class="form-control" name="company_code" value="{{ old('company_code', request()->cookie('company_code', session('company_code', 'ISARVA'))) }}" required placeholder="e.g. ISARVA">
                        </div>
                        @error('company_code')
                            <span style="color: #ef4444; font-size: 0.8em; margin-top: 5px; display: block;">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div class="form-group">
                        <label for="email" class="form-label">Email</label>
                        <div class="input-icon-wrap">
                            <i class="fas fa-user input-icon"></i>
                            <input id="email" type="text" class="form-control" name="email" value="{{ old('email') }}" required autofocus placeholder="Enter Email">
                        </div>
                        @error('email')
                            <span style="color: #ef4444; font-size: 0.8em; margin-top: 5px; display: block;">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div class="form-group">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-icon-wrap">
                            <i class="fas fa-lock input-icon"></i>
                            <input id="password" type="password" class="form-control" name="password" required autocomplete="current-password" placeholder="Enter Password">
                            <i class="fas fa-eye toggle-password" onclick="togglePassword()"></i>
                        </div>
                        @error('password')
                            <span style="color: #ef4444; font-size: 0.8em; margin-top: 5px; display: block;">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Remember & Forgot -->
                    <div class="checkbox-row">
                        <label class="custom-checkbox">
                            <input type="checkbox" name="remember" id="remember_me">
                            <span class="checkmark"></span>
                            <span>Remember this device</span>
                        </label>
                        
                        <a href="{{ route('password.request') }}" class="forgot-link">Forgot password?</a>
                    </div>

                    <button type="submit" class="btn-primary">
                        SIGN IN
                    </button>

                    <div class="divider">
                        Or continue with
                    </div>

                    <button type="button" onclick="googleLogin()" class="btn-google">
                        <img src="https://www.google.com/favicon.ico" alt="Google" style="width: 18px; height: 18px;">
                        Sign In with Google
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function googleLogin() {
            const code = document.getElementById('company_code').value.trim();
            if (!code) {
                alert('Please enter your company code first.');
                return;
            }
            window.location = '{{ route('auth.google') }}?company_code=' + encodeURIComponent(code);
        }

        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const icon = document.querySelector('.toggle-password');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
</x-guest-layout>