@extends('layouts.app')

@section('title', 'Employee Leave Status - HRMS')
@section('page-title', 'All Employee Leave Status')

@section('content')
<div class="p-6 space-y-6">
    <!-- Header Card -->
    <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-200">
        <div class="bg-gradient-to-r from-blue-600 to-indigo-700 px-8 py-10">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                            <i class="fas fa-users text-white text-xl"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold text-white">All Employee Leave Status</h1>
                        <p class="text-blue-100 text-xs sm:text-sm lg:text-base mt-2">Overview of total available leave balance and total leaves taken.</p>
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

    <!-- Main Card -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
            <div>
                <h3 class="font-bold text-gray-800">Leave Balance Summary</h3>
                <p class="text-xs text-gray-500 mt-1">Overview of total available leave balance and total leaves taken.</p>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ request()->fullUrlWithQuery(['format' => 'pdf']) }}" target="_blank" class="inline-flex items-center px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white rounded-lg transition-colors font-medium text-xs shadow-sm">
                    <i class="fas fa-file-pdf mr-1.5"></i> Export PDF
                </a>
                <span class="bg-indigo-100 text-indigo-700 text-xs font-semibold px-2.5 py-0.5 rounded-full">
                    {{ count($reportData) }} Employees
                </span>
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs text-gray-500 uppercase bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th scope="col" class="px-6 py-3">Employee</th>
                        <th scope="col" class="px-6 py-3 text-center">Available Leave Count</th>
                        <!--<th scope="col" class="px-6 py-3 text-center">Leave Taken Count</th>-->
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($reportData as $data)
                    <tr class="bg-white hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="h-8 w-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold mr-3">
                                    {{ strtoupper(substr($data['user']->name ?? '?', 0, 1)) }}
                                </div>
                                <div>
                                    <div class="font-medium text-gray-900">{{ $data['user']->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $data['user']->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex items-center justify-center px-3 py-1 rounded-full text-sm font-semibold {{ $data['available_leave'] > 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ floatval($data['available_leave']) }}
                            </span>
                        </td>
                        <!--<td class="px-6 py-4 text-center">-->
                        <!--     <span class="inline-flex items-center justify-center px-3 py-1 rounded-full text-sm font-semibold bg-gray-100 text-gray-800">-->
                        <!--        {{ floatval($data['leave_taken']) }}-->
                        <!--    </span>-->
                        <!--</td>-->
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="px-6 py-8 text-center text-gray-500">
                            <div class="flex flex-col items-center justify-center">
                                <i class="fas fa-users-slash text-4xl text-gray-300 mb-3"></i>
                                <p>No employee records found.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 text-xs text-gray-500">
            * Available Leave is calculated based on current leave allocation balance for the active financial year. Leave Taken is the total of all approved leaves in the active financial year.
        </div>
    </div>
</div>
@endsection
