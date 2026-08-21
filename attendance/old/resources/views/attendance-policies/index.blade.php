@extends('layouts.app')

@section('title', 'Attendance Policy Configuration')

@section('page-title', 'Attendance Policies')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50">
    <div class=" mx-auto p-6 space-y-6">

        <!-- Header -->
        <div class="mb-8">
            <div class="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-2xl p-8 text-white relative overflow-hidden">
                <div class="absolute inset-0 opacity-10">
                    <div class="absolute -top-4 -right-4 w-24 h-24 bg-white rounded-full"></div>
                    <div class="absolute top-10 -right-8 w-16 h-16 bg-white rounded-full"></div>
                </div>
                <div class="relative">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-3xl font-bold mb-2 flex items-center">
                                <i class="fas fa-cog mr-3"></i>
                                Attendance Policy Configuration
                            </h1>
                            <p class="text-indigo-100 text-lg">
                                Configure grace periods, thresholds, and overtime rules for your organization
                            </p>
                        </div>
                        <div class="hidden lg:block">
                            <div class="w-32 h-32 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                                <i class="fas fa-clipboard-check text-4xl text-white"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Success/Error Messages -->
        @if(session('success'))
            <div class="bg-green-50 border border-green-200 rounded-xl p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-check-circle text-green-400 text-xl"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-circle text-red-400 text-xl"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Active Policy Overview -->
        @if($activePolicy)
        <div class="bg-gradient-to-r from-green-500 to-emerald-600 rounded-2xl shadow-lg p-6 text-white mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <div class="flex items-center mb-2">
                        <i class="fas fa-check-circle text-2xl mr-3"></i>
                        <h2 class="text-2xl font-bold">Active Policy</h2>
                    </div>
                    <h3 class="text-3xl font-bold mb-2">{{ $activePolicy->name }}</h3>
                    <p class="text-green-100">{{ $activePolicy->description }}</p>
                </div>
                <div class="text-right">
                    <a href="{{ route('attendance-policies.edit', $activePolicy->id) }}" 
                       class="inline-flex items-center bg-white text-green-600 px-6 py-3 rounded-xl font-semibold hover:bg-green-50 transition-all duration-300">
                        <i class="fas fa-edit mr-2"></i>
                        Edit Configuration
                    </a>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6">
                <div class="bg-white bg-opacity-20 rounded-xl p-4">
                    <p class="text-green-100 text-sm mb-1">Late Grace</p>
                    <p class="text-2xl font-bold">{{ $activePolicy->late_arrival_grace_minutes }} min</p>
                </div>
                <div class="bg-white bg-opacity-20 rounded-xl p-4">
                    <p class="text-green-100 text-sm mb-1">Half Day Threshold</p>
                    <p class="text-2xl font-bold">{{ $activePolicy->half_day_late_threshold_minutes }} min</p>
                </div>
                <div class="bg-white bg-opacity-20 rounded-xl p-4">
                    <p class="text-green-100 text-sm mb-1">Absent Threshold</p>
                    <p class="text-2xl font-bold">{{ $activePolicy->absent_threshold_minutes }} min</p>
                </div>
                <div class="bg-white bg-opacity-20 rounded-xl p-4">
                    <p class="text-green-100 text-sm mb-1">OT Multiplier</p>
                    <p class="text-2xl font-bold">{{ $activePolicy->overtime_multiplier }}x</p>
                </div>
            </div>
        </div>
        @endif

        <!-- All Policies List -->
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold text-gray-900">All Attendance Policies</h3>
                <a href="{{ route('attendance-policies.create') }}" 
                   class="inline-flex items-center bg-gradient-to-r from-indigo-600 to-purple-600 text-white px-4 py-2 rounded-xl font-semibold hover:from-indigo-700 hover:to-purple-700 transition-all duration-300">
                    <i class="fas fa-plus mr-2"></i>
                    Create New Policy
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($policies as $policy)
                <div class="bg-white rounded-xl shadow border {{ $policy->is_active ? 'border-green-500 border-2' : 'border-gray-200' }} p-6 relative">
                    @if($policy->is_active)
                    <div class="absolute top-4 right-4">
                        <span class="bg-green-500 text-white text-xs font-bold px-3 py-1 rounded-full">ACTIVE</span>
                    </div>
                    @endif

                    <h4 class="text-lg font-bold text-gray-900 mb-2 pr-20">{{ $policy->name }}</h4>
                    <p class="text-sm text-gray-600 mb-4">{{ Str::limit($policy->description, 80) }}</p>

                    <div class="space-y-2 mb-4">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Late Grace:</span>
                            <span class="font-semibold text-gray-900">{{ $policy->late_arrival_grace_minutes }} min</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Half Day:</span>
                            <span class="font-semibold text-gray-900">{{ $policy->half_day_late_threshold_minutes }} min</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Absent:</span>
                            <span class="font-semibold text-gray-900">{{ $policy->absent_threshold_minutes }} min</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">OT Enabled:</span>
                            <span class="font-semibold {{ $policy->enable_overtime ? 'text-green-600' : 'text-red-600' }}">
                                {{ $policy->enable_overtime ? 'Yes' : 'No' }}
                            </span>
                        </div>
                    </div>

                    <div class="flex gap-2">
                        @if(!$policy->is_active)
                        <form action="{{ route('attendance-policies.activate', $policy->id) }}" method="POST" class="flex-1">
                            @csrf
                            <button type="submit" 
                                    class="w-full bg-gradient-to-r from-green-500 to-emerald-600 text-white px-4 py-2 rounded-lg font-semibold hover:from-green-600 hover:to-emerald-700 transition-all duration-300 text-sm">
                                <i class="fas fa-check mr-1"></i> Activate
                            </button>
                        </form>
                        @endif

                        <a href="{{ route('attendance-policies.edit', $policy->id) }}" 
                           class="flex-1 text-center bg-indigo-500 text-white px-4 py-2 rounded-lg font-semibold hover:bg-indigo-600 transition-all duration-300 text-sm">
                            <i class="fas fa-edit mr-1"></i> Edit
                        </a>

                        @if(!$policy->is_active)
                        <form action="{{ route('attendance-policies.destroy', $policy->id) }}" method="POST" 
                              onsubmit="return confirm('Are you sure you want to delete this policy?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="bg-red-500 text-white px-4 py-2 rounded-lg font-semibold hover:bg-red-600 transition-all duration-300 text-sm">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>

    </div>
</div>
@endsection
