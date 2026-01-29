@extends('layouts.app')

@section('title', 'Permission Management')

@section('page-title', 'Permission Management')

@section('styles')
<style>
    /* Fix Select2 conflicts with Tailwind */
    .select2-container {
        width: 100% !important;
        z-index: 9999 !important;
    }
    
    .select2-container--default .select2-selection--multiple {
        border: 1px solid #d1d5db !important;
        border-radius: 0.5rem !important;
        min-height: 44px !important;
        background-color: white !important;
        padding: 4px 8px !important;
        font-size: 0.875rem !important;
    }
    
    .select2-container--default .select2-selection--multiple .select2-selection__rendered {
        display: flex !important;
        flex-wrap: wrap !important;
        align-items: center !important;
        padding: 0 !important;
        margin: 0 !important;
        gap: 4px !important;
    }
    
    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background-color: #4f46e5 !important;
        border: 1px solid #4f46e5 !important;
        color: white !important;
        padding: 4px 8px !important;
        border-radius: 6px !important;
        font-size: 0.75rem !important;
        display: inline-flex !important;
        align-items: center !important;
        margin: 0 !important;
    }
    
    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
        color: white !important;
        margin-right: 4px !important;
        font-weight: bold !important;
        cursor: pointer !important;
        order: 2;
    }
    
    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
        color: #e5e7eb !important;
        background-color: rgba(255, 255, 255, 0.2) !important;
        border-radius: 2px !important;
    }
    
    /* Search field styling */
    .select2-container--default .select2-search--inline .select2-search__field {
        margin: 0 !important;
        padding: 0 !important;
        border: none !important;
        outline: none !important;
        background: transparent !important;
        color: #374151 !important;
        min-height: auto !important;
        font-size: 0.875rem !important;
    }
    
    /* Dropdown styling */
    .select2-dropdown {
        border: 1px solid #d1d5db !important;
        border-radius: 0.5rem !important;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15) !important;
        background-color: white !important;
        z-index: 99999 !important;
        margin-top: 4px !important;
    }
    
    .select2-container--default .select2-results__option {
        padding: 8px 12px !important;
        background-color: white !important;
        color: #374151 !important;
        font-size: 0.875rem !important;
        border-bottom: 1px solid #f3f4f6 !important;
    }
    
    .select2-container--default .select2-results__option:last-child {
        border-bottom: none !important;
    }
    
    .select2-container--default .select2-results__option--highlighted {
        background-color: #4f46e5 !important;
        color: white !important;
    }
    
    .select2-container--default .select2-results__option--selected {
        background-color: #e0e7ff !important;
        color: #374151 !important;
    }
    
    .select2-container--default .select2-search--dropdown {
        padding: 12px !important;
        background-color: #f9fafb !important;
        border-bottom: 1px solid #e5e7eb !important;
    }
    
    .select2-container--default .select2-search--dropdown .select2-search__field {
        border: 1px solid #d1d5db !important;
        border-radius: 0.5rem !important;
        padding: 8px 12px !important;
        background-color: white !important;
        width: 100% !important;
        font-size: 0.875rem !important;
    }
    
    /* Modal specific fixes */
    .select2-container--open {
        z-index: 1060 !important;
    }
    
    .select2-dropdown {
        z-index: 1070 !important;
    }
    
    /* Count badge */
    .routes-count-badge {
        background: #4f46e5;
        color: white;
        border-radius: 12px;
        padding: 2px 8px;
        font-size: 0.75rem;
        margin-left: 8px;
    }

    /* Permission card styling */
    .permission-card { 
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out; 
    }
    .permission-card:hover { 
        transform: translateY(-2px); 
        box-shadow: 0 4px 8px rgba(0,0,0,0.1); 
    }
    
    /* Focus states */
    .select2-container--default.select2-container--focus .select2-selection--multiple {
        border-color: #4f46e5 !important;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1) !important;
    }
    
    /* Error Styling */
    .error-message {
        color: #ef4444;
        font-size: 0.875rem;
        margin-top: 0.25rem;
    }
    
    input.border-red-500,
    textarea.border-red-500,
    select.border-red-500 {
        border-color: #ef4444;
    }
    
    input.border-red-500:focus,
    textarea.border-red-500:focus,
    select.border-red-500:focus {
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
    }
    
    /* Toast notifications */
    .toast {
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        min-width: 300px;
        max-width: 500px;
    }
    
    /* Modal transitions */
    #addPermissionModal,
    #editPermissionModal {
        transition: opacity 0.3s ease-in-out;
    }
    
    #addPermissionModal.opacity-0,
    #editPermissionModal.opacity-0 {
        opacity: 0;
    }
    
    /* Table row filtering animation */
    tbody tr {
        transition: opacity 0.15s ease-in-out;
    }
    
    tbody tr.opacity-0 {
        opacity: 0;
    }
    
    tbody tr.opacity-100 {
        opacity: 1;
    }
