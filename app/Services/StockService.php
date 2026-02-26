<?php

namespace App\Services;

use App\Models\Item;
use App\Models\Stock;
use App\Models\StockTransaction;
use Carbon\Carbon;

class StockService
{
    const VAT_RATE = 0.075; // 7.5% Nigerian VAT

    /**
     * Adjust stock for an item
     */
    public function adjustStock(Item $item, int $quantity, string $type, string $notes = null, ?int $userId = null, ?int $supplierId = null)
    {
        $stock = $item->stock ?? Stock::create(['item_id' => $item->id]);

        $quantityBefore = $stock->quantity_on_hand;
        $quantityAfter = $quantityBefore + $quantity;

        // Create transaction record
        StockTransaction::create([
            'item_id' => $item->id,
            'transaction_type' => $type,
            'quantity' => $quantity,
            'quantity_before' => $quantityBefore,
            'quantity_after' => $quantityAfter,
            'notes' => $notes,
            'user_id' => $userId,
            'supplier_id' => $supplierId,
            'transaction_date' => now(),
        ]);

        // Update stock
        $stock->update(['quantity_on_hand' => $quantityAfter]);

        return $stock;
    }

    /**
     * Record a purchase transaction
     */
    public function recordPurchase(Item $item, int $quantity, ?int $supplierId = null, ?int $userId = null, string $notes = null)
    {
        return $this->adjustStock($item, $quantity, 'purchase', $notes, $userId, $supplierId);
    }

    /**
     * Record a sale transaction
     */
    public function recordSale(Item $item, int $quantity, ?int $userId = null, string $notes = null)
    {
        $stock = $item->stock;
        if (!$stock || $stock->getAvailableQuantity() < $quantity) {
            throw new \Exception("Insufficient stock for item: {$item->name}");
        }

        return $this->adjustStock($item, -$quantity, 'sale', $notes, $userId);
    }

    /**
     * Check for low stock items
     */
    public function getLowStockItems()
    {
        return Item::with('stock')
            ->where('is_active', true)
            ->where('stock_type', 'stock')
            ->get()
            ->filter(function ($item) {
                return $item->isLowStock();
            });
    }

    /**
     * Get stock report
     */
    public function getStockReport($categoryId = null)
    {
        $query = Item::with(['category', 'stock', 'supplier'])
            ->where('is_active', true)
            ->where('stock_type', 'stock');

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        return $query->get()->map(function ($item) {
            return [
                'id' => $item->id,
                'sku' => $item->sku,
                'name' => $item->name,
                'category' => $item->category?->name,
                'quantity_on_hand' => $item->stock?->quantity_on_hand ?? 0,
                'quantity_available' => $item->stock?->getAvailableQuantity() ?? 0,
                'reorder_level' => $item->reorder_level,
                'cost_price' => $item->cost_price,
                'retail_price' => $item->retail_price,
                'value_at_cost' => ($item->stock?->quantity_on_hand ?? 0) * $item->cost_price,
                'is_low_stock' => $item->isLowStock(),
                'expiry_date' => $item->expiry_date,
                'is_expired' => $item->isExpired(),
            ];
        });
    }
}
