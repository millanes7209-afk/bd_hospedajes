<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CierreDiario;
use App\Services\CierreValidationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CierreDiarioApiController extends Controller
{
    protected $cierreValidationService;

    public function __construct(CierreValidationService $cierreValidationService)
    {
        $this->cierreValidationService = $cierreValidationService;
    }

    /**
     * Recibe y procesa un cierre diario enviado desde un POS o carrito remoto.
     */
    public function sincronizar(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'carrito_id' => 'required|exists:carritos,id',
            'fecha' => 'required|date_format:Y-m-d',
            'temp_min' => 'nullable|numeric',
            'temp_max' => 'nullable|numeric',
            'monto_real' => 'required|numeric|min:0',
            'observaciones' => 'nullable|string|max:500',
            'detalles' => 'required|array|min:1',
            'detalles.*.variante_id' => 'required|exists:variantes_saltenas,id',
            'detalles.*.cantidad_entregada' => 'required|integer|min:0',
            'detalles.*.cantidad_vendida_normal' => 'required|integer|min:0',
            'detalles.*.cantidad_sobrante' => 'required|integer|min:0',
            'detalles.*.promociones' => 'nullable|array',
            'detalles.*.promociones.*.promocion_id' => 'required|exists:promociones,id',
            'detalles.*.promociones.*.paquetes_vendidos' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => 'Validación de payload fallida.',
                'detalles_error' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        // 1. Validar unicidad (impedir cierres duplicados para la misma fecha y carrito)
        $existeCierre = CierreDiario::where('carrito_id', $data['carrito_id'])
            ->where('fecha', $data['fecha'])
            ->exists();

        if ($existeCierre) {
            return response()->json([
                'success' => false,
                'error' => "Ya existe un cierre registrado para el carrito ID {$data['carrito_id']} en la fecha {$data['fecha']}.",
            ], 409);
        }

        try {
            // 2. Procesar y guardar el cierre mediante el servicio centralizado
            $cierre = $this->cierreValidationService->guardarCierre($data);

            return response()->json([
                'success' => true,
                'mensaje' => 'Cierre diario sincronizado y registrado exitosamente en el sistema principal.',
                'data' => [
                    'cierre_id' => $cierre->id,
                    'carrito_id' => $cierre->carrito_id,
                    'fecha' => $cierre->fecha,
                    'monto_real' => (float) $cierre->monto_real,
                    'monto_estimado' => (float) $cierre->monto_estimado,
                    'diferencia' => (float) $cierre->diferencia,
                    'inconsistente' => (bool) $cierre->inconsistente,
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error interno al procesar el cierre: ' . $e->getMessage(),
            ], 500);
        }
    }
}
