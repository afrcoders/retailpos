<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\Item;
use App\Models\Stock;
use App\Models\StockTransaction;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * Sales Summary Report
     */
    public function salesSummary(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth());
        $endDate = $request->input('end_date', now());

        $sales = Sale::where('status', 'completed')
            ->whereDate('sale_date', '>=', $startDate)
            ->whereDate('sale_date', '<=', $endDate)
            ->get();

        return response()->json([
            'period' => "{$startDate->format('d/m/Y')} - {$endDate->format('d/m/Y')}",
            'total_sales' => $sales->count(),
            'total_revenue' => $sales->sum('total'),
            'total_subtotal' => $sales->sum('subtotal'),
            'total_discount' => $sales->sum('discount_amount'),
            'total_vat_collected' => $sales->sum('vat_amount'),
            'average_sale' => $sales->count() > 0 ? round($sales->avg('total'), 2) : 0,
            'payment_methods' => $sales->groupBy('payment_method')->map(function ($group) {
                return [
                    'method' => $group->first()->payment_method,
                    'count' => $group->count(),
                    'total' => $group->sum('total'),
                ];
            })->values(),
        ]);
    }

    /**
     * Sales by Item Report
     */
    public function salesByItem(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth());
        $endDate = $request->input('end_date', now());

        $sales = Sale::where('status', 'completed')
            ->whereDate('sale_date', '>=', $startDate)
            ->whereDate('sale_date', '<=', $endDate)
            ->with('items.item')
            ->get();

        $itemSales = [];

        foreach ($sales as $sale) {
            foreach ($sale->items as $saleItem) {
                $itemId = $saleItem->item_id;

                if (!isset($itemSales[$itemId])) {
                    $itemSales[$itemId] = [
                        'item_id' => $saleItem->item_id,
                        'sku' => $saleItem->item->sku,
                        'name' => $saleItem->item->name,
                        'category' => $saleItem->item->category?->name,
                        'quantity_sold' => 0,
                        'total_revenue' => 0,
                        'total_cost' => 0,
                        'profit' => 0,
                    ];
                }

                $itemSales[$itemId]['quantity_sold'] += $saleItem->quantity;
                $itemSales[$itemId]['total_revenue'] += $saleItem->line_total;
                $itemSales[$itemId]['total_cost'] += ($saleItem->quantity * $saleItem->item->cost_price);
            }
        }

        // Calculate profit
        foreach ($itemSales as &$item) {
            $item['profit'] = $item['total_revenue'] - $item['total_cost'];
            $item['profit_margin'] = $item['total_cost'] > 0 ? round(($item['profit'] / $item['total_cost']) * 100, 2) : 0;
        }

        // Sort by quantity sold (best sellers)
        usort($itemSales, function ($a, $b) {
            return $b['quantity_sold'] <=> $a['quantity_sold'];
        });

        return response()->json([
            'period' => "{$startDate->format('d/m/Y')} - {$endDate->format('d/m/Y')}",
            'items' => array_values($itemSales),
        ]);
    }

    /**
     * Inventory On-Hand Report
     */
    public function inventoryOnHand(Request $request)
    {
        $categoryId = $request->input('category_id');

        $query = Item::with(['category', 'stock', 'supplier'])
            ->where('is_active', true)
            ->where('stock_type', 'stock');

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        $items = $query->get()->map(function ($item) {
            $stock = $item->stock;
            $stockValue = ($stock?->quantity_on_hand ?? 0) * $item->cost_price;

            return [
                'item_id' => $item->id,
                'sku' => $item->sku,
                'name' => $item->name,
                'category' => $item->category?->name,
                'quantity_on_hand' => $stock?->quantity_on_hand ?? 0,
                'quantity_reserved' => $stock?->quantity_reserved ?? 0,
                'quantity_available' => $stock?->getAvailableQuantity() ?? 0,
                'reorder_level' => $item->reorder_level,
                'cost_price' => $item->cost_price,
                'value_at_cost' => $stockValue,
                'is_low_stock' => $item->isLowStock(),
                'supplier' => $item->supplier?->name,
            ];
        });

        $totalValue = $items->sum('value_at_cost');

        return response()->json([
            'report_date' => now()->format('d/m/Y H:i:s'),
            'total_items' => $items->count(),
            'total_quantity' => $items->sum('quantity_on_hand'),
            'total_value_at_cost' => round($totalValue, 2),
            'low_stock_count' => $items->where('is_low_stock', true)->count(),
            'items' => $items->values(),
        ]);
    }

    /**
     * Low Stock / Reorder Report
     */
    public function lowStockReport()
    {
        $items = Item::with(['category', 'stock', 'supplier'])
            ->where('is_active', true)
            ->where('stock_type', 'stock')
            ->get()
            ->filter(function ($item) {
                return $item->stock && $item->stock->getAvailableQuantity() <= $item->reorder_level;
            })
            ->map(function ($item) {
                $stock = $item->stock;

                return [
                    'item_id' => $item->id,
                    'sku' => $item->sku,
                    'name' => $item->name,
                    'category' => $item->category?->name,
                    'quantity_available' => $stock->getAvailableQuantity(),
                    'reorder_level' => $item->reorder_level,
                    'reorder_quantity' => $item->reorder_quantity,
                    'supplier' => $item->supplier?->name,
                    'supplier_contact' => $item->supplier?->phone,
                    'cost_per_unit' => $item->cost_price,
                    'estimated_reorder_cost' => $item->reorder_quantity * $item->cost_price,
                ];
            });

        return response()->json([
            'report_date' => now()->format('d/m/Y H:i:s'),
            'total_low_stock_items' => $items->count(),
            'total_estimated_reorder_value' => $items->sum('estimated_reorder_cost'),
            'items' => $items->values(),
        ]);
    }

    /**
     * Profit Report
     */
    public function profitReport(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth());
        $endDate = $request->input('end_date', now());

        $sales = Sale::where('status', 'completed')
            ->whereDate('sale_date', '>=', $startDate)
            ->whereDate('sale_date', '<=', $endDate)
            ->with('items.item')
            ->get();

        $totalRevenue = $sales->sum('total');
        $totalCost = 0;
        $totalVAT = $sales->sum('vat_amount');
        $totalDiscount = $sales->sum('discount_amount');

        foreach ($sales as $sale) {
            foreach ($sale->items as $saleItem) {
                $totalCost += ($saleItem->quantity * $saleItem->item->cost_price);
            }
        }

        $grossProfit = $totalRevenue - $totalCost;
        $profitMargin = $totalCost > 0 ? round(($grossProfit / $totalCost) * 100, 2) : 0;

        return response()->json([
            'period' => "{$startDate->format('d/m/Y')} - {$endDate->format('d/m/Y')}",
            'total_sales' => $sales->count(),
            'total_revenue' => round($totalRevenue, 2),
            'total_cost' => round($totalCost, 2),
            'total_discount_given' => round($totalDiscount, 2),
            'total_vat_collected' => round($totalVAT, 2),
            'gross_profit' => round($grossProfit, 2),
            'profit_margin_percentage' => $profitMargin,
        ]);
    }

    /**
     * Tax Report (VAT collected)
     */
    public function taxReport(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth());
        $endDate = $request->input('end_date', now());

        $sales = Sale::where('status', 'completed')
            ->whereDate('sale_date', '>=', $startDate)
            ->whereDate('sale_date', '<=', $endDate)
            ->get();

        $daily = $sales->groupBy(function ($sale) {
            return $sale->sale_date->format('Y-m-d');
        })->map(function ($daySales) {
            return [
                'date' => $daySales->first()->sale_date->format('d/m/Y'),
                'sales_count' => $daySales->count(),
                'total_sales' => round($daySales->sum('subtotal'), 2),
                'total_vat' => round($daySales->sum('vat_amount'), 2),
            ];
        });

        $totalSales = $sales->sum('subtotal');
        $totalVAT = $sales->sum('vat_amount');

        return response()->json([
            'period' => "{$startDate->format('d/m/Y')} - {$endDate->format('d/m/Y')}",
            'total_sales' => round($totalSales, 2),
            'total_vat_collected' => round($totalVAT, 2),
            'vat_percentage' => 7.5,
            'daily_breakdown' => $daily->values(),
        ]);
    }

    /**
     * Stock Movement Report
     */
    public function stockMovement(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth());
        $endDate = $request->input('end_date', now());
        $itemId = $request->input('item_id');

        $query = StockTransaction::with('item', 'user')
            ->whereDate('transaction_date', '>=', $startDate)
            ->whereDate('transaction_date', '<=', $endDate);

        if ($itemId) {
            $query->where('item_id', $itemId);
        }

        $transactions = $query->orderBy('transaction_date')->get();

        $summary = [
            'purchase' => $transactions->where('transaction_type', 'purchase')->sum('quantity'),
            'sale' => abs($transactions->where('transaction_type', 'sale')->sum('quantity')),
            'adjustment' => $transactions->where('transaction_type', 'adjustment')->sum('quantity'),
            'return' => $transactions->where('transaction_type', 'return')->sum('quantity'),
        ];

        return response()->json([
            'period' => "{$startDate->format('d/m/Y')} - {$endDate->format('d/m/Y')}",
            'summary' => $summary,
            'transactions' => $transactions->map(function ($trans) {
                return [
                    'date' => $trans->transaction_date->format('d/m/Y H:i:s'),
                    'item' => $trans->item->name,
                    'type' => $trans->transaction_type,
                    'quantity' => $trans->quantity,
                    'before' => $trans->quantity_before,
                    'after' => $trans->quantity_after,
                    'user' => $trans->user->name,
                ];
            }),
        ]);
    }
}
