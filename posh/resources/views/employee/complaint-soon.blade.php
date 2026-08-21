@extends('layouts.posh')

@section('title', 'File Complaint')
@section('page-title', 'File Complaint')
@section('page-subtitle', 'Coming in Phase 2')

@section('content')
<div class="card">
    <div class="card-body">
        <p>Confidential complaint intake (anonymous option, evidence upload, Case ID) will be built in <strong>Phase 2</strong> using the workflow from your <code>poshactresearch</code> prototype.</p>
        <a href="{{ route('employee.portal') }}" class="btn-ghost" style="margin-top:16px;">Back to Employee Portal</a>
    </div>
</div>
@endsection