</style>
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endsection

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50">
    <div class="max-w-full mx-auto p-6 space-y-6">
    <!-- Header card (gradient) -->
    <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-200">
        <div class="bg-gradient-to-r from-blue-600 to-indigo-700 px-8 py-8">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                        <i class="fas fa-key text-white text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h1 class="text-2xl font-bold text-white">Permission Management</h1>
                        <p class="text-blue-100 text-sm mt-2">Manage route-based permissions and group related routes under a single permission.</p>
                    </div>
                </div>

                <div class="hidden md:flex items-center">
                    <button id="openAddPermissionBtn" class="inline-flex items-center px-4 py-3 bg-white text-indigo-700 font-semibold rounded-lg shadow-md hover:bg-gray-100 transition">
                        <i class="fas fa-plus mr-2"></i> Add Permission
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow border border-gray-100 p-6">
        <div class="flex items-start justify-between mb-4">
            <div>
                <h3 class="text-lg font-semibold text-gray-900">Permission Management</h3>
                <p class="text-sm text-gray-500 mt-1">Manage route-based permissions and group related routes under a single permission.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-gradient-to-br from-indigo-50 to-indigo-100 rounded-2xl p-6 border border-indigo-200/50 shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold text-indigo-700 uppercase tracking-wide">Total Permissions</p>
                        <p class="text-3xl font-bold text-indigo-900 mt-2">{{ count($permissions) }}</p>
                    </div>
                    <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg">
                        <i class="fas fa-key text-white text-lg"></i>
                    </div>
                </div>
                <div class="mt-4 flex items-center text-xs text-indigo-600">
                    <i class="fas fa-arrow-up mr-1"></i>
                    <span>Active permissions</span>
                </div>
            </div>
            
            <div class="bg-gradient-to-br from-emerald-50 to-emerald-100 rounded-2xl p-6 border border-emerald-200/50 shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold text-emerald-700 uppercase tracking-wide">Available Routes</p>
                        <p class="text-3xl font-bold text-emerald-900 mt-2">{{ count($availableRoutes) }}</p>
                    </div>
                    <div class="w-12 h-12 bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-xl flex items-center justify-center shadow-lg">
                        <i class="fas fa-route text-white text-lg"></i>
                    </div>
                </div>
                <div class="mt-4 flex items-center text-xs text-emerald-600">
                    <i class="fas fa-circle mr-1"></i>
                    <span>Unassigned routes</span>
                </div>
            </div>
            
            <div class="bg-gradient-to-br from-amber-50 to-amber-100 rounded-2xl p-6 border border-amber-200/50 shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold text-amber-700 uppercase tracking-wide">Assigned Routes</p>
                        <p class="text-3xl font-bold text-amber-900 mt-2">{{ \App\Models\Permission::getAllUsedRouteNames()->count() }}</p>
                    </div>
                    <div class="w-12 h-12 bg-gradient-to-br from-amber-500 to-amber-600 rounded-xl flex items-center justify-center shadow-lg">
                        <i class="fas fa-link text-white text-lg"></i>
                    </div>
                </div>
                <div class="mt-4 flex items-center text-xs text-amber-600">
                    <i class="fas fa-check-circle mr-1"></i>
                    <span>Routes in use</span>
                </div>
            </div>
            
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-2xl p-6 border border-blue-200/50 shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold text-blue-700 uppercase tracking-wide">Total Routes</p>
                        <p class="text-3xl font-bold text-blue-900 mt-2">{{ collect(\Route::getRoutes())->map(fn($route)=>$route->getName())->filter()->count() }}</p>
                    </div>
                    <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center shadow-lg">
                        <i class="fas fa-sitemap text-white text-lg"></i>
                    </div>
                </div>
                <div class="mt-4 flex items-center text-xs text-blue-600">
                    <i class="fas fa-globe mr-1"></i>
                    <span>All system routes</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
            <div class="px-8 py-6 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-xl font-bold text-gray-900 flex items-center">
                            <i class="fas fa-shield-alt text-indigo-500 mr-3"></i>
                            Permission Overview
                        </h3>
                        <p class="text-sm text-gray-600 mt-1">Manage and organize your system's permissions</p>
                    </div>
                    <div class="flex items-center space-x-3">
                        <!-- Search Filter -->
                        <div class="flex items-center space-x-2 bg-white px-4 py-2 rounded-xl border border-gray-200 shadow-sm">
                            <i class="fas fa-search text-gray-400"></i>
                            <input type="text" 
                                   id="permissionSearch" 
                                   placeholder="Search permissions..." 
                                   class="text-sm bg-transparent border-0 focus:ring-0 focus:outline-none text-gray-700 placeholder-gray-400 w-48">
                        </div>
                        <div class="hidden md:flex items-center space-x-2 bg-indigo-50 px-4 py-2 rounded-xl">
                            <i class="fas fa-filter text-indigo-500"></i>
                            <span class="text-sm font-medium text-indigo-700" id="permissionsCount">{{ count($permissions) }} Permissions</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gradient-to-r from-indigo-50 to-purple-50">
                        <tr>
                            <th class="px-8 py-5 text-left text-xs font-bold text-indigo-700 uppercase tracking-wider">ID</th>
                            <th class="px-8 py-5 text-left text-xs font-bold text-indigo-700 uppercase tracking-wider">Display Name</th>
                            <th class="px-8 py-5 text-left text-xs font-bold text-indigo-700 uppercase tracking-wider">Permission Name</th>
                            <th class="px-8 py-5 text-left text-xs font-bold text-indigo-700 uppercase tracking-wider">Routes</th>
                            <th class="px-8 py-5 text-left text-xs font-bold text-indigo-700 uppercase tracking-wider">Module</th>
                            <th class="px-8 py-5 text-left text-xs font-bold text-indigo-700 uppercase tracking-wider">Action</th>
                            <th class="px-8 py-5 text-left text-xs font-bold text-indigo-700 uppercase tracking-wider">Status</th>
                            <th class="px-8 py-5 text-right text-xs font-bold text-indigo-700 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($permissions as $permission)
                        <tr class="hover:bg-gradient-to-r hover:from-indigo-50/50 hover:to-purple-50/50 transition-all duration-200 group">
                            <td class="px-8 py-6">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-lg flex items-center justify-center shadow-sm">
                                        <span class="text-white font-bold text-xs">{{ $permission->id }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-6">
                                <div class="font-semibold text-gray-900 group-hover:text-indigo-700 transition-colors">{{ $permission->display_name }}</div>
                            </td>
                            <td class="px-8 py-6">
                                <code class="bg-gradient-to-r from-gray-100 to-gray-200 text-gray-800 px-3 py-1 rounded-lg text-sm font-medium border border-gray-300">{{ $permission->name }}</code>
                            </td>
                            <td class="px-8 py-6">
                                @php
                                    $routes = [];
                                    
                                    // Get raw attribute value to handle it properly
                                    $rawRouteNames = $permission->getAttributes()['route_names'] ?? null;
                                    
                                    // Parse route_names based on format
                                    if (is_string($rawRouteNames) && !empty($rawRouteNames)) {
                                        // Try to decode JSON if it's a JSON string
                                        $decoded = json_decode($rawRouteNames, true);
                                        if (is_array($decoded)) {
                                            $routes = $decoded;
                                        } else {
                                            // If not valid JSON, use as a single route
                                            $routes = [$rawRouteNames];
                                        }
                                    } elseif (is_array($permission->route_names) && !empty($permission->route_names)) {
                                        // Use the accessor's result if it's already an array
                                        $routes = $permission->route_names;
                                    } elseif ($permission->route_name) {
                                        // Fallback to route_name if no route_names
                                        $routes = [$permission->route_name];
                                    }
                                @endphp
                                @if(count($routes) > 0)
                                    <div class="flex flex-wrap gap-2">
                                        @foreach(array_slice($routes, 0, 2) as $route)
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gradient-to-r from-indigo-100 to-purple-100 text-indigo-800 border border-indigo-200">
                                                <i class="fas fa-route mr-1 text-indigo-600"></i>
                                                {{ $route }}
                                            </span>
                                        @endforeach
                                        @if(count($routes) > 2)
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gradient-to-r from-gray-100 to-gray-200 text-gray-700 border border-gray-300">
                                                <i class="fas fa-plus mr-1"></i>
                                                +{{ count($routes) - 2 }} more
                                            </span>
                                        @endif
                                    </div>
                                    @if(count($routes) > 1)
                                        <div class="text-xs text-emerald-600 mt-2 font-medium">
                                            <i class="fas fa-layer-group mr-1"></i>
                                            {{ count($routes) }} routes grouped
                                        </div>
                                    @endif
                                @else
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gradient-to-r from-yellow-100 to-orange-100 text-yellow-800 border border-yellow-200">
                                        <i class="fas fa-exclamation-triangle mr-1"></i>
                                        No Route
                                    </span>
                                @endif
                            </td>
                            <td class="px-8 py-6">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gradient-to-r from-gray-100 to-slate-100 text-gray-800 border border-gray-300">
                                    <i class="fas fa-folder mr-1 text-gray-600"></i>
                                    {{ $permission->module }}
                                </span>
                            </td>
                            <td class="px-8 py-6">
                                <span class="text-sm font-medium text-gray-900">{{ $permission->action }}</span>
                            </td>
                            <td class="px-8 py-6">
                                @if($permission->is_active)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-gradient-to-r from-emerald-100 to-green-100 text-emerald-800 border border-emerald-200 shadow-sm">
                                        <div class="w-2 h-2 bg-emerald-500 rounded-full mr-2 animate-pulse"></div>
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-gradient-to-r from-red-100 to-rose-100 text-red-800 border border-red-200 shadow-sm">
                                        <div class="w-2 h-2 bg-red-500 rounded-full mr-2"></div>
                                        Inactive
                                    </span>
                                @endif
                            </td>
                            <td class="px-8 py-6 text-right">
                                <div class="flex items-center justify-end space-x-2">
                                    <button class="w-8 h-8 bg-indigo-50 hover:bg-indigo-100 text-indigo-600 rounded-lg flex items-center justify-center transition-all duration-200 hover:scale-110 shadow-sm" 
                                            onclick="editPermission({{ $permission->id }})" 
                                            title="Edit Permission">
                                        <i class="fas fa-edit text-sm"></i>
                                    </button>
                                    <button class="w-8 h-8 bg-red-50 hover:bg-red-100 text-red-600 rounded-lg flex items-center justify-center transition-all duration-200 hover:scale-110 shadow-sm" 
                                            onclick="deletePermission({{ $permission->id }})" 
                                            title="Delete Permission">
                                        <i class="fas fa-trash text-sm"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="px-8 py-16 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-16 h-16 bg-gradient-to-br from-gray-100 to-gray-200 rounded-full flex items-center justify-center mb-4">
                                        <i class="fas fa-shield-alt text-gray-400 text-2xl"></i>
                                    </div>
                                    <h3 class="text-lg font-semibold text-gray-900 mb-2">No permissions found</h3>
                                    <p class="text-gray-500 text-sm">Get started by creating your first permission</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Permission Modal -->
<div id="addPermissionModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl max-w-3xl w-full mx-auto overflow-hidden transform transition-all max-h-[90vh] flex flex-col">
        <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-4 flex justify-between items-center flex-shrink-0">
            <div class="flex items-center">
                <div class="w-8 h-8 bg-white bg-opacity-20 rounded-full flex items-center justify-center mr-3">
                    <i class="fas fa-key text-white text-sm"></i>
                </div>
                <h3 class="text-lg font-semibold text-white">Add New Permission</h3>
            </div>
            <button type="button" onclick="hideAddModal()" class="text-white hover:text-gray-200 p-1 rounded hover:bg-opacity-10 transition">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="flex-1 overflow-y-auto p-6">
            <form id="addPermissionForm">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Display Name</label>
                        <input type="text" name="display_name" id="display_name" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition" placeholder="e.g., View Employee List" required>
                        <p class="text-xs text-gray-500 mt-1">User-friendly name</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Permission Name</label>
                        <input type="text" name="name" id="name" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition" placeholder="e.g., employees.view" required>
                        <p class="text-xs text-gray-500 mt-1">System name</p>
                    </div>
                </div>
                
                <!-- Route Selection - Custom Checkbox Interface -->
                <div class="mt-6">
                    <label class="block text-sm font-medium text-gray-700 mb-3">
                        Route Names 
                        <span class="routes-count-badge">{{ count($availableRoutes) }} available</span>
                    </label>
                    
                    <!-- Search and Select Controls -->
                    <div class="mb-4 flex flex-col sm:flex-row gap-3">
                        <div class="flex-1">
                            <input type="text" 
                                   id="routeSearch" 
                                   placeholder="Search routes..." 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                        </div>
                        <div class="flex gap-2">
                            <button type="button" 
                                    id="selectAllRoutes" 
                                    class="px-4 py-2 bg-indigo-100 text-indigo-700 rounded-lg hover:bg-indigo-200 transition font-medium">
                                <i class="fas fa-check-square mr-1"></i>Select All
                            </button>
                            <button type="button" 
                                    id="clearAllRoutes" 
                                    class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition font-medium">
                                <i class="fas fa-times mr-1"></i>Clear All
                            </button>
                        </div>
                    </div>
                    
                    <!-- Route Selection Container -->
                    <div class="border border-gray-300 rounded-lg max-h-64 overflow-y-auto bg-white">
                        <div id="routesContainer" class="p-4 space-y-2">
                            @if(count($availableRoutes) > 0)
                                @foreach($availableRoutes as $route)
                                    <label class="flex items-center p-3 hover:bg-indigo-50 rounded-lg cursor-pointer transition-colors route-item" data-route="{{ $route }}">
                                        <input type="checkbox" 
                                               name="route_names[]" 
                                               value="{{ $route }}" 
                                               class="w-4 h-4 text-indigo-600 bg-gray-100 border-gray-300 rounded focus:ring-indigo-500 focus:ring-2 route-checkbox">
                                        <span class="ml-3 text-sm font-medium text-gray-900 route-name">{{ $route }}</span>
                                        <span class="ml-auto text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded">{{ Str::before($route, '.') }}</span>
                                    </label>
                                @endforeach
                            @else
                                <div class="text-center py-8">
                                    <i class="fas fa-info-circle text-gray-400 text-2xl mb-2"></i>
                                    <p class="text-gray-500">No available routes - all routes are already assigned to permissions</p>
                                </div>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Selected Routes Display -->
                    <div id="selectedRoutesDisplay" class="mt-3 hidden">
                        <div class="flex flex-wrap gap-2 p-3 bg-indigo-50 border border-indigo-200 rounded-lg">
                            <span class="text-sm font-medium text-indigo-700 mr-2">Selected:</span>
                            <div id="selectedRoutesTags" class="flex flex-wrap gap-2 p-1"></div>
                        </div>
                    </div>
                    
                    <p class="text-xs text-gray-500 mt-3 flex items-start">
                        <i class="fas fa-info-circle text-indigo-500 mt-0.5 mr-2"></i>
                        <span>Select multiple routes to group under this permission. Use the search box to filter routes, or use Select All/Clear All buttons.</span>
                    </p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Module</label>
                        <input type="text" id="module" name="module" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition" placeholder="e.g., employees" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Action</label>
                        <input type="text" id="action" name="action" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition" placeholder="e.g., view" required>
                    </div>
                </div>
                <div class="mt-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <textarea id="description" name="description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition" placeholder="Optional description of what this permission allows"></textarea>
                </div>
            </form>
        </div>
        <div class="flex justify-end space-x-3 mt-8 pt-6 border-t border-gray-200 px-6 py-4 bg-gray-50 flex-shrink-0">
            <button type="button" onclick="hideAddModal()" class="px-6 py-2.5 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition duration-200 font-medium">Cancel</button>
            <button type="button" onclick="savePermission()" class="px-6 py-2.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition duration-200 font-medium">
                <i class="fas fa-save mr-2"></i>Save Permission
            </button>
        </div>
    </div>
</div>

<!-- Edit Permission Modal -->
<div id="editPermissionModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl max-w-3xl w-full mx-auto overflow-hidden transform transition-all max-h-[90vh] flex flex-col">
        <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-4 flex justify-between items-center flex-shrink-0">
            <div class="flex items-center">
                <div class="w-8 h-8 bg-white bg-opacity-20 rounded-full flex items-center justify-center mr-3">
                    <i class="fas fa-edit text-white text-sm"></i>
                </div>
                <h3 class="text-lg font-semibold text-white">Edit Permission</h3>
            </div>
            <button type="button" onclick="hideEditModal()" class="text-white hover:text-gray-200 p-1 rounded hover:bg-opacity-10 transition">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="flex-1 overflow-y-auto p-6">
            <form id="editPermissionForm">
                @csrf
                <input type="hidden" name="id" id="edit_id">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Display Name</label>
                        <input type="text" name="display_name" id="edit_display_name" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Permission Name</label>
                        <input type="text" name="name" id="edit_name" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition" required>
                    </div>
                </div>
                
                <!-- Route Selection - Custom Checkbox Interface (Same as Add Modal) -->
                <div class="mt-6">
                    <label class="block text-sm font-medium text-gray-700 mb-3">
                        Route Names 
                        <span id="edit-routes-count-badge" class="routes-count-badge">0 available</span>
                    </label>
                    
                    <!-- Search and Select Controls -->
                    <div class="mb-4 flex flex-col sm:flex-row gap-3">
                        <div class="flex-1">
                            <input type="text" 
                                   id="editRouteSearch" 
                                   placeholder="Search routes..." 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                        </div>
                        <div class="flex gap-2">
                            <button type="button" 
                                    id="editSelectAllRoutes" 
                                    class="px-4 py-2 bg-indigo-100 text-indigo-700 rounded-lg hover:bg-indigo-200 transition font-medium">
                                <i class="fas fa-check-square mr-1"></i>Select All
                            </button>
                            <button type="button" 
                                    id="editClearAllRoutes" 
                                    class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition font-medium">
                                <i class="fas fa-times mr-1"></i>Clear All
                            </button>
                        </div>
                    </div>
                    
                    <!-- Route Selection Container -->
                    <div class="border border-gray-300 rounded-lg max-h-64 overflow-y-auto bg-white">
                        <div id="editRoutesContainer" class="p-4 space-y-2">
                            <!-- Routes will be populated dynamically -->
                        </div>
                    </div>
                    
                    <!-- Selected Routes Display -->
                    <div id="editSelectedRoutesDisplay" class="mt-3 hidden">
                        <div class="flex flex-wrap gap-2 p-3 bg-indigo-50 border border-indigo-200 rounded-lg">
                            <span class="text-sm font-medium text-indigo-700 mr-2">Selected:</span>
                            <div id="editSelectedRoutesTags" class="flex flex-wrap gap-2 p-1"></div>
                        </div>
                    </div>
                    
                    <p class="text-xs text-gray-500 mt-3 flex items-start">
                        <i class="fas fa-info-circle text-indigo-500 mt-0.5 mr-2"></i>
                        <span>Select multiple routes to group under this permission. Current routes are pre-selected.</span>
                    </p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Module</label>
                        <input type="text" id="edit_module" name="module" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Action</label>
                        <input type="text" id="edit_action" name="action" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition" required>
                    </div>
                </div>
                <div class="mt-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <textarea id="edit_description" name="description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"></textarea>
                </div>
            </form>
        </div>
        <div class="flex justify-end space-x-3 mt-8 pt-6 border-t border-gray-200 px-6 py-4 bg-gray-50 flex-shrink-0">
            <button type="button" onclick="hideEditModal()" class="px-6 py-2.5 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition duration-200 font-medium">Cancel</button>
            <button type="button" onclick="updatePermission()" class="px-6 py-2.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition duration-200 font-medium">
                <i class="fas fa-save mr-2"></i>Update Permission
            </button>
        </div>
    </div>
</div>

@push('scripts')
<!-- Select2 JS (only for edit modal) -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
// DOM ready
$(document).ready(function() {
    console.log('Initializing permission management...');
    
    // Initialize custom route selection for add modal
    initializeRouteSelection('add');
    
    // Wire up Add button
    $('#openAddPermissionBtn').on('click', showAddModal);
    
    // Initialize search functionality
    initializeSearchFilter();
});

// Initialize search filter functionality
function initializeSearchFilter() {
    const searchInput = document.getElementById('permissionSearch');
    const permissionsCount = document.getElementById('permissionsCount');
    const tableBody = document.querySelector('tbody');
    const totalPermissions = {{ count($permissions) }};
    
    if (!searchInput || !tableBody) return;
    
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase().trim();
        const rows = tableBody.querySelectorAll('tr');
        let visibleCount = 0;
        
        rows.forEach(row => {
            const cells = row.querySelectorAll('td');
            let isVisible = false;
            
            if (cells.length >= 6) { // Ensure we have enough cells
                // Search in: Display Name (index 1), Permission Name (index 2), Module (index 4), Action (index 5)
                const displayName = cells[1]?.textContent?.toLowerCase() || '';
                const permissionName = cells[2]?.textContent?.toLowerCase() || '';
                const module = cells[4]?.textContent?.toLowerCase() || '';
                const action = cells[5]?.textContent?.toLowerCase() || '';
                
                // Check if search term matches any of the searchable fields
                if (searchTerm === '' || 
                    displayName.includes(searchTerm) || 
                    permissionName.includes(searchTerm) || 
                    module.includes(searchTerm) || 
                    action.includes(searchTerm)) {
                    isVisible = true;
                    visibleCount++;
                }
            }
            
            // Show/hide row with animation
            if (isVisible) {
                row.style.display = '';
                row.classList.remove('opacity-0');
                row.classList.add('opacity-100');
            } else {
                row.classList.add('opacity-0');
                setTimeout(() => {
                    row.style.display = 'none';
                }, 150); // Match transition duration
            }
        });
        
        // Update the count display
        if (searchTerm === '') {
            permissionsCount.textContent = totalPermissions + ' Permissions';
        } else {
            permissionsCount.textContent = visibleCount + ' of ' + totalPermissions + ' Permissions';
        }
    });
    
    // Add clear search functionality
    searchInput.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            this.value = '';
            this.dispatchEvent(new Event('input'));
            this.blur();
        }
    });
}

