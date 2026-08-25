<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Pedido;
use App\Models\PedidoItem;
use Exception;

class PedidoController extends Controller
{
    /**
     * Listar todos los pedidos en el panel de administración
     */
    public function index()
    {
        $pedidos = DB::table('pedidos')
            ->orderBy('created_at', 'desc')
            ->get();

        $pedidosArray = array_map(function ($p) {
            $items = DB::table('pedido_items')
                ->leftJoin('productos', 'pedido_items.producto_id', '=', 'productos.id')
                ->select(
                    'pedido_items.*',
                    DB::raw("COALESCE(NULLIF(pedido_items.nombre_variante, ''), productos.nombre, 'PRODUCTO') AS nombre_variante")
                )
                ->where('pedido_items.pedido_id', $p->id)
                ->get();

            $pArr = (array) $p;
            $pArr['items'] = array_map(fn($i) => (array) $i, $items->toArray());
            return $pArr;
        }, $pedidos->toArray());

        return view('admin.pedidos', [
            'pedidos' => $pedidosArray
        ]);
    }

    /**
     * Endpoint API para la vista del cajero (polling en tiempo real)
     */
    public function getAdminApiPedidos()
    {
        $pedidos = DB::table('pedidos')
            ->orderBy('created_at', 'desc')
            ->get();

        $maxId = 0;
        $hashState = "";

        $pedidosArray = array_map(function ($p) use (&$maxId, &$hashState) {
            if ((int) $p->id > $maxId) {
                $maxId = (int) $p->id;
            }
            $hashState .= $p->id . '-' . $p->estado . ';';

            $items = DB::table('pedido_items')
                ->leftJoin('productos', 'pedido_items.producto_id', '=', 'productos.id')
                ->select(
                    'pedido_items.*',
                    DB::raw("COALESCE(NULLIF(pedido_items.nombre_variante, ''), productos.nombre, 'PRODUCTO') AS nombre_variante")
                )
                ->where('pedido_items.pedido_id', $p->id)
                ->get();

            $pArr = (array) $p;
            $pArr['items'] = array_map(fn($i) => (array) $i, $items->toArray());
            return $pArr;
        }, $pedidos->toArray());

        return response()->json([
            'total' => count($pedidosArray),
            'max_id' => $maxId,
            'hash_state' => md5($hashState),
            'pedidos' => $pedidosArray
        ]);
    }

    /**
     * Mostrar página de confirmación de pedido (Checkout)
     */
    public function showCheckout(Request $request)
    {
        $cartItemsDisplay = $request->input('cart_items', session('cart_items', []));
        $error = session('error', '');

        $displayTotal = 0;
        if (is_array($cartItemsDisplay)) {
            foreach ($cartItemsDisplay as $line) {
                $displayTotal += (float) ($line['precio'] ?? 0) * (int) ($line['qty'] ?? 0);
            }
        }

        return view('order', compact('cartItemsDisplay', 'displayTotal', 'error'));
    }

