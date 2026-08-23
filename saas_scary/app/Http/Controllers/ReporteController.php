<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Venta;
use App\Models\VentaItem;
use Illuminate\Support\Facades\DB;

class ReporteController extends Controller
{
    public function index(Request $request)
    {
        $fechaInicio = $request->get('fecha_inicio', date('Y-m-d'));
        $fechaFin = $request->get('fecha_fin', date('Y-m-d'));

        $tenant = app()->bound('tenant') ? app('tenant') : null;
        $estadosValidos = ['cerrada', 'CERRADA', 'completado', 'COMPLETADO', 'cerrado', 'CERRADO'];

        try {
            // 1. Total Ingresos y Total Ventas Cerradas
            $ventasCerradas = Venta::whereIn('estado', $estadosValidos)
                ->whereDate('fecha_apertura', '>=', $fechaInicio)
                ->whereDate('fecha_apertura', '<=', $fechaFin)
                ->get();

            $totalIngresos = $ventasCerradas->sum('monto_total');
            $cantidadVentas = $ventasCerradas->count();

            // 2. Desglose por Origen (Local vs WhatsApp)
            $ventasPorOrigen = Venta::whereIn('estado', $estadosValidos)
                ->whereDate('fecha_apertura', '>=', $fechaInicio)
                ->whereDate('fecha_apertura', '<=', $fechaFin)
                ->select('origen', DB::raw('COUNT(*) as total_pedidos'), DB::raw('SUM(monto_total) as total_monto'))
                ->groupBy('origen')
                ->get();

            // 3. Productos Más Vendidos
            $productosVendidos = VentaItem::whereHas('venta', function ($q) use ($fechaInicio, $fechaFin, $estadosValidos) {
                $q->whereIn('estado', $estadosValidos)
                    ->whereDate('fecha_apertura', '>=', $fechaInicio)
                    ->whereDate('fecha_apertura', '<=', $fechaFin);
            })
                ->select('nombre_producto', 'nombre_variante', DB::raw('SUM(cantidad) as total_cantidad'), DB::raw('SUM(precio_total) as total_ingreso'))
                ->groupBy('nombre_producto', 'nombre_variante')
                ->orderBy('total_cantidad', 'desc')
                ->get();

            // 4. Desglose de Ventas por Hora del Día (0 a 23 hrs)
            $ventasPorHora = Venta::whereIn('estado', $estadosValidos)
                ->whereDate('fecha_apertura', '>=', $fechaInicio)
                ->whereDate('fecha_apertura', '<=', $fechaFin)
                ->select(DB::raw('HOUR(fecha_apertura) as hora'), DB::raw('COUNT(*) as total_ventas'), DB::raw('SUM(monto_total) as total_monto'))
                ->groupBy('hora')
                ->orderBy('hora', 'asc')
                ->get();
        } catch (\Throwable $e) {
            $totalIngresos = 0;
            $cantidadVentas = 0;
            $ventasPorOrigen = collect([]);
            $productosVendidos = collect([]);
            $ventasPorHora = collect([]);
        }

        return view('admin.reportes.index', compact(
            'tenant',
            'fechaInicio',
            'fechaFin',
            'totalIngresos',
            'cantidadVentas',
            'ventasPorOrigen',
            'productosVendidos',
            'ventasPorHora'
        ));
    }
}
