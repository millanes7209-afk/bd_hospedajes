<?php
session_start();
require_once("../../conexion.php");

$empresaID = $_SESSION['empresaID'];

// Consulta para obtener el estado actual de las habitaciones e información inteligente de montos
// FILTRADO ESTRICTO POR EMPRESA
$sql = "SELECT hab.habitacionID, hab.estado, hab.numero, th.precio as precio_base, th.nombre as tipo,
                hab.bano, hab.tv, hab.ventilador, hab.descripcion as habitacion_descripcion,
                (SELECT GROUP_CONCAT(CONCAT('- ', c.nombres, ' ', c.apellido1) SEPARATOR '<br>') 
                 FROM hospedajes h 
                 JOIN hospedajes_clientes hc ON h.hospedajeID = hc.hospedajeID 
                 JOIN clientes c ON hc.clienteID = c.clienteID 
                 WHERE h.habitacionID = hab.habitacionID 
                 AND h.empresaID = ?
                 AND h.estado = 'ACTIVO' AND h._estado <> 'X' AND hc._estado <> 'X' AND c._estado <> 'X'
                 ORDER BY h.hospedajeID DESC LIMIT 1) AS cliente_activo,
                (SELECT h.checkout 
                 FROM hospedajes h 
                 WHERE h.habitacionID = hab.habitacionID 
                 AND h.empresaID = ?
                 AND h.estado = 'ACTIVO' AND h._estado <> 'X'
                 ORDER BY h.hospedajeID DESC LIMIT 1) AS checkout_activo,
                (SELECT h.monto 
                 FROM hospedajes h 
                 WHERE h.habitacionID = hab.habitacionID 
                 AND h.empresaID = ?
                 AND h.estado = 'ACTIVO' AND h._estado <> 'X'
                 ORDER BY h.hospedajeID DESC LIMIT 1) AS monto_hospedaje,
                (SELECT h.observaciones 
                 FROM hospedajes h 
                 WHERE h.habitacionID = hab.habitacionID 
                 AND h.empresaID = ?
                 AND h.estado = 'ACTIVO' AND h._estado <> 'X'
                 ORDER BY h.hospedajeID DESC LIMIT 1) AS observaciones_activo,
                (SELECT h.precio_diario 
                 FROM hospedajes h 
                 WHERE h.habitacionID = hab.habitacionID 
                 AND h.empresaID = ?
                 AND h.estado = 'ACTIVO' AND h._estado <> 'X'
                 ORDER BY h.hospedajeID DESC LIMIT 1) AS precio_diario_pactado,
                (SELECT h.hospedajeID
                 FROM hospedajes h
                 WHERE h.habitacionID = hab.habitacionID
                 AND h.empresaID = ?
                 AND h.estado = 'ACTIVO'
                 AND h._estado <> 'X'
                 LIMIT 1) AS hospedaje_activo_id
        FROM habitaciones hab 
        JOIN tipo_habitaciones th ON hab.tipohabitacionID = th.tipohabitacionID
        WHERE hab._estado <> 'X' 
        AND hab.empresaID = ?
        ORDER BY hab.numero ASC";

$rs = $db->obtenerTodo($sql, [$empresaID, $empresaID, $empresaID, $empresaID, $empresaID, $empresaID]);

$habitaciones = array();

foreach ($rs as $habitacion) {

    // LÓGICA DE PERSISTENCIA: Si el checkout venció y la habitación está OCUPADA, la pasamos a DEUDA
    $now_stamp = time();
    if ($habitacion['estado'] === 'OCUPADA' && !empty($habitacion['checkout_activo']) && strtotime($habitacion['checkout_activo']) < $now_stamp) {
        $db->ejecutar("UPDATE habitaciones SET estado = 'DEUDA' WHERE habitacionID = ? AND empresaID = ?", [$habitacion['habitacionID'], $empresaID]);
    }

    // LÓGICA DE DEUDA: Calcular días de exceso si corresponde
    $dias_deuda = 0;
    if ($habitacion['estado'] === 'DEUDA' && !empty($habitacion['checkout_activo'])) {
        $checkout_obj = new DateTime($habitacion['checkout_activo']);
        $ahora_obj = new DateTime();
        $dias_deuda = 1;
        $iter_date_limite = clone $checkout_obj;
        $iter_date_limite->modify('+1 day');
        $iter_date_limite->setTime(13, 0, 0);
        while ($iter_date_limite <= $ahora_obj) {
            $dias_deuda++;
            $iter_date_limite->modify('+1 day');
        }
    }

    $habitaciones[] = array(
        'habitacionID' => $habitacion['habitacionID'],
        'estado' => $habitacion['estado'],
        'numero' => $habitacion['numero'],
        'tipo' => $habitacion['tipo'],
        'cliente_activo' => $habitacion['cliente_activo'],
        'checkout_activo' => $habitacion['checkout_activo'],
        'precio_base' => $habitacion['precio_base'],
        'dias_deuda' => $dias_deuda,
        'precio_inteligente' => $habitacion['precio_diario_pactado'] ?? $habitacion['precio_base'],
        'bano' => $habitacion['bano'],
        'tv' => $habitacion['tv'],
        'ventilador' => $habitacion['ventilador'],
        'habitacion_descripcion' => $habitacion['habitacion_descripcion'],
        'observaciones_activo' => $habitacion['observaciones_activo']
    );
}

header('Content-Type: application/json');
echo json_encode($habitaciones);
?>