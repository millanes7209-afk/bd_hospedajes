<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Exception;

class CategoriaController extends Controller
{
    /**
     * Listar todas las categorías con conteo de productos
     */
    public function index()
    {
        $categorias = DB::table('categorias as c')
            ->select(
                'c.*',
                DB::raw('(SELECT COUNT(*) FROM productos p WHERE p.categoria_id = c.id) as total_productos')
            )
            ->orderBy('c.nombre', 'asc')
            ->get();

        $categoriasArray = array_map(fn($c) => (array) $c, $categorias->toArray());

        $tenant = app()->bound('tenant') ? app('tenant') : null;

        return view('admin.categorias.index', [
            'categorias' => $categoriasArray,
            'tenant' => $tenant
        ]);
    }

    /**
     * Guardar una nueva categoría
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:150',
        ]);

        $nombre = strtoupper(trim($request->nombre));
        $baseSlug = Str::slug($nombre);
        $slug = $baseSlug;
        $counter = 1;

        while (DB::table('categorias')->where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter++;
        }

        try {
            DB::table('categorias')->insert([
                'nombre' => $nombre,
                'slug' => $slug,
                'activo' => 1,
                'created_at' => now()
            ]);

            return redirect()->back()->with('success', 'CATEGORÍA CREADA CORRECTAMENTE.');
        } catch (Exception $e) {
            return redirect()->back()->withInput()->with('error', 'ERROR AL CREAR CATEGORÍA: ' . $e->getMessage());
        }
    }

    /**
     * Actualizar una categoría existente
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'required|string|max:150',
        ]);

        $nombre = strtoupper(trim($request->nombre));
        $activo = $request->has('activo') ? 1 : 0;

        try {
            DB::table('categorias')
                ->where('id', $id)
                ->update([
                    'nombre' => $nombre,
                    'activo' => $activo
                ]);

            return redirect()->back()->with('success', 'CATEGORÍA ACTUALIZADA CORRECTAMENTE.');
        } catch (Exception $e) {
            return redirect()->back()->withInput()->with('error', 'ERROR AL ACTUALIZAR CATEGORÍA: ' . $e->getMessage());
        }
    }

    /**
     * Cambiar estado activo/inactivo de categoría
     */
    public function toggleEstado($id)
    {
        $cat = DB::table('categorias')->where('id', $id)->first();
        if ($cat) {
            $nuevoEstado = $cat->activo ? 0 : 1;
            DB::table('categorias')->where('id', $id)->update(['activo' => $nuevoEstado]);
        }

        return redirect()->back()->with('success', 'ESTADO DE CATEGORÍA ACTUALIZADO.');
    }
}
