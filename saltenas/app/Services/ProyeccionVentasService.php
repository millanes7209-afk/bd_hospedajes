<?php

namespace App\Services;

use App\Models\Carrito;
use App\Models\CierreDiario;
use App\Models\CierreDiarioDetalle;
use App\Models\CierreDiarioPromocionDetalle;
use App\Models\VarianteSaltena;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ProyeccionVentasService
{
    private static $diasEspaniol = [
        0 => 'DOMINGO',
        1 => 'LUNES',
        2 => 'MARTES',
        3 => 'MIÉRCOLES',
        4 => 'JUEVES',
        5 => 'VIERNES',
        6 => 'SÁBADO'
    ];

    /**
     * Calcula la proyección de producción y ventas para una fecha específica.
     */
    public function obtenerProyeccion($fechaTarget = null, $carritoId = null, $tempMin = null, $tempMax = null)
    {
        // Fecha por defecto: Mañana
        $fecha = $fechaTarget ? Carbon::parse($fechaTarget) : Carbon::tomorrow();
        $fechaStr = $fecha->format('Y-m-d');
        $dayOfWeekNum = $fecha->dayOfWeek; // 0=Domingo, 1=Lunes, ...
        $nombreDia = self::$diasEspaniol[$dayOfWeekNum] ?? 'DÍA';

        // 1. Obtener cierres históricos del mismo día de la semana
        $queryCierres = CierreDiario::whereRaw('DAYOFWEEK(fecha) = ?', [$dayOfWeekNum + 1]); // MySQL DAYOFWEEK: 1=Sun, 2=Mon...

        if ($carritoId) {
            $queryCierres->where('carrito_id', $carritoId);
        }

        // Si se especificaron temperaturas, se consideran cierres en un rango de +/- 4 °C si hay datos
        if ($tempMin !== null && $tempMin !== '') {
            $queryCierres->where(function ($q) use ($tempMin) {
                $q->whereNull('temp_min')
                    ->orWhereBetween('temp_min', [(float) $tempMin - 4, (float) $tempMin + 4]);
            });
        }
        if ($tempMax !== null && $tempMax !== '') {
            $queryCierres->where(function ($q) use ($tempMax) {
                $q->whereNull('temp_max')
                    ->orWhereBetween('temp_max', [(float) $tempMax - 4, (float) $tempMax + 4]);
            });
        }

        $cierreIds = $queryCierres->pluck('id')->toArray();
        $diasAnalizadosCount = count($cierreIds);

        // Si no hay cierres históricos suficientes en ese día de la semana con filtro estricto,
        // ampliamos la búsqueda a todos los cierres históricos del mismo día de la semana sin filtro de clima
        if ($diasAnalizadosCount === 0) {
            $queryFallback = CierreDiario::whereRaw('DAYOFWEEK(fecha) = ?', [$dayOfWeekNum + 1]);
            if ($carritoId) {
                $queryFallback->where('carrito_id', $carritoId);
            }
            $cierreIds = $queryFallback->pluck('id')->toArray();
            $diasAnalizadosCount = count($cierreIds);
        }

        // Si aún no hay datos históricos para ese día de la semana, usamos los últimos 30 cierres globales
        if ($diasAnalizadosCount === 0) {
            $queryGlobal = CierreDiario::query();
            if ($carritoId) {
                $queryGlobal->where('carrito_id', $carritoId);
            }
            $cierreIds = $queryGlobal->orderBy('fecha', 'desc')->limit(30)->pluck('id')->toArray();
            $diasAnalizadosCount = count($cierreIds);
        }

        // 2. Calcular temperaturas promedio del día si no fueron provistas
        $tempMinPromedio = $tempMin;
        $tempMaxPromedio = $tempMax;
        if ($diasAnalizadosCount > 0 && ($tempMin === null || $tempMin === '')) {
            $tempMinPromedio = CierreDiario::whereIn('id', $cierreIds)->avg('temp_min');
        }
        if ($diasAnalizadosCount > 0 && ($tempMax === null || $tempMax === '')) {
            $tempMaxPromedio = CierreDiario::whereIn('id', $cierreIds)->avg('temp_max');
        }

        // 3. Desglose por Variante de Salteña
        $variantes = VarianteSaltena::where('activo', true)->orderBy('nombre')->get();
        $proyeccionVariantes = [];
        $totalUnidadesEstimadas = 0;
        $montoEstimadoTotal = 0;

        foreach ($variantes as $var) {
            $unidadesVendidasTotales = 0;
            $muestrasValidas = 0;

            if ($diasAnalizadosCount > 0) {
                $detalles = CierreDiarioDetalle::with(['promocionesDetalle.promocion'])
                    ->whereIn('cierre_diario_id', $cierreIds)
                    ->where('variante_id', $var->id)
                    ->get();

                foreach ($detalles as $det) {
                    $muestrasValidas++;
                    $vendidaNormal = (int) $det->cantidad_vendida_normal;
                    $unidadesPromos = 0;
                    foreach ($det->promocionesDetalle as $pd) {
                        if ($pd->promocion) {
                            $unidadesPromos += ($pd->paquetes_vendidos * $pd->promocion->unidades_por_paquete);
                        }
                    }
                    $unidadesVendidasTotales += ($vendidaNormal + $unidadesPromos);
                }
            }

            $unidadesSugeridas = $muestrasValidas > 0 ? (int) round($unidadesVendidasTotales / $muestrasValidas) : 0;
            $montoEstimadoVariante = $unidadesSugeridas * (float) $var->precio_venta;

            $totalUnidadesEstimadas += $unidadesSugeridas;
            $montoEstimadoTotal += $montoEstimadoVariante;

            $proyeccionVariantes[] = [
                'variante_id' => $var->id,
                'nombre' => strtoupper($var->nombre),
                'precio_venta' => (float) $var->precio_venta,
                'unidades_sugeridas' => $unidadesSugeridas,
                'monto_estimado' => $montoEstimadoVariante,
                'muestras' => $muestrasValidas,
            ];
        }

        // 4. Lista de Carritos para el filtro
        $carritosList = Carrito::where('activo', true)->orderBy('nombre')->get();

        return [
            'fecha_target' => $fechaStr,
            'nombre_dia' => $nombreDia,
            'day_of_week' => $dayOfWeekNum,
            'carrito_id' => $carritoId ? (int) $carritoId : null,
            'carritos_lista' => $carritosList,
            'temp_min' => $tempMinPromedio !== null ? round($tempMinPromedio, 1) : null,
            'temp_max' => $tempMaxPromedio !== null ? round($tempMaxPromedio, 1) : null,
            'dias_analizados' => $diasAnalizadosCount,
            'total_unidades' => $totalUnidadesEstimadas,
            'monto_total' => $montoEstimadoTotal,
            'variantes' => $proyeccionVariantes,
        ];
    }
}
