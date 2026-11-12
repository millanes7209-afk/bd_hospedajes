<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CarritoTurnoVivo;
use App\Services\SchemaSyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CarritoEstadoVivoApiController extends Controller
{
    /**
     * Recibe el snapshot en tiempo real del turno abierto/cerrado de un carrito POS.
     *
     * POST /api/v1/carritos/estado-vivo
     */
    public function actualizar(Request $request): JsonResponse
    {
        SchemaSyncService::ensureUpdatedColumns();

        $input = $request->all();
        if (empty($input['carrito_id'])) {
            $input['carrito_id'] = 1;
        }

        $validator = Validator::make($input, [
            'carrito_id' => 'required|exists:carritos,id',
            'turno_remoto_id' => 'nullable|integer|min:1',
            'estado' => 'required|in:abierto,cerrado',
            'stock_aceptado' => 'nullable|boolean',
            'fecha' => 'nullable|date_format:Y-m-d',
            'total_enviadas' => 'nullable|integer|min:0',
            'total_vendidas' => 'nullable|integer|min:0',
            'total_restantes' => 'nullable|integer|min:0',
            'dinero_total' => 'nullable|numeric|min:0',
            'dinero_efectivo' => 'nullable|numeric|min:0',
            'dinero_qr' => 'nullable|numeric|min:0',
            'desglose_variedades' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => 'Payload inválido.',
                'detalles' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();
        $carritoId = (int) $data['carrito_id'];
        $turnoRemotoId = isset($data['turno_remoto_id']) ? (int) $data['turno_remoto_id'] : null;
        $estado = $data['estado'];

        $existente = CarritoTurnoVivo::where('carrito_id', $carritoId)->first();

        // Evitar que una petición lenta de un turno ya cerrado vuelva a abrirlo,
        // o que un reintento de cierre viejo cierre un turno más nuevo ya abierto
        if ($existente && $turnoRemotoId !== null && $existente->turno_remoto_id !== null) {
            if ((int) $existente->turno_remoto_id === $turnoRemotoId && $existente->estado === 'cerrado' && $estado === 'abierto') {
                return response()->json([
                    'success' => true,
                    'ignorado' => true,
                    'mensaje' => 'Turno ya cerrado en Central.',
                ]);
            }
            if ($turnoRemotoId < (int) $existente->turno_remoto_id && $estado === 'cerrado') {
                return response()->json([
                    'success' => true,
                    'ignorado' => true,
                    'mensaje' => 'Cierre previo ignorado porque existe un turno más reciente.',
                ]);
            }
        }

        // Normalizar desglose de variedades para garantizar consistencia entre subtotales y totales
        $desgloseLimpio = [];
        $sumEnv = 0;
        $sumVend = 0;
        $sumRest = 0;

        if (!empty($data['desglose_variedades']) && is_array($data['desglose_variedades'])) {
            foreach ($data['desglose_variedades'] as $item) {
                $env = max(0, (int) ($item['enviada'] ?? 0));
                $vend = max(0, (int) ($item['vendida'] ?? 0));
                $rest = isset($item['restante']) ? max(0, (int) $item['restante']) : max(0, $env - $vend);
                $desgloseLimpio[] = [
                    'variante_id' => (int) ($item['variante_id'] ?? ($item['id'] ?? 0)),
                    'nombre' => (string) ($item['nombre'] ?? 'Salteña'),
                    'enviada' => $env,
                    'vendida' => $vend,
                    'restante' => $rest,
                ];
                $sumEnv += $env;
                $sumVend += $vend;
                $sumRest += $rest;
            }
        }

        $totalEnviadas = !empty($desgloseLimpio) ? $sumEnv : (int) ($data['total_enviadas'] ?? 0);
        $totalVendidas = !empty($desgloseLimpio) ? $sumVend : (int) ($data['total_vendidas'] ?? 0);
        $totalRestantes = !empty($desgloseLimpio) ? $sumRest : (int) ($data['total_restantes'] ?? 0);

        $dinEf = round((float) ($data['dinero_efectivo'] ?? 0), 2);
        $dinQr = round((float) ($data['dinero_qr'] ?? 0), 2);
        $dinTotal = isset($data['dinero_total']) ? round((float) $data['dinero_total'], 2) : round($dinEf + $dinQr, 2);

        $esMismoTurnoAbierto = $existente
            && $existente->estado === 'abierto'
            && ($turnoRemotoId === null || (int) $existente->turno_remoto_id === $turnoRemotoId);

        $abiertoAt = $esMismoTurnoAbierto && $existente->abierto_at
            ? $existente->abierto_at
            : ($estado === 'abierto' ? now() : ($existente->abierto_at ?? now()));

        $turnoVivo = CarritoTurnoVivo::updateOrCreate(
            ['carrito_id' => $carritoId],
            [
                'turno_remoto_id' => $turnoRemotoId,
                'estado' => $estado,
                'stock_aceptado' => (bool) ($data['stock_aceptado'] ?? false),
                'fecha' => $data['fecha'] ?? now()->timezone('America/La_Paz')->toDateString(),
                'abierto_at' => $abiertoAt,
                'cerrado_at' => $estado === 'cerrado' ? now() : null,
                'total_enviadas' => $totalEnviadas,
                'total_vendidas' => $totalVendidas,
                'total_restantes' => $totalRestantes,
                'dinero_total' => $dinTotal,
                'dinero_efectivo' => $dinEf,
                'dinero_qr' => $dinQr,
                'desglose_variedades' => $desgloseLimpio,
                'ultima_actualizacion_pos' => now(),
            ]
        );

        return response()->json([
            'success' => true,
            'estado' => $turnoVivo->estado,
            'total_restantes' => $turnoVivo->total_restantes,
        ]);
    }
}
