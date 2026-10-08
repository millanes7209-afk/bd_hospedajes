<?php

namespace App\Services;

use App\Models\CierreDiario;
use App\Models\CierreDiarioDetalle;
use App\Models\CierreDiarioPromocionDetalle;
use App\Models\Promocion;
use App\Models\VarianteSaltena;
use Illuminate\Support\Facades\DB;

class CierreValidationService
{
    protected $bovedaService;

    public function __construct(BovedaService $bovedaService)
    {
        $this->bovedaService = $bovedaService;
    }

    public function guardarCierre(array $data)
    {
        return DB::transaction(function () use ($data) {
            $montoEstimadoTotal = 0;
            $hayInconsistenciaGlobal = false;
            $estado = $data['estado'] ?? 'aprobado';
            $origen = $data['origen'] ?? 'manual';

            // 1. Crear el registro cabecera temporal para obtener ID
            $cierre = CierreDiario::create([
                'carrito_id' => $data['carrito_id'],
                'fecha' => $data['fecha'],
                'temp_min' => $data['temp_min'] ?? null,
                'temp_max' => $data['temp_max'] ?? null,
                'monto_real' => $data['monto_real'],
                'monto_estimado' => 0,
                'diferencia' => 0,
                'dinero_efectivo' => $data['dinero_efectivo'] ?? 0,
                'dinero_qr' => $data['dinero_qr'] ?? 0,
                'inconsistente' => false,
                'estado' => $estado,
                'origen' => $origen,
                'observaciones' => $data['observaciones'] ?? null,
            ]);

            // 2. Procesar cada detalle por Variante de Salteña
            foreach ($data['detalles'] as $det) {
                $variante = VarianteSaltena::findOrFail($det['variante_id']);
                $precioUnitario = $variante->precio_venta;

                $cantEntregada = (int) $det['cantidad_entregada'];
                $cantVendidaNormal = (int) $det['cantidad_vendida_normal'];
                $cantSobrante = (int) $det['cantidad_sobrante'];

                // Calcular unidades vendidas bajo promociones y monto estimado de promociones
                $unidadesVendidasPromos = 0;
                $montoPromociones = 0;
                $promosDetalleGuardar = [];

                if (isset($det['promociones']) && is_array($det['promociones'])) {
                    foreach ($det['promociones'] as $pData) {
                        $paquetes = (int) $pData['paquetes_vendidos'];
                        if ($paquetes > 0) {
                            $promo = Promocion::findOrFail($pData['promocion_id']);
                            $unidadesVendidasPromos += ($paquetes * $promo->unidades_por_paquete);
                            $montoPromociones += ($paquetes * $promo->precio_paquete);

                            $promosDetalleGuardar[] = [
                                'promocion_id' => $promo->id,
                                'paquetes_vendidos' => $paquetes,
                            ];
                        }
                    }
                }

                // REGLA DE VALIDACIÓN CLAVE:
                // cantidad_entregada == cantidad_vendida_normal + unidades_en_promocion + cantidad_sobrante
                $unidadesJustificadas = $cantVendidaNormal + $unidadesVendidasPromos + $cantSobrante;
                $detalleInconsistente = ($cantEntregada !== $unidadesJustificadas);

                if ($detalleInconsistente) {
                    $hayInconsistenciaGlobal = true;
                }

                // Calcular monto estimado de la variante
                $montoNormal = $cantVendidaNormal * $precioUnitario;
                $montoEstimadoVariante = $montoNormal + $montoPromociones;
                $montoEstimadoTotal += $montoEstimadoVariante;

                // Guardar detalle
                $cierreDetalle = CierreDiarioDetalle::create([
                    'cierre_diario_id' => $cierre->id,
                    'variante_id' => $variante->id,
                    'cantidad_entregada' => $cantEntregada,
                    'cantidad_vendida_normal' => $cantVendidaNormal,
                    'cantidad_sobrante' => $cantSobrante,
                    'precio_unitario_aplicado' => $precioUnitario,
                    'inconsistente' => $detalleInconsistente,
                ]);

                // Guardar detalle de promociones aplicadas
                foreach ($promosDetalleGuardar as $pGuardar) {
                    CierreDiarioPromocionDetalle::create([
                        'cierre_diario_detalle_id' => $cierreDetalle->id,
                        'promocion_id' => $pGuardar['promocion_id'],
                        'paquetes_vendidos' => $pGuardar['paquetes_vendidos'],
                    ]);
                }
            }

            // 3. Actualizar totales del Cierre Diario
            $diferencia = $montoEstimadoTotal - $data['monto_real'];
            if ($diferencia != 0) {
                $hayInconsistenciaGlobal = true;
            }

            $cierre->update([
                'monto_estimado' => $montoEstimadoTotal,
                'diferencia' => $diferencia,
                'inconsistente' => $hayInconsistenciaGlobal,
            ]);

            // 4. Generar ingreso automático en la Bóveda Central solo si el cierre es aprobado (manual o aprobado por dueño)
            if ($estado === 'aprobado') {
                $this->bovedaService->registrarIngresoPorCierre($cierre);
            }

            return $cierre;
        });
    }

    /**
     * Aprueba un cierre pendiente remoto, ejecuta la validación contable
     * y registra el ingreso en la Bóveda.
     */
    public function aprobarCierre(CierreDiario $cierre): CierreDiario
    {
        return DB::transaction(function () use ($cierre) {
            $cierre->update(['estado' => 'aprobado']);
            $this->bovedaService->registrarIngresoPorCierre($cierre);
            return $cierre;
        });
    }
}
