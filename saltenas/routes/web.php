<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SucursalController;
use App\Http\Controllers\CostoController;
use App\Http\Controllers\CierreDiarioController;

// Rutas Públicas / Autenticación
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');

// Rutas Protegidas por Autenticación
Route::middleware(['auth'])->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Dashboard & Analítica
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Módulo de Sucursales
    Route::get('/sucursales', [SucursalController::class, 'index'])->name('sucursales.index');
    Route::post('/sucursales', [SucursalController::class, 'store'])->name('sucursales.store');
    Route::post('/sucursales/update/{id}', [SucursalController::class, 'update'])->name('sucursales.update');
    Route::get('/sucursales/toggle/{id}', [SucursalController::class, 'toggleEstado'])->name('sucursales.toggle');

    // Módulo de Costos & Recetas
    Route::get('/costos', [CostoController::class, 'index'])->name('costos.index');
    Route::post('/costos/insumos', [CostoController::class, 'storeInsumo'])->name('costos.insumos.store');
    Route::post('/costos/insumos/update/{id}', [CostoController::class, 'updateInsumo'])->name('costos.insumos.update');
    Route::post('/costos/variantes', [CostoController::class, 'storeCostoSaltena'])->name('costos.variantes.store');
    Route::post('/costos/variantes/update/{id}', [CostoController::class, 'updateCostoSaltena'])->name('costos.variantes.update');

    // Módulo de Cierres Diarios
    Route::get('/cierres', [CierreDiarioController::class, 'index'])->name('cierres.index');
    Route::post('/cierres', [CierreDiarioController::class, 'store'])->name('cierres.store');
    Route::get('/cierres/eliminar/{id}', [CierreDiarioController::class, 'destroy'])->name('cierres.destroy');
});