// Initialize custom route selection functionality
function initializeRouteSelection(mode = 'add') {
    const prefix = mode === 'edit' ? 'edit' : '';
    const searchInput = document.getElementById(prefix + (prefix ? 'R' : 'r') + 'outeSearch');
    const selectAllBtn = document.getElementById(prefix + 'SelectAllRoutes');
    const clearAllBtn = document.getElementById(prefix + 'ClearAllRoutes');
    const containerSelector = mode === 'edit' ? '#editRoutesContainer' : '#routesContainer';
    
    const routeItems = document.querySelectorAll(`${containerSelector} .route-item`);
    const checkboxes = document.querySelectorAll(`${containerSelector} .route-checkbox`);
    
    // Search functionality
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            
            routeItems.forEach(item => {
                const routeName = item.querySelector('.route-name').textContent.toLowerCase();
                if (routeName.includes(searchTerm)) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    }
    
    // Select All button
    if (selectAllBtn) {
        selectAllBtn.addEventListener('click', function() {
            const visibleCheckboxes = document.querySelectorAll(`${containerSelector} .route-item[style*="display: flex"] .route-checkbox, ${containerSelector} .route-item:not([style*="display: none"]) .route-checkbox`);
            visibleCheckboxes.forEach(checkbox => {
                checkbox.checked = true;
            });
            updateSelectedRoutesDisplay(mode);
        });
    }
    
    // Clear All button
    if (clearAllBtn) {
        clearAllBtn.addEventListener('click', function() {
            checkboxes.forEach(checkbox => {
                checkbox.checked = false;
            });
            updateSelectedRoutesDisplay(mode);
        });
    }
    
    // Individual checkbox change events
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', () => updateSelectedRoutesDisplay(mode));
    });
}

