@extends('layouts.app')

@section('title', 'Inventory Report')

@section('content')
<div class="fade-in">
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('reports.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
        <h1 class="mb-0 ms-3"><i class="bi bi-boxes"></i> Inventory Report</h1>
    </div>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="bi bi-boxes"></i></div>
                <div class="stat-value">{{ \App\Models\Stock::sum('quantity_on_hand') ?? 0 }}</div>
                <div class="stat-label">Total Units</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="bi bi-exclamation-triangle"></i></div>
                <div class="stat-value">{{ \App\Models\Item::whereHas('stock', function($q) { $q->whereRaw('quantity_on_hand <= items.reorder_level'); })->count() }}</div>
                <div class="stat-label">Low Stock Items</div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">Stock Levels</div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Item</th>
                        <th>SKU</th>
                        <th>Category</th>
                        <th>Quantity</th>
                        <th>Reorder Level</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse (\App\Models\Stock::with('item.category')->get() as $stock)
                        <tr>
                            <td><strong>{{ $stock->item->name }}</strong></td>
                            <td><code>{{ $stock->item->sku }}</code></td>
                            <td>{{ $stock->item->category->name }}</td>
                            <td>{{ $stock->quantity_on_hand }}</td>
                            <td>{{ $stock->item->reorder_level }}</td>
                            <td>
                                @if ($stock->quantity_on_hand <= $stock->item->reorder_level)
                                    <span class="badge bg-danger">Low Stock</span>
                                @elseif ($stock->quantity_on_hand <= $stock->item->reorder_level * 1.5)
                                    <span class="badge bg-warning">Warning</span>
                                @else
                                    <span class="badge bg-success">Good</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No inventory data</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
