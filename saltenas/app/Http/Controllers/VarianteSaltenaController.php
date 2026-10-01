<?php

namespace App\Http\Controllers;

use App\Models\Insumo;
use App\Models\Preparacion;
use App\Models\VarianteReceta;
use App\Models\VarianteSaltena;
use Illuminate\Http\Request;

class VarianteSaltenaController extends Controller
{
    public function index()
    {
        $variantes = VarianteSaltena::with(['recetas.insumo', 'recetas.preparacion', 'promociones'])->orderBy('nombre')->get();
        $insumos = Insumo::orderBy('nombre')->get();
        $preparaciones = Preparacion::orderBy('nombre')->get();

        return view('variantes.index', compact('variantes', 'insumos', 'preparaciones'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'precio_venta' => 'required|numeric|min:0.01',
            'componentes' => 'nullable|array',
        ]);

        $var = VarianteSaltena::create([
            'nombre' => $request->nombre,
            'precio_venta' => $request->precio_venta,
            'activo' => true,
        ]);

        if ($request->has('componentes') && is_array($request->componentes)) {
            foreach ($request->componentes as $comp) {
                $tipo = $comp['tipo_componente'] ?? null;
                $insumoId = $comp['insumo_id'] ?? null;
                $preparacionId = $comp['preparacion_id'] ?? null;
                $cantidad = $comp['cantidad_usada'] ?? null;

                // Solo guardar si se seleccionó un insumo o preparación válido con cantidad > 0
                if ($tipo === 'insumo' && $insumoId && $cantidad > 0) {
                    VarianteReceta::create([
                        'variante_id' => $var->id,
                        'tipo_componente' => 'insumo',
                        'insumo_id' => $insumoId,
                        'preparacion_id' => null,
                        'cantidad_usada' => $cantidad,
                    ]);
                } elseif ($tipo === 'preparacion' && $preparacionId && $cantidad > 0) {
                    VarianteReceta::create([
                        'variante_id' => $var->id,
                        'tipo_componente' => 'preparacion',
                        'insumo_id' => null,
                        'preparacion_id' => $preparacionId,
                        'cantidad_usada' => $cantidad,
                    ]);
                }
            }
        }

        return redirect()->route('productos.index', ['tab' => 'variantes'])->with('success', 'Variante de Salteña creada exitosamente.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'precio_venta' => 'required|numeric|min:0.01',
            'componentes' => 'nullable|array',
        ]);

        $var = VarianteSaltena::findOrFail($id);
        $var->update([
            'nombre' => $request->nombre,
            'precio_venta' => $request->precio_venta,
        ]);

        if ($request->has('componentes')) {
            VarianteReceta::where('variante_id', $var->id)->delete();
            if (is_array($request->componentes)) {
                foreach ($request->componentes as $comp) {
                    $tipo = $comp['tipo_componente'] ?? null;
                    $insumoId = $comp['insumo_id'] ?? null;
                    $preparacionId = $comp['preparacion_id'] ?? null;
                    $cantidad = $comp['cantidad_usada'] ?? null;

                    if ($tipo === 'insumo' && $insumoId && $cantidad > 0) {
                        VarianteReceta::create([
                            'variante_id' => $var->id,
                            'tipo_componente' => 'insumo',
                            'insumo_id' => $insumoId,
                            'preparacion_id' => null,
                            'cantidad_usada' => $cantidad,
                        ]);
                    } elseif ($tipo === 'preparacion' && $preparacionId && $cantidad > 0) {
                        VarianteReceta::create([
                            'variante_id' => $var->id,
                            'tipo_componente' => 'preparacion',
                            'insumo_id' => null,
                            'preparacion_id' => $preparacionId,
                            'cantidad_usada' => $cantidad,
                        ]);
                    }
                }
            }
        }

        return redirect()->route('productos.index', ['tab' => 'variantes'])->with('success', 'Variante de Salteña actualizada exitosamente.');
    }
}
