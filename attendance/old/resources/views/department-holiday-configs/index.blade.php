@extends('layouts.app')

@section('title', 'Holiday Quotas - HRMS')
@section('page-title', 'Holiday Quotas')

@section('content')
<div class="min-h-screen bg-gray-50 py-6 px-6">
    <div class="max-w-full mx-auto space-y-8">
        <!-- Success Message -->
        @if(session('success'))
            <div class="bg-green-50 border-l-4 border-green-400 p-4 rounded-r-md shadow-sm">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-check-circle text-green-400 text-lg"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Info Message -->
        @if(session('info'))
            <div class="bg-blue-50 border-l-4 border-blue-400 p-4 rounded-r-md shadow-sm">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-info-circle text-blue-400 text-lg"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-blue-800">{{ session('info') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Error Message -->
        @if(session('error'))
            <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded-r-md shadow-sm">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-circle text-red-400 text-lg"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Header Card -->
        <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-200">
            <div class="bg-gradient-to-r from-blue-600 to-indigo-700 px-8 py-10">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                                <i class="fas fa-users-cog text-white text-xl"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <h1 class="text-2xl font-bold text-white">Holiday Quotas by Department</h1>
                            <p class="text-blue-100 text-sm mt-2">
                                Configure holiday quotas per employee for each department in financial year {{ $selectedYear }}
                            </p>
                        </div>
                    </div>
                    <div class="hidden md:flex items-center">
                        <div class="w-16 h-16 bg-white bg-opacity-10 rounded-full flex items-center justify-center">
                            <i class="fas fa-cogs text-white text-2xl"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters & Actions -->
        <div class="bg-white rounded-2xl shadow p-6 border border-gray-100">
            <div class="flex flex-col lg:flex-row lg:items-center gap-4">
                <div class="flex-1 max-w-sm">
                    <!-- Financial Year Filter -->
                    <select id="financialYearFilter" class="w-full px-4 py-3 text-base border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-400 focus:border-blue-400 bg-gray-50 transition-colors">
                        @foreach($financialYears as $year => $label)
                            <option value="{{ $year }}" {{ $year == $selectedYear ? 'selected' : '' }}>
                                FY {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex gap-3">
                    <a href="{{ route('holiday-department-configs.create') }}" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-semibold rounded-lg shadow-md hover:from-blue-700 hover:to-indigo-700 transition-all duration-300">
                        <i class="fas fa-plus mr-2"></i> Add Configuration
                    </a>
                </div>
            </div>
        </div>

        <!-- Configurations Table -->
        <div class="bg-white rounded-2xl shadow border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b bg-gradient-to-r from-gray-50 to-gray-100">
                <h3 class="text-xl font-bold text-gray-800 flex items-center">
                    <i class="fas fa-list text-blue-600 mr-2"></i>
                    Holiday Configurations for FY {{ $selectedYear }}
                </h3>
            </div>

            @if($configs->count() > 0)
                <!-- Desktop Table -->
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 border-b">
                            <tr>
                                <th class="px-6 py-4 text-left font-semibold text-gray-500 uppercase">Department</th>
                                <th class="px-6 py-4 text-left font-semibold text-gray-500 uppercase">Employees</th>
                                <th class="px-6 py-4 text-left font-semibold text-gray-500 uppercase">Total per Employee</th>
                                <th class="px-6 py-4 text-left font-semibold text-gray-500 uppercase">Fixed / Flexible</th>
                                <th class="px-6 py-4 text-left font-semibold text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-4 text-left font-semibold text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @foreach($configs as $config)
                                <tr class="hover:bg-blue-50 transition">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            @if($config->department)
                                                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                                                    <i class="fas fa-building text-blue-600"></i>
                                                </div>
                                                <div>
                                                    <div class="font-semibold text-gray-900">{{ $config->department->name }}</div>
                                                    <div class="text-xs text-gray-500">{{ $config->department->code }}</div>
                                                </div>
                                            @else
                                                <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center mr-3">
                                                    <i class="fas fa-exclamation-triangle text-red-600"></i>
                                                </div>
                                                <div>
                                                    <div class="font-semibold text-red-900">Department Not Found</div>
                                                    <div class="text-xs text-red-500">ID: {{ $config->department_id }}</div>
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <i class="fas fa-users text-gray-400 mr-2"></i>
                                            <span class="text-lg font-bold text-gray-700">{{ $config->employee_count }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="text-lg font-bold text-blue-600">{{ $config->allowed_holidays }}</span>
                                        <div class="text-xs text-gray-500">quota per employee</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="space-y-1">
                                            <div class="flex items-center gap-2">
                                                <span class="text-sm font-medium text-gray-600">Fixed:</span>
                                                <span class="text-sm font-bold text-indigo-600">{{ $config->fixed_public_holidays ?? 0 }}</span>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <span class="text-sm font-medium text-gray-600">Flexible:</span>
                                                <span class="text-sm font-bold text-purple-600">{{ $config->flexible_public_holidays ?? 0 }}</span>
                                            </div>
                                        </div>
                                    </td>
                                  
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold
                                            {{ $config->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            <i class="fas {{ $config->is_active ? 'fa-check-circle' : 'fa-times-circle' }} mr-1"></i>
                                            {{ $config->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-2">
                                            <a href="{{ route('holiday-department-configs.show', $config) }}" 
                                               class="text-blue-600 hover:text-blue-900 p-2 rounded-lg hover:bg-blue-100 transition" 
                                               title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('holiday-department-configs.edit', $config) }}" 
                                               class="text-green-600 hover:text-green-900 p-2 rounded-lg hover:bg-green-100 transition" 
                                               title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @if($config->used_holidays == 0)
                                                <form action="{{ route('holiday-department-configs.destroy', $config) }}" 
                                                      method="POST" 
                                                      class="inline" 
                                                      onsubmit="return confirm('Are you sure you want to delete this configuration?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" 
                                                            class="text-red-600 hover:text-red-900 p-2 rounded-lg hover:bg-red-100 transition" 
                                                            title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-16">
                    <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-5 shadow">
                        <i class="fas fa-cogs text-gray-400 text-3xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-2">No Configurations Found</h3>
                    <p class="text-gray-500 mb-6">No holiday configurations found for the selected financial year.</p>
                    <a href="{{ route('holiday-department-configs.create') }}" 
                       class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-semibold rounded-lg shadow hover:from-blue-700 hover:to-indigo-700 transition">
                        <i class="fas fa-plus mr-2"></i>
                        Add First Configuration
                    </a>
                </div>
            @endif
        </div>

        <!-- Departments Without Configuration -->
        @if($departmentsWithoutConfig->count() > 0)
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-r-md shadow-sm">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-triangle text-yellow-400 text-lg"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-yellow-800">
                            {{ $departmentsWithoutConfig->count() }} departments don't have holiday configuration for FY {{ $selectedYear }}:
                        </h3>
                        <div class="mt-2 text-sm text-yellow-700">
                            <ul class="list-disc list-inside">
                                @foreach($departmentsWithoutConfig as $dept)
                                    <li>{{ $dept->name }} ({{ $dept->code }})</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Admin Tools Section -->
        @if(Auth::user()->role === 'super_admin')
            <div class="bg-gray-50 rounded-xl shadow-sm p-6 border border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">
                    <i class="fas fa-tools text-gray-600 mr-2"></i>
                    Admin Tools
                </h3>
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-sm font-medium text-gray-800">Clean Up Orphaned Configurations</h4>
                        <p class="text-sm text-gray-600">Remove configurations that reference deleted departments</p>
                    </div>
                    <form method="POST" action="{{ route('holiday-department-configs.cleanup-orphaned') }}" class="inline">
                        @csrf
                        <button type="submit" 
                                class="inline-flex items-center px-4 py-2 text-sm font-medium text-red-700 bg-red-100 border border-red-200 rounded-lg hover:bg-red-200 hover:text-red-800 transition-colors"
                                onclick="return confirm('Are you sure you want to clean up orphaned configurations? This action cannot be undone.')">
                            <i class="fas fa-broom mr-2"></i>
                            Clean Up
                        </button>
                    </form>
                </div>
            </div>
        @endif
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const financialYearFilter = document.getElementById('financialYearFilter');

    function updateFilters() {
        const params = new URLSearchParams();
        if (financialYearFilter.value) params.set('financial_year', financialYearFilter.value);
        
        window.location.search = params.toString();
    }

    financialYearFilter.addEventListener('change', updateFilters);
});
</script>
@endsection
