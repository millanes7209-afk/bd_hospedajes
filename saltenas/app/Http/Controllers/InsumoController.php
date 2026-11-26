<?php

namespace App\Http\Controllers;

use App\Models\Insumo;
use App\Services\SchemaSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        if ($request->has('stock_actual')) {
            $request->merge(['stock_actual' => str_replace(',', '.', (string)$request->stock_actual)]);
        }
        if ($request->has('stock_minimo')) {
            $request->merge(['stock_minimo' => str_replace(',', '.', (string)$request->stock_minimo)]);
        }

        $request->validate([
            'nombre' => 'required|string|max:255',
            'unidad_medida' => 'required|string|max:50',
            'stock_actual' => 'nullable|numeric|min:0',
            'stock_minimo' => 'nullable|numeric|min:0',
        ]);

        $nombre = trim($request->nombre);
        // Evitar duplicados por nombre
        $existente = Insumo::whereRaw('LOWER(nombre) = ?', [mb_strtolower($nombre)])->first();
        if ($existente) {
            return redirect()->route('productos.index', ['tab' => 'insumos'])
                ->with('warning', 'El insumo "' . strtoupper($nombre) . '" ya existe en el catálogo.');
        }

        Insumo::create([
            'nombre' => $nombre,
            'unidad_medida' => strtolower(trim($request->unidad_medida)),
            'stock_actual' => $request->stock_actual ? (float)$request->stock_actual : 0,
            'stock_minimo' => $request->stock_minimo ? (float)$request->stock_minimo : 0,
        ]);

        return redirect()->route('productos.index', ['tab' => 'insumos'])
            ->with('success', 'Insumo "' . strtoupper($nombre) . '" creado correctamente.');
    }

    public function update(Request $request, $id)
    {
        SchemaSyncService::ensureUpdatedColumns();

        if ($request->has('stock_actual')) {
            $request->merge(['stock_actual' => str_replace(',', '.', (string)$request->stock_actual)]);
        }
        if ($request->has('stock_minimo')) {
            $request->merge(['stock_minimo' => str_replace(',', '.', (string)$request->stock_minimo)]);
        }

        $request->validate([
            'nombre' => 'required|string|max:255',
            'unidad_medida' => 'required|string|max:50',
            'stock_actual' => 'nullable|numeric|min:0',
            'stock_minimo' => 'nullable|numeric|min:0',
        ]);

        $insumo = Insumo::findOrFail($id);
        $nombre = trim($request->nombre);

        // Verificar que no choque con otro insumo distinto
        $duplicado = Insumo::whereRaw('LOWER(nombre) = ?', [mb_strtolower($nombre)])
            ->where('id', '!=', $insumo->id)
            ->first();

        if ($duplicado) {
            return redirect()->route('productos.index', ['tab' => 'insumos'])
                ->with('error', 'Ya existe otro insumo con el nombre "' . strtoupper($nombre) . '".');
        }

        $insumo->update([
            'nombre' => $nombre,
            'unidad_medida' => strtolower(trim($request->unidad_medida)),
            'stock_actual' => $request->has('stock_actual') && $request->stock_actual !== '' ? (float)$request->stock_actual : $insumo->stock_actual,
            'stock_minimo' => $request->has('stock_minimo') && $request->stock_minimo !== '' ? (float)$request->stock_minimo : $insumo->stock_minimo,
        ]);

        return redirect()->route('productos.index', ['tab' => 'insumos'])
            ->with('success', 'Insumo "' . strtoupper($insumo->nombre) . '" actualizado correctamente.');
    }

    public function destroy($id)
    {
        SchemaSyncService::ensureUpdatedColumns();

        $insumo = Insumo::findOrFail($id);

        $enRecetasVar = DB::table('variante_receta')->where('insumo_id', $insumo->id)->count();
        $enRecetasPrep = DB::table('preparacion_receta')->where('insumo_id', $insumo->id)->count();
        $enCompras = DB::table('compra_detalles')->where('insumo_id', $insumo->id)->count();

        if ($enRecetasVar > 0 || $enRecetasPrep > 0 || $enCompras > 0) {
            return redirect()->route('productos.index', ['tab' => 'insumos'])
                ->with('error', 'No se puede eliminar "' . strtoupper($insumo->nombre) . '" porque está en uso (' . ($enRecetasVar + $enRecetasPrep) . ' recetas y ' . $enCompras . ' compras asociadas). Puedes editar su nombre o stock en vez de borrarlo.');
        }

        $nombre = $insumo->nombre;
        $insumo->delete();

        return redirect()->route('productos.index', ['tab' => 'insumos'])
            ->with('success', 'Insumo "' . strtoupper($nombre) . '" eliminado correctamente.');
    }
}
