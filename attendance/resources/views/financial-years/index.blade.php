@extends('layouts.app')

@section('title', 'Financial Year Management - HRMS')
@section('page-title', 'Financial Year Management')

@section('content')
<div class="p-6 space-y-6">
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

    <!-- Centralized Management Alert -->
    <div class="bg-indigo-600 rounded-xl shadow-md border border-indigo-700 overflow-hidden mb-6">
        <div class="px-6 py-4 flex items-center justify-between">
            <div class="flex items-center">
                <div class="w-10 h-10 bg-white bg-opacity-20 rounded-lg flex items-center justify-center mr-4">
                    <i class="fas fa-sync-alt text-white animate-spin-slow"></i>
                </div>
                <div>
                    <h4 class="text-white font-bold">Centralized Management Enabled</h4>
                    <p class="text-indigo-100 text-sm">Financial Years are automatically synchronized from the Payroll System.</p>
                </div>
            </div>
            <div class="hidden sm:block">
                <span class="bg-white bg-opacity-20 text-white text-xs font-bold px-3 py-1 rounded-full border border-white border-opacity-30">
                    READ-ONLY MODE
                </span>
            </div>
        </div>
    </div>

    <!-- Header Card -->
    <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-200">
        <div class="bg-gray-50 px-8 py-10">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-calendar-alt text-indigo-600 text-xl"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-900">Financial Years</h1>
                        <p class="text-gray-500 text-xs sm:text-sm lg:text-base mt-2">
                            View and switch between financial year periods synced from Payroll.
                        </p>
                    </div>
                </div>
                <div class="flex space-x-3">
                    <button disabled class="bg-gray-100 text-gray-400 px-4 py-2 rounded-lg cursor-not-allowed flex items-center text-sm font-medium border border-gray-200">
                        <i class="fas fa-lock mr-2 text-xs"></i> Settings Locked
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="bg-white rounded-2xl shadow border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b bg-gray-50 flex justify-between items-center">
            <h3 class="text-lg font-bold text-gray-800 flex items-center">
                <i class="fas fa-list-ul text-indigo-600 mr-2"></i> Financial Year List
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="px-6 py-4 text-left font-semibold text-gray-500 uppercase">FY Name</th>
                        <th class="px-6 py-4 text-left font-semibold text-gray-500 uppercase">Start Date</th>
                        <th class="px-6 py-4 text-left font-semibold text-gray-500 uppercase">End Date</th>
                        <th class="px-6 py-4 text-left font-semibold text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-4 text-left font-semibold text-gray-500 uppercase text-right">Active Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($financialYears as $fy)
                        <tr class="hover:bg-indigo-50 transition">
                            <td class="px-6 py-4 font-bold text-gray-900">{{ $fy->name }}</td>
                            <td class="px-6 py-4">{{ \Carbon\Carbon::parse($fy->start_date)->format('M d, Y') }}</td>
                            <td class="px-6 py-4">{{ \Carbon\Carbon::parse($fy->end_date)->format('M d, Y') }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 rounded-full text-xs font-bold {{ $fy->status === 'open' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                    {{ ucfirst($fy->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                @if($fy->is_active)
                                    <span class="inline-flex items-center text-blue-600 font-bold bg-blue-50 px-3 py-1 rounded-full border border-blue-200">
                                        <i class="fas fa-check-circle mr-1"></i> Current Active
                                    </span>
                                @else
                                    <form action="{{ route('financial-years.activate', $fy->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="text-xs bg-white hover:bg-blue-50 text-gray-600 hover:text-blue-700 px-4 py-1.5 rounded-full transition font-bold border border-gray-200 shadow-sm">
                                            Switch to this Year
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-gray-500">No financial years found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