// Update selected routes display
function updateSelectedRoutesDisplay(mode = 'add') {
    const prefix = mode === 'edit' ? 'edit' : '';
    const containerSelector = mode === 'edit' ? '#editRoutesContainer' : '#routesContainer';
    
    const selectedCheckboxes = document.querySelectorAll(`${containerSelector} .route-checkbox:checked`);
    const displayDiv = document.getElementById(prefix + 'SelectedRoutesDisplay');
    const tagsContainer = document.getElementById(prefix + 'SelectedRoutesTags');
    
    // Debug log to check what's being selected
    console.log(`${mode} mode - Selected checkboxes:`, selectedCheckboxes.length);
    
    if (selectedCheckboxes.length > 0) {
        displayDiv.classList.remove('hidden');
        tagsContainer.innerHTML = '';
        
        // Create a tag for each selected route
        selectedCheckboxes.forEach(checkbox => {
            createRouteTag(checkbox.value, tagsContainer, mode);
        });
    } else {
        displayDiv.classList.add('hidden');
    }
}

// Create route tag element
function createRouteTag(routeName, container, mode) {
    console.log(`Creating tag for ${routeName} in ${mode} mode`);
    
    const tag = document.createElement('span');
    tag.className = 'inline-flex items-center px-2 py-1 m-1 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800';
    tag.innerHTML = `
        ${routeName}
        <button type="button" class="ml-1 text-indigo-600 hover:text-indigo-800" onclick="removeRouteTag('${routeName}', '${mode}')">
            <i class="fas fa-times text-xs"></i>
        </button>
    `;
    container.appendChild(tag);
}

