
@extends('layouts.app')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{asset('css/leads.css') }}">
<div class="container-fluid p-4">
    <div class="card mt-0">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-3">
            <h4 class="mb-0">Product Categories</h4>
            <div class="d-flex gap-2 flex-wrap">
                <!-- <button id="toggleFiltersButton" class="btn btn-outline-secondary btn-sm">Show Filters</button> -->
                <a href="{{ route('product_categories.create') }}" class="btn btn-custom btn-sm d-flex align-items-center gap-2 @if(!auth()->user()->hasCrmPermission('create_crm_product_category_guard')) disabled @endif">
                    <i class="bi bi-plus"></i> Add Category
                </a>
            </div>
        </div>
        <div class="card-body">
            <!-- @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif -->
            <!-- <form method="GET" action="{{ route('product_categories.index') }}" class="mb-3" id="filtersForm" style="display: none;">
                <div class="row g-2 align-items-end">
                    <div class="col-xl-3 col-md-4">
                        <label for="category_name" class="form-label mb-1">Category Name</label>
                        <input type="text" name="category_name" id="category_name" class="form-control" value="{{ request('category_name') }}" placeholder="Search by Category Name">
                    </div>
                    <div class="col-xl-3 col-md-4">
                        <label for="status" class="form-label mb-1">Status</label>
                        <select name="status" id="status" class="form-select">
                            <option value="">All</option>
                            <option value="1" @if(request('status')==='1') selected @endif>Active</option>
                            <option value="0" @if(request('status')==='0') selected @endif>Inactive</option>
                        </select>
                    </div>
                </div>
            </form> -->
            <div class="table-responsive" style="overflow:auto;">
                <table class="table table-bordered table-hover align-middle mb-0" style="overflow:visible;">
                    <thead class="custom-display d-none d-md-table-row-group">
                        <tr>
                            <th></th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $category)
                            <tr>
                                <td style="overflow:visible; position:relative;" data-label="Actions">
                                    <div class="dropdown">
                                        <button class="btn btn-link p-0 text-dark" type="button" id="productCategoryActionsDropdown{{ $category->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="bi bi-three-dots-vertical fs-5"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-start shadow rounded-3 py-2 mt-2" aria-labelledby="productCategoryActionsDropdown{{ $category->id }}">
                                            <li>
                                                <a class="dropdown-item px-4 py-2 fs-6 @if(!auth()->user()->hasCrmPermission('edit_crm_product_category_guard')) disabled @endif" href="{{ route('product_categories.edit', $category->id) }}">Edit</a>
                                            </li>
                                            <li>
                                                <form action="{{ route('product_categories.destroy', $category->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this category?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item px-4 py-2 fs-6 text-danger @if(!auth()->user()->hasCrmPermission('delete_crm_product_category_guard')) disabled @endif">Delete</button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                                <td data-label="Name">
                                    <a class="text-decoration-none @if(!auth()->user()->hasCrmPermission('edit_crm_product_category_guard')) disabled @endif" href='{{ route('product_categories.edit', $category->id) }}'>{{ $category->category_name }}</a>
                                </td>
                                <td data-label="Description">{{ $category->description }}</td>
                                <td data-label="Status">{{ $category->status ? 'Active' : 'Inactive' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="10" class="text-center">No categories found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="d-flex flex-wrap justify-content-between align-items-center mt-3 pagination-info">
                <div class="small text-muted">Showing {{ $categories->firstItem() ?? 0 }} to {{ $categories->lastItem() ?? 0 }} of {{ $categories->total() }} categories</div>
                <div class="pagination-custom text-center my-3">
                    {{ $categories->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

<script>
document.addEventListener('DOMContentLoaded', function() {
    var form = document.getElementById('filtersForm');
    if (!form) return;

    function debounce(fn, wait) {
        var timeout;
        return function() {
            var context = this, args = arguments;
            clearTimeout(timeout);
            timeout = setTimeout(function() { fn.apply(context, args); }, wait);
        };
    }

    var submitDebounced = debounce(function() { form.submit(); }, 450);

    var fields = ['category_name', 'status'];
    fields.forEach(function(id) {
        var el = document.getElementById(id);
        if (!el) return;
        var ev = (el.tagName.toLowerCase() === 'input') ? 'input' : 'change';
        el.addEventListener(ev, submitDebounced);
    });
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggleFiltersButton = document.getElementById('toggleFiltersButton');
    const filtersForm = document.getElementById('filtersForm');

    if (localStorage.getItem('productCategoryFiltersVisible') === 'true') {
        filtersForm.style.display = 'block';
        toggleFiltersButton.textContent = 'Hide Filters';
    } else {
        filtersForm.style.display = 'none';
        toggleFiltersButton.textContent = 'Show Filters';
    }

    toggleFiltersButton.addEventListener('click', function() {
        if (filtersForm.style.display === 'none') {
            filtersForm.style.display = 'block';
            toggleFiltersButton.textContent = 'Hide Filters';
            localStorage.setItem('productCategoryFiltersVisible', 'true');
        } else {
            filtersForm.style.display = 'none';
            toggleFiltersButton.textContent = 'Show Filters';
            localStorage.setItem('productCategoryFiltersVisible', 'false');
        }
    });
});
</script>
