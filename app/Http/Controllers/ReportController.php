<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Item;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index()
    {
        return view('reports.index');
    }

    public function sales(Request $request)
    {
        $from = $request->input('from', Carbon::today()->subDays(30)->format('Y-m-d'));
        $to = $request->input('to', Carbon::today()->format('Y-m-d'));

        $sales = Sale::whereBetween('created_at', [$from, $to])
            ->with('items')
            ->orderBy('created_at', 'desc')
            ->get();

        $totalSales = $sales->sum('total');
        $totalTax = $sales->sum('tax');
        $totalItems = $sales->sum('total_items');

        return view('reports.sales', [
            'sales' => $sales,
            'totalSales' => $totalSales,
            'totalTax' => $totalTax,
            'totalItems' => $totalItems,
            'from' => $from,
            'to' => $to,
        ]);
    }

    public function inventory()
    {
        $items = Item::with('stock', 'category')
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->name,
                    'sku' => $item->sku,
                    'category' => $item->category->name,
                    'cost' => $item->cost_price,
                    'retail' => $item->retail_price,
                    'stock' => $item->stock->quantity_on_hand,
                    'value' => $item->stock->quantity_on_hand * $item->cost_price,
                ];
            });

        return view('reports.inventory', [
            'items' => $items,
        ]);
    }
}
