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

        $preparaciones = Preparacion::with(['receta.insumo.preciosHistorial'])->orderBy('nombre')->get();
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

        $isJson = $request->ajax() || $request->wantsJson();

        try {
            $request->validate([
                'nombre' => 'required|string|max:255',
                'tipo_preparacion' => 'nullable|in:masa,jigote',
                'rinde_cantidad' => 'required|numeric|min:0.01',
                'insumos' => 'required|array|min:1',
                'insumos.*.insumo_id' => 'required|exists:insumos,id',
                'insumos.*.cantidad_usada' => 'required|numeric|min:0.0001',
            ], [
                'nombre.required' => 'El nombre de la preparación (ej. Masa o Jigote) es obligatorio.',
                'rinde_cantidad.required' => 'El rendimiento (cantidad de salteñas/porciones) es obligatorio.',
                'rinde_cantidad.min' => 'El rendimiento debe ser mayor a 0.',
                'insumos.required' => 'Debes agregar al menos un insumo a la preparación.',
                'insumos.*.cantidad_usada.required' => 'Debes indicar la cantidad de cada insumo.',
            ]);

            $tipo = $request->input('tipo_preparacion', 'masa');
            if (!in_array($tipo, ['masa', 'jigote'])) {
                $nombreLow = strtolower($request->nombre);
                $tipo = (str_contains($nombreLow, 'jigote') || str_contains($nombreLow, 'relleno') || str_contains($nombreLow, 'queso') || str_contains($nombreLow, 'carne') || str_contains($nombreLow, 'pollo')) ? 'jigote' : 'masa';
            }

            $prep = null;
            DB::transaction(function () use ($request, $tipo, &$prep) {
                $prep = Preparacion::create([
                    'nombre' => trim($request->nombre),
                    'tipo_preparacion' => $tipo,
                    'rinde_cantidad' => (float)$request->rinde_cantidad,
                    'stock_actual' => 0,
                ]);

                foreach ($request->insumos as $item) {
                    $cant = (float)str_replace(',', '.', (string)($item['cantidad_usada'] ?? 0));
                    $insumoId = (int)($item['insumo_id'] ?? 0);
                    $unidadIngresada = strtolower(trim($item['unidad_ingresada'] ?? ''));

                    if ($cant > 0 && $insumoId > 0) {
                        $insumo = Insumo::find($insumoId);
                        if ($insumo) {
                            $baseUnidad = strtolower(trim($insumo->unidad_medida));
                            // Si el insumo está en kg y el usuario ingresó gramos (g)
                            if ($baseUnidad === 'kg' && in_array($unidadIngresada, ['g', 'gr', 'gramos', 'gramo'])) {
                                $cant = $cant / 1000;
                            } elseif (in_array($baseUnidad, ['litro', 'l']) && in_array($unidadIngresada, ['ml', 'mililitro'])) {
                                $cant = $cant / 1000;
                            }

                            PreparacionReceta::create([
                                'preparacion_id' => $prep->id,
                                'insumo_id' => $insumo->id,
                                'cantidad_usada' => $cant,
                            ]);
                        }
                    }
                }
            });

            if ($isJson) {
                return response()->json([
                    'success' => true,
                    'message' => 'Preparación creada exitosamente.',
                    'preparacion' => $prep->load('receta.insumo.preciosHistorial'),
                ], 200);
            }

            return redirect()->route('productos.index', ['tab' => 'preparaciones'])
                ->with('success', "Preparación '{$prep->nombre}' guardada exitosamente.");

        } catch (\Illuminate\Validation\ValidationException $e) {
            $firstError = collect($e->errors())->flatten()->first() ?? 'Error de validación.';
            if ($isJson) {
                return response()->json([
                    'success' => false,
                    'error' => $firstError,
                    'errors' => $e->errors(),
                ], 422);
            }
            return redirect()->route('productos.index', ['tab' => 'preparaciones'])
                ->withErrors($e->validator)
                ->withInput()
                ->with('error', 'Error al crear la preparación: ' . $firstError);
        } catch (\Throwable $e) {
            if ($isJson) {
                return response()->json([
                    'success' => false,
                    'error' => 'Error en base de datos: ' . $e->getMessage(),
                ], 500);
            }
            return redirect()->route('productos.index', ['tab' => 'preparaciones'])
                ->withInput()
                ->with('error', 'Error al crear la preparación: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        SchemaSyncService::ensureUpdatedColumns();

        if ($request->has('rinde_cantidad')) {
            $request->merge([
                'rinde_cantidad' => str_replace(',', '.', (string)$request->rinde_cantidad)
            ]);
        }

        $isJson = $request->ajax() || $request->wantsJson();

        try {
            $request->validate([
                'nombre' => 'required|string|max:255',
                'tipo_preparacion' => 'nullable|in:masa,jigote',
                'rinde_cantidad' => 'required|numeric|min:0.01',
                'insumos' => 'required|array|min:1',
                'insumos.*.insumo_id' => 'required|exists:insumos,id',
                'insumos.*.cantidad_usada' => 'required|numeric|min:0.0001',
            ]);

            $prep = Preparacion::findOrFail($id);
            $tipo = $request->input('tipo_preparacion', $prep->tipo_preparacion ?? 'masa');

            DB::transaction(function () use ($request, $prep, $tipo) {
                $prep->update([
                    'nombre' => trim($request->nombre),
                    'tipo_preparacion' => $tipo,
                    'rinde_cantidad' => (float)$request->rinde_cantidad,
                ]);

                PreparacionReceta::where('preparacion_id', $prep->id)->delete();

                foreach ($request->insumos as $item) {
                    $cant = (float)str_replace(',', '.', (string)($item['cantidad_usada'] ?? 0));
                    $insumoId = (int)($item['insumo_id'] ?? 0);
                    $unidadIngresada = strtolower(trim($item['unidad_ingresada'] ?? ''));

                    if ($cant > 0 && $insumoId > 0) {
                        $insumo = Insumo::find($insumoId);
                        if ($insumo) {
                            $baseUnidad = strtolower(trim($insumo->unidad_medida));
                            if ($baseUnidad === 'kg' && in_array($unidadIngresada, ['g', 'gr', 'gramos', 'gramo'])) {
                                $cant = $cant / 1000;
                            } elseif (in_array($baseUnidad, ['litro', 'l']) && in_array($unidadIngresada, ['ml', 'mililitro'])) {
                                $cant = $cant / 1000;
                            }

                            PreparacionReceta::create([
                                'preparacion_id' => $prep->id,
                                'insumo_id' => $insumo->id,
                                'cantidad_usada' => $cant,
                            ]);
                        }
                    }
                }
            });

            if ($isJson) {
                return response()->json([
                    'success' => true,
                    'message' => 'Preparación actualizada exitosamente.',
                    'preparacion' => $prep->fresh()->load('receta.insumo.preciosHistorial'),
                ], 200);
            }

            return redirect()->route('productos.index', ['tab' => 'preparaciones'])
                ->with('success', 'Preparación actualizada exitosamente.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            $firstError = collect($e->errors())->flatten()->first() ?? 'Error de validación.';
            if ($isJson) {
                return response()->json([
                    'success' => false,
                    'error' => $firstError,
                    'errors' => $e->errors(),
                ], 422);
            }
            return redirect()->route('productos.index', ['tab' => 'preparaciones'])
                ->withErrors($e->validator)
                ->withInput()
                ->with('error', 'Error al actualizar la preparación: ' . $firstError);
        } catch (\Throwable $e) {
            if ($isJson) {
                return response()->json([
                    'success' => false,
                    'error' => 'Error en base de datos: ' . $e->getMessage(),
                ], 500);
            }
            return redirect()->route('productos.index', ['tab' => 'preparaciones'])
                ->withInput()
                ->with('error', 'Error al actualizar la preparación: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        SchemaSyncService::ensureUpdatedColumns();

        try {
            $prep = Preparacion::findOrFail($id);
            $nombre = $prep->nombre;
            $prep->delete();

            return redirect()->route('productos.index', ['tab' => 'preparaciones'])
                ->with('success', "Preparación '{$nombre}' eliminada correctamente.");
        } catch (\Throwable $e) {
            return redirect()->route('productos.index', ['tab' => 'preparaciones'])
                ->with('error', 'Error al eliminar la preparación: ' . $e->getMessage());
        }
    }
}
