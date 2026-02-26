<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    use HasFactory;

    protected $table = 'stock';

    protected $fillable = [
        'item_id',
        'quantity_on_hand',
        'quantity_reserved',
        'last_counted_at',
    ];

    protected $casts = [
        'last_counted_at' => 'datetime',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function getAvailableQuantity()
    {
        return $this->quantity_on_hand - $this->quantity_reserved;
    }

    public function isLow()
    {
        return $this->item && $this->getAvailableQuantity() <= $this->item->reorder_level;
    }

    public function canSell($quantity)
    {
        return $this->getAvailableQuantity() >= $quantity;
    }
}
