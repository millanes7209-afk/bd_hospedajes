<?php

namespace App\Http\Controllers;

use App\Models\Insumo;
use App\Models\Preparacion;
use App\Models\VarianteReceta;
use App\Models\VarianteSaltena;
use App\Services\SchemaSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VarianteSaltenaController extends Controller
{
    public function index()
    {
        SchemaSyncService::ensureUpdatedColumns();

        $variantes = VarianteSaltena::with([
            'recetas.insumo.preciosHistorial',
            'recetas.preparacion.receta.insumo.preciosHistorial',
            'promociones'
        ])->orderBy('nombre')->get();

        $insumos = Insumo::orderBy('nombre')->get();
        $preparaciones = Preparacion::orderBy('nombre')->get();

        return view('variantes.index', compact('variantes', 'insumos', 'preparaciones'));
    }

    public function store(Request $request)
    {
        SchemaSyncService::ensureUpdatedColumns();

        if ($request->has('precio_venta')) {
            $request->merge([
                'precio_venta' => str_replace(',', '.', (string)$request->precio_venta)
            ]);
        }

        $isJson = $request->ajax() || $request->wantsJson();

        try {
            $request->validate([
                'nombre' => 'required|string|max:255',
                'precio_venta' => 'required|numeric|min:0.01',
                'componentes' => 'nullable|array',
            ], [
                'nombre.required' => 'El nombre de la salteña es obligatorio.',
                'precio_venta.required' => 'El precio de venta al público es obligatorio.',
                'precio_venta.numeric' => 'El precio de venta debe ser un número válido (ej. 8.00).',
                'precio_venta.min' => 'El precio de venta debe ser mayor a 0.',
            ]);

            $createdVar = null;
            DB::transaction(function () use ($request, &$createdVar) {
                $createdVar = VarianteSaltena::create([
                    'nombre' => trim($request->nombre),
                    'precio_venta' => (float)$request->precio_venta,
                    'activo' => true,
                ]);

                if ($request->has('componentes') && is_array($request->componentes)) {
                    foreach ($request->componentes as $comp) {
                        $tipo = $comp['tipo_componente'] ?? null;
                        $insumoId = !empty($comp['insumo_id']) ? (int)$comp['insumo_id'] : null;
                        $preparacionId = !empty($comp['preparacion_id']) ? (int)$comp['preparacion_id'] : null;
                        $cantidadStr = !empty($comp['cantidad_usada']) ? str_replace(',', '.', (string)$comp['cantidad_usada']) : '0';
                        $cantidad = (float)$cantidadStr;

                        if ($cantidad <= 0) {
                            continue;
                        }

                        if ($tipo === 'insumo' && $insumoId) {
                            VarianteReceta::create([
                                'variante_id' => $createdVar->id,
                                'tipo_componente' => 'insumo',
                                'insumo_id' => $insumoId,
                                'preparacion_id' => null,
                                'cantidad_usada' => $cantidad,
                            ]);
                        } elseif ($tipo === 'preparacion' && $preparacionId) {
                            VarianteReceta::create([
                                'variante_id' => $createdVar->id,
                                'tipo_componente' => 'preparacion',
                                'insumo_id' => null,
                                'preparacion_id' => $preparacionId,
                                'cantidad_usada' => $cantidad,
                            ]);
                        }
                    }
                }
            });

            if ($isJson) {
                return response()->json([
                    'success' => true,
                    'message' => 'Variante de Salteña creada exitosamente.',
                    'variante' => $createdVar->fresh()->load([
                        'recetas.insumo.preciosHistorial',
                        'recetas.preparacion.receta.insumo.preciosHistorial'
                    ]),
                ], 200);
            }

            return redirect()->route('productos.index', ['tab' => 'variantes'])
                ->with('success', 'Variante de Salteña creada exitosamente.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            $firstError = collect($e->errors())->flatten()->first() ?? 'Error de validación.';
            if ($isJson) {
                return response()->json([
                    'success' => false,
                    'error' => $firstError,
                    'errors' => $e->errors(),
                ], 422);
            }
            return redirect()->route('productos.index', ['tab' => 'variantes'])
                ->withErrors($e->validator)
                ->withInput()
                ->with('error', 'Error al registrar la variante: ' . $firstError);
        } catch (\Throwable $e) {
            if ($isJson) {
                return response()->json([
                    'success' => false,
                    'error' => 'Error en base de datos: ' . $e->getMessage(),
                ], 500);
            }
            return redirect()->route('productos.index', ['tab' => 'variantes'])
                ->withInput()
                ->with('error', 'Error al registrar la variante: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        SchemaSyncService::ensureUpdatedColumns();

        if ($request->has('precio_venta')) {
            $request->merge([
                'precio_venta' => str_replace(',', '.', (string)$request->precio_venta)
            ]);
        }

        $isJson = $request->ajax() || $request->wantsJson();

        try {
            $request->validate([
                'nombre' => 'required|string|max:255',
                'precio_venta' => 'required|numeric|min:0.01',
                'componentes' => 'nullable|array',
            ], [
                'nombre.required' => 'El nombre de la variante es obligatorio.',
                'precio_venta.required' => 'El precio de venta es obligatorio.',
                'precio_venta.numeric' => 'El precio de venta debe ser un número válido.',
            ]);

            $var = VarianteSaltena::findOrFail($id);

            DB::transaction(function () use ($request, $var) {
                $var->update([
                    'nombre' => trim($request->nombre),
                    'precio_venta' => (float)$request->precio_venta,
                ]);

                if ($request->has('componentes')) {
                    VarianteReceta::where('variante_id', $var->id)->delete();
                    if (is_array($request->componentes)) {
                        foreach ($request->componentes as $comp) {
                            $tipo = $comp['tipo_componente'] ?? null;
                            $insumoId = !empty($comp['insumo_id']) ? (int)$comp['insumo_id'] : null;
                            $preparacionId = !empty($comp['preparacion_id']) ? (int)$comp['preparacion_id'] : null;
                            $cantidadStr = !empty($comp['cantidad_usada']) ? str_replace(',', '.', (string)$comp['cantidad_usada']) : '0';
                            $cantidad = (float)$cantidadStr;

                            if ($cantidad <= 0) {
                                continue;
                            }

                            if ($tipo === 'insumo' && $insumoId) {
                                VarianteReceta::create([
                                    'variante_id' => $var->id,
                                    'tipo_componente' => 'insumo',
                                    'insumo_id' => $insumoId,
                                    'preparacion_id' => null,
                                    'cantidad_usada' => $cantidad,
                                ]);
                            } elseif ($tipo === 'preparacion' && $preparacionId) {
                                VarianteReceta::create([
                                    'variante_id' => $var->id,
                                    'tipo_componente' => 'preparacion',
                                    'insumo_id' => null,
                                    'preparacion_id' => $preparacionId,
                                    'cantidad_usada' => $cantidad,
                                ]);
                            }
                        }
                    }
                }
            });

            if ($isJson) {
                return response()->json([
                    'success' => true,
                    'message' => 'Variante de Salteña actualizada exitosamente.',
                    'variante' => $var->fresh()->load([
                        'recetas.insumo.preciosHistorial',
                        'recetas.preparacion.receta.insumo.preciosHistorial'
                    ]),
                ], 200);
            }

            return redirect()->route('productos.index', ['tab' => 'variantes'])
                ->with('success', 'Variante de Salteña actualizada exitosamente.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            $firstError = collect($e->errors())->flatten()->first() ?? 'Error de validación.';
            if ($isJson) {
                return response()->json([
                    'success' => false,
                    'error' => $firstError,
                    'errors' => $e->errors(),
                ], 422);
            }
            return redirect()->route('productos.index', ['tab' => 'variantes'])
                ->withErrors($e->validator)
                ->withInput()
                ->with('error', 'Error al actualizar la variante: ' . $firstError);
        } catch (\Throwable $e) {
            if ($isJson) {
                return response()->json([
                    'success' => false,
                    'error' => 'Error en base de datos: ' . $e->getMessage(),
                ], 500);
            }
            return redirect()->route('productos.index', ['tab' => 'variantes'])
                ->withInput()
                ->with('error', 'Error al actualizar la variante: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        SchemaSyncService::ensureUpdatedColumns();

        try {
            $var = VarianteSaltena::findOrFail($id);
            $nombre = $var->nombre;
            $var->delete();

            return redirect()->route('productos.index', ['tab' => 'variantes'])
                ->with('success', "Variante '{$nombre}' eliminada correctamente.");
        } catch (\Throwable $e) {
            return redirect()->route('productos.index', ['tab' => 'variantes'])
                ->with('error', 'Error al eliminar la variante: ' . $e->getMessage());
        }
    }

    public function toggle($id)
    {
        SchemaSyncService::ensureUpdatedColumns();

        try {
            $var = VarianteSaltena::findOrFail($id);
            $var->activo = !$var->activo;
            $var->save();

            $estado = $var->activo ? 'activada' : 'desactivada';
            return redirect()->route('productos.index', ['tab' => 'variantes'])
                ->with('success', "Variante '{$var->nombre}' {$estado} correctamente.");
        } catch (\Throwable $e) {
            return redirect()->route('productos.index', ['tab' => 'variantes'])
                ->with('error', 'Error al cambiar estado de variante: ' . $e->getMessage());
        }
    }
}
