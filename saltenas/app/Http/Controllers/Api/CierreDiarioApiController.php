<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CierreDiario;
use App\Models\Carrito;
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
     * Soporta resolución automática de carrito_id si el cliente no lo especifica.
     */
    public function sincronizar(Request $request): JsonResponse
    {
        // Auto-resolver carrito_id si no viene explícito
        $input = $request->all();
        if (empty($input['carrito_id'])) {
            $carrito = Carrito::where('activo', true)->first();
            $input['carrito_id'] = $carrito ? $carrito->id : 1;
        }

        $validator = Validator::make($input, [
            'carrito_id' => 'required|exists:carritos,id',
            'fecha' => 'required|date_format:Y-m-d',
            'temp_min' => 'nullable|numeric',
            'temp_max' => 'nullable|numeric',
            'monto_real' => 'required|numeric|min:0',
            'observaciones' => 'nullable|string|max:500',
            'detalles' => 'nullable|array',
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
        $cierreExistente = CierreDiario::where('carrito_id', $data['carrito_id'])
            ->where('fecha', $data['fecha'])
            ->first();

        if ($cierreExistente) {
            return response()->json([
                'success' => true,
                'mensaje' => 'El cierre diario ya se encontraba sincronizado previamente.',
                'data' => [
                    'cierre_id' => $cierreExistente->id,
                    'carrito_id' => $cierreExistente->carrito_id,
                    'fecha' => $cierreExistente->fecha,
                    'monto_real' => (float) $cierreExistente->monto_real,
                ]
            ], 200);
        }

        try {
            // Generar detalles por defecto si no vienen especificados
            if (empty($data['detalles'])) {
                $data['detalles'] = [
                    [
                        'variante_id' => 1,
                        'cantidad_entregada' => 0,
                        'cantidad_vendida_normal' => 0,
                        'cantidad_sobrante' => 0,
                    ]
                ];
            }

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
