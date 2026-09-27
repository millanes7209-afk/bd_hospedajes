<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Interes;
use App\Models\Publicacion;
use App\Models\PublicacionFoto;
use App\Models\Venta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MisPublicacionesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $publicaciones = Auth::user()->publicaciones()
            ->with('fotos', 'categoria')
            ->latest()
            ->paginate(10);
        return view('mis-prendas.index', compact('publicaciones'));
    }

    public function create()
    {
        $categorias = Categoria::where('activa', true)->get();
        return view('mis-prendas.create', compact('categorias'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'titulo' => 'required|string|max:150',
            'descripcion' => 'nullable|string|max:1000',
            'precio' => 'required|numeric|min:0',
            'categoria_id' => 'required|exists:categorias,id',
            'talla' => 'nullable|string|max:20',
            'marca' => 'nullable|string|max:80',
            'estado_prenda' => 'required|in:nuevo,usado',
            'color_dominante' => 'nullable|string|max:40',
            'temporada' => 'nullable|string|max:40',
            'fotos' => 'required|array|min:1|max:6',
            'fotos.*' => 'image|max:4096',
        ]);

        $data['user_id'] = Auth::id();
        $data['estado_pub'] = $request->filled('publicar') ? 'publicada' : 'borrador';
        unset($data['fotos']);

        $publicacion = Publicacion::create($data);

        foreach ($request->file('fotos') as $i => $foto) {
            $ruta = $foto->store('prendas', 'public');
            PublicacionFoto::create([
                'publicacion_id' => $publicacion->id,
                'ruta' => $ruta,
                'es_principal' => $i === 0,
                'orden' => $i,
            ]);
        }

        return redirect()->route('mis-prendas.index')
            ->with('success', 'Prenda publicada correctamente.');
    }

    public function edit(Publicacion $misPrenda)
    {
        $this->authorize('update', $misPrenda);
        $categorias = Categoria::where('activa', true)->get();
        $misPrenda->load('fotos');
        return view('mis-prendas.edit', compact('misPrenda', 'categorias'));
    }

    public function update(Request $request, Publicacion $misPrenda)
    {
        $this->authorize('update', $misPrenda);

        $data = $request->validate([
            'titulo' => 'required|string|max:150',
            'descripcion' => 'nullable|string|max:1000',
            'precio' => 'required|numeric|min:0',
            'categoria_id' => 'required|exists:categorias,id',
            'talla' => 'nullable|string|max:20',
            'marca' => 'nullable|string|max:80',
            'estado_prenda' => 'required|in:nuevo,usado',
            'color_dominante' => 'nullable|string|max:40',
            'temporada' => 'nullable|string|max:40',
            'fotos' => 'nullable|array|max:6',
            'fotos.*' => 'image|max:4096',
        ]);

        unset($data['fotos']);
        $misPrenda->update($data);

        if ($request->hasFile('fotos')) {
            foreach ($request->file('fotos') as $i => $foto) {
                $ruta = $foto->store('prendas', 'public');
                PublicacionFoto::create([
                    'publicacion_id' => $misPrenda->id,
                    'ruta' => $ruta,
                    'es_principal' => false,
                    'orden' => $misPrenda->fotos()->count() + $i,
                ]);
            }
        }

        return redirect()->route('mis-prendas.index')
            ->with('success', 'Prenda actualizada.');
    }

    public function destroy(Publicacion $misPrenda)
    {
        $this->authorize('delete', $misPrenda);
        foreach ($misPrenda->fotos as $foto) {
            Storage::disk('public')->delete($foto->ruta);
        }
        $misPrenda->delete();
        return back()->with('success', 'Prenda eliminada.');
    }

    /** Muestra el formulario para elegir comprador */
    public function vendidaForm(Publicacion $misPrenda)
    {
        $this->authorize('update', $misPrenda);
        $interesados = Interes::where('publicacion_id', $misPrenda->id)
            ->with('usuario')
            ->get();
        return view('mis-prendas.vendida', compact('misPrenda', 'interesados'));
    }

    /** Marca la publicación como vendida */
    public function marcarVendida(Request $request, Publicacion $misPrenda)
    {
        $this->authorize('update', $misPrenda);

        $request->validate([
            'comprador_id' => 'required|exists:users,id',
        ]);

        $misPrenda->update([
            'estado_pub' => 'vendida',
            'comprador_id' => $request->comprador_id,
        ]);

        Venta::create([
            'publicacion_id' => $misPrenda->id,
            'vendedor_id' => Auth::id(),
            'comprador_id' => $request->comprador_id,
        ]);

        return redirect()->route('mis-prendas.index')
            ->with('success', '¡Prenda marcada como vendida!');
    }
}
