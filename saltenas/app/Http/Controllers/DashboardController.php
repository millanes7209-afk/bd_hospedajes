<?php

namespace App\Http\Controllers;

use App\Models\Carrito;
use App\Models\CierreDiario;
use App\Models\Compra;
use App\Services\BovedaService;

class DashboardController extends Controller
{
    protected $bovedaService;

    public function __construct(BovedaService $bovedaService)
    {
        $this->bovedaService = $bovedaService;
    }

    public function index()
    {
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
            'cierresInconsistentesList'
        ));
    }
}
