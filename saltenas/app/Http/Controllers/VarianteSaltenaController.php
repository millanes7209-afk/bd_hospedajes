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
            'componentes' => 'required|array|min:1',
        ]);

        $var = VarianteSaltena::create([
            'nombre' => $request->nombre,
            'precio_venta' => $request->precio_venta,
            'activo' => true,
        ]);

        foreach ($request->componentes as $comp) {
            VarianteReceta::create([
                'variante_id' => $var->id,
                'tipo_componente' => $comp['tipo_componente'],
                'insumo_id' => $comp['tipo_componente'] === 'insumo' ? $comp['insumo_id'] : null,
                'preparacion_id' => $comp['tipo_componente'] === 'preparacion' ? $comp['preparacion_id'] : null,
                'cantidad_usada' => $comp['cantidad_usada'],
            ]);
        }

        return redirect()->route('variantes.index')->with('success', 'Variante de Salteña creada exitosamente con su receta.');
    }
}
