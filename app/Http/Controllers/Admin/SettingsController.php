<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    public function index()
    {
        // Common printer configurations
        $commonPrinters = [
            'xprinter_xp_m804' => [
                'name' => 'Xprinter XP-M804',
                'type' => 'thermal',
                'width' => '80mm',
                'paper_width_mm' => 80,
                'printable_width_mm' => 72,
                'resolution_dpi' => 203,
                'description' => 'Xprinter XP-M804 Thermal Receipt Printer – 80mm paper, 72mm printable area, 203 dpi (8 dots/mm)',
                'brands' => ['Xprinter XP-M804'],
                'features' => ['Auto-cut', '203 dpi', '80mm paper', '72mm print area', 'USB/Bluetooth'],
            ],
            'thermal_80mm' => [
                'name' => 'Standard Thermal Receipt Printer (80mm)',
                'type' => 'thermal',
                'width' => '80mm',
                'paper_width_mm' => 80,
                'printable_width_mm' => 72,
                'resolution_dpi' => 203,
                'description' => 'Most common POS thermal printer (Epson TM-T20II, Star TSP143, etc.)',
                'brands' => ['Epson TM-T20II', 'Star TSP143', 'Citizen CT-S310A', 'Bixolon SRP-275III'],
                'features' => ['Auto-cut', 'Fast printing', 'Compact design', 'USB/Ethernet'],
            ],
            'thermal_58mm' => [
                'name' => 'Compact Thermal Receipt Printer (58mm)',
                'type' => 'thermal_58mm',
                'width' => '58mm',
                'paper_width_mm' => 58,
                'printable_width_mm' => 48,
                'resolution_dpi' => 203,
                'description' => 'Space-saving thermal printer for small counters',
                'brands' => ['Epson TM-T20III', 'Star TSP100', 'Citizen CT-S2000', 'Bixolon SRP-270'],
                'features' => ['Compact', 'Wall mountable', 'Low power consumption', 'Mobile friendly'],
            ],
            'thermal_mobile' => [
                'name' => 'Mobile Thermal Printer',
                'type' => 'thermal_58mm',
                'width' => '58mm',
                'paper_width_mm' => 58,
                'printable_width_mm' => 48,
                'resolution_dpi' => 203,
                'description' => 'Portable thermal printer for mobile POS',
                'brands' => ['Epson TM-P20', 'Star SM-S230i', 'Citizen CMP-25L', 'Bixolon SPP-R200III'],
                'features' => ['Battery powered', 'Bluetooth/WiFi', 'Lightweight', 'Belt clip'],
            ],
            'laser_a4' => [
                'name' => 'Standard Laser/Inkjet Printer (A4)',
                'type' => 'laser',
                'width' => 'a4',
                'paper_width_mm' => 210,
                'printable_width_mm' => 190,
                'resolution_dpi' => 300,
                'description' => 'Standard office printer for formal receipts and invoices',
                'brands' => ['HP LaserJet Pro', 'Canon PIXMA', 'Brother HL-L2350DW', 'Epson EcoTank'],
                'features' => ['High quality', 'Color options', 'Duplex printing', 'Large paper capacity'],
            ],
            'dot_matrix' => [
                'name' => 'Dot Matrix Impact Printer',
                'type' => 'dot_matrix',
                'width' => '80mm',
                'paper_width_mm' => 80,
                'printable_width_mm' => 70,
                'resolution_dpi' => 180,
                'description' => 'Impact printer for multi-part forms and carbon copies',
                'brands' => ['Epson LX-350', 'OKI ML1190', 'Fujitsu DL3850+', 'Star SP742'],
                'features' => ['Carbon copy support', 'Continuous forms', 'Durable', 'Low cost per page'],
            ],
            'kitchen_thermal' => [
                'name' => 'Kitchen Thermal Printer (80mm)',
                'type' => 'thermal',
                'width' => '80mm',
                'paper_width_mm' => 80,
                'printable_width_mm' => 72,
                'resolution_dpi' => 203,
                'description' => 'Heat and grease resistant thermal printer for kitchens',
                'brands' => ['Epson TM-T88VI', 'Star TSP650II', 'Citizen CT-S651', 'Bixolon SRP-350plusIII'],
                'features' => ['Heat resistant', 'Splash proof', 'Fast printing', 'Kitchen mount options'],
            ],
            'wide_thermal' => [
                'name' => 'Wide Format Thermal Printer (112mm)',
                'type' => 'thermal',
                'width' => '112mm',
                'paper_width_mm' => 112,
                'printable_width_mm' => 104,
                'resolution_dpi' => 203,
                'description' => 'Wide thermal printer for detailed receipts and labels',
                'brands' => ['Epson TM-T90', 'Star TSP847II', 'Citizen CT-S4000', 'Bixolon SRP-350plus'],
                'features' => ['Wide format', 'Graphics support', 'Multiple interfaces', 'High resolution'],
            ]
        ];

        $printerTypes = [
            'thermal' => 'Thermal Printer (80mm)',
            'thermal_58mm' => 'Thermal Printer (58mm)',
            'laser' => 'Laser/Inkjet Printer (A4)',
            'dot_matrix' => 'Dot Matrix Printer'
        ];

        $receiptWidths = [
            '58mm' => '58mm (Small Thermal)',
            '80mm' => '80mm (Standard Thermal)',
            '112mm' => '112mm (Wide Thermal)',
            'a4' => 'A4 Paper (210mm)'
        ];

        // Printer profile keys for the profile selector
        $printerProfileKeys = collect($commonPrinters)->mapWithKeys(function ($p, $key) {
            return [$key => $p['name']];
        })->toArray();

        $settings = [
            'printer_type' => Setting::get('printer_type', 'thermal'),
            'receipt_width' => Setting::get('receipt_width', '80mm'),
            'company_name' => Setting::get('company_name', 'DAKOSS GLOBAL NIGERIA LTD'),
            'printer_profile' => Setting::get('printer_profile', 'thermal_80mm'),
        ];

        return view('admin.settings.printer', compact(
            'printerTypes', 'receiptWidths', 'settings', 'commonPrinters', 'printerProfileKeys'
        ));
    }

    public function update(Request $request)
    {
        $validProfileKeys = array_keys(Setting::getPrinterProfiles());

        $request->validate([
            'printer_type' => 'required|in:thermal,thermal_58mm,laser,dot_matrix',
            'receipt_width' => 'required|in:58mm,80mm,112mm,a4',
            'company_name' => 'required|string|max:255',
            'printer_profile' => 'sometimes|in:' . implode(',', $validProfileKeys) . ',' . implode(',', array_keys($this->getCommonPrinterKeys())),
        ]);

        Setting::set('printer_type', $request->printer_type, 'select', 'Default printer type for receipts');
        Setting::set('receipt_width', $request->receipt_width, 'select', 'Receipt paper width');
        Setting::set('company_name', $request->company_name, 'text', 'Company name on receipts');

        if ($request->filled('printer_profile')) {
            Setting::set('printer_profile', $request->printer_profile, 'select', 'Active printer profile key');
        }

        return redirect()->route('admin.settings.printer')
                        ->with('success', 'Printer settings updated successfully!');
    }

    /**
     * Helper: return the common printer preset keys for validation.
     */
    private function getCommonPrinterKeys(): array
    {
        return [
            'xprinter_xp_m804' => 'Xprinter XP-M804',
            'thermal_80mm'     => 'Standard Thermal 80mm',
            'thermal_58mm'     => 'Compact Thermal 58mm',
            'thermal_mobile'   => 'Mobile Thermal',
            'laser_a4'         => 'Laser A4',
            'dot_matrix'       => 'Dot Matrix',
            'kitchen_thermal'  => 'Kitchen Thermal',
            'wide_thermal'     => 'Wide Thermal 112mm',
        ];
    }
}
