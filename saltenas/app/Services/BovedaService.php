<?php

namespace App\Services;

use App\Models\BovedaMovimiento;
use App\Models\CierreDiario;
use App\Models\Compra;
use Illuminate\Support\Facades\DB;

class BovedaService
{
    public function getSaldoActual()
    {
        $ingresos = BovedaMovimiento::where('tipo', 'ingreso')->sum('monto');
        $egresos = BovedaMovimiento::where('tipo', 'egreso')->sum('monto');

        return $ingresos - $egresos;
    }

    public function getSaldoEfectivoActual()
    {
        SchemaSyncService::ensureUpdatedColumns();

        $ingresosEfectivo = BovedaMovimiento::where('tipo', 'ingreso')
            ->selectRaw("SUM(CASE WHEN (COALESCE(dinero_efectivo, 0) = 0 AND COALESCE(dinero_qr, 0) = 0) THEN monto ELSE COALESCE(dinero_efectivo, 0) END) as total")
            ->value('total') ?? 0;

        $egresos = BovedaMovimiento::where('tipo', 'egreso')->sum('monto');

        return $ingresosEfectivo - $egresos;
    }

    public function getSaldoQrActual()
    {
        SchemaSyncService::ensureUpdatedColumns();

        return BovedaMovimiento::where('tipo', 'ingreso')
            ->selectRaw("SUM(COALESCE(dinero_qr, 0)) as total")
            ->value('total') ?? 0;
    }

    public function registrarIngresoPorCierre(CierreDiario $cierre)
    {
        SchemaSyncService::ensureUpdatedColumns();

        return BovedaMovimiento::create([
            'tipo' => 'ingreso',
            'monto' => $cierre->monto_real,
            'dinero_efectivo' => $cierre->dinero_efectivo ?? 0,
            'dinero_qr' => $cierre->dinero_qr ?? 0,
            'fecha' => $cierre->fecha,
            'cierre_diario_id' => $cierre->id,
            'compra_id' => null,
        ]);
    }

    public function registrarEgresoPorCompra(Compra $compra)
    {
        SchemaSyncService::ensureUpdatedColumns();

        return BovedaMovimiento::create([
            'tipo' => 'egreso',
            'monto' => $compra->monto_total,
            'dinero_efectivo' => $compra->monto_total,
            'dinero_qr' => 0,
            'fecha' => $compra->fecha,
            'cierre_diario_id' => null,
            'compra_id' => $compra->id,
        ]);
    }
}
