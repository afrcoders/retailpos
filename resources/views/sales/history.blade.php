@extends('layouts.app')

@section('title', 'Sales History')

@section('extra_css')
<style>
    @media print {
        body * {
            visibility: hidden;
        }
        .print-receipt, .print-receipt * {
            visibility: visible;
        }
        .print-receipt {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            display: block !important;
        }
    }
    .print-receipt {
        display: none;
        width: 400px;
        margin: 0 auto;
        padding: 20px;
        background: white;
        font-family: monospace;
    }
    .receipt-header {
        text-align: center;
        margin-bottom: 20px;
        border-bottom: 2px solid #000;
        padding-bottom: 10px;
    }
    .receipt-items {
        margin: 20px 0;
    }
    .receipt-item {
        display: flex;
        justify-content: space-between;
        margin-bottom: 10px;
    }
    .receipt-total {
        border-top: 2px solid #000;
        margin-top: 10px;
        padding-top: 10px;
        text-align: right;
        font-weight: bold;
        font-size: 1.2em;
    }
</style>
@endsection

@section('content')
<div class="fade-in">
    <h1 class="mb-4"><i class="bi bi-receipt"></i> Sales History</h1>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle"></i>
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Recent Sales</span>
            <div class="input-group" style="max-width: 300px;">
                <span class="input-group-text"><i class="bi bi-calendar"></i></span>
                <input type="date" class="form-control">
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Sale #</th>
                        <th>Date</th>
                        <th>Customer</th>
                        <th>Items</th>
                        <th>Subtotal</th>
                        <th>Tax</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($sales as $sale)
                        <tr>
                            <td><strong>#{{ $sale->sale_number }}</strong></td>
                            <td>{{ $sale->created_at->format('M d, Y H:i') }}</td>
                            <td>{{ $sale->customer_name ?? 'Walk-in' }}</td>
                            <td>{{ $sale->items->count() }} items</td>
                            <td>₦{{ number_format($sale->subtotal, 2) }}</td>
                            <td>₦{{ number_format($sale->vat_amount, 2) }}</td>
                            <td><strong>₦{{ number_format($sale->total, 2) }}</strong></td>
                            <td>
                                <span class="badge bg-success">{{ ucfirst($sale->status) }}</span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-info" data-bs-toggle="modal"
                                            data-bs-target="#saleDetailModal{{ $sale->id }}" title="View Details">
                                        <i class="bi bi-eye"></i>
                                    </button>

                                    @if(Auth::user()->role->name === 'admin')
                                        <button class="btn btn-outline-danger" onclick="deleteSale({{ $sale->id }}, '{{ $sale->sale_number }}')" title="Delete Sale">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">No sales found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Sale Detail Modals -->
    @foreach($sales as $sale)
    <div class="modal fade" id="saleDetailModal{{ $sale->id }}" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Sale Details - {{ $sale->sale_number }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Date:</strong> {{ $sale->created_at->format('M d, Y H:i') }}</p>
                            <p><strong>Customer:</strong> {{ $sale->customer_name ?? 'Walk-in Customer' }}</p>
                            <p><strong>Payment Method:</strong> {{ ucfirst($sale->payment_method) }}</p>
                            <p><strong>Status:</strong> <span class="badge bg-success">{{ ucfirst($sale->status) }}</span></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Subtotal:</strong> ₦{{ number_format($sale->subtotal, 2) }}</p>
                            <p><strong>Tax:</strong> ₦{{ number_format($sale->vat_amount, 2) }}</p>
                            <p><strong>Total:</strong> <strong>₦{{ number_format($sale->total, 2) }}</strong></p>
                            <p><strong>Served by:</strong> {{ $sale->user->name ?? 'N/A' }}</p>
                        </div>
                    </div>

                    <h6 class="mt-3">Items Sold:</h6>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Qty</th>
                                    <th>Unit Price</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($sale->items as $saleItem)
                                <tr>
                                    <td>{{ $saleItem->item->name ?? 'N/A' }}</td>
                                    <td>{{ $saleItem->quantity }}</td>
                                    <td>₦{{ number_format($saleItem->unit_price, 2) }}</td>
                                    <td>₦{{ number_format($saleItem->line_total, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="printSaleReceipt({{ $sale->id }})"
                            data-sale-number="{{ $sale->sale_number }}"
                            data-sale-date="{{ $sale->created_at->format('d/m/Y, H:i:s') }}"
                            data-subtotal="{{ $sale->subtotal }}"
                            data-tax="{{ $sale->vat_amount }}"
                            data-total="{{ $sale->total }}"
                            data-items="{{ json_encode($sale->items->map(function($item) { return ['name' => $item->item->name ?? 'N/A', 'quantity' => $item->quantity, 'price' => $item->unit_price, 'total' => $item->line_total]; })) }}">
                        Print Receipt
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endforeach

    <!-- Print Receipt Template (Hidden) -->
    <div id="printReceiptContent" class="print-receipt">
        <div class="receipt-header">
            <h2>Dakoss Global Nigeria Limited Sales</h2>
            <p>Receipt #: <span id="printReceiptNumber"></span></p>
            <p><small id="printReceiptDate"></small></p>
        </div>

        <div class="receipt-items" id="printReceiptItems"></div>

        <div class="receipt-total">
            <div>Subtotal: ₦<span id="printReceiptSubtotal">0.00</span></div>
            <div>Tax (7.5%): ₦<span id="printReceiptTax">0.00</span></div>
            <div style="border-top: 1px solid #000; margin-top: 5px; padding-top: 5px;">
                TOTAL: ₦<span id="printReceiptTotal">0.00</span>
            </div>
        </div>

        <div style="text-align: center; margin-top: 20px; border-top: 2px solid #000; padding-top: 10px;">
            <p>Thank you for your purchase!</p>
            <p><small>Reprinted: <span id="printTime"></span></small></p>
        </div>
    </div>
</div>

<script>
function printSaleReceipt(saleId) {
    const button = event.target;
    const receiptNumber = button.getAttribute('data-sale-number');
    const receiptDate = button.getAttribute('data-sale-date');
    const subtotal = parseFloat(button.getAttribute('data-subtotal'));
    const tax = parseFloat(button.getAttribute('data-tax'));
    const total = parseFloat(button.getAttribute('data-total'));
    const items = JSON.parse(button.getAttribute('data-items'));

    // Set receipt data
    document.getElementById('printReceiptNumber').textContent = receiptNumber;
    document.getElementById('printReceiptDate').textContent = receiptDate;
    document.getElementById('printReceiptSubtotal').textContent = subtotal.toFixed(2);
    document.getElementById('printReceiptTax').textContent = tax.toFixed(2);
    document.getElementById('printReceiptTotal').textContent = total.toFixed(2);
    document.getElementById('printTime').textContent = new Date().toLocaleString();

    // Generate items HTML
    const itemsHtml = items.map(item => `
        <div class="receipt-item">
            <span>${item.name} x${item.quantity}</span>
            <span>₦${parseFloat(item.total).toLocaleString()}</span>
        </div>
    `).join('');

    document.getElementById('printReceiptItems').innerHTML = itemsHtml;

    // Print
    window.print();
}

function deleteSale(saleId, saleNumber) {
    if (!confirm(`Are you sure you want to delete sale #${saleNumber}? This action cannot be undone and will permanently remove all sale data including items and payment records.`)) {
        return;
    }

    fetch(`/sales/${saleId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(`Sale #${saleNumber} deleted successfully!`);
            location.reload();
        } else {
            alert('Error: ' + (data.message || 'Failed to delete sale'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while deleting the sale');
    });
}
</script>
@endsection
