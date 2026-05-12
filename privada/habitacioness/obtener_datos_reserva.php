<?php
session_start();
require_once("../../conexion.php");

$habitacionID = $_GET['habitacionID'] ?? null;
$empresaID = $_SESSION['empresaID'];

if (!$habitacionID) {
    echo json_encode(['error' => true, 'message' => 'ID de habitación no proporcionado.']);
    exit();
}

// Consultar los datos de la reserva asociada a la habitación
$sql = "SELECT
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
            AND r.empresaID = ?
            AND r.estado2 = 'ACTIVO'
            AND r._estado <> 'X'";

$reserva = $db->obtenerFila($sql, [$habitacionID, $empresaID]);

if ($reserva) {
    $reserva['habitacionID'] = $habitacionID;
    echo json_encode($reserva);
} else {
    echo json_encode(['error' => true, 'message' => 'No se encontró una reserva activa para esta habitación.']);
}
?>
