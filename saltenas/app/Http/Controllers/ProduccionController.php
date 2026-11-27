<?php

namespace App\Http\Controllers;

use App\Models\Insumo;
use App\Models\Preparacion;
use App\Models\ProduccionArmado;
use App\Models\ProduccionTanda;
use App\Models\VarianteSaltena;
use App\Services\SchemaSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProduccionController extends Controller
{
    public function index(Request $request)
    {
        SchemaSyncService::ensureUpdatedColumns();

        $tab = $request->get('tab', 'tandas');
        if (!in_array($tab, ['tandas', 'armado', 'historial'])) {
            $tab = 'tandas';
        }

        // Preparaciones organizadas por tipo
        $preparaciones = Preparacion::with(['receta.insumo.preciosHistorial'])->orderBy('nombre')->get();
        $masas = $preparaciones->filter(fn($p) => strtolower($p->tipo_preparacion) === 'masa' || str_contains(strtolower($p->nombre), 'masa') || str_contains(strtolower($p->nombre), 'tapa'));
        $jigotes = $preparaciones->filter(fn($p) => strtolower($p->tipo_preparacion) === 'jigote' || str_contains(strtolower($p->nombre), 'jigote') || str_contains(strtolower($p->nombre), 'relleno'));

        // Si no se clasificó por nombre, mostrar todas en ambos selectores para libertad total
        if ($masas->isEmpty()) {
            $masas = $preparaciones;
        }
        if ($jigotes->isEmpty()) {
            $jigotes = $preparaciones;
        }

        $variantes = VarianteSaltena::where('activo', true)->orderBy('nombre')->get();
        $insumos = Insumo::orderBy('nombre')->get();

        // Métricas de Stock Intermedio (Cámara Fría / Almacén de Producción)
        $totalTapasStock = $preparaciones->filter(fn($p) => str_contains(strtolower($p->nombre), 'masa') || str_contains(strtolower($p->nombre), 'tapa') || strtolower($p->tipo_preparacion) === 'masa')->sum('stock_actual');
        $totalJigoteStock = $preparaciones->filter(fn($p) => str_contains(strtolower($p->nombre), 'jigote') || str_contains(strtolower($p->nombre), 'relleno') || strtolower($p->tipo_preparacion) === 'jigote')->sum('stock_actual');
        $totalSaltenasArmadas = $variantes->sum('stock_armadas');

        // Historiales recientes
        $historialTandas = ProduccionTanda::with('preparacion')->orderByDesc('fecha')->orderByDesc('id')->take(20)->get();
        $historialArmados = ProduccionArmado::with(['variante', 'tapaPreparacion', 'jigotePreparacion'])->orderByDesc('fecha')->orderByDesc('id')->take(20)->get();

        // Datos JSON para cálculo reactivo en Alpine.js
        $preparacionesJson = $preparaciones->map(fn($p) => [
            'id' => $p->id,
            'nombre' => $p->nombre,
            'tipo_preparacion' => $p->tipo_preparacion ?? 'masa',
            'rinde_cantidad' => (float)$p->rinde_cantidad,
            'stock_actual' => (float)$p->stock_actual,
            'costo_unitario' => (float)$p->costoEnFecha(),
            'costo_total_lote' => (float)$p->costoTotalLote(),
            'receta' => $p->receta->map(fn($r) => [
                'insumo_id' => $r->insumo_id,
                'insumo_nombre' => $r->insumo->nombre ?? 'Insumo',
                'unidad' => $r->insumo->unidad_medida ?? 'kg',
                'cantidad_base' => (float)$r->cantidad_usada,
                'stock_disponible' => (float)($r->insumo->stock_actual ?? 0),
                'precio_compra' => (float)($r->insumo->ultimo_precio ?? 0),
            ]),
        ])->values();

        $variantesJson = $variantes->map(fn($v) => [
            'id' => $v->id,
            'nombre' => $v->nombre,
            'stock_armadas' => (float)$v->stock_armadas,
        ])->values();

        return view('produccion.index', compact(
            'tab',
            'preparaciones',
            'masas',
            'jigotes',
            'variantes',
            'insumos',
            'totalTapasStock',
            'totalJigoteStock',
            'totalSaltenasArmadas',
            'historialTandas',
            'historialArmados',
            'preparacionesJson',
            'variantesJson'
        ));
    }

    /**
     * Paso 1: Registrar Preparación de Tanda (Masa o Jigote).
     * Descuenta los insumos de materia prima y suma al stock intermedio de tapas o jigote.
     */
    public function storeTanda(Request $request)
    {
        SchemaSyncService::ensureUpdatedColumns();

        $request->validate([
            'fecha' => 'required|date',
            'preparacion_id' => 'required|exists:preparaciones,id',
            'tandas' => 'required|numeric|min:0.1',
            'cantidad_producida' => 'required|numeric|min:0.5',
            'observaciones' => 'nullable|string|max:500',
        ], [
            'preparacion_id.required' => 'Debes seleccionar qué masa o preparación vas a elaborar.',
            'tandas.required' => 'Debes indicar cuántas tandas hiciste (ej. 1 tanda).',
            'cantidad_producida.required' => 'Debes indicar la cantidad de tapas contadas o porciones aproximadas obtenidas.',
        ]);

        $prep = Preparacion::with('receta.insumo')->findOrFail($request->preparacion_id);
        $numTandas = (float)$request->tandas;
        $cantidadProducida = (float)$request->cantidad_producida;

        DB::transaction(function () use ($request, $prep, $numTandas, $cantidadProducida) {
            $costoTotal = 0;

            // 1. Descontar insumos de almacén según la receta escalada
            foreach ($prep->receta as $item) {
                if ($item->insumo) {
                    $cantidadTotalInsumo = (float)$item->cantidad_usada * $numTandas;
                    $precioInsumo = (float)$item->insumo->ultimo_precio;
                    $costoTotal += ($cantidadTotalInsumo * $precioInsumo);

                    // Descontar del stock de almacén central de compras
                    $item->insumo->decrement('stock_actual', $cantidadTotalInsumo);
                }
            }

            // 2. Sumar al stock intermedio de tapas o jigote
            $prep->increment('stock_actual', $cantidadProducida);

            // 3. Registrar el evento de producción
            ProduccionTanda::create([
                'fecha' => $request->fecha,
                'preparacion_id' => $prep->id,
                'tandas' => $numTandas,
                'cantidad_producida' => $cantidadProducida,
                'costo_total' => $costoTotal,
                'observaciones' => $request->observaciones,
            ]);
        });

        $tipo = str_contains(strtolower($prep->nombre), 'jigote') ? 'porciones aproximadas de jigote' : 'tapas contadas de masa';
        return redirect()->route('produccion.index', ['tab' => 'tandas'])
            ->with('success', "¡Tanda registrada! Se descontaron los insumos del almacén y se sumaron +{$cantidadProducida} {$tipo} al stock intermedio listo en frío.");
    }

    /**
     * Paso 2: Registrar Armado de Salteñas en Mesa.
     * Descuenta las tapas y el jigote elegidos libremente, y suma al stock de salteñas armadas.
     */
    public function storeArmado(Request $request)
    {
        SchemaSyncService::ensureUpdatedColumns();

        $request->validate([
            'fecha' => 'required|date',
            'variante_id' => 'required|exists:variantes_saltena,id',
            'cantidad_armada' => 'required|numeric|min:1',
            'tapa_preparacion_id' => 'nullable|exists:preparaciones,id',
            'tapas_usadas' => 'nullable|numeric|min:0',
            'jigote_preparacion_id' => 'nullable|exists:preparaciones,id',
            'jigote_porciones_usadas' => 'nullable|numeric|min:0',
            'observaciones' => 'nullable|string|max:500',
        ], [
            'variante_id.required' => 'Debes seleccionar qué tipo de salteña armaste.',
            'cantidad_armada.required' => 'Debes indicar cuántas salteñas se armaron.',
        ]);

        $var = VarianteSaltena::findOrFail($request->variante_id);
        $cantidadArmada = (float)$request->cantidad_armada;
        $tapasUsadas = $request->tapas_usadas !== null && $request->tapas_usadas !== '' ? (float)$request->tapas_usadas : $cantidadArmada;
        $jigoteUsado = $request->jigote_porciones_usadas !== null && $request->jigote_porciones_usadas !== '' ? (float)$request->jigote_porciones_usadas : $cantidadArmada;

        DB::transaction(function () use ($request, $var, $cantidadArmada, $tapasUsadas, $jigoteUsado) {
            $costoTotalArmado = 0;

            // 1. Descontar Tapas de la Masa seleccionada libremente
            if (!empty($request->tapa_preparacion_id) && $tapasUsadas > 0) {
                $prepMasa = Preparacion::find($request->tapa_preparacion_id);
                if ($prepMasa) {
                    $prepMasa->decrement('stock_actual', $tapasUsadas);
                    $costoTotalArmado += ($tapasUsadas * $prepMasa->costoEnFecha());
                }
            }

            // 2. Descontar Porciones del Jigote seleccionado libremente (Aproximado)
            if (!empty($request->jigote_preparacion_id) && $jigoteUsado > 0) {
                $prepJigote = Preparacion::find($request->jigote_preparacion_id);
                if ($prepJigote) {
                    $prepJigote->decrement('stock_actual', $jigoteUsado);
                    $costoTotalArmado += ($jigoteUsado * $prepJigote->costoEnFecha());
                }
            }

            // 3. Incrementar el stock de salteñas armadas listas en central
            $var->increment('stock_armadas', $cantidadArmada);

            // 4. Registrar el evento de armado
            ProduccionArmado::create([
                'fecha' => $request->fecha,
                'variante_id' => $var->id,
                'cantidad_armada' => $cantidadArmada,
                'tapa_preparacion_id' => $request->tapa_preparacion_id ?: null,
                'tapas_usadas' => $tapasUsadas,
                'jigote_preparacion_id' => $request->jigote_preparacion_id ?: null,
                'jigote_porciones_usadas' => $jigoteUsado,
                'costo_total_armado' => $costoTotalArmado,
                'observaciones' => $request->observaciones,
            ]);
        });

        return redirect()->route('produccion.index', ['tab' => 'armado'])
            ->with('success', "¡Armado registrado! Se contabilizaron +{$cantidadArmada} Salteñas de '{$var->nombre}' listas y se descontaron las tapas y el jigote del stock intermedio.");
    }

    /**
     * Ajuste manual de stock de preparación (por si se rompieron tapas o sobró jigote al final del día)
     */
    public function ajustarStock(Request $request, $id)
    {
        SchemaSyncService::ensureUpdatedColumns();

        $request->validate([
            'nuevo_stock' => 'required|numeric|min:0',
        ]);

        $prep = Preparacion::findOrFail($id);
        $anterior = (float)$prep->stock_actual;
        $nuevo = (float)$request->nuevo_stock;
        $prep->stock_actual = $nuevo;
        $prep->save();

        return redirect()->back()->with('success', "Stock de '{$prep->nombre}' ajustado de {$anterior} a {$nuevo}.");
    }
}
