<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <meta name="description" content="SoengSouy Admin Template">
    <meta name="keywords" content="admin, estimates, bootstrap, business, corporate, creative, management, minimal, modern, accounts, invoice, html5, responsive, CRM, Projects">
    <meta name="author" content="SoengSouy Admin Template">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>HRMS | @yield('title')</title>
    <!-- Favicon -->
    <link rel="shortcut icon" type="image/x-icon" href="{{ isset($companySettings->favicon) && !empty($companySettings->favicon) ? asset($companySettings->favicon) : asset('assets/img/favicon-dri.png') }}">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="{{ URL::to('assets/css/bootstrap.min.css') }}">
    <!-- Font Awesome 6 CSS - Updated for better icon support -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Lineawesome CSS -->
    <link rel="stylesheet" href="{{ URL::to('assets/css/line-awesome.min.css') }}">
    <!-- Datatable CSS -->
    <link rel="stylesheet" href="{{ URL::to('assets/css/dataTables.bootstrap4.min.css') }}">
    <!-- Select2 CSS -->
    <link rel="stylesheet" href="{{ URL::to('assets/css/select2.min.css') }}">
    <!-- Datetimepicker CSS -->
    <link rel="stylesheet" href="{{ URL::to('assets/css/bootstrap-datetimepicker.min.css') }}">
    <!-- Main CSS -->
    <link rel="stylesheet" href="{{ URL::to('assets/css/style.css') }}">
    <!-- Flasher CSS (optional, only needed if using themes) -->
    {{-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@flasher/flasher@2.1.6/dist/flasher.min.css"> --}}
    {{-- <link rel="stylesheet" href="{{ URL::to('public/vendor/flasher/flasher.min.css') }}"> --}}
    <link rel="stylesheet" href="{{ asset('vendor/flasher/flasher.min.css') }}">

    {{-- message toastr --}}
    <link rel="stylesheet" href="{{ URL::to('assets/css/toastr.min.css') }}">
    <script src="{{ URL::to('assets/js/toastr_jquery.min.js') }}"></script>
    <script src="{{ URL::to('assets/js/toastr.min.js') }}"></script>

    <script>
        window.ATTENDANCE_API_BASE_URL = '{{ env('ATTENDANCE_API_BASE_URL', 'http://default-attendance-url/api') }}';
        
        // Global error handler to prevent chart-related errors from showing
        window.addEventListener('error', function(e) {
            // Suppress common chart errors
            if (e.message && (
                e.message.includes('getContext') || 
                e.message.includes('Morris') || 
                e.message.includes('Graph container element not found') ||
                e.message.includes('Chart')
            )) {
                e.preventDefault();
                return false;
            }
        });
        
        // Suppress console errors for missing chart elements
        var originalError = console.error;
        console.error = function(message) {
            if (typeof message === 'string' && (
                message.includes('getContext') || 
                message.includes('Morris') ||
                message.includes('Graph container') ||
                message.includes('Chart')
            )) {
                return; // Don't show chart-related errors
            }
            originalError.apply(console, arguments);
        };
    </script>

    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" /> 
    
    <!-- Custom CSS for User Management Sync -->
    <style>
        .badge-sync {
            background-color: #17a2b8;
            color: white;
            font-size: 10px;
            padding: 2px 6px;
            border-radius: 10px;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.7; }
            100% { opacity: 1; }
        }
        
        .user-sync-menu .fa {
            margin-right: 8px;
            color: #17a2b8;
        }
        
        .sidebar .badge {
            font-size: 9px;
            padding: 2px 5px;
        }
    </style>
</head>

<body>
    @yield('style')
    <style>    
        .invalid-feedback{
            font-size: 14px;
        }
        .error{
            color: red;
        }
    </style>
    <!-- Main Wrapper -->
    <div class="main-wrapper">
        <!-- Loader -->
        <div id="loader-wrapper">
            <div id="loader">
                <div class="loader-ellips">
                  <span class="loader-ellips__dot"></span>
                  <span class="loader-ellips__dot"></span>
                  <span class="loader-ellips__dot"></span>
                  <span class="loader-ellips__dot"></span>
                </div>
            </div>
        </div>
        <!-- /Loader -->

        <!-- Header -->
        <div class="header">
            <!-- Logo -->
            <div class="header-left">
                <a href="{{ route('home') }}" class="logo">
                    {{-- <img src="{{ URL::to('/assets/images/'. Auth::user()->avatar) }}" width="auto" height="40" alt=""> --}}
                    <img src="{{ isset($companySettings->logo_image) && !empty($companySettings->logo_image) ? asset($companySettings->logo_image) : asset('assets/img/user-icon.webp') }}" width="auto"  style="height: 5rem;" alt="">
                </a>
            </div>
            <!-- /Logo -->
            <a id="toggle_btn" href="javascript:void(0);">
                <span class="bar-icon">
                    <span></span>
                    <span></span>
                    <span></span>
                </span>
            </a>
            <!-- Header Title -->
            <div class="page-title-box">
                {{-- <h3>Hi, {{ Session::get('name') }}</h3> --}}
                <h3>Welcome, {{ $companySettings->company_name }}</h3>
            </div>
            <!-- /Header Title -->
            <a id="mobile_btn" class="mobile_btn" href="#sidebar"><i class="fa fa-bars"></i></a>
            <!-- Header Menu -->
            <ul class="nav user-menu">
                <!-- Financial Year Switcher -->
                <li class="nav-item mr-3">
                    @include('components.financial-year-switcher')
                </li>
                
                <li class="nav-item dropdown has-arrow main-drop">
                    <a href="#" class="dropdown-toggle nav-link" data-toggle="dropdown">
                        <span class="user-img">
                        @php
                            $userAvatar = Auth::user()->avatar;
                            $displayAvatar = null;
                            
                            // For employee-converted users, prioritize employee profile image
                            if (Auth::user()->employee_id) {
                                $employeeData = DB::table('employee_basic_details')
                                    ->where('id', Auth::user()->employee_id)
                                    ->first();
                                if ($employeeData && !empty($employeeData->profile_image)) {
                                    $displayAvatar = $employeeData->profile_image;
                                }
                            }
                            
                            // Fall back to user avatar if no employee image found
                            if (!$displayAvatar) {
                                $displayAvatar = $userAvatar;
                            }
                            
                            // Generate the image URL
                            if ($displayAvatar) {
                                if (strpos($displayAvatar, 'assets/') !== false) {
                                    $imageUrl = url($displayAvatar);
                                } else {
                                    $imageUrl = url('/assets/employee_profile_image/' . $displayAvatar);
                                }
                            } else {
                                $imageUrl = url('/assets/img/user-icon.webp');
                            }
                        @endphp
                        <img src="{{ $imageUrl }}" alt="" style="width: 30px; height: 30px; object-fit: cover;">
                        <span class="status online"></span></span>
                        <span>{{ Session::get('name') }}</span>
                    </a>
                    <div class="dropdown-menu">
                        <a class="dropdown-item" href="{{ route('profile_user') }}">
                            <i class="fa fa-user-o"></i> My Profile</a>
                        @if (Auth::user()->role_name=='Admin' || Auth::user()->role_name=='Super Admin')
                            <a class="dropdown-item user-sync-menu" href="{{ route('userManagement') }}">
                                <i class="fa fa-users"></i> User Management <span class="badge badge-sync ml-1">Sync</span></a>
                        @endif
                        <a class="dropdown-item" href="{{ route('company/settings/page') }}">
                            <i class="fa fa-cog"></i> Settings</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="{{ route('logout') }}">
                            <i class="fa fa-sign-out"></i> Logout</a>
                    </div>
                </li>
            </ul>
            <!-- /Header Menu -->

            <!-- Mobile Menu -->
            <div class="dropdown mobile-user-menu">
                <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                    <i class="fa fa-ellipsis-v"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-right">
                    <a class="dropdown-item" href="{{ route('profile_user') }}">
                        <i class="fa fa-user-o"></i> My Profile</a>
                    @if (Auth::user()->role_name=='Admin' || Auth::user()->role_name=='Super Admin')
                        <a class="dropdown-item user-sync-menu" href="{{ route('userManagement') }}">
                            <i class="fa fa-users"></i> User Management <span class="badge badge-sync ml-1">Sync</span></a>
                    @endif
                    <a class="dropdown-item" href="{{ route('company/settings/page') }}">
                        <i class="fa fa-cog"></i> Settings</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="{{ route('logout') }}">
                        <i class="fa fa-sign-out"></i> Logout</a>
                </div>
            </div>
            <!-- /Mobile Menu -->

        </div>
        <!-- /Header -->
        <!-- Sidebar -->
        <!-- @include('sidebar.sidebar') -->
        <!-- /Sidebar -->
        
        <!-- Page Wrapper -->
        @yield('content')
        <!-- /Page Wrapper -->
    </div>
    <!-- /Main Wrapper -->

    <!-- jQuery -->
    <script src="{{ URL::to('assets/js/jquery-3.5.1.min.js') }}"></script>
    <!-- Bootstrap Core JS -->
    <script src="{{ URL::to('assets/js/popper.min.js') }}"></script>
    <script src="{{ URL::to('assets/js/bootstrap.min.js') }}"></script>
    <!-- Chart JS - Only load if specific chart elements exist -->
    @if(Request::is('home') || Request::is('dashboard'))
        <!-- Modern Chart.js for dashboard analytics -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
        <script>
            // Only load charts if elements exist
            document.addEventListener('DOMContentLoaded', function() {
                if (document.getElementById('lineChart')) {
                    // Load line chart script only if element exists
                    var script = document.createElement('script');
                    script.src = "{{ URL::to('assets/js/line-chart.js') }}";
                    script.onerror = function() { console.log('Chart script failed to load'); };
                    document.head.appendChild(script);
                }
            });
        </script>
    @endif
    
    @if(Request::is('home') || Request::is('dashboard') || Request::is('analytics') || Request::is('reports'))
        <!-- Morris charts for specific pages only -->
        <script src="{{ URL::to('assets/plugins/raphael/raphael.min.js') }}"></script>
        <script src="{{ URL::to('assets/plugins/morris/morris.min.js') }}"></script>
        <script>
            // Only load Morris charts if elements exist and Morris is available
            document.addEventListener('DOMContentLoaded', function() {
                if (typeof Morris !== 'undefined' && (document.getElementById('bar-charts') || document.getElementById('line-charts'))) {
                    var script = document.createElement('script');
                    script.src = "{{ URL::to('assets/js/chart.js') }}";
                    script.onerror = function() { console.log('Morris chart script failed to load'); };
                    document.head.appendChild(script);
                }
            });
        </script>
    @endif

    <!-- Slimscroll JS -->
    <script src="{{ URL::to('assets/js/jquery.slimscroll.min.js') }}"></script>
    <!-- Select2 JS -->
    <script src="{{ URL::to('assets/js/select2.min.js') }}"></script>
    <!-- Datetimepicker JS -->
    <script src="{{ URL::to('assets/js/moment.min.js') }}"></script>
    <script src="{{ URL::to('assets/js/bootstrap-datetimepicker.min.js') }}"></script>
    <!-- Datatable JS -->
    <script src="{{ URL::to('assets/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ URL::to('assets/js/dataTables.bootstrap4.min.js') }}"></script>
    <!-- Multiselect JS -->
    <script src="{{ URL::to('assets/js/multiselect.min.js') }}"></script>
    <!-- validation-->
    <script src="{{ URL::to('assets/js/jquery.validate.js') }}"></script>   
    <!-- Custom JS -->
    <script src="{{ URL::to('assets/js/app.js') }}"></script>
    @yield('script')

    {{-- <script src="https://cdn.jsdelivr.net/npm/@flasher/flasher@2.1.6/dist/flasher.min.js"></script>
    {!! Flasher::render() !!} --}}
    <script src="{{ URL::to('vendor/flasher/flasher.min.js') }}"></script>
    {!! Flasher::render() !!}
    
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
</body>
</html>