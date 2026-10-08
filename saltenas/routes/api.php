<?php

use App\Http\Controllers\Api\CatalogoApiController;
use App\Http\Controllers\Api\CierreDiarioApiController;
use App\Http\Middleware\VerifyApiKey;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — Sistema Central Salteñas & POS Remotos
|--------------------------------------------------------------------------
*/

// Endpoint de verificación de salud de la API (Ping)
Route::get('/v1/ping', function () {
    return response()->json([
        'status' => 'online',
        'sistema' => 'Salteñas Central API',
        'timestamp' => now()->toIso8601String(),
    ]);
});

// Grupo de rutas protegidas por API Key / Token de POS
Route::middleware([VerifyApiKey::class])->prefix('v1')->group(function () {
    // Sincronización de catálogo y precios para carritos
    Route::get('/catalogo', [CatalogoApiController::class, 'index']);

    // Recepción e ingesta de cierres diarios desde POS remotos
    Route::post('/cierres/sincronizar', [CierreDiarioApiController::class, 'sincronizar']);

    // Aprobación y rechazo de cierres pendientes (uso del dueño en Central)
    Route::patch('/cierres/{cierre}/aprobar', [CierreDiarioApiController::class, 'aprobar'])->name('api.cierres.aprobar');
    Route::patch('/cierres/{cierre}/rechazar', [CierreDiarioApiController::class, 'rechazar'])->name('api.cierres.rechazar');
});
