@extends('layouts.app')

@section('title', 'Create Attendance Rule - HRMS')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 p-6">
    <div class="max-w-2xl mx-auto">
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl border border-white/20 overflow-hidden">
            <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-8 py-6">
                <h2 class="text-2xl font-bold text-white">Create Attendance Rule</h2>
                <p class="text-indigo-100">Define thresholds for long shifts</p>
            </div>

            <form action="{{ route('attendance-rules.store') }}" method="POST" class="p-8 space-y-6">
                @csrf
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Rule Name</label>
                    <input type="text" name="name" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition-all" placeholder="e.g., Extreme Long Shift Recovery">
                </div>

                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Shift Threshold (Hours)</label>
                        <input type="number" step="0.5" name="shift_threshold_hours" value="18" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition-all">
                        <p class="text-xs text-gray-500 mt-1">If employee works more than this</p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Recovery Offset (Days)</label>
                        <input type="number" name="recovery_days_offset" value="2" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition-all">
                        <p class="text-xs text-gray-500 mt-1">e.g., 2 means Feb 3 if worked Feb 1</p>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Recovery Day Status</label>
                    <select name="recovery_status" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition-all">
                        <option value="compoff">Comp Off (Present)</option>
                        <option value="present">Present</option>
                        <option value="absent">Absent</option>
                    </select>
                </div>

                <div class="pt-4 flex items-center justify-end space-x-4">
                    <a href="{{ route('attendance-rules.index') }}" class="px-6 py-3 text-gray-600 font-medium hover:text-gray-900 transition-colors">Cancel</a>
                    <button type="submit" class="px-8 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-bold rounded-xl shadow-lg hover:from-indigo-700 hover:to-purple-700 transition-all transform hover:scale-105">
                        Save Rule
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
