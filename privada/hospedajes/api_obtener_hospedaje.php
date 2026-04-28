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
    $sqlH = "SELECT h.estado, h.checkout, h.observaciones, r.numero AS habitacion_numero, h.cajaID, h.empresaID, h._usuario
             FROM hospedajes h
             JOIN habitaciones r ON h.habitacionID = r.habitacionID
             WHERE h.hospedajeID = ? AND h._estado <> 'X'";
    $hospedaje = $db->obtenerFila($sqlH, [$hospedajeID]);

    if (!$hospedaje) {
        throw new Exception("Hospedaje no encontrado.");
    }

    // Determinar si el usuario actual es dueño de la misma caja/turno en la que se creó
    $es_propietario = false;
    $sesion_caja = $_SESSION['caja_abierta_id'] ?? null;
    $sesion_empresa = $_SESSION['empresaID'] ?? null;
    $sesion_usuario = $_SESSION['sesion_id_usuario'] ?? null;

    if ($hospedaje['cajaID'] == $sesion_caja && $hospedaje['empresaID'] == $sesion_empresa && $hospedaje['_usuario'] == $sesion_usuario) {
        $es_propietario = true;
    }

    // 2. Obtener nombres de clientes vinculados
    $sqlC = "SELECT CONCAT_WS(' ', c.apellido1, c.apellido2, c.nombres) as nombre_completo
             FROM hospedajes_clientes hc
             JOIN clientes c ON hc.clienteID = c.clienteID
             WHERE hc.hospedajeID = ? AND hc._estado <> 'X'";
    $clientes = $db->obtenerTodo($sqlC, [$hospedajeID]);

    // 3. Obtener detalle de pagos desde la nueva estructura (Join con ingresos e ingreso_pagos)
    $sqlM = "SELECT ip.ingresopagoID as movimientoID, ip.monto, ip.formapagoID
             FROM ingreso_pagos ip
             JOIN ingresos i ON ip.ingresoID = i.ingresoID
             JOIN hospedajes h ON h.ingresoID = i.ingresoID
             WHERE h.hospedajeID = ? AND i._estado <> 'X'";
             
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
        'movimientos' => $movimientosInfo, // ip.ingresopagoID como movimientoID
        'es_propietario' => $es_propietario
    ];

    echo json_encode($respuesta);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
