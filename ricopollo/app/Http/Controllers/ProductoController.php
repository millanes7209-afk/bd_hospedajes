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
            ->leftJoin('categorias', 'productos.categoria_id', '=', 'categorias.id')
            ->select('productos.*', 'categorias.nombre as categoria_nombre')
            ->orderBy('productos.id', 'desc')
            ->get();

        $queryVars = DB::table('producto_variantes');
        if (\Illuminate\Support\Facades\Schema::hasColumn('producto_variantes', 'orden_mostrado')) {
            $queryVars->orderBy('orden_mostrado', 'asc');
        }
        $variantes = $queryVars->orderBy('id', 'asc')->get();

        $variantesMap = [];
        foreach ($variantes as $v) {
            $variantesMap[$v->producto_id][] = (array) $v;
        }

        $productosArray = array_map(function ($p) {
            return (array) $p;
        }, $productos->toArray());

        $categorias = DB::table('categorias')->orderBy('id', 'asc')->get();
        $categoriasArray = array_map(fn($c) => (array) $c, $categorias->toArray());

        return view('productos.index', [
            'productos' => $productosArray,
            'variantesMap' => $variantesMap,
            'categorias' => $categoriasArray
        ]);
    }

    /**
     * Formulario de creación
     */
    public function create()
    {
        $catsQuery = DB::table('categorias');
        if (\Illuminate\Support\Facades\Schema::hasColumn('categorias', 'activo')) {
            $catsQuery->where('activo', 1);
        }
        $cats = $catsQuery->orderBy('nombre', 'asc')->get();
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
            'categoria_id' => 'required|integer',
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
            'categoria_id' => $request->categoria_id ?? $request->categoria_id,
            'nombre' => strtoupper(trim($request->nombre)),
            'slug' => $slug,
            'descripcion' => $request->descripcion,
            'precio_promo' => ($tipo === 'simple' && !empty($request->precio_promo)) ? $request->precio_promo : null,
            'stock' => ($tipo === 'simple' && isset($request->stock) && $request->stock !== '') ? intval($request->stock) : null,
            'disponible' => $request->has('disponible') ? 1 : 0,
            'imagen' => $imagenName,
            'user_id' => \Illuminate\Support\Facades\Session::get('usuario_id') ?? 1,
            'created_at' => now()
        ];

        if (\Illuminate\Support\Facades\Schema::hasColumn('productos', 'activo')) {
            $insertData['activo'] = $request->has('activo') ? 1 : 0;
        }

        if (\Illuminate\Support\Facades\Schema::hasColumn('productos', 'dia_promo')) {
            $insertData['dia_promo'] = ($tipo === 'simple') ? $diaPromo : null;
        }
        if (\Illuminate\Support\Facades\Schema::hasColumn('productos', 'dias_promo')) {
            $insertData['dias_promo'] = ($tipo === 'simple') ? $diaPromo : null;
        }

        $producto_id = DB::table('productos')->insertGetId($insertData);

        if ($tipo === 'simple') {
            DB::table('producto_variantes')->insert([
                'producto_id' => $producto_id,
                'nombre_variante' => '',
                'precio' => $request->precio ?? 0,
                'precio_promo' => !empty($request->precio_promo) ? $request->precio_promo : null,
                'disponible' => 1,
                'user_id' => \Illuminate\Support\Facades\Session::get('usuario_id') ?? 1,
                'created_at' => now()
            ]);
        } else {
            $this->storeVariantes($request, $producto_id);
        }

        return redirect()->route('admin.productos')->with('success', 'Producto creado exitosamente');
    }

    /**
     * Formulario de edición
     */
    public function edit($id)
    {
        $producto = DB::table('productos')->where('id', $id)->first();
        if (!$producto) {
            return redirect()->route('admin.productos')->with('error', 'Producto no encontrado');
        }

        $queryVarsEdit = DB::table('producto_variantes')->where('producto_id', $id);
        if (\Illuminate\Support\Facades\Schema::hasColumn('producto_variantes', 'orden_mostrado')) {
            $queryVarsEdit->orderBy('orden_mostrado', 'asc');
        }
        $variantes = $queryVarsEdit->orderBy('id', 'asc')->get();

        $cats = DB::table('categorias')->orderBy('nombre', 'asc')->get();

        // Virtualize 'tipo' and 'precio'
        if (count($variantes) === 1 && empty($variantes[0]->nombre_variante)) {
            $producto->tipo = 'simple';
            $producto->precio = $variantes[0]->precio;
        } else {
            $producto->tipo = 'variantes';
        }

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
        $producto = DB::table('productos')->where('id', $id)->first();
        if (!$producto) {
            return redirect()->route('admin.productos')->with('error', 'Producto no encontrado');
        }

        $request->validate([
            'categoria_id' => 'required|integer',
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
            while (DB::table('productos')->where('slug', $slug)->where('id', '!=', $id)->exists()) {
                $slug = $baseSlug . '-' . $counter++;
            }
        }

        $tipo = $request->input('tipo', 'simple');
        $diaPromo = !empty($request->dia_promo) ? strtolower(trim($request->dia_promo)) : null;

        $updateData = [
            'categoria_id' => $request->categoria_id ?? $request->categoria_id,
            'nombre' => strtoupper(trim($request->nombre)),
            'slug' => $slug,
            'descripcion' => $request->descripcion,
            'precio_promo' => ($tipo === 'simple' && !empty($request->precio_promo)) ? $request->precio_promo : null,
            'stock' => ($tipo === 'simple' && isset($request->stock) && $request->stock !== '') ? intval($request->stock) : null,
            'disponible' => $request->has('disponible') ? 1 : 0,
            'imagen' => $imagenName,
            'updated_at' => now()
        ];

        if (\Illuminate\Support\Facades\Schema::hasColumn('productos', 'activo')) {
            $updateData['activo'] = $request->has('activo') ? 1 : 0;
        }

        if (\Illuminate\Support\Facades\Schema::hasColumn('productos', 'dia_promo')) {
            $updateData['dia_promo'] = ($tipo === 'simple') ? $diaPromo : null;
        }
        if (\Illuminate\Support\Facades\Schema::hasColumn('productos', 'dias_promo')) {
            $updateData['dias_promo'] = ($tipo === 'simple') ? $diaPromo : null;
        }

        DB::table('productos')->where('id', $id)->update($updateData);

        if ($tipo === 'simple') {
            DB::table('producto_variantes')->where('producto_id', $id)->delete();
            DB::table('producto_variantes')->insert([
                'producto_id' => $id,
                'nombre_variante' => '',
                'precio' => $request->precio ?? 0,
                'precio_promo' => !empty($request->precio_promo) ? $request->precio_promo : null,
                'disponible' => 1,
                'user_id' => \Illuminate\Support\Facades\Session::get('usuario_id') ?? 1,
                'created_at' => now()
            ]);
        } else {
            $this->storeVariantes($request, $id);
        }

        return redirect()->route('admin.productos')->with('success', 'Producto actualizado correctamente');
    }

    /**
     * Alternar disponibilidad rápida de producto (AJAX / HTTP)
     */
    public function toggleDisponible(Request $request, $id)
    {
        $producto = DB::table('productos')->where('id', $id)->first();
        if ($producto) {
            $nuevoEstado = $producto->disponible ? 0 : 1;
            DB::table('productos')->where('id', $id)->update(['disponible' => $nuevoEstado]);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'producto_id' => $id,
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
            ->where('id', $variante_id)
            ->where('producto_id', $producto_id)
            ->first();

        if ($variante) {
            $nuevoEstado = $variante->disponible ? 0 : 1;
            DB::table('producto_variantes')
                ->where('id', $variante_id)
                ->update(['disponible' => $nuevoEstado]);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'producto_id' => $producto_id,
                    'variante_id' => $variante_id,
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
        $producto = DB::table('productos')->where('id', $id)->first();
        if ($producto) {
            if ($producto->imagen && file_exists(public_path('assets/productos/' . $producto->imagen))) {
                @unlink(public_path('assets/productos/' . $producto->imagen));
            }
            $variantes = DB::table('producto_variantes')->where('producto_id', $id)->get();
            foreach ($variantes as $v) {
                if (!empty($v->imagen) && file_exists(public_path('assets/productos/' . $v->imagen))) {
                    @unlink(public_path('assets/productos/' . $v->imagen));
                }
            }
            DB::table('producto_variantes')->where('producto_id', $id)->delete();
            DB::table('productos')->where('id', $id)->delete();
        }

        return redirect()->route('admin.productos')->with('success', 'Producto eliminado');
    }

    /**
     * Guardado/Actualización privada de variantes (Sin imágenes por requerimiento)
     */
    private function storeVariantes(Request $request, $producto_id)
    {
        $variantes = $request->input('variantes');
        if (!$variantes || !is_array($variantes)) {
            return;
        }

        $savedVarianteIDs = [];
        $hasDiaPromo = \Illuminate\Support\Facades\Schema::hasColumn('producto_variantes', 'dia_promo');
        $hasDiasPromo = \Illuminate\Support\Facades\Schema::hasColumn('producto_variantes', 'dias_promo');
        $hasOrdenMostrado = \Illuminate\Support\Facades\Schema::hasColumn('producto_variantes', 'orden_mostrado');

        foreach ($variantes as $index => $vData) {
            if (empty($vData['nombre_variante']) && empty($vData['nombre'])) {
                continue;
            }

            $nombreVar = strtoupper(trim($vData['nombre_variante'] ?? $vData['nombre']));
            $vId = $vData['variante_id'] ?? $vData['variante_id'] ?? null;
            $diaPromoVar = !empty($vData['dia_promo']) ? strtolower(trim($vData['dia_promo'])) : null;

            $vRecord = [
                'producto_id' => $producto_id,
                'nombre_variante' => $nombreVar,
                'cantidad' => isset($vData['cantidad']) && $vData['cantidad'] !== '' ? floatval($vData['cantidad']) : null,
                'unidad' => !empty($vData['unidad']) ? trim($vData['unidad']) : null,
                'precio' => $vData['precio'] ?? 0,
                'precio_promo' => !empty($vData['precio_promo']) ? $vData['precio_promo'] : null,
                'stock' => isset($vData['stock']) && $vData['stock'] !== '' ? intval($vData['stock']) : null,
                'activo' => isset($vData['activo']) ? 1 : 0,
                'disponible' => isset($vData['disponible']) ? 1 : 0,
                'user_id' => \Illuminate\Support\Facades\Session::get('usuario_id') ?? 1,
            ];

            if ($hasOrdenMostrado) {
                $vRecord['orden_mostrado'] = intval($vData['orden_mostrado'] ?? $index);
            }

            if ($hasDiaPromo) {
                $vRecord['dia_promo'] = $diaPromoVar;
            }
            if ($hasDiasPromo) {
                $vRecord['dias_promo'] = $diaPromoVar;
            }

            if ($vId) {
                DB::table('producto_variantes')->where('id', $vId)->update($vRecord);
                $savedVarianteIDs[] = $vId;
            } else {
                $newId = DB::table('producto_variantes')->insertGetId(array_merge($vRecord, ['created_at' => now()]));
                $savedVarianteIDs[] = $newId;
            }
        }

        $oldVars = DB::table('producto_variantes')->where('producto_id', $producto_id);
        if (count($savedVarianteIDs) > 0) {
            $oldVars->whereNotIn('id', $savedVarianteIDs);
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

