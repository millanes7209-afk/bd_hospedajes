<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CartController;

Route::prefix('carrito')->group(function () {
    // Existing POST routes
    Route::post('aceptar-stock', [CartController::class, 'aceptarStock']);
    Route::post('registrar-venta', [CartController::class, 'registrarVenta']);
    Route::post('cerrar-turno', [CartController::class, 'cerrarTurno']);
    Route::get('verificar-despacho', [CartController::class, 'verificarDespacho']);
    // New GET routes for the UI
    Route::get('ventas', [CartController::class, 'obtenerVentas']);
    Route::get('totales-diarios', [CartController::class, 'obtenerTotales']);
    Route::get('stock', [CartController::class, 'obtenerStock']);
});
