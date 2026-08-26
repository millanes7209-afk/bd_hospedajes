<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Enums\DiaSemana;

class MenuController extends Controller
{
    public function index()
    {
        // Obtener productos activos y disponibles
        $productosRaw = DB::table('productos as p')
            ->select('p.*', 'c.nombre as categoria_nombre')
            ->leftJoin('categorias as c', 'p.categoriaID', '=', 'c.categoriaID')
            ->where(function ($q) {
                $q->whereNull('p.disponible')->orWhere('p.disponible', 1);
            })
            ->where(function ($q) {
                $q->whereNull('p.activo')->orWhere('p.activo', 1);
            })
            ->where(function ($query) {
                $query->whereNull('c.activo')->orWhere('c.activo', 1);
            })
            ->orderBy('p.productoID', 'desc')
            ->get();

        // Obtener variantes activas y disponibles (verificando dinámicamente si existe la columna orden_mostrado)
        $hasOrdenVar = \Illuminate\Support\Facades\Schema::hasColumn('producto_variantes', 'orden_mostrado');
        $queryVar = DB::table('producto_variantes as v')
            ->where(function ($q) {
                $q->whereNull('v.disponible')->orWhere('v.disponible', 1);
            })
            ->where(function ($q) {
                $q->whereNull('v.activo')->orWhere('v.activo', 1);
            });

        if ($hasOrdenVar) {
            $queryVar->orderBy('v.orden_mostrado', 'asc');
        }

        $variantesRaw = $queryVar->orderBy('v.varianteID', 'asc')->get();

        $variantesMap = [];
        foreach ($variantesRaw as $v) {
            $vArray = (array) $v;
            // Transformar claves de camelCase a snake_case para consistencia con la vista
            $vTransformed = [];
            foreach ($vArray as $key => $value) {
                // Mapeo específico de claves conocidas
                $keyMap = [
                    'varianteID' => 'variante_id',
                    'nombre_variante' => 'nombre_variante',
                    'precio' => 'precio',
                    'precio_promo' => 'precio_promo',
                    'dia_promo' => 'dia_promo',
                    'stock' => 'stock',
                    'imagen' => 'imagen',
                    'activo' => 'activo',
                    'disponible' => 'disponible',
                    'orden_mostrado' => 'orden_mostrado',
                ];
                $newKey = $keyMap[$key] ?? $key;
                $vTransformed[$newKey] = $value;
            }
            $variantesMap[$v->productoID][] = $vTransformed;
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
            $tieneVariantes = ($p['tipo'] ?? 'simple') === 'variantes' || !empty($variantesMap[$p['productoID']]);

            $activeVariants = [];
            if ($tieneVariantes) {
                $activeVariants = $variantesMap[$p['productoID']] ?? [];
                if (empty($activeVariants)) {
                    continue; // Saltar si no tiene variantes activas
                }
            }

            $cat = strtoupper($p['categoria_nombre'] ?: 'MENÚ');

            if ($tieneVariantes) {
                $menuGrouped[$cat][] = [
                    'producto_id' => $p['productoID'],
                    'nombre' => $p['nombre'],
                    'descripcion' => $p['descripcion'] ?? '',
                    'imagen' => $p['imagen'] ?? null,
                    'categoria_nombre' => $p['categoria_nombre'] ?? '',
                    'tieneVariantes' => true,
                    'variantes' => $activeVariants,
                ];
            } else {
                $pPrecioActivo = obtenerPrecioActivo($p);
                $menuGrouped[$cat][] = [
                    'producto_id' => $p['productoID'],
                    'nombre' => $p['nombre'],
                    'descripcion' => $p['descripcion'] ?? '',
                    'precio' => $pPrecioActivo,
                    'precio_orig' => $p['precio'] ?? 0,
                    'precio_promo' => $p['precio_promo'] ?? null,
                    'dia_promo' => $p['dia_promo'] ?? null,
                    'imagen' => $p['imagen'] ?? null,
                    'categoria_nombre' => $p['categoria_nombre'] ?? '',
                    'tieneVariantes' => false,
                ];
            }

            $variants = [];
            if ($tieneVariantes) {
                foreach ($activeVariants as $v) {
                    $vPrecioActivo = obtenerPrecioActivo($v);
                    $variants[] = [
                        'id' => 'v' . $v['varianteID'],
                        'variante_id' => $v['varianteID'],
                        'nombre' => strtoupper($v['nombre_variante']),
                        'precio' => $vPrecioActivo,
                        'enPromo' => $vPrecioActivo < $v['precio'],
                        'precioOrig' => $v['precio'],
                        'imagen' => $v['imagen'] ?? $p['imagen'] ?? null,
                    ];
                }
            }

            $catalogoJson[$p['productoID']] = [
                'id' => $p['productoID'],
                'producto_id' => $p['productoID'],
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
            ->select('productoID', 'nombre', 'disponible', 'activo')
            ->get();

        $variantes = DB::table('producto_variantes')
            ->select('varianteID', 'productoID', 'nombre_variante', 'disponible', 'activo')
            ->get();

        return response()->json([
            'success' => true,
            'productos' => $productos,
            'variantes' => $variantes,
            'timestamp' => now()->toDateTimeString()
        ]);
    }
}
