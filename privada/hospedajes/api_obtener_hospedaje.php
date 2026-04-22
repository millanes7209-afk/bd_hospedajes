<?php
session_start();
require_once("../../conexion.php");

header('Content-Type: application/json');

$hospedajeID = $_GET['hospedajeID'] ?? null;

if (!$hospedajeID) {
    echo json_encode(['error' => 'Falta hospedajeID']);
    exit;
}

try {
    // 1. Obtener datos básicos del hospedaje y número de habitación
    $sqlH = "SELECT h.estado, h.checkout, h.observaciones, r.numero AS habitacion_numero
             FROM hospedajes h
             JOIN habitaciones r ON h.habitacionID = r.habitacionID
             WHERE h.hospedajeID = ? AND h._estado <> 'X'";
    $hospedaje = $db->obtenerFila($sqlH, [$hospedajeID]);

    if (!$hospedaje) {
        throw new Exception("Hospedaje no encontrado.");
    }

    // 2. Obtener nombres de clientes vinculados
    $sqlC = "SELECT CONCAT_WS(' ', c.apellido1, c.apellido2, c.nombres) as nombre_completo
             FROM hospedajes_clientes hc
             JOIN clientes c ON hc.clienteID = c.clienteID
             WHERE hc.hospedajeID = ? AND hc._estado <> 'X'";
    $clientes = $db->obtenerTodo($sqlC, [$hospedajeID]);

    // 3. Obtener detalle de movimientos financieros (pagos)
    $sqlM = "SELECT m.movimientoID, m.monto, m.formapagoID
             FROM movimientos m
             WHERE m.referenciaID = ? 
             AND m.categoria = 'HOSPEDAJE' 
             AND m._estado <> 'X'";
    $movimientosInfo = $db->obtenerTodo($sqlM, [$hospedajeID]);

    // Calcular suma total para el resumen
    $total_pagado = 0;
    foreach($movimientosInfo as $mov) {
        $total_pagado += $mov['monto'];
    }

    // Consolidar respuesta
    $respuesta = [
        'hospedaje' => $hospedaje,
        'clientes' => array_column($clientes, 'nombre_completo'),
        'total_pagado' => $total_pagado,
        'movimientos' => $movimientosInfo
    ];

    echo json_encode($respuesta);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
