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
        $tab = $request->get('tab', 'variantes');
        if (!in_array($tab, ['variantes', 'preparaciones', 'insumos'])) {
            $tab = 'variantes';
        }

        $variantes = VarianteSaltena::with(['recetas.insumo', 'recetas.preparacion', 'promociones'])->orderBy('nombre')->get();
        $insumos = Insumo::orderBy('nombre')->get();
        $preparaciones = Preparacion::with('recetas.insumo')->orderBy('nombre')->get();

        return view('productos.index', compact('variantes', 'insumos', 'preparaciones', 'tab'));
    }
}
