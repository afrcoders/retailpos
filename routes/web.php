<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Public Routes
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

Route::get('/home', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

Route::middleware(['guest'])->group(function () {
    Route::get('/login', function () {
        return view('auth.login');
    })->name('login');

    Route::post('/login', [\App\Http\Controllers\AuthController::class, 'login'])->name('login.post');
});

Route::get('/logout', function () {
    auth()->logout();
    return redirect('/login');
})->name('logout');

// Protected Routes
Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Sales Staff Routes
    Route::middleware(['role:sales_staff,subadmin,admin'])->group(function () {
        Route::get('/sales', [SalesController::class, 'pos'])->name('sales.pos');
        Route::get('/sales-history', [SalesController::class, 'history'])->name('sales.history');
        Route::post('/sales/create', [SalesController::class, 'storeAjax'])->name('sales.ajax');
    });

    // Admin & Subadmin Routes
    Route::middleware(['role:subadmin,admin'])->group(function () {
        // Products - Full CRUD
        Route::get('/items', [ItemController::class, 'index'])->name('items.index');
        Route::get('/items/create', [ItemController::class, 'create'])->name('items.create');
        Route::post('/items', [ItemController::class, 'store'])->name('items.store');
        Route::get('/items/{item}', [ItemController::class, 'show'])->name('items.show');
        Route::get('/items/{item}/edit', [ItemController::class, 'edit'])->name('items.edit');
        Route::put('/items/{item}', [ItemController::class, 'update'])->name('items.update');
        Route::delete('/items/{item}', [ItemController::class, 'destroy'])->name('items.destroy');

        // Customers - Full CRUD
        Route::resource('customers', \App\Http\Controllers\CustomerController::class);

        // Categories - Full CRUD
        Route::get('/categories', [ItemController::class, 'categories'])->name('categories.index');
        Route::post('/categories', [ItemController::class, 'storeCategory'])->name('categories.store');
        Route::put('/categories/{category}', [ItemController::class, 'updateCategory'])->name('categories.update');
        Route::delete('/categories/{category}', [ItemController::class, 'destroyCategory'])->name('categories.destroy');

        // Suppliers - Full CRUD
        Route::get('/suppliers', [ItemController::class, 'suppliers'])->name('suppliers.index');
        Route::post('/suppliers', [ItemController::class, 'storeSupplier'])->name('suppliers.store');
        Route::put('/suppliers/{supplier}', [ItemController::class, 'updateSupplier'])->name('suppliers.update');
        Route::delete('/suppliers/{supplier}', [ItemController::class, 'destroySupplier'])->name('suppliers.destroy');

        // Stock Management
        Route::get('/stock', [StockController::class, 'index'])->name('stock.index');
        Route::get('/stock-movements', [StockController::class, 'movements'])->name('stock.movements');
        Route::post('/stock/adjust', [StockController::class, 'adjust'])->name('stock.adjust');

        // Reports
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/sales', [ReportController::class, 'sales'])->name('reports.sales');
        Route::get('/reports/inventory', [ReportController::class, 'inventory'])->name('reports.inventory');

        // Change Password
        Route::get('/change-password', [UserController::class, 'changePasswordForm'])->name('password.change');
        Route::post('/change-password', [UserController::class, 'changePassword'])->name('password.update');
    });

    // Admin Only Routes
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
        Route::delete('/sales/{sale}', [SalesController::class, 'destroy'])->name('sales.destroy');
        Route::get('/settings', [UserController::class, 'settings'])->name('settings.index');

        // Printer Settings
        Route::get('/settings/printer', [\App\Http\Controllers\Admin\SettingsController::class, 'index'])->name('admin.settings.printer');
        Route::put('/settings/printer', [\App\Http\Controllers\Admin\SettingsController::class, 'update'])->name('admin.settings.printer.update');
    });
});

