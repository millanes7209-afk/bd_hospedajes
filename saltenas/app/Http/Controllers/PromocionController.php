<?php

namespace App\Http\Controllers;

use App\Models\Promocion;
use App\Models\VarianteSaltena;
use Illuminate\Http\Request;

class PromocionController extends Controller
{
    public function index()
    {
        $promociones = Promocion::with('variante')->orderBy('nombre')->get();
        $variantes = VarianteSaltena::where('activo', true)->orderBy('nombre')->get();

        return view('promociones.index', compact('promociones', 'variantes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'variante_id' => 'required|exists:variantes_saltena,id',
            'nombre' => 'required|string|max:255',
            'unidades_por_paquete' => 'required|integer|min:2',
            'precio_paquete' => 'required|numeric|min:0.01',
        ]);

        Promocion::create([
            'variante_id' => $request->variante_id,
            'nombre' => $request->nombre,
            'unidades_por_paquete' => $request->unidades_por_paquete,
            'precio_paquete' => $request->precio_paquete,
            'activo' => true,
        ]);

        return redirect()->route('promociones.index')->with('success', 'Promoción explícita registrada correctamente.');
    }
}
