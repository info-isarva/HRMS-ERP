@extends('layouts.master')
@section('title', 'Employee List')
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
        
        .mobile-employee-cards {
            display: block !important;
        }
        
        .desktop-table {
            display: none !important;
        }
        
        .employee-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 0.75rem;
            margin-bottom: 1rem;
            padding: 1rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            transition: all 0.2s ease;
        }
        
        .employee-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }
        
        .employee-card-header {
            display: flex;
            align-items: center;
            margin-bottom: 0.75rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid #f3f4f6;
        }
        
        .employee-card-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            margin-right: 1rem;
            font-size: 1rem;
        }
        
        .employee-card-info h6 {
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 0.25rem;
            font-size: 1rem;
        }
        
        .employee-card-info h6 a {
            color: #667eea;
            text-decoration: none;
            transition: all 0.2s ease;
        }
        
        .employee-card-info h6 a:hover {
            color: #764ba2;
            text-decoration: underline;
        }
        
        .employee-card-info .employee-id {
            font-size: 0.8125rem;
            color: #6b7280;
        }
        
        .employee-card-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.75rem;
            margin-bottom: 1rem;
        }
        
        .employee-card-field {
            display: flex;
            flex-direction: column;
        }
        
        .employee-card-label {
            font-size: 0.75rem;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.25rem;
            font-weight: 500;
        }
        
        .employee-card-value {
            font-size: 0.875rem;
            color: #1f2937;
            font-weight: 500;
        }
        
        .employee-card-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 0.75rem;
            border-top: 1px solid #f3f4f6;
        }
        
        .employee-card-status {
            display: flex;
            align-items: center;
        }
        
        .employee-card-buttons {
            display: flex;
            gap: 0.5rem;
        }
        
        .employee-card-buttons .btn-action {
            width: 36px;
            height: 36px;
            font-size: 0.875rem;
        }
        
        /* DataTables mobile overrides */
        .dataTables_wrapper {
            padding: 1rem;
        }
        
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter {
            text-align: center;
            margin-bottom: 1rem;
        }
        
        .dataTables_wrapper .dataTables_filter input {
            width: 100% !important;
            max-width: 300px;
            margin: 0.5rem 0 0 0 !important;
        }
        
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_paginate {
            text-align: center;
            padding: 0.75rem 0;
        }
        
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            padding: 0.5rem 0.75rem;
            margin: 0 0.125rem;
            font-size: 0.8125rem;
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
        
        .employee-card {
            padding: 0.875rem;
            margin-bottom: 0.875rem;
        }
        
        .employee-card-header {
            margin-bottom: 0.625rem;
            padding-bottom: 0.625rem;
        }
        
        .employee-card-avatar {
            width: 40px;
            height: 40px;
            font-size: 0.875rem;
            margin-right: 0.75rem;
        }
        
        .employee-card-info h6 {
            font-size: 0.9375rem;
        }
        
        .employee-card-info .employee-id {
            font-size: 0.75rem;
        }
        
        .employee-card-details {
            grid-template-columns: 1fr;
            gap: 0.625rem;
            margin-bottom: 0.875rem;
        }
        
        .employee-card-label {
            font-size: 0.6875rem;
        }
        
        .employee-card-value {
            font-size: 0.8125rem;
        }
        
        .employee-card-actions {
            padding-top: 0.625rem;
        }
        
        .employee-card-buttons .btn-action {
            width: 32px;
            height: 32px;
            font-size: 0.8125rem;
        }
        
        .dataTables_wrapper {
            padding: 0.75rem;
        }
        
        .dataTables_wrapper .dataTables_length select {
            font-size: 0.8125rem;
            padding: 0.375rem 1.5rem 0.375rem 0.5rem;
        }
        
        .dataTables_wrapper .dataTables_filter input {
            font-size: 0.8125rem;
            padding: 0.5rem 0.75rem;
        }
        
        .dataTables_wrapper .dataTables_info {
            font-size: 0.8125rem;
        }
        
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            padding: 0.375rem 0.5rem;
            font-size: 0.75rem;
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
        
        .employee-card {
            padding: 0.75rem;
            margin-bottom: 0.75rem;
        }
        
        .employee-card-avatar {
            width: 36px;
            height: 36px;
            font-size: 0.8125rem;
            margin-right: 0.625rem;
        }
        
        .employee-card-info h6 {
            font-size: 0.875rem;
        }
        
        .employee-card-buttons .btn-action {
            width: 30px;
            height: 30px;
            font-size: 0.75rem;
        }
        
        .dataTables_wrapper {
            padding: 0.5rem;
        }
    }
    
    /* Hide/Show based on screen size */
    @media (min-width: 769px) {
        .mobile-employee-cards {
            display: none !important;
        }
        
        .desktop-table {
            display: table !important;
        }
    }
    
    /* Touch Device Enhancements */
    .touch-device .employee-card:hover {
        transform: none;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }
    
    .employee-card.touched {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }
    
    .employee-card.focused {
        border-color: #667eea;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.15);
    }
    
    /* DataTables responsive fixes */
    .dataTables_wrapper .dtr-details {
        background-color: #f8f9fa;
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
        padding: 1rem;
        margin-top: 0.5rem;
    }
    
    .dtr-data {
        font-size: 0.875rem;
    }
    
    .dtr-title {
        font-weight: 600;
        color: #1f2937;
    }
    
    /* Loading States */
    .table-loading {
        position: relative;
        pointer-events: none;
        opacity: 0.6;
    }
    
    .table-loading::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 20px;
        height: 20px;
        margin: -10px 0 0 -10px;
        border: 2px solid #667eea;
        border-radius: 50%;
        border-top-color: transparent;
        animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
        to {
            transform: rotate(360deg);
        }
    }

    /* FIX 1: Improved filter card layout for medium-large screens (1200px - 1464px) */
    @media (min-width: 1200px) and (max-width: 1464px) {
        .filter-card .row {
            display: flex;
            flex-wrap: wrap;
        }
        
        .filter-card .row > [class*="col-"] {
            flex: 0 0 50%;
            max-width: 50%;
            margin-bottom: 1rem;
        }
        
        /* Make the buttons column take full width and align properly */
        .filter-card .row > [class*="col-"]:last-child {
            flex: 0 0 100%;
            max-width: 100%;
            margin-top: 0.5rem;
            margin-bottom: 0;
        }
        
        .filter-card .btn-group-responsive {
            display: flex;
            justify-content: flex-start;
            gap: 0.75rem;
            width: 100%;
        }
        
        .filter-card .btn-group-responsive .btn {
            flex: 0 0 auto;
            min-width: 140px;
        }
    }

    /* FIX 2: Remove duplicate employee count in mobile header */
    /* The duplicate was in the mobile stats row - removed the extra count display */

    /* Improved filter card layout for medium screens */
    @media (min-width: 768px) and (max-width: 1199px) {
        .filter-card .row {
            display: flex;
            flex-wrap: wrap;
        }
        
        .filter-card .row > [class*="col-"] {
            flex: 0 0 50%;
            max-width: 50%;
        }
        
        .filter-card .row > [class*="col-"]:nth-child(5) {
            flex: 0 0 100%;
            max-width: 100%;
            margin-top: 1rem;
        }
        
        .filter-card .btn-group-responsive {
            display: flex;
            justify-content: flex-start;
            gap: 0.75rem;
        }
        
        .filter-card .btn-group-responsive .btn {
            flex: 0 0 auto;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
    }
</style>

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
                            <i class="fas fa-users fa-lg" style="color: rgba(255,255,255,0.9);"></i>
                        </div>
                        <div class="ms-3">
                            <h1 class="page-header-title">Employee Directory</h1>
                            <p class="page-header-subtitle">Manage and track all employee information</p>
                        </div>
                    </div>
                    <!-- Stats Section Restored to Top -->
                    <div class="d-flex align-items-center text-end d-none d-md-flex">
                        <div class="page-header-stats me-3 text-white">
                            <p class="page-header-stats-label mb-0" style="opacity: 0.9; font-size: 0.875rem;">Total Employees</p>
                            <p class="page-header-stats-value mb-0 fw-bold" id="employee-count" style="font-size: 1.75rem;">{{ $employees->count() }}</p>
                        </div>
                        <div class="page-header-stats-icon p-2 rounded" style="background: rgba(255,255,255,0.2); width: auto; height: auto;">
                            <i class="fas fa-user-friends text-white" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="p-3 d-flex justify-content-between align-items-center flex-wrap gap-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Employees</li>
                    </ol>
                </nav>
                <div class="d-flex gap-2">
                    @if (Auth::user()->hasPermission('employees.add_create'))
                    <a href="{{ route('employees.new') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i> Add Employee
                    </a>
                    @endif
                </div>
            </div>
        </div>

        <!-- Modern Filter Card -->
        <div class="filter-card">
            <form method="GET" action="{{ route('employees.index') }}">
                <div class="row align-items-end">
                    <div class="col-xl-3 col-lg-4 col-md-6 col-12 mb-3">
                        <label class="font-weight-600 text-muted mb-2" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">
                            <i class="fas fa-search me-1"></i> Search
                        </label>
                        <div class="search-wrapper">
                            <i class="fas fa-search"></i>
                            <input type="text" id="searchInput" name="search" class="form-control" 
                                   placeholder="Search by Name or ID"
                                   value="{{ $filters['search'] ?? '' }}">
                        </div>
                    </div>
                    <div class="col-xl-2 col-lg-4 col-md-6 col-12 mb-3">
                        <label class="font-weight-600 text-muted mb-2" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">
                            <i class="fas fa-building me-1"></i> Department
                        </label>
                        <select name="department" class="form-control form-select">
                            <option value="">All Departments</option>
                            @foreach($departments as $key => $value)
                                <option value="{{ $key }}" {{ ($filters['department'] ?? '') == $key ? 'selected' : '' }}>
                                    {{ $value }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-xl-2 col-lg-4 col-md-6 col-12 mb-3">
                        <label class="font-weight-600 text-muted mb-2" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">
                            <i class="fas fa-briefcase me-1"></i> Designation
                        </label>
                        <select name="designation" class="form-control form-select">
                            <option value="">All Designations</option>
                            @foreach($designations as $key => $value)
                                <option value="{{ $key }}" {{ ($filters['designation'] ?? '') == $key ? 'selected' : '' }}>
                                    {{ $value }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-xl-2 col-lg-4 col-md-6 col-12 mb-3">
                        <label class="font-weight-600 text-muted mb-2" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">
                            <i class="fas fa-map-marker-alt me-1"></i> Location
                        </label>
                        <select name="location" class="form-control form-select">
                            <option value="">All Locations</option>
                            @foreach($locations as $key => $value)
                                <option value="{{ $key }}" {{ ($filters['location'] ?? '') == $key ? 'selected' : '' }}>
                                    {{ $value }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-xl-2 col-lg-4 col-md-6 col-12 mb-3">
                        <label class="font-weight-600 text-muted mb-2" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">
                            <i class="fas fa-toggle-on me-1"></i> Status
                        </label>
                        <select name="status" class="form-control form-select">
                            <option value="">All Statuses</option>
                            @foreach($statuses as $key => $value)
                                <option value="{{ $key }}" {{ ($filters['status'] ?? '') == $key ? 'selected' : '' }}>
                                    {{ $value }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <!-- FIX 1: Improved button column for better responsive behavior -->
                    <div class="col-xl-3 col-lg-8 col-md-12 col-12 mb-3">
                        <div class="btn-group-responsive d-flex flex-wrap flex-md-nowrap gap-2 justify-content-start">
                            <button type="submit" class="btn btn-primary flex-fill me-md-2 me-0 mb-2 mb-md-0" style="min-width: 140px;">
                                <i class="fas fa-search me-2"></i> Search
                            </button>
                            <a href="{{ route('employees.index') }}" class="btn btn-secondary flex-fill" style="min-width: 120px;">
                                <i class="fas fa-redo me-2"></i> Reset
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Modern Table Card -->
        <div class="table-card">
            @if($employees->count() > 0)
            
            <!-- Desktop Table View -->
            <div class="table-responsive">
                <table class="table table-hover desktop-table" id="employeeTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Employee</th>
                            <th>Email</th>
                            <th>Department</th>
                            <th>Location</th>
                            <th>Designation</th>
                            <th>Reporting Manager</th>
                            <th>Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($employees as $key => $employee)
                        <tr>
                            <td class="font-weight-600 text-muted">{{ $key + 1 }}</td>
                            <td>
                                <div class="employee-info">
                                    <div class="employee-avatar">
                                        {{ strtoupper(substr($employee->name, 0, 1)) }}
                                    </div>
                                    <div class="employee-details">
                                        <div class="employee-name">
                                            @if (Auth::user()->hasPermission('employees.edit_update'))
                                                <a href="{{ route('employees.edit', $employee->id) }}">{{ $employee->name }}</a>
                                            @else
                                                {{ $employee->name }}
                                            @endif
                                        </div>
                                        <div class="employee-id">ID: {{ $employee->employee_id }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <i class="fas fa-envelope text-muted me-2" style="font-size: 0.75rem;"></i>
                                <span style="font-size: 0.875rem;">{{ $employee->email ?? 'N/A' }}</span>
                            </td>
                            <td>
                                <span class="badge bg-info text-white">
                                    {{ $departments[$employee->department] ?? 'N/A' }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-secondary text-white">
                                    {{ $employee->locationObj->name ?? 'N/A' }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-warning text-dark">
                                    {{ $designations[$employee->designation] ?? 'N/A' }}
                                </span>
                            </td>
                            <td>
                                @if($employee->reportingManager)
                                    <div style="font-size: 0.875rem;">
                                        <i class="fas fa-user-tie text-muted me-2" style="font-size: 0.75rem;"></i>
                                        <strong>{{ $employee->reportingManager->name }}</strong>
                                        <br>
                                        <small class="text-muted ms-4">ID: {{ $employee->reportingManager->employee_id }}</small>
                                    </div>
                                @else
                                    <span class="text-muted" style="font-size: 0.875rem;">
                                        <i class="fas fa-minus-circle me-1"></i> None
                                    </span>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-{{ $employee->status == 1 ? 'success' : 'danger' }}">
                                    <i class="fas fa-circle me-1" style="font-size: 0.5rem;"></i>
                                    {{ $statuses[$employee->status] ?? 'Inactive' }}
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="action-buttons justify-content-center">
                                    @if (Auth::user()->hasPermission('employees.edit_update'))
                                    <a href="{{ route('employees.edit', $employee->id) }}" 
                                       class="btn-action btn-action-edit"
                                       title="Edit Employee"
                                       data-toggle="tooltip">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Mobile Card View -->
            <div class="mobile-employee-cards" style="display: none;">
                @foreach($employees as $key => $employee)
                <div class="employee-card" data-employee-id="{{ $employee->id }}">
                    <!-- Card Header with Avatar and Basic Info -->
                    <div class="employee-card-header">
                        <div class="employee-card-avatar">
                            {{ strtoupper(substr($employee->name, 0, 1)) }}
                        </div>
                        <div class="employee-card-info">
                            <h6 class="mb-0">
                                @if (Auth::user()->hasPermission('employees.edit_update'))
                                    <a href="{{ route('employees.edit', $employee->id) }}">{{ $employee->name }}</a>
                                @else
                                    {{ $employee->name }}
                                @endif
                            </h6>
                            <div class="employee-id">ID: {{ $employee->employee_id }}</div>
                        </div>
                    </div>
                    
                    <!-- Card Details Grid -->
                    <div class="employee-card-details">
                        <div class="employee-card-field">
                            <div class="employee-card-label">
                                <i class="fas fa-envelope me-1"></i> Email
                            </div>
                            <div class="employee-card-value">
                                {{ $employee->email ?? 'N/A' }}
                            </div>
                        </div>
                        
                        <div class="employee-card-field">
                            <div class="employee-card-label">
                                <i class="fas fa-building me-1"></i> Department
                            </div>
                            <div class="employee-card-value">
                                <span class="badge bg-info text-dark">
                                    {{ $departments[$employee->department] ?? 'N/A' }}
                                </span>
                            </div>
                        </div>
                        
                        <div class="employee-card-field">
                            <div class="employee-card-label">
                                <i class="fas fa-map-marker-alt me-1"></i> Location
                            </div>
                            <div class="employee-card-value">
                                <span class="badge bg-secondary text-white">
                                    {{ $employee->locationObj->name ?? 'N/A' }}
                                </span>
                            </div>
                        </div>
                        
                        <div class="employee-card-field">
                            <div class="employee-card-label">
                                <i class="fas fa-briefcase me-1"></i> Designation
                            </div>
                            <div class="employee-card-value">
                                <span class="badge bg-warning text-dark">
                                    {{ $designations[$employee->designation] ?? 'N/A' }}
                                </span>
                            </div>
                        </div>
                        
                        <div class="employee-card-field">
                            <div class="employee-card-label">
                                <i class="fas fa-user-tie me-1"></i> Manager
                            </div>
                            <div class="employee-card-value">
                                @if($employee->reportingManager)
                                    <div>
                                        <strong>{{ $employee->reportingManager->name }}</strong>
                                        <br>
                                        <small class="text-muted">ID: {{ $employee->reportingManager->employee_id }}</small>
                                    </div>
                                @else
                                    <span class="text-muted">
                                        <i class="fas fa-minus-circle me-1"></i> None
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <!-- Card Actions -->
                    <div class="employee-card-actions">
                        <div class="employee-card-status">
                            <span class="badge badge-{{ $employee->status == 1 ? 'success' : 'danger' }}">
                                <i class="fas fa-circle me-1" style="font-size: 0.5rem;"></i>
                                {{ $statuses[$employee->status] ?? 'Inactive' }}
                            </span>
                        </div>
                        
                        <div class="employee-card-buttons">
                            @if (Auth::user()->hasPermission('employees.edit_update'))
                            <a href="{{ route('employees.edit', $employee->id) }}" 
                               class="btn-action btn-action-edit"
                               title="Edit Employee"
                               data-toggle="tooltip">
                                <i class="fas fa-edit"></i>
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            
            @else
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i class="fas fa-users-slash"></i>
                </div>
                <h3 class="empty-state-title">No Employees Found</h3>
                <p class="empty-state-text">Try adjusting your search or filter criteria</p>
                @if (Auth::user()->hasPermission('employees.add_create'))
                <a href="{{ route('employees.new') }}" class="btn btn-primary mt-3">
                    <i class="fa fa-plus me-2"></i> Add First Employee
                </a>
                @endif
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    $(document).ready(function() {
        // Initialize tooltips
        $('[data-toggle="tooltip"]').tooltip();
        
        // Initialize DataTable with responsive configuration
        var table = $('#employeeTable').DataTable({
            "responsive": true,
            "pageLength": 10,
            "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            "order": [[1, "asc"]], // Sort by employee name
            "columnDefs": [
                // { "orderable": false, "targets": [0, 8] }, // Disable sorting on # and Actions columns
                { "searchable": false, "targets": [0, 3, 4, 5, 6, 7, 8] },  // Exclude non-relevant columns from search
                { "responsivePriority": 1, "targets": 1 }, // Employee column priority
                { "responsivePriority": 2, "targets": 8 }, // Actions column priority
                { "responsivePriority": 3, "targets": 7 }, // Status column priority
                { "responsivePriority": 4, "targets": 3 }, // Department column priority
                { "responsivePriority": 5, "targets": 4 }  // Location column priority
            ],
            "language": {
                "search": "_INPUT_",
                "searchPlaceholder": "Search employees by name, ID, email...",
                "lengthMenu": "Show _MENU_ employees",
                "info": "Showing _START_ to _END_ of _TOTAL_ employees",
                "infoEmpty": "No employees to display",
                "infoFiltered": "(filtered from _MAX_ total employees)",
                "zeroRecords": "No matching employees found",
                "emptyTable": "No employees available",
                "paginate": {
                    "first": "First",
                    "last": "Last",
                    "next": "Next",
                    "previous": "Previous"
                }
            },
            "dom": '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
            "drawCallback": function(settings) {
                // Update employee count in header - FIX 2: Only update one element
                var info = this.api().page.info();
                $('#employee-count').text(info.recordsDisplay);
                
                // Reinitialize tooltips after table redraw
                $('[data-toggle="tooltip"]').tooltip();
            }
        });
        
        // Mobile layout detection and switching
        function checkMobileLayout() {
            var isMobile = window.innerWidth <= 768;
            
            if (isMobile) {
                // Show mobile cards, hide desktop table
                $('.mobile-employee-cards').show();
                $('.desktop-table').hide();
                
                // Disable DataTables on mobile
                if (table) {
                    table.destroy();
                    table = null;
                }
                
                // Apply mobile filtering to cards
                applyMobileFiltering();
                
            } else {
                // Show desktop table, hide mobile cards
                $('.mobile-employee-cards').hide();
                $('.desktop-table').show();
                
                // Reinitialize DataTables if not already initialized
                if (!table) {
                    table = $('#employeeTable').DataTable({
                        "responsive": true,
                        "pageLength": 10,
                        "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
                        "order": [[1, "asc"]],
                        "columnDefs": [
                            { "orderable": false, "targets": [0, 8] },
                            { "searchable": false, "targets": [0, 3, 4, 5, 6, 7, 8] },
                            { "responsivePriority": 1, "targets": 1 },
                            { "responsivePriority": 2, "targets": 8 },
                            { "responsivePriority": 3, "targets": 7 },
                            { "responsivePriority": 4, "targets": 3 },
                            { "responsivePriority": 5, "targets": 4 }
                        ],
                        "language": {
                            "search": "_INPUT_",
                            "searchPlaceholder": "Search employees by name, ID, email...",
                            "lengthMenu": "Show _MENU_ employees",
                            "info": "Showing _START_ to _END_ of _TOTAL_ employees",
                            "infoEmpty": "No employees to display",
                            "infoFiltered": "(filtered from _MAX_ total employees)",
                            "zeroRecords": "No matching employees found",
                            "emptyTable": "No employees available",
                            "paginate": {
                                "first": "First",
                                "last": "Last",
                                "next": "Next",
                                "previous": "Previous"
                            }
                        },
                        "dom": '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
                        "drawCallback": function(settings) {
                            var info = this.api().page.info();
                            $('#employee-count').text(info.recordsDisplay);
                            $('[data-toggle="tooltip"]').tooltip();
                        }
                    });
                    
                    // Sync search input with DataTable
                    $('#searchInput').on('keyup', function() {
                        if (table) {
                            table.search(this.value).draw();
                        }
                    });
                }
            }
        }
        
        // Apply filtering to mobile cards
        function applyMobileFiltering() {
            var searchTerm = $('#searchInput').val().toLowerCase();
            
            $('.employee-card').each(function() {
                var $card = $(this);
                var cardText = $card.text().toLowerCase();
                
                if (searchTerm === '' || cardText.includes(searchTerm)) {
                    $card.show();
                } else {
                    $card.hide();
                }
            });
            
            // FIX 2: Only update one employee count element
            var visibleCards = $('.employee-card:visible').length;
            $('#employee-count').text(visibleCards);
        }
        
        // Initial layout check
        checkMobileLayout();
        
        // Handle window resize
        $(window).on('resize', function() {
            clearTimeout(window.resizeTimer);
            window.resizeTimer = setTimeout(function() {
                checkMobileLayout();
            }, 250);
        });
        
        // Store initial server-side search value
        var initialSearchValue = "{{ $filters['search'] ?? '' }}";

        // Sync the filter card search with DataTable search or mobile filtering
        // Use 'input' event to capture typing, pasting, and 'x' button clear
        $('#searchInput').on('input', function() {
            var currentValue = this.value;

            // If user clears the input AND there was a server-side search active,
            // submit the form to reload data from server (clearing the filter)
            if (currentValue === '' && initialSearchValue !== '') {
                $(this).closest('form').submit();
                return;
            }

            if (window.innerWidth <= 768) {
                // Mobile filtering
                applyMobileFiltering();
            } else {
                // DataTable filtering
                if (table) {
                    table.search(currentValue).draw();
                }
            }
        });

        // Make selects auto-submit the filter form (URL-based filtering)
        $('select[name="department"], select[name="designation"], select[name="status"], select[name="location"]').on('change', function() {
            // Submit the surrounding form to apply server-side (URL) filters
            $(this).closest('form').submit();
        });

        // Handle filter form submission normally (let it reload the page with GET params)
        $('.filter-card form').on('submit', function() {
            // No JS interception here — allow normal GET submission so server-side filters apply
            return true;
        });

        // Reset filters — navigate to the base index URL to clear query params
        $('.filter-card .btn-secondary').on('click', function(e) {
            e.preventDefault();
            window.location.href = '{{ route('employees.index') }}';
        });
        
        // Confirmation for delete action
        $(document).on('submit', '.delete-form', function(e) {
            e.preventDefault();
            var form = this;
            
            if (confirm('Are you sure you want to delete this employee? This action cannot be undone.')) {
                form.submit();
            }
        });
        
        // Smooth scroll to top when clicking action buttons
        $(document).on('click', '.btn-action', function() {
            $('html, body').animate({
                scrollTop: 0
            }, 300);
        });
        
        // Mobile card click to expand/focus (optional enhancement)
        $(document).on('click', '.employee-card', function(e) {
            // Don't trigger if clicking on action buttons
            if (!$(e.target).closest('.employee-card-buttons').length) {
                $(this).toggleClass('focused');
            }
        });
        
        // Touch-friendly interactions for mobile
        if ('ontouchstart' in window) {
            // Add touch-friendly classes
            $('body').addClass('touch-device');
            
            // Prevent hover effects on touch devices
            $('.employee-card').on('touchstart', function() {
                $(this).addClass('touched');
            });
            
            $('.employee-card').on('touchend', function() {
                var $this = $(this);
                setTimeout(function() {
                    $this.removeClass('touched');
                }, 300);
            });
        }
    });
</script>
@endsection