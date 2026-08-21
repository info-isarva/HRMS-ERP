<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    {{-- Bootstrap 5 & DataTables CSS --}}
    <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"> -->
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Bootstrap 5 and Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

    <!---Custom CSS --->
    <link href="{{ asset('css/custom-style.css') }}" rel="stylesheet">
    <!-- Select2 CSS -->
    <link href="{{ asset('css/select2.min.css') }}" rel="stylesheet" />

    <!-- Font Awesome for time/date icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>

<body class="font-sans antialiased">
    <!-- Before </body> -->
    <!-- jQuery and Select2 JS (only once, before custom scripts) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="{{ asset('js/swal.min.js') }}"></script>
    <script src="{{ asset('js/swal.js') }}"></script>
    @stack('styles')
    <div class="min-h-screen bg-gray-100 dark:bg-gray-900">

        {{-- @include('layouts.navigation') --}}
        <!-- Page Heading -->
        @isset($header)
            <header class="bg-white dark:bg-gray-800 shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endisset

        <!-- Page Content -->
        <!-- <div class="container-fluid min-vh-100 bg-light"> -->
        <div class="min-vh-100 bg-light">
            <div class="row g-0">
                @include('layouts.sidebar')
                <main class="col-12 col-lg-12   custom-margin  @auth with-top-header @endauth d-flex flex-column min-vh-100"
                    style="max-width:calc(100%-280px);">

                    @auth

                        @php
                            $currentRoute = \Illuminate\Support\Facades\Route::currentRouteName();
                            $routeTitles = [
                                'dashboard' => 'Dashboard',
                                'leads.index' => 'Leads',
                                'deals.index' => 'Deals',
                                'deals.pipeline' => 'Deals Pipeline',
                                'organizations.index' => 'Company',
                                'organizations.create' => 'Company',
                                'organizations.show' => 'Company',
                                'organizations.edit' => 'Company',
                                'people.index' => 'Contact Persons',
                                'people.show' => 'Contact Persons',
                                'people.edit' => 'Contact Persons',
                                'people.create' => 'Contact Persons',
                                'customers.index' => 'Company Owners',
                                'customers.show' => 'Comapny Owner',
                                'customers.edit' => 'Comapny Owner',
                                'customers.create' => 'Comapny Owner',
                                'users.index' => 'Users',
                                'reports.leads_by_source_custom' => 'Leads by Source',
                                'reports.deals_by_source_custom' => 'Deals by Source',
                                'profile.edit' => 'Profile',
                                'calllogs.index' => 'Call Logs',
                                'calllogs.create' => 'Call Logs',
                                'calllogs.edit' => 'Call Logs',
                                'calllogs.show' => 'Call Logs',
                                'calllogs.import.page' => 'Call Logs',
                                'activity-logs.index' => 'Audit Logs',
                                'activity-logs.show' => 'Audit Logs',
                            ];

                            if ($currentRoute && isset($routeTitles[$currentRoute])) {
                                $generatedTitle = $routeTitles[$currentRoute];
                            } elseif ($currentRoute) {
                                // Fallback: take first segment of route name or URI
                                $parts = explode('.', $currentRoute);
                                $generatedTitle = ucfirst($parts[0]);
                            } else {
                                $segments = request()->segments();
                                $generatedTitle = $segments ? ucfirst($segments[0]) : 'Dashboard';
                            }
                        @endphp

                        <div class="top-slim-header px-3">
                            <div class="d-flex align-items-center py-2 w-100" style="gap: 0;">
                                <!-- Left side: Dashboard title and sidebar toggle -->
                                <div class="d-flex align-items-center" style="gap: 12px;">
                                    <button class="btn btn-sm btn-outline-secondary" id="sidebarToggle">
                                        <i class="bi bi-arrow-bar-left"></i>
                                    </button>
                                    <h5 class="mb-0" id="pageTitle">@yield('page_title', $generatedTitle)</h5>
                                </div>
                                <!-- Spacer to push right section -->
                                <div style="flex:1 1 auto;"></div>


                                <!-- Right side: Notifications + Profile dropdown -->
                                <div class="d-flex align-items-center" style="gap: 16px;">
                                    <!-- Financial Year Dropdown -->
                                    <div class="d-flex align-items-center me-3 position-relative fy-desktop-dropdown"
                                        id="fy-selector-container">
                                        @php
                                            $__selectedFyId = session('selected_financial_year', null);
                                            $__activeFy = \App\Models\FinancialYear::where('active', 1)->first();
                                            $__selectedFy = $__selectedFyId
                                                ? \App\Models\FinancialYear::find($__selectedFyId)
                                                : $__activeFy;
                                            $__buttonLabel = $__selectedFy
                                                ? \Carbon\Carbon::parse($__selectedFy->from_date)->format('M, Y') .
                                                    ' - ' .
                                                    \Carbon\Carbon::parse($__selectedFy->to_date)->format('M, Y')
                                                : 'Select Year';
                                            $__isCurrent = $__selectedFy && $__selectedFy->active;
                                        @endphp
                                        <div class="d-flex align-items-center px-3 py-1 rounded-3"
                                            style="background: rgb(239 246 255 / var(--tw-bg-opacity, 1) 1);color: #000;gap: 10px;min-width: 270px;border: 2px solid rgb(239 246 255 / var(--tw-bg-opacity, 1) 1);">
                                            <i class="bi bi-calendar-event" style="font-size:1.3rem;"></i>
                                            <span class="fw-semibold" style="font-size:16px;">FY:</span>
                                            <button type="button"
                                                class="btn btn-light d-flex align-items-center px-3 py-1 rounded-2"
                                                id="fyDropdownBtn"
                                                style="font-size:15px; font-weight:500; border:2px solid #e3eafc; box-shadow:none;">
                                                {{ $__buttonLabel }}
                                                @if ($__isCurrent)
                                                    <span class="badge bg-success ms-2"
                                                        style="font-size:13px;">Current</span>
                                                @elseif($__selectedFyId)
                                                    <span class="badge bg-primary ms-2"
                                                        style="font-size:13px;">Selected</span>
                                                @endif
                                            </button>
                                        </div>
                                        <!-- Dropdown -->
                                        <div id="fyDropdown" class="position-absolute shadow-lg rounded-3"
                                            style="top:48px; left:0; min-width:270px; background:#fff; z-index:1000; display:none;">
                                            <div class="px-3 pt-3 pb-2 border-bottom fw-semibold text-dark"
                                                style="font-size:15px;"><i class="bi bi-calendar-event me-2"></i> Select
                                                Financial Year</div>
                                            @php
                                                $fys = \App\Models\FinancialYear::orderBy('from_date', 'desc')->get();
                                                $selectedFyId = session('selected_financial_year', null);
                                            @endphp
                                            @foreach ($fys as $fy)
                                                <div class="d-flex align-items-center justify-content-between px-3 py-2 fy-item"
                                                    data-fy-id="{{ $fy->id }}"
                                                    style="border-bottom:1px solid #f0f0f0; cursor:pointer;">
                                                    <span>{{ $fy->from_date->format('M, Y') }} -
                                                        {{ $fy->to_date->format('M, Y') }}</span>
                                                    <div class="d-flex align-items-center" style="gap:8px;">
                                                        @if ($fy->active)
                                                            <span class="badge bg-success">Current</span>
                                                        @endif
                                                        @if ($fy->id == $selectedFyId)
                                                            <i class="bi bi-check-lg text-success" title="Selected"></i>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach

                                        </div>
                                    </div>

                                    <!-- Notifications dropdown -->
                                    <style>
                                        /* Visual styling for unread notifications in header */
                                        .notif-item.unread {
                                            background: #fff8e6;
                                            font-weight: 600;
                                            border-left: 4px solid #f39c12;
                                        }

                                        .notif-item {
                                            transition: background .12s ease;
                                        }

                                        .notif-item .notif-body {
                                            display: block;
                                        }
                                    </style>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-light position-relative" type="button"
                                            id="notificationDropdownBtn" data-bs-toggle="dropdown" aria-expanded="false"
                                            style="padding:8px 10px;">
                                            <i class="bi bi-bell" style="font-size:1.1rem;"></i>
                                            @php $unreadCount = Auth::user() ? Auth::user()->unreadNotifications()->count() : 0; @endphp
                                            @if ($unreadCount > 0)
                                                <span id="notifCount"
                                                    class="badge bg-danger position-absolute top-0 start-100 translate-middle"
                                                    style="font-size:11px;">{{ $unreadCount }}</span>
                                            @endif
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end p-0"
                                            aria-labelledby="notificationDropdownBtn"
                                            style="min-width:320px; max-height:420px; overflow:auto;">
                                            <li
                                                class="px-3 py-2 border-bottom fw-semibold d-flex align-items-center justify-content-between">
                                                Notifications
                                                <a href="#" id="markAllRead" class="btn btn-sm btn-link">Mark all</a>
                                            </li>
                                            @php $notes = Auth::user() ? Auth::user()->notifications()->orderBy('created_at','desc')->limit(10)->get() : collect(); @endphp
                                            <div id="notifItems">
                                                @forelse($notes as $notification)
                                                    @php
                                                        $notifLink =
                                                            $notification->data['meeting_link'] ??
                                                            ($notification->data['task_link'] ??
                                                                ($notification->data['related_link'] ?? ''));
                                                    @endphp
                                                    <li class="px-3 py-2 notif-item {{ $notification->read_at ? '' : 'unread' }}"
                                                        data-id="{{ $notification->id }}"
                                                        data-link="{{ $notifLink }}" style="cursor:pointer;">
                                                        <div class="small text-muted">
                                                            {{ $notification->created_at->diffForHumans() }}</div>
                                                        <div class="notif-body">{!! $notification->data['message'] ?? ($notification->data['title'] ?? 'Notification') !!}</div>
                                                    </li>
                                                @empty
                                                    <li class="px-3 py-3 text-muted small">No notifications</li>
                                                @endforelse
                                            </div>
                                            <li>
                                                <hr class="dropdown-divider m-0">
                                            </li>
                                            <li><a class="dropdown-item text-center"
                                                    href="{{ route('notifications.index') }}">View all</a></li>
                                        </ul>
                                    </div>

                                    <div class="dropdown">
                                        <button
                                            class="btn btn-sm btn-light d-inline-flex align-items-center justify-content-center"
                                            type="button" id="mobileProfileDropdown" data-bs-toggle="dropdown"
                                            aria-expanded="false"
                                            style="padding: 0 8px; display: flex; align-items: center;">
                                            @php $avatar = Auth::user()->avatar; @endphp
                                            @if ($avatar && file_exists(public_path('assets/employee_profile_image/' . $avatar)))
                                                <img src="{{ asset('assets/employee_profile_image/' . $avatar) }}"
                                                    class="rounded-circle shadow-sm border"
                                                    style="width:40px; height:40px; object-fit:cover;" alt="User">
                                            @else
                                                <img src="{{ asset('user-thumbnail.png') }}"
                                                    class="rounded-circle shadow-sm border"
                                                    style="width:40px; height:40px;object-fit:cover;" alt="User">
                                            @endif
                                            <span class="fw-semibold text-dark ms-2"
                                                style="font-size: 16px;">{{ Auth::user()->name }}</span>
                                            <i class="bi bi-chevron-down ms-2"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end"
                                            aria-labelledby="mobileProfileDropdown">
                                            <li class="px-3 py-2 user-dropdown-header">
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center"
                                                        style="width:32px;height:32px;">
                                                        {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 2)) }}</div>
                                                    <div>
                                                        <div class="fw-semibold">{{ Auth::user()->name }}</div>
                                                        <div class="text-muted small">{{ Auth::user()->email }}</div>
                                                    </div>
                                                </div>
                                            </li>
                                            <li>
                                                <hr class="dropdown-divider m-0">
                                            </li>
                                            <li><a class="dropdown-item" href="{{ route('profile.edit') }}">Profile</a>
                                            </li>
                                            <li><a class="dropdown-item" href="{{ route('password.edit') }}">Change
                                                    Password</a></li>
                                            <li>
                                                <form method="POST" action="{{ route('logout') }}" class="m-0">
                                                    @csrf
                                                    <button type="submit" class="dropdown-item text-danger">Sign
                                                        out</button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            @php
                                $selectedFyId = session('selected_financial_year', null);
                                $activeFy = \App\Models\FinancialYear::where('active', 1)->first();
                                $isHistorical = $selectedFyId && $activeFy && $selectedFyId != $activeFy->id;
                                $selectedFy = $selectedFyId ? \App\Models\FinancialYear::find($selectedFyId) : null;
                            @endphp

                        </div>
                    @endauth

                    @if ($isHistorical)
                        <div class="pb-4">
                            <div class="alert alert-warning mb-0 rounded-0 d-flex align-items-center justify-content-between"
                                role="alert" style="border-left:4px solid #f39c12;">
                                <div>
                                    <strong>Read-only:</strong>
                                    You are viewing a historical financial year @if ($selectedFy)
                                        ({{ \Carbon\Carbon::parse($selectedFy->from_date)->format('d M, Y') }} -
                                        {{ \Carbon\Carbon::parse($selectedFy->to_date)->format('d M, Y') }})
                                    @endif. Creating, editing or deleting records is disabled for
                                    this period.
                                </div>

                            </div>

                        </div>
                    @endif

                    {{-- Flash messages (success/error/validation) --}}
                    <div class="container-fluid px-3">
                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {!! session('success') !!}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                            </div>
                        @endif

                        @if (session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {!! session('error') !!}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                            </div>
                        @endif
                    </div>

                    @yield('content')
<!-- Footer Section -->

                    <div class="top-slim-footer px-3 mt-auto" >
                        <div class="container-fluid text-center">
                            <div class="d-flex flex-wrap justify-content-between align-items-center">
                                <p class="text-sm text-gray-500 mb-0">© 2026 CRM. All rights reserved.</p>
                                <!-- <div class="d-flex align-items-center">
                                    <a href="#"
                                        class="text-sm text-gray-500 hover:text-blue-600 transition-colors me-3">Privacy
                                        Policy</a>
                                    <a href="#"
                                        class="text-sm text-gray-500 hover:text-blue-600 transition-colors me-3">Terms
                                        of Service</a>
                                    <a href="#"
                                        class="text-sm text-gray-500 hover:text-blue-600 transition-colors">Support</a>
                                </div> -->
                                <div class="d-flex align-items-center">
                                    <span class="text-sm text-gray-500 me-1">Version</span>
                                    <span class="text-sm font-medium text-gray-900">1.0</span>
                                </div>
                            </div>
                        </div>
                    </div>

                </main>
            </div>

        </div>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                document.querySelectorAll('#fyDropdown .fy-item').forEach(function(el) {
                    el.addEventListener('click', function() {
                        var fyId = this.getAttribute('data-fy-id');
                        fetch("{{ route('financial_year.select') }}", {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                financial_year_id: fyId
                            })
                        }).then(function(r) {
                            return r.json();
                        }).then(function(j) {
                            if (j.success) {
                                location.reload();
                            } else {
                                alert('Failed to select financial year: ' + (j.error ||
                                    'unknown'));
                            }
                        }).catch(function(e) {
                            alert('Request failed: ' + e.message);
                        });
                    });
                });
            });
        </script>
        {{-- Bootstrap and DataTables JS --}}
        <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

        <script src="{{ asset('js/custom-script.js') }}"></script>
        <script src="{{ asset('js/select2.min.js') }}"></script>
        @yield('scripts')
        @stack('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var notifItems = document.querySelectorAll('.notif-item');
                var markAllBtn = document.getElementById('markAllRead');
                var notifCountEl = document.getElementById('notifCount');
                var mobileNotifCountEl = document.getElementById('mobileNotifCount');
                var mobileMarkAllBtn = document.getElementById('mobileMarkAllRead');
                var mobileNotifContainerEl = document.getElementById('mobileNotifItems');

                function postMark(payload, cb) {
                    fetch("{{ route('notifications.markRead') }}", {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(payload)
                    }).then(function(r) {
                        return r.json();
                    }).then(function(j) {
                        if (cb) cb(j);
                    }).catch(function(e) {
                        console.error('Mark read failed', e);
                    });
                }

                notifItems.forEach(function(item) {
                    item.addEventListener('click', function(e) {
                        var id = this.getAttribute('data-id');
                        var link = this.getAttribute('data-link');
                        postMark({
                            id: id
                        }, function(resp) {
                            if (resp && resp.success) {
                                item.classList.remove('unread');
                                if (notifCountEl) {
                                    var v = parseInt(resp.unread || 0);
                                    notifCountEl.textContent = v > 0 ? v : '';
                                    if (v === 0) notifCountEl.style.display = 'none';
                                }
                                if (mobileNotifCountEl) {
                                    var mv = parseInt(resp.unread || 0);
                                    mobileNotifCountEl.textContent = mv > 0 ? mv : '';
                                    if (mv === 0) mobileNotifCountEl.style.display = 'none';
                                }
                                if (link) {
                                    window.location = link;
                                }
                            }
                        });
                    });
                });

                if (markAllBtn) {
                    markAllBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        postMark({
                            all: 1
                        }, function(resp) {
                            if (resp && resp.success) {
                                document.querySelectorAll('.notif-item.unread').forEach(function(n) {
                                    n.classList.remove('unread');
                                });
                                if (notifCountEl) notifCountEl.style.display = 'none';
                                if (mobileNotifCountEl) mobileNotifCountEl.style.display = 'none';
                            }
                        });
                    });
                }

                // Polling: fetch recent notifications every 15 seconds and update dropdown
                function renderNotifItems(items) {
                    var container = document.getElementById('notifItems');
                    var mobileContainer = document.getElementById('mobileNotifItems');
                    if (!container && !mobileContainer) return;
                    if (!items || items.length === 0) {
                        if (container) container.innerHTML =
                            '<li class="px-3 py-3 text-muted small">No notifications</li>';
                        if (mobileContainer) mobileContainer.innerHTML =
                            '<li class="px-3 py-3 text-muted small">No notifications</li>';
                        return;
                    }
                    var html = '';
                    items.forEach(function(i) {
                        var cls = i.read_at ? '' : 'unread';
                        var link = i.task_link || '';
                        html += '<li class="px-3 py-2 notif-item ' + cls + '" data-id="' + i.id +
                            '" data-link="' + link + '" style="cursor:pointer;">';
                        html += '<div class="small text-muted">' + (i.created_at || '') + '</div>';
                        html += '<div class="notif-body">' + (i.message || '') + '</div>';
                        html += '</li>';
                    });
                    if (container) container.innerHTML = html;
                    if (mobileContainer) mobileContainer.innerHTML = html;
                    // rebind handlers for both containers
                    var bindSelector = '#notifItems .notif-item, #mobileNotifItems .notif-item';
                    document.querySelectorAll(bindSelector).forEach(function(item) {
                        item.addEventListener('click', function(e) {
                            var id = this.getAttribute('data-id');
                            var link = this.getAttribute('data-link');
                            postMark({
                                id: id
                            }, function(resp) {
                                if (resp && resp.success) {
                                    item.classList.remove('unread');
                                    if (notifCountEl) {
                                        var v = parseInt(resp.unread || 0);
                                        notifCountEl.textContent = v > 0 ? v : '';
                                        if (v === 0) notifCountEl.style.display = 'none';
                                    }
                                    if (mobileNotifCountEl) {
                                        var mv = parseInt(resp.unread || 0);
                                        mobileNotifCountEl.textContent = mv > 0 ? mv : '';
                                        if (mv === 0) mobileNotifCountEl.style.display = 'none';
                                    }
                                    if (link) {
                                        window.location = link;
                                    }
                                }
                            });
                        });
                    });
                }

                function fetchNotifications() {
                    fetch("{{ route('notifications.recent') }}", {
                            credentials: 'same-origin'
                        })
                        .then(function(r) {
                            return r.json();
                        })
                        .then(function(j) {
                            if (!j || !j.success) return;
                            var unread = j.unread || 0;
                            if (notifCountEl) {
                                notifCountEl.textContent = unread > 0 ? unread : '';
                                notifCountEl.style.display = unread > 0 ? '' : 'none';
                            }
                            if (mobileNotifCountEl) {
                                mobileNotifCountEl.textContent = unread > 0 ? unread : '';
                                mobileNotifCountEl.style.display = unread > 0 ? '' : 'none';
                            }
                            renderNotifItems(j.items || []);
                        }).catch(function(e) {
                            console.error('Failed to fetch notifications', e);
                        });
                }

                // initial fetch and interval
                fetchNotifications();
                // Poll every 1 seconds (user requested)
                setInterval(fetchNotifications, 1000);
            });
        </script>
    </div>
</body>

<!-- Hover popover for collapsed sidebar: show only the hovered icon's title and submenu -->
<div id="sidebarHoverPop" class="sidebar-hover-pop" aria-hidden="true" style="display:none;">
    <div class="popover-inner p-2">
        <div class="popover-menu p-2"></div>
    </div>
</div>



</body>

</html>
