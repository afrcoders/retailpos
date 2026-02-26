<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Category;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ItemController extends Controller
{
    protected $auditService;

    public function __construct(AuditService $auditService)
    {
        $this->auditService = $auditService;
        $this->middleware('auth:sanctum');
    }

    /**
     * Get all items with filters
     */
    public function index(Request $request)
    {
        $query = Item::with(['category', 'supplier', 'stock']);

        // Filter by category
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        // Search by name, sku, or barcode
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        // Get low stock items
        if ($request->has('low_stock') && $request->low_stock) {
            $query->whereHas('stock', function ($q) {
                $q->whereRaw('quantity_on_hand <= items.reorder_level');
            });
        }

        // Pagination
        $per_page = $request->input('per_page', 15);
        $items = $query->paginate($per_page);

        return response()->json($items);
    }

    /**
     * Get single item
     */
    public function show(Item $item)
    {
        return response()->json($item->load(['category', 'supplier', 'stock', 'stockTransactions']));
    }

    /**
     * Create new item
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'sku' => 'required|unique:items',
            'name' => 'required|string',
            'description' => 'nullable|string',
            'barcode' => 'nullable|unique:items',
            'category_id' => 'required|exists:categories,id',
            'item_type' => 'required|in:standard,kit,temporary',
            'stock_type' => 'required|in:stock,non-stock',
            'cost_price' => 'required|numeric|min:0',
            'retail_price' => 'required|numeric|min:0',
            'wholesale_price' => 'nullable|numeric|min:0',
            'reorder_level' => 'required|integer|min:0',
            'reorder_quantity' => 'required|integer|min:0',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'size' => 'nullable|string',
            'colour' => 'nullable|string',
            'brand' => 'nullable|string',
            'expiry_date' => 'nullable|date',
        ]);

        $item = Item::create($validated);

        // Create stock record for stock items
        if ($item->stock_type === 'stock') {
            $item->stock()->create(['quantity_on_hand' => 0]);
        }

        // Log the action
        $this->auditService->log('create', 'Item', $item->id, null, $item->toArray(), auth()->id());

        return response()->json($item, 201);
    }

    /**
     * Update item
     */
    public function update(Request $request, Item $item)
    {
        $validated = $request->validate([
            'name' => 'string',
            'description' => 'nullable|string',
            'barcode' => Rule::unique('items')->ignore($item->id),
            'category_id' => 'exists:categories,id',
            'cost_price' => 'numeric|min:0',
            'retail_price' => 'numeric|min:0',
            'wholesale_price' => 'nullable|numeric|min:0',
            'reorder_level' => 'integer|min:0',
            'reorder_quantity' => 'integer|min:0',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'size' => 'nullable|string',
            'colour' => 'nullable|string',
            'brand' => 'nullable|string',
            'expiry_date' => 'nullable|date',
            'is_active' => 'boolean',
        ]);

        $oldValues = $item->toArray();
        $item->update($validated);

        // Log the action
        $this->auditService->log('update', 'Item', $item->id, $oldValues, $item->toArray(), auth()->id());

        return response()->json($item);
    }

    /**
     * Delete item
     */
    public function destroy(Item $item)
    {
        // Check if item has stock transactions
        if ($item->stockTransactions()->count() > 0) {
            return response()->json(['message' => 'Cannot delete item with stock transactions'], 400);
        }

        $itemData = $item->toArray();
        $item->delete();

        // Log the action
        $this->auditService->log('delete', 'Item', $item->id, $itemData, null, auth()->id());

        return response()->json(null, 204);
    }

    /**
     * Search by barcode
     */
    public function searchByBarcode(Request $request)
    {
        $request->validate(['barcode' => 'required|string']);

        $item = Item::where('barcode', $request->barcode)
            ->where('is_active', true)
            ->with(['category', 'supplier', 'stock'])
            ->first();

        if (!$item) {
            return response()->json(['message' => 'Item not found'], 404);
        }

        return response()->json($item);
    }

    /**
     * Get low stock items
     */
    public function lowStock()
    {
        $items = Item::with(['category', 'stock'])
            ->where('is_active', true)
            ->where('stock_type', 'stock')
            ->get()
            ->filter(function ($item) {
                return $item->stock && $item->stock->quantity_available <= $item->reorder_level;
            });

        return response()->json($items->values());
    }
}
