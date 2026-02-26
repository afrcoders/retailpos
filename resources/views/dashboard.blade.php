@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="fade-in">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0"><i class="bi bi-speedometer2"></i> Dashboard</h1>
        <small class="text-muted">{{ date('l, d F Y') }}</small>
    </div>

    <!-- Role-based Dashboard -->
    @if(Auth::user()->role->name === 'sales_staff')
        <!-- Sales Staff Dashboard -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon"><i class="bi bi-cash"></i></div>
                    <div class="stat-value">₦{{ number_format($todaySales, 2) }}</div>
                    <div class="stat-label">Today's Sales</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon"><i class="bi bi-receipt"></i></div>
                    <div class="stat-value">{{ $totalTransactions }}</div>
                    <div class="stat-label">Transactions</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon"><i class="bi bi-arrow-repeat"></i></div>
                    <div class="stat-value">{{ $totalReturns }}</div>
                    <div class="stat-label">Returns</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon"><i class="bi bi-percent"></i></div>
                    <div class="stat-value">₦{{ number_format($totalDiscount, 2) }}</div>
                    <div class="stat-label">Discounts Given</div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <i class="bi bi-graph-up"></i> Sales Trend (Today)
                    </div>
                    <div class="card-body">
                        <canvas id="salesChart" height="80"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <i class="bi bi-info-circle"></i> Quick Actions
                    </div>
                    <div class="card-body">
                        <a href="/sales" class="btn btn-primary w-100 mb-2"><i class="bi bi-cash-register"></i> New Sale</a>
                        <a href="/sales-history" class="btn btn-outline-primary w-100 mb-2"><i class="bi bi-receipt"></i> View History</a>
                        <button onclick="window.print()" class="btn btn-outline-primary w-100"><i class="bi bi-printer"></i> Print</button>
                    </div>
                </div>
            </div>
        </div>

    @elseif(Auth::user()->role->name === 'subadmin' || Auth::user()->role->name === 'admin')
        <!-- Admin & Subadmin Dashboard -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon"><i class="bi bi-box"></i></div>
                    <div class="stat-value">{{ $totalItems }}</div>
                    <div class="stat-label">Total Products</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon"><i class="bi bi-exclamation-circle"></i></div>
                    <div class="stat-value text-warning">{{ $lowStockItems }}</div>
                    <div class="stat-label">Low Stock Items</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon"><i class="bi bi-cash-register"></i></div>
                    <div class="stat-value">₦{{ number_format($todaySales, 2) }}</div>
                    <div class="stat-label">Today's Sales</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon"><i class="bi bi-people"></i></div>
                    <div class="stat-value">{{ $totalUsers }}</div>
                    <div class="stat-label">Active Users</div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <i class="bi bi-graph-up"></i> Sales Overview (Last 7 Days)
                    </div>
                    <div class="card-body">
                        <canvas id="salesChart" height="80"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <i class="bi bi-exclamation-triangle"></i> Low Stock Alert
                    </div>
                    <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                        @forelse($lowStockItemsList as $item)
                            <div class="mb-3 pb-3 border-bottom">
                                <div class="d-flex justify-content-between">
                                    <strong>{{ $item->name }}</strong>
                                    <span class="badge badge-warning">{{ $item->stock->quantity_on_hand }} left</span>
                                </div>
                                <small class="text-muted">Reorder Level: {{ $item->reorder_level }}</small>
                            </div>
                        @empty
                            <div class="alert alert-success mb-0">
                                <i class="bi bi-check-circle"></i> All items are well-stocked!
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <i class="bi bi-arrow-left-right"></i> Recent Stock Movements
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Type</th>
                                        <th>Quantity</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentStockMovements as $movement)
                                        <tr>
                                            <td>{{ $movement->item->name }}</td>
                                            <td>
                                                @if($movement->type === 'in')
                                                    <span class="badge badge-success">In</span>
                                                @else
                                                    <span class="badge badge-danger">Out</span>
                                                @endif
                                            </td>
                                            <td>{{ $movement->quantity }}</td>
                                            <td><small>{{ $movement->created_at->format('M d, Y') }}</small></td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-3">No stock movements yet</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <i class="bi bi-receipt"></i> Top Selling Items (Today)
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Qty Sold</th>
                                        <th>Revenue</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($topSellingItems as $item)
                                        <tr>
                                            <td>{{ $item->name }}</td>
                                            <td>{{ $item->total_qty }}</td>
                                            <td>₦{{ number_format($item->total_revenue, 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center text-muted py-3">No sales yet today</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
    const ctx = document.getElementById('salesChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json($chartLabels ?? []),
                datasets: [{
                    label: 'Sales (₦)',
                    data: @json($chartData ?? []),
                    borderColor: '#1a472a',
                    backgroundColor: 'rgba(26, 71, 42, 0.1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '₦' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    }
</script>
@endsection
