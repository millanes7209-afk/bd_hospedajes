<?php
session_start();
require_once("../../conexion.php");

// Obtener la fecha y hora actual
$fecha_actual = date("Y-m-d H:i:s");

// Consultar notificaciones pendientes cuya fecha programada sea igual o anterior a la fecha actual
$sql = "SELECT * FROM notificaciones WHERE estado = 'pendiente' AND fecha_programada <= ? ORDER BY fecha_programada ASC";
$rs = $db->obtenerTodo($sql, array($fecha_actual));

if ($rs) {
    // Devolver las notificaciones pendientes en formato JSON
    echo json_encode($rs);
} else {
    echo json_encode(array()); // No hay notificaciones pendientes
}
?>
