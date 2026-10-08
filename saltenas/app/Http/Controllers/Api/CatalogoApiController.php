<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Carrito;
use App\Models\Promocion;
use App\Models\VarianteSaltena;
use Illuminate\Http\JsonResponse;

class CatalogoApiController extends Controller
{
    /**
     * Retorna el catálogo completo para sincronización con POS remotos.
     */
    public function index(): JsonResponse
    {
        $carritos = Carrito::where('activo', true)
            ->select('id', 'nombre', 'ubicacion')
            ->orderBy('nombre')
            ->get();

        $variantes = VarianteSaltena::where('activo', true)
            ->select('id', 'nombre', 'precio_venta', 'descripcion')
            ->orderBy('nombre')
            ->get();

        $promociones = Promocion::where('activo', true)
            ->select('id', 'nombre', 'unidades_por_paquete', 'precio_paquete', 'descripcion')
            ->orderBy('nombre')
            ->get();

        return response()->json([
            'success' => true,
            'timestamp' => now()->toIso8601String(),
            'data' => [
                'carritos' => $carritos,
                'variantes' => $variantes,
                'promociones' => $promociones,
            ]
        ]);
    }
}
