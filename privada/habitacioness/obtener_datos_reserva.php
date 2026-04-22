<?php
require_once("../../conexion.php");

// Obtener el habitacionID desde el parámetro GET
$habitacionID = $_GET['habitacionID'];

// Consultar los datos de la reserva asociada a la habitación
$sql = $db->Prepare("SELECT
                        r.reservaID,
                        r.monto_reserva AS monto_total,
                        r.monto_pagado,
                        r.monto_pendiente,
                        c.clienteID,
                        CONCAT(c.nombres, ' ', c.apellidos) AS cliente,
                        h.numero
                    FROM
                        reservas r
                        INNER JOIN clientes c ON r.clienteID = c.clienteID
                        INNER JOIN habitaciones h ON r.habitacionID = h.habitacionID
                    WHERE
                        r.habitacionID = ?
                        AND r.estado2 = 'ACTIVO'
                        AND r._estado <> 'X'");

$rs = $db->GetAll($sql, array($habitacionID));

if ($rs) {
    $data = $rs[0];
    $data['habitacionID'] = $habitacionID;
    echo json_encode($data);
} else {
    echo json_encode(['error' => true, 'message' => 'No se encontró una reserva activa para esta habitación.']);
}
?>
