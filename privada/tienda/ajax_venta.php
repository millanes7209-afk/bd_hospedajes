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
        $formas_pago = $datos['formas_pago'] ?? [];

        if (empty($items) || empty($formas_pago)) {
            echo json_encode(['status' => 'error', 'mensaje' => 'Datos inválidos']);
            exit;
        }

        try {
            $db->beginTransaction();

            // Calcular monto total
            $monto_total_items = 0;
            $monto_efectivo = 0;
            $monto_qr = 0;
            foreach ($items as $item) {
                $monto_total_items += ($item['cantidad'] * $item['precio']);
            }

            $monto_total_front = floatval($datos['monto_total'] ?? 0);
            $monto_total = ($monto_total_front > 0) ? $monto_total_front : $monto_total_items;

            // Insertar movimiento de venta
            $sql_venta = "INSERT INTO movimientos_tienda (empresaID, usuarioID, tipo, monto_total, _estado, _fec_insercion, _usuario)
                          VALUES (?, ?, 'VENTA', ?, 'A', NOW(), ?)";
            $db->ejecutar($sql_venta, [$empresaID, $usuarioID, $monto_total, $usuarioID]);
            $movimientoID = $db->ultimoInsertId();

            // Insertar pagos en tienda_pagos
            foreach ($formas_pago as $fp) {
                $formapagoID = intval($fp['formapagoID'] ?? 0);
                $monto_fp = floatval($fp['monto'] ?? 0);

                if (!$formapagoID || $monto_fp <= 0)
                    continue;

                // Obtener el tipo para clasificar en efectivo/qr
                $sql_tipo = "SELECT tipo FROM formas_pago WHERE formapagoID = ? AND _estado = 'A' LIMIT 1";
                $fp_data = $db->obtenerFila($sql_tipo, [$formapagoID]);
                if ($fp_data) {
                    $tipo_upper = strtoupper($fp_data['tipo']);
                    if (strpos($tipo_upper, 'QR') !== false) {
                        $monto_qr += $monto_fp;
                    } else {
                        $monto_efectivo += $monto_fp;
                    }

                    $sql_pago = "INSERT INTO tienda_pagos (movimientoID, formapagoID, monto, _estado)
                                 VALUES (?, ?, ?, 'A')";
                    $db->ejecutar($sql_pago, [$movimientoID, $formapagoID, $monto_fp]);
                }
            }

            // Validar stock disponible para todos los productos en el carrito
            foreach ($items as $item) {
                $pID = intval($item['productoID'] ?? 0);
                $cant = intval($item['cantidad'] ?? 0);
                if (!$pID || $cant <= 0)
                    continue;

                $prod = $db->obtenerFila("SELECT nombre, stock FROM productos WHERE productoID = ? AND empresaID = ? AND _estado = 'A'", [$pID, $empresaID]);
                if (!$prod) {
                    throw new Exception("El producto seleccionado no existe.");
                }
                if ($prod['stock'] < $cant) {
                    throw new Exception("Stock insuficiente para '{$prod['nombre']}'. Disponible: {$prod['stock']} unidad(es).");
                }
            }

            // Insertar detalles de venta y actualizar stock
            foreach ($items as $item) {
                $subtotal = $item['cantidad'] * $item['precio'];

                $sql_detalle = "INSERT INTO movimiento_detalles (movimientoID, productoID, cantidad, precio_unitario, subtotal, _estado)
                                VALUES (?, ?, ?, ?, ?, 'A')";
                $db->ejecutar($sql_detalle, [$movimientoID, $item['productoID'], $item['cantidad'], $item['precio'], $subtotal]);

                $sql_update_stock = "UPDATE productos SET stock = GREATEST(0, stock - ?) WHERE productoID = ? AND empresaID = ?";
                $db->ejecutar($sql_update_stock, [$item['cantidad'], $item['productoID'], $empresaID]);
            }

            // Actualizar caja_tienda
            $sql_update_caja = "UPDATE caja_tienda 
                                SET saldo_efectivo = saldo_efectivo + ?,
                                    saldo_qr = saldo_qr + ?,
                                    saldo_total = saldo_total + ?,
                                    _fec_modificacion = NOW()
                                WHERE empresaID = ? AND _estado = 'A'";
            $db->ejecutar($sql_update_caja, [$monto_efectivo, $monto_qr, $monto_total, $empresaID]);

            $db->commit();

            echo json_encode(['status' => 'ok', 'mensaje' => 'Venta registrada exitosamente']);
        } catch (Exception $e) {
            $db->rollBack();
            echo json_encode(['status' => 'error', 'mensaje' => 'Error al registrar venta: ' . $e->getMessage()]);
        }
    }
} else {
    echo json_encode(['status' => 'error', 'mensaje' => 'Método no permitido']);
}
