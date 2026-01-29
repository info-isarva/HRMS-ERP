<x-guest-layout>
    <div class="login-card-container">
        <!-- Left Side: Content & Visuals -->
        <div class="login-content-side">
            <div class="content-inner">
                <h1 class="content-title">Admin Compliance</h1>
                <p class="content-subtitle">Ensure security and data integrity.</p>
                
                <div class="feature-item">
                    <div class="feature-icon"><i class="fas fa-shield-alt"></i></div>
                    <div class="feature-text">Mandatory Monthly Review</div>
                </div>
                
                <div class="feature-item">
                    <div class="feature-icon"><i class="fas fa-user-lock"></i></div>
                    <div class="feature-text">Privileged Access Audit</div>
                </div>
                <!-- Copyright Footer on Left Side -->
                 <div class="footer-copyright">
                    <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
                </div>
            </div>
        </div>

        <!-- Right Side: Consent Form -->
        <div class="login-form-side">
            <div class="form-wrapper-width">
                <!-- Logo -->
                <img src="{{ asset('images/logo_image.1749279521.svg') }}" alt="Logo" class="brand-logo">
                
                <h2 class="form-title">Compliance Consent</h2>
                
                <div class="mb-4 text-sm text-gray-600" style="text-align: justify; color: #64748b; line-height: 1.6;">
                    <p class="mb-3">
                        {{ __('As an Admin/Super Admin, you are required to review and accept this mandatory compliance form upon your first login and subsequently once every month.') }}
                    </p>
                    
                    <div class="p-3 bg-gray-50 rounded border border-gray-200 mb-3" style="background-color: #f8fafc; border: 1px solid #e2e8f0; padding: 15px; border-radius: 8px;">
                        <p class="mb-2 font-semibold" style="font-weight: 600; color: #334155;">I hereby acknowledge and agree:</p>
                        <ul style="list-style-type: disc; padding-left: 20px; margin-bottom: 0;">
                            <li class="mb-1">{{ __('To strictly adhere to organization data handling policies.') }}</li>
                            <li class="mb-1">{{ __('That my administrative actions are logged and audited.') }}</li>
                            <li>{{ __('To maintain the confidentiality of sensitive information.') }}</li>
                        </ul>
                    </div>

                    <p>
                        {{ __('By clicking "I Agree", I confirm my understanding and acceptance of these responsibilities.') }}
                    </p>
                </div>

                <form method="POST" action="{{ route('admin.consent.store') }}">
                    @csrf

                    <button type="submit" class="btn-primary">
                        {{ __('I Agree & Continue') }}
                    </button>
                    
                     <div class="divider">
                        <span>Isarva HRMS</span>
                    </div>
                </form>

                 <div style="text-align: center; margin-top: 10px;">
                     <form method="POST" action="{{ route('logout') }}" class="d-inline">
                        @csrf
                        <button type="submit" style="background:none; border:none; color:#2563eb; text-decoration:underline; cursor:pointer; font-size:0.9rem;">
                            {{ __('Logout') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>
