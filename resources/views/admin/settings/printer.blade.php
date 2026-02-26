@extends('layouts.app')

@section('title', 'Printer Settings')

@section('extra_css')
<style>
.printer-preset {
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    padding: 1rem;
    margin-bottom: 1rem;
    cursor: pointer;
    transition: all 0.3s ease;
}

.printer-preset:hover {
    border-color: #1a472a;
    box-shadow: 0 2px 8px rgba(26, 71, 42, 0.1);
}

.printer-preset.selected {
    border-color: #1a472a;
    background-color: #f8f9fa;
}

.printer-brand {
    display: inline-block;
    background: #e9ecef;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 0.75rem;
    margin: 2px;
}

.printer-feature {
    display: inline-block;
    background: #d1ecf1;
    color: #0c5460;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 0.75rem;
    margin: 2px;
}

.search-highlight {
    background-color: yellow;
    font-weight: bold;
}
</style>
@endsection

@section('content')
<div class="fade-in">
    <div class="row">
        <!-- Configuration Panel -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0"><i class="bi bi-printer"></i> Current Configuration</h4>
                </div>
                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <form method="POST" action="{{ route('admin.settings.printer.update') }}" id="printerForm">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label">Company Name</label>
                            <input type="text" name="company_name" class="form-control @error('company_name') is-invalid @enderror"
                                   value="{{ old('company_name', $settings['company_name']) }}" required>
                            @error('company_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Printer Type</label>
                            <select name="printer_type" class="form-select @error('printer_type') is-invalid @enderror" required>
                                @foreach($printerTypes as $value => $label)
                                    <option value="{{ $value }}" {{ old('printer_type', $settings['printer_type']) == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('printer_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Receipt Width</label>
                            <select name="receipt_width" class="form-select @error('receipt_width') is-invalid @enderror" required>
                                @foreach($receiptWidths as $value => $label)
                                    <option value="{{ $value }}" {{ old('receipt_width', $settings['receipt_width']) == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('receipt_width')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Hidden field tracking the active printer profile key -->
                        <input type="hidden" name="printer_profile" id="printerProfileInput"
                               value="{{ old('printer_profile', $settings['printer_profile'] ?? 'thermal_80mm') }}">

                        <!-- Active printer profile badge -->
                        <div class="mb-3">
                            <label class="form-label">Active Profile</label>
                            <div>
                                <span class="badge bg-success" id="activeProfileBadge">
                                    {{ $printerProfileKeys[$settings['printer_profile'] ?? 'thermal_80mm'] ?? 'Standard Thermal 80mm' }}
                                </span>
                            </div>
                            <small class="text-muted">Selected via the preset panel on the right, or the POS printer dropdown.</small>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Update Settings
                            </button>
                            <button type="button" class="btn btn-secondary" onclick="testPrint()">
                                <i class="bi bi-printer"></i> Test Print
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Printer Presets Panel -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0"><i class="bi bi-collection"></i> Common Printer Configurations</h4>
                    <div class="col-md-4">
                        <input type="text" id="printerSearch" class="form-control" placeholder="Search printers, brands, or features...">
                    </div>
                </div>
                <div class="card-body" style="max-height: 600px; overflow-y: auto;">
                    <div id="printerPresets">
                        @foreach($commonPrinters as $key => $printer)
                            <div class="printer-preset" data-type="{{ $printer['type'] }}" data-width="{{ $printer['width'] }}" data-profile-key="{{ $key }}"
                                 data-search="{{ strtolower($printer['name'] . ' ' . implode(' ', $printer['brands']) . ' ' . implode(' ', $printer['features']) . ' ' . $printer['description']) }}">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <h5 class="mb-2">{{ $printer['name'] }}</h5>
                                        <p class="text-muted mb-2">{{ $printer['description'] }}</p>

                                        <div class="mb-2">
                                            <strong>Popular Brands:</strong><br>
                                            @foreach($printer['brands'] as $brand)
                                                <span class="printer-brand">{{ $brand }}</span>
                                            @endforeach
                                        </div>

                                        <div class="mb-2">
                                            <strong>Features:</strong><br>
                                            @foreach($printer['features'] as $feature)
                                                <span class="printer-feature">{{ $feature }}</span>
                                            @endforeach
                                        </div>

                                        <div class="small">
                                            <strong>Type:</strong> {{ $printerTypes[$printer['type']] ?? $printer['type'] }} |
                                            <strong>Width:</strong> {{ $receiptWidths[$printer['width']] ?? $printer['width'] }}
                                        </div>
                                    </div>
                                    <div class="ms-3">
                                        <button class="btn btn-outline-primary btn-sm select-preset"
                                                data-type="{{ $printer['type'] }}" data-width="{{ $printer['width'] }}">
                                            <i class="bi bi-check-circle"></i> Select
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div id="noResults" class="text-center py-4" style="display: none;">
                        <i class="bi bi-search" style="font-size: 3rem; color: #ccc;"></i>
                        <p class="text-muted mt-2">No printers found matching your search.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Search functionality
document.getElementById('printerSearch').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const presets = document.querySelectorAll('.printer-preset');
    let visibleCount = 0;

    presets.forEach(preset => {
        const searchData = preset.getAttribute('data-search');
        const isVisible = searchData.includes(searchTerm);

        preset.style.display = isVisible ? 'block' : 'none';
        if (isVisible) {
            visibleCount++;
            // Highlight search terms
            highlightSearchTerms(preset, searchTerm);
        }
    });

    document.getElementById('noResults').style.display = visibleCount === 0 ? 'block' : 'none';
});

function highlightSearchTerms(element, searchTerm) {
    if (!searchTerm) return;

    const walker = document.createTreeWalker(
        element,
        NodeFilter.SHOW_TEXT,
        null,
        false
    );

    const textNodes = [];
    let node;

    while (node = walker.nextNode()) {
        textNodes.push(node);
    }

    textNodes.forEach(textNode => {
        const parent = textNode.parentNode;
        if (parent.tagName === 'SCRIPT' || parent.tagName === 'STYLE') return;

        const text = textNode.textContent;
        const regex = new RegExp(`(${searchTerm})`, 'gi');

        if (regex.test(text)) {
            const highlightedHTML = text.replace(regex, '<span class="search-highlight">$1</span>');
            const wrapper = document.createElement('div');
            wrapper.innerHTML = highlightedHTML;

            while (wrapper.firstChild) {
                parent.insertBefore(wrapper.firstChild, textNode);
            }
            parent.removeChild(textNode);
        }
    });
}

// Preset selection
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('select-preset') || e.target.closest('.select-preset')) {
        const button = e.target.classList.contains('select-preset') ? e.target : e.target.closest('.select-preset');
        const type = button.getAttribute('data-type');
        const width = button.getAttribute('data-width');
        const profileKey = button.closest('.printer-preset')?.getAttribute('data-profile-key') || '';

        // Update form fields
        document.querySelector('select[name="printer_type"]').value = type;
        document.querySelector('select[name="receipt_width"]').value = width;

        // Update printer profile hidden input & badge
        const profileInput = document.getElementById('printerProfileInput');
        if (profileInput && profileKey) profileInput.value = profileKey;
        const badge = document.getElementById('activeProfileBadge');
        if (badge) badge.textContent = button.closest('.printer-preset')?.querySelector('h5')?.textContent || profileKey;

        // Visual feedback
        document.querySelectorAll('.printer-preset').forEach(p => p.classList.remove('selected'));
        button.closest('.printer-preset').classList.add('selected');

        // Show success message
        const alert = document.createElement('div');
        alert.className = 'alert alert-success alert-dismissible fade show';
        alert.innerHTML = `
            <i class="bi bi-check-circle"></i> Printer configuration selected!
            <strong>Remember to click "Update Settings" to save.</strong>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        const form = document.getElementById('printerForm');
        form.insertBefore(alert, form.firstChild);

        // Auto dismiss after 5 seconds
        setTimeout(() => {
            if (alert.parentNode) {
                alert.remove();
            }
        }, 5000);
    }
});

function testPrint() {
    const companyName = document.querySelector('input[name="company_name"]').value;
    const profileKey  = document.getElementById('printerProfileInput')?.value || 'thermal_80mm';

    // Dynamic printer profile specs for test receipt
    const profiles = @json($commonPrinters);
    const profile  = profiles[profileKey] || profiles['thermal_80mm'] || {};
    const printableWidthMm = profile.printable_width_mm || 72;
    const safeWidthMm      = printableWidthMm - 2;

    const testWindow = window.open('', '_blank');
    testWindow.document.write(`
        <html>
        <head>
            <title>Test Receipt</title>
            <style>
                body { font-family: 'Courier New', monospace; margin: 20px; }
                .receipt {
                    width: ${safeWidthMm}mm;
                    max-width: ${safeWidthMm}mm;
                    margin: 0 auto;
                    padding: 1mm;
                    box-sizing: border-box;
                    overflow: hidden;
                }
                .receipt img { max-width: 100%; height: auto; }
                .header { text-align: center; border-bottom: 1px dashed #000; padding-bottom: 10px; margin-bottom: 10px; }
                .footer { text-align: center; border-top: 1px dashed #000; padding-top: 10px; margin-top: 10px; }
                .meta   { font-size: 9px; color: #666; text-align: center; margin-top: 8px; }
            </style>
        </head>
        <body>
            <div class="receipt">
                <div class="header">
                    <strong>${companyName}</strong><br>
                    <small>TEST RECEIPT</small><br>
                    <small>` + new Date().toLocaleString() + `</small>
                </div>
                <div>Sample Item 1 x1 ........... ₦1,000</div>
                <div>Sample Item 2 x2 ........... ₦2,000</div>
                <div style="border-top: 1px dashed #000; padding-top: 5px; margin-top: 10px;">
                    <strong>TOTAL: ₦3,000</strong>
                </div>
                <div class="footer">
                    <small>Thank you for your purchase!</small>
                </div>
                <div class="meta">
                    Printer: ${profile.name || profileKey}<br>
                    Paper: ${profile.paper_width_mm || 80}mm · Print area: ${printableWidthMm}mm · Safe: ${safeWidthMm}mm<br>
                    DPI: ${profile.resolution_dpi || 203}
                </div>
            </div>
        </body>
        </html>
    `);
    testWindow.document.close();
    setTimeout(() => testWindow.print(), 500);
}
</script>
@endsection
