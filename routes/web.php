<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\ProviderManager;
use App\Livewire\ProductManager;
use App\Livewire\PurchaseManager;
use App\Livewire\TransferManager;

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
    Route::get('/compras', PurchaseManager::class)->name('purchases');
    Route::get('/sucursales', App\Livewire\BranchManager::class)->name('branches');
    Route::get('/categorias', App\Livewire\CategoryManager::class)->name('categories');
    Route::get('/tasas', App\Livewire\ExchangeRateManager::class)->name('exchange-rates');
    Route::get('/transfers', TransferManager::class)->name('transfers.index');
});
