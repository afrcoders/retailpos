<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Category;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SalesController extends Controller
{
    /**
     * Handle AJAX sale creation from POS page (web route).
     */
    public function storeAjax(\Illuminate\Http\Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'payment_method' => 'required|in:cash,card,transfer,mixed',
            'customer_id' => 'nullable',
            'subtotal' => 'required|numeric',
            'tax' => 'required|numeric',
            'total' => 'required|numeric',
        ]);

        try {
            DB::beginTransaction();

            // Create the sale record
            $sale = new Sale();
            $sale->sale_number = $sale->generateSaleNumber();
            $sale->user_id = Auth::id();
            $sale->subtotal = $validated['subtotal'];
            $sale->discount_amount = 0;
            $sale->discount_percentage = 0;
            $sale->vat_amount = $validated['tax'];
            $sale->vat_percentage = 7.5;
            $sale->total = $validated['total'];
            $sale->amount_paid = $validated['total']; // Assuming full payment
            $sale->change = 0;
            $sale->payment_method = $validated['payment_method'];
            $sale->status = 'completed';
            $sale->sale_date = now();
            $sale->save();

            // Create sale items
            foreach ($validated['items'] as $itemData) {
                $item = Item::find($itemData['id']);

                $saleItem = new SaleItem();
                $saleItem->sale_id = $sale->id;
                $saleItem->item_id = $itemData['id'];
                $saleItem->quantity = $itemData['quantity'];
                $saleItem->unit_price = $item->retail_price;
                $saleItem->discount_amount = 0;
                $saleItem->discount_percentage = 0;
                $saleItem->vat_amount = ($item->retail_price * $itemData['quantity']) * 0.075;
                $saleItem->line_total = $item->retail_price * $itemData['quantity'];
                $saleItem->save();

                // Update stock (reduce quantity)
                if ($item->stock) {
                    $item->stock->quantity_on_hand -= $itemData['quantity'];
                    $item->stock->save();
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'receipt_number' => $sale->sale_number,
                'sale_id' => $sale->id,
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }
    public function pos()
    {
        $items = Item::with('stock', 'category')
            ->where('is_active', true)
            ->get();

        $categories = Category::where('is_active', true)->get();

        // Fetch all customers (simulate for now if no table exists)
        if (class_exists('App\\Models\\Customer')) {
            $customers = \App\Models\Customer::where('is_active', true)->get();
        } else {
            $customers = collect();
        }

        return view('sales.pos', [
            'items' => $items,
            'categories' => $categories,
            'customers' => $customers,
            'printerConfig' => Setting::getPrinterConfig(),
        ]);
    }

    public function history()
    {
        $sales = Sale::with('items', 'user')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('sales.history', [
            'sales' => $sales,
        ]);
    }

    public function destroy(Sale $sale)
    {
        try {
            DB::beginTransaction();

            // Store sale information for response
            $saleNumber = $sale->sale_number;

            // Delete related sale items first (due to foreign key constraints)
            $sale->items()->delete();

            // Delete the sale
            $sale->delete();

            DB::commit();

            // For AJAX requests, return JSON success
            if (request()->expectsJson() || request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Sale #{$saleNumber} deleted successfully"
                ]);
            }

            // For regular requests, redirect with success message
            return redirect()->route('sales.history')
                ->with('success', "Sale #{$saleNumber} deleted successfully!");

        } catch (\Exception $e) {
            DB::rollback();

            // For AJAX requests, return JSON error
            if (request()->expectsJson() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete sale: ' . $e->getMessage()
                ], 500);
            }

            // For regular requests, redirect with error
            return redirect()->route('sales.history')
                ->withErrors(['error' => 'Failed to delete sale: ' . $e->getMessage()]);
        }
    }
}
