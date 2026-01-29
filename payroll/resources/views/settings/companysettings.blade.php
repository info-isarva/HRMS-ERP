
@extends('layouts.master')
@section('title',  'Company Settings')
@section('content')

<style>
    /* Page Header Card */
    .page-header-card {
        background: white;
        border-radius: 1rem;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
        overflow: hidden;
        margin-bottom: 2rem;
    }

    .page-header-gradient {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 2rem 1.5rem;
        position: relative;
        color: white;
    }

    .page-header-pattern {
        position: absolute;
        inset: 0;
        background: rgba(0, 0, 0, 0.04);
    }

    .page-header-circle-1,
    .page-header-circle-2 {
        position: absolute;
        background: rgba(255, 255, 255, 0.08);
        border-radius: 50%;
    }
    .page-header-circle-1 { top: -1rem; right: -1rem; width: 6rem; height: 6rem; }
    .page-header-circle-2 { bottom: -1rem; left: -1rem; width: 8rem; height: 8rem; }

    .page-header-icon-box {
        width: 4rem;
        height: 4rem;
        background: rgba(255, 255, 255, 0.16);
        border: 1px solid rgba(255, 255, 255, 0.22);
        border-radius: 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .page-header-title {
        font-size: 1.75rem;
        font-weight: 700;
        color: white;
        margin-bottom: 0.25rem;
    }
    .page-header-subtitle { color: rgba(255,255,255,0.9); margin: 0; }

    /* Modern Settings Card */
    .settings-card {
        background: white;
        border-radius: 1rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
        overflow: visible;
        border: 1px solid #e5e7eb;
        margin-bottom: 1.5rem;
    }

    .settings-card .card-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        border-radius: 1rem 1rem 0 0 !important;
        padding: 1.5rem;
    }

    .settings-card .card-header h5 {
        font-weight: 600;
        margin: 0;
        display: flex;
        align-items: center;
        font-size: 1.1rem;
    }

    .settings-card .card-header i {
        margin-right: 0.5rem;
        opacity: 0.9;
    }

    .settings-card .card-body {
        padding: 2rem;
    }

    /* Form Styling */
    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-label {
        font-weight: 600;
        color: #374151;
        margin-bottom: 0.5rem;
        font-size: 0.875rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .form-control, .form-select {
        border: 2px solid #e5e7eb;
        border-radius: 0.5rem;
        padding: 0.75rem 1rem;
        font-size: 0.875rem;
        transition: all 0.2s ease;
        background: white;
    }

    .form-control:focus, .form-select:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        outline: none;
    }

    .form-control::placeholder {
        color: #9ca3af;
    }

    /* Image Upload Styling */
    .image-upload-card {
        background: white;
        border: 2px dashed #e5e7eb;
        border-radius: 0.75rem;
        padding: 1.5rem;
        text-align: center;
        transition: all 0.3s ease;
        cursor: pointer;
        margin-bottom: 1rem;
        position: relative;
        min-height: 200px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
    }

    .image-upload-card:hover {
        border-color: #667eea;
        background: linear-gradient(135deg, #f0f4ff 0%, #e0e7ff 100%);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.15);
    }

    .image-upload-card img {
        width: 80px;
        height: 80px;
        object-fit: cover;
        border-radius: 0.5rem;
        margin-bottom: 0.75rem;
        border: 3px solid white;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .image-upload-card input[type="file"] {
        display: none;
    }

    .image-upload-card .upload-content {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        flex: 1;
    }

    .image-upload-text {
        color: #6b7280;
        font-size: 0.875rem;
        margin-top: 0.5rem;
    }

    /* Button Styling */
    .btn {
        border-radius: 0.5rem;
        padding: 0.75rem 2rem;
        font-weight: 500;
        transition: all 0.2s ease;
        border: none;
        font-size: 0.875rem;
    }

    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        background: linear-gradient(135deg, #5a67d8 0%, #6b46c1 100%);
    }

    /* Error Messages */
    .text-danger {
        color: #ef4444 !important;
        font-size: 0.75rem;
        margin-top: 0.25rem;
    }

    .invalid-feedback {
        display: block;
        color: #ef4444;
        font-size: 0.75rem;
        margin-top: 0.25rem;
    }

    /* Required Field Indicator */
    .text-danger {
        color: #ef4444 !important;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .page-header-gradient {
            padding: 1.5rem 1rem;
        }

        .settings-card .card-body {
            padding: 1.5rem;
        }

        .btn {
            width: 100%;
            margin-bottom: 0.5rem;
        }

        .image-upload-card {
            margin-bottom: 1rem;
        }
    }

    /* Section Headers */
    .section-header {
        font-size: 1.1rem;
        font-weight: 600;
        color: #374151;
        margin-bottom: 1.5rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid #e5e7eb;
    }

    /* Form Row Spacing */
    .form-row {
        margin-bottom: 2rem;
    }

    .form-row:last-child {
        margin-bottom: 0;
    }

    /* Additional spacing for sections */
    .row.mb-4 {
        margin-bottom: 2rem !important;
    }

    .row.mb-4 .col-12.mb-3 {
        margin-bottom: 1.5rem !important;
    }
</style>

    <div class="page-wrapper">
        <div class="content container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <!-- Modern Page Header -->
                    <div class="page-header-card">
                        <div class="page-header-gradient">
                            <div class="page-header-pattern"></div>
                            <div class="page-header-circle-1"></div>
                            <div class="page-header-circle-2"></div>
                            <div class="d-flex align-items-center">
                                <div class="page-header-icon-box">
                                    <i class="fas fa-building fa-lg"></i>
                                </div>
                                <div class="ms-3">
                                    <h1 class="page-header-title">Company Settings</h1>
                                    <p class="page-header-subtitle">Configure your company information and branding</p>
                                </div>
                            </div>
                        </div>
                        <div class="p-3 d-flex justify-content-between align-items-center">
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb mb-0">
                                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                                    <li class="breadcrumb-item"><a href="#">Settings</a></li>
                                    <li class="breadcrumb-item active">Company Settings</li>
                                </ol>
                            </nav>
                        </div>
                    </div>

                    <div class="settings-card">
                        <div class="card-header">
                            <h5><i class="fas fa-cogs me-2"></i>Company Information</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('company/settings/save') }}" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="id" value="1">

                                <!-- Basic Information -->
                                <div class="row mb-4">
                                    <div class="col-12 mb-3">
                                        <h6 class="section-header">Basic Information</h6>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">Company Name <span class="text-danger">*</span></label>
                                            <input class="form-control" type="text" name="company_name"
                                                   value="{{ $companySettings->company_name ?? '' }}"
                                                   placeholder="Enter company name" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">Contact Person <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="contact_person"
                                                   value="{{ $companySettings->contact_person ?? '' }}"
                                                   placeholder="Enter contact person name" required>
                                        </div>
                                    </div>
                                </div>

                                <!-- Branding & Assets -->
                                <div class="row mb-4">
                                    <div class="col-12 mb-3">
                                        <h6 class="section-header">Branding & Assets</h6>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">Company Logo <span class="text-danger">*</span></label>
                                            <div class="image-upload-card" onclick="document.getElementById('logoImageInput').click()">
                                                <img id="logoImagePreview"
                                                    src="{{ isset($companySettings->logo_image) && !empty($companySettings->logo_image) ? asset($companySettings->logo_image) : asset('assets/img/user-icon.webp') }}"
                                                    alt="Company Logo">
                                                <div class="upload-content">
                                                    <i class="fas fa-camera fa-2x text-muted mb-2"></i>
                                                    <p class="mb-1">Click to upload company logo</p>
                                                    <small class="image-upload-text">PNG, JPG up to 2MB</small>
                                                </div>
                                                <input type="file" name="logo_image" id="logoImageInput" accept="image/*">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">Favicon <span class="text-danger">*</span></label>
                                            <div class="image-upload-card" onclick="document.getElementById('faviconImageInput').click()">
                                                <img id="faviconImagePreview"
                                                    src="{{ isset($companySettings->favicon) && !empty($companySettings->favicon) ? asset($companySettings->favicon) : asset('assets/img/user-icon.webp') }}"
                                                    alt="Favicon">
                                                <div class="upload-content">
                                                    <i class="fas fa-image fa-2x text-muted mb-2"></i>
                                                    <p class="mb-1">Click to upload favicon</p>
                                                    <small class="image-upload-text">ICO, PNG up to 1MB</small>
                                                </div>
                                                <input type="file" name="favicon_image" id="faviconImageInput" accept="image/*">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Address & Location -->
                                <div class="row mb-4">
                                    <div class="col-12 mb-3">
                                        <h6 class="section-header">Address & Location</h6>
                                    </div>
                                    <div class="col-12 mb-3">
                                        <div class="form-group">
                                            <label class="form-label">Address <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="address"
                                                   value="{{ $companySettings->address ?? '' }}"
                                                   placeholder="Enter full address" required>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label class="form-label">Country <span class="text-danger">*</span></label>
                                            <select class="form-control form-select" name="country" required>
                                                <option value="">Select Country</option>
                                                <option value="India" {{ ($companySettings->country ?? '') == 'India' ? 'selected' : '' }}>India</option>
                                                <option value="United Kingdom" {{ ($companySettings->country ?? '') == 'United Kingdom' ? 'selected' : '' }}>United Kingdom</option>
                                                <option value="USA" {{ ($companySettings->country ?? '') == 'USA' ? 'selected' : '' }}>USA</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label class="form-label">City <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="city"
                                                   value="{{ $companySettings->city ?? '' }}"
                                                   placeholder="Enter city" required>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label class="form-label">State/Province <span class="text-danger">*</span></label>
                                            <select class="form-control form-select" name="state_province" required>
                                                <option value="">Select State</option>
                                                <option value="Karnataka" {{ ($companySettings->state_province ?? '') == 'Karnataka' ? 'selected' : '' }}>Karnataka</option>
                                                <option value="Pursat" {{ ($companySettings->state_province ?? '') == 'Pursat' ? 'selected' : '' }}>Pursat</option>
                                                <option value="Kan dal" {{ ($companySettings->state_province ?? '') == 'Kan dal' ? 'selected' : '' }}>Kan dal</option>
                                                <option value="Ta Keav" {{ ($companySettings->state_province ?? '') == 'Ta Keav' ? 'selected' : '' }}>Ta Keav</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label class="form-label">Postal Code</label>
                                            <input type="text" class="form-control" name="postal_code"
                                                   value="{{ $companySettings->postal_code ?? '' }}"
                                                   placeholder="Enter postal code">
                                        </div>
                                    </div>
                                </div>

                                <!-- Contact Information -->
                                <div class="row mb-4">
                                    <div class="col-12 mb-3">
                                        <h6 class="section-header">Contact Information</h6>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">Email <span class="text-danger">*</span></label>
                                            <input type="email" class="form-control" name="email"
                                                   value="{{ $companySettings->email ?? '' }}"
                                                   placeholder="company@example.com" required>
                                            @error('email')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">Phone Number</label>
                                            <input type="tel" class="form-control" name="phone_number"
                                                   value="{{ $companySettings->phone_number ?? '' }}"
                                                   placeholder="+1 (555) 123-4567">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">Mobile Number <span class="text-danger">*</span></label>
                                        <input type="tel" class="form-control" name="mobile_number"
                                               value="{{ $companySettings->mobile_number ?? '' }}"
                                               placeholder="+1 (555) 123-4567" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">Fax</label>
                                            <input type="text" class="form-control" name="fax"
                                                   value="{{ $companySettings->fax ?? '' }}"
                                                   placeholder="Enter fax number">
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label class="form-label">Website URL</label>
                                            <input type="text" class="form-control" name="website_url"
                                                   value="{{ $companySettings->website_url ?? '' }}"
                                                   placeholder="https://www.company.com">
                                        </div>
                                    </div>
                                </div>

                                <!-- Submit Button -->
                                <div class="row">
                                    <div class="col-12">
                                        <div class="d-flex justify-content-end">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-save me-2"></i>Save Company Settings
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

