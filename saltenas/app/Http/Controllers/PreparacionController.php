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
                'rinde_cantidad' => 'required|numeric|min:0.01',
                'insumos' => 'required|array|min:1',
                'insumos.*.insumo_id' => 'required|exists:insumos,id',
                'insumos.*.cantidad_usada' => 'required|numeric|min:0.0001',
            ], [
                'nombre.required' => 'El nombre de la preparación (ej. Masa) es obligatorio.',
                'rinde_cantidad.required' => 'El rendimiento (cantidad de salteñas) es obligatorio.',
                'rinde_cantidad.min' => 'El rendimiento debe ser mayor a 0.',
                'insumos.required' => 'Debes agregar al menos un insumo a la preparación.',
                'insumos.*.cantidad_usada.required' => 'Debes indicar la cantidad de cada insumo.',
            ]);

            $prep = null;
            DB::transaction(function () use ($request, &$prep) {
                $prep = Preparacion::create([
                    'nombre' => trim($request->nombre),
                    'rinde_cantidad' => (float)$request->rinde_cantidad,
                ]);

                foreach ($request->insumos as $item) {
                    $cant = (float)str_replace(',', '.', (string)$item['cantidad_usada']);
                    if ($cant > 0 && !empty($item['insumo_id'])) {
                        PreparacionReceta::create([
                            'preparacion_id' => $prep->id,
                            'insumo_id' => (int)$item['insumo_id'],
                            'cantidad_usada' => $cant,
                        ]);
                    }
                }
            });

            if ($isJson) {
                return response()->json([
                    'success' => true,
                    'message' => 'Preparación (Masa) creada exitosamente.',
                    'preparacion' => $prep->load('receta.insumo.preciosHistorial'),
                ], 200);
            }

            return redirect()->route('productos.index', ['tab' => 'preparaciones'])
                ->with('success', 'Preparación (ej. Masa) creada exitosamente.');

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
                'rinde_cantidad' => 'required|numeric|min:0.01',
                'insumos' => 'required|array|min:1',
                'insumos.*.insumo_id' => 'required|exists:insumos,id',
                'insumos.*.cantidad_usada' => 'required|numeric|min:0.0001',
            ]);

            $prep = Preparacion::findOrFail($id);

            DB::transaction(function () use ($request, $prep) {
                $prep->update([
                    'nombre' => trim($request->nombre),
                    'rinde_cantidad' => (float)$request->rinde_cantidad,
                ]);

                PreparacionReceta::where('preparacion_id', $prep->id)->delete();

                foreach ($request->insumos as $item) {
                    $cant = (float)str_replace(',', '.', (string)$item['cantidad_usada']);
                    if ($cant > 0 && !empty($item['insumo_id'])) {
                        PreparacionReceta::create([
                            'preparacion_id' => $prep->id,
                            'insumo_id' => (int)$item['insumo_id'],
                            'cantidad_usada' => $cant,
                        ]);
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
