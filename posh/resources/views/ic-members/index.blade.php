@extends('layouts.posh')

@section('title', 'IC Setup')
@section('page-title', 'IC Setup')
@section('page-subtitle', '')

@php
    $input = 'w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-blue-950 placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none transition';
    $label = 'mb-1 block text-[10px] font-semibold uppercase tracking-wide text-slate-500';
    $womenPct = $total > 0 ? round(($womenCount / $total) * 100) : 0;

    $roleHints = [
        'presiding_officer' => 'Must be a senior woman employee',
        'internal_member' => 'Employee of the organisation',
        'external_member' => 'From NGO / association (not employer)',
        'member_secretary' => 'Records & coordinates IC work',
    ];
@endphp

@section('page-banner')
<div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-blue-800 via-indigo-800 to-violet-900 text-white shadow-xl">
    <div class="absolute inset-0 opacity-20">
        <div class="absolute -top-6 right-0 h-32 w-32 rounded-full bg-violet-400 blur-3xl"></div>
    </div>
    <div class="relative px-6 py-5 lg:px-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-white/15 ring-1 ring-white/25">
                <i class="fas fa-people-group text-xl text-indigo-200"></i>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-widest text-indigo-200/90">Section 4 — POSH Rules</p>
                <h1 class="text-xl font-bold tracking-tight lg:text-2xl">Internal Committee Setup</h1>
                <p class="mt-1 text-sm text-indigo-100/90">Constitute your IC before handling complaints</p>
            </div>
        </div>
        @if($total > 0)
            <span class="inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-semibold ring-1 shrink-0 {{ $meetsWomenQuota ? 'bg-emerald-500/25 text-emerald-100 ring-emerald-300/40' : 'bg-amber-500/25 text-amber-100 ring-amber-300/40' }}">
                <i class="fas {{ $meetsWomenQuota ? 'fa-check-circle' : 'fa-triangle-exclamation' }}"></i>
                {{ $womenCount }}/{{ $total }} women ({{ $womenPct }}%)
            </span>
        @endif
    </div>
</div>
@endsection

@section('content')

