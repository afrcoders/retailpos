@extends('layouts.app')

@section('title', 'Reports')

@section('content')
<div class="fade-in">
    <h1 class="mb-4"><i class="bi bi-bar-chart"></i> Reports</h1>

    <div class="row">
        <div class="col-md-6 mb-3">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="bi bi-graph-up" style="font-size: 3rem; color: var(--dakoss-primary);"></i>
                    <h5 class="card-title mt-3">Sales Report</h5>
                    <p class="card-text text-muted">View detailed sales analytics</p>
                    <a href="{{ route('reports.sales') }}" class="btn btn-primary">
                        <i class="bi bi-arrow-right"></i> View Report
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-3">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="bi bi-boxes" style="font-size: 3rem; color: var(--dakoss-secondary);"></i>
                    <h5 class="card-title mt-3">Inventory Report</h5>
                    <p class="card-text text-muted">Monitor stock levels</p>
                    <a href="{{ route('reports.inventory') }}" class="btn btn-primary">
                        <i class="bi bi-arrow-right"></i> View Report
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
