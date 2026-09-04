<?php
error_reporting(0);
ini_set('display_errors', 0);

session_start();
require_once("../../conexion.php");

header('Content-Type: application/json');

$empresaID = $_SESSION['empresaID'] ?? null;
$usuarioID = $_SESSION['sesion_id_usuario'] ?? null;

if (!$empresaID || !$usuarioID) {
    echo json_encode(['status' => 'error', 'mensaje' => 'No autorizado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $datos = json_decode(file_get_contents('php://input'), true);

    if ($datos['accion'] === 'crear') {
        $items = $datos['items'] ?? [];

        if (empty($items)) {
            echo json_encode(['status' => 'error', 'mensaje' => 'Datos inválidos']);
            exit;
        }

        try {
            $db->beginTransaction();

            // Calcular monto total de la compra
            $monto_total_items = 0;
            foreach ($items as $item) {
                $monto_total_items += ($item['cantidad'] * $item['costo']);
            }

            $monto_total_front = floatval($datos['monto_total'] ?? 0);
            $monto_total = ($monto_total_front > 0) ? $monto_total_front : $monto_total_items;

            // Insertar movimiento de compra
            $sql_compra = "INSERT INTO movimientos_tienda (empresaID, usuarioID, tipo, monto_total, _estado, _fec_insercion, _usuario)
                           VALUES (?, ?, 'COMPRA', ?, 'A', NOW(), ?)";
            $db->ejecutar($sql_compra, [$empresaID, $usuarioID, $monto_total, $usuarioID]);
            $movimientoID = $db->ultimoInsertId();

            // Insertar detalles de compra y actualizar stock de cada producto
            foreach ($items as $item) {
                $productoID = $item['productoID'] ?? null;
                $cantidad = $item['cantidad'] ?? 0;
                $costo = $item['costo'] ?? 0;
                $subtotal = $cantidad * $costo;

                if (!$productoID || $cantidad <= 0)
                    continue;

                $sql_detalle = "INSERT INTO movimiento_detalles (movimientoID, productoID, cantidad, precio_unitario, subtotal, _estado)
                                VALUES (?, ?, ?, ?, ?, 'A')";
                $db->ejecutar($sql_detalle, [$movimientoID, $productoID, $cantidad, $costo, $subtotal]);

                // Actualizar stock y precio de costo del producto
                $sql_update = "UPDATE productos 
                               SET stock = stock + ?, 
                                   precio_costo = ?,
                                   _fec_modificacion = NOW()
                               WHERE productoID = ? AND empresaID = ?";
                $db->ejecutar($sql_update, [$cantidad, $costo, $productoID, $empresaID]);
            }

            // Actualizar caja_tienda (restar monto de compras)
            $sql_update_caja = "UPDATE caja_tienda 
                                SET saldo_efectivo = saldo_efectivo - ?,
                                    saldo_total = saldo_total - ?,
                                    _fec_modificacion = NOW()
                                WHERE empresaID = ? AND _estado = 'A'";
            $db->ejecutar($sql_update_caja, [$monto_total, $monto_total, $empresaID]);

            $db->commit();

            echo json_encode(['status' => 'ok', 'mensaje' => 'Compra registrada exitosamente']);
        } catch (Exception $e) {
            $db->rollBack();
            echo json_encode(['status' => 'error', 'mensaje' => 'Error al registrar compra: ' . $e->getMessage()]);
        }
    }
} else {
    echo json_encode(['status' => 'error', 'mensaje' => 'Método no permitido']);
}
