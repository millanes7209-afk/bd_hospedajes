<?php
require_once("conexion.php");
$res = $db->obtenerTodo("DESCRIBE hospedajes_auditoria_montos");
echo json_encode($res, JSON_PRETTY_PRINT);
?>
