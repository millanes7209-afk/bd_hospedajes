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

    public function registrarIngresoPorCierre(CierreDiario $cierre)
    {
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
        return BovedaMovimiento::create([
            'tipo' => 'egreso',
            'monto' => $compra->monto_total,
            'fecha' => $compra->fecha,
            'cierre_diario_id' => null,
            'compra_id' => $compra->id,
        ]);
    }
}
