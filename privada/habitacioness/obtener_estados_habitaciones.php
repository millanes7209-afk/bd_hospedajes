<?php
session_start();
require_once("../../conexion.php");

$empresaID = $_SESSION['empresaID'];

// Consulta para obtener el estado actual de las habitaciones e información inteligente de montos
// FILTRADO ESTRICTO POR EMPRESA
$sql = "SELECT hab.habitacionID, hab.estado, hab.numero, th.precio as precio_base,
                (SELECT CONCAT(c.nombres, ' ', c.apellido1) 
                 FROM hospedajes h 
                 JOIN hospedajes_clientes hc ON h.hospedajeID = hc.hospedajeID 
                 JOIN clientes c ON hc.clienteID = c.clienteID 
                 WHERE h.habitacionID = hab.habitacionID 
                 AND h.empresaID = ?
                 AND h.estado IN ('ACTIVO', 'DEUDA') AND h._estado <> 'X' AND hc._estado <> 'X' AND c._estado <> 'X'
                 ORDER BY h.hospedajeID DESC LIMIT 1) AS cliente_activo,
                (SELECT h.checkout 
                 FROM hospedajes h 
                 WHERE h.habitacionID = hab.habitacionID 
                 AND h.empresaID = ?
                 AND h.estado IN ('ACTIVO', 'DEUDA') AND h._estado <> 'X'
                 ORDER BY h.hospedajeID DESC LIMIT 1) AS checkout_activo,
                (SELECT h.monto 
                 FROM hospedajes h 
                 WHERE h.habitacionID = hab.habitacionID 
                 AND h.empresaID = ?
                 AND h.estado IN ('ACTIVO') AND h._estado <> 'X'
                 ORDER BY h.hospedajeID DESC LIMIT 1) AS monto_hospedaje
        FROM habitaciones hab 
        JOIN tipo_habitaciones th ON hab.tipohabitacionID = th.tipohabitacionID
        WHERE hab._estado <> 'X' 
        AND hab.empresaID = ?
        ORDER BY hab.numero ASC";

$rs = $db->obtenerTodo($sql, [$empresaID, $empresaID, $empresaID, $empresaID]);

$habitaciones = array();

foreach ($rs as $habitacion) {

    // LÓGICA DE PERSISTENCIA: Si el checkout venció, actualizamos la base de datos REAL
    $now_stamp = time();
    if ($habitacion['estado'] === 'OCUPADA' && !empty($habitacion['checkout_activo']) && strtotime($habitacion['checkout_activo']) < $now_stamp) {
        $db->ejecutar("UPDATE habitaciones SET estado = 'DEUDA' WHERE habitacionID = ?", [$habitacion['habitacionID']]);
    }

    // LÓGICA DE LIMPIEZA AUTOMÁTICA: Si está marcada como OCUPADA pero no tiene hospedaje real vinculado
    if ($habitacion['estado'] === 'OCUPADA' && empty($habitacion['cliente_activo'])) {
        $habitacion['estado'] = 'LIMPIEZA';
        $db->ejecutar("UPDATE habitaciones SET estado = 'LIMPIEZA' WHERE habitacionID = ?", [$habitacion['habitacionID']]);
    }

    $habitaciones[] = array(
        'habitacionID' => $habitacion['habitacionID'],
        'estado' => $habitacion['estado'],
        'numero' => $habitacion['numero'],
        'cliente_activo' => $habitacion['cliente_activo'],
        'checkout_activo' => $habitacion['checkout_activo'],
        'precio_base' => $habitacion['precio_base'],
        'precio_inteligente' => $habitacion['monto_hospedaje'] ?? $habitacion['precio_base']
    );
}

header('Content-Type: application/json');
echo json_encode($habitaciones);
?>