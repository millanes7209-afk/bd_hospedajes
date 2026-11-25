<?php

namespace App\Services;

use App\Models\Compra;
use App\Models\CompraDetalle;
use App\Models\Insumo;
use App\Models\InsumoPrecioHistorial;
use Illuminate\Support\Facades\DB;

class CompraService
{
    protected $bovedaService;

    public function __construct(BovedaService $bovedaService)
    {
        $this->bovedaService = $bovedaService;
    }

    public function registrarCompra(array $data)
    {
        SchemaSyncService::ensureUpdatedColumns();

        return DB::transaction(function () use ($data) {
            $montoTotal = 0;
            foreach ($data['items'] as $item) {
                $montoTotal += ((float)$item['cantidad'] * (float)$item['precio_unitario']);
            }

            $compra = Compra::create([
                'fecha' => $data['fecha'],
                'monto_total' => $montoTotal,
                'metodo_pago' => $data['metodo_pago'] ?? 'efectivo',
                'proveedor' => !empty($data['proveedor']) ? trim($data['proveedor']) : null,
                'comprobante_nro' => !empty($data['comprobante_nro']) ? trim($data['comprobante_nro']) : null,
                'observaciones' => !empty($data['observaciones']) ? trim($data['observaciones']) : null,
            ]);

            foreach ($data['items'] as $item) {
                $cantidad = (float)$item['cantidad'];
                $precioUnitario = (float)$item['precio_unitario'];
                $subtotal = $cantidad * $precioUnitario;

                CompraDetalle::create([
                    'compra_id' => $compra->id,
                    'insumo_id' => $item['insumo_id'],
                    'cantidad' => $cantidad,
                    'precio_unitario' => $precioUnitario,
                    'subtotal' => $subtotal,
                ]);

                // Actualizar automáticamente el historial de precios del insumo con el último precio
                InsumoPrecioHistorial::create([
                    'insumo_id' => $item['insumo_id'],
                    'precio' => $precioUnitario,
                    'vigente_desde' => $data['fecha'],
                ]);

                // Incrementar el stock actual del insumo en el almacén central
                $insumo = Insumo::find($item['insumo_id']);
                if ($insumo) {
                    $insumo->increment('stock_actual', $cantidad);
                }
            }

            // Generar egreso automático en la Bóveda Central (descuenta de efectivo o QR según corresponda)
            $this->bovedaService->registrarEgresoPorCompra($compra);

            return $compra;
        });
    }
}