    /**
     * Procesar y guardar un nuevo pedido
     */
    public function storeOrder(Request $request)
    {
        $cartFinal = $request->input('cart_items_final', []);

        $cliente_nombre = strtoupper(trim($request->input('cliente_nombre', 'CLIENTE')));
        $cliente_telefono = strtoupper(trim($request->input('cliente_telefono', '')));
        $tipo_pedido = 'domicilio';
        $direccion = strtoupper(trim($request->input('direccion_entrega', '')));
        $numero_mesa = '';
        $nota = strtoupper(trim($request->input('nota', '')));
        $metodo_pago = trim($request->input('metodo_pago', 'ninguno'));
        if (!in_array($metodo_pago, ['efectivo', 'qr', 'ninguno'])) {
            $metodo_pago = 'ninguno';
        }
        $latitud = $request->filled('latitud') ? (float) $request->input('latitud') : null;
        $longitud = $request->filled('longitud') ? (float) $request->input('longitud') : null;
        $numero_pedido = 'RPO-' . time();

        if (empty($cliente_telefono) || empty($cliente_nombre) || empty($direccion)) {
            return redirect()->back()->withInput()->with('error', 'TODOS LOS CAMPOS OBLIGATORIOS DEBEN SER COMPLETADOS.');
        }

        if (empty($cartFinal)) {
            return redirect()->back()->withInput()->with('error', 'DEBES AGREGAR AL MENOS UN PRODUCTO.');
        }

        // Protección anti-duplicados backend (Previene doble clic o peticiones simultáneas)
        $pedidoReciente = DB::table('pedidos')
            ->where('cliente_telefono', $cliente_telefono)
            ->where('cliente_nombre', $cliente_nombre)
            ->where('created_at', '>=', now()->subSeconds(10))
            ->orderBy('id', 'desc')
            ->first();

        if ($pedidoReciente) {
            // Si ya existe un pedido idéntico creado hace menos de 10 segundos, redirigir al ticket existente
            return redirect()->route('ticket.show', ['id' => $pedidoReciente->id]);
        }

        DB::beginTransaction();
        try {
            $pedido_id = DB::table('pedidos')->insertGetId([
                'numero_pedido' => $numero_pedido,
                'cliente_nombre' => $cliente_nombre,
                'cliente_telefono' => $cliente_telefono,
                'tipo_pedido' => $tipo_pedido,
                'numero_mesa' => $numero_mesa,
                'direccion_entrega' => $direccion,
                'nota' => $nota,
                'monto_total' => 0,
                'estado' => 'pendiente',
                'estado_pago' => 'pendiente',
                'metodo_pago' => $metodo_pago,
                'latitud' => $latitud,
                'longitud' => $longitud,
                'created_at' => now()
            ]);

            $total = 0;
            foreach ($cartFinal as $line) {
                $qty = (int) ($line['qty'] ?? 0);
                if ($qty <= 0)
                    continue;

                $type = $line['type'] ?? 'producto';
                $producto_id = (int) ($line['producto_id'] ?? 0);
                $nombre = strtoupper($line['nombre'] ?? '');
                $precio = (float) ($line['precio'] ?? 0);
                $lineTotal = $precio * $qty;

                $nombre_variante = $nombre;

                DB::table('pedido_items')->insert([
                    'pedido_id' => $pedido_id,
                    'producto_id' => $producto_id ?: null,
                    'nombre_variante' => $nombre_variante,
                    'cantidad' => $qty,
                    'precio_unitario' => $precio,
                    'precio_total' => $lineTotal
                ]);

                $total += $lineTotal;
            }

            if ($total <= 0) {
                throw new Exception('DEBES AGREGAR AL MENOS UN PRODUCTO.');
            }

            DB::table('pedidos')->where('id', $pedido_id)->update(['monto_total' => $total]);

            // Registrar en registros_pedidos auditoría
            try {
                DB::table('registros_pedidos')->insert([
                    'pedido_id' => $pedido_id,
                    'evento' => 'SOLICITUD_CREADA',
                    'detalles' => 'SOLICITUD DE PEDIDO ENVIADA POR EL CLIENTE',
                    'created_at' => now()
                ]);
            } catch (Exception $ex) {
                // Ignore if log table fails
            }

            DB::commit();

            return redirect()->route('ticket.show', ['id' => $pedido_id]);
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', strtoupper($e->getMessage() ?: 'ERROR AL CREAR PEDIDO'));
        }
    }

    /**
     * Endpoint API para consultar el estado en tiempo real (AJAX)
     */
    public function getApiEstado($id)
    {
        $pedido = DB::table('pedidos')->where('id', $id)->first();
        if (!$pedido) {
            return response()->json(['error' => 'Pedido no encontrado'], 404);
        }

        return response()->json([
            'id' => $pedido->id,
            'numero_pedido' => $pedido->numero_pedido,
            'estado' => strtolower($pedido->estado),
            'estado_pago' => strtolower($pedido->estado_pago),
            'metodo_pago' => strtolower($pedido->metodo_pago),
            'monto_total' => (float) $pedido->monto_total,
            'cliente_nombre' => $pedido->cliente_nombre,
            'cliente_telefono' => $pedido->cliente_telefono,
            'direccion_entrega' => $pedido->direccion_entrega,
            'nota' => $pedido->nota,
            'created_at' => $pedido->created_at
        ]);
    }

