@extends('layouts.app')

@section('content')
@php
    $selectedFyId = session('selected_financial_year', null);
    $activeFy = \App\Models\FinancialYear::where('active', 1)->first();
    $isHistorical = $selectedFyId && $activeFy && $selectedFyId != $activeFy->id;
@endphp
<div class="container-fluid p-4">
    <div class="card mt-0">

        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h3 class="mb-0">{{ $person->first_name }} {{ $person->last_name }}</h3>
            <div class="d-flex gap-2 justify-content-end align-items-center ms-auto flex-nowrap" style="white-space:nowrap;">
                <a href="{{ route('people.index') }}" class="btn btn-light btn-sm">&laquo; Back</a>
                @if(!$isHistorical)
                <a href="{{ route('people.edit', $person->id) }}" class="btn btn-outline-secondary btn-sm @if(!auth()->user()->hasCrmPermission('edit_crm_contact_person_guard')) disabled @endif"><i class="bi bi-pencil"></i></a>
                <form method="POST" action="{{ route('people.delete', $person->id) }}" style="display:inline; margin:0;">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="delete-person-btn btn btn-outline-danger btn-sm @if(!auth()->user()->hasCrmPermission('delete_crm_contact_person_guard')) disabled @endif" data-person-name="{{ $person->first_name }}" style="margin-left:0"><i class="bi bi-trash"></i></button>
                </form>
                @endif
            </div>
        </div>
        <div class="card-body row">
            <div class="col-md-5 border-end">
                <h5 class="mb-3">DETAILS</h5>
                <div class="mb-4">
                    <div class="row mb-2 align-items-center">
                        <div class="col-5 col-sm-4 fw-bold details-label text-start text-sm-end">First Name</div>
                        <div class="col-7 col-sm-8 details-value text-end text-sm-start">{{ $person->first_name }}</div>
                    </div>
                    <div class="row mb-2 align-items-center">
                        <div class="col-5 col-sm-4 fw-bold details-label text-start text-sm-end">Last Name</div>
                        <div class="col-7 col-sm-8 details-value text-end text-sm-start">{{ $person->last_name }}</div>
                    </div>
                    <div class="row mb-2 align-items-center">
                        <div class="col-5 col-sm-4 fw-bold details-label text-start text-sm-end">Gender</div>
                        <div class="col-7 col-sm-8 details-value text-end text-sm-start">{{ $person->gender }}</div>
                    </div>
                    <div class="row mb-2 align-items-center">
                        <div class="col-5 col-sm-4 fw-bold details-label text-start text-sm-end">Date of Birth</div>
                        <div class="col-7 col-sm-8 details-value text-end text-sm-start">{{ $person->dob }}</div>
                    </div>
                    <div class="row mb-2 align-items-center">
                        <div class="col-5 col-sm-4 fw-bold details-label text-start text-sm-end">Email</div>
                        <div class="col-7 col-sm-8 details-value text-end text-sm-start">{{ $person->email }}</div>
                    </div>
                    <div class="row mb-2 align-items-center">
                        <div class="col-5 col-sm-4 fw-bold details-label text-start text-sm-end">Phone</div>
                        <div class="col-7 col-sm-8 details-value text-end text-sm-start">{{ $person->phone }}</div>
                    </div>
                    <div class="row mb-2 align-items-center">
                        <div class="col-5 col-sm-4 fw-bold details-label text-start text-sm-end">Mobile</div>
                        <div class="col-7 col-sm-8 details-value text-end text-sm-start">{{ $person->mobile }}</div>
                    </div>
                    <div class="row mb-2 align-items-center">
                        <div class="col-5 col-sm-4 fw-bold details-label text-start text-sm-end">Job Title</div>
                        <div class="col-7 col-sm-8 details-value text-end text-sm-start">{{ $person->job_title }}</div>
                    </div>
                    <div class="row mb-2 align-items-center">
                        <div class="col-5 col-sm-4 fw-bold details-label text-start text-sm-end">Lead Source</div>
                        <div class="col-7 col-sm-8 details-value text-end text-sm-start">{{ $person->lead_source }}</div>
                    </div>
                    <div class="row mb-2 align-items-center">
                        <div class="col-5 col-sm-4 fw-bold details-label text-start text-sm-end">Address</div>
                        <div class="col-7 col-sm-8 details-value text-end text-sm-start">{{ $person->address }}</div>
                    </div>
                    <div class="row mb-2 align-items-center">
                        <div class="col-5 col-sm-4 fw-bold details-label text-start text-sm-end">Notes</div>
                        <div class="col-7 col-sm-8 details-value text-end text-sm-start">{{ $person->notes }}</div>
                    </div>
                </div>
                {{-- <div class="card mb-3">
                    <div class="card-header py-2 px-3"><i class="bi bi-person-circle me-2"></i>OWNER</div>
                    <div class="card-body py-2 px-3">
                        <div class="row align-items-center">
                            <div class="col-4 text-end fw-bold">Name</div>
                            <div class="col-8"><a href="#">{{ optional($person->owner)->name ?? '-' }}</a></div>
                        </div>
                    </div>
                </div> --}}
            </div>
            <div class="col-md-7">
                <div class="card mb-3">
                    <div class="card-header py-2 px-3 d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-building me-2"></i>Company</span>
                        @if (!$person->organization_id)
                        @if(!$isHistorical)
                            <button class="btn btn-outline-secondary btn-sm @if(!auth()->user()->hasCrmPermission('create_crm_organization_guard')) disabled @endif" data-bs-toggle="modal" data-bs-target="#addOrganizationModal"><i class="bi bi-plus"></i> Add Organization</button>
                        @endif
                        @endif
                    </div>
                    <div class="card-body py-2 px-3">
                        @if ($person->organization_id)
                        <ul class="list-group">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span><i class="bi bi-buildings"></i> <a class="text-decoration-none" href="{{ route('organizations.show', optional($person->organization)->id) }}">{{ optional($person->organization)->name ?? '-' }}</a></span>
                            </li>
                        </ul>
                        @else
                        <div class="text-center text-muted">No Company linked.</div>
                        @endif
                    </div>
                </div>
                <!-- <ul class="nav nav-tabs mb-3">
                    <li class="nav-item"><a class="nav-link active" href="#">Activity</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Notes</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Tasks</a></li>
                </ul> -->
                <!-- <div class="tab-content p-3 border bg-white" style="min-height:300px;"> -->
                    <!-- Tab content placeholder -->
                <!-- </div> -->
            </div>
        </div>
    </div>
