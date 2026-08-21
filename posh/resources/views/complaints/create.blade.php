@extends('layouts.posh')

@section('title', 'File Complaint')
@section('page-title', 'New Complaint')
@section('page-subtitle', '')

@section('page-banner')
<div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-blue-800 via-blue-900 to-indigo-900 text-white shadow-xl">
    <div class="absolute inset-0 opacity-20">
        <div class="absolute -bottom-6 left-1/4 h-32 w-32 rounded-full bg-blue-400 blur-3xl"></div>
        <div class="absolute -top-8 right-0 h-40 w-40 rounded-full bg-indigo-400 blur-3xl"></div>
    </div>
    <div class="relative px-6 py-6 lg:px-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-white/10 ring-1 ring-white/20">
                <i class="fas fa-file-circle-plus text-xl text-blue-200"></i>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-widest text-blue-200/90">Secure intake</p>
                <h1 class="text-xl font-bold tracking-tight lg:text-2xl">New Complaint</h1>
                <p class="mt-1 text-sm text-slate-300">Confidential — Section 9, POSH Act 2013</p>
            </div>
        </div>
        <a href="{{ route('employee.portal') }}" class="inline-flex items-center gap-2 rounded-xl bg-white/10 px-4 py-2 text-sm font-medium ring-1 ring-white/20 hover:bg-white/20 transition shrink-0">
            <i class="fas fa-arrow-left"></i> Back to portal
        </a>
    </div>
</div>
@endsection

@section('content')

