@extends('layouts.app')

@section('title', 'Point of Sale')

@section('extra_css')
<style>
    .pos-container {
        display: grid;
        grid-template-columns: 2fr 3fr;
        gap: 2rem;
        height: calc(100vh - 200px);
    }

    .pos-items {
        overflow-y: auto;
        background: white;
        border-radius: 12px;
        padding: 1rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }

    .pos-cart {
        display: flex;
        flex-direction: column;
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }

    .cart-items {
        flex: 1;
        overflow-y: auto;
        padding: 1.5rem;
        max-height: calc(100vh - 400px);
    }

    .cart-header {
        background: linear-gradient(135deg, #1a472a 0%, #0f3a1f 100%);
        color: white;
        padding: 1rem;
        font-weight: 600;
    }

    .cart-item {
        padding: 1rem 1.5rem;
        background: #f8f9fa;
        border-radius: 8px;
        margin-bottom: 0.75rem;
        border-left: 4px solid #1a472a;
    }

    .cart-item-name {
        font-weight: 600;
        font-size: 1.1rem;
        color: #1f2937;
        margin-bottom: 0.3rem;
    }

    .cart-item-details {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
    }

    .cart-item-info {
        flex: 1;
    }

    .cart-item-controls {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .cart-item-price {
        font-size: 0.9rem;
        color: #6b7280;
    }

    .quantity-input {
        width: 70px;
        padding: 0.5rem;
        border: 1px solid #ddd;
        border-radius: 4px;
        text-align: center;
    }

    .cart-item-total {
        font-weight: 700;
        font-size: 1.1rem;
        color: #1a472a;
        min-width: 100px;
        text-align: right;
    }

    .cart-footer {
        padding: 1rem;
        border-top: 2px solid #e0e0e0;
        background: #f8f9fa;
    }

    .item-card {
        background: white;
        border: 2px solid #e0e0e0;
        border-radius: 10px;
        padding: 1rem;
        margin-bottom: 0.8rem;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .item-card:hover {
        border-color: #1a472a;
        box-shadow: 0 4px 12px rgba(26, 71, 42, 0.15);
        transform: translateY(-2px);
    }

    .item-card.selected {
        background: #f0f7f3;
        border-color: #f39200;
    }

    .item-name {
        font-weight: 600;
        color: #1a472a;
        margin-bottom: 0.3rem;
    }

    .item-sku {
        font-size: 0.85rem;
        color: #999;
    }

    .item-price {
        font-size: 1.2rem;
        color: #f39200;
        font-weight: 700;
    }

    .item-stock {
        font-size: 0.85rem;
        color: #666;
        margin-top: 0.3rem;
    }

    .total-amount {
        font-size: 2rem;
        font-weight: 700;
        color: #1a472a;
        text-align: right;
    }

    .print-receipt {
        display: none;
    }

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

    /* Dynamic Receipt Styles Based on Printer Configuration */
    /* ── These are initial defaults; they are overridden dynamically by JS ── */
    .receipt-container {
        margin: 0 auto;
        background: white;
        font-family: 'Courier New', monospace;
        box-sizing: border-box;
        overflow: hidden;           /* prevent content bleed */
        /* Width & padding set dynamically via JS */
    }

    .receipt-header {
        text-align: center;
        border-bottom: 1px dashed #000;
        font-weight: bold;
    }

    .receipt-items {
        margin: 10px 0;
    }

    .receipt-item {
        display: block;
        font-size: 11px;
    }

    .receipt-item-line {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
    }

    .receipt-item-name {
        flex: 1;
        margin-right: 5px;
        word-wrap: break-word;
    }

    .receipt-item-price {
        white-space: nowrap;
        font-weight: bold;
    }

    .receipt-total {
        border-top: 1px dashed #000;
        text-align: right;
        font-weight: bold;
    }

    .receipt-total-line {
        display: flex;
        justify-content: space-between;
        margin-bottom: 3px;
    }

    .receipt-footer {
        text-align: center;
        border-top: 1px dashed #000;
    }

    /* ── Receipt Preview Panel ── */
    .receipt-preview-panel {
        background: #f8f9fa;
        border: 2px solid #dee2e6;
        border-radius: 12px;
        padding: 1rem;
        margin-top: 1rem;
    }
    .receipt-preview-panel .preview-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.75rem;
    }
    .receipt-preview-wrapper {
        background: #e9ecef;
        padding: 1rem;
        border-radius: 8px;
        display: flex;
        justify-content: center;
        overflow: hidden;
    }
    .receipt-preview-simulated {
        background: white;
        box-shadow: 0 2px 12px rgba(0,0,0,0.12);
        min-height: 200px;
        position: relative;
        overflow: hidden;
    }
    .printer-info-badge {
        display: inline-block;
        background: #1a472a;
        color: white;
        padding: 3px 10px;
        border-radius: 12px;
        font-size: 0.75rem;
        margin-right: 4px;
    }
    .overflow-warning {
        background: #fff3cd;
        border: 1px solid #ffc107;
        border-radius: 8px;
        padding: 0.75rem 1rem;
        margin-top: 0.5rem;
        display: none;
    }
    .overflow-warning.visible { display: block; }

    /* ── Printer Selector in POS ── */
    .pos-printer-selector {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .pos-printer-selector select {
        max-width: 240px;
    }

    @media (max-width: 1200px) {
        .pos-container {
            grid-template-columns: 1fr;
        }
    }
</style>
@endsection

@section('content')
<div class="fade-in">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0"><i class="bi bi-cash-register"></i> Point of Sale</h1>
        <div class="d-flex align-items-center gap-3">
            <div class="pos-printer-selector">
                <label class="form-label mb-0 fw-bold" for="posPrinterProfile"><i class="bi bi-printer"></i></label>
                <select id="posPrinterProfile" class="form-select form-select-sm" onchange="onPrinterProfileChange(this.value)">
                    @php $profiles = \App\Models\Setting::getPrinterProfiles(); @endphp
                    @foreach($profiles as $key => $p)
                        <option value="{{ $key }}" {{ $printerConfig['profile_key'] === $key ? 'selected' : '' }}>
                            {{ $p['name'] }} ({{ $p['paper_width_mm'] }}mm)
                        </option>
                    @endforeach
                </select>
            </div>
            <span class="badge badge-info">{{ Auth::user()->name }}</span>
        </div>
    </div>


    <div class="row mb-3">
        <div class="col-md-4">
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-search"></i></span>
                <input type="text" id="searchInput" class="form-control" placeholder="Search product by name, SKU, or barcode...">
            </div>
        </div>
        <div class="col-md-3">
            <select id="categoryFilter" class="form-select">
                <option value="">All Categories</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <select id="customerSelect" class="form-select">
                <option value="">Walk-in Customer</option>
                @foreach($customers as $customer)
                    <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                @endforeach
                <option value="new">+ Add New Customer</option>
            </select>
        </div>
        <div class="col-md-2">
            <button class="btn btn-outline-secondary w-100" onclick="clearCart()">
                <i class="bi bi-trash"></i> Clear Cart
            </button>
        </div>
    </div>

    <!-- New Customer Modal -->
    <div class="modal fade" id="newCustomerModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Customer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="newCustomerForm">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Customer Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Customer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="pos-container">
        <!-- Products List -->
        <div class="pos-items" id="itemsList">
            @forelse($items as $item)
                <div class="item-card" onclick="addToCart({{ $item->id }}, '{{ $item->name }}', {{ $item->retail_price }})">
                    <div class="item-name">{{ $item->name }}</div>
                    <div class="item-sku">SKU: {{ $item->sku }}</div>
                    <div class="item-price">₦{{ number_format($item->retail_price, 2) }}</div>
                    <div class="item-stock">
                        <span class="@if($item->stock->quantity_on_hand <= $item->reorder_level) text-danger @else text-success @endif">
                            Stock: {{ $item->stock->quantity_on_hand }}
                        </span>
                    </div>
                </div>
            @empty
                <div class="alert alert-info">No products available</div>
            @endforelse
        </div>

        <!-- Shopping Cart -->
        <div class="pos-cart">
            <div class="cart-header">
                <i class="bi bi-cart3"></i> Shopping Cart
            </div>
            <div class="cart-items" id="cartItems">
                <div class="text-center text-muted py-5">
                    <i class="bi bi-cart-x" style="font-size: 2rem;"></i>
                    <p class="mt-2">Cart is empty</p>
                </div>
            </div>
            <div class="cart-footer">
                <div class="row mb-3">
                    <div class="col-6">Subtotal:</div>
                    <div class="col-6 text-end">₦<span id="subtotal">0.00</span></div>
                </div>
                <div class="row mb-3 fw-bold">
                    <div class="col-6 total-amount">Total:</div>
                    <div class="col-6 total-amount">₦<span id="total">0.00</span></div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Payment Method</label>
                    <select id="paymentMethod" class="form-select">
                        <option value="cash">Cash</option>
                        <option value="card">Card</option>
                        <option value="transfer">Bank Transfer</option>
                        <option value="mixed">Mixed</option>
                    </select>
                </div>
                <div class="d-grid gap-2">
                    <button class="btn btn-primary btn-lg" onclick="showConfirmModal()">
                        <i class="bi bi-check-circle"></i> Complete Sale
                    </button>
                    <button class="btn btn-secondary" onclick="printReceipt()">
                        <i class="bi bi-printer"></i> Print Receipt
                    </button>
                    <button class="btn btn-outline-info" onclick="openPreview()">
                        <i class="bi bi-eye"></i> Preview Receipt
                    </button>
                </div>

                <!-- Confirm Sale Modal -->
                <div class="modal fade" id="confirmSaleModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Confirm Sale</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <p>Are you sure you want to complete this sale?</p>
                                <ul id="confirmSaleSummary"></ul>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                <button type="button" class="btn btn-success" onclick="completeSale()">Yes, Complete Sale</button>
                            </div>
                        </div>
                    </div>
                </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Receipt Template (Hidden – used for actual print) -->
<div id="receiptContent" class="print-receipt">
    <div class="receipt-container" id="receiptContainer">
        <div class="receipt-header" id="receiptHeader">
            <div>{{ $printerConfig['company_name'] }}</div>
            <div>Sales Receipt</div>
            <div>Receipt #: <span id="receiptNumber"></span></div>
            <div><span id="receiptDate"></span></div>
        </div>

        <div class="receipt-items" id="receiptItems"></div>

        <div class="receipt-total">
            <div class="receipt-total-line">
                <span>Subtotal:</span>
                <span>₦<span id="receiptSubtotal">0.00</span></span>
            </div>
            <div class="receipt-total-line" style="border-top: 1px dashed #000; padding-top: 3px; margin-top: 3px;">
                <span>TOTAL:</span>
                <span><strong>₦<span id="receiptTotal">0.00</span></strong></span>
            </div>
        </div>

        <div class="receipt-footer">
            <div>Thank you for your purchase!</div>
            <div>Visit us again!</div>
            <div style="margin-top: 8px;"><span id="printTime"></span></div>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════
     Receipt Preview Panel  – shows a real-time simulated preview
     ═══════════════════════════════════════════════════════════════ -->
<div class="receipt-preview-panel" id="receiptPreviewPanel" style="display:none;">
    <div class="preview-header">
        <div>
            <strong><i class="bi bi-eye"></i> Receipt Preview</strong>
            <span class="printer-info-badge" id="previewPrinterBadge"></span>
            <span class="printer-info-badge" id="previewDimensionBadge"></span>
        </div>
        <div>
            <button class="btn btn-sm btn-outline-secondary" onclick="closePreview()"><i class="bi bi-x-lg"></i> Close</button>
        </div>
    </div>

    <!-- Overflow warning banner -->
    <div class="overflow-warning" id="overflowWarning">
        <i class="bi bi-exclamation-triangle-fill text-warning"></i>
        <strong>Content overflow detected!</strong>
        <span id="overflowDetail"></span>
    </div>

    <div class="receipt-preview-wrapper">
        <div class="receipt-preview-simulated" id="previewCanvas">
            <!-- Cloned receipt rendered here -->
        </div>
    </div>
</div>

<script>
/* ═══════════════════════════════════════════════════════════
   Printer Profile Database & Layout Engine
   ═══════════════════════════════════════════════════════════ */

/**
 * Full printer profiles mirrored from the server.
 * NO hardcoded widths anywhere else – everything derives from here.
 */
const PRINTER_PROFILES = @json(\App\Models\Setting::getPrinterProfiles());

/* Active configuration – mutable; changes when user picks a printer */
let activePrinterConfig = @json($printerConfig);

/* ── Conversion helpers ── */
function mmToPixels(mm, dpi) {
    return Math.round((mm / 25.4) * dpi);
}

/**
 * Derive layout metrics from a profile object.
 * Returns { paperMm, printableMm, safeMm, dpi, printablePx, safePx, canvasPx,
 *           cssPaperWidth, cssPrintableWidth, cssSafeWidth, fontSize, lineHeight, padding }
 */
function computeLayout(profile) {
    const dpi  = profile.resolution_dpi || 203;
    const paperMm     = profile.paper_width_mm;
    const printableMm = profile.printable_width_mm;
    const safeMm      = printableMm - 2; // 2 mm total padding (safe margin rule)

    const printablePx = mmToPixels(printableMm, dpi);
    const safePx      = mmToPixels(safeMm, dpi);

    // CSS widths for on-screen simulation (96 dpi screen baseline)
    const cssPaperWidth     = paperMm + 'mm';
    const cssPrintableWidth = printableMm + 'mm';
    const cssSafeWidth      = safeMm + 'mm';

    // Adaptive font size / padding
    let fontSize, lineHeight, padding;
    if (paperMm <= 58) {
        fontSize   = '10px';
        lineHeight = '1.2';
        padding    = '3mm';
    } else if (paperMm <= 80) {
        fontSize   = '12px';
        lineHeight = '1.2';
        padding    = '4mm'; // 2mm each side ≈ safe margin
    } else if (paperMm <= 112) {
        fontSize   = '14px';
        lineHeight = '1.3';
        padding    = '4mm';
    } else {
        // A4 / laser
        fontSize   = '14px';
        lineHeight = '1.4';
        padding    = '10mm';
    }

    return {
        paperMm, printableMm, safeMm, dpi,
        printablePx, safePx, canvasPx: safePx,
        cssPaperWidth, cssPrintableWidth, cssSafeWidth,
        fontSize, lineHeight, padding,
    };
}

/* ── Apply layout to the receipt container & preview ── */
function applyLayout(profileKey) {
    const profile = PRINTER_PROFILES[profileKey];
    if (!profile) { console.warn('Unknown printer profile:', profileKey); return; }

    const layout = computeLayout(profile);

    // --- Receipt container (used for actual print & hidden template) ---
    const rc = document.getElementById('receiptContainer');
    if (rc) {
        rc.style.width      = layout.cssSafeWidth;
        rc.style.maxWidth    = layout.cssSafeWidth;
        rc.style.padding     = layout.padding;
        rc.style.fontSize    = layout.fontSize;
        rc.style.lineHeight  = layout.lineHeight;
        rc.style.fontFamily  = profile.paper_width_mm >= 210 ? 'Arial, sans-serif' : "'Courier New', monospace";
    }

    // --- Preview canvas ---
    const pc = document.getElementById('previewCanvas');
    if (pc) {
        pc.style.width     = layout.cssPrintableWidth;
        pc.style.maxWidth  = layout.cssPrintableWidth;
        pc.style.padding   = layout.padding;
        pc.style.fontSize  = layout.fontSize;
        pc.style.lineHeight = layout.lineHeight;
        pc.style.fontFamily = profile.paper_width_mm >= 210 ? 'Arial, sans-serif' : "'Courier New', monospace";
    }

    // --- Scale images/logos so they cannot overflow ---
    document.querySelectorAll('#receiptContainer img, #previewCanvas img').forEach(img => {
        img.style.maxWidth = '100%';
        img.style.height   = 'auto';
    });

    // --- Update info badges ---
    const badge1 = document.getElementById('previewPrinterBadge');
    const badge2 = document.getElementById('previewDimensionBadge');
    if (badge1) badge1.textContent = profile.name;
    if (badge2) badge2.textContent = `${layout.printableMm}mm print · ${layout.safeMm}mm safe · ${layout.dpi} dpi`;

    // --- Run overflow check ---
    validateContentWidth(layout);

    // Update the mutable config
    activePrinterConfig = Object.assign({}, activePrinterConfig, profile, {
        profile_key:        profileKey,
        safe_width_mm:      layout.safeMm,
        printable_width_px: layout.printablePx,
        safe_width_px:      layout.safePx,
        canvas_width_px:    layout.canvasPx,
    });
}

/* ── Validation: content must not exceed printable width ── */
function validateContentWidth(layout) {
    const warningEl = document.getElementById('overflowWarning');
    const detailEl  = document.getElementById('overflowDetail');
    if (!warningEl) return true;

    // Measure the preview canvas or receipt container
    const container = document.getElementById('previewCanvas') || document.getElementById('receiptContainer');
    if (!container) { warningEl.classList.remove('visible'); return true; }

    const maxAllowedPx = container.getBoundingClientRect().width;
    let overflowing = false;
    let overflowItems = [];

    container.querySelectorAll('*').forEach(el => {
        if (el.scrollWidth > maxAllowedPx + 2) { // 2px tolerance
            overflowing = true;
            overflowItems.push(el.tagName.toLowerCase());
        }
    });

    if (overflowing) {
        warningEl.classList.add('visible');
        if (detailEl) detailEl.textContent =
            ` Some elements (${[...new Set(overflowItems)].join(', ')}) exceed the ${layout.printableMm}mm printable width. Content may be clipped on print.`;
    } else {
        warningEl.classList.remove('visible');
    }

    return !overflowing;
}

/* ── Called when user picks a different printer from the POS dropdown ── */
function onPrinterProfileChange(profileKey) {
    applyLayout(profileKey);
    // Re-render the preview if it's open
    const panel = document.getElementById('receiptPreviewPanel');
    if (panel && panel.style.display !== 'none') {
        renderPreview();
    }
}

/* ── Preview system ── */
function openPreview() {
    const panel = document.getElementById('receiptPreviewPanel');
    panel.style.display = 'block';
    renderPreview();
}

function closePreview() {
    document.getElementById('receiptPreviewPanel').style.display = 'none';
}

function renderPreview() {
    const canvas = document.getElementById('previewCanvas');
    if (!canvas) return;

    // Clone the receipt content into the preview
    const source = document.getElementById('receiptContainer');
    if (!source) return;
    canvas.innerHTML = source.innerHTML;

    // Ensure images are constrained
    canvas.querySelectorAll('img').forEach(img => {
        img.style.maxWidth = '100%';
        img.style.height   = 'auto';
    });

    // Re-apply layout dimensions to the preview wrapper
    const profileKey = document.getElementById('posPrinterProfile')?.value
                     || activePrinterConfig.profile_key
                     || 'thermal_80mm';
    const profile = PRINTER_PROFILES[profileKey];
    if (profile) {
        const layout = computeLayout(profile);
        canvas.style.width    = layout.cssPrintableWidth;
        canvas.style.maxWidth = layout.cssPrintableWidth;
        canvas.style.padding  = layout.padding;

        // Deferred overflow check (after layout settles)
        requestAnimationFrame(() => validateContentWidth(layout));
    }
}

/* ═══════════════════════════════════════════════════════════
   POS Cart Logic  (preserved & enhanced)
   ═══════════════════════════════════════════════════════════ */

let cart = [];
let lastSale = null;
const TAX_RATE = 0.075;

function addToCart(itemId, itemName, price) {
    const existingItem = cart.find(item => item.id === itemId);
    if (existingItem) {
        existingItem.quantity++;
    } else {
        cart.push({ id: itemId, name: itemName, price: price, quantity: 1 });
    }
    updateCart();
}

function removeFromCart(itemId) {
    cart = cart.filter(item => item.id !== itemId);
    updateCart();
}

function updateQuantity(itemId, quantity) {
    const item = cart.find(i => i.id === itemId);
    if (item) {
        item.quantity = Math.max(1, parseInt(quantity));
        updateCart();
    }
}

function clearCart() {
    if (confirm('Are you sure you want to clear the cart?')) {
        cart = [];
        updateCart();
    }
}

function updateCart() {
    const cartItems = document.getElementById('cartItems');

    if (cart.length === 0) {
        cartItems.innerHTML = `
            <div class="text-center text-muted py-5">
                <i class="bi bi-cart-x" style="font-size: 2rem;"></i>
                <p class="mt-2">Cart is empty</p>
            </div>
        `;
    } else {
        cartItems.innerHTML = cart.map(item => `
            <div class="cart-item">
                <div class="cart-item-name">${item.name}</div>
                <div class="cart-item-details">
                    <div class="cart-item-info">
                        <div class="cart-item-price">₦${item.price.toLocaleString()} each</div>
                    </div>
                    <div class="cart-item-controls">
                        <input type="number" min="1" value="${item.quantity}"
                               onchange="updateQuantity(${item.id}, this.value)"
                               class="quantity-input">
                        <div class="cart-item-total">
                            ₦${(item.price * item.quantity).toLocaleString()}
                        </div>
                        <button class="btn btn-sm btn-danger" onclick="removeFromCart(${item.id})" title="Remove">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        `).join('');
    }

    calculateTotals();
}

function calculateTotals() {
    const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    const total = subtotal; // No tax

    document.getElementById('subtotal').textContent = subtotal.toFixed(2);
    document.getElementById('total').textContent = total.toFixed(2);
}

function showConfirmModal() {
    if (cart.length === 0) {
        alert('Cart is empty!');
        return;
    }
    const summary = document.getElementById('confirmSaleSummary');
    summary.innerHTML = cart.map(item =>
        `<li>${item.name} x${item.quantity} - ₦${(item.price * item.quantity).toLocaleString()}</li>`
    ).join('');
    new bootstrap.Modal(document.getElementById('confirmSaleModal')).show();
}

function completeSale() {
    if (cart.length === 0) {
        alert('Cart is empty!');
        return;
    }
    const paymentMethod = document.getElementById('paymentMethod').value;
    const customerId    = document.getElementById('customerSelect').value;
    const subtotal      = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    const tax           = 0;
    const total         = subtotal;

    fetch('/sales/create', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
        },
        body: JSON.stringify({
            items: cart,
            payment_method: paymentMethod,
            customer_id: customerId,
            subtotal, tax, total
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            lastSale = {
                cart: JSON.parse(JSON.stringify(cart)),
                subtotal, tax, total,
                receiptNumber: data.receipt_number || Math.floor(Math.random() * 100000),
                receiptDate: new Date().toLocaleString(),
            };
            cart = [];
            updateCart();
            bootstrap.Modal.getInstance(document.getElementById('confirmSaleModal')).hide();
            alert('Sale completed successfully!');
            printReceipt();
        }
    })
    .catch(err => console.error(err));
}

/* ── Print receipt with validation ── */
function printReceipt() {
    let sale;
    if (lastSale) {
        sale = lastSale;
    } else {
        const calculatedSubtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        sale = {
            cart: cart,
            subtotal: calculatedSubtotal,
            tax: 0,
            total: calculatedSubtotal,
            receiptNumber: Math.floor(Math.random() * 100000),
            receiptDate: new Date().toLocaleString('en-GB', {
                year: 'numeric', month: '2-digit', day: '2-digit',
                hour: '2-digit', minute: '2-digit'
            })
        };
    }

    document.getElementById('receiptNumber').textContent = sale.receiptNumber || Math.floor(Math.random() * 100000);
    document.getElementById('receiptDate').textContent   = sale.receiptDate || new Date().toLocaleString('en-GB', {
        year: 'numeric', month: '2-digit', day: '2-digit',
        hour: '2-digit', minute: '2-digit'
    });

    const receiptItemsHtml = (sale.cart || []).map(item => `
        <div class="receipt-item">
            <div class="receipt-item-line">
                <span class="receipt-item-name">${item.name}</span>
                <span class="receipt-item-price">₦${(item.price * item.quantity).toLocaleString()}</span>
            </div>
            <div style="font-size: 10px; color: #666; margin-left: 2px;">
                ${item.quantity} x ₦${item.price.toLocaleString()}
            </div>
        </div>
    `).join('');

    document.getElementById('receiptItems').innerHTML      = receiptItemsHtml;
    document.getElementById('receiptSubtotal').textContent  = sale.subtotal.toFixed(2);
    document.getElementById('receiptTotal').textContent     = sale.total.toFixed(2);
    document.getElementById('printTime').textContent        = new Date().toLocaleString();

    // Re-apply dynamic layout before printing
    const profileKey = document.getElementById('posPrinterProfile')?.value
                     || activePrinterConfig.profile_key
                     || 'thermal_80mm';
    applyLayout(profileKey);

    // ── Pre-print validation ──
    const profile = PRINTER_PROFILES[profileKey];
    if (profile) {
        const layout = computeLayout(profile);
        const isValid = validateContentWidth(layout);
        if (!isValid) {
            const proceed = confirm(
                `⚠ Content exceeds the ${layout.printableMm}mm printable width of the ${profile.name}.\n` +
                `Printing may clip edges. Continue anyway?`
            );
            if (!proceed) return;
        }
    }

    window.print();
}

/* ── Customer selection logic ── */
document.getElementById('customerSelect').addEventListener('change', function() {
    if (this.value === 'new') {
        new bootstrap.Modal(document.getElementById('newCustomerModal')).show();
        this.value = '';
    }
});

document.getElementById('newCustomerForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const name = this.name.value.trim();
    if (name) {
        const select = document.getElementById('customerSelect');
        const option = document.createElement('option');
        option.value = 'new_' + Date.now();
        option.textContent = name;
        select.insertBefore(option, select.querySelector('option[value="new"]'));
        select.value = option.value;
        bootstrap.Modal.getInstance(document.getElementById('newCustomerModal')).hide();
    }
});

/* ── Search functionality ── */
document.getElementById('searchInput').addEventListener('keyup', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    document.querySelectorAll('.item-card').forEach(item => {
        item.style.display = item.textContent.toLowerCase().includes(searchTerm) ? '' : 'none';
    });
});

document.getElementById('categoryFilter').addEventListener('change', function() {
    // Implement category filtering
});

/* ═══════════════ Initialise on page load ═══════════════ */
document.addEventListener('DOMContentLoaded', function() {
    const initialProfile = activePrinterConfig.profile_key || 'thermal_80mm';
    applyLayout(initialProfile);
});
</script>
@endsection
