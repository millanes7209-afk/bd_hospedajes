<?php

namespace App\Http\Controllers;

use App\Models\Carrito;
use App\Models\CierreDiario;
use App\Models\Compra;
use App\Services\BovedaService;
use App\Services\ProyeccionVentasService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected $bovedaService;
    protected $proyeccionService;

    public function __construct(BovedaService $bovedaService, ProyeccionVentasService $proyeccionService)
    {
        $this->bovedaService = $bovedaService;
        $this->proyeccionService = $proyeccionService;
    }

    public function index(Request $request)
    {
        $fechaProyeccion = $request->get('fecha_proyeccion', Carbon::tomorrow()->format('Y-m-d'));
        $carritoId = $request->get('carrito_id');
        $tempMin = $request->get('temp_min');
        $tempMax = $request->get('temp_max');

        $proyeccion = $this->proyeccionService->obtenerProyeccion($fechaProyeccion, $carritoId, $tempMin, $tempMax);

        if ($request->ajax()) {
            return response()->json($proyeccion);
        }

        $saldoBoveda = $this->bovedaService->getSaldoActual();
        $totalCarritos = Carrito::where('activo', true)->count();
        $totalCierresInconsistentes = CierreDiario::where('inconsistente', true)->count();
        $totalUltimasCompras = Compra::count();

        $ultimosCierres = CierreDiario::with(['carrito', 'detalles.variante'])
            ->orderBy('fecha', 'desc')
            ->orderBy('id', 'desc')
            ->limit(5)
            ->get();

        $cierresInconsistentesList = CierreDiario::with(['carrito', 'detalles.variante'])
            ->where('inconsistente', true)
            ->orderBy('fecha', 'desc')
            ->limit(5)
            ->get();

        return view('dashboard', compact(
            'saldoBoveda',
            'totalCarritos',
            'totalCierresInconsistentes',
            'totalUltimasCompras',
            'ultimosCierres',
            'cierresInconsistentesList',
            'proyeccion'
        ));
    }
}

