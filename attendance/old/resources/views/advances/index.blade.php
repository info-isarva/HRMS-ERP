@extends('layouts.app')

@section('title', 'My Salary Advances - HRMS')
@section('page-title', 'My Salary Advances')

@section('content')
<div class="p-6 space-y-6">
    {{-- Hero --}}
    <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-200">
        <div class="bg-gradient-to-r from-emerald-600 via-teal-600 to-teal-700 px-6 sm:px-8 py-8 sm:py-10">
            <div class="flex items-center justify-between gap-4">
                <div class="flex items-center min-w-0">
                    <div class="w-12 h-12 bg-white bg-opacity-20 rounded-xl flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-hand-holding-dollar text-white text-xl"></i>
                    </div>
                    <div class="ml-4 min-w-0">
                        <h1 class="text-2xl sm:text-3xl font-bold text-white truncate">Salary Advances</h1>
                        <p class="text-emerald-100 text-sm sm:text-base mt-1">
                            Track your active advances, monthly deductions, and closed history
                        </p>
                    </div>
                </div>
                <div class="hidden sm:flex w-14 h-14 bg-white bg-opacity-15 rounded-full items-center justify-center flex-shrink-0 border border-white border-opacity-20">
                    <i class="fas fa-sack-dollar text-white text-2xl"></i>
                </div>
            </div>
        </div>
    </div>

    @php
        $advances = collect($advancesList);
        $activeAdvances = $advances->filter(fn($adv) => $adv['status'] === 'active');
        $closedAdvances = $advances->filter(fn($adv) => $adv['status'] !== 'active');
    @endphp

    {{-- Active Advances Section --}}
    <div class="space-y-4">
        <h2 class="text-lg font-bold text-gray-800 flex items-center">
            <span class="w-2.5 h-2.5 bg-emerald-500 rounded-full mr-2"></span>
            Active Advances ({{ $activeAdvances->count() }})
        </h2>

        @if($activeAdvances->isEmpty())
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 text-center text-gray-500">
                You do not have any active salary advances.
            </div>
        @else
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                @foreach($activeAdvances as $adv)
                    @php
                        $total = (float)$adv['advance_amount'];
                        $deducted = (float)$adv['total_deducted'];
                        $remaining = (float)$adv['remaining_amount'];
                        $pct = $total > 0 ? min(100, round(($deducted / $total) * 100)) : 0;
                    @endphp
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden flex flex-col justify-between">
                        <div class="p-6 space-y-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs font-semibold text-gray-400 uppercase">Advance Amount</p>
                                    <h3 class="text-2xl font-bold text-gray-900 mt-1">₹{{ number_format($total, 2) }}</h3>
                                </div>
                                <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-emerald-100 text-emerald-800 uppercase">
                                    {{ $adv['status'] }}
                                </span>
                            </div>

                            {{-- Progress Bar --}}
                            <div>
                                <div class="flex justify-between text-xs font-medium text-gray-500 mb-1">
                                    <span>Paid: ₹{{ number_format($deducted, 2) }}</span>
                                    <span>{{ $pct }}%</span>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-2">
                                    <div class="bg-emerald-600 h-2 rounded-full" style="width: {{ $pct }}%"></div>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4 pt-2 border-t border-gray-100 text-sm">
                                <div>
                                    <p class="text-xs text-gray-400">Monthly EMI</p>
                                    <p class="font-bold text-gray-800 mt-0.5">₹{{ number_format($adv['monthly_deduction'], 2) }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-400">Remaining Balance</p>
                                    <p class="font-bold text-emerald-700 mt-0.5">₹{{ number_format($remaining, 2) }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-400">Tenure</p>
                                    <p class="font-medium text-gray-700 mt-0.5">{{ $adv['tenure_months'] }} Months</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-400">Repayment Period</p>
                                    <p class="font-medium text-gray-700 mt-0.5">
                                        {{ \Carbon\Carbon::parse($adv['start_date'])->format('M Y') }} - 
                                        {{ \Carbon\Carbon::parse($adv['end_date'])->format('M Y') }}
                                    </p>
                                </div>
                            </div>

                            @if($adv['notes'])
                                <div class="bg-gray-50 rounded-lg p-3 text-xs text-gray-500 italic mt-2">
                                    <strong>Notes:</strong> {{ $adv['notes'] }}
                                </div>
                            @endif
                        </div>

                        {{-- Deductions History Accordion/List --}}
                        <div class="border-t border-gray-100 bg-gray-50 px-6 py-4">
                            <h4 class="text-xs font-bold text-gray-600 uppercase mb-2">Deduction History</h4>
                            @if(empty($adv['deductions']))
                                <p class="text-xs text-gray-400">No deductions recorded yet.</p>
                            @else
                                <div class="space-y-1.5 max-h-28 overflow-y-auto pr-1">
                                    @foreach($adv['deductions'] as $ded)
                                        <div class="flex justify-between text-xs text-gray-600 bg-white p-2 rounded border border-gray-100">
                                            <span>
                                                {{ date('F Y', mktime(0, 0, 0, $ded['month'], 10)) }} {{ $ded['year'] }}
                                            </span>
                                            <span class="font-bold text-red-600">-₹{{ number_format($ded['amount'], 2) }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Closed Advances Section --}}
    <div class="space-y-4 pt-6">
        <h2 class="text-lg font-bold text-gray-800 flex items-center">
            <span class="w-2.5 h-2.5 bg-gray-400 rounded-full mr-2"></span>
            Closed / Completed Advances ({{ $closedAdvances->count() }})
        </h2>

        @if($closedAdvances->isEmpty())
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 text-center text-gray-500">
                No closed advances recorded.
            </div>
        @else
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-200 text-gray-500 font-semibold">
                                <th class="px-6 py-3">Start Date</th>
                                <th class="px-6 py-3">Amount</th>
                                <th class="px-6 py-3">Tenure</th>
                                <th class="px-6 py-3">Total Deducted</th>
                                <th class="px-6 py-3">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-150 text-gray-700">
                            @foreach($closedAdvances as $adv)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4">{{ \Carbon\Carbon::parse($adv['start_date'])->format('d M Y') }}</td>
                                    <td class="px-6 py-4 font-semibold">₹{{ number_format($adv['advance_amount'], 2) }}</td>
                                    <td class="px-6 py-4">{{ $adv['tenure_months'] }} Months</td>
                                    <td class="px-6 py-4 text-emerald-700 font-semibold">₹{{ number_format($adv['total_deducted'], 2) }}</td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 py-0.5 text-xs font-bold rounded-full bg-gray-100 text-gray-700 uppercase">
                                            {{ $adv['status'] }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
