<?php

namespace App\Http\Controllers;

use App\Models\Insumo;
use App\Models\CostoSaltena;
use Illuminate\Http\Request;

class CostoController extends Controller
{
    public function index()
    {
        $insumos = Insumo::orderBy('nombre', 'asc')->get();
        $costos = CostoSaltena::orderBy('nombre_variante', 'asc')->get();
        return view('costos.index', compact('insumos', 'costos'));
    }

    public function storeInsumo(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'unidad_medida' => 'required|string|max:30',
            'costo_unitario' => 'required|numeric|min:0',
        ]);

        Insumo::create($request->all());
        return back()->with('success', 'Insumo registrado correctamente.');
    }

    public function updateInsumo(Request $request, $id)
    {
        $insumo = Insumo::findOrFail($id);
        $request->validate([
            'nombre' => 'required|string|max:100',
            'unidad_medida' => 'required|string|max:30',
            'costo_unitario' => 'required|numeric|min:0',
        ]);

        $insumo->update($request->all());
        return back()->with('success', 'Insumo actualizado.');
    }

    public function storeCostoSaltena(Request $request)
    {
        $request->validate([
            'nombre_variante' => 'required|string|max:100',
            'costo_unidad' => 'required|numeric|min:0',
            'precio_venta' => 'required|numeric|min:0',
        ]);

        CostoSaltena::create($request->all());
        return back()->with('success', 'Variante de salteña registrada.');
    }

    public function updateCostoSaltena(Request $request, $id)
    {
        $costo = CostoSaltena::findOrFail($id);
        $request->validate([
            'nombre_variante' => 'required|string|max:100',
            'costo_unidad' => 'required|numeric|min:0',
            'precio_venta' => 'required|numeric|min:0',
        ]);

        $costo->update($request->all());
        return back()->with('success', 'Costo/Precio actualizado.');
    }
}
