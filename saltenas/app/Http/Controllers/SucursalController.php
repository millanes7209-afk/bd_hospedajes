<?php

namespace App\Http\Controllers;

use App\Models\Sucursal;
use Illuminate\Http\Request;

class SucursalController extends Controller
{
    public function index()
    {
        $sucursales = Sucursal::orderBy('id', 'asc')->get();
        return view('sucursales.index', compact('sucursales'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'direccion' => 'nullable|string|max:255',
            'encargado' => 'nullable|string|max:100',
        ]);

        Sucursal::create([
            'nombre' => $request->nombre,
            'direccion' => $request->direccion,
            'encargado' => $request->encargado,
            'activa' => true,
        ]);

        return back()->with('success', 'Sucursal registrada con éxito.');
    }

    public function update(Request $request, $id)
    {
        $sucursal = Sucursal::findOrFail($id);
        $request->validate([
            'nombre' => 'required|string|max:100',
            'direccion' => 'nullable|string|max:255',
            'encargado' => 'nullable|string|max:100',
        ]);

        $sucursal->update([
            'nombre' => $request->nombre,
            'direccion' => $request->direccion,
            'encargado' => $request->encargado,
        ]);

        return back()->with('success', 'Sucursal actualizada.');
    }

    public function toggleEstado($id)
    {
        $sucursal = Sucursal::findOrFail($id);
        $sucursal->update(['activa' => !$sucursal->activa]);
        return back()->with('success', 'Estado de la sucursal actualizado.');
    }
}
