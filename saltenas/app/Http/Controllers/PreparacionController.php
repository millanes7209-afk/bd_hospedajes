<?php

namespace App\Http\Controllers;

use App\Models\Insumo;
use App\Models\Preparacion;
use App\Models\PreparacionReceta;
use Illuminate\Http\Request;

class PreparacionController extends Controller
{
    public function index()
    {
        $preparaciones = Preparacion::with('receta.insumo')->orderBy('nombre')->get();
        $insumos = Insumo::orderBy('nombre')->get();

        return view('preparaciones.index', compact('preparaciones', 'insumos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'rinde_cantidad' => 'required|numeric|min:0.01',
            'insumos' => 'required|array|min:1',
            'insumos.*.insumo_id' => 'required|exists:insumos,id',
            'insumos.*.cantidad_usada' => 'required|numeric|min:0.0001',
        ]);

        $prep = Preparacion::create([
            'nombre' => $request->nombre,
            'rinde_cantidad' => $request->rinde_cantidad,
        ]);

        foreach ($request->insumos as $item) {
            PreparacionReceta::create([
                'preparacion_id' => $prep->id,
                'insumo_id' => $item['insumo_id'],
                'cantidad_usada' => $item['cantidad_usada'],
            ]);
        }

        return redirect()->route('productos.index', ['tab' => 'preparaciones'])->with('success', 'Preparación (ej. Masa) creada exitosamente.');
    }
}
