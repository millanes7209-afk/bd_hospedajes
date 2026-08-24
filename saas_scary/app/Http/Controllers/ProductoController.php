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

        $queryVars = DB::table('producto_variantes');
        if (\Illuminate\Support\Facades\Schema::hasColumn('producto_variantes', 'orden_mostrado')) {
            $queryVars->orderBy('orden_mostrado', 'asc');
        }
        $variantes = $queryVars->orderBy('varianteID', 'asc')->get();

        $variantesMap = [];
        foreach ($variantes as $v) {
            $variantesMap[$v->productoID][] = (array) $v;
        }

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
        $cats = DB::table('categorias')->where('activo', 1)->orderBy('nombre', 'asc')->get();
        if ($cats->isEmpty()) {
            $cats = DB::table('categorias')->orderBy('nombre', 'asc')->get();
        }
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
            'tipo' => 'required|in:simple,variantes',
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

        $tipo = $request->input('tipo', 'simple');
        $diaPromo = !empty($request->dia_promo) ? strtolower(trim($request->dia_promo)) : null;

        $insertData = [
            'categoriaID' => $request->categoriaID,
            'nombre' => strtoupper(trim($request->nombre)),
            'slug' => $slug,
            'descripcion' => $request->descripcion,
            'tipo' => $tipo,
            'precio' => ($tipo === 'simple') ? ($request->precio ?? 0) : null,
            'precio_promo' => ($tipo === 'simple' && !empty($request->precio_promo)) ? $request->precio_promo : null,
            'stock' => ($tipo === 'simple' && isset($request->stock) && $request->stock !== '') ? intval($request->stock) : null,
            'activo' => $request->has('activo') ? 1 : 0,
            'disponible' => $request->has('disponible') ? 1 : 0,
            'imagen' => $imagenName,
            'fecha_creacion' => now()
        ];

        if (\Illuminate\Support\Facades\Schema::hasColumn('productos', 'dia_promo')) {
            $insertData['dia_promo'] = ($tipo === 'simple') ? $diaPromo : null;
        }
        if (\Illuminate\Support\Facades\Schema::hasColumn('productos', 'dias_promo')) {
            $insertData['dias_promo'] = ($tipo === 'simple') ? $diaPromo : null;
        }

        $productoID = DB::table('productos')->insertGetId($insertData);

        if ($tipo === 'variantes') {
            $this->storeVariantes($request, $productoID);
        }

        return redirect()->route('admin.productos')->with('success', 'Producto creado exitosamente');
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

        $variantes = DB::table('producto_variantes')
            ->where('productoID', $id)
            ->orderBy('orden_mostrado', 'asc')
            ->orderBy('varianteID', 'asc')
            ->get();

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
            'tipo' => 'required|in:simple,variantes',
        ]);

        $imagenName = $producto->imagen;
        if ($request->hasFile('imagen')) {
            if ($imagenName && file_exists(public_path('assets/productos/' . $imagenName))) {
                @unlink(public_path('assets/productos/' . $imagenName));
            }
            $imagenName = time() . '_' . $request->file('imagen')->getClientOriginalName();
            $request->file('imagen')->move(public_path('assets/productos'), $imagenName);
        }

        $slug = $producto->slug;
        if (trim($producto->nombre) !== trim($request->nombre) || empty($slug)) {
            $baseSlug = Str::slug($request->nombre);
            $slug = $baseSlug;
            $counter = 1;
            while (DB::table('productos')->where('slug', $slug)->where('productoID', '!=', $id)->exists()) {
                $slug = $baseSlug . '-' . $counter++;
            }
        }

        $tipo = $request->input('tipo', 'simple');
        $diaPromo = !empty($request->dia_promo) ? strtolower(trim($request->dia_promo)) : null;

        $updateData = [
            'categoriaID' => $request->categoriaID,
            'nombre' => strtoupper(trim($request->nombre)),
            'slug' => $slug,
            'descripcion' => $request->descripcion,
            'tipo' => $tipo,
            'precio' => ($tipo === 'simple') ? ($request->precio ?? 0) : null,
            'precio_promo' => ($tipo === 'simple' && !empty($request->precio_promo)) ? $request->precio_promo : null,
            'stock' => ($tipo === 'simple' && isset($request->stock) && $request->stock !== '') ? intval($request->stock) : null,
            'activo' => $request->has('activo') ? 1 : 0,
            'disponible' => $request->has('disponible') ? 1 : 0,
            'imagen' => $imagenName,
            'fecha_modificacion' => now()
        ];

        if (\Illuminate\Support\Facades\Schema::hasColumn('productos', 'dia_promo')) {
            $updateData['dia_promo'] = ($tipo === 'simple') ? $diaPromo : null;
        }
        if (\Illuminate\Support\Facades\Schema::hasColumn('productos', 'dias_promo')) {
            $updateData['dias_promo'] = ($tipo === 'simple') ? $diaPromo : null;
        }

        DB::table('productos')->where('productoID', $id)->update($updateData);

        if ($tipo === 'variantes') {
            $this->storeVariantes($request, $id);
        } else {
            $variaciones = DB::table('producto_variantes')->where('productoID', $id)->get();
            foreach ($variaciones as $v) {
                if (!empty($v->imagen) && file_exists(public_path('assets/productos/' . $v->imagen))) {
                    @unlink(public_path('assets/productos/' . $v->imagen));
                }
            }
            DB::table('producto_variantes')->where('productoID', $id)->delete();
        }

        return redirect()->route('admin.productos')->with('success', 'Producto actualizado correctamente');
    }

    /**
     * Alternar disponibilidad rápida de producto (AJAX / HTTP)
     */
    public function toggleDisponible(Request $request, $id)
    {
        $producto = DB::table('productos')->where('productoID', $id)->first();
        if ($producto) {
            $nuevoEstado = $producto->disponible ? 0 : 1;
            DB::table('productos')->where('productoID', $id)->update(['disponible' => $nuevoEstado]);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'productoID' => $id,
                    'disponible' => $nuevoEstado
                ]);
            }
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => false], 404);
        }

        return redirect()->route('admin.productos');
    }

    /**
     * Alternar disponibilidad de variante específica (AJAX / HTTP)
     */
    public function toggleVarianteDisponible(Request $request, $producto_id, $variante_id)
    {
        $variante = DB::table('producto_variantes')
            ->where('varianteID', $variante_id)
            ->where('productoID', $producto_id)
            ->first();

        if ($variante) {
            $nuevoEstado = $variante->disponible ? 0 : 1;
            DB::table('producto_variantes')
                ->where('varianteID', $variante_id)
                ->update(['disponible' => $nuevoEstado]);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'productoID' => $producto_id,
                    'varianteID' => $variante_id,
                    'disponible' => $nuevoEstado
                ]);
            }
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => false], 404);
        }

        return redirect()->route('admin.productos');
    }

    /**
     * Eliminar producto
     */
    public function destroy($id)
    {
        $producto = DB::table('productos')->where('productoID', $id)->first();
        if ($producto) {
            if ($producto->imagen && file_exists(public_path('assets/productos/' . $producto->imagen))) {
                @unlink(public_path('assets/productos/' . $producto->imagen));
            }
            $variantes = DB::table('producto_variantes')->where('productoID', $id)->get();
            foreach ($variantes as $v) {
                if (!empty($v->imagen) && file_exists(public_path('assets/productos/' . $v->imagen))) {
                    @unlink(public_path('assets/productos/' . $v->imagen));
                }
            }
            DB::table('producto_variantes')->where('productoID', $id)->delete();
            DB::table('productos')->where('productoID', $id)->delete();
        }

        return redirect()->route('admin.productos')->with('success', 'Producto eliminado');
    }

    /**
     * Guardado/Actualización privada de variantes (Sin imágenes por requerimiento)
     */
    private function storeVariantes(Request $request, $productoID)
    {
        $variantes = $request->input('variantes');
        if (!$variantes || !is_array($variantes)) {
            return;
        }

        $savedVarianteIDs = [];
        $hasDiaPromo = \Illuminate\Support\Facades\Schema::hasColumn('producto_variantes', 'dia_promo');
        $hasDiasPromo = \Illuminate\Support\Facades\Schema::hasColumn('producto_variantes', 'dias_promo');

        foreach ($variantes as $index => $vData) {
            if (empty($vData['nombre_variante']) && empty($vData['nombre'])) {
                continue;
            }

            $nombreVar = strtoupper(trim($vData['nombre_variante'] ?? $vData['nombre']));
            $vId = $vData['varianteID'] ?? null;
            $diaPromoVar = !empty($vData['dia_promo']) ? strtolower(trim($vData['dia_promo'])) : null;

            $vRecord = [
                'productoID' => $productoID,
                'nombre_variante' => $nombreVar,
                'cantidad' => isset($vData['cantidad']) && $vData['cantidad'] !== '' ? floatval($vData['cantidad']) : null,
                'unidad' => !empty($vData['unidad']) ? trim($vData['unidad']) : null,
                'precio' => $vData['precio'] ?? 0,
                'precio_promo' => !empty($vData['precio_promo']) ? $vData['precio_promo'] : null,
                'stock' => isset($vData['stock']) && $vData['stock'] !== '' ? intval($vData['stock']) : null,
                'activo' => isset($vData['activo']) ? 1 : 0,
                'disponible' => isset($vData['disponible']) ? 1 : 0,
                'orden_mostrado' => intval($vData['orden_mostrado'] ?? $index),
            ];

            if ($hasDiaPromo) {
                $vRecord['dia_promo'] = $diaPromoVar;
            }
            if ($hasDiasPromo) {
                $vRecord['dias_promo'] = $diaPromoVar;
            }

            if ($vId) {
                DB::table('producto_variantes')->where('varianteID', $vId)->update($vRecord);
                $savedVarianteIDs[] = $vId;
            } else {
                $newId = DB::table('producto_variantes')->insertGetId(array_merge($vRecord, ['fecha_creacion' => now()]));
                $savedVarianteIDs[] = $newId;
            }
        }

        $oldVars = DB::table('producto_variantes')->where('productoID', $productoID);
        if (count($savedVarianteIDs) > 0) {
            $oldVars->whereNotIn('varianteID', $savedVarianteIDs);
        }

        $oldVarsList = $oldVars->get();
        foreach ($oldVarsList as $ov) {
            if (!empty($ov->imagen) && file_exists(public_path('assets/productos/' . $ov->imagen))) {
                @unlink(public_path('assets/productos/' . $ov->imagen));
            }
        }
        $oldVars->delete();
    }
}
