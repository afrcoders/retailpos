<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_number',
        'user_id',
        'customer_name',
        'customer_phone',
        'customer_email',
        'subtotal',
        'discount_amount',
        'discount_percentage',
        'vat_amount',
        'vat_percentage',
        'total',
        'amount_paid',
        'change',
        'payment_method',
        'status',
        'notes',
        'sale_date',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'vat_amount' => 'decimal:2',
        'vat_percentage' => 'decimal:2',
        'total' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'change' => 'decimal:2',
        'sale_date' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function generateSaleNumber()
    {
        $date = now()->format('Ymd');
        $count = self::whereDate('created_at', today())->count() + 1;
        return 'SAL-' . $date . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }
}
