<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BovedaController;
use App\Http\Controllers\CompraController;
use App\Http\Controllers\CarritoController;
use App\Http\Controllers\InsumoController;
use App\Http\Controllers\PreparacionController;
use App\Http\Controllers\VarianteSaltenaController;
use App\Http\Controllers\PromocionController;
use App\Http\Controllers\CierreDiarioController;

// Rutas Públicas
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');

// Rutas Protegidas
Route::middleware(['auth'])->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Bóveda Central (Caja Única)
    Route::get('/boveda', [BovedaController::class, 'index'])->name('boveda.index');

    // Compras & Historial de Precios
    Route::get('/compras', [CompraController::class, 'index'])->name('compras.index');
    Route::post('/compras', [CompraController::class, 'store'])->name('compras.store');

    // Carritos (Puntos de Venta)
    Route::get('/carritos', [CarritoController::class, 'index'])->name('carritos.index');
    Route::post('/carritos', [CarritoController::class, 'store'])->name('carritos.store');
    Route::get('/carritos/toggle/{id}', [CarritoController::class, 'toggleEstado'])->name('carritos.toggle');

    // Insumos
    Route::get('/insumos', [InsumoController::class, 'index'])->name('insumos.index');
    Route::post('/insumos', [InsumoController::class, 'store'])->name('insumos.store');

    // Preparaciones Intermedias (Masa)
    Route::get('/preparaciones', [PreparacionController::class, 'index'])->name('preparaciones.index');
    Route::post('/preparaciones', [PreparacionController::class, 'store'])->name('preparaciones.store');

    // Variantes de Salteña & Receta
    Route::get('/variantes', [VarianteSaltenaController::class, 'index'])->name('variantes.index');
    Route::post('/variantes', [VarianteSaltenaController::class, 'store'])->name('variantes.store');

    // Promociones Explícitas (Combos 3x10Bs)
    Route::get('/promociones', [PromocionController::class, 'index'])->name('promociones.index');
    Route::post('/promociones', [PromocionController::class, 'store'])->name('promociones.store');

    // Cierres Diarios & Validación de Inconsistencias
    Route::get('/cierres', [CierreDiarioController::class, 'index'])->name('cierres.index');
    Route::post('/cierres', [CierreDiarioController::class, 'store'])->name('cierres.store');
    Route::get('/cierres/eliminar/{id}', [CierreDiarioController::class, 'destroy'])->name('cierres.destroy');
});
