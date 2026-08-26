<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Exception;

class CategoriaController extends Controller
{
    /**
     * Listar todas las categorías con conteo de productos
     */
    public function index()
    {
        return redirect()->route('admin.productos', ['tab' => 'categorias']);
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
                'created_at' => now()
            ]);

            return redirect()->route('admin.productos', ['tab' => 'categorias'])->with('success', 'CATEGORÍA CREADA CORRECTAMENTE.');
        } catch (Exception $e) {
            return redirect()->route('admin.productos', ['tab' => 'categorias'])->withInput()->with('error', 'ERROR AL CREAR CATEGORÍA: ' . $e->getMessage());
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

        try {
            $catPk = Schema::hasColumn('categorias', 'id') ? 'id' : (Schema::hasColumn('categorias', 'categoriaID') ? 'categoriaID' : 'id');

            DB::table('categorias')
                ->where($catPk, $id)
                ->update([
                    'nombre' => $nombre,
                    'updated_at' => now()
                ]);

            return redirect()->route('admin.productos', ['tab' => 'categorias'])->with('success', 'CATEGORÍA ACTUALIZADA CORRECTAMENTE.');
        } catch (Exception $e) {
            return redirect()->route('admin.productos', ['tab' => 'categorias'])->withInput()->with('error', 'ERROR AL ACTUALIZAR CATEGORÍA: ' . $e->getMessage());
        }
    }

    /**
     * Cambiar estado activo/inactivo de categoría
     */
    public function toggleEstado($id)
    {
        return redirect()->route('admin.productos', ['tab' => 'categorias']);
    }
}
