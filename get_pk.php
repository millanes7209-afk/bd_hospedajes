<?php
require_once("conexion.php");
$res = $db->obtenerTodo("DESCRIBE hospedajes_auditoria_montos");
echo $res[0]['Field'];
?>
