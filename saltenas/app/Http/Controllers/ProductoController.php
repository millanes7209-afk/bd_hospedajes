<?php

namespace App\Http\Controllers;

use App\Models\Insumo;
use App\Models\Preparacion;
use App\Models\VarianteSaltena;
use App\Services\SchemaSyncService;
use Illuminate\Http\Request;

class ProductoController extends Controller
{
    public function index(Request $request)
    {
        SchemaSyncService::ensureUpdatedColumns();

        $tab = $request->get('tab', old('tab', 'insumos'));
        if (!in_array($tab, ['insumos', 'preparaciones', 'variantes'])) {
            $tab = 'insumos';
        }

        $insumos = Insumo::with([
            'preciosHistorial' => function ($q) {
                $q->orderBy('vigente_desde', 'desc')->orderBy('id', 'desc');
            }
        ])->orderBy('nombre')->get();

        $preparaciones = Preparacion::with('receta.insumo')->orderBy('nombre')->get();
        $variantes = VarianteSaltena::with(['recetas.insumo', 'recetas.preparacion', 'promociones'])->orderBy('nombre')->get();

        return view('productos.index', compact('insumos', 'preparaciones', 'variantes', 'tab'));
    }
}