    /**
     * Cajero acepta la solicitud de pedido
     */
    public function aceptarPedido($id)
    {
        DB::table('pedidos')->where('id', $id)->update([
            'estado' => 'aceptado',
            'aceptado_en' => now()
        ]);

        try {
            DB::table('registros_pedidos')->insert([
                'pedido_id' => $id,
                'evento' => 'SOLICITUD_ACEPTADA',
                'detalles' => 'SOLICITUD ACEPTADA POR EL CAJERO',
                'created_at' => now()
            ]);
        } catch (Exception $e) {
        }

        return redirect()->back()->with('success', 'SOLICITUD ACEPTADA.');
    }

    /**
     * Cajero rechaza/cancela la solicitud de pedido
     */
    public function rechazarPedido(Request $request, $id)
    {
        $motivo = strtoupper(trim($request->input('motivo', 'SIN STOCK DISPONIBLE')));

        DB::table('pedidos')->where('id', $id)->update([
            'estado' => 'cancelado',
            'nota' => DB::raw("CONCAT(IFNULL(nota, ''), ' [RECHAZADO: {$motivo}]')")
        ]);

        try {
            DB::table('registros_pedidos')->insert([
                'pedido_id' => $id,
                'evento' => 'SOLICITUD_RECHAZADA',
                'detalles' => 'MOTIVO: ' . $motivo,
                'created_at' => now()
            ]);
        } catch (Exception $e) {
        }

        return redirect()->back()->with('success', 'SOLICITUD RECHAZADA.');
    }

    /**
     * El cliente selecciona su método de pago (QR o Efectivo) tras la aprobación
     */
    public function confirmarPagoCliente(Request $request, $id)
    {
        $metodo = trim(strtolower($request->input('metodo_pago', 'efectivo')));
        if (!in_array($metodo, ['efectivo', 'qr'])) {
            $metodo = 'efectivo';
        }

        $monto_pago = trim($request->input('monto_pago', ''));
        $notaAdd = "";
        if ($metodo === 'efectivo' && !empty($monto_pago)) {
            $notaAdd = " [PAGA CON: {$monto_pago} BS]";
        }

        DB::table('pedidos')->where('id', $id)->update([
            'metodo_pago' => $metodo,
            'estado' => 'preparando',
            'estado_pago' => ($metodo === 'qr') ? 'pagado' : 'pendiente'
        ]);

        if (!empty($notaAdd)) {
            DB::table('pedidos')->where('id', $id)->update([
                'nota' => DB::raw("CONCAT(IFNULL(nota, ''), '{$notaAdd}')")
            ]);
        }

        try {
            DB::table('registros_pedidos')->insert([
                'pedido_id' => $id,
                'evento' => 'METODO_PAGO_SELECCIONADO',
                'detalles' => 'METODO: ' . strtoupper($metodo) . ($notaAdd ? ' - ' . $notaAdd : ''),
                'created_at' => now()
            ]);
        } catch (Exception $e) {
        }

        return response()->json(['success' => true]);
    }

    /**
     * Mostrar ticket de un pedido
     */
    public function showTicket($id)
    {
        $pedido = DB::table('pedidos')->where('id', $id)->first();
        if (!$pedido) {
            return redirect()->route('menu')->with('error', 'Pedido no encontrado');
        }

        $items = DB::table('pedido_items')
            ->leftJoin('productos', 'pedido_items.producto_id', '=', 'productos.id')
            ->select(
                'pedido_items.*',
                DB::raw("COALESCE(NULLIF(pedido_items.nombre_variante, ''), productos.nombre, 'PRODUCTO') AS nombre_variante")
            )
            ->where('pedido_items.pedido_id', $id)
            ->get();

        return view('ticket', [
            'pedido' => (array) $pedido,
            'items' => array_map(fn($i) => (array) $i, $items->toArray())
        ]);
    }

    /**
     * Actualizar estado del pedido (Cajero)
     */
    public function updateEstado(Request $request, $id)
    {
        $estado = strtolower(trim($request->input('estado')));
        DB::table('pedidos')->where('id', $id)->update(['estado' => $estado]);

        try {
            DB::table('registros_pedidos')->insert([
                'pedido_id' => $id,
                'evento' => 'CAMBIO_ESTADO',
                'detalles' => 'NUEVO ESTADO: ' . strtoupper($estado),
                'created_at' => now()
            ]);
        } catch (Exception $e) {
        }

        return redirect()->back();
    }
}

