<?php

namespace App\Http\Controllers;

use App\Models\Compra;
use App\Models\Insumo;
use App\Services\CompraService;
use Illuminate\Http\Request;

class CompraController extends Controller
{
    protected $compraService;

    public function __construct(CompraService $compraService)
    {
        $this->compraService = $compraService;
    }

    public function index()
    {
        $compras = Compra::with('detalles.insumo')->orderBy('fecha', 'desc')->paginate(15);
        $insumos = Insumo::orderBy('nombre')->get();

        return view('compras.index', compact('compras', 'insumos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'fecha' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.insumo_id' => 'required|exists:insumos,id',
            'items.*.cantidad' => 'required|numeric|min:0.01',
            'items.*.precio_unitario' => 'required|numeric|min:0.01',
            'observaciones' => 'nullable|string',
        ]);

        $this->compraService->registrarCompra($request->all());

        return redirect()->route('compras.index')->with('success', 'Compra registrada exitosamente. El egreso de Bóveda y el historial de precios se actualizaron automáticamente.');
    }
}
