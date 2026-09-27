<?php

namespace App\Services;

use App\Models\Compra;
use App\Models\CompraDetalle;
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
        return DB::transaction(function () use ($data) {
            $montoTotal = 0;
            foreach ($data['items'] as $item) {
                $montoTotal += ($item['cantidad'] * $item['precio_unitario']);
            }

            $compra = Compra::create([
                'fecha' => $data['fecha'],
                'monto_total' => $montoTotal,
                'observaciones' => $data['observaciones'] ?? null,
            ]);

            foreach ($data['items'] as $item) {
                $subtotal = $item['cantidad'] * $item['precio_unitario'];

                CompraDetalle::create([
                    'compra_id' => $compra->id,
                    'insumo_id' => $item['insumo_id'],
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $item['precio_unitario'],
                    'subtotal' => $subtotal,
                ]);

                // Actualizar automáticamente el historial de precios del insumo
                InsumoPrecioHistorial::create([
                    'insumo_id' => $item['insumo_id'],
                    'precio' => $item['precio_unitario'],
                    'vigente_desde' => $data['fecha'],
                ]);
            }

            // Generar egreso automático en la Bóveda Central
            $this->bovedaService->registrarEgresoPorCompra($compra);

            return $compra;
        });
    }
}
