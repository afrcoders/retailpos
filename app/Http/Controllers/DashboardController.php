<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockTransaction;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->role->name === 'sales_staff') {
            return $this->salesStaffDashboard();
        } else {
            return $this->adminDashboard();
        }
    }

    private function salesStaffDashboard()
    {
        $today = Carbon::today();

        $todaySales = Sale::whereDate('created_at', $today)
            ->sum('total');

        $totalTransactions = Sale::whereDate('created_at', $today)->count();
        $totalReturns = Sale::whereDate('created_at', $today)->where('status', 'returned')->count();
        $totalDiscount = Sale::whereDate('created_at', $today)->sum('discount_amount');

        // Chart data (hourly sales for today)
        $chartLabels = [];
        $chartData = [];

        for ($hour = 6; $hour <= 22; $hour++) {
            $chartLabels[] = sprintf("%02d:00", $hour);
                 $sales = Sale::whereDate('created_at', $today)
        ->whereRaw('HOUR(created_at) = ?', [$hour])
        ->sum('total');

            $chartData[] = $sales;
        }

        // Only sales-related info for sales_staff
        return view('dashboard', [
            'todaySales' => $todaySales,
            'totalTransactions' => $totalTransactions,
            'totalReturns' => $totalReturns,
            'totalDiscount' => $totalDiscount,
            'chartLabels' => $chartLabels,
            'chartData' => $chartData,
            'isSalesStaff' => true,
        ]);
    }

    private function adminDashboard()
    {
        $today = Carbon::today();

        // Stats
        $totalItems = Item::count();
        $lowStockItems = Item::whereHas('stock', function ($q) {
            $q->whereRaw('quantity_on_hand <= items.reorder_level');
        })->count();

        $todaySales = Sale::whereDate('created_at', $today)->sum('total');
        $totalUsers = User::where('is_active', true)->count();

        // Low stock items list
        $lowStockItemsList = Item::with('stock')
            ->whereHas('stock', function ($q) {
                $q->whereRaw('quantity_on_hand <= items.reorder_level');
            })
            ->limit(5)
            ->get();

        // Recent stock movements
        $recentStockMovements = StockTransaction::with('item', 'user')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Top selling items today
        $topSellingItems = SaleItem::selectRaw('item_id, SUM(quantity) as total_qty, SUM(line_total) as total_revenue')
            ->whereDate('created_at', $today)
            ->groupBy('item_id')
            ->orderBy('total_revenue', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($saleItem) {
                $item = Item::find($saleItem->item_id);
                return (object) [
                    'name' => $item ? $item->name : 'Unknown Item',
                    'total_qty' => $saleItem->total_qty,
                    'total_revenue' => $saleItem->total_revenue,
                ];
            });

        // Chart data (daily sales for last 7 days)
        $chartLabels = [];
        $chartData = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $chartLabels[] = $date->format('M d');
            $sales = Sale::whereDate('created_at', $date)->sum('total');
            $chartData[] = $sales;
        }

        return view('dashboard', [
            'totalItems' => $totalItems,
            'lowStockItems' => $lowStockItems,
            'todaySales' => $todaySales,
            'totalUsers' => $totalUsers,
            'lowStockItemsList' => $lowStockItemsList,
            'recentStockMovements' => $recentStockMovements,
            'topSellingItems' => $topSellingItems,
            'chartLabels' => $chartLabels,
            'chartData' => $chartData,
        ]);
    }
}