</div>

<!-- Add Organization Modal -->
<div class="modal fade" id="addOrganizationModal" tabindex="-1" aria-labelledby="addOrganizationModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="addOrganizationForm" autocomplete="off">
        <div class="modal-header">
          <h5 class="modal-title" id="addOrganizationModalLabel">Add Organization</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
                    <div class="mb-3">
                        <label for="organization_id_modal" class="form-label">Organization</label>
                        <select class="form-select" id="organization_id_modal" name="organization_id" style="width:100%">
                            <option value="">Select organization</option>
                            @foreach(\App\Models\Organization::orderBy('name')->get() as $org)
                                <option value="{{ $org->id }}">{{ $org->name }}</option>
                            @endforeach
                        </select>
                        <div id="orgModalFeedback" class="text-danger small mt-2"></div>
                    </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary" id="saveOrganizationBtn">Save</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@include('partials.select2')
@push('scripts')
<script>
$(document).ready(function() {
    $('#organization_id_modal').select2({
        dropdownParent: $('#addOrganizationModal'),
        placeholder: 'Select organization',
        allowClear: true,
        width: '100%'
    });
    $('#saveOrganizationBtn').on('click', function(e) {
        e.preventDefault();
        $('#orgModalFeedback').text('');
        var orgId = $('#organization_id_modal').val();
        var orgName = $('#organization_id_modal option:selected').text();
        if (!orgId) {
            $('#orgModalFeedback').text('Please select an organization.');
            return;
        }
        $.ajax({
            url: '/people/{{ $person->id }}/add-organization',
            method: 'POST',
            contentType: 'application/json',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            data: JSON.stringify({ organization_id: orgId, organization_name: orgName }),
            success: function(data) {
                if (data.success) {
                    location.reload();
                } else {
                    $('#orgModalFeedback').text(data.message || 'Error saving organization.');
                }
            },
            error: function() {
                $('#orgModalFeedback').text('Error saving organization.');
            }
        });
    });

    //Swal Alert for delete confirmation
    var buttons = document.querySelectorAll('.delete-person-btn');
    var name = 'data-person-name';
    attachDeleteHandlers(buttons, name);
});
</script>
@endpush
