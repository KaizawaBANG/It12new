<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ChangeOrderController;
use App\Http\Controllers\MaterialRequisitionController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\GoodsReceiptController;
use App\Http\Controllers\GoodsReturnController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\MaterialIssuanceController;
use App\Http\Controllers\FabricationController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;

// Authentication routes (if using Laravel Breeze/Jetstream, these would be auto-generated)
// For now, we'll add a simple login route
Route::get('/login', function () {
    return view('auth.login');
})->name('login')->middleware('guest');

Route::post('/login', function (\Illuminate\Http\Request $request) {
    $credentials = $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    try {
        if (\Illuminate\Support\Facades\Auth::attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended('/');
        }
    } catch (\Exception $e) {
        // Log the error for debugging
        \Log::error('Login attempt failed: ' . $e->getMessage());
        
        // Check if it's a database/session issue
        if (str_contains($e->getMessage(), 'sessions') || str_contains($e->getMessage(), 'table')) {
            return back()->withErrors(['email' => 'Session storage error. Please run: php artisan migrate and php artisan config:clear']);
        }
    }

    return back()->withErrors(['email' => 'Invalid credentials']);
})->name('login.post');

Route::post('/logout', function (\Illuminate\Http\Request $request) {
    \Illuminate\Support\Facades\Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/login');
})->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// Projects
Route::resource('projects', ProjectController::class);

// Change Orders
Route::resource('change-orders', ChangeOrderController::class);
Route::post('change-orders/{changeOrder}/approve', [ChangeOrderController::class, 'approve'])->name('change-orders.approve');
Route::post('change-orders/{changeOrder}/reject', [ChangeOrderController::class, 'reject'])->name('change-orders.reject');

// Purchase Requests (Material Requisitions)
Route::resource('purchase-requests', MaterialRequisitionController::class);
Route::post('purchase-requests/{purchaseRequest}/approve', [MaterialRequisitionController::class, 'approve'])->name('purchase-requests.approve');
Route::post('purchase-requests/{purchaseRequest}/submit', [MaterialRequisitionController::class, 'submit'])->name('purchase-requests.submit');

// Quotations
Route::resource('quotations', QuotationController::class);
Route::get('quotations/compare', [QuotationController::class, 'compare'])->name('quotations.compare');
Route::post('quotations/{quotation}/accept', [QuotationController::class, 'accept'])->name('quotations.accept');
Route::post('quotations/{quotation}/reject', [QuotationController::class, 'reject'])->name('quotations.reject');
Route::get('api/supplier-prices', [QuotationController::class, 'getSupplierPrices'])->name('api.supplier-prices');

// Purchase Orders
Route::resource('purchase-orders', PurchaseOrderController::class);
Route::post('purchase-orders/{purchaseOrder}/approve', [PurchaseOrderController::class, 'approve'])->name('purchase-orders.approve');
Route::get('purchase-orders/{purchaseOrder}/print', [PurchaseOrderController::class, 'print'])->name('purchase-orders.print');

// Goods Receipts
Route::resource('goods-receipts', GoodsReceiptController::class);
Route::post('goods-receipts/{goodsReceipt}/approve', [GoodsReceiptController::class, 'approve'])->name('goods-receipts.approve');

// Goods Returns
Route::resource('goods-returns', GoodsReturnController::class);
Route::post('goods-returns/{goodsReturn}/approve', [GoodsReturnController::class, 'approve'])->name('goods-returns.approve');

// Inventory
Route::resource('inventory', InventoryController::class);
Route::post('inventory/{inventoryItem}/adjust-stock', [InventoryController::class, 'adjustStock'])->name('inventory.adjust-stock');

// Material Issuance
Route::resource('material-issuance', MaterialIssuanceController::class);
Route::post('material-issuance/{materialIssuance}/approve', [MaterialIssuanceController::class, 'approve'])->name('material-issuance.approve');
Route::post('material-issuance/{materialIssuance}/issue', [MaterialIssuanceController::class, 'issue'])->name('material-issuance.issue');

// Fabrication
Route::resource('fabrication', FabricationController::class);
Route::post('fabrication/{fabricationJob}/start', [FabricationController::class, 'start'])->name('fabrication.start');
Route::post('fabrication/{fabricationJob}/complete', [FabricationController::class, 'complete'])->name('fabrication.complete');

// Reports
Route::get('reports', [ReportsController::class, 'index'])->name('reports.index');
Route::get('reports/inventory-movement', [ReportsController::class, 'inventoryMovement'])->name('reports.inventory-movement');
Route::get('reports/purchase-history', [ReportsController::class, 'purchaseHistory'])->name('reports.purchase-history');
Route::get('reports/project-consumption', [ReportsController::class, 'projectConsumption'])->name('reports.project-consumption');
Route::get('reports/supplier-performance', [ReportsController::class, 'supplierPerformance'])->name('reports.supplier-performance');

// Suppliers
Route::resource('suppliers', SupplierController::class);
Route::post('suppliers/{supplier}/prices', [SupplierController::class, 'storePrice'])->name('suppliers.prices.store');
Route::put('suppliers/{supplier}/prices/{priceId}', [SupplierController::class, 'updatePrice'])->name('suppliers.prices.update');
Route::delete('suppliers/{supplier}/prices/{priceId}', [SupplierController::class, 'deletePrice'])->name('suppliers.prices.delete');

// Users
Route::resource('users', UserController::class);
});
