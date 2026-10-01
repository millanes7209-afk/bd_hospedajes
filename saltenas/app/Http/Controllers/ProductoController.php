<?php

namespace App\Http\Controllers;

use App\Models\Insumo;
use App\Models\Preparacion;
use App\Models\VarianteSaltena;
use Illuminate\Http\Request;

class ProductoController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'insumos');
        if (!in_array($tab, ['insumos', 'preparaciones', 'variantes'])) {
            $tab = 'insumos';
        }

        $insumos = Insumo::orderBy('nombre')->get();
        $preparaciones = Preparacion::with('recetas.insumo')->orderBy('nombre')->get();
        $variantes = VarianteSaltena::with(['recetas.insumo', 'recetas.preparacion', 'promociones'])->orderBy('nombre')->get();

        return view('productos.index', compact('insumos', 'preparaciones', 'variantes', 'tab'));
    }
}
