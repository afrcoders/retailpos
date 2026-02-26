<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\StockTransaction;
use Illuminate\Http\Request;

class StockController extends Controller
{
    public function index()
    {
        $items = Item::with('stock')
            ->orderBy('name')
            ->paginate(20);

        return view('stock.index', [
            'items' => $items,
        ]);
    }

    public function movements(Request $request)
    {
        $query = StockTransaction::with('item', 'user');

        if ($request->has('from_date') && $request->from_date) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->has('to_date') && $request->to_date) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }

        $movements = $query->orderBy('created_at', 'desc')
            ->paginate(20);

        $items = Item::where('is_active', true)->get();

        return view('stock.movements', [
            'movements' => $movements,
            'items' => $items,
        ]);
    }

    public function adjust(Request $request)
    {
        $validated = $request->validate([
            'item_id' => 'required|exists:items,id',
            'quantity' => 'required|integer',
            'type' => 'required|in:in,out,return,adjustment',
            'notes' => 'nullable|string',
        ]);

        $item = Item::findOrFail($validated['item_id']);
        $stock = $item->stock;

        $oldQuantity = $stock->quantity_on_hand;
        $newQuantity = $oldQuantity + ($validated['type'] === 'out' ? -$validated['quantity'] : $validated['quantity']);

        $stock->update(['quantity_on_hand' => max(0, $newQuantity)]);

        StockTransaction::create([
            'item_id' => $validated['item_id'],
            'type' => $validated['type'],
            'quantity_change' => $validated['type'] === 'out' ? -$validated['quantity'] : $validated['quantity'],
            'old_quantity' => $oldQuantity,
            'new_quantity' => max(0, $newQuantity),
            'notes' => $validated['notes'] ?? null,
            'user_id' => auth()->id(),
        ]);

        return redirect()->route('stock.movements')
            ->with('success', 'Stock adjusted successfully!');
    }}
