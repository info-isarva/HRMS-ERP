<x-guest-layout>
    <div class="login-card-container">
        <!-- Left Side: Content & Features -->
        <div class="login-content-side" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;">
            <div class="content-inner">
                <h2 class="content-title">Enterprise Workspace</h2>
                <p class="content-subtitle">
                    Secure access to your business applications
                </p>
                
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-users-shield"></i>
                    </div>
                    <div>
                        <div style="font-weight: 600; font-size: 0.95rem;">POSH Compliance</div>
                        <div style="font-size: 0.8rem; opacity: 0.8;">Zero tolerance for sexual harassment</div>
                    </div>
                </div>

                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-balance-scale"></i>
                    </div>
                    <div>
                        <div style="font-weight: 600; font-size: 0.95rem;">Confidential Redressal</div>
                        <div style="font-size: 0.8rem; opacity: 0.8;">ICC managed grievance resolution</div>
                    </div>
                </div>
                
                <div class="footer-copyright">
                   © {{ date('Y') }} <a href="https://isarvait.com" target="_blank" style="color: rgba(255,255,255,0.8);">isarvait.com</a>
                </div>
            </div>
        </div>

        <!-- Right Side: POSH Policy Form -->
        <div class="login-form-side" style="padding: 30px;">
            <div style="width: 100%; max-width: 550px; margin: 0 auto;">
                <div style="text-align: center; margin-bottom: 20px;">
                    <img src="{{ asset('images/logo_image.1749279521.svg') }}" alt="Logo" class="brand-logo" style="height: 50px; margin-bottom: 15px;">
                    <h3 class="form-title" style="margin-bottom: 10px; font-size: 1.3rem;">Prevention of Sexual Harassment Policy</h3>
                    <p style="color: var(--text-muted); font-size: 0.85rem;">Action Required: Digital Policy Acknowledgment</p>
                </div>

                <div style="height: 250px; overflow-y: auto; background: #f8fafc; border: 1px solid var(--border-color); border-radius: 8px; padding: 15px; margin-bottom: 20px; font-size: 0.85rem; color: var(--text-main);">
                    <h5 style="font-weight: 600; margin-bottom: 5px;">1. Policy Statement</h5>
                    <p style="margin-bottom: 10px;">Our company is committed to providing a safe, secure, and respectful work environment for all employees, free from discrimination, harassment, and any form of unwelcome conduct. We have a zero-tolerance policy towards sexual harassment.</p>
                    
                    <h5 style="font-weight: 600; margin-bottom: 5px;">2. Scope</h5>
                    <p style="margin-bottom: 10px;">This policy applies to all employees, contractors, interns, partners, clients, and visitors. It covers conduct at the workplace, work-related events, travel, and online or digital communications.</p>

                    <h5 style="font-weight: 600; margin-bottom: 5px;">3. What Constitutes Sexual Harassment</h5>
                    <p style="margin-bottom: 5px;">Under the POSH Act, 2013, sexual harassment includes any one or more of the following unwelcome acts or behavior (whether directly or by implication):</p>
                    <ul style="padding-left: 20px; margin-bottom: 10px; list-style-type: disc;">
                        <li>Physical contact and advances</li>
                        <li>A demand or request for sexual favors</li>
                        <li>Making sexually colored remarks</li>
                        <li>Showing pornography or sexually explicit content</li>
                        <li>Any other unwelcome physical, verbal, or non-verbal conduct of a sexual nature</li>
                    </ul>

                    <h5 style="font-weight: 600; margin-bottom: 5px;">4. Internal Complaints Committee (ICC)</h5>
                    <p style="margin-bottom: 10px;">A dedicated Internal Complaints Committee (ICC) has been established to receive, investigate, and address complaints of sexual harassment. The committee guarantees absolute confidentiality and protection to the victim and witnesses from any form of retaliation.</p>

                    <h5 style="font-weight: 600; margin-bottom: 5px;">5. Redressal & Support</h5>
                    <p style="margin-bottom: 10px;">Any aggrieved employee can register a confidential complaint through the secure Grievance Redressal Portal or by contacting any ICC board member directly. The ICC will conduct a formal inquiry in accordance with the law.</p>

                    <h5 style="font-weight: 600; margin-bottom: 5px;">6. Acknowledgment</h5>
                    <p>By clicking "I Agree" below, you acknowledge that you have read, understood, and agree to abide by the company's POSH Policy, and consent to digital tracking of this acknowledgment.</p>
                </div>

                <form method="POST" action="{{ route('compliance.posh.accept') }}">
                    @csrf
                    
                    <div style="margin-bottom: 20px;">
                        <label class="custom-checkbox" style="align-items: flex-start;">
                            <input type="checkbox" name="accept_terms" value="1" required>
                            <span class="checkmark" style="margin-top: 2px; flex-shrink: 0;"></span>
                            <span style="font-weight: 600; font-size: 0.9rem; line-height: 1.4;">I have read and I agree to the Prevention of Sexual Harassment (POSH) Policy.</span>
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
                        <button type="submit" class="btn-primary" style="flex: 1; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important; border: none !important;">
                            I AGREE & CONTINUE
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>
