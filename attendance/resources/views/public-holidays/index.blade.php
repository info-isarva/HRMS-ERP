@extends('layouts.app')

@section('title', 'Public Holidays - HRMS')
@section('page-title', 'Public Holidays')

@section('content')
<div class="min-h-screen bg-gray-50 py-4 sm:py-6 px-4 sm:px-6">
    <div class="container mx-auto max-w-full space-y-4 sm:space-y-8">
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

        <!-- Header Card -->
        <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-200">
            <div class="bg-gradient-to-r from-emerald-600 to-teal-700 px-4 sm:px-8 py-6 sm:py-10">
                <div class="flex flex-col sm:flex-row items-center justify-between">
                    <div class="flex items-center mb-4 sm:mb-0">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 sm:w-12 sm:h-12 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                                <i class="fas fa-calendar-day text-white text-lg sm:text-xl"></i>
                            </div>
                        </div>
                        <div class="ml-3 sm:ml-4">
                            <h1 class="text-xl sm:text-2xl font-bold text-white">Public Holidays</h1>
                            <p class="text-emerald-100 text-xs sm:text-sm mt-1 sm:mt-2">
                                Manage public holidays for financial year {{ $selectedYear }}
                            </p>
                        </div>
                    </div>
                    <div class="hidden md:flex items-center">
                        <div class="w-12 h-12 sm:w-16 sm:h-16 bg-white bg-opacity-10 rounded-full flex items-center justify-center">
                            <i class="fas fa-umbrella-beach text-white text-lg sm:text-2xl"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 sm:gap-6">
            <div class="bg-white rounded-xl shadow hover:shadow-lg transition-shadow duration-300 p-4 sm:p-6 border-l-4" style="border-left-color: #3b82f6;">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 font-medium text-xs sm:text-sm">Total Holidays</p>
                        <p class="text-2xl sm:text-3xl font-bold text-blue-700 mt-1">{{ $stats['total'] }}</p>
                    </div>
                    <div class="w-10 h-10 sm:w-12 sm:h-12 bg-blue-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-calendar text-blue-500 text-lg sm:text-xl"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow hover:shadow-lg transition-shadow duration-300 p-4 sm:p-6 border-l-4" style="border-left-color: #10b981;">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 font-medium text-xs sm:text-sm">Active</p>
                        <p class="text-2xl sm:text-3xl font-bold text-green-600 mt-1">{{ $stats['active'] }}</p>
                    </div>
                    <div class="w-10 h-10 sm:w-12 sm:h-12 bg-green-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-check-circle text-green-500 text-lg sm:text-xl"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow hover:shadow-lg transition-shadow duration-300 p-4 sm:p-6 border-l-4" style="border-left-color: #8b5cf6;">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 font-medium text-xs sm:text-sm">Upcoming</p>
                        <p class="text-2xl sm:text-3xl font-bold text-purple-600 mt-1">{{ $stats['upcoming'] }}</p>
                    </div>
                    <div class="w-10 h-10 sm:w-12 sm:h-12 bg-purple-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-clock text-purple-500 text-lg sm:text-xl"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow hover:shadow-lg transition-shadow duration-300 p-4 sm:p-6 border-l-4" style="border-left-color: #f59e0b;">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 font-medium text-xs sm:text-sm">Fixed</p>
                        <p class="text-2xl sm:text-3xl font-bold text-orange-600 mt-1">{{ $stats['fixed'] }}</p>
                    </div>
                    <div class="w-10 h-10 sm:w-12 sm:h-12 bg-orange-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-anchor text-orange-500 text-lg sm:text-xl"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow hover:shadow-lg transition-shadow duration-300 p-4 sm:p-6 border-l-4" style="border-left-color: #6366f1;">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 font-medium text-xs sm:text-sm">Flexible</p>
                        <p class="text-2xl sm:text-3xl font-bold text-indigo-600 mt-1">{{ $stats['flexible'] }}</p>
                    </div>
                    <div class="w-10 h-10 sm:w-12 sm:h-12 bg-indigo-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-exchange-alt text-indigo-500 text-lg sm:text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters & Actions -->
        <div class="bg-white rounded-2xl shadow p-4 sm:p-6 border border-gray-100">
            <div class="flex flex-col lg:flex-row lg:items-center gap-4">
                <div class="flex-1 grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Financial Year Filter -->
                    <select id="financialYearFilter" class="px-3 py-2 sm:py-3 text-sm sm:text-base border border-gray-200 rounded-lg focus:ring-2 focus:ring-emerald-400 focus:border-emerald-400 bg-gray-50 transition-colors">
                        @foreach($financialYears as $year)
                            <option value="{{ $year }}" {{ $year == $selectedYear ? 'selected' : '' }}>
                                FY {{ $year }}
                            </option>
                        @endforeach
                    </select>

                    <!-- Status Filter -->
                    <select id="statusFilter" class="px-3 py-2 sm:py-3 text-sm sm:text-base border border-gray-200 rounded-lg focus:ring-2 focus:ring-emerald-400 focus:border-emerald-400 bg-gray-50 transition-colors">
                        <option value="all" {{ $statusFilter == 'all' ? 'selected' : '' }}>All Status</option>
                        <option value="active" {{ $statusFilter == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ $statusFilter == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>

                    <!-- Type Filter -->
                    <select id="typeFilter" class="px-3 py-2 sm:py-3 text-sm sm:text-base border border-gray-200 rounded-lg focus:ring-2 focus:ring-emerald-400 focus:border-emerald-400 bg-gray-50 transition-colors">
                        <option value="all" {{ $typeFilter == 'all' ? 'selected' : '' }}>All Types</option>
                        <option value="fixed" {{ $typeFilter == 'fixed' ? 'selected' : '' }}>Fixed</option>
                        <option value="flexible" {{ $typeFilter == 'flexible' ? 'selected' : '' }}>Flexible</option>
                    </select>
                </div>

                @if(auth()->user()->isAdmin() || auth()->user()->isSuperAdmin())
                    <a href="{{ route('public-holidays.create') }}" class="w-full lg:w-auto inline-flex items-center justify-center px-4 sm:px-6 py-2 sm:py-3 bg-gradient-to-r from-emerald-600 to-teal-600 text-white font-semibold rounded-lg shadow-md hover:from-emerald-700 hover:to-teal-700 transition-all duration-300 text-sm sm:text-base">
                        <i class="fas fa-plus mr-2"></i> Add Holiday
                    </a>
                @endif
            </div>
        </div>

        <!-- Holidays Table/Card -->
        <div class="bg-white rounded-2xl shadow border border-gray-100 overflow-hidden">
            <div class="px-4 sm:px-6 py-3 sm:py-4 border-b bg-gradient-to-r from-gray-50 to-gray-100">
                <h3 class="text-lg sm:text-xl font-bold text-gray-800 flex items-center">
                    <i class="fas fa-list text-emerald-600 mr-2 text-sm sm:text-base"></i>
                    <span class="text-sm sm:text-base lg:text-lg">Holidays for FY {{ $selectedYear }}</span>
                </h3>
            </div>

            @if($holidays->count() > 0)
                <!-- Desktop Table -->
                <div class="hidden lg:block overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 border-b">
                            <tr>
                                <th class="px-6 py-4 text-left font-semibold text-gray-500 uppercase">Holiday</th>
                                <th class="px-6 py-4 text-left font-semibold text-gray-500 uppercase">Date</th>
                                <th class="px-6 py-4 text-left font-semibold text-gray-500 uppercase">Day</th>
                                <th class="px-6 py-4 text-left font-semibold text-gray-500 uppercase">Type</th>
                                <th class="px-6 py-4 text-left font-semibold text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-4 text-left font-semibold text-gray-500 uppercase">Scope</th>
                                <th class="px-6 py-4 text-left font-semibold text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @foreach($holidays as $holiday)
                                <tr class="hover:bg-emerald-50 transition holiday-row">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="w-3 h-3 rounded-full mr-3" style="background-color: {{ $holiday->color }}"></div>
                                            <div>
                                                <div class="font-semibold text-gray-900">{{ $holiday->name }}</div>
                                                @if($holiday->description)
                                                    <div class="text-xs text-gray-500 mt-1">{{ Str::limit($holiday->description, 50) }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-medium">{{ $holiday->formatted_date }}</div>
                                        @if($holiday->is_today)
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 mt-1">
                                                <i class="fas fa-calendar-day mr-1"></i> Today
                                            </span>
                                        @elseif($holiday->is_upcoming)
                                            <span class="text-xs text-green-600 mt-1 block">Upcoming</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="text-gray-700">{{ $holiday->day_of_week }}</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold
                                            {{ $holiday->type === 'fixed' ? 'bg-orange-100 text-orange-800' : 'bg-indigo-100 text-indigo-800' }}">
                                            <i class="fas {{ $holiday->type === 'fixed' ? 'fa-anchor' : 'fa-exchange-alt' }} mr-1"></i>
                                            {{ ucfirst($holiday->type) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold
                                            {{ $holiday->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            <i class="fas {{ $holiday->status === 'active' ? 'fa-check-circle' : 'fa-times-circle' }} mr-1"></i>
                                            {{ ucfirst($holiday->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold
                                            {{ $holiday->is_national ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                                            <i class="fas {{ $holiday->is_national ? 'fa-flag' : 'fa-building' }} mr-1"></i>
                                            {{ $holiday->is_national ? 'National' : 'Local' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-2">
                                            <a href="{{ route('public-holidays.show', $holiday) }}" class="text-emerald-600 hover:text-emerald-900 p-2 rounded-lg hover:bg-emerald-100 transition" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if(auth()->user()->isAdmin() || auth()->user()->isSuperAdmin())
                                                <a href="{{ route('public-holidays.edit', $holiday) }}" class="text-blue-600 hover:text-blue-900 p-2 rounded-lg hover:bg-blue-100 transition" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('public-holidays.toggle-status', $holiday) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="text-yellow-600 hover:text-yellow-900 p-2 rounded-lg hover:bg-yellow-100 transition" title="Toggle Status">
                                                        <i class="fas fa-toggle-{{ $holiday->status === 'active' ? 'on' : 'off' }}"></i>
                                                    </button>
                                                </form>
                                                <form action="{{ route('public-holidays.destroy', $holiday) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this holiday?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900 p-2 rounded-lg hover:bg-red-100 transition" title="Delete">
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

                <!-- Mobile Card View -->
                <div class="lg:hidden space-y-3 sm:space-y-4 p-3 sm:p-4">
                    @foreach($holidays as $holiday)
                        <div class="bg-white border border-gray-200 rounded-xl p-4 sm:p-6 holiday-row hover:shadow-lg transition-shadow duration-300">
                            <div class="flex items-center justify-between mb-3 sm:mb-4">
                                <div class="flex items-center flex-1 min-w-0">
                                    <div class="w-3 h-3 sm:w-4 sm:h-4 rounded-full mr-2 sm:mr-3 flex-shrink-0" style="background-color: {{ $holiday->color }}"></div>
                                    <div class="min-w-0 flex-1">
                                        <div class="font-semibold text-gray-900 text-sm sm:text-base truncate">{{ $holiday->name }}</div>
                                        <div class="text-xs text-gray-500">{{ $holiday->formatted_date }} • {{ $holiday->day_of_week }}</div>
                                    </div>
                                </div>
                                <span class="inline-flex items-center px-2 sm:px-3 py-1 rounded-full text-xs font-semibold ml-2 flex-shrink-0
                                    {{ $holiday->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ ucfirst($holiday->status) }}
                                </span>
                            </div>

                            @if($holiday->description)
                                <p class="text-xs sm:text-sm text-gray-600 mb-3 sm:mb-4">{{ Str::limit($holiday->description, 100) }}</p>
                            @endif

                            <div class="grid grid-cols-2 gap-3 sm:gap-4 text-xs sm:text-sm mb-3 sm:mb-4">
                                <div>
                                    <span class="text-gray-400 text-xs block mb-1">Type:</span>
                                    <span class="inline-flex items-center px-2 sm:px-3 py-1 rounded-full text-xs font-semibold
                                        {{ $holiday->type === 'fixed' ? 'bg-orange-100 text-orange-800' : 'bg-indigo-100 text-indigo-800' }}">
                                        {{ ucfirst($holiday->type) }}
                                    </span>
                                </div>
                                <div>
                                    <span class="text-gray-400 text-xs block mb-1">Scope:</span>
                                    <span class="inline-flex items-center px-2 sm:px-3 py-1 rounded-full text-xs font-semibold
                                        {{ $holiday->is_national ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                                        {{ $holiday->is_national ? 'National' : 'Local' }}
                                    </span>
                                </div>
                            </div>

                            @if(auth()->user()->isAdmin() || auth()->user()->isSuperAdmin())
                                <div class="border-t pt-3 sm:pt-4">
                                    <span class="text-gray-400 text-xs block mb-2">Actions:</span>
                                    <div class="flex items-center gap-1 sm:gap-2">
                                        <a href="{{ route('public-holidays.show', $holiday) }}" class="text-emerald-600 hover:text-emerald-900 p-1.5 sm:p-2 rounded hover:bg-emerald-100 transition text-sm" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('public-holidays.edit', $holiday) }}" class="text-blue-600 hover:text-blue-900 p-1.5 sm:p-2 rounded hover:bg-blue-100 transition text-sm" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('public-holidays.toggle-status', $holiday) }}" method="POST" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="text-yellow-600 hover:text-yellow-900 p-1.5 sm:p-2 rounded hover:bg-yellow-100 transition text-sm" title="Toggle Status">
                                                <i class="fas fa-toggle-{{ $holiday->status === 'active' ? 'on' : 'off' }}"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('public-holidays.destroy', $holiday) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900 p-1.5 sm:p-2 rounded hover:bg-red-100 transition text-sm" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-12 sm:py-16">
                    <div class="w-16 h-16 sm:w-20 sm:h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4 sm:mb-5 shadow">
                        <i class="fas fa-calendar-times text-gray-400 text-2xl sm:text-3xl"></i>
                    </div>
                    <h3 class="text-xl sm:text-2xl font-bold text-gray-800 mb-2">No Public Holidays</h3>
                    <p class="text-gray-500 mb-4 sm:mb-6 text-sm sm:text-base">No holidays found for the selected financial year and filters.</p>
                    @if(auth()->user()->isAdmin() || auth()->user()->isSuperAdmin())
                        <a href="{{ route('public-holidays.create') }}" class="inline-flex items-center px-4 sm:px-6 py-2 sm:py-3 bg-gradient-to-r from-emerald-600 to-teal-600 text-white font-semibold rounded-lg shadow hover:from-emerald-700 hover:to-teal-700 transition text-sm sm:text-base">
                            <i class="fas fa-plus mr-2"></i>
                            Add First Holiday
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const financialYearFilter = document.getElementById('financialYearFilter');
    const statusFilter = document.getElementById('statusFilter');
    const typeFilter = document.getElementById('typeFilter');

    function updateFilters() {
        const params = new URLSearchParams();
        if (financialYearFilter.value) params.set('financial_year', financialYearFilter.value);
        if (statusFilter.value !== 'all') params.set('status', statusFilter.value);
        if (typeFilter.value !== 'all') params.set('type', typeFilter.value);
        
        window.location.search = params.toString();
    }

    financialYearFilter.addEventListener('change', updateFilters);
    statusFilter.addEventListener('change', updateFilters);
    typeFilter.addEventListener('change', updateFilters);
});
</script>
@endsection