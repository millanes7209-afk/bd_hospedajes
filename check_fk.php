<?php
require_once("conexion.php");
echo "ESTRUCTURA HOSPEDAJES:\n";
print_r($db->obtenerTodo("DESCRIBE hospedajes"));
echo "\nESTRUCTURA HOSPEDAJES_AUDITORIA_MONTOS:\n";
print_r($db->obtenerTodo("DESCRIBE hospedajes_auditoria_montos"));
?>
