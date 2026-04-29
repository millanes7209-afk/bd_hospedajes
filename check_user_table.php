<?php
require_once("conexion.php");
$res = $db->obtenerTodo("SHOW TABLES LIKE '%usua%'");
print_r($res);
$res2 = $db->obtenerTodo("SHOW TABLES LIKE '%empleado%'");
print_r($res2);
?>
