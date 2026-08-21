@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">
            <div class="card mt-3">
                <div class="card-header">
                    <h4>Edit Product</h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('products.update', $product->id) }}">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label for="product_name" class="form-label">Product Name<span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="product_name" name="product_name" value="{{ old('product_name', $product->product_name) }}" required>
                        </div>
                        <div class="mb-3">
                            <label for="product_code" class="form-label">Product Code<span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="product_code" name="product_code" value="{{ old('product_code', $product->product_code) }}" required>
                            @error('product_code')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="product_owner_id" class="form-label">Product Owner<span class="text-danger">*</span></label>
                            <select class="form-select" id="product_owner_id" name="product_owner_id" required>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ (old('product_owner_id', $product->product_owner_id) == $user->id) ? 'selected' : '' }}>{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="product_status" name="product_status" value="1" {{ old('product_status', $product->product_status) ? 'checked' : '' }}>
                            <label class="form-check-label" for="product_status">Active</label>
                        </div>
                        <div class="mb-3">
                            <label for="product_category_id" class="form-label">Product Category<span class="text-danger">*</span></label>
                            <select class="form-select" id="product_category_id" name="product_category_id" required>
                                <option value="">Select Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('product_category_id', $product->product_category_id) == $category->id ? 'selected' : '' }}>{{ $category->category_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="unit_price" class="form-label">Unit Price<span class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control" id="unit_price" name="unit_price" value="{{ old('unit_price', $product->unit_price) }}" required>
                        </div>
                        <div class="mb-3">
                            <label for="commission_rate" class="form-label">Commission Rate</label>
                            <input type="number" class="form-control" id="commission_rate" name="commission_rate" value="{{ old('commission_rate', $product->commission_rate) }}">
                        </div>
                        <div class="mb-3">
                            <label for="tax_rate_id" class="form-label">Tax</label>
                            <select class="form-select" id="tax_rate_id" name="tax_rate_id">
                                <option value="">Select Tax</option>
                                @foreach($taxRates as $tax)
                                    <option value="{{ $tax->id }}" {{ old('tax_rate_id', $product->tax_rate_id) == $tax->id ? 'selected' : '' }}>{{ $tax->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="tax_status" name="tax_status" value="1" {{ old('tax_status', $product->tax_status) ? 'checked' : '' }}>
                            <label class="form-check-label" for="tax_status">Taxable</label>
                        </div>
                        <div class="mb-3">
                            <label for="product_description" class="form-label">Description</label>
                            <textarea class="form-control" id="product_description" name="product_description">{{ old('product_description', $product->product_description) }}</textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Update</button>
                        <a href="{{ route('products.index') }}" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
