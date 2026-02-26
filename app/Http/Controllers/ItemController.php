<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\Stock;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ItemController extends Controller
{
    // Item Methods
    public function index()
    {
        $items = Item::with('category', 'supplier', 'stock')
            ->orderBy('name')
            ->paginate(20);

        return view('items.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        $categories = Category::where('is_active', true)->get();
        $suppliers = Supplier::where('is_active', true)->get();

        return view('items.create', [
            'categories' => $categories,
            'suppliers' => $suppliers,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:items',
            'sku' => 'nullable|string|max:100|unique:items',
            'category_id' => 'required|exists:categories,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'description' => 'nullable|string',
            'unit_price' => 'required|numeric|min:0',
            'cost_price' => 'required|numeric|min:0',
            'reorder_level' => 'required|integer|min:1',
            'stock_level' => 'nullable|integer|min:0',
        ]);

        // Generate SKU if not provided
        if (empty($validated['sku'])) {
            $validated['sku'] = 'SKU-' . strtoupper(substr(str_replace(' ', '', $validated['name']), 0, 4)) . '-' . time();
        }

        // Map unit_price to retail_price for database compatibility
        $validated['retail_price'] = $validated['unit_price'];
        unset($validated['unit_price']);

        // Extract stock_level before creating item
        $stock_level = $validated['stock_level'] ?? 0;
        unset($validated['stock_level']);

        $item = Item::create($validated);

        // Create stock record
        Stock::create([
            'item_id' => $item->id,
            'quantity_on_hand' => $stock_level,
            'quantity_reserved' => 0,
        ]);

        return redirect()->route('items.index')
            ->with('success', 'Item created successfully!');
    }

    public function show(Item $item)
    {
        $item->load('category', 'supplier', 'stock');
        return view('items.show', ['item' => $item]);
    }

    public function edit(Item $item)
    {
        $item->load('category', 'supplier');
        $categories = Category::where('is_active', true)->get();
        $suppliers = Supplier::where('is_active', true)->get();

        return view('items.edit', [
            'item' => $item,
            'categories' => $categories,
            'suppliers' => $suppliers,
        ]);
    }

    public function update(Request $request, Item $item)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:items,name,' . $item->id,
            'sku' => 'nullable|string|max:100|unique:items,sku,' . $item->id,
            'category_id' => 'required|exists:categories,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'description' => 'nullable|string',
            'unit_price' => 'required|numeric|min:0',
            'cost_price' => 'required|numeric|min:0',
            'reorder_level' => 'required|integer|min:1',
            'stock_level' => 'nullable|integer|min:0',
        ]);

        // Extract stock_level before updating item
        $stock_level = $validated['stock_level'] ?? null;
        unset($validated['stock_level']);

        // Map unit_price to retail_price for database compatibility
        $validated['retail_price'] = $validated['unit_price'];
        unset($validated['unit_price']);

        $item->update($validated);

        // Update stock if provided
        if ($stock_level !== null) {
            if ($item->stock) {
                $item->stock->update(['quantity_on_hand' => $stock_level]);
            } else {
                Stock::create([
                    'item_id' => $item->id,
                    'quantity_on_hand' => $stock_level,
                    'quantity_reserved' => 0,
                ]);
            }
        }

        return redirect()->route('items.index')
            ->with('success', 'Item updated successfully!');
    }

    public function destroy(Item $item)
    {
        DB::beginTransaction();

        try {
            $itemName = $item->name;

            // Get all sales that contain this item
            $salesWithThisItem = Sale::whereHas('items', function ($query) use ($item) {
                $query->where('item_id', $item->id);
            })->get();

            $deletedSalesCount = $salesWithThisItem->count();

            // Delete sales that contain this item (this will also delete related sale_items due to foreign key cascades)
            foreach ($salesWithThisItem as $sale) {
                // Delete sale items first
                $sale->items()->delete();
                // Delete the sale
                $sale->delete();
            }

            // Now delete the item
            $item->delete();

            DB::commit();

            if ($deletedSalesCount > 0) {
                return redirect()->route('items.index')
                    ->with('success', "Item '{$itemName}' and {$deletedSalesCount} associated sale(s) deleted successfully!");
            } else {
                return redirect()->route('items.index')
                    ->with('success', "Item '{$itemName}' deleted successfully!");
            }

        } catch (\Exception $e) {
            DB::rollback();

            return redirect()->route('items.index')
                ->with('error', "Failed to delete item '{$item->name}': " . $e->getMessage());
        }
    }

    // Category Methods
    public function categories()
    {
        $categories = Category::withCount('items')
            ->orderBy('name')
            ->paginate(20);

        return view('categories.index', [
            'categories' => $categories,
        ]);
    }

    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        // Auto-generate code from name
        $validated['code'] = strtoupper(substr(str_replace(' ', '', $validated['name']), 0, 4)) . '-' . time();

        Category::create($validated);

        return redirect()->route('categories.index')
            ->with('success', 'Category created successfully!');
    }

    public function updateCategory(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        // Auto-generate code if empty
        if (empty($category->code)) {
            $validated['code'] = strtoupper(substr(str_replace(' ', '', $validated['name']), 0, 4)) . '-' . time();
        }

        $category->update($validated);

        return redirect()->route('categories.index')
            ->with('success', 'Category updated successfully!');
    }

    public function destroyCategory(Category $category)
    {
        // Check if category has items
        $itemsCount = $category->items()->count();

        if ($itemsCount > 0) {
            return redirect()->route('categories.index')
                ->with('error', "Cannot delete category '{$category->name}' because it has {$itemsCount} item(s) assigned to it. Please reassign or delete the items first.");
        }

        $categoryName = $category->name;
        $category->delete();

        return redirect()->route('categories.index')
            ->with('success', "Category '{$categoryName}' deleted successfully!");
    }

    // Supplier Methods
    public function suppliers()
    {
        $suppliers = Supplier::withCount('items')
            ->orderBy('name')
            ->paginate(20);

        return view('suppliers.index', [
            'suppliers' => $suppliers,
        ]);
    }

    public function storeSupplier(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:suppliers',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:suppliers',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        // Auto-generate code from name
        $validated['code'] = strtoupper(substr(str_replace(' ', '', $validated['name']), 0, 4)) . '-' . time();

        Supplier::create($validated);

        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier created successfully!');
    }

    public function updateSupplier(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:suppliers,name,' . $supplier->id,
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:suppliers,email,' . $supplier->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        // Auto-generate code if empty
        if (empty($supplier->code)) {
            $validated['code'] = strtoupper(substr(str_replace(' ', '', $validated['name']), 0, 4)) . '-' . time();
        }

        $supplier->update($validated);

        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier updated successfully!');
    }

    public function destroySupplier(Supplier $supplier)
    {
        $supplier->delete();

        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier deleted successfully!');
    }
}
