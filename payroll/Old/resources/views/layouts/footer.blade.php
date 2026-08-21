<div class="footer-modern">
    <div class="row align-items-center justify-content-between">
        <div class="col-md-6 text-center text-md-start">
            <p class="mb-0">© {{ date('Y') }} Isarva HRMS. All rights reserved.</p>
        </div>
        <div class="col-md-6 text-center text-md-end">
            <div class="footer-links">
                <a href="#">Privacy Policy</a>
                <a href="#">Terms of Service</a>
                <a href="#">Support</a>
                <span class="version-badge">Version 1.0</span>
            </div>
        </div>
    </div>
</div>

<style>
    .footer-modern {
        background-color: #fff;
        border-top: 1px solid #e5e7eb;
        padding: 1.25rem 2rem;
        position: relative;
        z-index: 1;
        margin-left: 280px; /* Match standard sidebar width */
        transition: margin-left 0.3s ease;
    }
    
    @media (max-width: 991.98px) {
        .footer-modern {
            margin-left: 0;
            padding: 1rem;
            margin-bottom: 60px; /* Add space for bottom nav if mobile */
        }
        
        .footer-links {
            justify-content: center;
            margin-top: 0.5rem;
            gap: 1rem;
        }
    }
    
    .footer-modern p {
        color: #6b7280;
        font-size: 0.875rem;
    }
    
    .footer-links {
        display: inline-flex;
        align-items: center;
        gap: 1.5rem;
        flex-wrap: wrap;
    }
    
    .footer-links a {
        color: #6b7280;
        font-size: 0.875rem;
        text-decoration: none;
        transition: color 0.2s;
    }
    
    .footer-links a:hover {
        color: #667eea;
    }
    
    .version-badge {
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
        color: #667eea;
        padding: 0.25rem 0.75rem;
        border-radius: 2rem;
        font-size: 0.75rem;
        font-weight: 600;
        border: 1px solid rgba(102, 126, 234, 0.1);
    }

    /* Handled by JavaScript usually, but we can try to match sidebar-collapsed class if it exists on body */
    .mini-sidebar .footer-modern {
        margin-left: 70px;
    }
</style>
