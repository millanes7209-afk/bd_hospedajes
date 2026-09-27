<?php

namespace App\Http\Controllers;

use App\Models\Insumo;
use Illuminate\Http\Request;

class InsumoController extends Controller
{
    public function index()
    {
        $insumos = Insumo::with([
            'preciosHistorial' => function ($q) {
                $q->orderBy('vigente_desde', 'desc');
            }
        ])->orderBy('nombre')->get();

        return view('insumos.index', compact('insumos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'unidad_medida' => 'required|string|max:50',
        ]);

        Insumo::create([
            'nombre' => $request->nombre,
            'unidad_medida' => $request->unidad_medida,
        ]);

        return redirect()->route('insumos.index')->with('success', 'Insumo creado correctamente.');
    }
}
