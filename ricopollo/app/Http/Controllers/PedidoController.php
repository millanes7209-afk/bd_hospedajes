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
            ->orderBy('fecha_creacion', 'desc')
            ->get();

        $pedidosArray = array_map(function ($p) {
            $items = DB::table('pedido_items')
                ->leftJoin('productos', 'pedido_items.productoID', '=', 'productos.productoID')
                ->select(
                    'pedido_items.*',
                    DB::raw("COALESCE(NULLIF(pedido_items.nombre_variante, ''), productos.nombre, 'PRODUCTO') AS nombre_variante")
                )
                ->where('pedido_items.pedidoID', $p->pedidoID)
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
            ->orderBy('fecha_creacion', 'desc')
            ->get();

        $maxId = 0;
        $hashState = "";

        $pedidosArray = array_map(function ($p) use (&$maxId, &$hashState) {
            if ((int) $p->pedidoID > $maxId) {
                $maxId = (int) $p->pedidoID;
            }
            $hashState .= $p->pedidoID . '-' . $p->estado . ';';

            $items = DB::table('pedido_items')
                ->leftJoin('productos', 'pedido_items.productoID', '=', 'productos.productoID')
                ->select(
                    'pedido_items.*',
                    DB::raw("COALESCE(NULLIF(pedido_items.nombre_variante, ''), productos.nombre, 'PRODUCTO') AS nombre_variante")
                )
                ->where('pedido_items.pedidoID', $p->pedidoID)
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
            ->where('fecha_creacion', '>=', now()->subSeconds(10))
            ->orderBy('pedidoID', 'desc')
            ->first();

        if ($pedidoReciente) {
            // Si ya existe un pedido idéntico creado hace menos de 10 segundos, redirigir al ticket existente
            return redirect()->route('ticket.show', ['id' => $pedidoReciente->pedidoID]);
        }

        DB::beginTransaction();
        try {
            $pedidoID = DB::table('pedidos')->insertGetId([
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
                'fecha_creacion' => now()
            ]);

            $total = 0;
            foreach ($cartFinal as $line) {
                $qty = (int) ($line['qty'] ?? 0);
                if ($qty <= 0)
                    continue;

                $type = $line['type'] ?? 'producto';
                $productoID = (int) ($line['productoID'] ?? 0);
                $nombre = strtoupper($line['nombre'] ?? '');
                $precio = (float) ($line['precio'] ?? 0);
                $lineTotal = $precio * $qty;

                $nombre_variante = $nombre;

                DB::table('pedido_items')->insert([
                    'pedidoID' => $pedidoID,
                    'productoID' => $productoID ?: null,
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

            DB::table('pedidos')->where('pedidoID', $pedidoID)->update(['monto_total' => $total]);

            // Registrar en registros_pedidos auditoría
            try {
                DB::table('registros_pedidos')->insert([
                    'pedidoID' => $pedidoID,
                    'evento' => 'SOLICITUD_CREADA',
                    'detalles' => 'SOLICITUD DE PEDIDO ENVIADA POR EL CLIENTE',
                    'fecha_creacion' => now()
                ]);
            } catch (Exception $ex) {
                // Ignore if log table fails
            }

            DB::commit();

            return redirect()->route('ticket.show', ['id' => $pedidoID]);
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
        $pedido = DB::table('pedidos')->where('pedidoID', $id)->first();
        if (!$pedido) {
            return response()->json(['error' => 'Pedido no encontrado'], 404);
        }

        return response()->json([
            'pedidoID' => $pedido->pedidoID,
            'numero_pedido' => $pedido->numero_pedido,
            'estado' => strtolower($pedido->estado),
            'estado_pago' => strtolower($pedido->estado_pago),
            'metodo_pago' => strtolower($pedido->metodo_pago),
            'monto_total' => (float) $pedido->monto_total,
            'cliente_nombre' => $pedido->cliente_nombre,
            'cliente_telefono' => $pedido->cliente_telefono,
            'direccion_entrega' => $pedido->direccion_entrega,
            'nota' => $pedido->nota,
            'fecha_creacion' => $pedido->fecha_creacion
        ]);
    }

    /**
     * Cajero acepta la solicitud de pedido
     */
    public function aceptarPedido($id)
    {
        DB::table('pedidos')->where('pedidoID', $id)->update([
            'estado' => 'aceptado',
            'aceptado_en' => now()
        ]);

        try {
            DB::table('registros_pedidos')->insert([
                'pedidoID' => $id,
                'evento' => 'SOLICITUD_ACEPTADA',
                'detalles' => 'SOLICITUD ACEPTADA POR EL CAJERO',
                'fecha_creacion' => now()
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

        DB::table('pedidos')->where('pedidoID', $id)->update([
            'estado' => 'cancelado',
            'nota' => DB::raw("CONCAT(IFNULL(nota, ''), ' [RECHAZADO: {$motivo}]')")
        ]);

        try {
            DB::table('registros_pedidos')->insert([
                'pedidoID' => $id,
                'evento' => 'SOLICITUD_RECHAZADA',
                'detalles' => 'MOTIVO: ' . $motivo,
                'fecha_creacion' => now()
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

        DB::table('pedidos')->where('pedidoID', $id)->update([
            'metodo_pago' => $metodo,
            'estado' => 'preparando',
            'estado_pago' => ($metodo === 'qr') ? 'pagado' : 'pendiente'
        ]);

        if (!empty($notaAdd)) {
            DB::table('pedidos')->where('pedidoID', $id)->update([
                'nota' => DB::raw("CONCAT(IFNULL(nota, ''), '{$notaAdd}')")
            ]);
        }

        try {
            DB::table('registros_pedidos')->insert([
                'pedidoID' => $id,
                'evento' => 'METODO_PAGO_SELECCIONADO',
                'detalles' => 'METODO: ' . strtoupper($metodo) . ($notaAdd ? ' - ' . $notaAdd : ''),
                'fecha_creacion' => now()
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
        $pedido = DB::table('pedidos')->where('pedidoID', $id)->first();
        if (!$pedido) {
            return redirect()->route('menu')->with('error', 'Pedido no encontrado');
        }

        $items = DB::table('pedido_items')
            ->leftJoin('productos', 'pedido_items.productoID', '=', 'productos.productoID')
            ->select(
                'pedido_items.*',
                DB::raw("COALESCE(NULLIF(pedido_items.nombre_variante, ''), productos.nombre, 'PRODUCTO') AS nombre_variante")
            )
            ->where('pedido_items.pedidoID', $id)
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
        DB::table('pedidos')->where('pedidoID', $id)->update(['estado' => $estado]);

        try {
            DB::table('registros_pedidos')->insert([
                'pedidoID' => $id,
                'evento' => 'CAMBIO_ESTADO',
                'detalles' => 'NUEVO ESTADO: ' . strtoupper($estado),
                'fecha_creacion' => now()
            ]);
        } catch (Exception $e) {
        }

        return redirect()->back();
    }

    /**
     * Impresión Directa mediante Socket TCP IP (Puerto 9100 - Impresora de Red/RICOH/ESC-POS)
     */
    public function imprimirDirectoTcp(Request $request, $id)
    {
        $printerIp = $request->input('ip', '192.168.0.211');
        $printerPort = (int) $request->input('port', 9100);

        $pedido = DB::table('pedidos')->where('pedidoID', $id)->first();
        if (!$pedido) {
            return response()->json(['success' => false, 'error' => 'Pedido no encontrado'], 404);
        }

        $items = DB::table('pedido_items')
            ->leftJoin('productos', 'pedido_items.productoID', '=', 'productos.productoID')
            ->select(
                'pedido_items.*',
                DB::raw("COALESCE(NULLIF(pedido_items.nombre_variante, ''), productos.nombre, 'PRODUCTO') AS nombre_variante")
            )
            ->where('pedido_items.pedidoID', $id)
            ->get();

        // Construir flujo de comandos ESC/POS
        $ESC = "\x1b";
        $GS = "\x1d";
        $LF = "\x0a";

        $data = $ESC . "@"; // Inicializar
        $data .= $ESC . "a\x01"; // Centrar
        $data .= $ESC . "!\x30"; // Texto Doble Alto / Ancho (Encabezado)
        $data .= "RICO POLLO\n";
        $data .= $ESC . "!\x00"; // Normal
        $data .= "COMPROBANTE DE COMPRA\n";
        $data .= "Tel: 591-76543210\n";
        $data .= "--------------------------------\n";

        $data .= $ESC . "a\x00"; // Izquierda
        $data .= "Pedido:  #" . $pedido->numero_pedido . "\n";
        $data .= "Fecha:   " . date('d/m/Y H:i', strtotime($pedido->fecha_creacion)) . "\n";
        $data .= "Cliente: " . mb_strtoupper($pedido->cliente_nombre) . "\n";
        $data .= "Tel:     " . $pedido->cliente_telefono . "\n";
        $data .= "Tipo:    " . mb_strtoupper($pedido->tipo_pedido) . "\n";
        if (!empty($pedido->direccion_entrega)) {
            $data .= "Direccion: " . mb_strtoupper($pedido->direccion_entrega) . "\n";
        }
        if (!empty($pedido->metodo_pago) && $pedido->metodo_pago !== 'ninguno') {
            $data .= "Pago:    " . mb_strtoupper($pedido->metodo_pago) . "\n";
        }
        $data .= "--------------------------------\n";

        foreach ($items as $item) {
            $cant = (int) $item->cantidad;
            $nombre = mb_strtoupper($item->nombre_variante ?: 'PRODUCTO');
            $precio = "Bs." . number_format((float) $item->precio_total, 2);
            $lineaNombre = $cant . "x " . $nombre;

            if (mb_strlen($lineaNombre) + mb_strlen($precio) >= 32) {
                $lineaNombre = mb_substr($lineaNombre, 0, 31 - mb_strlen($precio));
            }
            $espacios = max(1, 32 - mb_strlen($lineaNombre) - mb_strlen($precio));
            $data .= $lineaNombre . str_repeat(" ", $espacios) . $precio . "\n";
        }

        $data .= "--------------------------------\n";
        $data .= $ESC . "a\x01"; // Centrar
        $data .= $ESC . "!\x20"; // Texto Doble Alto
        $data .= "TOTAL: Bs." . number_format((float) $pedido->monto_total, 2) . "\n";
        $data .= $ESC . "!\x00"; // Normal
        $data .= "\nGRACIAS POR SU COMPRA!\n\n\n\n";
        $data .= $GS . "V\x01"; // Corte de papel

        // Conexión TCP Socket a la impresora de red
        $fp = @fsockopen($printerIp, $printerPort, $errno, $errstr, 2.5);
        if (!$fp) {
            return response()->json([
                'success' => false,
                'error' => "No se pudo conectar a la impresora en {$printerIp}:{$printerPort}. Verifique que la impresora esté encendida y conectada a la red local. (Detalle: {$errstr})"
            ], 500);
        }

        fwrite($fp, $data);
        fclose($fp);

        return response()->json([
            'success' => true,
            'message' => "Ticket impreso correctamente en {$printerIp}:{$printerPort}"
        ]);
    }
}

