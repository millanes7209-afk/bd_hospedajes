<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Venta;
use App\Models\VentaItem;
use App\Models\Pago;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class PosController extends Controller
{
    public function index()
    {
        try {
            $categorias = Categoria::orderBy('nombre', 'asc')->get();
            $productos = Producto::where('disponible', 1)
                ->with([
                    'variantes' => function ($q) {
                        $q->where('disponible', 1);
                    }
                ])
                ->get();

            foreach ($productos as $p) {
                $vars = $p->variantes;
                if (count($vars) > 1 || (count($vars) === 1 && !empty($vars[0]->nombre_variante))) {
                    $p->tipo = 'variantes';
                    $p->precio = 0;
                } else if (count($vars) === 1) {
                    $p->tipo = 'simple';
                    $p->precio = $vars[0]->precio;
                } else {
                    $p->tipo = 'simple';
                    $p->precio = $p->precio ?? 0;
                }
            }
        } catch (\Throwable $e) {
            $categorias = collect([]);
            $productos = collect([]);
        }

        return view('admin.pos.index', compact('categorias', 'productos'));
    }

    public function storeVenta(Request $request)
    {
        $request->validate([
            'items' => 'required|string', // JSON string de items
            'metodo_pago' => 'required|in:efectivo,qr',
            'monto_total' => 'required|numeric|min:0.01'
        ]);

        $items = json_decode($request->items, true);

        if (empty($items) || !is_array($items)) {
            return back()->with('error', 'DEBES AGREGAR AL MENOS UN PRODUCTO AL CARRITO');
        }

        $usuario_id = Session::get('usuario_id') ?? 1;

        $venta_id = DB::transaction(function () use ($request, $items, $usuario_id) {
            try {
                DB::statement("ALTER TABLE `ventas` MODIFY COLUMN `estado` VARCHAR(50) NOT NULL DEFAULT 'abierta'");
                DB::statement("ALTER TABLE `ventas` MODIFY COLUMN `origen` VARCHAR(50) NOT NULL DEFAULT 'local'");
                DB::statement("ALTER TABLE `ventas` MODIFY COLUMN `tipo_venta` VARCHAR(50) NOT NULL DEFAULT 'llevar'");
            } catch (\Throwable $e) {
            }

            $venta = Venta::create([
                'origen' => 'local',
                'tipo_venta' => 'llevar',
                'cliente_nombre' => !empty($request->cliente_nombre) ? strtoupper(trim($request->cliente_nombre)) : 'CLIENTE MOSTRADOR',
                'estado' => 'cerrada',
                'monto_total' => $request->monto_total,
                'usuario_apertura_id' => $usuario_id,
                'usuario_cierre_id' => $usuario_id,
                'fecha_apertura' => now(),
                'fecha_cierre' => now(),
            ]);

            foreach ($items as $item) {
                VentaItem::create([
                    'venta_id' => $venta->id,
                    'producto_id' => $item['producto_id'] ?? null,
                    'variante_id' => $item['variante_id'] ?? null,
                    'nombre_producto' => strtoupper($item['nombre_producto']),
                    'nombre_variante' => !empty($item['nombre_variante']) ? strtoupper($item['nombre_variante']) : null,
                    'cantidad' => intval($item['cantidad']),
                    'precio_unitario' => floatval($item['precio_unitario']),
                    'precio_total' => floatval($item['precio_total']),
                ]);
            }

            Pago::create([
                'venta_id' => $venta->id,
                'metodo_pago' => $request->metodo_pago,
                'monto' => $request->monto_total,
            ]);

            return $venta->id;
        });

        return redirect()->route('admin.pos')->with('success', 'VENTA COMPLETADA CON ÉXITO')->with('ticket_venta_id', $venta_id);
    }
}

