@extends('layouts.app')

@section('title', 'Shift Master - HRMS')

@section('page-title', 'Shift Master')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50">
    <div class="max-w-full mx-auto p-6 space-y-6">
    <!-- Header -->
    <div class="bg-white/80 backdrop-blur-sm overflow-hidden shadow-xl rounded-2xl border border-white/20">
        <div class="bg-gradient-to-r from-indigo-600 via-purple-600 to-blue-600 px-8 py-12 relative overflow-hidden">
            <!-- Background Pattern -->
            <div class="absolute inset-0 bg-black/10"></div>
            <div class="absolute -top-4 -right-4 w-24 h-24 bg-white/10 rounded-full"></div>
            <div class="absolute top-10 -right-8 w-16 h-16 bg-white/10 rounded-full"></div>
            <div class="absolute -bottom-6 -left-6 w-20 h-20 bg-white/10 rounded-full"></div>

            <div class="relative flex items-center justify-between">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-16 h-16 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center border border-white/30">
                            <i class="fas fa-clock text-white text-2xl"></i>
                        </div>
                    </div>
                    <div class="ml-6">
                        <h1 class="text-3xl font-bold text-white mb-2 flex items-center">
                            <i class="fas fa-clock mr-3"></i>
                            Shift Master
                        </h1>
                        <p class="text-indigo-100 text-lg">
                            Manage work shifts and their timings
                        </p>
                    </div>
                </div>
                <div class="hidden lg:flex items-center space-x-4">
                    <div class="text-right">
                        <p class="text-white/90 text-sm">Total Shifts</p>
                        <p class="text-3xl font-bold text-white">{{ $shifts->count() }}</p>
                    </div>
                    <div class="w-20 h-20 bg-white/10 backdrop-blur-sm rounded-2xl flex items-center justify-center border border-white/20">
                        <i class="fas fa-business-time text-white text-3xl"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="bg-green-50/80 backdrop-blur-sm border border-green-200/50 rounded-2xl p-6 shadow-lg">
            <div class="flex">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center">
                        <i class="fas fa-check-circle text-green-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-50/80 backdrop-blur-sm border border-red-200/50 rounded-2xl p-6 shadow-lg">
            <div class="flex">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-red-100 rounded-xl flex items-center justify-center">
                        <i class="fas fa-exclamation-circle text-red-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <h3 class="text-sm font-medium text-red-800">There were errors:</h3>
                    <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <!-- Actions -->
    <div class="bg-white/80 backdrop-blur-sm shadow-xl rounded-2xl border border-white/20 p-6">
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center space-y-4 lg:space-y-0">
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center">
                    <i class="fas fa-cogs text-white"></i>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Shift Management</h3>
                    <p class="text-gray-600">Create and manage your organization's work shifts</p>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-4">
                <!-- Filter by Shift Name -->
                <form method="GET" action="{{ route('shifts.index') }}" class="flex items-center space-x-3">
                    <select name="shift_name" onchange="this.form.submit()" class="w-64 px-5 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-base bg-white shadow-sm">
                        <option value="">All Shifts</option>
                        @foreach($allShifts as $shiftOption)
                            <option value="{{ $shiftOption->name }}" {{ request('shift_name') == $shiftOption->name ? 'selected' : '' }}>
                                {{ $shiftOption->name }}
                            </option>
                        @endforeach
                    </select>
                    @if(request('shift_name'))
                        <a href="{{ route('shifts.index') }}" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors text-sm font-medium">
                            <i class="fas fa-times mr-1"></i>
                            Clear
                        </a>
                    @endif
                </form>

                <!-- Add New Shift -->
                <a href="{{ route('shifts.create') }}"
                   class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold rounded-xl shadow-lg hover:from-indigo-700 hover:to-purple-700 transition-all duration-300 transform hover:scale-105">
                    <i class="fas fa-plus mr-2"></i>
                    Add New Shift
                </a>
            </div>
        </div>
    </div>

    <!-- Shifts List -->
    @if($shifts->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
            @foreach($shifts as $shift)
                <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl border border-white/20 hover:shadow-2xl transition-all duration-300 hover:bg-white/90">
                    <div class="p-5">
                        <!-- Header -->
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex items-center">
                                <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg">
                                    <i class="fas fa-clock text-white text-lg"></i>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-lg font-bold text-gray-900">{{ $shift->name }}</h3>
                                    <p class="text-sm text-gray-500 flex items-center">
                                        <i class="fas fa-calendar-alt mr-1"></i>
                                        {{ $shift->start_time }} - {{ $shift->end_time }}
                                    </p>
                                </div>
                            </div>
                            <div class="w-7 h-7 bg-green-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-check text-green-600 text-xs"></i>
                            </div>
                        </div>

                        <!-- Description -->
                        @if($shift->description)
                            <p class="text-gray-600 text-sm mb-4 bg-gray-50/50 rounded-xl p-3 border border-gray-100">{{ $shift->description }}</p>
                        @endif

                        <!-- Details -->
                        <div class="space-y-3 mb-4">
                            <div class="flex items-center justify-between p-3 bg-gradient-to-r from-orange-50 to-yellow-50 rounded-xl">
                                <span class="text-sm font-medium text-gray-600 flex items-center">
                                    <i class="fas fa-play mr-2 text-orange-500"></i>
                                    Start Time
                                </span>
                                <span class="text-base font-bold text-orange-600">{{ $shift->start_time }}</span>
                            </div>

                            <div class="flex items-center justify-between p-3 bg-gradient-to-r from-purple-50 to-pink-50 rounded-xl">
                                <span class="text-sm font-medium text-gray-600 flex items-center">
                                    <i class="fas fa-stop mr-2 text-purple-500"></i>
                                    End Time
                                </span>
                                <span class="text-base font-bold text-purple-600">{{ $shift->end_time }}</span>
                            </div>

                            <div class="flex items-center justify-between p-3 bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl">
                                <span class="text-sm font-medium text-gray-600 flex items-center">
                                    <i class="fas fa-users mr-2 text-green-500"></i>
                                    Duty Rosters
                                </span>
                                <span class="text-base font-bold text-green-600">{{ $shift->dutyRosters->count() }}</span>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center justify-between pt-4 border-t border-gray-200/50">
                            <div class="flex items-center space-x-2">
                                <a href="{{ route('shifts.show', $shift) }}"
                                   class="flex items-center justify-center w-9 h-9 bg-blue-100 hover:bg-blue-200 text-blue-600 rounded-xl transition-all duration-200 hover:scale-110"
                                   title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('shifts.edit', $shift) }}"
                                   class="flex items-center justify-center w-9 h-9 bg-green-100 hover:bg-green-200 text-green-600 rounded-xl transition-all duration-200 hover:scale-110"
                                   title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </div>

                            @if($shift->dutyRosters()->count() == 0)
                                <form action="{{ route('shifts.destroy', $shift) }}"
                                      method="POST"
                                      class="inline"
                                      onsubmit="return confirm('Are you sure you want to delete this shift?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="flex items-center justify-center w-9 h-9 bg-red-100 hover:bg-red-200 text-red-600 rounded-xl transition-all duration-200 hover:scale-110"
                                            title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            @else
                                <div class="flex items-center justify-center w-9 h-9 bg-gray-100 text-gray-400 rounded-xl" title="Cannot delete - has duty rosters">
                                    <i class="fas fa-lock"></i>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl border border-white/20 p-12 text-center">
            <div class="w-24 h-24 bg-gradient-to-br from-gray-100 to-gray-200 rounded-full flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-clock text-gray-400 text-3xl"></i>
            </div>
            <h3 class="text-2xl font-bold text-gray-900 mb-2">No Shifts Found</h3>
            <p class="text-gray-500 mb-8 text-lg">No shifts have been created yet. Start by adding your first shift!</p>
            <a href="{{ route('shifts.create') }}"
               class="inline-flex items-center px-8 py-4 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold rounded-xl shadow-lg hover:from-indigo-700 hover:to-purple-700 transition-all duration-300 transform hover:scale-105">
                <i class="fas fa-plus mr-3"></i>
                Add First Shift
            </a>
        </div>
    @endif
</div>
@endsection