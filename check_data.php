<?php
require_once("conexion.php");
print_r($db->obtenerTodo("SELECT * FROM hospedajes_auditoria_montos LIMIT 5"));
?>
