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
            $categorias = Categoria::where('activo', 1)->orderBy('nombre', 'asc')->get();
            $productos = Producto::where('activo', 1)
                ->where('disponible', 1)
                ->with([
                    'variantes' => function ($q) {
                        $q->where('activo', 1)->where('disponible', 1);
                    }
                ])
                ->get();
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

        $usuarioID = Session::get('usuarioID') ?? 1;

        $ventaID = DB::transaction(function () use ($request, $items, $usuarioID) {
            $venta = Venta::create([
                'origen' => 'local',
                'tipo_venta' => 'llevar',
                'cliente_nombre' => !empty($request->cliente_nombre) ? strtoupper(trim($request->cliente_nombre)) : 'CLIENTE MOSTRADOR',
                'estado' => 'cerrada',
                'monto_total' => $request->monto_total,
                'usuario_apertura_id' => $usuarioID,
                'usuario_cierre_id' => $usuarioID,
                'fecha_apertura' => now(),
                'fecha_cierre' => now(),
            ]);

            foreach ($items as $item) {
                VentaItem::create([
                    'ventaID' => $venta->ventaID,
                    'productoID' => $item['productoID'] ?? null,
                    'varianteID' => $item['varianteID'] ?? null,
                    'nombre_producto' => strtoupper($item['nombre_producto']),
                    'nombre_variante' => !empty($item['nombre_variante']) ? strtoupper($item['nombre_variante']) : null,
                    'cantidad' => intval($item['cantidad']),
                    'precio_unitario' => floatval($item['precio_unitario']),
                    'precio_total' => floatval($item['precio_total']),
                    'fecha_creacion' => now(),
                ]);
            }

            Pago::create([
                'ventaID' => $venta->ventaID,
                'metodo_pago' => $request->metodo_pago,
                'monto' => $request->monto_total,
                'fecha_creacion' => now(),
            ]);

            return $venta->ventaID;
        });

        return redirect()->route('admin.pos')->with('success', 'VENTA COMPLETADA CON ÉXITO')->with('ticket_venta_id', $ventaID);
    }
}
