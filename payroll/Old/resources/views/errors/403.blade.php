@extends('layouts.master')
@section('title', 'Access Denied')
@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-lg">
                    <div class="card-body text-center p-5">
                        <div class="error-icon mb-4">
                            <i class="fas fa-shield-alt text-danger" style="font-size: 5rem;"></i>
                        </div>
                        
                        <h1 class="error-title mb-3">
                            <span class="text-danger">403</span> - Access Denied
                        </h1>
                        
                        <p class="error-message text-muted mb-4">
                            <strong>You are not authorized to access this resource.</strong><br>
                            You don't have the required permissions to view this page.
                        </p>
                        
                        <div class="alert alert-warning mb-4">
                            <i class="fas fa-info-circle"></i>
                            <strong>Need Access?</strong> Contact your system administrator to request the necessary permissions.
                        </div>
                        
                        <div class="error-actions">
                            <a href="{{ route('home') }}" class="btn btn-primary btn-lg me-3">
                                <i class="fas fa-home"></i> Go to Dashboard
                            </a>
                            <button onclick="history.back()" class="btn btn-secondary btn-lg">
                                <i class="fas fa-arrow-left"></i> Go Back
                            </button>
                        </div>
                        
                        <hr class="my-4">
                        
                        <div class="help-section">
                            <h6 class="text-muted">What can I do?</h6>
                            <ul class="list-unstyled text-muted">
                                <li><i class="fas fa-check text-success"></i> Return to the dashboard</li>
                                <li><i class="fas fa-check text-success"></i> Contact your manager or HR</li>
                                <li><i class="fas fa-check text-success"></i> Request additional permissions</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.error-icon {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.05);
    }
    100% {
        transform: scale(1);
    }
}

.error-title {
    font-size: 2.5rem;
    font-weight: 600;
}

.error-message {
    font-size: 1.1rem;
    line-height: 1.6;
}

.card {
    border: none;
    border-radius: 15px;
}

.btn-lg {
    padding: 12px 30px;
    font-size: 1.1rem;
    border-radius: 8px;
}

.help-section ul li {
    padding: 5px 0;
}

.help-section ul li i {
    margin-right: 10px;
}
</style>
@endsection