<?php
require_once("conexion.php");
$res = $db->obtenerTodo("SHOW COLUMNS FROM hospedajes_auditoria_montos LIKE 'hospedajeID'");
print_r($res);
?>
