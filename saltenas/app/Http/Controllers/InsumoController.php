<?php

namespace App\Http\Controllers;

use App\Models\Insumo;
use App\Services\SchemaSyncService;
use Illuminate\Http\Request;

class InsumoController extends Controller
{
    public function index()
    {
        SchemaSyncService::ensureUpdatedColumns();

        $insumos = Insumo::with([
            'preciosHistorial' => function ($q) {
                $q->orderBy('vigente_desde', 'desc')->orderBy('id', 'desc');
            }
        ])->orderBy('nombre')->get();

        return view('insumos.index', compact('insumos'));
    }

    public function store(Request $request)
    {
        SchemaSyncService::ensureUpdatedColumns();

        $request->validate([
            'nombre' => 'required|string|max:255',
            'unidad_medida' => 'required|string|max:50',
            'stock_actual' => 'nullable|numeric|min:0',
            'stock_minimo' => 'nullable|numeric|min:0',
        ]);

        Insumo::create([
            'nombre' => $request->nombre,
            'unidad_medida' => $request->unidad_medida,
            'stock_actual' => $request->stock_actual ?? 0,
            'stock_minimo' => $request->stock_minimo ?? 0,
        ]);

        return redirect()->route('productos.index', ['tab' => 'insumos'])->with('success', 'Insumo creado correctamente.');
    }
}
