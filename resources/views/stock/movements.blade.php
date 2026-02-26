@extends('layouts.app')

@section('title', 'Stock Movements')

@section('content')
<div class="fade-in">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0"><i class="bi bi-arrow-left-right"></i> Stock Movements</h1>
        <button class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#stockAdjustmentModal">
            <i class="bi bi-plus-circle"></i> New Adjustment
        </button>
    </div>

    <div class="row mb-4">
        <div class="col-md-3">
            <input type="date" id="fromDate" class="form-control" value="{{ date('Y-m-d', strtotime('-30 days')) }}">
        </div>
        <div class="col-md-3">
            <input type="date" id="toDate" class="form-control" value="{{ date('Y-m-d') }}">
        </div>
        <div class="col-md-3">
            <select id="typeFilter" class="form-select">
                <option value="">All Types</option>
                <option value="in">Stock In</option>
                <option value="out">Stock Out</option>
                <option value="adjustment">Adjustment</option>
                <option value="transfer">Transfer</option>
            </select>
        </div>
        <div class="col-md-3">
            <button class="btn btn-primary w-100" onclick="applyFilters()">
                <i class="bi bi-funnel"></i> Filter
            </button>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <i class="bi bi-table"></i> All Stock Movements
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Product</th>
                            <th>Type</th>
                            <th>Quantity</th>
                            <th>Previous Stock</th>
                            <th>New Stock</th>
                            <th>Reference</th>
                            <th>Notes</th>
                            <th>User</th>
                        </tr>
                    </thead>
                    <tbody id="movementsTable">
                        @forelse($movements as $movement)
                            <tr>
                                <td><small>{{ $movement->created_at->format('M d, Y H:i') }}</small></td>
                                <td>{{ $movement->item->name }}</td>
                                <td>
                                    @if($movement->type === 'in')
                                        <span class="badge badge-success"><i class="bi bi-arrow-down"></i> Stock In</span>
                                    @elseif($movement->type === 'out')
                                        <span class="badge badge-danger"><i class="bi bi-arrow-up"></i> Stock Out</span>
                                    @elseif($movement->type === 'adjustment')
                                        <span class="badge badge-warning"><i class="bi bi-arrow-left-right"></i> Adjustment</span>
                                    @elseif($movement->type === 'transfer')
                                        <span class="badge badge-info"><i class="bi bi-shuffle"></i> Transfer</span>
                                    @endif
                                </td>
                                <td>
                                    <strong>
                                        @if($movement->type === 'in' || $movement->type === 'adjustment')
                                            <span class="text-success">+{{ $movement->quantity }}</span>
                                        @else
                                            <span class="text-danger">-{{ $movement->quantity }}</span>
                                        @endif
                                    </strong>
                                </td>
                                <td>{{ $movement->previous_stock }}</td>
                                <td>{{ $movement->new_stock }}</td>
                                <td>
                                    @if($movement->reference_type)
                                        <small class="badge badge-info">{{ $movement->reference_type }}</small>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>{{ $movement->notes ?? '—' }}</td>
                                <td><small>{{ $movement->user->name ?? 'System' }}</small></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                                    <p class="mt-2">No stock movements found</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Pagination -->
    @if($movements->hasPages())
        <nav class="mt-4">
            <ul class="pagination justify-content-center">
                {{ $movements->links() }}
            </ul>
        </nav>
    @endif
</div>

<!-- Stock Adjustment Modal -->
<div class="modal fade" id="stockAdjustmentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-arrow-left-right"></i> Stock Adjustment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="adjustmentForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Product</label>
                        <select id="itemId" name="item_id" class="form-select" required>
                            <option value="">Select a product...</option>
                            @foreach($items as $item)
                                <option value="{{ $item->id }}">{{ $item->name }} (Stock: {{ $item->stock->quantity_on_hand }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Adjustment Type</label>
                        <select id="adjustmentType" class="form-select" required>
                            <option value="">Select type...</option>
                            <option value="in">Stock In (Receiving)</option>
                            <option value="out">Stock Out (Disposal)</option>
                            <option value="adjustment">Adjustment (Correction)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Quantity</label>
                        <input type="number" id="adjustmentQty" name="quantity" class="form-control" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reference (e.g., PO#, DO#)</label>
                        <input type="text" id="reference" name="reference" class="form-control" placeholder="Optional reference number">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea id="adjustmentNotes" name="notes" class="form-control" rows="3" placeholder="Add notes about this adjustment..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Adjustment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.getElementById('adjustmentForm').addEventListener('submit', async function(e) {
        e.preventDefault();

        const formData = {
            item_id: document.getElementById('itemId').value,
            type: document.getElementById('adjustmentType').value,
            quantity: document.getElementById('adjustmentQty').value,
            reference: document.getElementById('reference').value,
            notes: document.getElementById('adjustmentNotes').value
        };

        try {
            const response = await fetch('/api/stock/adjustment', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + localStorage.getItem('api_token')
                },
                body: JSON.stringify(formData)
            });

            const data = await response.json();

            if (data.success) {
                alert('Stock adjustment recorded successfully!');
                bootstrap.Modal.getInstance(document.getElementById('stockAdjustmentModal')).hide();
                location.reload();
            } else {
                alert('Error: ' + (data.message || 'Failed to save adjustment'));
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred while saving the adjustment');
        }
    });

    function applyFilters() {
        const fromDate = document.getElementById('fromDate').value;
        const toDate = document.getElementById('toDate').value;
        const type = document.getElementById('typeFilter').value;

        // Build query string and reload
        const params = new URLSearchParams();
        if (fromDate) params.append('from_date', fromDate);
        if (toDate) params.append('to_date', toDate);
        if (type) params.append('type', type);

        window.location.href = '/stock-movements?' + params.toString();
    }
</script>
@endsection
