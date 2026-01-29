@extends('layouts.master')
@section('title', 'Activity Logs')

@section('style')
<style>
    .header-gradient {
        background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%) !important;
        color: white !important;
        font-family: 'Inter', sans-serif;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04) !important;
        border-radius: 1.5rem !important;
        margin-bottom: 2rem !important;
        padding: 2.5rem !important;
        position: relative;
        overflow: hidden;
    }
    
    .header-gradient::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg width="60" height="60" viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg"><g fill="none" fill-rule="evenodd"><g fill="%23ffffff" fill-opacity="0.05"><circle cx="50" cy="50" r="4"/><circle cx="10" cy="10" r="4"/><circle cx="30" cy="30" r="4"/></g></svg>') repeat;
        opacity: 0.3;
    }
    
    .header-gradient > * {
        position: relative;
        z-index: 1;
    }
    .shine-effect {
        position: relative;
        overflow: hidden;
    }
    .shine-effect::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(37, 99, 235, 0.1), transparent);
        animation: shine 3s infinite;
    }
    @keyframes shine {
        0% { left: -100%; }
        100% { left: 100%; }
    }
    
    /* Override any conflicting styles */
    .header-gradient * {
        color: white !important;
    }
    
    .header-gradient h1, .header-gradient h2, .header-gradient h3, .header-gradient h4, .header-gradient h5, .header-gradient h6 {
        color: white !important;
        font-weight: bold !important;
    }
    
    .filter-header * {
        color: white !important;
    }
    .pulse-animation {
        animation: pulse 2s infinite;
    }
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }
    .card-hover {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border: 1px solid #e5e7eb;
        background: white;
    }
    .card-hover:hover {
        transform: translateY(-2px);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        border-color: #d1d5db;
    }
    .table-row-hover:hover {
        background: linear-gradient(90deg, rgba(59, 130, 246, 0.05) 0%, rgba(147, 51, 234, 0.05) 100%);
        transform: scale(1.002);
        transition: all 0.2s ease;
    }
    
    /* Activity Icon Styling */
    .activity-icon {
        transition: all 0.3s ease;
    }
    .activity-icon:hover {
        transform: rotate(10deg) scale(1.1);
    }
    
    /* IP Badge Styling */
    .ip-badge {
        background: linear-gradient(45deg, #2563eb 0%, #3b82f6 100%);
        color: white !important  ;
        font-family: 'Courier New', monospace;
        box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.3);
    }
    .server-ip-badge {
        background: linear-gradient(45deg, #7c3aed 0%, #8b5cf6 100%);
        color: white !important;
        font-family: 'Courier New', monospace;
        box-shadow: 0 4px 6px -1px rgba(124, 58, 237, 0.3);
    }
    .security-badge {
        background: linear-gradient(45deg, #dc2626 0%, #ef4444 100%);
        color: white !important;
        font-family: 'Courier New', monospace;
        box-shadow: 0 4px 6px -1px rgba(220, 38, 38, 0.3);
    }
    
    /* User Avatar Styling */
    .user-avatar-bg-color {
        background: linear-gradient(135deg, #2563eb 0%, #7c3aed 100%) !important;
    }
    
    /* Activity Type Badges */
    .badge-created {
        background-color: #dcfce7 !important;
        color: #166534 !important;
        border: 1px solid #bbf7d0;
    }
    .badge-updated {
        background-color: #dbeafe !important;
        color: #1e40af !important;
        border: 1px solid #93c5fd;
    }
    .badge-deleted {
        background-color: #fee2e2 !important;
        color: #dc2626 !important;
        border: 1px solid #fca5a5;
    }
    .badge-default {
        background-color: #f3f4f6 !important;
        color: #374151 !important;
        border: 1px solid #d1d5db;
    }

    /* MISSING UTILITY CLASS */
    .text-gray-600 { color: #4b5563 !important; }
    
    /* ENFORCED VISIBILITY HELPERS */
    .text-force-dark { color: #374151 !important; }
    .text-force-black { color: #000000 !important; }
    .font-weight-medium { font-weight: 500 !important; }
    .font-weight-bold-custom { font-weight: 600 !important; }

    /* BOOTSTRAP OVERRIDES for Table Badges */
    #activityLogsTable .badge {
        --bs-badge-color: #1f2937;
        --bs-badge-padding-x: 0.65em;
        --bs-badge-padding-y: 0.35em;
        --bs-badge-font-size: 0.85em; /* Updated to 0.85em as per user change */
        --bs-badge-font-weight: 600;
        color: #1f2937 !important; /* Fallback/Force */
        background-color: transparent; /* Reset to rely on specific badge classes */
        white-space: normal !important; /* Allow wrapping if needed */
    }

    /* SPECIFIC OVERRIDE FOR IP/SERVER BADGES (Must be White Text) */
    #activityLogsTable .ip-badge,
    #activityLogsTable .server-ip-badge,
    #activityLogsTable .security-badge {
        color: #ffffff !important;
        --bs-badge-color: #ffffff;
    }

    /* Missing Icon Utilities */
    .bg-green-50 { background-color: #f0fdf4 !important; }
    .text-green-600 { color: #166534 !important; }
    .border-green-200 { border-color: #bbf7d0 !important; }
    
    .bg-blue-50 { background-color: #eff6ff !important; }
    .text-blue-600 { color: #2563eb !important; }
    .border-blue-200 { border-color: #bfdbfe !important; }

    .bg-red-50 { background-color: #fef2f2 !important; }
    .text-red-600 { color: #dc2626 !important; }
    .border-red-200 { border-color: #fecaca !important; }


    /* FORCE TABLE TEXT VISIBILITY (NUCLEAR OPTION) */
    #activityLogsTable tbody td {
        color: #374151 !important;
    }
    
    #activityLogsTable tbody td .text-dark,
    #activityLogsTable tbody td .text-gray-900 {
        color: #111827 !important;
        opacity: 1 !important;
    }

    /* Fix faint module text - make it much darker */
    #activityLogsTable tbody td .text-muted {
        color: #4b5563 !important; /* Gray-600 instead of Gray-400 */
        font-weight: 500 !important;
    }

    /* Force Badge Colors in Table - Ensure High Contrast */
    #activityLogsTable tbody td .badge-created { 
        color: #14532d !important; /* Dark Green */
        background-color: #dcfce7 !important; 
        border: 1px solid #bbf7d0 !important;
    }
    #activityLogsTable tbody td .badge-updated { 
        color: #1e3a8a !important; /* Dark Blue */
        background-color: #dbeafe !important; 
        border: 1px solid #93c5fd !important;
    }
    #activityLogsTable tbody td .badge-deleted { 
        color: #7f1d1d !important; /* Dark Red */
        background-color: #fee2e2 !important; 
        border: 1px solid #fca5a5 !important;
    }
    #activityLogsTable tbody td .badge-login { 
        color: #1e3a8a !important; /* Dark Blue */
        background-color: #eff6ff !important; 
        border: 1px solid #bfdbfe !important;
    }
    #activityLogsTable tbody td .badge-default { 
        color: #374151 !important; /* Dark Gray */
        background-color: #f3f4f6 !important;
    }
    
    /* Modal Badge Styling - Better Visibility with Specific Targeting */
    #activityLogDetailsModal .modal-body .badge {
        font-size: 0.875rem !important;
        padding: 0.5rem 0.75rem !important;
        font-weight: 600 !important;
        border-radius: 0.5rem !important;
        border: 1px solid transparent !important;
        text-shadow: none !important;
    }
    
    /* Specific badge color overrides for modal */
    #activityLogDetailsModal .modal-body .badge.bg-primary {
        background-color: #0d6efd !important;
        color: #ffffff !important;
        border-color: #0a58ca !important;
    }
    
    #activityLogDetailsModal .modal-body .badge.bg-success {
        background-color: #198754 !important;
        color: #ffffff !important;
        border-color: #157347 !important;
    }
    
    #activityLogDetailsModal .modal-body .badge.bg-info {
        background-color: #0dcaf0 !important;
        color: #000000 !important;
        border-color: #3dd5f3 !important;
    }
    
    #activityLogDetailsModal .modal-body .badge.bg-warning {
        background-color: #ffc107 !important;
        color: #000000 !important;
        border-color: #ffcd39 !important;
    }
    
    #activityLogDetailsModal .modal-body .badge.bg-danger {
        background-color: #dc3545 !important;
        color: #ffffff !important;
        border-color: #b02a37 !important;
    }
    
    #activityLogDetailsModal .modal-body .badge.bg-light {
        background-color: #f8f9fa !important;
        color: #212529 !important;
        border-color: #dee2e6 !important;
    }
    
    #activityLogDetailsModal .modal-body .badge.bg-dark {
        background-color: #212529 !important;
        color: #ffffff !important;
        border-color: #343a40 !important;
    }
    
    #activityLogDetailsModal .modal-body .badge.bg-secondary {
        background-color: #6c757d !important;
        color: #ffffff !important;
        border-color: #5a6268 !important;
    }
    
    /* Font monospace styling */
    #activityLogDetailsModal .modal-body .font-monospace {
        font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace !important;
        background-color: #f1f3f4 !important;
        padding: 0.375rem 0.75rem !important;
        border-radius: 0.375rem !important;
        border: 1px solid #d1d5db !important;
        color: #1f2937 !important;
        font-weight: 500 !important;
    }
    
    /* Text color overrides for clarity */
    #activityLogDetailsModal .modal-body .text-dark {
        color: #212529 !important;
        font-weight: 600 !important;
    }
    
    #activityLogDetailsModal .modal-body .text-white {
        color: #ffffff !important;
    }

    /* Bootstrap 5 -> 4 compatibility fallbacks */
    /* small helpers for missing utilities (gap, me/ms, fw-*) used in some parts of the markup */
    .gap-2 > * + * { margin-left: 0.5rem; }
    .gap-3 > * + * { margin-left: 0.75rem; }
    .g-3 { margin-left: -0.5rem; margin-right: -0.5rem; }
    .g-3 > [class*="col-"] { padding-left: 0.5rem; padding-right: 0.5rem; }

    /* fw-* fallbacks */
    .fw-bold { font-weight: 700 !important; }
    .fw-medium { font-weight: 500 !important; }

    /* me / ms fallbacks to use with existing markup (end/start margins) */
    .me-1 { margin-right: .25rem !important; }
    .me-2 { margin-right: .5rem !important; }
    .me-3 { margin-right: .75rem !important; }
    .ms-1 { margin-left: .25rem !important; }
    .ms-2 { margin-left: .5rem !important; }
    .ms-3 { margin-left: .75rem !important; }
    
    #activityLogDetailsModal .modal-body .text-muted {
        color: #6c757d !important;
    }
    
    .modal-body .bg-light {
        background-color: #f8f9fa !important;
    }
    
    /* Table Header Styling */
    .bg-gray-50 {
        background-color: #f9fafb !important;
    }
    
    /* Table Row Styling */
    .divide-y.divide-gray-200 > :not([hidden]) ~ :not([hidden]) {
        border-top-width: 1px;
        border-color: #e5e7eb;
    }
    
    /* Text Color Classes */
    .text-gray-500 {
        color: #6b7280 !important;
    }
    .text-gray-900 {
        color: #111827 !important;
    }
    .text-secondary {
        color: #7c3aed !important;
    }
    
    /* Enhanced Select Styling */
    .form-select {
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        background-image: url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3E%3Cpath fill='none' stroke='%23495057' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 1rem center;
        background-size: 16px 12px;
        padding-right: 2rem;
        padding-left:1rem;
        border: 2px solid #e9ecef;
        border-radius: 0.75rem;
        transition: all 0.3s ease;
        font-weight: 500;
        min-height: 48px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
       /* margin-top: 0.25rem; /* Add spacing below label */
    }
    
    .form-select:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        outline: none; 
    }
    
    .form-select:hover {
        border-color: #adb5bd;
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    }
    
    /* Export Dropdown Styling */
    .dropdown-menu {
        border-radius: 0.75rem;
        box-shadow: 0 12px 32px rgba(0, 0, 0, 0.18);
        border: 1px solid #e5e7eb;
        padding: 0.5rem 0;
        background: #ffffff;
        z-index: 1060; /* over header */
    }
    
    .dropdown-item {
        padding: 0.75rem 1.5rem;
        transition: all 0.2s ease;
        border-radius: 8px;
        margin: 0.125rem 0.5rem;
        color: #374151;
    }
    
    .dropdown-item:hover {
        background: linear-gradient(135deg, #eef2ff 0%, #f5f3ff 100%);
        color: #1d4ed8;
        transform: translateX(4px);
    }
    
    .btn-outline-primary {
        border-width: 2px;
        border-radius: 0.75rem;
        font-weight: 600;
        padding: 0.5rem 1rem;
        transition: all 0.3s ease;
    }
    
    .btn-outline-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(13, 110, 253, 0.3);
    }
    
    /* Enhanced Input Styling */
    .form-control {
        border: 2px solid #e9ecef;
        border-radius: 0.75rem;
        transition: all 0.3s ease;
        font-weight: 500;
        min-height: 48px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        margin-top: 0.25rem; /* Add spacing below label */
    }
    
    .form-control:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        outline: none;
    }
    
    .form-control:hover {
        border-color: #adb5bd;
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    }
    
    /* Form Label Styling */
    .form-label {
        margin-bottom: 0.5rem;
        font-weight: 600;
        color: #374151;
        display: block;
    }
    
    /* Enhanced Button Styling */
    .btn {
        border-radius: 0.75rem;
        font-weight: 600;
        padding: 0.75rem 1.5rem;
        transition: all 0.3s ease;
        min-height: 48px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    }
    
    .btn-primary {
        background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
        border: none;
    }
    
    .btn-secondary {
        background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
        border: none;
    }
    
    /* Filter Section Enhancement */
    .bg-gradient-primary {
        background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%) !important;
    }
    
    .filter-card {
        border-radius: 1rem !important;
        border: none !important;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1) !important;
        overflow: hidden !important;
    }
    
    .filter-header {
        background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%) !important;
        color: white !important;
        border-radius: 1rem 1rem 0 0 !important;
        padding: 1.25rem 1.5rem !important;
        border: none !important;
    }
    
    .filter-body {
        background: #f8f9fa !important;
        padding: 1.5rem !important;
    }
    
    /* Card Enhancements */
    .card {
        border-radius: 1rem;
        border: none;
    }

    /* Modern Activity Logs Table Styles */
    .activity-logs-container {
        background: white;
        border-radius: 1rem;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        margin-bottom: 2rem;
    }

    .activity-table-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 1.5rem 2rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .table-icon {
        width: 48px;
        height: 48px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 1rem;
    }

    .table-icon i {
        font-size: 1.2rem;
        color: white;
    }

    .header-actions .btn-modern {
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        color: white;
        border-radius: 8px;
        padding: 0.5rem 1rem;
        transition: all 0.3s ease;
        backdrop-filter: blur(10px);
    }

    .header-actions .btn-modern:hover {
        background: rgba(255, 255, 255, 0.2);
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    }

    /* .status-indicator {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        background: rgba(255, 255, 255, 0.1);
        padding: 0.5rem 1rem;
        border-radius: 20px;
        backdrop-filter: blur(10px);
    } */

    /* .status-dot {
        width: 10px;
        height: 10px;
        background: #28a745;
        border-radius: 50%;
        animation: pulse 2s infinite;
        box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.7);
        margin-right: 0.5rem;
        border: 2px solid rgba(255, 255, 255, 0.3);
    } */

    @keyframes pulse {
        0% {
            transform: scale(0.95);
            box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.7);
        }
        70% {
            transform: scale(1);
            box-shadow: 0 0 0 8px rgba(40, 167, 69, 0);
        }
        100% {
            transform: scale(0.95);
            box-shadow: 0 0 0 0 rgba(40, 167, 69, 0);
        }
    }

    /* Table Styling */
    .activity-table {
        margin-bottom: 0;
        border-collapse: separate;
        border-spacing: 0;
    }

    .activity-table thead th {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        color: #495057;
        font-weight: 600;
        font-size: 0.875rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 1rem 1.5rem;
        border: none;
        border-bottom: 2px solid #dee2e6;
        position: sticky;
        top: 0;
        z-index: 10;
    }

    .activity-table tbody tr {
        transition: all 0.3s ease;
        border-bottom: 1px solid #f1f3f4;
    }

    .activity-table tbody tr:hover {
        background: linear-gradient(135deg, #f8f9ff 0%, #fff8f8 100%);
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    .activity-table tbody td {
        padding: 1rem 1.5rem;
        vertical-align: middle;
        border: none;
        font-size: 0.9rem;
    }

    /* Mobile Styles - stricter rules for Bootstrap 4 layout */
    .desktop-only {
        display: block !important;
        width: 100% !important;
        float: none !important;
    }

    .mobile-only {
        display: none !important;
        width: 100% !important;
    }

    .mobile-activity-container {
        padding: 1rem;
        max-height: 60vh;
        overflow-y: auto;
        -webkit-overflow-scrolling: touch;
    }

    .mobile-activity-card {
        background: #ffffff;
        border-radius: 12px;
        padding: 1rem;
        margin-bottom: 1rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        border-left: 4px solid #0d6efd;
        transition: all 0.2s ease;
        width: 100% !important;
        box-sizing: border-box;
    }

    .mobile-activity-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 22px rgba(0, 0, 0, 0.12);
    }

    .mobile-activity-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.5rem;
        gap: 0.5rem;
    }

    .mobile-activity-title {
        font-weight: 600;
        color: #333;
        font-size: 0.95rem;
        display: inline-flex;
        align-items: center;
    }

    .mobile-activity-type {
        display: inline-block;
        padding: 0.25rem 0.5rem;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 500;
        text-transform: uppercase;
    }

    .mobile-activity-details {
        color: #666;
        font-size: 0.85rem;
        margin-bottom: 0.5rem;
    }

    .mobile-activity-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 0.5rem;
        border-top: 1px solid #f1f3f4;
        gap: 0.5rem;
    }

    .mobile-activity-time {
        color: #999;
        font-size: 0.75rem;
    }

    .loading-spinner {
        display: flex;
        justify-content: center;
        align-items: center;
    }

    /* Modern Dropdown Styles */
    .modern-dropdown {
        border-radius: 12px;
        border: 1px solid #e5e7eb;
        box-shadow: 0 12px 32px rgba(0, 0, 0, 0.18);
        padding: 0.5rem 0;
        background: #ffffff;
        backdrop-filter: blur(10px);
    }

    .modern-dropdown .dropdown-item {
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        margin: 0.25rem 0.5rem;
        transition: all 0.2s ease;
        color: #374151;
        font-size: 0.875rem;
    }

    .modern-dropdown .dropdown-item:hover {
        background: linear-gradient(135deg, #eef2ff 0%, #f5f3ff 100%);
        transform: translateX(4px);
        color: #1d4ed8;
    }

    /* Ensure header's white !important styles don't bleed into dropdown */
    .activity-table-header .dropdown-menu,
    .activity-table-header .dropdown-menu * {
        color: #374151 !important;
    }
    .activity-table-header .dropdown-menu .dropdown-item:hover {
        color: #1d4ed8 !important;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .desktop-only {
            display: none;
        }

        .mobile-only {
            display: block;
        }

        .activity-table-header {
            padding: 1rem 1.5rem;
        }

        .table-icon {
            width: 40px;
            height: 40px;
            margin-right: 0.75rem;
        }

        .header-actions .gap-2 {
            gap: 0.5rem !important;
        }

        .mobile-activity-container {
            padding: 0.5rem;
        }
    }

    @media (max-width: 576px) {
        .activity-table-header {
            padding: 1rem;
        }

        .header-actions {
            flex-direction: column;
            gap: 0.5rem;
        }

        .mobile-activity-card {
            padding: 0.75rem;
        }
    }

    /* DataTable Controls Styling */
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter {
        padding: 1.5rem 1.5rem 0 1.5rem;
    }

    /* Status indicator positioning */
    /* .status-indicator {
        position: relative !important;
        display: inline-flex !important;
        align-items: center !important;
        background: rgba(255, 255, 255, 0.15) !important;
        padding: 0.5rem 1rem !important;
        border-radius: 20px !important;
        backdrop-filter: blur(10px) !important;
        border: 1px solid rgba(255, 255, 255, 0.2) !important;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1) !important;
    }

    .status-indicator .text-success {
        color: #28a745 !important;
        font-weight: 600 !important;
        font-size: 0.875rem !important;
    } */

    /* Custom DataTable Controls */
    #customPageLength, #customSearch {
        background: rgba(255, 255, 255, 0.1) !important;
        border: 1px solid rgba(255, 255, 255, 0.2) !important;
        color: white !important;
        font-size: 0.875rem;
    }

    #customPageLength:focus, #customSearch:focus {
        background: rgba(255, 255, 255, 0.15) !important;
        border-color: rgba(255, 255, 255, 0.4) !important;
        box-shadow: 0 0 0 0.2rem rgba(255, 255, 255, 0.25) !important;
        outline: none;
    }

    #customPageLength option {
        background: #333 !important;
        color: white !important;
    }

    #customSearch::placeholder {
        color: rgba(255, 255, 255, 0.6) !important;
    }

    /* Override text-muted in header areas */
    .activity-table-header .text-muted {
        color: rgba(255, 255, 255, 0.7) !important;
    }

    .activity-table-header .text-white-50 {
        color: rgba(255, 255, 255, 0.75) !important;
    }

    /* Ensure all header text is visible */
    .activity-table-header,
    .activity-table-header * {
        color: white !important;
    }

    .activity-table-header small {
        color: rgba(255, 255, 255, 0.75) !important;
    }

    /* Hide default DataTable elements */
    .dataTables_length,
    .dataTables_filter,
    .dataTables_info {
        display: none !important;
    }

    /* Ensure table styling is maintained */
    #activityLogsTable_wrapper {
        background: transparent !important;
    }

    .dataTables_paginate {
        margin: 0 !important;
        padding: 0 !important;
    }

    /* Custom Pagination Styling */
    #customPagination {
        min-height: 40px;
        display: flex;
        align-items: center;
        justify-content: flex-end;
    }

    #customPagination .pagination {
        margin: 0 !important;
        display: flex !important;
        list-style: none !important;
        padding: 0 !important;
    }

    #customPagination .page-item {
        display: inline-block !important;
        margin: 0 2px !important;
    }

    #customPagination .page-link {
        background: rgba(255, 255, 255, 0.15) !important;
        border: 2px solid rgba(255, 255, 255, 0.3) !important;
        color: white !important;
        border-radius: 8px !important;
        padding: 0.5rem 0.75rem !important;
        font-size: 0.875rem !important;
        font-weight: 500 !important;
        text-decoration: none !important;
        display: inline-block !important;
        transition: all 0.3s ease !important;
        min-width: 40px !important;
        text-align: center !important;
    }

    #customPagination .page-link:hover {
        background: rgba(255, 255, 255, 0.25) !important;
        border-color: rgba(255, 255, 255, 0.5) !important;
        color: white !important;
        transform: translateY(-1px) !important;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2) !important;
    }

    #customPagination .page-item.active .page-link {
        background: rgba(255, 255, 255, 0.4) !important;
        border-color: rgba(255, 255, 255, 0.6) !important;
        color: white !important;
        font-weight: 700 !important;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3) !important;
    }

    #customPagination .page-item.disabled .page-link {
        background: rgba(255, 255, 255, 0.05) !important;
        border-color: rgba(255, 255, 255, 0.1) !important;
        color: rgba(255, 255, 255, 0.4) !important;
        cursor: not-allowed !important;
        transform: none !important;
        box-shadow: none !important;
    }

    /* Force visibility of pagination */
    .dataTables_paginate {
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
    }
    
    .card-header {
        border-radius: 1rem 1rem 0 0 !important;
        padding: 1.25rem 1.5rem;
    }
    
    .card-body {
        padding: 1.5rem;
    }
    
    /* Activity Log Modal Styles - Enhanced */
    #activityLogDetailsModal .modal-dialog {
        max-width: 95%;
        margin: 1.75rem auto;
    }
    
    #activityLogDetailsModal .modal-content {
        max-height: 90vh;
        overflow: hidden;
        border-radius: 1rem;
        border: none;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }
    
    #activityLogDetailsModal .modal-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
        border: none;
        padding: 1.5rem;
    }
    
    #activityLogDetailsModal .modal-body {
        max-height: calc(90vh - 140px);
        overflow-y: auto;
        padding: 2rem;
    }
    
    #activityLogDetailsModal .modal-footer {
        border: none;
        padding: 1.5rem;
        background-color: #f8f9fa;
    }
    
    #activityLogDetailsModal .btn-close {
        font-size: 1.5rem !important;
        opacity: 1 !important;
        background: none !important;
        border: none !important;
        color: white !important;
        padding: 0.5rem !important;
        border-radius: 50% !important;
        width: 40px !important;
        height: 40px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        transition: all 0.3s ease !important;
        box-shadow: none !important;
        cursor: pointer !important;
        line-height: 1 !important;
    }
    
    #activityLogDetailsModal .btn-close:hover {
        background-color: rgba(255, 255, 255, 0.2) !important;
        transform: scale(1.1) !important;
        opacity: 1 !important;
    }
    
    #activityLogDetailsModal .btn-close:focus {
        box-shadow: 0 0 0 0.25rem rgba(255, 255, 255, 0.25) !important;
        outline: none !important;
        opacity: 1 !important;
    }
    
    #activityLogDetailsModal .btn-close:active {
        transform: scale(0.95) !important;
    }
    
    #activityLogDetailsModal pre {
        white-space: pre-wrap !important;
        word-wrap: break-word !important;
        max-width: 100% !important;
        overflow-x: auto !important;
        font-size: 11px !important;
        line-height: 1.3 !important;
        background: #f8fafc !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 0.5rem !important;
        padding: 0.75rem !important;
    }
    
    @media (max-width: 768px) {
        #activityLogDetailsModal .modal-dialog {
            max-width: 98%;
            margin: 0.5rem auto;
        }
        
        #activityLogDetailsModal .modal-content {
            max-height: 95vh;
        }
        
        #activityLogDetailsModal .modal-body {
            max-height: calc(95vh - 100px);
            padding: 15px;
        }
        
        .form-select, .form-control, .btn {
            min-height: 44px;
            font-size: 16px; /* Prevents zoom on iOS */
        }

        .form-control-lg.form-control{
            border-radius: 0.75rem !important;
        }
    }
    
    /* Modern Filter Container */
    .filter-container {
        position: sticky;
        top: 1rem;
        z-index: 100;
    }
    
    .filter-card {
        background: #ffffff;
        border: none;
        border-radius: 1.5rem;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        overflow: hidden;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .filter-header {
        background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
        color: white;
        padding: 1.5rem 2rem;
        position: relative;
    }
    
    .filter-icon {
        width: 3rem;
        height: 3rem;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 1rem;
        backdrop-filter: blur(4px);
    }
    
    .filter-body {
        padding: 2rem;
    }
    
    .filter-section {
        position: relative;
    }
    
    .filter-section-header {
        display: flex;
        align-items: center;
        font-weight: 600;
        color: #374151;
        margin-bottom: 1rem;
        font-size: 1rem;
    }
    
    .filter-section-header i {
        margin-right: 0.75rem;
        font-size: 1.1rem;
    }
    
    /* Modern Search Input */
    .search-input-wrapper {
        position: relative;
    }
    
    .search-icon {
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: #9ca3af;
        z-index: 10;
    }
    
    .search-input {
        padding-left: 3rem;
        padding-right: 1rem;
        height: 3.5rem;
        border: 2px solid #e5e7eb;
        border-radius: 1rem;
        font-size: 1rem;
        transition: all 0.3s ease;
        background: #f9fafb;
        width: 100%;
    }
    
    .search-input:focus {
        border-color: #4f46e5;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        outline: none;
        background: white;
    }
    
    /* Modern Form Controls */
    .filter-input-group {
        position: relative;
    }
    
    .filter-label {
        display: block;
        font-weight: 600;
        color: #374151;
        margin-bottom: 0.5rem;
        font-size: 0.875rem;
    }
    
    .filter-label i {
        margin-right: 0.5rem;
    }
    
    .modern-select, .modern-input {
        height: 3rem;
        border: 2px solid #e5e7eb;
        border-radius: 0.75rem;
        padding: 0.75rem 1rem;
        font-size: 0.875rem;
        background: #f9fafb;
        transition: all 0.3s ease;
        width: 100%;
    }
    
    .modern-select:focus, .modern-input:focus {
        border-color: #4f46e5;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        outline: none;
        background: white;
    }
    
    .modern-select:hover, .modern-input:hover {
        border-color: #d1d5db;
        background: white;
    }
    
    /* Date Range Picker */
    .date-range-toggle .btn {
        height: 3rem;
        border-radius: 0.75rem;
        background: #f9fafb;
        border: 2px solid #e5e7eb;
        color: #6b7280;
        transition: all 0.3s ease;
    }
    
    .date-range-toggle .btn:hover {
        background: white;
        border-color: #d1d5db;
        color: #374151;
    }
    
    .date-range-picker {
        background: #f8fafc;
        border-radius: 0.75rem;
        padding: 1.5rem;
        border: 1px solid #e2e8f0;
    }
    
    /* Modern Buttons */
    .btn-modern {
        padding: 0.75rem 1.5rem;
        border-radius: 0.75rem;
        font-weight: 600;
        transition: all 0.3s ease;
        border: none;
        position: relative;
        overflow: hidden;
    }
    
    .btn-modern::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
        transition: left 0.5s;
    }
    
    .btn-modern:hover::before {
        left: 100%;
    }
    
    .btn-modern:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }
    
    .filter-actions {
        background: #f8fafc;
        border-radius: 1rem;
        padding: 1.5rem;
        margin-top: 1rem;
    }
    
    .text-purple { color: #7c3aed; }
    
    /* Responsive Filter Adjustments */
    @media (max-width: 768px) {
        .filter-header {
            padding: 1rem 1.5rem;
        }
        
        .filter-body {
            padding: 1.5rem;
        }
        
        .filter-icon {
            width: 2.5rem;
            height: 2.5rem;
            margin-right: 0.75rem;
        }
        
        .search-input {
            height: 3rem;
        }
        
        .modern-select, .modern-input {
            height: 2.75rem;
        }
        
        .btn-modern {
            padding: 0.625rem 1.25rem;
            font-size: 0.875rem;
        }
        
        .filter-actions {
            padding: 1rem;
        }
        
        .filter-actions .d-flex {
            flex-direction: column;
            gap: 1rem;
        }
        
        .filter-actions .d-flex > div {
            width: 100%;
        }
        
        .filter-actions .btn {
            width: 100%;
        }
    }

    /* Export Loader Overlay */
    .export-loader-backdrop {
        position: fixed;
        inset: 0;
        background: rgba(17, 24, 39, 0.45);
        z-index: 2000;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .export-loader-dialog {
        background: #ffffff;
        border-radius: 0.75rem;
        padding: 1.25rem 1.5rem;
        box-shadow: 0 20px 40px rgba(0,0,0,0.25);
        width: 320px;
        text-align: center;
        border: 1px solid #e5e7eb;
    }
    .export-loader-title { font-weight: 700; color: #111827; font-size: 1rem; }
    .export-loader-text { color: #6b7280; font-size: 0.875rem; }
    .export-loader-spinner { color: #4f46e5; }
</style>
@endsection

@section('content')
    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <!-- Page Content -->
        <div class="content container-fluid">
            <!-- Header Section -->
            <div class="header-gradient shadow-lg">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <div class="me-4">
                            <div class="bg-white bg-opacity-20 rounded p-3 d-inline-block" style="background-color: rgb(102 124 231) !important;">
                                <i class="fas fa-shield-halved fa-2x text-white"></i>
                            </div>
                        </div>
                        <div>
                            <h1 class="h2 fw-bold mb-2 text-white">Activity Logs</h1>
                            <p class="mb-0" style="color: rgba(255, 255, 255, 0.8);">
                                <i class="fas fa-clock-rotate-left me-2"></i>
                                System monitoring and activity tracking
                            </p>
                        </div>
                    </div>
                    <div class="text-end">
                        <div class="small mb-1" style="color: rgba(255, 255, 255, 0.8);">
                            <i class="fas fa-user-shield me-2"></i>
                            Super Admin Access
                        </div>
                        <div class="h5 fw-bold text-white mb-1">{{ auth()->user()->name }}</div>
                        <div class="small" style="color: rgba(255, 255, 255, 0.8);">
                            <i class="fas fa-clock me-2"></i>
                            {{ now()->format('Y-m-d H:i:s') }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Analytics Cards -->
            <div class="row mb-4">
                <!-- Total Activities Card -->
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="card border-0 shadow-lg card-hover h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="text-muted small mb-1">Total Activities</p>
                                    <h3 class="fw-bold text-dark mb-0">{{ number_format($totalLogs ?? 31) }}</h3>
                                    <p class="text-muted small mt-1 mb-0">
                                        <i class="fas fa-chart-line me-2"></i>All time
                                    </p>
                                </div>
                                <div class="text-primary">
                                    <i class="fas fa-chart-bar fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Today's Events Card -->
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="card border-0 shadow-lg card-hover h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="text-muted small mb-1">Today's Events</p>
                                    <h3 class="fw-bold text-dark mb-0">{{ number_format($todayLogs ?? 5) }}</h3>
                                    <p class="text-muted small mt-1 mb-0">
                                        <i class="fas fa-calendar-day me-2"></i>Today
                                    </p>
                                </div>
                                <div class="text-success">
                                    <i class="fas fa-calendar-day fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Weekly Activity Card -->
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="card border-0 shadow-lg card-hover h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="text-muted small mb-1">Weekly Activity</p>
                                    <h3 class="fw-bold text-dark mb-0">{{ number_format($weeklyLogs ?? 8) }}</h3>
                                    <p class="text-muted small mt-1 mb-0">
                                        <i class="fas fa-calendar-week me-2"></i>Last 7 days
                                    </p>
                                </div>
                                <div class="text-warning">
                                    <i class="fas fa-calendar-week fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Active Users Card -->
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="card border-0 shadow-lg card-hover h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="text-muted small mb-1">Active Users</p>
                                    <h3 class="fw-bold text-dark mb-0">{{ number_format($activeUsers ?? 1) }}</h3>
                                    <p class="text-muted small mt-1 mb-0">
                                        <i class="fas fa-users me-2"></i>Last 7 days
                                    </p>
                                </div>
                                <div class="text-info">
                                    <i class="fas fa-users fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modern Filter Section -->
            <div class="filter-container mb-4">
                <div class="filter-card">
                    <div class="filter-header d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <div class="filter-icon">
                                <i class="fas fa-filter"></i>
                            </div>
                            <div>
                                <h5 class="mb-0 fw-bold">Smart Filters</h5>
                                <small class="opacity-75">Refine your activity search</small>
                            </div>
                        </div>
                        <button class="btn filter-toggle-btn d-lg-none" type="button" data-toggle="collapse" data-target="#filterCollapse" aria-expanded="true">
                            <i class="fas fa-chevron-down"></i>
                        </button>
                    </div>
                    
                    <div class="collapse show" id="filterCollapse">
                        <div class="filter-body">
                            <!-- Search Section -->
                            <div class="filter-section mb-4">
                                <div class="filter-section-header">
                                    <i class="fas fa-search text-primary"></i>
                                    <span>Search & Filter</span>
                                </div>
                                
                                <div class="row g-3">
                                    <div class="col-12">
                                        <div class="search-input-wrapper">
                                            <i class="fas fa-search search-icon"></i>
                                            <input type="text" class="form-control search-input" id="search_query" placeholder="Search activities, users, descriptions...">
                                            <div class="search-suggestions d-none">
                                                <div class="suggestion-item">Recent searches will appear here</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Filter Grid -->
                            <div class="filter-section mb-4">
                                <div class="filter-section-header">
                                    <i class="fas fa-sliders-h text-success"></i>
                                    <span>Filter Options</span>
                                </div>
                                
                                <div class="row g-3">
                                    <div class="col-lg-3 col-md-6">
                                        <div class="filter-input-group">
                                            <label class="filter-label">
                                                <i class="fas fa-layer-group text-warning"></i>
                                                Category
                                            </label>
                                            <select class="form-select modern-select" id="module_filter">
                                                <option value="">All Categories</option>
                                                @foreach($modules as $module)
                                                    <option value="{{ $module }}">{{ $module }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="col-lg-3 col-md-6">
                                        <div class="filter-input-group">
                                            <label class="filter-label">
                                                <i class="fas fa-bolt text-danger"></i>
                                                Activity Type
                                            </label>
                                            <select class="form-select modern-select" id="activity_type_filter">
                                                <option value="">All Types</option>
                                                @foreach($activityTypes as $type)
                                                    <option value="{{ $type }}">{{ $type }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="col-lg-3 col-md-6">
                                        <div class="filter-input-group">
                                            <label class="filter-label">
                                                <i class="fas fa-user text-info"></i>
                                                User
                                            </label>
                                            <select class="form-select modern-select" id="user_filter">
                                                <option value="">All Users</option>
                                                @foreach($users as $user)
                                                    <option value="{{ $user->user_id }}">{{ $user->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="col-lg-3 col-md-6">
                                        <div class="filter-input-group">
                                            <label class="filter-label">
                                                <i class="fas fa-calendar text-purple"></i>
                                                Date Range
                                            </label>
                                            <div class="date-range-toggle">
                                                <button type="button" class="btn btn-outline-secondary btn-sm w-100" id="dateRangeToggle">
                                                    <i class="fas fa-calendar-alt me-2"></i>Select Date Range
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Date Range Picker (Hidden by default) -->
                                <div class="date-range-picker d-none mt-3" id="dateRangePicker">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="filter-label">From Date</label>
                                            <input type="date" class="form-control modern-input" id="date_from">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="filter-label">To Date</label>
                                            <input type="date" class="form-control modern-input" id="date_to">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Action Buttons -->
                            <div class="filter-actions">
                                <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center">
                                    <div class="d-flex flex-wrap gap-2">
                                        <button type="button" class="btn btn-primary btn-modern" id="apply_filters">
                                            <i class="fas fa-search me-2"></i>Apply Filters
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary btn-modern" id="clear_filters">
                                            <i class="fas fa-times me-2"></i>Clear All
                                        </button>
                                    </div>
                                    <div class="d-flex flex-wrap gap-2">
                                        <button type="button" class="btn btn-outline-warning btn-modern" id="cleanup_old_logs">
                                            <i class="fas fa-broom me-2"></i>Cleanup
                                        </button>
                                        <button type="button" class="btn btn-outline-danger btn-modern" id="clear_all_logs">
                                            <i class="fas fa-trash me-2"></i>Clear All
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>                
        </div>
        <div class="content container-fluid">
            <div class="activity-logs-container">
                <!-- Desktop Table View -->
                <div class="activity-table-container desktop-only">
                    <div class="activity-table-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <div class="table-icon">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                                <div>
                                    <h5 class="mb-0 fw-bold text-white">Activity Timeline</h5>
                                    <small class="text-white-50">Real-time system activity monitoring</small>
                                </div>
                            </div>
                            <div class="header-actions d-flex align-items-center">
                                <!-- Export Dropdown -->
                                <div class="dropdown me-3">
                                    <button class="btn btn-outline-primary btn-modern dropdown-toggle" type="button" id="exportDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="fas fa-download me-2"></i>Export
                                    </button>
                                    <div class="dropdown-menu modern-dropdown" aria-labelledby="exportDropdown">
                                        <a class="dropdown-item export-btn" href="#" data-format="excel">
                                            <i class="fas fa-file-excel me-2 text-success"></i>Excel (.xlsx)
                                        </a>
                                        <a class="dropdown-item export-btn" href="#" data-format="csv">
                                            <i class="fas fa-file-csv me-2 text-info"></i>CSV (.csv)
                                        </a>
                                        <a class="dropdown-item export-btn" href="#" data-format="pdf">
                                            <i class="fas fa-file-pdf me-2 text-danger"></i>PDF (.pdf)
                                        </a>
                                    </div>
                                </div>
                                <!-- <div class="status-indicator">
                                    <div class="status-dot"></div>
                                    <small class="text-success fw-medium">Live</small>
                                </div> -->
                            </div>
                        </div>
                        
                        <!-- Custom DataTable Controls -->
                        <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top" style="border-color: rgba(255,255,255,0.2) !important;">
                            <div class="d-flex align-items-center">
                                <span class="text-white me-2">Show</span>
                                <select class="form-control form-select form-control-sm" id="customPageLength" style="width: auto; background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); color: white;">
                                    <option value="10">10</option>
                                    <option value="25" selected>25</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                    <option value="200">200</option>
                                </select>
                                <span class="text-white ms-2">entries</span>
                            </div>
                            <div class="d-flex align-items-center">
                                <span class="text-white me-2">Search:</span>
                                <input type="text" class="form-control form-control-sm" id="customSearch" placeholder="Search activities..." style="width: 200px; background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); color: white;">
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="activity-table table table-hover" id="activityLogsTable">
                            <thead>
                                <tr>
                                    <th><i class="fas fa-cog me-2"></i>Activity</th>
                                    <th><i class="fas fa-user me-2"></i>User</th>
                                    <th><i class="fas fa-tag me-2"></i>Type</th>
                                    <th><i class="fas fa-network-wired me-2"></i>Network</th>
                                    <th><i class="fas fa-clock me-2"></i>Timestamp</th>
                                    <th><i class="fas fa-search me-2"></i>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- DataTable will populate this -->
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Custom Table Footer with Info -->
                    <div class="d-flex justify-content-between align-items-center p-3" style="background: linear-gradient(135deg, rgba(102, 126, 234, 0.8) 0%, rgba(118, 75, 162, 0.8) 100%); border-top: 1px solid rgba(255,255,255,0.2); min-height: 60px;">
                        <div id="customTableInfo" class="text-white" style="font-size: 0.875rem; font-weight: 500;">
                            <!-- Will be populated by JavaScript -->
                        </div>
                        <div id="customPagination">
                            <!-- DataTable pagination will be moved here -->
                        </div>
                    </div>
                </div>

                <!-- Mobile Card View -->
                <div class="mobile-activity-list mobile-only">
                    <div class="activity-table-header mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <div class="table-icon">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                                <div>
                                    <h5 class="mb-0 fw-bold text-white">Activity Logs</h5>
                                    <small class="text-white-50">System activities</small>
                                </div>
                            </div>
                            <div class="header-actions d-flex align-items-center">
                                <!-- Mobile Export Dropdown -->
                                <div class="dropdown me-2">
                                        <button class="btn btn-outline-primary btn-sm dropdown-toggle" type="button" id="mobileExportDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="fas fa-download me-1"></i>Export
                                    </button>
                                    <div class="dropdown-menu modern-dropdown" aria-labelledby="mobileExportDropdown">
                                        <a class="dropdown-item export-btn" href="#" data-format="excel">
                                            <i class="fas fa-file-excel me-2 text-success"></i>Excel
                                        </a>
                                        <a class="dropdown-item export-btn" href="#" data-format="csv">
                                            <i class="fas fa-file-csv me-2 text-info"></i>CSV
                                        </a>
                                        <a class="dropdown-item export-btn" href="#" data-format="pdf">
                                            <i class="fas fa-file-pdf me-2 text-danger"></i>PDF
                                        </a>
                                    </div>
                                </div>
                                <!-- <div class="status-indicator">
                                    <div class="status-dot"></div>
                                    <small class="text-success fw-medium">Live</small>
                                </div> -->
                            </div>
                        </div>
                    </div>
                    <div id="mobileActivityList" class="mobile-activity-container">
                        <!-- Mobile cards will be populated by JavaScript -->
                        <div class="text-center py-5 text-muted">
                            <div class="loading-spinner">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                            <p class="mt-3">Loading activity logs...</p>
                        </div>
                    </div>
                </div>
            </div>

        <!-- Activity Log Details Modal -->
        <div class="modal fade" id="activityLogDetailsModal" tabindex="-1" role="dialog" aria-labelledby="activityLogDetailsModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content">
                    <div class="modal-header text-white border-0" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <h5 class="modal-title font-weight-bold d-flex align-items-center" id="activityLogDetailsModalLabel">
                            <i class="fas fa-shield-halved me-2"></i>
                            Activity Log Details
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="background: none; border: none; color: white; font-size: 1.5rem; opacity: 1;" onclick="$('#activityLogDetailsModal').modal('hide');">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body p-4">
                        <div id="activityLogDetails">
                            <!-- Content will be loaded dynamically -->
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-0">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal" onclick="closeActivityModal()">
                            <i class="fas fa-times me-2"></i>Close
                        </button>
                        <button type="button" class="btn btn-primary" data-dismiss="modal">
                            <i class="fas fa-check me-2"></i>Done
                        </button>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script type="text/javascript">
    // Global notification function
    function showNotification(type, message) {
        const iconClass = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle';
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        
        const toast = $(`<div class="alert ${alertClass} alert-dismissible fade show position-fixed" style="top: 20px; right: 20px; z-index: 9999;">
            <i class="fas ${iconClass} me-2"></i>${message}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>`);
        
        $('body').append(toast);
        setTimeout(() => toast.alert('close'), type === 'success' ? 3000 : 5000);
    }

    // Export loader overlay helpers
    function showExportLoader(message) {
        const html = `
            <div class="export-loader-backdrop" id="exportLoader">
                <div class="export-loader-dialog">
                    <div class="d-flex justify-content-center mb-3 export-loader-spinner">
                        <div class="spinner-border" role="status" aria-hidden="true"></div>
                    </div>
                    <div class="export-loader-title mb-1">Preparing export…</div>
                    <div class="export-loader-text" id="exportLoaderText">${message || 'This may take a few seconds'}</div>
                </div>
            </div>`;
        if (!document.getElementById('exportLoader')) {
            $('body').append(html);
        } else {
            $('#exportLoaderText').text(message || 'This may take a few seconds');
        }
    }
    function hideExportLoader() {
        $('#exportLoader').remove();
    }

    $(document).ready(function() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Mobile Detection
        const isMobile = window.innerWidth <= 768;
        
        // Initialize DataTable with enhanced mobile support
        const table = $('#activityLogsTable').DataTable({
            lengthMenu: [
                [10, 25, 50, 100, 200],
                [10, 25, 50, 100, 200]
            ],
            pageLength: isMobile ? 10 : 25,
            order: [[4, 'desc']], // Order by timestamp descending
            processing: true,
            serverSide: true,
            responsive: true,
            scrollX: true,
            autoWidth: false,
            dom: 'rtip',
            language: {
                search: "",
                searchPlaceholder: "Search activities...",
                lengthMenu: "_MENU_ per page",
                info: "Showing _START_ to _END_ of _TOTAL_ activities",
                infoEmpty: "No activity logs found",
                infoFiltered: "(filtered from _MAX_ total entries)",
                emptyTable: "No activity logs available",
                zeroRecords: "No matching records found",
                processing: '<div class="d-flex justify-content-center align-items-center py-3"><div class="spinner-border text-primary me-2" role="status"></div>Loading activity logs...</div>'
            },
            ajax: {
                url: "{{ route('activity-logs.data') }}",
                type: "POST",
                dataType: 'json',
                data: function(d) {
                    d.search_query = $('#search_query').val();
                    d.user_filter = $('#user_filter').val();
                    d.activity_type_filter = $('#activity_type_filter').val();
                    d.module_filter = $('#module_filter').val();
                    d.date_from = $('#date_from').val();
                    d.date_to = $('#date_to').val();
                    d._token = $('meta[name="csrf-token"]').attr('content');
                },
                dataSrc: function(json) {
                    // Also populate mobile cards
                    if (isMobile) {
                        populateMobileCards(json.data);
                    }
                    return json.data;
                },
                error: function(xhr, error, thrown) {
                    console.log('DataTables error:', error);
                    console.log('Server response:', xhr.responseText);
                    
                    $('#activityLogsTable_processing').hide();
                    $('#activityLogsTable tbody').html(
                        '<tr><td colspan="6" class="text-center text-danger">' +
                        'Error loading data. Please try refreshing the page.' +
                        '</td></tr>'
                    );
                    
                    // Show error in mobile view too
                    if (isMobile) {
                        $('#mobileActivityList').html(
                            '<div class="text-center py-5 text-danger">' +
                            '<i class="fas fa-exclamation-triangle fa-2x mb-3"></i>' +
                            '<p>Error loading activity logs</p>' +
                            '<button class="btn btn-outline-primary btn-sm" onclick="location.reload()">Retry</button>' +
                            '</div>'
                        );
                    }
                }
            },                            columns: [
                { 
                    data: null,
                    orderable: false, 
                    searchable: false,
                    render: function(data, type, row) {
                        const activityType = row.activity_type ? row.activity_type.toLowerCase() : 'default';
                        let iconClass = 'fa-eye';
                        let colorClass = 'bg-gray-50 text-gray-600 border-gray-200';
                        
                        if (activityType.includes('create') || activityType.includes('add')) {
                            iconClass = 'fa-plus';
                            colorClass = 'bg-green-50 text-green-600 border-green-200';
                        } else if (activityType.includes('update') || activityType.includes('edit')) {
                            iconClass = 'fa-edit';
                            colorClass = 'bg-blue-50 text-blue-600 border-blue-200';
                        } else if (activityType.includes('delete') || activityType.includes('remove')) {
                            iconClass = 'fa-trash';
                            colorClass = 'bg-red-50 text-red-600 border-red-200';
                        } else if (activityType.includes('login') || activityType.includes('auth')) {
                            iconClass = 'fa-sign-in-alt';
                            colorClass = 'bg-blue-50 text-blue-600 border-blue-200';
                        }
                        
                        return `
                            <div class="d-flex align-items-start">
                                <div class="rounded-circle d-flex align-items-center justify-content-center me-3 activity-icon border ${colorClass}" style="width: 40px; height: 40px; flex-shrink: 0;">
                                    <i class="fas ${iconClass}"></i>
                                </div>
                                <div style="word-wrap: break-word; word-break: break-word; white-space: normal; max-width: 200px;">
                                    <div class="fw-medium text-gray-900" style="line-height: 1.3; color: #111827 !important;">${row.description || 'Activity'}</div>
                                    <div class="small text-muted" style="color: #4b5563 !important; font-weight: 500;">${row.module || 'System'}</div>
                                </div>
                            </div>
                        `;
                    }
                },
                { 
                    data: null,
                    render: function(data, type, row) {
                        if (row.user_name && row.user_name !== 'SYSTEM') {
                            const initial = row.user_name ? row.user_name.charAt(0).toUpperCase() : 'U';
                            return `
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center me-3 user-avatar user-avatar-bg-color text-white border border-primary" style="width: 32px; height: 32px; font-size: 12px; font-weight: 600;">
                                        ${initial}
                                    </div>
                                    <div>
                                        <div class="fw-medium text-dark">${row.user_name}</div>
                                        <div class="small text-muted">${row.email || ''}</div>
                                    </div>
                                </div>
                            `;
                        } else {
                            return `
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center me-3 text-white border border-secondary" style="width: 32px; height: 32px; background: linear-gradient(135deg, #6c757d 0%, #495057 100%);">
                                        <i class="fas fa-robot" style="font-size: 12px;"></i>
                                    </div>
                                    <div>
                                        <div class="fw-medium text-dark">SYSTEM</div>
                                        <div class="small text-muted">Automated Process</div>
                                    </div>
                                </div>
                            `;
                        }
                    }
                },
                { 
                    data: 'activity_type',
                    render: function(data, type, row) {
                        if (!data) return 'Unknown';
                        
                        const activityType = data.toLowerCase();
                        let badgeClass = 'badge-default';
                        
                        if (activityType.includes('create') || activityType.includes('add')) {
                            badgeClass = 'badge-created';
                        } else if (activityType.includes('update') || activityType.includes('edit')) {
                            badgeClass = 'badge-updated';
                        } else if (activityType.includes('delete') || activityType.includes('remove')) {
                            badgeClass = 'badge-deleted';
                        } else if (activityType.includes('login') || activityType.includes('auth')) {
                            badgeClass = 'badge-login';
                        }
                        
                        // Format text: Replace underscores with spaces and Title Case
                        let formattedText = data;
                        if (data && typeof data === 'string') {
                            formattedText = data.replace(/_/g, ' ')
                                .toLowerCase()
                                .split(' ')
                                .map(word => word.charAt(0).toUpperCase() + word.slice(1))
                                .join(' ');
                        }
                        
                        return `<span class="badge ${badgeClass}" style="color: #1f2937 !important; font-weight: 600;">${formattedText}</span>`;
                    }
                },
                { 
                    data: 'ip_address',
                    render: function(data, type, row) {
                        if (data && data !== 'N/A' && data !== 'unknown') {
                            return `
                                <div class="d-flex align-items-center">
                                    <span class="badge ip-badge me-2" style="font-size: 0.7rem; padding: 0.25rem 0.5rem;">
                                        <i class="fas fa-user me-2"></i>${data}
                                    </span>
                                    <span class="small text-muted">Client</span>
                                </div>
                            `;
                        } else {
                            return `
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-light text-dark border" style="font-size: 0.7rem; padding: 0.25rem 0.5rem;">
                                        <i class="fas fa-home me-2"></i>Local Access
                                    </span>
                                </div>
                            `;
                        }
                    }
                },
                { 
                    data: 'activity_timestamp',
                    render: function(data, type, row) {
                        if (!data) return '';
                        
                        // Parse the date format from backend (d-m-Y H:i:s)
                        let date;
                        try {
                            // Convert from "26-08-2025 16:54:54" to "2025-08-26 16:54:54" for proper parsing
                            const parts = data.split(' ');
                            if (parts.length === 2) {
                                const dateParts = parts[0].split('-');
                                if (dateParts.length === 3) {
                                    // Rearrange from d-m-Y to Y-m-d
                                    const isoDateString = dateParts[2] + '-' + dateParts[1] + '-' + dateParts[0] + ' ' + parts[1];
                                    date = new Date(isoDateString);
                                } else {
                                    date = new Date(data);
                                }
                            } else {
                                date = new Date(data);
                            }
                        } catch (e) {
                            console.error('Date parsing error:', e, 'Data:', data);
                            return data; // Return original data if parsing fails
                        }
                        
                        // Check if date is valid
                        if (isNaN(date.getTime())) {
                            console.error('Invalid date:', data);
                            return data; // Return original data if invalid
                        }
                        
                        const dateStr = date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
                        const timeStr = date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
                        
                        // Calculate relative time
                        const now = new Date();
                        const diffMs = now - date;
                        const diffHours = Math.floor(diffMs / (1000 * 60 * 60));
                        const diffDays = Math.floor(diffHours / 24);
                        
                        let relativeTime;
                        if (diffDays > 7) {
                            relativeTime = `${diffDays} days ago`;
                        } else if (diffDays > 0) {
                            relativeTime = `${diffDays} day${diffDays > 1 ? 's' : ''} ago`;
                        } else if (diffHours > 0) {
                            relativeTime = `${diffHours} hour${diffHours > 1 ? 's' : ''} ago`;
                        } else {
                            relativeTime = 'Just now';
                        }
                        
                        return `
                            <div>
                                <div class="fw-medium text-dark">${dateStr}</div>
                                <div class="small text-muted">${timeStr}</div>
                                <div class="small text-secondary">${relativeTime}</div>
                            </div>
                        `;
                    }
                },
                { 
                    data: null,
                    orderable: false, 
                    searchable: false,
                    render: function(data, type, row) {
                        return `
                            <button type="button" class="btn btn-sm btn-primary view-details" data-id="${row.id}" style="background: linear-gradient(135deg, #2563eb 0%, #7c3aed 100%); border: none; border-radius: 0.5rem; padding: 0.375rem 0.75rem; transition: all 0.3s ease;">
                                <i class="fas fa-search me-2"></i>View Details
                            </button>
                        `;
                    }
                }
            ],
            responsive: true,
            language: {
                processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw"></i><span class="sr-only">Loading...</span>'
            }
        });

        // Custom DataTable controls
        $('#customPageLength').on('change', function() {
            table.page.len($(this).val()).draw();
        });

        $('#customSearch').on('keyup', function() {
            table.search($(this).val()).draw();
        });

        // Update custom table info on draw
        table.on('draw', function() {
            const info = table.page.info();
            $('#customTableInfo').html(
                `Showing ${info.start + 1} to ${info.end} of ${info.recordsDisplay} entries` +
                (info.recordsTotal !== info.recordsDisplay ? ` (filtered from ${info.recordsTotal} total entries)` : '')
            );
            
            // Move pagination to custom location and ensure visibility
            setTimeout(function() {
                const paginateElement = $('.dataTables_paginate');
                if (paginateElement.length > 0) {
                    paginateElement.appendTo('#customPagination');
                    paginateElement.show();
                    paginateElement.css({
                        'display': 'block',
                        'visibility': 'visible',
                        'opacity': '1'
                    });
                }
            }, 100);
        });

        // Initial pagination setup
        table.on('init', function() {
            setTimeout(function() {
                const paginateElement = $('.dataTables_paginate');
                if (paginateElement.length > 0) {
                    paginateElement.appendTo('#customPagination');
                    paginateElement.show();
                }
            }, 200);
        });

        // Apply filters
        $('#apply_filters').on('click', function() {
            table.draw();
        });

        // Clear filters
        $('#clear_filters').on('click', function() {
            $('#search_query').val('');
            $('#user_filter').val('').trigger('change');
            $('#activity_type_filter').val('').trigger('change');
            $('#module_filter').val('').trigger('change');
            $('#date_from').val('');
            $('#date_to').val('');
            table.draw();
        });

        // Toggle date range picker (custom) for Bootstrap 4
        $('#dateRangeToggle').on('click', function() {
            const picker = $('#dateRangePicker');
            if (picker.hasClass('d-none')) {
                picker.removeClass('d-none').slideDown(200);
            } else {
                picker.addClass('d-none').slideUp(200);
            }
        });

        // Export CSV functionality
        $('#export_csv').on('click', function() {
            const $button = $(this);
            const originalText = $button.html();
            
            // Show loading state
            $button.html('<i class="fas fa-spinner fa-spin me-2"></i>Exporting...');
            $button.prop('disabled', true);
            showExportLoader('Generating CSV file…');
            
            // Prepare data for export
            const exportData = {
                format: 'csv',
                search_query: $('#search_query').val(),
                user_filter: $('#user_filter').val(),
                activity_type_filter: $('#activity_type_filter').val(),
                module_filter: $('#module_filter').val(),
                date_from: $('#date_from').val(),
                date_to: $('#date_to').val(),
                _token: $('meta[name="csrf-token"]').attr('content')
            };
            
            // Remove empty parameters
            Object.keys(exportData).forEach(key => {
                if (!exportData[key]) {
                    delete exportData[key];
                }
            });
            
            // Use AJAX to handle the export
            $.ajax({
                url: "{{ route('activity-logs.export') }}",
                type: "GET",
                data: exportData,
                xhrFields: {
                    responseType: 'blob'
                },
                success: function(data, status, xhr) {
                    // Get filename from header or generate one
                    let filename = 'activity_logs_' + new Date().toISOString().slice(0,19).replace(/:/g, '-') + '.csv';
                    const disposition = xhr.getResponseHeader('Content-Disposition');
                    if (disposition && disposition.indexOf('filename=') !== -1) {
                        filename = disposition.split('filename=')[1].replace(/['"]/g, '');
                    }
                    
                    // Create blob and download
                    const blob = new Blob([data], { type: 'text/csv' });
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = filename;
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(url);
                    document.body.removeChild(a);
                    
                    // Show success message
                    showNotification('success', 'CSV export completed successfully!');
                },
                error: function(xhr, status, error) {
                    console.error('Export error:', error);
                    showNotification('error', 'Export failed. Please try again.');
                },
                complete: function() {
                    // Reset button state
                    $button.html(originalText);
                    $button.prop('disabled', false);
                    hideExportLoader();
                }
            });
        });

        // Cleanup old logs functionality
        $('#cleanup_old_logs').on('click', function() {
            if (confirm('Are you sure you want to cleanup old activity logs? This will PERMANENTLY DELETE logs older than 1 week (hard delete - cannot be restored).')) {
                const $button = $(this);
                const originalText = $button.html();
                
                // Show loading state
                $button.html('<i class="fas fa-spinner fa-spin me-2"></i>Cleaning...');
                $button.prop('disabled', true);
                
                $.ajax({
                    url: "{{ route('activity-logs.cleanup') }}",
                    type: "POST",
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            showNotification('success', 'Cleanup completed! ' + response.deleted_count + ' old logs were removed.');
                            // Refresh the table
                            try {
                                table.draw();
                            } catch (error) {
                                console.error('Error refreshing table:', error);
                            }
                        } else {
                            showNotification('error', response.message || 'Cleanup failed.');
                        }
                        // Reset button state immediately
                        $button.html(originalText);
                        $button.prop('disabled', false);
                    },
                    error: function(xhr, status, error) {
                        console.error('Cleanup error:', error);
                        showNotification('error', 'Cleanup failed. Please try again.');
                        // Reset button state on error
                        $button.html(originalText);
                        $button.prop('disabled', false);
                    }
                });
            }
        });

        // Clear all logs functionality
        $('#clear_all_logs').on('click', function() {
            if (confirm('Are you sure you want to clear ALL activity logs? This will perform a SOFT DELETE (logs can be restored) and create a backup export first. This action cannot be fully undone.')) {
                const $button = $(this);
                const originalText = $button.html();
                
                // Show loading state
                $button.html('<i class="fas fa-spinner fa-spin me-2"></i>Clearing...');
                $button.prop('disabled', true);
                
                $.ajax({
                    url: "{{ route('activity-logs.clear') }}",
                    type: "POST",
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            let message = 'All logs cleared! ' + response.message;
                            if (response.backup_filename) {
                                message += '\n\nBackup created: ' + response.backup_filename;
                            }
                            showNotification('success', message);
                            table.draw(); // Refresh the table
                        } else {
                            showNotification('error', response.message || 'Clear failed.');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Clear error:', error);
                        showNotification('error', 'Clear failed. Please try again.');
                    },
                    complete: function() {
                        // Reset button state
                        $button.html(originalText);
                        $button.prop('disabled', false);
                    }
                });
            }
        });

        // View details
        $(document).on('click', '.view-details', function() {
            const logId = $(this).data('id');
            
            $.ajax({
                url: "{{ route('activity-logs.details') }}",
                type: "POST",
                data: {
                    id: logId,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        displayActivityLogDetails(response.data);
                        $('#activityLogDetailsModal').modal('show');
                    } else {
                        alert('Failed to load activity log details: ' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading details:', error);
                    alert('Error loading activity log details. Please try again.');
                }
            });
        });

        // Export functionality
        $('.export-btn').on('click', function(e) {
            e.preventDefault();
            const format = $(this).data('format');
            const $button = $(this);
            const originalText = $button.html();
            
            console.log('Export clicked, format:', format);
            
            // Show loading state
            $button.html('<i class="fas fa-spinner fa-spin me-2"></i>Exporting...');
            $button.prop('disabled', true);
            const pretty = (format || '').toString().toUpperCase();
            const msg = pretty ? `Generating ${pretty} file…` : 'Preparing export…';
            showExportLoader(msg);
            
            // Prepare data for AJAX request
            const exportData = {
                format: format,
                search_query: $('#search_query').val(),
                user_filter: $('#user_filter').val(),
                activity_type_filter: $('#activity_type_filter').val(),
                module_filter: $('#module_filter').val(),
                date_from: $('#date_from').val(),
                date_to: $('#date_to').val(),
                _token: $('meta[name="csrf-token"]').attr('content')
            };
            
            // Remove empty parameters
            Object.keys(exportData).forEach(key => {
                if (!exportData[key]) {
                    delete exportData[key];
                }
            });
            
            console.log('Export data:', exportData);
            
            // Use AJAX to handle the export with proper authentication
            $.ajax({
                url: "{{ route('activity-logs.export') }}",
                type: "GET",
                data: exportData,
                xhrFields: {
                    responseType: 'blob'
                },
                success: function(data, status, xhr) {
                    console.log('Export success');
                    
                    // Get filename from header or generate one
                    let filename = 'activity_logs_' + new Date().toISOString().slice(0,19).replace(/:/g, '-');
                    const disposition = xhr.getResponseHeader('Content-Disposition');
                    if (disposition && disposition.indexOf('filename=') !== -1) {
                        filename = disposition.split('filename=')[1].replace(/['"]/g, '');
                    } else {
                        // Generate filename based on format
                        switch(format) {
                            case 'excel':
                                filename += '.xlsx';
                                break;
                            case 'csv':
                                filename += '.csv';
                                break;
                            case 'pdf':
                                filename += '.pdf';
                                break;
                            case 'json':
                                filename += '.json';
                                break;
                            default:
                                filename += '.csv';
                        }
                    }
                    
                    // Create blob and download
                    const blob = new Blob([data], { 
                        type: xhr.getResponseHeader('Content-Type') || 'application/octet-stream' 
                    });
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = filename;
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(url);
                    document.body.removeChild(a);
                    
                    // Show success message
                    showNotification('success', 'Export completed successfully!');
                },
                error: function(xhr, status, error) {
                    console.error('Export error:', error, xhr.responseText);
                    
                    let errorMessage = 'Export failed. Please try again.';
                    if (xhr.status === 401 || xhr.status === 403) {
                        errorMessage = 'Access denied. Please check your permissions.';
                    } else if (xhr.status === 404) {
                        errorMessage = 'Export service not found. Please contact administrator.';
                    } else if (xhr.responseText) {
                        try {
                            const response = JSON.parse(xhr.responseText);
                            errorMessage = response.message || errorMessage;
                        } catch (e) {
                            // Ignore JSON parse error
                        }
                    }
                    
                    // Show error message
                    showNotification('error', errorMessage);
                },
                complete: function() {
                    // Reset button state
                    $button.html(originalText);
                    $button.prop('disabled', false);
                    hideExportLoader();
                }
            });
        });

        function displayActivityLogDetails(data) {
            let oldDataHtml = '';
            let newDataHtml = '';
            let changesHtml = '';

            // Pretty JSON formatter with syntax highlighting
            function formatJson(obj, indent = 0) {
                if (obj === null) return '<span class="text-danger">null</span>';
                if (obj === undefined) return '<span class="text-muted">undefined</span>';
                if (typeof obj === 'boolean') return `<span class="text-warning">${obj}</span>`;
                if (typeof obj === 'number') return `<span class="text-info">${obj}</span>`;
                if (typeof obj === 'string') return `<span class="text-success">"${obj.replace(/"/g, '\\"')}"</span>`;

                if (Array.isArray(obj)) {
                    if (obj.length === 0) return '[]';

                    const indentStr = '  '.repeat(indent);
                    const nextIndentStr = '  '.repeat(indent + 1);

                    let result = '[\n';
                    obj.forEach((item, index) => {
                        result += nextIndentStr + formatJson(item, indent + 1);
                        if (index < obj.length - 1) result += ',';
                        result += '\n';
                    });
                    result += indentStr + ']';
                    return result;
                }

                if (typeof obj === 'object') {
                    const keys = Object.keys(obj);
                    if (keys.length === 0) return '{}';

                    const indentStr = '  '.repeat(indent);
                    const nextIndentStr = '  '.repeat(indent + 1);

                    let result = '{\n';
                    keys.forEach((key, index) => {
                        result += nextIndentStr + `<span class="text-primary">"${key}"</span>: ` + formatJson(obj[key], indent + 1);
                        if (index < keys.length - 1) result += ',';
                        result += '\n';
                    });
                    result += indentStr + '}';
                    return result;
                }

                return String(obj);
            }

            // Enhanced comparison with prettier formatting
            function highlightDifferences(oldObj, newObj, path = '') {
                if (oldObj === null || oldObj === undefined) {
                    return '<span class="text-success fw-bold">[NEW]</span> ' + formatJson(newObj);
                }

                if (newObj === null || newObj === undefined) {
                    return '<span class="text-danger fw-bold">[REMOVED]</span> ' + formatJson(oldObj);
                }

                if (typeof oldObj !== typeof newObj) {
                    return '<span class="text-warning fw-bold">[TYPE CHANGED]</span>\n' +
                           '<span class="text-muted">From:</span> ' + formatJson(oldObj) + '\n' +
                           '<span class="text-muted">To:</span> ' + formatJson(newObj);
                }

                if (typeof oldObj === 'object' && oldObj !== null) {
                    if (Array.isArray(oldObj) && Array.isArray(newObj)) {
                        let result = '[\n';
                        let maxLength = Math.max(oldObj.length, newObj.length);
                        const indentStr = '  ';

                        for (let i = 0; i < maxLength; i++) {
                            if (i > 0) result += ',\n';

                            if (i >= oldObj.length) {
                                result += indentStr + '<span class="text-success fw-bold">+ ' + formatJson(newObj[i]) + '</span>';
                            } else if (i >= newObj.length) {
                                result += indentStr + '<span class="text-danger fw-bold">- ' + formatJson(oldObj[i]) + '</span>';
                            } else if (JSON.stringify(oldObj[i]) !== JSON.stringify(newObj[i])) {
                                result += indentStr + '<span class="text-warning fw-bold">~ ' + formatJson(oldObj[i]) + ' → ' + formatJson(newObj[i]) + '</span>';
                            } else {
                                result += indentStr + formatJson(newObj[i]);
                            }
                        }

                        result += '\n]';
                        return result;
                    } else {
                        let result = '{\n';
                        let allKeys = new Set([...Object.keys(oldObj), ...Object.keys(newObj)]);
                        let first = true;
                        const indentStr = '  ';

                        for (let key of allKeys) {
                            if (!first) result += ',\n';
                            first = false;

                            result += indentStr + `<span class="text-primary">"${key}"</span>: `;

                            if (!(key in oldObj)) {
                                result += '<span class="text-success fw-bold">+ ' + formatJson(newObj[key]) + '</span>';
                            } else if (!(key in newObj)) {
                                result += '<span class="text-danger fw-bold">- ' + formatJson(oldObj[key]) + '</span>';
                            } else if (JSON.stringify(oldObj[key]) !== JSON.stringify(newObj[key])) {
                                result += '<span class="text-warning fw-bold">~ ' + formatJson(oldObj[key]) + ' → ' + formatJson(newObj[key]) + '</span>';
                            } else {
                                result += formatJson(newObj[key]);
                            }
                        }

                        result += '\n}';
                        return result;
                    }
                } else if (oldObj !== newObj) {
                    return '<span class="text-warning fw-bold">' + formatJson(oldObj) + ' → ' + formatJson(newObj) + '</span>';
                } else {
                    return formatJson(newObj);
                }
            }

            if (data.old_data && data.new_data) {
                changesHtml = highlightDifferences(data.old_data, data.new_data);

                if (changesHtml.trim() === '') {
                    changesHtml = '<div class="text-muted fst-italic text-center"><i class="fas fa-check-circle me-2"></i>No changes detected</div>';
                }
            } else if (data.old_data) {
                oldDataHtml = '<pre class="bg-dark text-light border rounded p-3 mb-0" style="font-family: \'Monaco\', \'Menlo\', \'Ubuntu Mono\', monospace; font-size: 12px; line-height: 1.4; white-space: pre-wrap;">' + formatJson(data.old_data) + '</pre>';
            } else if (data.new_data) {
                newDataHtml = '<pre class="bg-dark text-light border rounded p-3 mb-0" style="font-family: \'Monaco\', \'Menlo\', \'Ubuntu Mono\', monospace; font-size: 12px; line-height: 1.4; white-space: pre-wrap;">' + formatJson(data.new_data) + '</pre>';
            }
            
            const html = `
                <div class="row g-4">
                    <!-- Basic Information Card -->
                    <div class="col-12">
                        <div class="card border-0 shadow-lg">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0 d-flex align-items-center">
                                    <i class="fas fa-user-circle me-2"></i>Basic Information
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                            <span class="fw-bold text-dark">Timestamp:</span>
                                            <span class="badge bg-dark text-white">${data.activity_timestamp}</span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                            <span class="fw-bold text-dark">User:</span>
                                            <span class="badge bg-primary text-white">${data.user_name || 'N/A'}</span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center py-2">
                                            <span class="fw-bold text-dark">User ID:</span>
                                            <span class="badge bg-secondary text-white font-monospace">${data.user_id || 'N/A'}</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                            <span class="fw-bold text-dark">Email:</span>
                                            <span class="badge bg-info text-dark">${data.email || 'N/A'}</span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                            <span class="fw-bold text-dark">Phone:</span>
                                            <span class="badge bg-warning text-dark">${data.phone_number || 'N/A'}</span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center py-2">
                                            <span class="fw-bold text-dark">Role:</span>
                                            <span class="badge bg-success text-white">${data.role_name || 'N/A'}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Activity Information Card -->
                    <div class="col-12">
                        <div class="card border-0 shadow-lg">
                            <div class="card-header bg-warning text-dark">
                                <h6 class="mb-0 d-flex align-items-center">
                                    <i class="fas fa-bolt me-2"></i>Activity Information
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                            <span class="fw-bold text-dark">Activity Type:</span>
                                            <span class="badge bg-success text-white">${data.activity_type}</span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center py-2">
                                            <span class="fw-bold text-dark">Module:</span>
                                            <span class="badge bg-info text-dark">${data.module}</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                            <span class="fw-bold text-dark">IP Address:</span>
                                            <span class="badge bg-secondary text-white font-monospace">${data.ip_address || 'N/A'}</span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center py-2">
                                            <span class="fw-bold text-dark">Session ID:</span>
                                            <span class="badge bg-dark text-white font-monospace small">${data.session_id || 'N/A'}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Description Card -->
                    <div class="col-12">
                        <div class="card border-0 shadow-lg">
                            <div class="card-header bg-info text-white">
                                <h6 class="mb-0 d-flex align-items-center">
                                    <i class="fas fa-comment-alt me-2"></i>Description
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-info mb-0">
                                    <p class="mb-0">${data.description}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Data Comparison Card -->
                    <div class="col-12">
                        <div class="card border-0 shadow-lg">
                            <div class="card-header bg-success text-white">
                                <h6 class="mb-0 d-flex align-items-center">
                                    <i class="fas fa-code-branch me-2"></i>Data Changes
                                </h6>
                            </div>
                            <div class="card-body">
                                ${changesHtml ? `
                                <div class="mb-3">
                                    <div class="d-flex justify-content-center mb-3">
                                        <div class="legend p-3 bg-light rounded border">
                                            <small class="text-muted me-3">
                                                <span class="badge bg-success me-1">&nbsp;</span>
                                                <strong>Added</strong>
                                            </small>
                                            <small class="text-muted me-3">
                                                <span class="badge bg-danger me-1">&nbsp;</span>
                                                <strong>Removed</strong>
                                            </small>
                                            <small class="text-muted">
                                                <span class="badge bg-warning me-1">&nbsp;</span>
                                                <strong>Changed</strong>
                                            </small>
                                        </div>
                                    </div>
                                    <div class="border rounded p-0 bg-dark text-light position-relative" style="max-height: 500px; overflow-y: auto;">
                                        <div class="position-absolute top-0 end-0 p-2">
                                            <small class="text-muted">
                                                <i class="fas fa-code me-1"></i>JSON View
                                            </small>
                                        </div>
                                        <pre class="mb-0 p-3" style="font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace; font-size: 12px; line-height: 1.4; white-space: pre-wrap; margin-top: 30px;">${changesHtml}</pre>
                                    </div>
                                </div>
                                ` : `
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <h6 class="text-danger d-flex align-items-center mb-3">
                                            <i class="fas fa-history me-2"></i>Previous Data
                                        </h6>
                                        <div class="border border-danger rounded p-0 bg-dark position-relative" style="max-height: 300px; overflow-y: auto;">
                                            <div class="position-absolute top-0 end-0 p-2">
                                                <small class="text-muted">
                                                    <i class="fas fa-code me-1"></i>JSON View
                                                </small>
                                            </div>
                                            ${oldDataHtml || '<div class="text-muted fst-italic d-flex align-items-center justify-content-center h-100"><i class="fas fa-info-circle me-2"></i>No previous data</div>'}
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <h6 class="text-success d-flex align-items-center mb-3">
                                            <i class="fas fa-plus-circle me-2"></i>New Data
                                        </h6>
                                        <div class="border border-success rounded p-0 bg-dark position-relative" style="max-height: 300px; overflow-y: auto;">
                                            <div class="position-absolute top-0 end-0 p-2">
                                                <small class="text-muted">
                                                    <i class="fas fa-code me-1"></i>JSON View
                                                </small>
                                            </div>
                                            ${newDataHtml || '<div class="text-muted fst-italic d-flex align-items-center justify-content-center h-100"><i class="fas fa-info-circle me-2"></i>No new data</div>'}
                                        </div>
                                    </div>
                                </div>
                                `}
                            </div>
                        </div>
                    </div>
                    
                    <!-- User Agent Card -->
                    <div class="col-12">
                        <div class="card border-0 shadow-lg">
                            <div class="card-header bg-secondary text-white">
                                <h6 class="mb-0 d-flex align-items-center">
                                    <i class="fas fa-desktop me-2"></i>User Agent
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="bg-light border rounded p-3">
                                    <code class="small text-muted">${data.user_agent || 'N/A'}</code>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            $('#activityLogDetails').html(html);
        }

        // Mobile Card Population Function
        function populateMobileCards(data) {
            const mobileContainer = $('#mobileActivityList');
            
            if (!data || data.length === 0) {
                mobileContainer.html(
                    '<div class="text-center py-5 text-muted">' +
                    '<i class="fas fa-inbox fa-2x mb-3"></i>' +
                    '<p>No activity logs found</p>' +
                    '</div>'
                );
                return;
            }

            let cardsHtml = '';
            data.forEach(function(item) {
                const activityType = item.activity_type ? item.activity_type.toLowerCase() : 'default';
                let typeColor = 'secondary';
                let typeIcon = 'fa-eye';
                
                if (activityType.includes('create') || activityType.includes('add')) {
                    typeColor = 'success';
                    typeIcon = 'fa-plus';
                } else if (activityType.includes('update') || activityType.includes('edit')) {
                    typeColor = 'warning';
                    typeIcon = 'fa-edit';
                } else if (activityType.includes('delete') || activityType.includes('remove')) {
                    typeColor = 'danger';
                    typeIcon = 'fa-trash';
                } else if (activityType.includes('login') || activityType.includes('auth')) {
                    typeColor = 'info';
                    typeIcon = 'fa-sign-in-alt';
                }

                const timestamp = moment(item.created_at).format('MMM DD, YYYY HH:mm');
                const timeAgo = moment(item.created_at).fromNow();
                
                cardsHtml += `
                    <div class="mobile-activity-card" data-id="${item.id}">
                        <div class="mobile-activity-header">
                            <div class="mobile-activity-title">
                                <i class="fas ${typeIcon} me-2 text-${typeColor}"></i>
                                ${item.activity_name || 'Activity'}
                            </div>
                            <span class="mobile-activity-type bg-${typeColor} text-white">
                                ${item.activity_type || 'Unknown'}
                            </span>
                        </div>
                        <div class="mobile-activity-details">
                            <div class="mb-1">
                                <i class="fas fa-user me-2"></i>
                                <strong>User:</strong> ${item.user_name || 'System'}
                            </div>
                            ${item.ip_address ? `
                            <div class="mb-1">
                                <i class="fas fa-network-wired me-2"></i>
                                <strong>IP:</strong> ${item.ip_address}
                            </div>
                            ` : ''}
                            ${item.user_agent ? `
                            <div class="mb-1">
                                <i class="fas fa-desktop me-2"></i>
                                <strong>Device:</strong> ${item.user_agent.substring(0, 50)}...
                            </div>
                            ` : ''}
                        </div>
                        <div class="mobile-activity-footer">
                            <div class="mobile-activity-time">
                                <i class="fas fa-clock me-1"></i>
                                ${timestamp} (${timeAgo})
                            </div>
                            <button class="btn btn-sm btn-outline-primary view-details-mobile" data-id="${item.id}">
                                <i class="fas fa-eye me-1"></i>Details
                            </button>
                        </div>
                    </div>
                `;
            });

            mobileContainer.html(cardsHtml);
            
            // Bind click events for mobile view details
            $('.view-details-mobile').on('click', function() {
                const id = $(this).data('id');
                viewActivityDetails(id);
            });
        }

        // Enhanced responsive behavior
        function handleResponsiveChanges() {
            const currentWidth = window.innerWidth;
            
            if (currentWidth <= 768) {
                // Mobile view
                $('.desktop-only').hide();
                $('.mobile-only').show();
                
                // Refresh mobile data if table exists
                if (typeof table !== 'undefined') {
                    table.ajax.reload(null, false);
                }
            } else {
                // Desktop view
                $('.desktop-only').show();
                $('.mobile-only').hide();
            }
        }

        // Handle window resize
        $(window).on('resize', debounce(handleResponsiveChanges, 250));
        
        // Debounce function
        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }

        // Initialize responsive behavior
        handleResponsiveChanges();
    });
</script>
<script type="text/javascript">
    $(document).ready(function() {
        // Session expiry warning (client-side)
        let sessionLifetimeMinutes = 480; // Match .env SESSION_LIFETIME
        let warningMinutes = 10; // Warn 10 minutes before expiry
        let lastActivity = Date.now();
        let warningShown = false;

        function showSessionWarning() {
            if (!warningShown) {
                warningShown = true;
                $('body').append(`
                    <div class="modal fade" id="sessionExpiryModal" tabindex="-1" role="dialog" aria-labelledby="sessionExpiryModalLabel" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header bg-danger text-white">
                                    <h5 class="modal-title" id="sessionExpiryModalLabel">
                                        <i class="fas fa-exclamation-triangle me-2"></i>Session Expiry Warning
                                    </h5>
                                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <p>Your session will expire soon due to inactivity.<br>
                                    Please save your work and refresh or re-login to avoid losing progress.</p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-primary" onclick="location.reload()">Refresh Now</button>
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Dismiss</button>
                                </div>
                            </div>
                        </div>
                    </div>
                `);
                $('#sessionExpiryModal').modal('show');
            }
        }

        function checkSessionExpiry() {
            let now = Date.now();
            let elapsedMinutes = (now - lastActivity) / 1000 / 60;
            if (elapsedMinutes > (sessionLifetimeMinutes - warningMinutes)) {
                showSessionWarning();
            }
            if (elapsedMinutes > sessionLifetimeMinutes) {
                window.location.href = '/logout';
            }
        }

        // Reset lastActivity on user interaction
        $(document).on('mousemove keydown click scroll', function() {
            lastActivity = Date.now();
        });
        setInterval(checkSessionExpiry, 60000); // Check every minute

        // Modal close function - updated for Bootstrap 4
        window.closeActivityModal = function() {
            $('#activityLogDetailsModal').modal('hide');
        };

        // Ensure modal can be closed with escape key and backdrop click
        $('#activityLogDetailsModal').on('hidden.bs.modal', function () {
            $('#activityLogDetails').html('');
        });
        
    });
</script>
@endsection

