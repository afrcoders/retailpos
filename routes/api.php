<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ItemController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\SupplierController;
use App\Http\Controllers\API\SalesController;
use App\Http\Controllers\API\StockController;
use App\Http\Controllers\API\ReportController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public Authentication Routes
Route::post('auth/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Protected API Routes
Route::middleware('auth:sanctum')->group(function () {

    // Authentication
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::get('auth/me', [AuthController::class, 'me']);
    Route::put('auth/profile', [AuthController::class, 'updateProfile']);

    // Item Management
    Route::apiResource('items', ItemController::class)->names([
        'index' => 'api.items.index',
        'store' => 'api.items.store',
        'show' => 'api.items.show',
        'update' => 'api.items.update',
        'destroy' => 'api.items.destroy'
    ]);
    Route::get('items/search/barcode', [ItemController::class, 'searchByBarcode']);
    Route::get('items/filter/low-stock', [ItemController::class, 'lowStock']);

    // Category Management
    Route::apiResource('categories', CategoryController::class)->names([
        'index' => 'api.categories.index',
        'store' => 'api.categories.store',
        'show' => 'api.categories.show',
        'update' => 'api.categories.update',
        'destroy' => 'api.categories.destroy'
    ]);

    // Supplier Management
    Route::apiResource('suppliers', SupplierController::class)->names([
        'index' => 'api.suppliers.index',
        'store' => 'api.suppliers.store',
        'show' => 'api.suppliers.show',
        'update' => 'api.suppliers.update',
        'destroy' => 'api.suppliers.destroy'
    ]);

    // Sales Management
    Route::apiResource('sales', SalesController::class)->only(['index', 'show', 'store']);
    Route::get('sales/{sale}/receipt', [SalesController::class, 'receipt']);
    Route::post('sales/{sale}/return', [SalesController::class, 'processReturn']);
    Route::get('sales/summary', [SalesController::class, 'summary']);

    // Stock Management
    Route::apiResource('stock', StockController::class)->only(['index', 'show']);
    Route::post('stock/{item}/adjust', [StockController::class, 'adjust']);
    Route::post('stock/{item}/receive', [StockController::class, 'receive']);
    Route::get('stock/{item}/transactions', [StockController::class, 'transactions']);
    Route::get('stock/filter/low-stock', [StockController::class, 'lowStock']);
    Route::get('stock/report', [StockController::class, 'report']);
    Route::post('stock/count', [StockController::class, 'count']);

    // Reports
    Route::prefix('reports')->group(function () {
        Route::get('sales-summary', [ReportController::class, 'salesSummary']);
        Route::get('sales-by-item', [ReportController::class, 'salesByItem']);
        Route::get('inventory-on-hand', [ReportController::class, 'inventoryOnHand']);
        Route::get('low-stock', [ReportController::class, 'lowStockReport']);
        Route::get('profit', [ReportController::class, 'profitReport']);
        Route::get('tax', [ReportController::class, 'taxReport']);
        Route::get('stock-movement', [ReportController::class, 'stockMovement']);
    });
});
