<?php

namespace App\Http\Controllers;

use App\Models\Compra;
use App\Models\Insumo;
use App\Services\CompraService;
use App\Services\SchemaSyncService;
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
        SchemaSyncService::ensureUpdatedColumns();

        $compras = Compra::with('detalles.insumo')
            ->orderBy('fecha', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(15);

        $insumos = Insumo::with([
            'preciosHistorial' => function ($q) {
                $q->orderBy('vigente_desde', 'desc')->orderBy('id', 'desc');
            }
        ])->orderBy('nombre')->get();

        $totalComprasMes = Compra::whereYear('fecha', now()->year)
            ->whereMonth('fecha', now()->month)
            ->sum('monto_total');

        $totalComprasHoy = Compra::whereDate('fecha', now()->toDateString())
            ->sum('monto_total');

        return view('compras.index', compact('compras', 'insumos', 'totalComprasMes', 'totalComprasHoy'));
    }

    public function store(Request $request)
    {
        SchemaSyncService::ensureUpdatedColumns();

        $request->validate([
            'fecha' => 'required|date',
            'metodo_pago' => 'required|in:efectivo,qr',
            'proveedor' => 'nullable|string|max:100',
            'comprobante_nro' => 'nullable|string|max:50',
            'items' => 'required|array|min:1',
            'items.*.insumo_id' => 'required|exists:insumos,id',
            'items.*.cantidad' => 'required|numeric|min:0.01',
            'items.*.precio_unitario' => 'required|numeric|min:0.01',
            'observaciones' => 'nullable|string',
        ]);

        $this->compraService->registrarCompra($request->all());

        return redirect()->route('compras.index')->with('success', 'Compra registrada exitosamente. El stock de insumos y el egreso en Bóveda (' . strtoupper($request->metodo_pago) . ') se actualizaron correctamente.');
    }
}
