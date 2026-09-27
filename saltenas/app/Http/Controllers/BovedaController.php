<?php

namespace App\Http\Controllers;

use App\Models\BovedaMovimiento;
use App\Services\BovedaService;
use Illuminate\Http\Request;

class BovedaController extends Controller
{
    protected $bovedaService;

    public function __construct(BovedaService $bovedaService)
    {
        $this->bovedaService = $bovedaService;
    }

    public function index()
    {
        $saldoActual = $this->bovedaService->getSaldoActual();
        $movimientos = BovedaMovimiento::with(['cierreDiario.carrito', 'compra.detalles.insumo'])
            ->orderBy('fecha', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(15);

        return view('boveda.index', compact('saldoActual', 'movimientos'));
    }
}
