@extends('layouts.app')

@section('content')

<div class="container-fluid p-4">
    <h1 class="mb-4">Product Category Wise User Report</h1>

    <form method="GET" class="mb-4">
        <div class="row g-3 align-items-end">
            <div class="col-md-3">
                <label for="category" class="form-label fw-bold">Category:</label>
                <select id="category" name="category" class="form-select">
                    <option value="">All</option>
                    @foreach(App\Models\ProductCategory::all() as $category)
                        <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                            {{ $category->category_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="user_id" class="form-label fw-bold">User:</label>
                <select id="user_id" name="user_id" class="form-select">
                    <option value="">All</option>
                    @foreach(App\Models\User::all() as $user)
                        <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                            {{ $user->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="start_date" class="form-label fw-bold">Start Date:</label>
                <input type="date" id="start_date" name="start_date" class="form-control" value="{{ request('start_date') }}">
            </div>
            <div class="col-md-3">
                <label for="end_date" class="form-label fw-bold">End Date:</label>
                <input type="date" id="end_date" name="end_date" class="form-control" value="{{ request('end_date') }}">
            </div>
            <div class="col-md-6 mt-3 d-flex gap-2">
                <button type="submit" class="btn btn-custom" style="padding: 6px 20px !important;">Search</button>
                <a href="{{ url()->current() }}" class="btn btn-secondary">Reset</a>
            </div>
        </div>
    </form>

    <!-- Modal -->
    <div class="modal fade" id="dataModal" tabindex="-1" aria-labelledby="dataModalLabel" aria-hidden="true">
        <div class="modal-dialog" style="max-width: 1500px;">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="dataModalLabel">Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Content will be loaded dynamically -->
                </div>
            </div>
        </div>
    </div>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Product Category</th>
                <th>User</th>
                <th>Lead Count</th>
                <th>Deal Count</th>
                <th>Deal Won</th>
                <th>Deal Lost</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($results as $category => $users)
                @php $rowspan = count($users); @endphp
                @foreach ($users as $index => $user)
                    <tr>
                        @if ($index === 0)
                            <td class="d-md-table-cell align-middle" rowspan="{{ $rowspan }}"><span class="fw-bold">{{ $category }}</span></td>
                        @endif
                        <td>{{ $user['user'] }}</td>
                        <td>
                            @if($user['lead_count'] == 0)
                                {{ $user['lead_count'] }}
                            @else
                            <a href="#" class="count-link" data-url="{{ $url = '/leads/list/' . $user['user_id'] . '?category=' . $user['category_id'] }}">
                                 {{ $user['lead_count'] }}
                            </a>
                            @endif
                        </td>
                        <td>
                            @if ($user['deal_count'] == 0)
                                {{ $user['deal_count'] }}
                            @else
                             <a href="#" class="count-link" data-url="{{ $url = '/deals/list/' . $user['user_id'] . '?category=' . $user['category_id'] }}">
                                {{ $user['deal_count'] }}
                            </a>
                            @endif
                           
                        </td>
                        <td>
                            @if ($user['deal_won'] == 0)
                                {{ $user['deal_won'] }}
                            @else
                             <a href="#" class="count-link" data-url="{{ $url = '/deals/list/' . $user['user_id'] . '?category=' . $user['category_id'] . '&stage=Closed Won' }}">
                                {{ $user['deal_won'] }}
                            </a>
                            @endif
                        </td>
                        <td>
                            @if ($user['deal_lost'] == 0)
                                {{ $user['deal_lost'] }}
                            @else
                             <a href="#" class="count-link" data-url="{{ $url = '/deals/list/' . $user['user_id'] . '?category=' . $user['category_id'] . '&stage=Closed Lost' }}">
                                {{ $user['deal_lost'] }}
                            </a>
                            @endif
                        </td>
                    </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.count-link').forEach(function (link) {
            link.addEventListener('click', function (event) {
                event.preventDefault();
                const url = this.getAttribute('data-url');
                console.log(url);
                fetch(url)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Failed to load data');
                        }
                        return response.text();
                    })
                    .then(html => {
                        const modal = document.getElementById('dataModal');
                        modal.querySelector('.modal-body').innerHTML = html;
                        const bootstrapModal = new bootstrap.Modal(modal);
                        bootstrapModal.show();
                    })
                    .catch(error => {
                        const modal = document.getElementById('dataModal');
                        modal.querySelector('.modal-body').innerHTML = `<p class='text-danger'>${error.message}</p>`;
                        const bootstrapModal = new bootstrap.Modal(modal);
                        bootstrapModal.show();
                    });
            });
        });
    });
</script>
@endsection