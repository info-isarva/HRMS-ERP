@php
    $screen = $screen ?? 'dashboard';
@endphp
<figure class="guide-screen my-6 rounded-2xl border border-slate-200/90 bg-slate-100/80 p-3 shadow-md overflow-hidden ring-1 ring-slate-200/50">
    <div class="guide-screen-chrome flex items-center gap-2 px-3 py-2 rounded-t-xl bg-slate-200/90 border border-slate-300/50 border-b-0">
        <span class="h-2.5 w-2.5 rounded-full bg-rose-400"></span>
        <span class="h-2.5 w-2.5 rounded-full bg-amber-400"></span>
        <span class="h-2.5 w-2.5 rounded-full bg-emerald-400"></span>
        <span class="ml-2 text-[10px] font-mono text-slate-500 truncate flex-1">{{ config('posh.product_short_name') }} — screen preview</span>
    </div>
    <div class="guide-screen-body rounded-b-xl border border-slate-300/50 bg-white overflow-hidden text-left">
        @switch($screen)
            @case('dashboard')
                <div class="h-8 bg-gradient-to-r from-blue-700 to-indigo-800 px-3 flex items-center gap-2">
                    <span class="w-5 h-5 rounded bg-white/20"></span>
                    <span class="text-[9px] text-white font-semibold">Compliance Command Center</span>
                </div>
                <div class="p-3 grid grid-cols-3 gap-2">
                    @foreach(['Open cases', 'IC members', 'Compliance'] as $label)
                        <div class="rounded-lg border border-slate-100 p-2 bg-slate-50">
                            <p class="text-[8px] text-slate-500">{{ $label }}</p>
                            <p class="text-sm font-bold text-blue-900">12</p>
                        </div>
                    @endforeach
                </div>
                @break
            @case('employee-portal')
                <div class="p-3 space-y-2">
                    <div class="rounded-lg bg-amber-50 border border-amber-200 p-2 text-[9px] text-amber-900">Policy acknowledgement pending</div>
                    <div class="grid grid-cols-2 gap-2">
                        <div class="rounded-lg bg-blue-600 text-white p-2 text-[9px] font-semibold text-center">Read policy</div>
                        <div class="rounded-lg border p-2 text-[9px] text-center">File complaint</div>
                    </div>
                    <div class="rounded-lg border p-2">
                        <p class="text-[8px] text-slate-500 mb-1">IC contacts</p>
                        <div class="h-2 bg-slate-100 rounded w-3/4"></div>
                    </div>
                </div>
                @break
            @case('policy-employee')
                <div class="h-6 bg-gradient-to-r from-blue-600 to-violet-700"></div>
                <div class="p-3 space-y-2">
                    <div class="h-2 bg-slate-100 rounded w-full"></div>
                    <div class="h-2 bg-slate-100 rounded w-5/6"></div>
                    <div class="h-2 bg-slate-100 rounded w-4/6"></div>
                    <div class="mt-3 rounded-lg bg-emerald-600 text-white text-[9px] font-semibold py-2 text-center">I acknowledge this policy</div>
                </div>
                @break
            @case('new-complaint')
                <div class="p-3 space-y-2">
                    <p class="text-[10px] font-bold text-blue-950">File New Complaint</p>
                    @foreach(['Complainant', 'Respondent', 'Incident date', 'Description'] as $f)
                        <div>
                            <p class="text-[7px] text-slate-500 uppercase">{{ $f }}</p>
                            <div class="h-6 rounded border border-slate-200 bg-slate-50 mt-0.5"></div>
                        </div>
                    @endforeach
                    <div class="h-7 rounded-lg bg-blue-600 text-[9px] text-white flex items-center justify-center font-semibold">Submit complaint</div>
                </div>
                @break
            @case('my-cases')
                <div class="p-3">
                    <p class="text-[10px] font-bold text-blue-950 mb-2">My Cases</p>
                    @foreach([['POSH-2026-0001', 'Under review'], ['POSH-2026-0003', 'Closed']] as $row)
                        <div class="flex justify-between items-center py-2 border-b border-slate-100 text-[9px]">
                            <span class="font-mono text-indigo-800">{{ $row[0] }}</span>
                            <span class="text-emerald-700 text-[8px]">{{ $row[1] }}</span>
                        </div>
                    @endforeach
                </div>
                @break
            @case('all-cases')
                <div class="p-3">
                    <div class="flex gap-2 mb-2">
                        <div class="h-6 flex-1 rounded border bg-slate-50 text-[8px] flex items-center px-2 text-slate-400">Search cases…</div>
                        <div class="h-6 w-16 rounded bg-blue-600"></div>
                    </div>
                    @foreach(range(1, 3) as $i)
                        <div class="flex gap-2 py-1.5 border-b border-slate-50 text-[8px]">
                            <span class="font-mono text-blue-800">POSH-2026-000{{ $i }}</span>
                            <span class="ml-auto text-slate-500">Operate →</span>
                        </div>
                    @endforeach
                </div>
                @break
            @case('operate')
                <div class="flex min-h-[120px]">
                    <div class="w-1/3 bg-slate-50 border-r p-2 space-y-1">
                        @foreach(['1 Review', '2 Conciliation', '3 Interim', '4 Notice'] as $s)
                            <div class="text-[7px] py-1 px-1 rounded {{ $loop->first ? 'bg-indigo-100 text-indigo-800 font-semibold' : 'text-slate-500' }}">{{ $s }}</div>
                        @endforeach
                    </div>
                    <div class="flex-1 p-2">
                        <p class="text-[9px] font-bold text-blue-950">Step 1: IC Review</p>
                        <div class="mt-2 h-12 rounded border border-dashed border-slate-200 bg-slate-50/50"></div>
                        <div class="mt-2 h-5 w-20 rounded bg-blue-600 ml-auto"></div>
                    </div>
                </div>
                @break
            @case('compliance')
                <div class="p-3 grid grid-cols-2 gap-2">
                    <div class="col-span-2 h-4 rounded bg-indigo-100 text-[8px] flex items-center px-2 text-indigo-800 font-semibold">Employer duties 71%</div>
                    @foreach(range(1, 4) as $i)
                        <div class="flex items-center gap-1 text-[8px]">
                            <span class="w-3 h-3 rounded border border-emerald-400 bg-emerald-50"></span>
                            <span class="h-2 bg-slate-100 rounded flex-1"></span>
                        </div>
                    @endforeach
                </div>
                @break
            @case('policy-admin')
                <div class="p-3">
                    <div class="flex justify-between mb-2">
                        <span class="text-[9px] font-bold text-blue-950">Policy versions</span>
                        <span class="text-[8px] px-2 py-0.5 rounded bg-blue-600 text-white">+ New</span>
                    </div>
                    <div class="rounded-lg border border-emerald-200 bg-emerald-50/50 p-2 text-[8px]">
                        <span class="text-emerald-800 font-semibold">● Active</span> v2026.1 — POSH Workplace Policy
                    </div>
                </div>
                @break
            @case('ic-setup')
                <div class="flex min-h-[100px]">
                    <div class="w-2/5 border-r p-2 bg-indigo-50/30">
                        <p class="text-[8px] font-bold text-blue-950">Add member</p>
                        <div class="mt-1 h-4 rounded border bg-white"></div>
                    </div>
                    <div class="flex-1 p-2 space-y-1">
                        @foreach(['PO · Anita', 'External · Meera'] as $m)
                            <div class="rounded border p-1.5 text-[8px]">{{ $m }}</div>
                        @endforeach
                    </div>
                </div>
                @break
            @case('settings')
                <div class="p-3 grid grid-cols-2 gap-2">
                    <div class="space-y-1">
                        <p class="text-[7px] text-slate-500">Org name</p>
                        <div class="h-5 rounded border bg-slate-50"></div>
                    </div>
                    <div class="space-y-1">
                        <p class="text-[7px] text-slate-500">QR intake</p>
                        <div class="h-5 rounded border bg-indigo-50 font-mono text-[6px] flex items-center px-1 text-indigo-700">/intake/…</div>
                    </div>
                </div>
                @break
            @case('intake')
                <div class="p-4 text-center">
                    <div class="w-10 h-10 mx-auto rounded-lg bg-indigo-100 flex items-center justify-center text-indigo-600 mb-2">
                        <i class="fas fa-qrcode text-lg"></i>
                    </div>
                    <p class="text-[10px] font-bold text-blue-950">Confidential complaint</p>
                    <p class="text-[8px] text-slate-500 mt-1">No login required</p>
                    <div class="mt-3 h-6 rounded-lg bg-blue-600 text-[8px] text-white flex items-center justify-center">Submit</div>
                </div>
                @break
            @case('annual-report')
                <div class="p-3">
                    <p class="text-[9px] font-bold text-blue-950">Annual Report 2026</p>
                    <div class="grid grid-cols-3 gap-1 mt-2">
                        @foreach(['Cases', 'Workshops', 'Acks'] as $s)
                            <div class="rounded bg-slate-50 p-1 text-center text-[8px]"><strong>5</strong><br>{{ $s }}</div>
                        @endforeach
                    </div>
                </div>
                @break
            @case('audit')
                <div class="p-3 space-y-1">
                    @foreach(['Complaint filed', 'Operate step saved', 'Policy published'] as $a)
                        <div class="text-[8px] flex gap-2 py-1 border-b border-slate-50">
                            <span class="text-slate-400">10:3{{ $loop->index }}</span>
                            <span class="text-blue-950">{{ $a }}</span>
                        </div>
                    @endforeach
                </div>
                @break
            @case('management')
                <div class="p-3">
                    <p class="text-[9px] font-bold text-amber-900">60-day action pending</p>
                    <div class="mt-2 rounded-lg border border-amber-200 bg-amber-50 p-2 text-[8px] font-mono">POSH-2026-0002</div>
                </div>
                @break
            @default
                <div class="p-8 text-center text-slate-400 text-sm">
                    <i class="fas fa-desktop text-2xl mb-2"></i>
                    <p>Screen preview</p>
                </div>
        @endswitch
    </div>
    @if(!empty($caption))
        <figcaption class="mt-2 text-center text-xs text-slate-500">{{ $caption }}</figcaption>
    @endif
</figure>
