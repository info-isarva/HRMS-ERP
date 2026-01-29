@extends('layouts.master')
@section('title', 'Increments & Promotions')
@section('content')
<style>
    /* Page Header Card */
    .page-header-card {
        background: white;
        border-radius: 1rem;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        margin-bottom: 2rem;
    }
    
    .page-header-gradient {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 2.5rem 2rem;
        position: relative;
    }
    
    .page-header-pattern {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.05);
    }
    
    .page-header-circle-1 {
        position: absolute;
        top: -1rem;
        right: -1rem;
        width: 6rem;
        height: 6rem;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
    }
    
    .page-header-circle-2 {
        position: absolute;
        bottom: -1rem;
        left: -1rem;
        width: 8rem;
        height: 8rem;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
    }
    
    .page-header-icon-box {
        width: 4rem;
        height: 4rem;
        background: rgba(255, 255, 255, 0.2);
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .page-header-title {
        font-size: 1.875rem;
        font-weight: 700;
        color: white;
        margin-bottom: 0.5rem;
    }
    
    .page-header-subtitle {
        font-size: 1rem;
        color: rgba(255, 255, 255, 0.9);
        margin: 0;
    }
    
    .page-header-stats {
        text-align: right;
    }
    
    .page-header-stats-label {
        font-size: 0.875rem;
        color: rgba(255, 255, 255, 0.9);
        margin-bottom: 0.25rem;
    }
    
    .page-header-stats-value {
        font-size: 1.875rem;
        font-weight: 700;
        color: white;
    }
    
    .page-header-stats-icon {
        width: 5rem;
        height: 5rem;
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    /* Modern Table Card */
    .table-card {
        background: white;
        border-radius: 1rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
        overflow: hidden;
        border: 1px solid #e5e7eb;
    }
    
    .table-card .table {
        margin-bottom: 0;
        width: 100% !important;
    }
    
    .table thead th {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white !important;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
        border: none;
        padding: 1rem 0.75rem;
        white-space: nowrap;
    }
    
    .table tbody tr {
        transition: background-color 0.2s ease;
        border-bottom: 1px solid #f3f4f6;
    }
    
    .table tbody tr:hover {
        background: #f9fafb !important;
    }
    
    /* Modern Filter Card */
    .filter-card {
        background: white;
        border-radius: 1rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
        padding: 1.5rem;
        margin-bottom: 2rem;
        border: 1px solid #e5e7eb;
    }
    
    .filter-card .form-control,
    .filter-card .form-control:focus {
        border: 2px solid #e5e7eb;
        border-radius: 0.5rem;
        padding: 0.625rem 1rem;
        font-size: 0.875rem;
        transition: all 0.2s ease;
    }
    
    .filter-card .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }
    
    .filter-card .btn {
        border-radius: 0.5rem;
        padding: 0.625rem 1.5rem;
        font-weight: 500;
        transition: all 0.2s ease;
    }
    
    .filter-card .btn-success {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
    }
    
    .filter-card .btn-success:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
    }

    .table tbody td {
        padding: 1rem 0.75rem;
        vertical-align: middle;
        font-size: 0.875rem;
        color: #374151;
    }
    
    /* DataTables Custom Styling */
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter,
    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_paginate {
        padding: 1rem;
    }
    
    .dataTables_wrapper .dataTables_filter input {
        border: 2px solid #e5e7eb;
        border-radius: 0.5rem;
        padding: 0.5rem 1rem;
        margin-left: 0.5rem;
    }
    
    .dataTables_wrapper .dataTables_filter input:focus {
        border-color: #667eea;
        outline: none;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }
    
    .dataTables_wrapper .dataTables_length select {
        border: 2px solid #e5e7eb;
        border-radius: 0.5rem;
        padding: 0.375rem 2rem 0.375rem 0.75rem;
        margin: 0 0.5rem;
    }
    
    .dataTables_wrapper .dataTables_paginate .paginate_button {
        padding: 0.375rem 0.75rem;
        margin: 0 2px;
        border-radius: 0.375rem;
        border: 1px solid #e5e7eb;
        background: white;
    }
    
    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white !important;
        border-color: #667eea;
    }
    
    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white !important;
        border-color: #667eea;
    }
    
    .dataTables_wrapper .dataTables_paginate .paginate_button.disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    /* Common Styles */
    .btn {
        border-radius: 0.5rem;
        padding: 0.625rem 1.5rem;
        font-weight: 500;
        transition: all 0.2s ease;
    }
    
    .btn-light {
        background: rgba(255, 255, 255, 0.9);
        border: none;
        color: #4b5563;
    }
    
    .btn-light:hover {
        background: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        color: #1f2937;
    }

    /* Employee Avatar */
    .employee-info {
        display: flex;
        align-items: center;
    }
    
    .employee-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 600;
        margin-right: 0.75rem;
        font-size: 0.875rem;
    }

    .employee-avatar img {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        object-fit: cover;
    }

    /* Page Header Card */
    .page-header-card {
        background: white;
        border-radius: 1rem;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        margin-bottom: 2rem;
    }
    
    .page-header-gradient {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 2.5rem 2rem;
        position: relative;
    }
    
    .page-header-pattern {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.05);
    }
    
    .page-header-circle-1 {
        position: absolute;
        top: -1rem;
        right: -1rem;
        width: 6rem;
        height: 6rem;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
    }
    
    .page-header-circle-2 {
        position: absolute;
        bottom: -1rem;
        left: -1rem;
        width: 8rem;
        height: 8rem;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
    }
    
    .page-header-icon-box {
        width: 4rem;
        height: 4rem;
        background: rgba(255, 255, 255, 0.2);
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .page-header-title {
        font-size: 1.875rem;
        font-weight: 700;
        color: white;
        margin-bottom: 0.5rem;
    }
    
    .page-header-subtitle {
        font-size: 1rem;
        color: rgba(255, 255, 255, 0.9);
        margin: 0;
    }
    
    .page-header-stats {
        text-align: right;
    }
    
    .page-header-stats-label {
        font-size: 0.875rem;
        color: rgba(255, 255, 255, 0.9);
        margin-bottom: 0.25rem;
    }
    
    .page-header-stats-value {
        font-size: 1.875rem;
        font-weight: 700;
        color: white;
    }
    
    .page-header-stats-icon {
        width: 5rem;
        height: 5rem;
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    /* Modern Filter Card */
    .filter-card {
        background: white;
        border-radius: 1rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
        padding: 1.5rem;
        margin-bottom: 2rem;
        border: 1px solid #e5e7eb;
    }
    
    .filter-card .form-control,
    .filter-card .form-control:focus {
        border: 2px solid #e5e7eb;
        border-radius: 0.5rem;
        padding: 0.625rem 1rem;
        font-size: 0.875rem;
        transition: all 0.2s ease;
    }
    
    .filter-card .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }
    
    /* Global Button Styling */
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
        color: white;
    }

    .btn-secondary {
        background: #6c757d;
        border: none;
        color: white;
    }

    .btn-secondary:hover {
        background: #5a6268;
        transform: translateY(-2px);
        color: white;
    }

    @media (max-width: 991px) {
        .filter-card .row > [class*="col-"] {
            margin-bottom: 1rem;
        }
    }
    
    /* Modern Table Card */
    .table-card {
        background: white;
        border-radius: 1rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
        overflow: hidden;
        border: 1px solid #e5e7eb;
    }
    
    .table-card .table {
        margin-bottom: 0;
        width: 100% !important;
    }
    
    .table thead th {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white !important;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
        border: none;
        padding: 1rem 0.75rem;
        white-space: nowrap;
    }
    
    .table tbody tr {
        transition: background-color 0.2s ease;
        border-bottom: 1px solid #f3f4f6;
    }
    
    .table tbody tr:hover {
        background: #f9fafb !important;
    }
    
    .table tbody td {
        padding: 1rem 0.75rem;
        vertical-align: middle;
        font-size: 0.875rem;
        color: #374151;
    }
    
    /* DataTables Custom Styling */
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter,
    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_paginate {
        padding: 1rem;
    }
    
    .dataTables_wrapper .dataTables_filter input {
        border: 2px solid #e5e7eb;
        border-radius: 0.5rem;
        padding: 0.5rem 1rem;
        margin-left: 0.5rem;
    }
    
    .dataTables_wrapper .dataTables_filter input:focus {
        border-color: #667eea;
        outline: none;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }
    
    .dataTables_wrapper .dataTables_length select {
        border: 2px solid #e5e7eb;
        border-radius: 0.5rem;
        padding: 0.375rem 2rem 0.375rem 0.75rem;
        margin: 0 0.5rem;
    }
    
    .dataTables_wrapper .dataTables_paginate .paginate_button {
        padding: 0.375rem 0.75rem;
        margin: 0 2px;
        border-radius: 0.375rem;
        border: 1px solid #e5e7eb;
        background: white;
    }
    
    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white !important;
        border-color: #667eea;
    }
    
    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white !important;
        border-color: #667eea;
    }
    
    .dataTables_wrapper .dataTables_paginate .paginate_button.disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
    
    /* Employee Avatar */
    .employee-info {
        display: flex;
        align-items: center;
    }
    
    .employee-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 600;
        margin-right: 0.75rem;
        font-size: 0.875rem;
    }
    
    .employee-details .employee-name {
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 2px;
    }
    
    .employee-details .employee-name a {
        color: #667eea;
        text-decoration: none;
        transition: all 0.2s ease;
    }
    
    .employee-details .employee-name a:hover {
        color: #764ba2;
        text-decoration: underline;
    }
    
    .employee-details .employee-id {
        font-size: 0.75rem;
        color: #6b7280;
    }
    
    /* Modern Badges */
    .badge {
        padding: 0.375rem 0.75rem;
        font-weight: 500;
        border-radius: 0.375rem;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .bg-success {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
    }
    
    .bg-danger {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
    }
    
    .bg-warning text-dark {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: white;
    }
    
    .bg-info text-dark {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        color: white;
    }
    
    /* Action Buttons */
    .action-buttons {
        display: flex;
        gap: 0.5rem;
    }
    
    .btn-action {
        width: 32px;
        height: 32px;
        border-radius: 0.375rem;
        display: flex;
        align-items: center;
        justify-content: center;
        border: none;
        transition: all 0.2s ease;
        font-size: 0.875rem;
    }
    
    .btn-action:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    }
    
    .btn-action-edit {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        color: white;
    }
    
    .btn-action-delete {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
    }
    
    /* Search Input with Icon */
    .search-wrapper {
        position: relative;
    }
    
    .search-wrapper i {
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: #9ca3af;
        z-index: 10;
        pointer-events: none;
    }
    
    .search-wrapper .form-control {
        padding-left: 2.75rem !important;
    }
    
    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
    }
    
    .empty-state-icon {
        font-size: 4rem;
        color: #d1d5db;
        margin-bottom: 1rem;
    }
    
    .empty-state-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #4b5563;
        margin-bottom: 0.5rem;
    }
    
    .empty-state-text {
        color: #6b7280;
    }
    
    /* Improved Filter Card Responsive Layout */
    @media (max-width: 1199px) {
        .filter-card .row > [class*="col-"] {
            margin-bottom: 1rem;
        }
        
        .filter-card .row > [class*="col-"]:last-child {
            margin-bottom: 0;
            margin-top: 0.5rem;
        }
        
        .filter-card .btn-group-responsive {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        
        .filter-card .btn-group-responsive .btn {
            flex: 1;
            min-width: 120px;
        }
    }
    
    @media (max-width: 991px) {
        .filter-card .row > [class*="col-"] {
            margin-bottom: 1rem;
        }
        
        .filter-card .row > [class*="col-"]:last-child {
            margin-top: 0;
        }
        
        .filter-card .btn-group-responsive {
            justify-content: flex-start;
        }
    }
    
    @media (max-width: 767px) {
        .filter-card .btn-group-responsive {
            flex-direction: column;
        }
        
        .filter-card .btn-group-responsive .btn {
            width: 100%;
        }
    }
    
    /* Mobile Responsive Styles */
    @media (max-width: 1200px) {
        .page-header-gradient {
            padding: 2rem 1.5rem;
        }
        
        .page-header-title {
            font-size: 1.625rem;
        }
        
        .table thead th,
        .table tbody td {
            padding: 0.875rem 0.625rem;
            font-size: 0.8125rem;
        }
        
        .employee-avatar {
            width: 35px;
            height: 35px;
            font-size: 0.8125rem;
        }
        
        .btn-action {
            width: 28px;
            height: 28px;
            font-size: 0.8125rem;
        }
    }
    
    @media (max-width: 1024px) {
        .table-card {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .table {
            min-width: 900px;
        }

        .table thead th,
        .table tbody td {
            padding: 0.75rem 0.5rem;
            font-size: 0.8125rem;
        }

        .employee-info {
            min-width: 180px;
        }

        .employee-avatar {
            width: 32px;
            height: 32px;
            font-size: 0.75rem;
            margin-right: 0.5rem;
        }

        .badge {
            font-size: 0.6875rem;
            padding: 0.25rem 0.5rem;
        }

        .btn-action {
            width: 26px;
            height: 26px;
            font-size: 0.75rem;
        }
    }
    
    @media (max-width: 992px) {
        .page-header-gradient {
            padding: 1.5rem 1.25rem;
        }
        
        .page-header-title {
            font-size: 1.5rem;
        }
        
        .page-header-subtitle {
            font-size: 0.9rem;
        }
        
        .filter-card {
            padding: 1.25rem;
        }
        
        /* Start showing table as cards on tablet */
        .table-card {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        
        .table {
            min-width: 900px;
        }
        
        .table thead th,
        .table tbody td {
            padding: 0.75rem 0.5rem;
            font-size: 0.8125rem;
        }
        
        .employee-info {
            min-width: 180px;
        }
        
        .employee-avatar {
            width: 32px;
            height: 32px;
            font-size: 0.75rem;
            margin-right: 0.5rem;
        }
        
        .badge {
            font-size: 0.6875rem;
            padding: 0.25rem 0.5rem;
        }
        
        .btn-action {
            width: 26px;
            height: 26px;
            font-size: 0.75rem;
        }
    }
    
    @media (max-width: 768px) {
        .page-header-gradient {
            padding: 1.25rem 1rem;
        }
        
        .page-header-title {
            font-size: 1.375rem;
        }
        
        .page-header-icon-box {
            width: 3rem;
            height: 3rem;
            margin-right: 1rem !important;
        }
        
        .page-header-icon-box i {
            font-size: 1.25rem !important;
        }
        
        .filter-card {
            padding: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .filter-card .form-control {
            font-size: 0.8125rem;
        }
        
        .filter-card .btn {
            font-size: 0.8125rem;
            padding: 0.5rem 1.25rem;
        }
        
        /* Mobile Card Layout for Table */
        .table-card {
            padding: 0;
        }
    }
    
    @media (max-width: 576px) {
        .container-fluid {
            padding-left: 1rem;
            padding-right: 1rem;
        }
        
        .page-header-gradient {
            padding: 1rem;
        }
        
        .page-header-title {
            font-size: 1.25rem;
        }
        
        .page-header-subtitle {
            font-size: 0.8125rem;
        }
        
        .page-header-icon-box {
            width: 2.5rem;
            height: 2.5rem;
            margin-right: 0.75rem !important;
        }
        
        .page-header-icon-box i {
            font-size: 1rem !important;
        }
        
        .filter-card {
            padding: 0.875rem;
            margin-bottom: 1.25rem;
        }
        
        .filter-card .form-control {
            font-size: 0.8125rem;
            padding: 0.5rem 0.75rem;
        }
        
        .filter-card .btn {
            font-size: 0.8125rem;
            padding: 0.5rem 1rem;
        }
        
        .filter-card .search-wrapper i {
            left: 0.75rem;
        }
        
        .filter-card .search-wrapper .form-control {
            padding-left: 2.5rem !important;
        }
    }
    
    @media (max-width: 480px) {
        .container-fluid {
            padding-left: 0.75rem;
            padding-right: 0.75rem;
        }
        
        .page-header-gradient {
            padding: 0.875rem;
        }
        
        .filter-card {
            padding: 0.75rem;
            margin-bottom: 1rem;
        }
        
        .employee-card-avatar {
            width: 36px;
            height: 36px;
            font-size: 0.8125rem;
            margin-right: 0.625rem;
        }
    }
    
    .employee-avatar img {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        object-fit: cover;
    }

    /* Select2 Custom Styles to Match Filter Card Inputs */
    .filter-card .select2-container .select2-selection--single {
        height: 45px; /* Matches form-control height (approx 21px text + 20px padding + 4px border) */
        border: 2px solid #e5e7eb;
        border-radius: 0.5rem;
        display: flex;
        align-items: center;
        transition: all 0.2s ease;
    }

    .filter-card .select2-container--default .select2-selection--single .select2-selection__rendered {
        padding-left: 1rem;
        padding-right: 2rem;
        color: #1f2937; /* Match body text color */
        font-size: 0.875rem;
        line-height: normal; /* Reset line-height to allow flex centering */
    }

    .filter-card .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 43px; /* Full height minus borders */
        right: 0.5rem;
        top: 0;
    }

    /* Focus state */
    .filter-card .select2-container--open .select2-selection--single {
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }
</style>

<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">
    
        <!-- Modern Page Header -->
        <div class="page-header-card">
            <div class="page-header-gradient">
                <div class="page-header-pattern"></div>
                <div class="page-header-circle-1"></div>
                <div class="page-header-circle-2"></div>
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <div class="page-header-icon-box">
                            <i class="fas fa-level-up-alt fa-lg" style="color: rgba(255,255,255,0.9);"></i>
                        </div>
                        <div class="ms-3">
                            <h1 class="page-header-title">Increments & Promotions</h1>
                            <p class="page-header-subtitle">Manage employee salary revisions and promotions</p>
                        </div>
                    </div>
                    <!-- Stats Section -->
                    <div class="d-flex align-items-center text-end d-none d-md-flex">
                        <div class="page-header-stats me-3 text-white">
                            <p class="page-header-stats-label mb-0" style="opacity: 0.9; font-size: 0.875rem;">Total Records</p>
                            <p class="page-header-stats-value mb-0 fw-bold" style="font-size: 1.75rem;">{{ $increments->count() }}</p>
                        </div>
                        <div class="page-header-stats-icon p-2 rounded" style="background: rgba(255,255,255,0.2); width: auto; height: auto;">
                            <i class="fas fa-chart-line text-white" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="p-3 d-flex justify-content-between align-items-center flex-wrap gap-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Increments & Promotions</li>
                    </ol>
                </nav>
                <div class="d-flex gap-2">
                    <a href="{{ route('increments.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i> New Increment/Promotion
                    </a>
                </div>
            </div>
        </div>
        <!-- /Modern Page Header -->

        <!-- Modern Filter Card -->
        <div class="filter-card">
            <div class="row align-items-end">
                <div class="col-xl-3 col-lg-4 col-md-6 col-12 mb-3">
                    <label class="font-weight-600 text-muted mb-2" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">
                        <i class="fas fa-search me-1"></i> Employee Name
                    </label>
                    <div class="search-wrapper">
                        <i class="fas fa-search"></i>
                        <input type="text" id="search_name" class="form-control" placeholder="Search by Name">
                    </div>
                </div>
                <!-- Matches Employee List: col-xl-2 for dropdowns -->
                <div class="col-xl-2 col-lg-4 col-md-6 col-12 mb-3">
                    <label class="font-weight-600 text-muted mb-2" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">
                        <i class="fas fa-building me-1"></i> Department
                    </label>
                    <select class="form-control form-select select" id="search_dept">
                        <option value="">All Departments</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->department }}">{{ $dept->department }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-xl-2 col-lg-4 col-md-6 col-12 mb-3">
                    <label class="font-weight-600 text-muted mb-2" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">
                        <i class="fas fa-briefcase me-1"></i> Designation
                    </label>
                    <select class="form-control form-select select" id="search_desg">
                        <option value="">All Designations</option>
                        @foreach($designations as $desg)
                            <option value="{{ $desg->position }}">{{ $desg->position }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-xl-3 col-lg-8 col-md-12 col-12 mb-3">
                    <div class="btn-group-responsive d-flex flex-wrap flex-md-nowrap gap-2 justify-content-start">
                        <button type="button" class="btn btn-primary flex-fill me-md-2 me-0 mb-2 mb-md-0 btn-search-custom" style="min-width: 140px;">
                            <i class="fas fa-search me-2"></i> Search
                        </button>
                        <button type="button" class="btn btn-secondary flex-fill" onclick="window.location.reload()" style="min-width: 120px;">
                            <i class="fas fa-redo me-2"></i> Reset
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <!-- /Modern Filter Card -->
        
        <div class="row">
            <div class="col-md-12">
                <div class="table-card">
                    <div class="table-responsive">
                        <table class="table table-striped custom-table" id="incrementsTable">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Department</th>
                                <th>Type</th>
                                <th>Effective Date</th>
                                <th>Current Designation</th>
                                <th>New Designation</th>
                                <th>Current CTC</th>
                                <th>New CTC</th>
                                <th>Increment %</th>
                                <th>Status</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($increments as $inc)
                            <tr>
                                <td>
                                    <h2 class="table-avatar">
                                        <a href="#" class="avatar">
                                            @if($inc->employee->profile_image)
                                                @php
                                                    $profileImage = $inc->employee->profile_image;
                                                    if(strpos($profileImage, 'assets/') !== false) {
                                                        $imageUrl = asset($profileImage);
                                                    } else {
                                                        $imageUrl = asset('assets/employee_profile_image/'.$profileImage);
                                                    }
                                                @endphp
                                                <img src="{{ $imageUrl }}" alt="">
                                            @else
                                                <div class="employee-avatar" style="width: 100%; height: 100%; margin: 0;">
                                                    {{ strtoupper(substr($inc->employee->name, 0, 1)) }}
                                                </div>
                                            @endif
                                        </a>
                                        <a href="{{ route('increments.edit', $inc->id) }}">{{ $inc->employee->name ?? 'N/A' }} <span>{{ $inc->employee->designationObj->position ?? '' }}</span></a>
                                    </h2>
                                </td>
                                <td>{{ $inc->employee->departmentObj->department ?? '-' }}</td>
                                <td>
                                    @if($inc->type == 'increment')
                                        <span class="badge bg-inverse-info">Increment</span>
                                    @elseif($inc->type == 'promotion')
                                        <span class="badge bg-inverse-warning">Promotion</span>
                                    @else
                                        <span class="badge bg-inverse-success">Both</span>
                                    @endif
                                </td>
                                <td>{{ $inc->effective_date->format('d M Y') }}</td>
                                <td>{{ $inc->previousDesignation->position ?? '-' }}</td>
                                <td>{{ $inc->newDesignation->position ?? '-' }}</td>
                                <td>{{ number_format($inc->previous_ctc, 2) }}</td>
                                <td>{{ number_format($inc->new_ctc, 2) }}</td>
                                <td>{{ $inc->increment_percentage }}%</td>
                                <td>
                                    @if($inc->status == 'approved')
                                        <span class="badge bg-inverse-primary">Approved</span>
                                    @elseif($inc->status == 'processed')
                                        <span class="badge bg-inverse-success">Processed</span>
                                    @else
                                        <span class="badge bg-inverse-warning">{{ ucfirst($inc->status) }}</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="dropdown dropdown-action">
                                        <a href="#" class="action-icon dropdown-toggle" data-toggle="dropdown" aria-expanded="false"><i class="material-icons">more_vert</i></a>
                                        <div class="dropdown-menu dropdown-menu-right">
                                            <a class="dropdown-item" href="{{ route('increments.edit', $inc->id) }}">
                                                <i class="fa fa-pencil m-r-5"></i> Edit
                                            </a>
                                            <a class="dropdown-item" href="#" data-toggle="modal" data-target="#view_increment_{{ $inc->id }}">
                                                <i class="fa fa-eye m-r-5"></i> View Details
                                            </a>
                                            <a class="dropdown-item" href="#" data-toggle="modal" data-target="#history_{{ $inc->employee_id }}">
                                                <i class="fa fa-history m-r-5"></i> View History
                                            </a>
                                            @if($inc->isLatest())
                                                <div class="dropdown-divider"></div>
                                                <form action="{{ route('increments.revert', $inc->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to revert this increment? This will restore the employee\'s previous salary structure.');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item text-danger">
                                                        <i class="fa fa-undo m-r-5"></i> Revert
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
        
        <!-- View Increment Modals -->
        @foreach($increments as $inc)
        <div class="modal custom-modal fade" id="view_increment_{{ $inc->id }}" role="dialog">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <h5 class="modal-title text-white">Increment / Promotion Details</h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <div class="row align-items-center">
                                            <div class="col-auto">
                                                <div class="employee-avatar" style="width: 60px; height: 60px; font-size: 1.5rem;">
                                                    @if($inc->employee->profile_image)
                                                        @php
                                                            $profileImage = $inc->employee->profile_image;
                                                            if(strpos($profileImage, 'assets/') !== false) {
                                                                $imageUrl = asset($profileImage);
                                                            } else {
                                                                $imageUrl = asset('assets/employee_profile_image/'.$profileImage);
                                                            }
                                                        @endphp
                                                        <img src="{{ $imageUrl }}" alt="">
                                                    @else
                                                        {{ strtoupper(substr($inc->employee->name, 0, 1)) }}
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="col">
                                                <h4 class="mb-1">{{ $inc->employee->name }}</h4>
                                                <p class="text-muted mb-0">{{ $inc->employee->designationObj->position ?? 'N/A' }} | ID: {{ $inc->employee->unique_id }}</p>
                                                <div class="mt-1">
                                                    @if($inc->type == 'increment')
                                                        <span class="badge bg-info">Increment</span>
                                                    @elseif($inc->type == 'promotion')
                                                        <span class="badge bg-warning">Promotion</span>
                                                    @else
                                                        <span class="badge bg-success">Both</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-body">
                                        <h6 class="card-title text-muted">Original Structure</h6>
                                        <ul class="list-group list-group-flush">
                                             <li class="list-group-item d-flex justify-content-between align-items-center">
                                                Effective Date
                                                <span class="fw-bold">{{ $inc->effective_date->format('d M Y') }}</span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                Previous Designation
                                                <span class="fw-bold">{{ $inc->previousDesignation->position ?? '-' }}</span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                Current Designation
                                                <span class="fw-bold">{{ $inc->newDesignation->position ?? '-' }}</span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                Previous CTC
                                                <span class="fw-bold">₹{{ number_format($inc->previous_ctc, 2) }}</span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-body">
                                        <h6 class="card-title text-muted">Proposed Changes</h6>
                                        <ul class="list-group list-group-flush">
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                Increment Amount
                                                <span class="fw-bold text-success">+₹{{ number_format($inc->increment_amount, 2) }}</span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                Percentage
                                                <span class="fw-bold text-success">{{ number_format($inc->increment_percentage, 2) }}%</span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                New CTC
                                                <span class="fw-bold text-primary">₹{{ number_format($inc->new_ctc, 2) }}</span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                Status
                                                @if($inc->status == 'approved')
                                                    <span class="badge bg-primary text-uppercase">Approved</span>
                                                @elseif($inc->status == 'processed')
                                                    <span class="badge bg-success text-uppercase">Processed</span>
                                                @elseif($inc->status == 'rejected')
                                                    <span class="badge bg-danger text-uppercase">Rejected</span>
                                                @else
                                                    <span class="badge bg-warning text-uppercase">Pending</span>
                                                @endif
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        @if($inc->new_salary_structure)
                            @php
                                $totalEarnings = 0;
                                $totalDeductions = 0;
                                
                                // Calculate Salary Components
                                if(isset($inc->new_salary_structure['salary'])) {
                                    foreach($inc->new_salary_structure['salary'] as $comp) {
                                        $master = $salaryComponents[$comp['salary_component_id']] ?? null;
                                        if($master) {
                                            if($master->type == 'earning') $totalEarnings += $comp['value'];
                                            else $totalDeductions += $comp['value'];
                                        }
                                    }
                                }
                                
                                // Calculate Statutory Components
                                if(isset($inc->new_salary_structure['statutory'])) {
                                    foreach($inc->new_salary_structure['statutory'] as $comp) {
                                        $master = $statutoryComponents[$comp['statutory_component_id']] ?? null;
                                        if($master) {
                                            if($master->type == 'earning') $totalEarnings += $comp['value'];
                                            else $totalDeductions += $comp['value'];
                                        }
                                    }
                                }
                                
                                $netSalary = $totalEarnings - $totalDeductions;
                            @endphp
                        
                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                        <h6 class="card-title text-muted">New Salary Structure</h6>
                                        <div class="table-responsive">
                                            <table class="table table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>Component</th>
                                                        <th class="text-end">Monthly Amount</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @if(isset($inc->new_salary_structure['salary']))
                                                        <tr class="table-primary"><th colspan="2">Earnings</th></tr>
                                                        @foreach($inc->new_salary_structure['salary'] as $comp)
                                                            @php $master = $salaryComponents[$comp['salary_component_id']] ?? null; @endphp
                                                            <tr>
                                                                <td>{{ $master->name ?? 'Component' }}</td>
                                                                <td class="text-end">₹{{ number_format($comp['value'], 2) }}</td>
                                                            </tr>
                                                        @endforeach
                                                    @endif
                                                    @if(isset($inc->new_salary_structure['statutory']))
                                                        <tr class="table-warning"><th colspan="2">Statutory Components</th></tr>
                                                        @foreach($inc->new_salary_structure['statutory'] as $comp)
                                                            @php $master = $statutoryComponents[$comp['statutory_component_id']] ?? null; @endphp
                                                            <tr>
                                                                <td>{{ $master->name ?? 'Component' }}</td>
                                                                <td class="text-end">₹{{ number_format($comp['value'], 2) }}</td>
                                                            </tr>
                                                        @endforeach
                                                    @endif
                                                    
                                                    <tr class="table-success fw-bold">
                                                        <td class="text-uppercase">In-Hand Salary</td>
                                                        <td class="text-end">₹{{ number_format($netSalary, 2) }}</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
<!-- /Page Wrapper -->
 @endsection
 @include('increments._history_modal')

@section('script')




<script>
    (function() {
        function initIncrementSearch() {
            if (typeof jQuery === 'undefined') {
                setTimeout(initIncrementSearch, 100);
                return;
            }
            
            var $ = jQuery;
            $(document).ready(function() {
                // Initialize DataTable explicitly with configuration matching employees/list
                var table = $('#incrementsTable').DataTable({
                    "responsive": true,
                    "pageLength": 10,
                    "searching": true, // Explicitly enable searching
                    "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
                    "language": {
                        "search": "_INPUT_",
                        "searchPlaceholder": "Search...",
                        "paginate": {
                            "first": "First",
                            "last": "Last",
                            "next": "Next",
                            "previous": "Previous"
                        }
                    },
                    // Custom DOM to hide default search input since we use custom filters
                    "dom": '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6">>rt<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
                    "columnDefs": [
                        { "orderable": false, "targets": [10] } // Action column
                    ]
                });
                
                // Initialize Select2 explicitly
                if($('.select').length > 0) {
                    $('.select').select2({
                        width: '100%'
                    });
                }
        
                // Instant Search Logic
                function performSearch() {
                    console.log('Performing search...');
                    var name = $('#search_name').val();
                    var dept = $('#search_dept').val();
                    var desg = $('#search_desg').val();
                    
                    console.log('Filters:', { name: name, dept: dept, desg: desg });
        
                    // Apply filters
                    // Column 0: Employee Name
                    // Column 1: Department
                    // Column 4: Current Designation
                    
                    table
                        .column(0).search(name)
                        .column(1).search(dept)
                        .column(4).search(desg)
                        .draw();
                        
                    // Log current search state for debugging
                    console.log('Table Search State:', {
                        col0: table.column(0).search(),
                        col1: table.column(1).search(),
                        col4: table.column(4).search()
                    });
                }
        
                // Use event delegation
                $(document).on('keyup input change paste', '#search_name', function() {
                    console.log('Search Name Input Detected');
                    performSearch();
                });
                
                $(document).on('change select2:select', '#search_dept, #search_desg', function() {
                    console.log('Dropdown Change Detected');
                    performSearch();
                });
                
                // Manual trigger
                $(document).on('click', '.btn-search-custom', function() {
                    console.log('Search Button Clicked');
                    performSearch();
                });
            });
        }
        
        // Start the check
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initIncrementSearch);
        } else {
            initIncrementSearch();
        }
    })();
</script>
@endsection