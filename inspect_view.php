<?php
require_once("conexion.php");
$res = $db->obtenerTodo("SHOW CREATE VIEW v_movimientos_caja");
print_r($res);
?>