{{-- Quick stats --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm">
        <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Total members</p>
        <p class="mt-1 text-2xl font-bold text-blue-950">{{ $total }}</p>
    </div>
    <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm">
        <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Women on IC</p>
        <p class="mt-1 text-2xl font-bold text-blue-950">{{ $womenCount }}</p>
        <div class="mt-2 h-1.5 rounded-full bg-slate-100 overflow-hidden">
            <div class="h-full rounded-full {{ $meetsWomenQuota ? 'bg-emerald-500' : 'bg-amber-500' }}" style="width: {{ min(100, $womenPct) }}%"></div>
        </div>
    </div>
    <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm">
        <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">50% women rule</p>
        <p class="mt-1 text-sm font-semibold {{ $meetsWomenQuota ? 'text-emerald-700' : ($total === 0 ? 'text-slate-500' : 'text-amber-700') }}">
            @if($total === 0)
                Add members to check
            @elseif($meetsWomenQuota)
                <i class="fas fa-check mr-1"></i> Requirement met
            @else
                <i class="fas fa-info-circle mr-1"></i> Need more women members
            @endif
        </p>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
    {{-- Add member --}}
    <div class="xl:col-span-1 order-first xl:order-2">
        <section class="xl:sticky xl:top-24 rounded-2xl border-2 border-indigo-200/80 bg-gradient-to-b from-indigo-50/80 to-white shadow-sm overflow-hidden">
            <div class="border-b border-indigo-100 bg-indigo-600/10 px-5 py-4">
                <h2 class="text-sm font-semibold text-blue-950 flex items-center gap-2">
                    <i class="fas fa-user-plus text-indigo-600"></i> Add IC member
                </h2>
                <p class="text-xs text-slate-600 mt-0.5">New person joins the committee</p>
            </div>
            <form method="POST" action="{{ route('ic-members.store') }}" class="p-5 space-y-5">
                @csrf

                <div>
                    <p class="text-xs font-semibold text-indigo-800 mb-3 flex items-center gap-1.5">
                        <span class="flex h-5 w-5 items-center justify-center rounded-full bg-indigo-600 text-[10px] font-bold text-white">1</span>
                        @if(($org->usesPayrollEmployees() ?? true))
                            Pick from {{ $org->usesPayrollEmployees() ? 'Payroll' : 'POSH' }} employees
                        @else
                            Person details
                        @endif
                    </p>
                    <div class="space-y-3">
                        @if(isset($directoryEmployees) && $directoryEmployees->isNotEmpty())
                        <div id="internal-picker-wrap">
                            <label class="{{ $label }}">Internal member from directory</label>
                            <select name="employee_directory_id" id="employee-directory-id" class="{{ $input }}">
                                <option value="">— Manual entry / External —</option>
                                @foreach($directoryEmployees as $dir)
                                    <option value="{{ $dir->id }}" @selected(old('employee_directory_id') == $dir->id)
                                        data-name="{{ $dir->name }}"
                                        data-email="{{ $dir->email }}"
                                        data-code="{{ $dir->employee_code }}"
                                        data-dept="{{ $dir->department }}"
                                        data-desig="{{ $dir->designation }}">
                                        {{ $dir->name }} · {{ $dir->employee_code ?? $dir->email }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-[11px] text-slate-500">For external NGO / legal experts, leave blank and choose External member role.</p>
                        </div>
                        @endif
                        <div>
                            <label class="{{ $label }}">Full name <span class="text-rose-500">*</span></label>
                            <input type="text" name="name" id="ic-member-name" value="{{ old('name') }}" required class="{{ $input }}" placeholder="e.g. Anita Sharma">
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="{{ $label }}">Employee code</label>
                                <input type="text" name="employee_code" id="ic-member-code" value="{{ old('employee_code') }}" class="{{ $input }}" placeholder="EMP-001">
                            </div>
                            <div>
                                <label class="{{ $label }}">Department</label>
                                <input type="text" name="department" id="ic-member-dept" value="{{ old('department') }}" class="{{ $input }}" placeholder="HR">
                            </div>
                        </div>
                        <div>
                            <label class="{{ $label }}">Designation</label>
                            <input type="text" name="designation" id="ic-member-desig" value="{{ old('designation') }}" class="{{ $input }}" placeholder="VP — Human Resources">
                        </div>
                    </div>
                </div>

                <div>
                    <p class="text-xs font-semibold text-indigo-800 mb-3 flex items-center gap-1.5">
                        <span class="flex h-5 w-5 items-center justify-center rounded-full bg-indigo-600 text-[10px] font-bold text-white">2</span>
                        Role &amp; contact
                    </p>
                    <div class="space-y-3">
                        <div>
                            <label class="{{ $label }}">IC role <span class="text-rose-500">*</span></label>
                            <div class="relative">
                                <select name="ic_role" required class="{{ $input }} appearance-none pr-9" id="add-ic-role">
                                    @foreach($icRoles as $key => $roleLabel)
                                        <option value="{{ $key }}" @selected(old('ic_role') === $key)>{{ $roleLabel }}</option>
                                    @endforeach
                                </select>
                                <i class="fas fa-chevron-down pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                            </div>
                            <p class="mt-1 text-[11px] text-indigo-700/80" id="role-hint">{{ $roleHints['presiding_officer'] ?? '' }}</p>
                        </div>
                        <div>
                            <label class="{{ $label }}">Work email</label>
                            <input type="email" name="email" id="ic-member-email" value="{{ old('email') }}" class="{{ $input }}" placeholder="name@company.com">
                            <p class="mt-1 text-[11px] text-slate-500">Used for SSO / IC login mapping</p>
                        </div>
                        <div>
                            <label class="{{ $label }}">Contact number</label>
                            <input type="text" name="contact_number" value="{{ old('contact_number') }}" class="{{ $input }}" placeholder="+91 …">
                        </div>
                    </div>
                </div>

                <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-indigo-200 bg-white p-3 has-[:checked]:ring-2 has-[:checked]:ring-indigo-200">
                    <input type="checkbox" name="is_woman" value="1" checked class="mt-0.5 h-4 w-4 rounded text-indigo-600 focus:ring-indigo-500">
                    <span class="text-sm text-blue-950">
                        <span class="font-semibold">Woman member</span>
                        <span class="block text-xs text-slate-500 mt-0.5">Counts toward the 50% women requirement on IC</span>
                    </span>
                </label>

                <button type="submit"
                    class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 py-3 text-sm font-semibold text-white shadow-md hover:from-blue-700 hover:to-indigo-700 transition">
                    <i class="fas fa-plus-circle"></i> Add to committee
                </button>
            </form>
        </section>

        <div class="mt-4 rounded-xl border border-blue-200/80 bg-blue-50/60 px-4 py-3 text-xs text-blue-900 leading-relaxed">
            <p class="font-semibold mb-1"><i class="fas fa-lightbulb text-blue-600 mr-1"></i> Typical IC composition</p>
            <ul class="list-disc list-inside space-y-0.5 text-blue-800/90">
                <li>Presiding Officer (senior woman)</li>
                <li>Two internal employee members</li>
                <li>One external member (NGO / legal)</li>
            </ul>
        </div>
    </div>

    {{-- Member roster --}}
    <div class="xl:col-span-2 order-2 xl:order-1 space-y-4">
        <div class="flex items-center justify-between gap-3">
            <h2 class="text-base font-semibold text-blue-950">Current committee ({{ $total }})</h2>
        </div>

        @forelse($members as $member)
            @php
                $initials = collect(preg_split('/\s+/', trim($member->name)))->take(2)->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->join('');
                $roleColors = [
                    'presiding_officer' => 'bg-violet-100 text-violet-800 ring-violet-200',
                    'internal_member' => 'bg-blue-100 text-blue-800 ring-blue-200',
                    'external_member' => 'bg-teal-100 text-teal-800 ring-teal-200',
                    'member_secretary' => 'bg-indigo-100 text-indigo-800 ring-indigo-200',
                ];
                $roleClass = $roleColors[$member->ic_role] ?? 'bg-slate-100 text-slate-700 ring-slate-200';
            @endphp
            <article class="rounded-2xl border border-slate-200/80 bg-white shadow-sm overflow-hidden">
                <div class="flex flex-wrap items-center gap-3 border-b border-slate-100 bg-slate-50/60 px-4 py-3">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 to-violet-600 text-sm font-bold text-white shadow-sm">
                        {{ $initials }}
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="font-semibold text-blue-950">{{ $member->name }}</p>
                        <p class="text-xs text-slate-500 truncate">
                            @if($member->designation){{ $member->designation }}@endif
                            @if($member->department) · {{ $member->department }}@endif
                        </p>
                    </div>
                    <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[11px] font-semibold ring-1 {{ $roleClass }}">
                        {{ $member->roleLabel() }}
                    </span>
                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-medium {{ $member->isExternal() ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-600' }}">
                        {{ $member->originLabel() }}
                    </span>
                    @if($member->is_active)
                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-semibold text-emerald-800">Active</span>
                    @else
                        <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-semibold text-slate-600">Inactive</span>
                    @endif
                </div>

                <form method="POST" action="{{ route('ic-members.update', $member) }}" class="p-4 space-y-4">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="{{ $label }}">Name</label>
                            <input type="text" name="name" value="{{ $member->name }}" required class="{{ $input }}">
                        </div>
                        <div>
                            <label class="{{ $label }}">IC role</label>
                            <select name="ic_role" class="{{ $input }}">
                                @foreach($icRoles as $key => $roleLabel)
                                    <option value="{{ $key }}" @selected($member->ic_role === $key)>{{ $roleLabel }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="{{ $label }}">Email</label>
                            <input type="email" name="email" value="{{ $member->email }}" class="{{ $input }}">
                        </div>
                        <div>
                            <label class="{{ $label }}">Contact</label>
                            <input type="text" name="contact_number" value="{{ $member->contact_number }}" class="{{ $input }}">
                        </div>
                        <div>
                            <label class="{{ $label }}">Employee code</label>
                            <input type="text" name="employee_code" value="{{ $member->employee_code }}" class="{{ $input }}">
                        </div>
                        <div>
                            <label class="{{ $label }}">Department</label>
                            <input type="text" name="department" value="{{ $member->department }}" class="{{ $input }}">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="{{ $label }}">Designation</label>
                            <input type="text" name="designation" value="{{ $member->designation }}" class="{{ $input }}">
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center justify-between gap-3 pt-2 border-t border-slate-100">
                        <div class="flex flex-wrap gap-4">
                            <label class="inline-flex items-center gap-2 text-sm text-blue-950 cursor-pointer">
                                <input type="checkbox" name="is_woman" value="1" @checked($member->is_woman) class="h-4 w-4 rounded text-indigo-600 focus:ring-indigo-500">
                                Woman member
                            </label>
                            <label class="inline-flex items-center gap-2 text-sm text-blue-950 cursor-pointer">
                                <input type="checkbox" name="is_active" value="1" @checked($member->is_active) class="h-4 w-4 rounded text-indigo-600 focus:ring-indigo-500">
                                Active on IC
                            </label>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="submit"
                                class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-4 py-2 text-xs font-semibold text-white hover:bg-indigo-700 transition">
                                <i class="fas fa-save"></i> Save changes
                            </button>
                        </div>
                    </div>
                </form>

                <div class="border-t border-slate-100 bg-slate-50/50 px-4 py-2.5">
                    <form method="POST" action="{{ route('ic-members.destroy', $member) }}" onsubmit="return confirm('Remove {{ $member->name }} from the IC?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex items-center gap-1.5 text-xs font-semibold text-rose-600 hover:text-rose-800 transition">
                            <i class="fas fa-trash-can"></i> Remove from committee
                        </button>
                    </form>
                </div>
            </article>
        @empty
            <div class="rounded-2xl border-2 border-dashed border-slate-200 bg-white px-6 py-14 text-center">
                <span class="inline-flex h-14 w-14 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-500 mb-3">
                    <i class="fas fa-people-group text-2xl"></i>
                </span>
                <p class="text-sm font-semibold text-blue-950">No IC members yet</p>
                <p class="mt-2 max-w-sm mx-auto text-xs text-slate-500 leading-relaxed">
                    Use the add member form to register your Presiding Officer, internal members, and external member.
                </p>
            </div>
        @endforelse
    </div>
</div>

@push('scripts')
<script>
(function () {
    const roleSelect = document.getElementById('add-ic-role');
    const hint = document.getElementById('role-hint');
    const hints = @json($roleHints);
    if (!roleSelect || !hint) return;
    function updateHint() {
        hint.textContent = hints[roleSelect.value] || '';
    }
    roleSelect.addEventListener('change', updateHint);
    updateHint();

    const dirSelect = document.getElementById('employee-directory-id');
    if (dirSelect) {
        const fields = {
            name: document.getElementById('ic-member-name'),
            email: document.getElementById('ic-member-email'),
            code: document.getElementById('ic-member-code'),
            dept: document.getElementById('ic-member-dept'),
            desig: document.getElementById('ic-member-desig'),
        };
        dirSelect.addEventListener('change', function () {
            const opt = dirSelect.selectedOptions[0];
            if (!opt || !opt.value) return;
            if (fields.name) fields.name.value = opt.dataset.name || '';
            if (fields.email) fields.email.value = opt.dataset.email || '';
            if (fields.code) fields.code.value = opt.dataset.code || '';
            if (fields.dept) fields.dept.value = opt.dataset.dept || '';
            if (fields.desig) fields.desig.value = opt.dataset.desig || '';
            if (roleSelect && roleSelect.value === 'external_member') {
                roleSelect.value = 'internal_member';
                updateHint();
            }
        });
    }
})();
</script>
@endpush
@endsection
