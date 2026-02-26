<?php

namespace App\Services;

use App\Models\Item;
use App\Models\Sale;
use App\Models\SaleItem;
use Carbon\Carbon;

class SalesService
{
    const VAT_RATE = 0.075; // 7.5% Nigerian VAT
    const CURRENCY = '₦';

    /**
     * Calculate VAT amount
     */
    public function calculateVAT($amount, $vatRate = null)
    {
        $rate = $vatRate ?? self::VAT_RATE;
        return round($amount * $rate, 2);
    }

    /**
     * Create a sale with items
     */
    public function createSale($userId, array $items, $customerName = null, $customerPhone = null, $customerEmail = null, array $payment = [])
    {
        try {
            $sale = new Sale();
            $sale->sale_number = $sale->generateSaleNumber();
            $sale->user_id = $userId;
            $sale->customer_name = $customerName;
            $sale->customer_phone = $customerPhone;
            $sale->customer_email = $customerEmail;
            $sale->payment_method = $payment['method'] ?? 'cash';
            $sale->vat_percentage = $payment['vat_percentage'] ?? self::VAT_RATE * 100;
            $sale->discount_percentage = $payment['discount_percentage'] ?? 0;
            $sale->sale_date = now();

            // Calculate totals
            $subtotal = 0;
            $totalVAT = 0;

            foreach ($items as $itemData) {
                $item = Item::findOrFail($itemData['item_id']);
                $quantity = $itemData['quantity'];
                $unitPrice = $itemData['unit_price'] ?? $item->retail_price;

                // Create sale item
                $saleItem = new SaleItem([
                    'item_id' => $item->id,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'discount_percentage' => $itemData['discount_percentage'] ?? 0,
                    'discount_amount' => 0,
                    'vat_amount' => 0,
                    'line_total' => 0,
                ]);

                // Calculate item totals
                $itemTotal = $quantity * $unitPrice;
                $itemDiscount = round($itemTotal * ($saleItem->discount_percentage / 100), 2);
                $itemAfterDiscount = $itemTotal - $itemDiscount;
                $itemVAT = $this->calculateVAT($itemAfterDiscount, $sale->vat_percentage / 100);
                $saleItem->discount_amount = $itemDiscount;
                $saleItem->vat_amount = $itemVAT;
                $saleItem->line_total = $itemAfterDiscount + $itemVAT;

                $sale->items()->save($saleItem);

                $subtotal += $itemTotal;
                $totalVAT += $itemVAT;
            }

            $sale->subtotal = $subtotal;
            $sale->discount_amount = round($subtotal * ($sale->discount_percentage / 100), 2);
            $sale->vat_amount = $totalVAT;
            $sale->total = $subtotal - $sale->discount_amount + $totalVAT;
            $sale->amount_paid = $payment['amount_paid'] ?? $sale->total;
            $sale->change = max(0, $sale->amount_paid - $sale->total);
            $sale->status = 'completed';
            $sale->save();

            return $sale;
        } catch (\Exception $e) {
            throw new \Exception("Failed to create sale: " . $e->getMessage());
        }
    }

    /**
     * Generate receipt for a sale
     */
    public function generateReceipt(Sale $sale)
    {
        $receipt = [
            'header' => "POINT OF SALE RECEIPT\n",
            'divider' => str_repeat("-", 50),
            'sale_number' => "Sale #: {$sale->sale_number}",
            'date' => "Date: " . $sale->sale_date->format('d/m/Y H:i:s'),
            'cashier' => "Cashier: {$sale->user->name}",
            'customer' => $sale->customer_name ? "Customer: {$sale->customer_name}" : "",
            'items' => $this->formatSaleItems($sale),
            'summary' => $this->formatSaleSummary($sale),
            'payment' => "Payment Method: " . ucfirst($sale->payment_method),
            'footer' => "Thank you for your patronage!",
        ];

        return implode("\n", array_filter($receipt));
    }

    private function formatSaleItems(Sale $sale)
    {
        $items = "ITEMS:\n";
        foreach ($sale->items as $saleItem) {
            $items .= sprintf(
                "%s x%d @ %s%.2f = %s%.2f\n",
                substr($saleItem->item->name, 0, 25),
                $saleItem->quantity,
                self::CURRENCY,
                $saleItem->unit_price,
                self::CURRENCY,
                $saleItem->line_total
            );
        }
        return $items;
    }

    private function formatSaleSummary(Sale $sale)
    {
        return sprintf(
            "Subtotal: %s%.2f\nDiscount: -%s%.2f\nVAT (%.1f%%): +%s%.2f\nTOTAL: %s%.2f\n\nAmount Paid: %s%.2f\nChange: %s%.2f",
            self::CURRENCY, $sale->subtotal,
            self::CURRENCY, $sale->discount_amount,
            $sale->vat_percentage, self::CURRENCY, $sale->vat_amount,
            self::CURRENCY, $sale->total,
            self::CURRENCY, $sale->amount_paid,
            self::CURRENCY, $sale->change
        );
    }

    /**
     * Get sales summary report
     */
    public function getSalesSummary($startDate = null, $endDate = null, $userId = null)
    {
        $query = Sale::where('status', 'completed');

        if ($startDate) {
            $query->whereDate('sale_date', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('sale_date', '<=', $endDate);
        }
        if ($userId) {
            $query->where('user_id', $userId);
        }

        $sales = $query->get();

        return [
            'total_sales' => $sales->count(),
            'total_revenue' => $sales->sum('total'),
            'total_vat_collected' => $sales->sum('vat_amount'),
            'total_discount_given' => $sales->sum('discount_amount'),
            'average_sale' => $sales->count() > 0 ? $sales->avg('total') : 0,
        ];
    }
}
