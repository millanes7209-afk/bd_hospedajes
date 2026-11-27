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
        ])
        ->orderByRaw('CASE WHEN stock_actual > 0 THEN 0 ELSE 1 END')
        ->orderBy('nombre')
        ->get();

        $totalComprasMes = Compra::whereYear('fecha', now()->year)
            ->whereMonth('fecha', now()->month)
            ->sum('monto_total');

        $totalComprasHoy = Compra::whereDate('fecha', now()->toDateString())
            ->sum('monto_total');

        $insumosList = $insumos->map(function ($i) {
            return [
                'id' => $i->id,
                'nombre' => $i->nombre,
                'unidad_medida' => $i->unidad_medida,
                'ultimo_precio' => (float)$i->ultimo_precio,
            ];
        })->values();

        return view('compras.index', compact('compras', 'insumos', 'insumosList', 'totalComprasMes', 'totalComprasHoy'));
    }

    public function store(Request $request)
    {
        SchemaSyncService::ensureUpdatedColumns();

        // Normalizar comas por puntos en cantidades y precios
        if ($request->has('items') && is_array($request->items)) {
            $items = $request->items;
            foreach ($items as $k => $item) {
                if (isset($item['cantidad'])) {
                    $items[$k]['cantidad'] = str_replace(',', '.', (string)$item['cantidad']);
                }
                if (isset($item['precio_unitario'])) {
                    $items[$k]['precio_unitario'] = str_replace(',', '.', (string)$item['precio_unitario']);
                }
            }
            $request->merge(['items' => $items]);
        }

        $request->validate([
            'fecha' => 'required|date',
            'metodo_pago' => 'required|in:efectivo,qr',
            'proveedor' => 'nullable|string|max:100',
            'comprobante_nro' => 'nullable|string|max:50',
            'items' => 'required|array|min:1',
            'items.*.cantidad' => 'required|numeric|min:0.001',
            'items.*.precio_unitario' => 'required|numeric|min:0.01',
            'observaciones' => 'nullable|string',
        ], [
            'items.required' => 'Debes agregar al menos un insumo a la compra.',
            'items.*.cantidad.required' => 'La cantidad es obligatoria en todos los ítems.',
            'items.*.cantidad.min' => 'La cantidad o peso debe ser mayor a 0.',
            'items.*.precio_unitario.required' => 'El precio unitario es obligatorio.',
            'items.*.precio_unitario.min' => 'El precio unitario debe ser mayor a 0.',
        ]);

        $this->compraService->registrarCompra($request->all());

        return redirect()->route('compras.index')->with('success', 'Compra registrada exitosamente en ' . strtoupper($request->metodo_pago) . ' y stock actualizado.');
    }
}
