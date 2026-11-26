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
            $itemsValidos = [];

            // Primera pasada: resolver insumos y calcular total exacto
            foreach ($data['items'] as $item) {
                $insumoId = $item['insumo_id'] ?? null;
                $nombre = trim($item['nombre'] ?? '');

                $insumo = null;
                if (!empty($insumoId) && is_numeric($insumoId) && (int)$insumoId > 0) {
                    $insumo = Insumo::find($insumoId);
                }

                if (!$insumo && !empty($nombre)) {
                    // Evitar doble ingreso: buscar si ya existe insumo con este nombre (case-insensitive)
                    $insumo = Insumo::whereRaw('LOWER(nombre) = ?', [mb_strtolower($nombre)])->first();
                    if (!$insumo) {
                        $unidad = !empty($item['unidad_medida']) ? strtolower(trim($item['unidad_medida'])) : 'unidad';
                        $insumo = Insumo::create([
                            'nombre' => $nombre,
                            'unidad_medida' => $unidad,
                            'stock_actual' => 0,
                            'stock_minimo' => 0,
                        ]);
                    }
                }

                if (!$insumo) {
                    continue;
                }

                $cantidad = (float)str_replace(',', '.', (string)($item['cantidad'] ?? 0));
                $precioUnitario = (float)str_replace(',', '.', (string)($item['precio_unitario'] ?? 0));

                if ($cantidad <= 0 || $precioUnitario <= 0) {
                    continue;
                }

                $subtotal = $cantidad * $precioUnitario;
                $montoTotal += $subtotal;

                $itemsValidos[] = [
                    'insumo' => $insumo,
                    'cantidad' => $cantidad,
                    'precio_unitario' => $precioUnitario,
                    'subtotal' => $subtotal,
                ];
            }

            if (empty($itemsValidos)) {
                throw new \Exception("Debes registrar al menos un insumo válido con cantidad y precio mayores a 0.");
            }

            $compra = Compra::create([
                'fecha' => $data['fecha'],
                'monto_total' => $montoTotal,
                'metodo_pago' => $data['metodo_pago'] ?? 'efectivo',
                'proveedor' => !empty($data['proveedor']) ? trim($data['proveedor']) : null,
                'comprobante_nro' => !empty($data['comprobante_nro']) ? trim($data['comprobante_nro']) : null,
                'observaciones' => !empty($data['observaciones']) ? trim($data['observaciones']) : null,
            ]);

            foreach ($itemsValidos as $it) {
                CompraDetalle::create([
                    'compra_id' => $compra->id,
                    'insumo_id' => $it['insumo']->id,
                    'cantidad' => $it['cantidad'],
                    'precio_unitario' => $it['precio_unitario'],
                    'subtotal' => $it['subtotal'],
                ]);

                // Actualizar automáticamente el historial de precios del insumo con el último precio
                InsumoPrecioHistorial::create([
                    'insumo_id' => $it['insumo']->id,
                    'precio' => $it['precio_unitario'],
                    'vigente_desde' => $data['fecha'],
                ]);

                // Incrementar el stock actual del insumo en el almacén central
                $it['insumo']->increment('stock_actual', $it['cantidad']);
            }

            // Descontar automáticamente de Bóveda Central (efectivo o QR)
            $this->bovedaService->registrarEgresoPorCompra($compra);

            return $compra;
        });
    }
}
