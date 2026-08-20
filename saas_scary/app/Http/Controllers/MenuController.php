<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MenuController extends Controller
{
    public function index()
    {
        // Replicating exactly the legacy behavior using Laravel Query Builder

        $productosRaw = DB::table('productos as p')
            ->select('p.*', 'c.nombre as categoria_nombre')
            ->leftJoin('categorias as c', 'p.categoriaID', '=', 'c.categoriaID')
            ->where('p.disponible', 1)
            ->where(function ($query) {
                $query->whereNull('c.activo')
                    ->orWhere('c.activo', 1);
            })
            ->orderBy('p.productoID', 'desc')
            ->get();

        $variantesRaw = DB::table('producto_variantes')->get();

        $variantesMap = [];
        foreach ($variantesRaw as $v) {
            $vArray = (array) $v;
            $variantesMap[$v->productoID][] = $vArray;
        }

        $menuGrouped = [];
        $catalogoJson = [];

        // Helper function for dynamic daily promo discount calculation using DiaSemana Enum
        if (!function_exists('obtenerPrecioActivo')) {
            function obtenerPrecioActivo($obj, $diaSemanaTexto = null)
            {
                $isArr = is_array($obj);
                $precio = (float) ($isArr ? ($obj['precio'] ?? 0) : ($obj->precio ?? 0));
                $precioPromo = (float) ($isArr ? ($obj['precio_promo'] ?? 0) : ($obj->precio_promo ?? 0));
                $diasPromoRaw = trim((string) ($isArr ? ($obj['dias_promo'] ?? '') : ($obj->dias_promo ?? '')));

                if ($precioPromo > 0) {
                    if (empty($diasPromoRaw)) {
                        return $precioPromo;
                    }

                    $diaActual = $diaSemanaTexto ? strtoupper(trim($diaSemanaTexto)) : \App\Enums\DiaSemana::getDiaActualEnEspanol();

                    $diasConfigurados = array_map(function ($d) {
                        return strtoupper(trim($d));
                    }, explode(',', $diasPromoRaw));

                    if (in_array($diaActual, $diasConfigurados, true)) {
                        return $precioPromo;
                    }
                }

                return $precio;
            }
        }

        foreach ($productosRaw as $pObj) {
            $p = (array) $pObj;
            $tieneVariantes = !empty($variantesMap[$p['productoID']]);

            $activeVariants = [];
            if ($tieneVariantes) {
                foreach ($variantesMap[$p['productoID']] as $v) {
                    if ($v['activo'] == 1) {
                        $activeVariants[] = $v;
                    }
                }
                if (empty($activeVariants)) {
                    continue; // Saltar producto si todas sus variantes están inactivas
                }
            }

            $cat = strtoupper($p['categoria_nombre']);

            if ($tieneVariantes) {
                $menuGrouped[$cat][] = [
                    'productoID' => $p['productoID'],
                    'nombre' => $p['nombre'],
                    'descripcion' => $p['descripcion'] ?? '',
                    'imagen' => $p['imagen'] ?? null,
                    'categoria_nombre' => $p['categoria_nombre'] ?? '',
                    'tieneVariantes' => true,
                    'variantes' => $activeVariants,
                ];
            } else {
                $menuGrouped[$cat][] = [
                    'productoID' => $p['productoID'],
                    'nombre' => $p['nombre'],
                    'descripcion' => $p['descripcion'] ?? '',
                    'precio' => $p['precio'] ?? 0,
                    'precio_promo' => $p['precio_promo'] ?? null,
                    'dias_promo' => $p['dias_promo'] ?? null,
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
                        'varianteID' => $v['varianteID'],
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
                'nombre' => strtoupper($p['nombre']),
                'tieneVariantes' => $tieneVariantes,
                'imagen' => $p['imagen'] ?? null,
                'variants' => $variants,
            ];
        }

        // Return view
        $tenant = app()->bound('tenant') ? app('tenant') : null;
        return view('menu.index', compact('menuGrouped', 'catalogoJson', 'tenant'));
    }
}
