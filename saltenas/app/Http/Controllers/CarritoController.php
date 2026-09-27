<?php

namespace App\Http\Controllers;

use App\Models\Carrito;
use Illuminate\Http\Request;

class CarritoController extends Controller
{
    public function index()
    {
        $carritos = Carrito::orderBy('nombre')->get();
        return view('carritos.index', compact('carritos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'zona' => 'nullable|string|max:255',
        ]);

        Carrito::create([
            'nombre' => $request->nombre,
            'zona' => $request->zona,
            'activo' => true,
        ]);

        return redirect()->route('carritos.index')->with('success', 'Carrito / Punto de venta registrado.');
    }

    public function toggleEstado($id)
    {
        $carrito = Carrito::findOrFail($id);
        $carrito->activo = !$carrito->activo;
        $carrito->save();

        return redirect()->route('carritos.index')->with('success', 'Estado del carrito actualizado.');
    }
}