// Remove route tag function
function removeRouteTag(routeName, mode = 'add') {
    const containerSelector = mode === 'edit' ? '#editRoutesContainer' : '#routesContainer';
    const checkbox = document.querySelector(`${containerSelector} .route-checkbox[value="${routeName}"]`);
    if (checkbox) {
        checkbox.checked = false;
        updateSelectedRoutesDisplay(mode);
    }
}

// Modal functions
function showAddModal() {
    const el = document.getElementById('addPermissionModal');
    el.classList.remove('hidden');
    el.classList.add('flex');
    
    // Reset form
    document.getElementById('addPermissionForm').reset();
    
    // Clear any previous errors
    clearFormErrors('addPermissionForm');
    
    // Reset custom route selection
    document.querySelectorAll('.route-checkbox').forEach(cb => cb.checked = false);
    document.getElementById('routeSearch').value = '';
    document.querySelectorAll('.route-item').forEach(item => item.style.display = 'flex');
    updateSelectedRoutesDisplay();
    
    // Focus on first field
    setTimeout(() => {
        document.getElementById('display_name').focus();
    }, 100);
}

function hideAddModal() {
    const el = document.getElementById('addPermissionModal');
    
    // Add fade-out animation
    el.classList.add('opacity-0');
    setTimeout(() => {
        el.classList.add('hidden');
        el.classList.remove('flex', 'opacity-0');
    }, 300);
}

