<?php

use App\Http\Controllers\Api\CatalogoApiController;
use App\Http\Controllers\Api\CierreDiarioApiController;
use App\Http\Controllers\Api\DespachoApiController;
use App\Http\Middleware\VerifyApiKey;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — Sistema Central Salteñas & POS Remotos
|--------------------------------------------------------------------------
*/

// Endpoint de verificación de salud de la API (Ping)
// Endpoint temporal para asegurar la eliminación de la restricción unique en BD de producción
Route::get('/v1/fix-despachos-db', function () {
    $msg = [];
    try {
        \Illuminate\Support\Facades\DB::statement("CREATE INDEX `despachos_diarios_carrito_id_fk_idx` ON `despachos_diarios` (`carrito_id`)");
        $msg[] = 'Índice de foreign key creado.';
    } catch (\Throwable $e) {
        $msg[] = 'Índice fk ya existía o ignorado: ' . $e->getMessage();
    }

    try {
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE `despachos_diarios` DROP INDEX `despachos_diarios_carrito_id_fecha_unique`");
        $msg[] = 'Índice unique eliminado con éxito. Múltiples despachos habilitados.';
    } catch (\Throwable $e) {
        $msg[] = 'Drop unique: ' . $e->getMessage();
    }

    return response()->json([
        'success' => true,
        'mensajes' => $msg
    ]);
});

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

    // Despacho diario: consulta del POS y confirmación de aceptación
    Route::get('/despachos/hoy', [DespachoApiController::class, 'hoy']);
    Route::patch('/despachos/{despacho}/aceptar', [DespachoApiController::class, 'aceptar']);
});
