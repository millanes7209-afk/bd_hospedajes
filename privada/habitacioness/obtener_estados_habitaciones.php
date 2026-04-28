<?php
session_start();
require_once("../../conexion.php");

// Consulta para obtener el estado actual de las habitaciones e información inteligente de montos
$sql = "SELECT hab.habitacionID, hab.estado, hab.numero, th.precio as precio_base,
                (SELECT CONCAT(c.nombres, ' ', c.apellido1) 
                 FROM hospedajes h 
                 JOIN hospedajes_clientes hc ON h.hospedajeID = hc.hospedajeID 
                 JOIN clientes c ON hc.clienteID = c.clienteID 
                 WHERE h.habitacionID = hab.habitacionID 
                 AND h.estado IN ('ACTIVO', 'DEUDA') AND h._estado <> 'X' AND hc._estado <> 'X' AND c._estado <> 'X'
                 ORDER BY h.hospedajeID DESC LIMIT 1) AS cliente_activo,
                (SELECT h.checkout 
                 FROM hospedajes h 
                 WHERE h.habitacionID = hab.habitacionID 
                 AND h.estado IN ('ACTIVO', 'DEUDA') AND h._estado <> 'X'
                 ORDER BY h.hospedajeID DESC LIMIT 1) AS checkout_activo,
                (SELECT h.monto 
                 FROM hospedajes h 
                 WHERE h.habitacionID = hab.habitacionID 
                 AND h.estado IN ('ACTIVO', 'DEUDA') AND h._estado <> 'X'
                 ORDER BY h.hospedajeID DESC LIMIT 1) AS monto_hospedaje
        FROM habitaciones hab 
        JOIN tipo_habitaciones th ON hab.tipohabitacionID = th.tipohabitacionID
        WHERE hab._estado <> 'X' 
        ORDER BY hab.numero ASC";
$rs = $db->GetAll($sql);

$habitaciones = array();

foreach ($rs as $habitacion) {
    
    // LÓGICA SMART (Sincronizada con habitaciones.php)
    if ($habitacion['estado'] === 'OCUPADA' && empty($habitacion['cliente_activo'])) {
        $habitacion['estado'] = 'LIMPIEZA'; 
        // Parche instantáneo silencioso a la BD para sincronizar la realidad:
        $db->Execute("UPDATE habitaciones SET estado = 'LIMPIEZA' WHERE habitacionID = ?", [$habitacion['habitacionID']]);
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

    // Devolver los datos en formato JSON
header('Content-Type: application/json');
echo json_encode($habitaciones);
?>
