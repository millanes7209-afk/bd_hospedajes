<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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
        $prodFk = Schema::hasColumn('productos', 'categoria_id') ? 'productos.categoria_id' : (Schema::hasColumn('productos', 'categoriaID') ? 'productos.categoriaID' : 'productos.categoria_id');
        $catPk = Schema::hasColumn('categorias', 'id') ? 'categorias.id' : (Schema::hasColumn('categorias', 'categoriaID') ? 'categorias.categoriaID' : 'categorias.id');
        $prodPk = Schema::hasColumn('productos', 'id') ? 'productos.id' : (Schema::hasColumn('productos', 'productoID') ? 'productos.productoID' : 'productos.id');

        $productos = DB::table('productos')
            ->leftJoin('categorias', $prodFk, '=', $catPk)
            ->select('productos.*', 'categorias.nombre as categoria_nombre')
            ->orderBy($prodPk, 'desc')
            ->get();

        $varPk = Schema::hasColumn('producto_variantes', 'id') ? 'id' : (Schema::hasColumn('producto_variantes', 'varianteID') ? 'varianteID' : (Schema::hasColumn('producto_variantes', 'variante_id') ? 'variante_id' : null));

        $queryVars = DB::table('producto_variantes');
        if (Schema::hasColumn('producto_variantes', 'orden_mostrado')) {
            $queryVars->orderBy('orden_mostrado', 'asc');
        }
        if ($varPk) {
            $queryVars->orderBy($varPk, 'asc');
        }
        $variantes = $queryVars->get();

        $pVarFk = Schema::hasColumn('producto_variantes', 'producto_id') ? 'producto_id' : (Schema::hasColumn('producto_variantes', 'productoID') ? 'productoID' : 'producto_id');
        $variantesMap = [];
        foreach ($variantes as $v) {
            $pIdVal = $v->{$pVarFk} ?? ($v->producto_id ?? null);
            if ($pIdVal) {
                $variantesMap[$pIdVal][] = (array) $v;
            }
        }

        $productosArray = array_map(fn($p) => (array) $p, $productos->toArray());

        $catOrderPk = Schema::hasColumn('categorias', 'id') ? 'id' : (Schema::hasColumn('categorias', 'categoriaID') ? 'categoriaID' : 'id');
        $categorias = DB::table('categorias')->orderBy($catOrderPk, 'asc')->get();
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
        $catOrderPk = Schema::hasColumn('categorias', 'id') ? 'id' : (Schema::hasColumn('categorias', 'categoriaID') ? 'categoriaID' : 'id');
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
            'categoria_id' => 'required',
            'nombre' => 'required|string|max:150',
            'tipo' => 'required|in:simple,variantes',
        ]);

        $baseSlug = Str::slug($request->nombre);
        $slug = $baseSlug;
        $counter = 1;
        if (Schema::hasColumn('productos', 'slug')) {
            while (DB::table('productos')->where('slug', $slug)->exists()) {
                $slug = $baseSlug . '-' . $counter++;
            }
        }

        $imagenName = null;
        if ($request->hasFile('imagen')) {
            $imagenName = time() . '_' . $request->file('imagen')->getClientOriginalName();
            $request->file('imagen')->move(public_path('assets/productos'), $imagenName);
        }

        $tipo = $request->input('tipo', 'simple');
        $diaPromo = !empty($request->dia_promo) ? strtolower(trim($request->dia_promo)) : null;

        $catFk = Schema::hasColumn('productos', 'categoria_id') ? 'categoria_id' : (Schema::hasColumn('productos', 'categoriaID') ? 'categoriaID' : 'categoria_id');

        $insertData = [
            $catFk => $request->categoria_id ?? $request->categoriaID,
            'nombre' => strtoupper(trim($request->nombre)),
        ];

        if (Schema::hasColumn('productos', 'disponible')) {
            $insertData['disponible'] = $request->has('disponible') ? 1 : 0;
        }
        if (Schema::hasColumn('productos', 'slug')) {
            $insertData['slug'] = $slug;
        }
        if (Schema::hasColumn('productos', 'descripcion')) {
            $insertData['descripcion'] = !empty($request->descripcion) ? strtoupper(trim($request->descripcion)) : null;
        }
        if (Schema::hasColumn('productos', 'precio_promo')) {
            $insertData['precio_promo'] = ($tipo === 'simple' && !empty($request->precio_promo)) ? $request->precio_promo : null;
        }
        if (Schema::hasColumn('productos', 'stock')) {
            $insertData['stock'] = ($tipo === 'simple' && isset($request->stock) && $request->stock !== '') ? intval($request->stock) : null;
        }
        if (Schema::hasColumn('productos', 'imagen')) {
            $insertData['imagen'] = $imagenName;
        }
        if (Schema::hasColumn('productos', 'user_id')) {
            $insertData['user_id'] = \Illuminate\Support\Facades\Session::get('usuario_id') ?? 1;
        }
        if (Schema::hasColumn('productos', 'created_at')) {
            $insertData['created_at'] = now();
        }
        if (Schema::hasColumn('productos', 'dia_promo')) {
            $insertData['dia_promo'] = ($tipo === 'simple') ? $diaPromo : null;
        }
        if (Schema::hasColumn('productos', 'dias_promo')) {
            $insertData['dias_promo'] = ($tipo === 'simple') ? $diaPromo : null;
        }

        $producto_id = DB::table('productos')->insertGetId($insertData);

        $pVarFk = Schema::hasColumn('producto_variantes', 'producto_id') ? 'producto_id' : (Schema::hasColumn('producto_variantes', 'productoID') ? 'productoID' : 'producto_id');

        if ($tipo === 'simple') {
            $simpleVarData = [
                $pVarFk => $producto_id,
                'nombre_variante' => '',
                'precio' => $request->precio ?? 0,
            ];
            if (Schema::hasColumn('producto_variantes', 'cantidad')) {
                $simpleVarData['cantidad'] = 1;
            }
            if (Schema::hasColumn('producto_variantes', 'unidad')) {
                $simpleVarData['unidad'] = 'und';
            }
            if (Schema::hasColumn('producto_variantes', 'disponible')) {
                $simpleVarData['disponible'] = 1;
            }
            if (Schema::hasColumn('producto_variantes', 'precio_promo')) {
                $simpleVarData['precio_promo'] = !empty($request->precio_promo) ? $request->precio_promo : null;
            }
            if (Schema::hasColumn('producto_variantes', 'user_id')) {
                $simpleVarData['user_id'] = \Illuminate\Support\Facades\Session::get('usuario_id') ?? 1;
            }
            if (Schema::hasColumn('producto_variantes', 'created_at')) {
                $simpleVarData['created_at'] = now();
            }
            DB::table('producto_variantes')->insert($simpleVarData);
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
        $prodPk = Schema::hasColumn('productos', 'id') ? 'id' : (Schema::hasColumn('productos', 'productoID') ? 'productoID' : 'id');
        $producto = DB::table('productos')->where($prodPk, $id)->first();
        if (!$producto) {
            return redirect()->route('admin.productos')->with('error', 'Producto no encontrado');
        }

        $pVarFk = Schema::hasColumn('producto_variantes', 'producto_id') ? 'producto_id' : (Schema::hasColumn('producto_variantes', 'productoID') ? 'productoID' : 'producto_id');
        $varPk = Schema::hasColumn('producto_variantes', 'id') ? 'id' : (Schema::hasColumn('producto_variantes', 'varianteID') ? 'varianteID' : (Schema::hasColumn('producto_variantes', 'variante_id') ? 'variante_id' : null));

        $queryVarsEdit = DB::table('producto_variantes')->where($pVarFk, $id);
        if (Schema::hasColumn('producto_variantes', 'orden_mostrado')) {
            $queryVarsEdit->orderBy('orden_mostrado', 'asc');
        }
        if ($varPk) {
            $queryVarsEdit->orderBy($varPk, 'asc');
        }
        $variantes = $queryVarsEdit->get();

        $cats = DB::table('categorias')->orderBy('nombre', 'asc')->get();

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
        $prodPk = Schema::hasColumn('productos', 'id') ? 'id' : (Schema::hasColumn('productos', 'productoID') ? 'productoID' : 'id');
        $producto = DB::table('productos')->where($prodPk, $id)->first();
        if (!$producto) {
            return redirect()->route('admin.productos')->with('error', 'Producto no encontrado');
        }

        $request->validate([
            'categoria_id' => 'required',
            'nombre' => 'required|string|max:150',
            'tipo' => 'required|in:simple,variantes',
        ]);

        $imagenName = $producto->imagen ?? null;
        if ($request->hasFile('imagen')) {
            if ($imagenName && file_exists(public_path('assets/productos/' . $imagenName))) {
                @unlink(public_path('assets/productos/' . $imagenName));
            }
            $imagenName = time() . '_' . $request->file('imagen')->getClientOriginalName();
            $request->file('imagen')->move(public_path('assets/productos'), $imagenName);
        }

        $slug = $producto->slug ?? null;
        if (trim($producto->nombre) !== trim($request->nombre) || empty($slug)) {
            $baseSlug = Str::slug($request->nombre);
            $slug = $baseSlug;
            $counter = 1;
            if (Schema::hasColumn('productos', 'slug')) {
                while (DB::table('productos')->where('slug', $slug)->where($prodPk, '!=', $id)->exists()) {
                    $slug = $baseSlug . '-' . $counter++;
                }
            }
        }

        $tipo = $request->input('tipo', 'simple');
        $diaPromo = !empty($request->dia_promo) ? strtolower(trim($request->dia_promo)) : null;
        $catFk = Schema::hasColumn('productos', 'categoria_id') ? 'categoria_id' : (Schema::hasColumn('productos', 'categoriaID') ? 'categoriaID' : 'categoria_id');

        $updateData = [
            $catFk => $request->categoria_id ?? $request->categoriaID,
            'nombre' => strtoupper(trim($request->nombre)),
        ];

        if (Schema::hasColumn('productos', 'disponible')) {
            $updateData['disponible'] = $request->has('disponible') ? 1 : 0;
        }
        if (Schema::hasColumn('productos', 'slug')) {
            $updateData['slug'] = $slug;
        }
        if (Schema::hasColumn('productos', 'descripcion')) {
            $updateData['descripcion'] = !empty($request->descripcion) ? strtoupper(trim($request->descripcion)) : null;
        }
        if (Schema::hasColumn('productos', 'precio_promo')) {
            $updateData['precio_promo'] = ($tipo === 'simple' && !empty($request->precio_promo)) ? $request->precio_promo : null;
        }
        if (Schema::hasColumn('productos', 'stock')) {
            $updateData['stock'] = ($tipo === 'simple' && isset($request->stock) && $request->stock !== '') ? intval($request->stock) : null;
        }
        if (Schema::hasColumn('productos', 'imagen')) {
            $updateData['imagen'] = $imagenName;
        }
        if (Schema::hasColumn('productos', 'updated_at')) {
            $updateData['updated_at'] = now();
        }
        if (Schema::hasColumn('productos', 'dia_promo')) {
            $updateData['dia_promo'] = ($tipo === 'simple') ? $diaPromo : null;
        }
        if (Schema::hasColumn('productos', 'dias_promo')) {
            $updateData['dias_promo'] = ($tipo === 'simple') ? $diaPromo : null;
        }

        DB::table('productos')->where($prodPk, $id)->update($updateData);

        $pVarFk = Schema::hasColumn('producto_variantes', 'producto_id') ? 'producto_id' : (Schema::hasColumn('producto_variantes', 'productoID') ? 'productoID' : 'producto_id');

        if ($tipo === 'simple') {
            DB::table('producto_variantes')->where($pVarFk, $id)->delete();
            $simpleVarData = [
                $pVarFk => $id,
                'nombre_variante' => '',
                'precio' => $request->precio ?? 0,
            ];
            if (Schema::hasColumn('producto_variantes', 'cantidad')) {
                $simpleVarData['cantidad'] = 1;
            }
            if (Schema::hasColumn('producto_variantes', 'unidad')) {
                $simpleVarData['unidad'] = 'und';
            }
            if (Schema::hasColumn('producto_variantes', 'disponible')) {
                $simpleVarData['disponible'] = 1;
            }
            if (Schema::hasColumn('producto_variantes', 'precio_promo')) {
                $simpleVarData['precio_promo'] = !empty($request->precio_promo) ? $request->precio_promo : null;
            }
            if (Schema::hasColumn('producto_variantes', 'user_id')) {
                $simpleVarData['user_id'] = \Illuminate\Support\Facades\Session::get('usuario_id') ?? 1;
            }
            if (Schema::hasColumn('producto_variantes', 'created_at')) {
                $simpleVarData['created_at'] = now();
            }
            DB::table('producto_variantes')->insert($simpleVarData);
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
        try {
            $prodPk = Schema::hasColumn('productos', 'id') ? 'id' : (Schema::hasColumn('productos', 'productoID') ? 'productoID' : 'id');
            $producto = DB::table('productos')->where($prodPk, $id)->first();
            if ($producto) {
                $currentVal = isset($producto->disponible) ? intval($producto->disponible) : 1;
                $nuevoEstado = $currentVal ? 0 : 1;

                $updateData = ['disponible' => $nuevoEstado];
                if (Schema::hasColumn('productos', 'updated_at')) {
                    $updateData['updated_at'] = now();
                }

                DB::table('productos')->where($prodPk, $id)->update($updateData);

                return response()->json([
                    'success' => true,
                    'producto_id' => $id,
                    'disponible' => $nuevoEstado
                ]);
            }

            return response()->json(['success' => false, 'message' => 'Producto no encontrado'], 404);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Alternar disponibilidad de variante específica (AJAX / HTTP)
     */
    public function toggleVarianteDisponible(Request $request, $producto_id, $variante_id)
    {
        try {
            $pVarFk = Schema::hasColumn('producto_variantes', 'producto_id') ? 'producto_id' : (Schema::hasColumn('producto_variantes', 'productoID') ? 'productoID' : 'producto_id');
            $vPk = Schema::hasColumn('producto_variantes', 'id') ? 'id' : (Schema::hasColumn('producto_variantes', 'varianteID') ? 'varianteID' : (Schema::hasColumn('producto_variantes', 'variante_id') ? 'variante_id' : 'id'));

            $variante = DB::table('producto_variantes')
                ->where($vPk, $variante_id)
                ->where($pVarFk, $producto_id)
                ->first();

            if ($variante) {
                $currentVal = isset($variante->disponible) ? intval($variante->disponible) : 1;
                $nuevoEstado = $currentVal ? 0 : 1;

                $updateData = ['disponible' => $nuevoEstado];
                if (Schema::hasColumn('producto_variantes', 'updated_at')) {
                    $updateData['updated_at'] = now();
                }

                DB::table('producto_variantes')
                    ->where($vPk, $variante_id)
                    ->update($updateData);

                return response()->json([
                    'success' => true,
                    'producto_id' => $producto_id,
                    'variante_id' => $variante_id,
                    'disponible' => $nuevoEstado
                ]);
            }

            return response()->json(['success' => false, 'message' => 'Variante no encontrada'], 404);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Alias para alternar disponibilidad de variante (AJAX / HTTP)
     */
    public function toggleVariante(Request $request, $producto_id, $variante_id)
    {
        return $this->toggleVarianteDisponible($request, $producto_id, $variante_id);
    }

    /**
     * Eliminar producto
     */
    public function destroy($id)
    {
        $prodPk = Schema::hasColumn('productos', 'id') ? 'id' : (Schema::hasColumn('productos', 'productoID') ? 'productoID' : 'id');
        $pVarFk = Schema::hasColumn('producto_variantes', 'producto_id') ? 'producto_id' : (Schema::hasColumn('producto_variantes', 'productoID') ? 'productoID' : 'producto_id');

        $producto = DB::table('productos')->where($prodPk, $id)->first();
        if ($producto) {
            if (!empty($producto->imagen) && file_exists(public_path('assets/productos/' . $producto->imagen))) {
                @unlink(public_path('assets/productos/' . $producto->imagen));
            }
            $variantes = DB::table('producto_variantes')->where($pVarFk, $id)->get();
            foreach ($variantes as $v) {
                if (!empty($v->imagen) && file_exists(public_path('assets/productos/' . $v->imagen))) {
                    @unlink(public_path('assets/productos/' . $v->imagen));
                }
            }
            DB::table('producto_variantes')->where($pVarFk, $id)->delete();
            DB::table('productos')->where($prodPk, $id)->delete();
        }

        return redirect()->route('admin.productos')->with('success', 'Producto eliminado');
    }

    /**
     * Guardado/Actualización privada de variantes
     */
    private function storeVariantes(Request $request, $producto_id)
    {
        $variantes = $request->input('variantes');
        if (!$variantes || !is_array($variantes)) {
            return;
        }

        $pVarFk = Schema::hasColumn('producto_variantes', 'producto_id') ? 'producto_id' : (Schema::hasColumn('producto_variantes', 'productoID') ? 'productoID' : 'producto_id');
        $vPk = Schema::hasColumn('producto_variantes', 'id') ? 'id' : (Schema::hasColumn('producto_variantes', 'varianteID') ? 'varianteID' : (Schema::hasColumn('producto_variantes', 'variante_id') ? 'variante_id' : 'id'));

        $savedVarianteIDs = [];
        $hasDiaPromo = Schema::hasColumn('producto_variantes', 'dia_promo');
        $hasDiasPromo = Schema::hasColumn('producto_variantes', 'dias_promo');
        $hasOrdenMostrado = Schema::hasColumn('producto_variantes', 'orden_mostrado');

        foreach ($variantes as $index => $vData) {
            if (empty($vData['nombre_variante']) && empty($vData['nombre'])) {
                continue;
            }

            $nombreVar = strtoupper(trim($vData['nombre_variante'] ?? $vData['nombre']));
            $vId = $vData['variante_id'] ?? $vData['id'] ?? null;
            $diaPromoVar = !empty($vData['dia_promo']) ? strtolower(trim($vData['dia_promo'])) : null;

            $vRecord = [
                $pVarFk => $producto_id,
                'nombre_variante' => $nombreVar,
                'precio' => $vData['precio'] ?? 0,
            ];

            if (Schema::hasColumn('producto_variantes', 'cantidad')) {
                $vRecord['cantidad'] = isset($vData['cantidad']) && $vData['cantidad'] !== '' ? floatval($vData['cantidad']) : null;
            }
            if (Schema::hasColumn('producto_variantes', 'unidad')) {
                $vRecord['unidad'] = !empty($vData['unidad']) ? trim($vData['unidad']) : null;
            }
            if (Schema::hasColumn('producto_variantes', 'precio_promo')) {
                $vRecord['precio_promo'] = !empty($vData['precio_promo']) ? $vData['precio_promo'] : null;
            }
            if (Schema::hasColumn('producto_variantes', 'stock')) {
                $vRecord['stock'] = isset($vData['stock']) && $vData['stock'] !== '' ? intval($vData['stock']) : null;
            }
            if (Schema::hasColumn('producto_variantes', 'disponible')) {
                $vRecord['disponible'] = isset($vData['disponible']) ? 1 : 0;
            }
            if (Schema::hasColumn('producto_variantes', 'user_id')) {
                $vRecord['user_id'] = \Illuminate\Support\Facades\Session::get('usuario_id') ?? 1;
            }

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
                DB::table('producto_variantes')->where($vPk, $vId)->update($vRecord);
                $savedVarianteIDs[] = $vId;
            } else {
                if (Schema::hasColumn('producto_variantes', 'created_at')) {
                    $vRecord['created_at'] = now();
                }
                $newId = DB::table('producto_variantes')->insertGetId($vRecord);
                $savedVarianteIDs[] = $newId;
            }
        }

        $oldVars = DB::table('producto_variantes')->where($pVarFk, $producto_id);
        if (count($savedVarianteIDs) > 0) {
            $oldVars->whereNotIn($vPk, $savedVarianteIDs);
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
