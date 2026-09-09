<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Venta;
use Illuminate\Support\Facades\DB;

class TransaccionController extends Controller
{
    public function index(Request $request)
    {
        $fechaInicio = $request->get('fecha_inicio', date('Y-m-d'));
        $fechaFin = $request->get('fecha_fin', date('Y-m-d'));

        $estadosValidos = ['cerrada', 'CERRADA', 'completado', 'COMPLETADO', 'cerrado', 'CERRADO'];

        try {
            $ventas = Venta::with(['items', 'pagos', 'usuarioApertura'])
                ->where(function ($q) use ($fechaInicio, $fechaFin) {
                    $q->whereDate(DB::raw('IFNULL(fecha_apertura, created_at)'), '>=', $fechaInicio)
                        ->whereDate(DB::raw('IFNULL(fecha_apertura, created_at)'), '<=', $fechaFin);
                })
                ->orderBy('id', 'desc')
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