function showEditModal() {
    const el = document.getElementById('editPermissionModal');
    el.classList.remove('hidden');
    el.classList.add('flex');
    
    // Clear any previous errors
    clearFormErrors('editPermissionForm');
    
    // Focus on first field
    setTimeout(() => {
        document.getElementById('edit_display_name').focus();
    }, 100);
}

function hideEditModal() {
    const el = document.getElementById('editPermissionModal');
    
    // Add fade-out animation
    el.classList.add('opacity-0');
    setTimeout(() => {
        el.classList.add('hidden');
        el.classList.remove('flex', 'opacity-0');
    }, 300);
}

// Clear validation errors
function clearFormErrors(formId) {
    const form = document.getElementById(formId);
    if (!form) return;
    
    // Remove all error messages
    form.querySelectorAll('.error-message').forEach(el => el.remove());
    
    // Remove error classes
    form.querySelectorAll('.border-red-500').forEach(el => {
        el.classList.remove('border-red-500');
        el.classList.remove('focus:ring-red-500');
        el.classList.remove('focus:border-red-500');
    });
}

// Display validation errors
function displayErrors(formId, errors) {
    const form = document.getElementById(formId);
    if (!form) return;
    
    clearFormErrors(formId);
    
    Object.keys(errors).forEach(field => {
        const inputElement = form.querySelector(`[name="${field}"], [name="${field}[]"]`);
        
        if (inputElement) {
            // Add red border to the input field
            inputElement.classList.add('border-red-500');
            inputElement.classList.add('focus:ring-red-500');
            inputElement.classList.add('focus:border-red-500');
            
            // Create and append error message
            const errorDiv = document.createElement('div');
            errorDiv.className = 'error-message text-red-600 text-sm mt-1';
            errorDiv.innerText = errors[field][0];
            
            // For select2 and custom input groups, find the proper parent
            let parent;
            if (field === 'route_names') {
                parent = document.querySelector('#routesContainer').parentElement;
            } else {
                parent = inputElement.parentElement;
            }
            
            if (parent) parent.appendChild(errorDiv);
        }
    });
}

