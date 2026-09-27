<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Publicacion;
use Illuminate\Http\Request;

class CatalogoController extends Controller
{
    public function index(Request $request)
    {
        $query = Publicacion::with(['fotos', 'vendedor', 'categoria'])
            ->where('estado_pub', 'publicada');

        if ($request->filled('categoria')) {
            $query->where('categoria_id', $request->categoria);
        }
        if ($request->filled('talla')) {
            $query->where('talla', $request->talla);
        }
        if ($request->filled('precio_max')) {
            $query->where('precio', '<=', $request->precio_max);
        }
        if ($request->filled('buscar')) {
            $query->where(function ($q) use ($request) {
                $q->where('titulo', 'like', '%' . $request->buscar . '%')
                    ->orWhere('marca', 'like', '%' . $request->buscar . '%');
            });
        }

        $publicaciones = $query->latest()->paginate(12)->withQueryString();
        $categorias = Categoria::where('activa', true)->get();

        return view('catalogo.index', compact('publicaciones', 'categorias'));
    }

    public function show(Publicacion $publicacion)
    {
        if ($publicacion->estado_pub !== 'publicada' && $publicacion->estado_pub !== 'reservada') {
            abort(404);
        }
        $publicacion->load(['fotos', 'vendedor', 'categoria']);
        return view('catalogo.show', compact('publicacion'));
    }
}
