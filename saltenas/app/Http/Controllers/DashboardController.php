<?php

namespace App\Http\Controllers;

use App\Models\CierreDiario;
use App\Models\Sucursal;
use App\Models\CostoSaltena;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $totalSucursales = Sucursal::where('activa', true)->count();
        $totalVendidas = CierreDiario::sum('saltenas_vendidas');
        $totalRecaudado = CierreDiario::sum('total_recaudado');
        $gananciaTotal = CierreDiario::sum('ganancia_neta');

        // Promedio de ventas según clima (Analítica clave)
        $promedioClima = CierreDiario::select('clima', DB::raw('AVG(saltenas_vendidas) as avg_vendidas'), DB::raw('COUNT(*) as total_dias'))
            ->groupBy('clima')
            ->get()
            ->keyBy('clima');

        // Datos para gráfico de Ventas por Sucursal
        $ventasPorSucursal = Sucursal::withSum('cierres', 'saltenas_vendidas')
            ->withSum('cierres', 'total_recaudado')
            ->get();

        // Datos para gráfico de los últimos 15 cierres
        $ultimosCierres = CierreDiario::with('sucursal')
            ->orderBy('fecha', 'desc')
            ->take(15)
            ->get()
            ->reverse();

        return view('dashboard', compact(
            'totalSucursales',
            'totalVendidas',
            'totalRecaudado',
            'gananciaTotal',
            'promedioClima',
            'ventasPorSucursal',
            'ultimosCierres'
        ));
    }
}
