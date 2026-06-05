<?php
session_start();
require_once("../../conexion.php");

header('Content-Type: application/json');

if (!isset($_SESSION['sesion_id_usuario']) || !in_array($_SESSION['sesion_rol'], ['ADMINISTRADOR', 'PROPIETARIO'])) {
    echo json_encode(['error' => 'Acceso Denegado']);
    exit();
}

$empresaID = $_SESSION['empresaID'];
$fecha_inicio = $_GET['inicio'] ?? date('Y-m-d', strtotime('-7 days'));
$fecha_fin = $_GET['fin'] ?? date('Y-m-d');

try {
    $datos = [];

    // 1. Ingresos vs Egresos por día (Curva Financiera)
    $vista = $db->getVistaMovimientos();
    $sql_finanzas = "SELECT DATE(fecha) as fecha, tipo, SUM(monto) as total
                     FROM $vista as t
                     WHERE empresaID = ? AND DATE(fecha) BETWEEN ? AND ?
                     GROUP BY DATE(fecha), tipo
                     ORDER BY fecha ASC";
    $datos['finanzas'] = $db->obtenerTodo($sql_finanzas, [$empresaID, $fecha_inicio, $fecha_fin]);

    // 2. Métodos de Pago
    $sql_pagos = "SELECT forma_pago as metodo, SUM(monto) as total
                  FROM $vista as t
                  WHERE empresaID = ? AND tipo = 'INGRESO' AND DATE(fecha) BETWEEN ? AND ?
                  GROUP BY forma_pago";
    $datos['metodos_pago'] = $db->obtenerTodo($sql_pagos, [$empresaID, $fecha_inicio, $fecha_fin]);

    // 3. Tipos de Habitación más usados
    $sql_habs = "SELECT t.nombre as tipo, COUNT(h.hospedajeID) as cantidad
                 FROM hospedajes h
                 INNER JOIN habitaciones hab ON h.habitacionID = hab.habitacionID
                 INNER JOIN tipo_habitaciones t ON hab.tipohabitacionID = t.tipohabitacionID
                 WHERE h.empresaID = ? AND DATE(h._fec_insercion) BETWEEN ? AND ? AND h._estado <> 'X'
                 GROUP BY t.tipohabitacionID
                 ORDER BY cantidad DESC";
    $datos['tipos_habitacion'] = $db->obtenerTodo($sql_habs, [$empresaID, $fecha_inicio, $fecha_fin]);

    // 4. Rendimiento de Personal (Productividad)
    $sql_personal = "SELECT u.usuario as recepcionista, 
                            COUNT(m.movimientoID) as operaciones, 
                            SUM(CASE WHEN m.tipo = 'INGRESO' THEN m.monto ELSE 0 END) as ingresos,
                            SUM(CASE WHEN m.tipo = 'EGRESO' THEN m.monto ELSE 0 END) as egresos,
                            (SUM(CASE WHEN m.tipo = 'INGRESO' THEN m.monto ELSE 0 END) - SUM(CASE WHEN m.tipo = 'EGRESO' THEN m.monto ELSE 0 END)) as neto
                     FROM $vista m
                     INNER JOIN usuarios u ON m.usuarioID = u.usuarioID
                     WHERE m.empresaID = ? AND DATE(m.fecha) BETWEEN ? AND ?
                     GROUP BY m.usuarioID
                     ORDER BY neto DESC";
    $datos['personal'] = $db->obtenerTodo($sql_personal, [$empresaID, $fecha_inicio, $fecha_fin]);

    // ... (Consultas 5, 6, 7 no requieren cambios ya que no usan movimientos) ...
    // [COPIANDO DE VUELTA PARA MANTENER ESTRUCTURA]
    // 5. Clientes Más Frecuentes
    $sql_clientes = "SELECT CONCAT(c.nombres, ' ', c.apellido1) as cliente, COUNT(hc.hospedajeID) as visitas
                     FROM hospedajes_clientes hc
                     INNER JOIN clientes c ON hc.clienteID = c.clienteID
                     INNER JOIN hospedajes h ON hc.hospedajeID = h.hospedajeID
                     WHERE h.empresaID = ? AND DATE(h._fec_insercion) BETWEEN ? AND ? AND h._estado <> 'X'
                     GROUP BY c.clienteID
                     ORDER BY visitas DESC
                     LIMIT 10";
    $datos['clientes_frecuentes'] = $db->obtenerTodo($sql_clientes, [$empresaID, $fecha_inicio, $fecha_fin]);

    // 6. Procedencia de los Clientes (Países)
    $sql_procedencia_pais = "SELECT p.nombre as origen, COUNT(hc.hospedajeID) as cantidad
                        FROM hospedajes_clientes hc
                        INNER JOIN clientes c ON hc.clienteID = c.clienteID
                        INNER JOIN paises p ON c.paisID = p.paisID
                        INNER JOIN hospedajes h ON hc.hospedajeID = h.hospedajeID
                        WHERE h.empresaID = ? AND DATE(h._fec_insercion) BETWEEN ? AND ? AND h._estado <> 'X'
                        GROUP BY p.paisID
                        ORDER BY cantidad DESC
                        LIMIT 10";
    $datos['procedencia_pais'] = $db->obtenerTodo($sql_procedencia_pais, [$empresaID, $fecha_inicio, $fecha_fin]);

    // 7. Procedencia de los Clientes (Departamentos)
    $sql_procedencia_depto = "SELECT c.lugar_nacimiento as origen, COUNT(hc.hospedajeID) as cantidad
                        FROM hospedajes_clientes hc
                        INNER JOIN clientes c ON hc.clienteID = c.clienteID
                        INNER JOIN hospedajes h ON hc.hospedajeID = h.hospedajeID
                        WHERE h.empresaID = ? AND DATE(h._fec_insercion) BETWEEN ? AND ? AND h._estado <> 'X'
                          AND c.lugar_nacimiento IS NOT NULL AND TRIM(c.lugar_nacimiento) <> ''
                        GROUP BY c.lugar_nacimiento
                        ORDER BY cantidad DESC
                        LIMIT 10";
    $datos['procedencia_depto'] = $db->obtenerTodo($sql_procedencia_depto, [$empresaID, $fecha_inicio, $fecha_fin]);

    // 8. Categorías de Movimientos (Ingresos) - Usando cuenta_nombre
    $sql_categorias_in = "SELECT cuenta_nombre as categoria, SUM(monto) as total
                       FROM $vista as t
                       WHERE empresaID = ? AND tipo = 'INGRESO' AND DATE(fecha) BETWEEN ? AND ?
                       GROUP BY cuenta_nombre
                       ORDER BY total DESC";
    $datos['categorias_ingreso'] = $db->obtenerTodo($sql_categorias_in, [$empresaID, $fecha_inicio, $fecha_fin]);

    // 9. Categorías de Movimientos (Egresos) - Usando cuenta_nombre
    $sql_categorias_out = "SELECT cuenta_nombre as categoria, SUM(monto) as total
                       FROM $vista as t
                       WHERE empresaID = ? AND tipo = 'EGRESO' AND DATE(fecha) BETWEEN ? AND ?
                       GROUP BY cuenta_nombre
                       ORDER BY total DESC";
    $datos['categorias_egreso'] = $db->obtenerTodo($sql_categorias_out, [$empresaID, $fecha_inicio, $fecha_fin]);

    echo json_encode($datos);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>