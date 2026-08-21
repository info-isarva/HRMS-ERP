@extends('layouts.app')

@section('title', 'My Payslips - HRMS')
@section('page-title', 'My Payslips')

@section('content')
<div class="p-6 space-y-6">
    @if(session('error'))
        <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded-r-md shadow-sm">
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle text-red-400 text-lg flex-shrink-0"></i>
                <p class="ml-3 text-sm font-medium text-red-800">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    {{-- Hero --}}
    <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-200">
        <div class="bg-gradient-to-r from-emerald-600 via-teal-600 to-teal-700 px-6 sm:px-8 py-8 sm:py-10">
            <div class="flex items-center justify-between gap-4">
                <div class="flex items-center min-w-0">
                    <div class="w-12 h-12 bg-white bg-opacity-20 rounded-xl flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-file-invoice-dollar text-white text-xl"></i>
                    </div>
                    <div class="ml-4 min-w-0">
                        <h1 class="text-2xl sm:text-3xl font-bold text-white truncate">My Payslips</h1>
                        <p class="text-emerald-100 text-sm sm:text-base mt-1">
                            View and download your salary slips by month and year
                        </p>
                    </div>
                </div>
                <div class="hidden sm:flex w-14 h-14 bg-white bg-opacity-15 rounded-full items-center justify-center flex-shrink-0 border border-white border-opacity-20">
                    <i class="fas fa-wallet text-white text-2xl"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Available Payslips</p>
                <p class="text-3xl font-bold text-emerald-700 mt-1">{{ $stats['total'] }}</p>
            </div>
            <div class="w-11 h-11 rounded-full bg-emerald-100 flex items-center justify-center">
                <i class="fas fa-receipt text-emerald-600"></i>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Selected Period</p>
                <p class="text-xl font-bold text-teal-700 mt-1">{{ $stats['selected_period'] ?? '—' }}</p>
            </div>
            <div class="w-11 h-11 rounded-full bg-teal-100 flex items-center justify-center">
                <i class="fas fa-calendar text-teal-600"></i>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Net Pay (Selected)</p>
                <p class="text-2xl font-bold text-indigo-700 mt-1">
                    @if($stats['selected_net'] !== null)
                        ₹{{ number_format($stats['selected_net'], 0) }}
                    @else
                        —
                    @endif
                </p>
            </div>
            <div class="w-11 h-11 rounded-full bg-indigo-100 flex items-center justify-center">
                <i class="fas fa-indian-rupee-sign text-indigo-600"></i>
            </div>
        </div>
    </div>

    @if($apiError)
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-8 text-center">
            <i class="fas fa-cloud-exclamation text-amber-500 text-3xl mb-3"></i>
            <p class="text-amber-800 font-medium">{{ $apiError }}</p>
            <p class="text-amber-600 text-sm mt-2">Ensure your login email matches your payroll record.</p>
        </div>
    @elseif($allPayslips->isEmpty())
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
            <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-file-circle-xmark text-gray-400 text-3xl"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-800">No payslips yet</h3>
            <p class="text-gray-500 mt-2 max-w-md mx-auto">
                Payslips appear here after payroll is finalized for a month.
            </p>
        </div>
    @else
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            {{-- Sidebar --}}
            <div class="lg:col-span-4 space-y-4">
                {{-- Period selector --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                    <h3 class="text-sm font-semibold text-gray-800 flex items-center mb-1">
                        <i class="fas fa-filter text-emerald-500 mr-2"></i> Select Period
                    </h3>
                    <p class="text-xs text-gray-500 mb-4">Choose month and year to view payslip</p>
                    <form method="GET" action="{{ route('payslips.index') }}" id="period-form" class="space-y-3">
                        <div>
                            <label for="year" class="block text-xs font-medium text-gray-600 mb-1.5">Year</label>
                            <select name="year" id="year"
                                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                                onchange="document.getElementById('period-form').submit()">
                                @foreach($yearOptions as $y)
                                    <option value="{{ $y }}" {{ $selectedYear === $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="month" class="block text-xs font-medium text-gray-600 mb-1.5">Month</label>
                            <select name="month" id="month"
                                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                                onchange="document.getElementById('period-form').submit()">
                                @php
                                    $monthNames = [
                                        1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
                                        5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
                                        9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December',
                                    ];
                                @endphp
                                @forelse($monthsForYear as $num)
                                    <option value="{{ $num }}" {{ $selectedMonth === $num ? 'selected' : '' }}>
                                        {{ $monthNames[$num] ?? $num }} {{ $selectedYear }}
                                    </option>
                                @empty
                                    <option value="">No payslips for {{ $selectedYear }}</option>
                                @endforelse
                            </select>
                        </div>
                    </form>
                </div>

                {{-- History --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 bg-gray-50">
                        <h3 class="text-sm font-semibold text-gray-800 flex items-center">
                            <i class="fas fa-clock-rotate-left text-emerald-500 mr-2"></i> Payslip History
                        </h3>
                    </div>
                    <div class="max-h-[420px] overflow-y-auto divide-y divide-gray-100">
                        @foreach($allPayslips as $slip)
                            @php
                                $isActive = (int) $slip['month'] === $selectedMonth && (int) $slip['year'] === $selectedYear;
                            @endphp
                            <a href="{{ route('payslips.index', ['month' => $slip['month'], 'year' => $slip['year']]) }}"
                                class="block px-5 py-4 transition-colors {{ $isActive ? 'bg-emerald-50 border-l-4 border-emerald-500' : 'hover:bg-gray-50 border-l-4 border-transparent' }}">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="font-semibold text-gray-900 {{ $isActive ? 'text-emerald-800' : '' }}">
                                            {{ $slip['period_label'] }}
                                        </p>
                                        <p class="text-xs text-gray-500 mt-0.5">
                                            {{ (float) $slip['days_worked'] }} / {{ (float) $slip['working_days'] }} days worked
                                        </p>
                                    </div>
                                    <p class="font-bold text-emerald-700 text-sm whitespace-nowrap">
                                        ₹{{ number_format($slip['net_pay'], 0) }}
                                    </p>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Main preview --}}
            <div class="lg:col-span-8">
                @if($detail)
                    @include('payslips.partials.detail', ['detail' => $detail])
                @else
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
                        <i class="fas fa-file-invoice text-gray-300 text-4xl mb-4"></i>
                        <p class="text-gray-600 font-medium">No payslip found for the selected period.</p>
                        <p class="text-gray-400 text-sm mt-1">Pick another month from the history list.</p>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>
@endsection
