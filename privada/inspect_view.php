<?php
require_once("../conexion.php");
$res = $db->obtenerTodo("DESCRIBE v_movimientos_caja");
echo "<pre>";
print_r($res);
echo "</pre>";
?>
