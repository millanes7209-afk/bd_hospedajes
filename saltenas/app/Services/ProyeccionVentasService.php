<?php

namespace App\Services;

use App\Models\Carrito;
use App\Models\CierreDiario;
use App\Models\CierreDiarioDetalle;
use App\Models\VarianteSaltena;
use Carbon\Carbon;

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
     * Calcula la proyección econométrica de ventas y producción utilizando:
     * 1. Regresión Lineal OLS ponderada por antigüedad (EWMA) para tendencia temporal.
     * 2. Factor econométrico de estacionalidad por día de la semana (Dummy Variables).
     * 3. Elasticidad de demanda respecto a la temperatura (Sensibilidad térmica).
     */
    public function obtenerProyeccion($fechaTarget = null, $carritoId = null, $tempMin = null, $tempMax = null)
    {
        $fecha = $fechaTarget ? Carbon::parse($fechaTarget) : Carbon::tomorrow();
        $fechaStr = $fecha->format('Y-m-d');
        $dayOfWeekNum = $fecha->dayOfWeek; // 0=Domingo, 1=Lunes, ...
        $nombreDia = self::$diasEspaniol[$dayOfWeekNum] ?? 'DÍA';

        // 1. Obtener cierres históricos ordenados cronológicamente
        $queryCierres = CierreDiario::orderBy('fecha', 'asc');
        if ($carritoId) {
            $queryCierres->where('carrito_id', $carritoId);
        }

        $cierres = $queryCierres->get();
        $totalCierresHistoricos = $cierres->count();

        // 2. Si se especifica o proyecta clima
        $tempMinPromedio = $tempMin;
        $tempMaxPromedio = $tempMax;

        if (($tempMin === null || $tempMin === '') && $totalCierresHistoricos > 0) {
            $tempMinPromedio = $cierres->whereNotNull('temp_min')->avg('temp_min');
        }
        if (($tempMax === null || $tempMax === '') && $totalCierresHistoricos > 0) {
            $tempMaxPromedio = $cierres->whereNotNull('temp_max')->avg('temp_max');
        }

        $variantes = VarianteSaltena::where('activo', true)->orderBy('nombre')->get();
        $proyeccionVariantes = [];
        $totalUnidadesEstimadas = 0;
        $montoEstimadoTotal = 0;

        // Si no hay datos suficientes en base de datos, fallback seguro
        if ($totalCierresHistoricos === 0) {
            foreach ($variantes as $var) {
                $proyeccionVariantes[] = [
                    'variante_id' => $var->id,
                    'nombre' => strtoupper($var->nombre),
                    'precio_venta' => (float) $var->precio_venta,
                    'unidades_sugeridas' => 0,
                    'monto_estimado' => 0,
                    'muestras' => 0,
                    'tendencia' => 'SIN DATOS',
                    'factor_estacionalidad' => 1.0,
                    'factor_clima' => 1.0,
                    'r2' => 0.0,
                ];
            }

            return [
                'metodo' => 'MODELO ECONOMÉTRICO OLS + ESTACIONALIDAD + ELASTICIDAD CLIMA',
                'fecha_target' => $fechaStr,
                'nombre_dia' => $nombreDia,
                'day_of_week' => $dayOfWeekNum,
                'carrito_id' => $carritoId ? (int) $carritoId : null,
                'carritos_lista' => Carrito::where('activo', true)->orderBy('nombre')->get(),
                'temp_min' => $tempMinPromedio !== null ? round((float) $tempMinPromedio, 1) : null,
                'temp_max' => $tempMaxPromedio !== null ? round((float) $tempMaxPromedio, 1) : null,
                'dias_analizados' => 0,
                'total_unidades' => 0,
                'monto_total' => 0.0,
                'variantes' => $proyeccionVariantes,
            ];
        }

        // Mapear cierres para econometría: indexar por fecha y día de la semana
        $cierreIds = $cierres->pluck('id')->toArray();
        $fechaInicial = Carbon::parse($cierres->first()->fecha);
        $diasSerieTarget = $fechaInicial->diffInDays($fecha);

        // Pre-cargar todos los detalles de venta de los cierres analizados
        $detallesVentas = CierreDiarioDetalle::with(['promocionesDetalle.promocion'])
            ->whereIn('cierre_diario_id', $cierreIds)
            ->get()
            ->groupBy('variante_id');

        // Ponderación exponencial por antigüedad (lambda = 0.90)
        $lambda = 0.90;

        foreach ($variantes as $var) {
            $detallesVar = $detallesVentas->get($var->id, collect());

            // Construir serie temporal de ventas por variante por cierre
            $serie = [];
            foreach ($cierres as $idx => $cierre) {
                $det = $detallesVar->where('cierre_diario_id', $cierre->id)->first();
                $ventas = 0;
                if ($det) {
                    $ventasNormales = (int) $det->cantidad_vendida_normal;
                    $ventasPromos = 0;
                    foreach ($det->promocionesDetalle as $pd) {
                        if ($pd->promocion) {
                            $ventasPromos += ($pd->paquetes_vendidos * $pd->promocion->unidades_por_paquete);
                        }
                    }
                    $ventas = $ventasNormales + $ventasPromos;
                }

                $dt = Carbon::parse($cierre->fecha);
                $x_t = $fechaInicial->diffInDays($dt); // Días transcurridos
                $dow = $dt->dayOfWeek; // Día de la semana
                $tmax = $cierre->temp_max !== null ? (float) $cierre->temp_max : (float) $tempMaxPromedio;

                $serie[] = [
                    'x' => $x_t,
                    'y' => $ventas,
                    'dow' => $dow,
                    'temp_max' => $tmax,
                    'cierre_id' => $cierre->id,
                ];
            }

            $N = count($serie);
            if ($N === 0) {
                $proyeccionVariantes[] = [
                    'variante_id' => $var->id,
                    'nombre' => strtoupper($var->nombre),
                    'precio_venta' => (float) $var->precio_venta,
                    'unidades_sugeridas' => 0,
                    'monto_estimado' => 0,
                    'muestras' => 0,
                    'tendencia' => 'SIN REGISTRO',
                    'factor_estacionalidad' => 1.0,
                    'factor_clima' => 1.0,
                    'r2' => 0.0,
                ];
                continue;
            }

            // A) Regresión Lineal Ponderada OLS (Trend Estimation)
            $sumW = 0;
            $sumWX = 0;
            $sumWY = 0;
            $sumWXX = 0;
            $sumWXY = 0;
            $sumWYY = 0;
            foreach ($serie as $i => $pt) {
                $w = pow($lambda, $N - 1 - $i); // Ponderación EWMA
                $x = $pt['x'];
                $y = $pt['y'];

                $sumW += $w;
                $sumWX += $w * $x;
                $sumWY += $w * $y;
                $sumWXX += $w * $x * $x;
                $sumWXY += $w * $x * $y;
                $sumWYY += $w * $y * $y;
            }

            $meanX = $sumWX / $sumW;
            $meanY = $sumWY / $sumW;

            $denomSlope = $sumWXX - ($sumWX * $sumWX / $sumW);
            $beta = $denomSlope != 0 ? ($sumWXY - ($sumWX * $sumWY / $sumW)) / $denomSlope : 0;
            $alpha = $meanY - ($beta * $meanX);

            // Tendencia estimada para la fecha objetivo
            $y_trend = $alpha + ($beta * $diasSerieTarget);
            if ($y_trend < 0) {
                $y_trend = max(0, $meanY);
            }

            // Coeficiente de determinación R2
            $denomR2 = sqrt(max(1e-9, ($sumWXX - $sumWX * $sumWX / $sumW) * ($sumWYY - $sumWY * $sumWY / $sumW)));
            $r2 = $denomR2 > 0 ? pow(($sumWXY - $sumWX * $sumWY / $sumW) / $denomR2, 2) : 0.0;

            // B) Factor Econométrico de Estacionalidad por Día de la Semana
            $serieDoW = array_filter($serie, function ($item) use ($dayOfWeekNum) {
                return $item['dow'] == $dayOfWeekNum;
            });
            $avgDoW = count($serieDoW) > 0 ? (array_sum(array_column($serieDoW, 'y')) / count($serieDoW)) : $meanY;
            $factorEstacionalidad = $meanY > 0 ? ($avgDoW / $meanY) : 1.0;
            // Limitar el factor de estacionalidad entre 0.5 y 1.8 para estabilidad
            $factorEstacionalidad = min(1.8, max(0.5, $factorEstacionalidad));

            // C) Elasticidad Clima / Sensibilidad Térmica
            $factorClima = 1.0;
            if ($tempMaxPromedio !== null && $meanY > 0) {
                $covTemp = 0;
                $varTemp = 0;
                foreach ($serie as $pt) {
                    $dT = $pt['temp_max'] - $tempMaxPromedio;
                    $dY = $pt['y'] - $meanY;
                    $covTemp += $dT * $dY;
                    $varTemp += $dT * $dT;
                }
                $gammaTemp = $varTemp > 0 ? ($covTemp / $varTemp) : 0;
                $deltaTTarget = ($tempMax !== null && $tempMax !== '') ? ((float) $tempMax - $tempMaxPromedio) : 0;
                $sensibilidad = ($gammaTemp * $deltaTTarget) / $meanY;
                $factorClima = 1.0 + min(0.35, max(-0.35, $sensibilidad));
            }

            // D) Cálculo Econométrico Final de la Variante
            $unidadesEstimadas = (int) round($y_trend * $factorEstacionalidad * $factorClima);
            if ($unidadesEstimadas < 0) {
                $unidadesEstimadas = 0;
            }

            $montoEstimadoVariante = $unidadesEstimadas * (float) $var->precio_venta;
            $totalUnidadesEstimadas += $unidadesEstimadas;
            $montoEstimadoTotal += $montoEstimadoVariante;

            // Clasificación de la Tendencia
            $tendenciaTexto = 'ESTABLE ➔';
            if ($beta > 0.05) {
                $tendenciaTexto = 'ALCISTA ↑';
            } elseif ($beta < -0.05) {
                $tendenciaTexto = 'DECRECIENTE ↓';
            }

            $proyeccionVariantes[] = [
                'variante_id' => $var->id,
                'nombre' => strtoupper($var->nombre),
                'precio_venta' => (float) $var->precio_venta,
                'unidades_sugeridas' => $unidadesEstimadas,
                'monto_estimado' => $montoEstimadoVariante,
                'muestras' => $N,
                'tendencia' => $tendenciaTexto,
                'beta_tendencia' => round($beta, 4),
                'factor_estacionalidad' => round($factorEstacionalidad, 2),
                'factor_clima' => round($factorClima, 2),
                'r2' => round($r2, 4),
            ];
        }

        return [
            'metodo' => 'MODELO ECONOMÉTRICO OLS + ESTACIONALIDAD + ELASTICIDAD CLIMA',
            'fecha_target' => $fechaStr,
            'nombre_dia' => $nombreDia,
            'day_of_week' => $dayOfWeekNum,
            'carrito_id' => $carritoId ? (int) $carritoId : null,
            'carritos_lista' => Carrito::where('activo', true)->orderBy('nombre')->get(),
            'temp_min' => $tempMinPromedio !== null ? round((float) $tempMinPromedio, 1) : null,
            'temp_max' => $tempMaxPromedio !== null ? round((float) $tempMaxPromedio, 1) : null,
            'dias_analizados' => $totalCierresHistoricos,
            'total_unidades' => $totalUnidadesEstimadas,
            'monto_total' => $montoEstimadoTotal,
            'variantes' => $proyeccionVariantes,
        ];
    }
}
