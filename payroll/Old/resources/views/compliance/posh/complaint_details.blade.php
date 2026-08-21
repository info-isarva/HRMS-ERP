@extends('layouts.master')
@section('title', 'POSH Case File: ' . $complaint->complaint_number)

@section('content')
@include('compliance.posh._deprecated-banner')
<style>
    /* Premium Header */
    .page-header-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
        overflow: hidden;
        margin-bottom: 2rem;
        border: 1px solid #e9ecef;
    }
    
    .page-header-gradient {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 2.25rem 2rem;
        position: relative;
    }
    
    .page-header-pattern {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.03);
    }
    
    .page-header-circle-1 {
        position: absolute;
        top: -1rem;
        right: -1rem;
        width: 6rem;
        height: 6rem;
        background: rgba(255, 255, 255, 0.08);
        border-radius: 50%;
    }
    
    .page-header-circle-2 {
        position: absolute;
        bottom: -1rem;
        left: -1rem;
        width: 8rem;
        height: 8rem;
        background: rgba(255, 255, 255, 0.08);
        border-radius: 50%;
    }
    
    .page-header-icon-box {
        width: 3.5rem;
        height: 3.5rem;
        background: rgba(255, 255, 255, 0.2);
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .page-header-icon-box i {
        font-size: 1.75rem;
        color: white;
    }
    
    .page-title {
        color: white;
        font-weight: 700;
        font-size: 1.6rem;
        margin-bottom: 0.25rem;
    }
    
    .page-subtitle {
        color: rgba(255, 255, 255, 0.85);
        font-size: 0.9rem;
        margin-bottom: 0;
    }

    /* Custom Color Utilities */
    .text-indigo { color: #667eea !important; }
    .text-purple { color: #764ba2 !important; }
    .bg-indigo-light { background-color: #f0f4ff !important; }
    .bg-purple-light { background-color: #f5f0ff !important; }
    
    /* Premium Spacing Indicators */
    .section-indicator-left {
        border-left: 3px solid #764ba2;
        padding-left: 10px;
    }
    
    .section-indicator-orange {
        border-left: 3px solid #f59e0b;
        padding-left: 10px;
    }
    
    .section-indicator-blue {
        border-left: 3px solid #3b82f6;
        padding-left: 10px;
    }
    
    .section-indicator-red {
        border-left: 3px solid #ef4444;
        padding-left: 10px;
    }
    
    /* Timeline styling */
    .timeline-container {
        border-left: 2px solid #e9ecef;
        padding-left: 1.5rem;
        position: relative;
    }
    
    .timeline-node {
        position: relative;
        margin-bottom: 2rem;
    }
    
    .timeline-node::before {
        content: "";
        position: absolute;
        left: -2.05rem;
        top: 0.25rem;
        width: 1rem;
        height: 1rem;
        border-radius: 50%;
        background-color: #cbd5e1;
        border: 2px solid white;
        box-shadow: 0 0 0 3px rgba(0, 0, 0, 0.05);
        z-index: 2;
    }
    
    .timeline-node.node-active::before {
        background-color: #764ba2;
    }
    
    .timeline-node.node-status::before {
        background-color: #3b82f6;
    }
    
    .timeline-node.node-meeting::before {
        background-color: #10b981;
    }
    
    .timeline-node.node-document::before {
        background-color: #f59e0b;
    }

    .timeline-card {
        border-radius: 10px;
        background: #f8fafc;
        border: 1px solid #e9ecef;
        padding: 1.25rem;
        transition: all 0.2s ease;
    }

    .timeline-card:hover {
        border-color: #cbd5e1;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.04);
        background: white;
    }
</style>

<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header-card">
            <div class="page-header-gradient">
                <div class="page-header-pattern"></div>
                <div class="page-header-circle-1"></div>
                <div class="page-header-circle-2"></div>
                <div class="row align-items-center position-relative">
                    <div class="col-md-8 d-flex align-items-center gap-3">
                        <div class="page-header-icon-box">
                            <i class="fas fa-folder-open"></i>
                        </div>
                        <div>
                            <h1 class="page-title">POSH Case Management</h1>
                            <p class="page-subtitle">Detailed timeline logs, legal actions, and case file records.</p>
                        </div>
                    </div>
                    <div class="col-md-4 text-md-end mt-3 mt-md-0">
                        <a href="{{ route('compliance.posh.complaints.index') }}" class="btn btn-light fw-bold" style="border-radius: 0.5rem; color: #764ba2;">
                            <i class="fas fa-arrow-left me-2"></i> Back to Cases
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notification Alerts -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" style="border-radius: 8px;">
                <div class="d-flex align-items-center">
                    <i class="fas fa-check-circle me-2 fs-5"></i>
                    <div><strong>Success!</strong> {{ session('success') }}</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" style="border-radius: 8px;">
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-triangle me-2 fs-5"></i>
                    <div><strong>Error!</strong> {{ session('error') }}</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" style="border-radius: 8px;">
                <div class="d-flex">
                    <i class="fas fa-exclamation-circle me-2 fs-5 mt-1"></i>
                    <div>
                        <strong>Validation Errors:</strong>
                        <ul class="mb-0 mt-1 ps-3 text-sm">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row">
            <!-- Left Column: Case details -->
            <div class="col-lg-5 mb-4">
                <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; border: 1px solid #e9ecef !important; overflow: hidden;">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="mb-0 fw-bold text-dark d-flex align-items-center" style="font-size: 1.1rem;">
                            <i class="fas fa-file-signature text-purple me-2"></i> Case Overview
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <!-- Case Number -->
                        <div class="d-flex justify-content-between align-items-center p-3 mb-3" style="background: #f8fafc; border-radius: 8px; border: 1px solid #e9ecef;">
                            <span class="text-secondary fw-semibold text-xs text-uppercase">Case Number</span>
                            <span class="fw-bold text-dark" style="font-size: 0.95rem;">{{ $complaint->complaint_number }}</span>
                        </div>

                        <!-- Status Badge -->
                        <div class="d-flex justify-content-between align-items-center p-3 mb-4" style="background: #f8fafc; border-radius: 8px; border: 1px solid #e9ecef;">
                            <span class="text-secondary fw-semibold text-xs text-uppercase">Current Status</span>
                            @php
                                $statusClass = '';
                                $statusLabel = 'Pending';
                                if($complaint->status === 'under_investigation') {
                                    $statusClass = 'bg-info text-dark';
                                    $statusLabel = 'Under Investigation';
                                } elseif($complaint->status === 'resolved') {
                                    $statusClass = 'bg-success text-white';
                                    $statusLabel = 'Resolved';
                                } elseif($complaint->status === 'dismissed') {
                                    $statusClass = 'bg-secondary text-white';
                                    $statusLabel = 'Dismissed';
                                } else {
                                    $statusClass = 'bg-warning text-dark';
                                }
                            @endphp
                            <span class="badge {{ $statusClass }} py-2 px-3 fw-bold" style="border-radius: 6px; font-size: 0.75rem; letter-spacing: 0.5px;">
                                {{ $statusLabel }}
                            </span>
                        </div>

                        <hr class="my-4" style="border-top: 1px solid #e9ecef;">

                        <!-- Complainant -->
                        <div class="mb-4">
                            <div class="section-indicator-left mb-2">
                                <span class="fw-bold text-xs text-uppercase tracking-wider" style="color: #64748b;">Complainant Details</span>
                            </div>
                            @if($complaint->is_anonymous)
                                <div class="d-flex align-items-center p-3" style="border-radius: 8px; border-left: 4px solid #dc3545; background-color: #fff5f5; border-top: 1px solid #ffe3e3; border-right: 1px solid #ffe3e3; border-bottom: 1px solid #ffe3e3;">
                                    <i class="fas fa-user-secret fa-2x text-danger me-3"></i>
                                    <div>
                                        <div class="fw-bold text-danger">Anonymous Complaint</div>
                                        <small class="text-secondary text-xs">Identity is protected and masked from the system.</small>
                                    </div>
                                </div>
                            @else
                                <div class="d-flex align-items-center p-3" style="background: #fafafc; border-radius: 8px; border: 1px solid #e9ecef;">
                                    <div class="rounded-circle text-white d-flex align-items-center justify-content-center me-3 fw-bold shadow-sm" style="width: 45px; height: 45px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); font-size: 0.95rem; flex-shrink: 0;">
                                        {{ strtoupper(substr($complaint->complainant_name ?? ($complaint->employee->name ?? 'U'), 0, 2)) }}
                                    </div>
                                    <div class="overflow-hidden">
                                        <div class="fw-bold text-dark" style="font-size: 0.95rem;">{{ $complaint->complainant_name ?? ($complaint->employee->name ?? 'Unknown') }}</div>
                                        <div class="text-xs text-secondary mt-1"><strong class="text-muted">Employee ID:</strong> {{ $complaint->employee->employee_id ?? 'N/A' }}</div>
                                        <div class="text-xs text-secondary"><strong class="text-muted">Email:</strong> {{ $complaint->complainant_email ?? ($complaint->employee->email ?? 'N/A') }}</div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Respondent -->
                        <div class="mb-4">
                            <div class="section-indicator-orange mb-2">
                                <span class="fw-bold text-xs text-uppercase tracking-wider" style="color: #64748b;">Respondent / Accused Details</span>
                            </div>
                            <div class="d-flex align-items-center p-3" style="background: #fafafc; border-radius: 8px; border: 1px solid #e9ecef;">
                                <div class="rounded-circle text-white d-flex align-items-center justify-content-center me-3 fw-bold shadow-sm" style="width: 45px; height: 45px; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); font-size: 0.95rem; flex-shrink: 0;">
                                    {{ strtoupper(substr($complaint->respondent_name, 0, 2)) }}
                                </div>
                                <div class="overflow-hidden">
                                    <div class="fw-bold text-dark" style="font-size: 0.95rem;">{{ $complaint->respondent_name }}</div>
                                    <div class="text-xs text-secondary mt-1"><strong class="text-muted">Department:</strong> {{ $complaint->respondent_department ?? 'N/A' }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Incident Meta -->
                        <div class="mb-4">
                            <div class="section-indicator-blue mb-2">
                                <span class="fw-bold text-xs text-uppercase tracking-wider" style="color: #64748b;">Incident Information</span>
                            </div>
                            <div class="p-3 text-dark" style="background: #fafafc; border-radius: 8px; border: 1px solid #e9ecef; font-size: 0.85rem;">
                                <div class="mb-2"><strong style="color: #64748b;">Date of Incident:</strong> {{ \Carbon\Carbon::parse($complaint->incident_date)->format('d F Y') }}</div>
                                <div><strong style="color: #64748b;">Incident Location:</strong> {{ $complaint->incident_location }}</div>
                            </div>
                        </div>

                        <!-- Incident Description -->
                        <div class="mb-2">
                            <div class="section-indicator-red mb-2">
                                <span class="fw-bold text-xs text-uppercase tracking-wider" style="color: #64748b;">Incident Description</span>
                            </div>
                            <div class="p-3 text-dark" style="background: #fafafc; border-radius: 8px; border: 1px solid #e9ecef; font-size: 0.875rem; line-height: 1.6; ">
                                {{ $complaint->description }}
                            </div>
                        </div>

                        <!-- Resolution Summary -->
                        @if($complaint->resolution_summary)
                            <hr class="my-4" style="border-top: 1px solid #e9ecef;">
                            <div class="mb-2">
                                <div class="d-flex align-items-center mb-2">
                                    <div style="width: 3px; height: 16px; background-color: #198754; border-radius: 4px; margin-right: 8px;"></div>
                                    <span class="fw-bold text-success text-xs text-uppercase tracking-wider">Resolution Summary</span>
                                </div>
                                <div class="p-3 text-success" style="border-radius: 8px; font-size: 0.875rem; background-color: #f1fdf7; border: 1px solid #d1f2e4; border-left: 4px solid #198754;">
                                    <div class="fw-bold mb-1">Closed on {{ \Carbon\Carbon::parse($complaint->resolved_at)->format('d M Y, h:i A') }}</div>
                                    <div class="mt-1" style="color: #0f5132; line-height: 1.5;">{{ $complaint->resolution_summary }}</div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Right Column: Case Management Workflow -->
            <div class="col-lg-7">
                <!-- Submit Action Entry form -->
                @if(!in_array($complaint->status, ['resolved', 'dismissed']))
                    <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; border: 1px solid #e9ecef !important; overflow: hidden;">
                        <div class="card-header bg-white border-bottom py-3">
                            <h5 class="mb-0 fw-bold text-dark d-flex align-items-center" style="font-size: 1.1rem;">
                                <i class="fas fa-gavel text-purple me-2"></i> Take Action / Log Timeline Event
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <form action="{{ route('compliance.posh.complaints.log', $complaint->id) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                
                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label for="action_type" class="fw-semibold text-xs text-uppercase text-secondary mb-2">Action Type</label>
                                        <select class="form-select" name="action_type" id="action_type" required style="border-radius: 6px; padding: 0.6rem 0.9rem;">
                                            <option value="investigation_note">Investigation Note</option>
                                            <option value="meeting_minutes">Minutes of ICC Meeting</option>
                                            <option value="document_upload">Document Upload</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="status" class="fw-semibold text-xs text-uppercase text-secondary mb-2">Change Case Status</label>
                                        <select class="form-select" name="status" id="status" style="border-radius: 6px; padding: 0.6rem 0.9rem;">
                                            <option value="{{ $complaint->status }}">Keep current ({{ ucfirst(str_replace('_', ' ', $complaint->status)) }})</option>
                                            @if($complaint->status === 'pending')
                                                <option value="under_investigation">Under Investigation</option>
                                            @endif
                                            <option value="resolved">Resolved (Case closed)</option>
                                            <option value="dismissed">Dismissed (No substance found)</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Action Notes -->
                                <div class="mb-3" id="notes_container">
                                    <label for="notes" class="fw-semibold text-xs text-uppercase text-secondary mb-2">Action Notes / Description</label>
                                    <textarea class="form-control" name="notes" id="notes" rows="3" placeholder="Enter investigation steps, communications, or notes..." style="border-radius: 6px;"></textarea>
                                </div>

                                <!-- Minutes of Meeting -->
                                <div class="mb-3 d-none" id="minutes_container">
                                    <label for="minutes_of_meeting" class="fw-semibold text-xs text-uppercase text-secondary mb-2">Minutes of Meeting</label>
                                    <textarea class="form-control" name="minutes_of_meeting" id="minutes_of_meeting" rows="4" placeholder="Paste details of minutes, committee attendees, decisions..." style="border-radius: 6px;"></textarea>
                                </div>

                                <!-- Resolution Summary -->
                                <div class="mb-3 d-none" id="resolution_container">
                                    <label for="resolution_summary" class="fw-semibold text-xs text-uppercase text-danger mb-2">Resolution / Final Decision Summary</label>
                                    <textarea class="form-control" name="resolution_summary" id="resolution_summary" rows="3" placeholder="Enter final decision details and recommendations..." style="border-radius: 6px; border: 1px solid #f5c2c7;"></textarea>
                                </div>

                                <!-- Attachment -->
                                <div class="mb-4">
                                    <label for="attachment" class="fw-semibold text-xs text-uppercase text-secondary mb-2">Upload Supporting Document (Optional)</label>
                                    <input type="file" class="form-control" name="attachment" id="attachment" style="border-radius: 6px;">
                                    <small class="text-muted d-block mt-1">PDF, Image, or Doc up to 10MB.</small>
                                </div>

                                <button type="submit" class="btn btn-primary w-100 fw-bold py-2 shadow-sm" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; border-radius: 6px; font-size: 0.95rem;">
                                    <i class="fas fa-check-circle me-2"></i> Submit Action Entry
                                </button>
                            </form>
                        </div>
                    </div>
                @endif

                <!-- Case History Activity Timeline -->
                <div class="card border-0 shadow-sm" style="border-radius: 12px; border: 1px solid #e9ecef !important; overflow: hidden;">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="mb-0 fw-bold text-dark d-flex align-items-center" style="font-size: 1.1rem;">
                            <i class="fas fa-history text-purple me-2"></i> Case File Activity Timeline
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="timeline-container">
                            <!-- Initial Complaint Filed Node -->
                            <div class="timeline-node node-active">
                                <div class="timeline-card" style="border-left: 4px solid #764ba2;">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="fw-bold text-dark" style="font-size: 0.9rem;">Complaint Filed</span>
                                        <span class="text-xs text-secondary">{{ \Carbon\Carbon::parse($complaint->created_at)->format('d M Y, h:i A') }}</span>
                                    </div>
                                    <p class="mb-0 text-sm text-secondary">Complaint logged successfully in the system with ID: {{ $complaint->complaint_number }}.</p>
                                </div>
                            </div>

                            <!-- Case Activity Logs -->
                            @foreach($complaint->logs as $log)
                                @php
                                    $nodeTypeClass = 'node-active';
                                    $borderLeftColor = '#667eea';
                                    $iconColor = '#667eea';
                                    if($log->action_type === 'status_change') {
                                        $nodeTypeClass = 'node-status';
                                        $borderLeftColor = '#3b82f6';
                                        $iconColor = '#3b82f6';
                                    } elseif($log->action_type === 'meeting_minutes') {
                                        $nodeTypeClass = 'node-meeting';
                                        $borderLeftColor = '#10b981';
                                        $iconColor = '#10b981';
                                    } elseif($log->action_type === 'document_upload') {
                                        $nodeTypeClass = 'node-document';
                                        $borderLeftColor = '#f59e0b';
                                        $iconColor = '#f59e0b';
                                    }
                                @endphp
                                <div class="timeline-node {{ $nodeTypeClass }}">
                                    <div class="timeline-card" style="border-left: 4px solid {{ $borderLeftColor }};">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="fw-bold text-dark text-sm">
                                                @if($log->action_type === 'status_change')
                                                    <i class="fas fa-exchange-alt me-1" style="color: {{ $iconColor }};"></i> Status Updated
                                                @elseif($log->action_type === 'meeting_minutes')
                                                    <i class="fas fa-handshake me-1" style="color: {{ $iconColor }};"></i> Meeting Minutes Logged
                                                @elseif($log->action_type === 'document_upload')
                                                    <i class="fas fa-file-upload me-1" style="color: {{ $iconColor }};"></i> File Uploaded
                                                @else
                                                    <i class="fas fa-pen-fancy me-1" style="color: {{ $iconColor }};"></i> Timeline Entry
                                                @endif
                                            </span>
                                            <span class="text-xs text-secondary">{{ \Carbon\Carbon::parse($log->created_at)->format('d M Y, h:i A') }}</span>
                                        </div>
                                        <div class="text-xs text-secondary mb-2"><strong class="text-muted">Action by:</strong> {{ $log->actionByUser->name ?? 'ICC Board' }}</div>
                                        
                                        @if($log->notes)
                                            <p class="mb-0 text-sm text-secondary" style="white-space: pre-line;">{{ $log->notes }}</p>
                                        @endif

                                        @if($log->minutes_of_meeting)
                                            <div class="mt-2 border-top pt-2" style="border-top-color: #e9ecef !important;">
                                                <strong class="text-xs text-uppercase d-block mb-1 text-secondary">Minutes of Meeting:</strong>
                                                <p class="mb-0 text-sm text-secondary font-italic" style="white-space: pre-line;">{{ $log->minutes_of_meeting }}</p>
                                            </div>
                                        @endif

                                        @if($log->attachment_path)
                                            <div class="mt-3">
                                                <a href="{{ asset('storage/' . $log->attachment_path) }}" target="_blank" class="btn btn-sm btn-outline-primary" style="border-radius: 6px; font-size: 0.75rem; padding: 0.375rem 0.75rem; border: 1px solid #764ba2; color: #764ba2; background: transparent;">
                                                    <i class="fas fa-paperclip me-1"></i> View/Download: {{ $log->original_filename ?? 'Attachment' }}
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        var actionTypeSelect = document.getElementById("action_type");
        var statusSelect = document.getElementById("status");
        
        var notesContainer = document.getElementById("notes_container");
        var minutesContainer = document.getElementById("minutes_container");
        var resolutionContainer = document.getElementById("resolution_container");
        var notesInput = document.getElementById("notes");
        var minutesInput = document.getElementById("minutes_of_meeting");
        var resolutionInput = document.getElementById("resolution_summary");

        function updateFormLayout() {
            var actionType = actionTypeSelect ? actionTypeSelect.value : '';
            var status = statusSelect ? statusSelect.value : '';

            // Handle Action Type layout
            if(actionType === 'meeting_minutes') {
                minutesContainer.classList.remove('d-none');
                notesContainer.classList.add('d-none');
                notesInput.removeAttribute('required');
                minutesInput.setAttribute('required', 'required');
            } else {
                minutesContainer.classList.add('d-none');
                notesContainer.classList.remove('d-none');
                notesInput.setAttribute('required', 'required');
                minutesInput.removeAttribute('required');
            }

            // Handle Resolution / Closure layout
            if (status === 'resolved' || status === 'dismissed') {
                resolutionContainer.classList.remove('d-none');
                resolutionInput.setAttribute('required', 'required');
            } else {
                resolutionContainer.classList.add('d-none');
                resolutionInput.removeAttribute('required');
            }
        }

        if(actionTypeSelect) {
            actionTypeSelect.addEventListener("change", updateFormLayout);
        }
        if(statusSelect) {
            statusSelect.addEventListener("change", updateFormLayout);
        }

        updateFormLayout();
    });
</script>
@endsection
