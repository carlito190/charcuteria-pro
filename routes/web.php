<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\ProviderManager;
use App\Livewire\ProductManager;
use App\Livewire\PurchaseManager;
use App\Livewire\TransferManager;
use App\Livewire\Sales\CreateSale;
use App\Livewire\Sales\IndexSales;
use App\Livewire\Purchases\IndexPurchases;
use App\Livewire\Users\UserManager;
use App\Livewire\BrandManager;
use App\Livewire\ClientIndex;
use App\Livewire\AccountsReceivable;
use App\Http\Controllers\ProductExportController;


Route::get('/', function () {
    return view('welcome');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // --- RUTAS DE LA CHARCUTERÍA ---

    // Ruta para gestionar Proveedores
    Route::get('/proveedores', ProviderManager::class)->name('providers');

    // Aquí iremos añadiendo las demás, por ejemplo:
    Route::get('/productos', ProductManager::class)->name('products');
    Route::get('/compras/crear', PurchaseManager::class)->name('purchases');
    Route::get('/sucursales', App\Livewire\BranchManager::class)->name('branches');
    Route::get('/categorias', App\Livewire\CategoryManager::class)->name('categories');
    Route::get('/tasas', App\Livewire\ExchangeRateManager::class)->name('exchange-rates');
    Route::get('/transfers', TransferManager::class)->name('transfers.index');
    Route::get('/ventas/crear', CreateSale::class)->name('sales.create');
    Route::get('/ventas', IndexSales::class)->name('sales.index');
    Route::get('/compras', IndexPurchases::class)->name('purchases.index');
    Route::get('/usuarios', UserManager::class)->name('users.index');
    Route::get('/marcas', BrandManager::class)->name('brands.index');
    Route::get('/productos/exportar/pdf', [ProductExportController::class, 'pdf'])->name('products.export.pdf');
    Route::get('/productos/exportar/excel', [ProductExportController::class, 'excel'])->name('products.export.excel');
    Route::get('/clientes', ClientIndex::class)->name('clients.index');
    Route::get('/cuentas-por-cobrar', AccountsReceivable::class)->name('cxc.index');
});

Route::get('/ventas/{sale}/ticket', function (\App\Models\Sale $sale) {
    // Cargamos las relaciones necesarias
    $sale->load(['items.product', 'payments']);
    return view('sales.ticket', compact('sale'));
})->name('sales.ticket');
