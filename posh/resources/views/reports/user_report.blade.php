@extends('layouts.app')

@section('content')
<div class="container-fluid p-4">
    <h2>User Report</h2>
    <form method="GET" action="{{ route('reports.user_wise') }}" class="row g-3 align-items-end mb-4">
        <div class="col-md-4">
            <label for="user_id" class="form-label">User</label>
            <select id="user_id" name="user_id" class="form-select select2">
                <option value="">All Users</option>
                @foreach(App\Models\User::orderBy('name')->where('crm_role_type', '!=', '0')->get() as $user)
                    <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label for="from_date" class="form-label">From Date</label>
            <input type="date" id="from_date" name="from_date" class="form-control" value="{{ request('from_date') }}">
        </div>
        <div class="col-md-3">
            <label for="to_date" class="form-label">To Date</label>
            <input type="date" id="to_date" name="to_date" class="form-control" value="{{ request('to_date') }}">
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-dark w-100">Search</button>
        </div>
    </form>
    <div class="table-responsive mt-4">
    <table class="table table-bordered align-middle">
        <thead>
            <tr>
                <th>User Name</th>
                <th>Email</th>
                <th>Number of Leads</th>
                <th>Converted Leads</th>
                <th>Number of Deals</th>
            </tr>
        </thead>
        <tbody>
            @foreach($report as $row)
                <tr>
                    <td>{{ $row['user']->name }}</td>
                    <td>{{ $row['user']->email }}</td>
                    <td>
                        <a href="#" class="show-leads" data-user-id="{{ $row['user']->id }}">{{ $row['leads_count'] }}</a>
                    </td>
                    <td>
                        <a href="#" class="show-converted-leads" data-user-id="{{ $row['user']->id }}">{{ $row['converted_leads_count'] }}</a>
                    </td>
                    <td>
                        <a href="#" class="show-deals" data-user-id="{{ $row['user']->id }}">{{ $row['deals_count'] }}</a>
                    </td>
                    <!-- Deals Modal -->
                    <div class="modal fade" id="dealsModal" tabindex="-1" aria-labelledby="dealsModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-xl">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="dealsModalLabel">Deals List</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body" id="dealsModalBody">
                                    <!-- Deals will be loaded here -->
                                </div>
                            </div>
                        </div>
                    </div>
                </tr>
            @endforeach
        </tbody>
    </table>
    </div>
        </div>

        <!-- Modal -->
        <div class="modal fade" id="leadsModal" tabindex="-1" aria-labelledby="leadsModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="leadsModalLabel">Leads List</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" id="leadsModalBody">
                        <!-- Leads will be loaded here -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Converted Leads Modal -->
        <div class="modal fade" id="convertedLeadsModal" tabindex="-1" aria-labelledby="convertedLeadsModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="convertedLeadsModalLabel">Converted Leads List</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" id="convertedLeadsModalBody">
                        <!-- Converted leads will be loaded here -->
                    </div>
                </div>
            </div>
        </div>


        @section('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Set min date for to_date based on from_date
                var fromDateInput = document.getElementById('from_date');
                var toDateInput = document.getElementById('to_date');
                if (fromDateInput && toDateInput) {
                    fromDateInput.addEventListener('change', function() {
                        toDateInput.min = fromDateInput.value;
                        if (toDateInput.value && toDateInput.value < fromDateInput.value) {
                            toDateInput.value = fromDateInput.value;
                        }
                    });
                    // On page load, set min if from_date already selected
                    if (fromDateInput.value) {
                        toDateInput.min = fromDateInput.value;
                        if (toDateInput.value && toDateInput.value < fromDateInput.value) {
                            toDateInput.value = fromDateInput.value;
                        }
                    }
                }

                document.querySelectorAll('.show-leads').forEach(function(el) {
                    el.addEventListener('click', function(e) {
                        e.preventDefault();
                        var userId = this.getAttribute('data-user-id');
                        var fromDate = document.getElementById('from_date').value;
                        var toDate = document.getElementById('to_date').value;
                        var params = '?from_date=' + encodeURIComponent(fromDate) + '&to_date=' + encodeURIComponent(toDate);
                        fetch('/reports/user-leads/' + userId + params)
                            .then(response => response.text())
                            .then(html => {
                                document.getElementById('leadsModalBody').innerHTML = html;
                                $('#leadsModal').modal('show');
                                setTimeout(function() {
                                    document.querySelectorAll('.show-lead-details').forEach(function(leadEl) {
                                        leadEl.addEventListener('click', function(ev) {
                                            ev.preventDefault();
                                            var leadId = this.getAttribute('data-lead-id');
                                            fetch('/reports/lead-details/' + leadId)
                                                .then(response => response.text())
                                                .then(html => {
                                                    document.getElementById('leadDetailsModalBody').innerHTML = html;
                                                    $('#leadDetailsModal').modal('show');
                                                });
                                        });
                                    });
                                }, 500);
                            });
                    });
                });
                document.querySelectorAll('.show-deals').forEach(function(el) {
                    el.addEventListener('click', function(e) {
                        e.preventDefault();
                        var userId = this.getAttribute('data-user-id');
                        var fromDate = document.getElementById('from_date').value;
                        var toDate = document.getElementById('to_date').value;
                        var params = '?from_date=' + encodeURIComponent(fromDate) + '&to_date=' + encodeURIComponent(toDate);
                        fetch('/reports/user-deals/' + userId + params)
                            .then(response => response.text())
                            .then(html => {
                                document.getElementById('dealsModalBody').innerHTML = html;
                                $('#dealsModal').modal('show');
                            });
                    });
                });
                document.querySelectorAll('.show-converted-leads').forEach(function(el) {
                    el.addEventListener('click', function(e) {
                        e.preventDefault();
                        var userId = this.getAttribute('data-user-id');
                        var fromDate = document.getElementById('from_date').value;
                        var toDate = document.getElementById('to_date').value;
                        var params = '?from_date=' + encodeURIComponent(fromDate) + '&to_date=' + encodeURIComponent(toDate);
                        fetch('/reports/converted-leads/' + userId + params)
                            .then(response => response.text())
                            .then(html => {
                                document.getElementById('convertedLeadsModalBody').innerHTML = html;
                                $('#convertedLeadsModal').modal('show');
                            });
                    });
                });
            });
        </script>
        @endsection
</div>
@endsection
