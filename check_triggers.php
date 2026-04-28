<?php
require_once("conexion.php");
$res = $db->obtenerTodo("SHOW TRIGGERS LIKE 'opciones'");
print_r($res);
?>
