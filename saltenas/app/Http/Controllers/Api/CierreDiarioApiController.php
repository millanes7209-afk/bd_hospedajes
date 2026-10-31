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
     * Recibe el cierre definitivo de un carrito remoto y lo guarda como PENDIENTE para aprobación del dueño.
     */
    public function sincronizar(Request $request): JsonResponse
    {
        $input = $request->all();

        // Asignar Carrito 1 por defecto si no viene explícito
        if (empty($input['carrito_id'])) {
            $input['carrito_id'] = 1;
        }

        $validator = Validator::make($input, [
            'carrito_id' => 'required|exists:carritos,id',
            'fecha' => 'required|date_format:Y-m-d',
            'monto_real' => 'required|numeric|min:0',
            'dinero_efectivo' => 'nullable|numeric|min:0',
            'dinero_qr' => 'nullable|numeric|min:0',
            'observaciones' => 'nullable|string|max:1000',
            'total_vendidas' => 'nullable|integer|min:0',
            'total_sobrantes' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => 'Validación de payload fallida.',
                'detalles_error' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        // Un carrito cierra su turno una sola vez de forma definitiva. No hay correcciones.
        $cierreExistente = CierreDiario::where('carrito_id', $data['carrito_id'])
            ->where('fecha', $data['fecha'])
            ->first();

        if ($cierreExistente) {
            return response()->json([
                'success' => true,
                'mensaje' => 'El turno de este carrito ya se encuentra cerrado en Central.',
                'data' => [
                    'cierre_id' => $cierreExistente->id,
                    'estado' => $cierreExistente->estado,
                    'fecha' => $cierreExistente->fecha,
                ]
            ], 200);
        }

        try {
            // Construir detalles por variante recibidos desde el POS
            $detallesInput = $request->input('detalles');
            if (!empty($detallesInput) && is_array($detallesInput)) {
                $data['detalles'] = [];
                foreach ($detallesInput as $d) {
                    $data['detalles'][] = [
                        'variante_id' => (int) ($d['variante_id'] ?? 1),
                        'cantidad_entregada' => (int) ($d['cantidad_entregada'] ?? 0),
                        'cantidad_vendida_normal' => (int) ($d['cantidad_vendida_normal'] ?? 0),
                        'cantidad_sobrante' => (int) ($d['cantidad_sobrante'] ?? 0),
                    ];
                }
            } else {
                $data['detalles'] = [
                    [
                        'variante_id' => 1,
                        'cantidad_entregada' => (int) ($data['total_vendidas'] ?? 0) + (int) ($data['total_sobrantes'] ?? 0),
                        'cantidad_vendida_normal' => (int) ($data['total_vendidas'] ?? 0),
                        'cantidad_sobrante' => (int) ($data['total_sobrantes'] ?? 0),
                    ]
                ];
            }

            // Guardar como PENDIENTE (requiere aprobación del dueño en Central para pasar a Bóveda)
            $data['estado'] = 'pendiente';
            $data['origen'] = 'remoto';

            $cierre = $this->cierreValidationService->guardarCierre($data);

            return response()->json([
                'success' => true,
                'mensaje' => 'Cierre recibido exitosamente. Pendiente de aprobación por el dueño en Central.',
                'data' => [
                    'cierre_id' => $cierre->id,
                    'carrito_id' => $cierre->carrito_id,
                    'fecha' => $cierre->fecha,
                    'monto_real' => (float) $cierre->monto_real,
                    'dinero_efectivo' => (float) $cierre->dinero_efectivo,
                    'dinero_qr' => (float) $cierre->dinero_qr,
                    'estado' => $cierre->estado,
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error interno al procesar el cierre: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Aprueba un cierre pendiente remoto y lo registra en la Bóveda.
     */
    public function aprobar(CierreDiario $cierre): JsonResponse
    {
        if ($cierre->estado !== 'pendiente') {
            return response()->json(['success' => false, 'error' => 'Este cierre ya fue procesado.'], 409);
        }

        try {
            $cierre = $this->cierreValidationService->aprobarCierre($cierre);
            return response()->json(['success' => true, 'mensaje' => 'Cierre aprobado e ingresado en Bóveda.', 'cierre_id' => $cierre->id]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Rechaza u observa un cierre pendiente remoto.
     */
    public function rechazar(CierreDiario $cierre): JsonResponse
    {
        if ($cierre->estado !== 'pendiente') {
            return response()->json(['success' => false, 'error' => 'Este cierre ya fue procesado.'], 409);
        }

        $cierre->update(['estado' => 'rechazado']);
        return response()->json(['success' => true, 'mensaje' => 'Cierre marcado como rechazado/observado.']);
    }
}
