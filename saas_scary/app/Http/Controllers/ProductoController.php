<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Producto;
use App\Models\ProductoVariante;
use App\Models\Categoria;

class ProductoController extends Controller
{
    /**
     * Listar productos en panel de administración
     */
    public function index()
    {
        $productos = DB::table('productos')
            ->leftJoin('categorias', 'productos.categoriaID', '=', 'categorias.categoriaID')
            ->select('productos.*', 'categorias.nombre as categoria_nombre')
            ->orderBy('productos.productoID', 'desc')
            ->get();

        $variantes = DB::table('producto_variantes')->get();
        $variantesMap = [];
        foreach ($variantes as $v) {
            $variantesMap[$v->productoID][] = (array) $v;
        }

        // Convertir productos a arrays para compatibilidad de vistas
        $productosArray = array_map(function ($p) {
            return (array) $p;
        }, $productos->toArray());

        return view('productos.index', [
            'productos' => $productosArray,
            'variantesMap' => $variantesMap
        ]);
    }

    /**
     * Formulario de creación
     */
    public function create()
    {
        $cats = DB::table('categorias')->orderBy('nombre', 'asc')->get();
        $categoriasArray = array_map(fn($c) => (array) $c, $cats->toArray());

        return view('productos.form', [
            'producto' => null,
            'variantes' => [],
            'cats' => $categoriasArray,
            'categorias' => $categoriasArray,
            'id' => null,
            'error' => session('error', ''),
            'imagenActual' => null
        ]);
    }

    /**
     * Procesar guardado de nuevo producto
     */
    public function store(Request $request)
    {
        $request->validate([
            'categoriaID' => 'required|integer',
            'nombre' => 'required|string|max:150',
        ]);

        $baseSlug = Str::slug($request->nombre);
        $slug = $baseSlug;
        $counter = 1;
        while (DB::table('productos')->where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter++;
        }

        $imagenName = null;
        if ($request->hasFile('imagen')) {
            $imagenName = time() . '_' . $request->file('imagen')->getClientOriginalName();
            $request->file('imagen')->move(public_path('assets/productos'), $imagenName);
        }

        $diasPromoStr = !empty($request->dias_promo) ? implode(',', $request->dias_promo) : null;

        $productoID = DB::table('productos')->insertGetId([
            'categoriaID' => $request->categoriaID,
            'nombre' => $request->nombre,
            'slug' => $slug,
            'descripcion' => $request->descripcion,
            'precio' => $request->precio ?? 0,
            'precio_promo' => $request->precio_promo,
            'dias_promo' => $diasPromoStr,
            'disponible' => $request->has('disponible') ? 1 : 0,
            'imagen' => $imagenName,
            'fecha_creacion' => now()
        ]);

        if ($request->has('tiene_variantes')) {
            $this->storeVariantes($request, $productoID);
        }

        return redirect()->route('admin.productos');
    }

    /**
     * Formulario de edición
     */
    public function edit($id)
    {
        $producto = DB::table('productos')->where('productoID', $id)->first();
        if (!$producto) {
            return redirect()->route('admin.productos')->with('error', 'Producto no encontrado');
        }

        $variantes = DB::table('producto_variantes')->where('productoID', $id)->get();
        $cats = DB::table('categorias')->orderBy('nombre', 'asc')->get();

        $productoArray = (array) $producto;
        $variantesArray = array_map(fn($v) => (array) $v, $variantes->toArray());
        $categoriasArray = array_map(fn($c) => (array) $c, $cats->toArray());

        return view('productos.form', [
            'producto' => $productoArray,
            'variantes' => $variantesArray,
            'cats' => $categoriasArray,
            'categorias' => $categoriasArray,
            'id' => $id,
            'error' => session('error', ''),
            'imagenActual' => $producto->imagen ?? null
        ]);
    }

    /**
     * Procesar guardado de edición
     */
    public function update(Request $request, $id)
    {
        $producto = DB::table('productos')->where('productoID', $id)->first();
        if (!$producto) {
            return redirect()->route('admin.productos')->with('error', 'Producto no encontrado');
        }

        $request->validate([
            'categoriaID' => 'required|integer',
            'nombre' => 'required|string|max:150',
        ]);

        $imagenName = $producto->imagen;
        if ($request->hasFile('imagen')) {
            if ($imagenName && file_exists(public_path('assets/productos/' . $imagenName))) {
                @unlink(public_path('assets/productos/' . $imagenName));
            }
            $imagenName = time() . '_' . $request->file('imagen')->getClientOriginalName();
            $request->file('imagen')->move(public_path('assets/productos'), $imagenName);
        }

        $diasPromoStr = !empty($request->dias_promo) ? implode(',', $request->dias_promo) : null;

        $slug = $producto->slug;
        if ($producto->nombre !== $request->nombre) {
            $baseSlug = Str::slug($request->nombre);
            $slug = $baseSlug;
            $counter = 1;
            while (DB::table('productos')->where('slug', $slug)->where('productoID', '!=', $id)->exists()) {
                $slug = $baseSlug . '-' . $counter++;
            }
        }

        DB::table('productos')->where('productoID', $id)->update([
            'categoriaID' => $request->categoriaID,
            'nombre' => $request->nombre,
            'slug' => $slug,
            'descripcion' => $request->descripcion,
            'precio' => $request->precio ?? 0,
            'precio_promo' => $request->precio_promo,
            'dias_promo' => $diasPromoStr,
            'disponible' => $request->has('disponible') ? 1 : 0,
            'imagen' => $imagenName,
            'fecha_modificacion' => now()
        ]);

        if ($request->has('tiene_variantes')) {
            $this->storeVariantes($request, $id);
        } else {
            $variaciones = DB::table('producto_variantes')->where('productoID', $id)->get();
            foreach ($variaciones as $v) {
                if ($v->imagen && file_exists(public_path('assets/productos/' . $v->imagen))) {
                    @unlink(public_path('assets/productos/' . $v->imagen));
                }
            }
            DB::table('producto_variantes')->where('productoID', $id)->delete();
        }

        return redirect()->route('admin.productos');
    }

