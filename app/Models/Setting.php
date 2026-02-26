<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = ['key', 'value', 'type', 'description'];

    /**
     * Get a setting value by key
     */
    public static function get($key, $default = null)
    {
        return Cache::remember("setting_{$key}", 3600, function () use ($key, $default) {
            $setting = static::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        });
    }

    /**
     * Set a setting value
     */
    public static function set($key, $value, $type = 'text', $description = null)
    {
        $setting = static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'type' => $type,
                'description' => $description
            ]
        );

        Cache::forget("setting_{$key}");
        return $setting;
    }

    /**
     * Full printer profile database keyed by type+width combo.
     * Each profile includes physical specs needed for dynamic layout.
     */
    public static function getPrinterProfiles(): array
    {
        return [
            'xprinter_xp_m804' => [
                'name'               => 'Xprinter XP-M804',
                'type'               => 'thermal',
                'paper_width_mm'     => 80,
                'printable_width_mm' => 72,
                'resolution_dpi'     => 203,
                'description'        => 'Xprinter XP-M804 Thermal Receipt Printer (80mm, 203dpi)',
            ],
            'thermal_80mm' => [
                'name'               => 'Standard Thermal 80mm',
                'type'               => 'thermal',
                'paper_width_mm'     => 80,
                'printable_width_mm' => 72,
                'resolution_dpi'     => 203,
                'description'        => 'Generic 80mm thermal receipt printer',
            ],
            'thermal_58mm' => [
                'name'               => 'Compact Thermal 58mm',
                'type'               => 'thermal_58mm',
                'paper_width_mm'     => 58,
                'printable_width_mm' => 48,
                'resolution_dpi'     => 203,
                'description'        => 'Compact 58mm thermal receipt printer',
            ],
            'thermal_112mm' => [
                'name'               => 'Wide Thermal 112mm',
                'type'               => 'thermal',
                'paper_width_mm'     => 112,
                'printable_width_mm' => 104,
                'resolution_dpi'     => 203,
                'description'        => 'Wide format 112mm thermal printer',
            ],
            'laser_a4' => [
                'name'               => 'Laser / Inkjet A4',
                'type'               => 'laser',
                'paper_width_mm'     => 210,
                'printable_width_mm' => 190,
                'resolution_dpi'     => 300,
                'description'        => 'Standard A4 laser or inkjet printer',
            ],
            'dot_matrix_80mm' => [
                'name'               => 'Dot Matrix 80mm',
                'type'               => 'dot_matrix',
                'paper_width_mm'     => 80,
                'printable_width_mm' => 70,
                'resolution_dpi'     => 180,
                'description'        => 'Impact dot-matrix printer (80mm)',
            ],
        ];
    }

    /**
     * Resolve the active printer profile with computed layout metrics.
     *
     * Computed fields
     * ---------------
     * safe_width_mm        = printable_width_mm − 2 mm padding
     * printable_width_px   = (printable_width_mm / 25.4) * resolution_dpi
     * safe_width_px         = (safe_width_mm / 25.4) * resolution_dpi
     * canvas_width_px       = safe_width_px  (used for CSS)
     */
    public static function getPrinterConfig(): array
    {
        $printerKey  = static::get('printer_profile', 'thermal_80mm');
        $profiles    = static::getPrinterProfiles();
        $profile     = $profiles[$printerKey] ?? $profiles['thermal_80mm'];

        // --- Legacy fields kept for backward-compat Blade conditions --------
        $legacyType  = static::get('printer_type', $profile['type']);
        $legacyWidth = static::get('receipt_width', $profile['paper_width_mm'] . 'mm');

        // --- Computed layout metrics ----------------------------------------
        $safeWidthMm    = $profile['printable_width_mm'] - 2; // 2 mm total padding
        $printableWidthPx = round(($profile['printable_width_mm'] / 25.4) * $profile['resolution_dpi']);
        $safeWidthPx      = round(($safeWidthMm / 25.4) * $profile['resolution_dpi']);

        return [
            // Legacy
            'type'                => $legacyType,
            'width'               => $legacyWidth,
            'company_name'        => static::get('company_name', 'DAKOSS GLOBAL NIGERIA LTD'),

            // Full profile
            'profile_key'         => $printerKey,
            'name'                => $profile['name'],
            'paper_width_mm'      => $profile['paper_width_mm'],
            'printable_width_mm'  => $profile['printable_width_mm'],
            'resolution_dpi'      => $profile['resolution_dpi'],
            'description'         => $profile['description'],

            // Computed
            'safe_width_mm'       => $safeWidthMm,
            'printable_width_px'  => $printableWidthPx,
            'safe_width_px'       => $safeWidthPx,
            'canvas_width_px'     => $safeWidthPx,
        ];
    }
}
