<?php
error_reporting(E_ALL);
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

$accion = $_GET['accion'] ?? '';

if ($accion === 'listar') {
    $limite = intval($_GET['limite'] ?? 50);
    $offset = intval($_GET['offset'] ?? 0);
    $tipo = trim($_GET['tipo'] ?? 'TODAS');
    $desde = trim($_GET['desde'] ?? '');
    $hasta = trim($_GET['hasta'] ?? '');

    $movimientos = [];

    // 1. Obtener movimientos (VENTA / COMPRA)
    if (in_array($tipo, ['TODAS', 'VENTA', 'COMPRA'])) {
        $where = ["m.empresaID = ?", "m._estado = 'A'"];
        $params = [$empresaID];

        if ($tipo !== 'TODAS') {
            $where[] = "m.tipo = ?";
            $params[] = $tipo;
        }
        if (!empty($desde)) {
            $where[] = "DATE(m._fec_insercion) >= ?";
            $params[] = $desde;
        }
        if (!empty($hasta)) {
            $where[] = "DATE(m._fec_insercion) <= ?";
            $params[] = $hasta;
        }

        $whereClause = implode(" AND ", $where);
        $sqlMov = "SELECT
                    m.movimientoID AS transacciontiendaID,
                    m.movimientoID,
                    m.tipo,
                    m.monto_total,
                    DATE_FORMAT(m._fec_insercion, '%Y-%m-%d') AS fecha,
                    DATE_FORMAT(m._fec_insercion, '%H:%i:%s') AS hora,
                    m._fec_insercion,
                    COALESCE(CONCAT(e.nombres, ' ', e.apellidos), u.usuario, 'N/A') AS usuario_nombre,
                    '' AS motivo,
                    0 AS es_retiro
                FROM movimientos_tienda m
                LEFT JOIN usuarios u ON u.usuarioID = m.usuarioID
                LEFT JOIN empleados e ON e.empleadoID = u.empleadoID
                WHERE $whereClause
                ORDER BY m._fec_insercion DESC";

        $resMov = $db->obtenerTodo($sqlMov, $params);
        if (is_array($resMov)) {
            foreach ($resMov as &$mov) {
                $mov['detalles'] = $db->obtenerTodo(
                    "SELECT md.cantidad, md.precio_unitario, md.subtotal, p.nombre, p.medida
                     FROM movimiento_detalles md
                     LEFT JOIN productos p ON p.productoID = md.productoID
                     WHERE md.movimientoID = ? AND md._estado = 'A'",
                    [$mov['movimientoID']]
                );

                if ($mov['tipo'] === 'VENTA') {
                    $mov['pagos'] = $db->obtenerTodo(
                        "SELECT fp.tipo AS forma, tp.monto
                         FROM tienda_pagos tp
                         LEFT JOIN formas_pago fp ON fp.formapagoID = tp.formapagoID
                         WHERE tp.movimientoID = ? AND tp._estado = 'A'",
                        [$mov['movimientoID']]
                    );
                    $formasArray = array_unique(array_filter(array_map(function ($p) {
                        return strtoupper($p['forma'] ?? '');
                    }, $mov['pagos'])));
                    $mov['forma_pago_nombres'] = !empty($formasArray) ? implode(' + ', $formasArray) : 'EFECTIVO';
                } else {
                    $mov['pagos'] = [];
                    $mov['forma_pago_nombres'] = 'EFECTIVO';
                }
                $movimientos[] = $mov;
            }
            unset($mov);
        }
    }

    // 2. Obtener retiros (RETIRO)
    if (in_array($tipo, ['TODAS', 'RETIRO'])) {
        $whereRet = ["r.empresaID = ?", "r._estado = 'A'"];
        $paramsRet = [$empresaID];

        if (!empty($desde)) {
            $whereRet[] = "DATE(r._fec_insercion) >= ?";
            $paramsRet[] = $desde;
        }
        if (!empty($hasta)) {
            $whereRet[] = "DATE(r._fec_insercion) <= ?";
            $paramsRet[] = $hasta;
        }

        $whereClauseRet = implode(" AND ", $whereRet);
        $sqlRet = "SELECT
                    r.retiroID AS transacciontiendaID,
                    r.retiroID AS movimientoID,
                    'RETIRO' AS tipo,
                    r.monto_total,
                    r.monto_efectivo,
                    r.monto_qr,
                    r.motivo,
                    DATE_FORMAT(r._fec_insercion, '%Y-%m-%d') AS fecha,
                    DATE_FORMAT(r._fec_insercion, '%H:%i:%s') AS hora,
                    r._fec_insercion,
                    COALESCE(CONCAT(e.nombres, ' ', e.apellidos), u.usuario, 'N/A') AS usuario_nombre,
                    1 AS es_retiro
                FROM tienda_retiros r
                LEFT JOIN usuarios u ON u.usuarioID = r.usuarioID
                LEFT JOIN empleados e ON e.empleadoID = u.empleadoID
                WHERE $whereClauseRet
                ORDER BY r._fec_insercion DESC";

        $resRet = $db->obtenerTodo($sqlRet, $paramsRet);
        if (is_array($resRet)) {
            foreach ($resRet as &$ret) {
                $ef = (float) ($ret['monto_efectivo'] ?? 0);
                $qr = (float) ($ret['monto_qr'] ?? 0);
                $metodos = [];
                if ($ef > 0)
                    $metodos[] = 'EFECTIVO';
                if ($qr > 0)
                    $metodos[] = 'QR';
                $ret['forma_pago_nombres'] = !empty($metodos) ? implode(' + ', $metodos) : 'EFECTIVO';
                $motivoTxt = !empty($ret['motivo']) ? $ret['motivo'] : 'Retiro de ganancias';
                $ret['detalles'] = [
                    [
                        'nombre' => 'Motivo: ' . $motivoTxt,
                        'medida' => null,
                        'cantidad' => 1,
                        'precio_unitario' => $ret['monto_total'],
                        'subtotal' => $ret['monto_total']
                    ]
                ];
                $ret['pagos'] = [];
                $movimientos[] = $ret;
            }
            unset($ret);
        }
    }

    // Ordenar por fecha descendente
    usort($movimientos, function ($a, $b) {
        return strtotime($b['_fec_insercion']) - strtotime($a['_fec_insercion']);
    });

    // Paginacion
    $resultado = array_slice($movimientos, $offset, $limite);

    echo json_encode(['status' => 'ok', 'data' => $resultado]);

} else {
    echo json_encode(['status' => 'error', 'mensaje' => 'Accion no valida: ' . htmlspecialchars($accion)]);
}
