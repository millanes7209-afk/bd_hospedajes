<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mesa;
use App\Models\Venta;
use App\Models\VentaItem;
use App\Models\Pago;
use App\Models\Producto;
use App\Models\ProductoVariante;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;

class MesaController extends Controller
{
    /**
     * Muestra la vista principal de Mesas (POS Local)
     */
    public function index()
    {
        try {
            // Cargar mesas con su cuenta activa (si está ocupada)
            $mesas = Mesa::with(['cuentaActiva.items', 'cuentaActiva.pagos'])->get();

            // Si no existen mesas creadas, inicializar Mesa 1 y Mesa 2 por defecto
            if ($mesas->isEmpty()) {
                Mesa::create(['nombre' => 'Mesa 1', 'estado' => 'libre']);
                Mesa::create(['nombre' => 'Mesa 2', 'estado' => 'libre']);
                $mesas = Mesa::with(['cuentaActiva.items', 'cuentaActiva.pagos'])->get();
            }
        } catch (\Throwable $e) {
            $mesas = collect([]);
        }

        try {
            // Cargar catálogo de productos disponibles para agregar a cuentas
            $productos = Producto::where('disponible', 1)
                ->where('disponible', 1)
                ->with([
                    'variantes' => function ($q) {
                        $q->where('activo', 1)->where('disponible', 1);
                    }
                ])
                ->get();
        } catch (\Throwable $e) {
            $productos = collect([]);
        }

        return view('admin.mesas.index', compact('mesas', 'productos'));
    }

    /**
     * Abre una cuenta en una mesa libre
     */
    public function abrirMesa(Request $request, $mesa_id)
    {
        $mesa = Mesa::findOrFail($mesa_id);

        if ($mesa->estado === 'ocupada') {
            return back()->with('error', 'LA MESA YA SE ENCUENTRA OCUPADA');
        }

        DB::transaction(function () use ($mesa) {
            $mesa->update(['estado' => 'ocupada']);

            Venta::create([
                'origen' => 'local',
                'tipo_venta' => 'mesa',
                'mesa_id' => $mesa->id,
                'estado' => 'abierta',
                'monto_total' => 0.00,
                'usuario_apertura_id' => Session::get('usuario_id') ?? 1,
                'fecha_apertura' => now(),
            ]);
        });

        return back()->with('success', "CUENTA ABIERTA EN {$mesa->nombre}");
    }

    /**
     * Agrega un producto/variante a la cuenta abierta de una mesa
     */
    public function agregarItem(Request $request, $mesa_id)
    {
        $request->validate([
            'producto_id' => 'required|integer',
            'variante_id' => 'nullable|integer',
            'cantidad' => 'required|integer|min:1',
        ]);

        $mesa = Mesa::findOrFail($mesa_id);
        $venta = $mesa->cuentaActiva;

        if (!$venta) {
            return back()->with('error', 'NO HAY UNA CUENTA ABIERTA EN ESTA MESA');
        }

        $producto = Producto::findOrFail($request->producto_id);
        $variante = $request->variante_id ? ProductoVariante::find($request->variante_id) : null;

        $nombreProducto = strtoupper($producto->nombre);
        $nombreVariante = $variante ? strtoupper($variante->nombre_variante) : null;
        $precioUnitario = $variante ? $variante->precio : ($producto->precio ?? 0.00);

        $cantidad = (int) $request->cantidad;
        $precioTotal = $precioUnitario * $cantidad;

        DB::transaction(function () use ($venta, $producto, $variante, $nombreProducto, $nombreVariante, $cantidad, $precioUnitario, $precioTotal, $request) {
            VentaItem::create([
                'venta_id' => $venta->id,
                'producto_id' => $producto->id,
                'variante_id' => $variante ? $variante->id : null,
                'nombre_producto' => $nombreProducto,
                'nombre_variante' => $nombreVariante,
                'cantidad' => $cantidad,
                'precio_unitario' => $precioUnitario,
                'precio_total' => $precioTotal,
                'nota' => $request->nota ? strtoupper($request->nota) : null,
            ]);

            // Recalcular monto total de la venta
            $nuevoTotal = $venta->items()->sum('precio_total');
            $venta->update(['monto_total' => $nuevoTotal]);
        });

        return back()->with('success', "PRODUCTO AGREGADO A {$mesa->nombre}");
    }

    /**
     * Remueve un ítem de la cuenta abierta
     */
    public function removerItem($itemId)
    {
        $item = VentaItem::findOrFail($itemId);
        $venta = $item->venta;

        DB::transaction(function () use ($item, $venta) {
            $item->delete();
            $nuevoTotal = $venta->items()->sum('precio_total');
            $venta->update(['monto_total' => $nuevoTotal]);
        });

        return back()->with('success', 'ÍTEM REMOVIDO DE LA CUENTA');
    }

    /**
     * Registra un pago (QR o Efectivo) y cierra la mesa si el saldo llega a 0
     */
    public function registrarPago(Request $request, $venta_id)
    {
        $request->validate([
            'metodo_pago' => 'required|in:qr,efectivo',
            'monto' => 'required|numeric|min:0.01',
        ]);

        $venta = Venta::findOrFail($venta_id);

        DB::transaction(function () use ($venta, $request) {
            Pago::create([
                'venta_id' => $venta->id,
                'metodo_pago' => $request->metodo_pago,
                'monto' => $request->monto,
            ]);

            // Verificar si el saldo ha sido cubierto por completo
            $totalPagado = $venta->totalPagado();

            if ($totalPagado >= $venta->monto_total) {
                $venta->update([
                    'estado' => 'cerrada',
                    'usuario_cierre_id' => Session::get('usuario_id') ?? 1,
                    'fecha_cierre' => now(),
                ]);

                if ($venta->mesa_id) {
                    Mesa::where('id', $venta->mesa_id)->update(['estado' => 'libre']);
                }
            }
        });

        return back()->with('success', 'PAGO REGISTRADO CORRECTAMENTE');
    }

    /**
     * Cancela la apertura y libera una mesa ocupada
     */
    public function liberarMesa($mesa_id)
    {
        $mesa = Mesa::findOrFail($mesa_id);

        DB::transaction(function () use ($mesa) {
            $mesa->update(['estado' => 'libre']);

            $cuenta = Venta::where('mesa_id', $mesa->id)->where('estado', 'abierta')->first();
            if ($cuenta) {
                $cuenta->update([
                    'estado' => 'cerrada',
                    'fecha_cierre' => now()
                ]);
            }
        });

        return back()->with('success', "MESA {$mesa->nombre} LIBERADA CORRECTAMENTE");
    }
}

