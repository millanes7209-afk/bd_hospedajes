<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Venta;

class TransaccionController extends Controller
{
    public function index(Request $request)
    {
        $fechaInicio = $request->get('fecha_inicio', date('Y-m-d'));
        $fechaFin = $request->get('fecha_fin', date('Y-m-d'));

        $estadosValidos = ['cerrada', 'CERRADA', 'completado', 'COMPLETADO', 'cerrado', 'CERRADO'];

        try {
            $ventas = Venta::with(['items', 'pagos', 'usuarioApertura'])
                ->whereIn('estado', $estadosValidos)
                ->whereDate('fecha_apertura', '>=', $fechaInicio)
                ->whereDate('fecha_apertura', '<=', $fechaFin)
                ->orderBy('fecha_apertura', 'desc')
                ->get();

            $totalRecaudado = $ventas->sum('monto_total');
            $totalTransacciones = $ventas->count();
        } catch (\Throwable $e) {
            $ventas = collect([]);
            $totalRecaudado = 0;
            $totalTransacciones = 0;
        }

        return view('admin.transacciones.index', compact(
            'ventas',
            'fechaInicio',
            'fechaFin',
            'totalRecaudado',
            'totalTransacciones'
        ));
    }
}
