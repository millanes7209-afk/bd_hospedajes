<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Enums\DiaSemana;

class MenuController extends Controller
{
    public function index()
    {
        // Detectar columnas de la base de datos de forma dinámica
        $prodFk = \Illuminate\Support\Facades\Schema::hasColumn('productos', 'categoria_id') ? 'p.categoria_id' : (\Illuminate\Support\Facades\Schema::hasColumn('productos', 'categoriaID') ? 'p.categoriaID' : 'p.categoria_id');
        $catPk = \Illuminate\Support\Facades\Schema::hasColumn('categorias', 'id') ? 'c.id' : (\Illuminate\Support\Facades\Schema::hasColumn('categorias', 'categoriaID') ? 'c.categoriaID' : 'c.id');
        $prodPkCol = \Illuminate\Support\Facades\Schema::hasColumn('productos', 'id') ? 'id' : (\Illuminate\Support\Facades\Schema::hasColumn('productos', 'productoID') ? 'productoID' : 'id');

        // Obtener productos activos y disponibles
        $productosRaw = DB::table('productos as p')
            ->select('p.*', 'c.nombre as categoria_nombre')
            ->leftJoin('categorias as c', $prodFk, '=', $catPk)
            ->where(function ($q) {
                $q->whereNull('p.disponible')->orWhere('p.disponible', 1);
            })
            ->orderBy('p.' . $prodPkCol, 'desc')
            ->get();

        // Obtener variantes activas y disponibles (verificando dinámicamente si existe la columna orden_mostrado)
        $pVarFk = \Illuminate\Support\Facades\Schema::hasColumn('producto_variantes', 'producto_id') ? 'producto_id' : (\Illuminate\Support\Facades\Schema::hasColumn('producto_variantes', 'productoID') ? 'productoID' : 'producto_id');
        $varPkCol = \Illuminate\Support\Facades\Schema::hasColumn('producto_variantes', 'id') ? 'id' : (\Illuminate\Support\Facades\Schema::hasColumn('producto_variantes', 'varianteID') ? 'varianteID' : 'id');
        $hasOrdenVar = \Illuminate\Support\Facades\Schema::hasColumn('producto_variantes', 'orden_mostrado');

        $queryVar = DB::table('producto_variantes as v')
            ->where(function ($q) {
                $q->whereNull('v.disponible')->orWhere('v.disponible', 1);
            });

        if (\Illuminate\Support\Facades\Schema::hasColumn('producto_variantes', 'solo_local')) {
            $queryVar->where(function ($q) {
                $q->whereNull('v.solo_local')->orWhere('v.solo_local', 0);
            });
        }

        if ($hasOrdenVar) {
            $queryVar->orderBy('v.orden_mostrado', 'asc');
        }

        $variantesRaw = $queryVar->orderBy('v.' . $varPkCol, 'asc')->get();

        $variantesMap = [];
        foreach ($variantesRaw as $v) {
            $vArray = (array) $v;
            $pId = $v->{$pVarFk} ?? ($v->producto_id ?? ($v->productoID ?? null));
            if ($pId) {
                $variantesMap[$pId][] = $vArray;
            }
        }

        $menuGrouped = [];
        $catalogoJson = [];

        // Helper para cálculo dinámico del precio activo con promociones por día de la semana
        if (!function_exists('obtenerPrecioActivo')) {
            function obtenerPrecioActivo($obj, $diaSemanaTexto = null)
            {
                $isArr = is_array($obj);
                $precio = (float) ($isArr ? ($obj['precio'] ?? 0) : ($obj->precio ?? 0));
                $precioPromo = (float) ($isArr ? ($obj['precio_promo'] ?? 0) : ($obj->precio_promo ?? 0));

                $diaPromoSingle = strtolower(trim((string) ($isArr ? ($obj['dia_promo'] ?? '') : ($obj->dia_promo ?? ''))));
                $diasPromoRaw = strtolower(trim((string) ($isArr ? ($obj['dias_promo'] ?? '') : ($obj->dias_promo ?? ''))));

                if ($precioPromo > 0) {
                    $diaActual = strtolower($diaSemanaTexto ? trim($diaSemanaTexto) : DiaSemana::getDiaActualEnEspanol());

                    if (!empty($diaPromoSingle)) {
                        if ($diaPromoSingle === $diaActual) {
                            return $precioPromo;
                        }
                    }

                    if (!empty($diasPromoRaw)) {
                        $diasConfigurados = array_map('trim', explode(',', $diasPromoRaw));
                        if (in_array($diaActual, $diasConfigurados, true)) {
                            return $precioPromo;
                        }
                    }

                    if (empty($diaPromoSingle) && empty($diasPromoRaw)) {
                        return $precioPromo;
                    }
                }

                return $precio;
            }
        }

        foreach ($productosRaw as $pObj) {
            $p = (array) $pObj;
            $pId = $p[$prodPkCol] ?? ($p['id'] ?? ($p['productoID'] ?? null));

            $activeVariants = $variantesMap[$pId] ?? [];
            if (empty($activeVariants)) {
                continue; // Imposible vender producto sin variantes/precio
            }

            $tieneVariantes = count($activeVariants) > 1 || (count($activeVariants) === 1 && !empty($activeVariants[0]['nombre_variante']));

            $cat = strtoupper($p['categoria_nombre'] ?: 'MENÚ');

            if ($tieneVariantes) {
                $menuGrouped[$cat][] = [
                    'producto_id' => $pId,
                    'nombre' => $p['nombre'],
                    'descripcion' => $p['descripcion'] ?? '',
                    'imagen' => $p['imagen'] ?? null,
                    'categoria_nombre' => $p['categoria_nombre'] ?? '',
                    'tieneVariantes' => true,
                    'variantes' => $activeVariants,
                ];
            } else {
                // Producto simple: se extrae precio de su variante única
                $singleVar = $activeVariants[0];
                $pMerged = array_merge($p, $singleVar);
                $pMerged['precio'] = $singleVar['precio'];

                $pPrecioActivo = obtenerPrecioActivo($pMerged);
                $menuGrouped[$cat][] = [
                    'producto_id' => $pId,
                    'nombre' => $p['nombre'],
                    'descripcion' => $p['descripcion'] ?? '',
                    'precio' => $pPrecioActivo,
                    'precio_orig' => $singleVar['precio'] ?? ($p['precio'] ?? 0),
                    'precio_promo' => $singleVar['precio_promo'] ?? ($p['precio_promo'] ?? null),
                    'dia_promo' => $singleVar['dia_promo'] ?? ($p['dia_promo'] ?? null),
                    'imagen' => $p['imagen'] ?? null,
                    'categoria_nombre' => $p['categoria_nombre'] ?? '',
                    'tieneVariantes' => false,
                ];
            }

            $variants = [];
            if ($tieneVariantes) {
                foreach ($activeVariants as $v) {
                    $vPrecioActivo = obtenerPrecioActivo($v);
                    $vIdVal = $v[$varPkCol] ?? ($v['id'] ?? ($v['variante_id'] ?? ($v['varianteID'] ?? null)));
                    $variants[] = [
                        'id' => 'v' . $vIdVal,
                        'variante_id' => $vIdVal,
                        'nombre' => strtoupper($v['nombre_variante']),
                        'precio' => $vPrecioActivo,
                        'enPromo' => $vPrecioActivo < $v['precio'],
                        'precioOrig' => $v['precio'],
                        'imagen' => $v['imagen'] ?? $p['imagen'] ?? null,
                    ];
                }
            }

            $catalogoJson[$pId] = [
                'id' => $pId,
                'nombre' => strtoupper($p['nombre']),
                'tieneVariantes' => $tieneVariantes,
                'imagen' => $p['imagen'] ?? null,
                'variants' => $variants,
            ];
        }

        $tenant = app()->bound('tenant') ? app('tenant') : null;
        return view('menu.index', compact('menuGrouped', 'catalogoJson', 'tenant'));
    }

    /**
     * Endpoint API para consultar disponibilidad de productos y variantes en tiempo real
     */
    public function getDisponibilidad()
    {
        $productos = DB::table('productos')
            ->select('id as producto_id', 'nombre', 'disponible')
            ->get();

        $variantes = DB::table('producto_variantes')
            ->select('id as variante_id', 'producto_id', 'nombre_variante', 'disponible')
            ->get();

        return response()->json([
            'success' => true,
            'productos' => $productos,
            'variantes' => $variantes,
            'timestamp' => now()->toDateTimeString()
        ]);
    }
}

