<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'sku',
        'name',
        'description',
        'barcode',
        'category_id',
        'item_type',
        'stock_type',
        'cost_price',
        'retail_price',
        'wholesale_price',
        'reorder_level',
        'reorder_quantity',
        'image_path',
        'size',
        'colour',
        'brand',
        'expiry_date',
        'supplier_id',
        'is_active',
    ];

    protected $casts = [
        'cost_price' => 'decimal:2',
        'retail_price' => 'decimal:2',
        'wholesale_price' => 'decimal:2',
        'expiry_date' => 'date',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function stock()
    {
        return $this->hasOne(Stock::class);
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function stockTransactions()
    {
        return $this->hasMany(StockTransaction::class);
    }

    public function purchaseOrderItems()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    // Helper methods
    public function isExpired()
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    public function isLowStock()
    {
        return $this->stock && $this->stock->quantity_available <= $this->reorder_level;
    }

    public function getProfit()
    {
        return $this->retail_price - $this->cost_price;
    }

    public function getProfitMargin()
    {
        if ($this->cost_price == 0) return 0;
        return (($this->retail_price - $this->cost_price) / $this->cost_price) * 100;
    }

    public function generateBarcode()
    {
        if (!$this->barcode) {
            $this->barcode = 'BAR-' . strtoupper($this->sku) . '-' . $this->id;
        }
        return $this->barcode;
    }
}
