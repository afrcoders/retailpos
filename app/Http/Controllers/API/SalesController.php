<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Services\SalesService;
use App\Services\StockService;
use App\Services\AuditService;
use Illuminate\Http\Request;

class SalesController extends Controller
{
    protected $salesService;
    protected $stockService;
    protected $auditService;

    public function __construct(SalesService $salesService, StockService $stockService, AuditService $auditService)
    {
        $this->salesService = $salesService;
        $this->stockService = $stockService;
        $this->auditService = $auditService;
        $this->middleware('auth:sanctum');
    }

    /**
     * Get sales with filters
     */
    public function index(Request $request)
    {
        $query = Sale::with(['user', 'items.item']);

        if ($request->has('start_date')) {
            $query->whereDate('sale_date', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->whereDate('sale_date', '<=', $request->end_date);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        return response()->json($query->orderByDesc('sale_date')->paginate(15));
    }

    /**
     * Get single sale
     */
    public function show(Sale $sale)
    {
        return response()->json($sale->load(['user', 'items.item']));
    }

    /**
     * Create a new sale
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'nullable|numeric|min:0',
            'items.*.discount_percentage' => 'nullable|numeric|min:0|max:100',
            'customer_name' => 'nullable|string',
            'customer_phone' => 'nullable|string',
            'customer_email' => 'nullable|email',
            'payment_method' => 'required|in:cash,card,transfer,mixed',
            'amount_paid' => 'required|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'vat_percentage' => 'nullable|numeric|min:0|max:100',
        ]);

        try {
            $payment = [
                'method' => $validated['payment_method'],
                'amount_paid' => $validated['amount_paid'],
                'discount_percentage' => $validated['discount_percentage'] ?? 0,
                'vat_percentage' => $validated['vat_percentage'] ?? 7.5,
            ];

            $sale = $this->salesService->createSale(
                auth()->id(),
                $validated['items'],
                $validated['customer_name'] ?? null,
                $validated['customer_phone'] ?? null,
                $validated['customer_email'] ?? null,
                $payment
            );

            // Adjust stock for each item
            foreach ($sale->items as $saleItem) {
                $this->stockService->recordSale($saleItem->item, $saleItem->quantity, auth()->id(), "Sale #{$sale->sale_number}");
            }

            // Log the action
            $this->auditService->log('create_sale', 'Sale', $sale->id, null, $sale->toArray(), auth()->id());

            return response()->json($sale->load(['user', 'items.item']), 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Get sales receipt
     */
    public function receipt(Sale $sale)
    {
        $receipt = $this->salesService->generateReceipt($sale);

        return response()->json([
            'receipt' => $receipt,
            'sale' => $sale->load(['user', 'items.item']),
        ]);
    }

    /**
     * Get sales summary
     */
    public function summary(Request $request)
    {
        $summary = $this->salesService->getSalesSummary(
            $request->input('start_date'),
            $request->input('end_date'),
            $request->input('user_id')
        );

        return response()->json($summary);
    }

    /**
     * Process a return/refund
     */
    public function processReturn(Request $request, Sale $sale)
    {
        if ($sale->status !== 'completed') {
            return response()->json(['message' => 'Only completed sales can be returned'], 400);
        }

        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.sale_item_id' => 'required|exists:sale_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'reason' => 'nullable|string',
        ]);

        try {
            // Create a new sale with negative quantities
            $returnItems = [];
            foreach ($request->items as $item) {
                $saleItem = $sale->items()->findOrFail($item['sale_item_id']);
                $returnItems[] = [
                    'item_id' => $saleItem->item_id,
                    'quantity' => -$item['quantity'],
                    'unit_price' => $saleItem->unit_price,
                    'discount_percentage' => $saleItem->discount_percentage,
                ];
            }

            $returnSale = $this->salesService->createSale(
                auth()->id(),
                $returnItems,
                $sale->customer_name,
                $sale->customer_phone,
                $sale->customer_email,
                ['method' => 'cash', 'amount_paid' => 0]
            );

            $returnSale->update(['status' => 'returned']);

            // Restore stock
            foreach ($returnSale->items as $returnItem) {
                $this->stockService->recordSale($returnItem->item, -$returnItem->quantity, auth()->id(), "Return for Sale #{$sale->sale_number}");
            }

            $this->auditService->log('process_return', 'Sale', $sale->id, null, $returnSale->toArray(), auth()->id());

            return response()->json($returnSale, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }
}