    /**
     * Alternar disponibilidad de producto
     */
    public function toggleDisponible($id)
    {
        $producto = DB::table('productos')->where('productoID', $id)->first();
        if ($producto) {
            $nuevoEstado = $producto->disponible ? 0 : 1;
            DB::table('productos')->where('productoID', $id)->update(['disponible' => $nuevoEstado]);
        }
        return redirect()->route('admin.productos');
    }

    /**
     * Eliminar producto
     */
    public function destroy($id)
    {
        DB::table('producto_variantes')->where('productoID', $id)->delete();
        DB::table('productos')->where('productoID', $id)->delete();
        return redirect()->route('admin.productos');
    }

    /**
     * Alternar disponibilidad de una variante específica
     */
    public function toggleVariante($producto_id, $variante_id)
    {
        $variante = DB::table('producto_variantes')
            ->where('varianteID', $variante_id)
            ->where('productoID', $producto_id)
            ->first();

        if ($variante) {
            $nuevoEstado = $variante->activo ? 0 : 1;
            DB::table('producto_variantes')
                ->where('varianteID', $variante_id)
                ->update(['activo' => $nuevoEstado]);
        }
        return redirect()->route('admin.productos');
    }

    /**
     * Funcionalidad privada de guardado de variantes
     */
    private function storeVariantes(Request $request, $productoID)
    {
        $variantes = $request->input('variantes');
        if (!$variantes || !is_array($variantes))
            return;

        $savedVarianteIDs = [];

        foreach ($variantes as $index => $vData) {
            if (empty($vData['nombre']))
                continue;

            $vId = $vData['varianteID'] ?? null;
            $diasPromoVarStr = !empty($vData['dias_promo']) ? implode(',', $vData['dias_promo']) : null;

            $imagenVarName = null;
            if ($vId) {
                $existe = DB::table('producto_variantes')->where('varianteID', $vId)->first();
                $imagenVarName = $existe ? $existe->imagen : null;
            }

            if ($request->hasFile("variantes_imagenes.{$index}")) {
                if ($imagenVarName && file_exists(public_path('assets/productos/' . $imagenVarName))) {
                    @unlink(public_path('assets/productos/' . $imagenVarName));
                }
                $file = $request->file("variantes_imagenes.{$index}");
                $imagenVarName = time() . '_v_' . $file->getClientOriginalName();
                $file->move(public_path('assets/productos'), $imagenVarName);
            }

            if ($vId) {
                DB::table('producto_variantes')->where('varianteID', $vId)->update([
                    'nombre_variante' => $vData['nombre'],
                    'precio' => $vData['precio'] ?? 0,
                    'precio_promo' => $vData['precio_promo'],
                    'dias_promo' => $diasPromoVarStr,
                    'activo' => isset($vData['activo']) ? 1 : 0,
                    'imagen' => $imagenVarName
                ]);
                $savedVarianteIDs[] = $vId;
            } else {
                $newId = DB::table('producto_variantes')->insertGetId([
                    'productoID' => $productoID,
                    'nombre_variante' => $vData['nombre'],
                    'precio' => $vData['precio'] ?? 0,
                    'precio_promo' => $vData['precio_promo'],
                    'dias_promo' => $diasPromoVarStr,
                    'activo' => isset($vData['activo']) ? 1 : 0,
                    'imagen' => $imagenVarName
                ]);
                $savedVarianteIDs[] = $newId;
            }
        }

        $oldVars = DB::table('producto_variantes')
            ->where('productoID', $productoID);

        if (count($savedVarianteIDs) > 0) {
            $oldVars->whereNotIn('varianteID', $savedVarianteIDs);
        }

        $oldVarsList = $oldVars->get();

        foreach ($oldVarsList as $ov) {
            if ($ov->imagen && file_exists(public_path('assets/productos/' . $ov->imagen))) {
                @unlink(public_path('assets/productos/' . $ov->imagen));
            }
        }
        $oldVars->delete();
    }
}
