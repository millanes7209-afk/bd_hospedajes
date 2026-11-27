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
        if (!in_array($tab, ['insumos', 'preparaciones', 'variantes', 'costeo'])) {
            $tab = 'insumos';
        }

        $insumos = Insumo::with([
            'preciosHistorial' => function ($q) {
                $q->orderBy('vigente_desde', 'desc')->orderBy('id', 'desc');
            }
        ])
        ->orderByRaw('CASE WHEN stock_actual > 0 THEN 0 ELSE 1 END')
        ->orderBy('nombre')
        ->get();

        $preparaciones = Preparacion::with([
            'receta.insumo.preciosHistorial'
        ])->orderBy('nombre')->get();

        $variantes = VarianteSaltena::with([
            'recetas.insumo.preciosHistorial',
            'recetas.preparacion.receta.insumo.preciosHistorial',
            'promociones'
        ])->orderBy('nombre')->get();

        // Métricas de Costeo Dinámico (Fase 2)
        $variantesConCosto = $variantes->filter(fn($v) => $v->costo_produccion > 0);
        $costoPromedio = $variantesConCosto->isNotEmpty() ? $variantesConCosto->avg('costo_produccion') : 0;
        $margenPromedio = $variantesConCosto->isNotEmpty() ? $variantesConCosto->avg(fn($v) => $v->margenPorcentaje()) : 0;
        $varianteMasRentable = $variantesConCosto->sortByDesc(fn($v) => $v->margenPorcentaje())->first();
        $insumosConPrecio = $insumos->filter(fn($i) => $i->ultimo_precio > 0)->count();

        // Datos JSON para reactividad instantánea con Alpine.js
        $insumosJson = $insumos->map(fn($i) => [
            'id' => $i->id,
            'nombre' => $i->nombre,
            'unidad_medida' => strtolower($i->unidad_medida),
            'ultimo_precio' => (float)$i->ultimo_precio,
        ])->values();

        $preparacionesJson = $preparaciones->map(fn($p) => [
            'id' => $p->id,
            'nombre' => $p->nombre,
            'rinde_cantidad' => (float)$p->rinde_cantidad,
            'costo_unitario' => (float)$p->costoEnFecha(),
            'costo_total_lote' => (float)$p->costoTotalLote(),
            'insumos' => $p->receta->map(fn($r) => [
                'insumo_id' => $r->insumo_id,
                'cantidad_usada' => (float)$r->cantidad_usada,
            ]),
        ])->values();

        $variantesJson = $variantes->map(fn($v) => [
            'id' => $v->id,
            'nombre' => $v->nombre,
            'precio_venta' => (float)$v->precio_venta,
            'costo_produccion' => (float)$v->costo_produccion,
            'margen_bruto' => (float)$v->margenBruto(),
            'margen_porcentaje' => (float)$v->margenPorcentaje(),
            'estado_margen' => $v->estadoMargen(),
            'activo' => (bool)$v->activo,
            'componentes' => $v->recetas->map(fn($r) => [
                'tipo_componente' => $r->tipo_componente,
                'insumo_id' => $r->insumo_id,
                'preparacion_id' => $r->preparacion_id,
                'cantidad_usada' => (float)$r->cantidad_usada,
            ]),
        ])->values();

        return view('productos.index', compact(
            'insumos',
            'preparaciones',
            'variantes',
            'tab',
            'costoPromedio',
            'margenPromedio',
            'varianteMasRentable',
            'insumosConPrecio',
            'insumosJson',
            'preparacionesJson',
            'variantesJson'
        ));
    }
}
