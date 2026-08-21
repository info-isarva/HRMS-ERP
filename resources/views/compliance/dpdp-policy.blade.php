<x-guest-layout>
    <div class="login-card-container">
        <!-- Left Side: Content & Features -->
        <div class="login-content-side">
            <div class="content-inner">
                <h2 class="content-title">Enterprise Workspace</h2>
                <p class="content-subtitle">
                    Secure access to your business applications
                </p>
                
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <div>
                        <div style="font-weight: 600; font-size: 0.95rem;">Data Privacy (DPDP Act)</div>
                        <div style="font-size: 0.8rem; opacity: 0.8;">Your personal data is protected by law</div>
                    </div>
                </div>

                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-lock"></i>
                    </div>
                    <div>
                        <div style="font-weight: 600; font-size: 0.95rem;">Secure Consent</div>
                        <div style="font-size: 0.8rem; opacity: 0.8;">Digital signatures with IP and Timestamp tracking</div>
                    </div>
                </div>
                
                <div class="footer-copyright">
                   © {{ date('Y') }} <a href="https://isarvait.com" target="_blank">isarvait.com</a>
                </div>
            </div>
        </div>

        <!-- Right Side: DPDP Policy Form -->
        <div class="login-form-side" style="padding: 30px;">
            <div style="width: 100%; max-width: 550px; margin: 0 auto;">
                <div style="text-align: center; margin-bottom: 20px;">
                    <img src="{{ asset('images/logo_image.1749279521.svg') }}" alt="Logo" class="brand-logo" style="height: 50px; margin-bottom: 15px;">
                    <h3 class="form-title" style="margin-bottom: 10px; font-size: 1.3rem;">Digital Personal Data Protection Act</h3>
                    <p style="color: var(--text-muted); font-size: 0.85rem;">Action Required: Privacy Consent</p>
                </div>

                <div style="height: 250px; overflow-y: auto; background: #f8fafc; border: 1px solid var(--border-color); border-radius: 8px; padding: 15px; margin-bottom: 20px; font-size: 0.85rem; color: var(--text-main);">
                    <h5 style="font-weight: 600; margin-bottom: 5px;">1. Introduction</h5>
                    <p style="margin-bottom: 10px;">In accordance with the Digital Personal Data Protection (DPDP) Act, we are committed to protecting your personal data and respecting your privacy. This policy outlines how we collect, use, and safeguard your information.</p>
                    
                    <h5 style="font-weight: 600; margin-bottom: 5px;">2. Data We Collect</h5>
                    <p style="margin-bottom: 5px;">We collect personal information necessary for your employment, including but not limited to:</p>
                    <ul style="padding-left: 20px; margin-bottom: 10px; list-style-type: disc;">
                        <li>Identification details (Name, DOB, Government IDs)</li>
                        <li>Contact information</li>
                        <li>Financial details for payroll processing</li>
                        <li>Employment history and performance records</li>
                        <li>Attendance and leave data</li>
                    </ul>

                    <h5 style="font-weight: 600; margin-bottom: 5px;">3. Purpose of Collection</h5>
                    <p style="margin-bottom: 10px;">Your data is collected strictly for employment-related purposes, including payroll processing, benefits administration, performance evaluation, and compliance with legal obligations.</p>

                    <h5 style="font-weight: 600; margin-bottom: 5px;">4. Your Rights</h5>
                    <p style="margin-bottom: 5px;">Under the DPDP Act, you have the right to:</p>
                    <ul style="padding-left: 20px; margin-bottom: 10px; list-style-type: disc;">
                        <li>Access your personal data</li>
                        <li>Request correction of inaccurate data</li>
                        <li>Request erasure of data (subject to legal retention requirements)</li>
                        <li>Withdraw consent (which may impact your employment processing)</li>
                    </ul>

                    <h5 style="font-weight: 600; margin-bottom: 5px;">5. Data Security</h5>
                    <p style="margin-bottom: 10px;">We implement robust technical and organizational measures to protect your data against unauthorized access, alteration, or destruction.</p>

                    <h5 style="font-weight: 600; margin-bottom: 5px;">6. Acknowledgment</h5>
                    <p>By clicking "I Agree" below, you acknowledge that you have read, understood, and consent to the collection and processing of your personal data as described in this policy.</p>
                </div>

                <form method="POST" action="{{ route('compliance.dpdp.accept') }}">
                    @csrf
                    
                    <div style="margin-bottom: 20px;">
                        <label class="custom-checkbox" style="align-items: flex-start;">
                            <input type="checkbox" name="accept_terms" value="1" required>
                            <span class="checkmark" style="margin-top: 2px; flex-shrink: 0;"></span>
                            <span style="font-weight: 600; font-size: 0.9rem; line-height: 1.4;">I have read and I agree to the Digital Personal Data Protection (DPDP) Policy.</span>
                        </label>
                        <div style="font-size: 0.75rem; color: #94a3b8; margin-top: 8px; padding-left: 24px;">
                            * Note: Your IP address ({{ request()->ip() }}) and timestamp will be recorded as part of your digital consent signature.
                        </div>
                        @error('accept_terms')
                            <span style="color: #ef4444; font-size: 0.8em; margin-top: 5px; display: block; padding-left: 24px;">{{ $message }}</span>
                        @enderror
                    </div>

                    <div style="display: flex; gap: 15px;">
                        <button type="submit" name="reject" value="1" style="flex: 1; padding: 12px; background: white; color: #ef4444; border: 1px solid #ef4444; border-radius: 6px; font-weight: 600; font-size: 0.95rem; cursor: pointer; transition: all 0.2s;" formnovalidate onmouseover="this.style.background='#fef2f2'" onmouseout="this.style.background='white'">
                            I REJECT
                        </button>
                        <button type="submit" class="btn-primary" style="flex: 1;">
                            I AGREE & CONTINUE
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>
