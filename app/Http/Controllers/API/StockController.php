<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Stock;
use App\Models\StockTransaction;
use App\Services\StockService;
use App\Services\AuditService;
use Illuminate\Http\Request;

class StockController extends Controller
{
    protected $stockService;
    protected $auditService;

    public function __construct(StockService $stockService, AuditService $auditService)
    {
        $this->stockService = $stockService;
        $this->auditService = $auditService;
        $this->middleware('auth:sanctum');
    }

    /**
     * Get stock levels for all items
     */
    public function index(Request $request)
    {
        $query = Stock::with(['item.category', 'item.supplier']);

        if ($request->has('low_stock')) {
            $query->whereHas('item', function ($q) {
                $q->whereRaw('quantity_on_hand <= reorder_level');
            });
        }

        if ($request->has('category_id')) {
            $query->whereHas('item', function ($q) use ($request) {
                $q->where('category_id', $request->category_id);
            });
        }

        return response()->json($query->paginate(15));
    }

    /**
     * Get stock for specific item
     */
    public function show(Item $item)
    {
        $stock = $item->stock ?? Stock::create(['item_id' => $item->id]);

        return response()->json($stock->load('item'));
    }

    /**
     * Adjust stock (manual adjustment)
     */
    public function adjust(Request $request, Item $item)
    {
        $validated = $request->validate([
            'quantity_adjustment' => 'required|integer',
            'reason' => 'required|string|in:count,loss,damage,miscount',
            'notes' => 'nullable|string',
        ]);

        try {
            $stock = $this->stockService->adjustStock(
                $item,
                $validated['quantity_adjustment'],
                'adjustment',
                $validated['notes'] ?? "Stock adjustment - {$validated['reason']}",
                auth()->id()
            );

            $this->auditService->log('adjust_stock', 'Stock', $stock->id, null, $stock->toArray(), auth()->id());

            return response()->json($stock);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Record stock receipt (from purchase order)
     */
    public function receive(Request $request, Item $item)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'reference_number' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        try {
            $stock = $this->stockService->recordPurchase(
                $item,
                $validated['quantity'],
                $validated['supplier_id'],
                auth()->id(),
                $validated['notes'] ?? "Stock received - {$validated['reference_number']}"
            );

            $this->auditService->log('receive_stock', 'Stock', $stock->id, null, $stock->toArray(), auth()->id());

            return response()->json($stock);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Get stock transactions
     */
    public function transactions(Request $request, Item $item)
    {
        $query = $item->stockTransactions();

        if ($request->has('transaction_type')) {
            $query->where('transaction_type', $request->transaction_type);
        }

        if ($request->has('start_date')) {
            $query->whereDate('transaction_date', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->whereDate('transaction_date', '<=', $request->end_date);
        }

        return response()->json($query->orderByDesc('transaction_date')->paginate(20));
    }

    /**
     * Get low stock items
     */
    public function lowStock()
    {
        $items = $this->stockService->getLowStockItems();

        return response()->json($items->values());
    }

    /**
     * Get stock report
     */
    public function report(Request $request)
    {
        $report = $this->stockService->getStockReport($request->input('category_id'));

        return response()->json($report);
    }

    /**
     * Physical inventory count
     */
    public function count(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.counted_quantity' => 'required|integer|min:0',
        ]);

        $results = [];

        foreach ($validated['items'] as $itemData) {
            $item = Item::findOrFail($itemData['item_id']);
            $stock = $item->stock;
            $variance = $itemData['counted_quantity'] - ($stock?->quantity_on_hand ?? 0);

            if ($variance != 0) {
                $this->stockService->adjustStock(
                    $item,
                    $variance,
                    'adjustment',
                    "Physical count adjustment - Counted: {$itemData['counted_quantity']}, System: {$stock?->quantity_on_hand}",
                    auth()->id()
                );
            }

            $stock->update(['last_counted_at' => now()]);

            $results[] = [
                'item_id' => $item->id,
                'sku' => $item->sku,
                'name' => $item->name,
                'system_quantity' => $stock?->quantity_on_hand ?? 0,
                'counted_quantity' => $itemData['counted_quantity'],
                'variance' => $variance,
            ];
        }

        return response()->json($results);
    }
}
