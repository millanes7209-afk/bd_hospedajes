<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Carrito;
use App\Models\Promocion;
use App\Models\VarianteSaltena;
use App\Models\DespachoDiario;
use Illuminate\Http\JsonResponse;

class CatalogoApiController extends Controller
{
    /**
     * Retorna el catálogo completo y el despacho del día para el carrito que consulta.
     */
    public function index(): JsonResponse
    {
        $hoy = date('Y-m-d');

        // Carrito principal activo
        $carrito = Carrito::where('activo', true)->first();
        $carritoId = $carrito ? $carrito->id : 1;

        // 1. Variantes de Salteñas activas
        $variantes = VarianteSaltena::where('activo', true)
            ->select('id', 'nombre', 'precio_venta', 'descripcion')
            ->orderBy('nombre')
            ->get();

        // 2. Promociones activas
        $promociones = Promocion::where('activo', true)
            ->select('id', 'nombre', 'unidades_por_paquete', 'precio_paquete', 'descripcion')
            ->orderBy('nombre')
            ->get();

        // 3. Despacho del día registrado en Central para este carrito
        $despachoHoy = [];
        $despacho = DespachoDiario::with(['detalles.variante'])
            ->where('carrito_id', $carritoId)
            ->where('fecha', $hoy)
            ->first();

        if ($despacho && $despacho->detalles) {
            foreach ($despacho->detalles as $det) {
                if ($det->variante) {
                    $despachoHoy[] = [
                        'variante_id' => $det->variante_id,
                        'nombre' => $det->variante->nombre,
                        'precio' => (float) $det->variante->precio_venta,
                        'cantidad_enviada' => (int) $det->cantidad_enviada
                    ];
                }
            }
        }

        return response()->json([
            'success' => true,
            'timestamp' => now()->toIso8601String(),
            'data' => [
                'carrito_id' => $carritoId,
                'variantes' => $variantes,
                'promociones' => $promociones,
                'despacho_hoy' => $despachoHoy,
            ]
        ]);
    }
}
