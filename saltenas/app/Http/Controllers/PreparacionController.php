<?php

namespace App\Http\Controllers;

use App\Models\Insumo;
use App\Models\Preparacion;
use App\Models\PreparacionReceta;
use App\Services\SchemaSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PreparacionController extends Controller
{
    public function index()
    {
        SchemaSyncService::ensureUpdatedColumns();

        $preparaciones = Preparacion::with('receta.insumo')->orderBy('nombre')->get();
        $insumos = Insumo::orderBy('nombre')->get();

        return view('preparaciones.index', compact('preparaciones', 'insumos'));
    }

    public function store(Request $request)
    {
        SchemaSyncService::ensureUpdatedColumns();

        if ($request->has('rinde_cantidad')) {
            $request->merge([
                'rinde_cantidad' => str_replace(',', '.', (string)$request->rinde_cantidad)
            ]);
        }

        try {
            $request->validate([
                'nombre' => 'required|string|max:255',
                'rinde_cantidad' => 'required|numeric|min:0.01',
                'insumos' => 'required|array|min:1',
                'insumos.*.insumo_id' => 'required|exists:insumos,id',
                'insumos.*.cantidad_usada' => 'required|numeric|min:0.0001',
            ]);

            DB::transaction(function () use ($request) {
                $prep = Preparacion::create([
                    'nombre' => trim($request->nombre),
                    'rinde_cantidad' => (float)$request->rinde_cantidad,
                ]);

                foreach ($request->insumos as $item) {
                    $cant = (float)str_replace(',', '.', (string)$item['cantidad_usada']);
                    PreparacionReceta::create([
                        'preparacion_id' => $prep->id,
                        'insumo_id' => $item['insumo_id'],
                        'cantidad_usada' => $cant,
                    ]);
                }
            });

            return redirect()->route('productos.index', ['tab' => 'preparaciones'])
                ->with('success', 'Preparación (ej. Masa) creada exitosamente.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->route('productos.index', ['tab' => 'preparaciones'])
                ->withErrors($e->validator)
                ->withInput()
                ->with('error', 'Error al crear la preparación: Por favor revisa los campos requeridos.');
        } catch (\Throwable $e) {
            return redirect()->route('productos.index', ['tab' => 'preparaciones'])
                ->withInput()
                ->with('error', 'Error al crear la preparación: ' . $e->getMessage());
        }
    }
}
