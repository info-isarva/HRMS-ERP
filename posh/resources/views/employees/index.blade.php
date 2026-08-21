@extends('layouts.posh')

@section('title', 'Employees')
@section('page-title', 'Employees')
@section('page-subtitle', '')

@php
    $input = 'w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-blue-950 placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none transition';
    $label = 'mb-1 block text-[10px] font-semibold uppercase tracking-wide text-slate-500';
    $readOnly = $org->usesPayrollEmployees();
@endphp

@section('page-banner')
<div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-blue-800 via-indigo-800 to-violet-900 text-white shadow-xl">
    <div class="relative px-6 py-5 lg:px-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <p class="text-xs font-semibold uppercase tracking-widest text-indigo-200/90">{{ $org->deploymentLabel() }}</p>
            <h1 class="text-xl font-bold tracking-tight lg:text-2xl">Employee directory</h1>
            <p class="mt-1 text-sm text-indigo-100/90">
                @if($readOnly)
                    Read-only roster synced from Payroll (demo). External IC members are added separately in IC Setup.
                @else
                    Manage employees directly in POSH for standalone deployments.
                @endif
            </p>
        </div>
        @if($readOnly)
            <form method="POST" action="{{ route('employees.sync') }}">
                @csrf
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-indigo-700 shadow-lg hover:bg-indigo-50 transition">
                    <i class="fas fa-rotate"></i> Sync from Payroll
                </button>
            </form>
        @endif
    </div>
</div>
@endsection

@section('content')

@if(session('success'))
    <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
@endif

<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm">
        <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Total in directory</p>
        <p class="mt-1 text-2xl font-bold text-blue-950">{{ $employees->count() }}</p>
    </div>
    <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm">
        <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Source</p>
        <p class="mt-1 text-sm font-semibold text-indigo-700">{{ config('posh.employee_sources')[$org->employee_source] ?? '—' }}</p>
    </div>
    <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm">
        <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Last payroll sync</p>
        <p class="mt-1 text-sm font-semibold text-blue-950">{{ $org->payroll_synced_at?->format('d M Y, H:i') ?? 'Not synced yet' }}</p>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
    <div class="xl:col-span-2">
        <section class="rounded-2xl border border-slate-200/80 bg-white shadow-sm overflow-hidden">
            <div class="border-b border-slate-100 bg-slate-50/80 px-5 py-4 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-blue-950">Workforce roster</h2>
                @if($readOnly)
                    <span class="text-xs font-medium text-indigo-600 bg-indigo-50 px-2.5 py-1 rounded-full">Read-only</span>
                @endif
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-4 py-3">Name</th>
                            <th class="px-4 py-3">Code</th>
                            <th class="px-4 py-3">Department</th>
                            <th class="px-4 py-3">Source</th>
                            <th class="px-4 py-3">Login</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($employees as $emp)
                            <tr class="hover:bg-indigo-50/30">
                                <td class="px-4 py-3">
                                    <p class="font-medium text-blue-950">{{ $emp->name }}</p>
                                    <p class="text-xs text-slate-500">{{ $emp->email }}</p>
                                </td>
                                <td class="px-4 py-3 text-slate-600">{{ $emp->employee_code ?? '—' }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $emp->department ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium {{ $emp->source === 'payroll' ? 'bg-blue-100 text-blue-700' : 'bg-violet-100 text-violet-700' }}">
                                        {{ $emp->sourceLabel() }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    @if($emp->user_id)
                                        <span class="text-xs text-emerald-600 font-medium"><i class="fas fa-check-circle"></i> Active</span>
                                    @else
                                        <form method="POST" action="{{ route('employees.enable-login', $emp) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">Enable login</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-10 text-center text-slate-500">
                                    @if($readOnly)
                                        Click <strong>Sync from Payroll</strong> to load demo employees.
                                    @else
                                        Add your first employee using the form.
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    @unless($readOnly)
    <div>
        <section class="rounded-2xl border-2 border-indigo-200/80 bg-gradient-to-b from-indigo-50/80 to-white shadow-sm overflow-hidden">
            <div class="border-b border-indigo-100 px-5 py-4">
                <h2 class="text-sm font-semibold text-blue-950">Add employee</h2>
            </div>
            <form method="POST" action="{{ route('employees.store') }}" class="p-5 space-y-3">
                @csrf
                <div>
                    <label class="{{ $label }}">Full name</label>
                    <input type="text" name="name" required class="{{ $input }}">
                </div>
                <div>
                    <label class="{{ $label }}">Email</label>
                    <input type="email" name="email" required class="{{ $input }}">
                </div>
                <div>
                    <label class="{{ $label }}">Employee code</label>
                    <input type="text" name="employee_code" class="{{ $input }}">
                </div>
                <div>
                    <label class="{{ $label }}">Department</label>
                    <input type="text" name="department" class="{{ $input }}">
                </div>
                <div>
                    <label class="{{ $label }}">Designation</label>
                    <input type="text" name="designation" class="{{ $input }}">
                </div>
                <label class="flex items-center gap-2 text-sm text-slate-600">
                    <input type="checkbox" name="create_login" value="1" checked class="rounded text-indigo-600">
                    Create portal login (password: <code class="text-xs">password</code>)
                </label>
                <button type="submit" class="w-full rounded-xl bg-indigo-600 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700 transition">
                    Add employee
                </button>
            </form>
        </section>
    </div>
    @endunless
</div>

<div class="mt-6 rounded-xl border border-amber-200/80 bg-amber-50/60 px-4 py-3 text-xs text-amber-900 leading-relaxed">
    <p class="font-semibold mb-1"><i class="fas fa-info-circle text-amber-600 mr-1"></i> External IC members</p>
    <p>People outside your organisation (NGO, legal expert, Local Committee liaison) are <strong>not</strong> added here. Add them in <a href="{{ route('ic-members.index') }}" class="font-semibold text-indigo-700 hover:underline">IC Setup</a> as <strong>External member</strong>.</p>
</div>
@endsection