// Display toast notifications
function showToast(message, type = 'success') {
    // Create the toast container if it doesn't exist
    let toastContainer = document.getElementById('toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toast-container';
        toastContainer.className = 'fixed top-4 right-4 z-50 flex flex-col space-y-4 max-w-md';
        document.body.appendChild(toastContainer);
    }
    
    // Create toast
    const toast = document.createElement('div');
    toast.className = `toast transform transition-all duration-300 opacity-0 translate-x-8 flex items-center p-4 mb-4 rounded-lg shadow-lg min-w-max`;
    
    // Set colors based on type
    if (type === 'success') {
        toast.classList.add('bg-gradient-to-r', 'from-green-500', 'to-emerald-600', 'text-white');
    } else if (type === 'error') {
        toast.classList.add('bg-gradient-to-r', 'from-red-500', 'to-red-600', 'text-white');
    } else if (type === 'warning') {
        toast.classList.add('bg-gradient-to-r', 'from-amber-500', 'to-amber-600', 'text-white');
    } else {
        toast.classList.add('bg-gradient-to-r', 'from-blue-500', 'to-blue-600', 'text-white');
    }
    
    // Set icon based on type
    let icon;
    if (type === 'success') {
        icon = '<i class="fas fa-check-circle text-xl mr-3"></i>';
    } else if (type === 'error') {
        icon = '<i class="fas fa-exclamation-circle text-xl mr-3"></i>';
    } else if (type === 'warning') {
        icon = '<i class="fas fa-exclamation-triangle text-xl mr-3"></i>';
    } else {
        icon = '<i class="fas fa-info-circle text-xl mr-3"></i>';
    }
    
    // Set content
    toast.innerHTML = `
        <div class="flex items-center">
            ${icon}
            <div>
                <p class="font-medium">${message}</p>
            </div>
        </div>
        <button class="ml-auto flex-shrink-0 p-2 rounded-full hover:bg-black hover:bg-opacity-10 transition-colors">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    // Add close button functionality
    const closeButton = toast.querySelector('button');
    closeButton.addEventListener('click', () => {
        toast.classList.add('opacity-0', 'translate-x-8');
        setTimeout(() => {
            toast.remove();
        }, 300);
    });
    
    // Add to container and animate in
    toastContainer.appendChild(toast);
    setTimeout(() => {
        toast.classList.remove('opacity-0', 'translate-x-8');
    }, 10);
    
    // Auto dismiss after 5 seconds
    setTimeout(() => {
        if (toast.parentElement) {
            toast.classList.add('opacity-0', 'translate-x-8');
            setTimeout(() => {
                if (toast.parentElement) {
                    toast.remove();
                }
            }, 300);
        }
    }, 5000);
}

// Permission CRUD functions
function savePermission() {
    const form = document.getElementById('addPermissionForm');
    const formData = new FormData(form);
    
    // Get selected routes from checkboxes
    const selectedCheckboxes = document.querySelectorAll('#routesContainer .route-checkbox:checked');
    const selected = Array.from(selectedCheckboxes).map(cb => cb.value);
    console.log('Add mode - Selected routes:', selected);
    
    // Clear previous errors
    clearFormErrors('addPermissionForm');
    
    // Client-side validation
    const displayName = formData.get('display_name')?.trim();
    const name = formData.get('name')?.trim();
    const module = formData.get('module')?.trim();
    const action = formData.get('action')?.trim();
    
    let hasError = false;
    const clientErrors = {};
    
    if (!displayName) {
        clientErrors['display_name'] = ['Display Name is required.'];
        hasError = true;
    }
    if (!name) {
        clientErrors['name'] = ['Permission Name is required.'];
        hasError = true;
    }
    if (!module) {
        clientErrors['module'] = ['Module is required.'];
        hasError = true;
    }
    if (!action) {
        clientErrors['action'] = ['Action is required.'];
        hasError = true;
    }
    
    // Get selected routes from checkboxes (don't add them again since they're already in FormData)
    const selectedRoutes = Array.from(document.querySelectorAll('.route-checkbox:checked')).map(cb => cb.value);
    
    if (!selectedRoutes || selectedRoutes.length === 0) {
        clientErrors['route_names'] = ['Please select at least one route for this permission.'];
        hasError = true;
    }
    
    if (hasError) {
        displayErrors('addPermissionForm', clientErrors);
        return;
    }
    
    const saveBtn = $('#addPermissionModal button[onclick="savePermission()"]');
    const orig = saveBtn.html();
    saveBtn.html('<i class="fas fa-spinner fa-spin mr-2"></i>Saving...').prop('disabled', true);

    fetch('{{ route("permissions.save") }}', {
        method: 'POST',
        body: formData,
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') }
    })
    .then(response => response.json().then(data => ({ status: response.status, body: data })))
    .then(({ status, body }) => {
        if (body.success) { 
            hideAddModal(); 
            showToast(body.message + ' - Refreshing available routes...', 'success');
            // Reload the page to refresh available routes list
            // This ensures that assigned routes won't appear in future additions
            setTimeout(() => location.reload(), 1000); 
        } else {
            if (status === 422 && body.errors) {
                // Display validation errors
                displayErrors('addPermissionForm', body.errors);
            }
            showToast(body.message, 'error');
        }
    })
    .catch(e => { 
        console.error(e); 
        showToast('Failed to save permission', 'error');
    })
    .finally(() => { 
        saveBtn.html(orig).prop('disabled', false); 
    });
}

function editPermission(id) {
    // Show loading toast
    showToast('Loading permission details...', 'info');
    
    fetch(`{{ url('permissions/get') }}/${id}`)
    .then(r => r.json())
    .then(data => {
        if (!data.success) { 
            showToast('Error: ' + data.message, 'error'); 
            return; 
        }
        
        const p = data.permission;
        document.getElementById('edit_id').value = p.id;
        document.getElementById('edit_display_name').value = p.display_name;
        document.getElementById('edit_name').value = p.name;
        document.getElementById('edit_module').value = p.module;
        document.getElementById('edit_action').value = p.action;
        document.getElementById('edit_description').value = p.description || '';

        // Ensure route_names is properly parsed as an array
        let currentRoutes = [];
        
        // Check if route_names exists and parse it if it's a string
        console.log('Raw route_names value:', p.route_names);
        console.log('route_names type:', typeof p.route_names);
        
        if (p.route_names) {
            if (typeof p.route_names === 'string') {
                try {
                    console.log('Attempting to parse route_names as JSON string');
                    currentRoutes = JSON.parse(p.route_names);
                    console.log('Successfully parsed route_names:', currentRoutes);
                } catch (e) {
                    console.error('Error parsing route_names:', e);
                    currentRoutes = [];
                }
            } else if (Array.isArray(p.route_names)) {
                console.log('route_names is already an array');
                currentRoutes = p.route_names;
            } else {
                console.log('route_names is neither string nor array, type is:', typeof p.route_names);
            }
        }
        
        // Fall back to route_name if route_names is empty
        if (!currentRoutes.length && p.route_name) {
            currentRoutes = [p.route_name];
        }
        
        console.log('Current routes:', currentRoutes);
        
        // Populate the routes container with checkboxes
        const routesContainer = document.getElementById('editRoutesContainer');
        routesContainer.innerHTML = ''; // Clear existing routes
        
        // Update the routes count badge
        document.getElementById('edit-routes-count-badge').textContent = 
            `${data.availableRoutes.length} available`;
        
        if (data.availableRoutes && data.availableRoutes.length > 0) {
            data.availableRoutes.forEach(route => {
                // Check if the route is in currentRoutes
                const isSelected = Array.isArray(currentRoutes) && currentRoutes.includes(route);
                
                const routeItem = document.createElement('label');
                routeItem.className = 'flex items-center p-3 hover:bg-indigo-50 rounded-lg cursor-pointer transition-colors route-item';
                routeItem.dataset.route = route;
                
                routeItem.innerHTML = `
                    <input type="checkbox" 
                           name="route_names[]" 
                           value="${route}" 
                           class="w-4 h-4 text-indigo-600 bg-gray-100 border-gray-300 rounded focus:ring-indigo-500 focus:ring-2 route-checkbox"
                           ${isSelected ? 'checked' : ''}>
                    <span class="ml-3 text-sm font-medium text-gray-900 route-name">${route}</span>
                    <span class="ml-auto text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded">${route.split('.')[0]}</span>
                `;
                
                routesContainer.appendChild(routeItem);
            });
            
            // Initialize the custom route selection for edit modal
            initializeRouteSelection('edit');
            
            // Update the selected routes display
            updateSelectedRoutesDisplay('edit');
        } else {
            routesContainer.innerHTML = `
                <div class="text-center py-8">
                    <i class="fas fa-info-circle text-gray-400 text-2xl mb-2"></i>
                    <p class="text-gray-500">No available routes - all routes are already assigned to permissions</p>
                </div>
            `;
        }
        
        showEditModal();
    })
    .catch(e => { 
        console.error(e); 
        showToast('Failed to load permission', 'error'); 
    });
}

function updatePermission() {
    const form = document.getElementById('editPermissionForm');
    const fd = new FormData(form);
    
    // Get selected routes from checkboxes
    const selectedCheckboxes = document.querySelectorAll('#editRoutesContainer .route-checkbox:checked');
    const selected = Array.from(selectedCheckboxes).map(cb => cb.value);
    console.log('Selected routes:', selected);
    
    // Clear previous errors
    clearFormErrors('editPermissionForm');
    
    // Client-side validation
    let hasError = false;
    const clientErrors = {};
    
    // Ensure route_names is an array with at least one element
    if (!selected || selected.length === 0) { 
        clientErrors['route_names'] = ['Please select at least one route.'];
        hasError = true;
    } else {
        // Add selected routes to the form data explicitly
        // Remove existing route_names entries first
        for(let pair of fd.entries()) {
            if(pair[0] === 'route_names[]') {
                fd.delete(pair[0]);
            }
        }
        
        // Add each selected route
        selected.forEach(route => {
            fd.append('route_names[]', route);
        });
    }
    
    // Check other required fields
    const displayName = fd.get('display_name')?.trim();
    const name = fd.get('name')?.trim();
    const module = fd.get('module')?.trim();
    const action = fd.get('action')?.trim();
    
    if (!displayName) {
        clientErrors['display_name'] = ['Display Name is required.'];
        hasError = true;
    }
    if (!name) {
        clientErrors['name'] = ['Permission Name is required.'];
        hasError = true;
    }
    if (!module) {
        clientErrors['module'] = ['Module is required.'];
        hasError = true;
    }
    if (!action) {
        clientErrors['action'] = ['Action is required.'];
        hasError = true;
    }
    
    if (hasError) {
        displayErrors('editPermissionForm', clientErrors);
        return;
    }

    const btn = $('#editPermissionModal button[onclick="updatePermission()"]'); 
    const orig = btn.html(); 
    btn.html('<i class="fas fa-spinner fa-spin mr-2"></i>Updating...').prop('disabled', true);
    
    fetch('{{ route("permissions.update") }}', { 
        method: 'POST', 
        body: fd, 
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') } 
    })
    .then(response => response.json().then(data => ({ status: response.status, body: data })))
    .then(({ status, body }) => {
        if (body.success) { 
            hideEditModal(); 
            showToast(body.message + ' - Refreshing available routes...', 'success');
            setTimeout(() => location.reload(), 1000); 
        } else {
            if (status === 422 && body.errors) {
                // Display validation errors
                displayErrors('editPermissionForm', body.errors);
            }
            showToast(body.message, 'error');
        }
    })
    .catch(e => { 
        console.error(e); 
        showToast('Failed to update permission', 'error'); 
    })
    .finally(() => { 
        btn.html(orig).prop('disabled', false); 
    });
}

function deletePermission(id) {
    // Create and show confirmation modal
    const confirmModal = document.createElement('div');
    confirmModal.className = 'fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4';
    confirmModal.innerHTML = `
        <div class="bg-white rounded-xl shadow-xl max-w-md w-full mx-auto overflow-hidden transform transition-all">
            <div class="bg-gradient-to-r from-red-600 to-red-700 px-6 py-4">
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-white bg-opacity-20 rounded-full flex items-center justify-center mr-3">
                        <i class="fas fa-exclamation-triangle text-white text-sm"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-white">Confirm Delete</h3>
                </div>
            </div>
            <div class="p-6">
                <p class="text-gray-700 mb-4">Are you sure you want to delete this permission? This action cannot be undone.</p>
                
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" id="cancelDeleteBtn" class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition duration-200 font-medium">Cancel</button>
                    <button type="button" id="confirmDeleteBtn" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition duration-200 font-medium">
                        <i class="fas fa-trash mr-2"></i>Delete Permission
                    </button>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(confirmModal);
    
    // Add event listeners
    document.getElementById('cancelDeleteBtn').addEventListener('click', () => {
        confirmModal.remove();
    });
    
    document.getElementById('confirmDeleteBtn').addEventListener('click', () => {
        confirmModal.remove();
        
        // Show loading toast
        showToast('Deleting permission...', 'info');
        
        const fd = new FormData(); 
        fd.append('id', id);
        
        fetch('{{ route("permissions.delete") }}', { 
            method: 'POST', 
            body: fd, 
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') } 
        })
        .then(response => response.json().then(data => ({ status: response.status, body: data })))
        .then(({ status, body }) => {
            if (body.success) { 
                showToast(body.message + ' - Refreshing available routes...', 'success');
                setTimeout(() => location.reload(), 1000); 
            } else {
                showToast(body.message, 'error');
            }
        })
        .catch(e => { 
            console.error(e); 
            showToast('Failed to delete permission', 'error'); 
        });
    });
}

// Expose functions to global scope
window.showAddModal = showAddModal;
window.hideAddModal = hideAddModal;
window.showEditModal = showEditModal;
window.hideEditModal = hideEditModal;
window.editPermission = editPermission;
window.deletePermission = deletePermission;
</script>
@endpush

@endsection