@if ($errors->any())
    <div class="mb-6 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
        <p class="font-semibold mb-1"><i class="fas fa-circle-exclamation mr-1"></i> Please fix the following:</p>
        <ul class="list-disc list-inside space-y-0.5 text-rose-700">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('complaints.store') }}" enctype="multipart/form-data" id="complaint-form" class="space-y-6">
    @csrf

    {{-- Guidance --}}
    <div class="flex gap-4 rounded-2xl border border-blue-200/80 bg-gradient-to-r from-blue-50 to-indigo-50/80 px-5 py-4 shadow-sm">
        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm">
            <i class="fas fa-circle-info text-sm"></i>
        </div>
        <div class="text-sm text-slate-700 leading-relaxed">
            <p class="font-semibold text-blue-950">Before you submit</p>
            <p class="mt-1">Complaints must normally be filed within <strong>3 months</strong> of the incident (IC may extend by 3 more months). Only the Internal Committee sees full details if you file anonymously.</p>
        </div>
    </div>

    {{-- Anonymous option --}}
    <label class="group flex cursor-pointer items-start gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-indigo-300 hover:shadow-md has-[:checked]:border-indigo-400 has-[:checked]:ring-2 has-[:checked]:ring-indigo-100">
        <input type="checkbox" name="is_anonymous" value="1" id="f-anon"
            class="mt-1 h-5 w-5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
            {{ old('is_anonymous') ? 'checked' : '' }}>
        <div>
            <span class="flex items-center gap-2 text-sm font-semibold text-blue-950">
                <i class="fas fa-user-secret text-indigo-500"></i> File as anonymous
            </span>
            <p class="mt-1 text-sm text-slate-500">Your identity is hidden from the workplace; the IC can still investigate your complaint.</p>
        </div>
    </label>

    {{-- Your details --}}
    <section class="rounded-2xl border border-slate-200/80 bg-white shadow-sm overflow-hidden">
        <div class="flex items-center gap-3 border-b border-slate-100 bg-slate-50/80 px-5 py-4">
            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-100 text-sm font-bold text-blue-700">1</span>
            <div>
                <h2 class="text-sm font-semibold text-blue-950">Your details</h2>
                <p class="text-xs text-slate-500">Optional when filing anonymously</p>
            </div>
        </div>
        <div id="identity-fields" class="grid grid-cols-1 sm:grid-cols-3 gap-5 p-5 transition-opacity">
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Your name</label>
                <input type="text" name="complainant_name" value="{{ old('complainant_name', auth()->user()->name) }}"
                    class="complaint-input w-full rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-700 placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/20 outline-none transition">
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Employee code</label>
                <input type="text" name="employee_code" value="{{ old('employee_code', auth()->user()->employee_code) }}"
                    class="complaint-input w-full rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-700 placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/20 outline-none transition">
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Department</label>
                <input type="text" name="department" value="{{ old('department', auth()->user()->department) }}"
                    class="complaint-input w-full rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-700 placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/20 outline-none transition">
            </div>
        </div>
    </section>

    {{-- Respondent & incident --}}
    <section class="rounded-2xl border border-slate-200/80 bg-white shadow-sm overflow-hidden">
        <div class="flex items-center gap-3 border-b border-slate-100 bg-slate-50/80 px-5 py-4">
            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-100 text-sm font-bold text-indigo-700">2</span>
            <div>
                <h2 class="text-sm font-semibold text-blue-950">Respondent &amp; incident</h2>
                <p class="text-xs text-slate-500">Who and when — required fields marked *</p>
            </div>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 p-5">
            <div class="sm:col-span-2 lg:col-span-1">
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Respondent name <span class="text-rose-500">*</span></label>
                <input type="text" name="respondent_name" value="{{ old('respondent_name') }}" required
                    class="complaint-input w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 outline-none transition">
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Respondent type <span class="text-rose-500">*</span></label>
                <div class="relative">
                    <select name="respondent_type" id="respondent-type" required
                        class="complaint-input w-full appearance-none rounded-xl border border-slate-200 bg-white px-4 py-2.5 pr-10 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 outline-none transition">
                        @foreach($respondentTypes as $key => $label)
                            <option value="{{ $key }}" @selected(old('respondent_type') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <i class="fas fa-chevron-down pointer-events-none absolute right-3.5 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                </div>
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Respondent department</label>
                <input type="text" name="respondent_department" value="{{ old('respondent_department') }}"
                    class="complaint-input w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 outline-none transition">
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Incident date <span class="text-rose-500">*</span></label>
                <input type="date" name="incident_date" value="{{ old('incident_date') }}" required max="{{ date('Y-m-d') }}"
                    class="complaint-input w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 outline-none transition">
            </div>
            <div class="sm:col-span-2">
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Location / medium</label>
                <input type="text" name="incident_location" value="{{ old('incident_location') }}" placeholder="Office, Teams, WhatsApp, client site…"
                    class="complaint-input w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 outline-none transition">
            </div>
        </div>
    </section>

    {{-- Description --}}
    <section class="rounded-2xl border border-slate-200/80 bg-white shadow-sm overflow-hidden">
        <div class="flex items-center gap-3 border-b border-slate-100 bg-slate-50/80 px-5 py-4">
            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-violet-100 text-sm font-bold text-violet-700">3</span>
            <div>
                <h2 class="text-sm font-semibold text-blue-950">What happened</h2>
                <p class="text-xs text-slate-500">Minimum 20 characters — be factual and specific</p>
            </div>
        </div>
        <div class="p-5 space-y-4">
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Description of incident <span class="text-rose-500">*</span></label>
                <textarea name="description" rows="6" required minlength="20" placeholder="What happened, when, and the impact on you…"
                    class="complaint-input w-full resize-y rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm leading-relaxed placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 outline-none transition">{{ old('description') }}</textarea>
                <p class="mt-1.5 text-xs text-slate-400" id="desc-count">0 characters (minimum 20)</p>
            </div>

            <label class="flex cursor-pointer items-start gap-4 rounded-xl border border-amber-200/80 bg-amber-50/50 p-4 transition hover:bg-amber-50 has-[:checked]:ring-2 has-[:checked]:ring-amber-200">
                <input type="checkbox" name="vs_employer" value="1" id="f-employer"
                    class="mt-0.5 h-4 w-4 rounded border-amber-300 text-amber-600 focus:ring-amber-500"
                    {{ old('vs_employer') ? 'checked' : '' }}>
                <div class="text-sm">
                    <span class="font-semibold text-amber-900">Complaint against employer</span>
                    <p class="mt-0.5 text-amber-800/80">Routes to the Local Committee per POSH rules.</p>
                </div>
            </label>
        </div>
    </section>

    {{-- Evidence --}}
    <section class="rounded-2xl border border-slate-200/80 bg-white shadow-sm overflow-hidden">
        <div class="flex items-center gap-3 border-b border-slate-100 bg-slate-50/80 px-5 py-4">
            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-100 text-sm font-bold text-emerald-700">4</span>
            <div>
                <h2 class="text-sm font-semibold text-blue-950">Evidence <span class="font-normal text-slate-400">(optional)</span></h2>
                <p class="text-xs text-slate-500">PDF, images, Word — max 10MB each</p>
            </div>
        </div>
        <div class="p-5">
            <input type="file" name="evidence[]" id="evidence-input" class="sr-only" multiple
                accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,application/pdf,image/*">

            <div id="dropzone"
                class="relative flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50/50 px-6 py-10 text-center transition hover:border-blue-400 hover:bg-blue-50/30 cursor-pointer">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white shadow-lg shadow-blue-500/25 mb-4">
                    <i class="fas fa-cloud-arrow-up text-xl"></i>
                </div>
                <p class="text-sm font-semibold text-slate-700">Drag &amp; drop files here</p>
                <p class="mt-1 text-xs text-slate-500">or <span class="font-semibold text-blue-600">browse from your device</span></p>
                <p class="mt-3 inline-flex items-center gap-1.5 rounded-full bg-white px-3 py-1 text-xs text-slate-500 ring-1 ring-slate-200">
                    <i class="fas fa-file-pdf text-rose-500"></i>
                    <i class="fas fa-file-image text-blue-500"></i>
                    <i class="fas fa-file-word text-indigo-500"></i>
                    Screenshots, emails, documents
                </p>
            </div>

            <ul id="file-list" class="mt-4 space-y-2 hidden"></ul>
        </div>
    </section>

    {{-- Actions --}}
    <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-between gap-4 rounded-2xl border border-slate-200/80 bg-white px-5 py-4 shadow-sm">
        <p class="text-xs text-slate-500 flex items-center gap-2">
            <i class="fas fa-lock text-slate-400"></i>
            Submitted securely; only authorised IC/LC members can access.
        </p>
        <div class="flex flex-wrap items-center gap-3 shrink-0">
            <a href="{{ route('employee.portal') }}"
                class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                Cancel
            </a>
            <button type="submit"
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-2.5 text-sm font-semibold text-white shadow-md shadow-blue-500/20 transition hover:from-blue-700 hover:to-indigo-700 hover:shadow-lg">
                <i class="fas fa-paper-plane"></i> Submit complaint
            </button>
        </div>
    </div>
</form>

@push('scripts')
<script>
(function () {
    const anon = document.getElementById('f-anon');
    const identity = document.getElementById('identity-fields');
    const desc = document.querySelector('textarea[name="description"]');
    const descCount = document.getElementById('desc-count');
    const dropzone = document.getElementById('dropzone');
    const fileInput = document.getElementById('evidence-input');
    const fileList = document.getElementById('file-list');
    const respondentType = document.getElementById('respondent-type');
    const vsEmployer = document.getElementById('f-employer');

    let selectedFiles = [];

    function syncAnon() {
        if (!anon || !identity) return;
        const on = anon.checked;
        identity.style.opacity = on ? '0.45' : '1';
        identity.querySelectorAll('input').forEach(function (el) {
            el.disabled = on;
            el.tabIndex = on ? -1 : 0;
        });
    }
    anon?.addEventListener('change', syncAnon);
    syncAnon();

    function updateDescCount() {
        if (!desc || !descCount) return;
        const n = desc.value.length;
        descCount.textContent = n + ' characters' + (n < 20 ? ' (minimum 20)' : '');
        descCount.classList.toggle('text-emerald-600', n >= 20);
        descCount.classList.toggle('text-slate-400', n < 20);
    }
    desc?.addEventListener('input', updateDescCount);
    updateDescCount();

    respondentType?.addEventListener('change', function () {
        if (this.value === 'employer' && vsEmployer) vsEmployer.checked = true;
    });

    function formatSize(bytes) {
        if (bytes < 1024) return bytes + ' B';
        if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
        return (bytes / 1048576).toFixed(1) + ' MB';
    }

    function fileIcon(name) {
        const ext = (name.split('.').pop() || '').toLowerCase();
        if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext)) return { cls: 'fa-file-image', color: 'text-blue-500 bg-blue-50' };
        if (ext === 'pdf') return { cls: 'fa-file-pdf', color: 'text-rose-500 bg-rose-50' };
        if (['doc', 'docx'].includes(ext)) return { cls: 'fa-file-word', color: 'text-indigo-500 bg-indigo-50' };
        return { cls: 'fa-file', color: 'text-slate-500 bg-slate-100' };
    }

    function syncInputFiles() {
        const dt = new DataTransfer();
        selectedFiles.forEach(function (f) { dt.items.add(f); });
        fileInput.files = dt.files;
    }

    function renderFileList() {
        fileList.innerHTML = '';
        if (!selectedFiles.length) {
            fileList.classList.add('hidden');
            return;
        }
        fileList.classList.remove('hidden');
        selectedFiles.forEach(function (file, index) {
            const icon = fileIcon(file.name);
            const li = document.createElement('li');
            li.className = 'flex items-center gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-sm';
            li.innerHTML =
                '<span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg ' + icon.color + '">' +
                '<i class="fas ' + icon.cls + '"></i></span>' +
                '<div class="min-w-0 flex-1">' +
                '<p class="truncate text-sm font-medium text-slate-700">' + file.name + '</p>' +
                '<p class="text-xs text-slate-500">' + formatSize(file.size) + '</p>' +
                '</div>' +
                '<button type="button" class="shrink-0 rounded-lg p-2 text-slate-400 hover:bg-rose-50 hover:text-rose-600 transition" data-remove="' + index + '" aria-label="Remove">' +
                '<i class="fas fa-xmark"></i></button>';
            fileList.appendChild(li);
        });
        fileList.querySelectorAll('[data-remove]').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.stopPropagation();
                const i = parseInt(btn.getAttribute('data-remove'), 10);
                selectedFiles.splice(i, 1);
                syncInputFiles();
                renderFileList();
            });
        });
    }

    function addFiles(fileListLike) {
        const max = 10 * 1024 * 1024;
        const allowed = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];
        Array.from(fileListLike).forEach(function (file) {
            const ext = (file.name.split('.').pop() || '').toLowerCase();
            if (!allowed.includes(ext)) return;
            if (file.size > max) return;
            if (selectedFiles.some(function (f) { return f.name === file.name && f.size === file.size; })) return;
            selectedFiles.push(file);
        });
        syncInputFiles();
        renderFileList();
    }

    dropzone?.addEventListener('click', function () { fileInput.click(); });
    fileInput?.addEventListener('change', function () {
        addFiles(fileInput.files);
    });

    ['dragenter', 'dragover'].forEach(function (ev) {
        dropzone?.addEventListener(ev, function (e) {
            e.preventDefault();
            e.stopPropagation();
            dropzone.classList.add('border-blue-500', 'bg-blue-50/50', 'ring-2', 'ring-blue-200');
        });
    });
    ['dragleave', 'drop'].forEach(function (ev) {
        dropzone?.addEventListener(ev, function (e) {
            e.preventDefault();
            e.stopPropagation();
            dropzone.classList.remove('border-blue-500', 'bg-blue-50/50', 'ring-2', 'ring-blue-200');
            if (ev === 'drop' && e.dataTransfer?.files?.length) addFiles(e.dataTransfer.files);
        });
    });
})();
</script>
@endpush
@endsection
