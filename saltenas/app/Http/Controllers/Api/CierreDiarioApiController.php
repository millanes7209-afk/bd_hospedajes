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
     * Recibe el cierre de un carrito remoto y lo guarda como PENDIENTE para aprobación del dueño.
     */
    public function sincronizar(Request $request): JsonResponse
    {
        $input = $request->all();

        // Auto-resolver carrito_id por subdominio o tomar el primero activo
        if (empty($input['carrito_id'])) {
            $referer = $request->header('Referer') ?? $request->header('Origin') ?? '';
            $carrito = null;

            if ($referer) {
                $host = parse_url($referer, PHP_URL_HOST);
                $carrito = Carrito::where('subdominio', $host)->where('activo', true)->first();
            }

            if (!$carrito) {
                $carrito = Carrito::where('activo', true)->first();
            }

            $input['carrito_id'] = $carrito ? $carrito->id : 1;
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

        // Idempotencia: si ya existe un cierre para esta fecha+carrito, retornar el existente
        $cierreExistente = CierreDiario::where('carrito_id', $data['carrito_id'])
            ->where('fecha', $data['fecha'])
            ->first();

        if ($cierreExistente) {
            return response()->json([
                'success' => true,
                'mensaje' => 'El cierre ya estaba registrado en Central.',
                'data' => [
                    'cierre_id' => $cierreExistente->id,
                    'estado' => $cierreExistente->estado,
                    'fecha' => $cierreExistente->fecha,
                ]
            ], 200);
        }

        try {
            // Construir detalles mínimos para el servicio
            $data['detalles'] = [
                [
                    'variante_id' => 1,
                    'cantidad_entregada' => (int) ($data['total_vendidas'] ?? 0) + (int) ($data['total_sobrantes'] ?? 0),
                    'cantidad_vendida_normal' => (int) ($data['total_vendidas'] ?? 0),
                    'cantidad_sobrante' => (int) ($data['total_sobrantes'] ?? 0),
                ]
            ];

            // Guardar como PENDIENTE (requiere aprobación del dueño en Central)
            $data['estado'] = 'pendiente';
            $data['origen'] = 'remoto';

            $cierre = $this->cierreValidationService->guardarCierre($data);

            return response()->json([
                'success' => true,
                'mensaje' => 'Cierre recibido. Pendiente de aprobación por el dueño en el Sistema Central.',
                'data' => [
                    'cierre_id' => $cierre->id,
                    'carrito_id' => $cierre->carrito_id,
                    'fecha' => $cierre->fecha,
                    'monto_real' => (float) $cierre->monto_real,
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
            return response()->json(['success' => true, 'mensaje' => 'Cierre aprobado y registrado en Bóveda.', 'cierre_id' => $cierre->id]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Rechaza un cierre pendiente remoto.
     */
    public function rechazar(CierreDiario $cierre): JsonResponse
    {
        if ($cierre->estado !== 'pendiente') {
            return response()->json(['success' => false, 'error' => 'Este cierre ya fue procesado.'], 409);
        }

        $cierre->update(['estado' => 'rechazado']);
        return response()->json(['success' => true, 'mensaje' => 'Cierre rechazado.']);
    }
}