<script>
// Image preview functionality
document.getElementById('logoImageInput').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('logoImagePreview').src = e.target.result;
        };
        reader.readAsDataURL(file);
    }
});

document.getElementById('faviconImageInput').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('faviconImagePreview').src = e.target.result;
        };
        reader.readAsDataURL(file);
    }
});

// Form validation enhancement
document.querySelector('form').addEventListener('submit', function(e) {
    const requiredFields = document.querySelectorAll('input[required], select[required]');
    let isValid = true;
    let firstInvalidField = null;

    requiredFields.forEach(field => {
        // Remove previous validation classes
        field.classList.remove('is-invalid');

        if (!field.value.trim()) {
            field.classList.add('is-invalid');
            if (!firstInvalidField) {
                firstInvalidField = field;
            }
            isValid = false;
        }
    });

    if (!isValid) {
        e.preventDefault();
        alert('Please fill in all required fields.');
        if (firstInvalidField) {
            firstInvalidField.focus();
            firstInvalidField.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }
});

// Add visual feedback for required fields
document.addEventListener('DOMContentLoaded', function() {
    const requiredFields = document.querySelectorAll('input[required], select[required]');
    requiredFields.forEach(field => {
        field.addEventListener('blur', function() {
            if (this.value.trim() === '') {
                this.classList.add('is-invalid');
            } else {
                this.classList.remove('is-invalid');
            }
        });

        field.addEventListener('input', function() {
            if (this.value.trim() !== '') {
                this.classList.remove('is-invalid');
            }
        });
    });
});
</script>

@endsection