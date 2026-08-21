@extends('layouts.app')

@section('title', 'My Form 16 - HRMS')
@section('page-title', 'My Form 16')

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
                        <i class="fas fa-file-pdf text-white text-xl"></i>
                    </div>
                    <div class="ml-4 min-w-0">
                        <h1 class="text-2xl sm:text-3xl font-bold text-white truncate">My Form 16</h1>
                        <p class="text-emerald-100 text-sm sm:text-base mt-1">
                            Download annual TDS certificates for tax returns
                        </p>
                    </div>
                </div>
                <div class="hidden sm:flex w-14 h-14 bg-white bg-opacity-15 rounded-full items-center justify-center flex-shrink-0 border border-white border-opacity-20">
                    <i class="fas fa-shield-halved text-white text-2xl"></i>
                </div>
            </div>
        </div>
    </div>

    @if(empty($yearsList))
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
            <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-file-circle-xmark text-gray-400 text-3xl"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-800">No Form 16 available</h3>
            <p class="text-gray-500 mt-2 max-w-md mx-auto">
                Your annual Form 16 statement will be available after the end of the financial year.
            </p>
        </div>
    @else
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 bg-opacity-50">
                <h3 class="text-base font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-list text-emerald-500 mr-2"></i> Available Financial Years
                </h3>
            </div>
            <div class="divide-y divide-gray-100">
                @foreach($yearsList as $item)
                    <div class="px-6 py-4 flex items-center justify-between hover:bg-gray-50 transition-colors">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-emerald-50 rounded-lg flex items-center justify-center text-emerald-600">
                                <i class="fas fa-file-invoice text-lg"></i>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-800">{{ $item['label'] }}</p>
                                <p class="text-xs text-gray-500">TDS Certificate under Section 203</p>
                            </div>
                        </div>
                        <div>
                            <a href="{{ route('form16.download', ['year' => $item['year']]) }}" 
                               class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-emerald-600 hover:bg-emerald-700 shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition-colors">
                                <i class="fas fa-download mr-2"></i> Download PDF
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
