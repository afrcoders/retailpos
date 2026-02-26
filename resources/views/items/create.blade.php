@extends('layouts.app')

@section('title', 'Add New Item')

@section('content')
<div class="fade-in">
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('items.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
        <h1 class="mb-0 ms-3"><i class="bi bi-plus-circle"></i> Add New Item</h1>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">Item Information</div>
                <div class="card-body">
                    <form action="{{ route('items.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Item Name *</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                   placeholder="Enter item name" value="{{ old('name') }}" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">SKU/Barcode</label>
                            <input type="text" name="sku" class="form-control @error('sku') is-invalid @enderror"
                                   placeholder="e.g., BAR-001-001" value="{{ old('sku') }}">
                            @error('sku') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Category *</label>
                                <select name="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                                    <option value="">Select category</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Supplier</label>
                                <select name="supplier_id" class="form-select @error('supplier_id') is-invalid @enderror">
                                    <option value="">Select supplier</option>
                                    @foreach ($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                            {{ $supplier->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('supplier_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Cost Price (₦) *</label>
                                <input type="number" name="cost_price" class="form-control @error('cost_price') is-invalid @enderror"
                                       placeholder="0.00" step="0.01" value="{{ old('cost_price') }}" required>
                                @error('cost_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Unit Price (₦) *</label>
                                <input type="number" name="unit_price" class="form-control @error('unit_price') is-invalid @enderror"
                                       placeholder="0.00" step="0.01" value="{{ old('unit_price') }}" required>
                                @error('unit_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Reorder Level *</label>
                                <input type="number" name="reorder_level" class="form-control @error('reorder_level') is-invalid @enderror"
                                       placeholder="Minimum stock" value="{{ old('reorder_level', 1) }}" required>
                                @error('reorder_level') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Initial Stock Level</label>
                                <input type="number" name="stock_level" class="form-control @error('stock_level') is-invalid @enderror"
                                       placeholder="0" step="1" min="0" value="{{ old('stock_level', 0) }}">
                                @error('stock_level') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Enter item description">{{ old('description') }}</textarea>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" name="is_active" value="1" class="form-check-input"
                                       id="isActive" {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="isActive">
                                    Active
                                </label>
                            </div>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="{{ route('items.index') }}" class="btn btn-light">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Create Item
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
