@extends('layouts.app')

@section('title', 'Employee Monthly Leave Report - HRMS')
@section('page-title', 'Employee Monthly Leave Report')

@section('content')
<div class="p-6 space-y-6">
    <!-- Header Card -->
    <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-200">
        <div class="bg-gradient-to-r from-teal-600 to-emerald-700 px-8 py-10">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                            <i class="fas fa-calendar-alt text-white text-xl"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold text-white">Employee Monthly Leave Report</h1>
                        <p class="text-teal-100 text-xs sm:text-sm lg:text-base mt-2">Track leave patterns for individual employees across months.</p>
                    </div>
                </div>
                <div class="flex items-center">
                    <a href="{{ route('reports.index') }}" class="inline-flex items-center px-4 py-2 bg-white bg-opacity-20 hover:bg-opacity-30 text-white rounded-lg transition-colors font-medium text-sm border border-white border-opacity-30">
                        <i class="fas fa-arrow-left mr-2"></i> Back
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <form action="{{ route('reports.employee-monthly') }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-6 items-end">
            <div>
                <label for="user_id" class="block text-sm font-medium text-gray-700 mb-2">Select Employee</label>
                <select name="user_id" id="user_id" class="w-full h-11 rounded-lg border-gray-300 focus:border-teal-500 focus:ring-teal-500 px-4 shadow-sm" required>
                    <option value="all" {{ $userId == 'all' ? 'selected' : '' }}>-- All Employees --</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ $userId == $user->id ? 'selected' : '' }}>
                            {{ $user->name }} ({{ $user->email }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="year" class="block text-sm font-medium text-gray-700 mb-2">Select Year</label>
                <select name="year" id="year" class="w-full h-11 rounded-lg border-gray-300 focus:border-teal-500 focus:ring-teal-500 px-4 shadow-sm">
                    @for($y = date('Y'); $y >= date('Y') - 5; $y--)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <div class="flex space-x-2">
                <button type="submit" class="flex-1 h-11 bg-teal-600 hover:bg-teal-700 text-white font-medium rounded-lg transition-all shadow-md hover:shadow-lg flex items-center justify-center">
                    <i class="fas fa-filter mr-2"></i> Generate
                </button>
                <a href="{{ request()->fullUrlWithQuery(['format' => 'pdf']) }}" target="_blank" class="flex-1 h-11 bg-rose-600 hover:bg-rose-700 text-white font-medium rounded-lg transition-all shadow-md hover:shadow-lg flex items-center justify-center">
                    <i class="fas fa-file-pdf mr-2"></i> Export PDF
                </a>
            </div>
        </form>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Details Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                <h3 class="font-bold text-gray-800">{{ $selectedUser ? 'Employee Details' : 'Organization Summary' }}</h3>
            </div>
            <div class="p-6 text-center">
                <div class="w-20 h-20 bg-teal-100 rounded-full flex items-center justify-center mx-auto mb-4 text-teal-700 text-2xl font-bold">
                    @if($selectedUser)
                        {{ strtoupper(substr($selectedUser->name, 0, 1)) }}
                    @else
                        <i class="fas fa-users"></i>
                    @endif
                </div>
                <h2 class="text-xl font-bold text-gray-900">{{ $selectedUser ? $selectedUser->name : 'All Employees' }}</h2>
                <p class="text-gray-500 text-sm mb-4">{{ $selectedUser ? $selectedUser->email : 'Consolidated view for ' . $year }}</p>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div class="bg-gray-50 p-3 rounded-lg">
                        <p class="text-gray-500 mb-1">Total Leaves</p>
                        <p class="font-bold text-gray-900 text-lg">{{ array_sum($monthlyData) }}</p>
                    </div>
                    <div class="bg-gray-50 p-3 rounded-lg">
                        <p class="text-gray-500 mb-1">Type</p>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-teal-100 text-teal-800">
                            {{ $selectedUser ? 'Individual' : 'Consolidated' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Monthly Breakdown Table -->
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h3 class="font-bold text-gray-800">Monthly Leave Summary - {{ $year }}</h3>
                <span class="text-xs text-gray-500 italic">Total days taken per month</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-center">
                    <thead class="text-xs text-gray-500 uppercase bg-gray-50 border-b border-gray-100">
                        <tr>
                            @foreach(['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'] as $month)
                                <th class="px-3 py-3 font-semibold">{{ $month }}</th>
                            @endforeach
                            <th class="px-3 py-3 font-bold bg-teal-50 text-teal-700">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr class="hover:bg-gray-50 transition-colors">
                            @foreach($monthlyData as $monthNum => $days)
                                <td class="px-3 py-4">
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full {{ $days > 0 ? 'bg-teal-100 text-teal-800 font-bold' : 'bg-gray-50 text-gray-400' }}">
                                        {{ $days }}
                                    </span>
                                </td>
                            @endforeach
                            <td class="px-3 py-4 bg-teal-50">
                                <span class="text-lg font-bold text-teal-700">{{ array_sum($monthlyData) }}</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Chart Section -->
            <div class="p-6 border-t border-gray-100">
                <h4 class="text-sm font-semibold text-gray-700 mb-4">Visual Trend</h4>
                <div class="h-64">
                    <canvas id="leaveTrendChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('leaveTrendChart').getContext('2d');
        const labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        const data = @json(array_values($monthlyData));

        const gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, '#0ea5e9'); // Sky-500
        gradient.addColorStop(1, '#6366f1'); // Indigo-500

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Leave Days',
                    data: data,
                    backgroundColor: gradient,
                    hoverBackgroundColor: '#0284c7',
                    borderRadius: 6,
                    maxBarThickness: 40
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: {
                    duration: 2000,
                    easing: 'easeOutQuart'
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            font: { family: "'Inter', sans-serif" }
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.03)',
                            drawBorder: false
                        }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { family: "'Inter', sans-serif" } }
                    }
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(17, 24, 39, 0.9)',
                        padding: 12,
                        cornerRadius: 8,
                        titleFont: { size: 13, weight: 'bold' },
                        bodyFont: { size: 12 },
                        callbacks: {
                            label: (context) => ` ${context.parsed.y} Days Taken`
                        }
                    }
                }
            }
        });
    });
</script>
@endsection
