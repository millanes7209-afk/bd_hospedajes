<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Carrito;
use App\Models\Promocion;
use App\Models\VarianteSaltena;
use App\Models\DespachoDiario;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CatalogoApiController extends Controller
{
    /**
     * Retorna el catálogo completo y el despacho del día para el carrito que consulta.
     */
    public function index(Request $request): JsonResponse
    {
        $hoy = date('Y-m-d');

        // Eliminar restricción unique en BD de producción si aún existe
        try {
            try {
                \Illuminate\Support\Facades\DB::statement("CREATE INDEX `despachos_diarios_carrito_id_fk_idx` ON `despachos_diarios` (`carrito_id`)");
            } catch (\Throwable $eIdx) {}
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE `despachos_diarios` DROP INDEX `despachos_diarios_carrito_id_fecha_unique`");
        } catch (\Throwable $e) {}

        // Carrito consultado (directo a Carrito 1 por defecto)
        $carritoId = $request->get('carrito_id') ?: 1;

        // Listado de todos los carritos registrados activos
        $listaCarritos = Carrito::where('activo', true)->select('id', 'nombre', 'zona')->orderBy('nombre')->get();

        // 1. Variantes de Salteñas activas
        $variantes = VarianteSaltena::where('activo', true)
            ->select('id', 'nombre', 'precio_venta')
            ->orderBy('nombre')
            ->get();

        // 2. Promociones activas
        $promociones = Promocion::where('activo', true)
            ->select('id', 'nombre', 'unidades_por_paquete', 'precio_paquete')
            ->orderBy('nombre')
            ->get();

        // 3. Despachos del día registrados en Central para este carrito (acumula múltiples despachos de hoy)
        $despachosHoy = DespachoDiario::with(['detalles.variante'])
            ->where('carrito_id', $carritoId)
            ->where('fecha', $hoy)
            ->where('estado', '!=', 'cancelado')
            ->get();

        $despachoHoy = [];
        $variantesTotales = [];
        foreach ($despachosHoy as $desp) {
            foreach ($desp->detalles as $det) {
                if ($det->variante) {
                    $vid = $det->variante_id;
                    if (!isset($variantesTotales[$vid])) {
                        $variantesTotales[$vid] = [
                            'variante_id' => $vid,
                            'nombre' => $det->variante->nombre,
                            'precio' => ($det->variante->precio_venta !== null) ? (float) $det->variante->precio_venta : null,
                            'cantidad_enviada' => 0
                        ];
                    }
                    $variantesTotales[$vid]['cantidad_enviada'] += (int) $det->cantidad_enviada;
                }
            }
        }
        $despachoHoy = array_values($variantesTotales);

        return response()->json([
            'success' => true,
            'timestamp' => now()->toIso8601String(),
            'data' => [
                'carrito_id' => $carritoId,
                'carritos' => $listaCarritos,
                'variantes' => $variantes,
                'promociones' => $promociones,
                'despacho_hoy' => $despachoHoy,
            ]
        ]);
    }
}
