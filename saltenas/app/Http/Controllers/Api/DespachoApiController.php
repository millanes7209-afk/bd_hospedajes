<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DespachoDiario;
use App\Models\Carrito;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DespachoApiController extends Controller
{
    /**
     * Retorna el despacho del día para un carrito dado.
     * carritosuenos lo llama al cargar el POS cada mañana.
     *
     * GET /api/v1/despachos/hoy?carrito_id=1
     */
    public function hoy(Request $request): JsonResponse
    {
        $carritoId = $request->get('carrito_id');

        if (!$carritoId) {
            // Intentar resolver por header de origen
            $referer = $request->header('Referer') ?? $request->header('Origin') ?? '';
            $carrito = null;

            if ($referer) {
                $host = parse_url($referer, PHP_URL_HOST);
                $carrito = Carrito::where('subdominio', $host)->where('activo', true)->first();
            }

            if (!$carrito) {
                return response()->json([
                    'success' => false,
                    'error' => 'Se requiere carrito_id o un header Origin reconocido.',
                ], 422);
            }

            $carritoId = $carrito->id;
        }

        $despacho = DespachoDiario::with(['detalles.variante', 'carrito'])
            ->where('carrito_id', $carritoId)
            ->whereDate('fecha', now()->toDateString())
            ->first();

        if (!$despacho) {
            return response()->json([
                'success' => false,
                'sin_despacho' => true,
                'mensaje' => 'No hay despacho registrado para hoy en Central.',
            ], 200);
        }

        return response()->json([
            'success' => true,
            'despacho' => [
                'id' => $despacho->id,
                'fecha' => $despacho->fecha,
                'estado' => $despacho->estado,
                'observaciones' => $despacho->observaciones,
                'detalles' => $despacho->detalles->map(fn($d) => [
                    'id' => $d->id,
                    'variante_id' => $d->variante_id,
                    'variante_nombre' => $d->variante->nombre ?? '—',
                    'cantidad_enviada' => $d->cantidad_enviada,
                ]),
            ],
        ]);
    }

    /**
     * El carrito confirma que recibió el despacho (sin modificar cantidad).
     * carritosuenos lo llama cuando el vendedor toca "ACEPTAR STOCK".
     *
     * PATCH /api/v1/despachos/{despacho}/aceptar
     */
    public function aceptar(DespachoDiario $despacho): JsonResponse
    {
        if ($despacho->estado === 'aceptado') {
            return response()->json([
                'success' => true,
                'mensaje' => 'Este despacho ya estaba aceptado.',
                'estado' => 'aceptado',
            ]);
        }

        if ($despacho->estado === 'cerrado') {
            return response()->json([
                'success' => false,
                'error' => 'El despacho ya está cerrado y no puede modificarse.',
            ], 409);
        }

        // Copiar cantidad_enviada a cantidad_aceptada (el vendedor acepta todo)
        foreach ($despacho->detalles as $detalle) {
            $detalle->update(['cantidad_aceptada' => $detalle->cantidad_enviada]);
        }

        $despacho->update(['estado' => 'aceptado']);

        return response()->json([
            'success' => true,
            'mensaje' => 'Despacho aceptado. Stock confirmado en Central.',
            'estado' => 'aceptado',
        ]);
    }
}
