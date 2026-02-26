@extends('layouts.app')

@section('title', 'Sales Report')

@section('content')
<div class="fade-in">
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('reports.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
        <h1 class="mb-0 ms-3"><i class="bi bi-graph-up"></i> Sales Report</h1>
    </div>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="bi bi-cash"></i></div>
                <div class="stat-value">₦{{ number_format(\App\Models\Sale::sum('total'), 2) }}</div>
                <div class="stat-label">Total Sales</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="bi bi-receipt"></i></div>
                <div class="stat-value">{{ \App\Models\Sale::count() }}</div>
                <div class="stat-label">Total Transactions</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="bi bi-percent"></i></div>
                <div class="stat-value">₦{{ number_format(\App\Models\Sale::sum('vat_amount'), 2) }}</div>
                <div class="stat-label">Total Tax</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="bi bi-tag"></i></div>
                <div class="stat-value">₦{{ number_format(\App\Models\Sale::sum('discount_amount'), 2) }}</div>
                <div class="stat-label">Total Discounts</div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">Sales by Day</div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Transactions</th>
                        <th>Total</th>
                        <th>Tax</th>
                        <th>Discount</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse (\App\Models\Sale::selectRaw('DATE(created_at) as date, COUNT(*) as transactions, SUM(total) as total, SUM(vat_amount) as tax, SUM(discount_amount) as discount')->groupBy('date')->latest('date')->get() as $sale)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($sale->date)->format('M d, Y') }}</td>
                            <td>{{ $sale->transactions }}</td>
                            <td>₦{{ number_format($sale->total, 2) }}</td>
                            <td>₦{{ number_format($sale->tax, 2) }}</td>
                            <td>₦{{ number_format($sale->discount, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">No sales data</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
