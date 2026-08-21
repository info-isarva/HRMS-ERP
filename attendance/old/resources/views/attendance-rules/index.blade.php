@extends('layouts.app')

@section('title', 'Attendance Rules - HRMS')

@section('page-title', 'Attendance Rules')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50">
    <div class="max-w-full mx-auto p-6 space-y-6">
    <!-- Header -->
    <div class="bg-white/80 backdrop-blur-sm overflow-hidden shadow-xl rounded-2xl border border-white/20">
        <div class="bg-gradient-to-r from-purple-600 via-indigo-600 to-blue-600 px-8 py-12 relative overflow-hidden">
            <div class="absolute inset-0 bg-black/10"></div>
            <div class="absolute -top-4 -right-4 w-24 h-24 bg-white/10 rounded-full"></div>
            <div class="relative flex items-center justify-between">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-16 h-16 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center border border-white/30">
                            <i class="fas fa-gavel text-white text-2xl"></i>
                        </div>
                    </div>
                    <div class="ml-6">
                        <h1 class="text-3xl font-bold text-white mb-2 flex items-center">
                            Attendance Rules
                        </h1>
                        <p class="text-indigo-100 text-lg">
                            Define rules for long shifts and automated comp-offs
                        </p>
                    </div>
                </div>
                <div class="hidden lg:flex items-center space-x-4">
                    <a href="{{ route('attendance-rules.create') }}"
                       class="inline-flex items-center px-6 py-3 bg-white text-indigo-600 font-semibold rounded-xl shadow-lg hover:bg-indigo-50 transition-all duration-300 transform hover:scale-105">
                        <i class="fas fa-plus mr-2"></i>
                        Add New Rule
                    </a>
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
                <div class="ml-4 pt-2">
                    <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Rules List -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($rules as $rule)
            <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl border border-white/20 hover:shadow-2xl transition-all duration-300">
                <div class="p-6">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg">
                                <i class="fas fa-scroll text-white text-lg"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-bold text-gray-900">{{ $rule->name }}</h3>
                                <span class="px-2 py-1 {{ $rule->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }} text-xs rounded-full font-medium">
                                    {{ $rule->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4 mb-6">
                        <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl">
                            <span class="text-sm text-gray-600">Shift Threshold</span>
                            <span class="font-bold text-gray-900">{{ $rule->shift_threshold_hours }} Hours</span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl">
                            <span class="text-sm text-gray-600">Recovery Offset</span>
                            <span class="font-bold text-gray-900">{{ $rule->recovery_days_offset }} Days</span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-indigo-50 rounded-xl">
                            <span class="text-sm text-gray-600">Recovery Status</span>
                            <span class="font-bold text-indigo-600 uppercase">{{ $rule->recovery_status }}</span>
                        </div>
                    </div>

                    <div class="flex items-center justify-end space-x-2 pt-4 border-t border-gray-100">
                        <a href="{{ route('attendance-rules.edit', $rule) }}" class="p-2 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100 transition-colors">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('attendance-rules.destroy', $rule) }}" method="POST" onsubmit="return confirm('Delete this rule?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-2 bg-red-50 text-red-600 rounded-lg hover:bg-red-100 transition-colors">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full bg-white/80 backdrop-blur-sm rounded-2xl p-12 text-center border border-white/20 border-dashed">
                <i class="fas fa-clipboard-list text-gray-300 text-5xl mb-4"></i>
                <h3 class="text-xl font-bold text-gray-900">No Rules Defined</h3>
                <p class="text-gray-500 mb-6">Configure rules to handle extended shifts and recovery days.</p>
                <a href="{{ route('attendance-rules.create') }}" class="inline-flex items-center px-6 py-3 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition-colors">
                    <i class="fas fa-plus mr-2"></i> Create First Rule
                </a>
            </div>
        @endforelse
    </div>
</div>
@endsection
