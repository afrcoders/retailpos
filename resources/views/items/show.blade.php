@extends('layouts.app')

@section('title', $item->name)

@section('content')
<div class="fade-in">
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('items.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
        <h1 class="mb-0 ms-3"><i class="bi bi-box-seam"></i> {{ $item->name }}</h1>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between">
                    <span>Item Details</span>
                    <a href="{{ route('items.edit', $item) }}" class="btn btn-sm btn-warning">
                        <i class="bi bi-pencil"></i> Edit
                    </a>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted">SKU/Barcode</label>
                            <p><code>{{ $item->sku }}</code></p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted">Status</label>
                            <p>
                                @if ($item->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted">Category</label>
                            <p>{{ $item->category->name }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted">Supplier</label>
                            <p>{{ $item->supplier->name }}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted">Unit Price</label>
                            <p><strong>₦{{ number_format($item->retail_price, 2) }}</strong></p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted">Reorder Level</label>
                            <p>{{ $item->reorder_level }} units</p>
                        </div>
                    </div>

                    @if ($item->description)
                        <div class="mb-3">
                            <label class="form-label text-muted">Description</label>
                            <p>{{ $item->description }}</p>
                        </div>
                    @endif
                </div>
            </div>

            @if ($item->stock)
                <div class="card">
                    <div class="card-header">Stock Information</div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label text-muted">Quantity On Hand</label>
                                <p><strong>{{ $item->stock->quantity_on_hand }}</strong> units</p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted">Status</label>
                                <p>
                                    @if ($item->stock->quantity_on_hand <= $item->reorder_level)
                                        <span class="badge bg-danger">Low Stock</span>
                                    @else
                                        <span class="badge bg-success">In Stock</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
